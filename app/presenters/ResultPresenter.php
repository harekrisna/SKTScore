<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;
use Nette\Forms\Controls;
use App\Forms\ChooseCentersFormFactory;


class ResultPresenter extends BasePresenter {
    public $books;
    private $week;
    private $year;
    /** @var ChooseCentersFormFactory @inject */
    public $chooseCentersFormFactory;

    /** @inject @var Nette\Http\Response */
    public $httpResponse;

    public function beforeRender() {
        parent::beforeRender();
        $this->template->addFilter('decimalNumber', $this->context->getService("filters")->decimalNumber);
    }

	public function actionSetter($week, $year) {
		if($week == null) $week = date('W');
        if($year == null) $year = date('Y');

        $this->week = $week;
        $this->year = $year;
        
        $_SESSION['setter_week'] = $week;
        $_SESSION['setter_year'] = $year;
        
		if($this->getSignal() == null) { // při odeslání personResultsForm nedělat nic
			$book_distribution = $this->distribution->getPersonsBooksDistribution($week, $year);
			$books = $this->book->findAll();
			
			$persons = $this->person->findBy(['center_id' => $this->user->center_id]);
			if($this->getUser()->isInRole('superadmin'))
				$persons = $this->person->findAll();
			
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
			
			$this->template->centers = $this->center->findAll()->fetchPairs('id', 'title');
			
            if($this->isAjax()) { // ajaxová změna týdne
                $this->redrawControl('resultsTable');
            }
		}
	}

	public function renderSetter() {
		if($this->getSignal() == null) {
			       
            $this->template->week = $this->week;
            if($this->week[0] == "0")
				$this->template->week = $this->week[1];
				
            $this->template->year = $this->year;
			$this->template->persons = $this->person->findBy(['center_id' => $this->user->center_id]);
			if($this->getUser()->isInRole('superadmin'))
				$this->template->persons = $this->person->findAll();
			
            $this->template->primary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                           'priority' => "primary"])
                                                                 ->order('book.category.id, book.title');

