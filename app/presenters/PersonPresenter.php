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

    public function renderAdd() {
        parent::renderAdd();
        $this->template->center_title = $this->center->get($this->user->center_id)
                                                     ->title;
    }    

    public function renderEdit($record_id) {
        parent::renderEdit($record_id);
        $this->template->center_title = isset($this->record->center_id) ? $this->record->center->title : "";
    }

    public function renderList() {
        $this->template->records = $this->model->findBy(['center_id' => $this->user->center_id]);
    }
}
