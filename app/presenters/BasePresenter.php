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
	/** @var ShowCenter */
	protected $show_center;
	/** @var Country */
	protected $country;
	/** @var Book */
	protected $book;
	/** @var BookPriority */
	protected $book_priority;
	/** @var Category */
	protected $category;
	/** @var Distribution */
	protected $distribution;
	/** @var Week */
	protected $week_model;
	/** @var User */
	protected $user;
	
	protected function startup() {
		parent::startup();
		
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }		
        
        $this->user = $this->getUser()->getIdentity();

        if($this->user->data['need_change_password']) {
			$this->redirect('Sign:changePassword');
        }

        $this->admin = $this->context->getService('admin');
		$this->person = $this->context->getService('person');
		$this->center = $this->context->getService('center');
		$this->show_center = $this->context->getService('show_center');
		$this->country = $this->context->getService('country');
		$this->book = $this->context->getService('book');
		$this->book_priority = $this->context->getService('book_priority');
		$this->category = $this->context->getService('category');
		$this->distribution = $this->context->getService('distribution');
		$this->week_model = $this->context->getService('week');

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

	public function beforeRender() {
		$week = date("W");
		if($week[0] == "0")
			$week = $week[1];
		
		$this->template->setter_week = $week;			
	    $this->template->setter_year = date("Y");	
		
		if(isset($_SESSION['setter_week'])) {
			$this->template->setter_week = $_SESSION['setter_week'];			
	        $this->template->setter_year = $_SESSION['setter_year'];	
		}
		
		
		$this->template->week_from = $this->template->week_to = $week;			
	    $this->template->year_from = $this->template->year_to = date("Y");	    
	        	
		if(isset($_SESSION['week_from'])) {
			$this->template->week_from = $_SESSION['week_from'];
	        $this->template->year_from = $_SESSION['year_from'];
	        $this->template->week_to = $_SESSION['week_to'];
	        $this->template->year_to = $_SESSION['year_to'];
		}
		
        $this->template->addFilter('round', $this->context->getService("filters")->round);
	}
}