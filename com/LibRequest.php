<?php
/**
 * Date: 2019/11/15
 * Time: 16:41
 * ClassName: 请求工具
 * Desc:
 */
namespace com;

class LibRequest
{
    // 简单的curl
    public static function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 这个是重点 请求https。
        $data = curl_exec($ch);
        curl_close($ch);
//        $data = json_decode($data, true);
        return $data;
    }

    // curl请求
    public static function curlRequest($url, $method='get', $data=[], $isJson=false)
    {
        //初始化
        $curl = curl_init();

        if ($method == 'get') {
            $url = $url . '?' . http_build_query($data);
        }

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        $headerArray = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ($method == 'post') {
            //设置post方式提交
            curl_setopt($curl, CURLOPT_POST, 1);
            //设置post数据
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }


        //执行命令
        $result = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);

        //显示获得的数据
        if ($isJson) {
            return json_decode($result, true);
        } else {
            return $result;
        }
    }

    /**
     * 异步请求
     * @param string $url 请求地址
     * @param array $param 请求参数
     * @param bool $isReturn 是否返回结果
     * @return string
     */
    public static function sockopen($url, $param = [], $isReturn = false)
    {
        //解析url
        $url_pieces = parse_url($url);

        $host = $url_pieces['host'];
        if ($url_pieces['scheme'] == 'https') {
            $hosts = 'ssl://' . $host;
            $port  = '443';
        } else {
            $hosts = $host;
            $port  = '80';
        }

        $data = http_build_query($param);
        $path = $url_pieces['path'] . '?' . $data;
//    dump($path);exit;

        //用fsockopen()尝试连接
        if ($fp = fsockopen($hosts, $port, $errno, $errstr, 5)) {
            // （如果是https地址这里的域名开头需要用ssl://）
            stream_set_blocking($fp, 0); // 设定socket链接为无阻塞方式（默认为阻塞）

            //建立成功后，向服务器写入数据
            $http = "GET  $path HTTP/1.1\r\n";
            $http .= "Host:{$host}\r\n"; // （这里的域名开头不能用ssl://）
            $http .= "Connection: Close\r\n\r\n";
//        echo $http;exit;

            fwrite($fp, $http);
//        $response = fgets($fp,128); //检索HTTP状态码

            if (!$isReturn) {
                // 不需要结果，直接返回请求成功
                fclose($fp);
                return 'success';
            }

            $response = '';
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp); //关闭连接

            $responseArray = explode("\r\n", $response);
            foreach ($responseArray as $value) {
                if (LibString::isJson($value)) {
                    return $value;
                }
            }
            return 'no data';
        } else {
            //没有连接
            return 'fail';
        }
    }
}