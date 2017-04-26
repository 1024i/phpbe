<?php
namespace admin\controller;

use \system\be;
use \system\request;

class system extends \admin\system\controller
{

    // 登陆后首页
    public function dashboard()
    {
		$my = be::get_admin_user();

        $template = be::get_admin_template('system.dashboard');
		$template->set_title('后台首页');

		$row_user = be::get_row('user');
		$row_user->load($my->id);
		$template->set('user', $row_user);

		$admin_model_user = be::get_admin_model('user');
		$user_count = $admin_model_user->get_user_count();
		$template->set('user_count', $user_count);

		$admin_model_system = be::get_admin_model('system');
		$template->set('recent_logs', $admin_model_system->get_logs(array('user_id'=>$my->id,'offset'=>0, 'limit'=>10)));
		$template->set('app_count', $admin_model_system->get_app_count());
		$template->set('theme_count', $admin_model_system->get_theme_count());

        $template->display();
    }




	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 菜单管理
	public function menus()
	{
		$group_id = request::get('group_id', 0, 'int');

		$admin_model_system = be::get_admin_model('system');
		
		$groups = $admin_model_system->get_menu_groups();
		if ($group_id == 0) $group_id = $groups[0]->id;
		
		$template = be::get_admin_template('system.menus');
        $template->set_title('菜单列表');
        $template->set('menus', $admin_model_system->get_menus($group_id));
        $template->set('group_id', $group_id);
		$template->set('groups', $groups);
		$template->display();
	}
	
    public function menus_save()
    {
        $group_id = request::post('group_id', 0, 'int');

        $ids = request::post('id', array(), 'int');
        $parent_ids = request::post('parent_id', array(), 'int');
        $names = request::post('name', array());
        $urls =request::post('url', array(), 'html');
        $targets = request::post('target', array());
		$params = request::post('params', array());
        
        if (count($ids)>0) {
            for ($i = 0, $n = count($ids); $i < $n; $i++)
            {
				$id = $ids[$i];

                if ($id == 0 && $names[$i] == '') continue;
                
                $row_system_menu = be::get_row('system_menu');
				if ($id!=0) $row_system_menu->load($id);
                $row_system_menu->group_id = $group_id;
                $row_system_menu->parent_id = $parent_ids[$i];
                $row_system_menu->name = $names[$i];
                $row_system_menu->url = $urls[$i];
                $row_system_menu->target = $targets[$i];
				$row_system_menu->params = $params[$i];
                $row_system_menu->rank = $i;
                $row_system_menu->save();
            }
        }
        
		$admin_model_system = be::get_admin_model('system');
        $admin_model_system->update_menu($group_id);

        $row_system_menu_group = be::get_row('system_menu_group');
        $row_system_menu_group->load($group_id);
        system_log('修改菜单：'.$row_system_menu_group->name);

		$this->set_message('保存菜单成功！');
        $this->redirect('./?controller=system&task=menus&group_id='.$group_id);
    }
    

    public function ajax_menu_delete()
    {
        $id = request::post('id', 0, 'int');
        if (!$id) {
            $this->set('error', 2);
            $this->set('message', '参数(id)缺失！');
        } else {
            $row_system_menu = be::get_row('system_menu');
            $row_system_menu->load($id);
            
            $admin_model_system = be::get_admin_model('system');
            if ($admin_model_system->delete_menu($id)) {
				$admin_model_system->update_menu($row_system_menu->group_id);

                $this->set('error', 0);
                $this->set('message', '删除菜单成功！');
                
                system_log('删除菜单: #'.$id.' '.$row_system_menu->name);
            } else {
                $this->set('error', 3);
                $this->set('message', $admin_model_system->get_error());
            }
        }
        $this->ajax();
    }
    
    public function menu_set_link()
    {
        $id = request::get('id', 0, 'int');
		$url = request::get('url', '','');

		if ($url!='') $url = base64_decode($url);
        
		$template = be::get_admin_template('system.menu_set_link');
        
        $template->set('url', $url);
        
		$admin_model_system = be::get_admin_model('system');
		$apps = $admin_model_system->get_apps();
        $template->set('apps', $apps);
		
		$template->display();
    }
    
