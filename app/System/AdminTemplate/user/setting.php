<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->setLeftWidth(300);
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$configUser = $this->get('configUser');

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', './?controller=user&task=settingSave');

$htmlDefaultAvatarL = '<img src="../'.DATA.'/user/avatar/default/'.$configUser->defaultAvatarL.'" />';
$htmlDefaultAvatarL .= '<br /><input type="file" name="defaultAvatarL" />';

$htmlDefaultAvatarM = '<img src="../'.DATA.'/user/avatar/default/'.$configUser->defaultAvatarM.'" />';
$htmlDefaultAvatarM .= '<br /><input type="file" name="defaultAvatarM" />';

$htmlDefaultAvatarS = '<img src="../'.DATA.'/user/avatar/default/'.$configUser->defaultAvatarS.'" />';
$htmlDefaultAvatarS .= '<br /><input type="file" name="defaultAvatarS" />';

$uiEditor->setFields(
    array(
        'type'=>'radio',
        'name'=>'register',
        'label'=>'允许新用户注册',
        'value'=>$configUser->register,
        'options'=>array('1'=>'是', '0'=>'否')
   ),
    array(
        'type'=>'radio',
        'name'=>'captchaLogin',
        'label'=>'登陆页面验证码',
        'value'=>$configUser->captchaLogin,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'captchaRegister',
        'label'=>'注册页面验证码',
        'value'=>$configUser->captchaRegister,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'emailValid',
        'label'=>'新用户邮箱激活',
        'value'=>$configUser->emailValid,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'emailRegister',
        'label'=>'向新用户发送邮件',
        'value'=>$configUser->emailRegister,
        'options'=>array('1'=>'是', '0'=>'否')
   ),
    array(
        'type'=>'text',
        'name'=>'emailRegisterAdmin',
        'label'=>'向此邮箱提示新用户',
        'value'=>$configUser->emailRegisterAdmin,
        'validate'=>array(
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarLW',
        'label'=>'头像大图宽度<small>(px)</small>',
        'value'=>$configUser->avatarLW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarLH',
        'label'=>'头像大图高度<small>(px)</small>',
        'value'=>$configUser->avatarLH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarMW',
        'label'=>'头像中图宽度'.'<small>(px)</small>',
        'value'=>$configUser->avatarMW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarMH',
        'label'=>'头像中图高度'.'<small>(px)</small>',
        'value'=>$configUser->avatarMH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarSW',
        'label'=>'头像小图宽度'.'<small>(px)</small>',
        'value'=>$configUser->avatarSW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatarSH',
        'label'=>'头像小图高度'.'<small>(px)</small>',
        'value'=>$configUser->avatarSH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像大图',
        'html'=>$htmlDefaultAvatarL
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像中图',
        'html'=>$htmlDefaultAvatarM
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像小图',
        'html'=>$htmlDefaultAvatarS
   ),
    array(
        'type'=>'radio',
        'name'=>'connectQq',
        'label'=>'使用QQ登陆',
        'value'=>$configUser->connectQq,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'text',
        'name'=>'connectQqAppId',
        'label'=>'QQ APP ID',
        'value'=>$configUser->connectQqAppId
   ),
    array(
        'type'=>'text',
        'name'=>'connectQqAppKey',
        'label'=>'QQ APP KEY',
        'width'=>'400px',
        'value'=>$configUser->connectQqAppKey
   ),
    array(
        'type'=>'radio',
        'name'=>'connectSina',
        'label'=>'使用新浪微博登陆',
        'value'=>$configUser->connectSina,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'text',
        'name'=>'connectSinaAppKey',
        'label'=>'新浪微博 APP KEY',
        'value'=>$configUser->connectSinaAppKey
   ),
    array(
        'type'=>'text',
        'name'=>'connectSinaAppSecret',
        'label'=>'新浪微博 APP Secret',
        'width'=>'400px',
        'value'=>$configUser->connectSinaAppSecret
   )
);
$uiEditor->display();
?>
<!--{/center}-->