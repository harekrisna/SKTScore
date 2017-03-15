var monthPicker = function(input, ajax_handler) {
    this.week_from, 
    this.week_to, 
    this.year_from, 
    this.year_to,
    this.years_title = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec"),
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    var self = this;

    this.setFrom = function(year, week) {
        self.year_from = year;
        self.week_from = week;
        update();
    }
    
    this.setTo = function(year, week) {
        self.year_to = year;
        self.week_to = week;
        update();
    }    

    function update() {
        if(self.year_from != self.year_to) {
            $(input).datepicker('update', null);
            $(input).val("");
        }
        else {
            var is_in_mounth_range = false;
            var date = new Date();

            for (month_index = 1; month_index <= 12; month_index++) { 
                start_week = date.getMonthStartWeek(self.year_from, month_index);
                last_week = date.getMonthLastWeek(self.year_from, month_index);
                if(start_week == self.week_from && last_week == self.week_to) {
                    is_in_mounth_range = true;
                    break;
                }
            }

            if(is_in_mounth_range) {
                $(input).datepicker('update', new Date(self.year_from, month_index - 1, 1));
                $(input).val("Rok " + self.year_from + ": " + self.years_title[month_index-1]);
            }
            else {
                $(input).datepicker('update', null);
                $(input).val("");
            }
        }
    }

    this.beforeSend = function(func_declaration) {
        this.beforeSend = func_declaration;
    }

    this.afterReceive = function(func_declaration) {
        this.afterReceive = func_declaration;
    }

    this.getWeekFrom = function() { return this.week_from; }
    this.getWeekTo = function() { return this.week_to; }
    this.getYearFrom = function() { return this.year_from; }
    this.getYearTo = function() { return this.year_to; }


    this.clear = function() {
        this.week_from = this.week_to = this.year_from = this.year_to = null;
        $(input).datepicker('update', null);
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

        .on("changeDate", function(e) {
            var value = $(input).val();
            if(isNaN(Date.parse(value)))
                return;
            
            date = new Date(value);    
            week_from = date.getMonthStartWeek(date.getFullYear(), padLeft(date.getMonth() + 1, 2));
            week_to = date.getMonthLastWeek(date.getFullYear(), padLeft(date.getMonth() + 1, 2));

            year_from = year_to = date.getFullYear();
            
            self.setFrom(year_from, week_from);
            self.setTo(year_to, week_to);

            self.beforeSend();
            $(input).prop('disabled', true);

            $.get(ajax_handler,  {"week_from": week_from, 
                                  "year_from": year_from,
                                  "week_to": week_to, 
                                  "year_to": year_to,
                                 }, 
                function(payload) {
                    $.nette.success(payload);
                    self.afterReceive();
                    changeUrl("?week_from=" + week_from + "&year_from=" + year_from + "&week_to=" + week_to + "&year_to=" + year_to);
                    $(input).prop('disabled', false);
            });
        })
    }

    initMonthPicker(input);
};