    public function ajax_menu_set_home()
    {
		$id = request::get('id', 0, 'int');
        if ($id == 0) {
            $this->set('error', 1);
            $this->set('message','参数(id)缺失！');
        } else {
            $row_system_menu = be::get_row('system_menu');
            $row_system_menu->load($id);
            
            $admin_model_system = be::get_admin_model('system');
            if ($admin_model_system->set_home_menu($id)) {
				$admin_model_system->update_menu($row_system_menu->group_id);

                $this->set('error', 0);
                $this->set('message', '设置首页菜单成功！');
                
                system_log('设置新首页菜单：#'.$id.' '.$row_system_menu->name);
            } else {
                $this->set('error', 2);
                $this->set('message', $admin_model_system->get_error());
            }
        }
        $this->ajax();
    }


	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 菜单分组管理
	public function menu_groups()
	{
		$admin_model_system = be::get_admin_model('system');

		$template = be::get_admin_template('system.menu_groups');
        $template->set_title('添加新菜单组');
		$template->set('groups', $admin_model_system->get_menu_groups());
		$template->display();
	}
	
	
    // 修改菜单组
    public function menu_group_edit()
    {
        $id = request::request('id', 0, 'int');
        
        $row_menu_group =  be::get_row('system_menu_group');
        if ($id != 0) $row_menu_group->load($id);
        
        $template = be::get_admin_template('system.menu_group_edit');
        
        if ($id != 0)
            $template->set_title('修改菜单组');
        else
            $template->set_title('添加新菜单组');

        $template->set('menu_group', $row_menu_group);
        $template->display();
    }

    // 保存修改菜单组
    public function menu_group_edit_save()
    {
        $id = request::post('id', 0, 'int');

		$class_name = request::post('class_name', '');
        $row_menu_group =  be::get_row('system_menu_group');
        $row_menu_group->load(array('class_name'=>$class_name));
		if ($row_menu_group->id>0) {
			$this->set_message('已存在('.$class_name.')类名！', 'error');
			$this->redirect('./?controller=system&task=menu_group_edit&id=' . $id);
		}

        if ($id != 0) $row_menu_group->load($id);
		$row_menu_group->bind(request::post());
        if ($row_menu_group->save()) {
            system_log($id == 0?('添加新菜单组：'.$row_menu_group->name):('修改菜单组：'.$row_menu_group->name));
			$this->set_message($id == 0?'添加菜单组成功！':'修改菜单组成功！');

            $this->redirect('./?controller=system&task=menu_groups');
        } else {
			$this->set_message($row_menu_group->get_error(), 'error');
            $this->redirect('./?controller=system&task=menu_group_edit&id=' . $id);
		}
    }


    // 删除菜单组
    public function menu_group_delete()
    {
        $id = request::post('id', 0, 'int');

        $row_menu_group = be::get_row('system_menu_group');
        $row_menu_group->load($id);

		if ($row_menu_group->id == 0) {
			$this->set_message('菜单组不存在！', 'error');
		} else {
			if (in_array($row_menu_group->class_name, array('north', 'south', 'dashboard'))) {
				$this->set_message('系统菜单不可删除！', 'error');
			} else {
				$admin_model_system = be::get_admin_model('system');
				if ($admin_model_system->delete_menu_group($row_menu_group->id)) {
					system_log('成功删除菜单组！');
					$this->set_message('成功删除菜单组！');
				} else {
					$this->set_message($admin_model_system->get_error(), 'error');
				}
			}
		}

		
        $this->redirect('./?controller=system&task=menu_groups');
		
    }





	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 应用管理
	public function apps()
	{
		$admin_model_system = be::get_admin_model('system');
		$apps = $admin_model_system->get_apps();

		$template = be::get_admin_template('system.apps');
        $template->set_title('已安装的应用');
		$template->set('apps', $apps);
		$template->display();
	}

	public function remote_apps()
	{
	    $admin_model_system = be::get_admin_model('system');
        $remote_apps = $admin_model_system->get_remote_apps(post::_());

		$template = be::get_admin_template('system.remote_apps');
        $template->set_title('安装新应用');
        $template->set('remote_apps', $remote_apps);
		$template->display();
	}
	
	public function remote_app()
	{
		$app_id = request::get('app_id', 0, 'int');
		if ($app_id == 0) be_exit('参数(app_id)缺失！');

	    $admin_model_system = be::get_admin_model('system');
	    
        $remote_app = $admin_model_system->get_remote_app($app_id);

		$template = be::get_admin_template('system.remote_app');
        $template->set_title('安装新应用：'.($remote_app->status == '0'?$remote_app->app->label:''));
        $template->set('remote_app', $remote_app);
		$template->display();
	}

