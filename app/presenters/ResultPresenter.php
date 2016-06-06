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
	}
	
	protected function createComponentPersonForm() {
		$form = $this->factory->create();
		
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Osoba byla úspěšně přidána", 'success');
			$this->redrawControl('lolexx');
		};
		
		$form->onError[] = function ($form) {
			$this->redrawControl('lolexx');	
		};
		
		return $form;
	}
}
