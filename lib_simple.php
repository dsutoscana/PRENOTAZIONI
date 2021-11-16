<?php
//https://github.com/spipu/html2pdf


require_once('DB.php');

require_once FILELIB.'/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;




//require 'path/to/PHPMailer/src/Exception.php';
//require 'path/to/PHPMailer/src/PHPMailer.php';
//require 'path/to/PHPMailer/src/SMTP.php';


function Edata( $d )
{

	if( preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $d, $out) != 1 ) return false;

	$g=$out[1]; $m=$out[2]; $a=$out[3];
	if($g<0 || $g> 31 ) return false;
	if($m<0 || $m> 12 ) return false;
	if( $a<1900 || $a> 2100 ) return false;

	if( ($m == 4 || $m == 6 || $m == 11) && $g==31 ) return false;

	if($a % 4 != 0 && $m == 2 && $g > 28 ) return false;
	if($a % 4 == 0 && $m == 2 && $g > 29 ) return false;

	return  true;

}


function convdata($d)
{

	if( is_array($d) )  // se mi arriva un array di date.. restituisco un array di date convertito...
	{
		$ret=array();
		foreach( $d as $DD ) $ret[]=convdata($DD);
		return $ret;
	}


	if( preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $d, $out) == 1 )
	{
		$g=$out[1]; $m=$out[2]; $a=$out[3];
		$b=$a."-".$m."-".$g." 23:59:00 UTC";
		return  strtotime( $b );
	}

	if( preg_match('/\d{10}/', $d, $out) == 1 && 1420000000 < $d && $d < 2000000000) // fra 2015 e 2033
	{
			 //epoch..
			date_default_timezone_set('UTC');
			$ed = new DateTime( date('Y-m-d H:i:s', $d));
			return $ed->format('d/m/Y');
	}
	return $d;
}


function convdataseriale($d)
{

	$dx=$d;
	if( preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $d, $out) == 1 )
	{
		return $out[3].$out[2].$out[1];
	}
	if( preg_match('/^(\d{4})(\d{2})(\d{2})$/', $d, $out) == 1 )
	{
		return $out[3]."/".$out[2]."/".$out[1];
	}


	if(preg_match('/(\d{10})/', $d, $out) == 1 )
	{
		date_default_timezone_set('UTC');
		return date('Ymd', $dx);
	}
	return '';

}


// si verificano e aggiustano i dati per il DB
function verifiche_aggiustamenti( $tabella, $nome, $valore) // qua verifiche varie e aggiustamenti sui dati.
{

	//$valore=$valore1;
	//$valore=str_replace("\n", "", $valore);
	//$valore=str_replace("\r", "", $valore);


	//--------------------------------------------------------------------------------------------------------------------------------------------------------------
	if( $tabella== 'risorse' && ( $nome == 'giorno_part'|| $nome == 'giorno_part_no' ) )  // stringa nel formato "GG/MM/AAAA\nGG/MM/AAAA\nGG/MM/AAAA\n" -> array di date
	{
		$arr=explode("\n", $valore );

		if( count($arr) == 0 ) { return array( 'ris'=>true, 'val' => array() ); }

		$risu=array();
		foreach($arr as $d)
		{
			if($d==null || $d=='') continue;
			$d=trim($d);
			$d = preg_replace('/\s+/', '', $d);
			if( Edata($d)  ) $risu[]=convdata($d);
			else 				{ return array( 'ris'=>false, 'val' => 'Data errata : '.$d ); }

		}
		return array( 'ris'=>true, 'val' => $risu );
	}


	//--------------------------------------------------------------------------------------------------------------------------------------------------------------
	if(  $tabella == 'risorse' && ( $nome == 'giorno_rip_no' || $nome == 'giorno_rip' ) )  // gli elementi di giorno devono essere array.. se sono vuoti si gestisce
	{
		if( !is_array($valore) ||  $valore == null || $valore == '' ) return array( 'ris'=>true, 'val' => array() );
	}




	//--------------------------------------------------------------------------------------------------------------------------------------------------------------
	if( $tabella== 'risorse' &&  $nome == 'attivo_dal_al' )  //arriva  GG/MM/AAAA - GG/MM/AAAA\nGG/MM/AAAA - GG/MM/AAAA\nGG/MM/AAAA - GG/MM/AAAA  li converto in date epoch
	{
		$arr=explode("\n", $valore );

		if( count($arr) == 0 ) { return array( 'ris'=>true, 'val' => array() ); }

		$risu=array();
		foreach($arr as $d)
		{
			if( $d==null || $d=='' ) continue;
			$d=trim($d);
			$d = preg_replace('/\s+/', '', $d);     // toglo gli spazi

			if( preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})[\/\-\*\|](\d{1,2}\/\d{1,2}\/\d{4})/', $d, $out) != 1 ) { return array( 'ris'=>false, 'val' => 'Data errata  GG/MM/AAAA - GG/MM/AAAA  : '.$d ); }

			$d1=$out[1]; $d2=$out[2];

			if( Edata( $d1 ) && Edata( $d2 ) )
			{
				if( preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $d1, $outdd) != 1 ) { return array( 'ris'=>false, 'val' => 'Data errata  GG/MM/AAAA : '.$d1 ); }
				$dd1 = $outdd[3] . $outdd[2] . $outdd[1];

				if( preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $d2, $outdd) != 1 ) { return array( 'ris'=>false, 'val' => 'Data errata  GG/MM/AAAA : '.$d2 ); }
				$dd2 = $outdd[3] . $outdd[2] . $outdd[1];

				if( $dd1>$dd2 ) { return array( 'ris'=>false, 'val' => 'progressione data errata dal <= al  : '.$d ); }

				$risu[]=array(convdata($out[1]), convdata($out[2]));
			}
			else { return array( 'ris'=>false, 'val' => 'Data errata  GG/MM/AAAA - GG/MM/AAAA  : '.$d ); }
		}
		return array( 'ris'=>true, 'val' => $risu );
	}



	//--------------------------------------------------------------------------------------------------------------------------------------------------------------
	if( $tabella == 'servizi' &&  ( $nome == 'aperto_da' || $nome == 'aperto_a' ) )  //arriva  GG/MM/AAAA - GG/MM/AAAA\nGG/MM/AAAA - GG/MM/AAAA\nGG/MM/AAAA - GG/MM/AAAA  li converto in date epoch
	{

		if($valore==null || $valore=='') return array( 'ris'=>true, 'val' => $valore );
		$valore=trim($valore);
		$valore = preg_replace('/\s+/', '', $valore);
		if( Edata($valore)  ) 	return array( 'ris'=>true, 	'val' => convdata($valore) );
		else 	 				return array( 'ris'=>false, 'val' => 'Data errata : '.$valore );

	}





	//--------------------------------------------------------------------------------------------------------------------------------------------------------------
	if( $tabella == 'risorse' &&  ( $nome == 'ris_tempo' || $nome == 'tempo_minimo' ) )  //controllo su tempo massimo di slot.. 23:45 minuti.. 1425
	{

		if( 0<=$valore && $valore <= 1425  ) 	return array( 'ris'=>true, 	'val' => $valore );
		else 	 								return array( 'ris'=>false, 'val' => 'Il numero dei minuti minore di 1426 : '.$valore );

	}

	if( is_string( $valore )) $valore=pp($valore);

	return array( 'ris'=>true, 'val' => $valore );

}


