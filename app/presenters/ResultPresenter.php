<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;
use Nette\Forms\Controls;


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

    public function renderImportSkpn() {
        
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
        $this->template->category_sum_distribution = $this->distribution->getCategoriesDistributionSumInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_sum_distribution = $this->distribution->getMahaBigSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_points = $this->distribution->getPersonsSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points = $this->distribution->getAllSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_points_ceil = $this->distribution->getAllSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getPersonsBooksDistributionInterval($week_from, $year_from, $week_to, $year_to);
        
        $this->template->centers = $this->center->findAll();
        $this->template->centers_categories_distribution = $this->distribution->getCentersCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_mahabig_distribution = $this->distribution->getCentersMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_sum_distribution = $this->distribution->getCentersSumPointsInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_sum_distribution_ceil = $this->distribution->getCentersSumPointsCeilInterval($week_from, $year_from, $week_to, $year_to);

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
        $this->template->centers_count_weeks = $week_to - $week_from + 1;

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
        
        $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->mahabig_distribution = $this->distribution->getPersonsMahaBigDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->books_sum_distribution = $this->distribution->getBooksSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_distribution = $this->distribution->getBooksCentersDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->centers_sum_distribution = $this->distribution->getCentersSumDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->book_distribution = $this->distribution->getBooksPersonsDistributionInterval($week_from, $year_from, $week_to, $year_to);
        $this->template->allsum_books = $this->distribution->getAllSumBooksInterval($week_from, $year_from, $week_to, $year_to);

        if($this->isAjax()) {
            $this->redrawControl('overviewTable');
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
        foreach ($values->results as $book_id => $quantity) {
            $this->distribution->insertResult($values->person_id, $this->week, $this->year, $book_id, $quantity);    
        }

		$this->payload->categories_points = $this->distribution->getPersonCategoriesDistribution($values->person_id, $this->week, $this->year);
        $this->payload->points_sum = $this->distribution->getPersonSumPoints($values->person_id, $this->week, $this->year);
        $this->payload->person_id = $values->person_id;
        $this->flashMessage("Výsledky byly uloženy", 'success');
        $this->sendPayload();
    }

    public function createComponentParseSkpnForm() {
        $form = new Form; 
        $form->addTextArea("import_source","Import source")
             ->setRequired("Žádný zdroj pro inport!");
        $form->addSubmit('import', 'Načíst data');
        
        /*
        $renderer = (new \App\Forms\BootstrapFormRenderer);
        $form->setRenderer($renderer);

        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        */

        $form->onSuccess[] = array($this, 'parseSkpnFile');
        return $form;
    }

    public function createComponentImportSkpnForm() {
        $form = new Form; 
        $form->addText("year","Rok");
        $form->addText("week","Týden");

        $form->addSubmit('import', 'Importovat výsledky do databáze');

        $form->onSuccess[] = array($this, 'importSkpnData');
        return $form;
    }

    public function parseSkpnFile(Form $form, $values) {
        $source = trim($values['import_source']);

        $lines = explode("\n", $source); // parsování textu na pole řádků
        $lines = array_filter($lines, 'trim'); // remove any extra \r characters left behind

        preg_match_all('!\d+!', $lines[0], $matches); // vytažení čísel z prvního řádku (obsahuje rok a týden)

        if($matches == [[]]) { // pokud nenajdeme žádné čísla, je to chyba
            $form->addError("Při zpracovávání došlo k chybě. Pravděpodobně špatný formát vstupu.");
        }
        else {
            $firsl_line_matches = $matches[0];
            $this['importSkpnForm']['year']->setValue($firsl_line_matches[0]);
            $this['importSkpnForm']['week']->setValue($firsl_line_matches[1]);

            $cvs_persons = $this->person->findBy(['center.abbreviation' => "CVS"])
                                        ->fetchPairs('id', 'name');
            
            $this->template->parseForm = $form;

            $score_data_lines = array_slice($lines, 3, -2); // pole s řádky výsledků
            $persons_score = [];

            $person_index = 1;
            $persons_container = $this['importSkpnForm']->addContainer('person');

            foreach ($score_data_lines as $line) {
                $line = trim($line);
                preg_match('/^(\d+) (.+?(?=CZ))CZ +\d +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.)/', $line, $matches);
                $score = [];
                $score['position'] = $matches[1];
                $score['name'] = trim($matches[2]);
                $score['maha'] = $matches[3];
                $score['big'] = $matches[4];
                $score['medium'] = $matches[5];
                $score['small'] = $matches[6];
                $score['mag'] = $matches[7];
                $score['books'] = $matches[8];
                $score['points'] = $matches[9];

                $persons_score[] = $score;
                $container = $persons_container->addContainer($person_index);
                $container->addHidden('skpn_alias')->setValue($score['name']);
                $container->addCheckbox('do_import');
                $container->addSelect('person_id', "", $cvs_persons)->setPrompt('--- vyber osobu ---');
                $container->addHidden('maha')->setValue($score['maha']);
                $container->addHidden('big')->setValue($score['big']);
                $container->addHidden('medium')->setValue($score['medium']);
                $container->addHidden('small')->setValue($score['small']);
                $container->addHidden('mag')->setValue($score['mag']);
                $container->addHidden('books')->setValue($score['books']);
                $container->addHidden('points')->setValue($score['points']);
                $person_index++;
            }

            $this->template->persons_score = $persons_score;
        }
    }  

    public function importSkpnData(Form $form, $values) {
        $values = $form->getHttpData();
        Debugger::fireLog($values['person']);
        exit;
    }

    public function sendError(Form $form) {
        $this->payload->error = true;
        $this->flashMessage("Výsledky se nepodařilo uložit", 'error');
        $this->sendPayload();
    }  
}
