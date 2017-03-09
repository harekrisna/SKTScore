var yearPicker = function(input, ajax_handler) {
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
            date = new Date();
            if(week_from == 1 && week_to == date.weeksInYear(year_from)) {
                $(input).val("Rok " + year_from);
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

    this.clear = function() { 
        selected_month = null;
        $(input).val(""); 
    }

    // ajaxová obsluha inputu pro výběr týdne
    function initYearPicker(input) {
        $(input).datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true,
            format: 'yyyy',
            viewMode: "years", 
            minViewMode: "years",
            language: 'cs',
        })
        .click(function() { // při kliknutí na input se zvýrazní vybraný týden
            //redrawActiveWeek();
        })

        .keyup(function() { // při kliknutí na input se zvýrazní vybraný týden
            //SredrawActiveWeek();
        });
        
        $(input).on('change', function (e) { // při změně týdne ze změní zvýrazněný
            var value = $(input).val();
            
            var only_int_value = value.replace(/-/g, '');
            if(!isNaN(only_int_value)) {
                year = value;
                date = new Date();
                week_from = 1;
                week_to = date.weeksInYear(year);

                year_from = year_to = year;
                selected_year = year;

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

    initYearPicker(input);
};