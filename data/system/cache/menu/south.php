<?php
namespace data\system\cache\menu;

class south extends \system\menu
{
  public function __construct()
  {
    $this->add_menu(20, 0, '关于我们', url(''), '_self', array(), 0);
    $this->add_menu(21, 0, '测试1', url(''), '_self', array(), 0);
    $this->add_menu(22, 0, '测试2', url(''), '_self', array(), 0);
    $this->add_menu(23, 0, '测试3', url(''), '_self', array(), 0);
  }
}
