<?php
namespace App\System\Service;

use System\Be;

class Announcement extends \System\Service
{

    public function getSystemAnnouncements($conditions = [])
    {
        $tableSystemAnnouncement = Be::getTable('System.Announcement');

        $where = $this->createSystemAnnouncementWhere($conditions);
        $tableSystemAnnouncement->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableSystemAnnouncement->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'ordering';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableSystemAnnouncement->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableSystemAnnouncement->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableSystemAnnouncement->limit($conditions['limit']);

        return $tableSystemAnnouncement->getObjects();
    }


    public function getSystemAnnouncementCount($conditions = [])
    {
        return Be::getTable('System.Announcement')
            ->where($this->createSystemAnnouncementWhere($conditions))
            ->count();
    }

    private function createSystemAnnouncementWhere($conditions = [])
    {
        $where = [];

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = '(';
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['content', 'like', '%' . $conditions['key'] . '%'];
            $where[] = ')';
        }

        if (isset($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        return $where;
    }


    public function unblock($ids)
    {
        $db = Be::getDb();
        try {
            $db->beginTransaction();

            $table = Be::getTable('System.Announcement');
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

            $table = Be::getTable('System.Announcement');
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

            $table = Be::getTable('System.Announcement');
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
}