function daiAQL(  $AQL )
{

	switch ($AQL[1])
	{
		case 1: /* dalle prenotazioni .. elenco dei servizi  prenotabili  con controllo matricola */

/*

//se NOT disponibilità && servizio.controllo_matr == SI && servizio___matricola in matricole

if richiedidispo == false OK
else    if s.controllo_matr != 'SI' then OK
        else    if concat( s._key , '___', matri )   IN   mmm  then OK
                else NOK

$query=array('servizi', 1, $ora, $poi, $_SESSION['prenotazione']['matricola'], $_SESSION['disponibilita'] );

*/

$dispo=( $AQL[5]=='SI' ) ? 'true' : 'false';

$q="let richiedidispo = %s
let matri = '%s'
let mmm = (for m in matricole return  m._key)
FOR s IN servizi
FILTER s.aperto_da < %s  && %s < s.aperto_a
FILTER  richiedidispo == true ? true : ( s.controllo_matr != 'SI' ? true : ( concat( s._key , '___', matri )   IN   mmm  ? true : false  )  )
RETURN s ";

			//$q="FOR s IN servizi FILTER s.aperto_da < %s  && %s < s.aperto_a RETURN s";
			return sprintf( $q, $dispo, $AQL[4], $AQL[3], $AQL[2]  );
			break;

		case 2:  /*  seleziona risorsa dato servizio, gruppo e ateneo   da prenotazioni */
				$q="FOR r IN risorse  filter  r.attiva == 'SI'  &&   '%s' IN r.servizi &&  '%s' == r.gruppor  &&  '%s' IN r.ateneo_i  return DISTINCT r";
				return sprintf( $q, $AQL[2], $AQL[3], $AQL[4] );
				break;

		case 3:  /*  conta le prenotazioni esistenti in una data, una risorsa e uno slot  */
				$q="FOR i IN prenotazioni FILTER i.IDR == '%s' && i.data == '%s' && i.slot == '%s' COLLECT WITH COUNT INTO length RETURN length";
				return sprintf( $q, $AQL[2], $AQL[3], $AQL[4]  );
				break;

		case 4:  /* da prenotazioni .. elenco atenei ordinati per chiave..*/
				$q="for i in atenei  sort TO_NUMBER(i.ordine) DESC    return i";
				return $q;
				break;

		case 5:  /* elenco gruppi di risorse  che vanno bene dato servizio e ateneo da prenotazione  */
				$q='FOR r IN risorse   filter  r.attiva == "SI"  &&   "%s" IN r.servizi   &&    "%s" IN r.ateneo_i  return DISTINCT  ( for j in gruppir filter r.gruppor == j.gruppo_risorsa return j )';
				return sprintf( $q, $AQL[2], $AQL[3] );
				break;


		case 6:  /* da input_matricola, ricerca tutte le matrici che iniziano per c  */
				$q="let c = '%s'
				for m in matricole
				filter m._key like concat( c, '%%' )
				return REGEX_REPLACE(m._key, c, '')";
				return sprintf( $q, $AQL[2].'___' );
				break;

		case 7:  /* da ricerca, ricerca prenotazioni  il 9 sono prenotazioni stroriche */
		case 9:


		$arlet=array();
		$arfil=array();

		if(  $AQL['data'] != '')        {  $arlet[]=sprintf("let data = '%s' ", $AQL['data']  );         $arfil[]="filter p.data == data  ";        }
		if(  $AQL['data_da'] != '')     {  $arlet[]=sprintf("let data_da = '%s' ", convdataseriale($AQL['data_da'])  );   $arfil[]="filter  data_da <= dmia  ";        }
		if(  $AQL['data_a'] != '')      {  $arlet[]=sprintf("let data_a = '%s' ", convdataseriale($AQL['data_a'])  );     $arfil[]="filter dmia <= data_a  ";        }

		if(  $AQL['risorse'] != array())     {  $arlet[]=sprintf('let selectrisorse = ["%s"] ', implode (  '", "'  ,array_keys( $AQL['risorse'] ) ) );   $arfil[]="filter p.IDR IN selectrisorse  ";        }
		if(  $AQL['atenei'] != array())      {  $arlet[]=sprintf('let selectatenei = ["%s"] ', implode (  '", "'  , $AQL['atenei'] ) );   $arfil[]="filter p.ateneo IN selectatenei  ";        }
		if(  $AQL['gruppir'] != array())     {  $arlet[]=sprintf('let selectgruppir = ["%s"] ', implode (  '", "'  , $AQL['gruppir'] ) );   $arfil[]="filter p.gruppir IN selectgruppir  ";        }
		if(  $AQL['servizi'] != array())     {  $arlet[]=sprintf('let selectservizi = ["%s"] ', implode (  '", "'  , $AQL['servizi'] ) );   $arfil[]="filter p.servizio IN selectservizi  ";        }

		if(  $AQL['matricola'] !='')    {  $arlet[]=sprintf("let matricola = '%s'  ", $AQL['matricola']  ); $arfil[]="filter p.matricola == matricola  ";        }
		if(  $AQL['cognome'] !='')      {  $arlet[]=sprintf("let cognome = '%s'  ", $AQL['cognome']  );     $arfil[]="filter lower(p.cognome) LIKE lower(cognome)  ";        }
		if(  $AQL['mail'] !='')    	    {  $arlet[]=sprintf("let mail = '%s'  ", $AQL['mail']  );           $arfil[]="filter p.email == mail  ";        }
		if(  $AQL['cf'] !='')    	    {  $arlet[]=sprintf("let cf = '%s'  ", $AQL['cf']  );           	$arfil[]="filter p.cf == cf  ";        }
		if(  $AQL['IDP'] !='')          {  $arlet[]=sprintf("let IDP = '%s'  ", $AQL['IDP']  );             $arfil[]="filter p._key == IDP  ";        }



		$query='';
		foreach($arlet as $l  ) $query .= $l." ";

		if(  $AQL[1] == 7  ) $coll='prenotazioni';
		if(  $AQL[1] == 9  ) $coll='prenotazionistoriche';

		$query .= "for p in " . $coll . "  let dmia = concat( SUBSTRING(p.data, 6, 4),  SUBSTRING(p.data, 3, 2), SUBSTRING(p.data, 0, 2)  )  ";

		foreach($arfil as $f  ) $query .= $f." ";

		$query .= " return p ";

		return $query;
		break;

/*
		    let data = '07/12/2018'
		    let data_da = '20171114'
		    let data_a = '20201231'
		    let selectatenei = [ 'Università degli Studi Firenze', 'Università degli Studi Pisa', 'Università degli Studi Siena' ]
		    let selectservizi = [ 'borse di studio', 'rilascio tessera mensa' ]
		    let selectgruppir = ['Pisa Piazza Cavalieri', 'Pisa Economia Agraria', 'Firenze Nord']
		    let selectrisorse = ['546929', '546976', '547002' ]
		    let cognome = 'pippo%'
		    let matricola =  '11111'
			let mail = 'a.montanari@esseciesse.net'
			let cf = 'MNTLRT64R08L833N'
		    let IDP = '123'

		    for p in prenotazioni
		    let dmia = concat( SUBSTRING(p.data, 6, 4),  SUBSTRING(p.data, 3, 2), SUBSTRING(p.data, 0, 2)  )
		    filter p.data == data
		    filter  data_da <= dmia
		    filter dmia <= data_da
		    filter p.ateneo IN selectatenei
		    filter p.servizio IN selectservizi
		    filter p.gruppir IN selectgruppir
		    filter p.IDR IN selectrisorse
		    filter p.cognome LIKE cognome
		    filter p.matricola == matricola
			filter p.email == mail
			filter p.cf == cf
		    filter p._key == IDP
		    return p

*/

		case 8:  /* ststo dei DB da index.. */
			$q="let a = LENGTH( atenei )
				let g = LENGTH( gruppir )
				let m = LENGTH( matricole )
				let p = LENGTH( prenotazioni )
				let r = LENGTH( risorse )
				let s = LENGTH( servizi )
				let ema = LENGTH( email )
				let sto = LENGTH( prenotazionistoriche )
				return { 'atenei': a, 'gruppir':g , 'matricole': m, 'prenotazioni':p , 'risorse':r , 'servizi':s, 'email':ema, 'prenotazionistoriche':sto  }";
			return $q;
			break;




		case 10:
			$q='let s = "%s"
				let m = "%s"
				let cf = "%s"

				for p in prenotazioni

					filter p.servizio == s
					filter p.cf == cf || p.matricola == m

					COLLECT WITH COUNT INTO l
				return  l';

			return sprintf( $q, $AQL[2], $AQL[3], $AQL[4]  );
			break;

		default:
			return '';
	}


}





