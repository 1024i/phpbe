<?php
namespace App\System\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;
use Phpbe\System\Service\ServiceException;

class menu extends Service
{

    /**
     * 获取菜单项列表
     *
     * @param int $groupId 菜单组编号
     * @return array
     */
    public function getMenus($groupId)
    {
        return Be::getTable('System', 'Menu')
            ->where('group_id', $groupId)
            ->orderBy('ordering', 'ASC')
            ->getObjects();
    }

    /**
     * 删除菜单
     *
     * @param int $menuId 菜单编号
     * @throws \Exception
     */
    public function deleteMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System', 'Menu')->where('parent_id', $menuId)->update(['parent_id' => 0]);
            Be::getRow('System', 'Menu')->delete($menuId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 将某项菜单设置为首页
     *
     * @param $menuId
     * @throws \Exception
     */
    public function setHomeMenu($menuId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System', 'Menu')->where('home', 1)->update(['home' => 0]);
            Be::getTable('System', 'Menu')->where('id', $menuId)->update(['home' => 1]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 获取菜单组列表
     *
     * @return array
     */
    public function getMenuGroups()
    {
        return Be::getTable('System', 'MenuGroup')->orderBy('id', 'asc')->getObjects();
    }

    /**
     * 获取菜单组中总数
     *
     * @return int
     */
    public function getMenuGroupSum()
    {
        return Be::getTable('System', 'MenuGroup')->count();
    }

    /**
     * 删除菜单组
     *
     * @param $groupId
     * @throws \Exception
     */
    public function deleteMenuGroup($groupId)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            Be::getTable('System', 'Menu')->where('group_id', $groupId)->delete();
            Be::getRow('System', 'MenuGroup')->delete($groupId);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }


    /**
     * 更新菜单
     *
     * @param string $menuName 菜单名
     * @throws \Exception
     */
    public function update($menuName)
    {
        $group = Be::getRow('System', 'MenuGroup');
        $group->load(array('className' => $menuName));
        if (!$group->id) {
            throw new ServiceException('未找到调用类名为 ' . $menuName . ' 的菜单！');
        }

        $menus = Be::getTable('System', 'Menu')
            ->where('group_id', $group->id)
            ->orderBy('ordering', 'ASC')
            ->getObjects();

        $code = '<?php' . "\n";
        $code .= 'namespace Cache\\Runtime\\Menu;' . "\n";
        $code .= "\n";
        $code .= 'class ' . $group->className . ' extends \\Phpbe\\System\\Menu' . "\n";
        $code .= '{' . "\n";
        $code .= '  public function __construct()' . "\n";
        $code .= '  {' . "\n";
        foreach ($menus as $menu) {
            if ($menu->home == 1) {
                $homeParams = array();

                $menuParams = $menu->params;
                if ($menuParams == '') $menuParams = $menu->url;

                if (strpos($menuParams, '=')) {
                    $menuParams = explode('&', $menuParams);
                    foreach ($menuParams as $menuParam) {
                        $menuParam = explode('=', $menuParam);
                        if (count($menuParam) == 2) $homeParams[$menuParam[0]] = $menuParam[1];
                    }
                }

                $configSystem = Be::getConfig('System', 'Config');
                if (serialize($configSystem->homeParams) != serialize($homeParams)) {
                    $configSystem->homeParams = $homeParams;
                    $configSystem->updateConfig('System', $configSystem);
                }
            }

            $params = array();

            $menuParams = $menu->params;
            if ($menuParams == '') $menuParams = $menu->url;

            if (strpos($menuParams, '=')) {
                $menuParams = explode('&', $menuParams);
                foreach ($menuParams as $menuParam) {
                    $menuParam = explode('=', $menuParam);
                    if (count($menuParam) == 2) $params[] = '\'' . $menuParam[0] . '\'=>\'' . $menuParam[1] . '\'';
                }
            }

            $param = 'array(' . implode(',', $params) . ')';

            $url = $menu->url;
            if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
                $url = '\'' . $url . '\'';
            } else {
                $url = 'url(\'' . $url . '\')';
            }

            $code .= '    $this->addMenu(' . $menu->id . ', ' . $menu->parentId . ', \'' . $menu->name . '\', ' . $url . ', \'' . $menu->target . '\', ' . $param . ', ' . $menu->home . ');' . "\n";
        }
        $code .= '  }' . "\n";
        $code .= '}' . "\n";

        $path = Be::getRuntime()->getPathCache() . '/Runtime/Menu/' . $group->className . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }


}
