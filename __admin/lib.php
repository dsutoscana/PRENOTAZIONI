<?php
namespace triagens\ArangoDb;
require  $config['LIB_DIR']. '/arango/vendor/autoload.php';

require_once FILELIB.'/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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



function ValidateUser($user, $password)
{
	global $connectionOptions;

	$connection = new Connection($connectionOptions);

	$collectionHandler = new CollectionHandler($connection);


	$collectionName='utenti';
	if ( ! $collectionHandler->has($collectionName)) return array('ris'=>false);

	$passwordc=crypt($password, 'SCS');
	$cursor = $collectionHandler->byExample('utenti', ['login' => $user, 'password' => $passwordc]);

	$r=$cursor->getCount();

	if( $r == 1 )
	{
		$rud = $cursor->getAll();
		$ruo = $rud[0]->get('ruolo');
		$all = $rud[0]->getAll();
		$key=$all['_key'];  // mi serve solo per il cambio della password..
		$email=$all['email'];  // mi serve solo per il cambio della password..


		return array( 'ris'=>true, 'ruolo'=>$ruo, 'KEYlogin'=>$key, 'email'=>$email );
	}
	else return array('ris'=>false);
}



function GetCollection($collectionHandler=null, $collectionName, $filtro=array())
{
	global $connectionOptions;

	if( $collectionHandler == null )
	{
		$connection = new Connection($connectionOptions);
		$collectionHandler = new CollectionHandler($connection);
	}
	try
	{
			try { 	$collectionID = $collectionHandler->getCollectionName($collectionName);	} catch (\Exception $e) { return array( 'ris'=>false, "Non trovata collezione ".$collectionName ); }

			$cursor = $collectionHandler->byExample($collectionName, $filtro);
			$rud = $cursor->getAll();
			$risu=array();
			foreach ($rud as $d)
			{
				$risu[]=$d->getAll();
			}
			return $risu;

	}
	 catch (ConnectException $e) { return array( 'ris'=>false, "problema inserimento: connessione :".$e->getMessage() ); 	}
	 catch (ServerException $e) {  return array( 'ris'=>false, "problema inserimento: server :".$e->getMessage() );	}
	 catch (ClientException $e) {  return array( 'ris'=>false, "problema inserimento: client :".$e->getMessage() );	}

}


function InsertUpdateCampo($documentHandler=null, $collectionName, $keyDoc, $cosa=array(), $option=null)  // se key e' nullo lo crea
{
	global $connectionOptions;

	if( $documentHandler == null )
	{
		$connection = new Connection($connectionOptions);
		$documentHandler = new DocumentHandler($connection);
	}
	try
	{
			if( $keyDoc == null  )
			{
				$document = new Document();
				foreach($cosa as $k => $v) 	$document->set($k, $v);
				$documentId = $documentHandler->save($collectionName, $document);
			}
			else
			{
				try { 	$document = $collectionHandler->getById( $collectionName, $keyDoc);	} catch (\Exception $e) { return array( 'ris'=>false, "Non trovato documento ".$collectionName." ".$keyDoc ); }
				foreach($cosa as $k => $v) 	$document->set($k, $v);
				$documentHandler->update( $document );
			}
			return array( 'ris'=>true, "Update OK" );

	}
	 catch (ConnectException $e) { return array( 'ris'=>false, "problema aggiornamento doc: connessione :".$e->getMessage() ); 	}
	 catch (ServerException $e) {  return array( 'ris'=>false, "problema aggiornamento doc: server :".$e->getMessage() );	}
	 catch (ClientException $e) {  return array( 'ris'=>false, "problema aggiornamento doc: client :".$e->getMessage() );	}


}


