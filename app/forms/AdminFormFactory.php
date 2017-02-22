<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Security\User;
use Nette\Security\Passwords;

class AdminFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Admin */
	private $admin;
	/** @var Center */
	private $center;
	private $record;
		
	public function __construct(FormFactory $factory, \App\Model\Admin $admin, \App\Model\Center $center) {
		$this->factory = $factory;
		$this->admin = $admin;
		$this->center = $center;
	}

	public function create($record = null) {
		$this->record = $record;

		$form = $this->factory->create();
		$form->add_title = "Přidat nového správce";
		$form->edit_title = "Změnit údaje";
		$form->success_add_message = "Správce byl přidán";
		$form->success_edit_message = "Údaje byly upraveny";
		
		$data = $form->addContainer("data");

		$data->addText('fullname', 'Jméno správce')
			 ->setRequired('Zadejte jméno prosím.');

		$data->addText('username', 'Uživatelské jméno (pro přihlášení)')
			 ->setRequired('Zadejte uživatelské jméno prosím.');

		$data->addPassword('password', 'Heslo:');

		$data->addSelect('center_id', "Centrum", $this->center->findAll()->fetchPairs('id', 'title'));

	    $form->addSubmit('add', 'Přidat správce');
	    $form->addSubmit('edit', 'Uložit změny');

	    if($record != null) {
	    	$form['data']->setDefaults($record);
	    }

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		try {
			if($values->data->password != "") {
				$values->data->password = Passwords::hash($values->data->password);
			}
			else {
				unset($values->data->password);
			}


			if($form->isSubmitted()->name == "add") {
				$this->admin->insert($values->data);
			}
			else {
				$this->admin->update($this->record->id, $values->data);
			}
		}
		catch(\App\Model\DuplicateException $e) {
			$form['data']['username']->addError("Správce s tímto jménem již existuje.");
		}
	}
}
