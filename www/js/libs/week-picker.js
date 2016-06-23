Date.prototype.getWeek = function(date_string) {
	var date = new Date(date_string);
	date.setHours(0, 0, 0, 0);
	// Thursday in current week decides the year.
	date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
	// January 4 is always in week 1.
	var week1 = new Date(date.getFullYear(), 0, 4);
	// Adjust to Thursday in week 1 and count number of weeks from date to week1.
	return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000
		                    - 3 + (week1.getDay() + 6) % 7) / 7);
}

// Returns the four-digit year corresponding to the ISO week of the date.
Date.prototype.getWeekYear = function() {
	var date = new Date(this.getTime());
	date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
	return date.getFullYear();
}

Date.prototype.nowMySQLdate = function() {
	return date.getFullYear()  + '-' +
		   ('0' + (date.getMonth()+1)).slice(-2) + "-" +
		   ('0' + date.getDate()).slice(-2);
}

function bindWeekPicker(input) {
    $(input).datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: 'yyyy-mm-dd',
        language: 'cs',
    })
    .click(function() { // při prvním kliknutím na input se zvýrazní vybraný týden
        active_tr = $(".datepicker-dropdown table tr.active");
        if(Object.keys(active_tr).length == 4) { // je už něco označené?
            var active_tr = $(".datepicker-dropdown table td.cw:contains(" + QueryString.week_number + ")").parent();
            active_tr.find('td.active').removeClass('active');
            active_tr.addClass('active');
        }
    });
    
    $(input).on('change', function (e) { // při změně týdne ze změní zvýrazněný
        var value = $(input).val();
        var only_int_value = value.replace(/-/g, '');

        if(!isNaN(only_int_value)) {
            date = new Date()
            if(value == "") {
                value = date.nowMySQLdate();
            }
            var active_tr = $('.datepicker-dropdown table td.active.day').parent();
            active_tr.find('td.active').removeClass('active');
            active_tr.addClass('active');
                                
            var week_number = date.getWeek(value);
            var year = value.substring(0, 4);
            setWeekPicker(input, year, week_number);
            
            $('#resultsTable').fadeTo(500, 0, function(){
               $(this).css("visibility", "hidden");   
            });

            $('#fountainG').fadeIn();
            $(input).prop('disabled', true);

            $.get("result/setter", {"week_number": week_number, "year": year}, function(payload) {
                $.nette.success(payload);
                $('#resultsTable').footable();
                $('#resultsTable').hide();
                $('#resultsTable').fadeIn();
                $('#fountainG').fadeOut();
                changeUrl("?week_number=" + week_number + "&year=" + year );
                $(input).prop('disabled', false);
            });
        }
    });
}

function setWeekPicker(input, year, week_number) {
    $(input).val("Rok " + year + " - Týden " + week_number);
}