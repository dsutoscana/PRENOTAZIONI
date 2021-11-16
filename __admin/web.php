<?php
namespace triagens\ArangoDb;


ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);

if( !session_start()) { stampa('errore di start sessione....');stampa($_SESSION); };




if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}
$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];


$config=require_once('config.php');
require_once('lib.php');
require_once('lib_simple.php');
require_once('DB.php');

stampa($_SESSION);


require  $config['LIB_DIR']. '/arango/vendor/autoload.php';


 $ritorno=DB($_GET, $_POST);

 header('Content-type: application/json');
 echo json_encode( $ritorno );


 die();



/*


if( $utente =='dummy') // sessione ridotta.....
{





	try {

		$attivita='generico';

		$connection = new Connection($connectionOptions);

		$collectionHandler = new CollectionHandler($connection);
		$documentHandler = new DocumentHandler($connection);



		stampa($_GET, 'GET');
		stampa($_POST, 'POST');


		if( isset($_POST['AQL'])  )
		{

			$attivita='Query esplicita'; if( ! accessibile('ALL_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$AQL=json_decode( $_POST['AQL'] );
			$query=daiAQL( $AQL ); // TAB, id query, parametri

			stampa($AQL, 'AQL');
			stampa($query, 'AQL');
			$statement = new Statement(
					$connection,
					array(
							"query"     => $query,
							"count"     => true,
							"batchSize" => 1000,
							"sanitize"  => true
					)
					);


			$cursor = $statement->execute();
			$ret = array();

			$rud = $cursor->getAll();
			stampa($rud, 'RUD');
			foreach ($rud as $d)
			{
				if( is_object ( $d ) ) 	$ret[ $AQL[0] ][]=$d->getAll();
				else
				{
					$ret[ $AQL[0] ][]=$d;
				}
			}

			returnErrJ( false, "OK", $ret );
		}





		if( isset($_POST['new_prenotazione'])  )
		{
			$attivita='creazione nuova prenotazione';

			$collectionName='prenotazioni';

			//if( ! accessibile($collectionName.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$prenotazione=json_decode( $_POST['new_prenotazione'] );
			$document = new Document();

			$document->set('_key', GetKey());

			foreach($prenotazione as $name => $val)
			{
				$ris=verifiche_aggiustamenti($collectionName, $name, $val);
				if( ! $ris['ris'] ) returnErrJ( true, "Verifiche fallite salvataggio nuova prenotazione: $name : ".$ris['val'] );
				$document->set($name, $ris['val']);
			}


			$documentId = $documentHandler->save($collectionName, $document);

			returnErrJ( false, "OK", array( '_key'=>$documentId) );
		}






		if( isset($_GET['getall'])  )
		{

			$attivita = 'get_all';
			$cosa = $_GET['getall'];
			$tabelle=array('servizi', 'risorse', 'atenei');
			if( ! in_array ($cosa, $tabelle) ) returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$ret[ $cosa ]=array();

			$cursor = $collectionHandler->byExample( $cosa, array() );
			$rud = $cursor->getAll();
			foreach ($rud as $d) $ret[ $cosa ][]=$d->getAll();

			returnErrJ( false, "OK", $ret );
		}





		if( isset($_GET['get'])  )  // recupero generica
		{
			$attivita='recupera documento';
			$TAB=$_GET['tab'];		//if( ! accessibile($TAB.'_r', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$_GET['id'];
			$document = $documentHandler->getById( $TAB, $_key);

			returnErrJ( false, "OK", $document->getAll() );

		}


	}
	catch (ConnectException $e) { returnErrJ( true, "problema ".$attivita.": connessione", 	array( $e->getMessage() ) ); 	}
	catch (ServerException $e)  { returnErrJ( true, "problema ".$attivita.": server", 		array( $e->getMessage() ) );	}
	catch (ClientException $e)  { returnErrJ( true, "problema ".$attivita.": client", 		array( $e->getMessage() ) );	}


	die();
}




else
{



// sessione piena.. da operatore...

try {

	$attivita='generico';

	$connection = new Connection($connectionOptions);

	$collectionHandler = new CollectionHandler($connection);
	$documentHandler = new DocumentHandler($connection);



	stampa($_GET, 'GET');
	stampa($_POST, 'POST');


	if( isset($_POST['AQL'])  )
	{
		$attivita='Query esplicita'; if( ! accessibile('ALL_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

		$AQL=json_decode( $_POST['AQL'], true );

		//stampa($AQL, 'AQL');

		$query=daiAQL( $AQL ); // TAB, id query, parametri

		stampa($AQL, 'AQL');
		stampa($query, 'AQL');
		$statement = new Statement(
			$connection,
			array(
				"query"     => $query,
				"count"     => true,
				"batchSize" => 1000,
				"sanitize"  => true
			)
		);


		$cursor = $statement->execute();
		$ret[ $AQL[0] ] = array();

		$rud = $cursor->getAll();
		//stampa($rud, 'RUD');
		foreach ($rud as $d)
		{
			if( is_object ( $d ) ) 	$ret[ $AQL[0] ][]=$d->getAll();
			else
			{
				$ret[ $AQL[0] ][]=$d;
			}
		}

		returnErrJ( false, "OK", $ret );
	}



	if( isset($_GET['new'])  )
	{
		$attivita='creazione nuovo';

		$collectionName=$_GET['tab'];

		if( ! accessibile($collectionName.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

		$document = new Document();
		$documentId = $documentHandler->save($collectionName, $document);

		returnErrJ( false, "OK", array( '_key'=>$documentId) );
	}


	if( isset($_POST['new_prenotazione'])  )
	{
		$attivita='creazione nuova prenotazione';

		$collectionName='prenotazioni';

		if( ! accessibile($collectionName.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

		$prenotazione=json_decode( $_POST['new_prenotazione'] );
		$document = new Document();

		$document->set('_key', GetKey());

		foreach($prenotazione as $name => $val)
		{
				$ris=verifiche_aggiustamenti($collectionName, $name, $val);
				if( ! $ris['ris'] ) returnErrJ( true, "Verifiche fallite salvataggio nuova prenotazione: $name : ".$ris['val'] );
				$document->set($name, $ris['val']);
		}

		$documentId = $documentHandler->save($collectionName, $document);

		returnErrJ( false, "OK", array( '_key'=>$documentId) );
	}




	if( isset($_GET['get_all_crud'])  )
	{
		$attivita='get_all_document';

		$lista=json_decode($_GET['get_all_crud'], true);

		if( isset($_GET['example']) ) $ex=json_decode($_GET['example'], true);
		else $ex=array();

		stampa($ex, "EXAMPLE");


		$ret=array();
		foreach( $lista as $l)
		{
			$ret[ $l ]=array();

			$cursor = $collectionHandler->byExample( $l, $ex );
			$rud = $cursor->getAll();
			foreach ($rud as $d) $ret[ $l ][]=$d->getAll();
		}

		returnErrJ( false, "OK", $ret );
	}


	if( isset($_GET['get_all_crud_sort'])  )
	{
		$attivita='get_all_document_sort';

		$lista=json_decode($_GET['get_all_crud_sort']);



		$ret=array();
		foreach( $lista as $l)
		{

			$ret[$l]=array();
			$query="for i in $l   sort i._key   return i";
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
			foreach ($rud as $d)
			{
				if( is_object ( $d ) ) 	$ret[ $l ][]=$d->getAll();
				else
				{
					$ret[ $l ][]=$d;
				}
			}
		}

		returnErrJ( false, "OK", $ret );
	}



	if( isset($_GET['mod_crud'])  ) // modifica generica crud
	{
		$attivita='modifica documento';

		$TAB=$_GET['tab'];  		if( ! accessibile($TAB.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

		$_key=$_GET['ide'];
		$name=$_GET['campo'];
		$val=$_GET['valore'];

		$document = $documentHandler->getById( $TAB, $_key);
		if( $TAB=='utenti' && $name == 'password')
		{
			$login=$document->get('login');
			if($login == 'superadmin') returnErrJ( true, "Password super admin non modificabile.." );
			$val=crypt($val, 'SCS');
		}

		$ris=verifiche_aggiustamenti($TAB, $name, $val);
		if( ! $ris['ris'] ) returnErrJ( true, "Verifiche fallite: ".$ris['val'] );

		$document->set($name, $ris['val']);
		//$document->set($name, $val);
		$documentHandler->update( $document );

		returnErrJ( false, "OK" );

	}


	if( isset($_GET['dele']) && isset($_GET['ID']) )
	{
		$attivita='cancellazione documento';

		$TAB=$_GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) ) returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

		$_key=$_GET['ID'];

		$document = $documentHandler->getById( $TAB, $_key);
		$D=$document->getAll();

		if( $TAB=='utenti' )
		{
			$login=$document->get('login');
			if($login == 'superadmin') returnErrJ( true, "super admin non cancellabile.." );
		}


		// --------------------------------------------------------------
		// ---------------------------------
		// ---------------------------------               DA FARE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// ---------------------------------
		// --------------------------------------------------------------
		if( !verifica_cancellazione($TAB, $_key) ) returnErrJ( true, "questo elemento e' utilizzato in altri documenti. Eliminare ogni riferimento e riprovare" );

		$documentHandler->removeById($TAB, $_key);

		returnErrJ( false, "OK" , array('doc'=>$D) );
	}


	if( isset($_POST['delepres'])  )  // recupero generica
	{
		$attivita='cancella prenotazioni';

		if( !isset($_SESSION['DBprenotazioni']) ) returnErrJ( true, "Non trovato DB per la cancellazione delle prenotazioni ..." );
		$collectionName=$_SESSION['DBprenotazioni'];

		if( ! accessibile($collectionName.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

		$prenotazioni=json_decode( $_POST['delepres'] );

		foreach($prenotazioni as $_key ) $documentHandler->removeById($collectionName, $_key);

		returnErrJ( false, "OK" );

	}


	if( isset($_GET['mod'])  )  // modifica generica
	{
		$attivita='modifica documento';
		$TAB=$_GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

		$_key=$_POST['pk'];
		$name=$_POST['name'];
		$val=( isset($_POST['value'])  )?$_POST['value']:null;


		$ris=verifiche_aggiustamenti($TAB, $name, $val);
		if( ! $ris['ris'] ) returnErrJ( true, "Verifiche fallite: ".$ris['val'] );

		stampa($ris, 'risultato verifiche agiustamenti');

		$document = $documentHandler->getById( $TAB, $_key);
		$document->set($name, $ris['val']);
		$documentHandler->update( $document );

		returnErrJ( false, "OK" );

	}


	if( isset($_GET['get'])  )  // recupero generica
	{
		$attivita='recupera documento';
		$TAB=$_GET['tab'];		if( ! accessibile($TAB.'_r', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

		$_key=$_GET['id'];
		$document = $documentHandler->getById( $TAB, $_key);

		returnErrJ( false, "OK", $document->getAll() );

	}




 	if( isset($_GET['clona'])  )  // CLONA !!
	{
		$attivita='clona documento';
		$TAB=$_GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) )  returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

		$_key=$_GET['id'];
		$document = $documentHandler->getById( $TAB, $_key);

		$nuovo = new Document();
		foreach( $document->getAll() as $k => $v )	if( $k != '_key' )	$nuovo->set($k, $v);

		$documentId = $documentHandler->save($TAB, $nuovo);

		returnErrJ( false, "OK", array( '_key'=>$documentId) );

	}


}
catch (ConnectException $e) { returnErrJ( true, "problema ".$attivita.": connessione", 	array( $e->getMessage() ) ); 	}
catch (ServerException $e)  { returnErrJ( true, "problema ".$attivita.": server", 		array( $e->getMessage() ) );	}
catch (ClientException $e)  { returnErrJ( true, "problema ".$attivita.": client", 		array( $e->getMessage() ) );	}


die();



}




function verifica_cancellazione($TAB, $_key)
{

		return true;

}

die("NON CI DOVEVO ESSERE QUA... \n");

//// -------------------------------------------------------     VARIE    -----------------------------------------------------
function returnErrJ($ret, $MSG, $data=array())
{
	stampa( $data, $MSG ." debug da ritorno errore Json" );
	//if( $ret==true ) { stampa( $data, $MSG );}  // debug da togliere....

	header('Content-type: application/json');
	$results = array_merge( array( 'error' => $ret,  'success' => !$ret,  'error_msg' => $MSG), $data	);
	echo json_encode($results); 	die("");
}
function debug_backtrace_string($l=0) {
	$stack = '';
	$i = 1;
	$trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $l);
	unset($trace[0]); //Remove call to this function from stack trace
	foreach($trace as $node) {
		$stack .= "#$i ".$node['file'] ."(" .$node['line']."): ";
		if(isset($node['class'])) {
			$stack .= $node['class'] . "->";
		}
		$stack .= $node['function'] . "()" . PHP_EOL;
		$i++;
	}
	return $stack;
}
function stampa($a, $label='')
{
	$d=debug_backtrace_string(2);
	$x=print_r($a, true);
	$ha=fopen("log__.txt", 'a');
	fwrite($ha,$label." --> ".$d."\n");
	fwrite($ha, $x."\n--\n");
	fclose($ha);
}


*/



?>
