<?php


namespace app\admin\controller;


use think\Db;

class Record
{
    //开箱记录
    public function openList(){
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
            $whereTime[] = ['pb.create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['pb.create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['pb.create_time', 'between', $range];
        }
        $list = Db::table('player')
            ->alias('p')
            ->join('player_box_skin pb','pb.player_id = p.id')
            ->join('box b','b.id = pb.box_id')
            ->field('pb.player_id,p.name,p.img,pb.box_name,b.price,pb.skins_info,pb.status,pb.create_time')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->where('pb.type',1)
            ->where($where)
            ->where($whereS)
            ->where($whereTime)
            ->order('pb.create_time','desc')
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('player_box_skin pb','pb.player_id = p.id')
            ->join('box b','b.id = pb.box_id')
            ->where('pb.type',1)
            ->where($where)
            ->where($whereS)
            ->where($whereTime)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
                $list[$k]['statusStr'] = $list[$k]['status'] == 1 ? '未开奖' : ($list[$k]['status'] == 2 ? '已开' : '');
                $list[$k]['skins_info'] = (isset($list[$k]['skins_info']) && $list[$k]['skins_info']) ? json_decode($list[$k]['skins_info'],true) : '';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //对战记录
    public function battleRecord(){
        $page = input('post.page',1);
        $pageSize = input('post.pageSize',10);
        $status = input('post.status');
        $whereStatus =  $status ? ['status'=>$status] : [];
        $startTime = input('post.startTime');
        $endTime   = input('post.endTime');

        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['create_time', 'between', $range];
        }
        $list = Db::table('battle')
            ->order('create_time','desc')
            ->where($whereTime)
            ->where($whereStatus)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();
        $total = Db::table('battle')
            ->order('create_time','desc')
            ->where($whereTime)
            ->where($whereStatus)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['result_info'] = $list[$k]['result_info'] ? json_decode($list[$k]['result_info'],true) : '';
                $list[$k]['boxInfo']     = $list[$k]['boxInfo'] ? json_decode($list[$k]['boxInfo'],true) : '';
                $list[$k]['player_info'] = $player_info = $list[$k]['player_info'] ? unserialize($list[$k]['player_info']) : '';
                $list[$k]['statusStr']   = $list[$k]['status'] == 1 ? '等待中' : ($list[$k]['status'] == 2 ? '对战中' : '已结束');
                $arr = [];
                foreach ($player_info as $key=>$value){
                    if($player_info[$key]['id'] == $list[$k]['home_owner']){
                        $list[$k]['home_owner_name'] = $player_info[$key]['name'];
                    }
                    $arr[$key] = Db::table('player')->where('id',$player_info[$key]['id'])
                        ->field('id,name,img')->find();
                }
                $list[$k]['player_info'] = $arr;
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    // 饰品发货记录
    public function skinToSteam(){
        $page = input('post.page',1);
        $pageSize = input('post.pageSize',10);
        $status = input('post.status');
        $whereStatus =  $status ? ['status'=>$status] : [];
        $startTime = input('post.startTime');
        $endTime   = input('post.endTime');

        $where = ['ps.flag' => 1, 'ps.state' => 'processing', 'ps.status' => '4'];
        $whereTime =  [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['ps.create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['ps.create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['ps.create_time', 'between', $range];
        }
        $list = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id')
            ->field('p.name uname,ps.*')
            ->order('ps.create_time','desc')
            ->where($where)
            ->where($whereTime)
            ->where($whereStatus)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();

        $total = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id')
            ->where($where)
            ->where($whereTime)
            ->where($whereStatus)
            ->count();

        if($total>0){
            $user = new User();
            foreach ($list as $k => $v) {
                $list[$k]['statusStr'] = $user->getStatusStr($list[$k]['status']);
                $list[$k]['wayStr'] = $user->getWayStr($list[$k]['way']);
                $list[$k]['avatar'] = isset($v['avatar']) ? mainName() . $v['avatar'] : '';
            }

            $res = ['total' => $total, 'list' => $list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }

    }





}