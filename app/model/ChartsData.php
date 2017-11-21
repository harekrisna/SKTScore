<?php

namespace App\Model;
use Tracy\Debugger;
use DateTime;
/**
 * Model starající se o tabulku centre
 */
class ChartsData extends TableExtended
{
    /** @var string */
	protected $tableName = 'distribution';
    /** @var Person */
    private $person;

    public function __construct(\Nette\Database\Context $db, \Nette\Security\User $user, Person $person) {
        parent::__construct($db, $user);
        $this->person = $person;
    }
 

    // pole roků s polem týdnů s hodnotou kolik rozdal celkem bodů
    public function getWeeksPersonSumDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
                       ->group('year, week')
                       ->select("person_id, year, week, SUM(quantity * book.category.point_value) AS points_sum")
                       ->order('year, week');

        $distribution = [];
		
		foreach($result as $row) {
			$week = ltrim($row->week, '0');
            $distribution[$row->year][$week] = $row->points_sum;
        }
	        
        return $distribution;
    }

	// pole roků s polem měsíců s hodnotou kolik rozdal celkem bodů
    public function getMonthsPersonSumDistribution($person_id) {
		$weeks_data = $this->getWeeksPersonSumDistribution($person_id);
        $month_data = [];

        foreach ($weeks_data as $year => $weeks) {
            $month_data[$year] = [];

            foreach ($weeks as $week => $book_points) {
                $month = $this->getMonthOfWeek($year, $week);

                if(isset($month_data[$year][$month]))
                    $month_data[$year][$month] += $book_points;
                else 
                    $month_data[$year][$month] = $book_points;
            }
        }

        return $month_data;
    }
    

    // pole roků s hodnotou kolik rozdal celkem bodů
    public function getYearsPersonSumDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
                       ->group('year')
                       ->select("person_id, year, SUM(quantity * book.category.point_value) AS points_sum")
                       ->order('year');

        $distribution = [];
		
		foreach($result as $row) {
            $distribution[$row->year] = $row->points_sum;
        }
	        
        return $distribution;
    }


    // první týden výsledků dané osoby
    public function getPersonFirstWeekDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year, week')
                       ->order('year ASC, week ASC')
                       ->limit(1)
                       ->fetch();

        return $result;
    }
    
    // poslední týden výsledků dané osoby
    public function getPersonLastWeekDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year, week')
                       ->order('year DESC, week DESC')
                       ->limit(1)
                       ->fetch();

        return $result;
    }

    // první rok/měsíc výsledků osoby
    public function getPersonFirstMonthDistribution($person_id) {
        $first_week = $this->getPersonFirstWeekDistribution($person_id);
		$first_month = [];

        if(!$first_week) {
        	return null;
        }
        else {
        	return ['year' => $first_week->year,
        			'month' => $this->getMonthOfWeek($first_week->year, $first_week->week)];
        }
    }
    

	// poslední rok/měsíc výsledků osoby
    public function getPersonLastMonthDistribution($person_id) {
        $last_week = $this->getPersonLastWeekDistribution($person_id);

        if(!$last_week) {
        	return null;
        }
        else {
        	return ['year' => $last_week->year,
        			'month' => $this->getMonthOfWeek($last_week->year, $last_week->week)];
        }
    }

    // první rok výsledků dané osoby
    public function getPersonFirstYearDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year')
                       ->order('year ASC')
                       ->limit(1)
                       ->fetch();

        return $result;
    }
    
    // poslední rok výsledků dané osoby
    public function getPersonLastYearDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year')
                       ->order('year DESC')
                       ->limit(1)
                       ->fetch();

        return $result;
    }

    public function getMonthOfWeek($year, $week) {
    	$date = new DateTime();
    	$week_start_date = $date->setISODate($year, $week);
    	
    	$week_date = $week_start_date;
        $month_in_week = [];
        
        for($day = 1; $day <= 5; $day++) {
            $month_number = intval($week_date->format('m'));
           
            if(empty($month_in_week[$month_number])) {
                $month_in_week[$month_number] = 1;
            }
            else {
                $month_in_week[$month_number]++;
            }

            $week_date = $week_date->modify('+1 day');
        }

        return array_search(max($month_in_week), $month_in_week);
    }

    // vrátí první den týdne
	public function getStartDateOfWeek($year, $week) {
		$date = new DateTime();
		return $date->setISODate($year, $week);
	}

    // počet týdnů v roce
	public function getIsoWeeksInYear($year) {
	    $date = new DateTime;
	    $date->setISODate($year, 53);
	    return ($date->format("W") === "53" ? 53 : 52);
	}
	
	public function generateWeeksAxis($year_from, $week_from, $year_to, $week_to) {
		$week_from = intval($week_from);
		$week_to = intval($week_to);
	    $weeks_axis = [];
	    
	    if($year_from == $year_to) { // pouze interval v jednom roce
	    	for($week = $week_from; $week <= $week_to; $week++) {
				$year_weeks[$week] = null;
	    	}

	    	$weeks_axis[$year_from] = $year_weeks;    
	    }
	    else {  // interval s více roků
		    for($year = $year_from; $year <= $year_to; $year++) {
			    $weeks_in_year = $this->getIsoWeeksInYear($year);
			    $year_weeks = [];
			     
			    // první rok
			    if($year == $year_from) {
					for($week = $week_from; $week <= $weeks_in_year; $week++) {
						$year_weeks[$week] = null;    
			    	}    
			    }
			    elseif($year == $year_to) { // poslední rok
				    for($week = 1; $week <= $week_to; $week++) {
						$year_weeks[$week] = null;    
			    	}
			    }
			    else { // roky mezi
					for($week = 1; $week <= $weeks_in_year; $week++) {
						$year_weeks[$week] = null;    
				    }		        
			    }
			    
			    $weeks_axis[$year] = $year_weeks;
		    }
	    }
		
		return $weeks_axis;
	}

	public function generateMonthsAxis($year_from, $month_form, $year_to, $month_to) {
	    $months_axis = [];
	    
	    for($year = $year_from; $year <= $year_to; $year++) {
		    $months_in_year = 12;
		    $year_months = [];
		     
		    // první rok
		    if($year == $year_from) {
				for($month = $month_form; $month <= $months_in_year; $month++) {
					$year_months[$month] = null;    
		    	}    
		    }

		    elseif($year == $year_to) { // poslední rok
			    for($month = 1; $month <= $month_to; $month++) {
					$year_months[$month] = null;    
		    	}
		    }

		    else { // roky mezi
				for($month = 1; $month <= $months_in_year; $month++) {
					$year_months[$month] = null;    
			    }		        
		    }
		    
		    $months_axis[$year] = $year_months;
	    }
		
		return $months_axis;	
	}

	public function generateYearsAxis($year_from, $year_to) {
	    $years_axis = [];
	    
	    for($year = $year_from; $year <= $year_to; $year++) {
		    $years_axis[$year] = $year;
	    }
		
		return $years_axis;	
	}

	public function generatePersonWeeksChartData($person_id, $year_from, $week_from, $year_to, $week_to) {
		$x_data_weeks = [];
        $y_data_points = [];

    	$weeks_set = $this->generateWeeksAxis($year_from, $week_from, $year_to, $week_to);
        $chart_data = $this->getWeeksPersonSumDistribution($person_id);

        foreach($weeks_set as $year => $weeks) {
            foreach ($weeks as $week => $value) {
                $x_data_weeks[] = $year."/".$week;
                $y_data_points[] = isset($chart_data[$year][$week]) ? $chart_data[$year][$week] : 0;
            }
        }

        return ['x_data_weeks' => $x_data_weeks,
                'y_data_points' => $y_data_points];
    }

	public function generatePersonMonthsChartData($person_id, $year_from, $month_from, $year_to, $month_to) {
		$x_data_months = [];
        $y_data_points = [];

    	$months_set = $this->generateMonthsAxis($year_from, $month_from, $year_to, $month_to);
        $months_data = $this->getMonthsPersonSumDistribution($person_id);

        foreach($months_set as $year => $months) {
            foreach ($months as $month => $value) {
                $x_data_months[] = $year."/".$month;
                $y_data_points[] = isset($months_data[$year][$month]) ? $months_data[$year][$month] : 0;
            }
        }

        return ['x_data_months' => $x_data_months,
                'y_data_points' => $y_data_points];
    }    

	public function generatePersonYearsChartData($person_id, $year_from, $year_to) {
		$x_data_years = [];
        $y_data_points = [];

    	$years_set = $this->generateYearsAxis($year_from, $year_to);
        $years_data = $this->getYearsPersonSumDistribution($person_id);

        foreach($years_set as $year) {
            $x_data_years[] = $year;
            $y_data_points[] = isset($years_data[$year]) ? $years_data[$year] : 0;
        }

        return ['x_data_years' => $x_data_years,
                'y_data_points' => $y_data_points];
    }     
}
