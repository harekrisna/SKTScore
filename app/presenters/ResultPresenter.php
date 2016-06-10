<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter {
	
	public function renderSetter()	{
		//$this->template->weeks_in_year = gmdate("W", strtotime("31 December 2016"));
		$this->template->persons = $this->person->findAll();
	}
}
