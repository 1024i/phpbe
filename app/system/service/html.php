<?php
namespace app\system\service;

use System\Be;

class html extends \System\Service
{

    /**
     * 获取自定义模块列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getSystemHtmls($conditions = array())
    {
        $tableSystemHtml = Be::getTable('systemHtml');

        $where = $this->createSystemHtmlWhere($conditions);
        $tableSystemHtml->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableSystemHtml->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'id';
            $orderByDir = 'ASC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableSystemHtml->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableSystemHtml->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableSystemHtml->limit($conditions['limit']);

        return $tableSystemHtml->getObjects();
    }

    /**
     * 获取自定义模块总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getSystemHtmlCount($conditions = array())
    {
        return Be::getTable('systemHtml')
            ->where($this->createSystemHtmlWhere($conditions))
            ->count();
    }

    /**
     * 跟据查询条件生成 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createSystemHtmlWhere($conditions = array())
    {
        $where = array();

        if (array_key_exists('key', $conditions) && $conditions['key']) {
            $where[] = array('title', 'like', '%' . $conditions['key'] . '%');
        }

        if (array_key_exists('status', $conditions) && $conditions['status'] != -1) {
            $where[] = array('block', $conditions['status']);
        }

        return $where;
    }

    /**
     * 类名是否可用
     *
     * @param string $class 模块的类名
     * @param int $id
     * @return bool
     */
    public function isClassAvailable($class, $id)
    {
        $table = Be::getTable('systemHtml');
        if ($id > 0) {
            $table->where('id', '!=', $id);
        }
        $table->where('class', $class);
        return $table->count() == 0;
    }

    /**
     * 公开
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function unblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $ids = explode(',', $ids);

            $table = Be::getTable('systemHtml');
            if (!$table->where('id', 'in', $ids)->update(['block' => 0])) {
                throw new \Exception($table->getError());
            }

            $objects = $table->where('id', 'in', $ids)->getObjects();

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            if (!file_exists($dir)) {
                $libFso = Be::getLib('fso');
                $libFso->mkDir($dir);
            }

            foreach ($objects as $obj) {
                file_put_contents($dir . DS . $obj->class . '.html', $obj->body);
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
     * 屏蔽
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function block($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $ids = explode(',', $ids);

            $table = Be::getTable('systemHtml');
            if (!$table->where('id', 'in', $ids)->update(['block' => 1])) {
                throw new \Exception($table->getError());
            }

            $classes = $table->where('id', 'in', $ids)->getValues('class');

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            foreach ($classes as $class) {
                $path = $dir . DS . $class . '.html';
                if (file_exists($path)) @unlink($path);
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
     * 删除
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @return bool
     */
    public function delete($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $ids = explode(',', $ids);

            $table = Be::getTable('systemHtml');
            $classes = $table->where('id', 'in', $ids)->getValues('class');

            $dir = PATH_DATA . DS . 'system' . DS . 'cache' . DS . 'html';
            foreach ($classes as $class) {
                $path = $dir . DS . $class . '.html';
                if (file_exists($path)) @unlink($path);
            }

            if (!$table->where('id', 'in', $ids)->delete()) {
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


}
