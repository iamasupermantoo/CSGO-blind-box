<?php


namespace app\index\controller;

use app\push\controller\Push;
use phpDocumentor\Reflection\Types\False_;
use think\cache\driver\Redis;
use think\Db;

class Box
{
    public $redis = null;
    public function __construct()
    {
        $this->redis = new Redis();
    }
    //盒子列表index
    public function boxList()
    {
        $list = Db::table('box_type')
            ->order('order','asc')
            ->where('flag', 1)
            ->select();
        if (count($list) > 0) {
            foreach ($list as $k => $v) {
                $box_list = Db::table('box')
                    ->where(['flag' => 1, 'type' => $list[$k]['id']])
                    ->order('price', 'desc')
                    ->select();
                if (count($box_list) > 0) {
                    foreach ($box_list as $key=>$val){
                        $box_list[$key]['img_main'] = mainName().$box_list[$key]['img_main'];
                        $box_list[$key]['img_active'] = mainName().$box_list[$key]['img_active'];
                    }
                    $list[$k]['box_list'] = $box_list;
                } else {
                    unset($list[$k]);
                }
            }
        }
        //首页数据，判断当前是否有充值或者其他活动
        $active = Db::table('recharge_activity')
//            ->where('start_time','<=',currentTime())
//            ->where('end_time','>=',currentTime())
            ->select();
        if($active){
            foreach ($active as $k=>$v){
                $active[$k]['img'] = unserialize($active[$k]['img']);
            }
        }
        $list = array_values($list);
        $re = ['active'=>$active,'list'=>$list];
        return result(1, $re, '');
    }

    //盲盒详情
    public function boxInfo()
    {
        $box_id = input('post.box_id');
        if (!$box_id) {
            return result(0, '', '盒子信息错误');
        }
        $boxInfo = Db::table('box')
            ->where('id', $box_id)
            ->find();
        if (!$boxInfo) {
            return result(1, $boxInfo, '无数据');
        }
        $boxInfo['img_main'] = mainName().$boxInfo['img_main'];
        $boxInfo['img_active'] = mainName().$boxInfo['img_active'];

        $box_skins = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.probability,bs.type_id,bs.box_id,bs.type_id,bs.stock,bs.stock_group,bst.name as type_name,bst.color,bst.order,bst.img as background')
            ->join('box_skins bs','bs.skin_id = as.id')
            ->join('box_skins_type bst','bst.id = bs.type_id','left')
//            ->where('bs.stock','>',0)
//            ->where('bs.stock_group','>',0)
            ->where('bs.box_id',$box_id)
            ->order('bst.order','asc')
            ->order('as.price','desc')
            ->select();

        if ($box_skins) {
            //每个类型皮肤的总概率
            $skins_types = Db::table('box_skins_type')
                ->alias('bst')
                ->field('bst.*,bsp.probability as skins_type_probability')
                ->join('box_skins_probability bsp','bsp.box_skins_type_id = bst.id')
                ->where('box_id',$box_id)
                ->order('bst.order','asc')
                ->select();
            $boxInfo['skins_types'] = $skins_types;
        }
        $boxInfo['box_skins'] = $box_skins;
        return result(1, $boxInfo, '');
    }

