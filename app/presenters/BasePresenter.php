<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
	/** @var Person */
	protected $person;
	/** @var Book */
	protected $book;
	/** @var Category */
	protected $category;
	
	protected function startup() {
		parent::startup();
		
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }		
        
		$this->person = $this->context->getService('person');
		$this->book = $this->context->getService('book');
		$this->category = $this->context->getService('category');
	}

	public function afterRender() {
	    if ($this->isAjax() && $this->hasFlashSession())
	        $this->redrawControl('flashes');
	}
}