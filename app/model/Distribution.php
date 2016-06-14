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

    public function getResultsByPersonsAndCategories($week_id) {
        $result = $this->query("SELECT person_id, category.title AS category_title, SUM( quantity ) AS category_quantity_sum
                                FROM `distribution`
                                LEFT JOIN book ON distribution.book_id = book.id
                                LEFT JOIN category ON book.category_id = category.id
                                WHERE distribution.week_id = ?
                                GROUP BY person_id, category_id", $week_id)
                       ->fetchAll();

        $score = [];

        foreach ($result as $row) {
            $score[$row['person_id']][$row['category_title']] = $row['category_quantity_sum'];
        }

        Debugger::fireLog($score);
    }
}