    //购买盒子
    public function buyBox($player_id = null, $box_id = null, $num = null, $type = null, $battle_id = null,$consumeType=null,$task=null)
    {

        $battleRule = new BattleRule();
        $battleRule->query($player_id);
        $box_id = input('post.box_id') ? input('post.box_id') : $box_id;
        $num = input('post.num') ? input('post.num') : $num;//数量
        $player_id = input('post.player_id') ? input('post.player_id') : $player_id;
        $consumeType = $consumeType ? $consumeType : -1; //消费类型,-1:购买盲盒，-2：对战，-3：幸运饰品，-4：商城购买饰品
        $type = $type ? $type : 1;
        $box = '';
        $cartState = input('post.cartState');
        if(input('post.box_id')){
            $box = 'box';
        }
        if($task == 'task'){
            $box = 'box';
        }
        if (!trim($box_id) || (trim($num)<1)) {
            return result(0, '', '盲盒信息错误');
        }
        if (!trim($player_id)) {
            return result(0, '', '玩家信息错误');
        }
        $openModel = Db::table('open_model')->find();
        if($openModel){
            $model = $openModel['model'];
        }else{
            $model = 'stock';
        }



        // 启动事务，MyISAM 不支持事务处理，需要 InnoDB 引擎
        Db::startTrans();
        try {
            $boxInfo = Db::table('box')
                ->where('id', $box_id)
                ->find();
            if (!$boxInfo) {
                return result(0, '', '盲盒信息不存在');
            }
            $price = $boxInfo['price'];
            $total = number_format(($price * $num),'2','.','');
            $playerInfo = Db::table('player')
                ->where('id', $player_id)
                ->find();
            if (!$playerInfo) {
                return result(0, '', '玩家信息不存在');
            }
            if ($playerInfo['total_amount'] < $total) {
                return result(0, '', '当前余额不足');
            }
//            $stock = Db::table('box_skins')
//                ->where('box_id',$box_id)
//                ->field(['sum(stock) as stock,sum(stock_vip) as stock_vip,sum(set_stock) as set_stock,sum(set_stock_vip) as set_stock_vip'])
//                ->select();
            if($box){
                //购买盲盒
                //判断是否增加库存（总库存低于设计库存百分比时补）
//                $mend = Db::table('mend')->where('box_id',$box_id)->find();
//                if(($mend['billie']>0) || ($mend['vip_billie']>0)){
//                    //设置补货比例情况下才补货，其余情况不补
//                    if($stock[0]['set_stock']>0){
//                        $stockBillie = number_format($stock[0]['stock']/$stock[0]['set_stock'],'2','.','')*100;
//                        if(($stockBillie <= $mend['billie'])){
//                            $mendStock = new Opp();
//                            $mendStock->mend($box_id,'stock');
//                        }
//                    }
//                    if($stock[0]['set_stock_vip']>0){
//                        $stockVipBillie = number_format($stock[0]['stock_vip']/$stock[0]['set_stock_vip'],'2','.','')*100;
//                        if(($stockVipBillie <= $mend['vip_billie'])){
//                            $mendStock = new Opp();
//                            $mendStock->mend($box_id,'stock_vip');
//                        }
//                    }
//                }
            }else{
                //对战
                //判断是否增加库存（总库存低于设计库存百分比时补）
//                $mend = Db::table('mend')->where('box_id',$box_id)->find();
//                if(($mend['billie']>0) || ($mend['vip_billie']>0)){
//                    if($stock[0]['set_stock']>0){
//                        $stockBillie = number_format($stock[0]['stock']/$stock[0]['set_stock'],'2','.','')*100;
//                        if(($stockBillie <= $mend['billie'])){
//                            $mendStock = new Opp();
//                            $mendStock->mend($box_id,'stock');
//                        }
//                    }
//                    if($stock[0]['set_stock_vip']>0){
//                        $stockVipBillie = number_format($stock[0]['stock_vip']/$stock[0]['set_stock_vip'],'2','.','')*100;
//                        if(($stockVipBillie <= $mend['vip_billie'])){
//                            $mendStock = new Opp();
//                            $mendStock->mend($box_id,'stock_vip');
//                        }
//                    }
//                }
            }

            $buyBoxInfo = [
                'player_id' => $player_id,
                'box_id' => $boxInfo['id'],
                'box_name' => $boxInfo['name'],
                'num' => $num,
                'type' => $type,//类型，1：开箱，2：对战
                'create_time' => currentTime(),
                'order_num' => makeOder(),
                'battle_id' => $battle_id ? $battle_id : ''
            ];
            $buyBoxInfo['player_box_skin_id'] = Db::table('player_box_skin')->insertGetId($buyBoxInfo);


            $balance = new Balance();
            $total_amount = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->value('total_amount');
            $re = $balance->opBalance($player_id,$total,$total_amount,$consumeType);
            if(!$re){
                return result(0, '', '');
            }
            $buyBoxInfo['total_amount'] = number_format(($total_amount - $total),2,'.','');
            $buyBoxInfo['total_price'] = $total;



            if($box == 'box'){



                if($model == 'stock'){
                    $res = $this->getSkin($box_id,$player_id,$buyBoxInfo['player_box_skin_id']);
                }else if ($model == 'range'){


                    $res = $this->getSkin1($box_id,$player_id,$buyBoxInfo['player_box_skin_id'],$boxInfo['price'],$num,$playerInfo,'open','');
                }
                if($res == false){
                    return result(0, '', '');
                }


                $res['total_amount']  = $buyBoxInfo['total_amount'];
                $history['box_id']    = $box_id;
                $history['player_id'] = $player_id;
                $history['type']      = 2;
                $history['num']       = $num;
                $history['status']    = 1;
                Db::table('battle_player')->insert($history);
                Db::commit();
                $battleRule = new BattleRule();
                $skin_value = array_sum(array_map(function ($val){return $val['price'];},$res['skins_info']));
                $battleRule->editAssets($player_id,'',-$total,$skin_value,$num);
                //-----推送一条数据
                $push = new Push();
                $skins_info = $res['skins_info'];
                if($skins_info){
                    foreach ($skins_info as $k=>$v){
                        $skins_info[$k]['box_name']    = $boxInfo['name'];
                        $skins_info[$k]['imageUrl']    = $skins_info[$k]['img'];
                        $skins_info[$k]['create_time'] = currentTime();
                        $skins_info[$k]['img']         = $skins_info[$k]['background'];
                        $skins_info[$k]['player_img']  = $playerInfo['img'];
                        $skins_info[$k]['player_name'] = $playerInfo['name'];
                        $skins_info[$k]['skin_name']   = $skins_info[$k]['name'];
                        unset($skins_info[$k]['id']);
                        unset($skins_info[$k]['appId']);
                        unset($skins_info[$k]['itemId']);
                        unset($skins_info[$k]['name']);
                        unset($skins_info[$k]['price']);
                        unset($skins_info[$k]['probability']);
                        unset($skins_info[$k]['type_id']);
                        unset($skins_info[$k]['stock']);
                        unset($skins_info[$k]['stock_group']);
                        unset($skins_info[$k]['stock_vip']);
                        unset($skins_info[$k]['background']);
                        unset($skins_info[$k]['re_probability']);
                        unset($skins_info[$k]['open_number']);
                        unset($skins_info[$k]['open_number_md']);
                        unset($skins_info[$k]['order_num']);
                    }
                }
                $pushInfo['skins_info'] = $skins_info;
                $pushInfo['cartState']  = $cartState ? $cartState : 'true';
                $push->push1($pushInfo,'openBox');
                //-------------------
                return result(1,$res, '');
            }
            Db::commit();
            return result(1, $buyBoxInfo, '');
        } catch (\Exception $e) {
            Db::rollback();
//            return $e->getMessage();
            return result(0, '', $e->getMessage());
        }
    }

