<?php

//http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.php
//http://1000hz.github.io/bootstrap-validator/
//https://www.abeautifulsite.net/a-simple-php-captcha-script

ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);



require_once('config.php');
require_once('lib_simple.php');
require_once('lib.php');
require_once('html.php');
require_once('lang.php');

require_once( FILELIB."/captcha/simple-php-captcha.php" );


session_start();

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}

$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];


if( $utente == 'dummy' ) 	$URL=URL;
else 						$URL=URLADM;


$STATI=array('inizio', 'servizio', 'ateneo', 'indirizzo', 'data', 'finale', 'fine');



if( !isset( $_SESSION['disponibilita'] ) ) $_SESSION['disponibilita']='NO';   // default NO
if(  isset($_GET['dispo']) && $_GET['dispo']=='SI' && $_SESSION['Utente'] != 'dummy' ) { $_SESSION['disponibilita'] = 'SI';  $_SESSION['stato'] = 'servizio'; }
if(  isset($_GET['dispo']) && $_GET['dispo']=='NO' && $_SESSION['Utente'] != 'dummy' ) { $_SESSION['disponibilita'] = 'NO';  unset( $_SESSION['stato'] ); }



$msgerrore=array();

$pagina="test_prenotazioni_test.php";


echo "<pre>\n";
echo "SESSION ID -> \n";print_r(session_id())."\n\n";
echo "POST -> \n";print_r($_POST)."\n";
echo "\nGET  -> \n";print_r($_GET)."\n";
echo "\nSTATO  -> ";print_r($_SESSION['stato'])."\n";
echo "\nPRENOTAZIONE  -> \n";print_r($_SESSION['prenotazione'])."\n";
if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
echo "</pre>\n";



// verifica vecchio
if(   !isset( $_SESSION['stato'] )  ||
    ( !isset($_POST['submit'])    &&   !isset($_SESSION['disponibilita']) && !isset($_GET['submit']) )
  ) // inizio
{
	unset($STATO_FINALE);

	$_SESSION['prenotazione']['cognome']='';
	$_SESSION['prenotazione']['nome']='';
	$_SESSION['prenotazione']['matricola']='';
	$_SESSION['prenotazione']['cf']='';
	$_SESSION['prenotazione']['ateneo']='';
	$_SESSION['prenotazione']['servizio']='';
	$_SESSION['prenotazione']['email']='';
	$_SESSION['prenotazione']['cellulare']='';
	$_SESSION['prenotazione']['informativa_personali']='';
	$_SESSION['prenotazione']['consenso']='';
	$_SESSION['prenotazione']['gruppir']='';
	$_SESSION['prenotazione']['IDR']='';
	$_SESSION['prenotazione']['data']='';
	$_SESSION['prenotazione']['slot']='';
	$_SESSION['prenotazione']['robot']='';

	$_SESSION['stato']='inizio';
			$_SESSION['captcha'] = simple_php_captcha(array(
			'min_length' => 6,			'max_length' => 6,			'characters' => '23456789',	'min_font_size' => 28,
			'max_font_size' => 28,		'color' => '#666',			'angle_min' => 5,			'angle_max' => 10,
			'shadow' => true,			'shadow_color' => '#fff',	'shadow_offset_x' => -1,	'shadow_offset_y' => 1	));



}



// azione in inzio .. solo avanti
else if($_SESSION['stato'] == 'inizio' && isset($_POST['submit']) &&  $_POST['submit'] == AVANTI )  // da  inizio a   servizio  AVANTI -> servizio
{
	// arrivo da inzio.. verifica e poi passo oltre

	$_SESSION['prenotazione']['cognome']=$_POST['cognome'];
	$_SESSION['prenotazione']['nome']=$_POST['nome'];
	$_SESSION['prenotazione']['cf']=strtoupper ( $_POST['cf'] );
	$_SESSION['prenotazione']['matricola']=$_POST['matricola'];
	$_SESSION['prenotazione']['email']=$_POST['email'];
	$_SESSION['prenotazione']['cellulare']=$_POST['cellulare'];
	$_SESSION['prenotazione']['informativa_personali']=$_POST['informativa_personali'];
	$_SESSION['prenotazione']['consenso']=$_POST['consenso'];
	$_SESSION['prenotazione']['robot']=$_POST['robot'];


	$OK=true;
	if(  !codiceFiscale( $_SESSION['prenotazione']['cf'] )  ) 					{  $_SESSION['prenotazione']['cf']='';  	$OK=false; }
	if(  $_SESSION['captcha']['code'] != $_SESSION['prenotazione']['robot'] ) 	{  $_SESSION['prenotazione']['robot']='';  	$OK=false; }

	if( $OK) $_SESSION['stato'] = 'servizio';
	else
	{
		$_SESSION['captcha'] = simple_php_captcha(array(
				'min_length' => 6,			'max_length' => 6,			'characters' => '23456789',	'min_font_size' => 28,
				'max_font_size' => 28,		'color' => '#666',			'angle_min' => 5,			'angle_max' => 10,
				'shadow' => true,			'shadow_color' => '#fff',	'shadow_offset_x' => -1,	'shadow_offset_y' => 1	));

	}


}

