<?php
/**
 * Date: 2019/11/15
 * Time: 16:41
 * ClassName: 位置工具
 * Desc:
 */
namespace com;

class LibDistance
{
    // 坐标距离计算
    public static function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $earth_radius = 6371000; //approximate radius of earth in meters

        $dLat = deg2rad($latitude2 - $latitude1);
        $dLon = deg2rad($longitude2 - $longitude1);
        /*
        Using the
        Haversine formula

        http://en.wikipedia.org/wiki/Haversine_formula
        http://www.codecodex.com/wiki/Calculate_Distance_Between_Two_Points_on_a_Globe
        验证：百度地图  http://developer.baidu.com/map/jsdemo.htm#a6_1
        calculate the distance
         */
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        $d = $earth_radius * $c;

        return round($d); //四舍五入
    }
}