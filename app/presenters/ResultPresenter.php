<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonResultsFormFactory;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter {
	/** @persistent */
    public $week_number;
	/** @persistent */
    public $year;
    /** @var PersonResultsFormFactory @inject */
	public $factory;
	
	protected function startup() {
		parent::startup();
		if($this->week_number == "")
			$this->week_number = date("W");

		if($this->year == "")
			$this->year = date("Y");
	}

	public function renderSetter()	{
		//$this->template->weeks_in_year = gmdate("W", strtotime("31 December 2016"));
		$this->template->week_number = $this->week_number;
		$this->template->year = $this->year;
		$this->template->persons = $this->person->findAll();
		$this->template->books = $this->book->findAll();
	}

	protected function createComponentPersonResultsForm() {
		$form = $this->factory->create();
		return $form;
	}
}
