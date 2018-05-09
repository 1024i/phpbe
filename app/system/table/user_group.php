<?php
namespace table;

use Phpbe\System\Db;

class userGroup extends \system\table
{
	public $id = 0;
	public $name = '';
	public $note = '';
	public $permission = 0;	// 0 û���κ�Ȩ�� 1:����Ȩ�� -1:��ϸ����Ȩ��
	public $permissions = '';  // �Զ���Ȩ���Զ��ŷָ�
	public $default = 0;	// Ĭ��ע���û�����
	public $ordering = 0;

	public function __construct()
	{
		parent::__construct('beUserGroup', 'id');
	}

	public function setDefault()
	{
		db::execute('UPDATE `'.$this->tableName.'` SET `default`=0 WHERE `default`=1');
		db::execute('UPDATE `'.$this->tableName.'` SET `default`=1 WHERE `'.$this->tableKey.'` = \''.$this->{$this->tableKey}.'\'');
	}
}
