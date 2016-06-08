<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class BookFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Book */
	private $book;
	/** @var Category */
	private $category;
	/** @var User */
	private $user;
		
	public function __construct(FormFactory $factory, \App\Model\Book $book, \App\Model\Category $category) {
		$this->factory = $factory;
		$this->book = $book;
		$this->category = $category;
	}

	public function create() {
		$form = $this->factory->create();
		$data = $form->addContainer('data');

		$data->addText('title', 'Titul')
			 ->setRequired('Zadejte titul prosím.');

		$data->addText('abbreviation', 'Zkratka', 4, 4)
			 ->setRequired('Zadejte zkratku prosím.');

		$data->addSelect('category_id', 'Kategorie', $this->category->findAll()->fetchPairs('id', 'title'));

	    $form->addSubmit('send', 'Přidat knihu');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		try {
			$this->book->insert($values->data);
		}
		catch(\App\Model\DuplicateException $e) {
			if($e->foreign_key == "title") {
				$form['data']['title']->addError("Kniha s tímto názvem již existuje.");
			}
			
			if($e->foreign_key == "abbreviation") {
				$form['data']['abbreviation']->addError("Kniha s touto zkratkou již existuje.");
			}
		}
	}
}
