<?php
namespace admin\template\admin_user;

class edit extends \admin\theme
{
    
	protected function head()
	{
		$ui_editor = be::get_admin_ui('editor');
		$ui_editor->head();
		
	    $user = $this->get('user');
	    echo '<script type="text/javascript" language="javascript" src="template/user/js/edit.js"></script>';
	    if (($user->id>0)) {
		    echo '<script type="text/javascript" language="javascript">$(function(){hidePassword();});</script>';
		}

	}
    

	protected function center()
	{
		$user = $this->get('user');
				
		$config_user_group = be::get_config('user_group');
		$admin_config_user_group = be::get_admin_config('user_group');

		$group_options = array();
		foreach ($config_user_group->names as $id=>$name) {
			if ($id == 1) continue;
			$group_options[$id] = $name;
		}
		
		$admin_group_options = array();
		$admin_group_options[0] = '无后台权限';
		foreach ($admin_config_user_group->names as $id=>$name) {
			$admin_group_options[$id] = $name;
		}
		
		// 新建用户时选中默认用户组
		if ($user->id == 0) $user->group_id = $config_user_group->default;

		$ui_editor = be::get_admin_ui('editor');
		$ui_editor->set_action('save', './?controller=user&task=edit_save');	// 显示提交按钮
		$ui_editor->set_action('reset');// 显示重设按钮
		$ui_editor->set_action('back');	// 显示返回按钮
		$field_username = array(
				'type'=>'text',
			    'name'=>'username',
			    'label'=>'用户名',
				'value'=>$user->username,
				'width'=>'200px',
				'validate'=>array(
					'required'=>true,
			        'min_length'=>3,
					'max_length'=>60
				),
			);

		$field_email = array(
				'type'=>'text',
			    'name'=>'email',
			    'label'=>'邮箱',
				'value'=>$user->email,
				'width'=>'200px',
            	'validate'=>array(
					'required'=>true,
                    'email'=>true,
					'max_length'=>60
				)
			);
			
		$filed_password = array(
				'type'=>'password',
			    'name'=>'password',
			    'label'=>'密码',
				'width'=>'180px',
				'validate'=>array(
			        'min_length'=>5
				)
			);
			
		$filed_confirm_password = array(
				'type'=>'password',
			    'name'=>'password2',
			    'label'=>'确认密码',
				'width'=>'180px',
				'validate'=>array(
					'equal_to'=>'password'
				),
				'message'=>array(
					'equal_to'=>'两次输入的密码不匹配！'
				)
			);
			
	    if (($user->id == 0)) {
		    $field_username['validate']['remote'] = './?controller=user&task=check_username';
		    $field_username['message']['remote'] = '用户名已被占用！';
		    
		    $field_email['validate']['remote'] = './?controller=user&task=check_email';
		    $field_email['message']['remote'] = '邮箱已被占用！';

		    $filed_password['validate']['required'] = true;
		    
		    $filed_confirm_password['validate']['required'] = true;
		} else {
		    $filed_password['label'] = '<input type="checkbox" id="change_password" onclick="javascript:changePassword(this.checked);"> 重设密码';
		}

		$config_user = be::get_config('user');
		$html_avatar = '<img src="../'.DATA.'/user/avatar/'.($user->avatar_m == ''?('default/'.$config_user->default_avatar_m):$user->avatar_m).'" />';
		if ($user->id>0 && $user->avatar_m !='') $html_avatar .= ' <a href="javascript:;" onclick="javascript:deleteAvatar(this, '.$user->id.');" style="font-size:16px;">&times;</a>';
		$html_avatar .= '<br /><input type="file" name="avatar" />';		

		$ui_editor->set_fields(
			array(
				'type'=>'file',
			    'name'=>'avatar',
			    'label'=>'头像',
			    'html'=>$html_avatar
			),
			$field_username,
            $field_email,
            array(
				'type'=>'text',
			    'name'=>'name',
			    'label'=>'名称',
				'value'=>$user->name,
				'width'=>'120px',
            	'validate'=>array(
					'max_length'=>60
				)
			),
			$filed_password,
			$filed_confirm_password,
			array(
				'type'=>'text',
			    'name'=>'phone',
			    'label'=>'电话',
				'value'=>$user->phone,
				'width'=>'240px',
            	'validate'=>array(
					'max_length'=>20
				)
			),
			array(
				'type'=>'text',
			    'name'=>'phone',
			    'label'=>'手机',
				'value'=>$user->mobile,
				'width'=>'240px',
            	'validate'=>array(
					'max_length'=>20
				)
			),
			array(
				'type'=>'text',
			    'name'=>'qq',
			    'label'=>'QQ号码',
				'value'=>$user->qq,
				'width'=>'120px',
            	'validate'=>array(
					'max_length'=>12
				)
			),
			array(
				'type'=>'select',
			    'name'=>'group_id',
			    'label'=>'前台用户组',
			    'value'=>$user->group_id,
				'options'=>$group_options
			),
			array(
				'type'=>'select',
			    'name'=>'admin_group_id',
			    'label'=>'后台用户组',
			    'value'=>$user->admin_group_id,
				'options'=>$admin_group_options
			),
			array(
				'type'=>'checkbox',
			    'name'=>'block',
			    'label'=>'屏蔽该用户',
			    'value'=>$user->block,
				'options'=>array('1'=>'')
			)			
		);
		
		$ui_editor->add_hidden('id', $user->id);
		$ui_editor->display();

	}	

}
?>