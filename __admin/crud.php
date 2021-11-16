<?php

//http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.php


ini_set('session.gc_maxlifetime', 36000);
session_set_cookie_params(36000);



require_once('config.php');
require_once('lib_simple.php');


session_start();

if( !isset($_SESSION['Utente']) ) {	header('Content-type: application/json');	echo json_encode(array( 'error' => true,  'success' => false,  'error_msg' => "Sessione scaduta ...Refresh pagina principale"));	die('');}

$utente=$_SESSION['Utente'];
$ruolo=$_SESSION['Ruolo'];

if($utente=='dummy') die('utente non autorizzato');


if( isset( $_GET['newrisorsa'] ) ) $DATI=QUERYDB(URLDB.'?new&tab=risorse');


$ORTAB=array(1=>'atenei', 2=>'gruppir', 3=>'servizi', 4=>'utenti');


// qua definisci l'input per i singolo campo. Se non definisci niente e' default .. da settare a mano anche in javascript.. stessa cosa..
//$TIPO[0]['mark']['tipo']='SELECT'; $TIPO[0]['mark']['val']=array(0=>'ok', 1=>'giallo', 2=>'rosso'); $TIPO[0]['mark']['ID']=1;

$listaDB=json_encode(array_merge($ORTAB,array('risorse')));


$DATI=QUERYDB(URLDB.'?get_all_crud_sort='.$listaDB);

// il nome dei campi lo fisso.. in un documento non sono fissati sul DB..
//$TABE[0]=array('_key', 'sede', 'descrizione', 'indirizzo'); // sedi 
$TABE[1]=array('_key', 'ateneo', 'descrizione', 'ordine');  // atenei
$TABE[2]=array('_key', 'gruppo_risorsa', 'indirizzo'); // gruppo risorsa  gruppir
$TABE[3]=array('_key', 'tipo_servizio', 'nome', 'descrizione', 'descrizione_ext', 'aperto_da', 'aperto_a', 'giorni_p', 'giorni_vis', 'multipla_p', 'controllo_matr', 'ore_canc', 'stampa_delega'); // servizi
$TABE[4]=array('_key', 'login','nome', 'email', 'ruolo');  // utenti


// qua definisci l'input per i singolo campo. Se non definisci niente e' default .. da settare a mano anche in javascript.. stessa cosa..
$TIPO[4]['ruolo']['tipo']='SELECT';
$TIPO[4]['ruolo']['val']=array('superadmin'=>'superadmin', 'admin'=>'admin', 'operatore'=>'operatore');
$TIPO[4]['ruolo']['ID']=1;



$TIPO[2]['indirizzo']['tipo']='TEXTA';  // textarea.. non si mette nulla in JS.. non serve..
$TIPO[3]['tipo_servizio']['tipo']='TEXTA';  // textarea.. non si mette nulla in JS.. non serve..
$TIPO[3]['nome']['tipo']='TEXTA';  // textarea.. non si mette nulla in JS.. non serve..
$TIPO[3]['descrizione']['tipo']='TEXTA';  // textarea.. non si mette nulla in JS.. non serve..
$TIPO[3]['descrizione_ext']['tipo']='TEXTA';  // textarea.. non si mette nulla in JS.. non serve..


$TIPO[3]['multipla_p']['tipo']='SELECT';
$TIPO[3]['multipla_p']['val']=array('SI'=>'SI', 'NO'=>'NO');
$TIPO[3]['multipla_p']['ID']=2;

$TIPO[3]['controllo_matr']['tipo']='SELECT';
$TIPO[3]['controllo_matr']['val']=array('SI'=>'SI', 'NO'=>'NO');
$TIPO[3]['controllo_matr']['ID']=3;

$TIPO[3]['stampa_delega']['tipo']='SELECT';
$TIPO[3]['stampa_delega']['val']=array('SI'=>'SI', 'NO'=>'NO');
$TIPO[3]['stampa_delega']['ID']=4;


