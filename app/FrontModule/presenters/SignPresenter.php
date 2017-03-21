<?php

namespace App\FrontModule\Presenters;

use App\Model\UserMailer;
use App\Model\UserNotFoundException;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;


class SignPresenter extends \App\BasePresenter
{

	/** @var \Instante\ExtendedFormMacros\IFormFactory @inject */
	public $formFactory;

	/** @var  \App\Model\UserManager @inject */
	public $userManager;

	/** @persistent */
	public $backlink = '';


	const PASSWORD_MIN_LENGTH = 7;
	//todo figure out why admin module is not redirecting back
	public function signInSuccess($form, $values){
		try {
			$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('You need to enter a valid username and password.');
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
		$form->addText('username')
			->setRequired('Please enter your username.')
			->setAttribute('placeholder', 'Username');
		$form->addPassword('password')
			->setRequired('Please enter your password.')
			->setAttribute('placeholder', 'Password');
		$form->addCheckbox('remember', 'Keep me signed in');
		$form->addProtection('You have been logged out due to a timeout');
		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = [$this, 'signInSuccess'];
		return $form;
	}


	public function signUpSuccess($form, $values){
		try {
			$this->userManager->addUser($values->username, $values->email, $values->password);
		} catch (\App\Model\DuplicateNameException $e) {
			$message = 'Sorry, please choose a different username, ' . $values->username . ' is already taken';
			$form->addError($message);
			return;
		} catch (\App\Model\DuplicateEmailException $e) {
			$message = 'Sorry, please choose a different email, ' . $values->email . ' is already taken';
			$form->addError($message);
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
		$form->addText('username')
			->setRequired('Please pick a username.')
			->setAttribute('placeholder','Username');
		$form->addText('email')
			->setRequired('Please enter your e-mail.')
			->setAttribute('placeholder','E-mail')
			->addRule($form::EMAIL);
		$form->addPassword('password')
			->setRequired('Please create a password.')
			->setAttribute('placeholder', sprintf('Password, at least %d characters', self::PASSWORD_MIN_LENGTH))
			->addRule($form::MIN_LENGTH, NULL, self::PASSWORD_MIN_LENGTH);
		$form->addPassword('passwordVerify')
			->setRequired('Please retype your password')
			->addRule(Form::EQUAL, 'Passwords are not the same', $form['password'])
			->setAttribute('placeholder','Retype your password');
		$form->addSubmit('send', 'Sign up');
		$form->onSuccess[] = [$this, 'signUpSuccess'];
		return $form;

	}

	public function changePasswordSucces($form, $values){
		//todo implement
	}
	public function createComponentChangePassword(){
		$form = $this->formFactory->create();
		$form->addPassword('password')
			->setRequired('Please create a password.')
			->setAttribute('placeholder', sprintf('Password, at least %d characters', self::PASSWORD_MIN_LENGTH))
			->addRule($form::MIN_LENGTH, NULL, self::PASSWORD_MIN_LENGTH);
		$form->addPassword('passwordVerify')
			->setRequired('Please retype your password')
			->addRule(Form::EQUAL, 'Passwords are not the same', $form['password'])
			->setAttribute('placeholder','Retype your password');
		$form->addProtection('Time limit exceeded, please try again');
		//todo implement
		$form->addSubmit('send', 'Change Password');
		$form->onSuccess[] = [$this, 'changePasswordSuccess'];
		return $form;
	}

	public function renderChangePassword($email, $hash){
		//todo implement
	}


	protected function createComponentForgotForm(){
		$form = $this->formFactory->create();
		$form->addText('email', 'Your email:')
			->setRequired('We can\'t send you the reset link if you don\'t tell us your email ;)')
			->addRule($form::EMAIL);
		$form->addSubmit('send', 'Send reset link');
		$form->onSuccess[] = function ($form, $values){
			$this->userManager->sendResetMail($values->email);
		};
		return $form;
	}

	public function actionVerify($email, $hash){
		//todo beautify!
		try {
			$result = $this->userManager->verifyUser($email, $hash);
			$this->flashMessage($result);
		} catch (UserNotFoundException $e){
			$this->flashMessage('User not found!');
		};
	}

	public function actionOut()
	{
		$this->getUser()->logout();
        $this->flashMessage('Logout successful');
        $this->redirect('Homepage:');
	}
	



}
