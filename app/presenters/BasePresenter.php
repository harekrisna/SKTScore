<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected $person;
	
	protected function startup()	{
		parent::startup();
		
		$this->person = $this->context->getService('person');
	}
}