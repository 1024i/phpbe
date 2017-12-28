<?php
namespace app\cms\service;

use System\Be;

class articleManage extends \System\Service
{

    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getArticles($conditions = array())
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

        return $tableArticle->getObjects();
    }

    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getArticleCount($conditions = array())
    {
        return Be::getTable('Cms.article')
            ->where($this->createArticleWhere($conditions))
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

        if (isset($conditions['categoryId']) && $conditions['categoryId'] != -1) {
            if ($conditions['categoryId'] == 0)
                $where[] = ['categoryId', 0];
            elseif ($conditions['categoryId'] > 0) {
                $serviceArticle = Be::getService('Cms.Article');
                $ids = $serviceArticle->getSubCategoryIds($conditions['categoryId']);
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

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }

    public function unblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('Cms.article');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    public function block($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('Cms.article');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    public function delete($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $articleCommentIds = Be::getTable('articleComment')->where('articleId', $id)->getArray('id');
                if (count($articleCommentIds)) {
                    $table = Be::getTable('articleVoteLog');
                    if (!$table->where('commentId', 'in', $articleCommentIds)->delete()) {
                        throw new \Exception($table->getError());
                    }

                    $table = Be::getTable('articleVoteLog');
                    if (!$table->where('articleId', $id)->delete()) {
                        throw new \Exception($table->getError());
                    }

                    $table = Be::getTable('articleComment');
                    if (!$table->where('articleId', $id)->delete()) {
                        throw new \Exception($table->getError());
                    }
                }

                $rowArticle = Be::getRow('Cms.article');
                $rowArticle->load($id);

                if ($rowArticle->thumbnailL != '') $files[] = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . $rowArticle->thumbnailL;
                if ($rowArticle->thumbnailM != '') $files[] = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . $rowArticle->thumbnailM;
                if ($rowArticle->thumbnailS != '') $files[] = PATH_DATA . DS . 'Cms' . DS . 'Article' . DS . 'Thumbnail' . DS . $rowArticle->thumbnailS;

                if (!$rowArticle->delete()) {
                    throw new \Exception($rowArticle->getError());
                }
            }

            foreach ($files as $file) {
                @unlink($file);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
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
    public function getComments($conditions = array())
    {
        $tableArticleComment = Be::getTable('articleComment');
        $tableArticleComment->where($this->createCommentWhere($conditions));

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

        return $tableArticleComment->getObjects();
    }

    /**
     * 获取文章评论总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getCommentCount($conditions = array())
    {
        return Be::getTable('articleComment')
            ->where($this->createCommentWhere($conditions))
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

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '\'%' . $conditions['key'] . '%\''];
        }

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }


    public function commentsUnblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('articleComment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    public function commentsBlock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('articleComment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    public function commentsDelete($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('articleComment');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->delete()
            ) {
                throw new \Exception($table->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * 获取分类列表
     */
    public function getCategories()
    {
        return Be::getTable('articleCategory')->orderBy('ordering', 'asc')->getObjects();
    }


    /**
     * 删除分类
     * @param int $categoryId 分类编号
     * @return bool
     */
    public function deleteCategory($categoryId)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('Cms.article');
            if (!$table->where('categoryId', $categoryId)->update(['categoryId' => 0])) {
                throw new \Exception($table->getError());
            }

            $table = Be::getTable('articleCategory');
            if (!$table->where('parentId', $categoryId)->update(['parentId' => 0])) {
                throw new \Exception($table->getError());
            }

            $row = Be::getRow('articleCategory');
            if (!$row->delete($categoryId)) {
                throw new \Exception($row->getError());
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

}
