<?php
/**
 * Date: 2019/11/15
 * Time: 16:41
 * ClassName: 数组工具
 * Desc:
 */
namespace com;

class LibArray
{
    /**
     * @desc 根据某字段指定顺序对数组进行排序
     * @param array $data 处理数据
     * @param string $column 查询字段
     * @param array $sortData 排序数据
     * @return array
     */
    public static function sortArray($data, $column, $sortData)
    {
        $temp = [];
        foreach ($data as $val) {
            $temp[$val[$column]] = $val;
        }

        $newData = [];
        foreach ($sortData as $val) {
            if (!empty($temp[$val])) {
                $newData[] = $temp[$val];
            }
        }

        return $newData;
    }

    /**
     * @desc 根据某个字段拆分成新数组，并指定保存的字段（支持递归）
     * @param array $data 处理数据
     * @param string|int $key 拆分字段
     * @param string|int $column 获取字段
     * @param string|int $recursion 递归字段
     * @param array $newData 递归前保存的数据
     * @return array
     */
    public static function splitArray($data, $key, $column, $recursion = null, &$newData = [])
    {
        foreach ($data as $val) {
            $newData[$val[$key]][] = $val[$column];

            if (isset($recursion) && !empty($val[$recursion])) {
                self::splitArray($val[$recursion], $key, $column, $recursion, $newData);
            }
        }
        return $newData;
    }

    /**
     * @desc 合并两个二维数组（根据指定的key值）
     * @param array $array1 第一个二维数组（主）
     * @param array $array2 第二个二维数组（副）
     * @param string|int $key1 第一个数组的key
     * @param string|int $key2 第二个数组的key
     * @param bool $iskey 是否使用指定key值作为新的数组的key
     * @return array
     */
    public static function mergeDyadicArray($array1, $array2, $key1, $key2, $iskey = true)
    {
        $newArray = [];
        foreach ($array2 as $val2) {
            $newArray[$val2[$key2]] = $val2;
        }

        $data = [];
        foreach ($array1 as $val1) {
            if (isset($newArray[$val1[$key1]])) {
                if ($iskey) {
                    $data[$val1[$key1]] = array_merge($val1, $newArray[$val1[$key1]]);
                } else {
                    $data[] = array_merge($val1, $newArray[$val1[$key1]]);
                }
            } else {
                if ($iskey) {
                    $data[$val1[$key1]] = $val1;
                } else {
                    $data[] = $val1;
                }
            }
        }

        return $data;
    }


    /**
     * 将字符解析成数组(name=Bill&age=60, ['name'=>'Bill', 'age'=>60])
     * @param $str
     */
    public static function parseParams($str)
    {
        $arrParams = [];
        parse_str(html_entity_decode(urldecode($str)), $arrParams);
        return $arrParams;
    }


}