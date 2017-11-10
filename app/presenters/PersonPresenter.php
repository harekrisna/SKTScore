<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\PersonFormFactory;
use App\Forms\PersonMigrateFormFactory;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;


class PersonPresenter extends ComplexPresenter {	
	/** @var PersonFormFactory @inject */
	public $factory;

    /** @var PersonMigrateFormFactory @inject */
    public $personMigratefactory;

	protected function startup() {
		parent::startup();
		$this->model = $this->person;
	}

    public function renderList() {

        if($this->getUser()->isInRole('superadmin')) {
            $this->template->records = $this->model->findAll()
                                                   ->order('center.title');

            $this->template->centers = $this->center->findAll()
                                                    ->order('title')
                                                    ->fetchPairs('id', 'title');
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

    public function actionMigrate($person_id) {
        if(!$this->getUser()->isInRole('superadmin')) 
            throw new Nette\Application\ForbiddenRequestException("Nedostatečná práva.");
    }

    public function createComponentMigratePersonForm() {
        if(!$this->getUser()->isInRole('superadmin')) 
            throw new Nette\Application\ForbiddenRequestException("Nedostatečná práva.");

        $form = $this->personMigratefactory->create();
        
        $form->onSuccess[] = function ($form) {
            $this->flashMessage("Výsledky byly úpsěšně přesunuty", 'success');
            $form->getPresenter()->redirect('migrate');
        };

        return $form;
    }

    public function renderExpandRow($record_id) {
        $this->template->person_id = $record_id;
        $this->template->books_distribution = $this->distribution->getPersonBooksDistribution($record_id);
        parent::renderExpandRow($record_id);
    }

    public function actionDelete($id) {
        $person = $this->model->get($id);
        
        if(!$this->getUser()->isInRole('superadmin') && $person->center_id != $this->user->center_id) {
            throw new Nette\Application\ForbiddenRequestException("Nemůžete smazat osobu z jiného centra!");
        }

        $this->payload->success = $this->model->delete($id);
        $this->sendPayload();
    }
}
