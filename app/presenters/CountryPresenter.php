<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\CountryFormFactory;
use Tracy\Debugger;


class CountryPresenter extends ComplexPresenter {	
	/** @var CountryFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
        if(!$this->getUser()->isInRole('superadmin')) {
            throw new Nette\Application\BadRequestException();
        }
		$this->model = $this->country;
	}
}
