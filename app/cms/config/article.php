<?php
namespace app\cms\config;

class article
{
    public $get_summary = 80;  // 默认从内容中提取摘要长度
    public $get_meta_keywords = 10;  // 默认从内容中提取 META 关键词个数
    public $get_meta_description = 80;  // 默认从内容中提取 META 描述长度
    public $download_remote_image = true;  // 下载远程图片
    public $comment = true;  // 是否允许评论
    public $comment_public = true;  // 评论是否默认公开 1：公开 0：不公开，等待管理员审核
    public $thumbnail_l_w = 800;  // 缩图图大图宽度
    public $thumbnail_l_h = 600;  // 缩图图大图高度
    public $thumbnail_m_w = 200;  // 缩图图中图宽度
    public $thumbnail_m_h = 150;  // 缩图图中图高度
    public $thumbnail_s_w = 100;  // 缩图图小图宽度
    public $thumbnail_s_h = 75;  // 缩图图小图高度
    public $default_thumbnail_l = '0_l.gif';  // 默认缩略图大图 位于 DATA / article / thumbnail / default 文件夹下
    public $default_thumbnail_m = '0_m.gif';  // 默认缩略图中图 位于 DATA / article / thumbnail / default 文件夹下
    public $default_thumbnail_s = '0_s.gif';  // 默认缩略图小图 位于 DATA / article / thumbnail / default 文件夹下
    public $cache_expire = 600;  // 缓存有效期（音位：秒）

}
