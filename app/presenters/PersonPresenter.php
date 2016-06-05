<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class PersonPresenter extends BasePresenter {	

	/** @var PersonFormFactory @inject */
	public $factory;
	
    protected function startup()  {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
	
	protected function createComponentPersonForm() {
		$form = $this->factory->create();
		
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Osoba byla úspěšně přidána", 'success');
			$form->getPresenter()->redirect('Result:setter');
		};
		return $form;
	}	
}