$TIPO[3]['tipo_servizio']['tipo']='SELECT';
$TIPO[3]['tipo_servizio']['val']=array("Applicativi e amministrazione digitale" => "Applicativi e amministrazione digitale",
"Approvvigionamento e contratti" => "Approvvigionamento e contratti",
"Benefici agli studenti" => "Benefici agli studenti",
"Controllo di gestione" => "Controllo di gestione",
"Direzione generale" => "Direzione generale",
"Gestione risorse umane" => "Gestione risorse umane",
"Informazione, comunicazione, cultura e sport" => "Informazione, comunicazione, cultura e sport",
"Qualita\'a e sicurezza" => "Qualita\'a e sicurezza",
"Residenze" => "Residenze",
"Risorse economico-finanziarie" => "Risorse economico-finanziarie",
"Ristorazione" => "Ristorazione",
"Servizio tecnici manutentivi" => "Servizio tecnici manutentivi",
"Sistemi informatici" => "Sistemi informatici",
"Trasparenza e Anticorruzione" => "Trasparenza e Anticorruzione",
"Altro Servizio" => "Altro Servizio" );
$TIPO[3]['tipo_servizio']['ID']=5;





$ACTION='<a class="add" title="OK" data-toggle="tooltip"><i class="glyphicon glyphicon-floppy-saved"></i></a>' .
      '<a class="edit" title="Modifica" data-toggle="tooltip"><i class="glyphicon glyphicon-pencil"></i></a>' .
      '<a class="delete" title="Cancella" data-toggle="tooltip"><i class="glyphicon glyphicon-trash"></i></a>';

//sdie($_GET);


?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prenotazioni</title>

      <link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URLADM ?>/crud.css">
      <script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
      <script src="<?php echo URLIB ?>/bootstrap/js/bootstrap.min.js"></script>



<script type="text/javascript">

var URLDB='<?php echo URLDB; ?>';
var actions = '<?php echo $ACTION; ?>';
var row=[ <?php echo CreaModelloRigaInput(); ?> ];
</script>

<script src="<?php echo URLADM ?>/crud<?php echo $JS_EXT?>.js?ver=<?php echo ___VERSION__ ?>"></script>

</head>

<body>
 <div class="container-fluid">
  <ul class="nav nav-tabs  navbar-left">
    <li class="active">
    <li><a data-toggle="tab" href="#A1">Atenei</a></li>
    <li><a data-toggle="tab" href="#A2">Gruppi</a></li>
    <li><a data-toggle="tab" href="#A3">Servizi</a></li>
    <li><a data-toggle="tab" href="#A4">Utenti</a></li>
    <li><a data-toggle="tab" href="#A5">Risorse</a></li>
  </ul>

  <ul class="nav nav-tabs  navbar-right">
    <li><a href="<?php echo URLADM ?>">Torna alle pagina Principale</a></li>
    <li><a href="<?php echo URLADM ?>/?quit">Esci</a></li>
  </ul>


