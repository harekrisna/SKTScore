var weekPicker = function(input, ajax_handler) {
    this.week_number,
    this.year,
    this.beforeSend = function() {},
    this.afterReceive = function() {};

    var self = this;

    this.setWeekPicker = function(year, week_number) {
        self.week_number = week_number;
        self.year = year;
        date = new Date();
        monday = date.getMondayOfWeek(self.week_number, self.year);
        $(input).datepicker('update', monday);
        $(input).val("Rok " + self.year + ": týden " + self.week_number);
    }

    this.beforeSend = function(func_declaration) {
        beforeSend = func_declaration;
    }

    this.afterReceive = function(func_declaration) {
        afterReceive = func_declaration;
    }

    this.getWeek = function() { return self.week_number; }
    this.getYear = function() { return self.year; }

    this.clear = function() { 
        self.week_number = null;
        self.year = null;
        $(input).val(""); 
    }

    function highlightActiveWeek() {
        var table_year_title_th = $(".datepicker-dropdown table thead tr th.datepicker-switch");
        var table_year_title = $(table_year_title_th).first().html().replace(/[^\d.]/g, '');

        if(table_year_title == self.year) {
            var active_tr = $(".datepicker-dropdown table td.cw").filter(function() { // najdeme řádek obsahující číslo vybraného týdne
                return $(this).text() == self.week_number;
            }).parent();

            $(".datepicker-dropdown table tr").removeClass('active');
            active_tr.find('td.active').removeClass('active');
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
            
            beforeSend();
            $(input).prop('disabled', true);

            $.get(ajax_handler, {"week": week_number, "year": year}, function(payload) {
                $.nette.success(payload);
                afterReceive();
                changeUrl("?week=" + week_number + "&year=" + year );
                $(input).prop('disabled', false);
            });  
        })

        .on("show", function(e) {
            highlightActiveWeek();
        })

        .keyup(function(e) {
            highlightActiveWeek();
        })

    }

    initDatePicker(input);

    $('body').on('click', '.datepicker-dropdown th.prev, .datepicker-dropdown th.next', function(){
        highlightActiveWeek();
    });
};