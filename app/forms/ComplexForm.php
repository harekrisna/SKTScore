<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class ComplexForm extends Form
{
	public $add_title;
	public $edit_title;
	
	public function __construct() {
		$this->add_title = "Přidat záznam";
		$this->edit_title = "Upravit záznam";
	}
}
