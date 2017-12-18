<?php
use system\be;

function system_log($log)
{
	$service_system = be::get_service('system.admin');
	$service_system->new_log($log);
}
