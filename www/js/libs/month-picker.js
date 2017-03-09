var monthPicker = function(input, ajax_handler) {
    var week_from, 
        week_to, 
        year_from, 
        year_to,
        selected_month,
        years_title = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec"),
        beforeSend = function() {},
        afterReceive = function() {};

    var self = this;

    this.setFrom = function(year, week) {
        year_from = year;
        week_from = week;
    }
    
    this.setTo = function(year, week) {
        year_to = year;
        week_to = week;
    }    

    this.redraw = function() {
        if(year_from != year_to) {
            $(input).val("");    
        }
        else {
            is_in_mounth_range = false;
            for (month_index = 1; month_index <= 12; month_index++) { 
                start_week = this.getMonthStartWeek(year_from, month_index);
                last_week = this.getMonthLastWeek(year_from, month_index);
                if(start_week == week_from && last_week == week_to) {
                    is_in_mounth_range = true;
                    break;
                }
            }

            if(is_in_mounth_range) {
                $(input).val("Rok " + year_from + ": " + years_title[month_index-1]);
            }
            else {
                $(input).val("");
            }
        }
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

    this.getMonthStartWeek = function(year, month) {
        var first_day_of_month = year + "-" + month + "-01";
        //console.log(first_day_of_month);
        date = new Date(first_day_of_month);
        first_day_of_month_daynum = date.getDay();
        if(first_day_of_month_daynum == 0) first_day_of_month_daynum = 7; // korekce na neděli
        
        week = date.getWeek(first_day_of_month);
        if(first_day_of_month_daynum > 3) { // pokud měsíc začíná déle než ve středu budeme začínat od následujícího týdne, protože první týden náleží minulému měsíci
            week = week + 1;
        }

        if(date.getMonth() == 0) // v lednu začínáme prvním týdnem
            week = 1;

        return week;
    }

    this.getMonthLastWeek = function(year, month) {
        month = parseInt(month, 10);
        var date = new Date(year, month, 0);

        last_day_of_month_daynum = date.getDay();
        if(last_day_of_month_daynum == 0) last_day_of_month_daynum = 7; // korekce na neděli

        week = date.getWeek(date);
        if(last_day_of_month_daynum < 3) { // pokud měsíc končí v pondělí nebo v úterý tak ten týden se započítává až do dalšího
            week = week - 1;
        }

        if(date.getMonth() + 1 == 12) // pokud se jedná o prosinec nastaví se poslední týden v roce
            week = date.weeksInYear(date.getFullYear());

        return week;
    }

    this.clear = function() { 
        selected_month = null;
        $(input).val(""); 
    }

    // ajaxová obsluha inputu pro výběr týdne
    function initMonthPicker(input) {
        $(input).datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true,
            format: 'yyyy-mm',
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
                
                week_from = self.getMonthStartWeek(value.substring(0, 4), value.substring(5, 7));
                week_to = self.getMonthLastWeek(value.substring(0, 4), value.substring(5, 7));

                year_from = year_to = date.getFullYear();
                selected_month = date.getMonth();

                self.redraw();
                
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
};