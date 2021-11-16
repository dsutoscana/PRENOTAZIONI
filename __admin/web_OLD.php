<?php
namespace triagens\ArangoDb;


ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);

session_start();


//stampa($_SESSION);

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}
$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];


$config=require_once('config.php');
require_once('lib.php');


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


/*

// open connection
$connection = new Connection($connectionOptions);
$collectionHandler = new CollectionHandler($connection);


// create a new collection
$collectionName = "firstCollection";
$collection = new Collection($collectionName);


if ($collectionHandler->has($collectionName)) {
	// drops an existing collection with the same name to make
	// tutorial repeatable
	$collectionHandler->drop($collectionName);
}

$collectionId = $collectionHandler->create($collection);


echo "collection ID\n";
//var_dump($collectionId);
//var_dump($collection);

$documentHandler = new DocumentHandler($connection);


// create a document with some attributes
$document = new Document();
$document->set("a", "Foo");
$document->set("b", "bar");

// save document in collection
$documentId = $documentHandler->save($collectionName, $document);


echo "Document\n";
//print_r($document);
//print_r($documentId);

// read document
$document = $documentHandler->get($collectionName, $documentId);
//print_r($document);


// update document
$document->set("c", "qux");
$document->pippo="pippoooo";

$document->likes = ['fishing', 'hiking', 'swimming'];
$documentHandler->update($document);





// additional data via for-loop
for ($i = 0; $i < 5; $i++) {
	$document = new Document();
	$document->set("_key", "doc_" . $i . mt_rand());
	$val = 'falso';
	if ($i%2 === 0) {
		$val = 'vero';
	}
	$document->set("even", $val);
	$documentHandler->save($collectionName, $document);
}


// list all documents
$documents = $collectionHandler->all($collectionId);


$cursor = $collectionHandler->byExample('firstCollection', ['even' => 'vero']);
//var_dump($cursor->getAll());

foreach ( $cursor->getAll() as $k => $v )
{

	$att=$v->getAll(['_includeInternals'=>true]);
	echo "---\n";
	print_r($k);echo "\n";
	print_r($att);echo "\n";

}

//$documentDelete = $documentHandler->remove($document);
//print_r($documentDelete);


 */



$connection = new Connection($connectionOptions);

$collectionHandler = new CollectionHandler($connection);
$documentHandler = new DocumentHandler($connection);



stampa($_GET, 'GET');
stampa($_POST, 'POST');





if( isset($_GET['new'])  )
{
	$collectionName=$_GET['tab'];

	try
	{
			$collection = new Collection($collectionName);
			try { 		$collectionHandler->create($collection);	} catch (\Exception $e) {} 		// collection may already exist - ignore this error for now

			if ( !$collectionHandler->has($collectionName)) { 		returnErrJ( true, "problema inserimento attivita' fase 1. Collezione ". $collectionName ." non trovata" );	}

			$documentHandler = new DocumentHandler($connection);
			$document = new Document();
			$documentId = $documentHandler->save($collectionName, $document);

			returnErrJ( false, "OK", array( '_key'=>$documentId) );
	}
	 catch (ConnectException $e) { returnErrJ( true, "problema inserimento: connessione" ); 	}
	 catch (ServerException $e) {  returnErrJ( true, "problema inserimento: server" );	}
	 catch (ClientException $e) {  returnErrJ( true, "problema inserimento: client" );	}
}


if( isset($_GET['get_all_crud'])  )
{

	$utenti=GetCollection($collectionHandler, 'utenti');

	returnErrJ( false, "OK", array( 'dsu'=>array(), 'atenei'=>array(), 'gruppi'=>array(), 'utenti'=>$utenti ) );
}

/* if( isset($_GET['mod'])  )  // modifica generica
{
	$db=new SQL3();

	$TAB=$_GET['tab'];

	$id=$_POST['pk'];
	$name=$_POST['name'];
	$val=$_POST['value'];

	$s=sprintf("UPDATE %s SET %s='%s' WHERE id=%d", $TAB, $name , $val, $id  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema aggiornamento record" );	}
	$db->close();

	returnErrJ( false, "OK" );

}
 */


