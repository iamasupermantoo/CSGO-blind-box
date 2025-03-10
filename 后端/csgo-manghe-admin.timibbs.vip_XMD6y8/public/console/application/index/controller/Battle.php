<?php


namespace app\index\controller;


use app\push\controller\Push;
use app\push\controller\Test;
use think\cache\driver\Redis;
use think\Db;

class Battle
{
    //对战列表
    public function battleList(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',12);
        $list = Db::table('battle')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('status','asc')
            ->order('create_time','desc')
            ->select();
        $total =  Db::table('battle')
            ->count();
        if(count($list)>0){
            foreach ($list as $k=>$v){
                $list[$k]['player_info'] = unserialize($list[$k]['player_info']);
                if($list[$k]['player_info']){
                    foreach ($list[$k]['player_info'] as $key=>$val) {
                        $list[$k]['player_info'][$key] = Db::table('player')->where('id',$list[$k]['player_info'][$key]['id'])
                            ->field('id,name,img')
                            ->find();
                        $list[$k]['player_info'][$key]['img'] = $list[$k]['player_info'][$key]['img'] ? mainName().$list[$k]['player_info'][$key]['img'] : '';
                    }
                }
                $boxInfo = json_decode($list[$k]['boxInfo'],true);
                if($boxInfo){
                    foreach ($boxInfo as $key => $val){
                        $boxInfo[$key]['img_main'] = mainName().$boxInfo[$key]['img_main'];
                        $boxInfo[$key]['img_active'] = mainName().$boxInfo[$key]['img_active'];
                    }
                }
                $list[$k]['boxInfo'] = $boxInfo;
            }
            $re = ['total'=>$total,'battleList'=>$list];
            return result(1,$re,'');
        }
        return result(1,'','无数据');
    }
    //对战盒子列表
    public function battleBoxList(){
        $list = Db::table('box')
            ->where(['flag'=>1,'battle'=>1,'skin_exist'=>1])
            ->order('price','asc')
            ->select();
//        dump($list);
        if(count($list)>0){
            foreach ($list as $k=>$v){
                $count = Db::table('box_skins')->where('box_id',$list[$k]['id'])
                    ->field(['sum(set_stock) as set_stock,sum(set_stock_vip) as set_stock_vip,sum(stock_group) as stock_group,price'])
                    ->select();

                if(($count[0]['set_stock'] <= 0) || ($count[0]['set_stock_vip'] <= 0) || ($count[0]['stock_group'] <= 0)){
                    unset($list[$k]);
                }else{
                    $list[$k]['img_main'] = mainName().$list[$k]['img_main'];
                    $list[$k]['img_active'] = mainName().$list[$k]['img_active'];
                    //查询每个盒子是否存在低于箱子价格的饰品,有则显示，无则不显示，
                    $skin = Db::table('all_skin')
                        ->alias('as')
                        ->field('as.price')
                        ->join('box_skins bs','bs.skin_id=as.id')
                        ->where('bs.box_id',$list[$k]['id'])
                        ->where('as.price','<=',$list[$k]['price'])
                        ->find();
                    if(!$skin){
                        unset($list[$k]);
                    }
                }
            }
            $list = array_values($list);
            return result(1,$list,'');
        }
    }
    //玩家创建房间
    public function createRoom($mode=null,$boxs=null,$player_id=null)
    {
        $mode  = input('post.mode') ? input('post.mode') : $mode;                      //对战模式，2-3-4
        $boxs  = input('post.boxInfo') ? input('post.boxInfo') : $boxs;
        $player_id = input('post.player_id') ? input('post.player_id') : $player_id;
        $type = 2;                                         //类型，1：开箱，2：对战
        $room_num = 'R' . date('Ymd') . rand(10000, 99999) . date('His');
        $create_time = currentTime();

        $battleRule = new BattleRule();
        $battleRule->query($player_id);

        // $player = Db::table('player')->where('id',$player_id)->find();
        // if($player['group'] == 0){
        //     return result(0, '', '维护中');
        // }

//        $boxs = [
//            '0' => [
//                'box_id' => 1,
//                'num' => 2,
//                'name' => '盒子1'
//            ],
//            '1' => [
//                'box_id' => 2,
//                'num' => 1,
//                'name' => '盒子2'
//            ]
//        ];
//        $mode = 2;
//        $player_id = 1;


        $boxInfo = [];
        if (count($boxs) < 1) {
            return result(0, '', '请选择盒子');
        }else{
            foreach ($boxs as $k => $v){
                $box_id = $boxs[$k]['box_id'];
                $boxInfo[$k] = Db::table('box')
                    ->field('id as box_id,name,img_main,img_active,price')
                    ->where('id',$box_id)
                    ->find();
                $boxInfo[$k]['num']    = $boxs[$k]['num'];
            }
        }

        //本场对战所有箱子，所有玩家参与后，销售价格总和
        $total_price = array_sum(array_map(function ($val){return $val['price'];},$boxInfo)) * $mode;
        //本场对战所有箱子，单个玩家消费金额
        $total_value = array_sum(array_map(function ($val){return $val['price'];},$boxInfo));


        $player_info[] = Db::table('player')
            ->field('id,name,img,group,group_vip,type')
            ->where('id',$player_id)
            ->find();
        $battleInfo = [
            'home_owner' => $player_id,
            'room_num' => $room_num,
            'player_ids' => $player_id,
            'mode' => $mode,
            'create_time' => $create_time,
            'boxInfo' => json_encode($boxInfo),
            'player_info' => serialize($player_info),
            'total_price' => $total_price
        ];
        $box = new Box();
        Db::startTrans();
        try {
            $battle_id = Db::table('battle')->insertGetId($battleInfo);
            $_SESSION['battle_id'] = $battle_id;
            $_SESSION['battleInfo'] = json_encode($battleInfo);
            foreach ($boxInfo as $k => $v) {
                $re = $box->buyBox($player_id, $boxInfo[$k]['box_id'], $boxInfo[$k]['num'], $type, $battle_id,-2);
                $res = json_decode($re->getContent(), true);
                if ($res['status'] == 0) {
                    Db::rollback();
                    return result(0, '', $res['msg']);
                }
                $battleInfo['total_amount'] = $res['data']['total_amount'];
            }
            $history['battle_id'] = $battle_id;
            $history['player_id'] = $player_id;
            $history['type'] = 1;
            $history['num'] = count($boxInfo);
            Db::table('battle_player')->insert($history);
            Db::commit();
            $battleInfo['battle_id'] = $battle_id;
            $battleInfo['boxInfo'] = json_decode($battleInfo['boxInfo'], true);
            //-----推送一条数据
            $push = new Push();
            $battleInfo['status'] = 1;
            $battleInfo['id'] = $battle_id;
            $push->push($battleInfo,'add');
            //-------------------
            $battleRule = new BattleRule();
            $battleRule->editAssets($player_id,'',-$total_value,'',count($boxInfo));
            return result(1, $battleInfo, '创建成功');
        } catch (\Exception $e) {
            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }


    //房间详细
    public function battleDetail(){
        $battle_id = input('post.battle_id');
        if(!trim($battle_id)){
            return result(0, '', '缺少对战id信息');
        }
        $info = Db::table('battle')
            ->where('id',$battle_id)
            ->find();
        $winners = [];
        if($info['winner']){
            $winners = explode(',',$info['winner']);
        }
        $info['player_info'] = unserialize($info['player_info']);
        if($info['player_info']){
            foreach ($info['player_info'] as $k=>$v){
                $player_info = Db::table('player')->where('id',$info['player_info'][$k]['id'])
                    ->field('id,name,img')
                    ->find();
                $info['player_info'][$k]['name'] = $player_info['name'] ? $player_info['name'] : '';
                $info['player_info'][$k]['name'] = $player_info['img'] ?  mainName().$player_info['name'] : '';
            }
        }
        if($info){
            $result_info = json_decode($info['result_info'],true);
            $winner_owner = [];
            if(($info['status'] >= 2) && (count($winners) == 1) ){
                //进行中/结束 状态  +   一个赢家
                if($result_info){
                    foreach ($result_info as $k => $v){
                        $item = $result_info[$k];
                        if($item){
                            foreach ($item as $key => $val){
                                $perInfo['id'] = $item[$key]['id'];
                                $perInfo['name'] = $item[$key]['name'];
                                $perInfo['img'] = $item[$key]['img'];
                                $perInfo['price'] = $item[$key]['price'];
                                if($info['winner']>0){
                                    array_push($winner_owner,$perInfo);
                                }
                            }
                        }
                    }
                }
            }else if(($info['winner'] < 1) && ($info['status'] < 1)){
                //等待和进行状态
//                foreach ($info['player_info'] as $Key=>$Val){
//                    if($result_info){
//                        foreach ($result_info as $k => $v){
//                            $item = $result_info[$k];
//                            if($item){
//                                foreach ($item as $key => $val){
////                                dd($info['player_info'][$Key]['id']);
////                                dd($item[$key]['player_id']);
//                                    if($info['player_info'][$Key]['id'] == $item[$key]['player_id']){
//                                        $skins['id'] = $item[$key]['id'];
//                                        $skins['name'] = $item[$key]['name'];
//                                        $skins['img'] = $item[$key]['img'];
//                                        $skins['price'] = $item[$key]['price'];
//                                        $info['player_info'][$Key]['skins'][] = $skins;
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
            }
            $info['winner_owner'] = $winner_owner;
            $info['boxInfo'] = json_decode($info['boxInfo'],true);
            foreach ($info['boxInfo'] as $k=>$v){
                $info['boxInfo'][$k]['img_main'] = mainName().$info['boxInfo'][$k]['img_main'];
                $info['boxInfo'][$k]['img_active'] = mainName().$info['boxInfo'][$k]['img_active'];
                $info['boxInfo'][$k]['skin_list'] = Db::table('all_skin')
                    ->alias('as')
                    ->join('box_skins bs','bs.skin_id = as.id')
                    ->field('as.id,as.itemName,as.imageUrl')
                    ->where('bs.box_id',$info['boxInfo'][$k]['box_id'])
                    ->select();
            }
            return result(1, $info, '');
        }else{
            return result(0, '', '');
        }
    }


    //玩家加入房间
    public function addBattle($battle_id=null,$player_id=null)
    {
        $battle_id = input('post.battle_id') ? input('post.battle_id') : $battle_id;
        $player_id = input('post.player_id') ? input('post.player_id') : $player_id;

        // $player = Db::table('player')->where('id',$player_id)->find();
        // if($player['group'] == 0){
        //     return result(0, '', '维护中');
        // }

        if (empty(trim($player_id))) {
            return result(0, '', '缺少入战玩家信息');
        }

        $battleRule = new BattleRule();

        $openModel = Db::table('open_model')->find();
        if($openModel){
            $model = $openModel['model'];
        }else{
            $model = 'stock';
        }

        $battle_info = Db::table('battle')
            ->where('id', $battle_id)
            ->find();
        if (!$battle_info) {
            return result(0, '', '游戏房间信息不存在');
        }
        $player_ids = $battle_info['player_ids'];
        $player_ids = explode(',', $player_ids);
        foreach ($player_ids as $k=>$v){
            $battleRule->query($player_ids[$k]);
        }
        if(in_array($player_id,$player_ids)){
            return result(0, '', '您已经在该房间，不可与自己对战');
        }
        if (count($player_ids) >= $battle_info['mode']) {
            return result(0, '', '房间人数已满');
        }
        $boxInfo = json_decode($battle_info['boxInfo'], true);
        if(!$boxInfo){
            return result(0, '', '盲盒数据不存在');
        }
        //入战玩家购买相同的盒子
        $box = new Box();
        Db::startTrans();
        try {
            $total_amount = 0;
            $total_price = 0;
            foreach ($boxInfo as $k => $v) {
                $re = $box->buyBox($player_id, $boxInfo[$k]['box_id'], $boxInfo[$k]['num'], '2', $battle_id,-2);
                $res = json_decode($re->getContent(), true);
                if ($res['status'] == 0) {
                    Db::rollback();
                    return result(0, '', $res['msg']);
                }
                $total_amount = $res['data']['total_amount'];
                $total_price  += $res['data']['total_price'];
            }
            $battle_info['player_ids'] = $battle_info['player_ids'] . ',' . $player_id;
            $player_info = Db::table('player')
                ->field('id,name,img,group,group_vip,type')
                ->where('id',$player_id)
                ->find();
            $battle_info['player_info'] = unserialize($battle_info['player_info']);
            array_push($battle_info['player_info'],$player_info);
            $battle_info['player_info'] = serialize($battle_info['player_info']);
            Db::table('battle')->update($battle_info);
            $player_ids = explode(',', $battle_info['player_ids']);
            $result = [];
            if (count($player_ids) >= $battle_info['mode']) {
                if($model == 'stock'){
                    $result = $this->startBattleStock($battle_id);
                }else if ($model == 'range'){
                    $result = $battleRule->startBattle($battle_id);
                }
                if(is_array($result)){
                    if($result && ($result['result'] == [])){
                        return result(0, '','对战失败');
                    }
                }else{
                    $re = json_decode($result->getContent(),true);
                    if($re['status'] == 0){
                        return result(0, '','加入对战失败');
                    }
                }
                $battle = [
                    'battle_status'    => 'start',
                    'battle_statusStr' => '人数已满，开始对战',
                    'total_amount'     => $total_amount,
                    'result'           => $result
                ];
                Db::table('battle')->where('id',$battle_id)->setField('status',2);//进行中
                //-----推送一条数据
                $push = new Push();
                $battle_info['status'] = 2;
                $battle_info['id'] = $battle_id;
                $battle_info['boxInfo'] = json_decode($battle_info['boxInfo'],true);
                $push->push($battle_info,'update');
                //-------------------
            } else {
                $battle = [
                    'battle_status' => 'wait',
                    'battle_statusStr' => '人数未满，继续等待',
                    'total_amount'=>$total_amount,
                    'result'           => $result
                ];
                //-----推送一条数据
                $push = new Push();
                $battle_info['status'] = 1;
                $battle_info['id'] = $battle_id;
                $battle_info['boxInfo'] = json_decode($battle_info['boxInfo'],true);
                $push->push($battle_info,'update');
                //-------------------
            }
            $history['battle_id'] = $battle_id;
            $history['player_id'] = $player_id;
            $history['type'] = 1;
            $history['num'] = count($boxInfo);
            Db::table('battle_player')->insert($history);
            Db::commit();
            $battleRule = new BattleRule();
            $battleRule->editAssets($player_id,'',-$total_price,'',count($boxInfo));
            return result(1, $battle, '加入成功');
        } catch (\Exception $e) {
            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }


    //开始对战,不设置库存类型开奖
    public function startBattle()
    {
        $battle_id = input('post.battle_id');
        $battle_info = Db::table('battle')
            ->where('id', $battle_id)
            ->find();
        if (!$battle_info) {
            return result(0, '', '游戏房间信息不存在');
        }
        if ($battle_info['status'] == 2) {
            return result(0, '', '对战已结束');
        }
        $player_ids = $battle_info['player_ids'];
        $player_ids = explode(',', $player_ids);
        $boxInfo = json_decode($battle_info['boxInfo'], true);
        $openBox = new OpenBox();
        //每个玩家
        $players = [];
        foreach ($player_ids as $k => $v) {
            //每个盒子
            $players[$k]['player_id'] = $player_ids[$k];
            $price = 0;//每个玩家所有盒子开出皮肤的总价值
            $players[$k]['skins'] = [];//所有开出皮肤的列表
            foreach ($boxInfo as $key => $val) {
                //根据每个盒子数量，多次开奖
                $num = $boxInfo[$key]['num'];
                $oneBox = [];
                for ($i = 0; $i < $num; $i++) {
                    $result = $openBox->getResult($boxInfo[$key]['box_id'], $player_ids[$k], $boxInfo[$key]['name']);
                    unset($result['v']);
                    $result['battle_id'] = $battle_id;
                    $oneBox[$i] = $result;
                    $price += isset($result['price']) ? $result['price'] : 0;
                    array_push($players[$k]['skins'], $result);
                }
                $boxInfo[$key]['result'] = $oneBox;
            }
            $players[$k]['price'] = $price;
            $players[$k]['result_info'] = $boxInfo;
        }
        //判断出是谁获胜,获胜者得所有皮肤，失败方得0.01，平局则各自得各自的东西
        $prices = [];
        foreach ($players as $k => $v) {
            array_push($prices, $players[$k]['price']);
        }
        //如果开出的皮肤累计价值全部都相等，$uq只有一条数据，代表平局
        $uq = array_unique($prices);
        if (count($uq) == 1) {
            //平局
            foreach ($players as $key => $val) {
                $skins = $players[$key]['skins'];
                foreach ($skins as $k => $v) {
                    $skins[$k]['player_id'] = $players[$key]['player_id'];
                    if ($skins[$k]['id'] > 0) {
                        unset($skins[$k]['id']);
                        $this->putSkinToPackage($skins[$k]);
                    }
                }
                unset($players[$key]['skins']);
            }
        } else {
            //非平局,把失败方的所有皮肤归属于胜利者
            $maxPrice = max($prices);
            $r = $this->deep_in_array($maxPrice, $players);
            $winner_id = $r['player_id'];
            //把每个奖品存入获胜者背包
            foreach ($players as $key => $val) {
                $skins = $players[$key]['skins'];
                foreach ($skins as $k => $v) {
                    $skins[$k]['player_id'] = $winner_id;
                    if ($skins[$k]['id'] > 0) {
                        unset($skins[$k]['id']);
                        $this->putSkinToPackage($skins[$k]);
                    }
                }
                unset($players[$key]['skins']);
            }
            $update['winner'] = $winner_id;
            //给失败玩家账户增加0.01
            $this->addBalanceToLoser($winner_id, $player_ids);
        }
        //本场对战状态修改为已结束
        $update['status'] = 2;
        $update['result_info'] = json_encode($players);
        Db::table('battle')
            ->where('id', $battle_id)
            ->update($update);
        return result(1, $players, '');
    }


    //皮肤放入用户背包
    public function putSkinToPackage($skinInfo = array())
    {
        $skinInfo['create_time'] = currentTime();
        $skinInfo['way'] = 2;
        Db::table('player_skins')->insert($skinInfo);
    }

    //给失败玩家账户增加0.01
    public function addBalanceToLoser($winner_id = null, $player_ids = array())
    {
        $balance = new Balance();
        foreach ($player_ids as $k => $v) {
            if ($player_ids[$k] !== $winner_id) {
                $playerInfo = Db::table('player')
                    ->where(['id' => $player_ids[$k], 'flag' => 1])
                    ->find();
                $balance->opBalance($player_ids[$k], 0.01, $playerInfo['total_amount'], 1);
            }
        }
    }


    //判断二维数组中是否存在某个值
    public function deep_in_array($value, $array)
    {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item)) {
                return $item;
            } else if ($this->deep_in_array($value, $item)) {
                return $item;
            }
        }
        return false;
    }

    public $prizes = array();
    public $totalStock = 0;
    public $player_skin = array();
    public $value = 0;
    public $str = '';

    //开始对战（库存类型）
    public function startBattleStock($battle_id=null){
        $battle_id = input('post.battle_id') ? input('post.battle_id') : $battle_id;
        $battle_info = Db::table('battle')
            ->where('id', $battle_id)
            ->find();
        $player_info = unserialize($battle_info['player_info']);
        $battle_info['player_info'] = Db::table('player')
            ->alias('p')
            ->field('p.id,name,img')
            ->join('battle_player bp','bp.player_id = p.id','left')
            ->where('bp.battle_id',$battle_id)
            ->select();
        if (!$battle_info) {
            return result(0, '', '游戏房间信息不存在');
        }
        if (($battle_info['status'] == 3) || $battle_info['update_time']) {
            return result(0, '', '对战已结束');
        }

        //概率组账号id
        $groups = [];
        $groupAccount = Db::table('player')
            ->field('id')
            ->where('group',1)
            ->select();
        //如果存在概率组，则查询对应箱子饰品信息
        $group_skins = [];
        if($groupAccount){
            foreach ($groupAccount as $k=>$v){
                $groups[] = $groupAccount[$k]['id'];
            }
        }

        //vip账号id
        $vips = [];
        $vipAccount = Db::table('player')
            ->field('id')
            ->where('group_vip',1)
            ->select();
        //如果存在vip，则查询对应箱子饰品信息
        if($vipAccount){
            foreach ($vipAccount as $k=>$v){
                $vips[] = $vipAccount[$k]['id'];
            }
        }

        $player_ids = $battle_info['player_ids'];
        $player_ids = explode(',', $player_ids);
        $boxInfo = json_decode($battle_info['boxInfo'], true);
        $info = [];
        foreach ($boxInfo as $k=>$v){
            if($boxInfo[$k]['num']>1){
                $mum = $boxInfo[$k]['num'];
                for ($i=0;$i<$mum;$i++){
                    $boxInfo[$k]['num'] = 1;
                    array_push($info,$boxInfo[$k]);
                }
            }else{
                array_push($info,$boxInfo[$k]);
            }
        }
        $boxInfo = $info;
        //分轮次开奖，so 先循环$boxInfo
        Db::startTrans();
        try {
            $rounds = [];//总论次开奖信息
            $player = [];//用于设置每个用户得奖价值总和
            foreach ($boxInfo as $Key=>$value){
                //当前轮次的奖品列表
                $this->prizes = [];
                $this->prizes = Db::table('all_skin')
                    ->alias('as')
                    ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.probability,bs.type_id,bs.delivery,bs.buyPrice,bs.maxPrice,bs.box_id,bs.type_id,bs.stock,bs.stock_group,bs.stock_vip')
                    ->join('box_skins bs','bs.skin_id = as.id')
                    ->where(['bs.box_id' => $boxInfo[$Key]['box_id']])
                    ->order('bs.stock','asc')
                    ->select();
                //当前轮次，多个玩家依次开奖+
                //本轮开奖列表$current（包含多个玩家开同一个盲盒的奖品信息）
                $current = [];
                foreach ($player_ids as $key => $val){
                    $player[$Key][$player_ids[$key]]['value'] = 0;
                    $start = 0;
                    $end   = 0;
                    if($this->prizes){
                        //判断是否是概率组用户
                        $is_group = in_array($player_ids[$key],$groups);
                        $is_vip   = in_array($player_ids[$key],$vips);
                        if($is_vip){
                            $this->str = 'stock_vip';
                            //所有奖品总库存
                            $this->totalStock = array_sum(array_map(function($val){return $val[$this->str];}, $this->prizes));
                        }else if($is_group){
                            $this->str = 'stock_group';
                            //所有奖品总库存
                            $this->totalStock = array_sum(array_map(function($val){return $val[$this->str];}, $this->prizes));
                        }else{
                            $this->str = 'stock';
                            //所有奖品总库存
                            $this->totalStock = array_sum(array_map(function($val){return $val[$this->str];}, $this->prizes));
                        }
                        if($this->totalStock <= 0){
                            $mendStock = new Opp();
                            $mendStock->mend($boxInfo[$Key]['box_id'],$this->str);
                            $this->getPrizes($boxInfo[$Key]['box_id']);
                            $this->totalStock = array_sum(array_map(function($val){return $val[$this->str];}, $this->prizes));
                        }
                        $rand = rand(1,$this->totalStock);

                        if($is_vip){
                            foreach ($this->prizes as $k=>$v){
                                $this->str = 'stock_vip';
                                if($this->prizes[$k]['stock_vip'] > 0){
                                    // $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($stock/$this->totalStock)) * 100;
                                    // $end += (($this->prizes[$k]['re_probability']) * 100);
                                    $end += $this->prizes[$k]['stock_vip'];
                                    // dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].',价格：'.$this->prizes[$k]['price'].',库存：'.$this->prizes[$k]['stock_vip'].'】在'.$start.'--'.$end.'范围内.');
                                    if(($start < $rand) && ($rand <= $end)){
                                        //中奖($this->prizes[$k])后，把该奖品剔除一个库存，如果库存0，直接剔掉
                                        // dump($rand.',奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].',价格：'.$this->prizes[$k]['price'].',库存：'.$this->prizes[$k]['stock_vip'].'】');
                                        $this->prizes[$k][$this->str] -= 1;
                                        $this->player_skin = $this->prizes[$k];
                                        $this->player_skin['open_number']    = $rand;
                                        $this->player_skin['open_number_md'] = md5($rand);
                                        $this->player_skin['player_id'] = $player_ids[$key];
                                        $this->player_skin['battle_id'] = $battle_id;
                                        unset($this->player_skin['type_id']);
                                        unset($this->player_skin['delivery']);
                                        unset($this->player_skin['buyPrice']);
                                        unset($this->player_skin['maxPrice']);
                                        $current[$key] = $this->player_skin;
                                        $price = (float)$this->player_skin['price'];
                                        $player[$Key][$player_ids[$key]]['value'] += $price;
                                        if($this->prizes[$k][$this->str] <= 0){
                                            unset($this->prizes[$k]);
                                        }
                                        Db::table('box_skins')
                                            ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
//                                            ->setDec('stock_vip',1);
                                            ->setDec($this->str,1);
                                        $this->totalStock = array_sum(array_map(function($val){return $val['stock_vip'];}, $this->prizes));
                                        if($this->totalStock <= 0){
                                            $mendStock = new Opp();
                                            $mendStock->mend($boxInfo[$Key]['box_id'],'stock_vip');
                                            $this->getPrizes($boxInfo[$Key]['box_id']);
                                            $this->totalStock = array_sum(array_map(function($val){return $val['stock_vip'];}, $this->prizes));
                                        }
                                    }
                                    $start = $end;
                                }else{
                                    // dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].',价格：'.$this->prizes[$k]['price'].',库存：'.$this->prizes[$k]['stock_vip'].'】,不在中将范围');
                                    // return result(0,'','奖品库存不足，开奖失败1');
                                }
                            }

                        }else if($is_group){
                            foreach ($this->prizes as $k=>$v){
                                if($this->prizes[$k]['stock_group'] > 0){
                                    // $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock_group']/$this->totalStock)) * 100;
                                    // $end += (($this->prizes[$k]['re_probability']) * 100);
                                    $end += $this->prizes[$k]['stock_group'];
                                    // dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                                    if(($start < $rand) && ($rand <= $end)){
                                        //中奖($this->prizes[$k])后，把该奖品剔除一个库存，如果库存0，直接剔掉
                                        // dump($rand.',奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
//                                        $this->prizes[$k]['stock_group'] -= 1;
                                        $this->player_skin = $this->prizes[$k];
                                        $this->player_skin['open_number']    = $rand;
                                        $this->player_skin['open_number_md'] = md5($rand);
                                        $this->player_skin['player_id'] = $player_ids[$key];
                                        $this->player_skin['battle_id'] = $battle_id;
                                        unset($this->player_skin['type_id']);
                                        unset($this->player_skin['delivery']);
                                        unset($this->player_skin['buyPrice']);
                                        unset($this->player_skin['maxPrice']);
                                        $current[$key] = $this->player_skin;
                                        $price = (float)$this->player_skin['price'];
                                        $player[$Key][$player_ids[$key]]['value'] += $price;
                                        if($this->prizes[$k]['stock_group'] <= 0){
                                            unset($this->prizes[$k]);
                                        }
//                                        Db::table('box_skins')
//                                            ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
//                                            ->setDec('stock_group',1);
                                    }
                                    $start = $end;
                                }else{
                                    // dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].',价格：'.$this->prizes[$k]['price'].',库存：'.$this->prizes[$k]['stock_vip'].'】,不在中将范围');
                                    // return result(0,'','奖品库存不足，开奖失败2');
                                }
                            }
                        }else{
                            foreach ($this->prizes as $k=>$v){
                                if($this->prizes[$k]['stock'] > 0){
                                    // $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock']/$this->totalStock)) * 100;
                                    // $end += (($this->prizes[$k]['re_probability']) * 100);
                                    $end += $this->prizes[$k]['stock'];
                                    // dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                                    if(($start < $rand) && ($rand <= $end)){
                                        //中奖($this->prizes[$k])后，把该奖品剔除一个库存，如果库存0，直接剔掉
                                        // dump($rand.',奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
                                        $this->prizes[$k]['stock'] -= 1;
                                        $this->player_skin = $this->prizes[$k];
                                        $this->player_skin['open_number']    = $rand;
                                        $this->player_skin['open_number_md'] = md5($rand);
                                        $this->player_skin['player_id'] = $player_ids[$key];
                                        $this->player_skin['battle_id'] = $battle_id;
                                        unset($this->player_skin['type_id']);
                                        unset($this->player_skin['delivery']);
                                        unset($this->player_skin['buyPrice']);
                                        unset($this->player_skin['maxPrice']);
                                        $current[$key] = $this->player_skin;
                                        $price = (float)$this->player_skin['price'];
                                        $player[$Key][$player_ids[$key]]['value'] += $price;
                                        if($this->prizes[$k]['stock'] <= 0){
                                            unset($this->prizes[$k]);
                                        }
                                        Db::table('box_skins')
                                            ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
                                            ->setDec('stock',1);
                                        $this->totalStock = array_sum(array_map(function($val){return $val['stock'];}, $this->prizes));
                                        if($this->totalStock <= 0){
                                            $mendStock = new Opp();
                                            $mendStock->mend($boxInfo[$Key]['box_id'],'stock');
                                            $this->getPrizes($boxInfo[$Key]['box_id']);
                                            $this->totalStock = array_sum(array_map(function($val){return $val['stock'];}, $this->prizes));
                                        }
                                    }
                                    $start = $end;
                                }else{
                                    // return result(0,'','奖品库存不足，开奖失败3');
                                }
                            }
                        }
                    }else{
                        Db::rollback();
                        return result(0,'','奖品库存不足，开奖失败');
                    }
                }
                $rounds[$Key] = $current;
            }

            $total_value_list = [];
            $total_values = [];//每个玩家获得饰品总价值，数组
            foreach ($player_ids as $key=>$val){
                $total_value_list[$key]['player_id'] = $player_ids[$key];
                $total_value_list[$key]['total_value'] = 0;
                foreach ($player as $k=>$v){
                    $total_value_list[$key]['total_value'] += $player[$k][$player_ids[$key]]['value'];
                }
                array_push($total_values,$total_value_list[$key]['total_value']);
            }

            //开出饰品价值总和
            $total_value = array_sum($total_values);
            //计算所有玩家饰品总价值在数组中出现的次数，如果$numsArr长度为1，代表平局，大于1则分2种情况；
            //1）较高者平，需要瓜分，2）：较低者平，则较高者得

            $numsArr = [];
            foreach ($total_values as $str) {
                @$numsArr["$str"] = $numsArr["$str"] + 1;
            }

            $max = max($total_values);
            $distribute = '';//分发模式，'gua/win'

            $num = 0;
            if(count($numsArr) < 2){
                $draw = true;//平局
            }else{
                //最大价值$max在$numsArr中出现的次数，如果为1次，代表情况1）较高者平，需要瓜分
                $draw = false;//非平局
                $num = $numsArr["$max"];
                if($num>1){
//                    dump('1）较高者平，需要瓜分');
                    $distribute = 'gua';
                }else{
//                    dump('2）：较低者平，较高者得');
                    $distribute = 'win';
                }
            }
            $amount = 0;
            $winner_id = '';
            $battleRule = new BattleRule();
            if($draw && ($distribute == '')){
                //平局
                //把每轮开出来的皮肤的各自玩家背包
                foreach ($rounds as $k=>$v){
                    $skins = $rounds[$k];
                    foreach ($skins as $key=>$val){
                        $player_skin = $skins[$key];
                        unset($player_skin['id']);
                        unset($player_skin['stock']);
                        unset($player_skin['stock_group']);
                        unset($player_skin['stock_vip']);
                        unset($player_skin['open_number']);
                        unset($player_skin['open_number_md']);
                        unset($player_skin['re_probability']);
//                        unset($player_skin['box_id']);
                        $player_skin['way'] = 2;//获得途径，对战
                        $player_skin['create_time'] = currentTime();
                        Db::table('player_skins')->insert($player_skin);
                        $battleRule->editAssets($player_skin['player_id'],'','',$player_skin['price'],'');
                    }
                }
//                $player_info = $battle_info['player_info'];
                if($player_info){
                    foreach ($player_info as $k=>$v){
                        $player_info[$k]['skin_list'] = [];
                        foreach ($rounds as $key => $val){
                            foreach ($rounds[$key] as $item=>$value){
                                //需要重新更新数据库player_info字段
                                if($player_info[$k]['id'] == $rounds[$key][$item]['player_id']){
                                    $p_s['id'] = $rounds[$key][$item]['id'];
                                    $p_s['name'] = $rounds[$key][$item]['name'];
                                    $p_s['img'] = $rounds[$key][$item]['img'];
                                    $p_s['price'] = $rounds[$key][$item]['price'];
                                    $player_info[$k]['skin_list'][] = $p_s;
                                }
                            }
                        }
                    }
                }
                $update['player_info'] = serialize($player_info);
                //本场对战信息存对战表
                $update['result_info'] = json_encode($rounds);
                $update['update_time'] = currentTime();
                $update['winner'] = $winner_id;
                $update['total_value'] = $total_value;
                Db::table('battle')->where('id', $battle_id)->update($update);
            }else if(!$draw && $distribute == 'win'){
//                $player_info = $battle_info['player_info'];
                //唯一获胜者，非平局，不用瓜分
                $winner_id = $this->searchmax($total_value_list)['player_id'];//赢家
                //把每轮开出来的皮肤的player_id都改为赢家的id，并存入赢家背包
                foreach ($rounds as $k=>$v){
                    $skins = $rounds[$k];
                    foreach ($skins as $key=>$val){
                        $player_skin = $skins[$key];
                        $player_skin['player_id'] = $winner_id;
                        unset($player_skin['id']);
                        unset($player_skin['stock']);
                        unset($player_skin['stock_group']);
                        unset($player_skin['stock_vip']);
                        unset($player_skin['open_number']);
                        unset($player_skin['open_number_md']);
                        unset($player_skin['re_probability']);
//                        unset($player_skin['box_id']);
                        $player_skin['way'] = 2;//获得途径，对战
                        $player_skin['create_time'] = currentTime();
                        Db::table('player_skins')->insert($player_skin);
                        $battleRule->editAssets($player_skin['player_id'],'','',$player_skin['price'],'');
                    }
                }
                //给输家账户+1分钱
                foreach($player_ids as $k=>$v){
                    if($winner_id != $player_ids[$k]){
                        $Balance = new Balance();
                        $total_amount = Db::table('player')->where('id',$player_ids[$k])->value('total_amount');
                        $Balance->opBalance($player_ids[$k],0.01,$total_amount,4);
                        $battleRule->editAssets($player_ids[$k],'',0.01,'','');
                    }
                }

                $update['player_info'] = serialize($player_info);
                //本场对战信息存对战表
                $update['result_info'] = json_encode($rounds);
                $update['update_time'] = currentTime();
                $update['winner'] = $winner_id;
                $update['total_value'] = $total_value;
                Db::table('battle')->where('id', $battle_id)->update($update);
            }else if(!$draw && $distribute == 'gua'){
//                $player_info = $battle_info['player_info'];
                $winner_ids = [];
                foreach ($total_value_list as $k=>$v){
                    if($total_value_list[$k]['total_value'] == $max){
                        $winner_ids[] = $total_value_list[$k]['player_id'];
                    }
                }

                $money_be_distribute = 0;//被瓜分的钱
                foreach ($rounds as $k =>$v){
                    $skins = $rounds[$k];//每轮情况
                    foreach ($skins as $key=>$val){
                        if(in_array($skins[$key]['player_id'],$winner_ids)){
//                            $winner_ids[] = $skins[$key]['player_id'];
                            $player_skin = $skins[$key];
                            $player_skin['player_id'] = $skins[$key]['player_id'];
                            $skin_id = $player_skin['id'];
                            unset($player_skin['id']);
                            unset($player_skin['stock']);
                            unset($player_skin['stock_group']);
                            unset($player_skin['stock_vip']);
                            unset($player_skin['open_number']);
                            unset($player_skin['open_number_md']);
                            unset($player_skin['re_probability']);
//                            unset($player_skin['box_id']);
                            $player_skin['way'] = 2;//获得途径，对战
                            $player_skin['create_time'] = currentTime();
                            Db::table('player_skins')->insert($player_skin);
                            $battleRule->editAssets($player_skin['player_id'],'','',$player_skin['price'],'');
                            foreach ($player_info as $item=>$value){
                                if($player_info[$item]['id'] == $skins[$key]['player_id']){
                                    $p_s['id'] = $skin_id;
                                    $p_s['name'] = $player_skin['name'];
                                    $p_s['img'] = $player_skin['img'];
                                    $p_s['price'] = $player_skin['price'];
                                    $player_info[$item]['skin_list'][] = $p_s;
                                }
                            }
                        }else{
                            $money_be_distribute += $skins[$key]['price'];
                        }
                    }
                }
                if($num<=0){
                    return '发生意外';
                }
                $amount = (float)sprintf("%.2f",($money_be_distribute/$num));
                $winner_ids = array_unique($winner_ids);
                $balance = new Balance();
                foreach ($player_ids as $k=>$v){
                    $player_id = $player_ids[$k];
                    $in_array = in_array($player_id,$winner_ids);
                    if($in_array){
                        $total_amount = Db::table('player')
                            ->where(['id'=>$player_id,'flag'=>1])
                            ->value('total_amount');
                        $balance->opBalance($player_id,$amount,$total_amount,5);
                        $battleRule->editAssets($player_id,'',$amount,'','');
                    }else{
                        $total_amount = Db::table('player')
                            ->where(['id'=>$player_id,'flag'=>1])
                            ->value('total_amount');
                        $balance->opBalance($player_id,0.01,$total_amount,4);
                        $battleRule->editAssets($player_id,'',0.01,'','');
                    }
                }


                $update['player_info'] = serialize($player_info);
                //本场对战信息存对战表
                $update['result_info'] = json_encode($rounds);
                $update['update_time'] = currentTime();
                $update['winner'] = implode(',',$winner_ids);
                $update['total_value'] = $total_value;
                Db::table('battle')->where('id', $battle_id)->update($update);
                $winner_id = $update['winner'];
            }
