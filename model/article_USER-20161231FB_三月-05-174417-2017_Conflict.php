<?php
namespace model;

use \system\cache;
use \system\db;
use \system\be;


class article extends \system\model
{

    public function get_articles($options=array())
    {
		$where = $this->create_article_where($options);
		$values = $where[1];

		$sql = 'SELECT * FROM be_article WHERE block=0'.$where[0];

		if (array_key_exists('order_by_string', $options) && $options['order_by_string']) {
			$sql .= ' ORDER BY '.$options['order_by_string'];
		} else {
			$order_by = 'rank';
			$order_by_dir = 'DESC';
			if (array_key_exists('order_by', $options) && $options['order_by']) $order_by = $options['order_by'];
			if (array_key_exists('order_by_dir', $options) && $options['order_by_dir']) $order_by_dir = $options['order_by_dir'];
			$sql .= ' ORDER BY ? ?';
			$values[] = $order_by;
			$values[] = $order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $options) && $options['offset']) $offset = intval($options['offset']);
		if (array_key_exists('limit', $options) && $options['limit']) $limit = intval($options['limit']);
		$sql .= ' LIMIT '.$offset.', '.$limit;

		$config_article = be::get_config('article');
		return db::get_objects($sql, $values, $config_article->cache_expire);
    }

    
    public function get_article_count($options=array())
    {
		$where = $this->create_article_where($options);
        $sql = 'SELECT COUNT(*) FROM be_article WHERE block=0'.$where[0];

		$config_article = be::get_config('article');
		return db::get_result($sql, $where[1], $config_article->cache_expire);
    }

	private function create_article_where($options=array())
	{
		$sql = '';
		$values = array();

		if (array_key_exists('category_id', $options) && $options['category_id']!=-1) {
			if ($options['category_id'] == 0)
				$sql .= ' AND category_id=0';
			elseif ($options['category_id'] > 0) {
				$ids = $this->get_sub_category_ids($options['category_id']);
				if (count($ids) > 0) {
					$ids[] = $options['category_id'];
					$sql .= ' AND category_id IN(' . implode(',', array_fill(0, count($ids), '?')) . ')';
					$values = array_merge($values, $ids);
				} else {
					$sql .= ' AND category_id=?';
					$values[] = $options['category_id'];
				}
			}
		}
		if (array_key_exists('key', $options) && $options['key']) {
			$sql .= ' AND title LIKE ?';
			$values[] = '%'.$options['key'].'%';
		}

		if (array_key_exists('thumbnail', $options)) {
			if ($options['thumbnail'] == 1) {
				$sql .= ' AND thumbnail_s!=\'\'';
			} else {
				$sql .= ' AND thumbnail_s == \'\'';
			}
		}

		if (array_key_exists('top', $options)) {
			if ($options['top'] == 0) {
				$sql .= ' AND top=0';
			} else {
				$sql .= ' AND top>0';
			}
		}

		if (array_key_exists('from_time', $options) && is_numeric($options['from_time'])) {
			$sql .= ' AND create_time>?';
			$values[] = $options['from_time'];
		}

		if (array_key_exists('user_id', $options) && is_numeric($options['user_id'])) {
			$sql .= ' AND create_by_id=?';
			$values[] = $options['user_id'];
		}

		return array($sql, $values);
	}



	// 相似文章
	public function get_similar_articles($row_article, $n = 0)
	{
		$config_article = be::get_config('article');

		$similar_articles = array();

		// 按关键词查找类似文章
		if ($row_article->meta_keywords!='') {
			$keywords = explode(' ', $row_article->meta_keywords);
			$formatted_keywords = array();
			foreach ($keywords as $keyword) {
				$formatted_keywords[] = '%'.$keyword.'%';
			}

			$sql = 'SELECT * FROM be_article WHERE id!='.$row_article->id.' AND (title LIKE '.implode(' OR title LIKE ', array_fill(0, count($keywords), '?')).') ORDER BY hits DESC, create_time DESC LIMIT '.$n;
			$similar_articles = db::get_objects($sql, $formatted_keywords, $config_article->cache_expire);

			if (count($similar_articles) == 0) {
				$sql = 'SELECT * FROM be_article WHERE id!='.$row_article->id.' AND (body LIKE '.implode(' OR body LIKE ', array_fill(0, count($keywords), '?')).') ORDER BY hits DESC, create_time DESC LIMIT '.$n;
				$similar_articles = db::get_objects($sql, $formatted_keywords, $config_article->cache_expire);
			}
		}

		if ($row_article->title!='') {
			$lib_scws = be::get_lib('scws');
			$lib_scws->send_text($row_article->title);
			$scws_keywords = $lib_scws->get_tops(3);
			$keywords = array();
			if ($scws_keywords! == false) {
				foreach ($scws_keywords as $scws_keyword) {
					$keywords[] = $scws_keyword['word'];
				}
			}

			if (count($keywords)>0) {
				$formatted_keywords = array();
				foreach ($keywords as $keyword) {
					$formatted_keywords[] = '%'.$keyword.'%';
				}

				$sql = 'SELECT * FROM be_article WHERE id!='.$row_article->id.' AND (title LIKE '.implode(' OR title LIKE ', array_fill(0, count($keywords), '?')).') ORDER BY hits DESC, create_time DESC LIMIT '.$n;
				$similar_articles = db::get_objects($sql, $formatted_keywords, $config_article->cache_expire);

				if (count($similar_articles) == 0) {
					$sql = 'SELECT * FROM be_article WHERE id!='.$row_article->id.' AND (body LIKE '.implode(' OR body LIKE ', array_fill(0, count($keywords), '?')).') ORDER BY hits DESC, create_time DESC LIMIT '.$n;
					$similar_articles = db::get_objects($sql, $formatted_keywords, $config_article->cache_expire);
				}
			}
		}

		return $similar_articles;
	}
	
    public function get_comments($options=array())
    {
		$where = $this->create_comment_where($options);
		$values = $where[1];

		$sql = 'SELECT * FROM be_article_comment WHERE block=0'.$where[0];

		if (array_key_exists('order_by_string', $options) && $options['order_by_string']) {
			$sql .= ' ORDER BY '.$options['order_by_string'];
		} else {
			$order_by = 'create_time';
			$order_by_dir = 'DESC';
			if (array_key_exists('order_by', $options) && $options['order_by']) $order_by = $options['order_by'];
			if (array_key_exists('order_by_dir', $options) && $options['order_by_dir']) $order_by_dir = $options['order_by_dir'];
			$sql .= ' ORDER BY ? ? ';
			$values[] = $order_by;
			$values[] = $order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $options) && $options['offset']) $offset = $options['offset'];
		if (array_key_exists('limit', $options) && $options['limit']) $limit = $options['limit'];
		$sql .= ' LIMIT '.$offset.', '.$limit;

		$config_article = be::get_config('article');
		$comments = db::get_objects($sql, $values, $config_article->cache_expire);
		return $comments;
    }

    
    public function get_comment_count($options=array())
    {
		$where = $this->create_comment_where($options);

        $sql = 'SELECT COUNT(*) FROM be_article_comment WHERE block=0'. $where[0];

		$config_article = be::get_config('article');
		$comment_count = db::get_result($sql, $where[1], $config_article->cache_expire);
		return $comment_count;
    }

	private function create_comment_where($options=array())
	{
		$sql = '';
		$values = array();

		if (array_key_exists('article_id', $options) && $options['article_id']>0) {
			$sql .= ' AND article_id=?';
			$values[] = $options['article_id'];
		}
		if (array_key_exists('user_id', $options) && is_numeric($options['user_id'])) {
			$sql .= ' AND user_id=?';
			$values[] = $options['user_id'];
		}

		return array($sql, $values);
	}

	// 活跃会员, 即参与评论最多的会员
	public function get_active_users($limit = 10)
	{
		$sql = 'SELECT user_id FROM be_article_comment GROUP BY user_id ORDER BY COUNT(*) DESC LIMIT '.$limit;

		$config_article = be::get_config('article');
		$objs = db::get_objects($sql, array(), $config_article->cache_expire);

		$active_users = array();
		foreach ($objs as $obj) {
			$active_users[] = be::get_user($obj->user_id);
		}

		return $active_users;
	}

    private $categories = null;
    private $category_tree = null;

    public function get_categories()
    {
        if ($this->categories === null) {
            $this->categories = $this->_create_categories($this->get_category_tree());
        }
        return $this->categories;
    }

    public function get_category_count()
    {
		$sql = 'SELECT COUNT(*) FROM be_article_category';

		$config_article = be::get_config('article');
		$category_count = db::get_result($sql, array(), $config_article->cache_expire);

        return $category_count;
    }

    public function get_category_tree()
    {
        if ($this->category_tree === null) {
			$sql = 'SELECT * FROM be_article_category';

			$config_article = be::get_config('article');
			$categories = db::get_objects($sql, array(), $config_article->cache_expire);

            $this->category_tree = $this->_create_categoy_tree($categories);
        }
        return $this->category_tree;
    }

    public function get_sub_category_ids($category_id)
    {
        $categories = $this->get_categories();
        
        $ids = array();
        $level = 0;
        $start = false;
        foreach ($categories as $category) {
            if ($start) {
                if ($category->level > $level) {
                    $ids[] = $category->id;
                } else {
                    break;
                }
            }
            elseif ($category->id == $category_id) {
                $level = $category->level;
                $start = true;
            }
        }
        return $ids;
    }
    
    

    private function _create_categories($category_tree = null, &$categories = array())
    {
        if (count($category_tree)) {
            foreach ($category_tree as $category) {
                $sub_category = null;
                if (isset($category->sub_category)) {
                    $sub_category = $category->sub_category;
                    unset($category->sub_category);
                }
                $categories[] = $category;
                
                if ($sub_category ! == null) $this->_create_categories($sub_category, $categories);
            }
        }
        return $categories;
    }

    private function _create_categoy_tree(&$categories = null, $parent_id = 0, $level = 0)
    {
        $tree = array();
        foreach ($categories as $category) {
            if ($category->parent_id == $parent_id) {
                $category->level = $level;
                $sub_category = $this->_create_categoy_tree($categories, $category->id, $level + 1);
                if (count($sub_category)) $category->sub_category = $sub_category;
                $category->children = count($sub_category);
                $tree[] = $category;
            }
        }
        return $tree;
    }


	public function like($article_id)
	{
		return be::get_row('article')->load($article_id)->increment('like', 1);
	}

	public function dislike($article_id)
	{
		return be::get_row('article')->load($article_id)->increment('dislike', 1);
	}


	public function comment_like($comment_id)
	{
		return be::get_row('article_comment')->load($comment_id)->increment('like', 1);
	}

	public function comment_dislike($comment_id)
	{
		return be::get_row('article_comment')->load($comment_id)->increment('dislike', 1);
	}

}
?>