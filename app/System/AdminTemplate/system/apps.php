<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<script type="text/javascript" language="javascript" src="template/system/js/apps.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$apps = $this->get('apps');

$uiList = Be::getUi('grid');

$uiList->setAction('listing', './?app=System&controller=System&task=apps');
$uiList->setAction('create', './?app=System&controller=System&task=remoteApps', '安装新应用');

foreach ($apps as $app) {
    //$app->id = $app->name;
    $app->homePage = '';
    $app->deleteHtml = '';

    if ($app->id>1024) {
        $app->homePage = '<a class="btn btn-info" href="http://www.phpbe.com/app/'.$app->id.'.html" target="Blank"><i class="icon-white icon-search"></i> 查看</a>';
        $app->deleteHtml = '<a class="btn btn-danger" href="javascript:;" onclick="javascript:deleteApp(this, \''.$app->name.'\');"><i class="icon-white icon-remove"></i> 卸载</a>';
    }
}

$uiList->setData($apps);
$uiList->setFooter('共安装了 <strong>'.count($apps).'</strong> 个应用');

$uiList->setFields(
    array(
        'name'=>'icon',
        'label'=>'',
        'align'=>'center',
        'template'=>'<img src="{icon}" />',
        'width'=>'30'
    ),
    array(
        'name'=>'label',
        'label'=>'名称',
        'align'=>'left'
    ),
    array(
        'name'=>'version',
        'label'=>'版本',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'name',
        'label'=>'标识',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'homePage',
        'label'=>'',
        'align'=>'center',
        'width'=>'90'
    ),
    array(
        'name'=>'deleteHtml',
        'label'=>'',
        'align'=>'center',
        'width'=>'120'
    )
);
$uiList->display();
?>
<!--{/center}-->