    public $prizes_u = array();
    public $prizes_l = array();
    //盒子抽皮肤，区间范围类型
    public function getSkin1($box_id=null,$player_id=null,$player_box_skin_id=null,$boxPrice=null,$num=null,$playerInfo=null,$typeStr=null,$params=null)
    {
        //记录一个盒子真实价格
        $realBoxPrice = $boxPrice;
        $box_id = input('post.box_id') ? input('post.box_id') : $box_id;
        $player_box_skin_id = input('post.player_box_skin_id') ? input('post.player_box_skin_id') : $player_box_skin_id;
        if($typeStr == 'open'){
            if (empty(trim($box_id)) || empty(trim($player_box_skin_id))) {
                echo '盲盒信息错误';
                return false;
            }
        }else{
            if (empty(trim($box_id))) {
                echo '盲盒信息错误';
                return false;
            }
        }
        if (empty(trim($player_id))) {
            echo '玩家信息错误';
            return false;
        }
        $in_range = false;
        $lower_min_limit = false;
        $cap = false;
        $add_percent = 0;//充值后，回升的百分比
        $battleRule = new BattleRule();
        if($typeStr == 'open'){
            $rand = rand(0,10000);
            $queryInfo = $battleRule->query($player_id);
            $type = $playerInfo['group'] > 0 ? 'group':($playerInfo['group_vip']>0 ? 'vip' : 'ordinary');
            $whereDataSet = ['type' =>$type];
            $dataSet = Db::table('data_set_open')
                ->where($whereDataSet)
                ->where('flag',1)
                ->find();

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
            $dataSet['up_percent'] = $dataSet['up_percent']>0 ? $dataSet['up_percent'] : 50;//3段情况下，回升至最大资产比

            $dataSet['recharge_up_percent'] = $dataSet['recharge_up_percent']>0 ? $dataSet['recharge_up_percent'] : 30;//充值回升金额比
            $dataSet['bili4'] = $dataSet['bili4']>0 ? $dataSet['bili4'] : 30;//充值回升，随机大额饰品的概率
            $dataSet['percent4'] = $dataSet['percent4']>0 ? $dataSet['percent4'] : 50;//充值回升，bili4几率外的机盒子价格百分比比例


            $limit_times = $dataSet['limit_times'];
            //用户实际总资产：余额+有效饰品价格+已取回饰品价格
            $totalAssets = $queryInfo['total_amount'] + $queryInfo['total_skin_value'];
            $total_recharge = $queryInfo['total_recharge'];
            $percent = ($totalAssets/$total_recharge)*100;
            if($dataSet['max_percent'] > 0){
                $rr = rand(0,20);
                if($rr%3 != 0){
                    //偶尔性的根据开箱次数修改区间上限，增加上下浮动性
                    $dataSet['max_percent'] = $dataSet['max_percent'] - (($queryInfo['total_time']/10)*0.5);
                    $dataSet['max_percent'] = $dataSet['max_percent'] >= 1 ? $dataSet['max_percent'] : 1;
                }
            }
            $successiveLostTime = $this->redis->get('successiveLostTime'.$player_id.'_'.$box_id);
            if($successiveLostTime){
                //如果存在连续输的次数，则本轮饰品价格 = 盒子价格 * （该阶段比列值+（自定义比例*连续输的次数））
                $dataSet['percent1'] = $dataSet['percent1'] + (20*$successiveLostTime);
//                $dataSet['percent2'] = $dataSet['percent2'] + (15*$successiveLostTime);
//                $dataSet['percent3'] = $dataSet['percent3'] + (10*$successiveLostTime);
            }
            //如果为充值后需要回升,且上场亏损（正数为亏，负数为盈）的情况
            $loss = $queryInfo['loss'];
            //次产有上限区间
            if($dataSet['max_percent']>0){
                if($queryInfo['capping'] && ($loss > 0)){
                    $cap = true;
                    //回升的百分比
                    $copping_percent = $dataSet['recharge_up_percent'];
                    //本场充值金额,充值金额和上场亏损，取最小值来计算本场的回升金额
                    $recharge = $queryInfo['recharge'];
                    $min = min([$loss,$recharge]);
                    //本场要回升的金额
                    $capping_amount = keep_decimal(($min*$copping_percent)/100);
                    //本场回升的百分比
                    $add_percent = keep_decimal(($capping_amount/$total_recharge)*100);
                    //总资产还在上限之上，或者次数达到上限
                    if($rand <= $dataSet['bili4']*100){
                        $boxPrice = $boxPrice * 10;
                    }else{
                        $boxPrice = $boxPrice * $dataSet['percent4']/100;
                    }
                }else{
                    //总资产还在上限之上，或者次数达到上限
                    if(($percent>=$dataSet['max_percent']) || ($limit_times < $queryInfo['total_time'])){
                        if($rand <= $dataSet['bili1']*100){
                            //有几率出大件，且大件金额不超过$boxPrice(盒子价格)*$dataSet['percent1']/100
                            $boxPrice = number_format($boxPrice*$dataSet['percent1']/100,'2','.','');
                        }
                    }else{
                        //区间内
                        $in_range = true;
                        $lower_min_limit = $queryInfo['lower_min_limit'];
                        if($lower_min_limit){
                            //如果资产低于过1次下限区间，则回升值设定区间
                            if($rand <= $dataSet['bili3']*100){
                                //所有饰品中随机，上限存在者则不超过上限
                                $boxPrice = 0;
                            }else{
                                //有几率出大件，且大件金额不超过$boxPrice(盒子价格)*$dataSet['percent3']/100
                                $boxPrice = number_format($boxPrice*$dataSet['percent3']/100,'2','.','');
                            }
                        }else{
                            if(($percent>=$dataSet['min_percent']) && ($percent<=$dataSet['max_percent'])){
                                if($rand <= $dataSet['bili2']*100){
                                    //所有饰品中随机，总资产也不超过区间上限
                                    $boxPrice = 0;
                                }else{
                                    //有几率出大件，且大件金额不超过$boxPrice(盒子价格)*$dataSet['percent2']/100
                                    $boxPrice = number_format($boxPrice*$dataSet['percent2']/100,'2','.','');
                                }
                            }
                            //区间下限以下，未到达次数，有一定几率从所有饰品中随机，几率外则大件金额不超过$boxPrice(盒子价格)*$dataSet['percent3']/100
                            if($percent<$dataSet['min_percent']){
                                $lower_min_limit = true;
                                $battleRule->editAssets($player_id,0,0,0,0,'',1,'');
                                if($rand <= $dataSet['bili3']*100){
                                    //所有饰品中随机，上限存在者则不超过上限
                                    $boxPrice = 0;
                                }else{
                                    //有几率出大件，且大件金额不超过$boxPrice(盒子价格)*$dataSet['percent3']/100
                                    $boxPrice = number_format($boxPrice*$dataSet['percent3']/100,'2','.','');
                                }
                            }
                        }
                    }
                }
            }else{
                //资产不限制
                if($rand <= $dataSet['bili1']*100){
                    //所有饰品中随机，总资产也不超过区间上限
                    $rand1 = rand(0,10);
                    $re = $rand1%2;
                    if($re == 0){
                        $boxPrice = 0;
                    }else{
                        $boxPrice = keep_decimal($boxPrice * $dataSet['percent1']/100);
                    }
                }else{
                    //饰品金额不超过$boxPrice(盒子价格)*$dataSet['percent1']/100
                    $boxPrice = keep_decimal($boxPrice * $dataSet['percent1']/100);
                }
            }
        }else {
            //对战
            $queryInfo = $playerInfo;


            $dataSet = $queryInfo['dataSet'];
            //总充值（虚）
            $total_recharge = $queryInfo['total_recharge'];

            $t = $queryInfo['group'] == 1 ? 'group' : ($queryInfo['group_vip']== 1 ? 'vip' : '普通用户');
            //优先判断全员是否不限额，如果是则一定几率在  全部饰品中 随机,最大权重，其次为判断$params['percent']
            $rand   = $params['rand'];
            $count  = $params['count'];//人数
            $boxNum = $params['boxNum'];//盒子数量
            if($params['infinite']){
                //全员不限额,按阶段1概率+饰品价格百分比
                if($rand <= $dataSet['bili1']*100){
                    $boxPrice = 0;
                }else{
                    $boxPrice = keep_decimal($boxPrice * $dataSet['percent1']/100);
                }
//                dump("全员不限额,".$t."饰品价格在 ".($boxPrice>0 ? $boxPrice : '全部随机')." 以下");
            }else{
                if($params['percent']){
                    //$params['percent']为true时，表示至少1人资产在区间上限及以上，或者至少1人是次数上限了，按阶段1概率+饰品价格百分比
                    if($rand <= $dataSet['bili1']*100){
                        $boxPrice = keep_decimal($boxPrice * $dataSet['percent1']/100);
                    }
//                    dump("percent为true,都在区间上限以上或者至少1人次数限制,".$t."饰品价格在 $boxPrice 以下");
                }else{
                    if($params['stage'] == 1){
                        //全员在区间上限以上，$params['percent']无论是否为true，都忽略了此$params['stage'] == 1步骤
//                        if($rand <= $dataSet['bili1']*100){
//                            $boxPrice = keep_decimal($boxPrice * $dataSet['percent1']/100);
//                        }
                    }else if($params['stage'] == 2){
                        //全员在区间内，按阶段2概率+饰品价格百分比
                        if($params['minPrice'] > $boxPrice){
                            //差额大于箱子价格
                            if($params['minPrice'] < keep_decimal($boxPrice * $dataSet['percent2']/100)){
                                //差额小于盒子价格*百分比,表示至少1人资产比较接近区间上限，则随机饰品不能太高
                                if($rand <= $dataSet['bili2']*100){
                                    $boxPrice = keep_decimal($boxPrice * $dataSet['percent2']/100);
                                }else{
                                    $boxPrice = $params['minPrice'];
                                }
                            }else{
                                //差额大于盒子价格*百分比,表示至少1人资产和区间上限差的还比较多，则随机饰品价格可以提高
                                $value1 = keep_decimal($params['minPrice']/($count*$boxNum));
                                $value2 = keep_decimal($boxPrice * $dataSet['percent2']/100);
                                if($rand <= $dataSet['bili2']*100){
                                    $boxPrice = max($value1,$value2);
                                }else{
                                    $boxPrice = min($value1,$value2);
                                }
                            }
                        }else{
                            //差额小于箱子价格，表示至少1人资产很接近区间上限，则随机饰品不能太高
                            if($rand <= $dataSet['bili2']*100){
                                $boxPrice = keep_decimal($boxPrice * $dataSet['percent2']/100);
                            }
                        }
                    }else if($params['stage'] == 3){
                        //全员在区间下限以下，按阶段3概率+饰品价格百分比
                        $value1 = keep_decimal($params['minPrice']/($count*$boxNum));
                        $value2 = keep_decimal($boxPrice * $dataSet['percent3']/100);
                        if($rand <= $dataSet['bili3']*100){
                            $boxPrice = max($value1,$value2);
                        }else{
                            $boxPrice = min($value1,$value2);
                        }
                    }else{
                        //区间内、区间下限以下，不限，至少存在两个的情况
                        //均按阶段2概率+饰品价格百分比
                        $value1 = keep_decimal($params['minPrice']/($count*$boxNum));
                        $value2 = keep_decimal($boxPrice * $dataSet['percent2']/100);
                        if($rand <= $dataSet['bili2']*100){
                            $boxPrice = max($value1,$value2);
                        }else{
                            $boxPrice = min($value1,$value2);
                        }
//                        dump("区间内、区间下限以下，不限，至少存在两个的情况,".$t."饰品价格在 $boxPrice 以下");
                    }
                }
            }
        }
        if($boxPrice < $realBoxPrice && $boxPrice > 0){
            //最低保证饰品价格 <= 箱子价格
            $boxPrice = $realBoxPrice;
        }
        $wherePriceL = $boxPrice > 0 ? [['as.price','<=',$boxPrice]] : [];
        if($total_recharge>=0){
            $this->prizes = Db::table('all_skin')
                ->alias('as')
                ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.type_id,bs.box_id,bs.type_id,bst.color,bst.img as background')
                ->join('box_skins bs','bs.skin_id = as.id','left')
                ->join('box_skins_type bst','bst.id = bs.type_id','left')
                ->where(['bs.box_id'=>$box_id,'as.flag'=>1])
                ->where($wherePriceL)
                ->order('as.price','desc')
                ->select();
            //盒子价格及以下的饰品
            $realBoxPrice_prizes = [];
            foreach ($this->prizes as $k=>$v){
                if($this->prizes[$k]['price'] <= $realBoxPrice){
                    $realBoxPrice_prizes[] = $this->prizes[$k];
                }
            }
            if(!$this->prizes){
                echo '查无饰品';
                return false;
            }
            for($i=0;$i<$num;$i++){
                $randK = rand(0,count($this->prizes)-1);
                $this->player_skin[$i] = $this->prizes[$randK];
                //区间内和区间下限以下,上限用户，或者充值的回升
                if((($typeStr == 'open') && ($dataSet['max_percent']>0) && $in_range) || (($typeStr == 'open') && ($dataSet['max_percent']>0) && $cap)){
                    if(!$lower_min_limit){
                        if($boxPrice == 0){
                            if($randK == 0){
                                $r = rand(0,10);
                                $re = $r%2;
                                if($re == 0){
                                    $this->player_skin[$i] = $this->prizes[1];
                                }
                            }
                        }
                    }
                    //开箱情况下，由于区间范围内的情况下有几率随机全部饰品，so，此情况下如果有区间上限限制，则不能超过区间上限
                    //当前已获得的全部饰品价格（eg：5箱子，则每循环一次，计算一次）
                    $total_value = array_sum(array_map(function ($val){return $val['price'];},$this->player_skin));
                    //玩家当前总资产 = 余额+背包+本场取回
                    $total_assets = $queryInfo['total_amount'] + $queryInfo['total_skin_value'] + $queryInfo['total_retrieve_value'];
                    if($lower_min_limit){
                        //如果资产低于过1次区间下限，最大回升到设定百分比
                        $dataSet['max_percent'] = $dataSet['up_percent']>0 ? $dataSet['up_percent'] : 50;
                    }
                    if($cap){
                        $dataSet['max_percent'] = 100 + $add_percent;
                    }

                    if(($total_assets + $total_value - $realBoxPrice * $num) > ($queryInfo['total_recharge'] * $dataSet['max_percent']/100)){
                        //当前已获得的全部饰品价格超过了区间上限，则把当前获得的这个饰品替换成盒子价格及以下的随时饰品
                        if($realBoxPrice_prizes){
                            $this->player_skin[$i] = $realBoxPrice_prizes[rand(0,count($realBoxPrice_prizes)-1)];
                        }else{
                            //如果没有则取奖池中的最后一个，作为最便宜的,(存在饰品价格都高于箱子价格的情况下才会走这)
                            $this->player_skin[$i] = $this->prizes[count($this->prizes)-1];
                        }
                    }
                }
            }
            if(($typeStr == 'open') && ($dataSet['max_percent'] > 0) && $cap){
                //当前已获得的全部饰品价格（eg：5箱子，统计5个箱子开出的饰品价值）
                $total_value = array_sum(array_map(function ($val){return $val['price'];},$this->player_skin));
                //玩家当前总资产 = 余额+背包+本场取回
                $total_assets = $queryInfo['total_amount'] + $queryInfo['total_skin_value'] + $queryInfo['total_retrieve_value'];
                if(($total_assets + $total_value - $realBoxPrice * $num) >= ($queryInfo['total_recharge'] * (100 + $add_percent)/100)){
                    $battleRule->editAssets($player_id,'','','','','','','','cancel');
                }
            }

            if($typeStr == 'open'){
                if($num != count($this->player_skin)){
                    echo '数量错误';
                    return false;
                }
                $re = ['skins_info'=>$this->player_skin];
                $player_skins_ids = [];
                //本轮获得的全部饰品价值
                $currentRandSkinsValue = 0;
                foreach ($this->player_skin as $k=>$v){
                    $currentRandSkinsValue += $this->player_skin[$k]['price'];
                    unset($this->player_skin[$k]['id']);
                    unset($this->player_skin[$k]['background']);
                    unset($this->player_skin[$k]['color']);
                    $this->player_skin[$k]['player_id'] = $player_id;
                    $this->player_skin[$k]['open_number'] = $this->randProbability($box_id,$this->player_skin[$k]['type_id']);
                    $this->player_skin[$k]['open_number_md'] = md5( $this->player_skin[$k]['open_number']);
                    $this->player_skin[$k]['create_time'] = currentTime();
                    $this->player_skin[$k]['way'] = 1 ;
                    $player_skins_ids[] = Db::table('player_skins')->insertGetId($this->player_skin[$k]);
                }
                $re['player_skins_ids'] = $player_skins_ids;
                //本轮支付金额
                $currentPaymentAmount = keep_decimal($realBoxPrice*$num);
                $re['currentPaymentAmount'] = $currentPaymentAmount;
                $re['currentRandSkinsValue'] = keep_decimal($currentRandSkinsValue);
                if($currentPaymentAmount > $currentRandSkinsValue){
                    //亏一次就记录连续次数，增加开盒子价格的百分比
                    $successiveLostTime = $this->redis->get('successiveLostTime'.$player_id.'_'.$box_id);
                    if(!$successiveLostTime){
                        $successiveLostTime = 1;
                        $this->redis->set('successiveLostTime'.$player_id.'_'.$box_id,$successiveLostTime,0);
                    }else{
                        $successiveLostTime = $successiveLostTime + 1;
                        $this->redis->set('successiveLostTime'.$player_id.'_'.$box_id,$successiveLostTime,0);
                    }
                }else{
                    $this->redis->set('successiveLostTime'.$player_id.'_'.$box_id,0,0);
                    $successiveLostTime = $this->redis->get('successiveLostTime'.$player_id.'_'.$box_id);
                }
                $re['boxPrice'] = $boxPrice;
                $re['successiveLostTime'] = $successiveLostTime ? $successiveLostTime : 0;
                //抽中的皮肤存数据库
                $update['skins_info'] = json_encode($this->player_skin);
                $update['status'] = 2;//状态，1：未开奖，2：开奖
                $update['update_time'] = currentTime();
                Db::table('player_box_skin')->where('id', $player_box_skin_id)->update($update);
                return $re;
            }
            if($typeStr == 'battle'){
                foreach ($this->player_skin as $k=>$v){
//                    unset($this->player_skin[$k]['id']);
                    unset($this->player_skin[$k]['background']);
                    unset($this->player_skin[$k]['color']);
                    $this->player_skin[$k]['player_id'] = $player_id;
                    $this->player_skin[$k]['open_number'] = $this->randProbability($box_id,$this->player_skin[$k]['type_id']);
                    $this->player_skin[$k]['open_number_md'] = md5( $this->player_skin[$k]['open_number']);
                    $this->player_skin[$k]['create_time'] = currentTime();
                    $this->player_skin[$k]['way'] = 2 ;
                }
                return  $this->player_skin[0];
            }
        }
        echo '用户（' . $player_id . '）recharge为0,总充值:' . $total_recharge;
        return false;
    }

