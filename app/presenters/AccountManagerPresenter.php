<?php

namespace App\Presenters;

use Nette;
use App\Forms\ChangePasswordFormFactory;


class AccountManagerPresenter extends BasePresenter {
	/** @var ChangePasswordFormFactory @inject */
	public $changePasswordFormFactory;

	protected function createComponentChangePasswordForm() {
		$form = $this->changePasswordFormFactory->create();
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Heslo bylo úspěšně změněno, přihlaste se znovu.", 'success');
			$this->getUser()->logout();
			$this->redirect('Sign:in');
		};
		return $form;
	}	
}
