<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_list = be::get_admin_ui('grid');
$ui_list->head();
?>
<link type="text/css" rel="stylesheet" href="bootstrap/2.3.2/css/bootstrap-lightbox.css" />
<script type="text/javascript" language="javascript" src="bootstrap/2.3.2/js/bootstrap-lightbox.js"></script>

<script type="text/javascript" language="javascript" src="template/system/js/remote_themes.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$remote_themes = $this->get('remote_themes');
$local_themes = $this->get('local_themes');

if ($remote_themes->status!='0') {
    echo $remote_themes->description;
    return;
}

$themes = $remote_themes->themes;

$installed_theme_ids = array();
foreach ($local_themes as $local_theme) {
    $installed_theme_ids[] = $local_theme->id;
}

foreach ($themes as $theme) {

    $theme->create_time = date('Y-m-d',$theme->create_time);

    if ($theme->auther_website) {
        $theme->auther_website = '<a href="'.$theme->auther_website.'" target="_blank">'.$theme->auther_website.'</a>';
    } else {
        $theme->auther_website = '';
    }

    if (in_array($theme->id, $installed_theme_ids)) {
        $theme->install_button = '<a class="btn disabled" onclick="javascript:;"><i class="icon-ok"></i> 已安装</a>';

    } else {
        $theme->install_button = '<a class="btn btn-success" onclick="javascript:install(this, '.$theme->id.');"><i class="icon-white icon-download"></i> 安装</a>';
    }
}

$ui_list = be::get_admin_ui('grid');
$ui_list->set_action('listing', './?controller=system&task=remote_themes');

$ui_list->set_data($themes);

$thumbnail_template = '';
$thumbnail_template .= '<a href="javascript:" onclick="javascript:jQuery(\'#theme_thumbnail_{id}\').lightbox();" data-title="" data-content="<div style=\'width:400px;height:400px;line-height:400px;text-align:center;\'><img src=\'{image_m}\' style=\'max-width:400px;\' /></div>" data-toggle="popover" data-html="true" data-trigger="hover">';
$thumbnail_template .= '	<img src="{image_s}" style="max-width:120px;" border="0" />';
$thumbnail_template .= '</a>';
$thumbnail_template .= '<div class="lightbox fade hide" id="theme_thumbnail_{id}">';
$thumbnail_template .= '	<div class="lightbox-content">';
$thumbnail_template .= '		<img src="{image_l}" />';
$thumbnail_template .= '		<div class="lightbox-caption"><p>{label}</p></div>';
$thumbnail_template .= '	</div>';
$thumbnail_template .= '</div>';


$name_templagte = '<a href="javascript:;" title="详细描述" data-toggle="tooltip"  onClick="javasscript:$(\'#modal_description_{id}\').modal();"><strong>{label}</strong></a>';
$name_templagte .= '<div class="muted">{summary}</div>';
$name_templagte .= '<div class="modal hide fade" id="modal_description_{id}">';
$name_templagte .= '	<div class="modal-header">';
$name_templagte .= '		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
$name_templagte .= '		<h3>{label}</h3>';
$name_templagte .= '	</div>';
$name_templagte .= '	<div class="modal-body">';
$name_templagte .= '		<p>{description}</p>';
$name_templagte .= '	</div>';
$name_templagte .= '	<div class="modal-footer">';
$name_templagte .= '		<a href="#" class="btn" data-dismiss="modal">关闭</a>';
$name_templagte .= '	</div>';
$name_templagte .= '</div>';

$auther_template = '<strong>{auther}</strong><br />';
$auther_template .= '{auther_email}<br />';
$auther_template .= '{auther_website}';

$ui_list->set_filters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键词',
        'value'=>$remote_themes->key,
        'width'=>'120px'
   )
);

$ui_list->set_fields(
    array(
        'name'=>'thumbnail',
        'label'=>'缩略图',
        'align'=>'center',
        'width'=>'120',
        'template'=>$thumbnail_template
    ),
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'60'
    ),
    array(
        'name'=>'name',
        'label'=>'名称',
        'align'=>'left',
        'template'=>$name_templagte
    ),
    array(
        'name'=>'create_time',
        'label'=>'作者',
        'align'=>'left',
        'width'=>'200',
        'template'=>$auther_template
    ),
    array(
        'name'=>'create_time',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'install_button',
        'align'=>'center',
        'width'=>'120'
    )
);

$pagination = be::get_admin_ui('pagination');
$pagination->set_total($remote_themes->total);
$pagination->set_limit($remote_themes->limit);
$pagination->set_page($remote_themes->page);

$ui_list->set_pagination($pagination);
$ui_list->display();
?>
<!--{/center}-->