//azione in servizio
elseif($_SESSION['stato'] == 'servizio' && isset($_POST['submit']) &&  $_POST['submit'] == INDIETRO)   // da servizio a inizio INDIETRO   inizio <-
{
			$_SESSION['captcha'] = simple_php_captcha(array(
			'min_length' => 6,			'max_length' => 6,			'characters' => '23456789',	'min_font_size' => 28,
			'max_font_size' => 28,		'color' => '#666',			'angle_min' => 5,			'angle_max' => 10,
			'shadow' => true,			'shadow_color' => '#fff',	'shadow_offset_x' => -1,	'shadow_offset_y' => 1	));

			$_SESSION['prenotazione']['robot']='';
			$_SESSION['prenotazione']['selectservizi']='';
			$_SESSION['stato'] = 'inizio';

}
elseif($_SESSION['stato'] == 'servizio' && isset($_POST['submit']) &&  $_POST['submit'] == AVANTI)  // da servizio a  ateneo  AVANTI -> ateneo
{

	// '<option value="%s||%s||%s||%s"></option>', $s['nome'], $s['giorni_p'], $s['multipla_p'], $s['controllo_matr'],
			$arr=explode("||", $_POST['selectservizi']);
			$_SESSION['prenotazione']['servizio']=$arr[0];
			$_SESSION['prenotazione']['giorni_p']=$arr[1];
			$_SESSION['prenotazione']['multipla_p']=$arr[2];
            $_SESSION['prenotazione']['controllo_matr']=$arr[3];
            $_SESSION['prenotazione']['stampa_delega']=$arr[4];
            $_SESSION['prenotazione']['ore_canc']=$arr[5];
            $_SESSION['prenotazione']['giorni_vis']=$arr[6];
            $_SESSION['prenotazione']['aperto_da']=$arr[7];
            $_SESSION['prenotazione']['tipo_servizio']=$arr[8];

            // qua inserire controllo se questa matricola o questo codice fiscale e questo servizio sono gia' presenti in una prenotazione attiva...
            // eiste una prenotazione che abbia questa matricola o questo CF e questo servizio E che il servizio non consenta prenotazioni multiple ??
            // se lo trova.. errore...

			if( $_SESSION['prenotazione']['multipla_p'] == 'NO' && $_SESSION['disponibilita'] == 'NO')
			{
				$query=array('numero', 10, $_SESSION['prenotazione']['servizio'], $_SESSION['prenotazione']['matricola'], $_SESSION['prenotazione']['cf'] );

				$DATI=QUERYDB(URLDB, array( 'AQL'=>json_encode($query)  ));
				//sdie($DATI);

				if( $DATI['numero'][0] > 0 )  // sto prenotando un servizio gia' prenotato... errore..
				{
					$_SESSION['stato'] = 'servizio'; // non cambio stato
					$msgerrore['servizio']="Esiste già una prenotazione per questo servizio... Non posso accettarne un'altra !";
				}
				else $_SESSION['stato'] = 'ateneo';
			}

			else $_SESSION['stato'] = 'ateneo';   // verifica servizio .. sessuna verifica



}


// azione in ateneo
elseif($_SESSION['stato'] == 'ateneo' && isset($_POST['submit']) &&  $_POST['submit'] == INDIETRO)   // da ateneo a servizio INDIETRO   servizio  <-
{
	$_SESSION['stato'] = 'servizio';

}
elseif($_SESSION['stato'] == 'ateneo' && isset($_POST['submit']) &&  $_POST['submit'] == AVANTI)  // da ateneo  a  indirizzo   AVANTI -> indirizzo
{
	$_SESSION['prenotazione']['ateneo']=$_POST['selectateneo'];
	$_SESSION['stato'] = 'indirizzo';   // verifica servizio .. nessuna verifica
}


// azione in indirizzo
elseif($_SESSION['stato'] == 'indirizzo' && isset($_POST['submit']) &&  $_POST['submit'] == INDIETRO)   // da indirizzo  a ateneo INDIETRO   ateneo  <-
{
	$_SESSION['stato'] = 'ateneo';

}
elseif($_SESSION['stato'] == 'indirizzo' && isset($_POST['submit']) &&  $_POST['submit'] == AVANTI)  // da indirizzo   a  data   AVANTI -> data
{
	$aar=explode('||', $_POST['selectgruppir']);
	$_SESSION['prenotazione']['gruppir']		=	$aar[0];
	$_SESSION['prenotazione']['gruppir_ind']	=	$aar[1];
	$_SESSION['stato'] = 'data';   // verifica servizio .. nessuna verifica
}




// azione in data
elseif($_SESSION['stato'] == 'data' && isset($_POST['submit']) &&  $_POST['submit'] == INDIETRO)   // da data  a indirizzo  INDIETRO   indirizzo  <-
{
	$_SESSION['stato'] = 'indirizzo';

}
elseif($_SESSION['stato'] == 'data' && isset($_GET['submit']) &&  $_GET['submit'] == AVANTI)  // da data    a  finale   AVANTI -> finale
{

	preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $_GET['data'], $oda);

	$_SESSION['prenotazione']['IDR'] =  $_GET['IDR'];
	$_SESSION['prenotazione']['data'] = $oda[3]."/".$oda[2]."/".$oda[1];
	$_SESSION['prenotazione']['slot'] = $_GET['slot'];
	$_SESSION['prenotazione']['orada'] = $_GET['orada'];
	$_SESSION['stato'] = 'finale';
}


// azione (esecuzione) in finale ----
elseif($_SESSION['stato'] == 'finale' && isset($_POST['submit']) &&  $_POST['submit'] == INDIETRO)   // da finale  a data  INDIETRO   data  <-
{
	$_SESSION['stato'] = 'data';

}
elseif($_SESSION['stato'] == 'finale' && isset($_POST['submit']) &&  $_POST['submit'] == 'CREA PRENOTAZIONE')  // da data    a  finale   AVANTI -> finale
{


	$_SESSION['stato'] = 'fine';
}






