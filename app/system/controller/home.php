<?php
namespace App\System\Controller;

use System\Be;
use System\Response;
use System\Controller;

class Home extends Controller
{
	public function home()
	{
		$configSystem = Be::getConfig('System.System');
		Response::setTitle($configSystem->homeTitle);
        Response::setMetaKeywords($configSystem->homeMetaKeywords);
        Response::setMetaDescription($configSystem->homeMetaDescription);
        Response::display();
	}
}
