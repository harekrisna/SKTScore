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
 

    // pole osob s polem kategorií s hodnotou kolik rozdal v této kategorii a za jaké centrum za daný týden
    public function getPersonWeeksDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
                       ->group('year, week')
                       ->select("person_id, year, week, SUM(quantity * book.category.point_value) AS points_sum")
                       ->order('year, week');

        return $result;
    }
    
    // první měsíc s výsledky dané osoby
    public function getPersonFirstWeeksDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year, week')
                       ->order('year, week')
                       ->limit(1)
                       ->fetch();

        return $result;
    }    
    
    // první měsíc s výsledky dané osoby
    public function getPersonLastWeeksDistribution($person_id) {
        $result = $this->findBy(['person_id' => $person_id])
        			   ->group('year, week')
                       ->order('year DESC, week DESC')
                       ->limit(1)
                       ->fetch();

        return $result;
    } 
    
    // počet týdnů v roce
	function getIsoWeeksInYear($year) {
	    $date = new DateTime;
	    $date->setISODate($year, 53);
	    return ($date->format("W") === "53" ? 53 : 52);
	}
	
	function generateWeeksAxis($year_from, $week_form, $year_to, $week_to) {		    
	    $weeks_axis = [];
	    
	    Debugger::fireLog($year_from);
	    Debugger::fireLog($week_form);
	    Debugger::fireLog($year_to);
	    Debugger::fireLog($week_to);
	    
	    for($year = $year_from; $year <= $year_to; $year++) {
		    $weeks_in_year = $this->getIsoWeeksInYear($year);
		    $year_weeks = [];
		     
		    // první rok
		    if($year == $year_from) {
				for($week = $week_form; $week <= $weeks_in_year; $week++) {
					$year_weeks[$week] = 0;    
		    	}    
		    }
		    elseif($year == $year_to) { // poslední rok
			    for($week = 1; $week <= $week_to; $week++) {
					$year_weeks[$week] = 0;    
		    	}
		    }
		    else { // roky mezi
				for($week = 1; $week <= $weeks_in_year; $week++) {
					$year_weeks[$week] = 0;    
			    }		        
		    }
		    
		    $weeks_axis[$year] = $year_weeks;
	    }
		
		return $weeks_axis;
	}
}
