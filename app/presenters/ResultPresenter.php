<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;


class ResultPresenter extends BasePresenter
{	
    protected function startup()  {
        parent::startup();
    
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
	public function renderSetter()	{
		$this->template->weeks_in_year = gmdate("W", strtotime("31 December 2016"));
	}
}
