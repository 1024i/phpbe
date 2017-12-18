<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->set_left_width(280);
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$config_article = $this->get('config_article');

$ui_editor = be::get_ui('editor');
$ui_editor->set_action('save', './?controller=article&task=setting_save');

$html_default_thumbnail_l = '<img src="../'.DATA.'/article/thumbnail/default/'.$config_article->default_thumbnail_l.'" />';
$html_default_thumbnail_l .= '<br /><input type="file" name="default_thumbnail_l" />';

$html_default_thumbnail_m = '<img src="../'.DATA.'/article/thumbnail/default/'.$config_article->default_thumbnail_m.'" />';
$html_default_thumbnail_m .= '<br /><input type="file" name="default_thumbnail_m" />';

$html_default_thumbnail_s = '<img src="../'.DATA.'/article/thumbnail/default/'.$config_article->default_thumbnail_s.'" />';
$html_default_thumbnail_s .= '<br /><input type="file" name="default_thumbnail_s" />';

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'get_summary',
        'label'=>'提取摘要字数',
        'value'=>$config_article->get_summary,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'get_meta_keywords',
        'label'=>'提取META关键词个数',
        'value'=>$config_article->get_meta_keywords,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'get_meta_description',
        'label'=>'提取 META 描述字数',
        'value'=>$config_article->get_meta_description,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'comment',
        'label'=>'允许评论',
        'value'=>$config_article->comment,
        'options'=>array('1'=>'允许', '0'=>'禁止')
   ),
    array(
        'type'=>'radio',
        'name'=>'comment_public',
        'label'=>'评论默认公开',
        'value'=>$config_article->comment_public,
        'options'=>array('1'=>'公开', '0'=>'不公开，需要审核')
   ),
    array(
        'type'=>'radio',
        'name'=>'download_remote_image',
        'label'=>'下载远程图片',
        'value'=>$config_article->download_remote_image,
        'options'=>array('1'=>'默认选中', '0'=>'默认不选中')
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_l_w',
        'label'=>'缩图图大图宽度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_l_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_l_h',
        'label'=>'缩图图大图高度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_l_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_m_w',
        'label'=>'缩图图中图宽度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_m_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_m_h',
        'label'=>'缩图图中图高度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_m_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_s_w',
        'label'=>'缩图图小图宽度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_s_w,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnail_s_h',
        'label'=>'缩图图小图高度'.' <small>(px)</small>',
        'value'=>$config_article->thumbnail_s_h,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图大图',
        'html'=>$html_default_thumbnail_l
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图中图',
        'html'=>$html_default_thumbnail_m
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图小图',
        'html'=>$html_default_thumbnail_s
   )
);
$ui_editor->display();
?>
<!--{/center}-->