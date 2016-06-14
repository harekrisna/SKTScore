<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter {
	/** @persistent */
    public $week_number;
	/** @persistent */
    public $year;
    

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
		$week_id = $this->week->getWeekId($this->week_number, $this->year);
		$this->template->distribution = $this->distribution->findBy(['week_id' => $week_id]);
		$this->distribution->getResultsByPersonsAndCategories($week_id);
	}

	public function createComponentPersonResultsForm() {
		$form = new Multiplier(function ($person_id) {
            $form = new Form;
            $results = $form->addContainer("results");
            $books = $this->book->findAll();
            foreach ($books as $book) {
                $results->addText($book->id, $book->title)
                        ->setType('number') // <input type=number>
                        ->setDefaultValue(0)
                        ->addRule(Form::INTEGER, 'Musí být číslo')
                        ->addRule(Form::RANGE, 'Musí být v rozsahu %d do %d', array(0, 999));
            }

            $form->addHidden('person_id', $person_id);
            $form->addSubmit('save', 'Uložit');
            $form->onSuccess[] = array($this, 'saveResults');
            return $form;
        });
		return $form;
	}

    public function saveResults(Form $form, $values) {
        $week_id = $this->week->getOrCreateWeekId($this->week_number, $this->year);

        foreach ($values->results as $book_id => $quantity) {
            $this->distribution->insertResult($values->person_id, $week_id, $book_id, $quantity);    
        }

        $this->flashMessage("Výsledky byly uloženy", 'success');
        $this->redrawControl('flashes');
    }    
}