//            dd($rounds);
//            dd(['winner_id'=>$winner_id,'per_amount'=>$amount,'result'=>$rounds]);
            Db::commit();
            $re = ['winner_id'=>$winner_id,'per_amount'=>$amount,'result'=>$rounds];
            return $re;
            // return result(1,$re,'');
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
//            return result(0,'',$e->getMessage());
        }
    }

    //没有库存时，补库存之后再重新获取一下奖品列表
    public function getPrizes($box_id=null){
        $this->prizes = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.probability,bs.type_id,bs.delivery,bs.buyPrice,bs.maxPrice,bs.box_id,bs.type_id,bs.stock,bs.stock_group,bs.stock_vip')
            ->join('box_skins bs','bs.skin_id = as.id')
            ->where(['bs.box_id' => $box_id])
            ->order('bs.stock','asc')
            ->select();
    }

    //点击查看进行中状态的对战房间，返回结果(这里最好用推送保险)
    public function resultInfo(){
        $battle_id = input('post.battle_id');
        $battle_info = Db::table('battle')
            ->where('id', $battle_id)
            ->find();
        $battle_info['result'] = json_decode($battle_info['result_info'],true);
        $battle_info['boxInfo'] = json_decode($battle_info['boxInfo'],true);
//        if($battle_info['boxInfo']){
//            foreach ($battle_info['boxInfo'] as $k=>$v){
//                $battle_info['boxInfo'][$k]['img_main'] = mainName().$battle_info['boxInfo'][$k]['img_main'];
//                $battle_info['boxInfo'][$k]['img_active'] = mainName().$battle_info['boxInfo'][$k]['img_active'];
//            }
//        }
        $battle_info['player_info'] = $battle_info['player_info'] ? unserialize($battle_info['player_info']) :'';
        return result(1,$battle_info,'');
    }

    //前台完成后再请求奖对战状态修改为已开奖
    public function setBattleStatus($battle_id=null){
        $battle_id = input('post.battle_id') ? input('post.battle_id') : $battle_id;
        Db::table('battle')->where('id',$battle_id)->setField('status',3);
        //-----推送一条数据
        $battleInfo = Db::table('battle')->where('id',$battle_id)->find();
        $battleInfo['boxInfo'] = json_decode($battleInfo['boxInfo'],true);
        $push = new Push();
        $battleInfo['status'] = 3;
        $push->push($battleInfo,'update');
        //-------------------
        Db::table('battle_player')->where('battle_id',$battle_id)->setField('status',1);
        return result(1,'','本场开奖已完成');
    }


    public function searchmax($arr)
    {
        $quitDates  = array_column($arr, 'total_value');
        $maxItemKey = array_search(max($quitDates), $quitDates);
        return $arr[$maxItemKey];
    }


    public function getResult($box_id=null,$player_id=null,$box_name=null){
        $box_id    = input('post.box_id',$box_id);
        $player_id = input('post.player_id',$player_id);
        $box_name  = input('post.box_name',$box_name);
        if(!$box_id || !$box_name){
            return result(0, '', '盲盒信息错误');
        }
        if(!$player_id){
            return result(0, '', '玩家信息错误');
        }
        $this->totalStock = array_sum(array_map(function($val){return $val['stock'];}, $this->prizes));//总库存
        //随机数
        $rand = rand(1,10000);
        if($this->prizes){
            $start = 0;
            $end   = 0;
            foreach ($this->prizes as $k=>$v){
//                dump($this->prizes[$k]['stock']);
                if($this->prizes[$k]['stock']>0){
                    $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock']/$this->totalStock)) * 100;
                    $end += (($this->prizes[$k]['re_probability']) * 100);
//                    dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                    if(count($this->prizes) == ($k+1)){
                        //最后一次循环可能存在所有几率累计相加低于10000，误差可能在1-2之间，直接定死
                        $end = 10000;
                    }
                    if(($start < $rand) && ($rand <= $end)){
                        //随机值在范围内表示中奖该皮肤
//                        dump('开奖号码：'.$rand.',中奖奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
                        $this->prizes[$k]['open_number']    = $rand;
                        $this->prizes[$k]['open_number_md'] = md5($rand);
                        //奖品存入背包，扣一个库存,如果扣减后库存为0，则移除
                        $this->prizes[$k]['stock'] = $this->prizes[$k]['stock'] - 1;
//                        dump($this->prizes[$k]);
                        $this->player_skin = $this->prizes[$k];
                        if($this->prizes[$k]['stock'] == 0){
                            unset($this->prizes[$k]);
//                            $this->prizes = array_values($this->prizes);
                        }
                    }
                    $start = $end + 1;
                }
            }
        }else{
            $this->player_skin = [];
        }
    }


    //更新对战房间列表
    public function updateRoom($battle_id=null){
        $battle_id = input('post.battle_id') ? input('post.battle_id') :$battle_id;
        $info = Db::table('battle')->where(['id'=>$battle_id])->find();
        $info['boxInfo']     = json_decode($info['boxInfo'],true);
        if($info['boxInfo']){
            foreach ($info['boxInfo'] as $k=>$v){
                $info['boxInfo'][$k]['img_main'] = $info['boxInfo'][$k]['img_main'] ? mainName().$info['boxInfo'][$k]['img_main'] : '';
                $info['boxInfo'][$k]['img_active'] = $info['boxInfo'][$k]['img_active'] ? mainName().$info['boxInfo'][$k]['img_active'] : '';
            }
        }
        $info['player_info'] = $info['player_info'] ? unserialize($info['player_info']) :'';
        if($info['player_info']){
            foreach ($info['player_info'] as $k=>$v){
                $info['player_info'][$k]['img'] = $info['player_info'][$k]['img'] ? mainName().$info['player_info'][$k]['img'] : '';
            }
        }
        return json(['info'=>$info]);
    }



    //个人对战记录
    public function battleHistory(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $player_id = input('post.player_id');
        $list = Db::table('battle')
            ->alias('b')
            ->join('battle_player bp','bp.battle_id = b.id','left')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('b.id','desc')
            ->where('bp.player_id',$player_id)
            ->select();
        $total = Db::table('battle')
            ->alias('b')
            ->join('battle_player bp','bp.battle_id = b.id','left')
            ->where('bp.player_id',$player_id)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['player_info'] = $list[$k]['player_info'] ? unserialize($list[$k]['player_info']) : '';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //排行
    public function ranking(){
        $start = date("Y-m-d",strtotime("-1 day")).' 00:00:00';
        $end = date("Y-m-d",strtotime("-1 day")).' 23:59:59';
        $range = $start . ',' . $end;
        $where[] = ['b.create_time', 'between', $range];

        $ranking = Db::table('player')
            ->alias('p')
            ->join('balance b','b.player_id = p.id')
            ->field(['p.id,p.name,p.img,sum(b.amount) as total_consume'])
            ->group('p.id')
            ->where($where)
            ->where(['b.type'=>-2])
            ->order('total_consume','asc')
            ->limit(0,8)
            ->select();


        if($ranking){
            foreach ($ranking as $k=>$v){
                $ranking[$k]['total_consume'] = abs($ranking[$k]['total_consume']);
                $ranking[$k]['img'] = $ranking[$k]['img'] ? mainName().$ranking[$k]['img'] :'';
            }
            //昨日之星
            $star = $ranking[0];
            $star['total_consume'] =  $star['total_consume'];
            $star['img'] = $star['img'] ? mainName().$star['img']: '';
        }else{
            $star = '';
        }
        $res = ['star'=>$star,'rank'=>$ranking];
        return result(1,$res,'');
    }


}