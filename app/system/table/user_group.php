<?php
namespace table;

use system\db;

class user_group extends \system\table
{
	public $id = 0;
	public $name = '';
	public $note = '';
	public $permission = 0;	// 0 û���κ�Ȩ�� 1:����Ȩ�� -1:��ϸ����Ȩ��
	public $permissions = '';  // �Զ���Ȩ���Զ��ŷָ�
	public $default = 0;	// Ĭ��ע���û�����
	public $rank = 0;

	public function __construct()
	{
		parent::__construct('be_user_group', 'id');
	}

	public function set_default()
	{
		db::execute('UPDATE `'.$this->table_name.'` SET `default`=0 WHERE `default`=1');
		db::execute('UPDATE `'.$this->table_name.'` SET `default`=1 WHERE `'.$this->table_key.'` = \''.$this->{$this->table_key}.'\'');
	}
}
?>