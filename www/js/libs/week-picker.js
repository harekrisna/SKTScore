var weekPicker = function(input, ajax_handler) {
    this.week_number,
    this.year,
    this.beforeSend = function() {},
    this.afterReceive = function() {};
    
    this.prev_button = $(input).prev('button.btn-left');
    this.next_button = $(input).next('button.btn-right');

    var self = this;

    this.setWeekPicker = function(year, week_number) {
        this.week_number = week_number;
        this.year = year;
        date = new Date();
        monday = date.getMondayOfWeek(week_number, year);
        $(input).datepicker('update', monday);
        $(input).val("Rok " + year + ": týden " + week_number);
        self.enableButtons();
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
        this.disableButtons();
    }

    this.disableButtons = function() {
        $(this.prev_button).prop('disabled', true);
        $(this.next_button).prop('disabled', true);
    }

    this.enableButtons = function() {
        $(this.prev_button).prop('disabled', false);
        $(this.next_button).prop('disabled', false);
    }

    this.disableControls = function() {
        $(input).prop('disabled', true);
        this.disableButtons();
    }

    this.enableControls = function() {
        $(input).prop('disabled', false);
        this.enableButtons();
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

    function sendRequest() {
        self.beforeSend();
        self.disableControls();

        $.get(ajax_handler, {"week": self.week_number, "year": self.year}, function(payload) {
            $.nette.success(payload);
            self.afterReceive();
            changeUrl("?week=" + self.week_number + "&year=" + self.year );
            self.enableControls();
        }); 
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
            sendRequest(); 
        })

        .on("show", function(event) {
            redrawActiveWeek();
        })

        .keyup(function(event) {
            redrawActiveWeek();
        });

        $(self.next_button).on("click", function(event) {
            var next_week = parseInt(self.week_number) + 1;
            var year = parseInt(self.year);

            if(next_week > date.weeksInYear(year)) {
                year = year + 1;
                next_week = 1;
            }

            self.setWeekPicker(year, next_week);
            sendRequest();
        });

        $(self.prev_button).on("click", function(event) {
            var prev_week = parseInt(self.week_number) - 1;
            var year = parseInt(self.year);

            if(prev_week == 0) {
                year = year - 1;
                prev_week = date.weeksInYear(year);
            }

            self.setWeekPicker(year, prev_week);
            sendRequest();
        });
    }

    initDatePicker(input);
};