<?php
namespace data\cache\menu;

class dashboard extends \system\menu
{
  public function __construct()
  {
    $this->add_menu(12, 0, '用户中心', url('controller=user_profile&task=home'), '_self', array('controller'=>'user_profile','task'=>'home'), 0);
    $this->add_menu(13, 0, '账号设置', url(''), '_self', array(), 0);
    $this->add_menu(15, 13, '上传头像', url('controller=user_profile&task=edit_avatar'), '_self', array('controller'=>'user_profile','task'=>'edit_avatar'), 0);
    $this->add_menu(16, 13, '更改密码', url('controller=user_profile&task=edit_password'), '_self', array('controller'=>'user_profile','task'=>'edit_password'), 0);
    $this->add_menu(17, 13, '修改资料', url('controller=user_profile&task=edit'), '_self', array('controller'=>'user_profile','task'=>'edit'), 0);
    $this->add_menu(14, 0, '退出', url('controller=user&task=logout'), '_self', array('controller'=>'user','task'=>'logout'), 0);
  }
}
