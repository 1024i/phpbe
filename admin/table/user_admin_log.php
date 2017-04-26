<?php
namespace admin\table;

class user_admin_log extends \system\table
{
	public $id = null;
	public $username = '';
	public $success = 0;
	public $description = '';
	public $ip = '';
	public $create_time = 0;

	public function __construct()
	{
		parent::__construct('be_user_admin_log', 'id');
	}
}
?>