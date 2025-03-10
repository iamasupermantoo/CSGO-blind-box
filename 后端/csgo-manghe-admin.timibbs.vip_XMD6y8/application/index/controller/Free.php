<?php


namespace app\index\controller;


use think\Db;

class Free
{

    public $skin_list   = array();
    public $players     = array();
    public $playersInfo = array();


    //免费皮肤房间列表
    public function freeRoomList()
    {
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('free_room')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('status','asc')
            ->order('type','asc')
            ->order('pool', 'desc')
            ->where(['flag' => 1])
            ->select();
        unset($list['password']);
        $total = Db::table('free_room')
            ->where(['flag' => 1])
            ->count();
        if ($total > 0) {
            foreach ($list as $k => $v) {
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
                $list[$k]['skin_list'] = Db::table('all_skin')
                    ->alias('as')
                    ->field('as.id,as.itemName,as.imageUrl,as.price')
                    ->join('free_skins fs','fs.skin_id = as.id','left')
                    ->where(['fs.free_room_id'=>$list[$k]['id'],'as.flag'=>1])
                    ->order('as.price','desc')
                    ->limit(0, 3)
                    ->select();
            }
            $res = ['total' => $total, 'list' => $list];
            return result(1, $res, '');
        } else {
            return result(0, '', '无数据');
        }
    }


    //房间详情
    public function freeRoomDetail()
    {
        $free_room_id = input('post.free_room_id');
//        $player_id = input('post.player_id');
        $detail = Db::table('free_room')
            ->where(['id' => $free_room_id, 'flag' => 1])
            ->find();
        unset($detail['password']);
        if (!$detail) {
            return result(0, '', '查无该房间信息');
        }

        //参与人数
//        $detail['player_list'] = Db::table('free_room')
//            ->alias('r')
//            ->where('r.id', $free_room_id)
//            ->field('p.id,p.name,p.img')
//            ->join('free_room_player rp', 'rp.free_room_id=r.id')
//            ->join('player p', 'p.id=rp.player_id', 'LEFT')
//            ->select();

        $detail['player_list'] = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img,p.name')
            ->join('free_room_player rp','rp.player_id = p.id','left')
            ->where('rp.free_room_id',$free_room_id)
            ->select();
        foreach ($detail['player_list'] as $k=>$v){
            $detail['player_list'][$k]['img'] = $detail['player_list'][$k]['img'] ? mainName().$detail['player_list'][$k]['img'] : '';
        }

        //未开奖，正常显示奖品列表
        if($detail['status'] == 1){
//            $detail['skin_list'] = Db::table('free_room')
//                ->alias('r')
//                ->where('r.id',$free_room_id)
//                ->field('s.id,s.name,s.img,s.price')
//                ->join('free_room_skin rs', 'rs.free_room_id=r.id')
//                ->join('free_skins s', 's.id=rs.free_skin_id', 'LEFT')
//                ->order('s.price', 'desc')
//                ->select();

            $detail['skin_list'] = Db::table('all_skin')
                ->alias('as')
                ->field('as.id,as.itemName as name,as.imageUrl as img,as.price')
                ->join('free_skins fs', 'fs.skin_id=as.id', 'LEFT')
                ->order('as.price', 'desc')
                ->where(['fs.free_room_id'=>$free_room_id,'as.flag'=>1])
                ->select();

        }

        //如果已经开奖，查询开奖结果/剩余没抽中的奖品
        if($detail['status'] == 2){
            $detail['player_skin'] = unserialize($detail['result'])['player_skin'];//开奖结果
            $detail['skin_list'] = unserialize($detail['result'])['skin_list'];//剩余没抽中的奖品
            if($detail['player_skin']){
                foreach ($detail['player_skin'] as $k=>$k){
                    $detail['player_skin'][$k]['player_img'] = $detail['player_skin'][$k]['player_img'] ? mainName().$detail['player_skin'][$k]['player_img'] : '';
                }
            }
        }
        if(isset($detail['player_skin']) && $detail['player_skin']){
            $detail['player_skin'] = array_sort($detail['player_skin'],'price','0');
            $detail['player_skin'] = array_values($detail['player_skin']);
        }
        $detail['img'] = $detail['img'] ? mainName().$detail['img'] : '';
        return result(1, $detail, '');
    }


