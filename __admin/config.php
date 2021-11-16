<?php

// echo crypt('password123', 'SCS');
// die();



$filebase="/WWW/prenotazioni.it/website";
$urlbase="http://prenotazioni.it";
$urlbaseadmin="http://prenotazioni.it/__admin";
define('URL', 		$urlbase);
define('URLADM', 	$urlbaseadmin);
define('URLIB', 	$urlbase.'/lib');
define('URLDB', 	$urlbaseadmin.'/web.php');
define('FILELIB', 	$filebase.'/lib');
define('FILEADM', 	$filebase.'/__admin');
define('FILE', 		$filebase);

define('URLWEB', 	$urlbase.'/web.php');



$JS_EXT='';


define( 'PTUTTI', 0);
define( 'POPER', 1);
define( 'PADMIN', 2);
define( 'PSADMIN', 3);

define( 'AVANTI', 'Avanti');
define( 'INDIETRO', 'Indietro');

define('___VERSION__', 5);

$POTERI=array('superadmin'=>3, 'admin'=>2, 'operatore'=>1, 'ruolodummy'=>0);

$SECONDI_GIORNO=86400;

// funzionalità / livello
$permessi['main']=POPER;

$permessi['crud_atenei']=PADMIN;
$permessi['crud_gruppir']=PADMIN;
$permessi['crud_utenti']=PSADMIN;

$permessi['crea_prenotazione']=POPER;
$permessi['disponibilita']=POPER;
$permessi['ricerca_prenotazione']=POPER;
$permessi['ricerca_archivio']=POPER;
$permessi['crud_servizi']=PADMIN;
$permessi['crud_risorse']=PADMIN;
$permessi['vedi_log_oper']=PADMIN;
$permessi['vedi_log_studenti']=POPER;
$permessi['archivia_prenotazioni']=PSADMIN;
$permessi['modifica_password']=POPER;
$permessi['cancella_tutti']=PSADMIN;
$permessi['invia_mail']=PADMIN;
$permessi['invia_mail_prova']=PADMIN;
$permessi['crea_mail']=PADMIN;
$permessi['export_excel']=POPER;
$permessi['export_pdf']=POPER;
$permessi['export_csv']=POPER;
$permessi['export_csvs3']=POPER;
$permessi['riassunto']=PSADMIN;


$permessi['utenti_m']=PSADMIN;
$permessi['atenei_m']=PADMIN;
$permessi['gruppir_m']=PADMIN;
$permessi['risorse_m']=PADMIN;
$permessi['servizi_m']=PADMIN;
$permessi['risorse_r']=POPER;
$permessi['prenotazioni_m']=POPER;
$permessi['prenotazionistoriche_m']=POPER;
$permessi['ALL_m']=POPER;
$permessi['ALL_m']=PTUTTI;
/*
$permessi['']=PTUTTI;
$permessi['']=PTUTTI;
$permessi['']=PTUTTI;
$permessi['']=PTUTTI;
$permessi['']=PTUTTI;*/


function accessibile($funzionalita, $ruolo)
{
	global $permessi, $POTERI;

	if (!isset($permessi[ $funzionalita ]) || ! isset($POTERI[ $ruolo ]) || $POTERI[ $ruolo ] < $permessi[ $funzionalita ] ) return false; else return true;
}

$config = array(

		'LOGFILE' => '_1234872316LOG213849____.txt',
		'CONTATTI' => 'https://www.MY:SITE.it/contattaci/',

		'BASE_DIR' => "/WWW/prenotazioni.it/website",
		'LIB_DIR'  => "/WWW/prenotazioni.it/website/lib",


		'Host' => 'in.smtpok.com',
		'Username' => 's48195_5',        // SMTP username
		'Password' => 'mbPew?gglY',                           //  SMTP password
		'SMTPSecure' => 'tls',                           //   Enable TLS encryption, `ssl` also accepted
		'Port' => 25,
		'setFrom' => 'noreply@MY_AZIENDA.it',
		'setFromNome' => 'Servizio Prenotazione',


);


return $config;

?>
