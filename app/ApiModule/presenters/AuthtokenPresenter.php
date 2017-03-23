<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace App\ApiModule\Presenters;

use App\Entities\DeviceManager;
use App\Entities\NoSuchDeviceException;
use App\Model\UserManager;
use Nette;
use Nette\Http\IResponse;
use Nette\Security\User;



/**
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class AuthtokenPresenter extends BasePresenter
{

    /**
     * @var DeviceManager devicemanager @inject
     */
    public $deviceManager;

    /**
     * Gets the existing Auth Token
     */
    public function actionRead()
    {
        list($username, $password, $deviceName) = $this->checkCallParameters($this->getHttpRequest()->getHeaders());
        try {

            $this->user->login($username, $password);
            $this->payload->access_token =  $this->deviceManager->getDeviceAuthToken($username, $deviceName);
            $this->success();
        } catch (NoSuchDeviceException $e){
            $this->error("No such device, did you use device id instead of name by mistake?", IResponse::S404_NOT_FOUND);
        } catch (Nette\Security\AuthenticationException $e) {
            $this->error("Invalid credentials", IResponse::S401_UNAUTHORIZED);
        }
    }

    public function actionReadAll()
    {
        $this->actionRead();
    }

    /**
     * Generates a new Auth Token
     */
    public function actionCreate()
    {
        //this is a post, so it generates a completely new token
        list($username, $password, $deviceName) = $this->checkCallParameters($this->getHttpRequest()->getHeaders());

        try {

            $this->user->login($username, $password);
            $this->payload->access_token =  $this->deviceManager->updateDeviceAuthToken($username, $deviceName);
            $this->success();

        } catch (NoSuchDeviceException $e){
            $this->error("No such device, did you use device id instead of name by mistake?", IResponse::S404_NOT_FOUND);
        } catch (Nette\Security\AuthenticationException $e) {
            $this->error("Invalid credentials", IResponse::S401_UNAUTHORIZED);
        }
    }

    /**
     * @return array
     */
    public function checkCallParameters( array $headers): array
    {
        if (!$username = $headers['username']) {
            $this->error("Missing field 'username'", IResponse::S400_BAD_REQUEST);
        }

        if (!$password = $headers['password']) {
            $this->error("Missing field 'password'", IResponse::S400_BAD_REQUEST);
        }

        if (!$deviceName = $headers['devicename']) {
            $this->error("Missing field 'devicename", IResponse::S400_BAD_REQUEST);
            return array($username, $password, $deviceName);
        }
        return array($username, $password, $deviceName);
    }

}