	public function ajax_install_app()
	{
		$app_id = request::get('app_id', 0, 'int');
		if ($app_id == 0) {
		    $this->set('error', 1);
            $this->set('message', '参数(app_id)缺失！');
			$this->ajax();
		}

	    $admin_model_system = be::get_admin_model('system');
        $remote_app = $admin_model_system->get_remote_app($app_id);
	    if ($remote_app->status!='0') {
		    $this->set('error', 2);
            $this->set('message', $remote_app->description);
			$this->ajax();
	    }

		$app = $remote_app->app;
		if (file_exists(PATH_ADMIN.DS.'apps'.DS.$app->name.'php')) {
		    $this->set('error', 3);
            $this->set('message', '已存在安装标识为'.$app->name.'的应用');
			$this->ajax();
		}

        if ($admin_model_system->install_app($app)) {
            system_log('安装新应用：'.$app->name);
            
            $this->set('error', 0);
            $this->set('message', '应用安装成功！');
        } else {
            $this->set('error', 4);
            $this->set('message', $admin_model_system->get_error());
        }

		$this->ajax();
	}

	public function ajax_uninstall_app()
	{
		$app_name = request::get('app_name','');
		if ($app_name == '') {
		    $this->set('error', 1);
            $this->set('message', '参数(app_name)缺失！');
			$this->ajax();
		}

		$admin_model_system = be::get_admin_model('system');
		if ($admin_model_system->uninstall_app($app_name)) {
            system_log('卸载应用：'.$app_name);
            
            $this->set('error', 0);
            $this->set('message', '应用卸载成功！');
        } else {
            $this->set('error', 2);
            $this->set('message', $admin_model_system->get_error());
        }
        
        $this->ajax();
	}


	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 主题管理
	public function themes()
	{
		$admin_model_system = be::get_admin_model('system');
		$themes = $admin_model_system->get_themes(post::_());

		$template = be::get_admin_template('system.themes');
        $template->set_title('已安装的主题');
		$template->set('themes', $themes);
		$template->display();
	}

	// 设置默认主题
	public function ajax_theme_set_default()
	{
		$theme = request::get('theme','');
        if ($theme == '') {
            $this->set('error', 1);
            $this->set('message', '参数(theme)缺失！');
        } else {
            $admin_model_system = be::get_admin_model('system');
            if ($admin_model_system->set_default_theme($theme)) {
                system_log('设置主题（'.$theme.') 为默认主题！');
                
                $this->set('error', 0);
                $this->set('message', '设置默认主题成功！');
            } else {
                $this->set('error', 2);
                $this->set('message', $admin_model_system->get_error());
            }
        }
        $this->ajax();
	}
	

	// 在线主题
	public function remote_themes()
	{
	    $admin_model_system = be::get_admin_model('system');
	    
		$local_themes = $admin_model_system->get_themes();
        $remote_themes = $admin_model_system->get_remote_themes(request::post());

		$template = be::get_admin_template('system.remote_themes');
        $template->set_title('安装新主题');
        $template->set('local_themes', $local_themes);
		$template->set('remote_themes', $remote_themes);
		$template->display();
	}

	// 安装主题
	public function ajax_install_theme()
	{
		$theme_id = request::get('theme_id', 0, 'int');
		if ($theme_id == 0) {
		    $this->set('error', 1);
            $this->set('message', '参数(theme_id)缺失！');
			$this->ajax();
		}

	    $admin_model_system = be::get_admin_model('system');
        $remote_theme = $admin_model_system->get_remote_theme($theme_id);

	    if ($remote_theme->status!='0') {
		    $this->set('error', 2);
            $this->set('message', $remote_theme->description);
			$this->ajax();
	    }

		if ($admin_model_system->install_theme($remote_theme->theme)) {
			system_log('安装新主题：'.$remote_theme->theme->name);

			$this->set('error', 0);
			$this->set('message', '主题新安装成功！');
			$this->ajax();
		} else {
			$this->set('error', 3);
            $this->set('message', $admin_model_system->get_error());
			$this->ajax();
		}
	}




