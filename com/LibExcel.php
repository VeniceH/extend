<?php
/**
 * Date: 2020/08/06
 * Time: 10:39
 * ClassName: excel工具
 * Desc:
 */

namespace com;


class LibExcel
{
    /**
     * 导出excel
     * @param array $data 数据
     * @param string $fileName 导出文件名称
     * @param array $columns 列表字段名=>宽度
     */
    public static function excel($data, $fileName = '帖子列表', $columns=[], $savePath='')
    {
        error_reporting(E_ALL);
        set_time_limit(300);
        ini_set("max_execution_time", 300);
        ini_set("memory_limit", "1000M");
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);

        //创建对象
        include_once "../extend/org/PHPExcel.php";
        include_once "../extend/org/PHPExcel/CachedObjectStorageFactory.php";
        include_once "../extend/org/PHPExcel/Settings.php";

        $cacheMethod   = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '512MB');
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $excel = new \PHPExcel();

        //填充表头信息
        $excel->getActiveSheet()->setTitle($fileName);

        $letter       = [];
        $objSheet     = $excel->getActiveSheet();
        $letterColumn = 'A';
        foreach ($columns as $column) {
            $letter[] = $letterColumn; //Excel表格式
            $objSheet->getColumnDimension($letterColumn)->setWidth($column); //填充表格信息
            $letterColumn++;
        }

        //表头数组
        $tableheader = array_keys($columns);

        // 插入普通文件
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }

        $data = json_decode(json_encode($data), true);

        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $value = str_replace(['='], '', $value);
//                    $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i", "$value"); // 默认为字符串类型（避免特殊字符报错）
                $j++;
            }
        }

        //创建Excel输入对象
        ob_end_clean();
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);

        if ($savePath) {
            $objWriter->save($savePath . $fileName . '.csv');
            return ;
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:text/html;charset=utf-8");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");

        //多浏览器下兼容中文标题
        $encoded_filename = urlencode($fileName);
        $ua               = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.csv"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.csv"');
        }
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit;
    }
}