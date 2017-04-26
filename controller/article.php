<?php
namespace controller;

use \system\be;
use \system\request;
use \system\session;

class article extends \system\controller
{

	public function home()
	{

		/*
		$users = \system\db::get_objects("SELECT create_time FROM be_article WHERE ( ( ( ( category_id = '40' OR category_id = '42' ) OR category_id = '44' ) OR create_time > '1396694808' ) AND home = '0' ) ORDER BY create_time ASC");
		print_r($users);
		echo '<br />';
		exit;
		*/

		$table = be::get_table('article');
		$table->where('a', 1);


		$table->where('(');
		$table->where('category_id', 44);
		$table->where('OR');
		$table->where('create_time', '>', 1396694808);
		$table->where(')');

		$table->where(array(
			'(',
				'(',
					array('category_id', 40),
					'OR',
					array('category_id', 41),
				')',
				'OR',
				array('category_id', 42),
				'OR',
				'(',
					array('category_id', 43),
					'OR',
					array('category_id', 44),
				')',
			')',
			'OR',
			array('create_time', '>', 1396694808),
		));

		$table->where(array(
			array(
				array(
					array(
						array('category_id', 40),
						'OR',
						array('category_id', 41)
					),
					'OR',
					array('category_id', 42)
				),
				'OR',
				array('category_id', 44)
			),
			'OR',
			array('create_time', '>', 1396694808),
		));

		echo $table->get_last_sql();
		exit;

		$where = array();
		$where[] = array(
			'(',
				array('category_id', '=', 44),
				'OR',
				'(',
					array('category_id', '=', 41),
					'OR',
					array('category_id', '=', 42),
				')',
			')',
			'OR',
			array('create_time', '>', 1396694808),
		);

		$where[] = array('home', 0);


		$table->where($where);
/*
		$table->where(array(
			array('category_id', 44),
			'OR',
			array('create_time', '>', 1396694808),
		));
*/

		$table->order_by('create_time', 'ASC');

		//$table->limit(1);
		//$table->cache(1000);


		$users = $table->get_results('create_time');
		throw new \ErrorException('test');

		echo $table->get_error();
		print_r($users);
		echo '<br />';
		echo $table->get_last_sql();
		exit;

		$model_article = be::get_model('article');

		// 最新带图文章
		$latest_thumbnail_articles = $model_article->get_articles(array('thumbnail'=>1, 'order_by'=>'create_time', 'order_by_dir'=>'DESC','limit'=>6));

		$active_users = $model_article->get_active_users();
		// 本月热点
		$month_hottest_articles = $model_article->get_articles(array('order_by'=>'hits', 'order_by_dir'=>'DESC','from_time'=>time()-86400*30,'limit'=>6));
		// 推荐文章
		$top_articles = $model_article->get_articles(array('top'=>1, 'order_by'=>'top', 'order_by_dir'=>'DESC','limit'=>6));

		$top_categories = array();
		$categories = $model_article->get_categories();
		foreach ($categories as $category) {
			if ($category->parent_id>0) continue;
			$top_categories[] = $category;

			$category->articles = $model_article->get_articles(array('category_id'=>$category->id, 'order_by'=>'create_time', 'order_by_dir'=>'DESC','limit'=>6));
		}

		$template = be::get_template('article.home');
		
		$config_system = be::get_config('system');
		$template->set_title($config_system->home_title);
		$template->set_meta_keywords($config_system->home_meta_keywords);
		$template->set_meta_description($config_system->home_meta_description);

		$template->set('latest_thumbnail_articles', $latest_thumbnail_articles);

		$template->set('active_users', $active_users);
		$template->set('month_hottest_articles', $month_hottest_articles);
		$template->set('top_articles', $top_articles);

		$template->set('categories', $top_categories);

		$template->display();
	}