// recupero DATI....
// se mi da l'ok sono in stato servizio...
if( $_SESSION['stato'] == 'servizio')
{
	/*
 s.aperto_da < 1543614176  && 1544478176 < s.aperto_a
                          ora                                   ora + 10
                           !                                        !
                 1/1/2018                       31/12/2018
       !  !                                                  !   !
NADA  ora + 10 <   aperto_da       !!      NOT   aperto_a < ora
OK    ora + 10 >=   aperto_da       &&         aperto_a >= ora
OK    ora + 10 >=   aperto_da       &&         aperto_a >= ora
      aperto_da <=  ora + 10        &&         ora <= aperto_a             OK
*/

		$ora=time();
		$poi=$ora+$SECONDI_ATTIVI_GENERALE;
        // attenzione che se siamo in richiesta disponiblità la matricola non esiste....
		$query=array('servizi', 1, $ora, $poi,  (isset( $_SESSION['disponibilita'])  && ($_SESSION['disponibilita']=='SI')  ) ? '' : $_SESSION['prenotazione']['matricola'], is($_SESSION, 'disponibilita', 'NO') );

		$DATI=QUERYDB(URLDB, array( 'AQL'=>json_encode($query)  ));

		//echo "<pre>\n"; print_r($DATI); die();
}

if( $_SESSION['stato'] == 'ateneo')
{

	$query=array('atenei', 4 );

	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );

	//echo "<pre>\n"; print_r($DATI); die();

}

if( $_SESSION['stato'] == 'indirizzo')
{
	$query=array('gruppir', 5,  $_SESSION['prenotazione']['servizio'],   $_SESSION['prenotazione']['ateneo']  );
	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );

	//echo "<pre>\n"; print_r($DATI); die();

}

if( $_SESSION['stato'] == 'data')
{

	$query=array('risorse', 2,  $_SESSION['prenotazione']['servizio'],  $_SESSION['prenotazione']['gruppir'],  $_SESSION['prenotazione']['ateneo']  );

	$RIS=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );
	//echo "<pre>\n"; print_r($RIS); die('finito qua');

	$DATI['date_risorse']=array();
	$giornoS = array( 0=>"Dom", 1=>"Lun", 2=>"Mar", 3=>"Mer", 4=>"Gio", 5=>"Ven", 6=>"Sab" );

	date_default_timezone_set('UTC');
	$ora=strtotime(date('d-m-Y' , time()));

	$SECONDI_ATTIVI  = $_SESSION['prenotazione']['giorni_p'] * $SECONDI_GIORNO; // giorni_p (giorni di prenotazione) *  secondi nel giorno

	if( $ora < $_SESSION['prenotazione']['aperto_da'] ) 	$INIZIO = $_SESSION['prenotazione']['aperto_da']-$SECONDI_GIORNO+60;  // se inizio servizio e' piu' avanti di ora inizio = inzio servizio: gestione perche il giorno in secondi e' 23:59
	else  													$INIZIO = $ora;

	$INIZIO += $_SESSION['prenotazione']['giorni_vis'] * $SECONDI_GIORNO;  // ora + giorni_vis * secondi nel giorno si somma comunque il delta dei giorni di visualizzazione

	for( $t = $INIZIO;  $t < $INIZIO+$SECONDI_ATTIVI; $t += $SECONDI_GIORNO )
	{


		date_default_timezone_set('UTC');
		$datag=date('Ymd', $t);
		$dataita=dataita($t);
        //$giorno = $giornoS[ $datag=date('w', $t) ];
        $giorno = $giornoS[ date('w', $t) ];
		foreach( is($RIS,'risorse',array()) as $r )
		{
/*
                 giorno si trova fra quelli speciali accessi OPPURE  in_array (  $datag, $gp )

				 risorsa.giorno settimana == giorno attuale       in_array( $giorno, $r['giorno_rip']  )
				 risorsa.NO giorno settimene !=  giorno attuale   |in_array( $giorno, $r['giorno_rip_no']  )

				 giorno si trova fra quelli attivi --> 				 per ogni giorno attivo   $inte

				giorno NON si trova fra quelli speciali spenti
				ritorna...
*/

            if( $r['attiva'] != 'SI' ) continue;

			$gp=array(); 	foreach( is($r,'giorno_part',array() ) as $g )       { date_default_timezone_set('UTC'); $gp[]    = date('Ymd', $g); }    // giorno speciale particolare aperti
  			$gpno=array(); 	foreach( is($r,'giorno_part_no',array() ) as $g )    { date_default_timezone_set('UTC'); $gpno[]  = date('Ymd', $g); }   // giorno speciale particolare chiusi
            $inte=false; 	foreach( is($r,'attivo_dal_al',array() ) as $atda )  if( convdataseriale($atda[0]) <= convdataseriale($t) && convdataseriale($t) < convdataseriale($atda[1]) ) { $inte=true; break; }      // intervallo di attività



			if(
				in_array (  $datag, $gp )     ||    // la data e' fra quelli speciali aperti
				(
						in_array( $giorno, is($r,'giorno_rip',array() )  )  		&&   // il giorno e' fra quelli ripetuti
						! in_array( $giorno, is($r,'giorno_rip_no',array() )  ) 	&&  // il giorno NON è fra quelli non ripetuti
						$inte 											&& // il giorno è nell'intervallo di attività
						! in_array (  $datag, $gpno ) 						// il giorno non e' fra quelli esclusi particolari

				)
			  )
			  {   	// questa risorsa va bene.. la aggiungo...
			  		// prima ci calcolo un po' di cose..


				$dettR=array();
				$dettR['_key']=$r['_key'];
				$dettR['nome']=$r['nome'];

				$elaborazioni_al_minuto = $r['elabxtempo'] / $r['tempo_minimo'];

				$tempo_disponibile_totale_risorsa   = 	calcola_minuti( $r['orario_da'] , $r['orario_a']);
				$numero_slot 						= 	round( $tempo_disponibile_totale_risorsa / $r['ris_tempo'] , 0, PHP_ROUND_HALF_DOWN ); // gli sloto sono dati dai minuti disponibili / lunghezza singolo slot
				$numero_elab_x_slot					= 	round( $r['ris_tempo'] * $elaborazioni_al_minuto  , 0, PHP_ROUND_HALF_DOWN ); // capieza slot .. larghezza slot X minuti_per_ogni elaborazione

				$dettR['numero_slot'] 				= 	$numero_slot;		  // quanti slot ho
				$dettR['elab_x_minuto']				=   $elaborazioni_al_minuto;
				$dettR['capienza_slot'] 			=	$numero_elab_x_slot;  // quanto sta dentro ad ogni slot

				$dettR['dettagli ora'] 				=	array($t, $r['orario_da'], $r['ris_tempo'], $numero_slot);  // debug si puo' togliere.....

				$dettR['elenco_slot_possibili'] 	=	calcola_slot( $t, $r['orario_da'], $r['ris_tempo'], $numero_slot);  // prenotazioni a 0



				foreach($dettR['elenco_slot_possibili'] as $k  => &$AA  )
				{
						$query=array(  'contaprenotazioni', 3,  $r['_key'],  $dataita,  $k  );  // IDX slot parte da 0 ....
						$RISP=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );
						$AA['numero_prenotazioni']=$RISP['contaprenotazioni'][0];
				}


			  	$DATI['date_risorse'][$t][]=$dettR;

			  }

		}
	}

    //sdie($DATI['date_risorse']);
	//echo "<pre>\n"; print_r($DATI); die('finito qua');


}

