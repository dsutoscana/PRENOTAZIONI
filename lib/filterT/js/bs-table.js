// JavaScript Document
(function($){
	'use strict';


	$.fn.bsTable = function (callback, download, dltext, dltooltip, filter) {
		var $table;
		var $tableRows;
		var $tableRowsAndHeader;
		var $tableHeaders;
		var $tableBody;
		var $downloadIcon;
		var $downloadWrapper;
		var $downloadButton;
		var $filterMessage;
		var $filterRow;
		var $filterWrapper;
		var $filter;
		var cb;
		var dl;
		var dlClass;
		var flt;
		var fltClass;

		//set default values if parameter not passed
		download = typeof download !== 'undefined' ? download : false;
		dltext = typeof dltext !== 'undefined' ? dltext : ' Download';
		dltooltip = typeof dltooltip !== 'undefined' ? dltooltip : 'Click to download';
		filter = typeof filter !== 'undefined' ? filter : false;

		//set callback to execute when row is clicked
		cb = callback;
		//set download
		dl = download;
		//set filter
		flt = filter;

		if(dl === true) { 			dlClass = ''		} else dlClass = 'display:none';

		if(flt === true) { 			fltClass = '' 		} else fltClass = 'display:none';

		//add class to identify as bstable
		$(this).addClass('bstable');

		//get table
		$table = $(this).find('table');
		//get table body rows
		$tableRows = $(this).find('tbody > tr');
		//alert($tableRows.length);
		//get table body rows with header
		$tableRowsAndHeader = $(this).find('table tr');
		//console.log($tableRowsAndHeader);
		//get table headers
		$tableHeaders = $(this).find('table th');
		//console.log($tableHeaders);
		$tableBody = $(this).find('table tbody');
		//console.log($tableBody);

		//set classes for table - bootstrap table classes
		//$table.addClass('table table-bordered table-condensed table-hover');
		//highlight header row of table
		//$table.find('th').parent().addClass('warning');


		//filter message - shows how many rows found
		$filterMessage = $('<p/>', {			'class': 'col-md-2'		});
		//row for filter
		$filterRow = $('<div/>', {			'class': 'row',		});
		//column for search box
		$filterWrapper = $('<div/>', {			'class': 'pull-left col-md-2'		});
		//add case insensitive selector
		$.expr[':'].icontains = function(a, i, m) {
		  return $(a).text().toUpperCase()
		      .indexOf(m[3].toUpperCase()) >= 0;
		};
		//search box
		$filter = $('<input/>', {
			 'class': 'form-control input-sm',
			 'style': fltClass,
			 'type': 'text',
			 'placeholder': 'Filtro generale ...',
			 'keyup': function () {
				 	//hide all rows
					var $rows = $tableRows.hide();

					//if anything was entered into search box
					if (this.value.length) {
						//split search terms
						var data = this.value.split(" ");
						//loop through each search term
						$.each(data, function (i, v) {
							//show row using case insensitive selector
							$rows.filter(":icontains('" + v + "')").show();
						});
						//update filter message to show how many rows found
						$filterMessage.text($tableRows.parent().find('tr:visible').length + " attivita' trovate");
					} else {
						//if nothing entered, show all rows
						$rows.show();
						//delete filter message
						$filterMessage.text('');
					}
				}
		});

		//add filter row above table
		this.prepend(
			$filterRow
				.append($filterWrapper.append($filter))
				.append($filterMessage)
				/*.append($downloadWrapper
					.append($downloadButton
						.append($downloadIcon)
						.append(dltext)
					)
				)*/
		);

		//click event for table rows
		//$tableRows.click(function() {
			//return the row back to the function
			//callback($(this));
		//});


		//return this for chaining other functions
		return this;
	};
}(jQuery));
