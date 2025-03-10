<?php


namespace app\admin\controller;


use think\Db;

class Recharge
{
    //充值记录
    public function rechargeList(){
        $page = input('post.page',1);
        $pageSize = input('post.pageSize',10);
        $status = input('post.status');
        $searchKey = input('post.searchKey');
        $where = $status ? ['r.status'=>$status] : [];
        $whereS = [];
        trim($searchKey) ? $whereS[] = ['p.name|p.mobile','like','%'.$searchKey.'%'] : '';

        $startTime = input('post.startTime');
        $endTime   = input('post.endTime');

        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['r.create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['r.create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['r.create_time', 'between', $range];
        }

        $list = Db::table('player')
            ->alias('p')
            ->field('p.name,r.*')
            ->join('recharge r','r.player_id = p.id')
            ->where($where)
            ->where($whereS)
            ->where($whereTime)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('r.create_time','desc')
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->field('p.name,r.*')
            ->join('recharge r','r.player_id = p.id')
            ->where($where)
            ->where($whereS)
            ->where($whereTime)
            ->count();
        //当前筛选条件下的总额度
        $totalMoney = Db::table('recharge')
            ->alias('r')
            ->where(['r.status'=>3])
            ->where('r.mode','IN', ['weixin','zhifubao'])
            ->where($whereTime)
            ->sum('money');
        if($total>0){
            //状态，1：未支付，2：待支付，3：已支付，4:支付失败
            foreach ($list as $k=>$v){
                $list[$k]['statusStr'] = ($list[$k]['status'] == 1) ? '未支付' : ($list[$k]['status'] == 3 ? '成功' :'');
            }
            $res = ['totalMoney'=>$totalMoney,'total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }


    //后台查看支付配置信息
    public function getPayList(){
        $page = input('post.page',1);
        $pageSize = input('post.pageSize',10);
        $type = input('post.type');
        $where = [];
        if($type){
            $where = ['type'=>$type];
        }
        $list = Db::table('pay')
            ->where($where)
            ->where('flag',1)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();
        $total = Db::table('pay')
            ->where($where)
            ->where('flag',1)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }
    //保存/编辑支付配置信息
    public function savePayInfo(){
        $post = input('post.');
        $type = $post['type'];
        if($type == 'alipay'){
            $mode = 'zhifubao';
        }else if($type == 'weixin'){
            $mode = $type;
        }else{
            return result(0,'','信息错误');
        }
        $post['mode'] = $mode;
        if($post['id']>0){
            Db::table('pay')->where('id',$post['id'])->update($post);
        }else{
            Db::table('pay')->insert($post);
        }
        return result(1,'','');
    }

    //启用/关闭状态
    public function editStatus(){
        $post = input('post.');
        $id   = input('post.id');
        $type = input('post.type');
        $status = input('post.status');
        if($status){
            $status = 1;
            Db::table('pay')->where('type',$type)->setField('status',0);
            Db::table('pay')->where('id',$id)->setField('status',$status);
        }else{
            Db::table('pay')->where('id',$id)->setField('status',0);
        }
        return result(1,'','');
    }

    //删除支付配置
    public function delPayInfo(){
        $post = input('post.');
        $id = $post['id'];
        Db::table('pay')->where('id',$id)->setField('flag',0);
        return result(1,'','');
    }

}