if( $_SESSION['stato'] == 'finale')
{

	$DATI=QUERYDB(URLDB.'?get&tab=risorse&id='.$_SESSION['prenotazione']['IDR']);
	//echo "<pre>\n"; print_r($DATI); die('finito qua');

}



if( $_SESSION['stato'] == 'fine' )
{

	// unset($_SESSION['prenotazione']['cf']);     cancello CF che non memorizzo.... eliminato comando.. adesso lo memorizzo
	$DATI=QUERYDB( URLDB, array( 'new_prenotazione'=>json_encode($_SESSION['prenotazione'])  ) );

	$_SESSION['prenotazione']['ID']=$DATI['_key'];
    logattivita("Creata prenotazione ".$_SESSION['prenotazione']['ID']);



	$ris=crea_pdf_prenotazione( $_SESSION['prenotazione'] );

	if( $ris['ris'] )
	{
		$risM=\triagens\ArangoDb\InviaMail($ris['file'], $_SESSION['prenotazione']['email'], 'prenotazione', $ris);
		unlink ($ris['file']);
        logattivita("Inviata mail creazione prenotazione ID: ".$_SESSION['prenotazione']['ID']. " mail: ".$_SESSION['prenotazione']['email']." : ".$ris['file']);
	}


	$DATI['mail'] = $_SESSION['prenotazione']['email'];
	$DATI['ris']=$ris;
	$DATI['risM']=$ris;

	//echo "<pre>\n"; print_r($DATI); die('finito qua');

	unset($_SESSION['stato']);
	unset($_SESSION['prenotazione']);
	unset($_SESSION['disponibilita']);

	$STATO_FINALE='fine';

}




?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prenotazioni</title>
    	<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      	<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

		<link rel="stylesheet" href="<?php echo $URL ?>/stile.css">
  		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
    	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    	<script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>
    	<!-- <script src="<?php echo URLIB ?>/validator/validator.min.js"></script> -->
    	<script src="<?php echo URLIB ?>/validator/validator.min.js"></script>

<style>
input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {    -webkit-appearance: none;    margin: 0; }
			input[type='number'] {  -moz-appearance:textfield;}
			input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; }

.help-block {     color: white; }


.spaziodata  { margin-left:0px; }
@media screen and (min-width: 768px) {	.spaziodata  { margin-left:5px; } }
@media screen and (min-width: 992px) { 	.spaziodata  { margin-left:25px; } }
@media screen and (min-width: 1200px) { .spaziodata  { margin-left:65px; } }


</style>

<script type="text/javascript">
$(document).ready(function(){
   $('#emailnew').on("cut copy paste",function(e) {
      e.preventDefault();
   });
});
</script>

</head>


<?php 	  if( $utente == 'dummy') 	{ ?>

		<body style="background-image: url('bg.jpg');">
		<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">

<?php } else { ?>

		<body>
 		<div class="container-fluid" style="margin:15px">

<?php } ?>


   <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php
	  if( $utente == 'dummy') 	echo intestazionepub('NO');
	  else 						echo navigazione();

	  ?>
</div>
<div class="row"><div class="col-xs-12 col-md-offset-3 col-md-5" >

 <h4><?php $l="Procedura Gestione Prenotazioni"; echo l($l); ?></h4>



