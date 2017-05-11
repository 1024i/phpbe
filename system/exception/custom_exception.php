<?php
namespace system\exception;

/**
 * 错误异常
 */
class error_exception extends \exception
{

    /**
     * 错误异常构造函数
     * @param int $code 错误的级别
     * @param string $message 错误的信息
     * @param string $file 发生错误的文件名
     * @param int $line 错误发生的行号
     */
    public function __construct($code, $message, $file, $line)
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }


}
