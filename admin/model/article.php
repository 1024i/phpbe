<?php
namespace admin\model;

use \system\be;
use \system\db;

class article extends \system\model
{

    public function get_articles($option=array())
    {
		$sql = 'SELECT * FROM `be_article` WHERE 1'.$this->create_article_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = 'rank';
			$order_by_dir = 'DESC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
    }

    
    public function get_article_count($option=array())
    {
        $sql = 'SELECT COUNT(*) FROM `be_article` WHERE 1'. $this->create_article_sql($option);
        return db::get_result($sql);
    }

	private function create_article_sql($option=array())
	{
		$sql = '';

		if (array_key_exists('category_id', $option) && $option['category_id']!=-1) {
			if ($option['category_id'] == 0)
				$sql .= ' AND `category_id`=0';
			elseif ($option['category_id'] > 0) {
				$model_article = be::get_model('article');
				$ids = $model_article->get_sub_category_ids($option['category_id']);
				if (count($ids) > 0) {
					$ids[] = $option['category_id'];
					$sql .= ' AND `category_id` IN(' . implode(',', $ids) . ')';
				}
				else
					$sql .= ' AND `category_id`=' . $option['category_id'];
			}
		}
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `title` LIKE \'%' . $option['key'] . '%\'';
		if (array_key_exists('status', $option) && $option['status']!=-1) $sql .= ' AND `block`='.$option['status'];

		return $sql;
	}


    public function unblock($ids)
    {
        if (!db::execute('UPDATE `be_article` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function block($ids)
    {
        if (!db::execute('UPDATE `be_article` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function delete($ids)
    {
		$array = explode(',', $ids);
		foreach ($array as $id) {
			db::execute('DELETE FROM `be_article_vote_log` WHERE `comment_id` IN(SELECT `id` FROM `be_article_comment` WHERE `article_id`='.$id.')');
			db::execute('DELETE FROM `be_article_vote_log` WHERE `article_id`='.$id);
			db::execute('DELETE FROM `be_article_comment` WHERE `article_id`='.$id);

            $row_article = be::get_row('article');
            $row_article->load($id);

			if ($row_article->thumbnail_l!='') @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.$row_article->thumbnail_l);
			if ($row_article->thumbnail_m!='') @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.$row_article->thumbnail_m);
			if ($row_article->thumbnail_s!='') @unlink(PATH_DATA.DS.'article'.DS.'thumbnail'.DS.$row_article->thumbnail_s);

            $row_article->delete();
        }
        return true;
    }


    public function get_comments($option=array())
    {
		$sql = 'SELECT * FROM `be_article_comment` WHERE 1'.$this->create_comment_sql($option);

		if (array_key_exists('order_by_string', $option) && $option['order_by_string']) {
			$sql .= ' ORDER BY '.$option['order_by_string'];
		} else {
			$order_by = 'create_time';
			$order_by_dir = 'DESC';

			if (array_key_exists('order_by', $option) && $option['order_by']) $order_by = $option['order_by'];
			if (array_key_exists('order_by_dir', $option) && $option['order_by_dir']) $order_by_dir = $option['order_by_dir'];
			$sql .= ' ORDER BY '.$order_by.' '.$order_by_dir;
		}

		$offset = 0;
		$limit = 0;
		if (array_key_exists('offset', $option) && $option['offset']) $offset = $option['offset'];
		if (array_key_exists('limit', $option) && $option['limit']) $limit = $option['limit'];

        return db::get_objects($sql, $offset, $limit);
    }

    
    public function get_comment_count($option=array())
    {
        $sql = 'SELECT COUNT(*) FROM `be_article_comment` WHERE 1'. $this->create_comment_sql($option);
        return db::get_result($sql);
    }

	private function create_comment_sql($option=array())
	{
		$sql = '';

		if (array_key_exists('article_id', $option) && $option['article_id']>0) $sql .= ' AND `article_id`=' . $option['article_id'];
		if (array_key_exists('key', $option) && $option['key']) $sql .= ' AND `title` LIKE \'%' . $option['key'] . '%\'';
		if (array_key_exists('status', $option) && $option['status']!=-1) $sql .= ' AND `block`='.$option['status'];

		return $sql;
	}


    public function comments_unblock($ids)
    {
        if (!db::execute('UPDATE `be_article_comment` SET `block`=0 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function comments_block($ids)
    {
        if (!db::execute('UPDATE `be_article_comment` SET `block`=1 WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }

    public function comments_delete($ids)
    {
        if (!db::execute('DELETE FROM `be_article_comment` WHERE `id` IN(' . $ids . ')')) {
            $this->set_error(db::get_error());
            return false;
        }
        return true;
    }


    /**
     * 获取分类列表
     */
	public function get_categories()
	{
		return db::get_objects('SELECT * FROM `be_article_category` ORDER BY `rank` ASC');
	}
    
	
	/**
	 * 删除分类
	 * @param int $category_id 分类编号
	 */
	public function delete_category($category_id)
	{
		db::execute('UPDATE `be_article` SET `category_id`=0 WHERE `category_id`='.$category_id);
		db::execute('UPDATE `be_article_category` SET `parent_id`=0 WHERE `parent_id`='.$category_id);
		db::execute('DELETE FROM `be_article_category` WHERE `id`='.$category_id);

		return true;
	}

}
?>