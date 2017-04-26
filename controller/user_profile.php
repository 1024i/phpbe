<?php
namespace controller;

use \system\be;
use \system\request;

class user_profile extends user_auth
{

	public function home()
	{
        $template = be::get_template('user_profile.home');
        $template->set_title('用户中心');
        $template->display();
	}


    // 上传头像
    public function edit_avatar()
    {
        $template = be::get_template('user_profile.edit_avatar');
        $template->set_title('上传头像');
        $template->display();
    }


    // 上传头像 保存
    public function edit_avatar_save()
    {
		$config_system = be::get_config('system');

		$avatar = $_FILES['avatar'];
        if ($avatar['error'] == 0) {
			$name = strtolower($avatar['name']);
			$type = '';
			$pos = strrpos($name, '.');
			if ($pos! == false) {
				$type = substr($name, $pos+1);
			}
			if (!in_array($type, $config_system->allow_upload_image_types)) {
				$this->set_message('您上传的不是合法的图像文件！', 'error');
			} else {
				$lib_image = be::get_lib('image');
				$lib_image->open($avatar['tmp_name']);
				if (!$lib_image->is_image()) {
					$this->set_message('您上传的不是合法的图像文件！', 'error');
				} else {
					$my = be::get_user();

					$row_user = be::get_row('user');
					$row_user->load($my->id);

					$config_user = be::get_config('user');
					
                    $avatar_dir = PATH_DATA.DS.'user'.DS.'avatar'.DS;
                    
					// 删除旧头像
					if ($row_user->avatar_s!='') @unlink($avatar_dir.$row_user->avatar_s);
					if ($row_user->avatar_m!='') @unlink($avatar_dir.$row_user->avatar_m);
					if ($row_user->avatar_l!='') @unlink($avatar_dir.$row_user->avatar_l);

					$t = date('YmdHis');
                    
                    $image_type = $lib_image->get_type();

					// 按配置文件里的尺寸大小生成新头像
					$lib_image->resize($config_user->avatar_l_w, $config_user->avatar_l_h, 'north');
					$lib_image->save($avatar_dir.$my->id.'_'.$t.'_l.'.$image_type);
					$my->avatar_l = $row_user->avatar_l = $my->id.'_'.$t.'_l.'.$image_type;

					$lib_image->resize($config_user->avatar_m_w, $config_user->avatar_m_h, 'north');
					$lib_image->save($avatar_dir.$my->id.'_'.$t.'_m.'.$image_type);
					$my->avatar_m = $row_user->avatar_m = $my->id.'_'.$t.'_m.'.$image_type;

					$lib_image->resize($config_user->avatar_s_w, $config_user->avatar_s_h, 'north');
					$lib_image->save($avatar_dir.$my->id.'_'.$t.'_s.'.$image_type);
					$my->avatar_s = $row_user->avatar_s = $my->id.'_'.$t.'_s.'.$image_type;

					if ($row_user->save()) {
						$this->set_message('您的头像已更新！');
					} else {
						$this->set_message($row_user->get_error(), 'error');
					}
				}
			}
			
			@unlink($avatar['tmp_name']);
        } else {
			$upload_errors = array(
                '1'=>'您上传的文件过大！',
                '2'=>'您上传的文件过大！',
                '3'=>'文件只有部分被上传！',
                '4'=>'没有文件被上传！',
                '5'=>'上传的文件大小为 0！'
           );
			$error = '';
			if (array_key_exists($avatar['error'], $upload_errors)) {
				$error = $upload_errors[$avatar['error']];
			} else {
				$error = '错误代码：'.$avatar['error'];
			}
			$this->set_message('上传失败'.'('.$error.')', 'error');
		}

		$this->redirect(url('controller=user_profile&task=edit_avatar'));
	}

	// 删除头像，即改成系统默认头像
	public function init_avatar()
	{
		$my = be::get_user();

		$row_user = be::get_row('user');
		$row_user->load($my->id);

		$config_user = be::get_config('user');
		
        $avatar_dir = PATH_DATA.DS.'user'.DS.'avatar'.DS;
        
		// 删除旧头像
		if ($row_user->avatar_s != '') @unlink($avatar_dir.$row_user->avatar_s);
		if ($row_user->avatar_m != '') @unlink($avatar_dir.$row_user->avatar_m);
		if ($row_user->avatar_l != '') @unlink($avatar_dir.$row_user->avatar_l);

		// 改为默认头像
		$my->avatar_s = $row_user->avatar_s = '';
		$my->avatar_m = $row_user->avatar_m = '';
		$my->avatar_l = $row_user->avatar_l = '';

		if ($row_user->save()) {
			$this->set_message('您的头像已删除！');
        } else {
            $this->set_message($row_user->get_error(), 'error');
        }

		$this->redirect(url('controller=user_profile&task=edit_avatar'));
	}


    // 修改用户资料
    public function edit()
    {
        $template = be::get_template('user_profile.edit');
        $template->set_title('修改资料');
        $template->display();
    }

    // 修改用户资料
    public function ajax_edit_save()
    {
		$my = be::get_user();

		$row_user = be::get_row('user');
		$row_user->load($my->id);
        
		$my->name = $row_user->name = request::post('name', '');
        $my->gender = $row_user->gender = request::post('gender', 0, 'int');
		$my->phone = $row_user->phone = request::post('phone', '');
		$my->mobile = $row_user->mobile = request::post('mobile', '');
		$my->qq = $row_user->qq = request::post('qq', '');

        if ($row_user->save()) {
            $this->set('error', 0);
            $this->set('message', '您的资料已保存！');
            $this->ajax();
        } else {
            $this->set('error', 2);
            $this->set('message', $row_user->get_error());
            $this->ajax();
        }
    }

    // 修改密码
    public function edit_password()
    {
        $template = be::get_template('user_profile.edit_password');
        $template->set_title('修改密码');
        $template->display();
    }

    // 修改密码
    public function ajax_edit_password_save()
    {
		$my = be::get_user();

        $password = request::post('password', '');
        $password1 = request::post('password1', '');
		$password2 = request::post('password2', '');

		$row_user = be::get_row('user');
		$row_user->load($my->id);

		$model_user = be::get_model('user');
		if ($model_user->encrypt_password($password)!=$row_user->password) {
            $this->set('error', 1);
            $this->set('message', '当前密码错误！');
            $this->ajax();
		}

        if ($password1 != $password2) {
            $this->set('error', 2);
            $this->set('message', '两次输入的密码不匹配！');
            $this->ajax();
        }

		$row_user->password = $model_user->encrypt_password($password1);
        
        if ($row_user->save()) {
            $this->set('error', 0);
            $this->set('message', '您的密码已重设！');
            $this->ajax();
        } else {
            $this->set('error', 3);
            $this->set('message', $row_user->get_error());
            $this->ajax();
        }
    
    }

}
?>