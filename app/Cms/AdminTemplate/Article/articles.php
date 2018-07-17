<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$articles = $this->get('articles');
$categories = $this->get('categories');

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?app=Cms&controller=Article&action=articles');
$uiList->setAction('create', './?app=Cms&controller=Article&action=edit');
$uiList->setAction('edit', './?app=Cms&controller=Article&action=edit');
$uiList->setAction('unblock', './?app=Cms&controller=Article&action=unblock');
$uiList->setAction('block', './?app=Cms&controller=Article&action=block');
$uiList->setAction('delete', './?app=Cms&controller=Article&action=delete');

$categoryOptions = array();
$categoryOptions['-1'] = '所有文章';
$categoryOptions['0'] = '未分类文章';
foreach ($categories as $category) {
    if ($category->level>0)
        $categoryOptions[$category->id] = str_repeat('&nbsp; ', $category->level) . $category->name;
    else
        $categoryOptions[$category->id] = $category->name;
}

$uiList->setFilters(
    array(
        'type'=>'text',
        'name'=>'key',
        'label'=>'关键字',
        'value'=>$this->get('key'),
        'width'=>'120px'
   ),
    array(
        'type'=>'select',
        'name'=>'categoryId',
        'label'=>'所属分类',
        'options'=>$categoryOptions,
        'value'=>$this->get('categoryId'),
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

$indexCategories = array();
$indexCategories[0] = '未分类文章';
foreach ($categories as $category) {
    $indexCategories[$category->id] = $category->name;
}

$configArticle = Be::getConfig('Cms', 'Article');

foreach ($articles as $article) {
    $article->titleHtml = '<span class="text-warning">['.$indexCategories[$article->categoryId].']</span> <a href="'.url('app=Cms&controller=Article&action=detail&articleId='.$article->id).'" title="'.$article->title.'" target="Blank" data-toggle="tooltip">'.limit($article->title, 50).'</a>';
    $article->createTime =	date('Y-m-d H:i',$article->createTime);

    $creator = Be::getUser($article->createById);
    $article->creator =	$creator->id>0?$creator->name:'不存在';

    if ($article->thumbnailS == '') {
        $article->thunbmailHtml = '<img src="../'.DATA.'/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailS.'" width="48" />';
    } else {
        $article->thunbmailHtml = '<img src="../'.DATA.'/Article/Thumbnail/'.$article->thumbnailS.'" width="48" />';
    }

    $article->comment = '<a href="./?app=Cms&controller=Article&action=comments&articleId='.$article->id.'" class="label'.($article->commentCount>0?' label-info':'').'">'.$article->commentCount.'</a>';
    $article->ordering = '<span class="label'.($article->ordering>0?' label-success':'').'">'.$article->ordering.'</span>';
    $article->top = '<span class="label'.($article->top>0?' label-warning':'').'">'.$article->top.'</span>';
}

$uiList->setData($articles);

$uiList->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'thunbmailHtml',
        'label'=>'缩略图',
        'align'=>'center',
        'style'=>'margin:0;padding:2px;',
        'width'=>'50'
    ),
    array(
        'name'=>'titleHtml',
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
        'name'=>'createTime',
        'label'=>'发布时间',
        'align'=>'center',
        'width'=>'120',
        'orderBy'=>'createTime'
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
        'orderBy'=>'ordering'
    ),
    array(
        'name'=>'top',
        'label'=>'推荐',
        'align'=>'center',
        'width'=>'40',
        'orderBy'=>'top'
    ),
    array(
        'name'=>'hits',
        'label'=>'点击量',
        'align'=>'center',
        'width'=>'60',
        'orderBy'=>'hits'
    )
);

$uiList->setPagination($this->get('pagination'));
$uiList->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiList->display();
?>
<!--{/center}-->
