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
                                                                           'priority' => "primary"]);

            $this->template->secondary_books = $this->book_priority->findBy(['user_id' => $this->user->id,
                                                                             'priority' => "secondary"]);

            $this->template->category_distribution = $this->distribution->getPersonsCategoriesDistribution($this->week, $this->year);
            $this->template->book_points = $this->distribution->getPersonsSumPoints($this->week, $this->year);
		}
	}

    protected function createComponentChooseCentersForm() {
        $form = $this->chooseCentersFormFactory->create();

        $form->onSuccess[] = function ($form) {
            $this->flashMessage("Centra byla nastavena", 'success');
            $this->redirect("personsOverview");
        };

        return $form;
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

        $centers_group = $this->center->findAll()->group('country_id');
        $this->template->centers_group = $centers_group;
        $selected_centers = [];
        
        $selected_centers_db = $this->show_center->findBy(['user_id' => $this->user->getId()]);
        foreach ($selected_centers_db as $selected_center) {
            $selected_centers[] = $selected_center->center_id;
        }

        $centers = [];

        foreach ($centers_group as $center_group) {
            $centers_country = $this->center->findBy(['country_id' => $center_group->country_id]);
            foreach ($centers_country as $center_country) {
                $centers[$center_group->country_id][] = ['center' => $center_country,
                                                         'checked' => in_array($center_country->id, $selected_centers) ? true : false];
            }
        }

        $this->template->centers = $centers;
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

    public function renderBooksOverview($week_from, $year_from, $week_to, $year_to) {
        $this->template->week_from = $week_from;
        $this->template->year_from = $year_from;
        $this->template->week_to = $week_to;
        $this->template->year_to = $year_to;

        $_SESSION['week_from'] = $week_from;
        $_SESSION['year_from'] = $year_from;
        $_SESSION['week_to'] = $week_to;
        $_SESSION['year_to'] = $year_to;
        
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
			
			$persons_pairs = $this->person->findAll();
            
            if($this->user->id == 5) 
            	$persons_pairs = $this->person->findBy(['center.title' => "CVS Lužce"]);
            
			$persons_pairs = $persons_pairs->order('name ASC')
            			  				   ->fetchPairs('id', 'name');
            
            $this->template->parseForm = $form;
            
            $score_data_lines = [];
            $record_flag = false;
            
            // vyzobeme pouze řádky mezi počáteční a koncovou dělící čárou "-------------------------" 
            foreach($lines as $line) {
	            $line = trim($line);
	            if(strpos($line, "------") !== false) {
		            if($record_flag == false) {
	                	$record_flag = true;
	                	continue;
	                }
	                else {
		                break;
	                }
                }
                
                if($record_flag == true) {
	                $score_data_lines[] = $line;
                }
            }
            
            $persons_score = [];

            $person_index = 1;
            $persons_container = $this['importSkpnForm']->addContainer('person');
			
			
            foreach ($score_data_lines as $line) {
                $line = trim($line);
                
                if(strpos($line, "------") !== false) {
	                continue;
                }
                
                $score = [];
                
                preg_match('/^(\d+) (.+?(?=(CZ|Prabhupad Bhavan)))(CZ|Prabhupad Bhavan) +\d +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.)/', $line, $matches);
               
                if($matches == []) { // Pravděpodobně chybí počet týdnů (Wk). zkusíme rozpársovat bez toho
	                preg_match('/^(\d+) (.+?(?=(CZ|Prabhupad Bhavan)))(CZ|Prabhupad Bhavan) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.) +(\d+|\.)/', $line, $matches);
	                
	                if($matches == []) {
		            	$form->addError("Při zpracovávání došlo k chybě.");
		            	return;
		            }
                }
          
                $score['position'] = $matches[1];
                $score['name'] = trim($matches[2]);
                $score['maha'] = $matches[5];
                $score['big'] = $matches[6];
                $score['medium'] = $matches[7];
                $score['small'] = $matches[8];
                $score['mag'] = $matches[9];
                $score['books'] = $matches[10];
                $score['points'] = $matches[11];
                
                $persons_score[] = $score;
                $container = $persons_container->addContainer($person_index);
                $container->addHidden('skpn_alias')->setValue($score['name']);
                $container->addCheckbox('do_import');
                $container->addSelect('person_id', "", $persons_pairs)
                		  ->setPrompt('--- vyber osobu ---');
                
                $person = $this->person->findBy(['skpn_alias' => $score['name']])
                                       ->fetch();
                    
                if(isset($person->id)) {
                    $container['person_id']->setDefaultValue($person->id);
                }
                
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
        $year = $values['year'];
        $week = $values['week'];
        $persons = $values['person'];
        
        $maha_book = $this->book->findBy(['title' => 'Mahá'])->fetch();
        $big_book = $this->book->findBy(['title' => 'Big'])->fetch();
        $medium_book = $this->book->findBy(['title' => 'Medium'])->fetch();
        $small_book = $this->book->findBy(['title' => 'Small'])->fetch();
        $mag_book = $this->book->findBy(['title' => 'Mag'])->fetch();
        
        foreach ($persons as $person) {
	        if(isset($person['do_import']) && $person['do_import'] == "on") {
            	if($person['person_id'] != "") {
	            	if($person['maha'] == ".") $person['maha'] == "0";
			        if($person['big'] == ".") $person['big'] == "0";
			        if($person['medium'] == ".") $person['medium'] == "0";
			        if($person['small'] == ".") $person['small'] == "0";
			        if($person['mag'] == ".") $person['mag'] == "0";
			        
			        $person_db = $this->person->get($person['person_id']);

        			$this->distribution->insertResult($person['person_id'], $week, $year, $person_db->center_id, $maha_book->id, $person['maha']);
        			$this->distribution->insertResult($person['person_id'], $week, $year, $person_db->center_id, $big_book->id, $person['big']);
        			$this->distribution->insertResult($person['person_id'], $week, $year, $person_db->center_id, $medium_book->id, $person['medium']);
        			$this->distribution->insertResult($person['person_id'], $week, $year, $person_db->center_id, $small_book->id, $person['small']);
        			$this->distribution->insertResult($person['person_id'], $week, $year, $person_db->center_id, $mag_book->id, $person['mag']);

                    $persons_alias = $this->person->findBy(['skpn_alias' => $person['skpn_alias']]);

                    foreach ($persons_alias as $person_alias) {
                        $this->person->update($person_alias->id, ['skpn_alias' => NULL]);
                    }

                    $this->person->update($person['person_id'], ['skpn_alias' => $person['skpn_alias']]);
            	}
            }  
        }
        
        $this->flashMessage("Výsledky byly úspěšně importovány.", 'success');
        //$this->redirect("personsOverview", $week, $year, $week, $year);
    }

    public function sendError(Form $form) {
        $this->payload->error = true;
        $this->flashMessage("Výsledky se nepodařilo uložit", 'error');
        $this->sendPayload();
    }  
}
