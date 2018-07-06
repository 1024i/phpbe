<?php
namespace App\System\Config;

class System
{
    public $debug = true;  // 是否开启调试模式
    public $errorLog = E_ALL | E_STRICT;  // 错误日志模式 0: 不开启 大于0：处理对应的错误，同 errorReporting
    public $offline = false;  // 是否暂停网站
    public $offlineMessage = '<p>系统升级，请稍候访问。</p>';  // 暂停网站时显示的信息
    public $siteName = 'BE';  // 网站名称
    public $sef = false;  // 是否开启伪静态
    public $sefSuffix = '.html';  // 伪静态页后辍
    public $theme = 'huxiu';  // 主题
    public $homeParams = ['app'=>'Cms', 'controller'=>'Article', 'action'=>'home'];  // 默认首页参数
    public $homeTitle = '首页';  // 首页的标题
    public $homeMetaKeywords = '';  // 首页的 meta keywords
    public $homeMetaDescription = '';  // 首页的 meta description
    public $allowUploadFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];  // 允许上传的文件类型
    public $allowUploadImageTypes = ['jpg', 'jpeg', 'gif', 'png'];  // 允许上传的图片类型
    public $timezone = 'Asia/Shanghai'; // 时区
}
