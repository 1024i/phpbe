<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_list = be::get_ui('grid');
$ui_list->head();
?>
<link type="text/css" rel="stylesheet" href="bootstrap/2.3.2/css/bootstrap-lightbox.css" />
<script type="text/javascript" language="javascript" src="bootstrap/2.3.2/js/bootstrap-lightbox.js"></script>

<link type="text/css" rel="stylesheet" href="template/system/css/themes.css">
<script type="text/javascript" language="javascript" src="template/system/js/themes.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$themes = $this->get('themes');

$ui_list = be::get_ui('grid');

$ui_list->set_action('listing', './?controller=system&task=themes');
$ui_list->set_action('create', './?controller=system&task=remote_themes', '安装新主题');


$config_system = be::get_config('system.system');
foreach ($themes as $key=>$theme) {
    $theme->key = $key;

    $theme->is_default = $config_system->theme == $key?1:0;

    if ($theme->author_website) {
        $theme->author_website = '<a href="'.$theme->author_website.'" target="_blank" class="muted">'.$theme->author_website.'</a>';
    } else {
        $theme->author_website = '';
    }

    $theme->delete_html = '<a class="icon delete"'.($theme->is_default?' style="display:none;"':'').' href="javascript:;" onclick="javascript:deleteTheme(this, \''.$key.'\');"></a>';
}

$ui_list->set_data($themes);

$ui_list->set_footer('共安装了 <strong>'.count($themes).'</strong> 个主题');


$thumbnail_template = '';
$thumbnail_template .= '<a href="javascript:" onclick="javascript:jQuery(\'#theme_thumbnail_{id}\').lightbox();" data-title="" data-content="<div style=\'width:400px;height:400px;line-height:400px;text-align:center;\'><img src=\''.URL_ROOT.'/themes/{key}/{thumbnail_m}\' style=\'max-width:400px;\' /></div>" data-toggle="popover" data-html="true" data-trigger="hover">';
$thumbnail_template .= '	<img src="'.URL_ROOT.'/themes/{key}/{thumbnail_s}" style="max-width:120px;" border="0" />';
$thumbnail_template .= '</a>';
$thumbnail_template .= '<div class="lightbox fade hide" id="theme_thumbnail_{id}">';
$thumbnail_template .= '	<div class="lightbox-content">';
$thumbnail_template .= '		<img src="'.URL_ROOT.'/themes/{key}/{thumbnail_l}" />';
$thumbnail_template .= '		<div class="lightbox-caption"><p>{name}</p></div>';
$thumbnail_template .= '	</div>';
$thumbnail_template .= '</div>';



$label_templagte = '<strong>{name}</strong>';
$label_templagte .= '<div class="muted">{description}</div>';

$author_template = '<strong>{author}</strong><br />';
$author_template .= '{author_email}<br />';
$author_template .= '{author_website}';


$ui_list->set_fields(
    array(
        'name'=>'thumbnail',
        'label'=>'缩略图',
        'align'=>'center',
        'template'=>$thumbnail_template,
        'width'=>'130'
    ),
    array(
        'name'=>'label',
        'label'=>'名称/详细描述',
        'align'=>'left',
        'template'=>$label_templagte
    ),
    array(
        'name'=>'author',
        'label'=>'作者',
        'align'=>'left',
        'template'=>$author_template,
        'width'=>'200'
    ),
    array(
        'label'=>'设为默认主题',
        'align'=>'center',
        'width'=>'120',
        'template'=>'<a href="javascript:;" onclick="javascript:setDefault(\'{key}\')" class="default default-{is_default}" id="default-{key}"></a>'
    ),
    array(
        'name'=>'delete_html',
        'label'=>'',
        'align'=>'center',
        'width'=>'120'
    )
);
$ui_list->display();
?>
<!--{/center}-->