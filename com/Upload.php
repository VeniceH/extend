<?php
/**
 * @Version Beta 1.0
 * @Date: 2017/8/10 20:26
 * @Description 文件上传类（因为有数据库配置，以及调用了TP的图片处理，只适合在TP上使用）
 * @History
 */
namespace com;

use think\Db;
use think\Image;
use think\image\Exception;

class Upload
{
    protected $imgPath = 'upload\img\\'; // 指定的本地保存地址
    protected $filePath = 'upload/file/'; // 指定的本地文件保存地址
    protected $address; // 存放地址（local：本地；server：图片服务器）
    public $source; // 来源（自定义的参数，仅用于记录在数据库）
    public $former_id; // 原图id（仅用于记录在数据库）
    public $error;
    public $path; // 文件保存路径
    public $url; // 图片访问地址(本地保存相对路径，服务器保存绝对路径)
    public $name; // 图片名称
    public $thumb = []; // 压缩图信息

    protected static $errorCont = [
        '1' => '超过php.ini允许的大小。',
        '2' => '超过表单允许的大小。',
        '3' => '图片只有部分被上传。',
        '4' => '请选择图片。',
        '6' => '找不到临时目录。',
        '7' => '写文件到硬盘出错。',
        '8' => 'File upload stopped by extension。',
    ];
    protected static $typeCont = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    public function __construct($source='')
    {
        $this->source = $source;
    }

    /**
     * @desc 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @desc 获取上传图片的路径
     * @return string
     */
    public function getUrl()
    {
        /*if ($this->address = 'local')
        {
            return $this->url . $this->name;
        } else {
            return '\\' . $this->url . $this->name;
        }*/

        return $this->url;
    }

    /**
     * @desc 获取缩略图的路径
     * @param string $size 尺寸（不存则返回所有尺寸）
     * @return array|mixed
     */
    public function getThumb($size=null)
    {
        if ($size && !empty($this->thumb[$size]))
        {
            return $this->thumb[$size];
        }
        return $this->thumb;
    }

    /**
     * @desc 过滤错误信息并返回文件类型
     * @param array $fileData 文件内容
     * @return bool|mixed
     */
    protected function filter($fileData)
    {
        if (!$fileData)
        {
            $this->error = '上传文件不能为空';
            return false;
        }

        // 返回错误信息
        if ($fileData['error'] > 0)
        {
            if (!empty(self::$errorCont[$fileData['error']]))
            {
                $this->error = self::$errorCont[$fileData['error']];
            } else {
                $this->error = '未知错误。';
            }
            return false;
        }

        // 获取类型
        if (!empty(self::$typeCont[$fileData['type']]))
        {
            $picTypeRet = self::$typeCont[$fileData['type']];
        } else {
            $this->error = '文件类型不支持';
            return false;
        }

        return $picTypeRet;
    }

    // 保存数据库
    protected function saveDb($name, $suffix)
    {
        // 保存原图信息
        $imgData = [
            'source' => $this->source,
            'url' => $this->url,
            'path' => $this->path,
            'name' => $name,
            'suffix' => $suffix,
            'add_time' => time(),
        ];
        $this->former_id = Db::table('farm_img_former')->insertGetId($imgData);
    }

    /**
     * @desc 本地图片上传
     * @param array $fileData 文件内容
     * @return bool|mixed
     */
    public function localImageUpload($fileData)
    {
        // 过滤错误信息并返回文件类型
        $picTypeRet = $this->filter($fileData);

        $imgPath = $this->imgPath;

        // 没有目录则创建
        if(!is_dir($imgPath))
        {
            mkdir($imgPath,0777, true);
        }
//    dump($fileData);exit;

        // 生成新的文件名称
        $newPicName = md5(uniqid());
        $newPicPah = ROOT_PATH . 'public\\' . $imgPath . $newPicName . "." . $picTypeRet;
        $result = move_uploaded_file($fileData["tmp_name"], $newPicPah); // 转移到指定目录


        if (!$result) {
            $this->error = '图片保存失败';
            return false;
        }

        // 检查图片是否已经存在
        try {
            $image = Image::open($newPicPah);
            if (empty($image))
            {
                $this->error = '图片不存在';
                return false;
            }
        } catch(Exception $e) {
            $this->error = '图片不存在';
            return false;
        }


        $this->address = 'local';
        $this->name = $newPicName . "." . $picTypeRet;
        $this->path = $imgPath ;
        $this->url = '\\' . $imgPath . $newPicName . "." . $picTypeRet;

        $this->saveDb($newPicName, $picTypeRet);
        return $this;
    }

    /**
     * @desc 服务器图片上传
     * @param array $fileData 文件内容
     * @return bool|mixed
     */
    public function serverImageUpload($fileData)
    {
        // 过滤错误信息并返回文件类型
        $picTypeRet = $this->filter($fileData);

        $datePath = date('Y') . '/' . date('m') . '/' . date('d');
        $imgPath = IMG_SERVICE_DIR . $datePath;
//        echo $imgPath;exit;
        // 没有目录则创建
        if(!is_dir($imgPath))
        {
            if (!mkdir($imgPath, 0777, true))
            {
                $this->error = '目录创建失败';
                return false;
            }
        }

        // 生成新的文件名称
        $newPicName = md5(uniqid());
        $newPicPah = $imgPath . '/' . $newPicName . "." . $picTypeRet;
        $result = move_uploaded_file($fileData["tmp_name"], $newPicPah); // 转移到指定目录

        if (!$result) {
            $this->error = '图片保存失败';
            return false;
        }

        // 检查图片是否已经存在
        try {
            $image = Image::open($newPicPah);
            if (empty($image))
            {
                $this->error = '图片不存在';
                return false;
            }
        } catch(Exception $e) {
            $this->error = '图片不存在';
            return false;
        }


        $this->address = 'server';
        $this->name = $newPicName . "." . $picTypeRet;
        $this->path = $imgPath . '/' ;
        $this->url = IMG_SERVICE_NAME . $datePath . '/' . $newPicName . "." . $picTypeRet;

        $this->saveDb($newPicName, $picTypeRet);
        return $this;
    }

