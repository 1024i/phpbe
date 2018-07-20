<?php
use Phpbe\System\Be;
?>

<!--{center}-->
<?php
$groups = $this->get('groups');

$uiGrid = Be::getUi('grid');

$uiGrid->setAction('listing', './?app=System&controller=System&action=menuGroups');
$uiGrid->setAction('create', './?app=System&controller=System&action=menuGroupEdit');
$uiGrid->setAction('edit', './?app=System&controller=System&action=menuGroupEdit');
$uiGrid->setAction('delete', './?app=System&controller=System&action=menuGroupDelete');

$uiGrid->setData($groups);

$uiGrid->setFields(
    array(
        'name'=>'name',
        'label'=>'菜单组名',
        'align'=>'left'
    ),
    array(
        'name'=>'className',
        'label'=>'调用类名',
        'align'=>'center',
        'width'=>'180'
    )
);
$uiGrid->display();

?>
<div class="comment">
    <ul>
        <li>* 菜单组类名为开发人员开发时调用。</li>
        <li>* north, south, dashboard 为系统默认顶部菜单,底部和用户中心菜单类名， 禁止改动和删除。</li>
    </ul>
</div>
<!--{/center}-->