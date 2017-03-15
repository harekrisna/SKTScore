var weekPicker = function(input, ajax_handler) {
    this.week_number,
    this.year,
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    var self = this;

    this.setWeekPicker = function(year, week_number) {
        this.week_number = week_number;
        this.year = year;
        date = new Date();
        monday = date.getMondayOfWeek(week_number, year);
        $(input).datepicker('update', monday);
        $(input).val("Rok " + year + ": týden " + week_number);
    }

    this.beforeSend = function(func_declaration) {
        this.beforeSend = func_declaration;
    }

    this.afterReceive = function(func_declaration) {
        this.afterReceive = func_declaration;
    }

    this.getWeek = function() { return this.week_number; }
    this.getYear = function() { return this.year; }

    this.clear = function() { 
        this.week_number = null;
        this.year = null;
        $(input).val(""); 
    }

    function redrawActiveWeek() {
        var table_year_title_th = $(".datepicker-dropdown table thead tr th.datepicker-switch");
        var table_year_title = $(table_year_title_th).first().html().replace(/[^\d.]/g, '');
        
        $(".datepicker-dropdown table tr").removeClass('active');
        $(".datepicker-dropdown table tr td.active").removeClass('active');

        if(table_year_title == self.year) {
            var active_tr = $(".datepicker-dropdown table td.cw").filter(function() { // najdeme řádek obsahující číslo vybraného týdne
                return $(this).text() == self.week_number;
            }).parent();

            active_tr.addClass('active');
        }
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

        .on("changeDate", function(e) {
            var value = $(input).val();
            if(isNaN(Date.parse(value)))
                return;

            date = new Date(value);
            week_number = date.getWeek();
            year = date.getFullYear();            
            self.setWeekPicker(year, week_number);
            
            self.beforeSend();
            $(input).prop('disabled', true);

            $.get(ajax_handler, {"week": week_number, "year": year}, function(payload) {
                $.nette.success(payload);
                self.afterReceive();
                changeUrl("?week=" + week_number + "&year=" + year );
                $(input).prop('disabled', false);
            });  
        })

        .on("show", function(event) {
            redrawActiveWeek();
        })

        .keyup(function(event) {
            redrawActiveWeek();
        })
    }

    initDatePicker(input);
};