<div class="row"><div class="col-md-12" >


  <div class="tab-content">

    <div id="A1" class="tab-pane fade"><?php CreaCrud("Atenei", 1 )?></div>
    <div id="A2" class="tab-pane fade"><?php CreaCrud("Gruppi", 2 )?></div>
    <div id="A3" class="tab-pane fade">
    <br>
    <pre>
    Legenda campi:

       servizio       : Elenco delle tipologie di servizio disponibili. Usato come filtro in caso di molti servizi
       nome           : Nome del servizio, usato per elenco e per uso interno
       descrizione    : Nome del servizio realmente visualizzato dall'utente nel menu' a discesa
       aperto_da / a  : Intervallo di date (in formato GG/MM/AAAA) da cui il servizio e' attivo. Se data attuale fuori da questo range il servizio NON viene visto in prenotazione
       giorni_p       : Numero giorni in cui viene data la possibilità di prenotare, a partire dalla data odierna + giorni_vis. Se siamo fuori dal range di apertura dal primo giorno utile + giorni_vis
       giorni_vis     : Numero giorni sommati alla data odierna (o la data di inzio servizio) da cui il servizio viene visualizzato come disponibile
       multipla_p     : Possibilita' per una matricola O un codice fiscale, di prenotazione multipla di un servizio
       controllo_matr : Controllo se la matricola e' presente o meno nel file delle matricole. Se non e' presente il servizio non viene visualizzato (senza errori..)
       ore_canc       : Ore dalla prenotazione ricevuta entro il quale e' possibile cancellare una prenotazione. Se si cancella entro queste ore NON si permette la cancellazione. 0 per poter cancellare sempre
       stampa_delega  : Nel pdf della prenotazione viene o meno stampato il form di delega alla fruizione del servzio
    </pre>


    <?php CreaCrud("Servizi", 3 )?></div>
    <div id="A4" class="tab-pane fade"><?php CreaCrud("Utenti", 4 )?></div>
    <div id="A5" class="tab-pane fade"><?php

    ?>

<div class="row"><div class="col-md-12" >

  <table class="table table-bordered  js-dynamitable" id="risorse" style="font-size:90%; background:white; margin:10px 10px 0px 0px">
    <thead>
      <?php
          $lista=array( 	'_key'=>'id', "nome"=>'nome risorsa', 'attiva'=>'attiva', 'servizi'=>'Servizi svolti', "gruppor"=>'Gruppo risorsa (indirizzo)', "ateneo"=>'Ateneo',
                  "orario_da"=>'Ora da', "orario_a"=>'Ora a', "giorno_rip"=>'Giorni ripetuti', "giorno_rip_no"=>'Giorni ripetuti esclusi',
                  "giorno_part"=>'Giorni particolari inclusi', "giorno_part_no"=>'Giorni particolari esclusi', "attivo_dal_al"=>'Attivo dal giorno a giorno',
                  "tempo_minimo"=>'Tempo di riferimento minimo', "elabxtempo"=>'# Attivita eseguite', "ris_tempo"=>'Risoluzione tempo presentato' );

          //$tr1='<tr><th class="cx"> # </th>';$tr2='<tr><th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>';
          $tr1='<tr>';$tr2='<tr>';
          foreach( $lista as $k=>$c )
          {
            if( $c == '_key') continue;
            $tr1 .= "<th>".$c."</th>";
            $tr2 .= '<th><input class="js-filter form-control input-sm" type="text" value="" size="5" placeholder="ricerca...."></th>';

          }
          $tr1 .= "<th>Azioni</th><tr>\n";
          $tr2 .= "<th><a href='".URLADM ."/crud.php?newrisorsa#miotab_A5'><button type='button' class='btn btn-info' ide='' ><i class='glyphicon glyphicon-plus'></i> Nuovo</button></a></th><tr>\n";




          echo $tr1;
          echo $tr2;
      ?>

  </thead>
  <tbody>
<?php