function InserisciMail($oggetto, $testo, $arK)
{
	global $connectionOptions;

	if($testo == '') return array( 'ris'=>false, "testo mail non presente" );

	$connection = new Connection($connectionOptions);
	$documentHandler = new DocumentHandler($connection);

	try
	{

		if(!is_array($arK) )
		{
				$document = new Document();
				$document->set('email', $arK);
				$document->set('testo', $testo);
				$document->set('oggetto', $oggetto);
				$documentId = $documentHandler->save('email', $document);

				return array( 'ris'=>true, "OK Inviata 1 mail" );

		}
		else
		{
			foreach($arK as $k)
			{
				$preno=$documentHandler->getById('prenotazioni', $k);
				$email=$preno->get('email');

				$document = new Document();
				$document->set('email', $email);
				$document->set('testo', $testo);
				$document->set('oggetto', $oggetto);
				$documentId = $documentHandler->save('email', $document);

			}
			return array( 'ris'=>true, "".count($arK) );
		}

	}
	catch (ConnectException $e) { return array( 'ris'=>false, "problema inserimento mail : connessione :".$e->getMessage() ); 	}
	catch (ServerException $e) {  return array( 'ris'=>false, "problema inserimento mail : server :".$e->getMessage() );	}
	catch (ClientException $e) {  return array( 'ris'=>false, "problema inserimento mail : client :".$e->getMessage() );	}

}




function GetFirstMail()
{
	global $connectionOptions;

	$connection = new Connection($connectionOptions);

	try
	{
			$query="for i in email limit 1 return i";
			$statement = new Statement(
					$connection,
					array(
							"query"     => $query,
							"count"     => true,
							"batchSize" => 1000,
							"sanitize"  => true
					)	);


			$cursor = $statement->execute();

			$rud = $cursor->getAll();

			if( isset($rud[0]) && is_object ($rud[0]) ) return array( 'ris'=>true, $rud[0]->getAll() );
			else 										return array( 'ris'=>false, 0 );

	}
	catch (ConnectException $e) { return array( 'ris'=>false, "problema invio mail : connessione :".$e->getMessage() ); 	}
	catch (ServerException $e) {  return array( 'ris'=>false, "problema invio mail : server :".$e->getMessage() );	}
	catch (ClientException $e) {  return array( 'ris'=>false, "problema invio mail : client :".$e->getMessage() );	}
}


function DeleMail($_key)
{
	global $connectionOptions;

	$connection = new Connection($connectionOptions);
	$documentHandler = new DocumentHandler($connection);
	$documentHandler->removeById('email', $_key);

}




function InserisciMatricole($KEY, $MAT, $SRV)
{
	global $connectionOptions;
	$connection = new Connection($connectionOptions);


	if( count($SRV) > 0 )
	{
		$lista="'".implode ( "','" , $SRV )."'";

		//print_r($SRV);die();


		$querydelNOT="FOR m IN matricole filter SUBSTRING(m._key, 0, FIND_FIRST(m._key, '___') +3)  NOT IN  [" . $lista . "] REMOVE { _key: m._key } IN matricole";
		//print_r($querydelNOT);die();
		$statement = new Statement( $connection,
								array(					"query"     => $querydelNOT,
														"count"     => true,
														"batchSize" => 1000,
														"sanitize"  => true					)		);
		$cursor = $statement->execute();
	}


	$querydel=  'FOR m IN matricole filter m._key LIKE CONCAT("'.$KEY.'", "___%")  REMOVE { _key: m._key } IN matricole';
	$statement = new Statement( $connection,
								array(					"query"     => $querydel,
														"count"     => true,
														"batchSize" => 1000,
														"sanitize"  => true					)		);
	$cursor = $statement->execute();


		$ris=array();
		$connection = new Connection($connectionOptions);

		$collectionHandler = new CollectionHandler($connection);
		$documentHandler = new DocumentHandler($connection);

		$c=0;
		foreach($MAT as $m)
		{
			if($m =='' ) continue;
			$document = new Document();
			$document->set('_key', $KEY.'___'.$m);
			$documentId = $documentHandler->save('matricole', $document);
			$c++;
		}

		return $c;


}



