var weekIntervalPicker = function(input_from, input_to, ajax_handler) {
    this.week_from, 
    this.week_to, 
    this.year_from, 
    this.year_to,
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    this.from_prev_button = $(input_from).prev('button.btn-left');
    this.from_next_button = $(input_from).next('button.btn-right');
    this.to_prev_button = $(input_to).prev('button.btn-left');
    this.to_next_button = $(input_to).next('button.btn-right');


    var selected_week_number;
    
    var self = this;

    this.setFrom = function(year, week_number) {
        this.year_from = year;
        this.week_from = week_number;
        date = new Date();
        monday = date.getMondayOfWeek(week_number, year);
        $(input_from).datepicker('update', monday);
        $(input_from).val("Rok " + year + ": týden " + week_number);
    }
    
    this.setTo = function(year, week_number) {
        this.year_to = year;
        this.week_to = week_number;
        date = new Date();
        monday = date.getMondayOfWeek(week_number, year);
        $(input_to).datepicker('update', monday);
        $(input_to).val("Rok " + year + ": týden " + week_number);
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

    this.disableControls = function() {
        $(input_from).prop('disabled', true);
        $(input_to).prop('disabled', true);
        $(self.from_prev_button).prop('disabled', true);
        $(self.from_next_button).prop('disabled', true);
        $(self.to_prev_button).prop('disabled', true);
        $(self.to_next_button).prop('disabled', true);
    }

    this.enableControls = function() {
        $(input_from).prop('disabled', false);
        $(input_to).prop('disabled', false);
        $(self.from_prev_button).prop('disabled', false);
        $(self.from_next_button).prop('disabled', false);
        $(self.to_prev_button).prop('disabled', false);
        $(self.to_next_button).prop('disabled', false);
    }

    function redrawActiveWeek(event) {
        var table_year_title_th = $(".datepicker-dropdown table thead tr th.datepicker-switch");
        var table_year_title = $(table_year_title_th).first().html().replace(/[^\d.]/g, '');

        if('#' + event.target.id == input_from) {
            year = self.year_from;
            week = self.week_from;
        }
        else if('#' + event.target.id == input_to) { 
            year = self.year_to;
            week = self.week_to;
        }

        $(".datepicker-dropdown table tr").removeClass('active');
        $(".datepicker-dropdown table tr td.active").removeClass('active');

        if(table_year_title == year) {
            var active_tr = $(".datepicker-dropdown table td.cw").filter(function() {
                return $(this).text() == week;
            }).parent();

            active_tr.attr('class', "active");
        }
    }

    function sendRequest() {
        $.get(ajax_handler, {"week_from": self.week_from, 
                             "year_from": self.year_from,
                             "week_to": self.week_to, 
                             "year_to": self.year_to,
                            }, 
            function(payload) {
                $.nette.success(payload);
                self.afterReceive();
                changeUrl("?week_from=" + self.week_from + "&year_from=" + self.year_from + "&week_to=" + self.week_to + "&year_to=" + self.year_to);
                self.enableControls();
            }
        );
    }

    function initDatePicker(input) {
        $(input).datepicker({
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'yyyy-mm-dd',
            language: 'cs',
        })
        .on("changeDate", function(event) {
            var value = $(input).val();            
            if(isNaN(Date.parse(value)))
                return;
            
            date = new Date(value);
            var active_tr = $('.datepicker-dropdown table td.active.day').parent();
            active_tr.find('td.active').removeClass('active');
            active_tr.addClass('active');
                                
            var week_number = date.getWeek();
            var year = date.getFullYear();
			
            if('#' + event.target.id == input_from) { // změna od
                self.setFrom(year, week_number);
                if(year + padLeft(week_number, 2) > self.year_to + padLeft(self.week_to, 2)) { // pokud je vybrán týden od, který je větší než do, týden do se navýší
                    self.setTo(year, week_number);    
                }
            }
            else if('#' + event.target.id == input_to) { // změna do
                self.setTo(year, week_number);
                if(year + padLeft(week_number, 2) < self.year_from + padLeft(self.week_from, 2)) { // pokud je vybrán týden do, který je menší než od, týden od se sníží
                    self.setFrom(year, week_number);    
                }
            }

            self.beforeSend();
            self.disableControls();
            sendRequest();
        })

        .on("show", function(event) {
            redrawActiveWeek(event);
        })

        .keyup(function(event) {
            redrawActiveWeek(event);
        });
    }

    function initButtonControlsFrom() {
        $(self.from_next_button).on("click", function(event) {
            var next_week_from = parseInt(self.week_from) + 1;
            var year_from = parseInt(self.year_from);

            if(next_week_from > date.weeksInYear(year_from)) {
                year_from = year_from + 1;
                next_week_from = 1;
            }

            self.setFrom(year_from, next_week_from);
            
            if(year_from + padLeft(next_week_from, 2) > self.year_to + padLeft(self.week_to, 2)) { // pokud je vybrán týden od, který je větší než do, týden do se navýší
                self.setTo(year_from, next_week_from);    
            }

            self.beforeSend();
            self.disableControls();
            sendRequest();
        });

        $(self.from_prev_button).on("click", function(event) {
            var prev_week_from = parseInt(self.week_from) - 1;
            var year_from = parseInt(self.year_from);

            if(prev_week_from == 0) {
                year_from = year_from - 1;
                prev_week_from = date.weeksInYear(year_from);
            }

            self.setFrom(year_from, prev_week_from);
            self.beforeSend();
            self.disableControls();
            sendRequest();
        });        
    }

    function initButtonControlsTo() {
        $(self.to_next_button).on("click", function(event) {
            var next_week_to = parseInt(self.week_to) + 1;
            var year_to = parseInt(self.year_to);

            if(next_week_to > date.weeksInYear(year_to)) {
                year_to = year_to + 1;
                next_week_to = 1;
            }

            self.setTo(year_to, next_week_to);
            self.beforeSend();
            self.disableControls();
            sendRequest();
        });

        $(self.to_prev_button).on("click", function(event) {
            var prev_week_to = parseInt(self.week_to) - 1;
            var year_to = parseInt(self.year_to);

            if(prev_week_to == 0) {
                year_to = year_to - 1;
                prev_week_to = date.weeksInYear(year_to);
            }

            self.setTo(year_to, prev_week_to);
            
            if(year_to + padLeft(prev_week_to, 2) < self.year_from + padLeft(self.week_from, 2)) { // pokud je vybrán týden do, který je menší než od, týden od se sníží
                self.setFrom(year_to, prev_week_to);    
            }

            self.beforeSend();
            self.disableControls();
            sendRequest();
        });        
    }

    initDatePicker(input_from);
    initButtonControlsFrom();
    initDatePicker(input_to);
    initButtonControlsTo();
}