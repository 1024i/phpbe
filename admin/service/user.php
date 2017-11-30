<?php

namespace admin\service;

use system\be;

class user extends \system\service
{

    /**
     * 获取指定条件的用户列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function get_users($conditions = [])
    {
        $table_user = be::get_table('user');

        $where = $this->create_user_where($conditions);
        $table_user->where($where);

        if (isset($conditions['order_by_string']) && $conditions['order_by_string']) {
            $table_user->order_by($conditions['order_by_string']);
        } else {
            $order_by = 'id';
            $order_by_dir = 'DESC';
            if (isset($conditions['order_by']) && $conditions['order_by']) $order_by = $conditions['order_by'];
            if (isset($conditions['order_by_dir']) && $conditions['order_by_dir']) $order_by_dir = $conditions['order_by_dir'];
            $table_user->order_by($order_by, $order_by_dir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $table_user->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $table_user->limit($conditions['limit']);

        return $table_user->get_objects();
    }

    /**
     * 获取指定条件的用户总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function get_user_count($conditions = [])
    {
        return be::get_table('user')
            ->where($this->create_user_where($conditions))
            ->count();
    }

    /**
     * 生成查询条件 where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function create_user_where($conditions = [])
    {
        $where = [];

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = '(';
            $where[] = ['username', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['name', 'like', '%' . $conditions['key'] . '%'];
            $where[] = 'OR';
            $where[] = ['email', 'like', '%' . $conditions['key'] . '%'];
            $where[] = ')';
        }

        if (isset($conditions['status']) && is_numeric($conditions['status']) && $conditions['status'] != -1) {
            $where[] = ['block', $conditions['status']];
        }

        if (isset($conditions['role_id']) && is_numeric($conditions['role_id']) && $conditions['role_id'] > 0) {
            $where[] = ['role_id', $conditions['role_id']];
        }

        return $where;
    }

    /**
     * 屏蔽用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function unblock($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('user');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 0])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 启用用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function block($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $table = be::get_table('user');
            if (!$table->where('id', 'in', explode(',', $ids))
                ->update(['block' => 1])
            ) {
                throw new \exception($table->get_error());
            }

            $db->commit();
        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 删除用户账号
     *
     * @param string $ids 以逗号分隔的多个用户ID
     * @return bool
     */
    public function delete($ids)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $files = [];

            $array = explode(',', $ids);
            foreach ($array as $id) {

                $row_user = be::get_row('user');
                $row_user->load($id);

                if ($row_user->avatar_s != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_s;
                if ($row_user->avatar_m != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_m;
                if ($row_user->avatar_l != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_l;

                if (!$row_user->delete()) {
                    throw new \exception($row_user->get_error());
                }
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 初始化用户头像
     *
     * @param int $user_id 用户ID
     * @return bool
     */
    public function init_avatar($user_id)
    {
        $db = be::get_db();
        try {
            $db->begin_transaction();

            $row_user = be::get_row('user');
            $row_user->load($user_id);

            $files = [];
            if ($row_user->avatar_s != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_s;
            if ($row_user->avatar_m != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_m;
            if ($row_user->avatar_l != '') $files[] = PATH_DATA . DS . 'user' . DS . 'avatar' . DS . $row_user->avatar_l;

            $row_user->avatar_s = '';
            $row_user->avatar_m = '';
            $row_user->avatar_l = '';

            if (!$row_user->save()) {
                throw new \exception($row_user->get_error());
            }

            $db->commit();

            foreach ($files as $file) {
                if (file_exists($file)) @unlink($file);
            }

        } catch (\exception $e) {
            $db->rollback();

            $this->set_error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 检测用户名是否可用
     *
     * @param $username
     * @param int $user_id
     * @return bool
     */
    public function is_username_available($username, $user_id = 0)
    {
        $table = be::get_table('user');
        if ($user_id > 0) {
            $table->where('id', '!=', $user_id);
        }
        $table->where('username', $username);
        return $table->count() == 0;
    }

    /**
     * 检测邮箱是否可用
     *
     * @param $email
     * @param int $user_id
     * @return bool
     */
    public function is_email_available($email, $user_id = 0)
    {
        $table = be::get_table('user');
        if ($user_id > 0) {
            $table->where('id', '!=', $user_id);
        }
        $table->where('email', $email);
        return $table->count() == 0;
    }

    /**
     * 获取角色列表
     *
     * @return array
     */
    public function get_roles()
    {
        return be::get_table('user_role')->order_by('rank', 'asc')->get_objects();
    }

    /**
     * 更新所有角色缓存
     */
    public function update_user_roles()
    {
        $roles = $this->get_roles();
        $service_system = be::get_service('system');
        foreach ($roles as $role) {
            $service_system->update_cache_user_role($role->id);
        }
    }

    /**
     * 更新指定角色缓存
     *
     * @param int $role_id 角色ID
     */
    public function update_user_role($role_id)
    {
        $service_system = be::get_service('system');
        $service_system->update_cache_user_role($role_id);
    }
}
