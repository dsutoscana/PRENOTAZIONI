<?php
namespace triagens\ArangoDb;

$config=require_once('config.php');
require_once('lib.php');
require_once('html.php');
require_once('lang.php');
require_once('lib_simple.php');


require  $config['LIB_DIR']. '/arango/vendor/autoload.php';

	$connectionOptions =array(
			ConnectionOptions::OPTION_ENDPOINT => 'tcp://127.0.0.1:8529',
			ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
			ConnectionOptions::OPTION_DATABASE => 'prenotazioni',
			ConnectionOptions::OPTION_AUTH_USER => 'username',
			ConnectionOptions::OPTION_AUTH_PASSWD => 'password',
			ConnectionOptions::OPTION_CONNECTION => 'Close',
			ConnectionOptions::OPTION_TIMEOUT => 3,
			ConnectionOptions::OPTION_RECONNECT => true,
			ConnectionOptions::OPTION_CREATE => true,
			ConnectionOptions::OPTION_UPDATE_POLICY => UpdatePolicy::LAST,
	);

	try {

			$attivita='generico';

			$connection = new Connection($connectionOptions);

			$collectionHandler = new CollectionHandler($connection);
			$documentHandler = new DocumentHandler($connection);

			if( isset($_GET['del'])  )
			{

				$errore='';
				$TAB='prenotazioni';

				$_key=Decrypta($_GET['ID']);

				if( ! is_numeric($_key) ) $errore='Codice cancellazione non riconosciuto';


				$document = $documentHandler->getById( $TAB, $_key);
				$P=$document->getAll();

				$nomeserv = $P['servizio'];

				$cursor = $collectionHandler->byExample( 'servizi', array( 'nome'=>$nomeserv ) );
				$rud = $cursor->getAll();
				$S = $rud[0]->getAll();
				$ore=$S['ore_canc'];

				//sdie($S);

				$now=time();
				$preno=convdata($P['data']);

				$mag48ore = ( $preno - $now > 3600 * $ore ) ? true : false ;
				if($ore == 0 )  $mag48ore=true;


				if($mag48ore)
				{
					$documentHandler->removeById($TAB, $_key);
					logattivita("procedura Cancella prenotazione invocata da remoto. ID: ".$_key);
				}
				else logattivita("procedura Cancella prenotazione invocata da remoto. ID: ".$_key. " Cancellazione non effettuata per <troppo tardi>. ora : ".$now."  - prenotazione : ".$preno );
			}

		}
		catch (ConnectException $e) { $errore=$errore."  ".$e->getMessage();	}
		catch (ServerException $e)  { $errore=$errore."  ".$e->getMessage();	}
		catch (ClientException $e)  { $errore=$errore."  ".$e->getMessage(); }


		?>
<!DOCTYPE html>
    <html lang="it">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procedura prenotazioni servizi</title>
        <link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
</head>
  <body style="background-image: url('bg.jpg');">

  <div class="container" style="background-color: #fff;box-shadow: 0px 0px 10px #000;">
<?php intestazionepub('NO'); ?>
  <div class="row" style="margin:30px"><!-- seconda riga -->
<div class="col-xs-12">
<h4>Procedura cancellazione prenotazione con ID : <?php echo $_key ?></h4>
<?php

	if( $errore == '' )
	{

		if( $mag48ore )
		{
			$ris=crea_pdf_cancellazione( $P );

			if( $ris['ris'] )
			{
				$risM=InviaMail($ris[0], $P['email'], 'cancellazione');
				unlink ($ris[0]);
			}
		}


		if( $mag48ore )	{ ?>
		<h4 style="color:green">Avvenuta con successo.</h4>
		<h3 style="color:green">E' stata inviata mail di conferma all'indirizzo scelto nella prenotazione.</h3>

		<?php } else {

			$htmlbody="<h3>Ci dispiace ma non è possibile cancellare la prenotazione nelle ".$ore." ore precedenti l'appuntamento.</h3>";
			if( $P['stampa_delega'] == 'SI') $htmlbody .= "<h3>Si ricorda che è possibile usufruire del servizio delegando altra persona<h3>";
			$htmlbody .= "<h2>Per ulteriori informazioni può rivolgersi al Settore di interesse (".$P['tipo_servizio'].") i cui riferimenti sono sul sito aziendale <a href='http://www.MY_SITE.it' >www.MY_SITE.it</a></h2>";

			$body="\n\nCi dispiace ma non è possibile cancellare la prenotazione nelle ".$ore." ore precedenti l'appuntamento.\n\n";
			if( $P['stampa_delega'] == 'SI') $body .= "Si ricorda che è possibile usufruire del servizio delegando altra persona.\n\n";
			$body .= "\n\nPer ulteriori informazioni può rivolgersi al Settore di interesse (".$P['tipo_servizio'].") i cui riferimenti sono sul sito aziendale <a href='http://www.MY_SITE.it' >www.MY_SITE.it</a>\n\n\n";

			$risM=InviaMail($ris[0], $P['email'], 'NOcancellazione48ore', array('body'=>$body, 'htmlbody'=>$htmlbody  ));

			?>
		<h4 style="color:green">Cancellazione NON avvenuta. Cancellazione possibile solo entro <?php echo $ore ?> ore dalla prenotazione.</h4>
		<h3 style="color:green">E' stata inviata mail di NON avvenuta cancellazione all'indirizzo scelto nella prenotazione.</h3>
		<?php if( $P['stampa_delega'] == 'SI') { ?>
		<h3>Si ricorda che è possibile usufruire del servizio delegando altra persona, come spiegato nella mail di prenotazione.</h3>
		<?php }?>

		<h3>Per ulteriori informazioni può rivolgersi al Settore di interesse (<?= $P['tipo_servizio'] ?> ) i cui riferimenti sono sul sito aziendale <a href="http://www.MY_SITE.it">www.MY_SITE.it</a></h3>

	<?php }
	}
	else
	{	?>
	     <h4>Spiacenti, si e' presentato un errore nella procedura di cancellazione.</h4>
		<h3>Messagio di errore : <?php echo $errore; ?></h3>
		<h3>Riprovare piu' tardi o contattare il supporto per mezzo del pulsante "Contatti"</h3>
		<?php
	}



?>
</div>
  </div><!-- fine seconda riga -->
  </div>
    <script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>
