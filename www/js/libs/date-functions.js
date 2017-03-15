Date.prototype.getWeek = function(date_string = null) {
    if(date_string != null)
	   var date = new Date(date_string);
    else
       var date = this;

	date.setHours(0, 0, 0, 0);
	// Thursday in current week decides the year.
	date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
	// January 4 is always in week 1.
	var week1 = new Date(date.getFullYear(), 0, 4);
	// Adjust to Thursday in week 1 and count number of weeks from date to week1.
	return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000
		                    - 3 + (week1.getDay() + 6) % 7) / 7);
}

// Returns the four-digit year corresponding to the ISO week of the date.
Date.prototype.getWeekYear = function() {
	var date = new Date(this.getTime());
	date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
	return date.getFullYear();
}

Date.prototype.nowMySQLdate = function() {
	return date.getFullYear()  + '-' +
		   ('0' + (date.getMonth()+1)).slice(-2) + "-" +
		   ('0' + date.getDate()).slice(-2);
}

Date.prototype.getMondayOfWeek = function(w, y) {
    var simple = new Date(y, 0, 1 + (w - 1) * 7);
    var dow = simple.getDay();
    var ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
    return ISOweekStart;
}

Date.prototype.getSundayOfWeek = function(w, y) {
    var simple = new Date(y, 0, 1 + (w - 1) * 7);
    var dow = simple.getDay();
    var ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1 + 6);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay() + 6);
    return ISOweekStart;
}

// číslo prvního týdne v daném měsíc a v daném roce
Date.prototype.getMonthStartWeek = function(year, month) {
    var first_day_of_month = year + "-" + month + "-01";
    //console.log(first_day_of_month);
    date = new Date(first_day_of_month);
    first_day_of_month_daynum = date.getDay();
    if(first_day_of_month_daynum == 0) first_day_of_month_daynum = 7; // korekce na neděli
    
    week = date.getWeek(first_day_of_month);
    if(first_day_of_month_daynum > 3) { // pokud měsíc začíná déle než ve středu budeme začínat od následujícího týdne, protože první týden náleží minulému měsíci
        week = week + 1;
    }

    if(date.getMonth() == 0) // v lednu začínáme prvním týdnem
        week = 1;

    return week;
}

// číslo posledního týdne v daném měsíc a v daném roce
Date.prototype.getMonthLastWeek = function(year, month) {
    month = parseInt(month, 10);
    var date = new Date(year, month, 0);

    last_day_of_month_daynum = date.getDay();
    if(last_day_of_month_daynum == 0) last_day_of_month_daynum = 7; // korekce na neděli

    week = date.getWeek(date);
    if(last_day_of_month_daynum < 3) { // pokud měsíc končí v pondělí nebo v úterý tak ten týden se započítává až do dalšího
        week = week - 1;
    }

    if(date.getMonth() + 1 == 12) // pokud se jedná o prosinec nastaví se poslední týden v roce
        week = date.weeksInYear(date.getFullYear());

    return week;
}

Date.prototype.convertDateToMySQLdate = function(date) {
    return date.getFullYear()  + '-' +
           ('0' + (date.getMonth()+1)).slice(-2) + "-" +
           ('0' + date.getDate()).slice(-2);
}

Date.prototype.getMonday = function(date_string) {
    var d = new Date(date_string);
    var day = d.getDay(),
        diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
    return new Date(d.setDate(diff));
}

Date.prototype.getSunday = function(date_string) {
    var d = new Date(date_string);
    var day = d.getDay(),
        diff = d.getDate() - day + (day == 0 ? -6:1) + 6; // adjust when day is sunday
    return new Date(d.setDate(diff));
}

Date.prototype.weeksInYear = function(year) {
    
    function getWeekNumber(d) {
        // Copy date so don't modify original
        d = new Date(+d);
        d.setHours(0,0,0);
        // Set to nearest Thursday: current date + 4 - current day number
        // Make Sunday's day number 7
        d.setDate(d.getDate() + 4 - (d.getDay()||7));
        // Get first day of year
        var yearStart = new Date(d.getFullYear(),0,1);
        // Calculate full weeks to nearest Thursday
        var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7)
        // Return array of year and week number
        return [d.getFullYear(), weekNo];
    }

    var d = new Date(year, 11, 31);
    var week = getWeekNumber(d)[1];
    return week == 1? getWeekNumber(d.setDate(24))[1] : week;
}