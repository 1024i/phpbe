<?php
namespace app;

class article extends \system\app
{

	public function __construct()
	{
		parent::__construct(1, '文章', '1.0', 'template/article/images/article.gif');
	}

	// 新建前台菜单时可用的链接项
    public function get_menus()
    {
		return array(
			array(
				'name'=>'文章首页',
				'url'=>'controller=article&task=home'
			),
			array(
				'name'=>'文章列表页面',
				'url'=>'controller=article&task=articles'
			),
			array(
				'name'=>'指定分类文章列表页面',
				'url'=>'controller=article&task=articles&category_id={ID}'
			),
			array(
				'name'=>'指定的一篇文章',
				'url'=>'controller=article&task=detail&article_id={ID}'
			)
		);
    }

    public function get_admin_menus()
	{
		return array(
			array(
				'name'=>'文章列表',
				'url'=>'./?controller=article&task=articles',
				'icon'=>'template/article/images/articles.png'
			),
			array(
				'name'=>'分类管理',
				'url'=>'./?controller=article&task=categories',
				'icon'=>'template/article/images/category.gif'
			),
			array(
				'name'=>'评论',
				'url'=>'./?controller=article&task=comments',
				'icon'=>'template/article/images/comments.png'
			),
			array(
				'name'=>'设置',
				'url'=>'./?controller=article&task=setting',
				'icon'=>'template/article/images/setting.png'
			)
		);
	}
	

	public function get_permissions()
	{
		return array(
			'home'=>array('查看文章系统首页','article.home'),
			'articles'=>array('查看文章列表','article.articles'),
			'detail'=>array('查看文章详情','article.detail'),

			'vote'=>array(
				'对文章内容表态（喜欢/不喜欢）',
				array(
					'article.ajax_like',
					'article.ajax_dislike'
				)
			),

			'comment'=>array('发表评论','article.ajax_comment'),

			'comment_vote'=>array(
				'对评论内容表态（顶/踩）',
				array(
					'article.ajax_comment_like',
					'article.ajax_comment_dislike'
				)
			),

			'user'=>array('作者资料', 'article.user')
		);
	}



	public function get_admin_permissions()
	{
		return array(
			'-'=>array(
				'不检查权限',
				array(
					'article.ajax_get_summary',
					'article.ajax_get_meta_keywords',
					'article.ajax_get_meta_description'
				)
			),

			'articles'=>array('查看文章列表', 'article.articles'),

			'edit'=>array(
				'添加/修改文章',
				array(
					'article.edit',
					'article.edit_save',
					'article.unblock',
					'article.block'
				)
			),

			'delete'=>array('删除文章', 'article.delete'),

			'categories'=>array(
				'管理文章分类',
				array(
					'article.categories',
					'article.save_categories',
					'article.ajax_delete_category'
				)
			),

            'comments'=>array(
				'管理用户评论',
				array(
					'article.comments',
					'article.comments_unblock',
					'article.comments_block',
					'article.comments_delete'
				)
			),

			'setting'=>array(
				'设置文章系统参数',
				array(
					'article.setting',
					'article.setting_save'
				)
			)
		);
	}

}
