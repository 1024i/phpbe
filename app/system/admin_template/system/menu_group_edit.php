<?php
use system\be;
?>

<!--{head}-->
<?php
$ui_editor = be::get_ui('editor');
$ui_editor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$menu_group = $this->get('menu_group');

$ui_editor = be::get_ui('editor');


$ui_editor->set_action('save', './?controller=system&task=menu_group_edit_save');	// 显示提交按钮
$ui_editor->set_action('reset');// 显示重设按钮
$ui_editor->set_action('back', './?controller=system&task=menu_groups');	// 显示返回按钮

if ($menu_group->class_name == 'north' || $menu_group->class_name == 'south' || $menu_group->class_name == 'dashboard') {
    echo '<script type="text/javascript" language="javascript">$(function(){ $("#class_name").prop("disabled", true); });</script>';
}

$ui_editor->set_fields(
    array(
        'type'=>'text',
        'name'=>'name',
        'label'=>'菜单组名',
        'value'=>$menu_group->name,
        'width'=>'200px',
        'validate'=>array(
            'required'=>true,
            'max_length'=>60
        )
    ),
    array(
        'type'=>'text',
        'name'=>'class_name',
        'label'=>'调用类名',
        'value'=>$menu_group->class_name,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'max_length'=>60
        )
    )
);

$ui_editor->add_hidden('id', $menu_group->id);
$ui_editor->display();
?>
<!--{/center}-->