function QUERYDBWEB($url, $POST=null)  // chiama sempre una url esterna...
{

		if( $POST != null )
		{
			$opts = array('http' => array(
			        					'method'  => 'POST',
			        					'header'  => 'Content-type: application/x-www-form-urlencoded'."\r\n".'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n",
			        					'content' => http_build_query(  $POST )
			    )
			);
		}
		else	$opts = array('http' => array('header'=> 'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n"));
		$context = stream_context_create($opts);
		session_write_close(); // unlock the file
		$DATI = json_decode( file_get_contents( $url, false, $context  ), true );
		session_start(); // Lock the file

		if(  !(  isset( $DATI) && isset($DATI['success']) &&  $DATI['success']==1  )  ) {	echo "<pre>Errore generico collegamento al DB : \n";print_r($DATI); echo "RIPROVARE piu' tardi....</pre>"; die(); }

		return $DATI;
}


function QUERYDB($url, $POST=null)  // invoca funzione locale...
{

	if($POST==null ) $POST=array();

	$b=parse_url ( $url  );

	if( !isset($b['query'])  ) $GET=array();
	else
	{
			$GET=array();
			$c=  explode ( '&' , $b['query']);
			foreach($c as $s)
			{
				$t=explode('=', $s);
				$GET[ $t[0] ] = is($t,1);
			}
	}


	//$DATI = json_decode( \triagens\ArangoDb\DB( $GET, $POST  ), true );
	$DATI =  \triagens\ArangoDb\DB(  $GET, $POST  );


	if(  !(  isset( $DATI) && isset($DATI['success']) &&  $DATI['success']==1  )  ) {	echo "<pre>Errore generico collegamento al DB : \n";print_r($DATI); echo "RIPROVARE piu' tardi....</pre>"; die(); }

	return $DATI;
}



function codiceFiscale($cf)
{
	if($cf=='')                             return false;
	if(strlen($cf)!= 16)    return false;

	$cf=strtoupper($cf);
	if(!preg_match("/[A-Z0-9]+$/", $cf)) return false;

	$s = 0;
	for($i=1; $i<=13; $i+=2)
	{
		$c=$cf[$i];
		if('0'<=$c and $c<='9') $s+=ord($c)-ord('0');
		else                                    $s+=ord($c)-ord('A');
	}

	for($i=0; $i<=14; $i+=2)
	{
		$c=$cf[$i];
		switch($c)
		{
			case '0':  $s += 1;  break;
			case '1':  $s += 0;  break;
			case '2':  $s += 5;  break;
			case '3':  $s += 7;  break;
			case '4':  $s += 9;  break;
			case '5':  $s += 13;  break;
			case '6':  $s += 15;  break;
			case '7':  $s += 17;  break;
			case '8':  $s += 19;  break;
			case '9':  $s += 21;  break;
			case 'A':  $s += 1;  break;
			case 'B':  $s += 0;  break;
			case 'C':  $s += 5;  break;
			case 'D':  $s += 7;  break;
			case 'E':  $s += 9;  break;
			case 'F':  $s += 13;  break;
			case 'G':  $s += 15;  break;
			case 'H':  $s += 17;  break;
			case 'I':  $s += 19;  break;
			case 'J':  $s += 21;  break;
			case 'K':  $s += 2;  break;
			case 'L':  $s += 4;  break;
			case 'M':  $s += 18;  break;
			case 'N':  $s += 20;  break;
			case 'O':  $s += 11;  break;
			case 'P':  $s += 3;  break;
			case 'Q':  $s += 6;  break;
			case 'R':  $s += 8;  break;
			case 'S':  $s += 12;  break;
			case 'T':  $s += 14;  break;
			case 'U':  $s += 16;  break;
			case 'V':  $s += 10;  break;
			case 'W':  $s += 22;  break;
			case 'X':  $s += 25;  break;
			case 'Y':  $s += 24;  break;
			case 'Z':  $s += 23;  break;
		}
	}

	if( chr($s%26+ord('A'))!=$cf[15] )      return false;

	return true;
}



function calcola_minuti( $da , $a)
{
	if( preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $da, $oda) != 1  ) return 0;
	if( preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $a, $oa) != 1  ) return 0;

	$m1 = 60 - $oda[2];
	$m2 =  $oa[2];
	$m3 = ($oa[1] - $oda[1] - 1) * 60;

	return $m1 + $m2 + $m3;
}



