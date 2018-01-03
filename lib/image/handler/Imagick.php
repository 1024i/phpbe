<?php
namespace lib\image\handler;

/*
@创建日期: 2012年1月18日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com)
*/

class Imagick extends \system\Lib
{
    /**
     * @var \Imagick
     */
    private $image = null;
    private $type = null;

    // 构造函数
    public function __construct()
    {
    }

    // 析构函数
    public function __destruct()
    {
        if ($this->image !== null) $this->image->destroy();
    }

    // 载入图像
    public function open($path)
    {
        $this->image = new \Imagick($path);
        if ($this->image) {
            $this->type = strtolower($this->image->getImageFormat());
        }
        return $this->image;
    }

    public function crop($x = 0, $y = 0, $width = null, $height = null)
    {
        if ($width == null) $width = $this->image->getImageWidth() - $x;
        if ($height == null) $height = $this->image->getImageHeight() - $y;
        if ($width <= 0 || $height <= 0) return;
        
        if ($this->type == 'gif') {
            $image = $this->image;
            $canvas = new \Imagick();
            
            $images = $image->coalesceImages();
            foreach ($images as $frame) {
                $img = new \Imagick();
                $img->readImageBlob($frame);
                $img->cropImage($width, $height, $x, $y);
                
                $canvas->addImage($img);
                $canvas->setImageDelay($img->getImageDelay());
                $canvas->setImagePage($width, $height, 0, 0);
            }
            
            $image->destroy();
            $this->image = $canvas;
        } else {
            $this->image->cropImage($width, $height, $x, $y);
        }
    }

