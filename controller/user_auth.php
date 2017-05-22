<?php
namespace controller;

use \system\be;
use \system\request;
use \system\response;

class user_auth extends \system\controller
{
    public function __construct()
    {
		$my = be::get_user();
        if ($my->id == 0) {
            response::error('登陆超时，请重新登陆！', url('controller=user&task=login&return=http_referer'), -1);
		}
    }
}
