<?php
namespace App\System\AdminController;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;

/**
 * 配置中心
 *
 * @package App\System\AdminController
 */
class Config extends \Phpbe\System\AdminController
{

    // 配置中心
    public function dashboard()
    {
        $service = Be::getService('System', 'Config');
        $configTree = $service->getConfigTree();
        Response::set('configTree', $configTree);

        Response::setTitle('配置中心');
        Response::display();
    }

    // 配置
    public function edit()
    {
        $app = Request::get('app');
        $config = Request::get('config');

        $service = Be::getService('System', 'Config');
        $config = $service->getConfig($app, $config);

        Response::set('config', $config);

        Response::setTitle('配置中心');
        Response::display();
    }

    public function save()
    {

    }



    // 系统配置
    public function config()
    {
        Response::setTitle('系统基本设置');
        Response::set('config', Be::getConfig('System', 'System'));
        Response::display();
    }

    public function configSave()
    {
        $config = Be::getConfig('System', 'System');
        $config->offline = Request::post('offline', 0, 'int');
        $config->offlineMessage = Request::post('offlineMessage', '', 'html');
        $config->siteName = Request::post('siteName', '');
        $config->sef = Request::post('sef', 0, 'int');
        $config->sefSuffix = Request::post('sefSuffix', '');
        $config->homeTitle = Request::post('homeTitle', '');
        $config->homeMetaKeywords = Request::post('homeMetaKeywords', '');
        $config->homeMetaDescription = Request::post('homeMetaDescription', '');

        $allowUploadFileTypes = Request::post('allowUploadFileTypes', '');
        $allowUploadFileTypes = explode(',', $allowUploadFileTypes);
        $allowUploadFileTypes = array_map('trim', $allowUploadFileTypes);
        $config->allowUploadFileTypes = $allowUploadFileTypes;

        $allowUploadImageTypes = Request::post('allowUploadImageTypes', '');
        $allowUploadImageTypes = explode(',', $allowUploadImageTypes);
        $allowUploadImageTypes = array_map('trim', $allowUploadImageTypes);
        $config->allowUploadImageTypes = $allowUploadImageTypes;

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($config, Be::getRuntime()->getDataPath() . '/config/system.php');

        systemLog('改动系统基本设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&action=config');
    }


    // 邮件服务配置
    public function configMail()
    {
        $config = Be::getConfig('System', 'Mail');

        Response::setTitle('发送邮件设置');
        Response::set('config', $config);
        Response::display();
    }

    public function configMailSave()
    {
        $config = Be::getConfig('System', 'Mail');

        $config->fromMail = Request::post('fromMail', '');
        $config->fromName = Request::post('fromName', '');
        $config->smtp = Request::post('smtp', 0, 'int');
        $config->smtpHost = Request::post('smtpHost', '');
        $config->smtpPort = Request::post('smtpPort', 0, 'int');
        $config->smtpUser = Request::post('smtpUser', '');
        $config->smtpPass = Request::post('smtpPass', '');
        $config->smtpSecure = Request::post('smtpSecure', '');

        $serviceSystem = Be::getService('system');
        $serviceSystem->updateConfig($config, Be::getRuntime()->getDataPath() . '/config/mail.php');

        systemLog('改动发送邮件设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&action=configMail');
    }

    public function configMailTest()
    {
        Response::setTitle('发送邮件测试');
        Response::display();
    }

    public function configMailTestSave()
    {
        $toEmail = Request::post('toEmail', '');
        $subject = Request::post('subject', '');
        $body = Request::post('body', '', 'html');

        $libMail = Be::getLib('Mail');
        $libMail->setSubject($subject);
        $libMail->setBody($body);
        $libMail->to($toEmail);

        if ($libMail->send()) {
            systemLog('发送测试邮件到 ' . $toEmail . ' -成功');
            Response::setMessage('发送邮件成功！');
        } else {
            $error = $libMail->getError();

            systemLog('发送测试邮件到 ' . $toEmail . ' -失败：' . $error);
            Response::setMessage('发送邮件失败：' . $error, 'error');
        }

        Response::redirect('./?app=System&controller=System&action=configMailTest&toEmail=' . $toEmail);
    }


    // 水印设置
    public function configWatermark()
    {
        $config = Be::getConfig('System', 'Watermark');

        Response::setTitle('水印设置');
        Response::set('config', $config);
        Response::display();
    }

    private function isRgbColor($arr)
    {
        if (!is_array($arr)) return false;
        if (count($arr) != 3) return false;
        foreach ($arr as $x) {
            if (!is_numeric($x)) return false;
            $x = intval($x);
            if ($x < 0) return false;
            if ($x > 255) return false;
        }
        return true;
    }

    public function configWatermarkSave()
    {
        $config = Be::getConfig('System', 'Watermark');

        $config->watermark = Request::post('watermark', 0, 'int');
        $config->type = Request::post('type', '');
        $config->position = Request::post('position', '');
        $config->offsetX = Request::post('offsetX', 0, 'int');
        $config->offsetY = Request::post('offsetY', 0, 'int');

        $config->text = Request::post('text', '');
        $config->textSize = Request::post('textSize', 0, 'int');

        $textColor = Request::post('textColor', '');
        $textColors = explode(',', $textColor);
        $textColors = array_map('trim', $textColors);

        if (!$this->isRgbColor($textColors)) $textColors = array(255, 0, 0);
        $config->textColor = $textColors;

        $image = $_FILES['image'];
        if ($image['error'] == 0) {
            $libImage = Be::getLib('image');
            $libImage->open($image['tmpName']);
            if ($libImage->isImage()) {
                $watermarkName = date('YmdHis') . '.' . $libImage->getType();
                $watermarkPath = Be::getRuntime()->getDataPath() . '/system/watermark/' .  $watermarkName;
                if (move_uploaded_file($image['tmpName'], $watermarkPath)) {
                    // @unlink(Be::getRuntime()->getDataPath().'/system/watermark/'.$config->image);
                    $config->image = $watermarkName;
                }
            }
        }

        $serviceSystem = Be::getService('System', 'System');
        $serviceSystem->updateConfig($config, Be::getRuntime()->getDataPath() . '/Config/Watermark.php');

        systemLog('修改水印设置');

        Response::setMessage('保存成功！');
        Response::redirect('./?app=System&controller=System&action=configWatermark');
    }

    public function configWatermarkTest()
    {
        $src = Be::getRuntime()->getDataPath() . '/System/Watermark/test-0.jpg';
        $dst = Be::getRuntime()->getDataPath() . '/System/Watermark/test-1.jpg';

        if (!file_exists($src)) Response::end(DATA . '/System/Watermark/test-0.jpg 文件不存在');
        if (file_exists($dst)) @unlink($dst);

        copy($src, $dst);

        sleep(1);

        $adminServiceSystem = Be::getService('System', 'Admin');
        $adminServiceSystem->watermark($dst);

        Response::setTitle('水印预览');
        Response::display();
    }

}