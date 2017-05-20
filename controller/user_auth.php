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
			if (request::is_ajax()) {
                response::set('error', -1);
                response::set('message', '登陆超时，请重新登陆！');
                response::set('redirect_url', url('controller=user&task=login&return=http_referer'));
                response::ajax();
			} else {
                response::error('登陆超时，请重新登陆！', url('controller=user&task=login&return=http_referer'));
			}
		}
    }
}
