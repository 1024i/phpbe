<?php
use System\Be;
?>

<!--{head}-->
<?php
$uiList = Be::getUi('grid');
$uiList->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$comments = $this->get('comments');

$uiList = Be::getUi('grid');

$uiList->setAction('list', './?app=Cms&controller=Article&task=comments');
$uiList->setAction('unblock', './?app=Cms&controller=Article&task=commentsUnblock');
$uiList->setAction('block', './?app=Cms&controller=Article&task=commentsBlock');
$uiList->setAction('delete', './?app=Cms&controller=Article&task=commentsDelete');

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
        'name'=>'status',
        'label'=>'状态',
        'options'=>array(
            '-1'=>'所有',
            '0'=>'公开',
            '1'=>'屏蔽'
       ),
        'value'=>$this->get('status')
   ),
    array(
        'type'=>'hidden',
        'name'=>'articleId',
        'value'=>$this->get('articleId')
   )
);

$libIp = Be::getLib('ip');
foreach ($comments as $comment) {
    $comment->articleHtml = '<a href="'.url('app=Cms&controller=Article&task=detail&articleId='.$comment->articleId).'" title="'.$comment->article->title.'" target="Blank" data-toggle="tooltip">'.limit($comment->article->title, 20).'</a>';

    $bodyHtml = '';

    if (strlen($comment->body)<30) {
        $bodyHtml = $comment->body;
    } else {
        $bodyHtml = '<a href="javascript:;" onclick="javascript:$(\'#modal-comment-'.$comment->id.'\').modal();">'.limit($comment->body, 30).'</a>';
        $bodyHtml .= '<div class="modal hide fade" id="modal-comment-'.$comment->id.'">';
        $bodyHtml .= '<div class="modal-header">';
        $bodyHtml .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';

        $commentUser = Be::getUser($comment->userId);
        if ($commentUser->id>0) {
            $html .= '<h4>'.$commentUser->name.'：</h4>';
        } else {
            $html .= '<h4>'.$comment->userName.'：</h4>';
        }

        $bodyHtml .= '</div>';
        $bodyHtml .= '<div class="modal-body" style="word-break:break-all;word-wrap:break-word;">';
        $bodyHtml .= $comment->body;
        $bodyHtml .= '</div>';
        $bodyHtml .= '<div class="modal-footer">';
        $bodyHtml .= '<input type="button" class="btn" data-dismiss="modal" value="'.'关闭'.'">';
        $bodyHtml .= '</div>';
        $bodyHtml .= '</div>';
    }

    $comment->bodyHtml = $bodyHtml;
    $comment->createTime =	date('Y-m-d H:i',$comment->createTime);

    $creator = Be::getUser($comment->userId);
    $comment->creator =	$creator->id>0?$creator->name:'不存在';
    $comment->address = '<a href="javascript:;" title="'.$libIp->convert($comment->ip).'" data-toggle="tooltip">'.$comment->ip.'</a>';
}

$uiList->setData($comments);

$uiList->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'30',
        'orderBy'=>'id'
    ),
    array(
        'name'=>'articleHtml',
        'label'=>'关联文章',
        'align'=>'left'
    ),
    array(
        'name'=>'bodyHtml',
        'label'=>'评论',
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
        'name'=>'address',
        'label'=>'地理位置',
        'align'=>'center',
        'width'=>'120'
    )
);

$uiList->setPagination($this->get('pagination'));
$uiList->orderBy($this->get('orderBy'), $this->get('orderByDir'));
$uiList->display();
?>
<!--{/center}-->