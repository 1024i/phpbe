<?php
namespace app\system\tool;

class random
{

    /**
     * 数字字符串
     *
     * @param int $n 长度
     * @return string
     */
    public static function number($n = 8) {
        return self::create($n, '0123456789');
    }

    /**
     * 普通字符串，英文小写+数字
     *
     * @param int $n 长度
     * @return string
     */
    public static function simple($n = 8) {
        return self::create($n, 'abcdefghijklmnopqrstuvwxyz0123456789');
    }

    /**
     * 复杂字符串，英文大写+英文小写+数字
     *
     * @param int $n 长度
     * @return string
     */
    public static function complex($n = 8) {
        return self::create($n, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
    }

    /**
     * 安全字符串，英文大写+英文小写+数字+特殊字符
     *
     * @param int $n 长度
     * @return string
     */
    public static function secure($n = 8) {
        return self::create($n, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=');
    }

    /**
     * 生成随机字符串
     *
     * @param int $n 长度
     * @param string $seed 种子
     * @return string
     */
    public static function create($n, $seed) {
        $return = '';
        $len = strlen($seed) - 1;
        for ($i = 0; $i < $n; $i++){
            $return .= $seed[rand(0, $len)];
        }
        return $return;
    }

}
