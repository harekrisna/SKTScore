<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tracy\Debugger;
use Nette\Security\Passwords;

class ChangePasswordFormFactory extends Nette\Object {
	/** @var FormFactory */
	private $factory;
	/** @var User */
	private $user;
	/** @var UserManager */
	private $user_manager;
	/** @var Admin */
	private $admin;


	public function __construct(FormFactory $factory, User $user, \App\Model\UserManager $user_manager, \App\Model\Admin $admin) {
		$this->factory = $factory;
		$this->user = $user;
		$this->user_manager = $user_manager;
		$this->admin = $admin;
	}
	

	/**
	 * @return Form
	 */
	public function create() {
		$form = new Form;
		$form->addPassword('actual_password', 'Současné heslo')
			 ->setRequired('Prosím zadej aktuální heslo.');

		$form->addPassword('new_password', 'Nové heslo')
			 ->setRequired('Prosím zadej nové heslo.')
			 ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 6);

		$form->addPassword('new_password_verify', 'Nové heslo pro kontrolu')
			 ->setRequired('Prosím zadej heslo ještě jednou pro kontrolu.')
			 ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['new_password']);

		$form->addSubmit('send', 'Změnit heslo');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}


	public function formSucceeded(Form $form, $values) {
		try {
			$this->user_manager->authenticate([$this->user->getIdentity()->username, $values->actual_password]);
			$this->admin->update($this->user->getId(), ['password' => Passwords::hash($values->new_password),
														'password_readable' => $values->new_password,
														'need_change_password' => 0]);

		} catch (Nette\Security\AuthenticationException $e) {
			$form['actual_password']->addError('Nesprávné současné heslo.');
		}
	}
}
