<?php

namespace theme\sample;


class config
{
    /*
     * 在BE系统官网上存放的主题 ID, 用于识别用户安装。
     * 不需要通过BE系统官网管理时可以直接设为 0
     */
    public $id = 0;

    public $name = '示例主题'; // 主题名称
    public $description = '示例主题'; // 主题描述

    public $author = 'Lou Barnes';  // 作者
    public $authorEmail = 'lou@loubarnes.com'; // 作者邮箱
    public $authorWebsite = 'http://www.1024i.com'; // 作者网站

    public $colors = array('#333333');

    /*
     * 缩略图文件路径
     */
    public $thumbnailL = URL_ROOT . '/theme/sample/l.jpg';  // 缩略图大图 800 x 800 px
    public $thumbnailM = URL_ROOT . '/theme/sample/m.jpg';  // 缩略图中图 400 x 400 px
    public $thumbnailS = URL_ROOT . '/theme/sample/s.jpg';  // 缩略图小图 200 x 200 px
}
