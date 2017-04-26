<?php
namespace config;

class system
{
    public $error_log = '0';  // 错误日志模式 0: 不开启 1:直接输出 2：写入error文件 3:直接输出+写入error文件
    public $offline = '0';  // 是否暂停网站
    public $offline_message = '<p>系统升级，请稍候访问。</p>';  // 暂停网站时显示的信息
    public $site_name = 'BE v2.0';  // 网站名称
    public $sef = '0';  // 是否开启伪静态
    public $sef_suffix = '.html';  // 伪静态页后辍
    public $theme = 'huxiu';  // 默认主题
    public $home_params = array('controller'=>'article', 'task'=>'home');  // 默认首页参数
    public $home_title = '首页';  // 首页的标题
    public $home_meta_keywords = '';  // 首页的 meta keywords
    public $home_meta_description = '';  // 首页的 meta description
    public $allow_upload_file_types = array('jpg', 'jpeg', 'gif', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar');  // 允许上传的文件类型
    public $allow_upload_image_types = array('jpg', 'jpeg', 'gif', 'png');  // 允许上传的图片类型
}
