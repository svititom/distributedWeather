<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace App\ApiModule\Presenters;

use App\Entities\DeviceManager;
use Nette;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class BasePresenter extends \App\BasePresenter
{



	const HTTP_HEADER_ALLOW = "Access-Control-Allow-Methods";
	const HEADER_AUTHORIZATION = 'auth-token';

	/**
     * @var DeviceManager @inject
     */
	public $deviceManager;



    /**
	 * @var Nette\Application\Application
	 * @inject
	 */
	public $application;

	/**
	 * @var string[]
	 */
	private static $actionMap = [
	    /** whats the difference between read and read all? ~ */
		'read' => IRequest::GET,
		'readAll' => IRequest::GET,
		'create' => IRequest::POST,
		'update' => IRequest::PUT,
		'delete' => IRequest::DELETE,
	];



	protected function startup()
	{
		Debugger::$productionMode = FALSE;

		parent::startup();

		if (!$this->isMethodAllowed($this->getAction())) {
			$this->getHttpResponse()->addHeader(self::HTTP_HEADER_ALLOW, implode(", ", $this->getAllowedMethods()));
			$this->error("Method '{$this->getAction()}' not allowed", IResponse::S405_METHOD_NOT_ALLOWED);
		}
	}



	public function success($status = 'ok', $httpCode = IResponse::S200_OK)
	{
	    $this->getHttpResponse()->setCode($httpCode);
		$this->payload->status = $status;
		$this->sendPayload();
	}



	public function error($error = NULL, $httpCode = IResponse::S404_NOT_FOUND)
	{
		$this->getHttpResponse()->setCode($httpCode);
		$this->payload->error = [
			'message' => $error,
		];
		$this->payload->status = 'error';
		$this->sendPayload();
	}



	/**
	 * Returns TRUE if given action is supported by current presenter.
	 *
	 * @param string $action
	 * @return bool
	 */
	private function isMethodAllowed($action)
	{

		return $this->getReflection()->hasMethod($this->formatActionMethod($action))
			|| $this->getReflection()->hasMethod($this->formatRenderMethod($action));
	}



	/**
	 * Returns array of allowed methods by current presenter.
	 *
	 * @return string[]
	 */
	private function getAllowedMethods()
	{
		$allowedMethods = [];
		foreach (self::$actionMap as $action => $method) {
			if ($this->isMethodAllowed($action)) {
				$allowedMethods[] = $method;
			}
		}

		return array_unique($allowedMethods);
	}



	public function sendTemplate()
	{
		throw new Nette\NotImplementedException;
	}

}
