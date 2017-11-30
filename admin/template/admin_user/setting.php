<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_admin_ui('editor');
$ui_editor->set_left_width(300);
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config_admin_user = $this->config_admin_user;

$ui_editor = be::get_admin_ui('editor');
$ui_editor->set_action('save', './?controller=admin_user&task=setting_save');

$html_default_avatar_l = '<img src="../'.DATA.'/admin_user/avatar/default/'.$config_admin_user->default_avatar_l.'" />';
$html_default_avatar_l .= '<br /><input type="file" name="default_avatar_l" />';

$html_default_avatar_m = '<img src="../'.DATA.'/admin_user/avatar/default/'.$config_admin_user->default_avatar_m.'" />';
$html_default_avatar_m .= '<br /><input type="file" name="default_avatar_m" />';

$html_default_avatar_s = '<img src="../'.DATA.'/admin_user/avatar/default/'.$config_admin_user->default_avatar_s.'" />';
$html_default_avatar_s .= '<br /><input type="file" name="default_avatar_s" />';

$ui_editor->set_fields(

    array(
        'type'=>'text',
        'name'=>'avatar_l_w',
        'label'=>'头像大图宽度<small>(px)</small>',
        'value'=>$config_admin_user->avatar_l_w,
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
        'value'=>$config_admin_user->avatar_l_h,
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
        'value'=>$config_admin_user->avatar_m_w,
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
        'value'=>$config_admin_user->avatar_m_h,
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
        'value'=>$config_admin_user->avatar_s_w,
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
        'value'=>$config_admin_user->avatar_s_h,
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
    )
);
$ui_editor->display();
?>
<!--{/center}-->

