
$(document).ready(function () {
	;(function($){
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
	}(jQuery));
});
