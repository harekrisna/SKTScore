<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class PersonFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var Guru */
	private $guru_model;
	/** @var Region */
	private $region_model;
	/** @var Center */
	private $center_model;	
	/** @var PreachingZone */
	private $preachingZone_model;
	/** @var MembershipLevel */
	private $membershipLevel_model;	
	
	
	public function __construct(FormFactory $factory, \App\Model\Guru $guru_model, \App\Model\Region $region_model, \App\Model\Center $center_model, \App\Model\PreachingZone $preachingZone_model, \App\Model\MembershipLevel $membershipLevel_model) {
		$this->factory = $factory;
		$this->guru_model = $guru_model;
		$this->region_model = $region_model;
		$this->center_model = $center_model;
		$this->preachingZone_model = $preachingZone_model;
		$this->membershipLevel_model = $membershipLevel_model;

	}

	public function create() {
		$form = $this->factory->create();
		$regionPairs = $this->region_model->findAll()->fetchPairs('id', 'title');
		$guruPairs = $this->guru_model->findAll()->fetchPairs('id', 'name');
		$centerPairs = $this->center_model->findAll()->fetchPairs('id', 'title');
		$preachingZonePairs = $this->preachingZone_model->findAll()->fetchPairs('id', 'title');
		$membershipLevelPairs = $this->membershipLevel_model->findAll()->fetchPairs('id', 'title');
				
		$form->addText('name', 'Jméno *');
			 //->setRequired('Zadejte jméno prosím.');

		$form->addText('surname', 'Příjmení *');
			 //->setRequired('Zadejte příjmení prosím.');

		$form->addText('subject', 'Subjekt')->setDisabled();
		$form->addText('spiritual_name', 'Duchovní jméno');
		$form->addText('birth_date', 'Datum narození');
		$form->addRadioList('das', 'Dás / Déví', ['d' => "Dás", 'dd' => "Déví"])
			 ->getSeparatorPrototype()->setName(NULL)
			 ->setValue('d');	
	         
	    $form->addCheckbox('is_leader', 'Vedoucí');     
		$form->addCheckbox('bv_active', 'BV Aktivní');
		$form->addCheckbox('skt_active', 'Sankírtan oddaný');
		
		$form->addText('city', 'Město:', 30, 255);
		$form->addText('street', 'Ulice:', 30, 255);
		$form->addText('zip_code', 'PSČ:', 6, 6);
		$form->addSelect('region_id', 'Kraj', $regionPairs)
		     ->setPrompt('- nezadáno -');	
					
		$form->addText('email', 'Email')
			 ->setType('email')
   			 ->addCondition($form::FILLED)
		 	 	->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu');	
	    
	    $form->addText('phone', 'Mobil:', 20, 20);
		
		$form->addSelect('guru_id', 'Guru', $guruPairs)
	         ->setPrompt('- nezadáno -');	

	    $form->addSelect('center_id', 'Centrum', $centerPairs)
		     ->setPrompt('- nezadáno -');

	    $form->addSelect('leader_id', 'Vedení')
		     ->setPrompt('- nezadáno -');

	    $form->addSelect('preaching_zone_1', 'Kazatelská zóna 1')
		     ->setPrompt('- nezadáno -');		     

	    $form->addSelect('preaching_zone_2', 'Kazatelská zóna 2')
		     ->setPrompt('- nezadáno -');

	    $form->addSelect('preaching_zone_3', 'Kazatelská zóna 3')
		     ->setPrompt('- nezadáno -');		     		     

	    $form->addSelect('membership_level', 'Kategorie členství', $membershipLevelPairs)
		     ->setPrompt('- nezadáno -');	

	    $form->addSubmit('send', 'Login');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values) {
		Debugger::fireLog($values);
	}
}
