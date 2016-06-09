
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

function initFooTable(table) {
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
	                	swal("Smazáno!", "Záznam byl smazán.", "success");
	                }
	               	else {
	               		swal("Chyba!", "Záznam se nepodařilo smazat.", "error");
	               	}
	            }
	        );
	    });
	});
}