    /**
     * @desc 图片压缩（需要先调用图片上传后再调该方法）
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $type 压缩方式(1:等比例缩放; 2:缩放后填充; 3:居中裁剪; 4:左上角裁剪; 5:右下角裁剪; 6:固定尺寸缩放)
     * @return bool|mixed
     */
    public function imageThumb($width=200, $height=200, $type=1)
    {
        $filename = $this->path . $this->name;
//echo $filename;exit;
        // 检查图片是否存在
        if (!is_file($filename))
        {
            $this->error = '图片路径不正确';
            return false;
        }
        // 获取图片对象
        try {
            $image = Image::open($filename);
            if (empty($image))
            {
                $this->error = '图片不存在';
                return false;
            }
        } catch(Exception $e) {
            $this->error = '图片不存在';
            return false;
        }

        $size = $width . 'x' . $height;
        $thumbPath = $this->path . $size;
//dump($thumbPath);exit;

        // 没有目录则创建
        if (!is_dir($thumbPath))
        {
            mkdir($thumbPath,0777, true);
        }

        if ($this->address == 'local')
        {
            $newPicPah = $thumbPath . '\\' . $this->name;
        } else {
            $newPicPah = $thumbPath . '/' . $this->name;
        }
//echo $newPicPah;exit;
        // 压缩图片
        $image->thumb($width, $height, $type)->save($newPicPah);
        if (!is_file($newPicPah)) // 检查图片是否存在
        {
            $this->error = '图片压缩失败';
            return false;
        }

        // 把压缩后的图片路径保存到thumb变量中
        if ($this->address == 'local')
        {
            $this->thumb[$size] = '\\' . $newPicPah;
        } else {
            $this->thumb[$size] = IMG_SERVICE_NAME . str_replace(IMG_SERVICE_DIR, '', $newPicPah);
        }

        // 记录压缩后的图片信息
        $thumbData = [
            'former_id' => $this->former_id,
            'width' => $width,
            'height' => $height,
            'url' => $this->thumb[$size],
            'add_time' => time(),
        ];
        Db::table('farm_img_thumb')->insert($thumbData);
        return $this;
    }

    /**
     * @desc 批量压缩图片
     * @param array $sizeArray 尺寸数组(格式例如：array('200x200', '300x300', '320x240'))
     * @param bool $thumbNow 是否马上压缩(true：马上压缩，false：不进行压缩，只记录数据库在后续再处理)
     * @param int $type 压缩方式(1:等比例缩放; 2:缩放后填充; 3:居中裁剪; 4:左上角裁剪; 5:右下角裁剪; 6:固定尺寸缩放)
     * @return bool|mixed
     */
    public function batchThumb($sizeArray=[], $thumbNow=true, $type=1)
    {
        if (empty($this->former_id))
        {
            $this->error = '没有原图的数据记录';
            return false;
        }
        Db::table('farm_img_former')->where(['id'=>$this->former_id])->update(['thumb_size'=>implode(',', $sizeArray)]);

        if ($thumbNow  === true)
        {
            foreach ($sizeArray as $size)
            {
                $tmp = explode('x', $size);
                $this->imageThumb($tmp[0], $tmp[1], $type);
            }
            Db::table('farm_img_former')->where(['id'=>$this->former_id])->update(['is_thumb'=>1]);
        }

        return $this;
    }

    /**
     * @desc 删除原图（需要先调用图片压缩后再调该方法）
     * @return bool
     */
    public function deleteFormer()
    {
        if (!is_file($this->path)) // 检查图片是否存在
        {
            $this->error = '图片路径不正确';
            return false;
        }

        if (!unlink($this->path))
        {
            return true;
        } else {
            return false;
        }
    }


    // 本地文件上传
    public function localFileUpload($fileData, $savePath, $reservedName=false)
    {
        if ($reservedName) {
            // 保留原来的文件名称
            $newName = $fileData['name'];
        } else {
            // 生成新的文件名称
            $newName = md5(uniqid());
            $picTypeRet = explode('.', $fileData['name'])[1];
            $newName = $newName . "." . $picTypeRet;
        }

        $newPah = $savePath . $newName;
        $result = move_uploaded_file($fileData["tmp_name"], $newPah); // 转移到指定目录


        if (!$result) {
            $this->error = '文件保存失败';
            return false;
        }

        $this->address = 'local';
        $this->name = $newName;
        $this->path = $savePath ;
        $this->url = '/' . $savePath . $newName;

        return $this;
    }

    public function localApkUpload($fileData, $savePath)
    {
        $newName = md5(uniqid());
        $oldName = str_replace('.apk', '', $fileData['name']);
        $newName = "{$oldName}-{$newName}.apk";


        $newPath = $savePath . $newName;
        $result = move_uploaded_file($fileData["tmp_name"], $newPath); // 转移到指定目录


        if (!$result) {
            $this->error = '文件保存失败';
            return false;
        }

        $this->address = 'local';
        $this->name = $newName;
        $this->path = $savePath ;

        return $this;
    }

}