if( isset($_GET['mod_crud'])  ) // modifica generica crud
{

	$TAB=$_GET['tab'];
	$_key=$_GET['ide'];
	$name=$_GET['campo'];
	$val=$_GET['valore'];

	$r = InsertUpdateCampo( $documentHandler, $TAB, $_key, array($name=>$val) );





	if( $r['ris'] === false )  { 		returnErrJ( true, "problema aggiornamento record" );	}

	returnErrJ( false, "OK" );

}




if( isset($_GET['dele']) && isset($_GET['ID']) )
{
	$db=new SQL3();


	if(  $_GET['tab'] == 'cli'  )
	{
		$s=sprintf('select count(id) from att where cli = %s', $_GET['ID']);
		$r = $db->querySingle($s); 	if( $r === false )  { 		returnErrJ( true, "problema query attivita'" );	}
		if($r>0) { 		returnErrJ( true, "Impossibile cancellare cliente.. Sono presente ".$r." attività correlale. Cancellarle tutte e poi cancellare il cliente" );	}
	}


	$s=sprintf("DELETE FROM %s WHERE id=%d",$_GET['tab'],   $_GET['ID']  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema cancellazione  record" );	}
	$db->close();

	returnErrJ( false, "OK" );
}




die();



//stampa($_SESSION);

if( !isset($_SESSION['login_user']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}



//die();
define("__FILEDB__" , 'PROD._sqlite3_.db');


class SQL3 extends SQLite3
{
	function __construct($c=null)
	{
		if( $c != null ) $c=SQLITE3_OPEN_READONLY;
		else             $c=SQLITE3_OPEN_READWRITE;

		try { 	$this->open(__FILEDB__, $c); }
		catch (Exception $exception) {  returnErrJ( true, "Errore accesso al DB : ".$exception->getMessage() );	}
		return $this;
	}
}





if( isset($_GET['get_all_att'])  )
{
	$db=new SQL3();

	$r = $db->query('SELECT * FROM att');
	$att=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $att[  $res['id'] ] = $res; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM cli');
	$cli=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $cli[ $res['id'] ] = array( 'id'=> $res['id'], 'cliente'=>$res['cliente'], 'sede'=>$res['sede'], 'varie'=>$res['varie'], 'mark'=>$res['mark']  ) ; 	}
	$r->finalize();

	$db->close();
	returnErrJ( false, "OK", array( 'cli'=>$cli, 'att'=>$att) );
}







if( isset($_GET['get_all_ini'])  )
{
	$db=new SQL3();

	$cassetta=0;
	if( isset( $_GET['cassetta'] ) && $_GET['cassetta'] == 'SI') $cassetta=1;

	$r = $db->query('SELECT * FROM att WHERE "cass" = '.$cassetta);
	$att=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $att[  $res['id'] ] = $res; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM ass');
	$ass=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $ass[  $res['slug'] ] = $res['nome']; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM lav');
	$lav=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $lav[] = array( 'id'=> $res['id'], 'lav'=>$res['lav'], 'freq'=>$res['freq'] ) ; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM prea');
	$prea=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $prea[] = $res['slug']; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM cli');
	$cli=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $cli[ $res['id'] ] = array( 'id'=> $res['id'], 'cliente'=>$res['cliente'], 'sede'=>$res['sede'], 'varie'=>$res['varie'], 'mark'=>$res['mark']  ) ; 	}
	$r->finalize();

	$db->close();
	returnErrJ( false, "OK", array( 'cli'=>$cli, 'att'=>$att, 'ass'=>$ass, 'lav'=>$lav, 'prea'=>$prea) );
}