    /*
	* 更改图像大小
	$fit: 适应大小方式
	'force': 把图片强制变形成 $width X $height 大小
	'scale': 按比例在安全框 $width X $height 内缩放图片, 输出缩放后图像大小 不完全等于 $width X $height
	'scale_fill': 按比例在安全框 $width X $height 内缩放图片，安全框内没有像素的地方填充色, 使用此参数时可设置背景填充色 $bg_color = array(255,255,255)(红,绿,蓝, 透明度(0透明))
	其它: 智能模能 缩放图像并载取图像的中间部分 $width X $height 像素大小
	$fit = 'force','scale','scale_fill' 时： 输出完整图像
	$fit = 图像方位值 时, 输出指定位置部分图像 
	字母与图像的对应关系如下:
	
	northwest    north    northeast
	
	west         center        east
	
	southwest    south    southeast
	
	*/
    public function resize($width = 100, $height = 100, $fit = 'center', $fillColor = array(255,255,255,0))
    {
        
        switch ($fit)
        {
            case 'force':
                if ($this->type == 'gif') {
                    $image = $this->image;
                    $canvas = new \Imagick();
                    
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $img = new \Imagick();
                        $img->readImageBlob($frame);
                        $img->thumbnailImage($width, $height, false);
                        
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
                    $image->destroy();
                    $this->image = $canvas;
                } else {
                    $this->image->thumbnailImage($width, $height, false);
                }
                break;
            case 'scale':
                if ($this->type == 'gif') {
                    $image = $this->image;
                    $canvas = new \Imagick();
                    
                    $images = $image->coalesceImages();                    
                    foreach ($images as $frame) {
                        $img = new \Imagick();
                        $img->readImageBlob($frame);
                        $img->thumbnailImage($width, $height, true);
                        
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                    }
                    $image->destroy();
                    $this->image = $canvas;
                } else {
                    $this->image->thumbnailImage($width, $height, true);
                }
                break;
            case 'scale_fill':
                $size = $this->image->getImagePage();
                $srcWidth = $size['width'];
                $srcHeight = $size['height'];
                
                $x = 0;
                $y = 0;
                
                $dstWidth = $width;
                $dstHeight = $height;
                
                if ($srcWidth * $height > $srcHeight * $width) {
                    $dstHeight = intval($width * $srcHeight / $srcWidth);
                    $y = intval(($height - $dstHeight) / 2);
                } else {
                    $dstWidth = intval($height * $srcWidth / $srcHeight);
                    $x = intval(($width - $dstWidth) / 2);
                }
                
                $image = $this->image;
                $canvas = new \Imagick();
                
                $color = 'rgba(' . $fillColor[0] . ',' . $fillColor[1] . ',' . $fillColor[2] . ',' . $fillColor[3] . ')';
                if ($this->type == 'gif') {
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $frame->thumbnailImage($width, $height, true);
                        
                        $draw = new \ImagickDraw();
                        $draw->composite($frame->getImageCompose(), $x, $y, $dstWidth, $dstHeight, $frame);
                        
                        $img = new \Imagick();
                        $img->newImage($width, $height, $color, 'gif');
                        $img->drawImage($draw);
                        
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
                } else {
                    $image->thumbnailImage($width, $height, true);
                    
                    $draw = new \ImagickDraw();
                    $draw->composite($image->getImageCompose(), $x, $y, $dstWidth, $dstHeight, $image);
                    
                    $canvas->newImage($width, $height, $color, $this->getType());
                    $canvas->drawImage($draw);
                    $canvas->setImagePage($width, $height, 0, 0);
                }
                $image->destroy();
                $this->image = $canvas;
                break;
            default:
                $size = $this->image->getImagePage();
                $srcWidth = $size['width'];
                $srcHeight = $size['height'];
                
                $cropX = 0;
                $cropY = 0;
                
                $cropW = $srcWidth;
                $cropH = $srcHeight;
                
                if ($srcWidth * $height > $srcHeight * $width) {
                    $cropW = intval($srcHeight * $width / $height);
                } else {
                    $cropH = intval($srcWidth * $height / $width);
                }
                
                switch ($fit)
                {
                    case 'northwest':
                        $cropX = 0;
                        $cropY = 0;
                        break;
                    case 'north':
                        $cropX = intval(($srcWidth - $cropW) / 2);
                        $cropY = 0;
                        break;
                    case 'northeast':
                        $cropX = $srcWidth - $cropW;
                        $cropY = 0;
                        break;
                    case 'west':
                        $cropX = 0;
                        $cropY = intval(($srcHeight - $cropH) / 2);
                        break;
                    case 'center':
                        $cropX = intval(($srcWidth - $cropW) / 2);
                        $cropY = intval(($srcHeight - $cropH) / 2);
                        break;
                    case 'east':
                        $cropX = $srcWidth - $cropW;
                        $cropY = intval(($srcHeight - $cropH) / 2);
                        break;
                    case 'southwest':
                        $cropX = 0;
                        $cropY = $srcHeight - $cropH;
                        break;
                    case 'south':
                        $cropX = intval(($srcWidth - $cropW) / 2);
                        $cropY = $srcHeight - $cropH;
                        break;
                    case 'southeast':
                        $cropX = $srcWidth - $cropW;
                        $cropY = $srcHeight - $cropH;
                        break;
                    default:
                        $cropX = intval(($srcWidth - $cropW) / 2);
                        $cropY = intval(($srcHeight - $cropH) / 2);
                }
                
                $image = $this->image;
                $canvas = new \Imagick();
                
                if ($this->type == 'gif') {
                    $images = $image->coalesceImages();
                    foreach ($images as $frame) {
                        $img = new \Imagick();
                        $img->readImageBlob($frame);
                        $img->cropImage($cropW, $cropH, $cropX, $cropY);
                        $img->thumbnailImage($width, $height, true);
                        
                        $canvas->addImage($img);
                        $canvas->setImageDelay($img->getImageDelay());
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
                } else {
                    $image->cropImage($cropW, $cropH, $cropX, $cropY);
                    $image->thumbnailImage($width, $height, true);
                    $canvas->addImage($image);
                    $canvas->setImagePage($width, $height, 0, 0);
                }
                $image->destroy();
                $this->image = $canvas;
        }
    
    }

    // 添加水印图片
    public function watermark($path, $x = 0, $y = 0)
    {
        $watermark = new \Imagick($path);
        $draw = new \ImagickDraw();
        $draw->composite($watermark->getImageCompose(), $x, $y, $watermark->getImageWidth(), $watermark->getimageheight(), $watermark);
        
        if ($this->type == 'gif') {
            $image = $this->image;
            $canvas = new \Imagick();
            
            $images = $image->coalesceImages();
            foreach ($images as $frame) {
                $img = new \Imagick();
                $img->readImageBlob($frame);
                $img->drawImage($draw);
                
                $canvas->addImage($img);
                $canvas->setImageDelay($img->getImageDelay());
            }
            $image->destroy();
            $this->image = $canvas;
        } else {
            $this->image->drawImage($draw);
        }
    }

    // 添加水印文字
    public function text($text, $x = 0, $y = 0, $angle = 0, $style = array())
    {
		$font = isset($style['font'])?$style['font']:(PATH_ROOT.DS.'libs'.DS.'image'.DS.'fzxbsjw.ttf');
		$font_size = isset($style['font_size'])?$style['font_size']:20;

		$color = isset($style['color'])?$style['color']:array(64, 64, 64);
		$color = 'rgb('.implode(', ', $color).')';

        $pixel = new \ImagickPixel();
        $pixel->setColor($color);

        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($font_size);
        $draw->setFillColor($pixel);

        if (isset($style['under_color'])) $draw->setTextUnderColor($style['under_color']);
        
        if ($this->type == 'gif') {
            foreach ($this->image as $frame) {
                $frame->annotateImage($draw, $x, $y, $angle, $text);
            }
        } else {
            $this->image->annotateImage($draw, $x, $y, $angle, $text);
        }
    }

    // 保存到指定路径
    public function save($path)
    {
        $this->image->stripImage();
        
        if ($this->type == 'gif') {
            $this->image->writeImages($path, true);
        } else {
            $this->image->writeImage($path);
        }
    }

    // 输出图像
    public function output($header = true)
    {
        $this->image->stripImage();
        if ($header) header('Content-type: ' . $this->type);
        echo $this->image->getImagesBlob();
    }

    public function getWidth()
    {
        $size = $this->image->getImagePage();
        return $size['width'];
    }

    public function getHeight()
    {
        $size = $this->image->getImagePage();
        return $size['height'];
    }

    // 设置图像类型， 默认与源类型一致
    public function setType($type = 'png')
    {
        $this->type = $type;
        $this->image->setImageFormat($type);
    }

    // 获取源图像类型
    public function getType()
    {
        if ($this->type == 'jpeg') return 'jpg';
        return $this->type;
    }

    // 当前对象是否为图片
    public function isImage()
    {
        if ($this->image)
            return true;
        else
            return false;
    }

    // ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  == 以下为 imagick 特有函数. GD 库未实现

    // 生成缩略图 $fit为真时将保持比例并在安全框 $width X $height 内生成缩略图片
    public function thumbnail($width = 100, $height = 100, $fit = true)
    {
        $this->image->thumbnailImage($width, $height, $fit);
    }

    /*
	添加一个边框
	$width: 左右边框宽度
	$height: 上下边框宽度
	$color: 颜色: RGB 颜色 'rgb(255,0,0)' 或 16进制颜色 '#FF0000' 或颜色单词 'white'/'red'...
	*/
    public function border($width, $height, $color = 'rgb(220, 220, 220)')
    {
        $pixel = new \ImagickPixel();
        $pixel->setColor($color);
        $this->image->borderImage($pixel, $width, $height);
    }

    // 模糊
    public function blur($radius, $sigma)
    {
        $this->image->blurImage($radius, $sigma);
    }

    // 高斯模糊
    public function gaussianBlur($radius, $sigma)
    {
        $this->image->gaussianBlurImage($radius, $sigma);
    }

    // 运动模糊
    public function motionBlur($radius, $sigma, $angle)
    {
        $this->image->motionBlurImage($radius, $sigma, $angle);
    }

    // 径向模糊
    public function radialBlur($radius)
    {
        $this->image->radialBlurImage($radius);
    }

    // 添加噪点
    public function addNoise($type = null)
    {
        $this->image->addNoiseImage($type == null ? \Imagick::NOISE_IMPULSE : $type);
    }

    // 调整色阶
    public function level($black_point, $gamma, $white_point)
    {
        $this->image->levelImage($black_point, $gamma, $white_point);
    }

    // 调整亮度、饱和度、色调
    public function modulate($brightness, $saturation, $hue)
    {
        $this->image->modulateImage($brightness, $saturation, $hue);
    }

    // 素描
    public function charcoal($radius, $sigma)
    {
        $this->image->charcoalImage($radius, $sigma);
    }

    // 油画效果
    public function oilPaint($radius)
    {
        $this->image->oilPaintImage($radius);
    }

    // 水平翻转
    public function flop()
    {
        $this->image->flopImage();
    }

    // 垂直翻转
    public function flip()
    {
        $this->image->flipImage();
    }

}

