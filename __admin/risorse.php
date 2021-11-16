<?php

//http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.php
//https://vitalets.github.io/x-editable/
//https://github.com/bootstrap-wysiwyg/bootstrap3-wysiwyg

ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);



require_once('config.php');
require_once('lib_simple.php');
require_once('html.php');

session_start();

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}

$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];
if($utente=='dummy') die('utente non autorizzato');

$VAL=array();$VAL['servizi']=array();
$lista=array( '_key', 'servizi', "nome", "attiva", "gruppor", "ateneo_i", "orario_da", "orario_a", "giorno_rip", "giorno_rip_no", "giorno_part", "giorno_part_no", "attivo_dal_al", "tempo_minimo", "elabxtempo", "ris_tempo" );


if( isset( $_GET['NEW_RISORSA'] ) )
{
    $DATI=QUERYDB(URLDB.'?new&tab=risorse');
    //echo "<pre>";print_r($DATI); die('qua');
    logattivita("Creata nuova risorsa ");

}
elseif( isset( $_GET['CLONA'] ) && isset($_GET['IDR']) && $_GET['IDR'] != '' )
{
	$IDR=$_GET['IDR'];

	$DATI=QUERYDB(URLDB.'?clona&tab=risorse&id='.$IDR);
	//echo "<pre>";print_r($DATI); die('qua');

	if(!isset($DATI['_key'])) {	echo "<pre>Errore generico collegamento al DB : Non presente nuovo indice\n";print_r($DATI); echo "</pre>"; die(); }
	$IDR=$DATI['_key'];

	$DATI=QUERYDB(URLDB.'?get&tab=risorse&id='.$IDR);
	//echo "<pre>";print_r($DATI); die('qua');
    logattivita("Creata nuova risorsa con clonazione da IDR: ".$IDR);

}
elseif( isset($_GET['IDR']) && isset($_GET['VEDI']) )
{
       $IDR=$_GET['IDR'];

       $DATI=QUERYDB(URLDB.'?get&tab=risorse&id='.$IDR);
       //echo "<pre>";print_r($DATI); die('qua');

}
elseif( isset($_GET['DELETE']) )
{
	$IDR=$_GET['IDR'];

	$DATI=QUERYDB(URLDB.'?dele&tab=risorse&ID='.$IDR);
	//echo "<pre>";print_r($DATI); die('qua');
    logattivita("Cancellata risorsa ID ".$IDR);

	header( "Location: " . URLADM .'/crud.php#miotab_A5' );
	die();

}

