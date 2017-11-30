<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_admin_ui('editor');
$ui_editor->head();

$admin_user = $this->admin_user;
echo '<script type="text/javascript" language="javascript" src="template/admin_user/js/edit.js"></script>';
if (($admin_user->id>0)) {
    echo '<script type="text/javascript" language="javascript">$(function(){hidePassword();});</script>';
}
?>
<!--{/head}-->

<!--{center}-->
<?php
$admin_user = $this->admin_user;

$roles = $this->roles;
$role_map = [];
foreach ($roles as $role) {
    $role_map[$role->id] = $role->name;
}

$ui_editor = be::get_admin_ui('editor');
$ui_editor->set_action('save', './?controller=admin_user&task=edit_save');	// 显示提交按钮
$ui_editor->set_action('reset');// 显示重设按钮
$ui_editor->set_action('back');	// 显示返回按钮
$field_username = array(
        'type'=>'text',
        'name'=>'username',
        'label'=>'用户名',
        'value'=>$admin_user->username,
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
        'value'=>$admin_user->email,
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

if (($admin_user->id == 0)) {
    $field_username['validate']['remote'] = './?controller=admin_user&task=check_username';
    $field_username['message']['remote'] = '用户名已被占用！';

    $field_email['validate']['remote'] = './?controller=admin_user&task=check_email';
    $field_email['message']['remote'] = '邮箱已被占用！';

    $filed_password['validate']['required'] = true;

    $filed_confirm_password['validate']['required'] = true;
} else {
    $filed_password['label'] = '<input type="checkbox" id="change_password" onclick="javascript:changePassword(this.checked);"> 重设密码';
}

$config_admin_user = be::get_admin_config('admin_user');
$html_avatar = '<img src="../'.DATA.'/admin_user/avatar/'.($admin_user->avatar_m == ''?('default/'.$config_admin_user->default_avatar_m):$admin_user->avatar_m).'" />';
if ($admin_user->id>0 && $admin_user->avatar_m !='') $html_avatar .= ' <a href="javascript:;" onclick="javascript:deleteAvatar(this, '.$admin_user->id.');" style="font-size:16px;">&times;</a>';
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
        'value'=>$admin_user->name,
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
        'value'=>$admin_user->phone,
        'width'=>'240px',
        'validate'=>array(
            'max_length'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'phone',
        'label'=>'手机',
        'value'=>$admin_user->mobile,
        'width'=>'240px',
        'validate'=>array(
            'max_length'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'qq',
        'label'=>'QQ号码',
        'value'=>$admin_user->qq,
        'width'=>'120px',
        'validate'=>array(
            'max_length'=>12
        )
    ),
    array(
        'type'=>'select',
        'name'=>'role_id',
        'label'=>'角色',
        'value'=>$admin_user->role_id,
        'options'=>$role_map
    ),
    array(
        'type'=>'checkbox',
        'name'=>'block',
        'label'=>'屏蔽该用户',
        'value'=>$admin_user->block,
        'options'=>array('1'=>'')
    )
);

$ui_editor->add_hidden('id', $admin_user->id);
$ui_editor->display();
?>
<!--{/center}-->