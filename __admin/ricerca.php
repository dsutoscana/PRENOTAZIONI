<?php

//http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.php
//http://1000hz.github.io/bootstrap-validator/
//https://www.abeautifulsite.net/a-simple-php-captcha-script


//https://github.com/bootstrap-wysiwyg/bootstrap3-wysiwyg

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


if( !isset( $_SESSION['DBprenotazioni'] ) ) $_SESSION['DBprenotazioni']='prenotazioni';   // default NO storico... per cui attuale
if(  isset( $_GET['storico']) && $_GET['storico']=='SI' ) { $_SESSION['DBprenotazioni']='prenotazionistoriche';  unset($_SESSION['statoric']);}		// se lo setto esplicitamente diventa storico. reset ricerca
if(  isset( $_GET['storico']) && $_GET['storico']=='NO' ) { $_SESSION['DBprenotazioni']='prenotazioni';   unset($_SESSION['statoric']);}		// se lo setto esplicitamente NO diventa attuale. reset ricerca


/*
echo "<pre>\n";
echo "POST -> \n";print_r($_POST)."\n";
echo "\nGET  -> \n";print_r($_GET)."\n";
echo "\nSTATO  -> ";print_r($_SESSION['statoric'])."\n";
echo "\nRICERCA  -> \n";print_r($_SESSION['ricerca'])."\n";
//if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
echo "</pre>\n";
*/


if(   	!isset( $_SESSION['statoric'] )    											||
		(  isset($_POST['submit']) && $_POST['submit'] == 'RESET RICERCA' )

  ) // -------------------------------------------------------------------------------------------  inizio
{

	unset( $_SESSION['ricerca'] );
// data, ateneo, risorsa, servizio, gruppor  matricola mail IDP
    $_SESSION['ricerca']['data']='';
	$_SESSION['ricerca']['data_da']='';
    $_SESSION['ricerca']['data_a']='';
	$_SESSION['ricerca']['atenei']=array();
    $_SESSION['ricerca']['risorse']=array();
	$_SESSION['ricerca']['servizi']=array();
    $_SESSION['ricerca']['gruppir']=array();
    $_SESSION['ricerca']['matricola']='';
    $_SESSION['ricerca']['cognome']='';
	$_SESSION['ricerca']['mail']='';
	$_SESSION['ricerca']['cf']='';
	$_SESSION['ricerca']['IDP']='';
	$_SESSION['ricerca']['testomail']='';
	$_SESSION['ricerca']['oggettomail']='';

	$_SESSION['statoric']='inizio';

}


