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




session_start();

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}

$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];



/*
echo "<pre>\n";
echo "POST -> \n";print_r($_POST)."\n";
echo "\nGET  -> \n";print_r($_GET)."\n";
echo "\nSTATO  -> ";print_r($_SESSION['statoric'])."\n";
echo "\nRICERCA  -> \n";print_r($_SESSION['ricerca'])."\n";
//if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
echo "</pre>\n";*/

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


		<link rel="stylesheet" href="<?php echo URLADM ?>/stile.css">
  		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
    	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    	<script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>
    	<script src="<?php echo URLIB ?>/validator/validator.min.js"></script>

        <script type="text/javascript">
        function closeWin() { window.top.close(); }
        </script>


        <style>
        input::-webkit-outer-spin-button,input::-webkit-inner-spin-button {    -webkit-appearance: none;    margin: 0; }
        			input[type='number'] {  -moz-appearance:textfield;}
        			input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; }
        .help-block { color: white; }
        </style>



</head>


<?php
if(isset($_GET['vedilogoper']) || isset($_GET['vediloguser'])) { //---------------------------------------------------------------------------   RISULTATO OPERATIVO
    $testo= (isset($_GET['vedilogoper']) ) ? "Visualizzazione LOG operatori" : "Visualizzazione LOG Studenti" ;
?>

<body style="background-image: url('bg.jpg');">
<div class="container-fluid" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


<div class="row"><div class="col-md-12" >
<br>
<h3><?php echo $testo; ?></h3>
<br>
<pre>

<?php
    if( isset($_GET['vedilogoper']) )   $file = FILEADM."/".$config['LOGFILE'];
    else                                $file = FILE."/".$config['LOGFILE'];
    //echo file_get_contents( $file );

system("tac $file");
    ?>

</pre>
<br>
<br>
<p>&nbsp;</p>
<button type="button" class="btn btn-success  pull-right" onclick="closeWin();">Chiudi</button>
<br>
<br>
<p>&nbsp;</p>

</div></div>
<?php } ?>





<?php
if(isset($_GET['EXEarchiviastorico']) ) { //---------------------------------------------------------------------------   RISULTATO OPERATIVO
    $testo= "Esecuzione manuale archivazione prenotazioni nel DB storico";

    $ris=\triagens\ArangoDb\SpostaPrenotazioneInSotrico();
    if( $ris['ris'] )       $testoris="Spostamento avvenuto con successo !";
    else                    $testoris="Errore nello spostamento delle prenotazioni nel DB storico : Messasggio di errore : !". $ris[0];


    logattivita("Procedura manuale sposta storico : ".$testoris);


?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


<div class="row"><div class="col-md-12" >
<br>
<h3><?php echo $testo; ?></h3>
<br>

<h4><?php echo $testoris; ?></h4>
<br>
<br>
<p>&nbsp;</p>
<button type="button" class="btn btn-success  pull-right" onclick="closeWin();">Chiudi</button>
<br>
<br>
<p>&nbsp;</p>

</div></div>
<?php } ?>





<?php
if(isset($_GET['InviaPassword']) ) { //---------------------------------------------------------------------------   RISULTATO OPERATIVO

	$testo= "Generazione e invio password ad un utente";


	$PWD=randomPassword();

	$q=sprintf('?mod_crud&tab=utenti&ide=%s&campo=%s&valore=%s', $_GET['key'], 'password', $PWD);

	$DATI=QUERYDB(URLDB.$q);

	$ris=\triagens\ArangoDb\InviaMail('', $_GET['email'], 'password', array('nome'=>$_GET['utente'], 'login'=>$_GET['login'], 'password'=>$PWD )  ) ;

	if( $ris['ris'] )       $testoris="Generazione e invio avvenuto con successo !";
	else                    $testoris="Errore nella generazione e invio password a utente : Messasggio di errore : !". $ris[0];


    logattivita("Creazione password e invio mail a utente ".$_GET['utente']."  ".$_GET['login']."  :   ".$testoris);


?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


<div class="row"><div class="col-md-12" >
<br>
<h3><?php echo $testo; ?></h3>
<br>

<h4><?php echo $testoris; ?></h4>
<br>
<br>
<p>&nbsp;</p>
<button type="button" class="btn btn-success  pull-right" onclick="closeWin();">Chiudi</button>
<br>
<br>
<p>&nbsp;</p>

</div></div>
<?php } ?>




