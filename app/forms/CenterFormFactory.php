<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class CenterFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Center */
	private $center;
	/** @var Country */
	private $country;
	private $record;
		
	public function __construct(FormFactory $factory, \App\Model\Center $center, \App\Model\Country $country) {
		$this->factory = $factory;
		$this->center = $center;
		$this->country = $country;
	}

	public function create($record = null) {
		$this->record = $record;

		$form = $this->factory->create();
		$form->add_title = "Přidat nové centrum";
		$form->edit_title = "Změnit údaje";
		$form->success_add_message = "Centrum bylo přidáno";
		$form->success_edit_message = "Údaje byly upraveny";
		
		$data = $form->addContainer("data");

		$data->addText('title', 'Název')
			 ->setRequired('Zadejte název prosím.');

		$data->addText('abbreviation', 'Zkratka')
			 ->setRequired('Zadejte zkratku prosím.');
		
		$country_items = $this->country->findAll()->fetchPairs('id', 'title');
		$data->addSelect("country_id", "Stát", $country_items);
	    
	    $form->addSubmit('add', 'Přidat centrum');
	    $form->addSubmit('edit', 'Uložit změny');

	    if($record != null) {
	    	$form['data']->setDefaults($record);
	    }

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		try {
			if($form->isSubmitted()->name == "add") {
				$this->center->insert($values->data);
			}
			else {
				$this->center->update($this->record->id, $values->data);
			}
		}
		catch(\App\Model\DuplicateException $e) {
			if($e->foreign_key == "title") {
				$form['data']['title']->addError("Centrum s tímto názvem již existuje.");
			}
			
			if($e->foreign_key == "abbreviation") {
				$form['data']['abbreviation']->addError("Centrum s touto zkratkou již existuje.");
			}
		}
	}
}
