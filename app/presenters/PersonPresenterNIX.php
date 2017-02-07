<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;


class PersonPresenterNIX extends BasePresenter {	

	/** @var PersonFormFactory @inject */
	public $factory;
	
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
	public function actionAdd()	{
		$this['personForm']->setDefaults(array(
			"das" => "d"
		));
	}
		
	protected function createComponentPersonForm() {
		$form = $this->factory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Result:setter');
		};
		return $form;
	}	
	
}
