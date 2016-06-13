<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class ComplexForm extends Form
{
	public $add_title;
	public $edit_title;
	public $success_add_message;
	public $success_edit_message;
	
	public function __construct() {
		$this->add_title = "Přidat záznam";
		$this->edit_title = "Upravit záznam";
		$this->success_add_message = "Záznam byl přidán";
		$this->success_edit_message = "Záznam byl upraven";
	}
}
