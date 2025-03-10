<?php


namespace app\index\controller;

use phpDocumentor\Reflection\Types\False_;
use think\cache\driver\Redis;
use think\Db;
use think\facade\Cache;

class BattleRule
{
    public $redis = null;
    public function __construct()
    {
        $this->redis = new Redis();
    }
    //区间范围开奖
    public function startBattle($battle_id=null){
        $balance = new Balance();
        $battle_id = input('post.battle_id') ? input('post.battle_id') : $battle_id;
        $battle_info = Db::table('battle')
            ->where('id', $battle_id)
            ->find();
        $player_info = unserialize($battle_info['player_info']);
        $battleRule = new BattleRule();
        //用户缓存信息集合,存入上限额度与当前总资产（余额+饰品+取回价值）的差额
        $assetsArr = [];
        $playerArr = $player_info;
        $count_above_upper_limit = 0;
        $count_in_range = 0;
        $count_below_lower_limit = 0;
        $count_infinite = 0;
        $priceArr = [];
        $percent = false;
        foreach ($playerArr as $k=>$v){
            $query = $battleRule->query($playerArr[$k]['id']);
            $type = $playerArr[$k]['group']> 0 ? 'group':($playerArr[$k]['group_vip']>0 ? 'vip' : 'ordinary');
            $whereDataSet = ['type' =>$type];
            $dataSet = Db::table('data_set')->where($whereDataSet)->where('flag',1)->find();

            if($type == 'ordinary'){
                $dataSet['min_percent'] = $dataSet['min_percent']>0 ? $dataSet['min_percent'] :20;
                $dataSet['max_percent'] = $dataSet['max_percent']>0 ? $dataSet['max_percent'] :80;
                $dataSet['limit_times'] = $dataSet['limit_times']>0 ? $dataSet['limit_times'] :500;
            }
            if($type == 'group'){
                $dataSet['min_percent'] = $dataSet['min_percent']>0 ? $dataSet['min_percent'] :20;
//                $dataSet['max_percent'] = $dataSet['max_percent']>0 ? $dataSet['max_percent'] :90;
                $dataSet['limit_times'] = $dataSet['limit_times']>0 ? $dataSet['limit_times'] :500;
            }
            if($type == 'group_vip'){
                $dataSet['min_percent'] = $dataSet['min_percent']>0 ? $dataSet['min_percent'] :20;
                $dataSet['max_percent'] = $dataSet['max_percent']>0 ? $dataSet['max_percent'] :90;
                $dataSet['limit_times'] = $dataSet['limit_times']>0 ? $dataSet['limit_times'] :500;
            }
            $dataSet['bili1'] = $dataSet['bili1']>0 ? $dataSet['bili1'] : 20;//默认20几率随机盒子价格百分比以下饰品
            $dataSet['bili2'] = $dataSet['bili2']>0 ? $dataSet['bili2'] : 30;//默认20几率随机盒子价格百分比以下饰品
            $dataSet['bili3'] = $dataSet['bili3']>0 ? $dataSet['bili3'] : 40;//默认20几率随机盒子价格百分比以下饰品
            $dataSet['percent1'] = $dataSet['percent1']>0 ? $dataSet['percent1'] : 200;//机盒子价格百分比比例
            $dataSet['percent2'] = $dataSet['percent2']>0 ? $dataSet['percent2'] : 300;//机盒子价格百分比比例
            $dataSet['percent3'] = $dataSet['percent3']>0 ? $dataSet['percent3'] : 400;//机盒子价格百分比比例

            //当前实际资产
            $currentAssets = $query['total_amount'] + $query['total_skin_value'];
            //区间上限资产
            $max_assets = $query['total_recharge'] * $dataSet['max_percent']/100;
            //区间下限资产
            $min_assets = $query['total_recharge'] * $dataSet['min_percent']/100;

            if($dataSet['max_percent']<=0){
                //$dataSet['max_percent']小于等于0，表示为可以无上限，一般均为概率组
                //如果$count0等于玩家人数，则所有玩家无上限,随便怎么玩
                $count_infinite++;
            }else{
                if($query['total_time'] > $dataSet['limit_times']){
                    //只要如果其中有一人次数上限了，则都按一定小的几率取随机盒子价格*百分数以下的饰品
                    $percent = true;
                }
                if($currentAssets >= $max_assets){
                    //只要如果有玩家现资产>=区间上限,则都按一定小的几率取随机盒子价格*百分数以下的饰品
                    $percent = true;
                    $count_above_upper_limit++;
                }else if(($currentAssets >= $min_assets) && ($currentAssets < $max_assets)){
                    //如果有玩家资产都在 区间内
                    if($max_assets>0){
                        //如果区间上限资产-当前资产的差额大于0，存差额，用于所有人都是当前情况下，随机饰品价值不超过其中最小的差额
                        $price = $max_assets - $currentAssets;
                        $priceArr[] = $price;
                    }
                    $count_in_range++;
                }else{
                    //如果有玩家资产都在 区间下限以下
                    if($max_assets>0){
                        //如果区间上限资产-当前资产的差额大于0，存差额，用于所有人都是当前情况下，随机饰品价值不超过其中最小的差额
                        $price = $max_assets - $currentAssets;
                        $priceArr[] = $price;
                    }
                    $count_below_lower_limit++;
                }
            }
            $query['dataSet'] = $dataSet;
            $query = array_merge($playerArr[$k],$query);
            $assetsArr[] = $query;
        }
        $minPrice = 0;
        $infinite = false;
        $stage = 0;//标记所有玩家统一阶段，例如：1是资产100%-80%，2是80%-20%，3是20%以下，仅用于次数未到上限
        if($count_infinite == count($playerArr)){
            //全员 无上限
            $infinite = true;
        }else if ($count_above_upper_limit == count($playerArr)){
            //全员区间上
            $stage = 1;
        }else if($count_in_range == count($playerArr)){
            //全员区间内
            //所有玩家资产都在 区间上限以下，则按$max_assets-$currentAssets最小差额值来用户筛选饰品，并加上几率控制
            //最小差额可能会存在低于箱子价格，低了则定义为箱子价格*百分比
            $minPrice = min($priceArr);
            $stage = 2;
        }else if($count_below_lower_limit == count($playerArr)){
            //全员 区间下限以下，
            $minPrice = min($priceArr);
            $stage = 3;
        }else{
            //既存在 无上限，也存在资产在 区间上限以下，则按$max_assets-$currentAssets最小差额值来用户筛选饰品，并加上几率控制
            //最小差额可能会存在低于箱子价格，低了则定义为箱子价格*百分比
            $minPrice = min($priceArr);
        }
        //对战参数
        $params = [
            'infinite' => $infinite,//最大权重，全员无限,其次为$percent
            'percent'  => $percent,//如果为true，其他条件全部忽略
            'minPrice' => $minPrice,//$minPrice和$infinite条件仅存在一个，用$infinite为TRUE来判断
            'stage'    => $stage,
            'count'    => count($playerArr),//人数
        ];
        $battle_info['player_info'] = $player_info;
        if (!$battle_info) {
            echo '游戏房间信息不存在';
            return false;
        }
        if (($battle_info['status'] == 3) || $battle_info['update_time']) {
            echo '对战已结束';
            return false;
        }
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
        //分轮开奖
        $rounds = [];//本次对战总轮次
        $round = [];//每轮信息
        $playerPrize = [];//每个玩家本次对战抽到的所有奖品
        $prizes = [];//所有奖品，用户平局时，或win状态下，循环插入数据
        $box = new Box();
        foreach ($boxInfo as $Key=>$value){
            $box_id = $boxInfo[$Key]['box_id'];
            $boxPrice = $boxInfo[$Key]['price'];
            $params['rand']   = rand(0,10000);
            $params['boxNum'] = count($boxInfo);
            foreach ($assetsArr as $k=>$v){
                $player_id = $assetsArr[$k]['id'];
                $playerInfo = $assetsArr[$k];
                $re = $box->getSkin1($box_id,$player_id,'',$boxPrice,1,$playerInfo,'battle',$params);
                if(!$re){
                    return result(0,'','失败');
                }
                $re['battle_id'] = $battle_id;
                $round[$k] = $re;//每轮每个玩家奖品
                if($player_id == $re['player_id']){
                    $playerPrize[$player_id][] = $re;
                }
                $prizes[] = $re;
            }
            $rounds[$Key] = $round;
        }
        $total_value = 0;//每个玩家获奖总价值
        $total_values = [];//所有玩家获奖总价值的数组
        $all_skin_value = 0;//所有奖品价值
        foreach ($playerPrize as $k=>$v){
            $total_value = array_sum(array_map(function ($v){return $v['price'];},$playerPrize[$k]));
            $total_values[] = $total_value;
            $all_skin_value += $total_value;
        }
        //计算所有玩家饰品总价值在数组中出现的次数，如果$numsArr长度为1，代表平局，大于1则分2种情况；
        //1）较高者平，需要瓜分，2）：较低者平，则较高者得
        $numsArr = [];
        foreach ($total_values as $str) {
            @$numsArr["$str"] = $numsArr["$str"] + 1;
        }
        $max = max($total_values);
        $num = 0;
        if(count($numsArr) < 2){
            //平局
            $draw = true;
            $distribute = 'ping';
        }else{
            //最大价值$max在$numsArr中出现的次数，如果为1次，代表情况1）较高者平，需要瓜分
            $draw = false;//非平局
            $num = $numsArr["$max"];
            if($num>1){
                //dump('1）较高者平，需要瓜分');
                $distribute = 'gua';
            }else{
                //dump('2）：较低者平，较高者得');
                $distribute = 'win';
            }
        }
        //获胜者奖品列表
        if($distribute == 'ping'){
            foreach ($prizes as $k=>$v){
                foreach ($player_info as $key=>$val){
                    if($prizes[$k]['player_id'] == $player_info[$key]['id']){
                        $prize['id'] = $prizes[$k]['id'];
                        $prize['name'] = $prizes[$k]['name'];
                        $prize['img'] = $prizes[$k]['img'];
                        $prize['price'] = $prizes[$k]['price'];
                        $player_info[$key]['skin_list'][] = $prize;
                    }
                }
                unset($prizes[$k]['id']);
                Db::table('player_skins')->insertGetId($prizes[$k]);
                $this->editAssets($prizes[$k]['player_id'],'','',$prizes[$k]['price'],'');
            }
        }
        $winner_id = '';
        if($distribute == 'win'){
            $max = max($total_values);
            foreach ($playerPrize as $k=>$v){
                $count = array_sum(array_map(function ($v){return $v['price'];},$playerPrize[$k]));
                if($max == $count){
                    $winner_id = $k;
                }else{
                    $loser_id = $k;
                    $this->editAssets($loser_id,'',0.01,'','');
                    $total_amount = Db::table('player')
                        ->where(['id'=>$loser_id,'flag'=>1])
                        ->value('total_amount');
                    $balance->opBalance($loser_id,0.01,$total_amount,4);
                }
            }
            foreach ($prizes as $k=>$v){
                unset($prizes[$k]['id']);
                $prizes[$k]['player_id'] = $winner_id;
                Db::table('player_skins')->insertGetId($prizes[$k]);
            }
            $this->editAssets($winner_id,'','',$all_skin_value,'');
        }
        $per_amount = 0;
        $winner_ids = [];
        if($distribute == 'gua'){
            $max = max($total_values);
            $i = 0;
            foreach ($playerPrize as $k=>$v){
                $count = array_sum(array_map(function ($v){return $v['price'];},$playerPrize[$k]));
                if($max == $count){
                    if($i <= 0){
                        $winner_id = $k;
                    }else{
                        $winner_id .= ','.$k;
                    }
                    $winner_ids[] = $k;
                }
                $i++;
            }
            //失败玩家奖品总金额
            $loser_prize_total_value = 0;
            //失败玩家数量
            $loser_num = 0;
            foreach ($prizes as $k=>$v){

                $player_id = $prizes[$k]['player_id'];
                if(in_array($player_id, $winner_ids)){

                    foreach ($player_info as $key=>$val){
                        if($prizes[$k]['player_id'] == $player_info[$key]['id']){
                            $prize['id'] = $prizes[$k]['id'];
                            $prize['name'] = $prizes[$k]['name'];
                            $prize['img'] = $prizes[$k]['img'];
                            $prize['price'] = $prizes[$k]['price'];
                            $player_info[$key]['skin_list'][] = $prize;
                        }
                    }
                    unset($prizes[$k]['id']);

                    $prizes[$k]['player_id'] = $player_id;
                    Db::table('player_skins')->insertGetId($prizes[$k]);

                    $this->editAssets($player_id,'','',$prizes[$k]['price'],'');
                }else{
                    $total_amount = Db::table('player')
                        ->where(['id'=>$player_id,'flag'=>1])
                        ->value('total_amount');
                    $balance->opBalance($player_id,0.01,$total_amount,4);
                    $this->editAssets($player_id,'',0.01,'','');
                    $loser_prize_total_value += array_sum(array_map(function ($v){return $v['price'];},$playerPrize[$player_id]));
                    $loser_num++;
                }
            }
            //每个胜利玩家瓜分到的金额
            $per_amount = number_format(($loser_prize_total_value/count($winner_ids)),'2','.','');
            foreach ($winner_ids  as $k=>$v){
                $this->editAssets($winner_ids[$k],'',$per_amount,'','');
                $total_amount = Db::table('player')
                    ->where(['id'=>$winner_ids[$k],'flag'=>1])
                    ->value('total_amount');
                $balance->opBalance($winner_ids[$k],$per_amount,$total_amount,5);
            }
        }
        $update['player_info'] = serialize($player_info);
        $update['result_info'] = json_encode($rounds);
//        $update['player_info'] = $player_info;
//        $update['result_info'] = $rounds;
        $update['update_time'] = currentTime();
        $update['winner'] = $winner_id;
        $update['total_value'] = $all_skin_value;
        Db::table('battle')->where('id', $battle_id)->update($update);
        $result['winner_id'] = $winner_id;
        $result['per_amount'] = $per_amount;
        $result['result'] = $rounds;
        return $result;
    }
    //1.查询当前（用户资产情况），充值的金额,账户余额，背包当前有效饰品的价值，开箱次数(对战+盲盒+幸运饰品)，
    public function query($player_id=null){
        ini_set("error_reporting","E_ALL & ~E_NOTICE");
        $queryInfo = $this->redis->get('player_assets_'.$player_id);
        $queryInfo = '';
        if(!$queryInfo){
            $queryInfo = Db::table('data')->where('player_id',$player_id)->find();
            if($queryInfo){
                unset($queryInfo['id']);
            }
        }
        if(!$queryInfo){
            $player = Db::table('player')->where('id',$player_id)->field('group,total_amount,type')->find();
            $total_amount = $player['total_amount'];
            $player_type = $player['type'];
            $total_recharge = 0;
            $real_total_recharge = 0;
            if($player_type == 1){
                //正常用户
                //（初始金额）为本轮充值的金额，初始金额 = 本轮充值+余额+背包有效饰品价值，备注：不含之前已取回饰品（易导致充值后，损失比例变多）
                $total_recharge = Db::table('recharge')
                    ->where(['status'=>3,'state'=>0])
                    ->where('player_id',$player_id)
                    ->sum('coin');
                //实际总充值
                $real_total_recharge = Db::table('recharge')
                    ->where(['status'=>3])
                    ->where('player_id',$player_id)
                    ->sum('coin');
                //背包有效饰品价值
                $total_skin_value = Db::table('player_skins')
                    ->where('player_id',$player_id)
                    ->where('status',1)
                    ->sum('price');
                $rechargeInfo = Db::table('recharge')->where(['status'=>3,'state'=>0,'player_id'=>$player_id])->value('before_amount');
                $amount = isset($rechargeInfo['before_amount']) ? $rechargeInfo['before_amount'] : 0;
                $amount = $amount > 0 ? $amount : 0;
                $total_recharge = $total_recharge + $amount + $total_skin_value;
            }else if($player_type == 2){
                //机器人
                $total_recharge = $total_amount;
            }

            //当前背包有效饰品价值
            $total_skin_value = Db::table('player_skins')
                ->where('player_id',$player_id)
                ->where('status',1)
                ->sum('price');




            //全部已取回饰品价格
            $real_total_retrieve_value = Db::table('player_skins')
                ->where('player_id',$player_id)
                ->where('state','success')
                ->sum('price');
            //本轮已经取回+取回中（代发货和待收货）的饰品，（每充值一轮算新的一轮）
            $total_retrieve_value = Db::table('player_skins')
                ->where('player_id',$player_id)
                ->where('state1',1)
                ->where('state','success')
                ->whereOr('status',2)
                ->sum('price');
            //累计开箱的个数
            $total_time = Db::table('battle_player')
                ->where(['player_id'=>$player_id,'state'=>0])
                ->sum('num');
            //上一场亏损，正数为亏，负数为盈
            $loss = Db::table('recharge')
                ->where(['player_id'=>$player_id,'state'=>0])
                ->order('create_time','desc')
                ->value('loss');
            $queryInfo = [
                'player_id'                 => $player_id,
                'real_total_recharge'       => $real_total_recharge >0 ? $real_total_recharge : 0,
                'total_recharge'            => $total_recharge > 0 ? $total_recharge : ($total_amount ? $total_amount : 0),
                'recharge'                  => isset($rechargeInfo['coin']) ? $rechargeInfo['coin'] : 0,//本场的充值金额
                'total_amount'              => $total_amount ? $total_amount : 0,
                'total_skin_value'          => $total_skin_value,
                'total_time'                => $total_time,
                'lower_min_limit'           => 0,//默认0,只要资产低于一次区间下限，则标记为1
                'real_total_retrieve_value' => $real_total_retrieve_value ? $real_total_retrieve_value : 0,
                'total_retrieve_value'      => $total_retrieve_value ? $total_retrieve_value : 0,
                'capping'                   => 0,//默认不需要回升
                'loss'                      => $loss > 0 ? $loss : 0,//亏损金额
            ];

            $data = Db::table('data')->where('player_id',$player_id)->find();
            if(empty($data)){
                Db::table('data')->insert($queryInfo);
            }else{
                Db::table('data')->where('player_id',$player_id)->update($queryInfo);
            }
            $this->redis->set('player_assets_'.$player_id,$queryInfo,3600);
        }
        return $queryInfo;
    }
    //2.（用户资产情况），修改缓存中的充值的金额,账户余额，背包当前有效饰品的价值，开箱次数(对战+盲盒+幸运饰品)
    /**
     * @param null $player_id
     * @param float $recharge
     * @param float $amount
     * @param float $skin_value
     * @param int $time
     * @return bool
     */
    public function editAssets($player_id=null,$recharge=0.00,$amount=0.00,$skin_value=0.00,$time=0,$rechargeOrder=null,$lower_min_limit=null,$retrievePrice=0.00,$capping=null){
        ini_set("error_reporting","E_ALL & ~E_NOTICE");
        $queryInfo = $this->redis->get('player_assets_'.$player_id);

        if(!$queryInfo){
            $queryInfo = $this->query($player_id);
        }

        if($rechargeOrder){
            //充值的时候还需要记录以下，当前背包饰品价值，以及上一轮取回的饰品价值
            $rechargeUpdate['skins_value'] = Db::table('player_skins')
                ->where(['status'=>1,'player_id'=>$rechargeOrder['player_id']])
                ->sum('price');
            $rechargeUpdate['retrieve_value'] = Db::table('player_skins')
                ->where('player_id',$rechargeOrder['player_id'])
                ->where('state1',1)//状态1表示上一次充值到现在期间所产生的饰品
                ->where('status',2)//状态2包含取回中（代发货和待收货）的饰品
                ->sum('price');
            //计算上一场的亏损金额  上一场的初始金 - 余额 - 上一场背包留下饰品金额 - 上一场取回饰品价值
            $loss = $queryInfo['total_recharge'] - $queryInfo['total_amount'] - $rechargeUpdate['skins_value'] - $rechargeUpdate['retrieve_value'];
            $capping = 0;
            if($loss>0){
                //大于0，表示亏损，需要回升设定金额
                $capping = 1;
            }
            $rechargeUpdate['loss'] = $loss;
            Db::table('recharge')->where('id',$rechargeOrder['id'])->update($rechargeUpdate);

            //每次充值，将之前的订单state状态改为1，battle_player的状态也改为1，并记录一次当前账户余额
            Db::table('recharge')
                ->where('id','neq',$rechargeOrder['id'])
                ->where(['player_id'=>$player_id,'state'=>0])
                ->setField('state',1);
            Db::table('recharge')
                ->where(['player_id'=>$player_id,'state'=>0,'id'=>$rechargeOrder['id']])
                ->setField('before_amount',$rechargeOrder['total_amount']);
            Db::table('battle_player')->where(['player_id'=>$player_id])->setField('state',1);
            //每次充值，为新一轮，本次充值时，将现背包中的饰品标记，，新加入背包的作为本轮饰品,仅用于记录本轮取回饰品价值计算，取回一次饰品标记1，表示本轮取回
            Db::table('player_skins')->where(['player_id'=>$player_id,'state1'=>1])->setField('state1',0);
            //实际总充值
            $real_total_recharge = Db::table('recharge')
                ->where(['status'=>3])
                ->where('player_id',$player_id)
                ->sum('coin');
            $queryInfo['total_time'] = 0;
            $this->redis->clear('player_assets_'.$player_id);
            $queryInfo = [
                'player_id'           => $player_id,
                'real_total_recharge' => $real_total_recharge > 0 ? $real_total_recharge : 0,
                'total_recharge'      => $queryInfo['total_skin_value'] + $rechargeOrder['total_amount'] + $recharge,//充值时，余额+本次充值金额作为初始金
                'recharge'            => $rechargeOrder['coin'],
                'total_amount'        => $rechargeOrder['total_amount'] + $recharge,
                'total_skin_value'    => $queryInfo['total_skin_value'],
                'total_time'          => 0,
                'lower_min_limit'     => 0,//每次充值，默认0
                'real_total_retrieve_value' => $queryInfo['real_total_retrieve_value'],//实际取回饰品总价值
                'total_retrieve_value'      => 0,//当前轮取回饰品价值
                'capping'             => $capping,//充值后，是否需要回升指定百分比，0：不需，1：需要，（只要回升到了1次，即标记为0，表示已经回升过，不需要再回升）
                'loss'                => $loss,//亏损金额
            ];
        }else{
            $queryInfo = [
                'player_id'        => $player_id,
                'real_total_recharge' => $queryInfo['real_total_recharge'] > 0 ? $queryInfo['real_total_recharge'] : 0,
                'total_recharge'   => $queryInfo['total_recharge']+$recharge,
                'recharge'         => $queryInfo['recharge'],
                'total_amount'     => $queryInfo['total_amount']+$amount,
                'total_skin_value' => $queryInfo['total_skin_value']+$skin_value,
                'total_time'       => $queryInfo['total_time']+$time,
                'lower_min_limit'  => $queryInfo['lower_min_limit'] ? $queryInfo['lower_min_limit'] : ($lower_min_limit ? $lower_min_limit : $queryInfo['lower_min_limit']),
                'real_total_retrieve_value' => $queryInfo['real_total_retrieve_value'] + $retrievePrice,
                'total_retrieve_value'      => $queryInfo['total_retrieve_value'] + $retrievePrice,
                'capping'          => $capping == 'cancel' ? 0 : $queryInfo['capping'],
                'loss'             => $queryInfo['loss']
            ];
        }
        $data = Db::table('data')->where('player_id',$player_id)->find();
        if(empty($data)){
            Db::table('data')->insert($queryInfo);
        }else{
            Db::table('data')->where('player_id',$player_id)->update($queryInfo);
        }
        $this->redis->set('player_assets_'.$player_id,$queryInfo,3600);
    }

    //清除所有缓存数据
    public function clear($player_id=null){
        $re=[];
        if($player_id>0){
//            $this->redis->clear('player_assets_'.$player_id);//用户资产
//            $this->redis->clear('skinToSteam'.$player_id);//取回
//            $this->redis->clear('exchangeToMoney'.$player_id);//兑换
            //----
            $this->redis->rm('player_assets_'.$player_id);
            $this->redis->rm('skinToSteam'.$player_id);//取回
            $this->redis->rm('exchangeToMoney'.$player_id);//兑换

            $re['player_assets_'] = $this->redis->get('player_assets_'.$player_id);//用户资产
            $re['skinToSteam'] = $this->redis->get('skinToSteam'.$player_id);//取回
            $re['exchangeToMoney'] = $this->redis->get('exchangeToMoney'.$player_id);//兑换
            Db::table('data')->where('player_id',$player_id)->delete();
            $re['query'] = $this->query($player_id);
        }else{
            $this->redis->clear();
        }
        return result(1,$re,'');
    }

}