<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiGrid = Be::getUi('grid');
$uiGrid->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$serviceApp = Be::getService('System', 'app');
$installedApps = $serviceApp->getApps();

$remoteApps = $this->get('remoteApps');
if ($remoteApps->status!='0') {
    echo $remoteApps->description;
    return;
}

$apps = $remoteApps->apps;

foreach ($apps as $app) {
    $app->createTime = date('Y-m-d',$app->createTime);

    $app->installed = 0;
    foreach ($installedApps as $installedApp) {
        if ($installedApp->id == $app->id) {
            $app->installed = 1;
            break;
        }
    }
}

$uiGrid = Be::getUi('grid');
$uiGrid->setAction('listing', './?app=System&controller=System&action=remoteApps');

$uiGrid->setData($apps);

$uiGrid->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键词',
        'value'=>$remoteApps->key,
        'width'=>'120px'
   )
);

$uiGrid->setFields(
    array(
        'name'=>'logo',
        'label'=>'缩略图',
        'align'=>'center',
        'width'=>'100',
        'template'=>'<img src="{logo}" width="90">'
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
        'template'=>'<strong>{label}</strong><br />{summary}'
    ),
    array(
        'name'=>'createTime',
        'label'=>'作者',
        'align'=>'left',
        'width'=>'200',
        'template'=>'<strong>{auther}</strong><br />{autherEmail}<br />{autherWebsite}'
    ),
    array(
        'name'=>'createTime',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'installed',
        'label'=>'已安装?',
        'align'=>'center',
        'width'=>'80',
        'template'=>'<a class="icon checked-{installed}"></a>'
    ),
    array(
        'align'=>'center',
        'width'=>'120',
        'template'=>'<a class="btn btn-success" onclick="javascript:window.location.href=\'./?app=System&controller=System&action=remoteApp&appId={id}\';"><i class="icon-white icon-search"></i> 查看</a>'
    )

);

$pagination = Be::getUi('Pagination');
$pagination->setTotal($remoteApps->total);
$pagination->setLimit($remoteApps->limit);
$pagination->setPage($remoteApps->page);

$uiGrid->setPagination($pagination);
$uiGrid->display();
?>
<!--{/center}-->