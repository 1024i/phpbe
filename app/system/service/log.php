<?php

namespace app\system\service;

use System\Be;

class log extends \System\Service
{

    public function newLog($log)
    {
        $my = Be::getAdminUser();
        $rowSystemLog = Be::getRow('System.log');
        $rowSystemLog->userId = $my->id;
        $rowSystemLog->title = $log;
        $rowSystemLog->ip = $_SERVER['REMOTE_ADDR'];
        $rowSystemLog->createTime = time();
        $rowSystemLog->save();
    }

    /**
     * 获取系统操作日志列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getLogs($conditions = array())
    {
        $tableSystemLog = Be::getTable('system.log');

        $where = $this->createLogWhere($conditions);
        $tableSystemLog->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableSystemLog->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'id';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableSystemLog->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableSystemLog->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableSystemLog->limit($conditions['limit']);

        return $tableSystemLog->getObjects();
    }

    /**
     * 获取系统操作日志总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getLogCount($conditions = array())
    {
        return Be::getTable('system.log')
            ->where($this->createLogWhere($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createLogWhere($conditions = [])
    {
        $where = [];

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['userId']) && is_numeric($conditions['userId']) && $conditions['userId'] != 0) {
            $where[] = ['userId', $conditions['userId']];
        }

        return $where;
    }


    /**
     * 删除三个月(90天)前的后台用户登陆日志
     *
     * @return bool
     */
    public function deleteLogs()
    {
        return Be::getTable('system.log')->where('createTime', '<', (time() - 90 * 86400))->delete();
    }


}