<?php
if(isset($_GET['modificaPassword']) ) { //---------------------------------------------------------------------------   RICHIEDI CAMBIO PASSWORD

if($utente == 'dummy') die("Utente non autorizzato...");

?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


<div class="row"><div class="col-md-offset-3 col-md-4" >
    <br>
    <h2>Gestione Prenotazioni Servizi</h2>
    <br>

    <h4>Procedura di cambio password</h4>

    <br>

    <?php echo "Login : $utente<br><br>"; ?>

    <form role="form" action="<?php echo URLADM .'/varie.php'?>" method="post">


        <div class="form-group">
          <label for="pold" class="control-label">Inserisci password attuale</label>
          <div class="input-group">
            <span class="input-group-addon">Pv</span>
            <input type="password" class="form-control" id="pold" placeholder="Vecchia password" name="pold" >
          </div>
          <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        </div>


        <div class="form-group">
          <label for="pn1" class="control-label">Inserisci nuova password</label>
          <div class="input-group">
            <span class="input-group-addon">Pn 1</span>
            <input type="password"  class="form-control" id="pn1" placeholder="Nuova Password" name="pn1" >
          </div>
          <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        </div>


        <div class="form-group">
          <label for="pn2" class="control-label">Inserisci nuovamente la nuova password</label>
          <div class="input-group">
            <span class="input-group-addon">Pn 2</span>
            <input type="password" class="form-control" id="pn2" placeholder="Inserisci nuovamente la password" name="pn2" >
          </div>
          <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        </div>


      <div class="form-group">
        <input type="submit" name ="submit" class="btn btn-success pull-right" value="CAMBIA PASSWORD">
      </div>
    <br><p>&nbsp;</p>

    </form>


</div></div>
<?php }   // esegue il pezzo sotto....................................
?>



<?php
if(isset($_POST['submit'])  && $_POST['submit']=='CAMBIA PASSWORD' ) { //-----------------------------------------------------------------  ESEGUI CAMBIO PASSWORD

	$testo= "Cambio password per login : ".$utente;


    if( $utente == 'superadmin' )               $testoris="Cambio password per utente superadmin non ammessa... !";
    elseif( $_POST['pn1'] != $_POST['pn2'] )    $testoris="Le due nuove password non sono uguali....!!!!";
    else
    {
        $validated = \triagens\ArangoDb\ValidateUser($utente, $_POST['pold']);
        if( $validated['ris']!==true ) $testoris="Password vecchia NON corretta !!!!";
        else
        {
            $q=sprintf('?mod_crud&tab=utenti&ide=%s&campo=password&valore=%s', $_SESSION['KEYlogin'],  $_POST['pn1']);
        	$DATI=QUERYDB(URLDB.$q);

            $testoris="Password modificata con successo";

        }
    }

    logattivita("Modifica password  utente ".$utente."   :   ".$testoris);


?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">

<div class="row"><div class="col-md-offset-3 col-md-6" >
<br>
<h3><?php echo $testo; ?></h3>
<br>
<h4><?php echo $testoris; ?></h4>
<br>
<br>
<p>&nbsp;</p>
<button type="button" class="btn btn-success  pull-right" onclick="closeWin();">Chiudi</button>
<br>
<br>
<p>&nbsp;</p>
</div></div>
<?php } ?>









</body>
</html>



<?php

function randomPassword()
{
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 6; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

?>
