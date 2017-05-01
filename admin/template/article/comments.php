<?php
namespace admin\template\article;

class comments extends \admin\theme
{

	protected function head()
	{
		$ui_list = be::get_admin_ui('list');
		$ui_list->head();
	}


	private function get_format_body($comment)
	{
		if (strlen($comment->body)<30) return $comment->body;

		$html = '<a href="javascript:;" onclick="javascript:$(\'#modal-comment-'.$comment->id.'\').modal();">'.limit($comment->body, 30).'</a>';
		$html .= '<div class="modal hide fade" id="modal-comment-'.$comment->id.'">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';

		$comment_user = be::get_user($comment->user_id);
		if ($comment_user->id>0) {
			$html .= '<h4>'.$comment_user->name.'：</h4>';
		} else {
			$html .= '<h4>'.$comment->user_name.'：</h4>';
		}
		
		$html .= '</div>';
		$html .= '<div class="modal-body" style="word-break:break-all;word-wrap:break-word;">';
		$html .= $comment->body;
		$html .= '</div>';
		$html .= '<div class="modal-footer">';
		$html .= '<input type="button" class="btn" data-dismiss="modal" value="'.'关闭'.'">';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}


	protected function center()
	{
		$comments = $this->get('comments');

		$ui_list = be::get_admin_ui('list');
		
		$ui_list->set_action('list', './?controller=article&task=comments');
		$ui_list->set_action('unblock', './?controller=article&task=comments_unblock');
		$ui_list->set_action('block', './?controller=article&task=comments_block');
		$ui_list->set_action('delete', './?controller=article&task=comments_delete');

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
                'name'=>'article_id', 
                'value'=>$this->get('article_id')
           )
		);

		$lib_ip = be::get_lib('ip');
	    foreach ($comments as $comment) {
			$comment->article_html = '<a href="'.url('controller=article&task=detail&article_id='.$comment->article_id).'" title="'.$comment->article->title.'" target="_blank" data-toggle="tooltip">'.limit($comment->article->title, 20).'</a>';
			
			$comment->body_html = $this->get_format_body($comment);
			
			$comment->create_time =	date('Y-m-d H:i',$comment->create_time);

			$creator = be::get_user($comment->user_id);
			$comment->creator =	$creator->id>0?$creator->name:'不存在';
			$comment->address = '<a href="javascript:;" title="'.$lib_ip->convert($comment->ip).'" data-toggle="tooltip">'.$comment->ip.'</a>';
		}
		
		$ui_list->set_data($comments);
		
		$ui_list->set_fields(
			array(
    			'name'=>'id',
    			'label'=>'ID',
    			'align'=>'center',
				'width'=>'30',
			    'order_by'=>'id'
			),
			array(
    			'name'=>'article_html',
    			'label'=>'关联文章',
    			'align'=>'left'
    		),
    		array(
    			'name'=>'body_html',
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
    			'name'=>'create_time',
    			'label'=>'发布时间',
    			'align'=>'center',
    		    'width'=>'120',
    		    'order_by'=>'create_time'
    		),
    		array(
    			'name'=>'address',
    			'label'=>'地理位置',
    			'align'=>'center',
    		    'width'=>'120'
    		)
		);

		$ui_list->set_pagination($this->get('pagination'));
		$ui_list->order_by($this->get('order_by'), $this->get('order_by_dir'));
		$ui_list->display();
	}	

}
?>