if(
	( $_SESSION['statoric']=='inizio'  && isset($_POST['submit']) &&  $_POST['submit'] == 'RICERCA' )  ||   // ------------------------  ricerca
	( $_SESSION['statoric']=='tabella' && isset($_POST['submit']) &&  $_POST['submit'] == 'RICERCA' )   // ------------------------  reload pagina dalla ricerca...
  )
{

    	$_SESSION['ricerca']['data']=is($_POST,'data');
		$_SESSION['ricerca']['data_da']=is($_POST,'data_da');
    	$_SESSION['ricerca']['data_a']=is($_POST,'data_a');

    	if(isset($_POST['selectrisorse']) ) foreach( $_POST['selectrisorse'] as $k )  { $ar=explode('||', $k);  $_SESSION['ricerca']['risorse'][ $ar[0] ] = $ar[1]; }
    	else $_SESSION['ricerca']['risorse']=array();

    	if( isset($_POST['selectatenei'])  )   $_SESSION['ricerca']['atenei']  = $_POST['selectatenei'];    else  $_SESSION['ricerca']['atenei']=array();
    	if( isset($_POST['selectservizi']) )   $_SESSION['ricerca']['servizi'] = $_POST['selectservizi'];   else  $_SESSION['ricerca']['servizi']=array();
    	if( isset($_POST['selectgruppir']) )   $_SESSION['ricerca']['gruppir'] = $_POST['selectgruppir'];   else  $_SESSION['ricerca']['gruppir']=array();

    	$_SESSION['ricerca']['matricola'] 	= strtoupper( is($_POST,'matricola') );
    	$_SESSION['ricerca']['cognome'] 	= is($_POST,'cognome');
		$_SESSION['ricerca']['mail'] 		= is($_POST,'mail');
		$_SESSION['ricerca']['cf'] 			= is($_POST,'cf');
		$_SESSION['ricerca']['IDP'] 		= is($_POST,'IDP');


		$AQL= array();
    	$AQL[0]='prenotazioni';
    	$AQL[1]=7;

		if( $_SESSION['DBprenotazioni']=='prenotazionistoriche' ) $AQL[1]=9;

    	$k='data'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='data_da'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='data_a'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='risorse'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='atenei'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='servizi'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='gruppir'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='matricola'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='cognome'; $AQL[$k]=$_SESSION['ricerca'][$k];
		$k='mail'; $AQL[$k]=$_SESSION['ricerca'][$k];
		$k='cf'; $AQL[$k]=$_SESSION['ricerca'][$k];
    	$k='IDP'; $AQL[$k]=$_SESSION['ricerca'][$k];

    	//sdie($_SESSION);

    	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($AQL)  ) );
    	//echo "<pre>\n"; print_r($DATI); die();

    	$_SESSION['ricerca']['query']=$AQL;
    	$_SESSION['ricerca']['keys']=array(); foreach($DATI['prenotazioni'] as $p) $_SESSION['ricerca']['keys'][] = $p['_key'];

		$_SESSION['ricerca']['testomail'] = is($_POST, 'testomail', $_SESSION['ricerca']['testomail']);
		$_SESSION['ricerca']['oggettomail'] = is($_POST, 'oggettomail', $_SESSION['ricerca']['oggettomail']);


    	$_SESSION['statoric']='tabella';

}


if(  ($_SESSION['statoric'] == 'tabella' || $_SESSION['statoric'] == 'operativo' ) && isset($_POST['submit']) && $_POST['submit'] == 'TORNA ALLA RICERCA')  // ------ pulisici e inizio
{
		$_SESSION['statoric'] = 'inizio';
}



if( $_SESSION['statoric'] == 'tabella'  && isset($_POST['testomail'])  )                          // -------------------------------------   viene dalla creazione testo mail
{
	$_SESSION['ricerca']['testomail'] = is($_POST, 'testomail', $_SESSION['ricerca']['testomail']);
	$_SESSION['ricerca']['oggettomail'] = is($_POST, 'oggettomail', $_SESSION['ricerca']['oggettomail']);

	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($_SESSION['ricerca']['query'])  ) );
	//echo "<pre>\n"; print_r($DATI); die();

}

// ----------------------------------------------------------------------------------------  OPERATIVO....
// operativo dello stato
if( $_SESSION['statoric']=='inizio' )
{
		// richiesta dati per riempire le form a discesa
		$ORTAB=array(1=>'atenei', 2=>'gruppir', 3=>'servizi', 4=>'risorse');
		$listaDB=json_encode($ORTAB);
		$DATI=QUERYDB(URLDB.'?get_all_crud_sort='.$listaDB);
}



if(  $_SESSION['statoric'] == 'tabella'  && isset($_POST['submit']) && $_POST['submit'] == 'CANCELLA TUTTI')  // ------ cencella tutti
{
		$_SESSION['statoric'] = 'operativo';
		$DATI=QUERYDB( URLDB, array( 'delepres'=>json_encode($_SESSION['ricerca']['keys'])  ) );  // qua in base al valore della sessione decide il DB...
		//echo "<pre>\n"; print_r($DATI); die();

		if( $DATI['error'] != 1 ) 	$risultato_operazione="Cancellazione delle prenotazioni terminata con successo !";
		else 						$risultato_operazione="Cancellazione delle prenotazioni terminata con ERRORE ! .. Messasggio di errore : ".$DATI['error_msg'];

		logattivita("Procedura Cancella TUTTI. Ris: ".$risultato_operazione);
}


