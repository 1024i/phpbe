<?php
namespace data\system\cache\menu;

class north extends \system\menu
{
  public function __construct()
  {
    $this->add_menu(1, 0, '首页', url('controller=article&task=home'), '_self', array('controller'=>'article','task'=>'home'), 1);
    $this->add_menu(7, 0, '看点', url('controller=article&task=articles&category_id=44'), '_self', array('controller'=>'article','task'=>'articles','category_id'=>'44'), 0);
    $this->add_menu(6, 0, '观点', url('controller=article&task=articles&category_id=45'), '_self', array('controller'=>'article','task'=>'articles','category_id'=>'45'), 0);
    $this->add_menu(19, 0, '图说', url('controller=article&task=articles&category_id=46'), '_self', array('controller'=>'article','task'=>'articles','category_id'=>'46'), 0);
  }
}
