<?php

namespace App\FrontModule\Presenters;

use Instante\Bootstrap3Renderer\RenderModeEnum;
use Nette;
use App\Model\ArticleManager;



class HomepagePresenter extends \App\BasePresenter
{
    private $articleManager;

	/** @var \Instante\ExtendedFormMacros\IFormFactory @inject */
    public $formFactory;

	public function createComponentSignInForm(){
		$form = $this->formFactory->create();
		$form->getRenderer()->setRenderMode(RenderModeEnum::INLINE);
		$form->addText('name','UserName:')
			->setRequired('Username required');
		$form->addPassword('password','Password')
			->setRequired('Password Required');
		$form->addSubmit('send', 'Login');

		$form->onSuccess[] = [$this, 'signInSucceeded'];
		return $form;
	}
	public function createComponentSignOutForm(){
		$form = $this->formFactory->create();
		return $form;
	}

	public function __construct(ArticleManager $articleManager)
	{
        $this->articleManager = $articleManager;
    }

    public function renderDefault()
	{
		$this->template->posts = $this->articleManager->getPublicArticles();
        $this->template->userIdentity = $this->getUser()->getId();
	}

}
