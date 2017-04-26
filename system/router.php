<?php
namespace system;

class router
{
	/**
	 * 搜索引警友好的网址格式:
	 *
	 * @param string $controller 控制器
	 * @param string $task 任务
	 * @param array $params 相关参数
	 * @return string
	 * @sample
	 * <pre>
	 * echo url('controller=article&task=detail&id=1'); // 输出：http://www.yourdomain.com/article/detail/1.html
	 * </pre>
	 */
	public function encode_url($controller, $task, $params=array())
	{
		$config_system = be::get_config('system');

		$url_params = '';
		if (count($params)) {
			foreach ($params as $key=>$val) {
				$url_params .= '/' . $key . '-' . $val;
			}
		}
		
		return URL_ROOT . '/' . $controller . '/' . $task . $url_params . $config_system->sef_suffix;
	}

	/**
	 * 解析网址
	 *
	 * @params array() $urls 网址按 "/" 拆分成的数组 $urls = explode('/', '/{controller}/{task}......');
	 * @return bool
	 */
	public function decode_url($urls)
	{
		$len = count($urls);
		if ($len >= 3) {
			$task = $urls[2];
			$_GET['task'] = $_REQUEST['task'] = $task;

			if ($len > 3) {
				/**
				 * 把网址按以下规则匹配
				 * /{controller}/{task}/{参数名1}-{参数值1}/{参数名2}-{参数值2}/{参数名3}-{参数值3}.html
				 * 其中{参数名}-{参数值} 值对不限数量
				 */
				for ($i = 3; $i < $len; $i++) {
					$pos = strpos($urls[$i], '-');
					if ($pos !== false) {
						$key = substr($urls[$i], 0, $pos);
						$val = substr($urls[$i], $pos+1);
						
						$_GET[$key] = $_REQUEST[$key] = $val;
					}
				}
			}
		}

		return true;
	}

}
