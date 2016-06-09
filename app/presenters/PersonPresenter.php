<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class PersonPresenter extends BasePresenter {	

	/** @var PersonFormFactory @inject */
	public $factory;

	public function renderAdd() {
		$this->setView("form");
		$this->template->form_title = "Přidat osobu";
	}

	protected function createComponentPersonForm() {
		$form = $this->factory->create();
		
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Osoba byla úspěšně přidána", 'success');
			$form->getPresenter()->redirect('Person:add');
		};
		return $form;
	}	
}
