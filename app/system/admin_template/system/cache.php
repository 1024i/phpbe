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

$data = [];

$x = new \stdClass();
$x->id = 'file';
$x->name = '数据缓存';
$data[] = $x;

$x = new \stdClass();
$x->id = 'html';
$x->name = '自定义模块';
$data[] = $x;

$x = new \stdClass();
$x->id = 'menu';
$x->name = '菜单';
$data[] = $x;

$x = new \stdClass();
$x->id = 'row';
$x->name = '行模型';
$data[] = $x;

$x = new \stdClass();
$x->id = 'table';
$x->name = '表模型';
$data[] = $x;

$x = new \stdClass();
$x->id = 'template';
$x->name = '前台模板';
$data[] = $x;

$x = new \stdClass();
$x->id = 'admin_template';
$x->name = '后台模板';
$data[] = $x;

$x = new \stdClass();
$x->id = 'user_role';
$x->name = '用户角色';
$data[] = $x;

$x = new \stdClass();
$x->id = 'admin_user_role';
$x->name = '管理员角色';
$data[] = $x;

foreach ($data as $x) {
    $x->operation = '<a href="./?controller=system&task=clear_cache&type=' . $x->id . '">清除</a>';
}

$ui_list = be::get_ui('grid');
$ui_list->set_data($data);

$ui_list->set_fields(
    [
        'name' => 'id',
        'label' => '类型',
        'align' => 'center',
    ],
    [
        'name' => 'name',
        'label' => '类型名称',
        'align' => 'center',
    ],
    [
        'name' => 'operation',
        'label' => '操作',
        'align' => 'center',
    ]
);
$ui_list->display();
?>
<!--{/center}-->