<?php
namespace app\system\tool;

class string
{

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
    public static function limit($string, $length = 50, $etc = '...')
    {
        $string = strip_tags($string);
        $length *= 2; //按中文时宽度应加倍


        if (strlen($string) <= $length) return $string;

        $length -= strlen($etc); // 去除结尾符长度
        if ($length <= 0) return '';

        $strLen = strlen($string);

        $pos = 0; // 当前处理到的字符位置
        $lastLen = 0; // 最后一次处理的字符所代表的文字的宽度
        $len = 0; // 文字宽度累加值


        while ($pos < $strLen) // 系统采用了utf-8编码， 逐字符判断
        {
            $char = ord($string[$pos]);
            if ($char == 9 || $char == 10 || (32 <= $char && $char <= 126)) {
                $lastLen = 1;
                $pos++;
                $len++;
            }
            elseif (192 <= $char && $char <= 223) {
                $lastLen = 2;
                $pos += 2;
                $len += 2;
            }
            elseif (224 <= $char && $char <= 239) {
                $lastLen = 3;
                $pos += 3;
                $len += 2;
            }
            elseif (240 <= $char && $char <= 247) {
                $lastLen = 4;
                $pos += 4;
                $len += 2;
            }
            elseif (248 <= $char && $char <= 251) {
                $lastLen = 5;
                $pos += 5;
                $len += 2;
            }
            elseif ($char == 252 || $char == 253) {
                $lastLen = 6;
                $pos += 6;
                $len += 2;
            } else {
                $pos++;
            }

            if ($len >= $length) break;
        }

        // 超过指定宽度， 减去最后一次处理的字符所代表的文字宽度
        if ($len >= $length) {
            $pos -= $lastLen;
            $string = substr($string, 0, $pos);
            $string .= $etc;
        }

        return $string;
    }


}
