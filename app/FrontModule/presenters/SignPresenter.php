<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\Forms;


class SignPresenter extends \App\BasePresenter
{

	/** @var \Instante\ExtendedFormMacros\IFormFactory @inject */
	public $formFactory;

	/** @persistent */
	public $backlink = '';


	const PASSWORD_MIN_LENGTH = 7;

	public function signInSuccess($form, $values){
		try {
			$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('The username or password you entered is incorrect.');
			return;
		}
		$this->restoreRequest($this->backlink);
		$this->redirect('Homepage:');
	}
	/**
	 * Sign-in form factory.
	 * @return Nette\Forms\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');
		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');
		$form->addCheckbox('remember', 'Keep me signed in');
		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = [$this, 'signInSuccess'];
		return $form;
	}


	public function signUpSuccess($form, $values){
		try {
			$this->userManager->add($values->username, $values->email, $values->password);
		} catch (\App\Model\DuplicateNameException $e) {
			$form->addError('Username is already taken.');
			return;
		}
		$this->redirect('Sign:in');
	}
	/**
	 * Sign-up form factory.
	 * @return \Nette\Forms\Form
	 */
	protected function createComponentSignUpForm()
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Pick a username:')
			->setRequired('Please pick a username.');
		$form->addText('email', 'Your e-mail:')
			->setRequired('Please enter your e-mail.')
			->addRule($form::EMAIL);
		$form->addPassword('password', 'Create a password:')
			->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
			->setRequired('Please create a password.')
			->addRule($form::MIN_LENGTH, NULL, self::PASSWORD_MIN_LENGTH);
		$form->addSubmit('send', 'Sign up');
		$form->onSuccess[] = [$this, 'signUpSuccess'];
		return $form;

	}




	public function actionOut()
	{
		$this->getUser()->logout();
        $this->flashMessage('Logout successful');
        $this->redirect('Homepage:');
	}

}