    //随机范围内的值
    public function randProbability($box_id=null,$type_id=null){
        $probability = Db::table('box_skins_probability')->where('box_id',$box_id)->select();
        $rangeEnd = 0;
        $rand = 0;
        foreach ($probability as $k=>$v){
            $rangeEnd += $probability[$k]['probability'];
            if($type_id == $probability[$k]['box_skins_type_id']){
                $rangeStart = ($rangeEnd - $probability[$k]['probability'])*100 + 1 ;
                $rangeEnd = $rangeEnd*100;
                $rand = rand($rangeStart,$rangeEnd);
            }
        }
        return $rand;
    }


    public function getSkin($box_id=null,$player_id=null,$player_box_skin_id=null){
        $box_id = input('post.box_id') ? input('post.box_id') : $box_id;
        $player_id = input('post.player_id') ? input('post.player_id') : $player_id;
        $player_box_skin_id = input('post.player_box_skin_id') ? input('post.player_box_skin_id') : $player_box_skin_id;

        if (empty(trim($box_id)) || empty(trim($player_box_skin_id))) {
            echo '盲盒信息错误';
            return false;
        }
        if (empty(trim($player_id))) {
            echo '玩家信息错误';
            return false;
        }

        //概率组账号id
        $groups = [];
        $groupAccount = Db::table('player')
            ->field('id')
            ->where('group',1)
            ->select();
        //如果存在概率组，则查询对应箱子饰品信息
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



        //查询玩家购买的盒子信息，盒子数量，
        $player_box_skin_info = Db::table('player_box_skin')
            ->where(['id'=>$player_box_skin_id,'box_id'=>$box_id,'player_id'=>$player_id])
            ->find();
        if (!$player_box_skin_info) {
            echo '购买的商品不存在或已开奖';
            return false;
        }
        if ($player_box_skin_info['status'] == 2) {
            echo '已开过奖';
            return false;
        }
        $this->prizes = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.probability,bs.type_id,bs.box_id,bs.type_id,bs.stock,bs.stock_group,bs.stock_vip,bst.color,bst.img as background')
            ->join('box_skins bs','bs.skin_id = as.id','left')
            ->join('box_skins_type bst','bst.id = bs.type_id','left')
            ->where(['bs.box_id'=>$box_id,'as.flag'=>1])
            ->order('bs.stock','desc')
            ->select();
        $num = $player_box_skin_info['num'];

        $skins_info = [];
//        Db::startTrans();
//        try {
        $player_skins_ids = [];
        for ($i = 0; $i < $num; $i++) {
            $this->getResult($box_id, $player_id, $player_box_skin_info['box_name'],$groups,$vips);//中奖奖品信息
            $is_group = in_array($player_id,$groups);
            $is_vip   = in_array($player_id,$vips);
            if($this->player_skin){
                if($is_vip){
                    Db::table('box_skins')
                        ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
                        ->setDec('stock_vip',1);
                }else{
                    if($is_group){
//                            Db::table('box_skins')
//                                ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
//                                ->setDec('stock_group',1);
                    }else{
                        Db::table('box_skins')
                            ->where(['box_id'=>$this->player_skin['box_id'],'skin_id'=>$this->player_skin['id']])
                            ->setDec('stock',1);
                    }
                }
            }else{
                echo 'stock不足';
                return false;
            }

            $player_skins_info = $this->player_skin;
            unset($player_skins_info['id']);
            unset($player_skins_info['stock']);
            unset($player_skins_info['stock_group']);
            unset($player_skins_info['stock_vip']);
            unset($player_skins_info['color']);
            unset($player_skins_info['background']);
            unset($player_skins_info['re_probability']);
            $player_skins_info['player_id'] = $player_id;
            //（一次性买多个盒子）设置子订单，或只有一个盒子则为主订单
            if ($num == 1) {
                $this->player_skin['order_num'] = $player_skins_info['order_num'] = $player_box_skin_info['order_num'];
            } else {
                $orderLength = strlen($player_box_skin_info['order_num']);
                //截取主订单后3位接在子订单尾部
                $endNum = substr($player_box_skin_info['order_num'], $orderLength - 3);
                $this->player_skin['order_num'] = $player_skins_info['order_num'] = makeOder('', 3) . $endNum;
            }
            $player_skins_info['create_time'] = currentTime();
            $player_skins_info['way'] = 1;
            $player_skins_id = Db::table('player_skins')->insertGetId($player_skins_info);
            $player_skins_ids[] = $player_skins_id;
            unset($this->player_skin['v']);
//                unset($this->player_skin['img']);
//                unset($this->player_skin['price']);
            array_push($skins_info, $this->player_skin);
            //幸运值操作，越高越容易开金色，开到史诗或传说幸运值归零（待开发）
        }
        //抽中的皮肤存数据库
        $update['skins_info'] = json_encode($skins_info);
        $update['status'] = 2;//状态，1：未开奖，2：开奖
        $update['update_time'] = currentTime();
        Db::table('player_box_skin')->where('id', $player_box_skin_id)->update($update);
//            Db::commit();
        $res = ['player_skins_ids'=>$player_skins_ids,'skins_info'=>$skins_info];
        return $res;
//            return result(1,$res, '');
//        } catch (\Exception $e) {
////            Db::rollback();
//            return $e->getMessage();
////            return result(0, '', $e->getMessage());
//        }

    }



