<?php


namespace app\index\controller;


use think\Db;

class Setting
{
    //网站背景图
    public function background(){
        $re = Db::table('set_background')
            ->where('flag',1)
            ->find();
        if($re){
            return result(1, $re, '');
        }else{
            return result(0, '', '无数据');
        }
    }
}