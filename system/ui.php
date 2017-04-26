<?php
namespace system;

/**
 * 界面模块基类
 */
abstract class ui extends obj
{
    
	abstract function head();    // 引入相关文件到 html 的 head 区域
    abstract function display();    // 输出
    
}