<?php
namespace router;

use \system\be;

class article extends \system\router
{

	public function encode_url($controller, $task, $params=array())
	{
		$config_system = be::get_config('system');
		
		if ($task == 'articles') {
			if (array_key_exists('category_id', $params)) {
				if (array_key_exists('page', $params)) {
					return URL_ROOT.'/article/c'.$params['category_id'].'/p'.$params['page'].'/';
				}
				return URL_ROOT.'/article/c'.$params['category_id'].'/';
			}
		}
		elseif ($task == 'detail') {
			if (array_key_exists('article_id', $params)) {
				return URL_ROOT.'/article/'.$params['article_id'].$config_system->sef_suffix;
			}
		}
		elseif ($task == 'user') {
			if (array_key_exists('user_id', $params)) {
				return URL_ROOT.'/article/user/'.$params['user_id'].$config_system->sef_suffix;
			}
		}
		
		return parent::encode_url($controller, $task, $params);
	}

	public function decode_url($urls)
	{
		$len = count($urls);
		if ($len > 2) {
			if (is_numeric($urls[2])) {
				$_GET['task'] = $_REQUEST['task'] = 'detail';
				$_GET['article_id'] = $_REQUEST['article_id'] = $urls[2];

				return true;
			}
			elseif (substr($urls[2],0,1) == 'c') {
				$_GET['task'] = $_REQUEST['task'] = 'articles';
				$_GET['category_id'] = $_REQUEST['category_id'] = substr($urls[2],1);

				if ($len > 3 && substr($urls[3],0,1) == 'p') {
					$_GET['page'] = $_REQUEST['page'] = substr($urls[3],1);
				}
				return true;
			}
			elseif ($urls[2] == 'user') {
				$_GET['task'] = $_REQUEST['task'] = 'user';
				$_GET['user_id'] = $_REQUEST['user_id'] = $urls[3];

				return true;
			}
		}

		return parent::decode_url($urls);
	}
}
?>