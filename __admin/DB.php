<?php


namespace triagens\ArangoDb;

require  $config['LIB_DIR']. '/arango/vendor/autoload.php';





function DB($___GET, $___POST)
{
	global $utente, $ruolo;


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




if( $utente =='dummy') // sessione ridotta.....
{





	try {

		$attivita='generico';

		$connection = new Connection($connectionOptions);

		$collectionHandler = new CollectionHandler($connection);
		$documentHandler = new DocumentHandler($connection);



		stampa($___GET, 'GET');
		stampa($___POST, 'POST');


		if( isset($___POST['AQL'])  )
		{

			$attivita='Query esplicita'; if( ! accessibile('ALL_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$AQL=json_decode( $___POST['AQL'] );
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
			stampa($rud, 'RUD');
			foreach ($rud as $d)
			{
				if( is_object ( $d ) ) 	$ret[ $AQL[0] ][]=$d->getAll();
				else
				{
					$ret[ $AQL[0] ][]=$d;
				}
			}

			return returnErrJ( false, "OK", $ret );
		}





		if( isset($___POST['new_prenotazione'])  )
		{
			$attivita='creazione nuova prenotazione';

			$collectionName='prenotazioni';

			//if( ! accessibile($collectionName.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$prenotazione=json_decode( $___POST['new_prenotazione'] );
			$document = new Document();

			$document->set('_key', GetKey());

			foreach($prenotazione as $name => $val)
			{
				$ris=verifiche_aggiustamenti($collectionName, $name, $val);
				if( ! $ris['ris'] ) return returnErrJ( true, "Verifiche fallite salvataggio nuova prenotazione: $name : ".$ris['val'] );
				$document->set($name, $ris['val']);
			}


			$documentId = $documentHandler->save($collectionName, $document);

			return returnErrJ( false, "OK", array( '_key'=>$documentId) );
		}






		if( isset($___GET['getall'])  )
		{

			$attivita = 'get_all';
			$cosa = $___GET['getall'];
			$tabelle=array('servizi', 'risorse', 'atenei');
			if( ! in_array ($cosa, $tabelle) ) return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$ret[ $cosa ]=array();

			$cursor = $collectionHandler->byExample( $cosa, array() );
			$rud = $cursor->getAll();
			foreach ($rud as $d) $ret[ $cosa ][]=$d->getAll();

			return returnErrJ( false, "OK", $ret );
		}





		if( isset($___GET['get'])  )  // recupero generica
		{
			$attivita='recupera documento';
			$TAB=$___GET['tab'];		//if( ! accessibile($TAB.'_r', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$___GET['id'];
			$document = $documentHandler->getById( $TAB, $_key);

			return returnErrJ( false, "OK", $document->getAll() );

		}


	}
	catch (ConnectException $e) { return returnErrJ( true, "problema ".$attivita.": connessione", 	array( $e->getMessage() ) ); 	}
	catch (ServerException $e)  { return returnErrJ( true, "problema ".$attivita.": server", 		array( $e->getMessage() ) );	}
	catch (ClientException $e)  { return returnErrJ( true, "problema ".$attivita.": client", 		array( $e->getMessage() ) );	}


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



		stampa($___GET, 'GET');
		stampa($___POST, 'POST');


		if( isset($___POST['AQL'])  )
		{
			$attivita='Query esplicita'; if( ! accessibile('ALL_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$AQL=json_decode( $___POST['AQL'], true );

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

			return returnErrJ( false, "OK", $ret );
		}



		if( isset($___GET['new'])  )
		{
			$attivita='creazione nuovo';

			$collectionName=$___GET['tab'];

			if( ! accessibile($collectionName.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$document = new Document();
			$documentId = $documentHandler->save($collectionName, $document);

			return returnErrJ( false, "OK", array( '_key'=>$documentId) );
		}


		if( isset($___POST['new_prenotazione'])  )
		{
			$attivita='creazione nuova prenotazione';

			$collectionName='prenotazioni';

			if( ! accessibile($collectionName.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$prenotazione=json_decode( $___POST['new_prenotazione'] );
			$document = new Document();

			$document->set('_key', GetKey());

			foreach($prenotazione as $name => $val)
			{
				$ris=verifiche_aggiustamenti($collectionName, $name, $val);
				if( ! $ris['ris'] ) return returnErrJ( true, "Verifiche fallite salvataggio nuova prenotazione: $name : ".$ris['val'] );
				$document->set($name, $ris['val']);
			}

			$documentId = $documentHandler->save($collectionName, $document);

			return returnErrJ( false, "OK", array( '_key'=>$documentId) );
		}




		if( isset($___GET['get_all_crud'])  )
		{
			$attivita='get_all_document';

			$lista=json_decode($___GET['get_all_crud'], true);

			if( isset($___GET['example']) ) $ex=json_decode($___GET['example'], true);
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

			return returnErrJ( false, "OK", $ret );
		}


		if( isset($___GET['get_all_crud_sort'])  )
		{
			$attivita='get_all_document_sort';

			$lista=json_decode($___GET['get_all_crud_sort']);



			$ret=array();
			foreach( $lista as $l)
			{

				$ret[$l]=array();
				if( $l == 'atenei')  $query="for i in $l  sort TO_NUMBER(i.ordine) DESC   return i";
				else $query="for i in $l   sort i._key   return i";
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

			return returnErrJ( false, "OK", $ret );
		}



		if( isset($___GET['mod_crud'])  ) // modifica generica crud
		{
			$attivita='modifica documento';

			$TAB=$___GET['tab'];
				if( ! accessibile($TAB.'_m', $ruolo) )
				{

					if( !( $TAB == 'utenti'  &&  $___GET['campo'] == 'password' ) )  // eccezione.. cambio password fattibile da chiunque...
					return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );
				}

			$_key=$___GET['ide'];
			$name=$___GET['campo'];
			$val=$___GET['valore'];

			$document = $documentHandler->getById( $TAB, $_key);
			if( $TAB=='utenti' && $name == 'password')
			{
				$login=$document->get('login');
				if($login == 'superadmin') return returnErrJ( true, "Password super admin non modificabile.." );
				$val=crypt($val, 'SCS');
			}

			$ris=verifiche_aggiustamenti($TAB, $name, $val);
			if( ! $ris['ris'] ) return returnErrJ( true, "Verifiche fallite: ".$ris['val'] );

			$document->set($name, $ris['val']);
			//$document->set($name, $val);
			$documentHandler->update( $document );

			return returnErrJ( false, "OK" );

		}


		if( isset($___GET['dele']) && isset($___GET['ID']) )
		{
			$attivita='cancellazione documento';

			$TAB=$___GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) ) return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$___GET['ID'];

			$document = $documentHandler->getById( $TAB, $_key);
			$D=$document->getAll();

			if( $TAB=='utenti' )
			{
				$login=$document->get('login');
				if($login == 'superadmin') return returnErrJ( true, "super admin non cancellabile.." );
			}


			// --------------------------------------------------------------
			// ---------------------------------
			// ---------------------------------               DA FARE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// ---------------------------------
			// --------------------------------------------------------------
			if( !verifica_cancellazione($TAB, $_key) ) return returnErrJ( true, "questo elemento e' utilizzato in altri documenti. Eliminare ogni riferimento e riprovare" );

			$documentHandler->removeById($TAB, $_key);

			return returnErrJ( false, "OK" , array('doc'=>$D) );
		}


		if( isset($___POST['delepres'])  )  // recupero generica
		{
			$attivita='cancella prenotazioni';

			if( !isset($_SESSION['DBprenotazioni']) ) return returnErrJ( true, "Non trovato DB per la cancellazione delle prenotazioni ..." );
			$collectionName=$_SESSION['DBprenotazioni'];

			if( ! accessibile($collectionName.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita);

			$prenotazioni=json_decode( $___POST['delepres'] );

			foreach($prenotazioni as $_key ) $documentHandler->removeById($collectionName, $_key);

			return returnErrJ( false, "OK" );

		}


		if( isset($___GET['mod'])  )  // modifica generica
		{
			$attivita='modifica documento';
			$TAB=$___GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$___POST['pk'];
			$name=$___POST['name'];
			$val=( isset($___POST['value'])  )?$___POST['value']:null;


			$ris=verifiche_aggiustamenti($TAB, $name, $val);
			if( ! $ris['ris'] ) return returnErrJ( true, "Verifiche fallite: ".$ris['val'] );

			stampa($ris, 'risultato verifiche agiustamenti');

			$document = $documentHandler->getById( $TAB, $_key);
			$document->set($name, $ris['val']);
			$documentHandler->update( $document );

			return returnErrJ( false, "OK" );

		}


		if( isset($___GET['get'])  )  // recupero generica
		{
			$attivita='recupera documento';
			$TAB=$___GET['tab'];		if( ! accessibile($TAB.'_r', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$___GET['id'];
			$document = $documentHandler->getById( $TAB, $_key);

			return returnErrJ( false, "OK", $document->getAll() );

		}




		if( isset($___GET['clona'])  )  // CLONA !!
		{
			$attivita='clona documento';
			$TAB=$___GET['tab'];		if( ! accessibile($TAB.'_m', $ruolo) )  return returnErrJ( true, "Utente non abilitato a questa operazione ".$attivita );

			$_key=$___GET['id'];
			$document = $documentHandler->getById( $TAB, $_key);

			$nuovo = new Document();
			foreach( $document->getAll() as $k => $v )	if( $k != '_key' )	$nuovo->set($k, $v);

			$documentId = $documentHandler->save($TAB, $nuovo);

			return returnErrJ( false, "OK", array( '_key'=>$documentId) );

		}


	}
	catch (ConnectException $e) { return returnErrJ( true, "problema ".$attivita.": connessione", 	array( $e->getMessage() ) ); 	}
	catch (ServerException $e)  { return returnErrJ( true, "problema ".$attivita.": server", 		array( $e->getMessage() ) );	}
	catch (ClientException $e)  { return returnErrJ( true, "problema ".$attivita.": client", 		array( $e->getMessage() ) );	}


	die();



}


}  /*fine DB */

function verifica_cancellazione($TAB, $_key)
{

	return true;

}





?>
