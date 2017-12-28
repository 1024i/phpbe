<?php

namespace app\system\service;

use System\Be;

class watermark extends \System\Service
{

    public function save($image)
    {
        $libImage = Be::getLib('image');
        $libImage->open($image);

        if (!$libImage->isImage()) {
            $this->setError('不是合法的图片！');
            return false;
        }

        $width = $libImage->getWidth();
        $height = $libImage->getHeight();

        $configWatermark = Be::getConfig('System.Watermark');

        $x = 0;
        $y = 0;
        switch ($configWatermark->position) {
            case 'north':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'northeast':
                $x = $width + $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'east':
                $x = $width + $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
            case 'southeast':
                $x = $width + $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'south':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'southwest':
                $x = $configWatermark->offsetX;
                $y = $height + $configWatermark->offsetY;
                break;
            case 'west':
                $x = $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
            case 'northwest':
                $x = $configWatermark->offsetX;
                $y = $configWatermark->offsetY;
                break;
            case 'center':
                $x = $width / 2 + $configWatermark->offsetX;
                $y = $height / 2 + $configWatermark->offsetY;
                break;
        }

        $x = intval($x);
        $y = intval($y);

        if ($configWatermark->type == 'text') {
            $style = array();
            $style['fontSize'] = $configWatermark->textSize;
            $style['color'] = $configWatermark->textColor;

            // 添加文字水印
            $libImage->text($configWatermark->text, $x, $y, 0, $style);
        } else {
            // 添加图像水印
            $libImage->watermark(PATH_DATA . DS . 'system' . DS . 'watermark' . DS . $configWatermark->image, $x, $y);
        }

        $libImage->save($image);

        return true;
    }


}
