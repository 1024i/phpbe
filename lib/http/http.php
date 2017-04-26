<?php
namespace lib\http;

/*
@版本日期: 2011年8月22日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class http extends \system\lib
{

    private $handler = null;

    // 构造函数
    public function __construct()
    {
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $this->handler = new \lib\http\handler\curl();
        } elseif (function_exists('fopen') && ini_get('allow_url_fopen') == true) {
            $this->handler = new \lib\http\handler\fopen();
        } elseif (function_exists('fsockopen')) {
            $this->handler = new \lib\http\handler\fsockopen();
        }

        if ($this->handler === null) {
            $this->set_error('您的服务器没有安装可用于HTTP通信的扩展');
            return false;
        }
    }


    // 析构函数
    public function __destruct()
    {

    }


    public function get($url)
    {
        $this->handler->init();
        $this->handler->set_url($url);
        $this->handler->set_header('method', 'GET');

        return $this->handler->request();
    }

    public function post($url, $data)
    {
        $this->handler->init();
        $this->handler->set_url($url);
        $this->handler->set_header('method', 'POST');

        if ($data ! == null) {
            foreach ($data as $key => $val) {
                $this->handler->set_data($key, $val);
            }
        }
        return $this->handler->request();
    }

    public function set_header($name, $value)
    {
        $this->handler->set_header($name, $value);
    }

}

?>