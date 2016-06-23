
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
});

function initFooTable(table, success_message = "Záznam byl smazán.", error_message = "Záznam se nepodařilo smazat.") {
	$(table).footable();

	$(table).on('click', '.row-delete', function (event) {
		var invoker = this;
		event.preventDefault();
	    swal({
	        title: "Opravdu si přejete záznam smazat?",
	        text: "Tato operace je nevratná!",
	        type: "warning",
	        showCancelButton: true,
	        confirmButtonColor: "#DD6B55",
	        confirmButtonText: "Ano, smazat!",
	        cancelButtonText: "Zrušit!",
	        closeOnConfirm: false
	    }, function () {
	        var table = $('#booksTable').data('footable');
	        var row = $(invoker).parents('tr:first');
	        $.get(invoker.href, 
	            function (payload) { 
	                $.nette.success(payload);
	                if(payload.success) {
	                	table.removeRow(row);
	                	swal("Smazáno!", success_message, "success");
	                }
	               	else {
	               		swal("Chyba!", error_message, "error");
	               	}
	            }
	        );
	    });
	});
}

$("body").on("keydown", "input[type=number]", function(e) {
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