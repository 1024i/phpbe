<?php
namespace controller;

use \system\be;
use \system\request;

class user_auth extends \system\controller
{

    public function __construct()
    {
		$user = be::get_user();
        if ($user->id == 0) {
			if (request::is_ajax()) {
				$this->set('error', -1);
				$this->set('message', '登陆超时，请重新登陆！');
				$this->set('redirect_url', url('controller=user&task=login&return=http_referer'));
				$this->ajax();
			} else {
				$this->set_message('登陆超时，请重新登陆！', 'error');
				$this->redirect(url('controller=user&task=login&return=http_referer'));
			}
		}
    }
}
?>