else  {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Comando non riconosciuto")); die('Comando non riconosciuto'); }

foreach($lista as $l) $VAL[ $l ] = ( isset($DATI[$l]) ) ? $DATI[$l] : null ; // riempio $VAL con i valori della risorsa.

//echo "<pre>";print_r($DATI); die('qua');

// richiesta dati per riempire le form a discesa
$ORTAB=array(1=>'atenei', 2=>'gruppir', 3=>'servizi');
$listaDB=json_encode($ORTAB);
$DATI=QUERYDB(URLDB.'?get_all_crud_sort='.$listaDB);

//echo "<pre>";print_r($DATI); die('qua');

$ATENEI='[';  	foreach ($DATI['atenei']  as $k => $v) 		$ATENEI  .= sprintf( '{value: "%s", text: "%s"},', p($v['ateneo']), p($v['ateneo']));   				$ATENEI .= ']';
$GRUPPIR='[';  	foreach ($DATI['gruppir'] as $k => $v) 		$GRUPPIR .= sprintf( '{value: "%s", text: "%s"},', p($v['gruppo_risorsa']), p($v['gruppo_risorsa']));   				$GRUPPIR .= ']';
$SERVIZI='[';  	foreach ($DATI['servizi'] as $k => $v) 		$SERVIZI .= sprintf( '{value: "%s", text: "%s"},', p($v['nome']), p($v['nome']) );   				$SERVIZI .= ']';
// $SERVIZI=pcr($SERVIZI);

$ORARIOv=array(
				'00:00', '00:15', '00:30', '00:45', '01:00', '01:15', '01:30', '01:45',
				'02:00', '02:15', '02:30', '02:45', '03:00', '03:15', '03:30', '03:45', '03:00', '03:15', '03:30', '03:45',
                '05:00', '05:15', '05:30', '05:45', '06:00', '06:15', '06:30', '06:45', '07:00', '07:15', '07:30', '07:45',
                '08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30', '09:45', '10:00', '10:15', '10:30', '10:45',
				'11:00', '11:15', '11:30', '11:45', '12:00', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30', '13:45',
				'14:00', '14:15', '14:30', '14:45', '15:00', '15:15', '15:30', '15:45', '16:00', '16:15', '16:30', '16:45',
				'17:00', '17:15', '17:30', '17:45', '18:00', '18:15', '18:30', '18:45', '19:00', '19:15', '19:30', '19:45',
				'20:00', '20:15', '20:30', '20:45', '21:00', '21:15', '21:30', '21:45', '22:00', '22:15', '22:30', '22:45',
				'23:00', '23:15', '23:30', '23:45', );

$ORARIO='[';  		foreach ($ORARIOv as $k => $v) 				$ORARIO .= sprintf( "{value: '%s', text: '%s'},", $v, $v);   		$ORARIO .= ']';

$SERVIZIVAL='[';  	foreach (  is($VAL,'servizi', array()) as $k => $v) 		$SERVIZIVAL .= sprintf( ' "%s",', $v);  	$SERVIZIVAL .= ']';
$ATENEIVAL='[';  	foreach (  is($VAL,'ateneo_i', array()) as $k => $v) 		$ATENEIVAL  .= sprintf( ' "%s",', $v);  	$ATENEIVAL .= ']';

$GIORNO_RIP     = '['; foreach( is( $VAL,'giorno_rip', array() ) as $v ) 		$GIORNO_RIP .= sprintf( " '%s',", $v);  	$GIORNO_RIP .= ']';
$GIORNO_RIP_NO  = '['; foreach( is( $VAL,'giorno_rip_no', array() ) as $v )		$GIORNO_RIP_NO .= sprintf( " '%s',", $v);  	$GIORNO_RIP_NO .= ']';
$GIORNO_PART    = '['; foreach( is( $VAL,'giorno_part', array() ) as $v )		$GIORNO_PART .= sprintf( " '%s',", $v);  	$GIORNO_PART .= ']';
$GIORNO_PART_NO = '['; foreach( is( $VAL,'giorno_part_no',array() ) as $v )		$GIORNO_PART_NO .= sprintf( " '%s',", $v);  $GIORNO_PART_NO .= ']';

?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prenotazioni</title>
    	<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      	<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

		<link rel="stylesheet" href="<?php echo URLADM ?>/stile.css">
  		<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
    	<script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>
    	<script src="<?php echo URLIB ?>/editable/bootstrap3-editable/js/bootstrap-editable.js"></script>





<script type="text/javascript">

var URLDB='<?php echo URLDB; ?>';
var ATENEI=<?php echo $ATENEI; ?>;
var ATENEIVAL=<?php echo $ATENEIVAL; ?>;
var GRUPPIR=<?php echo $GRUPPIR; ?>;
var SERVIZI=<?php echo $SERVIZI; ?>;
var SERVIZIVAL=<?php echo $SERVIZIVAL; ?>;
var ORARIO=<?php echo $ORARIO; ?>;
var GIORNI=["Lun", "Mar", "Mer", "Gio", "Ven", "Sab", "Dom"];


var GIORNO_RIP=<?php echo $GIORNO_RIP; ?>;
var GIORNO_RIP_NO=<?php echo $GIORNO_RIP_NO; ?>;
var GIORNO_PART=<?php echo $GIORNO_PART; ?>;
var GIORNO_PART_NO=<?php echo $GIORNO_PART_NO; ?>;





$(document).ready(function(){


    $.fn.editable.defaults.mode = 'popup'; //'popup' , 'inline
    $.fn.editable.defaults.url = URLDB + '?mod&tab=risorse';
    $.fn.editable.defaults.emptytext= '&nbsp;&nbsp;&nbsp;';
    $.fn.editable.defaults.emptyclass = 'editable-empty-mio';
    $.fn.editable.defaults.showbuttons = false;
    $.fn.editable.defaults.success= function (data) {
		if( data.error ) { alert( "Problema modifica record. Contattare supporto. MSG : " + data.error_msg ); return false; }
	};
	$.fn.editable.defaults.error= function (data) { alert("Errore di connessione al server DB"); return false; };


    c_num("#nome");          // text

    c_checkbox('#ateneo_i', 			'Scegli gli Atenei', ATENEI, ATENEIVAL);
    c_select('#gruppor', 			'Scegli un gruppo di risorse', GRUPPIR);

    c_checkbox('#servizi', 			'Scegli i servizi', SERVIZI, SERVIZIVAL);

    c_select('#orario_da', 			'Orario di inzio', ORARIO);
    c_select('#orario_a',  			'Orario di fine',  ORARIO);

    c_checkbox('#giorno_rip',     	'Scegli i giorni di attivita',  GIORNI, GIORNO_RIP);
    c_checkbox('#giorno_rip_no',  	'Scegli i giorni di fermo',     GIORNI, GIORNO_RIP_NO);

    c_note( '#giorno_part',     	'Giorni specifici inclusi' );
    c_note( '#giorno_part_no',  	'Giorni specifici esclusi' );
    c_note( '#attivo_dal_al',  	'Attio dal - al' );

    c_num("#tempo_minimo");  // text
    c_num("#elabxtempo");   // text
    c_num("#ris_tempo");    // text


    c_select('#attiva', 'Risorsa Attiva', [{value: "SI", text: "SI"}, {value: "NO", text: "NO"}] );


});

function c_checkbox(cosa, label, arra, valo)
{
	$(cosa).editable({
		type: 'checklist',
		mode: 'inline',
		showbuttons :true,
		value: valo,
		title: label,
		source: arra
	});
}

function c_select(cosa, label, arra)
{
	$(cosa).editable({
		type: 'select',
		title: label,
		source: arra
	});
}

//function c_num(cosa, label)
function c_num(cosa)
{
    $(cosa).editable({
    	type: 'text',
    	//title: label,
    });
}
function c_note(cosa, desc)
{
    $(cosa).editable({
        type: 'textarea',
        mode: 'popup',
        showbuttons :true,
        title: desc,
        rows: 5,
    });
}

function ErroreConnessioneDB(result)
{
	alert("Errore di connessione al server DB");
	return false; // sempre...
}





</script>


</head>

<body>
 <div class="container-fluid">
  <div class="row" style="margin:20px;"><!-- prima riga -->

      <?php echo navigazione(); ?>
</div>
<div class="row"><div class="col-md-offset-3 col-md-5" >

<?php

$a='<a href="#" id="%s" data-pk="%s"  data-title="%s" data-value="%s" >%s</a>';
$idk=$VAL['_key'];


 ?>
 <h2>Gestione Risorsa</h2>
    <table class="table table-bordered">
        <tr><th>ID</th><td><?php echo $idk ?></td></tr>
        <tr><th>Nome</th><td><?php  $id='nome';  printf( $a, $id,  $idk, 'nome risorsa', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Risorsa Attiva ?</th><td><?php  $id='attiva';  printf( $a, $id,  $idk, 'risorsa attiva ?', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Gruppo risorsa (indirizzo)</th><td><?php  $id='gruppor';  printf( $a, $id,  $idk, 'gruppo Risorsa', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Ateneo</th><td><?php  $id='ateneo_i';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s" ></a>', $id,  $idk, 'Atenei'  );?></td></tr>
        <tr><th>Servizi svolti</th><td><?php  $id='servizi';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s" ></a>', $id,  $idk, 'Servizi'  );?></td></tr>

        <tr><th>Orario da</th><td><?php  $id='orario_da';  printf( $a, $id,  $idk, 'Ora da', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Orario a</th><td><?php  $id='orario_a';  printf( $a, $id,  $idk, 'Ora a', $VAL[$id], $VAL[$id]   );?></td></tr>

        <?php 		$eledate='';foreach( is($VAL,'attivo_dal_al', array() ) as $DD ) $eledate .= convdata($DD[0]). " - ". convdata($DD[1])."\n";  ?>

        <tr><th>Attivo dal giorno al giorno  ( GG/MM/AAAA - GG/MM/AAAA ) due per riga</th><td><?php  $id='attivo_dal_al';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s"  data-value="%s" ></a>', $id,  $idk, 'Attivo dal giorno a giorno', $eledate  );?></td></tr>


        <tr><th>Giorni ripetuti</th><td><?php  $id='giorno_rip';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s" ></a>', $id,  $idk, 'Giorni ripetuti' );?></td></tr>
        <tr><th>Giorni ripetuti esclusi</th><td><?php  $id='giorno_rip_no';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s" ></a>', $id,  $idk, 'Giorni ripetuti esclusi'  );?></td></tr>


        <tr><th>Giorni particolari inclusi ( GG/MM/AAAA ) uno per riga</th><td><?php  $id='giorno_part';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s"   data-value="%s"></a>', $id,  $idk, 'Giorni particolari inclusi nella attività', implode("\n", convdata( is($VAL,'giorno_part', array()  ))  )   );?></td></tr>
        <tr><th>Giorni particolari esclusi ( GG/MM/AAAA ) uno per riga</th><td><?php  $id='giorno_part_no';  printf( '<a href="#" id="%s" data-pk="%s"  data-title="%s"   data-value="%s" ></a>', $id,  $idk, 'Giorni particolari esclusi dalla attività', implode("\n", convdata(  is( $VAL,'giorno_part_no', array() )  ))    );?></td></tr>



        <tr><th>Tempo di riferimento minimo (minuti)</th><td><?php  $id='tempo_minimo';  printf( $a, $id,  $idk, 'Tempo di riferimento minimo', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Numero attività per tempo di riferimento</th><td><?php  $id='elabxtempo';  printf( $a, $id,  $idk, 'Attivita eseguite nel tempo di riferimento', $VAL[$id], $VAL[$id]   );?></td></tr>
        <tr><th>Risoluzione tempo di presentazione scelta (in minuti)</th><td><?php  $id='ris_tempo';  printf( $a, $id,  $idk, 'Risoluzione tempo presentato', $VAL[$id], $VAL[$id]   );?></td></tr>

    </table>




</div></div>
<div class="row">
<div class="col-md-offset-3 col-md-1" >
<a href="<?php echo URLADM .'/risorse.php?CLONA&tab=risorse&IDR='.$idk ?>"><button type="button" class="btn btn-warning" onclick="return confirm('Confermi clonazione (duplicazione con diverso ID) si questa risorsa?');">Clona questa risorsa</button></a>
</div>
<div class="col-md-offset-1 col-md-1" >
<a href="<?php echo URLADM .'/risorse.php?DELETE&IDR='.$idk ?>"><button type="button" class="btn btn-danger"  onclick="return confirm('Confermi cancellazione definitiva di questa risorsa (attuabile SOLO se non esistono riferimenti a questa risorsa...)?');">Cancella questa risorsa</button></a>
</div>
<div class="col-md-offset-1 col-md-1" >
<a href="<?php echo URLADM .'/crud.php#miotab_A5' ?>"><button type="button" class="btn btn-success"  >Elenco risorse</button></a>
</div>
</div>
<br><br>
		<script src="<?php echo URLIB ?>/filter/dynamitable_nosort.jquery.js"></script>
</body>
</html>
