<?php

namespace App\Model;
use Tracy\Debugger;
/**
 * Model starající se o tabulku centre
 */
class Distribution extends TableExtended
{
    /** @var string */
	  protected $tableName = 'distribution';
    /** @var Nette\Database\Table\Selection */
    private $centers_to_show;
    /** @var Person */
    private $person;

    public function __construct(\Nette\Database\Context $db, \Nette\Security\User $user, ShowCenter $show_center, Person $person) {
        parent::__construct($db, $user);
        $this->person = $person;
        $this->centers_to_show = $show_center->findBy(['user_id' => $this->user->getId()])
                                             ->select('center_id');

    }

    public function setCentersToShow($centers_to_show) {
        $this->centers_to_show = $centers_to_show;
    }

    public function insertResult($person_id, $week, $year, $center_id, $book_id, $quantity) {
        $week = str_pad($week, 2, "0", STR_PAD_LEFT);
	    
        $distribution_record = $this->findBy(['person_id' => $person_id, 
                                              'week' => $week,
                                              'year' => $year,
                                              'book_id' => $book_id
                                              ])
                                    ->fetch();

        if($distribution_record) {
            if($quantity == 0) {
                return $distribution_record->delete();
            }
            else {
                return $this->update(['person_id' => $person_id, 
                                      'week' => $week,
                                      'year' => $year,
                                      'book_id' => $book_id],
                                     ['quantity' => $quantity,
                                      'center_id' => $center_id]
                                    );
            }
        }
        else {
            if($quantity > 0) {
                return $this->insert(['person_id' => $person_id, 
                                      'week' => $week,
                                      'year' => $year,
                                      'book_id' => $book_id,
                                      'center_id' => $center_id,
                                      'quantity' => $quantity]);
            }
        }
    }   

    // pole osob s polem kategorií s hodnotou kolik rozdal v této kategorii a za jaké centrum za daný týden
    public function getPersonsCategoriesDistribution($week, $year) {
        $week = str_pad($week, 2, "0", STR_PAD_LEFT);

        $result = $this->findBy(['week' => $week,
                                 'year' => $year])
                       ->group('person_id, book.category.id')
                       ->select("person_id, center_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum");

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
            $score[$row['person_id']]['center_id'] = $row['center_id'];
        }

