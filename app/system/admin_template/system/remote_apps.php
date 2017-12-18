<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_list = be::get_ui('grid');
$ui_list->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$service_app = be::get_service('system.app');
$installed_apps = $service_app->get_apps();

$remote_apps = $this->get('remote_apps');
if ($remote_apps->status!='0') {
    echo $remote_apps->description;
    return;
}

$apps = $remote_apps->apps;

foreach ($apps as $app) {
    $app->create_time = date('Y-m-d',$app->create_time);

    $app->installed = 0;
    foreach ($installed_apps as $installed_app) {
        if ($installed_app->id == $app->id) {
            $app->installed = 1;
            break;
        }
    }
}

$ui_list = be::get_ui('grid');
$ui_list->set_action('listing', './?controller=system&task=remote_apps');

$ui_list->set_data($apps);

$ui_list->set_filters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键词',
        'value'=>$remote_apps->key,
        'width'=>'120px'
   )
);

$ui_list->set_fields(
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
        'name'=>'create_time',
        'label'=>'作者',
        'align'=>'left',
        'width'=>'200',
        'template'=>'<strong>{auther}</strong><br />{auther_email}<br />{auther_website}'
    ),
    array(
        'name'=>'create_time',
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
        'template'=>'<a class="btn btn-success" onclick="javascript:window.location.href=\'./?controller=system&task=remote_app&app_id={id}\';"><i class="icon-white icon-search"></i> 查看</a>'
    )

);

$pagination = be::get_ui('pagination');
$pagination->set_total($remote_apps->total);
$pagination->set_limit($remote_apps->limit);
$pagination->set_page($remote_apps->page);

$ui_list->set_pagination($pagination);
$ui_list->display();
?>
<!--{/center}-->