    public $prizes = array();
    public $totalStock = 0;
    public $player_skin = array();
    public $str = '';
    public $count = 0;

    public function getResult($box_id=null,$player_id=null,$box_name=null,$groups,$vips){
        $box_id    = input('post.box_id',$box_id);
        $player_id = input('post.player_id',$player_id);
        $box_name  = input('post.box_name',$box_name);
        if(!$box_id || !$box_name){
            return result(0, '', '盲盒信息错误');
        }
        if(!$player_id){
            return result(0, '', '玩家信息错误');
        }
//        $this->totalStock = array_sum(array_map(function($val){return $val['stock'];}, $this->prizes));//总库存

        //判断是否是概率组用户
        $is_group = in_array($player_id,$groups);
        $is_vip   = in_array($player_id,$vips);

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
        //随机数
        $rand = rand(1,$this->totalStock);

        if($this->totalStock > 0){
            $this->rand_skin($rand,$is_vip,$is_group,$box_id);
        }else{
            $mendStock = new Opp();
            $mendStock->mend($box_id,$this->str);
            $this->getPrizes($box_id);
            $this->totalStock = array_sum(array_map(function($val){return $val[$this->str];}, $this->prizes));
            $rand = rand(1,$this->totalStock);
            $this->rand_skin($rand,$is_vip,$is_group,$box_id);
        }
    }

