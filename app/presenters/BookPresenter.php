<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form;
use App\Forms\BookFormFactory;


class BookPresenter extends BasePresenter {
	/** @persistent */
    public $category_id;
    /** @var object */
    private $record;
	/** @var Book */
	private $model;
	/** @var BookFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
		$this->model = $this->book;
	}
	
	public function renderAdd() {
		$this->setView("form");
		$this->template->form_title = "Přidat knihu";
	}

	public function actionEdit($record_id) {
		$this->record = $this->model->get($record_id);
		
		if (!$this->record)
            throw new Nette\Application\BadRequestException("Kniha nenalezena.");
			
        $this->template->record = $this->record;
	}

	public function renderEdit($record_id) {
		$this->setView("form");
		$this->template->form_title = "Upravit knihu";
	}

	public function renderList($category_id) {
		if($this->category_id != "") 
			$this->template->books = $this->model->findBy(['category_id' => $this->category_id]);
		else 
	        $this->template->books = $this->model->findAll();
	}

	protected function createComponentBookForm() {
		$form = $this->factory->create($this->record);
		
		$form->onSuccess[] = function ($form) {
			if($form->isSubmitted()->name == "add") {
				$this->flashMessage("Kniha byla úspěšně přidána", 'success');
				$form->getPresenter()->redirect('Book:add');
			}
			else {
				$this->flashMessage("Kniha byla upravena", 'success');
				$form->getPresenter()->redirect('Book:list');
			}
		};

		return $form;
	}	

	protected function createComponentCategoryForm() {
		$form = new Form;
		$category_pairs = ['' => "Vše"] + $this->category->findAll()->fetchPairs('id', 'title');

		$form->addSelect('category_id', "Kategorie", $category_pairs)
			 ->setValue($this->category_id);

		$form->onSuccess[] = array($this, 'changeCategorySubmit');
		return $form;
	}

	public function changeCategorySubmit(Form $form, $values) {
		$this->category_id = $values->category_id;
		$this->redirect("list");
	}

	public function actionDelete($id) {
		$this->payload->success = $this->model->delete($id);
		$this->sendPayload();
	}

    public function handleSetBookPriorityType($book_id, $type) {
        $this->book->setBookTypePriority($book_id, $type);
        $this->sendPayload();
    }	
}
