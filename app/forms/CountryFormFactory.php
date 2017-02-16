<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class CountryFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Country */
	private $model;
	private $record;
		
	public function __construct(FormFactory $factory, \App\Model\Country $country) {
		$this->factory = $factory;
		$this->model = $country;
	}

	public function create($record = null) {
		$this->record = $record;

		$form = $this->factory->create();
		$form->add_title = "Přidat nový stát";
		$form->edit_title = "Změnit údaje";
		$form->success_add_message = "Stát byl přidán";
		$form->success_edit_message = "Údaje byly upraveny";
		
		$data = $form->addContainer("data");

		$data->addText('title', 'Název')
			 ->setRequired('Zadejte název prosím.');

		$data->addText('abbreviation', 'Zkratka')
			 ->setRequired('Zadejte zkratku prosím.');

	    $form->addSubmit('add', 'Přidat stát');
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
				$this->model->insert($values->data, false);
			}
			else {
				$this->model->update($this->record->id, $values->data);
			}
		}
		catch(\App\Model\DuplicateException $e) {
			if($e->foreign_key == "title") {
				$form['data']['title']->addError("Stát s tímto názvem již existuje.");
			}
			
			if($e->foreign_key == "abbreviation") {
				$form['data']['abbreviation']->addError("Stát s touto zkratkou již existuje.");
			}
		}
	}
}
