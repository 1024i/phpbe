<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->set_left_width(300);
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config_user = $this->get('config_user');

$ui_editor = be::get_ui('editor');
$ui_editor->set_action('save', './?controller=user&task=setting_save');

$html_default_avatar_l = '<img src="../'.DATA.'/user/avatar/default/'.$config_user->default_avatar_l.'" />';
$html_default_avatar_l .= '<br /><input type="file" name="default_avatar_l" />';

$html_default_avatar_m = '<img src="../'.DATA.'/user/avatar/default/'.$config_user->default_avatar_m.'" />';
$html_default_avatar_m .= '<br /><input type="file" name="default_avatar_m" />';

$html_default_avatar_s = '<img src="../'.DATA.'/user/avatar/default/'.$config_user->default_avatar_s.'" />';
$html_default_avatar_s .= '<br /><input type="file" name="default_avatar_s" />';

$ui_editor->set_fields(
    array(
        'type'=>'radio',
        'name'=>'register',
        'label'=>'允许新用户注册',
        'value'=>$config_user->register,
        'options'=>array('1'=>'是', '0'=>'否')
   ),
    array(
        'type'=>'radio',
        'name'=>'captcha_login',
        'label'=>'登陆页面验证码',
        'value'=>$config_user->captcha_login,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'captcha_register',
        'label'=>'注册页面验证码',
        'value'=>$config_user->captcha_register,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'email_valid',
        'label'=>'新用户邮箱激活',
        'value'=>$config_user->email_valid,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'radio',
        'name'=>'email_register',
        'label'=>'向新用户发送邮件',
        'value'=>$config_user->email_register,
        'options'=>array('1'=>'是', '0'=>'否')
   ),
    array(
        'type'=>'text',
        'name'=>'email_register_admin',
        'label'=>'向此邮箱提示新用户',
        'value'=>$config_user->email_register_admin,
        'validate'=>array(
            'email'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_l_w',
        'label'=>'头像大图宽度<small>(px)</small>',
        'value'=>$config_user->avatar_l_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_l_h',
        'label'=>'头像大图高度<small>(px)</small>',
        'value'=>$config_user->avatar_l_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_m_w',
        'label'=>'头像中图宽度'.'<small>(px)</small>',
        'value'=>$config_user->avatar_m_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_m_h',
        'label'=>'头像中图高度'.'<small>(px)</small>',
        'value'=>$config_user->avatar_m_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_s_w',
        'label'=>'头像小图宽度'.'<small>(px)</small>',
        'value'=>$config_user->avatar_s_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'avatar_s_h',
        'label'=>'头像小图高度'.'<small>(px)</small>',
        'value'=>$config_user->avatar_s_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像大图',
        'html'=>$html_default_avatar_l
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像中图',
        'html'=>$html_default_avatar_m
   ),
    array(
        'type'=>'file',
        'label'=>'默认头像小图',
        'html'=>$html_default_avatar_s
   ),
    array(
        'type'=>'radio',
        'name'=>'connect_qq',
        'label'=>'使用QQ登陆',
        'value'=>$config_user->connect_qq,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'text',
        'name'=>'connect_qq_app_id',
        'label'=>'QQ APP ID',
        'value'=>$config_user->connect_qq_app_id
   ),
    array(
        'type'=>'text',
        'name'=>'connect_qq_app_key',
        'label'=>'QQ APP KEY',
        'width'=>'400px',
        'value'=>$config_user->connect_qq_app_key
   ),
    array(
        'type'=>'radio',
        'name'=>'connect_sina',
        'label'=>'使用新浪微博登陆',
        'value'=>$config_user->connect_sina,
        'options'=>array('1'=>'启用', '0'=>'停用')
   ),
    array(
        'type'=>'text',
        'name'=>'connect_sina_app_key',
        'label'=>'新浪微博 APP KEY',
        'value'=>$config_user->connect_sina_app_key
   ),
    array(
        'type'=>'text',
        'name'=>'connect_sina_app_secret',
        'label'=>'新浪微博 APP Secret',
        'width'=>'400px',
        'value'=>$config_user->connect_sina_app_secret
   )
);
$ui_editor->display();
?>
<!--{/center}-->