<?php
namespace lib\http\handler;

/*
@版本日期: 2011年8月30日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class fsockopen extends \lib\http\handler
{

    public function request()
    {
        $url = parse_url($this->url);

        $host = $url['host'];
        $port = 80;
        $ssl = false;

        $path = '/';
        if (isset($url['path'])) $path = $url['path'] . (isset($url['query']) ? '?' . $url['query'] : '');

        if (!isset($url['port'])) {
            if (($url['scheme'] == 'ssl' || $url['scheme'] == 'https') && extension_loaded('openssl')) {
                $host = 'ssl://' . $host;
                $port = 443;
                $ssl = true;
            }
        }

        $error_num = null; // 存储错误编号
        $error_msg = null; // 存储错误信息

        $handle = fsockopen($host, $port, $error_num, $error_msg, $this->headers['timeout']);
        if (false === $handle) {
            $this->set_error($error_num . ': ' . $error_msg);
            return null;
        }
        stream_set_timeout($handle, $this->headers['timeout']);

        $headers = $this->headers['method'] . ' ' . $path . ' HTTP/' . $this->headers['http_version'] . "\r\n";
        $headers .= 'Host: ' . $host . "\r\n";
        $headers .= 'User-agent: ' . $this->headers['user_agent'] . "\r\n";

        if ($this->headers['method'] == 'POST' && count($this->data)) {
            $data = http_build_query($this->data);
            $headers .= 'Content-Type: application/x-www-form-urlencoded; charset=utf-8' . "\r\n";
            $headers .= 'Content-Length: ' . strlen($data) . "\r\n";
            $headers .= "\r\n";
            $headers .= $data;
        } else {
            $headers .= 'Content-Length: 0' . "\r\n";
            $headers .= "\r\n";
        }

        fwrite($handle, $headers);

        $response = '';
        while (!feof($handle)) $response .= fread($handle, 4096);
        fclose($handle);

        list($header, $body) = explode("\r\n\r\n", $response, 2);

        return $body;

    }

}

?>