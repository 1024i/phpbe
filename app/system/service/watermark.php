<?php

namespace app\system\service;

use system\be;

class watermark extends \system\service
{

    public function save($image)
    {
        $lib_image = be::get_lib('image');
        $lib_image->open($image);

        if (!$lib_image->is_image()) {
            $this->set_error('不是合法的图片！');
            return false;
        }

        $width = $lib_image->get_width();
        $height = $lib_image->get_height();

        $config_watermark = be::get_config('watermark');

        $x = 0;
        $y = 0;
        switch ($config_watermark->position) {
            case 'north':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'northeast':
                $x = $width + $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'east':
                $x = $width + $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
            case 'southeast':
                $x = $width + $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'south':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'southwest':
                $x = $config_watermark->offset_x;
                $y = $height + $config_watermark->offset_y;
                break;
            case 'west':
                $x = $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
            case 'northwest':
                $x = $config_watermark->offset_x;
                $y = $config_watermark->offset_y;
                break;
            case 'center':
                $x = $width / 2 + $config_watermark->offset_x;
                $y = $height / 2 + $config_watermark->offset_y;
                break;
        }

        $x = intval($x);
        $y = intval($y);

        if ($config_watermark->type == 'text') {
            $style = array();
            $style['font_size'] = $config_watermark->text_size;
            $style['color'] = $config_watermark->text_color;

            // 添加文字水印
            $lib_image->text($config_watermark->text, $x, $y, 0, $style);
        } else {
            // 添加图像水印
            $lib_image->watermark(PATH_DATA . DS . 'system' . DS . 'watermark' . DS . $config_watermark->image, $x, $y);
        }

        $lib_image->save($image);

        return true;
    }


}