	// 删除主题
	public function ajax_uninstall_theme()
	{
		$theme = request::get('theme','');
        if ($theme == '') {
		    $this->set('error', 1);
            $this->set('message', '参数(theme)缺失！');
			$this->ajax();
		}

		$admin_model_system = be::get_admin_model('system');
		if ($admin_model_system->uninstall_theme($theme)) {
			system_log('卸载主题：'.$theme);

			$this->set('error', 0);
			$this->set('message', '主题卸载成功！');
			$this->ajax();
		} else {
			$this->set('error', 2);
            $this->set('message', $admin_model_system->get_error());
			$this->ajax();
		}
	}


	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 系统配置
    public function config()
	{
		$template = be::get_admin_template('system.config');
		$template->set_title('系统基本设置');
		$template->set('config', be::get_config('system'));
		$template->display();
	}

	public function config_save()
	{
		$config = be::get_config('system');
		$config->offline = request::post('offline', 0, 'int');
		$config->offline_message = request::post('offline_message', '', 'html');
		$config->site_name = request::post('site_name', '');
		$config->sef = request::post('sef', 0, 'int');
		$config->sef_suffix = request::post('sef_suffix', '');
        $config->home_title = request::post('home_title', '');
        $config->home_meta_keywords = request::post('home_meta_keywords', '');
        $config->home_meta_description = request::post('home_meta_description', '');

		$allow_upload_file_types = request::post('allow_upload_file_types', '');
		$allow_upload_file_types = explode(',', $allow_upload_file_types);
		$allow_upload_file_types = array_map('trim', $allow_upload_file_types);
		$config->allow_upload_file_types = $allow_upload_file_types;

		$allow_upload_image_types = request::post('allow_upload_image_types', '');
		$allow_upload_image_types = explode(',', $allow_upload_image_types);
		$allow_upload_image_types = array_map('trim', $allow_upload_image_types);
		$config->allow_upload_image_types = $allow_upload_image_types;

		config::save($config, PATH_ROOT.DS.'configs'.DS.'system.php');
		
		system_log('改动系统基本设置');
		
		$this->set_message('保存成功！');
		$this->redirect('./?controller=system&task=config');
	}
	
	
	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 邮件服务配置
	public function config_mail()
	{
		$config = be::get_config('system_mail');

		$template = be::get_admin_template('system.config_mail');
		$template->set_title('发送邮件设置');
		$template->set('config', $config);
		$template->display();
	}

	public function config_mail_save()
	{
		$config = be::get_config('system_mail');

		$config->from_mail = request::post('from_mail', '');
		$config->from_name = request::post('from_name', '');
		$config->smtp = request::post('smtp', 0, 'int');
		$config->smtp_host = request::post('smtp_host', '');
		$config->smtp_port = request::post('smtp_port', 0, 'int');
		$config->smtp_user = request::post('smtp_user', '');
		$config->smtp_pass = request::post('smtp_pass', '');
        $config->smtp_secure = request::post('smtp_secure', '');

		$admin_model_system = be::get_admin_model('system');
		$admin_model_system->save_config_file($config, PATH_ROOT.DS.'configs'.DS.'system_mail.php');
		
		system_log('改动发送邮件设置');
		
		$this->set_message('保存成功！');
		$this->redirect('./?controller=system&task=config_mail');
	}

	public function config_mail_test()
	{
		$template = be::get_admin_template('system.config_mail_test');
		$template->set_title('发送邮件测试');
		$template->display();
	}

	public function config_mail_test_save()
	{
		$to_email = request::post('to_email', '');
		$subject = request::post('subject', '');
		$body = request::post('body', '', 'html');

		$lib_mail = be::get_lib('mail');
		$lib_mail->set_subject($subject);
		$lib_mail->set_body($body);
		$lib_mail->to($to_email);

		if ($lib_mail->send()) {
            system_log('发送测试邮件到 '.$to_email.' -成功');
			$this->set_message('发送邮件成功！');
		} else {
			$error = $lib_mail->get_error();

			system_log('发送测试邮件到 '.$to_email.' -失败：'.$error);
			$this->set_message('发送邮件失败：'.$error, 'error');
		}

		$this->redirect('./?controller=system&task=config_mail_test&to_email='.$to_email);
	}


	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 水印设置
	public function config_watermark()
	{
		$config = be::get_config('system_watermark');

		$template = be::get_admin_template('system.config_watermark');
		$template->set_title('水印设置');
		$template->set('config', $config);
		$template->display();
	}

