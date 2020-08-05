<?php
/**
 * Date: 2019/11/15
 * Time: 16:41
 * ClassName: 字符串换算工具
 * Desc:
 */
namespace com;

class LibString
{
    // 判断是否json格式
    public static function isJson($string)
    {
        $data = json_decode($string);
        if (is_object($data) || is_array($data)) {
            return true;
        } else {
            return false;
        }
    }

    // 截取文字，超出使用省略号
    public static function cutStr($str, $len, $suffix = "...")
    {
        if (function_exists('mb_substr')) {
            if (mb_strlen($str, 'utf8') > $len) {
                $str = mb_substr($str, 0, $len) . $suffix;
            }
            return $str;
        } else {
            if (mb_strlen($str, 'utf8') > $len) {
                $str = substr($str, 0, $len) . $suffix;
            }
            return $str;
        }
    }

    // 异常utf8格式转换（json_decode报错JSON_ERROR_UTF8时可用）
    public static function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    // 随机字符
    public static function randomString($length = 8, $type = 1)
    {
        // 密码字符集，可任意添加你需要的字符
        switch ($type) {
            case 1:
                $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 2:
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
                break;
            default:
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $string .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $string;
    }


    // 正则图片路径提取
    public static function drawImgSrc($string)
    {
//    $imgpreg = '/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i';
        $imgpreg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s?>/i';
        preg_match_all($imgpreg, $string, $img);
        return $img[1];
    }


}