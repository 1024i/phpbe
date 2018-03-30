<?php

/**
 * 处理网址
 * 
 * @param string $url 要处理的网址，启用 SEF 时生成伪静态页， 为空时返回网站网址
 * @return string
 */
function url($url)
{
    return \System\Util\System::url($url);
}

function adminUrl($url)
{
    return \System\Util\System::adminUrl($url);
}