	public function articles()
	{

		$table = be::get_table();
		$table->table('be_article');
		$table->where('category_id', 44);
		$table->where('home', 0);
		$table->where('create_time', '>', 1396694808);
		$table->cache(10000);


		print_r($table->parse_sql());

		$users = $table->select();
		print_r($users);
		exit;

		$config_article = be::get_config('article');

		$template = be::get_template('article.articles');

		$category_id = request::get('category_id', 0, 'int');
		$template->set('category_id', $category_id);
		
		$row_article_category = be::get_row('article_category');
		$row_article_category->cache($config_article->cache_expire);
		$row_article_category->load($category_id);

		if ($row_article_category->id == 0) be_exit('文章分类不存在！');

		$template->set_title($row_article_category->name);
		$template->set('category', $row_article_category);

		if ($row_article_category->parent_id>0) {
			$parent_category = null;
			$tmp_category = $row_article_category;
			while($tmp_category->parent_id>0)
			{
				$parent_id = $tmp_category->parent_id;
				$tmp_category = be::get_row('article_category');
				$tmp_category->load($parent_id);
			}
			$parent_category = $tmp_category;
			$template->set('parent_category', $parent_category);

			$north_menu = be::get_menu('north');
			$north_menu_tree = $north_menu->get_menu_tree();
			if (count($north_menu_tree)) {
				$menu_exist = false;
				foreach ($north_menu_tree as $menu) {
					if (
						array_key_exists('controller', $menu->params) && $menu->params['controller'] == 'article' &&
						array_key_exists('task', $menu->params) && $menu->params['task'] == 'listing' &&
						array_key_exists('category_id', $menu->params) && $menu->params['category_id'] == $parent_category->id
					)
					{
						$template->set('menu_id', $menu->id);
						break;
					}
				}
			}
		} else {
			$template->set('parent_category', $row_article_category);
		}

		$model_article = be::get_model('article');

		$option = array('category_id'=>$category_id);

		$limit = 10;
		$pagination = be::get_ui('pagination');
		$pagination->set_limit($limit);
		$pagination->set_total($model_article->get_article_count($option));
		$pagination->set_page(request::get('page', 1, 'int'));
		$pagination->set_url('controller=article&task=articles&category_id='.$category_id);
		$template->set('pagination', $pagination);

		$option['offset'] = $pagination->get_offset();
		$option['limit'] = $limit;
		$option['order_by_string'] = '`top` DESC, `rank` DESC, `create_time` DESC';

		$articles = $model_article->get_articles($option);
		$template->set('articles',  $articles);
		
		// 热门文章
		$hottest_articles = $model_article->get_articles(array('category_id'=>$category_id, 'order_by'=>'hits', 'order_by_dir'=>'DESC','limit'=>10));
		$template->set('hottest_articles', $hottest_articles);

		// 推荐文章
		$top_articles = $model_article->get_articles(array('category_id'=>$category_id, 'top'=>1, 'order_by'=>'top', 'order_by_dir'=>'DESC','limit'=>10));
		$template->set('top_articles', $top_articles);
		
		$template->display();
	}


	public function detail()
	{
		$config_article = be::get_config('article');

		$article_id = request::get('article_id', 0 ,'int');
		if ($article_id == 0) be_exit('参数(article_id)缺失！');
		
		$row_article = be::get_row('article');
		$row_article->cache($config_article->cache_expire);
		$row_article->load($article_id);
		$row_article->increment('hit', 1); // 点击量加 1

		$model_article = be::get_model('article');

		$similar_articles = $model_article->get_similar_articles($row_article, 10);

		// 热门文章
		$hottest_articles = $model_article->get_articles(array('category_id'=>$row_article->category_id, 'order_by'=>'hits', 'order_by_dir'=>'DESC','limit'=>10));
		
		// 推荐文章
		$top_articles = $model_article->get_articles(array('category_id'=>$row_article->category_id, 'top'=>1, 'order_by'=>'top', 'order_by_dir'=>'DESC','limit'=>10));

		$comments = $model_article->get_comments(array('article_id'=>$article_id));


		$template = be::get_template('article.detail');
		$template->set_title($row_article->title);
		$template->set_meta_keywords($row_article->meta_keywords);
		$template->set_meta_description($row_article->meta_description);

		$north_menu = be::get_menu('north');
		$north_menu_tree = $north_menu->get_menu_tree();
		if (count($north_menu_tree)) {
			$menu_exist = false;
			foreach ($north_menu_tree as $menu) {
				if (
					array_key_exists('controller', $menu->params) && $menu->params['controller'] == 'article' &&
					array_key_exists('task', $menu->params) && $menu->params['task'] == 'detail' &&
					array_key_exists('article_id', $menu->params) && $menu->params['article_id'] == $article_id
				)
				{
					$template->set('menu_id', $menu->id);
					if ($menu->home == 1) $template->set('home', 1);
					$menu_exist = true;
					break;
				}
			}
			
			if (!$menu_exist) {
				foreach ($north_menu_tree as $menu) {
					if (
						array_key_exists('controller', $menu->params) && $menu->params['controller'] == 'article' &&
						array_key_exists('task', $menu->params) && $menu->params['task'] == 'listing' &&
						array_key_exists('category_id', $menu->params) && $menu->params['category_id'] == $row_article->category_id
					)
					{
						$template->set('menu_id', $menu->id);
						$menu_exist = true;
						break;
					}
				}
			}
		}

		$template->set('article', $row_article);
		$template->set('similar_articles', $similar_articles);
		$template->set('hottest_articles', $hottest_articles);
		$template->set('top_articles', $top_articles);
		$template->set('comments', $comments);

		$template->display();
	}



