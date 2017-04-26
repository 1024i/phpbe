<?php
namespace admin\table;

class system_menu_group extends \system\table
{
	public $id = 0;
	public $name = '';
	public $class_name = '';

	public function __construct()
	{
		parent::__construct('be_system_menu_group', 'id');
	}
}