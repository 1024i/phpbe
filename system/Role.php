<?php
namespace System;

/**
 * 角色基类
 */
abstract class Role
{
    public $name = '';
    public $permission = -1;
    public $permissions = [];

    /**
     * 检测是否有权限访问指定控制器和任务
     *
     * @param $controller
     * @param $task
     * @return bool
     */
    public function hasPermission($controller, $task) {
        if ($this->permission == 1) return true;
        if ($this->permission == 0) return false;

        $key = $controller.'.'.$task;
        return in_array($key, $this->permissions);
    }
}