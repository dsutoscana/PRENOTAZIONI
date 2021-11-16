$(document).ready(function(){

	$('[data-toggle="tooltip"]').tooltip();

	//var actions = $("table td:last-child").html();

   	var hash = document.location.hash;
   	var prefix = "miotab_";
   	if (hash) { $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').tab('show'); 	}
   	$('.nav-tabs a').on('shown.bs.tab', function (e) {
       window.location.hash = e.target.hash.replace("#", "#" + prefix);
   	});


    var tipo_input = Array(2);
    tipo_input[1] = {'superadmin':'superadmin', 'admin':'admin', 'operatore':'operatore'};
    tipo_input[2] = {'SI':'SI', 'NO':'NO'};
	tipo_input[3] = {'SI':'SI', 'NO':'NO'};
	tipo_input[4] = {'SI':'SI', 'NO':'NO'};
	tipo_input[5] = {	"Applicativi e amministrazione digitale" : "Applicativi e amministrazione digitale",
	"Approvvigionamento e contratti" : "Approvvigionamento e contratti",
	"Benefici agli studenti" : "Benefici agli studenti",
	"Controllo di gestione" : "Controllo di gestione",
	"Direzione generale" : "Direzione generale",
	"Gestione risorse umane" : "Gestione risorse umane",
	"Informazione, comunicazione, cultura e sport" : "Informazione, comunicazione, cultura e sport",
	"Qualita\'a e sicurezza" : "Qualita\'a e sicurezza",
	"Residenze" : "Residenze",
	"Risorse economico-finanziarie" : "Risorse economico-finanziarie",
	"Ristorazione" : "Ristorazione",
	"Servizio tecnici manutentivi" : "Servizio tecnici manutentivi",
	"Sistemi informatici" : "Sistemi informatici",
	"Trasparenza e Anticorruzione" : "Trasparenza e Anticorruzione",
	"Altro Servizio" : "Altro Servizio" };



	// Aggiungi riga
    $(".add-new").click(function(){

			var cosa= $(this).attr('ide');
			var dove= $(this).attr('dove');
			var index = $('#'+dove+" tbody tr:first").index();

			$(".add-new").attr("disabled", "disabled");

			var idx=recuperaNewID(dove);
			if( idx == false ) { alert("Errore creazione nuovo cliente..");return false;}

    		$('#'+dove).prepend(row[cosa]);
			$('#'+dove+" tbody tr").eq(index).find(".add, .edit").toggle();
			$('#'+dove+" tbody tr").eq(index).attr('idx', idx);    // idx nel TR
			$('#'+dove+" tbody tr:first td:first").html(idx);    // idx nel TR


      		$('[data-toggle="tooltip"]').tooltip();

    });


	// OK
	$(document).on("click", ".add", function(){
		var empty = false;
		var input = $(this).parents("tr").find('input[type="text"],select,textarea[type="text"]'); // cerca tutti i campi input

/*		// forza la presenza di dati nei campi..
        input.each(function() {   // per ogni campo verifica che sia pieno e non vuoto
			if(!$(this).val())
			{
				$(this).addClass("error");  // se vuoto aggiunge la classe errore
				empty = true;
			}
			else { $(this).removeClass("error");  }
		});
		$(this).parents("tr").find(".error").first().focus();  // fuoco su quelli in errore
*/


		if(!empty) // se ok
		{
			input.each(function() {
				var i=$(this).closest("tr").attr('idx');
				var n=$(this).attr('name');
				var v=$(this).val();
				var dove = $(this).closest("table").attr('id');

				//if( dove == 'cli' && ( n == 'cliente' || n == 'sede' ) ) v=v.toUpperCase();   // maiuscolo sede e cliente ....

				if( ! Modifica(dove, i, n, v) ) { return false; };  // qua salva con jquery

				if( $(this).parent("td").attr('tipo_sel') > 0  )  // SELECT .. il parent td e' ha tipo_sel .. sei un select => prendo il valore da tipo_input
				{
                    $(this).parent("td").html(  $(this).find(":selected").text()  );
                    $(this).parent("td").attr(  'sel_value', v );

				}
				else $(this).parent("td").html(  v   );



			});  // metti in html il valore dell'imput

			$(this).parents("tr").find(".add, .edit").toggle();  // accendi o spegni   le classi edit e add
			$(".add-new").removeAttr("disabled");

		}
    });

	// Edit
	$(document).on("click", ".edit", function(){
        $(this).parents("tr").find("td:not(:last-child):not(:first-child)").each(function()  // per ogni campo del tr non l'ultimo perche' action.. ne0 il primo perche' ID
       	{

        	var n=$(this).attr('name');
			if(  $(this).attr('tipo_sel') > 0  )   //SELECT
			{

					var tipo_sel=$(this).attr('tipo_sel');
					var obj =  tipo_input[ tipo_sel ];
					var txt='<select class="form-control" name="' + n + '" id="' + n + '">';
					for (var prop in obj)
					{
				    	    if(!obj.hasOwnProperty(prop)) continue;  // non so cosa sia... ma ci vuole..
				        	if( $(this).text() == obj[prop])   	txt += '<option value="' + prop + '" selected>' + obj[prop] + '</option>';
				        	else  								txt += '<option value="' + prop + '">' + obj[prop] + '</option>';
					}
					txt += '</selected>';
					$(this).html(txt);

			}
			else if( $(this).attr('tipo_texta') > 0) $(this).html('<textarea type="text" class="texta form-control" name="'+n+'" >' + $(this).text() + '</textarea>');
			else $(this).html('<input type="text" class="form-control" value="' + $(this).text() + '" name="'+n+'" >');
		});


		$(this).parents("tr").find(".add, .edit").toggle(); // accende spegne  le classi edit e add
		$(".add-new").attr("disabled", "disabled");
    });

	// Delete
	$(document).on("click", ".delete", function(){
				var i=$(this).closest("tr").attr('idx');
				var dove = $(this).closest("table").attr('id');
				if( ! Cancella(dove, i) ) {return false;}
        		$(this).parents("tr").remove();
				$(".add-new").removeAttr("disabled");
    });
});

function recuperaNewID(tab)
{
	var ritorno;

	$.ajax({
		type: 'GET',
		url: URLDB+'?new',
		data: { 'tab': tab },
		dataType: 'json',
		async : false,
		success: function (data) {
    			if( data.error ) { alert( "Problema creazione record. Contattare supporto. MSG : " + data.error_msg ); return false; }
    			ritorno= data._key; return false;
		},
		error: function (result) {
				alert("Errore di connessione al server DB");
				ritorno= false;	return false; // sempre...
		}
	});

	return ritorno;
}



function Modifica(tab, idx, campo, valore)
{
	 var ritorno;

		$.ajax({
			type: 'GET',
			url: URLDB+'?mod_crud',
			data: { 'tab': tab, 'ide': idx, 'campo':campo, 'valore':valore },
			dataType: 'json',
			async : false,
			success: function (data) {
	   			if( data.error ) { alert( "Problema aggioramento record. Contattare supporto. MSG : " + data.error_msg ); return false; }
   				ritorno=true;			return false;// sempre...
			},
			error: function (result) {
					alert("Errore di connessione al server DB");
					ritorno=false;		return false; // sempre...
			}
		});
		return ritorno;
}



function Cancella(tab, idx)
{
     var ritorno;

 	if (confirm('Confermi cancellazione record ?'))
	{

		$.ajax({
			type: 'GET',
			url: URLDB+'?dele',
			data: { 'tab': tab, 'ID':idx },
			dataType: 'json',
			async : false,
			success: function (data) {
	   			if( data.error ) { alert( "Problema cancellazione record. Contattare supporto. MSG : " + data.error_msg ); return false; }
   				ritorno=true;   				return false;// sempre...
			},
			error: function (result) {
					alert("Errore di connessione al server DB");
					ritorno=false;				return false; // sempre...
			}
		});
		return ritorno;
	}

}
