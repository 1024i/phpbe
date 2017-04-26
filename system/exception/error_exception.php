<?php
namespace system\exception;

/**
 * 错误异常
 */
class error_exception extends \exception
{

    private $context = null;

    /**
     * 错误异常构造函数
     * @param int $code 错误的级别
     * @param string $message 错误的信息
     * @param string $file 发生错误的文件名
     * @param int $line 错误发生的行号
     * @param array $context 指向错误发生时活动符号表的 array。 包含错误触发处作用域内所有变量的数组。
     */
    public function __construct($code, $message, $file, $line, $context = array())
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }


}
