<?php
use system\be;

function system_log($log)
{
	$service_system = be::get_admin_service('system');
	$service_system->new_log($log);
}
