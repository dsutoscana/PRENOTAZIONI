<?php

$config=require_once('config.php');

require_once('lib.php');


$NumeroMailPerGruppo = 10;
$NumeroSecondiAttesaPerGruppo = 120;
$NumeroSecondiTraUnaMailQuellaDopo = 5;


if(! Cli() ) exit ("Solo command line !!");


//print_r($argv);

if(count( $argv)==1 || $argv[1]=='help' )
{
  echo $argv[0]."  {MAIL|SPOSTA}  ";
}


if( isset($argv[2]) && $argv[2] == 'FORZA' ) 	LOCCA::Unlock();
if( $argv[1] == 'MAIL' ) 	LeggiMailEInvia();
if( $argv[1] == 'SPOSTA' ) 	Sposta();



die(-1);

function LeggiMailEInvia()
{
	global $NumeroMailPerGruppo, $NumeroSecondiAttesaPerGruppo, $NumeroSecondiTraUnaMailQuellaDopo;

	$a=LOCCA::Lock();


	if($a === false ) exit('file locked.. esco e attendo..');
	elseif( $a === 'NOTOK')
	{
		LOCCA::Unlock();  // al prossimo giro lo trovo vuoto...
		exit ('Lock sbloccato con forza');
	}
	if( $a !== true ) exit('problema con il lock del file') ;

	// loccato... vado con le mail..


	$continuo=true;
	while($continuo)
	{
		$c=0;
		while ( $c < $NumeroMailPerGruppo && $continuo)
		{
			$ris=\triagens\ArangoDb\GetFirstMail();

			//print_r($ris);

			if(  $ris['ris'] &&  !isset($ris[0]['oggetto']) ) $ris[0]['oggetto']='oggetto mail';
			if( $ris['ris'] )
			{
					$risM = \triagens\ArangoDb\InviaMail("", $ris[0]['email'], 'generico', array('oggetto'=>$ris[0]['oggetto'],   'htmlbody'=> $ris[0]['testo'],  'body'=>$ris[0]['testo']  ) );
					if( $risM['ris'] )
					{
						\triagens\ArangoDb\DeleMail( $ris[0]['_key'] );
						//echo "cancello mail ".$ris[0]['_key']."\n";
						sleep( $NumeroSecondiTraUnaMailQuellaDopo );
						$c++;
					}
					else
					{
						$msg=$risM[0];
						$continuo=false;
					}
			}
			else
			{
				$msg=$ris[0];
				$continuo=false;
			}
		}
		if( $continuo ) sleep( $NumeroSecondiAttesaPerGruppo );
	}

	LOCCA::Unlock();
	exit($msg);




}


function Sposta()
{

    $ris=\triagens\ArangoDb\SpostaPrenotazioneInSotrico();
    if( $ris['ris'] )       $testoris=0;
    else                    $testoris="Errore nello spostamento delle prenotazioni nel DB storico : Messasggio di errore : !". $ris[0];

    exit($testoris);
}




class LOCCA
{

		protected static $file_lock="/tmp/___lock_invio_mail____";
		// ritorna si se puo' loccare, NO se non puo' loccare
		public function Lock(  $file_lock="/tmp/___lock_invio_mail____", $deltatempo=30 )
		{

      		if (@mkdir($file_lock, 0700))  // acuqisisco
      		{
        			$fh = fopen( $file_lock."/tempo.txt", 'x' );
        			fprintf($fh, "%d\n", time());
        			fflush($fh);
        			fclose($fh);
        			return true;
      		}
      		else //esiste...
      		{
      			$fh = fopen( $file_lock."/tempo.txt", 'r' );
      			$t = fscanf($fh, "%d\n");
        		fflush($fh);
      			fclose($fh);

      			//\triagens\ArangoDb\sdie1($t);

      			if( time() - $t[0] > $deltatempo) return "NOTOK";
      			else return false;
      		}

		}

		public function Time($file_lock="/tmp/___lock_invio_mail____")  // refresho il tempo per dire che sono vivo..
		{
			$fh = fopen( $file_lock."/tempo.txt", 'x' );
			fprintf($fh, "%d\n", time());
    		fflush($fh);
			fclose($fh);
		}

		public function Unlock($file_lock="/tmp/___lock_invio_mail____") // sblocca il lock
		{
			@unlink( $file_lock."/tempo.txt");
			@rmdir($file_lock);
		}
}
/*



   locca OK
   NOOK -> esiste lock OK
       -> esiste lock non ok -> azione -> forza lock

   refresh lock



 */

class FileLocker
{

  protected static $loc_files = array();

  public static function lockFile($file_name, $delta=100)
  {
    $loc_file = fopen($file_name, 'c');
    if ( !$loc_file )  throw new \Exception('Can\'t create lock file!');

    if ( $wait )	 $lock = flock($loc_file, LOCK_EX);
    else 			$lock = flock($loc_file, LOCK_EX | LOCK_NB);

    if ( $lock )
    {
      self::$loc_files[$file_name] = $loc_file;
      fprintf($loc_file, "%d\n", time());
      return $loc_file;
    }
    else if ( $wait ) throw new \Exception('Can\'t lock file!');
    else return false;
  }

  public static function unlockFile($file_name)
  {
    fclose(self::$loc_files[$file_name]);
    @unlink($file_name);
    unset(self::$loc_files[$file_name]);
  }

}


function Cli()
{
	$sapi_type = substr(php_sapi_name(), 0, 3);
	if ($sapi_type == 'cgi'  ||  $sapi_type == 'apa' ) return false;  	 // apache or cgi
	else return true; 													// cli
}





?>
