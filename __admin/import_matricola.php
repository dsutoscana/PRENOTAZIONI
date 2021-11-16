<?php

//http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.php
//http://1000hz.github.io/bootstrap-validator/
//https://www.abeautifulsite.net/a-simple-php-captcha-script

ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);



require_once('config.php');
require_once('lib.php');
require_once('lib_simple.php');
require_once('html.php');

require_once( FILELIB."/captcha/simple-php-captcha.php" );


session_start();

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}

$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];
if($utente=='dummy') die('utente non autorizzato');




if(  isset($_GET['VIEW']) )  /// visualizzazione matricole --------------------------------------------------------------
{
	$serv=$_GET['VIEW'];

	$DATI=QUERYDB(URLDB.'?get_all_crud='.json_encode(array('servizi'))."&example=".json_encode(array( '_key' => $serv )) );
	//echo "<pre>\n"; print_r($DATI); die();
	if( count( $DATI['servizi'] ) ==  0 )
	{
		echo "<pre>\n\n\nATTENZIONE ... NON TROVATO SERVIZIO con ID  :  ".$serv;
		echo "\n\n\n\nsituazione molto strana... contattare supporto informatico..\n\n\n";
		die('procedura stoppata con attenzione');

	}
	$servizio=$DATI['servizi'][0]['descrizione'];




	$query=array('matricole', 6,  $serv  );

	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );
	//echo "<pre>\n"; print_r($DATI); die();

	if( count( $DATI['matricole'] ) ==  0 )
	{
		echo "<pre>\n\n\nATTENZIONE ... NON TROVATE MATRICOLE ASSOCIATE SERVIZIO con ID  :  ".$serv;
		echo "\n\n\n\nSe non sono state caricate matricole per questo servizio... caricarle.\n\n\n";

		echo "\n\n\n\nSe sono state caricate le matricole contattare supporto informatico..\n\n\n";
		die('procedura arrestata');

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

	<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
	</head>


	<body>
	<div class="container-fluid" style="margin:15px">
	<div class="row" style="margin:20px;"><!-- prima riga -->

	</div>

	<div class="row"><div class="col-xs-12 col-md-offset-3 col-md-5" >

	 <h2>Prenotazioni</h2>
	 <br>
	 <h3>Procedura Import Matricole - Visualizzazione matricole</h3>
	 <br>
	 <h4>Servizio : <?php echo $servizio ?></h4>
	 <br>
	 <pre>
<?php

	$i=0;
	foreach( $DATI['matricole'] as $v )
	{

		if( $i % 5 == 0 ) echo "\n";
		echo "$v     ";
		$i++;
	}
	?>
	</pre>
	</div>
	</body>
	</html>
	<?php
	die(); // siamo in una finestra blanck.. deve finire cosi'...
} /// -------------------------------------------------------------------















if( isset($_GET['ID']) )  $_SESSION['ID_Servizio_import_matricola']=$_GET['ID'];