	// 喜欢
	public function ajax_like()
	{
		$my = be::get_user();
		if ($my->id == 0) {
			$this->set('error', 1);
			$this->set('message', '请先登陆！');
			$this->ajax();
		}

		$article_id = request::get('article_id', 0, 'int');
		if ($article_id == 0) {
			$this->set('error', 2);
			$this->set('message', '参数(article_id)缺失！');
			$this->ajax();
		}

		$row_article = be::get_row('article');

		$row_article->load($article_id);
		if ($row_article->id == 0 || $row_article->block == 1) {
			$this->set('error', 3);
			$this->set('message', '文章不存在！');
			$this->ajax();
		}

		$row_article_vote_log = be::get_row('article_vote_log');
		$row_article_vote_log->load(array('article_id'=>$article_id, 'user_id'=>$my->id));
		if ($row_article_vote_log->id>0) {
			$this->set('error', 4);
			$this->set('message', '您已经表过态啦！');
			$this->ajax();
		}
		$row_article_vote_log->article_id = $article_id;
		$row_article_vote_log->user_id = $my->id;
		$row_article_vote_log->save();

		$model_article = be::get_model('article');
		$model_article->like($article_id);

		$this->set('error', 0);
		$this->set('message', '提交成功！');
		$this->ajax();
	}

	// 不喜欢
	public function ajax_dislike()
	{
		$my = be::get_user();
		if ($my->id == 0) {
			$this->set('error', 1);
			$this->set('message', '请先登陆！');
			$this->ajax();
		}

		$article_id = request::get('article_id', 0, 'int');
		if ($article_id == 0) {
			$this->set('error', 2);
			$this->set('message', '参数(article_id)缺失！');
			$this->ajax();
		}

		$row_article = be::get_row('article');
		$row_article->load($article_id);
		if ($row_article->id == 0 || $row_article->block == 1) {
			$this->set('error', 3);
			$this->set('message', '文章不存在！');
			$this->ajax();
		}

		$row_article_vote_log = be::get_row('article_vote_log');
		$row_article_vote_log->load(array('article_id'=>$article_id, 'user_id'=>$my->id));
		if ($row_article_vote_log->id>0) {
			$this->set('error', 4);
			$this->set('message', '您已经表过态啦！');
			$this->ajax();
		}
		$row_article_vote_log->article_id = $article_id;
		$row_article_vote_log->user_id = $my->id;
		$row_article_vote_log->save();

		$model_article = be::get_model('article');
		$model_article->dislike($article_id);

		$this->set('error', 0);
		$this->set('message', '提交成功！');
		$this->ajax();
	}


	public function ajax_comment()
	{
		$my = be::get_user();
		if ($my->id == 0) {
			$this->set('error', 1);
			$this->set('message', '请先登陆！');
			$this->ajax();
		}

		$article_id = request::post('article_id', 0, 'int');
		if ($article_id == 0) {
			$this->set('error', 2);
			$this->set('message', '参数(article_id)缺失！');
			$this->ajax();
		}

		$row_article = be::get_row('article');
		$row_article->load($article_id);
		if ($row_article->id == 0 || $row_article->block == 1) {
			$this->set('error', 3);
			$this->set('message', '文章不存在！');
			$this->ajax();
		}

		$body = request::post('body', '');
		$body = trim($body);
		$body_length = strlen($body);
		if ($body_length == 0) {
			$this->set('error', 5);
			$this->set('message', '请输入评论内容！');
			$this->ajax();
		}

		if ($body_length>2000) {
			$this->set('error', 6);
			$this->set('message', '评论内容过长！');
			$this->ajax();
		}

		$row_article_comment = be::get_row('article_comment');
		$row_article_comment->article_id = $article_id;
		$row_article_comment->user_id = $my->id;
		$row_article_comment->user_name = $my->name;
		$row_article_comment->body = $body;
		$row_article_comment->ip = $_SERVER['REMOTE_ADDR'];
		$row_article_comment->create_time = time();

		$config_article = be::get_config('article');
		$row_article_comment->block = ($config_article->comment_public == 1?0:1);

		$row_article_comment->save();

		$this->set('error', 0);
		$this->set('message', '提交成功！');
		$this->ajax();
	}

