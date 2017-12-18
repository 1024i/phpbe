<?php
namespace service;

use system\be;

class article extends \system\service
{

    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_articles($conditions = [])
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

        $table_article->cache(be::get_config('cms.article')->cache_expire);
        return $table_article->get_objects();
    }


    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_article_count($conditions = [])
    {
        return be::get_table('cms.article')
            ->where($this->create_article_where($conditions))
            ->cache(be::get_config('cms.article')->cache_expire)
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
        $where[] = ['block', 0];

        if (isset($conditions['category_id']) && $conditions['category_id'] != -1) {
            if ($conditions['category_id'] == 0)
                $where[] = ['category_id', 0];
            elseif ($conditions['category_id'] > 0) {
                $ids = $this->get_sub_category_ids($conditions['category_id']);
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

        if (isset($conditions['thumbnail'])) {
            if ($conditions['thumbnail'] == 1) {
                $where[] = ['thumbnail_s', '!=', ''];
            } else {
                $where[] = ['thumbnail_s', '=', ''];
            }
        }

        if (isset($conditions['top'])) {
            if ($conditions['top'] == 0) {
                $where[] = ['top', '=', 0];
            } else {
                $where[] = ['top', '>', 0];
            }
        }

        if (isset($conditions['from_time']) && is_numeric($conditions['from_time'])) {
            $where[] = ['create_time', '>', $conditions['from_time']];
        }

        if (isset($conditions['user_id']) && is_numeric($conditions['user_id'])) {
            $where[] = ['create_by_id', '>', $conditions['user_id']];
        }

        return $where;
    }


    /**
     * 获取相似文章
     *
     * @param \system\row | mixed $row_article 当前文章
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    public function get_similar_articles($row_article, $n)
    {
        $similar_articles = [];

        // 按关键词查找类似文章
        if ($row_article->meta_keywords != '') {
            $keywords = explode(' ', $row_article->meta_keywords);
            $similar_articles = $this->_get_similar_articles($row_article, $keywords, $n);
        }

        if (count($similar_articles) > 0) return $similar_articles;

        if ($row_article->title != '') {
            $lib_scws = be::get_lib('scws');
            $lib_scws->send_text($row_article->title);
            $scws_keywords = $lib_scws->get_tops(3);
            $keywords = [];
            if ($scws_keywords !== false) {
                foreach ($scws_keywords as $scws_keyword) {
                    $keywords[] = $scws_keyword['word'];
                }
            }

            $similar_articles = $this->_get_similar_articles($row_article, $keywords, $n);
        }

        return $similar_articles;
    }

    /**
     * 获取相似文章
     *
     * @param \system\row | mixed $row_article 当前文章
     * @param array $keywords 关键词
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    private function _get_similar_articles($row_article, $keywords, $n)
    {
        $similar_articles = [];

        $config_article = be::get_config('cms.article');
        $keywords_count = count($keywords);
        if ($keywords_count > 0) {
            $table_article = be::get_table('cms.article');
            $table_article->where('id', '!=', $row_article->id);
            $table_article->where('(');
            for ($i = 0; $i < $keywords_count; $i++) {
                $table_article->where('title', 'like', '%' . $keywords[$i] . '%');
                if ($i < ($keywords_count - 1)) $table_article->where('OR');
            }
            $table_article->where(')');
            $table_article->order_by('hits DESC, create_time DESC');
            $table_article->limit($n);
            $table_article->cache($config_article->cache_expire);
            $similar_articles = $table_article->get_objects();

            if (count($similar_articles) == 0) {
                $table_article->init();
                $table_article->where('id', '!=', $row_article->id);
                $table_article->where('(');
                for ($i = 0; $i < $keywords_count; $i++) {
                    $table_article->where('body', 'like', '%' . $keywords[$i] . '%');
                    if ($i < ($keywords_count - 1)) $table_article->where('OR');
                }
                $table_article->where(')');
                $table_article->order_by('hits DESC, create_time DESC');
                $table_article->limit($n);
                $table_article->cache($config_article->cache_expire);
                $similar_articles = $table_article->get_objects();
            }
        }

        return $similar_articles;
    }

    /**
     * 获取评论列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_comments($conditions = [])
    {
        $table_article_comment= be::get_table('cms.article_comment');

        $where = $this->create_comment_where($conditions);
        $table_article_comment->where($where);

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

        $table_article_comment->cache(be::get_config('cms.article')->cache_expire);

        return $table_article_comment->get_objects();
    }

    /**
     * 获取评论总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_comment_count($conditions = [])
    {
        return be::get_table('cms.article_comment')
            ->where($this->create_comment_where($conditions))
            ->cache(be::get_config('cms.article')->cache_expire)
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

        if (isset($conditions['user_id']) && is_numeric($conditions['user_id'])) {
            $where[] = ['user_id', $conditions['user_id']];
        }

        return $where;
    }

    /**
     * 活跃会员, 即参与评论最多的会员
     *
     * @param int $limit 获取多少个
     * @return array 用户对象数组
     */
    public function get_active_users($limit = 10)
    {
        $user_ids = be::get_table('cms.article_comment')
            ->group_by('user_id')
            ->order_by('COUNT(*) DESC')
            ->limit($limit)
            ->cache(be::get_config('cms.article')->cache_expire)
            ->get_values('user_id');

        $active_users = [];
        foreach ($user_ids as $user_id) {
            $active_users[] = be::get_user($user_id);
        }

        return $active_users;
    }

    private $categories = null;
    private $category_tree = null;

    /**
     * 获取分类列表
     *
     * @return array|null
     */
    public function get_categories()
    {
        if ($this->categories === null) {
            $this->categories = $this->_create_categories($this->get_category_tree());
        }
        return $this->categories;
    }

    /**
     * 获取分类总数
     *
     * @return int
     */
    public function get_category_count()
    {
        return be::get_table('cms.article_category')
            ->cache(be::get_config('cms.article')->cache_expire)
            ->count();
    }

    /**
     * 获取分类树
     *
     * @return array|null
     */
    public function get_category_tree()
    {
        if ($this->category_tree === null) {
            $categories = be::get_table('cms.article_category')
                ->cache(be::get_config('cms.article')->cache_expire)
                ->get_objects();

            $this->category_tree = $this->_create_category_tree($categories);
        }
        return $this->category_tree;
    }

    /**
     * 获取指定分类ID下的所有层级的子分类ID
     *
     * @param $category_id
     * @return array
     */
    public function get_sub_category_ids($category_id)
    {
        $categories = $this->get_categories();

        $ids = [];
        $level = 0;
        $start = false;
        foreach ($categories as $category) {
            if ($start) {
                if ($category->level > $level) {
                    $ids[] = $category->id;
                } else {
                    break;
                }
            } elseif ($category->id == $category_id) {
                $level = $category->level;
                $start = true;
            }
        }
        return $ids;
    }

    /**
     * 生成分类列表，按树结构格式化过
     *
     * @param array $category_tree 分类树
     * @param array $categories
     * @return array
     */
    private function _create_categories($category_tree = null, &$categories = [])
    {
        if (count($category_tree)) {
            foreach ($category_tree as $category) {
                $sub_category = null;
                if (isset($category->sub_category)) {
                    $sub_category = $category->sub_category;
                    unset($category->sub_category);
                }
                $categories[] = $category;

                if ($sub_category !== null) $this->_create_categories($sub_category, $categories);
            }
        }
        return $categories;
    }

    /**
     * 生成分类树
     *
     * @param array $categories
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    private function _create_category_tree(&$categories = null, $parent_id = 0, $level = 0)
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parent_id) {
                $category->level = $level;
                $sub_category = $this->_create_category_tree($categories, $category->id, $level + 1);
                if (count($sub_category)) $category->sub_category = $sub_category;
                $category->children = count($sub_category);
                $tree[] = $category;
            }
        }
        return $tree;
    }

    /**
     * 顶
     *
     * @param int $article_id 文章编号
     * @return bool
     */
    public function like($article_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $my = be::get_user();
            if ($my->id == 0) {
                throw new \exception('请先登陆！');
            }

            $row_article = be::get_row('cms.article');
            $row_article->load($article_id);
            if ($row_article->id == 0 || $row_article->block == 1) {
                throw new \exception('文章不存在！');
            }

            $row_article_vote_log = be::get_row('cms.article_vote_log');
            $row_article_vote_log->load(['article_id' => $article_id, 'user_id' => $my->id]);
            if ($row_article_vote_log->id > 0) {
                throw new \exception('您已经表过态啦！');
            }
            $row_article_vote_log->article_id = $article_id;
            $row_article_vote_log->user_id = $my->id;
            $row_article_vote_log->save();

            $row_article->increment('like', 1);

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 踩
     *
     * @param int $article_id 文章编号
     * @return bool
     */
    public function dislike($article_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $my = be::get_user();
            if ($my->id == 0) {
                throw new \exception('请先登陆！');
            }

            $row_article = be::get_row('cms.article');
            $row_article->load($article_id);
            if ($row_article->id == 0 || $row_article->block == 1) {
                throw new \exception('文章不存在！');
            }

            $row_article_vote_log = be::get_row('cms.article_vote_log');
            $row_article_vote_log->load(['article_id' => $article_id, 'user_id' => $my->id]);
            if ($row_article_vote_log->id > 0) {
                throw new \exception('您已经表过态啦！');
            }
            $row_article_vote_log->article_id = $article_id;
            $row_article_vote_log->user_id = $my->id;
            $row_article_vote_log->save();

            $row_article->increment('dislike', 1);

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 提交评论
     *
     * @param int $article_id 文章编号
     * @param string $comment_body 评论内容
     * @return bool
     */
    public function comment($article_id, $comment_body)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $my = be::get_user();
            if ($my->id == 0) {
                throw new \exception('请先登陆！');
            }

            $row_article = be::get_row('cms.article');
            $row_article->load($article_id);
            if ($row_article->id == 0 || $row_article->block == 1) {
                throw new \exception('文章不存在！');
            }

            $comment_body = trim($comment_body);
            $comment_body_length = strlen($comment_body);
            if ($comment_body_length == 0) {
                throw new \exception('请输入评论内容！');
            }

            if ($comment_body_length > 2000) {
                throw new \exception('评论内容过长！');
            }

            $row_article_comment = be::get_row('cms.article_comment');
            $row_article_comment->article_id = $article_id;
            $row_article_comment->user_id = $my->id;
            $row_article_comment->user_name = $my->name;
            $row_article_comment->body = $comment_body;
            $row_article_comment->ip = $_SERVER['REMOTE_ADDR'];
            $row_article_comment->create_time = time();

            $config_article = be::get_config('cms.article');
            $row_article_comment->block = ($config_article->comment_public == 1 ? 0 : 1);

            $row_article_comment->save();

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 顶
     *
     * @param int $comment_id 文章评论编号
     * @return bool
     */
    public function comment_like($comment_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $my = be::get_user();
            if ($my->id == 0) {
                throw new \exception('请先登陆！');
            }

            $row_article_comment = be::get_row('cms.article_comment');
            $row_article_comment->load($comment_id);
            if ($row_article_comment->id == 0 || $row_article_comment->block == 1) {
                throw new \exception('评论不存在！');
            }

            $row_article_vote_log = be::get_row('cms.article_vote_log');
            $row_article_vote_log->load(['comment_id' => $comment_id, 'user_id' => $my->id]);
            if ($row_article_vote_log->id > 0) {
                throw new \exception('您已经表过态啦！');
            }
            $row_article_vote_log->comment_id = $comment_id;
            $row_article_vote_log->user_id = $my->id;
            $row_article_vote_log->save();

            $row_article_comment->increment('like', 1);

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 踩
     *
     * @param int $comment_id 文章评论编号
     * @return bool
     */
    public function comment_dislike($comment_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $my = be::get_user();
            if ($my->id == 0) {
                throw new \exception('请先登陆！');
            }

            $row_article_comment = be::get_row('cms.article_comment');
            $row_article_comment->load($comment_id);
            if ($row_article_comment->id == 0 || $row_article_comment->block == 1) {
                throw new \exception('评论不存在！');
            }

            $row_article_vote_log = be::get_row('cms.article_vote_log');
            $row_article_vote_log->load(['comment_id' => $comment_id, 'user_id' => $my->id]);
            if ($row_article_vote_log->id > 0) {
                throw new \exception('您已经表过态啦！');
            }
            $row_article_vote_log->comment_id = $comment_id;
            $row_article_vote_log->user_id = $my->id;
            $row_article_vote_log->save();

            $row_article_comment->increment('dislike', 1);

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

}