<?php

if( isset($STATO_FINALE) &&  $STATO_FINALE == 'fine') 	htmlfine();
else
{

/* 		echo "<pre>\n";
		echo "POST -> \n";print_r($_POST)."\n";
		echo "\nGET  -> \n";print_r($_GET)."\n";
		echo "\nSTATO  -> ";print_r($_SESSION['stato'])."\n";
		echo "\nPRENOTAZIONE  -> \n";print_r($_SESSION['prenotazione'])."\n";
		//echo "\nPRENOTAZIONE  -> \n";print_r($_SESSION['captcha'])."\n";
		if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
		print_r($utente);
		echo "</pre>\n"; */

		if(			$_SESSION['stato'] == 'inizio' 		) 		{ 	htmlinizio();		}
		else if( 	$_SESSION['stato'] == 'servizio' 	)		{ 	htmlservizio();  	}
		else if( 	$_SESSION['stato'] == 'ateneo' 		)		{ 	htmlateneo();  	}
		else if( 	$_SESSION['stato'] == 'indirizzo' 	)		{ 	htmlgruppir();  	}
		else if( 	$_SESSION['stato'] == 'data' 		)		{ 	htmldateslot();  	}
		else if( 	$_SESSION['stato'] == 'finale' 		)		{ 	htmlfinale();  	}

}

?>
</div></div>




</body>
</html>


<?php
function htmlfine()
{
	global $DATI, $_SESSION, $URL;


	if($DATI['ris']['ris'] && $DATI['risM']['ris'])
	{
	?>
	<h3><label for="servizio" class="control-label"><?php $l="Prenotazione creata : ID"; echo l($l); ?> <?php echo $DATI['_key'] ?></label></h3>
	<br><br>
	<h5><label for="servizio" class="control-label"><?php $l="Riceverai mail all'indrizzo"; echo l($l); ?> <?php  $DATI['mail'] ?> <?php $l="con la conferma e le istruzioni di dettaglio"; echo l($l); ?></label></h5>

	<?php } else {?>

	<h3><label for="servizio" class="control-label"><?php $l="Prenotazione NON creata !! "; echo l($l); ?></label></h3>
	<br><br>
	<?php if( !$DATI['ris']['ris'])  { ?><h5><label for="servizio" class="control-label"><?php $l="Messaggio di errore creazione informazione"; echo l($l); ?> :  <?php  $DATI['ris'][0] ?> </label></h5><?php }?>
	<?php if( !$DATI['risM']['ris']) { ?><h5><label for="servizio" class="control-label"><?php $l="Messaggio di errore invio mail"; echo l($l); ?>  :  <?php  $DATI['risM'][0] ?></label></h5><?php }?>
	<h5><?php $l="Riprovare piu' tardi !"; echo l($l); ?></h5>

	<?php } ?>


<form id="formgruppir" action="<?php echo $URL ?>" method="post">

  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="TORNA ALLA PAGINA PRINCIPALE">
  </div>
</form>
<br>
<br>
<p>&nbsp;</p>

<?php
}




function htmlfinale()
{
	global $DATI, $_SESSION, $URL;


	?>
	<label for="servizio" class="control-label"><?php $l="Riassunto informazioni inserite"; echo l($l); ?>: </label>

	<?php
	echo "<pre>\n";

    if(!isset($_SESSION['lang']) ||  $_SESSION['lang']=='IT')
    {

	printf("<b>Studente</b> : \n");

	printf("[Nome]    %s\n", $_SESSION['prenotazione']['nome']);
	printf("[Cognome] %s\n", $_SESSION['prenotazione']['cognome']);
	printf("[Matr.]   %s\n", $_SESSION['prenotazione']['matricola']);
	printf("[CF]      %s\n", $_SESSION['prenotazione']['cf']);
	printf("[Ateneo]  %s\n", $_SESSION['prenotazione']['ateneo']);
	printf("[Email]   %s\n", $_SESSION['prenotazione']['email']);
	printf("[Cell.]   %s\n", $_SESSION['prenotazione']['cellulare']);
	//printf("  [accettazione informativa dati personali]\n      %s\n", ($_SESSION['prenotazione']['informativa_personali'] == 'on')?'SI':'NO');
	//printf("  [consenso trattamento informazioni]\n      %s\n", ($_SESSION['prenotazione']['consenso'])?'SI':'NO'  );

	printf("\n<b>Prenotazione</b> : \n");

	printf("[Servizio]\n      %s\n", $_SESSION['prenotazione']['servizio']);
	printf("[Indirizzo]\n      %s - %s\n", $_SESSION['prenotazione']['gruppir'], $_SESSION['prenotazione']['gruppir_ind']);
	printf("[Nome sportello]\n      %s\n", $DATI['nome']);
	printf("[data di prenotazione]\n      %s\n", $_SESSION['prenotazione']['data']);
	printf("[ora di prenotazione ]\n      %s\n", $_SESSION['prenotazione']['orada']);
    }
    else { // inglese secco....
        printf("<b>Student</b> : \n");

    	printf("[Name]\n      %s\n", $_SESSION['prenotazione']['nome']);
    	printf("[SurName]\n      %s\n", $_SESSION['prenotazione']['cognome']);
    	printf("[Matricola]\n      %s\n", $_SESSION['prenotazione']['matricola']);
    	printf("[Italian Fiscal Code]\n      %s\n", $_SESSION['prenotazione']['cf']);
    	printf("[School of subscription]\n      %s\n", $_SESSION['prenotazione']['ateneo']);
    	printf("[Email]\n      %s\n", $_SESSION['prenotazione']['email']);
    	printf("[Mobile]\n      %s\n", $_SESSION['prenotazione']['cellulare']);
    	//printf("  [accettazione informativa dati personali]\n      %s\n", ($_SESSION['prenotazione']['informativa_personali'] == 'on')?'SI':'NO');
    	//printf("  [consenso trattamento informazioni]\n      %s\n", ($_SESSION['prenotazione']['consenso'])?'SI':'NO'  );

    	printf("\n<b>Reservation</b> : \n");

    	printf("[Service]\n      %s\n", $_SESSION['prenotazione']['servizio']);
    	printf("[Address]\n      %s - %s\n", $_SESSION['prenotazione']['gruppir'], $_SESSION['prenotazione']['gruppir_ind']);
    	printf("[Name office]\n      %s\n", $DATI['nome']);
    	printf("[data of reservation]\n      %s\n", $_SESSION['prenotazione']['data']);
    	printf("[hous of  reservation]\n      %s\n", $_SESSION['prenotazione']['orada']);
    }

	?>
	</pre>
<form id="formgruppir" data-toggle="validator"  role="form" action="<?php echo $URL .'/'.$pagina ?>" method="post">

  <div class="form-group has-feedback">
    <label for="emailnew" class="control-label"><?php $l="Inserisci nuovamente la email cui inviare la prenotazione. "; echo l($l); ?><br><b><span style="color:red"><?php $l="CONTROLLARE"; echo l($l); ?></span><?php $l="che sia corretta"; echo l($l); ?> ..</b><?php $l=" a questo indirizzo verranno inviate conferma e codice per cancellazione<br>(L'indirizzo di email deve essere uguale a quella inserita prima..)"; echo l($l); ?></label>
    <div class="input-group">
      <span class="input-group-addon">@</span>
      <input type="text" pattern="^<?php echo $_SESSION['prenotazione']['email'] ?>$"  maxlength="50" class="form-control" id="emailnew" placeholder="Email studente" data-error="Mai non uguale a quella inserita.." required name="emailnew" value="">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="CREA PRENOTAZIONE"  >
  </div>
</form>
<form action="<?php echo $URL .'/'.$pagina?>" method="post">
    <input type="submit" name ="submit" class="btn btn-warning" value="<?php echo INDIETRO ?>">
</form>



<?php
}




