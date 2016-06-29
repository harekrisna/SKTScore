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

    public function insertResult($person_id, $week, $year, $book_id, $quantity) {
        $distribution_record = $this->findBy(['person_id' => $person_id, 
                                              'week' => $week,
                                              'year' => $year,
                                              'book_id' => $book_id])
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
                                     ['quantity' => $quantity]);
            }
        }
        else {
            if($quantity > 0) {
                return $this->insert(['person_id' => $person_id, 
                                      'week' => $week,
                                      'year' => $year,
                                      'book_id' => $book_id,
                                      'quantity' => $quantity]);
            }
        }
    }   


    public function getPersonsCategoriesDistribution($week, $year) {
        $result = $this->findBy(['week' => $week,
                                 'year' => $year])
                       ->group('person_id, book.category.id')
                       ->select("person_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum");

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }

    public function getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $result = $this->getTable()->select("person_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum")
                                   ->where('(week >= ? AND year >= ?) AND (week <= ? AND year <= ?)', $week_from, $year_from, $week_to, $year_to)
                                   ->group('person_id, book.category.id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }    

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

    public function getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to) {
        $result = $this->getTable()->select('person_id, SUM(quantity * book.category.point_value) AS points_sum')
                                   ->group('person_id')
                                   ->where('(week >= ? AND year >= ?) AND (week <= ? AND year <= ?)', $week_from, $year_from, $week_to, $year_to);
                       

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = $row['points_sum'];
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

    public function getPersonsBooksDistribution($week, $year) {
        $result = $this->findBy(['week' => $week,
                                 'year' => $year]);
        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }

    public function getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to) {
        $result = $this->getTable()->select('person_id, book_id, SUM(quantity) AS quantity')
                                   ->where('(week >= ? AND year >= ?) AND (week <= ? AND year <= ?)', $week_from, $year_from, $week_to, $year_to)
                                   ->group('person_id, book_id');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }    
}