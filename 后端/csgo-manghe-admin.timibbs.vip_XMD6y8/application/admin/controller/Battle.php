<?php


namespace app\admin\controller;


use think\Db;

class Battle
{
    //对战列表
    public function battleList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('battle')
            ->where(['flag'=>1])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('status','asc')
            ->order('create_time','desc')
            ->select();
        $total = Db::table('battle')
            ->where(['flag'=>1])
            ->count();
        if ($total > 0) {
            $res['total'] = $total;
            $res['list']  = $list;
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //
}