function htmldateslot()
{
	global $DATI, $_SESSION, $URL, $utente;
	?>

	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Servizo scelto"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">S</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['servizio']?>" disabled>
	</div>
	</div>

	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Ateneo frequentato"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">A</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['ateneo']?>" disabled>
	</div>
	</div>

	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Indirizzo sportello dove ricevere il servizio"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">G</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['gruppir'] .' - ' . $_SESSION['prenotazione']['gruppir_ind']?>" disabled>
	</div>
	</div>



<form id="formgruppir" action="<?php echo $URL .'/'.$pagina?>" method="post">

<table class="table table-hover">

 <tr><th><?php $l="Date"; echo l($l); ?><br><?php $l="disponibili"; echo l($l); ?></th><th><?php $l="Orario"; echo l($l); ?></th></tr>

 <?php

 foreach($DATI['date_risorse'] as $k => $risorse)
 {
		$dataita=dataita( $k );
		$dataitarov=dataita( $k, 'rov' );
		$slotP='';

		echo "<tr>";
		echo "<td>$dataita</td>";
		echo "<td>";
		foreach($risorse as $ris)
		{
				echo $ris['nome']."<br>";
				foreach($ris['elenco_slot_possibili'] as $kslot => $slot)
				{
					if( $slot['ora'] == '00:00' && $slot['orafine'] == '23:45') 	 	printf('<span class="spaziodata">tutto il giorno &nbsp;&nbsp;&nbsp;</span>' );
					else  																printf('<span class="spaziodata">%s -> %s &nbsp;&nbsp;&nbsp;</span>', $slot['ora'],  $slot['orafine'] );

					if( $slot['numero_prenotazioni'] >= $ris['capienza_slot'] ) echo '<span style="color:red">non disponibile</span><br>';
					else
					{
						echo '<span style="color:green">OK</span> .. &nbsp;&nbsp; ';

						if(  ! isset( $_SESSION['disponibilita'] ) || $_SESSION['disponibilita'] != 'SI' )
						printf('<a href="%s/%s?submit=%s&IDR=%s&data=%s&slot=%s&orada=%s" style="color:blue">%s</a>',  $URL, $pagina, AVANTI, $ris['_key'], $dataitarov, $kslot, $slot['ora'], l("prenota!"));

						if(  $utente != 'dummy' ) printf( '&nbsp;&nbsp;&nbsp; [ tot=%s &nbsp;&nbsp;&nbsp; disp=%s &nbsp;&nbsp;&nbsp; occ=%s ]   <br>', $ris['capienza_slot'], $ris['capienza_slot']-$slot['numero_prenotazioni'], $slot['numero_prenotazioni']);
						else echo "<br>";
					}

				}

		}
		echo "</td>";
		echo "</tr>\n";
 }
 ?>

</table>


  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-warning" value="<?php echo INDIETRO ?>">
  </div>
</form>


<?php
}





