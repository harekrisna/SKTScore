var mesic = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

$(document).ready(function () {
	;(function($){
		if($.fn.datepicker){
			$.fn.datepicker.dates['cs'] = {
				days: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota"],
				daysShort: ["Ned", "Pon", "Úte", "Stř", "Čtv", "Pát", "Sob"],
				daysMin: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
				months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
				monthsShort: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čer", "Čnc", "Srp", "Zář", "Říj", "Lis", "Pro"],
				today: "Aktuální týden",
				clear: "Vymazat",
				weekStart: 1,
				format: "dd.m.yyyy"
			};
		}
	}(jQuery));

   if (typeof jQuery.validator !== "undefined") {
      jQuery.extend(jQuery.validator.messages, {
          required: "Toto pole je povinné.",
      });
  }
});

function initFooTable(table, success_message = "Záznam byl smazán.", error_message = "Záznam se nepodařilo smazat.") {
  	$(table).footable({
        'paging': {
            'enabled': true,
            'size': 50,
        },

        'sorting': {
            'enabled': true,
        },

        'filtering': {
            'enabled': true,
            'placeholder': "Vyhledat...",
            'dropdownTitle': "Hledat v:",
            'space': "OR",
        },
        'empty': "Žádné výsledky",
    });

  	$(table).on('click', '.row-delete', function (event) {
    		var invoker = this;
        var delete_name = $(invoker).data('delete_name');
        if(delete_name == undefined) {
            delete_name = " ";
        }
        else {
            delete_name = " \"" + delete_name + "\" ";
        }

    		event.preventDefault();
  	    swal({
  	        title: "Opravdu si přejete záznam" + delete_name +"smazat?",
  	        text: "Tato operace je nevratná!",
  	        type: "warning",
  	        showCancelButton: true,
  	        confirmButtonColor: "#DD6B55",
  	        confirmButtonText: "Ano, smazat!",
  	        cancelButtonText: "Zrušit!",
  	        closeOnConfirm: false,
            inputValue: table  // způsob jak dostat do anonymní funkce parametr table
  	    }, function () {
            var table = this.inputValue;  
  	        var table = $(table).data('footable');
  	        var row = $(invoker).parents('tr:first');
            row = FooTable.getRow(row);
  	        $.get(invoker.href, 
  	            function (payload) { 
  	                $.nette.success(payload);
  	                if(payload.success) {
  	                	  row.delete();
  	                	  swal("Smazáno!", success_message, "success");
  	                }
  	               	else {
  	               		  swal("Chyba!", error_message, "error");
  	               	}
  	            }
  	        );
  	    });
  	});

    $(table).on("expand.ft.row", function (e, ft, row) {
        e.preventDefault();
        var row = $(row.$el);
        row.find(" > td span.footable-toggle").toggleClass("fooicon-plus fooicon-minus");
        var next = row.next();
        if(next.hasClass("footable-detail-row")) {
            if(next.is(":visible")) 
                next.hide(); // collapse
            else
                next.show(); // expand
        }
        else { // expand first
            var colspan = ($(row).closest("table").find("thead > tr.footable-header > th").length - 1);
            row.after('<tr class="footable-detail-row"><td colspan="' + colspan + '"><div id="main_loading" class="sk-spinner sk-spinner-three-bounce table-tr-spinner"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></td></tr>'); // add expanded row
            
            var expand_td = row.next().find("td");
            var expand_url = row.data('expand-url');

            $.get({
                url: expand_url,
                dataType : "html",
                success: function (data) {
                    $.nette.success(data);
                    expand_td.html(data);
                }
            });
        }
    });

    $(table).on("before.ft.filtering", function (e, ft, filters) {

        // odstranění diakritiky a ostatního z vyhledávání ve fulltextu
        filters.forEach(function(filter) {
            if(filter['name'] == "search") {
                filter['query']['parts'].forEach(function(part) {
                    part['query'] = webalize(part['query']);
                });
            }
        });

        $(table).find('tbody tr.footable-detail-row').remove();
        $(table).find('tbody td span.fooicon-minus').removeClass('fooicon-minus')
                                                    .addClass('fooicon-plus');
    });

    $(table).on("before.ft.sorting", function (e, ft, sorter) {
        $(table).find('tbody tr.footable-detail-row').remove();
        $(table).find('tbody td span.fooicon-minus').removeClass('fooicon-minus')
                                                    .addClass('fooicon-plus');
    });

    $(table).show();
}

