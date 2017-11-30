<?php
use system\be;
?>
<!--{center}-->
<?php
$categories = $this->get('categories');

$ui_category_tree = be::get_admin_ui('category_tree');
$ui_category_tree->set_data($categories);
$ui_category_tree->set_action('save', './?controller=article&task=save_categories');
$ui_category_tree->set_action('delete', './?controller=article&task=ajax_delete_category');
$ui_category_tree->set_fields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'60',
        'template'=>'<span style="color:#999">{id}</span>'
    )
);
$ui_category_tree->display();
?>
<!--{/center}-->