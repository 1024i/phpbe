<?php
namespace App\System\Config;

class Watermark
{
  public $watermark = 1;  // 是否启用 0:不启用 1:启用
  public $type = 'image';  // 类型 text:文字 image:图像
  public $position = 'southeast';  // 水印位置 north/northeast/east/southeast/south/southwest/west/northwest/center
  public $offsetX = -70;  // 水平偏移像素值
  public $offsetY = -70;  // 垂直偏移像素值
  public $text = 'BE';  // 文印文字
  public $textSize = 20;  // 文印文字大小
  public $textColor = ['255', '255', '255'];  // 文印文字颜色
  public $image = '0.png';  // 图像水印文件，位于 DATA / system / watermark 文件夹下
}
