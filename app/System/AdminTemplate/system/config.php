<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->setLeftWidth(200);
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config = $this->get('config');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?app=System&controller=System&action=configSave');

$uiEditor->setFields(
    array(
        'type'=>'radio',
        'name'=>'offline',
        'label'=>'启用/关闭网站',
        'value'=>$config->offline,
        'options'=>array('0'=>'启用', '1'=>'关闭')
   ),
    array(
        'type'=>'richtext',
        'name'=>'offlineMessage',
        'label'=>'关闭网站时提示信息',
        'value'=>$config->offlineMessage,
        'width'=>'500px',
        'height'=>'45px'
   ),
    array(
        'type'=>'text',
        'name'=>'siteName',
        'label'=>'关闭网站时提示信息',
        'width'=>'400px',
        'value'=>$config->siteName
   ),
    array(
        'type'=>'radio',
        'name'=>'sef',
        'label'=>'伪静态页',
        'value'=>$config->sef,
        'options'=>array('1'=>'启用', '0'=>'关闭')
   ),
    array(
        'type'=>'text',
        'name'=>'sefSuffix',
        'label'=>'伪静态页后缀',
        'width'=>'90px',
        'value'=>$config->sefSuffix
   ),
    array(
        'type'=>'text',
        'name'=>'homeTitle',
        'label'=>'首页标题',
        'width'=>'400px',
        'value'=>$config->homeTitle
   ),
    array(
        'type'=>'text',
        'name'=>'homeMetaKeywords',
        'label'=>'首页 META 关键词',
        'width'=>'500px',
        'value'=>$config->homeMetaKeywords
   ),
    array(
        'type'=>'text',
        'name'=>'homeMetaDescription',
        'label'=>'首页 META 描述',
        'width'=>'500px',
        'value'=>$config->homeMetaDescription
   ),
    array(
        'type'=>'text',
        'name'=>'allowUploadFileTypes',
        'label'=>'允许上传的文件类型',
        'width'=>'400px',
        'value'=>implode(', ', $config->allowUploadFileTypes)
   ),
    array(
        'type'=>'text',
        'name'=>'allowUploadImageTypes',
        'label'=>'允许上传的图像类型',
        'width'=>'400px',
        'value'=>implode(', ', $config->allowUploadImageTypes)
   )
);
$uiEditor->display();
?>
<!--{/center}-->