        return $score;
    }


    // pole osob s polem kategorií s hodnotou kolik rozdal v této kategorii
    public function getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select("person_id, center.title AS center, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum")
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id, book.category.id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
        }
        
        
        $result = $this->getTable()->select('person_id, center.id AS center_id, center.title AS center_title, COUNT(DISTINCT(distribution.center_id)) AS diff_centers')
            								       ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
            								       ->group('person_id');
                               
	      foreach ($result as $row) {
	          if($row->diff_centers > 1) { // osoba má v daném období výsledky patřící do více center 
                $person = $this->person->get($row['person_id']);
                $score[$row['person_id']]['center_id'] = $person->center->id; // hlavní centrum osoby
		        	  $score[$row['person_id']]['center_title'] = $person->center->title; // hlavní centrum osoby
		        } 
	          else { // osoba má v daném období výsledky patřící do jednoho center 
                $score[$row['person_id']]['center_id'] = $row['center_id'];
		            $score[$row['person_id']]['center_title'] = $row['center_title'];
	          }
        }
		
        return $score;
    }    

    // pole osob s polem kategorie s hodnotou kolik rozdal v této kategorii
    public function getCategoriesDistributionSumInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select("person_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum")
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('book.category.id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }

    // pole osob se součtem mahá a big
    public function getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select("person_id, SUM(quantity) AS mahabig_quantity_sum")
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('book.category.title = ? OR book.category.title = ?', "Mahá", "Big")
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = $row['mahabig_quantity_sum'];
        }

        return $score;
    }  

    // celkový součet mahá a big v daném období
    public function getMahaBigSumDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select("SUM(quantity) AS mahabig_quantity_sum")
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('book.category.title = ? OR book.category.title = ?', "Mahá", "Big")
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->fetch();

        return $result['mahabig_quantity_sum'];
    } 

    // pole katekorií se součtem rozdaných knih pro danou osobu
    public function getPersonCategoriesDistribution($person_id, $week, $year) {
        $result = $this->findBy(['week' => $week,
                                 'year' => $year,
                                 'person_id' => $person_id])
                       ->group('book.category.id')
                       ->select("book.category.title AS category_title, SUM(quantity) AS category_quantity_sum");

        $score = [];
        foreach ($result as $row) {
            $score[$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }

    public function getPersonsSumPoints($week, $year) {
        $week = str_pad($week, 2, "0", STR_PAD_LEFT);
        
        $result = $this->findBy(['week' => $week,
                                 'year' => $year])
                       ->group('person_id')
                       ->select('person_id, SUM(quantity * book.category.point_value) AS points_sum');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = $row['points_sum'];
        }
        
        return $score;
    }

    public function getPersonsRankBySumPoints($week, $year) {
        $week = str_pad($week, 2, "0", STR_PAD_LEFT);
        
        $result = $this->findBy(['week' => $week,
                                 'year' => $year])
                       ->group('person_id')
                       ->select('person_id, SUM(quantity * book.category.point_value) AS points_sum')
                       ->where('person.center_id IN(?)', $this->centers_to_show)
                       ->order('points_sum DESC');

        $ranks = [];
        $rank = 0;
        $iterator = 0;
        $last_person_sum_points = INF;
        
        foreach ($result as $row) {
            $iterator++;            
            if($last_person_sum_points > $row['points_sum']) {
               $rank = $iterator;
            }

            $ranks[$row['person_id']] = $rank;
            $last_person_sum_points = $row['points_sum'];
        }
        
        return $ranks;
    }

    public function getPersonsRankBySumPointsInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);
        
        $result = $this->getTable()->select('person_id, SUM(quantity * book.category.point_value) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id')
                                   ->order('points_sum DESC');

        $ranks = [];
        $rank = 0;
        $iterator = 0;
        $last_person_sum_points = INF;
        
        foreach ($result as $row) {
            $iterator++;            
            if($last_person_sum_points > $row['points_sum']) {
               $rank = $iterator;
            }

            $ranks[$row['person_id']] = $rank;
            $last_person_sum_points = $row['points_sum'];
        }
        
        return $ranks;
    }

    // pole osob se součtem bodů v časovém intervalu
    public function getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, SUM(quantity * book.category.point_value) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id')
                                   ->order('points_sum DESC');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = floatval($row['points_sum']);
        }
        
        return $score;
    }

    // nezaokrouhlený součet bodů všech osob v časovém intervalu
    public function getAllSumPointsInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('SUM(quantity * book.category.point_value) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->fetch();

        return $result['points_sum'];
    }

    // nezaokrouhlený součet bodů všech osob v časovém intervalu
    public function getAllSumBooksInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('SUM(quantity) AS quantity_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->fetch();
								   
        return $result['quantity_sum'];
    }

    // správně zaokrouhlený součet bodů všech osob v časovém intervalu
    public function getAllSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, ROUND(SUM(quantity * book.category.point_value)) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id');
                       
        $total_sum_points = 0;
        foreach ($result as $row) {
            $total_sum_points += $row['points_sum'];
        }
        
        return $total_sum_points;
    }

    // vrátí pole osob s počtem týdnů, které v daném období rozdávala
    public function getPersonsWeeksDistribution($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, COUNT(DISTINCT(concat(week, year))) AS weeks_count')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id');
                       
        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = $row['weeks_count'];
        }
        
        return $score;
    }
    
    // pole center s počtem týdnů, které v daném období rozdávalo
    public function getCentersWeeksDistribution($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('center.id AS center_id, COUNT(DISTINCT(concat(week, year))) AS weeks_count')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('center_id');
                       
        $score = [];
        foreach ($result as $row) {
            $score[$row['center_id']] = $row['weeks_count'];
        }
        
        return $score;
    } 

    public function getPersonSumPoints($person_id, $week, $year) {
        $points_sum = $this->findBy(['week' => $week,
                                     'year' => $year,
                                     'person_id' => $person_id])
                           ->sum("quantity * book.category.point_value");
      
        return $points_sum;
    }

    // pole knih s počtem rozdaných knih pro konkrétní osobu
    public function getPersonBooksDistribution($person_id) {
        $result = $this->getTable()->select('book.title AS book_title, SUM(quantity) AS quantity')
                                   ->where('person_id', $person_id)
                                   ->group('book_id');
        $score = [];
        foreach ($result as $row) {
            $score[$row['book_title']] = $row['quantity'];
        }
        
        return $score;
    }

    // pole knih s počtem rozdaných knih pro konkrétní osobu v daném intervalu
    public function getPersonBooksDistributionInterval($person_id, $week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('book.id AS book_id, SUM(quantity) AS quantity')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person_id', $person_id)
                                   ->group('book_id');
        $score = [];
        foreach ($result as $row) {
            $score[$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }

    // pole osob s polem knih s počtem rozdaných knih za konkrétní týden
    public function getPersonsBooksDistribution($week, $year) {
        $week = str_pad($week, 2, '0', STR_PAD_LEFT);
        $result = $this->findBy(['week' => $week,
                                 'year' => $year]);
        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }

    public function getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, book_id, SUM(quantity) AS quantity, concat(year, week) AS yearweek')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('person_id, book_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }

    // pole knih s polem center s polem osoba_id a množství rozdaných knih
    public function getBooksPersonsDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, book_id, person.center_id, SUM(quantity) AS quantity, concat(year, week) AS yearweek')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('book_id, center_id, person_id');
        $score = [];
        foreach ($result as $row) {
            $score[$row['book_id']][$row['center_id']][] = ["person_id" => $row['person_id'],
                                                            "quantity" => $row['quantity']];
        }
        
        return $score;
    }

    // pole knih s polem center s hodnotou počtu rozdaných knih
    public function getBooksCentersDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('book_id, person.center_id, SUM(quantity) AS quantity, concat(year, week) AS yearweek')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('book_id, center_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['book_id']][$row['center_id']] = $row['quantity'];
        }
        
        return $score;
    }    


    // pole center s hodnotou: "zaokrouhlený celkový počet bodů"
    /* už asi není potřeba
    public function getCentersSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('person_id, center_id, ROUND(SUM(quantity * book.category.point_value)) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->group('person_id');
        
        $score = [];
        foreach ($result as $row) {
            if(empty($score[$row['center_id']])) {
               $score[$row['center_id']] = 0;
            }
            $score[$row['center_id']] += $row['points_sum'];
        }
        
        return $score;
    }   
	*/

    // pole center s hodnotou: "nezaokrouhlený celkový počet bodů"
    public function getCentersSumPointsInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('center.id AS center_id, SUM(quantity * book.category.point_value) AS points_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('center_id');
        
        $score = [];
        foreach ($result as $row) {
            $score[$row['center_id']] = $row['points_sum'];
        }
        
        return $score;
    }  
    

    // pole center s polem kategorií s hodnotou počet rozdaný knih v této kategorii
    public function getCentersCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('center.id AS center_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('center_id, category_title');
        
        $score = [];
        foreach ($result as $row) {
            $score[$row['center_id']][$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    } 


    // pole center s počtem rozdaných mahá a big knih
    public function getCentersMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('center.id AS center_id, book.category.title AS category_title, SUM(quantity) AS mahabig_sum')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('book.category.title = ? OR book.category.title = ?', "Mahá", "Big")
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('center_id');
        
        $score = [];
        foreach ($result as $row) {
            $score[$row['center_id']] = $row['mahabig_sum'];
        }

        return $score;
    } 

    // pole center s hodnotou: "celkový počet rozdaných knih"
    public function getCentersSumDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('center.id AS center_id, SUM(quantity) AS quantity')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('center_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['center_id']] = $row['quantity'];
        }
        
        return $score;
    }   

    // pole knih s hodnotou: "celkový počet rozdaných knih"
    public function getBooksSumDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $yearweek_from = $year_from.str_pad($week_from, 2, '0', STR_PAD_LEFT);
        $yearweek_to = $year_to.str_pad($week_to, 2, '0', STR_PAD_LEFT);

        $result = $this->getTable()->select('book_id, SUM(quantity) AS quantity')
                                   ->where('concat(year, week) >= ? AND concat(year, week) <= ?', $yearweek_from, $yearweek_to)
                                   ->where('person.center_id IN(?)', $this->centers_to_show)
                                   ->group('book_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }
}
