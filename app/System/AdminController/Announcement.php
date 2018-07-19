<?php

namespace App\System\AdminController;

use Phpbe\System\AdminController;

// 公告
class Announcement extends AdminController
{

    use \App\System\AdminTrait\Resource;

    protected $config = [
        'base' => [
            'name' => '公告'
        ],

        'lists' => [

            'toolbar' => [
                'create' => '新建',
                'export' => '导出'
            ],

            'action' => [
                'detail' => '查看',
                'edit' => '编辑',
                'delete' => '删除',
            ],
        ],

        'detail' => [
            'tabs' => array()
        ],

        'create' => [],

        'edit' => [],

        'block' => [
            'field' => 'block',
            'value' => 1
        ],

        'unblock' => [
            'field' => 'block',
            'value' => 0
        ],

        'delete' => [
            'field' => 'is_delete',
            'value' => 1
        ],

        'export' => [],
    ];


}