    //玩家加入免费皮肤房间
    public function addFreeRoom(){
        $free_room_id = input('post.free_room_id');
        $player_id    = input('post.player_id');
        $password     = input('post.password');
        if(empty(trim($player_id))){
            return result(0, '', '缺少玩家信息');
        }
        $roomInfo = Db::table('free_room')
            ->where('id',$free_room_id)
            ->find();
        if(!$roomInfo){
            return result(0, '', '房间信息不存在');
        }

        if ($roomInfo['invite_code']) {
            $inviter_id = Db::table('player')->where('invite_code', $roomInfo['invite_code'])->column('id');
            $invitation = Db::table('invitation')->where(['inviter_id' => $inviter_id, 'invitees_id' => $player_id])->find();
            if (empty($invitation)) {
                return result(0, '', '专属房间,您无法加入该房间!');
            }
        }

        if($roomInfo['status'] == 2){
            return result(0, '', '活动已结束');
        }
        //run_lottery_time既是开奖时间也是结束时间
        if(currentTime() > $roomInfo['run_lottery_time']){
            return result(0, '', '报名时间已结束');
        }
        $room_player['free_room_id']   = $free_room_id;
        $room_player['player_id']      = $player_id;
        $room_player['create_time']    = currentTime();
        $re = Db::table('free_room_player')
            ->where(['free_room_id'=>$free_room_id,'player_id'=>$player_id])
            ->find();
        if($re){
            return result(0, '', '您已在该房间内，看看别的活动房间吧');
        }

        //参加条件类型，1：密码/口令，2：充值
        if($roomInfo['condition_type']==1){
            if(md5(trim($password)) == $roomInfo['password_m']){
                //口令正确
                Db::table('free_room_player')->insert($room_player);
                $person_num = Db::table('free_room_player')->where('free_room_id',$free_room_id)->count();
                Db::table('free_room')->where('id',$free_room_id)->setField('person_num',$person_num);
                return result(1, '', '加入成功');
            }else{
                return result(0, '', '密码或口令有误，参加失败');
            }
        }else if($roomInfo['condition_type']==2){
            $charge = $roomInfo['condition_charge'];
            $time   = $roomInfo['condition_time'];
            //balance类型：1：皮肤兑换T币（增加），2：消费，3：充值
            $player_charge = Db::table('recharge')
                ->where(['status'=>3])
                ->where(['player_id'=>$player_id])
                ->where('create_time','>=',$time)
                ->sum('money');
            if($charge <= $player_charge){
                Db::table('free_room_player')->insert($room_player);
                $person_num = Db::table('free_room_player')->where('free_room_id',$free_room_id)->count();
                Db::table('free_room')->where('id',$free_room_id)->setField('person_num',$person_num);
                return result(1, '', '加入成功');
            }else{
                return result(0, '', '未达到充值额度条件，参加失败');
            }
        }else if($roomInfo['condition_type'] == 3){
            $charge = $roomInfo['condition_charge'];
            $time   = $roomInfo['condition_time'];
            //balance类型：1：皮肤兑换T币（增加），2：消费，3：充值
            $player_charge = Db::table('recharge')
                ->where(['status'=>3])
                ->where(['player_id'=>$player_id])
                ->where('create_time','>=',$time)
                ->sum('money');
            if((md5(trim($password)) == $roomInfo['password_m']) && ($charge <= $player_charge)){
                Db::table('free_room_player')->insert($room_player);
                $person_num = Db::table('free_room_player')->where('free_room_id',$free_room_id)->count();
                Db::table('free_room')->where('id',$free_room_id)->setField('person_num',$person_num);
                return result(1, '', '加入成功');
            }else{
                return result(0, '', '口令错误或者充值金额未达标，参加失败');
            }
        }
    }


