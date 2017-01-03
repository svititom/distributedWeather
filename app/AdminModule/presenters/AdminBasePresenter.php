<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 02-Jan-17
 * Time: 23:56
 */

namespace App\AdminModule\Presenters;


class AdminBasePresenter extends \App\BasePresenter
{
	function startup(){
		parent::startup();
		if (!$this->getUser()->isLoggedIn()){
			$this->flashMessage('You need to be logged in before using the admin module');
			$this->redirect(':Front:Sign:In');
		} else if (!$this->getUser()->isInRole('admin')){
			$this->flashMessage('You need to be in the admin role to access the admin module');
			$this->redirect(':Front:Sign:In');
		}

	}
}