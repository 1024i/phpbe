<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->setLeftWidth(200);
$uiEditor->head();
?>
<link type="text/css" rel="stylesheet" href="template/system/css/configWatermark.css" />
<script type="text/javascript" language="javascript" src="template/system/js/configWatermark.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$config = $this->get('config');

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?app=System&controller=System&task=configWatermarkSave');
$uiEditor->setAction('back', './?app=System&controller=System&task=configWatermarkTest', '预览');


$htmlPosition = '';
$htmlPosition .= '<input type="hidden" name="position" id="selected-position" value="'.$config->position.'" />';
$htmlPosition .= '<table class="position-table">';
$htmlPosition .= '<tr>';
$htmlPosition .= '<td><div data-position="northwest" class="position position-northwest'.($config->position == 'northwest'?' on':'').'">左上</div></td>';
$htmlPosition .= '<td><div data-position="north" class="position position-north'.($config->position == 'north'?' on':'').'">上</div></td>';
$htmlPosition .= '<td><div data-position="northeast" class="position position-northeast'.($config->position == 'northeast'?' on':'').'">右上</div></td>';
$htmlPosition .= '</tr>';
$htmlPosition .= '<tr>';
$htmlPosition .= '<td><div data-position="west" class="position position-west'.($config->position == 'west'?' on':'').'">左</div></td>';
$htmlPosition .= '<td><div data-position="center" class="position position-center'.($config->position == 'center'?' on':'').'">中</div></td>';
$htmlPosition .= '<td><div data-position="east" class="position position-east'.($config->position == 'east'?' on':'').'">右</div></td>';
$htmlPosition .= '</tr>';
$htmlPosition .= '<tr>';
$htmlPosition .= '<td><div data-position="southwest" class="position position-southwest'.($config->position == 'southwest'?' on':'').'">左下</div></td>';
$htmlPosition .= '<td><div data-position="south" class="position position-south'.($config->position == 'south'?' on':'').'">下</div></td>';
$htmlPosition .= '<td><div data-position="southeast" class="position position-southeast'.($config->position == 'southeast'?' on':'').'">右下</div></td>';
$htmlPosition .= '</tr>';
$htmlPosition .= '</table>';


$htmlImage = '<img src="../'.DATA.'/system/watermark/'.$config->image.'" />';
$htmlImage .= '<br /><input type="file" name="image" />';

$uiEditor->setFields(
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
        'html'=>$htmlPosition
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
        'name'=>'textSize',
        'label'=>'水印文字大小',
        'value'=>$config->textSize,
        'width'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'textColor',
        'label'=>'水印文字颜色'.' <small>(RGB)</small>',
        'value'=>implode(', ', $config->textColor),
        'width'=>'160px'
   ),
    array(
        'type'=>'file',
        'label'=>'水印图片',
        'html'=>$htmlImage
   ),
    array(
        'type'=>'text',
        'name'=>'offsetX',
        'label'=>'水平偏移'.' <small>(px)</small>',
        'value'=>$config->offsetX,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'number'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'offsetY',
        'label'=>'垂直偏移'.' <small>(px)</small>',
        'value'=>$config->offsetY,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'number'=>true
       )
   )
);

$uiEditor->display();

?>
<p class="muted">* 如何计算偏移：右侧可设置负值水平偏移，下部可设置负值垂直偏移。</p>
<p class="muted">* 如水印图片尺寸为 100px &times; 100px; 右下位置，可设置水平偏移: -100，垂直偏移：-100，为了美观，再加些边距，可设为：-120，-120。</p>
<!--{/center}-->