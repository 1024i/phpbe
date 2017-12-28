<?php
namespace App\Cms\Config;

class Article
{
    public $getSummary = 80;  // 默认从内容中提取摘要长度
    public $getMetaKeywords = 10;  // 默认从内容中提取 META 关键词个数
    public $getMetaDescription = 80;  // 默认从内容中提取 META 描述长度
    public $downloadRemoteImage = true;  // 下载远程图片
    public $comment = true;  // 是否允许评论
    public $commentPublic = true;  // 评论是否默认公开 1：公开 0：不公开，等待管理员审核
    public $thumbnailLW = 800;  // 缩图图大图宽度
    public $thumbnailLH = 600;  // 缩图图大图高度
    public $thumbnailMW = 200;  // 缩图图中图宽度
    public $thumbnailMH = 150;  // 缩图图中图高度
    public $thumbnailSW = 100;  // 缩图图小图宽度
    public $thumbnailSH = 75;  // 缩图图小图高度
    public $defaultThumbnailL = '0L.gif';  // 默认缩略图大图 位于 DATA / article / thumbnail / default 文件夹下
    public $defaultThumbnailM = '0M.gif';  // 默认缩略图中图 位于 DATA / article / thumbnail / default 文件夹下
    public $defaultThumbnailS = '0S.gif';  // 默认缩略图小图 位于 DATA / article / thumbnail / default 文件夹下
    public $cacheExpire = 600;  // 缓存有效期（音位：秒）

}
