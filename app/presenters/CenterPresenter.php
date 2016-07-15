<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\CenterFormFactory;
use Tracy\Debugger;


class CenterPresenter extends ComplexPresenter {	
	/** @var CenterFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
        if(!$this->getUser()->isInRole('superadmin')) {
            throw new Nette\Application\BadRequestException();
        }
		$this->model = $this->center;
	}
}
