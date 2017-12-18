<?php
namespace app\cms\service;

use system\be;

class article_manage extends \system\service
{

    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_articles($conditions = array())
    {
        $table_article = be::get_table('cms.article');

        $where = $this->create_article_where($conditions);
        $table_article->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_article->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'rank';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_article->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_article->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_article->limit($conditions['limit']);

        return $table_article->get_objects();
    }

    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_article_count($conditions = array())
    {
        return be::get_table('cms.article')
            ->where($this->create_article_where($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_article_where($conditions = [])
    {
        $where = [];

        if (isset($conditions['category_id']) && $conditions['category_id'] != -1) {
            if ($conditions['category_id'] == 0)
                $where[] = ['category_id', 0];
            elseif ($conditions['category_id'] > 0) {
                $service_article = be::get_service('article');
                $ids = $service_article->get_sub_category_ids($conditions['category_id']);
                if (count($ids) > 0) {
                    $ids[] = $conditions['category_id'];
                    $where[] = ['category_id', 'in', $ids];
                } else {
                    $where[] = ['category_id', $conditions['category_id']];
                }
            }
        }

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }

    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('cms.article');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    public function block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('cms.article');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    public function delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $article_comment_ids = be::get_table('article_comment')->where('article_id', $id)->get_array('id');
                if (count($article_comment_ids)) {
                    $table = be::get_table('article_vote_log');
                    if (!$table->where('comment_id', 'in', $article_comment_ids)->delete()) {
                        throw new \exception($table->get_error());
                    }

                    $table = be::get_table('article_vote_log');
                    if (!$table->where('article_id', $id)->delete()) {
                        throw new \exception($table->get_error());
                    }

                    $table = be::get_table('article_comment');
                    if (!$table->where('article_id', $id)->delete()) {
                        throw new \exception($table->get_error());
                    }
                }

                $row_article = be::get_row('cms.article');
                $row_article->load($id);

                if ($row_article->thumbnail_l != '') $files[] = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . $row_article->thumbnail_l;
                if ($row_article->thumbnail_m != '') $files[] = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . $row_article->thumbnail_m;
                if ($row_article->thumbnail_s != '') $files[] = PATH_DATA . DS . 'cms' . DS . 'article' . DS . 'thumbnail' . DS . $row_article->thumbnail_s;

                if (!$row_article->delete()) {
                    throw new \exception($row_article->get_error());
                }
            }

            foreach ($files as $file) {
                @unlink($file);
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 获取文章评论列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_comments($conditions = array())
    {
        $table_article_comment = be::get_table('article_comment');
        $table_article_comment->where($this->create_comment_where($conditions));

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_article_comment->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'create_time';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_article_comment->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_article_comment->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_article_comment->limit($conditions['limit']);

        return $table_article_comment->get_objects();
    }

    /**
     * 获取文章评论总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_comment_count($conditions = array())
    {
        return be::get_table('article_comment')
            ->where($this->create_comment_where($conditions))
            ->count();
    }

    /**
     * 生成评论条件where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_comment_where($conditions = [])
    {
        $where = [];
        $where[] = ['block', 0];

        if (isset($conditions['article_id']) && is_numeric($conditions['article_id']) && $conditions['article_id'] > 0) {
            $where[] = ['article_id', $conditions['article_id']];
        }

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '\'%' . $conditions['key'] . '%\''];
        }

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }


    public function comments_unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('article_comment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    public function comments_block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('article_comment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    public function comments_delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('article_comment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->delete()
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * 获取分类列表
     */
    public function get_categories()
    {
        return be::get_table('article_category')->order_by('rank', 'asc')->get_objects();
    }


    /**
     * 删除分类
     * @param int $category_id 分类编号
     * @return bool
     */
    public function delete_category($category_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('cms.article');
            if (!$table->where('category_id', $category_id)->update(['category_id' => 0])) {
                throw new \exception($table->get_error());
            }

            $table = be::get_table('article_category');
            if (!$table->where('parent_id', $category_id)->update(['parent_id' => 0])) {
                throw new \exception($table->get_error());
            }

            $row = be::get_row('article_category');
            if (!$row->delete($category_id)) {
                throw new \exception($row->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

}