	// 顶
	public function ajax_comment_like()
	{
		$my = be::get_user();
		if ($my->id == 0) {
			$this->set('error', 1);
			$this->set('message', '请先登陆！');
			$this->ajax();
		}

		$comment_id = request::get('comment_id', 0, 'int');
		if ($comment_id == 0) {
			$this->set('error', 2);
            $this->set('message', '参数(comment_id)缺失！');
			$this->ajax();
		}

		$row_article_comment = be::get_row('article_comment');
		$row_article_comment->load($comment_id);
		if ($row_article_comment->id == 0 || $row_article_comment->block == 1) {
			$this->set('error', 3);
			$this->set('message', '评论不存在！');
			$this->ajax();
		}

		$row_article_vote_log = be::get_row('article_vote_log');
		$row_article_vote_log->load(array('comment_id'=>$comment_id, 'user_id'=>$my->id));
		if ($row_article_vote_log->id>0) {
			$this->set('error', 4);
			$this->set('message', '您已经表过态啦！');
			$this->ajax();
		}
		$row_article_vote_log->comment_id = $comment_id;
		$row_article_vote_log->user_id = $my->id;
		$row_article_vote_log->save();

		$model_article = be::get_model('article');
		$model_article->comment_like($comment_id);

		$this->set('error', 0);
		$this->set('message', '提交成功！');
		$this->ajax();
	}

	// 踩
	public function ajax_comment_dislike()
	{
		$my = be::get_user();
		if ($my->id == 0) {
			$this->set('error', 1);
			$this->set('message', '请先登陆！');
			$this->ajax();
		}

		$comment_id = request::get('comment_id', 0, 'int');
		if ($comment_id == 0) {
			$this->set('error', 2);
			$this->set('message', '参数(comment_id)缺失！');
			$this->ajax();
		}

		$row_article_comment = be::get_row('article_comment');
		$row_article_comment->load($comment_id);
		if ($row_article_comment->id == 0 || $row_article_comment->block == 1) {
			$this->set('error', 3);
			$this->set('message', '评论不存在！');
			$this->ajax();
		}

		$row_article_vote_log = be::get_row('article_vote_log');
		$row_article_vote_log->load(array('comment_id'=>$comment_id, 'user_id'=>$my->id));
		if ($row_article_vote_log->id>0) {
			$this->set('error', 4);
			$this->set('message', '您已经表过态啦！');
			$this->ajax();
		}
		$row_article_vote_log->comment_id = $comment_id;
		$row_article_vote_log->user_id = $my->id;
		$row_article_vote_log->save();


		$model_article = be::get_model('article');
		$model_article->comment_dislike($comment_id);

		$this->set('error', 0);
		$this->set('message', '提交成功！');
		$this->ajax();
	}

	public function user()
	{
		$user_id = request::get('user_id', 0, 'int');
		if ($user_id == 0) be_exit('参数(user_id)缺失！');

		$user = be::get_user($user_id);
		if ($user->block == 1) be_exit('该用户账号已被停用！');

		$model_article = be::get_model('article');

		$option = array('user_id'=>$user_id, 'order_by'=>'create_time', 'order_by_dir'=>'DESC','limit'=>30);
		$articles = $model_article->get_articles($option);
		$article_count = $model_article->get_article_count($option);

		$option = array('user_id'=>$user_id, 'order_by'=>'create_time', 'order_by_dir'=>'DESC','limit'=>30);
		$comments = $model_article->get_comments($option);
		foreach ($comments as $comment) {
			$row_article = be::get_row('article');
			$row_article->load($comment->article_id);
			$comment->article = $row_article;
		}
		$comment_count = $model_article->get_comment_count($option);

		$template = be::get_template('article.user');
		$template->set_title($user->name.' 的动态');
		$template->set_meta_keywords($user->name.' 的动态');
		$template->set_meta_description($user->name.' 的动态');
		$template->set('user', $user);
		$template->set('articles', $articles);
		$template->set('article_count', $article_count);
		$template->set('comments', $comments);
		$template->set('comment_count', $comment_count);
		$template->display();
	}

}
?>