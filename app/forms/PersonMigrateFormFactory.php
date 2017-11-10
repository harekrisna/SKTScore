<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Security\User;
use Nette\Database\SqlLiteral;

class PersonMigrateFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Person */
	private $person;
	/** @var Distribution */
	private $distribution;

	/** @var User */
	private $user;
		
	public function __construct(FormFactory $factory, \App\Model\Person $person, \App\Model\Distribution $distribution, User $user) {
		$this->factory = $factory;
		$this->person = $person;
		$this->distribution = $distribution;
		$this->user = $user;
	}

	public function create() {
		$form = $this->factory->create();
	
		$person_items = $this->person->findAll()
									 ->order('name')
									 ->fetchPairs('id', 'name');

		$form->addSelect("source_person_id", "Výsledky osoby", $person_items)
		     ->setPrompt("--- vyber osobu ---")
		     ->setRequired('Vyberte prosím osobu.');

		$form->addSelect("target_person_id", "Přesunout k osobě", $person_items)
		     ->setPrompt("--- vyber osobu ---")
		     ->setRequired('Vyberte prosím osobu.');

	    $form->addSubmit('save', 'Přesunout výsledky');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		$source_person_id = $values->source_person_id;
		$target_person_id = $values->target_person_id;

		$source_distributions = $this->distribution->findBy(['person_id' => $source_person_id]);
		foreach ($source_distributions as $source_distribution_book) {
			$target_distribution_week = $this->distribution->findBy(['person_id' => $target_person_id,
																	 'book_id' => $source_distribution_book->book_id,
																	 'week' => $source_distribution_book->week,
																	 'year' => $source_distribution_book->year])
														   ->fetch();

			if($target_distribution_week) { // cílová osoba má v daný týden na dané knize nějaký výsledek
				$target_distribution_week->update(['quantity' => new SqlLiteral("quantity + ".$source_distribution_book->quantity)]); // přičte se výsledek k cílovému
				$source_distribution_book->delete(); // odstraně se výsledek původní osoby
			}
			else { // cílová osoba nemá v daný týden na dané knize žádný výsledek
				$source_distribution_book->update(['person_id' => $target_person_id]); // výsledek zdrojové osoby se přesune k cílovémé osobě
			}
		}
	}
}
