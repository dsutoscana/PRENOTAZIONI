<?php



function l($s)
{
    global $_SESSION;

    if( isset($_SESSION['lang']) ) $lang=$_SESSION['lang'];
    else $lang = 'IT';

//echo $lang; die();


    //if( $lang == 'IT' ) return $s;    // da attivare...

    $L=array();

    $L['EN']['ciao']='hello';
    $L['EN']['Elenco dei servizi disponibili per la prenotazione ON LINE']='List of available services for ON LINE prenotation';
    $L['EN']['rilascio tessera per accedere alla mensa']='rilascio tessera per accedere alla mensa';
    $L['EN']['Rilascio certificato esami']='Rilascio certificato esami';
    $L['EN']['rilascio borse di studio']='rilascio borse di studio';
    $L['EN']['verifica in itinere esami']='verifica in itinere esami';
    $L["EN"]["Prenotazione SERVIZI on line"]="";
$L["EN"]["Elenco dei servizi disponibili per la prenotazione ON LINE"]="List of available services for ONLINE Reservation";
$L["EN"]["rilascio tessera per accedere alla mensa"]="Release card for the access to the canteen";
$L["EN"]["Rilascio certificato esami"]="Release examen certificate";
$L["EN"]["rilascio borse di studio"]="Release Scholarships";
$L["EN"]["verifica in itinere esami"]="verification in itinere examinations";
$L["EN"]["Inizia la procedura"]="Start";
$L["EN"]["Prenotazione SERVIZI on line"]="Online Booking Service";
$L["EN"]["Procedura Gestione Prenotazioni"]="Booking Procedure";
$L["EN"]["Nome"]="Name";
$L["EN"]["Nome studente"]="Name";
$L["EN"]["Non sono ammessi caratteri strani"]="Please use simple characters only";
$L["EN"]["Cognome"]="Surname";
$L["EN"]["Cognome studente"]="Surname";
$L["EN"]["Matricola"]="Student ID Number";
$L["EN"]["Matricola studente"]="Student ID Number";
$L["EN"]["Sono ammessi solo numeri o caratteri semplici. No spazi. Se non hai matricola universitaria inserisci 0"]="Please use simple characters or numbers only. 0 if you are not student";
$L["EN"]["Codice Fiscale"]="Italian TAX Code";
$L["EN"]["Codice fiscale studente"]="Italian TAX code";
$L["EN"]["Codice fiscale ERRATO"]="Invalid Italian TAX code (16 Char)";
$L["EN"]["Email studente"]="Email";
$L["EN"]["Formato email non riconosciuto"]="Invalid Email";
$L["EN"]["Cellulare"]="Mobile";
$L["EN"]["Cellulare studente per eventuali comunicazioni"]="Mobile phone";
$L["EN"]["Devi accettare l'informaitiva  altimenti non e' possibile procedere con l'acquisizione dei tuoi dati"]="Devi accettare l'informaitiva  altimenti non e' possibile procedere con l'acquisizione dei tuoi dati";
$L["EN"]["Dichiaro di aver preso visione della"]="I read";
$L["EN"]["Devi accettare il trattamento dei tuoi dati altimenti non e' possibile procedere con l'acquisizione dei tuoi dati"]="Devi accettare il trattamento dei tuoi dati altimenti non e' possibile procedere con l'acquisizione dei tuoi dati";
$L["EN"]["Acconsento al trattamento dei dati"]="I allow to use the informations above";
$L["EN"]["Inserisci i numeri che vedi.."]="Insert the number that you see";
$L["EN"]["Inserisci i 6 numeri che vedi qua..."]="Insert six numbers that you see here..";
$L["EN"]["Ricerca data"]="data search";
$L["EN"]["Servizio desiderato"]="Desired Service";
$L["EN"]["Servizo scelto"]="Select service";
$L["EN"]["Ateneo / Tipologia utente"]="University / User type";
$L["EN"]["Indirizzo sportello dove ricevere il servizio"]="Preferred service location";
$L["EN"]["Date"]="dates";
$L["EN"]["disponibili"]="availables";
$L["EN"]["Orario"]="Hour";
$L["EN"]["Riassunto informazioni inserite"]="Resume information iserted";
$L["EN"]["Inserisci nuovamente la email cui inviare la prenotazione. "]="Insert Again the email where send the booking";
$L["EN"]["CONTROLLARE"]="VERIFY";
$L["EN"]["che sia corretta"]="thst it's right";
$L["EN"][" a questo indirizzo verranno inviate conferma e codice per cancellazione<br>(L'indirizzo di email deve essere uguale a quella inserita prima..)"]=" at this email address will be send the confirmation e the code for the deletion<br>(The email addrress must be equal at the address inserted before..)";
$L["EN"]["Prenotazione creata : ID"]="Bookin created . ID";
$L["EN"]["Riceverai mail all'indrizzo"]="You will be receiced an email to the address";
$L["EN"]["con la conferma e le istruzioni di dettaglio"]="with the confirmation e the detailed instructions";
    $L['EN']['prenota!']='Booking!';
    $L['EN']['Dati generali identificazione']='Personal informations';
    $L["EN"]["Non sono disponibili indirizzi di sportelli per questo servizio e/o ateneo ..."]="No location available for these services... ";
    $L["EN"]["Non sono disponibili indirizzi per questo servizio e ateneo ..."]="Not address available for these services... ";
    $L["EN"]["prenota!"]="Booking!";
    $L["EN"]["prenotazioni spazi"]="Booking espaces";
    $L['EN']['Servizio']='Service';
    $L['EN']['Descrizione e note']='Description and notes';
    $L['EN']['']='';
    $L['EN']['']='';
    $L['EN']['']='';
    $L['EN']['']='';


    $file="/tmp/lang.php";
    if( !isset( $L['EN'][$s] )  )
    {
      $fh=fopen($file, "a+");

      fprintf($fh, '$L["EN"]["%s"]="";'."\n", str_replace ( '"' , '\"' , $s) );
      fclose($fh);



    }





    if( ! isset( $L[$lang][$s]  )  ) return $s;
    else return $L[$lang][$s];


}























 ?>
