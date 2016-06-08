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
	/** @var Book */
	private $model;
	/** @var BookFormFactory @inject */
	public $factory;

	protected function startup() {
		parent::startup();
		$this->model = $this->book;
	}

	public function renderList($category_id) {
		if($this->category_id != "") 
			$this->template->books = $this->model->findBy(['category_id' => $this->category_id]);
		else 
	        $this->template->books = $this->model->findAll();
	}

	protected function createComponentBookForm() {
		$form = $this->factory->create();
		
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Kniha byla úspěšně přidána", 'success');
			$form->getPresenter()->redirect('Book:add');
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
		//$this->model->delete($id);
		$this->sendPayload();
	}
}
