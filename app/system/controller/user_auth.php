<?php
namespace controller;

use System\Be;
use System\Request;
use System\Response;

class userAuth extends \System\Controller
{
    public function __construct()
    {
		$my = Be::getUser();
        if ($my->id == 0) {
            Response::error('登陆超时，请重新登陆！', url('controller=user&task=login&return=httpReferer'), -1);
		}
    }
}
