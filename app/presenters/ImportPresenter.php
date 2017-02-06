<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;
use Nette\Forms\Controls;


class ImportPresenter extends BasePresenter {

    public function createComponentParseSkpnResultListForm() {
        $form = new Form; 
        $form->addTextArea("import_source","Import source")
             ->setRequired("Žádný zdroj pro inport!");
        
        $form->addSubmit('import', 'Načíst data');

        $form->onSuccess[] = array($this, 'parseSkpnResultListFile');
        return $form;
    }

    public function parseSkpnResultListFile(Form $form, $values) {
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

    public function createComponentImportSkpnResultListForm() {
        $form = new Form; 
        $form->addText("year","Rok");
        $form->addText("week","Týden");

        $form->addSubmit('import', 'Importovat výsledky do databáze');

        $form->onSuccess[] = array($this, 'importSkpnResultListData');
        return $form;
    }

    public function importSkpnResultListData(Form $form, $values) {
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



    public function createComponentParseSkpnPersonListForm() {
        $form = new Form; 
        $form->addTextArea("import_source","Import source")
             ->setRequired("Žádný zdroj pro inport!");
        
        $form->addSubmit('import', 'Načíst data');

        $form->onSuccess[] = array($this, 'parseSkpnPersonListFile');
        return $form;
    }

    public function parseSkpnPersonListFile(Form $form, $values) {
        $source = trim($values['import_source']);

        $lines = explode("\n", $source); // parsování textu na pole řádků
        $lines = array_filter($lines, 'trim'); // remove any extra \r characters left behind

        $person_name = trim(substr($lines[0], strlen("Scores for "), strpos($lines[0], "in Temple") - strlen("Scores for ")));
        Debugger::fireLog($person_name);

        exit;
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
}