//$lista=array( 	'_key',  "nome", 'attiva', 'servizi',"gruppor", "ateneo", "orario_da", "orario_a", "giorno_rip", "giorno_rip_no",	"giorno_part", "giorno_part_no", "attivo_dal_al",	"tempo_minimo", "elabxtempo", "ris_tempo" );

	$std='<td>%s</td>';
	$stdmax='<td  style="width:180px">%s</td>';
	$stdc='<td class="cxc" style="width:30px">%s</td>';



    foreach($DATI['risorse'] as $nr => $r)
    {

      	echo "<tr>";

      			printf($std, is($r,'_key') );
                printf($std, is($r,'nome') );
                printf($std, is($r,'attiva') );
      			printf($std, implode(' , ', is($r,'servizi', array()) ) );
      			printf($std, is($r,'gruppor' ) );
      			printf($std, implode(' , ', is($r,'ateneo_i', array()) )    );
      			printf($std, is($r,'orario_da') );
      			printf($std, is($r,'orario_a')  );


                printf($std, implode(' , ', is($r,'giorno_rip',array()) ));
                printf($std, implode(' , ', is($r,'giorno_rip_no', array()) ));

      			$x=array(); foreach( is($r,'giorno_part',array()) 		as $y  ) $x[] = dataita($y);   printf($std, implode(' , ', $x ));
      			$x=array(); foreach( is($r,'giorno_part_no',array()) 	as $y  ) $x[] = dataita($y);   printf($std, implode(' , ', $x ));

      			$eledate='';foreach( is($r,'attivo_dal_al',array()) as $DD ) $eledate .= dataita($DD[0]). " - ". dataita($DD[1])."\n";


      			printf($stdmax, $eledate);
      			printf($stdc, is($r,'tempo_minimo'));
      			printf($stdc, is($r,'elabxtempo'));
      			printf($stdc, is($r,'ris_tempo'));
				printf($stdc, '<a href="'.URLADM .'/risorse.php?VEDI&IDR='.$r['_key'].'"><i class="glyphicon glyphicon-pencil"></i></a>');

		echo "</tr>";
    }
?>


  </tbody>
  </table>


</div></div>

  </div>


    </div></div></div>
    <script src="<?php echo URLIB ?>/filter/dynamitable_nosort.jquery.js"></script>
</body>
</html>



<?php

function CreaCrud($label, $ideOrTab)
{
  global $ACTION, $ORTAB, $DATI, $TABE, $TIPO, $ruolo;

  if( ! accessibile($ORTAB[$ideOrTab].'_m', $ruolo) ) return;

  $dove= $ORTAB[$ideOrTab];
  $campi=$TABE[$ideOrTab];
  $valori=(isset($DATI[ $ORTAB[$ideOrTab] ])  ) ? $DATI[ $ORTAB[$ideOrTab] ]: array();

  $A1=($ideOrTab==3)?' table3 ':'';
  $A2=($ideOrTab==4)?' table4 ':'';

  ?>
  <div class="table-wrapper">
  <div class="table-title">
  <div class="row">
  <div class="col-sm-8"><h2><?php echo $label?></h2></div>
  <div class="col-sm-4">
  <button type="button" class="btn btn-info add-new" ide="<?php echo $ideOrTab ?>" dove="<?php echo $dove?>"><i class="fa fa-plus"></i> Aggiungi nuovo</button>
  </div>
  </div>
  </div>
  <table class="table table-bordered  js-dynamitable <?php echo $A1.$A2 ?>" id="<?php echo $dove?>">
    <thead>
      <?php
          $tr1='<tr style="font-size:80%"><th class="cx"> # </th>';$tr2='<tr><th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>';
          foreach( $campi as $c ) {
            if( $c == '_key') continue;
            $tr1 .= "<th>".$c."</th>";
            $tr2 .= '<th><input class="js-filter form-control input-sm" type="text" value="" size="5" placeholder="ricerca...."></th>';

          }
          $tr1 .= "<th>Azioni</th><tr>\n";
          $tr2 .= "<th></th><tr>\n";
          echo $tr1;
          echo $tr2;
      ?>

  </thead>
  <tbody>
    <?php	foreach($valori as $v)
        {
          echo '<tr idx="'.$v['_key'].'">';
          echo "<td class='cx'>".$v['_key']."</td>";
          //foreach($v as $campo => $valo) // tra tutti gli elementi del documento
	      foreach($campi as $campo) // dico io quali elementi .. da $TABE
          {
               	$valo=   isset( $v[ $campo ] ) ? $v[ $campo ] : '' ;
      			if( $campo != '_key' )
            	{
              		if(! isset( $TIPO[$ideOrTab][$campo]['tipo'] ) ) 	echo  "<td name=".$campo.">".convdata($valo)."</td>";
              		else
              		{
                		if($TIPO[$ideOrTab][$campo]['tipo'] == 'SELECT' )
                		{
                			if( isset(  $TIPO[$ideOrTab][$campo]['val'][$valo]  ) )  	$valore_sel=$TIPO[$ideOrTab][$campo]['val'][$valo];
                			else 														$valore_sel='';

                  			echo  "<td name=".$campo." TIPO_SEL='".$TIPO[$ideOrTab][$campo]['ID']."' SEL_valule='".$valo."'>".$valore_sel."</td>";
                		}
                		elseif ($TIPO[$ideOrTab][$campo]['tipo'] == 'TEXTA' ) echo  "<td name=".$campo."  tipo_texta=\"1\" >".convdata($valo)."</td>";
                		else echo  "<td name=".$campo." ></td>";

              		}
            	}
           }

          $A2=($ideOrTab==3)?'<a class="upload" title="Upload Matricole" data-toggle="tooltip" href="'.URLADM.'/import_matricola.php?ID='.$v['_key'].'"><i class="glyphicon glyphicon-import"></i></a>':'';
          $A3=($ideOrTab==3)?'<a class="view" title="Vedi matricole caricate" data-toggle="tooltip" href="'.URLADM.'/import_matricola.php?VIEW='.$v['_key'].'" target="_blank"><i class="glyphicon glyphicon-eye-open"></i></a>':'';

          $A4=($ideOrTab==4)?'<a class="upload" title="Genera e Invia Password" data-toggle="tooltip" target="_blank"  href="'.URLADM.'/varie.php?InviaPassword&login='.$v['login'].'&utente='.$v['nome'].'&email='.$v['email'].'&key='.$v['_key'].'"><i class="glyphicon glyphicon-rub"></i></a>':'';

          echo '<td>'.$ACTION.$A2.$A3.$A4.'</td>';

          echo "<tr>\n";

        }
    ?>

  </tbody>
  </table>
  </div>

<?php

}



