var monthPicker = function(input, ajax_handler) {
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
            month_index = date.isInMonthRange(self.year_from, self.week_from, self.year_to, self.week_to);

            if(month_index) {
                $(input).datepicker('update', new Date(self.year_from, month_index - 1, 1));
                $(input).val("Rok " + self.year_from + ": " + self.years_title[month_index-1]);
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
                $(input).prop('disabled', false);
                self.enableControls();
        });
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

            sendRequest(); 
        });

        $(self.next_button).on("click", function(event) {
            var month_index = date.isInMonthRange(self.year_from, self.week_from, self.year_to, self.week_to);
            var year = parseInt(self.year_from);

            month_index = month_index + 1;
            if(month_index > 12)  {
                month_index = 1;
                year = year + 1;
            }

            week_from = date.getMonthStartWeek(year, padLeft(month_index , 2));
            week_to = date.getMonthLastWeek(year, padLeft(month_index, 2));
            year_from = year_to = year;

            self.setFrom(year_from, week_from);
            self.setTo(year_to, week_to);
            sendRequest();
        });

        $(self.prev_button).on("click", function(event) {
            var month_index = date.isInMonthRange(self.year_from, self.week_from, self.year_to, self.week_to);
            var year = parseInt(self.year_from);

            month_index = month_index - 1;
            if(month_index < 1)  {
                month_index = 12;
                year = year - 1;
            }

            week_from = date.getMonthStartWeek(year, padLeft(month_index , 2));
            week_to = date.getMonthLastWeek(year, padLeft(month_index, 2));
            year_from = year_to = year;

            self.setFrom(year_from, week_from);
            self.setTo(year_to, week_to);
            sendRequest();
        });
    }

    initMonthPicker(input);
};