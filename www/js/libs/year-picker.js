var yearPicker = function(input, ajax_handler) {
    this.week_from, 
    this.week_to, 
    this.year_from, 
    this.year_to,
    this.years_title = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec"),
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    var self = this;

    this.setFrom = function(year, week) {
        this.year_from = year;
        this.week_from = week;
        redraw();
    }
    
    this.setTo = function(year, week) {
        this.year_to = year;
        this.week_to = week;
        redraw();
    }    

    function redraw() {
        if(self.year_from != self.year_to) {
            $(input).val("");
        }
        else {
            date = new Date();
            if(self.week_from == 1 && self.week_to == date.weeksInYear(self.year_from)) {
                $(input).datepicker('update', new Date(self.year_from, 1));
                $(input).val("Rok " + self.year_from);
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
        
        .on("changeDate", function(e) {
            var value = $(input).val();
            if(isNaN(Date.parse(value)))
                return;

            year = value;
            date = new Date();
            week_from = 1;
            week_to = date.weeksInYear(year);

            self.setFrom(year, week_from);
            self.setTo(year, week_to);
            
            self.beforeSend();

            $(input).prop('disabled', true);

            $.get(ajax_handler,  {"week_from": week_from, 
                                  "year_from": year,
                                  "week_to": week_to, 
                                  "year_to": year,
                                 }, 
                function(payload) {
                    $.nette.success(payload);
                    self.afterReceive();
                    changeUrl("?week_from=" + week_from + "&year_from=" + year + "&week_to=" + week_to + "&year_to=" + year);
                    $(input).prop('disabled', false);
            });
        });
    }

    initYearPicker(input);
};