if(  $_SESSION['statoric'] == 'tabella'  && isset($_GET['dele']) && isset($_GET['ID']) )   // ------ cencella uno solo con o senza mail
{

	$_SESSION['statoric'] = 'operativo';
	$DATI=QUERYDB( URLDB . '?dele&tab=' . $_SESSION['DBprenotazioni'] . '&ID=' . $_GET['ID'] );
	//echo "<pre>\n"; print_r($DATI); die();

	if( $DATI['error'] != 1 ) 	$risultato_operazione="Cancellazione della prenotazione terminata con successo !";
	else 						$risultato_operazione="Cancellazione della prenotazione terminata con ERRORE ! .. Messasggio di errore : ".$DATI['error_msg'];

	logattivita("procedura Cancella prenotazione singola. ID: " .$_GET['ID']. " Ris: ".$risultato_operazione);

	if(  $_SESSION['DBprenotazioni']=='prenotazioni' && !isset($_GET['NO']) )
	{
			$ris=crea_pdf_cancellazione( $DATI['doc'] );

			if( $ris['ris'] )
			{
					$risM=\triagens\ArangoDb\InviaMail($ris[0], $DATI['doc']['email'], 'cancellazione');
					unlink ($ris[0]);
			}
	}


}



if(  $_SESSION['statoric'] == 'tabella'  && isset($_POST['submit']) && $_POST['submit'] == 'INVIA MAIL')     // -------------    invia email
{
	$_SESSION['statoric'] = 'operativo';

	$ris=\triagens\ArangoDb\InserisciMail( $_SESSION['ricerca']['oggettomail'], CreaMailTemplate( $_SESSION['ricerca']['testomail'] ), $_SESSION['ricerca']['keys'] );

	if( $ris['ris'] ) $risultato_operazione="Operazione di inserimento di ".$ris[0]." email in coda di invio terminata con successo !";
	else $risultato_operazione="Operazione di inserimento delle email in coda di invio terminata con ERRORE : messaggio di errore : ".$ris[0];

	logattivita( "Procedura invia mail massiva. Ris: ".$risultato_operazione );
}




if(  $_SESSION['statoric'] == 'tabella'  && isset($_POST['submit']) && $_POST['submit'] == 'INVIA MAIL PROVA')     // -------------    invia email
{
	$_SESSION['statoric'] = 'operativo';

	$ris=\triagens\ArangoDb\InserisciMail( $_SESSION['ricerca']['oggettomail'], CreaMailTemplate( $_SESSION['ricerca']['testomail'] ), $_SESSION['emaillogin']  );

	if( $ris['ris'] ) $risultato_operazione="Operazione di inserimento di ".$ris[0]." email in coda di invio terminata con successo !";
	else $risultato_operazione="Operazione di inserimento delle email in coda di invio terminata con ERRORE : messaggio di errore : ".$ris[0];

	logattivita( "Procedura invia mail massiva. Ris: ".$risultato_operazione );
}







/*
echo "<pre>\n";
echo "\nSTATO  -> ";print_r($_SESSION['statoric'])."\n";
echo "\nRICERCA  -> \n";print_r($_SESSION['ricerca'])."\n";
//if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
echo "</pre>\n";
*/



?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestione prenotazioni</title>
    	<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      	<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

		<link href="<?php echo URLIB ?>/wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"></link>

		<link rel="stylesheet" href="<?php echo URLADM ?>/stile.css">
  		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
    	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    	<script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>
    	<script src="<?php echo URLIB ?>/validator/validator.min.js"></script>

		<script src="<?php echo URLIB ?>/wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
		<script src="<?php echo URLIB ?>/wysihtml5/locales/bootstrap-wysihtml5.it-IT.js"></script>


        <style>
        input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {    -webkit-appearance: none;    margin: 0; }
        			input[type='number'] {  -moz-appearance:textfield;}
        			input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; }
        .help-block { color: white; }
        </style>



</head>




