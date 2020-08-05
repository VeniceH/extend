<?php
/**
 * Date: 2019/11/15
 * Time: 16:41
 * ClassName: 文件工具
 * Desc:
 */
namespace com;

class LibFile
{
    /**
     * csv 文件读取
     * @param string $csvfile 文件地址
     * @param int $lines 总共读取行数
     * @param int $offset 开始读取行数
     * @return array|bool
     */
    public static function csvGetLines($csvfile, $lines, $offset = 0)
    {
        if (!$fp = fopen($csvfile, 'r')) {
            return false;
        }
        $i = $j = 0;
        while (false !== ($line = fgets($fp))) {
            if ($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = array();
        while (($j++ < $lines) && !feof($fp)) {
            $data[] = mb_convert_encoding(fgetcsv($fp)[0], 'UTF-8', 'GBK');
        }
        fclose($fp);
        return $data;
    }


    // 删除原有的文件目录
    public static function delFileUnderDir($dirName)
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        delFileUnderDir("$dirName/$item");
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
        }
    }
}