if( isset($_GET['get_all_crud'])  )
{
	$db=new SQL3();

	$r = $db->query('SELECT * FROM cli');
	$cli=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $cli[] = array( 'id'=> $res['id'], 'cliente'=>$res['cliente'], 'sede'=>$res['sede'], 'varie'=>$res['varie'], 'mark'=>$res['mark']  ) ; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM ass');
	$ass=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $ass[]= array( 'id'=> $res['id'], 'slug'=>$res['slug'], 'nome'=>$res['nome'] ) ;	}
	$r->finalize();

	$r = $db->query('SELECT * FROM lav');
	$lav=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $lav[] = array( 'id'=> $res['id'], 'lav'=>$res['lav'], 'freq'=>$res['freq'] ) ; 	}
	$r->finalize();

	$r = $db->query('SELECT * FROM prea');
	$prea=array();
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $prea[] = array( 'id'=> $res['id'], 'slug'=>$res['slug'] ) ; 	}
	$r->finalize();

	$db->close();
	returnErrJ( false, "OK", array( 'cli'=>$cli, 'ass'=>$ass, 'lav'=>$lav, 'prea'=>$prea) );
}



if( isset($_GET['get_cli'])  )
{
	$db=new SQL3();

	$o='';
	$r = $db->query('SELECT * FROM cli');
	while($res = $r->fetchArray(SQLITE3_ASSOC)) {  $o .= sprintf( "<tr idx='%s'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",  $res['id'], $res['cliente'], $res['sede'], $res['varie'], $res['mark']  ) ; 	}
	$r->finalize();
	$db->close();
	//returnErrJ( false, "OK", array( 'cli'=>$cli) );
	echo "$o";die();
}




if( isset($_GET['new'])  )
{
	$db=new SQL3();

	$TAB=$_GET['tab'];

	$s=sprintf("INSERT INTO %s DEFAULT VALUES ", $TAB);
	stampa($s, 'coma');
	$r=$db->exec($s);  if( $r === false )  { 		returnErrJ( true, "problema inserimento attivita' fase 1" );	}
 	$r = $db->querySingle('select last_insert_rowid()'); 	if( $r === false )  { 		returnErrJ( true, "problema inserimento attivita'" );	}
	$db->close();

	returnErrJ( false, "OK", array( 'id'=>$r) );
}


if( isset($_GET['mod'])  )  // modifica generica
{
	$db=new SQL3();

	$TAB=$_GET['tab'];

	$id=$_POST['pk'];
	$name=$_POST['name'];
	$val=$_POST['value'];

	$s=sprintf("UPDATE %s SET %s='%s' WHERE id=%d", $TAB, $name , $val, $id  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema aggiornamento record" );	}
	$db->close();

	returnErrJ( false, "OK" );

}


if( isset($_GET['mod_ck'])  )  // modifica checklist.. se non ho valore vale 0
{
	$db=new SQL3();

	$TAB=$_GET['tab'];

	$id=$_POST['pk'];
	$name=$_POST['name'];
	$val=( isset( $_POST['value'] ) )  ? 1:0;

	$s=sprintf("UPDATE %s SET %s='%s' WHERE id=%d", $TAB, $name , $val, $id  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema aggiornamento record" );	}
	$db->close();

	returnErrJ( false, "OK" );

}


if( isset($_GET['mod_crud'])  ) // modifica generica crud
{
	$db=new SQL3();

	$TAB=$_GET['tab'];

	$id=$_GET['ide'];
	$name=$_GET['campo'];
	$val=$_GET['valore'];

	$s=sprintf("UPDATE %s SET %s='%s' WHERE id=%d", $TAB, $name , $val, $id  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema aggiornamento record" );	}
	$db->close();

	returnErrJ( false, "OK" );

}




if( isset($_GET['dele']) && isset($_GET['ID']) )
{
	$db=new SQL3();


	if(  $_GET['tab'] == 'cli'  )
	{
		$s=sprintf('select count(id) from att where cli = %s', $_GET['ID']);
		$r = $db->querySingle($s); 	if( $r === false )  { 		returnErrJ( true, "problema query attivita'" );	}
		if($r>0) { 		returnErrJ( true, "Impossibile cancellare cliente.. Sono presente ".$r." attività correlale. Cancellarle tutte e poi cancellare il cliente" );	}
	}


	$s=sprintf("DELETE FROM %s WHERE id=%d",$_GET['tab'],   $_GET['ID']  );
	stampa($s, 'coma');
	$r=$db->exec($s);

	if( $r === false )  { 		returnErrJ( true, "problema cancellazione  record" );	}
	$db->close();

	returnErrJ( false, "OK" );
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






?>
