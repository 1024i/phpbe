<?php
namespace lib\http;

/*
@版本日期: 2017年12月01日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/
class http extends \system\lib
{

    protected $options = null;
    protected $url = null;
    protected $data = null;
    protected $header = [];

    /**
     * 构造函数
     *
     * http constructor.
     * @throws \exception
     */
    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new \exception('您的服务器未安装用于HTTP通信的 CURL 扩展');
        }

        $this->init();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 初始化
     */
    public function init() {
        $this->options = [
            'connect_timeout' => 15,
            'timeout' => 30,
            'redirection' => 5,
            'http_version' => 1.0,
            'user_agent' => 'phpbe',
        ];
        $this->url = null;
        $this->data = [];
        $this->header = [];
    }

    /**
     * 设置项
     *
     * @param $name
     * @param $value
     */
    public function option($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * 设置头信息
     *
     * @param $value
     */
    public function header($value)
    {
        $this->header[] = $value;
    }

    /**
     * 验证身份
     *
     * @param $user
     * @param $pass
     */
    public function authorization($user, $pass) {
        $this->header[] = 'Authorization: Basic '.base64_encode($user.':'.$pass);
    }

    /**
     *
     * @param $url
     */
    public function set_url($url)
    {
        $this->url = $url;
    }

    /**
     * 设置要传递的一个数据， 键值对形式
     *
     * @param string | array $key 键名
     * @param mixed $val 值
     */
    public function set_data($key, $val = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $val;
        }
    }


    /**
     * GET 请求
     *
     * @param $url
     * @return bool|mixed
     */
    public function get($url)
    {
        $this->options['method'] = 'GET';
        $this->url = $url;
        return $this->request();
    }

    /**
     * POST 请求
     *
     * @param $url
     * @param $data
     * @return bool|mixed
     */
    public function post($url, $data = [])
    {
        $this->options['method'] = 'POST';
        $this->url = $url;
        $this->data = http_build_query($data);
        return $this->request();
    }

    /**
     * POST 请求，数据为JSON
     *
     * @param $url
     * @param mixed $data
     * @return bool|mixed
     */
    public function postJson($url, $data)
    {
        $this->options['method'] = 'POST';
        $this->header[] = 'Content-Type: application/json; charset=utf-8';
        $this->url = $url;
        $this->data = json_encode($data);
        return $this->request();
    }

    /**
     * 执行请求
     *
     * @return bool|mixed
     */
    private function request()
    {
        $url = parse_url($this->url);
        $ssl = ($url['scheme'] == 'https' || $url['scheme'] == 'ssl');

        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL, $this->url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $ssl);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($handle, CURLOPT_USERAGENT, $this->options['user_agent']);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $this->options['connect_timeout']);
        curl_setopt($handle, CURLOPT_TIMEOUT, $this->options['timeout']);
        curl_setopt($handle, CURLOPT_MAXREDIRS, $this->options['redirection']);

        if (count($this->header)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $this->header);
        }

        if (isset($this->options['userpwd'])) {
            // 是否权限认证，用户名：密码
            curl_setopt($handle, CURLOPT_USERPWD, $this->options['userpwd']);
        }

        if ($this->options['method'] == 'POST' && count($this->data)) {
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $this->data);
        }

        if ($this->options['http_version'] == '1.0')
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        else
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($handle, CURLOPT_HEADER, false);    //不返回头信息

        $response = curl_exec($handle);

        if (curl_errno($handle)) {
            $this->set_error('连接主机' . $this->url . '时发生错误: ' . curl_error($handle));
            curl_close($handle);

            return false;
        }

        curl_close($handle);
        return $response;
    }

}
