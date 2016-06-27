<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;
use App\Forms\BookFormFactory;

class ComplexPresenter extends BasePresenter {
    /** @var object */
    protected $record;
	/** @var Book */
	protected $model;

	public function renderAdd() {
		$this->setView("form");
		$this->template->form = $this['form'];
		$this->template->form_title = "Přidat záznam";
	}

	public function actionEdit($record_id) {
		$this->record = $this->model->get($record_id);
		if (!$this->record)
            throw new Nette\Application\BadRequestException("Záznam nenalezen.");
			
        $this->template->record = $this->record;
	}

	public function renderEdit($record_id) {
		$this->setView("form");
		$this->template->form = $this['form'];
		$this->template->form_title = "Upravit záznam";
	}

	public function renderList() {
		$this->template->records = $this->model->findAll();
	}

	protected function createComponentForm() {
		$form = $this->factory->create($this->record);
		
		$form->onSuccess[] = function ($form) {
			if($form->isSubmitted()->name == "add") {
				$this->flashMessage($form->success_add_message, 'success');
				$form->getPresenter()->redirect('add');
			}
			else {
				$this->flashMessage($form->success_edit_message, 'success');
				$form->getPresenter()->redirect('list');
			}
		};

		return $form;
	}	

	public function actionDelete($id) {
		$this->payload->success = $this->model->delete($id);
		$this->sendPayload();
	}
}
