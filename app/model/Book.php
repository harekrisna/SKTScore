<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

/**
 * Model starající se o tabulku centre
 */
class Book extends TableExtended {
    /** @var string */
	protected $tableName = 'book';
    
    /** @var Nette\Database\Table */
    protected $bookPriority;

    function setBookTypePriority($book_id, $type) {
        $bookPriority = $this->connection->table("book_priority");

        try {
            $bookPriority->insert(["user_id" => $this->user->getIdentity()->id,
                                   "book_id" => $book_id,
                                   "priority" => $type]);
        }
        catch(Nette\Database\UniqueConstraintViolationException $e) {
            $bookPriority->where(["user_id" => $this->user->getIdentity()->id, "book_id" => $book_id])
                         ->update(["priority" => $type]);
        }
    }
}