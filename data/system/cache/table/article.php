<?php
namespace data\system\cache\table;

class article extends \system\table
{
    protected $table_name = 'be_article'; // 表名
    protected $primary_key = 'id'; // 主键
    protected $fields = 'id,category_id,thumbnail_l,thumbnail_m,thumbnail_s,title,meta_keywords,meta_description,summary,body,home,create_time,create_by_id,create_by_name,modify_time,modify_by_id,modify_by_name,like,dislike,hits,top,block,rank'; // 字段列表

}

