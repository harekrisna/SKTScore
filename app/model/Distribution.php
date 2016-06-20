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

    public function insertResult($person_id, $week_id, $book_id, $quantity) {
        $distribution_record = $this->findBy(['person_id' => $person_id, 
                                              'week_id' => $week_id,
                                              'book_id' => $book_id])
                                    ->fetch();

        if($distribution_record) {
            if($quantity == 0) {
                return $distribution_record->delete();
            }
            else {
                return $this->update(['person_id' => $person_id, 
                                      'week_id' => $week_id,
                                      'book_id' => $book_id],
                                     ['quantity' => $quantity]);
            }
        }
        else {
            if($quantity > 0) {
                return $this->insert(['person_id' => $person_id, 
                                      'week_id' => $week_id,
                                      'book_id' => $book_id,
                                      'quantity' => $quantity]);
            }
        }
    }   


    public function getPersonsCategoriesDistribution($week_id) {
        $result = $this->findBy(['week_id' => $week_id])
                       ->group('person_id, book.category.id')
                       ->select("person_id, book.category.title AS category_title, SUM(quantity) AS category_quantity_sum");

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }

    public function getPersonCategoriesDistribution($person_id, $week_id) {
        $result = $this->findBy(['week_id' => $week_id,
                                 'person_id' => $person_id])
                       ->group('book.category.id')
                       ->select("book.category.title AS category_title, SUM(quantity) AS category_quantity_sum");

        $score = [];
        foreach ($result as $row) {
            $score[$row['category_title']] = $row['category_quantity_sum'];
        }

        return $score;
    }

    public function getPersonsSumPoints($week_id) {
        $result = $this->findBy(['week_id' => $week_id])
                       ->group('person_id')
                       ->select('person_id, SUM(quantity * book.category.point_value) AS points_sum');

        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']] = $row['points_sum'];
        }
        
        return $score;
    }

    public function getPersonSumPoints($person_id, $week_id) {
        $points_sum = $this->findBy(['week_id' => $week_id,
                                 'person_id' => $person_id])
                           ->sum("quantity * book.category.point_value");
      
        return $points_sum;
    }

    public function getPersonsBooksDistribution($week_id) {
        $result = $this->findBy(['week_id' => $week_id]);
        $score = [];
        foreach ($result as $row) {
            $score[$row['person_id']][$row['book_id']] = $row['quantity'];
        }
        
        return $score;
    }
}