<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter {
	/** @var PersonFormFactory @inject */
	public $factory;
		
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
	public function renderSetter()	{
		$this->template->weeks_in_year = gmdate("W", strtotime("31 December 2016"));
		$this->template->persons = $this->person->findAll();
	}
	
	protected function createComponentPersonForm() {
		$form = $this->factory->create();
		
		$form->onSuccess[] = function ($form, $values) {
			$this->flashMessage("Osoba byla úspěšně přidána", 'success');
			$form['name']->setValue("");
			$this->payload->success = true;
			$this->payload->name = $values->name;
			$this->redrawControl("personForm");
		};
		
		$form->onError[] = function ($form) {
			$this->flashMessage("Osoba s tímto jménem již existuje", 'error');
		};
		
		return $form;
	}
}
