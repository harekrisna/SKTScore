<?php

namespace App\Presenters;

use Nette;
use App\Forms\SignFormFactory;
use App\Forms\ChangePasswordFormFactory;
use Tracy\Debugger;


class SignPresenter extends Nette\Application\UI\Presenter {
	/** @var SignFormFactory @inject */
	public $signFormFactory;

	/** @var ChangePasswordFormFactory @inject */
	public $changePasswordFormFactory;

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = $this->signFormFactory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Result:setter');
		};
		return $form;
	}

	protected function createComponentChangePasswordForm() {
		$form = $this->changePasswordFormFactory->create();
		
		$form->onSuccess[] = function ($form) {
			$this->flashMessage("Heslo bylo úspěšně změněno, přihlaste se znovu.", 'success');
			$this->getUser()->logout();
			$this->redirect('in');
		};

		return $form;
	}	

	public function actionOut() {
		$this->getUser()->logout();
		$this->redirect('in');
	}

}
