<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class PersonPresenter extends ComplexPresenter {	
	/** @var PersonFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
		$this->model = $this->person;
	}
}