	private function is_rgb_color($arr)
	{
		if (!is_array($arr)) return false;
		if (count($arr)!=3) return false;
		foreach ($arr as $x) {
			if (!is_numeric($x)) return false;
			$x = intval($x);
			if ($x<0) return false;
			if ($x>255) return false;
		}
		return true;
	}

	public function config_watermark_save()
	{
		$config = be::get_config('system_watermark');

		$config->watermark = request::post('watermark', 0, 'int');
		$config->type = request::post('type', '');
		$config->position = request::post('position', '');
		$config->offset_x = request::post('offset_x', 0, 'int');
		$config->offset_y = request::post('offset_y', 0, 'int');

		$config->text = request::post('text', '');
		$config->text_size = request::post('text_size', 0, 'int');

		$text_color = request::post('text_color', '');
		$text_colors = explode(',', $text_color);
		$text_colors = array_map('trim', $text_colors);

		if (!$this->is_rgb_color($text_colors)) $text_colors = array(255, 0, 0);
		$config->text_color = $text_colors;

		$image = $_FILES['image'];
		if ($image['error'] == 0) {
			$lib_image = be::get_lib('image');
			$lib_image->open($image['tmp_name']);
			if ($lib_image->is_image()) {
				$watermark_name = date('YmdHis').'.'.$lib_image->get_type();
				$watermark_path = PATH_DATA.DS.'system'.DS.'watermark'.DS.$watermark_name;
				if (move_uploaded_file($image['tmp_name'], $watermark_path)) {
					// @unlink(PATH_DATA.DS.'system'.DS.'watermark'.DS.$config->image);
					$config->image = $watermark_name;
				}
			}
		}

		$admin_model_system = be::get_admin_model('system');
		$admin_model_system->save_config_file($config, PATH_ROOT.DS.'configs'.DS.'system_watermark.php');
		
		system_log('修改水印设置');
		
		$this->set_message('保存成功！');
		$this->redirect('./?controller=system&task=config_watermark');
	}

	public function config_watermark_test()
	{
		$src = PATH_DATA.DS.'system'.DS.'watermark'.DS.'test-0.jpg';
		$dst = PATH_DATA.DS.'system'.DS.'watermark'.DS.'test-1.jpg';

		if (!file_exists($src)) be_exit(DATA.'/system/watermakr/test-0.jpg 文件不存在');
		if (file_exists($dst)) @unlink($dst);

		copy($src, $dst);

		sleep(1);

		$admin_model_system = be::get_admin_model('system');
		$admin_model_system->watermark($dst);

		$template = be::get_admin_template('system.config_watermark_test');
		$template->set_title('水印预览');
		$template->display();
	}
	


	// ==  ==  ==  ==  ==  ==  ==  ==  ==  === 系统日志
	public function logs()
	{
		$user_id = request::post('user_id', 0, 'int');
		$key = request::post('key', '');
		$limit = request::post('limit', -1, 'int');
		if ($limit == -1) {
			$admin_config_system = be::get_admin_config('system');
			$limit = $admin_config_system->limit;
		}
		
		$admin_model_system = be::get_admin_model('system');
		$template = be::get_admin_template('system.logs');
        $template->set_title('系统日志');

		$pagination = be::get_admin_ui('pagination');
		$pagination->set_limit($limit);
		$pagination->set_total($admin_model_system->get_log_count(array('user_id'=>$user_id, 'key'=>$key)));
		$pagination->set_page(request::post('page', 1, 'int'));

		$template->set('pagination', $pagination);
		$template->set('user_id', $user_id);
		$template->set('key', $key);
		$template->set('admins', $admin_model_system->get_admins());
		$template->set('logs', $admin_model_system->get_logs( array('user_id'=>$user_id, 'key'=>$key, 'offset'=>$pagination->get_offset(), 'limit'=>$limit)));
		
		$template->display();
	}

	// 后台登陆日志
    public function ajax_delete_logs()
    {
		$admin_model_system = be::get_admin_model('system');
        $admin_model_system->delete_logs();

		system_log('删除三个月前系统日志');

		$this->set('error', 0);
		$this->set('message', '删除日志成功！');
		$this->ajax();
    }

	public function history_back()
	{
		$lib_history = be::get_lib('history');
		$lib_history->back();
	}
    
}
?>