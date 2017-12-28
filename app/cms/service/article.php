<?php
namespace service;

use System\Be;

class Article extends \System\Service
{

    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getArticles($conditions = [])
    {
        $tableArticle = Be::getTable('Cms.article');

        $where = $this->createArticleWhere($conditions);
        $tableArticle->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableArticle->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'ordering';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableArticle->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableArticle->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableArticle->limit($conditions['limit']);

        $tableArticle->cache(Be::getConfig('Cms.Article')->cacheExpire);
        return $tableArticle->getObjects();
    }


    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getArticleCount($conditions = [])
    {
        return Be::getTable('Cms.article')
            ->where($this->createArticleWhere($conditions))
            ->cache(Be::getConfig('Cms.Article')->cacheExpire)
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createArticleWhere($conditions = [])
    {
        $where = [];
        $where[] = ['block', 0];

        if (isset($conditions['categoryId']) && $conditions['categoryId'] != -1) {
            if ($conditions['categoryId'] == 0)
                $where[] = ['categoryId', 0];
            elseif ($conditions['categoryId'] > 0) {
                $ids = $this->getSubCategoryIds($conditions['categoryId']);
                if (count($ids) > 0) {
                    $ids[] = $conditions['categoryId'];
                    $where[] = ['categoryId', 'in', $ids];
                } else {
                    $where[] = ['categoryId', $conditions['categoryId']];
                }
            }
        }

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['thumbnail'])) {
            if ($conditions['thumbnail'] == 1) {
                $where[] = ['thumbnailS', '!=', ''];
            } else {
                $where[] = ['thumbnailS', '=', ''];
            }
        }

        if (isset($conditions['top'])) {
            if ($conditions['top'] == 0) {
                $where[] = ['top', '=', 0];
            } else {
                $where[] = ['top', '>', 0];
            }
        }

        if (isset($conditions['fromTime']) && is_numeric($conditions['fromTime'])) {
            $where[] = ['createTime', '>', $conditions['fromTime']];
        }

        if (isset($conditions['userId']) && is_numeric($conditions['userId'])) {
            $where[] = ['createById', '>', $conditions['userId']];
        }

