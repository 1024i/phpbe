<?php
use system\be;
?>

<!--{center}-->
<?php
$groups = $this->get('groups');

$ui_list = be::get_ui('grid');

$ui_list->set_action('listing', './?controller=system&task=menu_groups');
$ui_list->set_action('create', './?controller=system&task=menu_group_edit');
$ui_list->set_action('edit', './?controller=system&task=menu_group_edit');
$ui_list->set_action('delete', './?controller=system&task=menu_group_delete');

$ui_list->set_data($groups);

$ui_list->set_fields(
    array(
        'name'=>'name',
        'label'=>'菜单组名',
        'align'=>'left'
    ),
    array(
        'name'=>'class_name',
        'label'=>'调用类名',
        'align'=>'center',
        'width'=>'180'
    )
);
$ui_list->display();

?>
<div class="comment">
    <ul>
        <li>* 菜单组类名为开发人员开发时调用。</li>
        <li>* north, south, dashboard 为系统默认顶部菜单,底部和用户中心菜单类名， 禁止改动和删除。</li>
    </ul>
</div>
<!--{/center}-->