    //免费皮肤开奖
    public function getSkin(){
        $free_room_id = input('post.free_room_id');
        $roomInfo = Db::table('free_room')
            ->where('id',$free_room_id)
            ->find();
        if(!$roomInfo){
            return result(0, '', '房间信息不存在');
        }
        if($roomInfo['status'] == 2){
            return result(0, '', '房间已开奖');
        }
        //run_lottery_time既是开奖时间也是结束时间
        if(currentTime() < $roomInfo['run_lottery_time']){
            return result(0, '', '开奖时间还未到');
        }

//        $skin_list = Db::table('free_room')
//            ->alias('r')
//            ->where('r.id',$free_room_id)
////            ->field('s.id,s.name,s.img,s.price')
//            ->field('s.*')
//            ->join('free_room_skin rs', 'rs.free_room_id=r.id')
//            ->join('free_skins s', 's.id=rs.free_skin_id', 'LEFT')
//            ->order('s.price', 'aes')
//            ->select();

//        $player_list = Db::table('free_room')
//            ->alias('r')
//            ->where('r.id', $free_room_id)
//            ->field('p.id,p.name,p.img')
//            ->join('free_room_player rp', 'rp.free_room_id=r.id')
//            ->join('player p', 'p.id=rp.player_id', 'LEFT')
//            ->select();
        $skin_list = Db::table('all_skin')
            ->alias('as')
            ->join('free_skins fs','fs.skin_id = as.id','left')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,fs.free_room_id,fs.appoint,fs.appoint_id,fs.player_id')
            ->where('fs.free_room_id',$free_room_id)
            ->select();

        $player_list = Db::table('player')
            ->alias('p')
            ->join('free_room_player rp', 'rp.player_id=p.id','left')
            ->field('p.id,p.name,p.img')
            ->where('rp.free_room_id',$free_room_id)
            ->select();

        $battleRule = new BattleRule();
        foreach ($player_list as $k=>$v){
            $battleRule->query($player_list[$k]['id']);
        }

        $players      = [];
        $playersInfo  = [];
        foreach ($player_list as $key=>$val){
            array_push($players,$player_list[$key]['id']);
            array_push($playersInfo,$player_list[$key]);
        }
        if(count($players) < 1){
            return result(0, '', '暂无抽奖玩家');
        }
        $this->skin_list   = $skin_list;
        $this->players     = $players;
        $this->playersInfo = $playersInfo;

        $re = $this->gets();

        $re = json_decode($re->getContent(),true);
        if(!isset($re['data'])){
            return result(0, '', '暂无奖品，无法开奖');
        }
        if(!$re['status']){
            return result(0, '', '抽奖发生错误');
        }

//        $this->playersInfo;
        $this->skin_list;
        $update['status'] = 2;
        $update['result'] = serialize([
            'player_skin' => $re['data'],
            'player_list' => $this->playersInfo,
            'skin_list'   => $this->skin_list,
        ]);
        //抽奖完毕后修改房间状态,以及开奖结果信息
        Db::table('free_room')->where('id',$free_room_id)->update($update);
        return result(1, '', '抽奖完成');
    }

