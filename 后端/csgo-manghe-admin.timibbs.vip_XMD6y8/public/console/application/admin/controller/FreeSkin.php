<?php


namespace app\admin\controller;


use think\Db;

class FreeSkin
{
    //后台添加房间
    public function addFreeRoom(){
        $free_room_info = input('post.free_room_info');
        $free_room = [
            'room_name'        => $free_room_info['room_name'],
            'room_num'         => $free_room_info['room_num'],
            'img'              => $free_room_info['img'],
            'desc'             => $free_room_info['desc'],
            'pool'             => $free_room_info['pool'],//奖池
            'count'            => $free_room_info['count'],//奖品件数
            'person_num'       => $free_room_info['person_num'],
            'password'         => md5($free_room_info['password']),
            'run_lottery_time' => $free_room_info['run_lottery_time'],//开奖时间
            'condition_time'   => $free_room_info['condition_time'],//参加条件(充值开始时间)
            'condition_charge' => $free_room_info['condition_charge'],//参加条件(达标金额)
            'condition_type'   => $free_room_info['condition_type'],//参加条件类型，1：密码/口令，2：充值
            'type'             => $free_room_info['type'],//类型，1：官方，2：主播
        ];
        Db::table('free_room')->insert($free_room);
        return result(1,'','');
    }

    //修改房间信息
    public function editRoom(){
        $free_room_info = input('post.free_room_info');
        $free_room = [
            'id'               => $free_room_info['id'],
            'room_name'        => $free_room_info['room_name'],
            'room_num'         => $free_room_info['room_num'],
            'img'              => $free_room_info['img'],
            'desc'             => $free_room_info['desc'],
            'pool'             => $free_room_info['pool'],//奖池
            'count'            => $free_room_info['count'],//奖品件数
            'person_num'       => $free_room_info['person_num'],
            'password'         => md5($free_room_info['password']),
            'run_lottery_time' => $free_room_info['run_lottery_time'],//开奖时间
            'condition_time'   => $free_room_info['condition_time'],//参加条件(充值开始时间)
            'condition_charge' => $free_room_info['condition_charge'],//参加条件(达标金额)
            'condition_type'   => $free_room_info['condition_type'],//参加条件类型，1：密码/口令，2：充值
            'type'             => $free_room_info['type'],//类型，1：官方，2：主播
        ];
        Db::table('free_room')->update($free_room);
        return result(1,'','');
    }

    //删除房间
    public function delRoom(){
        $id = input('post.id');
        Db::table('free_room')->where('id',$id)->setField('flag',0);
        return result(1,'','');
    }


    //房间皮肤奖品列表
    public function freeList(){
        $page     = input('post.page',1);
        $pageSize = input('post.pageSize',10);
        $free_room_id = input('post.free_room_id');
        $list = Db::table('free_skin')
            ->where(['flag'=>1,'free_room_id'=>$free_room_id])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('free_skin')
            ->where(['flag'=>1,'free_room_id'=>$free_room_id])
            ->count();
        if($total > 0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(1,'','');
        }
    }


    //给免费皮肤房间添加皮肤奖品
    public function addSkin(){
        $free_skin_info = input('post.free_skin_info');
        $free_skin  = [
            'appId'       => $free_skin_info['appId'],
            'free_room_id'=> $free_skin_info['free_room_id'],
            'itemId'      => $free_skin_info['itemId'],
            'name'        => $free_skin_info['itemName'],
            'img'         => $free_skin_info['imageUrl'],
            'price'       => $free_skin_info['price'],
            'priceInfo'   => serialize($free_skin_info['priceInfo']),
            //手动设置信息
            'stock'       => $free_skin_info['stock'],//库存
            'buyPrice'    => $free_skin_info['buyPrice'],//购买价格
            'maxPrice'    => $free_skin_info['appId'],//最大购买价格，可选，会导致buyPrice失效，商品价格<=最大购买价格，可以购买成功
            'delivery'    => $free_skin_info['delivery'],//发货：1人工 2自动
            'probability' => $free_skin_info['probability'],//概率（虚标）
            'box_id'      => $free_skin_info,
            'appoint'     => $free_skin_info['appoint'],//指定账户
            'appoint_id'  => $free_skin_info['appoint_id'],//指定账户id
            'player_id'   => '',//得主玩家id
        ];
        Db::table('free_skins')->insert($free_skin);
        return result(1,'','');
    }

    //编辑皮肤
    public function editSkin(){
        $id = input('post.id');
        $free_skin_info = input('post.free_skin_info');
        $free_skin  = [
            'id'          => $id,
            'stock'       => $free_skin_info['stock'],//库存
            'buyPrice'    => $free_skin_info['buyPrice'],//购买价格
            'maxPrice'    => $free_skin_info['appId'],//最大购买价格，可选，会导致buyPrice失效，商品价格<=最大购买价格，可以购买成功
            'delivery'    => $free_skin_info['delivery'],//发货：1人工 2自动
            'probability' => $free_skin_info['probability'],//概率（虚标）
            'box_id'      => $free_skin_info,
            'appoint'     => $free_skin_info['appoint'],//指定账户
            'appoint_id'  => $free_skin_info['appoint_id'],//指定账户id
            'player_id'   => '',//得主玩家id
        ];
        Db::table('free_skins')->update($free_skin);
        return result(1,'','');
    }

    //删除
    public function delSkin(){
        $id = input('post.id');
        Db::table('free_skins')->where('id',$id)->setField('flag',0);
        return result(1,'','');
    }






}