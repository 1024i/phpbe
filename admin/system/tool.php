<?php
use Phpbe\System\Be;

function systemLog($log)
{
	$serviceSystem = Be::getService('System.Admin');
	$serviceSystem->newLog($log);
}