function calcola_slot( $ts, $da, $delta, $max)
{

	if( preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $da, $oda) != 1  ) return array();

	$min=$oda[2];
	$ora=$oda[1];
	$ini = $ts + $min*60 + $ora * 3600;

	$ret=array();
	for (  $a=0; $a<$max; $a++ )
	{
		    date_default_timezone_set('UTC');
			$ret[]=array('ora'=>date('H:i', $ini+ $a*$delta*60), 'numero_prenotazioni' => 0 , 'orafine'=>date('H:i', $ini+ ($a+1)*$delta*60)  );
	}
	return $ret;

}


function dataita($k, $rov=null)
{
	date_default_timezone_set('UTC');
	if( $rov !=null ) return date('Ymd', $k);
	return date('d/m/Y', $k);
}

function is($arr, $idx, $ret='')
{
	if( isset( $arr[ $idx ])  ) return  $arr[ $idx ];
	else  return $ret;

}

function p($v) // pulisci...
{
	//return preg_replace('/[']/', '', $string);
	return str_replace( '"', '\"',  str_replace("'","\'",$v)  );
}
function pp($v) // pulisci  e stostituisci...
{
	//return preg_replace('/[']/', '', $string);
	return str_replace( '"', '`',  str_replace("'","`",$v)  );
}
function pcr($s)// pulisce i cr
{
	return str_replace("\n", "", str_replace("\r", "", $s));

}
function Crypta($X)  {
	$KIAVE='LAURBETOMNI'; $KIAVED='0123456789';
	return convBase($X, $KIAVED, $KIAVE);
}
function Decrypta($X)  {
	$KIAVE='LAURBETOMNI'; $KIAVED='0123456789';
	return convBase($X,$KIAVE,$KIAVED);
}
function convBase($numberInput, $fromBaseInput, $toBaseInput)
{
	if ($fromBaseInput==$toBaseInput) return $numberInput;
	$fromBase = str_split($fromBaseInput,1);      	$toBase = str_split($toBaseInput,1);
	$number = str_split($numberInput,1);    		$fromLen=strlen($fromBaseInput);
	$toLen=strlen($toBaseInput);    				$numberLen=strlen($numberInput);
	$retval='';
	if ($toBaseInput == '0123456789')      {
		$retval=0;
		for ($i = 1;$i <= $numberLen; $i++)     $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
		return $retval;
	}
	if ($fromBaseInput != '0123456789')         $base10=convBase($numberInput, $fromBaseInput, '0123456789');
	else                                        $base10 = $numberInput;
	if ($base10<strlen($toBaseInput))    return $toBase[(int)$base10];
	while($base10 != '0')     {
		$retval = $toBase[bcmod($base10,$toLen)].$retval;
		$base10 = bcdiv($base10,$toLen,0);
	}
	return $retval;
}




