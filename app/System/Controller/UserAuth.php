<?php
namespace App\System\Controller;

use System\Be;
use System\Response;
use System\Controller;

class UserAuth extends Controller
{
    public function __construct()
    {
		$my = Be::getUser();
        if ($my->id == 0) {
            Response::error('登陆超时，请重新登陆！', url('controller=user&task=login&return=httpReferer'), -1);
		}
    }
}
