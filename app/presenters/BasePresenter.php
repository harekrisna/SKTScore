<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {
	/** @var Admin */
	protected $admin;
	/** @var Person */
	protected $person;
	/** @var Center */
	protected $center;
	/** @var Book */
	protected $book;
	/** @var Category */
	protected $category;
	/** @var Distribution */
	protected $distribution;
	/** @var Week */
	protected $week;
	/** @var User */
	protected $user;
	
	protected function startup() {
		parent::startup();
		
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }		
        
        $this->admin = $this->context->getService('admin');
		$this->person = $this->context->getService('person');
		$this->center = $this->context->getService('center');
		$this->book = $this->context->getService('book');
		$this->category = $this->context->getService('category');
		$this->distribution = $this->context->getService('distribution');
		$this->week = $this->context->getService('week');

		$this->user = $this->getUser()->getIdentity();
	}

	public function flashMessage($message, $type = 'info') {
		if ($this->isAjax()) {
			$this->payload->messages[] = ['message' => $message,
										  'type' => $type];
		}
		else {
			parent::flashMessage($message, $type);
		}
	}
}