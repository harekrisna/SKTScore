<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected $region; 
	protected $guru;
	protected $center;
	protected $preachingZone;
	protected $membershipLevel;
	
	protected function startup()	{
		parent::startup();
		
		$this->region = $this->context->getService('region');
		$this->guru = $this->context->getService('guru');  
		$this->center = $this->context->getService('center');  
		$this->preachingZone = $this->context->getService('preachingZone');
		$this->membershipLevel = $this->context->getService('membershipLevel');
	}
}