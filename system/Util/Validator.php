<?php
namespace System\Util;

class Validator
{

    /**
     * 是否是合法的手机号码
     *
     * @param string $mobile 手机号码
     * @return bool
     */
    public static function isMobile($mobile)
    {
        return strlen($mobile) == 11 && preg_match('/^1[3|4|5|7|8][0-9]\d{4,8}$/', $mobile);
    }

    /**
     * 是否是合法的邮箱
     *
     * @param string $email 邮箱
     * @return bool
     */
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 是否是合法的IP
     *
     * @param string $ip
     * @return bool
     */
    public static function isIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * 是否是合法的MAC地址
     *
     * @param string $mac
     * @return bool
     */
    public static function isMac($mac)
    {
        return filter_var($mac, FILTER_VALIDATE_MAC);
    }

    /**
     * 是否是合法的域名
     *
     * @param string $domain
     * @return bool
     */
    public static function isDomain($domain)
    {
        return filter_var($domain, FILTER_VALIDATE_DOMAIN);
    }

    /**
     * 是否是合法的IP
     *
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }


    /**
     * 是否是合法的身份证号
     *
     * @param string $idCard
     * @return bool
     */
    public static function isIdCard($idCard)
    {
        if (strlen($idCard) > 18) return false;
        return preg_match("/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i", $idCard);
    }

    /**
     * 是否是合法的邮政编码
     *
     * @param string $postcode
     * @return bool
     */
    public static function isPostcode($postcode)
    {
        return preg_match('/\d{6}/', $postcode);
    }

    /**
     * 是否为中文
     *
     * @param string $str
     * @return bool
     */
    public static function isChinese($str)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    }


}