<?php
		if($_SESSION['statoric'] == 'inizio' ) {   //---------------------------------------------------------------------------   INIZIO
?>


<body style="background-image: url('bg.jpg');">
<div class="container-fluid" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


   <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php echo navigazione();  ?>
</div>



<div class="row"><div class="col-xs-12 col-md-offset-2 col-md-8" >
    <h4>Procedura Gestione Prenotazioni</h4>
    <h4>Pagina ricerca prenotazioni per esecuzione comandi</h4>
	<?php if( $_SESSION['DBprenotazioni']=='prenotazionistoriche' ) { ?>
		<h4 style="color:red">Accesso ad ARCHIVIO PRENOTAZIONI STORICHE (Prenotazioni Archiviate)</h4>
	<?php } ?>
    <h4>Inserisci il valore in uno o piu' campi</h4>
    <h4>La ricerca è fatta in AND</h4>
    <h4>nel cognome puoi inserire il simbolo % </h4>
<?php
/*
		echo "<pre>\n";
		echo "POST -> \n";print_r($_POST)."\n";
        echo "\nGET  -> \n";print_r($_GET)."\n";
        echo "\nSESSION  -> \n";print_r($_SESSION['ricerca'])."\n";

        //echo "\nDATI  -> \n";print_r($DATI)."\n";
		echo "</pre>\n";
*/
        form_ricerca();
?>
</div></div>

<?php } ?>


<?php if($_SESSION['statoric'] == 'tabella' ) { //---------------------------------------------------------------------------   ELENCO TROVATI
?>

<body >
<div class="container-fluid" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


   <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php echo navigazione();  ?>
</div>



<div class="row"><div class="col-md-12" >
    <h4>Procedura Gestione Prenotazioni</h4>
    <h4>Pagina risultati ricerca</h4>
	<?php if( $_SESSION['DBprenotazioni']=='prenotazionistoriche' ) { ?>
		<h4 style="color:red">Accesso ad ARCHIVIO PRENOTAZIONI STORICHE (Prenotazioni Archiviate)</h4>
	<?php } ?>
    <h4>Risultati trovati : <span style="font-size:130%; color:red" ><?php echo count($DATI['prenotazioni']); ?></span> </h4>
    <br>
    <p>&nbsp;</p>
    <br>
<form id="formgruppir" action="<?php echo URLADM .'/ricerca.php'?>" method="post">

  <label for="dati" class="control-label">Operazioni possibili su queste <?php echo count($DATI['prenotazioni']); ?> prenotazioni trovate<br></label>
  <div class="form-group">

    <input type="submit" name ="submit" class="btn btn-default" value="TORNA ALLA RICERCA" style="margin-left:30px">
    <input type="submit" name ="submit" class="btn btn-warning" value="RESET RICERCA" style="margin-left:30px">
    <?php if( accessibile('export_excel', $ruolo) ) {?><a href="<?php echo URLADM .'/export.php?EXCEL'?>" target="_blank"><input type="button" name ="submit" class="btn btn-primary" value="ESPORTA IN EXCEL" style="margin-left:30px"></a><?php } ?>
    <?php if( accessibile('export_pdf', $ruolo) ) {?><a href="<?php echo URLADM .'/export.php?PDF'?>" target="_blank"><input type="button" name ="submit" class="btn btn-primary" value="ESPORTA IN PDF" style="margin-left:30px"></a><?php } ?>
	<?php if( accessibile('export_csv', $ruolo) ) {?><a href="<?php echo URLADM .'/export.php?CSV'?>" target="_blank"><input type="button" name ="submit" class="btn btn-primary" value="ESPORTA IN CSV" style="margin-left:30px"></a><?php } ?>
	<?php if( accessibile('export_csvs3', $ruolo) ) {?><a href="<?php echo URLADM .'/export.php?CSVS3'?>" target="_blank"><input type="button" name ="submit" class="btn btn-primary" value="ESPORTA IN CSV-S3" style="margin-left:30px"></a><?php } ?>
	<?php if( $_SESSION['DBprenotazioni']=='prenotazioni' ) { ?>
    	<?php if( accessibile('crea_mail', $ruolo) ) {?><input type="button" name ="submit" class="btn btn-primary" value="INSERISCI TESTO MAIL" style="margin-left:30px"  data-toggle="modal" data-target="#myModal"><?php } ?>
    	<?php if( accessibile('invia_mail', $ruolo) ) {?><input type="submit" name ="submit" class="btn btn-primary" value="INVIA MAIL" style="margin-left:30px" <?php echo ( $_SESSION['ricerca']['testomail']=='' ) ? 'disabled':''?> onclick="return confirm('Attenzione sto per inviare <?php echo count($DATI['prenotazioni']); ?> email !! CONFERMI INVIO ?');"><?php } ?>
    	<?php if( accessibile('invia_mail_prova', $ruolo) ) {?><input type="submit" name ="submit" class="btn btn-primary" value="INVIA MAIL PROVA" style="margin-left:30px" <?php echo ( $_SESSION['ricerca']['testomail']=='' ) ? 'disabled':''?> onclick="return confirm('Attenzione sto per inviare 1 email di prova a : <?php echo $_SESSION['emaillogin']; ?>   CONFERMI INVIO ?');"><?php } ?>
	<?php } ?>
    <?php if( accessibile('cancella_tutti', $ruolo) ) {?><input type="submit" name ="submit" class="btn btn-danger pull-right" value="CANCELLA TUTTI" style="margin-left:30px" onclick="return confirm('Confermi CANCELLAZIONE COMPLETA di queste <?php echo count($DATI['prenotazioni']); ?> prenotazioni in modo irreversibile?');"><?php } ?>
  </div>
</form>

<?php
/*
		echo "<pre>\n";
		echo "POST -> \n";print_r($_POST)."\n";
        echo "\nGET  -> \n";print_r($_GET)."\n";
        echo "\nSESSION  -> \n";print_r($_SESSION['ricerca'])."\n";

        //echo "\nDATI  -> \n";print_r($DATI)."\n";
		echo "</pre>\n";
*/
        tabella_prenotazioni( $DATI['prenotazioni'] );
?>
</div></div>





<?php } ?>






