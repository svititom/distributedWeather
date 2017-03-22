<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 02-Jan-17
 * Time: 23:56
 */

namespace App\AdminModule\Presenters;


use Nette\Http\IResponse;

abstract class BaseSecurePresenter extends \App\BasePresenter
{
	function startup(){
		parent::startup();
		if (!$this->getUser()->isLoggedIn()){
			$this->flashMessage('You need to be logged in before using the admin module');
			$this->redirect(':Front:Sign:In', array('backlink' => $this->storeRequest()));
		} else if (!$this->getUser()->isInRole('admin')){
			$this->flashMessage('You need to be in the admin role to access the admin module');
			$this->error("You need to be in the administrator role",IResponse::S401_UNAUTHORIZED);
			//$this->redirect(':Front:Sign:In', array('backlink' => $this->storeRequest()));
		}

	}
}