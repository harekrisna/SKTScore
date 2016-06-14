<?php

namespace App\Model;
use Tracy\Debugger;
/**
 * Model starající se o tabulku centre
 */
class Week extends TableExtended
{
  /** @var string */
	protected $tableName = 'week';
    
    public function getOrCreateWeekId($week_number, $year) {
        $week = $this->findBy(['number' => $week_number,
                               'year' => $year])
                     ->fetch();

        if(!$week) {
            return $this->insert(['number' => $week_number,
                                  'year' => $year]);
        }
        else {
            return $week->id;
        }
    }

    public function getWeekId($week_number, $year) {
        $week = $this->findBy(['number' => $week_number,
                               'year' => $year])
                     ->fetch();

        return $week->id;
    }    
}