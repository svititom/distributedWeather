<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\Model;


class MyWeatherDataPresenter extends \App\BasePresenter
{

	/** @var \Instante\ExtendedFormMacros\IFormFactory @inject */
	public $formFactory;

    protected function createComponentWeatherForm() {
    	$form = $this->formFactory->create();
		$form->addText('idea','Your ideas');
		$form->addSubmit('send');
		return $form;
	}

    public function renderDefault()
	{
		if(!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
		}
	}

}
