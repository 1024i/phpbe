<?php
namespace config;

class user_group
{
  public $names = array('1'=>'游客', '2'=>'普通会员');  // 用户组
  public $default = '2';  // 默认注册后的用户组
  public $permissions_1 = '1';
  public $permissions_2 = '1';
}
?>