        return $where;
    }


    /**
     * 获取相似文章
     *
     * @param \system\row | mixed $rowArticle 当前文章
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    public function getSimilarArticles($rowArticle, $n)
    {
        $similarArticles = [];

        // 按关键词查找类似文章
        if ($rowArticle->metaKeywords != '') {
            $keywords = explode(' ', $rowArticle->metaKeywords);
            $similarArticles = $this->_getSimilarArticles($rowArticle, $keywords, $n);
        }

        if (count($similarArticles) > 0) return $similarArticles;

        if ($rowArticle->title != '') {
            $libScws = Be::getLib('scws');
            $libScws->sendText($rowArticle->title);
            $scwsKeywords = $libScws->getTops(3);
            $keywords = [];
            if ($scwsKeywords !== false) {
                foreach ($scwsKeywords as $scwsKeyword) {
                    $keywords[] = $scwsKeyword['word'];
                }
            }

            $similarArticles = $this->_getSimilarArticles($rowArticle, $keywords, $n);
        }

        return $similarArticles;
    }

    /**
     * 获取相似文章
     *
     * @param \system\row | mixed $rowArticle 当前文章
     * @param array $keywords 关键词
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    private function _getSimilarArticles($rowArticle, $keywords, $n)
    {
        $similarArticles = [];

        $configArticle = Be::getConfig('Cms.Article');
        $keywordsCount = count($keywords);
        if ($keywordsCount > 0) {
            $tableArticle = Be::getTable('Cms.article');
            $tableArticle->where('id', '!=', $rowArticle->id);
            $tableArticle->where('(');
            for ($i = 0; $i < $keywordsCount; $i++) {
                $tableArticle->where('title', 'like', '%' . $keywords[$i] . '%');
                if ($i < ($keywordsCount - 1)) $tableArticle->where('OR');
            }
            $tableArticle->where(')');
            $tableArticle->orderBy('hits DESC, createTime DESC');
            $tableArticle->limit($n);
            $tableArticle->cache($configArticle->cacheExpire);
            $similarArticles = $tableArticle->getObjects();

            if (count($similarArticles) == 0) {
                $tableArticle->init();
                $tableArticle->where('id', '!=', $rowArticle->id);
                $tableArticle->where('(');
                for ($i = 0; $i < $keywordsCount; $i++) {
                    $tableArticle->where('body', 'like', '%' . $keywords[$i] . '%');
                    if ($i < ($keywordsCount - 1)) $tableArticle->where('OR');
                }
                $tableArticle->where(')');
                $tableArticle->orderBy('hits DESC, createTime DESC');
                $tableArticle->limit($n);
                $tableArticle->cache($configArticle->cacheExpire);
                $similarArticles = $tableArticle->getObjects();
            }
        }

        return $similarArticles;
    }

    /**
     * 获取评论列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getComments($conditions = [])
    {
        $tableArticleComment= Be::getTable('Cms.article_comment');

        $where = $this->createCommentWhere($conditions);
        $tableArticleComment->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableArticleComment->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'createTime';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableArticleComment->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableArticleComment->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableArticleComment->limit($conditions['limit']);

        $tableArticleComment->cache(Be::getConfig('Cms.Article')->cacheExpire);

        return $tableArticleComment->getObjects();
    }

    /**
     * 获取评论总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getCommentCount($conditions = [])
    {
        return Be::getTable('Cms.article_comment')
            ->where($this->createCommentWhere($conditions))
            ->cache(Be::getConfig('Cms.Article')->cacheExpire)
            ->count();
    }

    /**
     * 生成评论条件where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createCommentWhere($conditions = [])
    {
        $where = [];
        $where[] = ['block', 0];

        if (isset($conditions['articleId']) && is_numeric($conditions['articleId']) && $conditions['articleId'] > 0) {
            $where[] = ['articleId', $conditions['articleId']];
        }

        if (isset($conditions['userId']) && is_numeric($conditions['userId'])) {
            $where[] = ['userId', $conditions['userId']];
        }

        return $where;
    }

    /**
     * 活跃会员, 即参与评论最多的会员
     *
     * @param int $limit 获取多少个
     * @return array 用户对象数组
     */
    public function getActiveUsers($limit = 10)
    {
        $userIds = Be::getTable('Cms.article_comment')
            ->groupBy('userId')
            ->orderBy('COUNT(*) DESC')
            ->limit($limit)
            ->cache(Be::getConfig('Cms.Article')->cacheExpire)
            ->getValues('userId');

        $activeUsers = [];
        foreach ($userIds as $userId) {
            $activeUsers[] = Be::getUser($userId);
        }

        return $activeUsers;
    }

    private $categories = null;
    private $categoryTree = null;

    /**
     * 获取分类列表
     *
     * @return array|null
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $this->categories = $this->CreateCategories($this->getCategoryTree());
        }
        return $this->categories;
    }

    /**
     * 获取分类总数
     *
     * @return int
     */
    public function getCategoryCount()
    {
        return Be::getTable('Cms.article_category')
            ->cache(Be::getConfig('Cms.Article')->cacheExpire)
            ->count();
    }

    /**
     * 获取分类树
     *
     * @return array|null
     */
    public function getCategoryTree()
    {
        if ($this->categoryTree === null) {
            $categories = Be::getTable('Cms.article_category')
                ->cache(Be::getConfig('Cms.Article')->cacheExpire)
                ->getObjects();

            $this->categoryTree = $this->CreateCategoryTree($categories);
        }
        return $this->categoryTree;
    }

    /**
     * 获取指定分类ID下的所有层级的子分类ID
     *
     * @param $categoryId
     * @return array
     */
    public function getSubCategoryIds($categoryId)
    {
        $categories = $this->getCategories();

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
            } elseif ($category->id == $categoryId) {
                $level = $category->level;
                $start = true;
            }
        }
        return $ids;
    }

    /**
     * 生成分类列表，按树结构格式化过
     *
     * @param array $categoryTree 分类树
     * @param array $categories
     * @return array
     */
    private function createCategories($categoryTree = null, &$categories = [])
    {
        if (count($categoryTree)) {
            foreach ($categoryTree as $category) {
                $subCategory = null;
                if (isset($category->subCategory)) {
                    $subCategory = $category->subCategory;
                    unset($category->subCategory);
                }
                $categories[] = $category;

                if ($subCategory !== null) $this->createCategories($subCategory, $categories);
            }
        }
        return $categories;
    }

    /**
     * 生成分类树
     *
     * @param array $categories
     * @param int $parentId
     * @param int $level
     * @return array
     */
    private function CreateCategoryTree(&$categories = null, $parentId = 0, $level = 0)
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parentId == $parentId) {
                $category->level = $level;
                $subCategory = $this->CreateCategoryTree($categories, $category->id, $level + 1);
                if (count($subCategory)) $category->subCategory = $subCategory;
                $category->children = count($subCategory);
                $tree[] = $category;
            }
        }
        return $tree;
    }

    /**
     * 顶
     *
     * @param int $articleId 文章编号
     * @return bool
     */
    public function like($articleId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $my = Be::getUser();
            if ($my->id == 0) {
                throw new \Exception('请先登陆！');
            }

            $rowArticle = Be::getRow('Cms.article');
            $rowArticle->load($articleId);
            if ($rowArticle->id == 0 || $rowArticle->block == 1) {
                throw new \Exception('文章不存在！');
            }

            $rowArticleVoteLog = Be::getRow('Cms.article_vote_log');
            $rowArticleVoteLog->load(['articleId' => $articleId, 'userId' => $my->id]);
            if ($rowArticleVoteLog->id > 0) {
                throw new \Exception('您已经表过态啦！');
            }
            $rowArticleVoteLog->articleId = $articleId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticle->increment('like', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 踩
     *
     * @param int $articleId 文章编号
     * @return bool
     */
    public function dislike($articleId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $my = Be::getUser();
            if ($my->id == 0) {
                throw new \Exception('请先登陆！');
            }

            $rowArticle = Be::getRow('Cms.article');
            $rowArticle->load($articleId);
            if ($rowArticle->id == 0 || $rowArticle->block == 1) {
                throw new \Exception('文章不存在！');
            }

            $rowArticleVoteLog = Be::getRow('Cms.article_vote_log');
            $rowArticleVoteLog->load(['articleId' => $articleId, 'userId' => $my->id]);
            if ($rowArticleVoteLog->id > 0) {
                throw new \Exception('您已经表过态啦！');
            }
            $rowArticleVoteLog->articleId = $articleId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticle->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 提交评论
     *
     * @param int $articleId 文章编号
     * @param string $commentBody 评论内容
     * @return bool
     */
    public function comment($articleId, $commentBody)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $my = Be::getUser();
            if ($my->id == 0) {
                throw new \Exception('请先登陆！');
            }

            $rowArticle = Be::getRow('Cms.article');
            $rowArticle->load($articleId);
            if ($rowArticle->id == 0 || $rowArticle->block == 1) {
                throw new \Exception('文章不存在！');
            }

            $commentBody = trim($commentBody);
            $commentBodyLength = strlen($commentBody);
            if ($commentBodyLength == 0) {
                throw new \Exception('请输入评论内容！');
            }

            if ($commentBodyLength > 2000) {
                throw new \Exception('评论内容过长！');
            }

            $rowArticleComment = Be::getRow('Cms.article_comment');
            $rowArticleComment->articleId = $articleId;
            $rowArticleComment->userId = $my->id;
            $rowArticleComment->userName = $my->name;
            $rowArticleComment->body = $commentBody;
            $rowArticleComment->ip = $_SERVER['REMOTE_ADDR'];
            $rowArticleComment->createTime = time();

            $configArticle = Be::getConfig('Cms.Article');
            $rowArticleComment->block = ($configArticle->commentPublic == 1 ? 0 : 1);

            $rowArticleComment->save();

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 顶
     *
     * @param int $commentId 文章评论编号
     * @return bool
     */
    public function commentLike($commentId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $my = Be::getUser();
            if ($my->id == 0) {
                throw new \Exception('请先登陆！');
            }

            $rowArticleComment = Be::getRow('Cms.article_comment');
            $rowArticleComment->load($commentId);
            if ($rowArticleComment->id == 0 || $rowArticleComment->block == 1) {
                throw new \Exception('评论不存在！');
            }

            $rowArticleVoteLog = Be::getRow('Cms.article_vote_log');
            $rowArticleVoteLog->load(['commentId' => $commentId, 'userId' => $my->id]);
            if ($rowArticleVoteLog->id > 0) {
                throw new \Exception('您已经表过态啦！');
            }
            $rowArticleVoteLog->commentId = $commentId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticleComment->increment('like', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 踩
     *
     * @param int $commentId 文章评论编号
     * @return bool
     */
    public function commentDislike($commentId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $my = Be::getUser();
            if ($my->id == 0) {
                throw new \Exception('请先登陆！');
            }

            $rowArticleComment = Be::getRow('Cms.article_comment');
            $rowArticleComment->load($commentId);
            if ($rowArticleComment->id == 0 || $rowArticleComment->block == 1) {
                throw new \Exception('评论不存在！');
            }

            $rowArticleVoteLog = Be::getRow('Cms.article_vote_log');
            $rowArticleVoteLog->load(['commentId' => $commentId, 'userId' => $my->id]);
            if ($rowArticleVoteLog->id > 0) {
                throw new \Exception('您已经表过态啦！');
            }
            $rowArticleVoteLog->commentId = $commentId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticleComment->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

}
