<?php

/**
 * 处理网址
 * 
 * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
 * @return string
 */
function url($url)
{
    $config_system = \system\be::get_config('system');
    if ($config_system->sef !== '0') {
		$urls = explode('&', $url);

		if (count($urls) == 0) return URL_ROOT;

		$controller = null;
		$task = null;
		$params = array();

		foreach ($urls as $x) {
			$pos = strpos($x, '=');
			
			if ($pos !== false) {
				$key = substr($x, 0, $pos);
				$val = substr($x, $pos+1);

				if ($key == 'controller') {
					$controller = $val;
				} elseif ($key == 'task') {
					$task = $val;
				} else {
					$params[$key] = $val;
				}
			}
		}

		if ($controller == null) return URL_ROOT;
		if ($task == null) return URL_ROOT . '/' . $controller . $config_system->sef_suffix;

		$router = \system\be::get_router($controller);
		return $router->encode_url($controller, $task, $params);
	}

    return URL_ROOT . '/?' . $url;
}

/**
 * 
 * 限制字符串宽度
 * 名词说明
 * 字符: 一个字符占用一个字节， strlen 长度为 1
 * 文字：(可以看成由多个字符组成) 占用一个或多个字节  strlen 长度可能为 1,2,3,4,5,6
 * 
 * @param string $string 要限制的字符串
 * @param int $length 限制的宽度
 * @param string $etc 结层符号
 * @return string
 */
function limit($string, $length = 50, $etc = ' .  .  . ')
{
    $string = strip_tags($string);
    $length *= 2; //按中文时宽度应加倍
    

    if (strlen($string) <= $length) return $string;
    
    $length -= strlen($etc); // 去除结尾符长度
    if ($length <= 0) return '';
    
    $str_len = strlen($string);
    
    $pos = 0; // 当前处理到的字符位置
    $last_len = 0; // 最后一次处理的字符所代表的文字的宽度
    $len = 0; // 文字宽度累加值
    

    while ($pos < $str_len) // 系统采用了utf-8编码， 逐字符判断
    {
        $char = ord($string[$pos]);
        if ($char == 9 || $char == 10 || (32 <= $char && $char <= 126)) {
            $last_len = 1;
            $pos++;
            $len++;
        }
        elseif (192 <= $char && $char <= 223) {
            $last_len = 2;
            $pos += 2;
            $len += 2;
        }
        elseif (224 <= $char && $char <= 239) {
            $last_len = 3;
            $pos += 3;
            $len += 2;
        }
        elseif (240 <= $char && $char <= 247) {
            $last_len = 4;
            $pos += 4;
            $len += 2;
        }
        elseif (248 <= $char && $char <= 251) {
            $last_len = 5;
            $pos += 5;
            $len += 2;
        }
        elseif ($char == 252 || $char == 253) {
            $last_len = 6;
            $pos += 6;
            $len += 2;
        } else {
            $pos++;
        }
        
        if ($len >= $length) break;
    }
    
    // 超过指定宽度， 减去最后一次处理的字符所代表的文字宽度
    if ($len >= $length) {
        $pos -= $last_len;
        $string = substr($string, 0, $pos);
        $string .= $etc;
    }
    
    return $string;
}

/**
 * 格式化时间
 *
 * @param int $time unix 时间戳
 * @param int $max_days 多少天前或后以默认时间格式输出
 * @param string $default_format 默认时间格式
 * @return string
*/
function format_time($time, $max_days = 30, $default_format = 'Y-m-d')
{
	$t = time();

	$seconds = $t-$time;

	// 如果是{$max_days}天前，直接输出日期
	$max_seconds = $max_days*86400;
	if ($seconds > $max_seconds || $seconds <- $max_seconds) return date($default_format, $time);

	if ($seconds > 86400) {
		$days = intval($seconds / 86400);
		if ($days == 1) {
			if (date('a', $time) == 'am') return '昨天上午';
			else return '昨天下午';
		} elseif ($days == 2) {
			return '前天';
		}
		return $days . '天前';
	}
	elseif ($seconds > 3600) return intval($seconds / 3600) . '小时前';
	elseif ($seconds > 60) return intval($seconds / 60) . '分钟前';
	elseif ($seconds >= 0) return '刚才';
	elseif ($seconds > - 0) return '马上';
	elseif ($seconds > -3600) return intval(-$seconds / 60) . '分钟后';
	elseif ($seconds > -86400) return intval(-$seconds / 3600) . '小时后';
	else {
		$days = intval(-$seconds / 86400);
		if ($days == 1) {
			if (date('a', $time) == 'am') return '明天上午';
			else return '明天下午';
		} elseif ($days == 2) {
			return '后天';
		}
		return $days . '天后';
	}
}


function be_exit($string)
{
	exit('<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div style="padding:10px;text-align:center;">' . $string . '</div></body></html>');
}
