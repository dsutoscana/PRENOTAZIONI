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

if($utente == 'dummy') die("Utente non autorizzato...");

/*
echo "<pre>\n";
echo "POST -> \n";print_r($_POST)."\n";
echo "\nGET  -> \n";print_r($_GET)."\n";
echo "\nSTATO  -> ";print_r($_SESSION['statoric'])."\n";
echo "\nRICERCA  -> \n";print_r($_SESSION['ricerca'])."\n";
//if(isset($_SESSION['disponibilita'])) {echo "disponibilita    ->  ";print_r($_SESSION['disponibilita'])."\n";}
echo "</pre>\n";*/



$AQL=$_SESSION['ricerca']['query'];
$KEYS=$_SESSION['ricerca']['keys'];


$DATI=QUERYDB( URLDB, array( 'AQL'=>json_encode($AQL)  ) );
//echo "<pre>\n"; print_r($DATI); die();


if(isset($_GET['CSVS3']))
{

	$tmp= tempnam("/tmp", "EXPORTCSVRIC").".csv";
	$fh = fopen($tmp, 'w');
	$c=1;
	foreach( $DATI['prenotazioni'] as $k => $P)		fprintf( $fh, "%s;%s;%s\n", $P['matricola'], $P['data'], $c++ );
	fclose($fh);
	$nome="exportS3.csv";
}







if(isset($_GET['CSV']))
{


		$lista=array('_key', 'cognome', 'nome', 'matricola', 'cf', 'ateneo', 'servizio', 'email', 'cellulare', 'gruppir', 'gruppir_ind', 'IDR', 'data', 'slot', 'orada' );

	$tmp= tempnam("/tmp", "EXPORTCSVRIC").".csv";

	$fh = fopen($tmp, 'w');


	foreach( $DATI['prenotazioni'] as $k => $P)
	{
		$c=0;
		$clast=count($lista);
		foreach($lista as $l )
		{
			$c++;
			if( $l == 'IDR' )  {
				$ris= \triagens\ArangoDb\DaiNomeRisorsa( $P[$l] );
				if($ris['ris']) 	fprintf( $fh, "%s", $ris[0]);
				else 				fprintf( $fh, "%s", '');
			}
			else 				fprintf( $fh, "%s", $P[$l] );
			if( $c!=$clast ) fprintf($fh, "; ");
		}
		fprintf( $fh, "\n" );
	}

	fclose($fh);
	$nome="export.csv";

}





if(isset($_GET['EXCEL']))
{

	$tmp= tempnam("/tmp", "EXPORTEXCELRIC").".xls";


	require_once('Spreadsheet/Excel/Writer.php');
	$workbook = new Spreadsheet_Excel_Writer($tmp);
	//$workbook->send($tmp);
	$worksheet = $workbook->addWorksheet('Ricerca');


	$lista=array('_key', 'cognome', 'nome', 'matricola', 'cf', 'ateneo', 'servizio', 'email', 'cellulare', 'gruppir', 'gruppir_ind', 'IDR', 'data', 'slot', 'orada' );

	$worksheet->write(0, 0,  'ID prenotazione');
	$worksheet->write(0, 1,  'cognome');
	$worksheet->write(0, 2,  'nome');
	$worksheet->write(0, 3,  'matricola');
	$worksheet->write(0, 4,  'cod fisc');
	$worksheet->write(0, 5,  'ateneo');
	$worksheet->write(0, 6,  'servizio');
	$worksheet->write(0, 7,  'email');
	$worksheet->write(0, 8,  'cellulare');
	$worksheet->write(0, 9,  'indirizzo');
	$worksheet->write(0, 10,  'indirizzo esteso');
	$worksheet->write(0, 11, 'nome risorsa');
	$worksheet->write(0, 12, 'data');
	$worksheet->write(0, 13, 'slot');
	$worksheet->write(0, 14, 'orada');


	$r=1;
	foreach( $DATI['prenotazioni'] as $k => $P)
	{
		$c=0;
		foreach($lista as $l )
		{
			if( $l == 'IDR' )  {
				$ris= \triagens\ArangoDb\DaiNomeRisorsa( $P[$l] );
				if($ris['ris']) 	$worksheet->writeString($r, $c, $ris[0]);
				else 				$worksheet->writeString($r, $c,  '');
			}
			else 					$worksheet->writeString($r, $c,  "".$P[$l] );
			$c++;
		}
		$r++;
	}


	// We still need to explicitly close the workbook
	$workbook->close();
	$nome="export.xls";
}


if(isset($_GET['PDF']))
{

	$ris=crea_pdf_ExportRicercaPrenotazioni($DATI['prenotazioni'],$AQL);  // gestione del PDF tutta in un file.. per chiarezza... ritorna il file pdf..
	if($ris['ris']) {$tmp=$ris[0]; $nome="export.pdf";}
	else die("Errore PDF: ".$ris[0]);

}



header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$nome);
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($tmp);
unlink ($tmp);


die();
?>
