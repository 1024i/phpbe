<?php
namespace App\System\Service;

use System\Be;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends \System\Service
{
    private $mailer = null;

    // 构造函数
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->SetLanguage('zh_cn');

        $config = Be::getConfig('System.Mail');
        if ($config->from_mail) $this->mailer->From = $config->from_mail;
        if ($config->from_name) $this->mailer->FromName = $config->from_name;

        $this->mailer->IsHTML(true);

        if ($config->charset) $this->mailer->CharSet = $config->charset;
        if ($config->encoding) $this->mailer->Encoding = $config->encoding;


        if ($config->smtp == 1) {
            $this->mailer->IsSMTP();
            $this->mailer->Host = $config->smtp_host; // smtp 主机地址
            $this->mailer->Port = $config->smtp_port; // smtp 主机端口
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config->smtp_user; // smtp 用户名
            $this->mailer->Password = $config->smtp_pass; // smtp 用户密码
            $this->mailer->Timeout = $config->smtp_timeout; // smtp 超时时间 秒

            if ($config->smtp_secure != '0') $this->mailer->SMTPSecure = $config->smtp_secure; // smtp 加密 'ssl' 或 'tls'
        }
    }

    // 析构函数
    public function __destruct()
    {
        $this->mailer = null;
    }


    public function setFrom($from_mail, $from_name = '')
    {
        $this->mailer->SetFrom($from_mail, $from_name);
    }


    public function setReplyTo($reply_to_mail, $reply_to_name = '')
    {
        $this->mailer->AddReplyTo($reply_to_mail, $reply_to_name);
    }


    // 添加收件人
    public function to($email, $name = '')
    {
        if (!$this->mailer->AddAddress($email, $name)) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }


    // 添加收件人
    public function cc($email, $name = '')
    {
        if (!$this->mailer->AddCC($email, $name)) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }


    // 添加收件人
    public function bcc($email, $name = '')
    {
        if (!$this->mailer->AddBCC($email, $name)) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }


    public function addAttachment($path)
    {
        if (!$this->mailer->AddAttachment($path)) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }

    public function setSubject($subject = '')
    {
        $this->mailer->Subject = $subject;
    }

    public function setBody($body = '')
    {
        $this->mailer->Body = $body;
    }

    // 设置不支持 html 的客户端显示的主体内容
    public function set_alt_body($alt_body = '')
    {
        $this->mailer->AltBody = $alt_body;
    }

    // 占位符格式化
    public function format($text, $data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $text = str_replace('{' . $key . '}', $val, $text);
            }
        } else {
            $text = str_replace('{0}', $data, $text);
        }

        return $text;
    }

    public function send()
    {
        if (!$this->mailer->Send()) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }

    public function verify($email)
    {
        return $this->mailer->ValidateAddress($email);
        //return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
    }
}
