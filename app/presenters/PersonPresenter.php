<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use Tracy\Debugger;


class PersonPresenter extends ComplexPresenter {	
	/** @var PersonFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
		$this->model = $this->person;
	}

    public function renderList() {
        if($this->getUser()->isInRole('superadmin')) {
            $this->template->records = $this->model->findAll()
                                                   ->order('center.title');
        }
        else {
            $this->template->records = $this->model->findBy(['center_id' => $this->user->center_id]);
        }
    }

    public function handleSetPersonActivity($person_id, $active) {
        $this->person->findBy(['id' => $person_id])
                     ->update(['active' => $active == "true" ? 1 : 0]);

        $this->sendPayload();
    }    

    public function renderExpandRow($record_id) {
        $this->template->person_id = $record_id;
        parent::renderExpandRow($record_id);
    }
}
