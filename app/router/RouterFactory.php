<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use App\Api\Routers\RestRoute;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = $api = new RouteList('Api');
		$api[] = new RestRoute('/api/access_token', 'AccessToken:');
		$api[] = new RestRoute('/api/shows[/<id>]', 'Shows:');

		$router[] = $modules = new RouteList('Admin');
		$modules[] = new Route('/admin/<presenter>/<action>', 'Cli:default');

		$router[] = $front = new RouteList('Front');
//		$front[] = new Route('<presenter>/<action> ? email=<email> & hash=<hash>', 'Sign:verify');
		$front[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
