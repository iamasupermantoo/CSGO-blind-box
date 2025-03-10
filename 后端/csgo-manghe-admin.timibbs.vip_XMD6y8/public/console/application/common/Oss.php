<?php
namespace app\common;

use OSS\Core\OssException;
use OSS\OssClient;
use think\Db;


/**
 * 创建oss
 * Class oss
 * @package app\common
 */
class Oss
{
    protected $ossClient;
    protected $bucket;
    protected $endpoint;

    protected $accessKeyId;
    protected $accessKeySecret;

    public function __construct()
    {
        $storage = Db::table('file_storage')->where(['status'=>1,'flag'=>1])->find();

//        $accessKeyId     = 'LTAIOf112pDhfh3k';
//        $accessKeySecret = 'iQwsOi9KkMnM0VmURJrR9LEWLX7JjC';
//        $endpoint        = 'oss-cn-beijing.aliyuncs.com';
//        $bucket = 'dmskins';
        $accessKeyId     = $storage['accessKeyId'];
        $accessKeySecret = $storage['accessKeySecret'];
        $endpoint        = $storage['endpoint'];
        $bucket          = $storage['bucket'];

        $this->bucket = $bucket;
        $this->endpoint = $endpoint;

        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $this->ossClient = $ossClient;
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    /**
     * 指定文件目录 以及文件接收的file名
     * @param $dir
     * @param $name
     * @return bool
     */
    function uploadFile($dir, $name)
    {

        if (!isset($_FILES[$name])) {
            return false;
        }
        $file = $_FILES[$name];
        if (is_array($file['name'])) {
            $tmp   = [];
            $ext   = [];
            $count = count($file['name']);
            for ($i = 0; $i < $count; $i++) {
                if (empty($file['tmp_name'][$i]) || !is_file($file['tmp_name'][$i])) {
                    continue;
                }
                $ext[] = explode('.', $file['name'][$i])[1];
                $tmp[] = $file['tmp_name'][$i];
            }
        } else {
            $ext[] = explode('.', $file['name'])[1];
            $tmp[] = $file['tmp_name'];
        }
        $names = [];

        try {
            foreach ($tmp as $key => $tm) {
                $fileName = uniqid() . '.' . $ext[$key];
                $object   = $dir . '/' . $fileName;
                $res = $this->ossClient->uploadFile($this->bucket, $object, $tmp[$key]);
                //$names[]  =  'http://'.$this->bucket.'.'.$this->endpoint. '/'. $object;
                $names[] = $res['info']['url'];
            }
            return $names;
        } catch (OssException $e){
//            return $e->getMessage();
            return FALSE;
        }
    }

    //尝试写一个直接把下载到本地的文件上传oss
    public function uploads($dir=null,$file=null,$imgName){//$dir eg:test,mte项目名称，$file，文件路径，$imgName文件名称
        $fileName = uniqid().$imgName;
        $object   = $dir . '/' . $fileName;
        $res = $this->ossClient->uploadFile($this->bucket, $object, $file, $options = NULL);
        $names[] = $res['info']['url'];
        return $names;
    }

    //获取域名2019.08.20
    public function getDomainName (){
        $endpoint = $this->endpoint;
        $bucket = $this->bucket;
        return $bucket.'.'.$endpoint;
    }
    //获取配置信息2019.08.20
    public function getOss(){
        $ossConfig = [
            'KeyId' => $this->accessKeyId,
            'KeySecret' => $this->accessKeySecret,
            'Endpoint' => $this->endpoint,
            'Bucket' => $this->bucket,
        ];
        return $ossConfig;
    }

    public function accepts()
    {
        $url = $this->uploadFile('index', 'img');
        var_dump($url);
    }

    /**
     * @return \think\response\View
     */
    public function formTest()
    {
        return view();
    }

    public function deleteFile($filePath)
    {
        try {
            $prePath = 'http://'. $this->bucket . '.' .$this->endpoint . '/' ;
            if (stripos($filePath, $prePath) > -1) {
                $filePath = str_replace($prePath,'', $filePath);
            }
            return $this->ossClient->deleteObject($this->bucket,$filePath);

        //$str = 'https://'.$this->bucket.'.'.$this->endpoint. '/';
        //$fileName = substr($filePath,strlen($str)-1);
        //return $this->ossClient->deleteObject($this->bucket,$fileName);
        } catch (OssException $e){
            return $e->getMessage();
        }

    }
}

$oss = new Oss();
return $oss->getOss();