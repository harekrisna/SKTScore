<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Security\User;

class PersonFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Person */
	private $person;
	/** @var User */
	private $user;
	private $record;
		
	public function __construct(FormFactory $factory, \App\Model\Person $person, User $user) {
		$this->factory = $factory;
		$this->person = $person;
		$this->user = $user;
	}

	public function create($record = null) {
		$this->record = $record;

		$form = $this->factory->create();
		$form->add_title = "Přidat novou osobu";
		$form->edit_title = "Změnit údaje";
		$form->success_add_message = "Osoba byla přidána";
		$form->success_edit_message = "Údaje byly upraveny";
		
		$data = $form->addContainer("data");

		$data->addText('name', 'Jméno')
			 ->setRequired('Zadejte jméno prosím.');

	    $form->addSubmit('add', 'Přidat osobu');
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
				$values->data['center_id'] = $this->user->getIdentity()->center_id;
				$this->person->insert($values->data);
			}
			else {
				$this->person->update($this->record->id, $values->data);
			}
		}
		catch(\App\Model\DuplicateException $e) {
			$form['data']['name']->addError("Osoba s tímto jménem již existuje.");
		}
	}
}
