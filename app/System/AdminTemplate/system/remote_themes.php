<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<link type="text/css" rel="stylesheet" href="bootstrap/2.3.2/css/bootstrap-lightbox.css" />
<script type="text/javascript" language="javascript" src="bootstrap/2.3.2/js/bootstrap-lightbox.js"></script>

<script type="text/javascript" language="javascript" src="template/system/js/remoteThemes.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$remoteThemes = $this->get('remoteThemes');
$localThemes = $this->get('localThemes');

if ($remoteThemes->status!='0') {
    echo $remoteThemes->description;
    return;
}

$themes = $remoteThemes->themes;

$installedThemeIds = array();
foreach ($localThemes as $localTheme) {
    $installedThemeIds[] = $localTheme->id;
}

foreach ($themes as $theme) {

    $theme->createTime = date('Y-m-d',$theme->createTime);

    if ($theme->autherWebsite) {
        $theme->autherWebsite = '<a href="'.$theme->autherWebsite.'" target="Blank">'.$theme->autherWebsite.'</a>';
    } else {
        $theme->autherWebsite = '';
    }

    if (in_array($theme->id, $installedThemeIds)) {
        $theme->installButton = '<a class="btn disabled" onclick="javascript:;"><i class="icon-ok"></i> 已安装</a>';

    } else {
        $theme->installButton = '<a class="btn btn-success" onclick="javascript:install(this, '.$theme->id.');"><i class="icon-white icon-download"></i> 安装</a>';
    }
}

$uiList = Be::getUi('grid');
$uiList->setAction('listing', './?app=System&controller=System&task=remoteThemes');

$uiList->setData($themes);

$thumbnailTemplate = '';
$thumbnailTemplate .= '<a href="javascript:" onclick="javascript:jQuery(\'#themeThumbnail_{id}\').lightbox();" data-title="" data-content="<div style=\'width:400px;height:400px;line-height:400px;text-align:center;\'><img src=\'{imageM}\' style=\'max-width:400px;\' /></div>" data-toggle="popover" data-html="true" data-trigger="hover">';
$thumbnailTemplate .= '	<img src="{imageS}" style="max-width:120px;" border="0" />';
$thumbnailTemplate .= '</a>';
$thumbnailTemplate .= '<div class="lightbox fade hide" id="themeThumbnail_{id}">';
$thumbnailTemplate .= '	<div class="lightbox-content">';
$thumbnailTemplate .= '		<img src="{imageL}" />';
$thumbnailTemplate .= '		<div class="lightbox-caption"><p>{label}</p></div>';
$thumbnailTemplate .= '	</div>';
$thumbnailTemplate .= '</div>';


$nameTemplagte = '<a href="javascript:;" title="详细描述" data-toggle="tooltip"  onClick="javasscript:$(\'#modalDescription_{id}\').modal();"><strong>{label}</strong></a>';
$nameTemplagte .= '<div class="muted">{summary}</div>';
$nameTemplagte .= '<div class="modal hide fade" id="modalDescription_{id}">';
$nameTemplagte .= '	<div class="modal-header">';
$nameTemplagte .= '		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
$nameTemplagte .= '		<h3>{label}</h3>';
$nameTemplagte .= '	</div>';
$nameTemplagte .= '	<div class="modal-body">';
$nameTemplagte .= '		<p>{description}</p>';
$nameTemplagte .= '	</div>';
$nameTemplagte .= '	<div class="modal-footer">';
$nameTemplagte .= '		<a href="#" class="btn" data-dismiss="modal">关闭</a>';
$nameTemplagte .= '	</div>';
$nameTemplagte .= '</div>';

$autherTemplate = '<strong>{auther}</strong><br />';
$autherTemplate .= '{autherEmail}<br />';
$autherTemplate .= '{autherWebsite}';

$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键词',
        'value'=>$remoteThemes->key,
        'width'=>'120px'
   )
);

$uiList->setFields(
    array(
        'name'=>'thumbnail',
        'label'=>'缩略图',
        'align'=>'center',
        'width'=>'120',
        'template'=>$thumbnailTemplate
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
        'template'=>$nameTemplagte
    ),
    array(
        'name'=>'createTime',
        'label'=>'作者',
        'align'=>'left',
        'width'=>'200',
        'template'=>$autherTemplate
    ),
    array(
        'name'=>'createTime',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'installButton',
        'align'=>'center',
        'width'=>'120'
    )
);

$pagination = Be::getUi('Pagination');
$pagination->setTotal($remoteThemes->total);
$pagination->setLimit($remoteThemes->limit);
$pagination->setPage($remoteThemes->page);

$uiList->setPagination($pagination);
$uiList->display();
?>
<!--{/center}-->