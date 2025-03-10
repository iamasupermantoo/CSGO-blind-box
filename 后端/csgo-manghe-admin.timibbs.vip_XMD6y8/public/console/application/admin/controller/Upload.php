<?php


namespace app\admin\controller;


use app\common\Oss;
use think\Controller;

class Upload extends Controller
{
    public function upload($folder=null,$file=null,$name=null){
        $folder = input('post.folder') ? input('post.folder') : ($folder ? $folder : 'test');
        $folder = 'csgo/' . $folder;

        if(!$file){
            return result(0,'','请上传图片');
        }
//        $key = array_keys($file)[0];
//        $attach_name = $file[$key]['name'];//文件真实名字
//        $name = substr($attach_name, strripos($attach_name, "."));//后缀名
//        $uniqid = md5(uniqid(microtime(true),true));
//        //设置文件的名字
//        $setFileName = $uniqid.$name; //29882eb3b65e7441dcf4bdf59be24ca0.png
//        //创建文件夹
//        if (!is_dir(ROOT_PATH . 'public/' . 'images/'. $folder.'/')) {
//            mkdir(ROOT_PATH . 'public/' . 'images/'. $folder.'/', 0777, true);
//        }
//        //文件地址
//        $fileUrl = '/images/' . $folder.'/'.$setFileName;
//        //移动到框架应用根目录/public 目录下
//        move_uploaded_file($file[$key]['tmp_name'], ROOT_PATH . 'public/' . 'images/' . $folder.'/'.$setFileName);
//        return $fileUrl;

        $oss = new Oss();
//        $key = array_keys($file)[0];
//        dump($key);
        $fileUrl = $oss->uploadFile($folder,$name)[0];
        return $fileUrl;
    }
}