            $this->template->secondary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                             'priority' => "secondary"])
                                                                   ->order('book.category.id, book.title');

            $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistribution($this->week, $this->year);
            $this->template->book_points = $this->distribution->getPersonsSumPoints($this->week, $this->year);
		}
	}

    public function actionPersonsOverviewOneWeek($week, $year) {
        $this->setView('personsOverview');
        $this->renderPersonsOverview($week, $year, $week, $year);
    }

    public function renderPersonsOverview($week_from, $year_from, $week_to, $year_to) {
        $week_from == null ? $week_from = $_SESSION['week_from'] : $_SESSION['week_from'] = $week_from;
        $year_from == null ? $year_from = $_SESSION['year_from'] : $_SESSION['year_from'] = $year_from;
        $week_to == null ? $week_to = $_SESSION['week_to'] : $_SESSION['week_to'] = $week_to;
        $year_to == null ? $year_to = $_SESSION['year_to'] : $_SESSION['year_to'] = $year_to;
        
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
        $this->template->category_sum_distribution = $this->distribution->getCategoriesDistributionSumInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_sum_distribution = $this->distribution->getMahaBigSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_points = $this->distribution->getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points = $this->distribution->getAllSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points_ceil = $this->distribution->getAllSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to);

        if($this->isAjax()) {
	        $this->redrawControl('overviewTable');
            $this->redrawControl('resultsNavigation');
        }
    }

    public function renderResultsTablePrintout($week_from, $year_from, $week_to, $year_to) {
        $this->setLayout("layout.printout");

        $this->template->persons = $this->person->findAll();
        $this->template->books = $this->book->findAll();

        $this->template->weeks_distribution = $this->distribution->getPersonsWeeksDistribution($week_from, $year_from, $week_to, $year_to);
        $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->category_sum_distribution = $this->distribution->getCategoriesDistributionSumInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_sum_distribution = $this->distribution->getMahaBigSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_points = $this->distribution->getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points = $this->distribution->getAllSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points_ceil = $this->distribution->getAllSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);
        
        $this->template->centers = $this->center->findAll();
        $this->template->centers_categories_distribution = $this->distribution->getCentersCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_mahabig_distribution = $this->distribution->getCentersMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_sum_distribution = $this->distribution->getCentersSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        //$this->template->centers_sum_distribution_ceil = $this->distribution->getCentersSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);

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
        $this->template->centers_weeks_distribution = $this->distribution->getCentersWeeksDistribution($week_from, $year_from, $week_to, $year_to);
    }

    public function renderWsnResults($week_from, $year_from, $week_to, $year_to) {
        $this->setLayout("layout.empty");
        $this->setView('wsn');
        
        $persons = $this->person->findBy(['center_id' => $this->user->center_id]);

        $persons_assoc = [];
        $name_length_max = 0;
        
        foreach ($persons as $person) {
            $persons_assoc[$person->id] = $person;
        }

        $this->distribution->setCentersToShow($this->center->findBy(['id' => $this->user->center_id]));
        $book_points = $this->distribution->getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to);
                                      
        foreach ($book_points as $person_id => $sum_points) {
            if(mb_strlen($persons_assoc[$person_id]->name) > $name_length_max)
                $name_length_max = mb_strlen($persons_assoc[$person_id]->name);
        }

        $allsum_points_ceil = $this->distribution->getAllSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);
        $allsum_points_ceil = number_format($allsum_points_ceil, 0, ".", "'");

        $category_sum_distribution_db = $this->distribution->getCategoriesDistributionSumInterval($week_from, $year_from, $week_to, $year_to);
        $category_sum_distribution = [];

        foreach ($category_sum_distribution_db as $category => $sum_distribution) {
            $number = number_format($sum_distribution, 0, ".", "'");

            $category_sum_distribution[$category] = ['number' => $number,
                                                     'length' => max(strlen($number), 2)];
        }

        $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points_ceil = $allsum_points_ceil;
        $this->template->allsum_points_ceil_length = max(strlen($allsum_points_ceil), 6);
        

        $this->template->category_sum_distribution = $category_sum_distribution;
        $this->template->names_pad_right = $name_length_max + 1;
        $this->template->book_points = $book_points;
        $this->template->center = $this->center->get($this->user->center_id);
        $this->template->persons = $persons_assoc;
        $this->template->year = $year_from;
        $this->template->week_from = $week_from;
        $this->template->week_to = $week_to;
        $this->template->timestamp_from = strtotime($year_from."W".$week_from) - (86400 * 1);
        $this->template->timestamp_to = strtotime($year_to."W".$week_to) + (86400 * 5);
        
    }

    public function actionBooksOverviewOneWeek($week, $year) {
        $this->setView('booksOverview');
        $this->renderBooksOverview($week, $year, $week, $year);
    }

    public function renderBooksOverview($week_from, $year_from, $week_to, $year_to) {
        $week_from == null ? $week_from = $_SESSION['week_from'] : $_SESSION['week_from'] = $week_from;
        $year_from == null ? $year_from = $_SESSION['year_from'] : $_SESSION['year_from'] = $year_from;
        $week_to == null ? $week_to = $_SESSION['week_to'] : $_SESSION['week_to'] = $week_to;
        $year_to == null ? $year_to = $_SESSION['year_to'] : $_SESSION['year_to'] = $year_to;
        
        $this->template->week_from = $week_from;
        $this->template->year_from = $year_from;
        $this->template->week_to = $week_to;
        $this->template->year_to = $year_to;
        
		$this->template->persons = $this->person->findAll();
        $this->template->books = $this->book->findAll();
        $this->template->centers = $this->center->findAll();
        
        $this->template->books_sum_distribution = $this->distribution->getBooksSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_distribution = $this->distribution->getBooksCentersDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_sum_distribution = $this->distribution->getCentersSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getBooksPersonsDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_books = $this->distribution->getAllSumBooksInterval($week_from, $year_from, $week_to, $year_to);

        if($this->isAjax()) {
            $this->redrawControl('overviewTable');
            $this->redrawControl('resultsNavigation');
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
            $form->addHidden('center_id');
            $form->addSubmit('save', 'Uložit');
            $form->onSuccess[] = array($this, 'saveResults');
            $form->onError[] = array($this, 'sendError');
            return $form;
        });
		return $form;
	}

    public function saveResults(Form $form, $values) {  
        foreach ($values->results as $book_id => $quantity) {
	        if($this->getUser()->isInRole('superadmin')) {
            	$center_id = $values->center_id;
            }
            else {
	            $center_id = $this->admin->get($this->getUser()->id)->center_id;
            }
            
            $this->distribution->insertResult($values->person_id, $this->week, $this->year, $center_id, $book_id, $quantity);    
        }

		$this->payload->categories_points = $this->distribution->getPersonCategoriesDistribution($values->person_id, $this->week, $this->year);
        $this->payload->points_sum = $this->distribution->getPersonSumPoints($values->person_id, $this->week, $this->year);
        $this->payload->person_id = $values->person_id;
        $this->flashMessage("Výsledky byly uloženy", 'success');
        $this->sendPayload();
    }
}