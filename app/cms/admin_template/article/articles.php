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
$articles = $this->get('articles');
$categories = $this->get('categories');

$ui_list = be::get_ui('grid');

$ui_list->set_action('list', './?controller=article&task=articles');
$ui_list->set_action('create', './?controller=article&task=edit');
$ui_list->set_action('edit', './?controller=article&task=edit');
$ui_list->set_action('unblock', './?controller=article&task=unblock');
$ui_list->set_action('block', './?controller=article&task=block');
$ui_list->set_action('delete', './?controller=article&task=delete');

$category_options = array();
$category_options['-1'] = '所有文章';
$category_options['0'] = '未分类文章';
foreach ($categories as $category) {
    if ($category->level>0)
        $category_options[$category->id] = str_repeat('&nbsp; ', $category->level) . $category->name;
    else
        $category_options[$category->id] = $category->name;
}

$ui_list->set_filters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'category_id',
        'label'=>'所属分类',
        'options'=>$category_options,
        'value'=>$this->get('category_id'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'status',
        'label'=>'状态',
        'options'=>array(
            '-1'=>'所有',
            '0'=>'公开',
            '1'=>'屏蔽'
       ),
        'value'=>$this->get('status'),
        'width'=>'80px'
   )
);

$index_categories = array();
$index_categories[0] = '未分类文章';
foreach ($categories as $category) {
    $index_categories[$category->id] = $category->name;
}

$config_article = be::get_config('article');

foreach ($articles as $article) {
    $article->title_html = '<span class="text-warning">['.$index_categories[$article->category_id].']</span> <a href="'.url('controller=article&task=detail&article_id='.$article->id).'" title="'.$article->title.'" target="_blank" data-toggle="tooltip">'.limit($article->title, 50).'</a>';
    $article->create_time =	date('Y-m-d H:i',$article->create_time);

    $creator = be::get_user($article->create_by_id);
    $article->creator =	$creator->id>0?$creator->name:'不存在';

    if ($article->thumbnail_s == '') {
        $article->thunbmail_html = '<img src="../'.DATA.'/article/thumbnail/default/'.$config_article->default_thumbnail_s.'" width="48" />';
    } else {
        $article->thunbmail_html = '<img src="../'.DATA.'/article/thumbnail/'.$article->thumbnail_s.'" width="48" />';
    }

    $article->comment = '<a href="./?controller=article&task=comments&article_id='.$article->id.'" class="label'.($article->comment_count>0?' label-info':'').'">'.$article->comment_count.'</a>';
    $article->ordering = '<span class="label'.($article->ordering>0?' label-success':'').'">'.$article->ordering.'</span>';
    $article->top = '<span class="label'.($article->top>0?' label-warning':'').'">'.$article->top.'</span>';
}

$ui_list->set_data($articles);

$ui_list->set_fields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'order_by'=>'id'
    ),
    array(
        'name'=>'thunbmail_html',
        'label'=>'缩略图',
        'align'=>'center',
        'style'=>'margin:0;padding:2px;',
        'width'=>'50'
    ),
    array(
        'name'=>'title_html',
        'label'=>'标题',
        'align'=>'left'
    ),
    array(
        'name'=>'creator',
        'label'=>'作者',
        'align'=>'center',
        'width'=>'120'
    ),
    array(
        'name'=>'create_time',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120',
        'order_by'=>'create_time'
    ),
    array(
        'name'=>'comment',
        'label'=>'评论',
        'width'=>'40'
    ),
    array(
        'name'=>'ordering',
        'label'=>'排序',
        'align'=>'center',
        'width'=>'40',
        'order_by'=>'ordering'
    ),
    array(
        'name'=>'top',
        'label'=>'推荐',
        'align'=>'center',
        'width'=>'40',
        'order_by'=>'top'
    ),
    array(
        'name'=>'hits',
        'label'=>'点击量',
        'align'=>'center',
        'width'=>'60',
        'order_by'=>'hits'
    )
);

$ui_list->set_pagination($this->get('pagination'));
$ui_list->order_by($this->get('order_by'), $this->get('order_by_dir'));
$ui_list->display();
?>
<!--{/center}-->