<?php if($_SESSION['statoric'] == 'operativo' ) { //---------------------------------------------------------------------------   RISULTATO OPERATIVO
?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">

   <div class="row" style="margin:20px;"><!-- prima riga -->
      <?php echo navigazione();  ?>
</div>

<div class="row"><div class="col-md-12" >
    <h4>Procedura Gestione Prenotazioni</h4>
    <h4>Pagina risultati operazione</h4>
    <br>
    <h5><?php  echo $risultato_operazione ?></h5>
    <br>
    <p>&nbsp;</p>
    <br>


<form id="formgruppir" action="<?php echo URLADM .'/ricerca.php'?>" method="post">
  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-default" value="TORNA ALLA RICERCA" style="margin-left:30px">
  </div>
</form>

<?php
/*
		echo "<pre>\n";
		echo "POST -> \n";print_r($_POST)."\n";
        echo "\nGET  -> \n";print_r($_GET)."\n";
        echo "\nSESSION  -> \n";print_r($_SESSION['ricerca'])."\n";

        //echo "\nDATI  -> \n";print_r($DATI)."\n";
		echo "</pre>\n";
*/
?>
</div></div>




<?php } ?>





<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 650px;">
	<form id="formmodal" action="<?php echo URLADM .'/ricerca.php'?>" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body" style="height:380px;">
		  	<div class="form-group">
		  		<label for="servizio" class="control-label">Oggetto Mail</label>
		  		<div class="input-group">
		  			<span class="input-group-addon">@</span>
		  			<input type="text" id="oggettomail" name="oggettomail" class="form-control"  value="<?php echo $_SESSION['ricerca']['oggettomail']?>" >
		  		</div>
		  </div>
		  <textarea id="some-textarea" placeholder="Inserisci qua testo mail ..." style="width:600px; height:300px"><?php echo $_SESSION['ricerca']['testomail'] ?></textarea>
		  <input id="camponasc" type="hidden" name="testomail" value="">
      </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi senza salvare</button>
        <button type="button" class="btn btn-primary" onClick='submitDetailsForm()'>Salva</button>
      </div>
  	</form>
    </div>
  </div>
</div>