function crea_pdf_prenotazione($P)
{

	global $config;

	$tmp= tempnam("/tmp", "PRENO").".pdf";
	$linkdel=URL."/webdel.php?del&ID=".Crypta( $P['ID'] );

	try {


		ob_start();
?>
<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">
        <table style="width: 100%; border: solid 0px black;">
            <tr>
                <td style="text-align: left;    width: 33%"><img src="<?php echo URL ?>/logo.png"></td>
                <td style="text-align: center;    width: 34%; font-size:150%">Servizio di prenotazione ONLINE</td>
                <td style="text-align: right;    width: 33%"><?php echo date('d/m/Y'); ?></td>
            </tr>
        </table>


 <h2>Procedura Gestione Prenotazioni</h2>

 <?php
 		$a = '<tr><td style="width: 50%%; text-align: right; padding: 2px 15px;">%s</td><td style="width: 50%%; text-align: left; padding: 2px 15px;">%s</td></tr>';
 		//$a = '<tr><td style="width: 40; text-align: left;">%s</td><td style="width: 60; text-align: right;">%s</td></tr>';
 		?>

	<h3>Prenotazione creata : ID <?php echo $P['ID'] ?></h3>

     <table style="width: 100%;border: solid 0px #5544DD;" align="center" >
     <?php
     		//printf( $a, "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Studente</span>",''  );

     		//printf( $a, 'Nome : ', $P['nome']  );
     		//printf( $a, 'Cognome : ', $P['cognome']  );
     		//printf( $a, 'Matricola : ', $P['matricola']  );
     		//printf( $a, 'Ateno di iscrizione : ', $P['ateneo']  );
     		//printf( $a, 'Email : ', $P['email']  );
			//printf( $a, 'Codice Fiscale : ', $P['cf']  );
     		//printf( $a, 'Accettazione informativa dati personali : ',  ($P['informativa_personali'] == 'on')?'SI':'NO');
     		//printf( $a, 'Consenso trattamento informazioni : ',   ($P['consenso'])?'SI':'NO' );

     		printf( $a, "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Prenotazione</span>", ''  );

     		printf( $a, 'Servizio : ', $P['servizio']  );
     		printf( $a, 'Indirizzo esecuzione servizio : ', $P['gruppir']. " ". $P['gruppir_ind']  );
  //   		printf( $a, 'Nome sportello', $P['']  );
     		printf( $a, 'data di prenotazione : ', "<span style='color:red; font-weight:900'>".$P['data']."</span>"  );
     		printf( $a, 'ora di prenotazione : ', "<span style='color:red; font-weight:900'>".$P['orada']."</span>"  );
     		printf( $a, 'ID prenotazione : ', "<span style='color:red; font-weight:900'>".$P['ID']."</span>"  );
			printf( $a, 'Accettazione informativa dati personali : ',  ($P['informativa_personali'] == 'on')?'SI':'NO');
     		printf( $a, 'Consenso trattamento informazioni : ',   ($P['consenso'])?'SI':'NO' );



     ?>

    </table>
<br>
<div  align="center" style=" margin: auto;width: 70%; border: 3px solid #73AD21; padding: 0px 20px 20px 20px; align:center">

Per cancellare la seguente prenotazione click sul link sotto:<br><br>

<a href="<?php echo $linkdel ?>">CANCELLA PRENOTAZIONE <?php echo $P['ID']?></a><br>

<br>
Cancellazione possibile solo fino a <?php echo $P['ore_canc']; ?> ore prima della erogazione del servizo.<br>
<br>
Non rispondere alla presente mail, per qualsiasi problema contattare il seguente link: <a href="<?= $config['CONTATTI'] ?>">Contatti</a><br>

</div>
<br>
<?php if($P['stampa_delega']=='SI') { ?>
<hr>
<h5>DELEGA - <b style="color:red">Necessaria copia del Documento di Identità del dichiarante</b></h5>
Il/La sottoscritto/a ________________________________ <b>DELEGA</b><br>
il/la sig./sig.ra ___________________________________________________________________<br>

nato/a _________________________________________________ il _______________________<br>

residente in via ____________________________________________________ CAP __________<br>

città _____________________________________________________________ Prov. _________<br>

A sostituirlo/a a friure del servizio rilasciato da MY_AZIENDA<br>
<div style="text-align: right">Firma ______________________</div>
<br>
<?php } ?>
<br>
<br>
        <table style="width: 100%; border: solid 1px black;">
            <tr>
                <td style="text-align: left;    width: 70%">MY_AZIENDA: INDIRIZZO FISICO</td>
                <td style="text-align: right;    width: 30%">pagina [[page_cu]]/[[page_nb]]</td>
            </tr>
        </table>


</page>

<?php


		$content = ob_get_clean();

// qua creazione body html e body text della mail , la passo alla procedura di invio mail...
$bodyhtml="<h3>Prenotazione creata </h3><br><br><pre>";
$a="%' 35s  :  %-s\n";

//$bodyhtml .= "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Identificativo</span><br><br>";

//$bodyhtml .= sprintf( $a, 'Nome', $P['nome']  );
		$//bodyhtml .= sprintf( $a, 'Cognome', $P['cognome']  );

		$bodyhtml .= "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Prenotazione</span><br><br>";

		$bodyhtml .= sprintf( $a, 'Servizio', $P['servizio']  );
		$bodyhtml .= sprintf( $a, 'Indirizzo esecuzione servizio', $P['gruppir']. " ". $P['gruppir_ind']  );
		$bodyhtml .= sprintf( $a, 'data di prenotazione', "<span style='color:red; font-weight:900'>".$P['data']."</span>"  );
		$bodyhtml .= sprintf( $a, 'ora di prenotazione', "<span style='color:red; font-weight:900'>".$P['orada']."</span>"  );
		$bodyhtml .= sprintf( $a, 'ID prenotazione', "<span style='color:red; font-weight:900'>".$P['ID']."</span>"  );

		if($P['stampa_delega']=='SI') 	$bodyhtml .= "<br><br><br><br><br>In allegato PDF con i dettagli della prenotazione, il link per eventuale cancellazione e la delega per il ritiro <br><br>";
		else 							$bodyhtml .= "<br><br><br><br><br>In allegato PDF con i dettagli della prenotazione e il link per eventuale cancellazione<br><br>";


$body="Prenotazione creata \n\n\n\n";
$a="%' 35s  :  %-s\n";
//$body .= sprintf( $a, 'Nome', $P['nome']  );
		//$body .= sprintf( $a, 'Cognome', $P['cognome']  );

		$body .= sprintf( "Prenotazione\n\n"  );

		$body .= sprintf( $a, 'Servizio', $P['servizio']  );
		$body .= sprintf( $a, 'Indirizzo esecuzione servizio', $P['gruppir']. " ". $P['gruppir_ind']  );
		$body .= sprintf( $a, 'data di prenotazione', $P['data']  );
		$body .= sprintf( $a, 'ora di prenotazione', $P['orada']  );
		$body .= sprintf( $a, 'ID prenotazione', $P['ID']  );

		if($P['stampa_delega']=='SI') 	$body .= "\n\n\n\n\n\n\nIn allegato PDF con i dettagli, il link per eventuale cancellazione e la delega per il ritiro \n\n\n";
		else							$body .= "\n\n\n\n\n\n\nIn allegato PDF con i dettagli e il link per eventuale cancellazione\n\n\n";




		//echo $content;
		//sdie();


		$html2pdf = new Html2Pdf('P', 'A4', 'it');
		$html2pdf->writeHTML($content);
		$html2pdf->output($tmp, 'F');
		$html2pdf->clean();
		return array('ris'=>true, 'file'=>$tmp, 'bodyhtml'=>$bodyhtml, 'body'=>$body);
	} catch (Html2PdfException $e) {
		$html2pdf->clean();
		$formatter = new ExceptionFormatter($e);
		return array('ris'=>false, $formatter->getHtmlMessage() );
	}


}






