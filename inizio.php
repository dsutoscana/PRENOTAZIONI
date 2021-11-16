<?php

//https://www.jqueryscript.net/table/jQuery-Plugin-For-Multi-column-Table-Sorting-Filtering-Dynamitable.html
//https://www.jqueryscript.net/table/Data-Table-jQuery-Plugin-Bootstrap-bsTable.html
//http://vitalets.github.io/x-editable/
//https://github.com/spipu/html2pdf
//https://github.com/PHPMailer/PHPMailer



if( !session_start() ) die('problema di sessione...');




$config=require_once('config.php');

require_once('lib.php');
require_once('html.php');
require_once('lib_simple.php');
require_once('lang.php');

$_SESSION['Utente']='dummy';
$_SESSION['Ruolo']='ruolodummy';
$_SESSION['lang']='IT';


$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];


if( isset($_GET['lang']) )
{
    if(  $_GET['lang'] == 'IT'  ) $_SESSION['lang']='IT';
    if(  $_GET['lang'] == 'EN'  ) $_SESSION['lang']='EN';
}
 //$DATI=QUERYDB(URLWEB.'?getall=servizi');
 $ora=time();
 $poi=$ora+$SECONDI_GIORNO;
 // attenzione che se siamo in richiesta disponiblità la matricola non esiste....
 $query=array('servizi', 1, $ora, $poi,   '' , 'NO' );

 $DATI=QUERYDB(URLDB, array( 'AQL'=>json_encode($query)  ));

 //echo "<pre>\n"; print_r($DATI); die();

?>

<!DOCTYPE html>
    <html lang="it">
    <!-- Credits: procedura realizzata da SCS s.a.s. www.esseciesse.net  -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procedura prenotazioni servizi</title>

<!-- Bootstrap-->
        <link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>

</head>
  <body style="background-image: url('bg.jpg');">

  <div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">


<?php intestazionepub(); ?>




  <div class="row" style="margin:30px"><!-- seconda riga -->
<div class="col-md-12">
<h4><?php $s='Elenco dei servizi disponibili per la prenotazione ON LINE'; echo l($s); ?></h4>
<br>


<table class="table table-sm  table-striped">
  <thead>
    <tr>
      <th scope="col" style="padding-bottom:15px"><?php echo l('Servizio')?></th>
      <th scope="col" style="padding-bottom:15px"><?php echo l('Descrizione e note')?></th>
    </tr>
  </thead>
  <tbody>

  <?php  foreach ($DATI['servizi'] as $S) {  echo "<tr><td>" . l(is($S,'descrizione')) . "</td><td>" . l(is($S,'descrizione_ext')) . "</td></tr>";   }?>
  </tbody>
</table>

</div>
  </div><!-- fine seconda riga -->


  <div class="row"><!-- terza riga -->
<div class="col-xs-10" style="margin-bottom:30px">

<a class="btn btn-success pull-right" href="/prenotazioni.php" role="button"><?php echo l('Inizia la procedura');?></a>
</div>
</div>

  <div class="row"><!-- quarta riga DEBUG -->
<div class="col-xs-10" style="margin-bottom:30px">

<!-- <a href="/test_prenotazioni_test.php" >.</a> -->
</div>
</div>

<?php footer();?>
<br>
</div>



    <!-- Includi tutti i plugin compilati (sotto), o includi solo i file individuali necessari -->
    <script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
<!--
    <script src="<?php echo URLIB ?>/filter/dynamitable.jquery.js"></script>
    <script src="<?php echo URLIB ?>/filterT/js/bs-table.js"></script>
    <script src="<?php echo URLIB ?>/moment.min.js"></script>
    <script src="<?php echo URLIB ?>/moment.it.js"></script>
    <script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>
 -->


  </body>
</html>
