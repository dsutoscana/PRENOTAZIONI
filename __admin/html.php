<?php



function intestazione()
{
	?>
	<!DOCTYPE html>
		<html lang="it">
  <head>
    	<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>Gestione attivitia'</title>

    <!-- Bootstrap-->
    		<link href="<?php echo URLIB ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
      		<link href="<?php echo URLIB ?>/filterT/css/bs-table.css" type="text/css" rel="stylesheet">
      		<link href="<?php echo URLIB ?>/editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">

  			<script src="<?php echo URLIB ?>/jquery-1.12.4.js"></script>
      		<style>
.centra {   text-align: center;   }
.sx {   text-align: left;   }
      		</style>

  </head>

	<?php
}



function navigazione()
{
   global $ruolo, $_SESSION;
   ?>

<div style="font-size:14px">
      <ul class="nav nav-tabs">
        <li><a href="<?php echo URLADM ?>">Principale</a></li>
        <li class="dropdown" role="presentation">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Prenotazioni <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <?php if( accessibile('crea_prenotazione', $ruolo) ) {?><li><a href="<?php echo URLADM .'/prenotazioni.php?dispo=NO'?>">Crea</a></li><?php }?>
            <?php if( accessibile('disponibilita', $ruolo) ) {?><li><a href="<?php echo URLADM .'/prenotazioni.php?dispo=SI'?>">Disponibilità</a></li><?php }?>
			<?php if( accessibile('ricerca_prenotazione', $ruolo) ) {?><li><a href="<?php echo URLADM .'/ricerca.php?storico=NO'?>">Ricerca</a></li><?php }?>
			<li role="separator" class="divider"></li>
			<?php if( accessibile('ricerca_archivio', $ruolo) ) {?><li><a href="<?php echo URLADM .'/ricerca.php?storico=SI'?>">Cerca Nell'archivio storico</a></li><?php }?>
          </ul>
        </li>
        <li class="dropdown"  role="presentation">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configura <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <?php if( accessibile('crud_atenei', $ruolo) ) {?><li><a href="<?php echo URLADM .'/crud.php#miotab_A1'?>">Atenei</a></li><?php }?>
            <?php if( accessibile('crud_gruppir', $ruolo) ) {?><li><a href="<?php echo URLADM .'/crud.php#miotab_A2'?>">Gruppi risorse</a></li><?php }?>
            <?php if( accessibile('crud_servizi', $ruolo) ) {?><li><a href="<?php echo URLADM .'/crud.php#miotab_A3'?>">Servizi</a></li><?php }?>
            <?php if( accessibile('crud_risorse', $ruolo) ) {?><li role="separator" class="divider"></li>
            <li><a href="<?php echo URLADM .'/crud.php#miotab_A5'?>">Risorse</a></li><?php }?>
			<li role="separator" class="divider"></li>
            <?php if( accessibile('crud_utenti', $ruolo) ) {?><li><a href="<?php echo URLADM .'/crud.php#miotab_A4'?>">Operatori</a></li><?php }?>
			<?php if( accessibile('vedi_log_oper', $ruolo) ) {?><li role="separator" class="divider"></li>
			<li><a href="<?php echo URLADM .'/varie.php?vedilogoper'?>" target="_blank">Vedi LOG Operatori</a></li><?php }?>
			<?php if( accessibile('vedi_log_studenti', $ruolo) ) {?><li><a href="<?php echo URLADM .'/varie.php?vediloguser'?>" target="_blank">Vedi LOG Studenti</a></li><?php }?>
			<?php if( accessibile('archivia_prenotazioni', $ruolo) ) {?><li role="separator" class="divider"></li>
			<li><a href="<?php echo URLADM .'/varie.php?EXEarchiviastorico'?>" target="_blank" style="color:red">Archivia Prenotazioni</a></li><?php }?>

			<?php if( isset($_SESSION['KEYlogin'])  ) { ?>
				<li role="separator" class="divider"></li>
				<?php if( accessibile('modifica_password', $ruolo) ) {?><li><a href="<?php echo URLADM .'/varie.php?modificaPassword'?>" target="_blank" >Modifica Password</a></li><?php }?>
			<?php } ?>

          </ul>
        </li>
        <li><a href="<?php echo URLADM .'/?quit'?>">Esci</a></li>
      </ul>
</div>



<?php
}



function intestazionepub($imma=null)
{
	global $config;
	?>
	<div class="row" style="padding: 5px 0;margin: 0;background: #f2f2f2;border-bottom: 1px solid #ddd;"><!-- prima riga -->

       <div class="top">
           <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-6">
                    <div style="color: #d9232d;font-size: 16px; font-weight: bold; padding: 0px 20px;">
                         <a href="/inizio.php?lang=IT"  style="color: #d9232d;font-size: 16px; font-weight: bold;">IT</a> | <a href="/inizio.php?lang=EN" style="color: #d9232d;font-size: 16px; font-weight: bold;" >EN</a> </div>
               </div>
               <div class="col-md-6 col-sm-6 col-xs-6"><a href="<?= $config['CONTATTI'] ?>"><button type="button" class="btn btn-danger  pull-right">Contatti</button></a></div>
           </div>
       </div>
   </div>


   <div class="row" style="margin:20px;">
       <div class="col-md-5 col-sm-5 col-xs-12">
               <a href="/"><img src="/logo.jpg" alt="" width="100%"></a>
       </div>
       <div class="col-md-6 col-sm-6 col-xs-12">
               <h2><?php $s='Prenotazione SERVIZI on line'; echo l($s); ?></h2>
       </div>
   </div>



   <?php  if($imma != 'NO') { ?>
   <div class="row">   <div class="col-lg-12"> <img src="/1.jpg" alt="prenotazioni" style="width:100%" ></div></div>
<?php } ?>



	<?php
}


function tabella_prenotazioni($PRENOTAZIONI)
{
	//js-dynamitable
	global $_SESSION;

	if($_SESSION['DBprenotazioni'] == 'prenotazionistoriche') $color='style="color:#c55612"';
	else $color='';

	?>

	<?php if(count($PRENOTAZIONI)>100 ) {
		echo "<p>Sono presentei ".count($PRENOTAZIONI)." prenotazioni individuate. Visualizzo solo le prime <span style='color:red'>100</span> !</p>";
	}
	?>
	<div id="table-data1">
	<table  class="table table-hover" id="tabpreno"  <?= $color ?> >
	<tr><th>ID</th><th>Nome</th><th>Cognome</th><th>Matricola</th><th>Ateneo</th><th>email</th><th>Cod.Fisc.</th><th>Servizio</th><th>ID Risorsa</th><th>Indirizzo R.</th><th>data</th><th>ora</th><th>slot</th><th>Azione</th></tr>
<!--	<tr>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th><input class="js-filter form-control input-sm" type="text" value="" size="1" placeholder=""></th>
		<th></th>
	</tr>
-->

	<?php
	$c=0;
	foreach($PRENOTAZIONI as $P )
	{
		if($c>=100) break;
		echo "<tr>";
		echo "<td>".$P['_key']."</td>";
		echo "<td>".$P['nome']."</td>";
		echo "<td>".$P['cognome']."</td>";
		echo "<td>".$P['matricola']."</td>";
		echo "<td>".$P['ateneo']."</td>";
		echo "<td>".$P['email']."</td>";
		echo "<td>".$P['cf']."</td>";
		echo "<td>".$P['servizio']."</td>";
		echo "<td>".$P['IDR']."</td>";
		echo "<td>".$P['gruppir']."</td>";
		echo "<td>".$P['data']."</td>";
		echo "<td>".$P['orada']."</td>";
		echo "<td>".$P['slot']."</td>";



		$url=URLADM.'/ricerca.php?dele&tab=prenotazioni&ID='.$P['_key'];

		echo "<td>";
		if($_SESSION['DBprenotazioni']=='prenotazioni') { // mail solo se DB prenotazioni  NO sullo storico
			echo "<a href='".$url."' style='color:red'  onclick=\"return confirm('Confermi CANCELLAZIONE COMPLETA di questa prenotazione in modo irreversibile?');\">CANC</a>\n";
		}
		echo "<a href='".$url."&NO' style='color:#ff00fc'  onclick=\"return confirm('Confermi CANCELLAZIONE COMPLETA di questa prenotazione in modo irreversibile?');\">CANC no mail</a></td>";

		echo "</tr>";
		$c++;
	}
	?>


	</table>
	</div>
	<?php


}


function CreaMailTemplate($body)
{

	$aa='<img style="" src="https://portale.it/img/logo.jpg" saveddisplaymode="">';
	$ini='<html lang="it"><head>
    <meta charset="utf-8">
    <title>comunicazione</title></head><body>
	<div style="font-family: tahoma,new york,times,serif; font-size: 12pt; color: #000000">

		<style>
			img {text-align:center; max-width:400px; margin-bottom:10px;}
			p {color:#000;margin-bottom:5px;}
			.note {font-size: 11px;font-style: italic;}
		</style>

	<br><br>
		<div style="max-width: 600px; padding: 10px; margin: 30px auto; border: 8px solid white;">
			<p style="text-align: center;"><img src="data:image/jpg;base64,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bsdSCg4tKH59OJ9iNf5+v81X+OqFIGDeSnxwuYA7VkJu4xxUWo2Gu6fmigxgDX+Or/HV/jKMqJcTX/wzgEwD4S0/TkAi1i/4onXNIlVotzHSsfSGy7V3DXaQHhVhbXOAw6xm1Mexs2jP9lYFHCTm2q0u0oIyCwZdBK1GBlHTGugx4+9EnpiyOrf5erVXIwCWd9tKWBTcXBOO1X2+NCbga8/dpChybe1v0qfJIC+9M4qyZnsqEOf8OYjWrwKDBBgnw80BRyx80ojemohZQbxu1OCGza0dOetHAsDx0AwRrUCpLdwU0pyp1WE3Kg57wbKlb6GLro1Ywc1baPcOlHu/C0QAXiaVZ9FzPca8OayYah0wfnNXk9CuBsYh4/v0ZMgf6VS2VS2VS2VS2VNfMUNcUgEHK+ae/FS2VS2VS2VS2VSpcboImCP+OgMxSy4eL5mhMTeuvX6ZZtJZVrEloNte9XTPpeejV9ij+5SkiYCKLmk0QjyyIMzUcO+1lWda8fenxd5xhkSeaJBcZQ4c+aIXkswzQtr+ymkC7GW34bUxE7BINbpqeKP+nhzomL2ayKqC0d6BMxW896g23JMea1igJoQ64IonRKBipZkqrvOaBMInNNr0MkjISNQS27VO/QuxCYpQTUZm0c0N8+CzM5q0iMP+l//aAAwDAQACAAMAAAAQ8886e888888888888888888888888888887/AC9PPPKBOOHFJHFPNNMOOPPONPPPPPBPv/8AzzzzhzggxwCxzhTxQSgRwTChTzwD5RJ8wLzyzywwyTTyTzwzyxjnxywXzzwYS1Y9UbzzzzzzyzigiihQwhW5khqaj/8AOIGpOAK8888888888888888s88r888888c8g8888888888888888888888888888/8QAJxEBAAECBAQHAQAAAAAAAAAAAREAITFBUWFAcZHRECBQgbHB8KH/2gAIAQMBAT8Q9CjjyEpNYY60+EVPKmsMUiMPFANmhyOgMtqANryFxNTTUy8HYVlAMVpyM2gQQCxrGzGdRBScCE0jAjyQxZbyOC4GV/qlCUelQLg0JT2HB5MajUbQGVGFDQoZBoIi57MExsRfGrUM53+tJODdBDo8CTK9pgTlNIzr4slojrjBrjSOSLEx9Li/znFLgXIJtCri7fUbwSVEgXb6pAycBFgvK6uEYstq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"/></p>
    			<p>&nbsp;</p>';


	$end='<p><br><br>Questa e` una mail generata automaticamente, si prega di non rispondere</p>
		</div>
	</div>
	</body>
	</html>';

	return $ini.$body.$end;

}


function footer()
{
	?>

<style>



footer, #sub-footer{  	background:#3c3c3c !important;color:#fff !important; }
footer a,footer h4, #sub-footer a{color:#fff !important;}
#sub-footer{
	border-top: 1px solid #ddd;
	background:#f7f7f7;
}

/*footer{ padding:20px 0 0 0; }*/
footer a { 	color:#666; }
footer a:hover {	color:#444;}
footer h1, footer h2, footer h3, footer h4, footer h5, footer h6 { 	color:#666;}
footer address { 	line-height:1.6em; }
footer h5 a:hover, footer a:hover { 	text-decoration:none; }
ul.social-network {
	list-style:none;
	margin:0;
}
ul.social-network li {
	display:inline;
	margin: 0 5px;
}
footer ul.social-network li i { 	font-size: 1.3em; }
#sub-footer{
	text-shadow:none;
	padding:0;
	    padding-top: 15px;
    margin: 5px 0 0 0;
}
#sub-footer p{
	margin:0;
	padding:0;
}
#sub-footer span{ }
.copyright {
	text-align:left;
	font-size:12px;
}
#sub-footer ul.social-network { 	float:right; }
ul.link-list{
	margin:0;
	padding:0;
	list-style:none;
	float: none;
}
ul.link-list li{
	float: none;
	margin:0;
	padding:2px 0 2px 0;
	list-style:none;
}
footer ul.link-list li a{ 	color:#777; }
footer ul.link-list li a:hover {  	color:#333; }

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<footer>
<div class="row">
<div class="col-md-12">
<div class="col-sm-4 col-lg-4">
<div class="widget">
<h4>Sede Legale</h4> <address>Sede legale Viale Gramsci, 36 50132 - Firenze</address>
<div>P. I. 05913670484</div>
<div>C. F. 94164020482</div>
<p> <i class="icon-envelope-alt"></i> MY_AZIENDA@MAIL.it </p>
</div>
</div>
<div class="col-sm-4 col-lg-4">
<div class="widget">
<h4>Ulteriori Informazioni</h4>
<ul class="link-list">
<li><a target="_blank" href="http://www.MY_SITE.it/servizi/SERVIZIO_1/">SERVIZIO_1</a></li>
<li><a target="_blank" href="http://www.MY_SITE.it/servizi/SERVIZIO_2/">SERVIZIO_2</a></li>
<li><a target="_blank" href="http://www.MY_SITE.it/servizi/SERVIZIO_3/">SERVIZIO_3</a></li>
</ul>
</div>
</div>
<div class="col-sm-4 col-lg-4">
<div class="widget">
<h4></h4>
<ul class="link-list">
<li><a href="https://portale.MY_AZIENDA.it/page/privacy">Privacy policy</a></li>
<li><a href="http://www.MY_SITE.it/contattaci/" target="_blank">Contact us</a></li>
</ul>
</div>
</div>
</div>
</div>
<div id="sub-footer">
<div class="row">
<div class="col-md-12">
<div class="col-lg-6">
<div class="copyright"> <p>© All Right Reserved - <a target="_blank" href="http://www.esseciesse.net"><span style="color:#0ec80e">Credits : SCS s.a.s.</span></a></p>
</div>
</div>
<div class="col-lg-6">
<ul class="social-network">
<li><a target="_blank" href="https://www.facebook.com/MY_AZIENDA/" data-placement="top" title="Facebook"><i class="fa fa-facebook"></i></a></li>
<li><a target="_blank" href="https://twitter.com/MY_AZIENDA" data-placement="top" title="Twitter"><i class="fa fa-twitter"></i></a></li>
<li><a target="_blank" href="https://twitter.com/MY_AZIENDA" data-placement="top" title="Linkedin"><i class="fa fa-linkedin"></i></a></li>
<li><a target="_blank" href="https://www.instagram.com/MY_AZIENDA/" data-placement="top" title="Pinterest"><i class="fa fa-instagram"></i></a></li>
</ul>
</div>
</div>
</div>
</div>
</footer>


	<?php
}





?>
