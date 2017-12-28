<?php
namespace app\system\tool;

class date
{

    /**
     * 格式化时间
     *
     * @param int $time unix 时间戳
     * @param int $maxDays 多少天前或后以默认时间格式输出
     * @param string $defaultFormat 默认时间格式
     * @return string
     */
    public static function formatTime($time, $maxDays = 30, $defaultFormat = 'Y-m-d')
    {
        $t = time();

        $seconds = $t-$time;

        // 如果是{$maxDays}天前，直接输出日期
        $maxSeconds = $maxDays*86400;
        if ($seconds > $maxSeconds || $seconds <- $maxSeconds) return date($defaultFormat, $time);

        if ($seconds > 86400) {
            $days = intval($seconds / 86400);
            if ($days == 1) {
                if (date('a', $time) == 'am') return '昨天上午';
                else return '昨天下午';
            } elseif ($days == 2) {
                return '前天';
            }
            return $days . '天前';
        }
        elseif ($seconds > 3600) return intval($seconds / 3600) . '小时前';
        elseif ($seconds > 60) return intval($seconds / 60) . '分钟前';
        elseif ($seconds >= 0) return '刚才';
        elseif ($seconds > - 0) return '马上';
        elseif ($seconds > -3600) return intval(-$seconds / 60) . '分钟后';
        elseif ($seconds > -86400) return intval(-$seconds / 3600) . '小时后';
        else {
            $days = intval(-$seconds / 86400);
            if ($days == 1) {
                if (date('a', $time) == 'am') return '明天上午';
                else return '明天下午';
            } elseif ($days == 2) {
                return '后天';
            }
            return $days . '天后';
        }
    }
}
