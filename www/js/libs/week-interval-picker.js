var weekIntervalPicker = function(input_from, input_to, ajax_handler) {
    var week_from, 
        week_to, 
        year_from, 
        year_to,
        beforeSend = function() {},
        afterReceive = function() {};

    var selected_week_number;
    
    var self = this;

    this.setFrom = function(year, week_number) {
        $(input_from).val("Rok " + year + ": týden " + week_number);
        year_from = year;
        week_from = week_number;

        var date = new Date();
        var monday_date = date.getMondayOfWeek(week_number, year);
        var monday_mysql = date.convertDateToMySQLdate(monday_date);
        //$(input_to).datepicker('setStartDate', monday_mysql);
    }
    
    this.setTo = function(year, week_number) {
        $(input_to).val("Rok " + year + ": týden " + week_number);
        year_to = year;
        week_to = week_number;

        var date = new Date();
        var sunday_date = date.getSundayOfWeek(week_number, year);
        var sunday_mysql = date.convertDateToMySQLdate(sunday_date);
        //$(input_from).datepicker('setEndDate', sunday_mysql);
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

    function redrawActiveWeek() {
        var active_tr = $(".datepicker-dropdown table td.cw").filter(function() {
            return $(this).text() == selected_week_number;
        }).parent();

        $(".datepicker-dropdown table tr").removeClass('active');
        active_tr.find('td.active').removeClass('active');
        active_tr.attr('class', "active");
        
        // odstranění hover efektu nad neaktivnímy týdny
        active_tr = $(".datepicker-dropdown table tbody tr").each(function() {
            var cells = $(this).find('td.disabled');
            if(cells.length > 0) {
                var first_cell = $(this).find('td:first-child');
                first_cell.addClass('disabled');
            }
        });
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
        .click(function() { // při kliknutí na input se zvýrazní vybraný týden
            if('#' + this.id == input_from) {
                selected_week_number = week_from;
            }
            else if('#' + this.id == input_to) {
                selected_week_number = week_to;
            }
            redrawActiveWeek();
        })

        .keyup(function() { // při kliknutí na input se zvýrazní vybraný týden
            redrawActiveWeek();
        });

        $(input).on('change', function (e) { // při změně týdne ze změní zvýrazněný
            var value = $(input).val();
            var only_int_value = value.replace(/-/g, '');

            if(!isNaN(only_int_value)) {
                date = new Date()
                if(value == "") {
                    value = date.nowMySQLdate();
                }

                var active_tr = $('.datepicker-dropdown table td.active.day').parent();
                active_tr.find('td.active').removeClass('active');
                active_tr.addClass('active');
                                    
                var week_number = date.getWeek(value);
                var year = value.substring(0, 4);

                if('#' + this.id == input_from) { // změna od
                    self.setFrom(year, week_number);
                    if(year + padLeft(week_number, 2) > year_to + padLeft(week_to, 2)) { // pokud je vybrán týden od, který je větší než do, týden do se navýší
                        self.setTo(year, week_number);    
                    }
                }
                else if('#' + this.id == input_to) { // změna do
                    self.setTo(year, week_number);
                    if(year + padLeft(week_number, 2) < year_from + padLeft(week_from, 2)) { // pokud je vybrán týden do, který je menší než od, týden od se sníží
                        self.setFrom(year, week_number);    
                    }
                }

                beforeSend();
                $(input_from).prop('disabled', true);
                $(input_to).prop('disabled', true);


                $.get(ajax_handler, {"week_from": week_from, 
                                     "year_from": year_from,
                                     "week_to": week_to, 
                                     "year_to": year_to,
                                    }, 
                    function(payload) {
                        $.nette.success(payload);
                        afterReceive();
                        $(input_from).prop('disabled', false);
                        $(input_to).prop('disabled', false);
                        changeUrl("?week_from=" + week_from + "&year_from=" + year_from + "&week_to=" + week_to + "&year_to=" + year_to);
                    }
                );
            }
        });
    }

    initDatePicker(input_from);
    initDatePicker(input_to);

    // zvýraznění týdne při přeskakování na další a předchozí stránky datepickeru
    $('body').on('click', '.datepicker-dropdown th.prev, .datepicker-dropdown th.next', function(){
        redrawActiveWeek();
    });
}