<?php
namespace App\AdminModule\Presenters;

use Kdyby;
use Nette\Application\UI\Form;

use Nette\Http\IResponse;
use Symfony\Component\Console\Input\StringInput;
use Kdyby\Console\StringOutput;

class CliPresenter extends BaseSecurePresenter{

	protected function createComponentCommandForm(){
		$form = new Form;
		$form->setMethod('get');
		$form->addText('command','command')
			->setDefaultValue('list');

		$form->addSubmit('send', 'ENTER');
		$form->onSuccess[] = function ($form, $values){
			$output = $this->runCommand();
			$this->template->CliOutput = $output[0];
			$this->template->statusCode = $output[1];
			$this->template->statusColor = ($output[1] == 0) ? 'green' : 'red';

		};
		return $form;
	}


	/**
	 * @return array [string command output, return code]
	 */
	function runCommand(){
		$command = $this['commandForm']['command']->getValue();

		$console = $this->context->getService('console.application');

		$output = new StringOutput(StringOutput::VERBOSITY_NORMAL,false,null);
		$res = $console->run(new StringInput($command .' --no-interaction'), $output);
		$output = $output->getOutput();
		return [$output,$res];
	}

	public function renderDefault(){
		if(!$this->template->CliOutput) {
			$this->template->CliOutput = '';//todo is this working?
			$this->template->statusColor = 'black';
			$this->template->statusCode = 0;
		}

	}


}