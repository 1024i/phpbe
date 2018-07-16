<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();

$user = $this->get('user');
echo '<script type="text/javascript" language="javascript" src="template/user/js/edit.js"></script>';
if (($user->id>0)) {
    echo '<script type="text/javascript" language="javascript">$(function(){hidePassword();});</script>';
}
?>
<!--{/head}-->

<!--{center}-->
<?php
$user = $this->user;
$roles = $this->roles;

$defaultRoleId = 0;

$roleMap = [];
foreach ($roles as $role) {
    if ($role->id == 1) continue;
    $roleMap[$role->id] = $role->name;

    if ($role->default) {
        $defaultRoleId = $role->id;
    }
}

// 新建用户时选中默认用户组
if ($user->id == 0) $user->roleId = $defaultRoleId;

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', './?controller=user&action=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮
$fieldUsername = array(
        'type'=>'text',
        'name'=>'username',
        'label'=>'用户名',
        'value'=>$user->username,
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'minLength'=>3,
            'maxLength'=>60
        ),
    );

$fieldEmail = array(
        'type'=>'text',
        'name'=>'email',
        'label'=>'邮箱',
        'value'=>$user->email,
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'email'=>true,
            'maxLength'=>60
        )
    );

$filedPassword = array(
        'type'=>'password',
        'name'=>'password',
        'label'=>'密码',
        'width'=>'180px',
        'validate'=>array(
            'minLength'=>5
        )
    );

$filedConfirmPassword = array(
        'type'=>'password',
        'name'=>'password2',
        'label'=>'确认密码',
        'width'=>'180px',
        'validate'=>array(
            'equalTo'=>'password'
        ),
        'message'=>array(
            'equalTo'=>'两次输入的密码不匹配！'
        )
    );

if (($user->id == 0)) {
    $fieldUsername['validate']['remote'] = './?controller=user&action=checkUsername';
    $fieldUsername['message']['remote'] = '用户名已被占用！';

    $fieldEmail['validate']['remote'] = './?controller=user&action=checkEmail';
    $fieldEmail['message']['remote'] = '邮箱已被占用！';

    $filedPassword['validate']['required'] = true;

    $filedConfirmPassword['validate']['required'] = true;
} else {
    $filedPassword['label'] = '<input type="checkbox" id="changePassword" onclick="javascript:changePassword(this.checked);"> 重设密码';
}

$configUser = Be::getConfig('System.User');
$htmlAvatar = '<img src="../'.DATA.'/user/avatar/'.($user->avatarM == ''?('default/'.$configUser->defaultAvatarM):$user->avatarM).'" />';
if ($user->id>0 && $user->avatarM !='') $htmlAvatar .= ' <a href="javascript:;" onclick="javascript:deleteAvatar(this, '.$user->id.');" style="font-size:16px;">&times;</a>';
$htmlAvatar .= '<br /><input type="file" name="avatar" />';

$uiEditor->setFields(
    array(
        'type'=>'file',
        'name'=>'avatar',
        'label'=>'头像',
        'html'=>$htmlAvatar
    ),
    $fieldUsername,
    $fieldEmail,
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'名称',
        'value'=>$user->name,
        'width'=>'120px',
        'validate'=>array(
            'maxLength'=>60
        )
    ),
    $filedPassword,
    $filedConfirmPassword,
    array(
        'type'=>'text',
        'name'=>'phone',
        'label'=>'电话',
        'value'=>$user->phone,
        'width'=>'240px',
        'validate'=>array(
            'maxLength'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'phone',
        'label'=>'手机',
        'value'=>$user->mobile,
        'width'=>'240px',
        'validate'=>array(
            'maxLength'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'qq',
        'label'=>'QQ号码',
        'value'=>$user->qq,
        'width'=>'120px',
        'validate'=>array(
            'maxLength'=>12
        )
    ),
    array(
        'type'=>'select',
        'name'=>'roleId',
        'label'=>'角色',
        'value'=>$user->groupId,
        'options'=>$roleMap
    ),
    array(
        'type'=>'checkbox',
        'name'=>'block',
        'label'=>'屏蔽该用户',
        'value'=>$user->block,
        'options'=>array('1'=>'')
    )
);

$uiEditor->addHidden('id', $user->id);
$uiEditor->display();
?>
<!--{/center}-->