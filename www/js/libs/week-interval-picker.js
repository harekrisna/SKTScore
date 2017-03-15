var weekIntervalPicker = function(input_from, input_to, ajax_handler) {
    this.week_from, 
    this.week_to, 
    this.year_from, 
    this.year_to,
    this.beforeSend = function() {},
    this.afterReceive = function() {};

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
            $(input_from).prop('disabled', true);
            $(input_to).prop('disabled', true);
			
            $.get(ajax_handler, {"week_from": self.week_from, 
                                 "year_from": self.year_from,
                                 "week_to": self.week_to, 
                                 "year_to": self.year_to,
                                }, 
                function(payload) {
                    $.nette.success(payload);
                    self.afterReceive();
                    $(input_from).prop('disabled', false);
                    $(input_to).prop('disabled', false);
                    changeUrl("?week_from=" + self.week_from + "&year_from=" + self.year_from + "&week_to=" + self.week_to + "&year_to=" + self.year_to);
                }
            );
        })

        .on("show", function(event) {
            redrawActiveWeek(event);
        })

        .keyup(function(event) {
            redrawActiveWeek(event);
        })
    }

    initDatePicker(input_from);
    initDatePicker(input_to);
}