<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service\ServiceException;

class Announcement extends \Phpbe\System\Service
{

    public function getSystemAnnouncements($conditions = [])
    {
        $tableSystemAnnouncement = Be::getTable('System', 'Announcement');

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
        return Be::getTable('System', 'Announcement')
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


}