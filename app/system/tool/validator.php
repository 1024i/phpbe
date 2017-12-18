<?php

namespace app\system\tool;

class validator
{

    /**
     * 是否是手机号码
     *
     * @param string $mobile 手机号码
     * @return bool
     */
    public static function is_mobile($mobile)
    {
        return strlen($mobile) == 11 && preg_match('/^1[3|4|5|7|8][0-9]\d{4,8}$/', $mobile);
    }

    /**
     * 是否是邮箱
     *
     * @param string $email 邮箱
     * @return bool
     */
    public static function is_email($email)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
    }

    /**
     * 是否是IP
     *
     * @param string $ip
     * @return bool
     */
    public static function is_ip($ip)
    {
        return preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $ip);
    }

    /**
     * 是否是IP
     *
     * @param string $url
     * @return bool
     */
    public static function is_url($url)
    {
        return preg_match("/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/", $url);
    }

    /**
     * 是否是身份证号
     *
     * @param string $id_card
     * @return bool
     */
    public static function is_id_card($id_card)
    {
        if (strlen($id_card) > 18) return false;
        return preg_match("/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i", $id_card);
    }

    /**
     * 是否是邮政编码
     *
     * @param string $postcode
     * @return bool
     */
    public static function is_postcode($postcode)
    {
        return preg_match('/\d{6}/', $postcode);
    }

    /**
     * 是否为中文
     *
     * @param string $str
     * @return bool
     */
    public static function is_chinese($str)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    }


}
