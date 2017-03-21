<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 18-Mar-17
 * Time: 00:04
 */

namespace App\FrontModule\Presenters;


class BaseSecurePresenter extends \App\BasePresenter
{
    function startup()
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('You need to be logged in before using the admin module');
            $this->redirect(':Front:Sign:In', array('backlink' => $this->storeRequest()));
        }
    }
}