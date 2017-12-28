<?php
namespace lib\image;

/*
@版本日期: 2010年5月18日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class Image extends \system\Lib
{
    private $handler = null;
    private $imagick = false;
    private $gd = false;

    // 构造函数
    public function __construct()
    {
        if (class_exists('Imagick')) {
            $this->handler = new \lib\image\handler\Imagick();
            $this->imagick = true;
        } else {
            $this->handler = new \lib\image\handler\Gd();
            $this->gd = true;
        }
    }

    // 析构函数
    public function __destruct()
    {
        $this->handler = null;
    }
    
    // 检测当前是否为 imagick 处理器
    public function isImagick()
    {
        return $this->imagick;
    }
    
    // 检测当前是否为  GD 处理器
    public function isGD()
    {
        return $this->gd;
    }
    
    // 获取处理器
    public function getHandler()
    {
        return $this->handler;
    }

    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->handler, $fn), $args);
    }

}
