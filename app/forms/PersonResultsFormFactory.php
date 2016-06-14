<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;

class PersonResultsFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Book */
	private $book;	
		
	public function __construct(FormFactory $factory, \App\Model\Book $book) {
		$this->factory = $factory;
		$this->book = $book;
	}

	public function create() {
		return new Multiplier(function ($person_id) {
	        $form = $this->factory->create();
	        $results = $this->form->addContainer("results");
	        $books = $this->book->findAll();
	        foreach ($books as $book) {
	        	$results->addText($book->id, $book->title)
					    ->setType('number') // <input type=number>
					    ->setDefaultValue(0)
					    ->addRule(Form::INTEGER, 'Musí být číslo')
					    ->addRule(Form::RANGE, 'Musí být v rozsahu %d do %d', array(0, 999));
	        }

	        $form->addHidden('person_id', $person_id);
	        $form->addSubmit('save', 'Uložit');
	        $form->onSuccess[] = array($this, 'formSucceeded');
	        return $form;
	    });
	}

	public function formSucceeded(Form $form, $values) {
		Debugger::fireLog($values);
	}
}
