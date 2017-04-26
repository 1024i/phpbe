<?php
namespace admin\table;

class system_log extends \system\table
{
	public $id = 0;
	public $user_id = 0;
	public $title = '';
	public $ip = '';
	public $create_time = 0;

	public function __construct()
	{
		parent::__construct('be_system_log', 'id');
	}
}
?>