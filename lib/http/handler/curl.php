<?php
namespace lib\http\handler;

/*
@版本日期: 2011年8月27日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class curl extends \lib\http\handler
{

    public function request()
    {
        $url = parse_url($this->url);
        $ssl = ($url['scheme'] == 'https' || $url['scheme'] == 'ssl');

        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL, $this->url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $ssl);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($handle, CURLOPT_USERAGENT, $this->headers['user_agent']);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $this->headers['timeout']);
        curl_setopt($handle, CURLOPT_TIMEOUT, $this->headers['timeout']);
        curl_setopt($handle, CURLOPT_MAXREDIRS, $this->headers['redirection']);

        if ($this->headers['method'] == 'POST' && count($this->data)) {
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }

        if ($this->headers['http_version'] == '1.0')
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

?>