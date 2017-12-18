<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->head();
?>
<script type="text/javascript" language="javascript" src="template/article/js/edit.js"></script>
<!--{/head}-->

<!--{center}-->
<?php

$article = $this->get('article');
$categories = $this->get('categories');

$config_article = be::get_config('article');
$config_watermark = be::get_config('watermark');

$category_html = '<select name="category_id">';
$category_html .= '<option value="">不属于任何分类</option>';
foreach ($categories as $category) {
    $category_html .= '<option value="' . $category->id . '"';
    if ($category->children > 0) $category_html .= ' disabled="disabled"';
    if ($category->id == $article->category_id)  $category_html .= ' selected="selected"';
    $category_html .= '>';
    if ($category->level) $category_html .= str_repeat('&nbsp; ', $category->level);
    $category_html .= $category->name . '</option>';
}
$category_html .= '</select>';

$ui_editor = be::get_ui('editor');

$ui_editor->set_action('save', './?controller=article&task=edit_save');	// 显示提交按钮
$ui_editor->set_action('reset');	// 显示重设按钮
$ui_editor->set_action('back');	// 显示返回按钮

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'title',
        'label'=>'标题',
        'value'=>$article->title,
        'width'=>'500px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'select',
        'label'=>'所属分类',
        'html'=>$category_html
   ),
    array(
        'type'=>'file',
        'label'=>'缩略图',
        'html'=>'<img src="../'.DATA.'/article/thumbnail/'.($article->thumbnail_s == ''?('default/'.$config_article->default_thumbnail_s):$article->thumbnail_s).'" /> <label class="radio inline"><input type="radio" name="thumbnail_source" id="thumbnail_source_upload" value="upload" checked="checked" onchange="javascript:checkThunbmail();" />上传：</label><input type="file" name="thumbnail_upload" /><label class="radio inline" style="margin-left:20px;"><input type="radio" name="thumbnail_source" id="thumbnail_source_url" value="url" onchange="javascript:checkThunbmail();" />指定网址：</label><div class="input-append"><input type="text" id="src_thunbmail" name="thumbnail_url" /><button class="btn btn-success" type="button" onclick="javascript:selectImage(\'src_thunbmail\');"><i class="icon-share icon-white"></i></button></div>'
   ),
    array(
        'label'=>'附加选项',
        'html'=>'<label class="checkbox inline"><input type="checkbox" name="thumbnail_pick_up" id="thumbnail_pick_up" value="1" onchange="javascript:checkThumbnailPickUp();" />提取第一个图片为缩略图</label><label class="checkbox inline" style="margin-left:20px;"><input type="checkbox" name="download_remote_image" value="1"'.($config_article->download_remote_image == '1'?' checked="checked"':'').' />下载远程图片</label><label class="checkbox inline" style="margin-left:20px;"><input type="checkbox" name="download_remote_image_watermark" value="1"'.($config_watermark->watermark == '0'?'':' checked="checked"').' />下截远程图片添加水印</label>'
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>$article->body,
        'width'=>'600px',
        'height'=>'360px'
   )
);

$ui_editor->add_fields(
    array(
        'label'=>'从内容中提取',
        'html'=>'<input type="button" value="摘要" class="btn btn-success" onclick="javascript:getSummary(this);" /> <input type="button" value="Meta 关键字" class="btn btn-warning" onclick="javascript:getMetaKeywords(this);" /> <input type="button" value="Meta 描述" class="btn btn-info" onclick="javascript:getMetaDescription(this);" />'
    )
);


$ui_editor->add_fields(
    array(
        'type'=>'textarea',
        'name'=>'summary',
        'label'=>'摘要',
        'value'=>$article->summary,
        'width'=>'95%',
        'height'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'meta_keywords',
        'label'=>'<small>Meta 关键字</small>',
        'value'=>$article->meta_keywords,
        'width'=>'95%'
   ),
    array(
        'type'=>'textarea',
        'name'=>'meta_description',
        'label'=>'<small>Meta 描述<</small>',
        'value'=>$article->meta_description,
        'width'=>'95%',
        'height'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'hits',
        'label'=>'点击量',
        'value'=>$article->hits,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'create_time',
        'label'=>'发布时间',
        'value'=>$article->id == 0?date('Y-m-d H:i:s'):date('Y-m-d H:i:s', $article->create_time)
   ),
    array(
        'type'=>'text',
        'name'=>'rank',
        'label'=>'权重',
        'value'=>$article->rank,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'top',
        'label'=>'推荐',
        'value'=>$article->top,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$article->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);
$ui_editor->add_hidden('id', $article->id);
$ui_editor->display();
?>
<!--{/center}-->