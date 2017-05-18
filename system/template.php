<?php
namespace system;

/**
 * 模板基类
 */
class template
{
    public $title = ''; // 标题
    public $meta_keywords = ''; // meta keywords
    public $meta_description = '';  // meta description
    public $_message = null;  // 消息

    /*
    网站主色调
    数组 10 个元素
    下标（index）：0, 1, 2, 3, 4, 5, 6, 7, 8, 9，
    主颜色: $this->colors[0], 模板主要颜色，
    其它颜色 依次减淡 10%, 即 ([index]*10)%

    可以仅有一个元素 即 $this->colors[0], 指定下标不存在时自动跟据主颜色按百分比换算。
    */
    public $colors = array('#333333');

    public function get_color($index = 0)
    {
        if ($index == 0) return $this->colors[0];
        if (array_key_exists($index, $this->colors)) return $this->colors[$index];

        $lib_css = be::get_lib('css');
        return $lib_css->lighter($this->colors[0], $index*10);
    }

}
