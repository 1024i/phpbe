<?php
use System\Be;

function systemLog($log)
{
	$serviceSystem = Be::getService('System.Admin');
	$serviceSystem->newLog($log);
}
