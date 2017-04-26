<?php
namespace system;

/**
 * debug
 */
class debug
{

	/**
	 * 处理普通 错语
	 *
	 * @param int $code 错误的级别
	 * @param string $message 错误的信息
	 * @param string $file 发生错误的文件名
	 * @param int $line 错误发生的行号
	 * @param array $context 指向错误发生时活动符号表的 array。 包含错误触发处作用域内所有变量的数组。
	 * @throws exception\error_exception
	 */
	public static function error($code, $message, $file, $line, $context = array())
	{
		throw new exception\error_exception($code, $message, $file, $line, $context);

		if (!(error_reporting() & $code)) return;

		$error = array(
			'type' => 'error',
			'code' => $code,
			'message' => $message,
			'file' => $file,
			'line' => $line,
			'trace' => debug_backtrace()
		);

		self::handle($error);
	}


	/*
	 * 处理异常（exception）错语
	 *
	 * @param Exception $e 异常
	 *
	 * @author Lou Barnes
	 */
	public static function exception($e)
	{
		if (!$e) return;

		$error = array(
			'type' => 'exception',
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'trace' => $e->getTrace()
		);

		self::handle($error);
	}

	public static function shutdown() {

	}

	/*
	 * 处理SQL错语
	 *
	 * @param int $code 错误码
	 * @param string $message 错误信息
	 * @param string $sql 出错的 SQL 语句
	 *
	 * @author Lou Barnes
	 */
	public static function sql($code, $message, $sql)
	{
		$trace = debug_backtrace();

		$file = '';
		$line = 0;
		$db_class_path = PATH_ROOT.DS.'system'.DS.'db.php';
		foreach ($trace as $k => &$v) {
			if (!isset($v['file'])) continue;
			if (strncmp($db_class_path, $v['file'], strlen($db_class_path)) !== 0) {
				$file = $v['file'];
				$line = $v['line'];
			}
		}

		$error = array(
			'type' => 'sql',
			'code' => $code,
			'message' => $message.' SQL：'.$sql,
			'file' => $file,
			'line' => $line,
			'trace' => $trace
		);

		self::handle($error);
	}


	/*
	 * 合并处理错误信息
	 * 跟据DEBUG 设置项显录错误信息，记录到日志文件
	 *
	 * @param array $error 错误信息
	 *
	 * @author Lou Barnes
	 */
	private static function handle($error)
	{
		$config_system = be::get_config('system');
		if ($config_system->debug & 1)	// 输出调试信息
		{
			echo '<h1>出错啦！</h1>';
			echo '错误编号：'.$error['trace']['code'].'<br />';
			echo '错误信息：<pre style="display: inline;">'.htmlentities($error['trace']['message'], ENT_QUOTES, 'UTF-8').'</pre><br />';
			echo '文件：'.$error['trace']['file'].'<br />';
			echo '行号：'.$error['trace']['line'].'<br />';
			echo '时间：'.date('Y-m-d H:i:s').'<br />';
		}

		if ($config_system->debug & 2) // 记录调试信息
		{
			debug::log($error);
		}
	}


	/*
	 * 调试信息写入日志文件
	 *
	 * @param array $error 错误信息
	 *
	 * @author Lou Barnes
	 */
	public static function log($error)
	{
		$t = time();

		$error['POST'] = &$_POST;
		$error['GET'] = &$_GET;
		$error['COOKIE'] = &$_COOKIE;
		$error['SESSION'] = &$_SESSION;
		$error['SERVER'] = &$_SERVER;
		$error['REQUEST'] = &$_REQUEST;
		$error['time'] = $t;

		$path = PATH_ROOT.DS.'error'.DS.date('Y',$t).DS.date('m',$t).DS.date('d',$t).'.log';
		$dir = dirname($path);
		if (!is_dir($dir)) mkdir($dir, 0777, true);

		$formatted_error = strtr(serialize($error), array("\r\n" => ' '.chr(0), "\r" => chr(0), "\n" => chr(0))) . "\n";
		file_put_contents($path, $formatted_error, FILE_APPEND | LOCK_EX);
	}
}
