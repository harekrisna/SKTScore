var monthPicker = function(input, ajax_handler) {
    var week_from, 
        week_to, 
        year_from, 
        year_to,
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

    this.setMonthPicker = function(year, month) {
        years_title = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

        $(input).val("Rok " + year + ": " + years_title[month-1]);
    }

    this.setFrom = function(year, week) {
        year_from = year;
        week_from = week;
    }
    
    this.setTo = function(year, week) {
        year_to = year;
        week_to = week;
    }    

    this.beforeSend = function(func_declaration) {
        beforeSend = func_declaration;
    }

    this.afterReceive = function(func_declaration) {
        afterReceive = func_declaration;
    }

    this.getWeekFrom = function() { return week_from; }
    this.getWeekTo = function() { return week_to; }
    this.getYearFrom = function() { return year_from; }
    this.getYearTo = function() { return year_to; }

    this.clear = function() { 
        selected_week_number = null;
        $(input).val(""); 
    }

    // ajaxová obsluha inputu pro výběr týdne
    function initMonthPicker(input) {
        $(input).datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true,
            format: 'yyyy-mm-dd',
            viewMode: "months", 
            minViewMode: "months",
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
                var first_day_of_month = value;
                
                date = new Date(first_day_of_month);
                first_day_of_month_daynum = date.getDay();
                if(first_day_of_month_daynum == 0) first_day_of_month_daynum = 7; // korekce na neděli
                
                week_from = date.getWeek(value);
                if(first_day_of_month_daynum > 3) { // pokud měsíc začíná déle než ve středu budeme začínat od následujícího týdne, protože první týden náleží minulému měsíci
                    week_from = week_from + 1;
                }

                if(date.getMonth() == 0) // v lednu začínáme prvním týdnem
                    week_from = 1;


                var lastDayOfMonth = new Date(date.getFullYear(), date.getMonth()+1, 0);
                last_day_of_month_daynum = lastDayOfMonth.getDay();
                if(last_day_of_month_daynum == 0) last_day_of_month_daynum = 7; // korekce na neděli

                week_to = date.getWeek(lastDayOfMonth);
                if(last_day_of_month_daynum < 3) { // pokud měsíc končí v pondělí nebo v úterý tak ten týden se započítává až do dalšího
                    week_to = week_to - 1;
                }

                if(date.getMonth() + 1 == 12) // pokud se jedná o prosinec nastaví se poslední týden v roce
                    week_to = date.weeksInYear(date.getFullYear());

                year_from = year_to = date.getFullYear();

                self.setMonthPicker(year_from, date.getMonth()+1);
                
                beforeSend();
                $(input).prop('disabled', true);

                $.get(ajax_handler,  {"week_from": week_from, 
                                      "year_from": year_from,
                                      "week_to": week_to, 
                                      "year_to": year_to,
                                     }, 
                    function(payload) {
                        $.nette.success(payload);
                        afterReceive();
                        changeUrl("?week_from=" + week_from + "&year_from=" + year_from + "&week_to=" + week_to + "&year_to=" + year_to);
                        $(input).prop('disabled', false);
                });
            }
        });
    }

    initMonthPicker(input);
    
    // zvýraznění týdne při přeskakování na další a předchozí stránky datepickeru
    $('body').on('click', '.datepicker-dropdown th.prev, .datepicker-dropdown th.next', function(){
        redrawActiveWeek();
    });
};