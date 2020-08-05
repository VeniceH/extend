<?php
/**
 * Date: 2020/08/05
 * Time: 11:54
 * ClassName: 时间换算工具
 * Desc:
 */
namespace com;

class LibTime
{

    // 判断是否在同一星期
    public static function checkEqualWeek($time)
    {
        $start = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $end   = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
        if ($start < $time && $time < $end) {
            return true;
        } else {
            return false;
        }
    }

    // 时间转成秒（不带日期）
    public static function timeToSec($times)
    {
        $timeArr = explode(':', $times);
        $sec     = $timeArr[0] * 3600 + $timeArr[1] * 60 + $timeArr[2];

        return $sec;
    }

    /**
     * @desc 秒转时间
     * @param int $sec 秒数
     * @param int $type 返回格式类型（需要的在switch里面增加类型）
     * @return string|array
     */
    public static function secToTime($sec, $type = 0)
    {
        $day       = '0';
        $hour      = '00';
        $totalHour = '00';
        $minute    = '00';
        $second    = '00';
        if ($sec > 0) {
            $day       = floor($sec / 86400);
            $hour      = floor(($sec - 24 * $day) / 3600);
            $totalHour = floor($sec / 3600);
            $minute    = floor(($sec - 3600 * $totalHour) / 60);
            $second    = floor((($sec - 3600 * $totalHour) - 60 * $minute) % 60);
//        $result = $hour.':'.$minute.':'.$second;
        }
        $result['day']       = $day;
        $result['hour']      = $hour;
        $result['totalHour'] = $totalHour;
        $result['minute']    = $minute;
        $result['second']    = $second;

        if ($type == 0) {
            return $result;
        } else {
            switch ($type) {
                case 1:
                    $time = sprintf("%02d", $totalHour) . ':' . sprintf("%02d", $minute) . ':' . sprintf("%02d", $second);
                    break;
            }
            return $time;
        }
    }

}