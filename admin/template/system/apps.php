<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_list = be::get_admin_ui('grid');
$ui_list->head();
?>
<script type="text/javascript" language="javascript" src="template/system/js/apps.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$apps = $this->get('apps');

$ui_list = be::get_admin_ui('grid');

$ui_list->set_action('listing', './?controller=system&task=apps');
$ui_list->set_action('create', './?controller=system&task=remote_apps', '安装新应用');

foreach ($apps as $app) {
    //$app->id = $app->name;
    $app->home_page = '';
    $app->delete_html = '';

    if ($app->id>1024) {
        $app->home_page = '<a class="btn btn-info" href="http://www.phpbe.com/app/'.$app->id.'.html" target="_blank"><i class="icon-white icon-search"></i> 查看</a>';
        $app->delete_html = '<a class="btn btn-danger" href="javascript:;" onclick="javascript:deleteApp(this, \''.$app->name.'\');"><i class="icon-white icon-remove"></i> 卸载</a>';
    }
}

$ui_list->set_data($apps);
$ui_list->set_footer('共安装了 <strong>'.count($apps).'</strong> 个应用');

$ui_list->set_fields(
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
        'name'=>'home_page',
        'label'=>'',
        'align'=>'center',
        'width'=>'90'
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