function SpostaPrenotazioneInSotrico()
{
/*
	$oggi = date('Ymd');
	$ieri= $oggi-1;

	$q="let dataora = ".$ieri;
	$q .="for p in prenotazioni
			let datadoc = concat( SUBSTRING(p.data, 6, 4),  SUBSTRING(p.data, 3, 2), SUBSTRING(p.data, 0, 2)  )
			filter datadoc <= dataora
			insert p in prenotazionistoriche
			remove p in prenotazioni";
*/
	try {
		global $connectionOptions;
		$connection = new Connection($connectionOptions);


		$ieri = date('Ymd', time()-86400 );  // attenzione giorno in UTC.. se vicino alla mezzanotte puo' dare il giorno sbagliato....


		$query="let dataora = '".$ieri."'   ";
		$query .="    for p in prenotazioni
					let datadoc = concat( SUBSTRING(p.data, 6, 4),  SUBSTRING(p.data, 3, 2), SUBSTRING(p.data, 0, 2)  )
					filter datadoc <= dataora
					insert p in prenotazionistoriche
					remove p in prenotazioni";

		//sdie($query);

		$statement = new Statement( $connection,
							array(					"query"     => $query,
													"count"     => true,
													"batchSize" => 1000,
													"sanitize"  => true					)		);
		$cursor = $statement->execute();

		return array( 'ris'=>true, "OK" );

	}
	catch (ConnectException $e) { return array( 'ris'=>false, "problema inserimento mail : connessione :".$e->getMessage() ); 	}
	catch (ServerException $e) {  return array( 'ris'=>false, "problema inserimento mail : server :".$e->getMessage() );	}
	catch (ClientException $e) {  return array( 'ris'=>false, "problema inserimento mail : client :".$e->getMessage() );	}
}


function DaiNomeRisorsa($key)
{

	global $connectionOptions;

	$connection = new Connection($connectionOptions);
	$documentHandler = new DocumentHandler($connection);

	try
	{
		$riso=$documentHandler->getById('risorse', $key);
		$email=$riso->get('nome');

		return array( 'ris'=>true, $email );

	}
	catch (ConnectException $e) { return array( 'ris'=>false, "problema inserimento mail : connessione :".$e->getMessage() ); 	}
	catch (ServerException $e) {  return array( 'ris'=>false, "problema inserimento mail : server :".$e->getMessage() );	}
	catch (ClientException $e) {  return array( 'ris'=>false, "problema inserimento mail : client :".$e->getMessage() );	}

}



function InviaMail($f, $dest, $msg, $data=array())
{

	global $config;
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {

		//Server settings
		$mail->SMTPDebug = 0;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $config['Host'];
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $config['Username'];
		$mail->Password = $config['Password'];
		$mail->SMTPSecure = $config['SMTPSecure'];
		$mail->Port = $config['Port'];

		//Recipients
		$mail->setFrom($config['setFrom'], $config['setFromNome']);
		$mail->addAddress($dest, '');     // Add a recipient
		//$mail->addAddress('ellen@example.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');




		if($msg=='prenotazione')
		{
			$mail->addAttachment($f, 'conferma_prenotazione.pdf');         // Add attachments

			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Conferma prenotazione servizio';
			$mail->Body    = $data['bodyhtml'];
			$mail->AltBody = $data['body'];

		}
		if($msg=='cancellazione')
		{
			//Attachments
			$mail->addAttachment($f, 'conferma_cancellazione.pdf');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			//Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Conferma cancellazione servizio';
			$mail->Body    = '<b>In allegato documento di conferma cancellazione servizo<b>';
			$mail->AltBody = 'In allegato documento di conferma cancellazione servizo';

		}
		if($msg=='password')
		{
			$testo="
Credenziali di accesso al portale Prenotazioni servizi\n

Link di Accesso: <a href='http://prenotazioni.it/__admin/'>Link qua</a>\n

Nome utente : ".$data['nome']."\n

Login di Accesso : ".$data['login']."
Password di Accesso : ".$data['password']."\n


Cambiare la password al primo accesso\n

\n\n";

			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Invio credenziali accesso Servizio Prenotazioni';
			$mail->Body    = '<pre>'.$testo;
			$mail->AltBody = $testo;

			//sdie($mail);

		}


		if( $msg == 'NOcancellazione48ore' )
		{
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Cancellazione non effettuata';
			$mail->Body    = $data['htmlbody'];
			$mail->AltBody = $data['body'];

		}



		if( $msg == 'generico' )
		{
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $data['oggetto'];
			$mail->Body    = $data['htmlbody'];
			$mail->AltBody = $data['body'];
		}



		$mail->send();
		return array('ris'=>true, "Mail inviata al server con successo");
	} catch (Exception $e) {  	return array('ris'=>false, "Problema di invio o composizione mail: errore : ".$mail->ErrorInfo);}



}


function sdie1($x=null)
{
	echo "<pre>\n";
	print_r($x);
	die("\n\nterminato come da richiesta");
}

?>
