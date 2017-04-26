<?php
namespace lib\http;

/*
@版本日期: 2011年2月9日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class handler extends \system\obj
{
    protected $url = null;

    protected $headers = array();
    protected $data = array();


    // 构造函数
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->set_header('method', 'GET');
        $this->set_header('timeout', 5);
        $this->set_header('redirection', 5);
        $this->set_header('http_version', '1.0');
        $this->set_header('user_agent', 'Mr Bone v' . \system\be::get_version());

        $this->data = array();
    }

    public function set_url($url)
    {
        $this->url = $url;
    }

    public function set_header($name, $value)
    {
        $this->headers[$name] = $value;
    }


    // 设置要传递的一个数据， 键值对形式
    public function set_data($key, $val)
    {
        $this->data[$key] = $val;
    }


    // 发送请求, 返回body内容
    public function request()
    {
        return '';
    }


}

?>