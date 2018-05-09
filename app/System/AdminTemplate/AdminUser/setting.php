<?php
use Phpbe\System\Be;
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
$configAdminUser = $this->configAdminUser;

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', './?app=System&controller=AdminUser&task=settingSave');

$htmlDefaultAvatarL = '<img src="../'.DATA.'/adminUser/avatar/default/'.$configAdminUser->defaultAvatarL.'" />';
$htmlDefaultAvatarL .= '<br /><input type="file" name="defaultAvatarL" />';

$htmlDefaultAvatarM = '<img src="../'.DATA.'/adminUser/avatar/default/'.$configAdminUser->defaultAvatarM.'" />';
$htmlDefaultAvatarM .= '<br /><input type="file" name="defaultAvatarM" />';

$htmlDefaultAvatarS = '<img src="../'.DATA.'/adminUser/avatar/default/'.$configAdminUser->defaultAvatarS.'" />';
$htmlDefaultAvatarS .= '<br /><input type="file" name="defaultAvatarS" />';

$uiEditor->setFields(

    array(
        'type'=>'text',
        'name'=>'avatarLW',
        'label'=>'头像大图宽度<small>(px)</small>',
        'value'=>$configAdminUser->avatarLW,
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
        'value'=>$configAdminUser->avatarLH,
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
        'value'=>$configAdminUser->avatarMW,
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
        'value'=>$configAdminUser->avatarMH,
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
        'value'=>$configAdminUser->avatarSW,
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
        'value'=>$configAdminUser->avatarSH,
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
    )
);
$uiEditor->display();
?>
<!--{/center}-->