function crea_pdf_cancellazione($P)
{
	global $config;

	//echo "<pre>\n";	print_r($P); die();
	$tmp= tempnam("/tmp", "CANCPRENO").".pdf";

	try {

		ob_start();
		?>
<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">
        <table style="width: 100%; border: solid 0px black;">
            <tr>
                <td style="text-align: left;    width: 33%"><img src="<?php echo URL ?>/logo.png"></td>
                <td style="text-align: center;    width: 34%; font-size:150%">Servizio di prenotazione ONLINE</td>
                <td style="text-align: right;    width: 33%"><?php echo date('d/m/Y'); ?></td>
            </tr>
        </table>
        <br><br>

 <h2>Procedura Gestione Prenotazioni</h2>

 <?php
 		$a = '<tr><td style="width: 50%%; text-align: right; padding: 2px 15px;">%s</td><td style="width: 50%%; text-align: left; padding: 2px 15px;">%s</td></tr>';
 		?>
 <br>
	<h3>Conferma CANCELLAZIONE prenotazione : ID <?php echo $P['_key'] ?></h3>

     <table style="width: 100%;border: solid 0px #5544DD;" align="center" >
     <?php
     		//printf( $a, "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Studente</span>",''  );

     		//printf( $a, 'Nome : ', $P['nome']  );
     		//printf( $a, 'Cognome : ', $P['cognome']  );
     		//printf( $a, 'Matricola : ', $P['matricola']  );
     		//printf( $a, 'Ateno di iscrizione : ', $P['ateneo']  );
			//printf( $a, 'Email : ', $P['email']  );
			//printf( $a, 'Codice Fiscale : ', $P['cf']  );
     		//printf( $a, 'Accettazione informativa dati personali : ',  ($P['informativa_personali'] == 'on')?'SI':'NO');
     		//printf( $a, 'Consenso trattamento informazioni : ',   ($P['consenso'])?'SI':'NO' );

     		printf( $a, "<span style='color:#3eaa2a; font-size:130%; font-weight:900'>Prenotazione</span>", ''  );

     		printf( $a, 'Servizio : ', $P['servizio']  );
     		printf( $a, 'Indirizzo esecuzione servizio : ', $P['gruppir']. " ". $P['gruppir_ind']  );
     		printf( $a, 'data di prenotazione : ', "<span style='color:red; font-weight:900'>".$P['data']."</span>"  );
     		printf( $a, 'ora di prenotazione : ', "<span style='color:red; font-weight:900'>".$P['orada']."</span>"  );
     		printf( $a, 'ID prenotazione : ', "<span style='color:red; font-weight:900'>".$P['_key']."</span>"  );

     ?>

    </table>
<br>
<br>
<div  align="center" style=" margin: auto;width: 70%; border: 3px solid #73AD21; padding: 20px; align:center">

La presente prenotazione e' stata cancellata come da richiesta.

<br>
<br>
Non rispondere alla presente mail, per qualiasi problema conttattare il seguente link.<br>
<a href="<?= $config['CONTATTI'] ?>">Contatti</a><br>

</div>
<br><br><br><br><br>
        <table style="width: 100%; border: solid 1px black;">
            <tr>
                <td style="text-align: left;    width: 50%">MY_AZIENDA, INDIRIZZO FISICO</td>
                <td style="text-align: right;    width: 50%">pagina [[page_cu]]/[[page_nb]]</td>
            </tr>
        </table>
        <br>
</page>
<?php
		$content = ob_get_clean();
		$html2pdf = new Html2Pdf('P', 'A4', 'it');
		$html2pdf->writeHTML($content);
		$html2pdf->output($tmp, 'F');
		$html2pdf->clean();
		return array('ris'=>true, $tmp);
	} catch (Html2PdfException $e) {
		$html2pdf->clean();
		$formatter = new ExceptionFormatter($e);
		return array('ris'=>false, $formatter->getHtmlMessage() );
	}


}








