<?php
namespace app;

class article extends \system\app
{

	public function __construct()
	{
		parent::__construct(1, '文章', '1.0', 'template/article/images/article.gif');
	}

	// 新建前台菜单时可用的链接项
    public function getMenus()
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
				'url'=>'controller=article&task=articles&categoryId={ID}'
			),
			array(
				'name'=>'指定的一篇文章',
				'url'=>'controller=article&task=detail&articleId={ID}'
			)
		);
    }

    public function getAdminMenus()
	{
		return array(
			array(
				'name'=>'文章列表',
				'url'=>'./?app=Cms&controller=Article&task=articles',
				'icon'=>'template/article/images/articles.png'
			),
			array(
				'name'=>'分类管理',
				'url'=>'./?app=Cms&controller=Article&task=categories',
				'icon'=>'template/article/images/category.gif'
			),
			array(
				'name'=>'评论',
				'url'=>'./?app=Cms&controller=Article&task=comments',
				'icon'=>'template/article/images/comments.png'
			),
			array(
				'name'=>'设置',
				'url'=>'./?app=Cms&controller=Article&task=setting',
				'icon'=>'template/article/images/setting.png'
			)
		);
	}
	

	public function getPermissions()
	{
		return [
		    '查看文章系统首页' => [
                'article.home',
            ],
            '查看文章列表' => [
                'article.articles',
            ],
            '查看文章详情' => [
                'article.detail',
            ],
            '对文章内容表态（喜欢/不喜欢）' => [
                'article.ajaxLike',
                'article.ajaxDislike',
            ],
            '发表评论' => [
                'article.ajaxComment',
            ],
            '对评论内容表态（顶/踩）' => [
                'article.ajaxCommentLike',
                'article.ajaxCommentDislike',
            ],
            '作者资料' => [
                'article.user',
            ],
		];
	}


	public function getAdminPermissions()
	{
		return [
			'-' => [
                'article.ajaxGetSummary',
                'article.ajaxGetMetaKeywords',
                'article.ajaxGetMetaDescription',
			],
            '查看文章列表' => [
                'article.articles',
            ],
            '添加/修改文章' => [
                'article.edit',
                'article.editSave',
                'article.unblock',
                'article.block',
            ],
            '删除文章' => [
                'article.delete',
            ],
            '管理文章分类' => [
                'article.categories',
                'article.saveCategories',
                'article.ajaxDeleteCategory',
            ],
            '管理用户评论' => [
                'article.comments',
                'article.commentsUnblock',
                'article.commentsBlock',
                'article.commentsDelete'
            ],
            '设置文章系统参数' => [
                'article.setting',
                'article.settingSave',
            ],
		];
	}

}
