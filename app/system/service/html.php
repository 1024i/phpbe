<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service\ServiceException;

class Html extends \Phpbe\System\Service
{

    /**
     * 获取自定义模块列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getSystemHtmls($conditions = array())
    {
        $tableSystemHtml = Be::getTable('System', 'Html');

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
        return Be::getTable('System', 'Html')
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
        $table = Be::getTable('System', 'Html');
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
     * @throws \Exception
     */
    public function unblock($ids)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $ids = explode(',', $ids);

            $table = Be::getTable('System', 'Html');
            $table->where('id', 'in', $ids)->update(['block' => 0]);

            $objects = $table->where('id', 'in', $ids)->getObjects();

            $dir = Be::getRuntime()->getCachePath() . '/Runtime/Html';
            if (!file_exists($dir)) {
                $libFso = Be::getLib('fso');
                $libFso->mkDir($dir);
            }

            foreach ($objects as $obj) {
                file_put_contents($dir . '/' . $obj->class . '.html', $obj->body);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * 屏蔽
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @throws \Exception
     */
    public function block($ids)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $ids = explode(',', $ids);

            $table = Be::getTable('System', 'Html');
            $table->where('id', 'in', $ids)->update(['block' => 1]);

            $classes = $table->where('id', 'in', $ids)->getValues('class');

            $dir = Be::getRuntime()->getCachePath() . '/Runtime/Html';
            foreach ($classes as $class) {
                $path = $dir . '/' . $class . '.html';
                if (file_exists($path)) @unlink($path);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 删除
     *
     * @param string $ids 以逗号分隔的多个模块ID
     * @throws \Exception
     */
    public function delete($ids)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $ids = explode(',', $ids);

            $table = Be::getTable('System', 'Html');
            $classes = $table->where('id', 'in', $ids)->getValues('class');

            $dir = Be::getRuntime()->getCachePath() . '/Runtime/Html';
            foreach ($classes as $class) {
                $path = $dir . '/' . $class . '.html';
                if (file_exists($path)) @unlink($path);
            }

            $table->where('id', 'in', $ids)->delete();

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 更新自定义 html 内容
     *
     * @param string $class 调用类名
     * @throws \Exception
     */
    public function update($class)
    {
        $row = Be::getRow('System', 'Html');
        $row->load(array('class' => $class));
        if (!$row->id) {
            throw new ServiceException('未找到调用类名为 ' . $class . ' 的 html 内容！');
        }

        $path = Be::getRuntime()->getCachePath() . '/Runtime/Html/' . $class . '.html';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $row->body, LOCK_EX);
        chmod($path, 0755);
    }

}
