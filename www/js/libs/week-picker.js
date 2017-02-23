var weekPicker = function(input, ajax_handler) {
    var week_number,
        year,
        selected_week_number,
        beforeSend = function() {},
        afterReceive = function() {};

    var self = this;

    function redrawActiveWeek() {
        var active_tr = $(".datepicker-dropdown table td.cw").filter(function() { // najdeme řádek obsahující číslo vybraného týdne
            return $(this).text() == selected_week_number;
        }).parent();

        $(".datepicker-dropdown table tr").removeClass('active');
        active_tr.find('td.active').removeClass('active');
        active_tr.addClass('active');
        
        // odstranění hover efektu nad neaktivnímy týdny
        active_tr = $(".datepicker-dropdown table tbody tr").each(function() {
            var cells = $(this).find('td.disabled');
            if(cells.length > 0) {
                var first_cell = $(this).find('td:first-child');
                first_cell.addClass('disabled');
            }
        });
    }

    this.setWeekPicker = function(year, week_number) {
        $(input).val("Rok " + year + ": týden " + week_number);
        selected_week_number = week_number;
    }

    this.beforeSend = function(func_declaration) {
        beforeSend = func_declaration;
    }

    this.afterReceive = function(func_declaration) {
        afterReceive = func_declaration;
    }

    this.getWeek = function() { return week_number; }
    this.getYear = function() { return year; }

    this.clear = function() { 
        selected_week_number = null;
        $(input).val(""); 
    }

    // ajaxová obsluha inputu pro výběr týdne
    function initDatePicker(input) {
        $(input).datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'yyyy-mm-dd',
            language: 'cs',
        })
        .click(function() { // při kliknutí na input se zvýrazní vybraný týden
            redrawActiveWeek();
        })

        .keyup(function() { // při kliknutí na input se zvýrazní vybraný týden
            redrawActiveWeek();
        });
        
        $(input).on('change', function (e) { // při změně týdne ze změní zvýrazněný
            var value = $(input).val();
            var only_int_value = value.replace(/-/g, '');

            if(!isNaN(only_int_value)) {
                date = new Date();
                if(value == "") {
                    value = date.nowMySQLdate();
                }
                var active_tr = $('.datepicker-dropdown table td.active.day').parent();
                active_tr.find('td.active').removeClass('active');
                active_tr.addClass('active');
                              
                week_number = date.getWeek(value);
                selected_week_number = week_number;
                year = value.substring(0, 4);
                self.setWeekPicker(year, week_number);
                
                beforeSend();
                $(input).prop('disabled', true);

                $.get(ajax_handler, {"week": week_number, "year": year}, function(payload) {
                    $.nette.success(payload);
                    afterReceive();
                    changeUrl("?week=" + week_number + "&year=" + year );
                    $(input).prop('disabled', false);
                });
            }
        });
    }

    initDatePicker(input);
    
    // zvýraznění týdne při přeskakování na další a předchozí stránky datepickeru
    $('body').on('click', '.datepicker-dropdown th.prev, .datepicker-dropdown th.next', function(){
        redrawActiveWeek();
    });
};