<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\AdminFormFactory;
use Tracy\Debugger;


class AdminPresenter extends ComplexPresenter {	
	/** @var AdminFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
        if(!$this->getUser()->isInRole('superadmin')) {
            throw new Nette\Application\BadRequestException();
        }
		$this->model = $this->admin;
	}

    public function actionAdd() {
        $this['form']['data']['password']->setRequired('Zadejte heslo prosím.');
    }   

    public function renderList() {
        $this->template->records = $this->model->findBy(['role' => 'admin']);
    }   
}
