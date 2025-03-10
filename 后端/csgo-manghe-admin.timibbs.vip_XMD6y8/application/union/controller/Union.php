<?php


namespace app\union\controller;


use think\Db;

class Union
{
    public function getUnionList(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $list = Db::table('union')
//            ->field('id,name')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();
        $total = Db::table('union')
            ->count();
        if($list){
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //保存工会信息
    public function saveUnion(){
        $post = input('post.data');
        if(isset($post['id']) && ($post['id']>0)){
            $union = Db::table('union')->where(['name'=>$post['name']])->select();
            if(count($union)>1){
                return result(0,'','已存在相同工会');
            }else{
                Db::table('union')->update($post);
                return result(1,'','');
            }
        }else{
            $union = Db::table('union')->where(['name'=>$post['name']])->find();
            if($union){
                return result(0,'','已存在相同工会');
            }else{
                $post['create_time'] = currentTime();
                Db::table('union')->insert($post);
                return result(1,'','');
            }
        }
    }

    //删除工会
    public function delUnion(){
        $id = input('post.id');
        if($id>0){
            Db::table('union')->where('id',$id)->delete();
            return result(1,'','');
        }
        return result(0,'','');
    }
}