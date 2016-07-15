<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter {
    public $books;
    private $week;
    private $year;

	public function actionSetter($week, $year) {
		if($week == null) $week = date('W');
        if($year == null) $year = date('Y');
        
        $this->week = $week;
        $this->year = $year;
        
		if($this->getSignal() == null) { // při odeslání personResultsForm nedělat nic
			$book_distribution = $this->distribution->getPersonsBooksDistribution($week, $year);
			$books = $this->book->findAll();

			$persons = $this->person->findBy(['center_id' => $this->user->center_id]);
			foreach ($persons as $person) {
				foreach ($books as $book) {
					if(isset($book_distribution[$person->id][$book->id])) {
						$this['personResultsForm'][$person->id]['results'][$book->id]->setDefaultValue($book_distribution[$person->id][$book->id]);
					}
					else {
						$this['personResultsForm'][$person->id]['results'][$book->id]->setDefaultValue(0);
					}
				}
			}

            if($this->isAjax()) { // ajaxová změna týdne
                $this->redrawControl('resultsTable');
            }
		}
	}

	public function renderSetter() {
		if($this->getSignal() == null) {
            $this->template->week = $this->week;
            $this->template->year = $this->year;
			$this->template->persons = $this->person->findBy(['center_id' => $this->user->center_id]);
            $this->template->primary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                           'priority' => "primary"]);

            $this->template->secondary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                             'priority' => "secondary"]);

            $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistribution($this->week, $this->year);
            $this->template->book_points = $this->distribution->getPersonsSumPoints($this->week, $this->year);
		}
	}

    public function renderPersonsOverview($week_from, $year_from, $week_to, $year_to) {
        $this->template->week_from = $week_from;
        $this->template->year_from = $year_from;
        $this->template->week_to = $week_to;
        $this->template->year_to = $year_to;
        $this->template->persons = $this->person->findAll();
        $this->template->books = $this->book->findAll();
        $this->template->primary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                       'priority' => "primary"]);

        $this->template->secondary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                         'priority' => "secondary"]);

        $this->template->weeks_distribution = $this->distribution->getPersonsWeeksDistribution($week_from, $year_from, $week_to, $year_to);
        $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_points = $this->distribution->getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to);
        if($year_from != $year_to) {
            $score_title = $year_from.": týden ".$week_from." - ".$year_to." týden ".$week_to;
        }
        else if($week_from != $week_to) {
            $score_title = $year_from.": týden ".$week_from." - ".$week_to;
        }
        else {
            $score_title = $year_from.": týden ".$week_from; 
        }

        $this->template->score_title = $score_title;  

        if($this->isAjax()) {
            $this->redrawControl('overviewTable');
            $this->redrawControl('printTable');
        }
    }

    public function renderBooksOverview($week_from, $year_from, $week_to, $year_to) {
        $this->template->week_from = $week_from;
        $this->template->year_from = $year_from;
        $this->template->week_to = $week_to;
        $this->template->year_to = $year_to;
        $this->template->persons = $this->person->findAll();
        $this->template->books = $this->book->findAll();
        $this->template->centers = $this->center->findAll();
        
        $this->template->weeks_distribution = $this->distribution->getPersonsWeeksDistribution($week_from, $year_from, $week_to, $year_to);
        $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->books_sum_distribution = $this->distribution->getBooksSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_distribution = $this->distribution->getBooksCentersDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to);
        if($year_from != $year_to) {
            $score_title = $year_from.": týden ".$week_from." - ".$year_to." týden ".$week_to;
        }
        else if($week_from != $week_to) {
            $score_title = $year_from.": týden ".$week_from." - ".$week_to;
        }
        else {
            $score_title = $year_from.": týden ".$week_from; 
        }

        $this->template->score_title = $score_title;  

        if($this->isAjax()) {
            $this->redrawControl('overviewTable');
            $this->redrawControl('printTable');
        }
    }

	public function createComponentPersonResultsForm() {
		$this->books = $this->book->findAll();
        
		$form = new Multiplier(function ($person_id) {
            $form = new Form;
            $results = $form->addContainer("results");
            foreach ($this->books as $book) {
                $results->addText($book->id, $book->title)
                        ->setType('number') // <input type=number>
                        ->setAttribute('class', "form-control number")
                        ->setAttribute('min', 0)
                        ->setAttribute('max', 9999)
                        ->setAttribute('maxlength', 4)
                        ->setDefaultValue(0)
                        ->addCondition(Form::FILLED)
                            ->addRule(Form::INTEGER, 'Musí být číslo')
                            ->addRule(Form::RANGE, 'Musí být v rozsahu %d do %d', array(0, 9999));
            }

            $form->addHidden('person_id', $person_id);
            $form->addSubmit('save', 'Uložit');
            $form->onSuccess[] = array($this, 'saveResults');
            $form->onError[] = array($this, 'sendError');
            return $form;
        });
		return $form;
	}

    public function saveResults(Form $form, $values) {
        Debugger::fireLog($this->week);
        Debugger::fireLog($this->year);
        foreach ($values->results as $book_id => $quantity) {
            $this->distribution->insertResult($values->person_id, $this->week, $this->year, $book_id, $quantity);    
        }

		$this->payload->categories_points = $this->distribution->getPersonCategoriesDistribution($values->person_id, $this->week, $this->year);
        $this->payload->points_sum = $this->distribution->getPersonSumPoints($values->person_id, $this->week, $this->year);
        $this->payload->person_id = $values->person_id;
        $this->flashMessage("Výsledky byly uloženy", 'success');
        $this->sendPayload();
    }  

    public function sendError(Form $form) {
        $this->payload->error = true;
        $this->flashMessage("Výsledky se nepodařilo uložit", 'error');
        $this->sendPayload();
    }  
}
