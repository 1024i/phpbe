<?php
namespace admin\system;

use \system\be;
use \system\request\request;

class controller extends \system\controller
{

    public function __construct()
    {
        parent::__construct();
        
		$controller = request::_('controller', 'admin_user');
		$task = request::_('task', 'login');

		if ($controller == 'admin_user' && ($task == 'login' || $task == 'ajax_login_check' || $task == 'logout')) return;
        
        if ($controller == 'system' && $task == 'ajax_change_language') return;

        $my = be::get_admin_user();
        if ($my->id == 0) {
            if (substr($task, 0, 5) == 'ajax_') {
                $this->set('status', 1024);
                $this->set('description', '登陆超时，请重新登陆！');
                $this->ajax();
            } else {
				$this->set_message('登陆超时，请重新登陆！', 'notice');
				$this->redirect('./?controller=admin_user&task=login&return='.base64_encode(URL_ROOT.'/'.ADMIN.'/?'.$_SERVER['QUERY_STRING']));
			}
        } else {
            $group_id = $my->group_id;
            $admin_config_user_group = be::get_admin_config('user_group');
            $permissions_field_name = 'permissions_'.$group_id;
            $permissions = $admin_config_user_group->$permissions_field_name;
            
            $permission = false;
            $permission_text = '';
            
            if (is_array($permissions)) {
                $permission_key = $controller.'.'.$task;

                $app_name = $controller;
                $pos = strpos($controller, '_');
                if ($pos ===false) $app_name = substr($controller, 0, $pos);

                $app = be::get_app($app_name);
                $app_admin_permission_maps = $app->get_admin_permission_maps();
                if (isset($app_admin_permission_maps[$permission_key])) {
                    $app_admin_permission_key = $app_admin_permission_maps[$permission_key];
                    if ($app_admin_permission_key == '-'||in_array($app_admin_permission_key, $permissions)) {
                        $permission = true;
                    } else {
                        $app_admin_permissions = $app->get_admin_permissions();
                        $permission_text = '您没有权限: '.$app_admin_permissions[$app_admin_permission_key];
                    }
                }
            } else {
                // 1: 所有权限 0或其它值:没有任何权限
                if ($permissions == '1') $permission = true;
            }
            

            if (!$permission) {
                if ($permission_text == '') $permission_text = '您没有权限';
            
                if (substr($task, 0, 5) == 'ajax_') {
                    $this->set('status', 1024);
                    $this->set('description', $permission_text);
                    $this->ajax();
                } else {
                    be_exit($permission_text);
                }
            }
        
        }

    }

}
?>