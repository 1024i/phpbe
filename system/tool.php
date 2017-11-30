<?php

/**
 * 处理网址
 * 
 * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
 * @return string
 */
function url($url)
{
    return \tool\system::url($url);
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
function limit($string, $length = 50, $etc = '...')
{
    return \tool\system::limit($string, $length, $etc);
}
