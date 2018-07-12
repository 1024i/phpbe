<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;

class Link extends Service
{

    public function getSystemLinks($conditions = array())
    {
        $tableSystemLink = Be::getTable('System.Link');

        $where = $this->createSystemLinkWhere($conditions);
        $tableSystemLink->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableSystemLink->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'ordering';
            $orderByDir = 'ASC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableSystemLink->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableSystemLink->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableSystemLink->limit($conditions['limit']);

        return $tableSystemLink->getObjects();
    }

    public function getSystemLinkCount($conditions = array())
    {
        return Be::getTable('System.Link')
            ->where($this->createSystemLinkWhere($conditions))
            ->count();
    }

    private function createSystemLinkWhere($conditions = array())
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

    public function unblock($ids)
    {
        Be::getTable('System.Link')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
    }

    public function block($ids)
    {
        Be::getTable('System.Link')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
    }

    public function delete($ids)
    {
        Be::getTable('System.Link')->where('id', 'in', explode(',', $ids))->delete();
    }

    public function update()
    {
        $links = Be::getTable('System.Link')
            ->where('block', 0)
            ->orderBy('ordering', 'desc')
            ->getObjects();

        $configSystemLink = Be::getConfig('System.Link');
        $properties = get_object_vars($configSystemLink);
        foreach ($properties as $key => $val) {
            unset($configSystemLink->$key);
        }

        $i = 1;
        foreach ($links as $link) {
            $key = 'link_' . $i;
            $configSystemLink->$key = array('name' => $link->name, 'url' => $link->url);
            $i++;
        }

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($configSystemLink, Be::getRuntime()->getPathData() . '/config/systemLink.php');
    }

}
