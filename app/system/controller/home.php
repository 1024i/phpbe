<?php
namespace App\System\Controller;

use Phpbe\System\Be;
use Phpbe\System\Response;
use Phpbe\System\Controller;

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
