<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class FormFactory extends Nette\Object
{

	/**
	 * @return Form
	 */
	public function create() {
		$form = new Form;
		$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		return $form;
	}
}