function createFooTableFilter(column_name, prompt_value, options) {
    return FooTable.Filtering.extend({
        construct: function(instance){
            this._super(instance);
            this.statuses = options; // the options available in the dropdown
            this.def = prompt_value; // the default/unselected value for the dropdown (this would clear the filter when selected)
            this.$status = null; // a placeholder for our jQuery wrapper around the dropdown
        },

        $create: function(){
            this._super(); // call the base $create method, this populates the $form property
            var self = this, // hold a reference to my self for use later
                // create the bootstrap form group and append it to the form
                $form_grp = $('<div/>', {'class': 'form-group'})
                    .append($('<label/>', {'class': 'sr-only'}))
                    .prependTo(self.$form);

            // create the select element with the default value and append it to the form group
            self.$status = $('<select/>', { 'class': 'form-control' })
                .on('change', { self: self}, self._onStatusDropdownChanged)
                .append($('<option/>', { text: self.def}))
                .appendTo($form_grp);

            // add each of the statuses to the dropdown element
            $.each(self.statuses, function(i, status){
                self.$status.append($('<option value="' + i + '"/>').text(status));
            });
        },

        _onStatusDropdownChanged: function(e){
            var self = e.data.self, // get the MyFiltering object
                selected = $(this).val(); // get the current dropdown value

            if (selected !== self.def){ // if it's not the default value add a new filter
                self.addFilter(column_name, '"' + selected + '"', [column_name]);
            } else { // otherwise remove the filter
                self.removeFilter(column_name);
            }
            // initiate the actual filter operation
            self.filter();
        },

        draw: function(){
            this._super(); // call the base draw method, this will handle the default search input
            var status = this.find(column_name); // find the status filter
            if (status instanceof FooTable.Filter){ // if it exists update the dropdown to reflect the value
                this.$status.val(status.query.val().replace(/\"/g, ''));
            } else { // otherwise update the dropdown to the default value
                this.$status.val(this.def);
            }
        }
    });
}


$("body").on("keydown", "input[type=number]", function(e) {
	/*
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
         // Allow: Ctrl+A
        (e.keyCode == 65 && e.ctrlKey === true) ||
         // Allow: Ctrl+C
        (e.keyCode == 67 && e.ctrlKey === true) ||
         // Allow: Ctrl+X
        (e.keyCode == 88 && e.ctrlKey === true) ||
         // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
             // let it happen, don't do anything
             return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || ((e.keyCode < 48 || e.keyCode > 57) && e.keyCode != 40)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }

    if(this.value.length == 4 && e.keyCode != 40)
    	e.preventDefault();
    */ 
});

var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
  return query_string;
}();

function loading(button, text) {
    button.val(text);
    var count = 0;
    loading = setInterval(function(){
        count++;
        var dots = new Array(count % 5).join('.');
        button.val(text + dots);
    }, 500);

    return loading;
}

// deprecated
function generateScoreTableOrder(table, order_by_column_number) { 
    // manuální renderování sloupce pořadí
    var position = 0;
    var row_position = 0;
    var last_person_sumpoints = Infinity;
    $(table + ' > tbody > tr').each(function() {
        var person_sumpoints = $(this).find("td:nth-child(" + order_by_column_number + ")").data("value");
        var td = $(this).find('td:first-child');
        row_position += 1;
        if(last_person_sumpoints > person_sumpoints) {
            position = row_position;
        }
		
        td.html(position);
        if(person_sumpoints == null)
        	td.html(".");
        last_person_sumpoints = person_sumpoints;
    });
}

function padLeft(nr, n, str){
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}

var webalize = function (str) {
    var charlist;
    charlist = [
        ['Á','A'], ['Ä','A'], ['Č','C'], ['Ç','C'], ['Ď','D'], ['É','E'], ['Ě','E'],
        ['Ë','E'], ['Í','I'], ['Ň','N'], ['Ó','O'], ['Ö','O'], ['Ř','R'], ['Š','S'],
        ['Ť','T'], ['Ú','U'], ['Ů','U'], ['Ü','U'], ['Ý','Y'], ['Ž','Z'], ['á','a'],
        ['ä','a'], ['č','c'], ['ç','c'], ['ď','d'], ['é','e'], ['ě','e'], ['ë','e'],
        ['í','i'], ['ň','n'], ['ó','o'], ['ö','o'], ['ř','r'], ['š','s'], ['ť','t'],
        ['ú','u'], ['ů','u'], ['ü','u'], ['ý','y'], ['ž','z']
    ];
    for (var i in charlist) {
        var re = new RegExp(charlist[i][0],'g');
        str = str.replace(re, charlist[i][1]);
    }
    
    str = str.replace(/[^a-z0-9]/ig, '-');
    str = str.replace(/\-+/g, '-');
    if (str[0] == '-') {
        str = str.substring(1, str.length);
    }
    if (str[str.length - 1] == '-') {
        str = str.substring(0, str.length - 1);
    }
    
    return str.toLowerCase();
}