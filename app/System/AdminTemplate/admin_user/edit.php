<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();

$adminUser = $this->adminUser;
echo '<script type="text/javascript" language="javascript" src="template/adminUser/js/edit.js"></script>';
if (($adminUser->id>0)) {
    echo '<script type="text/javascript" language="javascript">$(function(){hidePassword();});</script>';
}
?>
<!--{/head}-->

<!--{center}-->
<?php
$adminUser = $this->adminUser;

$roles = $this->roles;
$roleMap = [];
foreach ($roles as $role) {
    $roleMap[$role->id] = $role->name;
}

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', './?app=System&controller=AdminUser&task=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮
$fieldUsername = array(
        'type'=>'text',
        'name'=>'username',
        'label'=>'用户名',
        'value'=>$adminUser->username,
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
        'value'=>$adminUser->email,
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

if (($adminUser->id == 0)) {
    $fieldUsername['validate']['remote'] = './?app=System&controller=AdminUser&task=checkUsername';
    $fieldUsername['message']['remote'] = '用户名已被占用！';

    $fieldEmail['validate']['remote'] = './?app=System&controller=AdminUser&task=checkEmail';
    $fieldEmail['message']['remote'] = '邮箱已被占用！';

    $filedPassword['validate']['required'] = true;

    $filedConfirmPassword['validate']['required'] = true;
} else {
    $filedPassword['label'] = '<input type="checkbox" id="changePassword" onclick="javascript:changePassword(this.checked);"> 重设密码';
}

$configAdminUser = Be::getConfig('System.AdminUser');
$htmlAvatar = '<img src="../'.DATA.'/adminUser/avatar/'.($adminUser->avatarM == ''?('default/'.$configAdminUser->defaultAvatarM):$adminUser->avatarM).'" />';
if ($adminUser->id>0 && $adminUser->avatarM !='') $htmlAvatar .= ' <a href="javascript:;" onclick="javascript:deleteAvatar(this, '.$adminUser->id.');" style="font-size:16px;">&times;</a>';
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
        'value'=>$adminUser->name,
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
        'value'=>$adminUser->phone,
        'width'=>'240px',
        'validate'=>array(
            'maxLength'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'phone',
        'label'=>'手机',
        'value'=>$adminUser->mobile,
        'width'=>'240px',
        'validate'=>array(
            'maxLength'=>20
        )
    ),
    array(
        'type'=>'text',
        'name'=>'qq',
        'label'=>'QQ号码',
        'value'=>$adminUser->qq,
        'width'=>'120px',
        'validate'=>array(
            'maxLength'=>12
        )
    ),
    array(
        'type'=>'select',
        'name'=>'roleId',
        'label'=>'角色',
        'value'=>$adminUser->roleId,
        'options'=>$roleMap
    ),
    array(
        'type'=>'checkbox',
        'name'=>'block',
        'label'=>'屏蔽该用户',
        'value'=>$adminUser->block,
        'options'=>array('1'=>'')
    )
);

$uiEditor->addHidden('id', $adminUser->id);
$uiEditor->display();
?>
<!--{/center}-->