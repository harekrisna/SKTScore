<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;

class ChooseCentersFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Center */
	private $center;
	/** @var ShowCenter */
	private $show_center;
	/** @var User */
	private $user;
		
	public function __construct(FormFactory $factory, \App\Model\Center $center, \App\Model\ShowCenter $show_center, Nette\Security\User $user) {
		$this->factory = $factory;
		$this->center = $center;
		$this->show_center = $show_center;
		$this->user = $user;
	}

	public function create($defaults = null) {
		$form = $this->factory->create();
		
		$center_items = $this->center->findAll()->fetchPairs('id', 'title');
		$form->addCheckboxList("centers", "Centra", $center_items);
	
	    $form->addSubmit('save', 'Uložit nastavení');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		$this->show_center->findBy(["user_id" => $this->user->getId()])
						  ->delete();

		foreach ($values->centers as $center_id) {
			$this->show_center->insert(["user_id" => $this->user->getId(),
										"center_id" => $center_id]);
		}
	}
}
