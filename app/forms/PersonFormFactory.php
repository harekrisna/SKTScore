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
	/** @var Center */
	private $center;	
	/** @var User */
	private $user;
	private $record;
		
	public function __construct(FormFactory $factory, \App\Model\Person $person, \App\Model\Center $center, User $user) {
		$this->factory = $factory;
		$this->person = $person;
		$this->center = $center;
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
		
        if($this->user->isInRole('superadmin')) { // super admin smí přidávat osoby do jakéhokoliv centra
            $center_items = $this->center->findAll()->fetchPairs('id', 'title');
        }
        else { // ostatní uživatelé smí přidávat osoby pouze do svého centra
            $center_id = $this->user->getIdentity()->center_id;
            $center_title = $this->center->get($center_id)['title'];

            $center_items = [$center_id => $center_title];
        }

		$data->addSelect("center_id", "Centrum", $center_items);

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
				if(!$this->user->isInRole("superadmin")) {
					$values->data['center_id'] = $this->user->getIdentity()->center_id;
				}

				$this->person->insert($values->data);
			}
			else {
				$this->person->update($this->record->id, $values->data);
			}
		}
		catch(\App\Model\DuplicateException $e) {
			$form['data']['name']->addError("Osoba s tímto jménem v tomto centru již existuje.");
		}
	}
}
