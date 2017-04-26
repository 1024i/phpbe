<?php

function system_log($log)
{
	$model_system = be::get_admin_model('system');
	$model_system->new_log($log);
}

?>