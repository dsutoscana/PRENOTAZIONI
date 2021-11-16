<?php

//https://www.jqueryscript.net/table/jQuery-Plugin-For-Multi-column-Table-Sorting-Filtering-Dynamitable.html
//https://www.jqueryscript.net/table/Data-Table-jQuery-Plugin-Bootstrap-bsTable.html
//http://vitalets.github.io/x-editable/
//https://github.com/spipu/html2pdf
//https://github.com/PHPMailer/PHPMailer


ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);



session_start();


$config=require_once('config.php');

require_once('lib.php');
require_once('html.php');
require_once('lib_simple.php');



//$r=\triagens\ArangoDb\GetCollection(null, 'utenti');
//print_r($r);
//die('finito');

if( !isset ( $_SESSION['Utente'] ) )  // non sono autenticato..
{
	if( $_SERVER["REQUEST_METHOD"] != "POST" )      {         login();         die();         }
	else
	{
		if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password']) )
		{
			$myusername=$_POST['username'];
			$mypassword=$_POST['password'];

			$validated = \triagens\ArangoDb\ValidateUser($myusername, $mypassword);
			if(isset( $validated['ris'] ) && $validated['ris']===true) { $_SESSION['Utente']=$myusername; $_SESSION['Ruolo']=$validated['ruolo'];$_SESSION['KEYlogin']=$validated['KEYlogin'];$_SESSION['emaillogin']=$validated['email']; logattivita("Nuova sessione: $myusername");}  // setto la sessione e vado oltre....
			else {  $error="Your Login Name or Password is invalid"; logattivita("Errore collegamento utente:  $myusername  password:  $mypassword"); die($error); }
		}
	}
}
elseif ( isset ( $_SESSION['Utente'] ) && $_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['quit']) ) {	session_destroy();  $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; header("Location: ".$actual_link);  die();  }  // sono collegato.. ho cliccato quit..



$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];

if($utente=='dummy') {session_destroy();  die('utente non autorizzato');}



	$query=array('all', 8 );
	$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($query)  ) );
	//echo "<pre>\n"; print_r($DATI); die();

?>

<?php echo intestazione(); ?>

<body style="background-image: url('bg.jpg');">
<div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">



 <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php echo navigazione(); ?>
</div>


  <div class="row"><!-- seconda riga -->
<div class="col-md-12">
<?php

		$D=$DATI['all'][0];
		echo "<pre>\n";
		echo "Paramtri generali informativi\n\n";

		echo "Utente collegato\n\n";

		printf( "Login                         : %s\n", $utente);
		printf( "Gruppo di appartenenza        : %s\n\n", $ruolo);


		echo "Oggetti presenti nel sistema\n\n";

		printf( "Atenei                        : %s\n", $D['atenei']);
		printf( "Gruppi di risorse (Indirizzi) : %s\n", $D['gruppir']);
		printf( "Risorse                       : %s\n", $D['risorse']);
		printf( "Servizi                       : %s\n", $D['servizi']);
		printf( "PRENOTAZIONI                  : %s\n", $D['prenotazioni']);
		printf( "PRENOTAZIONI Arch. Storico    : %s\n", $D['prenotazionistoriche']);
		printf( "Matricole                     : %s\n", $D['matricole']);
		printf( "Email in coda                 : %s\n", $D['email']);

if( accessibile('riassunto', $ruolo) )
{
		echo "Tabella permessi\n\n";
		echo "           RUOLO -->       Studente    Operatore    Admin  SuperAdmin\n";
		echo "PROCEDURA\n";
		echo "   |\n";
		echo "   v\n";
		echo "Cancellazione Totale                                            X\n";
		echo "Visuliazzazione Permessi                                        X\n";
		echo "CRUD Operatori                                                  X\n";
		echo "CRUD Atenei                                           X         X\n";
		echo "CRUD Servizi                                          X         X\n";
		echo "CRUD Gruppi Ris.                                      X         X\n";
		echo "CRUD Risorse                                          X         X\n";
		echo "Vedi Log Operatore                                    X         X\n";
		echo "Creazione Email                                       X         X\n";
		echo "Invio Email                                           X         X\n";
		echo "Invio Email Test                                      X         X\n";
		echo "Creazione prenotazione          X          X          X         X\n";
		echo "Disponibilita' prenotazione                X          X         X\n";
		echo "Ricerca prenotazione                       X          X         X\n";
		echo "Ricerca Archivio Storico                   X          X         X\n";
		echo "Vedi Log Studente                          X          X         X\n";
		echo "Export EXCEl                               X          X         X\n";
		echo "Expor PDF                                  X          X         X\n";
		echo "Export CSV                                 X          X         X\n";
		echo "Export CSV-S3                              X          X         X\n";
		echo "Cambio Password                            X          X         X\n";
		echo "Pagina Main                                X          X         X\n";
		echo "Esci                                       X          X         X\n";


}


?>


</div>
  </div><!-- fine seconda riga -->


  <div class="row"><!-- terza riga -->
<div class="col-md-12">

<?php



?>

</div>
</div>




    <!-- Includi tutti i plugin compilati (sotto), o includi solo i file individuali necessari -->
    <script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo URLIB ?>/filter/dynamitable.jquery.js"></script>
    <script src="<?php echo URLIB ?>/filterT/js/bs-table.js"></script>
    <script src="<?php echo URLIB ?>/moment.min.js"></script>
    <script src="<?php echo URLIB ?>/moment.it.js"></script>
    <script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>



  </body>
</html>


<?php
function login()
{
	?>
	<!DOCTYPE html>
<html lang="it">
<head><meta charset="utf-8"></head>
	<body>
<br><br>
<div align="center">
<div style="width:300px; border: solid 1px #333333; " align="left">
<div style="background-color:#333333; color:#FFFFFF; padding:3px;"><b>Login Admin Prenotazioni</b></div>
<div style="margin:30px">
<form action="" method="post">
<label>UserName  :</label><input type="text" name="username" class="box"/><br /><br />
<label>Password  :</label><input type="password" name="password" class="box" /><br/><br />
<input type="submit" value=" Submit "/><br />
</form>
</div>
</div>
</div></body></html>
<?php
//phpinfo();
        }
?>