if( ! isset($_POST['submit'])  )
{

        $DATI=QUERYDB(URLDB.'?get_all_crud='.json_encode(array('servizi'))."&example=".json_encode(array( '_key' => $_SESSION['ID_Servizio_import_matricola'] )) );
        //echo "<pre>\n"; print_r($DATI); die();
        if( count( $DATI['servizi'] ) ==  0 )
        {
            echo "<pre>\n\n\nATTENZIONE ... NON TROVATO SERVIZIO con ID  :  ".$_SESSION['ID_Servizio_import_matricola'];
            echo "\n\n\n\nsituazione molto strana... contattare supporto informatico..\n\n\n";
            die('procedura stoppata con attenzione');

        }
        $_SESSION['ID_Servizio_import_matricola_desc']=$DATI['servizi'][0]['descrizione'];


?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestione tabelle</title>
    	<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      	<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

		<link rel="stylesheet" href="<?php echo URLADM ?>/stile.css">
  		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
    	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    	<script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>
    	<script src="<?php echo URLIB ?>/validator/validator.min.js"></script>

<style>
.help-block {     color: white; }
</style>


</head>


<body>
 <div class="container-fluid" style="margin:15px">
   <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php echo navigazione(); ?>
</div>
<div class="row"><div class="col-xs-12 col-md-offset-3 col-md-5" >

 <h3>Procedura Import Matricole -  Prenotazioni</h3>

 <h3>Servizio : <?php echo $_SESSION['ID_Servizio_import_matricola_desc']  ?></h3>
 <div style="margin:30px;padding:30px">
 <br>
 <h4>Carica file di testo nel formato </h4>
 <h4>matricola;matricola;matricola;..... </h4>
 <h4>matricola e' fatta da numeri o caratteri, al massimo 11 char </h4>
 <br>
 <h4 style="color:red">ATTENZIONE ... </h4>
 <h4>La procedura di caricamento cancella tutte le precedenti matricole ASSOCIATE a questo servizio (<?php echo $_SESSION['ID_Servizio_import_matricola_desc']  ?>).</h4>
 <br>
 <h4>Viene inoltre attivata una procedura di pulizia nel DB di tutte le matricole NON associate ad alcun servizio.</h4>
</div>

<form id="formgruppir" action="<?php echo URLADM .'/import_matricola.php'?>" method="post" enctype="multipart/form-data">

<div class="form-group">
  <label for="exampleInputFile">File upload delle matricole</label>
  <input type="file" id="InputFile" name ="InputFile">
  <p class="help-block">Inserisci qua il file delle matricole.</p>
</div>


<div class="form-group">
 <input type="submit" name ="submit" class="btn btn-success pull-right" value="CARICA FILE">
</div>
</form>




</div></div>




</body>
</html>
<?php

}
else
{

//print_r($_FILES);die();
/*Array
(
    [InputFile] => Array
        (
            [name] => matricole
            [type] => application/octet-stream
            [tmp_name] => /tmp/phpbGgvSK
            [error] => 0
            [size] => 2379
        )

)

key --> $_SESSION['ID_Servizio_import_matricola']___matricola
*/

try {

    $file=file_get_contents($_FILES['InputFile']['tmp_name']);
    unlink($_FILES['InputFile']['tmp_name']);

    $newString1 = preg_replace('/\n/i', ';', $file);
    $newString = preg_replace('/[^A-Za-z0-9;]/i', '', $newString1);

    $arr=explode(';', $newString);
    //$nummat=count($arr);

    // pulizia
    $DATI=QUERYDB( URLDB.'?get_all_crud='.json_encode(array('servizi')) );
    $SRV=array();
    foreach($DATI['servizi'] as $d) $SRV[]=$d['_key'].'___';

    // inserimento
    $nummat=\triagens\ArangoDb\InserisciMatricole($_SESSION['ID_Servizio_import_matricola'], $arr, $SRV);


?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestione tabelle</title>
        	<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
          	<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

    		<link rel="stylesheet" href="<?php echo URLADM ?>/stile.css">
      		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
        	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    </head>


    <body>
     <div class="container-fluid" style="margin:15px">
       <div class="row" style="margin:20px;"><!-- prima riga -->

          <?php echo navigazione(); ?>
    </div>
    <div class="row"><div class="col-xs-12 col-md-offset-3 col-md-5" >

     <h3>Procedura Import Matricole -  Prenotazioni</h3>

     <h3>Servizio : <?php echo $_SESSION['ID_Servizio_import_matricola_desc']  ?></h3>
     <div style="margin:30px;padding:30px">
     <br>
     <h4>Caricate <?php echo $nummat ?> matricole con successo </h4>
     <br>
     <h4><a href="<?php echo URLADM .'/crud.php#miotab_A3'?>">Puoi tornare qua al menu' dei servizi.</a></h4>
     <br>
    </div>
    </div></div>

    </body>
    </html>
<?php


}
catch (Exception $e) {     echo '<pre>Evidenziato un problema import matricole.. : ',  $e->getMessage(), "\n\n\nRiprovare";}

}
