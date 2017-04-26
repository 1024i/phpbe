<?php
namespace lib\image\handler;

/*
@版本日期: 2010年5月18日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class gd extends \system\lib
{
    private $image = null;
    private $type = null;
    private $width = 0;
    private $height = 0;

    // 构造函数
    public function __construct()
    {
    }


    // 析构函数
    public function __destruct()
    {
        if ($this->image) imagedestroy($this->image);
    }

    // 载入图像
    public function open($path)
    {
        $image_info = getimagesize($path);

        if ($image_info && is_array($image_info)) {
            $this->type = $image_info['mime'];
            $this->width = $image_info[0];
            $this->height = $image_info[1];
            $this->image = $this->load($path, $this->type);
        }
        return false;
    }


    public function crop($x = 0, $y = 0, $width = null, $height = null)
    {
        if (!$this->image) return false;
        if ($x > $this->width || $y > $this->height) return false;

        if (!$width) $width = $this->width;
        if (!$height) $height = $this->height;

        if (($x + $width) > $this->width) {
            $width = $this->width - $x;
        }

        if (($y + $height) > $this->height) {
            $height = $this->height - $y;
        }

        $new_image = imagecreatetruecolor($width, $height);
        if ($this->type == 'image/gif') {
            $color = imagecolortransparent($this->image);
            imagepalettecopy($this->image, $new_image);
            imagefill($new_image, 0, 0, $color);
            imagecolortransparent($new_image, $color);
            imagetruecolortopalette($new_image, true, 255);
            imagecopyresized($new_image, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        } elseif ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $color = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $width, $height, $color);
            imagecopyresampled($new_image, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        } else {
            imagealphablending($new_image, false);
            imagecopyresampled($new_image, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        }

        imagedestroy($this->image);

        $this->image = $new_image;
        $this->width = $width;
        $this->height = $height;
    }

    /*
    * 更改图像大小
    $fit: 适应大小方式
    'force': 把图片强制变形成 $width X $height 大小
    'scale': 按比例在安全框 $width X $height 内缩放图片, 输出缩放后图像大小 不完全等于 $width X $height
    'scale_fill': 按比例在安全框 $width X $height 内缩放图片，安全框内没有像素的地方填充色, 使用此参数时可设置背景填充色 $bg_color = array(255,255,255)(红,绿,蓝, 透明度) 透明度(0不透明-127完全透明))
    其它: 智能模式 缩放图像并载取图像的中间部分 $width X $height 像素大小
    $fit = 'force','scale','scale_fill' 时： 输出完整图像
    $fit = 图像方位值 时, 输出指定位置截取的部分图像
    字母与图像的对应关系如下:

    northwest    north    northeast

    west         center        east

    southwest    south    southeast

    */
    public function resize($width = 100, $height = 100, $fit = 'center', $fill_color = array(255, 255, 255, 127))
    {
        if (!$this->image) return false;

        $src_x = 0;
        $src_y = 0;
        $dest_x = 0;
        $dest_y = 0;
        $src_w = $this->width;
        $src_h = $this->height;
        $dest_w = $new_width = $width;
        $dest_h = $new_height = $height;

        switch ($fit) {
            case 'force':
                break;
            case 'scale':
                if ($this->width * $height > $this->height * $width) {
                    $dest_h = $new_height = intval($this->height * $width / $this->width);
                } else {
                    $dest_w = $new_width = intval($this->width * $height / $this->height);
                }
                break;
            case 'scale_fill':
                if ($this->width * $height > $this->height * $width) {
                    $dest_h = intval($this->height * $width / $this->width);
                    $dest_y = intval(($height - $dest_h) / 2);
                } else {
                    $dest_w = intval($this->width * $height / $this->height);
                    $dest_x = intval(($width - $dest_w) / 2);
                }
                break;
            default:

                if ($this->width * $height > $this->height * $width) {
                    $src_w = $width * $this->height / $height;
                } else {
                    $src_h = $height * $this->width / $width;
                }

                switch ($fit) {
                    case 'northwest':
                        $src_x = 0;
                        $src_y = 0;
                        break;
                    case 'north':
                        $src_x = intval(($this->width - $src_w) / 2);
                        $src_y = 0;
                        break;
                    case 'northeast':
                        $src_x = $this->width - $src_w;
                        $src_y = 0;
                        break;
                    case 'west':
                        $src_x = 0;
                        $src_y = intval(($this->height - $src_h) / 2);
                        break;
                    case 'center':
                        $src_x = intval(($this->width - $src_w) / 2);
                        $src_y = intval(($this->height - $src_h) / 2);
                        break;
                    case 'east':
                        $src_x = $this->width - $src_w;
                        $src_y = intval(($this->height - $src_h) / 2);
                        break;
                    case 'southwest':
                        $src_x = 0;
                        $src_y = $this->height - $src_h;
                        break;
                    case 'south':
                        $src_x = intval(($this->width - $src_w) / 2);
                        $src_y = $this->height - $src_h;
                        break;
                    case 'southeast':
                        $src_x = $this->width - $src_w;
                        $src_y = $this->height - $src_h;
                        break;
                    default:
                        $src_x = intval(($this->width - $src_w) / 2);
                        $src_y = intval(($this->height - $src_h) / 2);
                }

                break;
        }

        $new_image = imagecreatetruecolor($new_width, $new_height);

        if ($this->type == 'image/gif') {
            imagepalettecopy($this->image, $new_image);
            if ($fill_color[3]) {
                $color = imagecolorallocatealpha($new_image, $fill_color[0], $fill_color[1], $fill_color[2], $fill_color[3]);
                imagefill($new_image, 0, 0, $color);
                imagecolortransparent($new_image, $color);
            } else {
                $color = imagecolorallocate($new_image, $fill_color[0], $fill_color[1], $fill_color[2]);
                imagefill($new_image, 0, 0, $color);
            }
            imagetruecolortopalette($new_image, true, 255);
            imagecopyresized($new_image, $this->image, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
        } elseif ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $color = imagecolorallocatealpha($new_image, $fill_color[0], $fill_color[1], $fill_color[2], $fill_color[3]);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $color);
            imagecopyresampled($new_image, $this->image, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
        } else {
            imagealphablending($new_image, false);
            if ($fit == 'scale_fill') {
                $color = imagecolorallocate($new_image, $fill_color[0], $fill_color[1], $fill_color[2]);
                imagefill($new_image, 0, 0, $color);
            }
            imagecopyresampled($new_image, $this->image, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);
        }

        imagedestroy($this->image);

        $this->image = $new_image;
        $this->width = $new_width;
        $this->height = $new_height;
    }


    // 添加水印
    public function watermark($path, $x = 0, $y = 0)
    {
        if (!$this->image) return false;

        $image_info = getimagesize($path);

        if ($image_info && is_array($image_info)) {
            $type = $image_info['mime'];
            $width = $image_info[0];
            $height = $image_info[1];
            $image = $this->load($path, $type);

            if (!$image) return false;
            imagecopymerge($this->image, $image, $x, $y, 0, 0, $width, $height, 100);
            return true;
        }
        return false;
    }

    // 添加水印文字
    public function text($text, $x = 0, $y = 0, $angle = 0, $style = array())
    {
        $font = isset($style['font']) ? $style['font'] : (PATH_ROOT . DS . 'libs' . DS . 'image' . DS . 'fzxbsjw.ttf');
        $font_size = isset($style['font_size']) ? $style['font_size'] : 20;
        $color = isset($style['color']) ? imagecolorallocate($this->image, $style['color'][0], $style['color'][1], $style['color'][2]) : imagecolorallocate($this->image, 64, 64, 64);

        imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font, $text);
    }


    // 保存到指定路径
    public function save($path)
    {
        if (!$this->image) return false;
        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagepng($this->image, $path);
        } elseif ($this->type == 'image/gif') {
            imagegif ($this->image, $path);
        } else {
            imagejpeg($this->image, $path, 80);
        }
    }

    // 输出图像
    public function output($header = true)
    {
        if (!$this->image) return false;
        if ($header) header('Content-type: ' . $this->type);
        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagepng($this->image);
        } elseif ($this->type == 'image/gif') {
            imagegif ($this->image);
        } else {
            imagejpeg($this->image, null, 80);
        }
    }

    public function get_width()
    {
        return $this->width;
    }

    public function get_height()
    {
        return $this->height;
    }

    // 设置图像类型， 默认与源类型一致
    public function set_type($type)
    {
        switch ($type) {
            case 'png':
                $this->type = 'image/png';
                break;
            case 'gif':
                $this->type = 'image/gif';
                break;
            default:
                $this->type = 'image/jpg';
                break;
        }
    }

    // 获取源图像类型
    public function get_type()
    {
        if (!$this->type) return 'unknown';

        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            return 'png';
        } elseif ($this->type == 'image/gif') {
            return 'gif';
        }
        return 'jpg';
    }

    // 当前对象是否为图片
    public function is_image()
    {
        if ($this->image)
            return true;
        else
            return false;
    }

    // 加载图像
    private function load($path, $type)
    {
        $image = null;
        // jpeg
        if (function_exists('imagecreatefromjpeg') && (($type == 'image/jpg') || ($type == 'image/jpeg') || ($type == 'image/pjpeg'))) {
            $image = @imagecreatefromjpeg($path);
            if ($image !== false) {
                return $image;
            }
        }

        // png
        if (function_exists('imagecreatefrompng') && (($type == 'image/png') || ($type == 'image/x-png'))) {
            $image = @imagecreatefrompng($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gif
        if (function_exists('imagecreatefromgif') && (($type == 'image/gif'))) {
            $image = @imagecreatefromgif ($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gd
        if (function_exists('imagecreatefromgd')) {
            $image = imagecreatefromgd($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gd2
        if (function_exists('imagecreatefromgd2')) {
            $image = @imagecreatefromgd2($path);
            if ($image !== false) {
                return $image;
            }
        }

        // bmp
        if (function_exists('imagecreatefromwbmp')) {
            $image = @imagecreatefromwbmp($path);
            if ($image !== false) {
                return $image;
            }
        }

        return $image;
    }


}
