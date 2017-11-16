var yearPicker = function(input, ajax_handler) {
    this.week_from, 
    this.week_to, 
    this.year_from, 
    this.year_to,
    this.years_title = new Array("leden", "únor", "březen", "duben", "květen", "červen", "červenec", "srpen", "září", "říjen", "listopad", "prosinec"),
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    this.prev_button = $(input).prev('button.btn-left');
    this.next_button = $(input).next('button.btn-right');

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
            self.disableButtons();
        }
        else {
            date = new Date();
            if(self.week_from == 1 && self.week_to == date.weeksInYear(self.year_from)) {
                $(input).datepicker('update', new Date(self.year_from, 1));
                $(input).val("Rok " + self.year_from);
                self.enableButtons();
            }
            else {
                $(input).datepicker('update', null);
                $(input).val("");
                self.disableButtons();
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
        this.disableButtons();
    }

    this.disableButtons = function() {
        $(self.prev_button).prop('disabled', true);
        $(self.next_button).prop('disabled', true);
    }

    this.enableButtons = function() {
        $(self.prev_button).prop('disabled', false);
        $(self.next_button).prop('disabled', false);
    }

    this.disableControls = function() {
        $(input).prop('disabled', true);
        self.disableButtons();
    }

    this.enableControls = function() {
        $(input).prop('disabled', false);
        self.enableButtons();
    }

    function sendRequest() {
        self.beforeSend();
        self.disableControls();

        $.get(ajax_handler,  {"week_from": self.week_from, 
                              "year_from": self.year_from,
                              "week_to": self.week_to, 
                              "year_to": self.year_to,
                             }, 
            function(payload) {
                $.nette.success(payload);
                self.afterReceive();
                changeUrl("?week_from=" + self.week_from + "&year_from=" + self.year_from + "&week_to=" + self.week_to + "&year_to=" + self.year_to);
                self.enableControls();
        });
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
            
            sendRequest();
        });

        $(self.next_button).on("click", function(event) {
            var year = parseInt(self.year_from);

            year = year + 1;
            week_from = 1;
            week_to = date.weeksInYear(year);

            self.setFrom(year, week_from);
            self.setTo(year, week_to);
            sendRequest();
        });

        $(self.prev_button).on("click", function(event) {
            var year = parseInt(self.year_from);

            year = year - 1;
            week_from = 1;
            week_to = date.weeksInYear(year);

            console.log(year);
            console.log(week_from);
            console.log(week_to);

            self.setFrom(year, week_from);
            self.setTo(year, week_to);
            sendRequest();
        });
    }

    initYearPicker(input);
};


