<?php
use system\be;
?>

<!--{head}-->
<?php

$admin_ui_editor = be::get_ui('editor');
$admin_ui_editor->head();
?>
<script type="text/javascript" language="javascript" src="template/user/js/role_permissions.js"></script>
<!--{/head}-->

<!--{center}-->
<?php
$role = $this->get('role');
$apps = $this->get('apps');

$admin_ui_editor = be::get_ui('editor');

$admin_ui_editor->set_action('save', './?controller=user&task=role_permissions_save');	// 显示提交按钮
$admin_ui_editor->set_action('reset');// 显示重设按钮
$admin_ui_editor->set_action('back', './?controller=user&task=roles');	// 显示返回按钮


$admin_ui_editor->add_field(
    array(
        'label'=>'用户角色',
        'html'=>$role->name
    )
);

if ($role->id == 1) {
    $admin_ui_editor->add_field(
        array(
            'type'=>'radio',
            'name'=>'permission',
            'label'=>'权限',
            'value'=>$role->permission,
            'options'=>array('1'=>'所有不需要登陆的功能', '0'=>'禁用任何功能', '-1'=>'自定义')
        )
    );
} else {
    $admin_ui_editor->add_field(
        array(
            'type'=>'radio',
            'name'=>'permission',
            'label'=>'权限',
            'value'=>$role->permission,
            'options'=>array('1'=>'所有功能', '0'=>'禁用任何功能', '-1'=>'自定义')
        )
    );
}

$permissions = explode(',', $role->permissions);

foreach ($apps as $app) {

    // 游客不能访问 user 应用
    if ($role->id == 1 && $app->name == 'user') continue;


    $app_permissions = $app->get_permissions();

    if (count($app_permissions)>0) {

        $select_all = true;

        $values = [];
        $options = [];
        foreach ($app_permissions as $key => $val) {
            if ($key == '-') continue;

            $value = implode(',', $val);
            if (array_diff($val, $permissions)) {
                $select_all = false;
            } else {
                $values[] = $value;
            }

            $options[$value] = $key;
        }

        if (count($options) == 0) continue;

        $admin_ui_editor->add_field(
            array(
                'type' => 'checkbox',
                'name' => 'permissions[]',
                'label' => '<label class="checkbox inline" style="padding:0;color:#468847;font-weight:bold;"><input type="checkbox" class="select-app-permissions"'.($select_all?' checked="checked"':'').'>'.$app->label.'</label>',
                'value' => $values,
                'options' => $options
            )
        );
    }
}

$admin_ui_editor->add_hidden('role_id', $role->id);
$admin_ui_editor->display();
?>
<!--{/center}-->