function CreaModelloRigaInput()
{
  global $TABE, $TIPO;

  $o='';

  $ultimo=array_key_last ( $TABE );  // ultimo elemento.. parto da 0 e vado all'utimo.. se non sono settati gestisco la cosa.. elemento array javascript nullo...


  for($k=0; $k<= $ultimo; $k++)
  //foreach( $TABE as $k => $v  )
  {


    if( isset( $TABE[$k]) )
    {
            $v=$TABE[$k];
            $o .= "'<tr><td class=\"cx\"></td>' +\n";
            foreach( $v as $valo )
            if($valo != '_key' )
            {
                    if( !isset($TIPO[$k][$valo]['tipo']) )
                    {
                            $o .= sprintf( "'<td name =\"%s\"><input type=\"text\" class=\"form-control\" name=\"%s\" id=\"%s\"></td>' +\n", $valo, $valo, $valo  );
                    }
                    else
                    {
                            if( $TIPO[$k][$valo]['tipo'] =='SELECT' )
                            {
                                $o .= sprintf("'<td  name=\"%s\" TIPO_SEL=\"%s\" SEL_valule=\"0\"   ><select class=\"form-control\" name=\"%s\" id=\"%s\">", $valo, $TIPO[$k][$valo]['ID'], $valo, $valo );
                                foreach( $TIPO[$k][$valo]['val'] as $valore => $lab ) $o .= sprintf(  "<option value=\"%s\">%s</option>", $valore, $lab  );
                                $o .=  "</select></td>' +\n";
                            }
                            if($TIPO[$k][$valo]['tipo'] == 'TEXTA')
                            {
                            	$o .= sprintf( "'<td name =\"%s\"><input type=\"text\" tipo_texta=\"1\" class=\"form-control\" name=\"%s\" id=\"%s\"></td>' +\n", $valo, $valo, $valo  );
                            }
                    }
            }
            $o .= "'<td>' + actions + '</td>' + '</tr>',\n";
    }
    else
    {
            $o .= ",\n";
    }
  }

  return $o;

}


?>
