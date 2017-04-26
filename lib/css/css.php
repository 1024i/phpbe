<?php
namespace lib\css;

/*
@版本日期: 2014年01月08日
@更新日期: 2016年9月14日
@著作权所有: PHPBE (http://www.phpbe.com)

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(i@liu12.com) http://www.liu12.com
*/
class css extends \system\lib
{

    // 构造函数

    public function __construct()
    {
    }

    // 析构函数

    public function __destruct()
    {
    }


    // 颜色加重
    public function darken($hex_color, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgb_color = $this->hex_to_rgb($hex_color);
        $hsl_color = $this->rgb_to_hsl($rgb_color);

        $hsl_color[2] = min(100, max(0, $hsl_color[2] - $percent));

        return $this->rgb_to_hex($this->hsl_to_rgb($hsl_color));
    }

    // 颜色减轻
    public function lighten($hex_color, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgb_color = $this->hex_to_rgb($hex_color);
        $hsl_color = $this->rgb_to_hsl($rgb_color);

        $hsl_color[2] = min(100, max(0, $hsl_color[2] + $percent));

        return $this->rgb_to_hex($this->hsl_to_rgb($hsl_color));
    }


    // 颜色加重(按当前亮度的百分比)
    public function darker($hex_color, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgb_color = $this->hex_to_rgb($hex_color);
        $hsl_color = $this->rgb_to_hsl($rgb_color);

        $hsl_color[2] = $hsl_color[2] - $hsl_color[2] * $percent / 100;

        return $this->rgb_to_hex($this->hsl_to_rgb($hsl_color));
    }

    // 颜色减轻(按剩余亮度的百分比)
    public function lighter($hex_color, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgb_color = $this->hex_to_rgb($hex_color);
        $hsl_color = $this->rgb_to_hsl($rgb_color);

        $hsl_color[2] = $hsl_color[2] + (100 - $hsl_color[2]) * $percent / 100;

        return $this->rgb_to_hex($this->hsl_to_rgb($hsl_color));
    }


    // 16进制字符串颜色（如: #999 / #FFFFFF ）转 RGB
    public function hex_to_rgb($str)
    {
        $c = array(0, 0, 0);

        if (substr($str, 0, 1) == '#') $str = substr($str, 1);

        $num = hexdec($str);
        $width = strlen($str) == 3 ? 16 : 256;

        for ($i = 2; $i >= 0; $i--) {
            $t = $num % $width;
            $num /= $width;

            $c[$i] = $t * (256 / $width) + $t * floor(16 / $width);
        }

        return $c;
    }


    // RGB 转 16进制字符串颜色（如: #999 / #FFFFFF ）
    public function rgb_to_hex($color)
    {
        return sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
    }


    // RGB 转 HSL
    public function rgb_to_hsl($color)
    {

        $r = $color[0] / 255;
        $g = $color[1] / 255;
        $b = $color[2] / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $l = ($min + $max) / 2;
        if ($min == $max) {
            $s = $h = 0;
        } else {
            if ($l < 0.5)
                $s = ($max - $min) / ($max + $min);
            else
                $s = ($max - $min) / (2.0 - $max - $min);

            if ($r == $max) $h = ($g - $b) / ($max - $min);
            elseif ($g == $max) $h = 2.0 + ($b - $r) / ($max - $min);
            elseif ($b == $max) $h = 4.0 + ($r - $g) / ($max - $min);

        }

        $out = array(
            ($h < 0 ? $h + 6 : $h) * 60,
            $s * 100,
            $l * 100,
       );

        return $out;
    }


    // HSL 转 RGB
    public function hsl_to_rgb($color)
    {

        $H = $color[0] / 360;
        $S = $color[1] / 100;
        $L = $color[2] / 100;

        if ($S == 0) {
            $r = $g = $b = $L;
        } else {
            $temp2 = $L < 0.5 ?
                $L * (1.0 + $S) :
                $L + $S - $L * $S;

            $temp1 = 2.0 * $L - $temp2;

            $r = $this->hsl_to_rgb_helper($H + 1 / 3, $temp1, $temp2);
            $g = $this->hsl_to_rgb_helper($H, $temp1, $temp2);
            $b = $this->hsl_to_rgb_helper($H - 1 / 3, $temp1, $temp2);
        }

        // $out = array(round($r*255), round($g*255), round($b*255));
        $out = array($r * 255, $g * 255, $b * 255);
        return $out;
    }


    protected function hsl_to_rgb_helper($comp, $temp1, $temp2)
    {
        if ($comp < 0) $comp += 1.0;
        elseif ($comp > 1) $comp -= 1.0;

        if (6 * $comp < 1) return $temp1 + ($temp2 - $temp1) * 6 * $comp;
        if (2 * $comp < 1) return $temp2;
        if (3 * $comp < 2) return $temp1 + ($temp2 - $temp1) * ((2 / 3) - $comp) * 6;

        return $temp1;
    }


}

?>