function htmlgruppir()
{
	global $DATI, $_SESSION, $URL;

	$selez=(isset(  $_SESSION['prenotazione']['gruppir'] ))? $_SESSION['prenotazione']['gruppir'] :'';

	?>

	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Servizo scelto"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">S</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['servizio']?>" disabled>
	</div>
	</div>

	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Ateneo frequentato"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">A</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['ateneo']?>" disabled>
	</div>
	</div>

<form id="formgruppir" action="<?php echo $URL .'/'.$pagina?>" method="post">

  <div class="form-group">
    <label for="gruppir" class="control-label"><?php $l="Indirizzo sportello dove ricevere il servizio"; echo l($l); ?></label>
    <div class="input-group">
      <span class="input-group-addon">G</span>

			<select class="form-control" name="selectgruppir">
			<?php
				foreach( $DATI['gruppir'] as $s) printf( '<option value="%s||%s" %s >%s</option>', $s[0]['gruppo_risorsa'], $s[0]['indirizzo'], ( $selez!='' && $selez == $s[0]['gruppo_risorsa']) ? ' selected="selected"' : '' , $s[0]['gruppo_risorsa']." - ".$s[0]['indirizzo']   );
			?>
			</select>
			<?php  	if( count($DATI['gruppir']) == 0 ) 	{ 	?>
			<label for="servizgruppirio" class="control-label"><?php $l="Non sono disponibili indirizzi di sportelli per questo servizio e/o ateneo ..."; echo l($l); ?></label>
			<?php } ?>

			<?php if( isset($msgerrore['gruppir'])  )  echo '<label for="servizio" class="control-label" style="color:red">'.$msgerrore['gruppir'].'</label>'; // mai usato...
			?>

    </div>
  </div>

  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-warning" value="<?php echo INDIETRO ?>">
    <?php  	if( count($DATI['gruppir']) > 0  && !isset($msgerrore['gruppir'])) 	{ 	?>
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="<?php echo AVANTI ?>">
    <?php } ?>
  </div>
</form>


<?php
}







function htmlateneo()
{
	global $DATI, $_SESSION, $URL;

	$selez=(isset(  $_SESSION['prenotazione']['ateneo'] ))? $_SESSION['prenotazione']['ateneo'] :'';
	?>


	<div class="form-group">
	<label for="servizio" class="control-label"><?php $l="Servizo scelto"; echo l($l); ?></label>
	<div class="input-group">
	<span class="input-group-addon">S</span>
	<input type="text"  class="form-control"  value="<?php echo $_SESSION['prenotazione']['servizio']?>" disabled>
	</div>
			</div>

<form id="formateneo" action="<?php echo $URL .'/'.$pagina?>" method="post">

  <div class="form-group">
    <label for="ateneo" class="control-label"><?php $l="Ateneo frequentato"; echo l($l); ?></label>
    <div class="input-group">
      <span class="input-group-addon">A</span>

			<select class="form-control" name="selectateneo">
			<?php
				foreach( $DATI['atenei'] as $s) printf( '<option value="%s" %s >%s</option>', $s['ateneo'],     ( $selez!='' && $selez == $s['ateneo']) ? ' selected="selected"' : '' ,     $s['descrizione']   );
			?>
			</select>
    </div>
  </div>

  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-warning" value="<?php echo INDIETRO ?>">
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="<?php echo AVANTI ?>">
  </div>
</form>


<?php
}




function htmlservizio()
{
	global $DATI, $URL, $msgerrore;

	?>


<form id="formservizi" action="<?php echo $URL .'/'.$pagina?>" method="post">

  <div class="form-group">
    <label for="servizio" class="control-label"><?php $l="Servizio desiderato"; echo l($l); ?></label>
    <div class="input-group">
      <span class="input-group-addon">S</span>

			<select class="form-control" name="selectservizi">
			<?php
				foreach( $DATI['servizi'] as $s) printf( '<option value="%s||%s||%s||%s||%s||%s||%s||%s||%s">%s</option>', $s['nome'], $s['giorni_p'], $s['multipla_p'], $s['controllo_matr'], $s['stampa_delega'], $s['ore_canc'], $s['giorni_vis'], $s['aperto_da'], $s['tipo_servizio'],  $s['descrizione']   );
			?>
			</select>
			<?php  	if( count($DATI['servizi']) == 0 ) 	{ 	?>
			<label for="servizio" class="control-label"><?php $l="Non sono disponibili servizi per questa matricola in questo periodo..."; echo l($l); ?></label>
			<?php } ?>

			<?php  	if( isset($msgerrore['servizio'])  )  echo '<label for="servizio" class="control-label" style="color:red">'.$msgerrore['servizio'].'</label>';  ?>


    </div>
  </div>

  <div class="form-group">

  <?php   if ($_SESSION['disponibilita']  == 'NO')  {  ?>

    <input type="submit" name ="submit" class="btn btn-warning" value="<?php echo INDIETRO ?>">

    <?php } ?>

	<?php  	if( count($DATI['servizi']) > 0  && !isset($msgerrore['servizio'])) 	{ 	?>
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="<?php echo AVANTI ?>">
	<?php } ?>


  </div>
</form>
	<?php
}




