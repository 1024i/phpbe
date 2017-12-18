<?php
use system\be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="template/system/js/base64.js"></script>

<link type="text/css" rel="stylesheet" href="template/system/css/menus.css">
<script type="text/javascript" language="javascript" src="template/system/js/menus.js"></script>

<link type="text/css" rel="stylesheet" href="template/system/css/menu_set_link.css">
<script type="text/javascript" language="javascript" src="template/system/js/menu_set_link.js"></script>

<!--{/head}-->

<!--{center}-->
<?php
$groups = $this->get('groups');
$group_id = $this->get('group_id');
$menus = $this->get('menus');
$current_group = $groups[0];

echo '<div class="groups">';
echo '<ul class="nav nav-tabs">';
foreach ($groups as $group) {
    echo '<li';
    if ($group_id == $group->id) {
        $current_group = $group;
        echo ' class="active"';
    }
    echo '><a href="./?controller=system&task=menus&group_id='.$group->id.'">'.$group->name.'</a></li>';
}
echo '</ul>';
echo '</div>';

foreach ($menus as $menu) {
    $str = '<select name="target[]" style="width:110px;">';
    $str .= '<option value="_self"';
    if ($menu->target == '_self')  $str .= ' selected="selected"';
    $str .= '>当前窗口</option>';
    $str .= '<option value="_blank"';
    if ($menu->target == '_blank')  $str .= ' selected="selected"';
    $str .= '>新窗口</option>';
    $str .= '</select>';
    $menu->target = $str;
}

$target_default = '<select name="target[]" style="width:110px;">';
$target_default .= '<option value="_self" selected="selected">当前窗口</option>';
$target_default .= '<option value="_blank">新窗口</option>';
$target_default .= '</select>';

$ui_category_tree = be::get_ui('category_tree');

$ui_category_tree->set_action('save', './?controller=system&task=menus_save');
$ui_category_tree->set_action('delete', './?controller=system&task=ajax_menu_delete');

$ui_category_tree->set_data($menus);


$field_url_template = '<input type="text" name="url[]" class="menu-url" value="{url}" style="width:300px;" />';
$field_url_template .= '<input type="hidden" class="menu-params" name="params[]" value="{params}"/>';
$field_url_template .= ' <a href="javascript:;" onclick="javascript:setMenu(this);"><i class="icon-edit"</i></a>';

$field_url_default = '<input type="text" name="url[]" class="menu-url" style="width:300px;" />';
$field_url_default .= '<input type="hidden" class="menu-params" name="params[]" />';
$field_url_default .= ' <a href="javascript:;" onclick="javascript:setMenu(this);"><i class="icon-edit"</i></a>';

$field_url = array(
        'name'=>'url',
        'label'=>'链接到',
        'align'=>'center',
        'width'=>'360',
        'template'=>$field_url_template,
        'default'=>$field_url_default
    );
$field_target = array(
        'name'=>'target',
        'label'=>'打开方式',
        'align'=>'center',
        'width'=>'60',
        'default'=>$target_default
    );
$field_home = array(
        'label'=>'设为首页',
        'align'=>'center',
        'width'=>'90',
        'template'=>'<a href="javascript:;" onclick="javascript:setHome({id})" class="home home-{home}" id="home-{id}"></a>'
    );

if ($current_group->class_name == 'north')
    $ui_category_tree->set_fields($field_url, $field_target, $field_home);
else
    $ui_category_tree->set_fields($field_url, $field_target);

$ui_category_tree->set_footer('<input type="hidden" name="group_id" value="'.$group_id.'">');
$ui_category_tree->display();
?>
<div class="comment">
    <ul>
        <li>* 添加多级菜单时需要模板支持。</li>
        <li>* 子菜单保存后方可添加更深级子菜单。</li>
    </ul>
</div>

<div class="modal hide fade" id="modal-menu">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>设置菜单链接</h3>
    </div>
    <div class="modal-body" id="modal-menu-body"></div>
    <div class="modal-footer">
        <input type="button" id="modal-menu-save-button" class="btn btn-primary" value="确认" onclick="javascript:saveMenu();" />
        <input type="button" class="btn" data-dismiss="modal" value="取消" />
    </div>
</div>
<!--{/center}-->