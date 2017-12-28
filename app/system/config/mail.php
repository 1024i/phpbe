<?php
namespace App\System\Config;

class Mail
{
    public $fromMail = 'be@phpbe.com';
    public $fromName = 'BE';
    public $charset = 'utf-8';
    public $encoding = 'base64';
    public $smtp = false;
    public $smtpHost = '';
    public $smtpPort = 25;
    public $smtpUser = '';
    public $smtpPass = '';
    public $smtpSecure = '0'; // 不加密 '0' 或 'ssl', 'tls'
    public $smtpTimeout = 10;
}
