<?php
namespace system;

class Router
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
	 * echo url('app=Cms&controller=Article&task=detail&id=1'); // 输出：http://www.yourdomain.com/article/detail/1.html
	 * </pre>
	 */
	public function encodeUrl($app, $controller, $task, $params=array())
	{
		$configSystem = Be::getConfig('System.System');

		$urlParams = '';
		if (count($params)) {
			foreach ($params as $key=>$val) {
				$urlParams .= '/' . $key . '-' . $val;
			}
		}
		echo URL_ROOT . '/' . $app . '/' . $controller . '/' . $task . $urlParams . $configSystem->sefSuffix;
		return URL_ROOT . '/' . $app . '/' . $controller . '/' . $task . $urlParams . $configSystem->sefSuffix;
	}


    public function encodeAdminUrl($app, $controller, $task, $params=array())
    {
        $configSystem = Be::getConfig('System.System');

        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key=>$val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        echo URL_ROOT . '/' . ADMIN . '/'  . $app . '/' . $controller . '/' . $task . $urlParams . $configSystem->sefSuffix;
        return URL_ROOT . '/' . ADMIN . '/' . $app . '/'  . $controller . '/' . $task . $urlParams . $configSystem->sefSuffix;
    }

    /**
	 * 解析网址
	 *
	 * @params array() $urls 网址按 "/" 拆分成的数组 $urls = explode('/', '/{controller}/{task}......');
	 * @return bool
	 */
	public function decodeUrl($urls)
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

    /**
     * 解析后台网址
     *
     * @params array() $urls 网址按 "/" 拆分成的数组 $urls = explode('/', '/{controller}/{task}......');
     * @return bool
     */
    public function decodeAdminUrl($urls)
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