<script type="text/javascript">
$('#some-textarea').wysihtml5({
	locale: "it-IT",
	  toolbar: {
	    "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
	    "emphasis": true, //Italics, bold, etc. Default true
	    "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
	    "html": false, //Button which allows you to edit the generated HTML. Default false
	    "link": true, //Button to insert a link. Default true
	    "image": true, //Button to insert an image. Default true,
	    "color": false, //Button to change color of font
	    "blockquote": true, //Blockquote
	    "size": 'xs' //default: none, other options are xs, sm, lg
	  }
	});

	function submitDetailsForm() {
			$('#myModal').modal('hide');
			$('#camponasc').val( $('#some-textarea').val() );
	       	$('#formmodal').submit();
	    }

</script>

<script src="<?php echo URLIB ?>/filter/dynamitable_nosort.jquery.js"></script>


</body>
</html>


<?php

function form_ricerca()
{
	global $_SESSION, $DATI;
    // data, ateneo, risorsa, servizio, gruppor cognome matricola mail IDP
	?>
 <h5>Dati ricerca prenotazioni</h5>

<form data-toggle="validator" role="form" action="<?php echo URLADM .'/ricerca.php'?>" method="post">



    <div class="form-group">
      <label for="servizi" class="control-label">Servizi (possibile selezione multipla con CTRL)</label>
      <div class="input-group">
        <span class="input-group-addon">S</span>

  			<select class="form-control" name="selectservizi[]" multiple="multiple">
  			<?php
            foreach( $DATI['servizi'] as $s) printf( '<option value="%s" %s>%s</option>', is($s,'nome'), (  in_array($s['nome'], $_SESSION['ricerca']['servizi'] ) ) ? ' selected="selected"' : '' , is($s,'descrizione')   );
  			?>
  			</select>
      </div>
    </div>


	<div class="form-group">
      <label for="atenei" class="control-label">Atenei (possibile selezione multipla con CTRL)</label>
      <div class="input-group">
        <span class="input-group-addon">A</span>

  			<select class="form-control" name="selectatenei[]" multiple="multiple">
  			<?php
  				foreach( $DATI['atenei'] as $s) printf( '<option value="%s" %s >%s</option>',  $s['ateneo'],   ( in_array( $s['ateneo'], $_SESSION['ricerca']['atenei'] ) ) ? ' selected="selected"' : '' ,     $s['descrizione']   );
  			?>
  			</select>
      </div>
    </div>


	<div class="form-group">
      <label for="gruppir" class="control-label">Indirizzo sportelli del servizio (possibile selezione multipla con CTRL)</label>
      <div class="input-group">
        <span class="input-group-addon">G</span>

  			<select class="form-control" name="selectgruppir[]" multiple="multiple">
  			<?php
  				foreach( $DATI['gruppir'] as $s) printf( '<option value="%s" %s >%s</option>', $s['gruppo_risorsa'], (  in_array($s['gruppo_risorsa'],  $_SESSION['ricerca']['gruppir'] )  ) ? ' selected="selected"' : '' , $s['gruppo_risorsa']." - ".$s['indirizzo']   );
  			?>
  			</select>
      </div>
    </div>




    <div class="form-group">
      <label for="risorse" class="control-label">Risorse (possibile selezione multipla con CTRL)</label>
      <div class="input-group">
        <span class="input-group-addon">R</span>

  			<select class="form-control" name="selectrisorse[]" multiple="multiple">
  			<?php
  				foreach( $DATI['risorse'] as $s ) printf( '<option value="%s||%s" %s >%s</option>', $s['_key'], $s['nome'],     ( isset(  $_SESSION['ricerca']['risorse'][  $s['_key'] ] )  ) ? ' selected="selected"' : '' ,     $s['nome']   );
  			?>
  			</select>
      </div>
    </div>



	    <div class="form-group has-feedback">
	      <label for="data" class="control-label">data prenotazione ( 1 giorno )</label>
	      <div class="input-group">
	        <span class="input-group-addon">D</span>
	        <input type="text" pattern="^\d\d\/\d\d/\d\d\d\d$" maxlength="10" class="form-control" id="data" placeholder="GG/MM/AAAA" data-error="Attenzione al formato data" name="data" value="<?php echo $_SESSION['ricerca']['data']?>">
	      </div>
	      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
	      <div class="help-block with-errors">formato data ok..</div>
	    </div>


<div class="form-inline">
	    <div class="form-group has-feedback" style="margin-right:30px" >
	      <label for="data_da" class="control-label">data prenotazione dal giorno</label>
	      <div class="input-group">
	        <span class="input-group-addon">DD</span>
	        <input type="text" pattern="^\d\d\/\d\d/\d\d\d\d$" maxlength="10" class="form-control" id="data_da" placeholder="GG/MM/AAAA" data-error="Attenzione al formato data" name="data_da" value="<?php echo $_SESSION['ricerca']['data_da']?>">
	      </div>
	      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
	      <div class="help-block with-errors">formato data ok..</div>
	    </div>



	    <div class="form-group has-feedback">
	      <label for="data_a" class="control-label">data prenotazione fino al giorno</label>
	      <div class="input-group">
	        <span class="input-group-addon">DA</span>
	        <input type="text" pattern="^\d\d\/\d\d/\d\d\d\d$" maxlength="10" class="form-control" id="data_a" placeholder="GG/MM/AAAA" data-error="Attenzione al formato data" name="data_a" value="<?php echo $_SESSION['ricerca']['data_a']?>">
	      </div>
	      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
	      <div class="help-block with-errors">formato data ok..</div>
	    </div>
</div>




  <div class="form-group has-feedback">
    <label for="cognome" class="control-label">Cognome</label>
    <div class="input-group">
      <span class="input-group-addon">C</span>
      <input type="text" pattern="^[\sA-Za-z\%]*$" maxlength="30" class="form-control" id="cognome" placeholder="Cognome studente" data-error="Non sono ammessi caratteri strani"  name="cognome" value="<?php echo $_SESSION['ricerca']['cognome']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group has-feedback">
    <label for="matricola" class="control-label">Matricola</label>
    <div class="input-group">
      <span class="input-group-addon">M</span>
      <input type="text" pattern="^[A-Za-z0-9]*$" maxlength="20" class="form-control" id="matricola" placeholder="Matricola precisa.. studente" data-error="Sono ammessi solo numeri o caratteri. No spazi"  name="matricola" value="<?php echo $_SESSION['ricerca']['matricola']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group has-feedback">
    <label for="mail" class="control-label">Email</label>
    <div class="input-group">
      <span class="input-group-addon">@</span>
      <input type="text" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"  maxlength="50" class="form-control" id="mail" placeholder="Email studente" data-error="Formato email non riconosciuto" name="mail" value="<?php echo $_SESSION['ricerca']['mail']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>


  <div class="form-group has-feedback ">
    <label for="cf" class="control-label">Codice Fiscale</label>
    <div class="input-group">
      <span class="input-group-addon">M</span>
      <input type="text" pattern="^[A-Za-z]{6}[a-zA-Z0-9]{2}[a-zA-Z][a-zA-Z0-9]{2}[a-zA-Z][a-zA-Z0-9]{3}[a-zA-Z]$" maxlength="16" class="form-control" id="cf" placeholder="Codice fiscale studente" data-error="Codice fiscale ERRATO" name="cf" value="<?php echo $_SESSION['ricerca']['cf']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>




  <div class="form-group has-feedback">
    <label for="matricola" class="control-label">ID prenotazione 12 numeri</label>
    <div class="input-group">
      <span class="input-group-addon">P</span>
      <input type="text" pattern="^[0-9]*$" maxlength="12" class="form-control" id="IDP" placeholder="ID prenotazione" data-error="Sono ammessi solo numeri"  name="IDP" value="<?php echo $_SESSION['ricerca']['IDP']?>">
    </div>
    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
    <div class="help-block with-errors">ok..</div>
  </div>




  <div class="form-group">
    <input type="submit" name ="submit" class="btn btn-success pull-right" value="<?php echo "RICERCA" ?>">
  </div>
<br><p>&nbsp;</p>

</form>




<?php  }  ?>
