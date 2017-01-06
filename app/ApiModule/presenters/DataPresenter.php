<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 03-Jan-17
 * Time: 14:25
 */

namespace App\ApiModule\Presenters;
use Nette;


class DataPresenter extends BasePresenter
{
	//authorization required
	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->error("Unauthorized, Request token at /api/access_token", Nette\Http\IResponse::S401_UNAUTHORIZED);
		}
	}

	//insert into db
	//todo get a json parser to define requirements
	public function actionCreate()
	{
		$this->success();
	}

}