<?php
namespace App\AdminModule\Presenters;

use App\BasePresenter;
use Kdyby;
use Nette\Application\UI\Form;

use Nette\Http\IResponse;
use Symfony\Component\Console\Input\StringInput;
use Kdyby\Console\StringOutput;

class CliPresenter extends AdminBasePresenter{

	protected function createComponentCommandForm(){
		$form = new Form;
		$form->setMethod('get');
		$form->addText('command','command')
			->setDefaultValue('list');

		$form->addSubmit('send', 'ENTER');
		$form->onSuccess[] = function ($this, $values){
			$output = $this->runCommand();
			$this->flashMessage($output);

		};
		return $form;
	}



	function runCommand(){
		$command = $this['commandForm']['command']->getValue();

		$console = $this->context->getService('console.application');

		$output = new StringOutput(StringOutput::VERBOSITY_NORMAL,false,null);
		$res = $console->run(new StringInput($command.' --no-interaction'), $output);

		$output = $output->getOutput();
		if ($res == 0){
			$output.= '<span style="color: green">RETURN CODE: 0</span>';
		} else {
			$output.= '<span style="color: red">RETURN CODE: '.$res.'</span>';
		}

		return $output;
	}

	protected function renderDefault(){
	}

}