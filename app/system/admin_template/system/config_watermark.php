<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->set_left_width(200);
$ui_editor->head();
?>
<link type="text/css" rel="stylesheet" href="template/system/css/config_watermark.css" />
<script type="text/javascript" language="javascript" src="template/system/js/config_watermark.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$config = $this->get('config');

$ui_editor = be::get_ui('editor');

$ui_editor->set_action('save', './?controller=system&task=config_watermark_save');
$ui_editor->set_action('back', './?controller=system&task=config_watermark_test', '预览');


$html_position = '';
$html_position .= '<input type="hidden" name="position" id="selected-position" value="'.$config->position.'" />';
$html_position .= '<table class="position-table">';
$html_position .= '<tr>';
$html_position .= '<td><div data-position="northwest" class="position position-northwest'.($config->position == 'northwest'?' on':'').'">左上</div></td>';
$html_position .= '<td><div data-position="north" class="position position-north'.($config->position == 'north'?' on':'').'">上</div></td>';
$html_position .= '<td><div data-position="northeast" class="position position-northeast'.($config->position == 'northeast'?' on':'').'">右上</div></td>';
$html_position .= '</tr>';
$html_position .= '<tr>';
$html_position .= '<td><div data-position="west" class="position position-west'.($config->position == 'west'?' on':'').'">左</div></td>';
$html_position .= '<td><div data-position="center" class="position position-center'.($config->position == 'center'?' on':'').'">中</div></td>';
$html_position .= '<td><div data-position="east" class="position position-east'.($config->position == 'east'?' on':'').'">右</div></td>';
$html_position .= '</tr>';
$html_position .= '<tr>';
$html_position .= '<td><div data-position="southwest" class="position position-southwest'.($config->position == 'southwest'?' on':'').'">左下</div></td>';
$html_position .= '<td><div data-position="south" class="position position-south'.($config->position == 'south'?' on':'').'">下</div></td>';
$html_position .= '<td><div data-position="southeast" class="position position-southeast'.($config->position == 'southeast'?' on':'').'">右下</div></td>';
$html_position .= '</tr>';
$html_position .= '</table>';


$html_image = '<img src="../'.DATA.'/system/watermark/'.$config->image.'" />';
$html_image .= '<br /><input type="file" name="image" />';

$ui_editor->set_fields(
    array(
        'type'=>'radio',
        'name'=>'watermark',
        'label'=>'默认添加水印',
        'value'=>$config->watermark,
        'options'=>array('1'=>'启用', '0'=>'关闭')
   ),
    array(
        'type'=>'radio',
        'name'=>'type',
        'label'=>'水印类型',
        'value'=>$config->type,
        'options'=>array('text'=>'文字', 'image'=>'图片')
   ),
    array(
        'label'=>'添加到图片中的位置',
        'html'=>$html_position
   ),
    array(
        'type'=>'text',
        'name'=>'text',
        'label'=>'水印文字',
        'value'=>$config->text,
        'width'=>'300px'
   ),
    array(
        'type'=>'text',
        'name'=>'text_size',
        'label'=>'水印文字大小',
        'value'=>$config->text_size,
        'width'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'text_color',
        'label'=>'水印文字颜色'.' <small>(RGB)</small>',
        'value'=>implode(', ', $config->text_color),
        'width'=>'160px'
   ),
    array(
        'type'=>'file',
        'label'=>'水印图片',
        'html'=>$html_image
   ),
    array(
        'type'=>'text',
        'name'=>'offset_x',
        'label'=>'水平偏移'.' <small>(px)</small>',
        'value'=>$config->offset_x,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'number'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'offset_y',
        'label'=>'垂直偏移'.' <small>(px)</small>',
        'value'=>$config->offset_y,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'number'=>true
       )
   )
);

$ui_editor->display();

?>
<p class="muted">* 如何计算偏移：右侧可设置负值水平偏移，下部可设置负值垂直偏移。</p>
<p class="muted">* 如水印图片尺寸为 100px &times; 100px; 右下位置，可设置水平偏移: -100，垂直偏移：-100，为了美观，再加些边距，可设为：-120，-120。</p>
<!--{/center}-->