function crea_pdf_ExportRicercaPrenotazioni($PP,$AQL)
{
	//echo "<pre>\n";	print_r($AQL); die();
	$tmp= tempnam("/tmp", "EXPORTPDF").".pdf";

	try {

		ob_start();
		?>

		<style type="text/Css">
		.test1 table, td, th, tr { 		    border: solid 1px #000; padding:5px; font-size:9px;	}

		</style>
<page backtop="10mm" backbottom="10mm" backleft="6mm" backright="6mm"  style="font-size: 10px">
<pre>
<?php
		echo "Filtri di ricerca : \n\n";
		if($AQL['data'] !='' ) printf("DATA : = a %s\n", $AQL['data']);
		if($AQL['data_da'] !='' ) printf("DATA maggiore di   : >= a %s\n", $AQL['data_da']);
		if($AQL['data_a'] !='' ) printf("DATA minore di   : =< di %s\n", $AQL['data_a']);
		if($AQL['matricola'] !='' ) printf("MATRICOLA   : = a %s\n", $AQL['matricola']);
		if($AQL['cognome'] !='' ) printf("COGNOME   : simile a  %s\n", $AQL['cognome']);
		if($AQL['mail'] !='' ) printf("EMAIL   : = a %s\n", $AQL['mail']);
		if($AQL['cf'] !='' ) printf("CODICE FISCALE    : = a %s\n", $AQL['cf']);
		if($AQL['IDP'] !='' ) printf("ID PRENOTAZIONE   : = a %s\n", $AQL['IDP']);
		if($AQL['risorse'] !=array() ) { printf("RISORSE   : = a \n"); foreach ($AQL['risorse'] as $x) echo "$x  "; echo "\n"; }
		if($AQL['atenei'] !=array() ) { printf("ATENEI   : = a \n"); foreach ($AQL['atenei'] as $x) echo "$x  "; echo "\n"; }
		if($AQL['servizi'] !=array() ) { printf("SERVIZI   : = a \n"); foreach ($AQL['servizi'] as $x) echo "$x  "; echo "\n"; }
		if($AQL['gruppir'] !=array() ) { printf("INDIRIZZI RISORSE   : = a \n"); foreach ($AQL['gruppir'] as $x) echo "$x  "; echo "\n"; }

 ?>
</pre>

<table class="test1" style="border-collapse: collapse; width:100%">
	<thead>
	<tr>
		<td >ID prenotazione</td>
		<td >cognome</td>
		<td >nome</td>
		<td >matricola</td>
		<td >cod fisc</td>
		<td >ateneo</td>
		<td >servizio</td>
		<td >email</td>
		<td >cellulare</td>
		<td >indirizzo</td>
		<td >nome risorsa</td>
		<td >data</td>
		<td >ora</td>
	</tr>
	</thead>
	<tbody>
	<?php
		$lista=array('_key'=>'6', 'cognome'=>'7', 'nome'=>'5', 'matricola'=>'6', 'cf'=>'10', 'ateneo'=>'10', 'servizio'=>'10', 'email'=>'13',
					'cellulare'=>'7', 'gruppir'=>'8', 'IDR' => '9', 'data' => '6', 'orada'=>'3' );

		foreach( $PP as $P)
		{
			echo "<tr>";
			foreach($lista as $l => $perc)
			{
				echo "<td style='white-space: normal; width:".$perc."%'>";
				if( $l == 'IDR' )  {
					$ris= \triagens\ArangoDb\DaiNomeRisorsa( $P[$l] );
					if($ris['ris']) 	echo  $ris[0];
					else 				echo  '';
				}
				else 					echo $P[$l];
				echo "</td>";
			}
			echo "</tr>";
		}
		?>
	</tbody>
</table>
</page>
<?php

//die();

		$content = ob_get_clean();
		$html2pdf = new Html2Pdf('L', 'A4', 'it');
		$html2pdf->writeHTML($content);
		$html2pdf->output($tmp, 'F');
		$html2pdf->clean();
		return array('ris'=>true, $tmp);
	} catch (Html2PdfException $e) {
		$html2pdf->clean();
		$formatter = new ExceptionFormatter($e);
		return array('ris'=>false, $formatter->getHtmlMessage() );
		}


}









function sdie($x=null)
{
	echo "<pre>\n";
	print_r($x);
	die("\n\nterminato come da richiesta");
}

function IndirizzoIpReale()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))      			$ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))       $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else       												$ip=$_SERVER['REMOTE_ADDR'];
    return $ip;
}