    // pub
    public function gets(){
        Db::startTrans();
        try {
            //每一轮都会剔除一次奖品和玩家信息，所以没一轮奖品默认从列表第一个开始，循环中的$k不需要，都替换为0
            $result = [];
            foreach ($this->skin_list as $k=>$v){
                $playerSkinsInfo = $this->skin_list[0];

                if($this->skin_list[0]['appoint']){
                    //如果设置指定，则从奖池中剔除该奖项，抽奖人队列中剔除该玩家，并存放玩家背包
                    if (in_array($this->skin_list[0]['appoint_id'],$this->players)){
//                    dump('指定了一个玩家（id:'.$this->skin_list[0]['appoint_id'].'）获得该奖，奖品是,id:'.$this->skin_list[0]['id'].','.$this->skin_list[0]['name']);
                        //把皮肤存入玩家背包
                        unset($playerSkinsInfo['id']);
                        unset($playerSkinsInfo['flag']);
                        unset($playerSkinsInfo['appoint_id']);
                        unset($playerSkinsInfo['appoint']);
                        $playerSkinsInfo['player_id']   = $this->skin_list[0]['appoint_id'];
                        $playerSkinsInfo['create_time'] = currentTime();
                        $playerSkinsInfo['way'] = 4;
                        Db::table('player_skins')->insert($playerSkinsInfo);
                        $battleRule = new BattleRule();
                        $battleRule->editAssets($playerSkinsInfo['player_id'],'','',$playerSkinsInfo['price'],'');
                        $re = [
                            'skin_id'     => $this->skin_list[0]['id'],
                            'skin_img'    => $playerSkinsInfo['img'],
                            'player_id'   => $this->skin_list[0]['appoint_id'],
                            'player_name' => array_merge(arrayFilterFieldValue($this->playersInfo,'id',$this->skin_list[0]['appoint_id']))[0]['name'],
                            'player_img'  => array_merge(arrayFilterFieldValue($this->playersInfo,'id',$this->skin_list[0]['appoint_id']))[0]['img'],
                            'price' => $playerSkinsInfo['price']
                        ];
                        array_push($result,$re);
                        //移除指定的玩家,剩余玩家继续抽
                        $key = array_search($this->skin_list[0]['appoint_id'] ,$this->players);
                        array_splice($this->players,$key,1);

                        $this->playersInfo = array_remove_by_key($this->playersInfo,$key);

                        unset($this->skin_list[0]);//从奖池中剔除该奖项
                        $this->skin_list = array_values($this->skin_list);//数组重新排序

                    }
                }else{
                    $players_num = count($this->players);
                    //如果玩家已经没了，则不抽奖
                    if($players_num>0){
                        //正常抽，根据玩家列表长度生成随机数匹配玩家列表索引值，$rand为数组的索引值，非玩家id
                        $rand = rand(0,$players_num - 1);
//                    dump('中奖玩家（id:'.$this->players[$rand].'）获得该奖，奖品是,id:'.$this->skin_list[0]['id'].','.$this->skin_list[0]['name']);
                        //把皮肤存入玩家背包 //顺序不能错，在unset后面数据就错了
                        unset($playerSkinsInfo['id']);
                        unset($playerSkinsInfo['flag']);
                        unset($playerSkinsInfo['appoint_id']);
                        unset($playerSkinsInfo['appoint']);
                        $playerSkinsInfo['player_id']   = $this->players[$rand];
                        $playerSkinsInfo['create_time'] = currentTime();
                        $playerSkinsInfo['way'] = 4;

                        Db::table('player_skins')->insert($playerSkinsInfo);
                        $battleRule = new BattleRule();
                        $battleRule->editAssets($playerSkinsInfo['player_id'],'','',$playerSkinsInfo['price'],'');
                        $re = [
                            'skin_id'     => $this->skin_list[0]['id'],
                            'skin_img'    => $playerSkinsInfo['img'],
                            'player_id'   => $playerSkinsInfo['player_id'],
                            'player_name' => array_merge(arrayFilterFieldValue($this->playersInfo,'id',$playerSkinsInfo['player_id']))[0]['name'],
                            'player_img'  => array_merge(arrayFilterFieldValue($this->playersInfo,'id',$playerSkinsInfo['player_id']))[0]['img'],
                            'price'       => $playerSkinsInfo['price']
                        ];
                        array_push($result,$re);

                        //移除指定的玩家,剩余玩家继续抽
                        $key = array_search($this->players[$rand],$this->players);
                        array_splice($this->players,$key,1);

                        $this->playersInfo = array_remove_by_key($this->playersInfo,$key);

                        unset($this->skin_list[0]);//从奖池中剔除该奖项
                        $this->skin_list = array_values($this->skin_list);//数组重新排序
                    }else{
                        break;
                    }
                }
            }
            Db::commit();
            return result(1, $result, '');;
        }catch (\Exception $e){
            Db::rollback();
            return result(0, $e->getMessage(), '');;
        }

    }


    //用户参与的免费房间
    public function myFreeRoom(){
        $player_id = input('post.player_id');
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $list = Db::table('free_room')
            ->alias('fr')
            ->field('fr.*')
            ->join('free_room_player frp','frp.free_room_id = fr.id')
            ->where(['fr.flag' => 1,'frp.player_id'=>$player_id])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('status','asc')
            ->order('pool', 'desc')
            ->select();
        unset($list['password']);
        $total = Db::table('free_room')
            ->alias('fr')
            ->join('free_room_player frp','frp.free_room_id = fr.id')
            ->where(['fr.flag' => 1,'frp.player_id'=>$player_id])
            ->count();
        if ($total > 0) {
            foreach ($list as $k => $v) {
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
                $list[$k]['skin_list'] = Db::table('all_skin')
                    ->alias('as')
                    ->field('as.id,as.itemName,as.imageUrl,as.price')
                    ->join('free_skins fs','fs.skin_id = as.id','left')
                    ->where(['fs.free_room_id'=>$list[$k]['id'],'as.flag'=>1])
                    ->order('as.price','desc')
                    ->limit(0, 3)
                    ->select();
            }
            $res = ['total' => $total, 'list' => $list];
            return result(1, $res, '');
        } else {
            return result(0, '', '无数据');
        }
    }



}