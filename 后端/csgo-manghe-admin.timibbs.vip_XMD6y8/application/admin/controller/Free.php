<?php


namespace app\admin\controller;


use think\Db;

class Free
{
    //免费皮肤房间列表
    public function freeRoomList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 50);
        $list = Db::table('free_room')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('pool', 'desc')
            ->where(['flag' => 1])
            ->select();
        $total = Db::table('free_room')
            ->where(['flag' => 1])
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = mainName().$list[$k]['img'];
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }

    //创建房间
    public function createRoom(){
        $data = json_decode(input('post.data'),true);
        
        $roomInfo = [
            'room_name' => $data['room_name'],
//            'room_num' => $data['room_num'],
            'desc' => $data['desc'],
            'password' => isset($data['password']) ? $data['password'] : '',
            'password_m' => isset($data['password']) ? md5($data['password']) : '',
            'run_lottery_time' => isset($data['run_lottery_time']) ?  strtr($data['run_lottery_time'], 'T', ' ') : '',
            'condition_time' => isset($data['condition_time']) ? strtr($data['condition_time'], 'T', ' ') : '',
            'condition_charge' => isset($data['condition_charge']) ? $data['condition_charge'] : '',
            'condition_type' => isset($data['condition_type']) ? $data['condition_type'] : '',
            'type' => isset($data['type']) ? $data['type'] : '',
            'create_time' => currentTime(),
            'invite_code' => isset($data['invite_code']) ? $data['invite_code'] : ''
        ];
        if($roomInfo['condition_type'] == 1){
            unset($roomInfo['condition_time']);
        }
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        if($url){
            $roomInfo['img'] = $url;
        }
        Db::table('free_room')->insert($roomInfo);
        return result(1, '', '');
    }

    //房间详情
    public function roomDetail(){
        $room_id = input('post.room_id');
//        $room_id = 1;
        $info = Db::table('free_room')
            ->where(['id'=>$room_id,'flag'=>1])
            ->find();
        $info['skin_list'] = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.buyPrice,bs.maxPrice')
            ->join('free_skins bs','bs.skin_id = as.id','left')
            ->where(['bs.free_room_id'=>$room_id,'as.flag'=>1])
            ->select();
        $info['player_list'] = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img')
            ->join('free_room_player fp','fp.player_id = p.id')
            ->where(['fp.free_room_id'=>$room_id])
            ->select();
        return result(1, $info, '');
    }

    //修改房间信息
    public function editRoom(){
        $data = json_decode(input('post.data'),true);
        $free_room_id = $data['id'];
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
//            dump($url);
        }
        $roomInfo = [
            'room_name' => $data['room_name'],
            'room_num' => $data['room_num'],
            'desc' => $data['desc'],
            'password' => $data['password'],
            'password_m' => md5($data['password']),
            'run_lottery_time' => strtr($data['run_lottery_time'], 'T', ' '),
//            'condition_time' => strtr($data['condition_time'], 'T', ' '),
            'condition_charge' => $data['condition_charge'],
            'condition_type' => $data['condition_type'],
            'type' => $data['type'],
            'invite_code' => $data['invite_code'],
//            'create_time' => currentTime()
        ];
        if($data['condition_time']){
            $roomInfo['condition_time'] = strtr($data['condition_time'], 'T', ' ');
        }
        if($url){
            $roomInfo['img'] = $url;
        }
        Db::table('free_room')->where('id',$free_room_id)->update($roomInfo);
        return result(1, '', '');
    }

    //删除房间
    public function delRoom(){
        $id = input('post.id');
        Db::table('free_room')->where('id',$id)->setField('flag',0);
        return result(1, '', '');
    }

    //奖品列表
    public function prizeList(){
        $free_room_id = input('post.free_room_id');
        if(!$free_room_id){
            return result(0, '', '缺少房间id信息');
        }
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.itemName,as.imageUrl,as.price,fs.pool,fs.appoint,fs.id as free_skins_id')
            ->join('free_skins fs','fs.skin_id = as.id','left')
//            ->join('player p','p.id = fs.player_id','left')
            ->where(['fs.free_room_id'=>$free_room_id,'as.flag'=>1])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('price','desc')
            ->select();
        $total = Db::table('all_skin')
            ->alias('as')
            ->join('free_skins fs','fs.skin_id = as.id','left')
            ->where(['fs.free_room_id'=>$free_room_id,'as.flag'=>1])
            ->order('price','desc')
            ->count();
        $pool = Db::table('free_room')->where('id',$free_room_id)->value('pool');;
        if($total>0){
            $res = ['total'=>$total,'pool'=>$pool,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '');
        }
    }

    //添加饰品到房间
    public function addSkinToRoom(){
        $data = input('post.data');
        $insertInfo = [
            'free_room_id' => $data['free_room_id'],
            'skin_id' => $data['skin_id']
        ];
        Db::table('free_skins')->insert($insertInfo);
        //计算奖池金额
        $pool = Db::table('all_skin')
            ->alias('as')
            ->join('free_skins fs','fs.skin_id = as.id')
            ->where('fs.free_room_id',$data['free_room_id'])
            ->sum('price');
        $count = Db::table('free_skins')->where('free_room_id',$data['free_room_id'])->count();
        $update = [
            'pool'=>$pool,
            'count'=>$count
        ];
        Db::table('free_room')->where('id',$data['free_room_id'])->update($update);
        return result(1, '', '');
    }

    //指定饰品给玩家
    public function appoint(){
        $skin_id = input('post.skin_id');
        $free_room_id = input('post.free_room_id');
        $account = input('post.account');//玩家账户，手机或者邮箱
        $free_skins_id = input('post.free_skins_id');
//        if(!trim($account)){
//            return result(0, '', '请输入玩家账号');
//        }
        if($account){
            $player_m = Db::table('player')
                ->where(['mobile'=>$account,'flag'=>1])
                ->find();
            $player_e = Db::table('player')
                ->where(['email'=>$account,'flag'=>1])
                ->find();
            if(!$player_m && !$player_e){
                return result(0, '', '玩家不存在');
            }
            $playerInfo = $player_m ? $player_m : $player_e;
            //玩家是否已经加入房间
            $is_add = Db::table('free_room_player')
                ->where(['free_room_id'=>$free_room_id,'player_id'=>$playerInfo['id']])
                ->find();
            if(!$is_add){
                return result(0, '', '错误，玩家暂未参加该活动');
            }
            $update['appoint']    = $playerInfo['mobile'];
            $update['player_id']  = $playerInfo['id'];
            $update['appoint_id'] = $playerInfo['id'];
            $appoint = Db::table('free_skins')
                ->where(['free_room_id'=>$free_room_id,'player_id'=>$playerInfo['id']])
                ->find();
            if($appoint){
                return result(0, '', '错误，该玩家已有指定饰品');
            }
        }else{
            $update['appoint']   = '';
            $update['player_id'] = '';
        }

        Db::table('free_skins')->where(['id'=>$free_skins_id])->update($update);
        return result(1, '', '');
    }

    //删除房间饰品
    public function delSkin(){
        $id = input('post.id');
        $free_room_id = Db::table('free_skins')->where(['id'=>$id])->value('free_room_id');
        Db::table('free_skins')->where(['id'=>$id])->delete();
        $pool = Db::table('all_skin')
            ->alias('as')
            ->join('free_skins fs','fs.skin_id = as.id')
            ->where('fs.free_room_id',$free_room_id)
            ->sum('price');
        $count = Db::table('free_skins')->where('free_room_id',$id)->count();
        $update = [
            'pool'=>$pool,
            'count'=>$count
        ];
        Db::table('free_room')->where('id',$free_room_id)->update($update);
        return result(1, '', '');
    }



    //玩家列表
    public function players(){
        $free_room_id = input('post.free_room_id');
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('player')
            ->alias('p')
            ->join('free_room_player rp','rp.player_id = p.id','left')
            ->where(['rp.free_room_id'=>$free_room_id,'p.flag'=>1])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('free_room_player rp','rp.player_id = p.id','left')
            ->where(['rp.free_room_id'=>$free_room_id,'p.flag'=>1])
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '');
        }
    }

    public function addRobot()
    {
        $free_room_id = input('post.free_room_id', null, 'intval');
        $limit = input('post.limit', 1, 'intval');

        if ($limit < 0) return result(0, '', '至少添加一个机器人');

        if ($free_room_id <= 0) return result(0, '', '房间号异常');

        $player_ids = Db::table('free_room_player')->where('free_room_id', $free_room_id)->column('player_id');
        $list = Db::table('player')
            ->limit($limit)
            ->whereNotIn('id', $player_ids)
            ->where(['flag' => 1, 'type' => 2])
            ->select();

        if (empty($list)) {
            return result(0, '', '无机器人可添加');
        }

        $room_players = [];
        $create_time = currentTime();
        foreach ($list as $k => $v) {
            $room_players[] = [
                'free_room_id' => $free_room_id,
                'player_id' => $v['id'],
                'create_time' => $create_time,
            ];
        }

        Db::startTrans();
        try {
            Db::table('free_room_player')->insertAll($room_players);
            $person_num = Db::table('free_room_player')->where('free_room_id', $free_room_id)->count();
            Db::table('free_room')->where('id', $free_room_id)->setField('person_num', $person_num);

            Db::commit();

            return result(1, '', '成功添加' . count($room_players) . '个机器人!');
        } catch (\Exception $ex) {
            Db::rollback();
        }
        return result(0, '', $ex->getMessage());
    }

}