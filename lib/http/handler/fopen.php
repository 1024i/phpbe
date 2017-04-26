<?php
namespace lib\http\handler;

/*
@创建日期: 2011年8月27日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class fopen extends \lib\http\handler
{

    public function request()
    {
        $url = parse_url($this->url);
        $ssl = ($url['scheme'] == 'https' || $url['scheme'] == 'ssl');

        $headers = '';
        foreach ($this->headers as $name => $value) $headers .= "{$name}: $value\r\n";

        $contexts = array('http' =>
            array(
                'method' => $this->headers['method'],
                'user_agent' => $this->headers['user_agent'],
                'max_redirects' => $this->headers['redirection'],
                'protocol_version' => (float)$this->headers['http_version'],
                'header' => $headers,
                'timeout' => $this->headers['timeout'],
                'ssl' => array(
                    'verify_peer' => $ssl,
                    'verify_host' => $ssl
               )
           )
       );

        if ($this->headers['method'] == 'POST' && count($this->data)) $contexts['http']['content'] = http_build_query($this->data);

        $context = stream_context_create($contexts);
        $handle = fopen($this->url, 'r', false, $context);
        stream_set_timeout($handle, $this->headers['timeout']);
        $response = stream_get_contents($handle);
        fclose($handle);

        return $response;

    }

}

?>