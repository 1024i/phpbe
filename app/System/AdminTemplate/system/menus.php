<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<script type="text/javascript" language="javascript" src="template/system/js/base64.js"></script>

<link type="text/css" rel="stylesheet" href="template/system/css/menus.css">
<script type="text/javascript" language="javascript" src="template/system/js/menus.js"></script>

<link type="text/css" rel="stylesheet" href="template/system/css/menuSetLink.css">
<script type="text/javascript" language="javascript" src="template/system/js/menuSetLink.js"></script>

<!--{/head}-->

<!--{center}-->
<?php
$groups = $this->get('groups');
$groupId = $this->get('groupId');
$menus = $this->get('menus');
$currentGroup = $groups[0];

echo '<div class="groups">';
echo '<ul class="nav nav-tabs">';
foreach ($groups as $group) {
    echo '<li';
    if ($groupId == $group->id) {
        $currentGroup = $group;
        echo ' class="active"';
    }
    echo '><a href="./?app=System&controller=System&action=menus&groupId='.$group->id.'">'.$group->name.'</a></li>';
}
echo '</ul>';
echo '</div>';

foreach ($menus as $menu) {
    $str = '<select name="target[]" style="width:110px;">';
    $str .= '<option value="Self"';
    if ($menu->target == 'Self')  $str .= ' selected="selected"';
    $str .= '>当前窗口</option>';
    $str .= '<option value="Blank"';
    if ($menu->target == 'Blank')  $str .= ' selected="selected"';
    $str .= '>新窗口</option>';
    $str .= '</select>';
    $menu->target = $str;
}

$targetDefault = '<select name="target[]" style="width:110px;">';
$targetDefault .= '<option value="Self" selected="selected">当前窗口</option>';
$targetDefault .= '<option value="Blank">新窗口</option>';
$targetDefault .= '</select>';

$uiCategoryTree = Be::getUi('categoryTree');

$uiCategoryTree->setAction('save', './?app=System&controller=System&action=menusSave');
$uiCategoryTree->setAction('delete', './?app=System&controller=System&action=ajaxMenuDelete');

$uiCategoryTree->setData($menus);


$fieldUrlTemplate = '<input type="text" name="url[]" class="menu-url" value="{url}" style="width:300px;" />';
$fieldUrlTemplate .= '<input type="hidden" class="menu-params" name="params[]" value="{params}"/>';
$fieldUrlTemplate .= ' <a href="javascript:;" onclick="javascript:setMenu(this);"><i class="icon-edit"</i></a>';

$fieldUrlDefault = '<input type="text" name="url[]" class="menu-url" style="width:300px;" />';
$fieldUrlDefault .= '<input type="hidden" class="menu-params" name="params[]" />';
$fieldUrlDefault .= ' <a href="javascript:;" onclick="javascript:setMenu(this);"><i class="icon-edit"</i></a>';

$fieldUrl = array(
        'name'=>'url',
        'label'=>'链接到',
        'align'=>'center',
        'width'=>'360',
        'template'=>$fieldUrlTemplate,
        'default'=>$fieldUrlDefault
    );
$fieldTarget = array(
        'name'=>'target',
        'label'=>'打开方式',
        'align'=>'center',
        'width'=>'60',
        'default'=>$targetDefault
    );
$fieldHome = array(
        'label'=>'设为首页',
        'align'=>'center',
        'width'=>'90',
        'template'=>'<a href="javascript:;" onclick="javascript:setHome({id})" class="home home-{home}" id="home-{id}"></a>'
    );

if ($currentGroup->className == 'north')
    $uiCategoryTree->setFields($fieldUrl, $fieldTarget, $fieldHome);
else
    $uiCategoryTree->setFields($fieldUrl, $fieldTarget);

$uiCategoryTree->setFooter('<input type="hidden" name="groupId" value="'.$groupId.'">');
$uiCategoryTree->display();
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