    public function rand_skin($rand=null,$is_vip=null,$is_group=null,$box_id=null){
        $start = 0;
        $end   = 0;
        if($is_vip){
            foreach ($this->prizes as $k=>$v){
                if($this->prizes[$k]['stock_vip']>0){
                    $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock_vip']/$this->totalStock)) * 100;
//                        $end += (($this->prizes[$k]['re_probability']) * 100);
                    $end += $this->prizes[$k]['stock_vip'];
//                    dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                    if(($start < $rand) && ($rand <= $end)){
                        //随机值在范围内表示中奖该皮肤
//                        dump('开奖号码：'.$rand.',中奖奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
                        $this->prizes[$k]['open_number']    = $rand;
                        $this->prizes[$k]['open_number_md'] = md5($rand);
                        $this->player_skin = $this->prizes[$k];
                        //奖品存入背包，扣一个库存,如果扣减后库存为0，则移除
                        $this->prizes[$k]['stock_vip'] -= 1;
                        if($this->prizes[$k]['stock_vip'] <= 0){
                            unset($this->prizes[$k]);
                        }
                    }
                    $start = $end;
                }
            }
        }else{
            if($is_group){
                foreach ($this->prizes as $k=>$v){
                    if($this->prizes[$k]['stock_group']>0){
                        $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock_group']/$this->totalStock)) * 100;
                        $end += $this->prizes[$k]['stock_group'];
//                    dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                        if(($start < $rand) && ($rand <= $end)){
                            //随机值在范围内表示中奖该皮肤
//                        dump('开奖号码：'.$rand.',中奖奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
                            $this->prizes[$k]['open_number']    = $rand;
                            $this->prizes[$k]['open_number_md'] = md5($rand);
                            $this->player_skin = $this->prizes[$k];
                            //奖品存入背包，扣一个库存,如果扣减后库存为0，则移除
//                                $this->prizes[$k]['stock_group'] = $this->prizes[$k]['stock_group'] - 1;

                            if($this->prizes[$k]['stock_group'] <= 0){
                                unset($this->prizes[$k]);
                            }
                            if($this->prizes == array()){
                                $mendStock = new Opp();
                                $mendStock->mend($box_id,'stock_group');
                            }
                        }
                        $start = $end;
                    }
                }
            }else{
                foreach ($this->prizes as $k=>$v){
                    if($this->prizes[$k]['stock']>0){
                        $this->prizes[$k]['re_probability'] = sprintf("%.4f", ($this->prizes[$k]['stock']/$this->totalStock)) * 100;
                        $end += $this->prizes[$k]['stock'];
//                    dump('第'.($k+1).'个奖品【'.$this->prizes[$k]['name'].'】在'.$start.'--'.$end.'范围内.');
                        if(($start < $rand) && ($rand <= $end)){
                            //随机值在范围内表示中奖该皮肤
//                        dump('开奖号码：'.$rand.',中奖奖品,id:'.$this->prizes[$k]['id'].',【'.$this->prizes[$k]['name'].'】');
                            $this->prizes[$k]['open_number']    = $rand;
                            $this->prizes[$k]['open_number_md'] = md5($rand);
                            $this->player_skin = $this->prizes[$k];
                            //奖品存入背包，扣一个库存,如果扣减后库存为0，则移除
                            $this->prizes[$k]['stock'] = $this->prizes[$k]['stock'] - 1;

                            if($this->prizes[$k]['stock'] <= 0){
                                unset($this->prizes[$k]);
                            }
                            if($this->prizes == array()){
                                $mendStock = new Opp();
                                $mendStock->mend($box_id,'stock');
                            }
                        }
                        $start = $end;
                    }
                }
            }
        }
    }

    //没有库存时，补库存之后再重新获取一下奖品列表
    public function getPrizes($box_id=null){
        $this->prizes = Db::table('all_skin')
            ->alias('as')
            ->field('as.id,as.appId,as.itemId,as.itemName as name,as.imageUrl as img,as.price,bs.probability,bs.type_id,bs.box_id,bs.type_id,bs.stock,bs.stock_group,bs.stock_vip,bst.color,bst.img as background')
            ->join('box_skins bs','bs.skin_id = as.id','left')
            ->join('box_skins_type bst','bst.id = bs.type_id','left')
            ->where(['bs.box_id'=>$box_id,'as.flag'=>1])
            ->order('bs.stock','desc')
            ->select();
    }


    //获取最近盲盒开奖情况
    public function lately(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $box_id  = input('post.box_id');
        $where = ($box_id>0) ? ['b.id'=>$box_id] : '';

        $list = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id')
            ->join('box b','b.id=ps.box_id','left')
            ->join('box_skins_type bst','bst.id = ps.type_id','left')
            ->field('ps.img as imageUrl,ps.name as skin_name,ps.box_id,b.name as box_name,p.name as player_name,p.img as player_img,ps.create_time,bst.color,bst.img')
            ->where($where)
            ->where(['way'=>1])
            ->order('ps.id','desc')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();
        if($list){
            foreach ($list as $k=>$v){
                $list[$k]['player_img'] = $list[$k]['player_img'] ? mainName().$list[$k]['player_img'] : '';
            }
            $res = ['list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '');
        }
    }


    public function battleToMend($box_id=null,$num=null){
        $stock = Db::table('box_skins')
            ->where('box_id',$box_id)
            ->field(['sum(stock) as stock,sum(stock_vip) as stock_vip,sum(set_stock) as set_stock,sum(set_stock_vip) as set_stock_vip'])
            ->select();
        $mend = Db::table('mend')->where('box_id',$box_id)->find();
        if(($mend['billie']>0) || ($mend['vip_billie']>0)){
            if($stock[0]['set_stock']>0){
                $stockBillie = number_format($stock[0]['stock']/$stock[0]['set_stock'],'2','.','')*100;
                if(($stockBillie <= $mend['billie']) || ($num > $stock[0]['stock'])){
                    $mendStock = new Opp();
                    $mendStock->mend($box_id,'stock');
                }
            }
            if($stock[0]['set_stock_vip']>0){
                $stockVipBillie = number_format($stock[0]['stock_vip']/$stock[0]['set_stock_vip'],'2','.','')*100;
                if(($stockVipBillie <= $mend['vip_billie']) || ($num > $stock[0]['stock_vip'])){
                    $mendStock = new Opp();
                    $mendStock->mend($box_id,'stock_vip');
                }
            }
        }else{
            if(((4*6)>$stock[0]['stock']) || ((4*6)>$stock[0]['stock_vip'])){
                if($stock[0]['set_stock']>0){
                    $mendStock = new Opp();
                    $mendStock->mend($box_id,'stock');
                }
                if($stock[0]['set_stock_vip']>0){
                    $mendStock = new Opp();
                    $mendStock->mend($box_id,'stock_vip');
                }
//                        return result(-1, '', '手滑了一下，再来一次');
                //这里可以操作补补库存
            }
        }
    }

}