<?php
namespace admin\table;

class system_menu extends \system\table
{
	public $id = null;
	public $group_id = 0;
	public $parent_id = 0;
	public $name = '';
	public $url = '';
	public $target = '_self';
	public $params = '';
	public $home = 0;
	public $block = 0;
	public $rank = 0;

	public function __construct()
	{
		parent::__construct('be_system_menu', 'id');
	}
}