function logattivita($azione)
{
	global $config, $utente;

	// data / ora / IP / utente / azione

	$cosa = str_replace ( '"' , '\"' , $azione );
	$ora=date('d/m/Y-H:i:s');
	$IP=IndirizzoIpReale();

	$out=sprintf("%s : %s : %s -> %s\n",  $ora, $IP, $utente, $cosa );
	file_put_contents ( ($utente=='dummy')? FILE."/".$config['LOGFILE'] : FILEADM."/".$config['LOGFILE'] , $out,  FILE_APPEND | LOCK_EX );
}


function GetKey()
{
	$k=''.round(microtime(true) * 100);
	return $k;


}


function  returnErrJ($ret, $MSG, $data=array())
{
	stampa( $data, $MSG ." debug da ritorno errore Json" );
	//if( $ret==true ) { stampa( $data, $MSG );}  // debug da togliere....


	$results = array_merge( array( 'error' => $ret,  'success' => !$ret,  'error_msg' => $MSG), $data	);
	//return json_encode($results);
	return $results;
}
function debug_backtrace_string($l=0) {
	$stack = '';
	$i = 1;
	$trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $l);
	unset($trace[0]); //Remove call to this function from stack trace
	foreach($trace as $node) {
		$stack .= "#$i ".$node['file'] ."(" .$node['line']."): ";
		if(isset($node['class'])) {
			$stack .= $node['class'] . "->";
		}
		$stack .= $node['function'] . "()" . PHP_EOL;
		$i++;
	}
	return $stack;
}
function stampa($a, $label='')
{
	return;

	$d=debug_backtrace_string(2);
	$x=print_r($a, true);
	$ha=fopen("log__.txt", 'a');
	fwrite($ha,$label." --> ".$d."\n");
	fwrite($ha, $x."\n--\n");
	fclose($ha);
}
function stampa1($a, $label='')
{
	return;

	$d=debug_backtrace_string(2);
	$x=print_r($a, true);
	$ha=fopen("log1__.txt", 'a');
	fwrite($ha,$label." --> ".$d."\n");
	fwrite($ha, $x."\n--\n");
	fclose($ha);
}

/**if ( ! function_exists( 'array_key_last' ) ) {
    /**
     * Polyfill for array_key_last() function added in PHP 7.3.
     *
     * Get the last key of the given array without affecting
     * the internal array pointer.
     *
     * @param array $array An array
     *
     * @return mixed The last key of array if the array is not empty; NULL otherwise.
     */
    function array_key_last( $array ) {
        $key = NULL;

        if ( is_array( $array ) ) {

            end( $array );
            $key = key( $array );
        }

        return $key;
    }
//}
