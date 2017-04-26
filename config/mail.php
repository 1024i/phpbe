<?php
namespace config;

class mail
{
    public $from_mail = 'be@phpbe.com';
    public $from_name = 'BE';
    public $charset = 'utf-8';
    public $encoding = 'base64';
    public $smtp = '0';
    public $smtp_host = '';
    public $smtp_port = '25';
    public $smtp_user = '';
    public $smtp_pass = '';
    public $smtp_secure = '0'; // 不加密 '0' 或 'ssl', 'tls'
    public $smtp_timeout = '10';
}