function htmlinizio()
{
	global $_SESSION, $URL;
	?>
 <h5><?php $l="Dati genearli identificazione"; echo l($l); ?></h5>

<form data-toggle="validator" role="form" action="<?php echo $URL .'/'.$pagina?>" method="post">


  <div class="form-group has-feedback">
    <label for="nome" class="control-label"><?php $l="Nome"; echo l($l); ?> *</label>
    <div class="input-group">
      <span class="input-group-addon">N</span>
      <input type="text" pattern="^[\sA-Za-z]*$" maxlength="20" class="form-control" id="nome" placeholder="<?php $l="Nome studente"; echo l($l); ?>" data-error="<?php $l="Non sono ammessi caratteri strani"; echo l($l); ?>" required name="nome" value="<?php echo $_SESSION['prenotazione']['nome']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group has-feedback">
    <label for="cognome" class="control-label"><?php $l="Cognome"; echo l($l); ?> *</label>
    <div class="input-group">
      <span class="input-group-addon">C</span>
      <input type="text" pattern="^[\sA-Za-z]*$" maxlength="30" class="form-control" id="cognome" placeholder="<?php $l="Cognome studente"; echo l($l); ?>" data-error="<?php $l="Non sono ammessi caratteri strani"; echo l($l); ?>" required name="cognome" value="<?php echo $_SESSION['prenotazione']['cognome']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>



  <div class="form-group has-feedback">
    <label for="matricola" class="control-label"><?php $l="Matricola"; echo l($l); ?> *</label>
    <div class="input-group">
      <span class="input-group-addon">M</span>
      <input type="text" pattern="^[A-Za-z0-9]*$" maxlength="20" class="form-control" id="matricola" placeholder="<?php $l="Matricola studente"; echo l($l); ?>" data-error="<?php $l="Sono ammessi solo numeri o caratteri semplici. No spazi. Se non hai matricola universitaria inserisci 0"; echo l($l); ?>" required name="matricola" value="<?php echo $_SESSION['prenotazione']['matricola']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>

<?php $errdan= ( $_SESSION['prenotazione']['nome'] !='' && $_SESSION['prenotazione']['cf'] == '') ? 'has-error has-danger':'';  ?>
  <div class="form-group has-feedback  <?php echo $errdan?>">
    <label for="cf" class="control-label"><?php $l="Codice Fiscale"; echo l($l); ?> *</label>
    <div class="input-group">
      <span class="input-group-addon">M</span>
      <input type="text" pattern="^[A-Za-z]{6}[a-zA-Z0-9]{2}[a-zA-Z][a-zA-Z0-9]{2}[a-zA-Z][a-zA-Z0-9]{3}[a-zA-Z]$" maxlength="16" class="form-control" id="cf" placeholder="<?php $l="Codice fiscale studente"; echo l($l); ?>" data-error="<?php $l="Codice fiscale ERRATO"; echo l($l); ?>" required name="cf" value="<?php echo $_SESSION['prenotazione']['cf']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group has-feedback">
    <label for="email" class="control-label">Email *</label>
    <div class="input-group">
      <span class="input-group-addon">@</span>
      <input type="text" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"  maxlength="50" class="form-control" id="email" placeholder="<?php $l="Email studente"; echo l($l); ?>" data-error="<?php $l="Formato email non riconosciuto"; echo l($l); ?>" required name="email" value="<?php echo $_SESSION['prenotazione']['email']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group">
    <label for="cellulare" class="control-label"><?php $l="Cellulare"; echo l($l); ?></label>
    <div class="input-group">
      <span class="input-group-addon">T</span>
      <input type="text" maxlength="14" class="form-control" id="cellulare" placeholder="<?php $l="Cellulare studente per eventuali comunicazioni"; echo l($l); ?>" name="cellulare" value="<?php echo $_SESSION['prenotazione']['cellulare']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group">
    <div class="checkbox">
      <label>
        <input type="checkbox" id="informativa_personali" data-error="<?php $l="Devi accettare l'informaitiva  altimenti non e' possibile procedere con l'acquisizione dei tuoi dati"; echo l($l); ?>" required name="informativa_personali" <?php echo ($_SESSION['prenotazione']['informativa_personali']=='on') ?  'checked':'';?>>
        <?php $l="Dichiaro di aver preso visione della"; echo l($l); ?> <a href="https://www.MY_SITE.it/privacy/" target="_blank">informativa sul trattamento dei dati personali</a> *
      </label>
      <div class="help-block with-errors"></div>
    </div>
  </div>


  <div class="form-group">
    <div class="checkbox">
      <label>
        <input type="checkbox" id="consenso" data-error="<?php $l="Devi accettare il trattamento dei tuoi dati altimenti non e' possibile procedere con l'acquisizione dei tuoi dati"; echo l($l); ?>" required name="consenso" <?php echo ($_SESSION['prenotazione']['consenso']=='on') ? 'checked':''; ?>>
        <?php $l="Acconsento al trattamento dei dati"; echo l($l); ?> *
      </label>
      <div class="help-block with-errors"></div>
    </div>
  </div>


<?php $errdan= ( $_SESSION['prenotazione']['nome'] !='' && $_SESSION['prenotazione']['robot'] == '') ? 'has-error has-danger':'';  ?>

  <div class="form-group <?php echo $errdan?>">
    <label for="robot" class="control-label"><?php $l="Inserisci i numeri che vedi.."; echo l($l);?> * </label>
    <img src="<?php echo $_SESSION['captcha']['image_src'] ?>" alt="CAPTCHA" />
    <div class="input-group">
      <span class="input-group-addon">H</span>
      <input type="text" pattern="^[0-9]{6}$"  maxlength="6" class="form-control" id="robot" placeholder="<?php $l="Inserisci i 6 numeri che vedi qua..."; echo l($l); ?>" required name="robot" value="<?php echo $_SESSION['prenotazione']['robot']?>">
    </div>
  </div>

  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="<?php echo AVANTI ?>">
  </div>
<br><p>&nbsp;</p>

</form>




<?php  }  ?>
