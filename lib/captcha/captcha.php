<?php
namespace lib\captcha;
/*
@版本日期: 2011年4月3日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class captcha extends \system\lib
{

    private $image = null;        // 画布
    private $width = 0;            // 宽度
    private $height = 0;        // 高度

    private $font_family = '';    // 字体
    private $font_size = 16;        // 大小
    private $font_color = array(0, 0, 0);        // 颜色

    private $bg_color = array(255, 255, 255);        // 背景颜色

    private $text;            // 输出的字符
    private $text_length = 4;    // 输出的字符长度

    // 构造函数
    public function __construct()
    {
        $this->font_family = PATH_ROOT . DS . 'libs' . DS . 'captcha' . DS . 'verdana.ttf';
    }


    // 析构函数
    public function __destruct()
    {
        if ($this->image) imagedestroy($this->image);
    }

    public function set_size($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }    // 设置图片大小

    public function set_width($width)
    {
        $this->width = $width;
    }    // 设置图片宽度

    public function set_height($height)
    {
        $this->height = $height;
    }    // 设置图片高度

    public function set_font_family($font_family)
    {
        $this->font_family = $font_family;
    }    // 设置字体 绝对路径

    public function set_font_size($font_size)
    {
        $this->font_size = $font_size;
    }    // 设置字体大小

    public function set_font_color($font_color)
    {
        $this->font_color = $font_color;
    }    // 设置字体颜色

    public function set_bg_color($bg_color)
    {
        $this->bg_color = $bg_color;
    }    // 设置背景颜色

    public function set_text_length($text_length)
    {
        $this->text_length = $text_length;
    }    // 设置字符长度


    public function init()
    {
        if ($this->width == 0) {
            $this->width = floor($this->font_size * 1.3) * $this->text_length + 10;
        }
        if ($this->height == 0) {
            $this->height = $this->font_size * 2;
        }

        $this->image = imagecreatetruecolor($this->width, $this->height);
        imagefill($this->image, 0, 0, imagecolorallocate($this->image, $this->bg_color[0], $this->bg_color[1], $this->bg_color[2]));

        $str = 'abcdefghijkmnpqrstuvwxy3456789';
        $len = strlen($str) - 1;
        for ($i = 0; $i < $this->text_length; $i++) {
            $this->text[] = $str[rand(0, $len)];
        }

        $font_color = imagecolorallocate($this->image, $this->font_color[0], $this->font_color[1], $this->font_color[2]);

        for ($i = 0; $i < $this->text_length; $i++) {
            $angle = rand(-1, 1) * rand(1, 30);
            imagettftext($this->image, $this->font_size, $angle, 5 + $i * floor($this->font_size * 1.3), floor($this->height * 0.75), $font_color, $this->font_family, $this->text[$i]);
        }
    }

    public function point($n = 100, $color = null)    //添加干扰点
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->font_color;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imagesetpixel($this->image, rand(0, $this->width), rand(0, $this->height), $color);
        }
    }


    public function line($n = 5, $color = null)    //添加干扰线
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->font_color;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imageline($this->image, 0, rand(0, $this->width), $this->width, rand(0, $this->height), $color);
        }
    }

    public function distortion()    //扭曲
    {
        if ($this->image == null) $this->init();

        $image = imagecreatetruecolor($this->width, $this->height);
        imagefill($image, 0, 0, imagecolorallocate($this->image, $this->bg_color[0], $this->bg_color[1], $this->bg_color[2]));
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $color = imagecolorat($this->image, $x, $y);
                imagesetpixel($image, (int)($x + sin($y / $this->height * 2 * M_PI - M_PI * 0.5) * 3), $y, $color);
            }
        }
        imagedestroy($this->image);
        $this->image = $image;
    }

    public function border($n = 1, $color = null)    //添加边框
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->font_color;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imagerectangle($this->image, $i, $i, $this->width - $i - 1, $this->height - $i - 1, $color);
        }
    }


    public function output($type = 'gif')    //输出图像
    {
        if ($this->image == null) $this->init();

        switch ($type) {
            case 'gif':
                header("Content-type: image/gif");
                imagegif ($this->image);
                break;
            case 'jpg':
            case 'jpeg':
                header("Content-type: image/jpeg");
                imagejpeg($this->image, '', 0.5);
                break;
            case 'png':
                header("Content-type: image/png");
                imagepng($this->image);
                break;
            case 'bmp':
                header("Content-type: image/vnd.wap.wbmp");
                imagewbmp($this->image);
                break;
            default:
                header("Content-type: image/gif");
                imagegif ($this->image);
                break;

        }
    }


    public function to_string()        //获取输出的字符
    {
        return is_array($this->text) ? implode('', $this->text) : '';
    }

}

?>