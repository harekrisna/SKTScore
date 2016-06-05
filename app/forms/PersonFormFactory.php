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
		
	public function __construct(FormFactory $factory, \App\Model\Person $person, User $user) {
		$this->factory = $factory;
		$this->person = $person;
		$this->user = $user;
	}

	public function create() {
		$form = $this->factory->create();
				
		$form->addText('name', 'Jméno')
			 ->setRequired('Zadejte jméno prosím.');

	    $form->addSubmit('send', 'Přidat osobu');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		try {
			$this->person->insert(['name' => $values['name'],
								   'created_by_user_id' => $this->user->getIdentity()->id]);
		}
		catch(\App\Model\DuplicateException $e) {
			$form['name']->addError("Osoba s tímto jménem již existuje.");
		}
	}
}
