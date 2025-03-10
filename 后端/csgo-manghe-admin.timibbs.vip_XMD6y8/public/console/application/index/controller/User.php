<?php


namespace app\index\controller;


use app\admin\controller\Upload;
use app\admin\controller\User as AdminUser;
use PHPUnit\Framework\Exception;
use think\cache\driver\Redis;
use think\Db;
use think\facade\Session;
use think\facade\Config;

class User
{
    public $redis;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    //绑定Steam账号
    public function bindSteam()
    {
        $player_id = input('post.player_id');
        $tradeUrl = input('post.tradeUrl');
        if (empty(trim($player_id))) {
            return result(0, '', '玩家信息错误');
        }
        if (empty(trim($tradeUrl))) {
            return result(0, '', '请输入您的交易链接');
        }

        //------
        $player = [
            'tradeUrl' => $tradeUrl,
            'steamId' => ''
        ];
        Db::table('player')->where('id', $player_id)->update($player);
        return result(1, '', '成功');
//        -------

        $steamId = Db::table('player')->where('id', $player_id)->value('steamId');
        $delInfo = devInfo();

        $url = '/open/user/steam-info/';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN',
            'appId'    => 730,
            'steamId'  => '',
            'tradeUrl' => $tradeUrl,
            'type'     => 1,//不同检测场景，1为购买，2为出售,示例值(1)
        ];
        $url = $request_url . '?' . http_build_query($params);

        $re_query = httpRequest($url, 'get','');
        $re_query = json_decode($re_query,true);
        if($re_query['success'] == false){
            return result(0, '', $re_query['errorMsg']);
        }
        $steamId = trim($steamId);

        if($steamId && ($steamId != $re_query['data']['steamInfo']['steamId'])){
            return result(0, '', '不可绑定');
        }


        $url = '/open/v1/seller/steam/bind/';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key'  => $delInfo['apiKey'],
            'language' => 'zh_CN'
        ];
        $data = [
            "apikey" => $delInfo['apiKey'],
            "tradeUrl" => $tradeUrl,
        ];
        $url = $request_url . '?' . http_build_query($params);

        $re = httpRequest($url, 'post', json_encode($data));
        $re = json_decode($re, true);
        if (isset($re['success']) && $re['success'] == true) {
            $player = [
                'tradeUrl' => $tradeUrl,
                'steamId' => $re_query['data']['steamInfo']['steamId']
            ];
            Db::table('player')->where('id', $player_id)->update($player);
            return result(1, '', $re['data']);
        }
        return result(0, '', $re['errorMsg']);
    }

    //用户绑定steam账号查询
    public function steamAccounts()
    {
        $delInfo = devInfo();
        $url = '/open/user/steam-accounts';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN'
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'get');
        return $re;
    }

    //玩家背包皮肤列表
    public function packageList()
    {
        $player_id = input('post.player_id');
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        if (empty(trim($player_id))) {
            return result(0, '', '玩家信息错误');
        }
        $skinList = Db::table('player_skins')
            ->alias('ps')
            ->field('as.exteriorName,ps.id,ps.name,ps.img,ps.price,ps.itemId,ps.status')
            ->join('all_skin as','as.itemId = ps.itemId','left')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where(['ps.player_id' => $player_id, 'ps.status' => [1, 4], 'ps.flag' => 1])
            ->order(Db::raw("ps.status asc, ps.price asc"))
            ->select();

        $total = Db::table('player_skins')
            ->where(['player_id' => $player_id, 'status' => [1, 4], 'flag' => 1])
            ->count();
        if ($total > 0) {
            $res = ['total' => $total, 'skinList' => $skinList];
            return result(1, $res, '');
        } else {
            return result(0, '', '无数据');
        }
    }

    //用户背包
    public function playerPackege(){

        $adminUser = new AdminUser();
        $playerPackege = $adminUser->playerPackege();

        return $playerPackege;
    }


    //玩家取回皮肤，相当于平台购买并发货到玩家的steam账号
    //先获取饰品的所有在售，选择人工或者自动发货(自动为好)，在通过普通购买发货到玩家steam账号
    public function skinToSteam()
    {
        $redis = new Redis();
        $player_skins_id = input('post.player_skins_id');
        $player_id = input('post.player_id');
        $itemId = input('post.itemId');
        $random = input('post.random');
        if(empty(trim($player_id))){
            return result(0, '', '用户信息有误');
        }

        $battleRule = new BattleRule();
        $battleRule->query($player_id);

        session_start();
        if(!isset($_SESSION['random'])){
            $_SESSION['random'] = $random;
        }else{
            if($_SESSION['random'] == $random){
                return result(0, '', '操作频繁');
            }else{
                $_SESSION['random'] = $random;
            }
        }


        $skinToSteam = $redis->get('skinToSteam'.$player_id);

        if(!$skinToSteam){
            $arr[] = $player_skins_id;
            $redis->set('skinToSteam'.$player_id,$arr,3600);
        }else{
            if(in_array($player_skins_id,$skinToSteam)){
                return result(0, '', '皮肤取回中，请勿重复操作');
            }else{
                array_push($skinToSteam,$player_skins_id);
                $redis->set('skinToSteam'.$player_id,$skinToSteam,3600);
            }
        }

        if(!$player_skins_id){
            $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
            return result(0, '', '皮肤信息错误');
        }

        $state = Db::table('player_skins')
            ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'itemId' => $itemId, 'status' => 2])
            ->value('state');
        if ($state == 'processing') {
            return result(0, '', '皮肤饰品提货中...');
        }

        if (empty(trim($player_id))) {
            $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
            return result(0, '', '玩家信息错误');
        }
        if (empty(trim($itemId))) {
            $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
            return result(0, '', '皮肤信息错误');
        }

        // 未满足充值金额,禁止提货
        $total = Db::table('recharge')
            ->where('player_id',$player_id)
            ->where('status',3)
            ->sum('money');
        $minpay  = 32.5;
        if ($total < $minpay) {
            // return result(0, '', '必须要充值5刀才能提货');
        }


        Db::startTrans();
        try {
            //获取皮肤信息
            $skinInfo = Db::table('player_skins')
                ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'itemId' => $itemId, 'status' => 1])
                ->find();
            if (!$skinInfo) {
                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
                return result(0, '', '皮肤不存在');
            }
            //玩家信息
            $player_info = Db::table('player')
                ->where(['id' => $player_id, 'flag' => 1])
                ->find();
            if (!$player_info) {
                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
                return result(0, '', '玩家不存在');
            }
            if (($player_info['group']>0) && ($player_info['mobile'] != '15881150667')) {
                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
                return result(0, '', '取回错误');
            }
            //禁止取回
            if (($player_info['allow'] == 2) && ($player_info['mobile'] != '15881150667')) {
                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
                return result(0, '', '取回错误');
            }

            if (!trim($player_info['tradeUrl'])) {
                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
                return result(0, '', '账户未绑定Steam');
            }

            $updateStatus = ['status' => 4, 'state' => 'processing'];
            Db::table('player_skins')->where('id', $player_skins_id)->update($updateStatus);
            Db::commit();
            return result(1, '', '操作成功，皮肤饰品提货中...');

//            $updateStatus = ['status' => 2];
//            Db::table('player_skins')->where('id', $player_skins_id)->update($updateStatus);
//
//            $buyWay = Db::table('set_buy_way')->find();
//            //优先快速购买，购买失败则再进行普通购买
//            if($buyWay['way'] == 'fast'){
//                $plat_order = makeOder('BK', '');//快速平台订单
////                $maxPrice = Db::table('all_skin')->where('itemId',$skinInfo['itemId'])->value('maxPrice');
//                $maxPrice = $skinInfo['price'];//能接受的最大发货价格为平台价格，注意美元和人民币，当前ahyltt.com为美元
//                $buyRe = $this->fastBuy($skinInfo['itemId'],$skinInfo['name'],$player_info['tradeUrl'],$plat_order,$maxPrice);
//                $buyRe = json_decode($buyRe,true);
//
//                if(($buyRe['success'] == true)){
//                    //此时返回的结果中还没有tradeOfferId和orderId，修改饰品状态
//                    $update['plat_order'] = $skinInfo['plat_order'] = $plat_order;
//                    $update['offerId']    = $skinInfo['offerId'] = $buyRe['data']['offerId'];
//                    $update['orderId']    = $skinInfo['orderId'] = $buyRe['data']['orderId'];
////                    $update['buyPrice']   = $skinInfo['buyPrice'] = $buyRe['data']['buyPrice'];
//                    $update['status']     = $skinInfo['status'] = 2;
//                    $update['state']      = $skinInfo['state'] ='sending';
//                    $update['order_time'] = currentTime();//下单时间
//                    Db::table('player_skins')->where('id', $player_skins_id)->update($update);
//                    $result[] = $skinInfo;
//                    Db::commit();
//                    $retrievePrice = $skinInfo['price'];
//                    $battleRule->editAssets($player_id,0,0,-$retrievePrice,'','','',$retrievePrice);
//                    $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
//                    return result(1,$result,'发送报价中,请关注您的取回助手');
//                }
//            }
//
//            $re = $this->sellList($skinInfo['itemId'], $skinInfo['delivery']);
//            if (isset($re['success']) && ($re['success'] == false)) {
//                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
//                return result(0, $re, '');
//            } else {
//                //-----------普通购买--------------
//                $plat_order = makeOder('BO', '');//普通购买平台订单
//                $buyRe = $this->buy($re['id'], $player_info['tradeUrl'], $skinInfo['buyPrice'], $skinInfo['maxPrice'], $plat_order);
//
//                $buyRe = json_decode($buyRe, true);
//                if (isset($buyRe['success']) && ($buyRe['success'] == false)) {
//                    $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
//                    return result(0, '', $buyRe['errorMsg']);
//                } else {
//                    $update['offerId'] = $buyRe['data']['offerId'];
//                    $update['orderId'] = $buyRe['data']['orderId'];
//                    $update['plat_order'] = $plat_order;
//                    $update['state']      = 'waiting_delivery';
//                    $update['order_time'] = currentTime();//下单时间
//                    Db::table('player_skins')->where('id', $player_skins_id)->update($update);
//                }
//                Db::commit();
//                $retrievePrice = $skinInfo['price'];
//                $battleRule->editAssets($player_id,0,0,-$retrievePrice,'','','',$retrievePrice);
//                $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
//                return result(1, '', '操作成功，请等待发货并关注您的取回助手');
//                //-------------------------
//            }
        }catch (\Exception $e){
            Db::rollback();
            Db::table('player_skins')
                ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'itemId' => $itemId, 'status' => 1])
                ->setField('state','');
            $this->setSkinToSteam($skinToSteam,$player_skins_id,$player_id);
            return result(1, '', $e->getMessage());
        }
    }


    public function sendSkinToSteam($player_skins_id = 0, $player_id = 0, $itemId = 0)
    {
        $battleRule = new BattleRule();
        $battleRule->query($player_id);

        $redis = new Redis();

        $skinToSteam = $redis->get('skinToSteam' . $player_id);

        Db::startTrans();
        try {
            //获取皮肤信息
            $skinInfo = Db::table('player_skins')
                ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'itemId' => $itemId, 'status' => 4])
                ->find();
            if (!$skinInfo) {
                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(0, '', '皮肤不存在');
            }
            //玩家信息
            $player_info = Db::table('player')
                ->where(['id' => $player_id, 'flag' => 1])
                ->find();
            if (!$player_info) {
                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(0, '', '玩家不存在');
            }
            if (($player_info['group'] > 0) && ($player_info['mobile'] != '15881150667')) {
                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(0, '', '取回错误');
            }




            //禁止取回
            if (($player_info['allow'] == 2) && ($player_info['mobile'] != '15881150667')) {
                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(0, '', '取回错误');
            }

            if (!trim($player_info['tradeUrl'])) {
                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(0, '', '账户未绑定Steam');
            }
            $updateStatus['status'] = 2;
            Db::table('player_skins')->where('id', $player_skins_id)->update($updateStatus);

            $buyWay = Db::table('set_buy_way')->find();
            //优先快速购买，购买失败则再进行普通购买
//            if ($buyWay['way'] == 'fast') {
//                $plat_order = makeOder('BK', '');//快速平台订单
////                $maxPrice = Db::table('all_skin')->where('itemId',$skinInfo['itemId'])->value('maxPrice');
//                $maxPrice = $skinInfo['price'];//能接受的最大发货价格为平台价格，注意美元和人民币，当前ahyltt.com为美元
//                $buyRe = $this->fastBuy($skinInfo['itemId'], $skinInfo['name'], $player_info['tradeUrl'], $plat_order, $maxPrice);
//                $buyRe = json_decode($buyRe, true);
//
//                if (($buyRe['success'] == true)) {
//                    //此时返回的结果中还没有tradeOfferId和orderId，修改饰品状态
//                    $update['plat_order'] = $skinInfo['plat_order'] = $plat_order;
//                    $update['offerId'] = $skinInfo['offerId'] = $buyRe['data']['offerId'];
//                    $update['orderId'] = $skinInfo['orderId'] = $buyRe['data']['orderId'];
////                    $update['buyPrice']   = $skinInfo['buyPrice'] = $buyRe['data']['buyPrice'];
//                    $update['status'] = $skinInfo['status'] = 2;
//                    $update['state'] = $skinInfo['state'] = 'sending';
//                    $update['order_time'] = currentTime();//下单时间
//                    Db::table('player_skins')->where('id', $player_skins_id)->update($update);
//                    $result[] = $skinInfo;
//                    Db::commit();
//                    $retrievePrice = $skinInfo['price'];
//                    $battleRule->editAssets($player_id, 0, 0, -$retrievePrice, '', '', '', $retrievePrice);
//                    $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
//                    return result(1, $result, '发送报价中,请关注您的取回助手');
//                }
//            }

            $re = $this->sellList($skinInfo['itemId'], $skinInfo['delivery']);
//            if (isset($re['success']) && ($re['success'] == false)) {
//                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
//                return result(0, $re, '');
//            } else {
                //-----------普通购买--------------
                $plat_order = makeOder('BO', '');//普通购买平台订单
//                $buyRe = $this->buy($re['id'], $player_info['tradeUrl'], $skinInfo['buyPrice'], $skinInfo['maxPrice'], $plat_order);
//
//                $buyRe = json_decode($buyRe, true);
//                if (isset($buyRe['success']) && ($buyRe['success'] == false)) {
//                    $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
//                    return result(0, '', $buyRe['errorMsg']);
//                } else {
//                    $update['offerId'] = $buyRe['data']['offerId'];
//                    $update['orderId'] = $buyRe['data']['orderId'];
//                    $update['plat_order'] = $plat_order;
//                    $update['state'] = 'waiting_delivery';
//                    $update['order_time'] = currentTime();//下单时间
//                    Db::table('player_skins')->where('id', $player_skins_id)->update($update);
//                }

                $update['offerId'] = '111';
                $update['orderId'] = '222';
                $update['plat_order'] = $plat_order;
                $update['state'] = 'waiting_receive';              // 'sending','waiting_delivery','waiting_receive'
                $update['order_time'] = currentTime();//下单时间
                Db::table('player_skins')->where('id', $player_skins_id)->update($update);



                Db::commit();
                $retrievePrice = $skinInfo['price'];
                $battleRule->editAssets($player_id, 0, 0, -$retrievePrice, '', '', '', $retrievePrice);

                $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
                return result(1, '', '操作成功，请等待发货并关注您的取回助手');
                //-------------------------
//            }
        } catch (\Exception $e) {
            Db::rollback();
            Db::table('player_skins')
                ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'itemId' => $itemId, 'status' => 1])
                ->setField('state', '');
            $this->setSkinToSteam($skinToSteam, $player_skins_id, $player_id);
            return result(1, '', $e->getMessage());
        }
    }



    //设置取回信息，在完成取回或者中间出错时，将本次取回的饰品信息从缓存中移除掉
    public function setSkinToSteam($skinToSteam=array(),$player_skins_id=null,$player_id=null){
        if(!$skinToSteam){
            $skinToSteam = array();
        }
        $skinToSteam = array_diff($skinToSteam,[$player_skins_id]);
        $this->redis->set('skinToSteam'.$player_id,$skinToSteam,3600);
    }


    //饰品的所有在售,(选出一个低价饰品)
    public function sellList($itemId = null, $delivery = null)
    {
        //$delivery 发货：1人工 2自动
        $delInfo = devInfo();
        $url = '/open/product/v1/sell/list';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN',
            'delivery' => $delivery,
            'itemId' => $itemId,
            'orderBy' => 2,//排序 0 默认排序 1 最新上架倒叙 2 价格升序 3 价格倒叙 4 磨损度升序 5 磨损度降序
            'limit' => 2
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'get');
        $re = json_decode($re, true);
        if (isset($re['success']) && ($re['success'] == true)) {
            if ($re['data']['total'] > 0) {
                return $re['data']['list'][0];
            }
            return false;
        } else {
            return $re;
        }
    }

    //平台购买饰品//普通购买
    public function buy($productId = null, $tradeUrl = null, $buyPrice = null, $maxPrice = null, $outTradeNo = null)
    {
        $delInfo = devInfo();
        $url = '/open/trade/v2/buy';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN'
        ];
        $data = [
            "buyPrice" => $buyPrice,
            "maxPrice" => $maxPrice,
            "outTradeNo" => $outTradeNo,
            "productId" => $productId,
            "tradeUrl" => $tradeUrl
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post', json_encode($data));
        return $re;
    }

    //平台购买饰品//快速购买
    public function fastBuy($itemId=null,$marketHashName=null,$tradeUrl=null,$outTradeNo=null,$maxPrice=null)
    {
//        $tradeUrl = 'https://steamcommunity.com/tradeoffer/new/?partner=1139012827&token=FCo1bt--';
//        $itemId = '553479329';
//        $marketHashName = '内格夫 | 舱壁 (崭新出厂)';
//        $outTradeNo = '25546212185';

        $delInfo = devInfo();
        $url = '/open/trade/v2/quick-buy';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN'
        ];

        $data = [
            "appId"          => 730,
            "delivery"       => 2,                    //可选参数，发货模式，1=人工，2自动
            "itemId"         => $itemId,
            "lowPrice"       => 1,                    //是否购买最低价，如果是1，购买最低价
            "marketHashName" => $marketHashName,
            "maxPrice"       => $maxPrice,
            "outTradeNo"     => $outTradeNo,
            "tradeUrl"       => $tradeUrl
        ];

        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post', json_encode($data));
        return $re;
    }


    //把皮肤兑换成T币
    public function exchangeToMoney()
    {
        $player_id = input('post.player_id');
        $player_skins_ids = input('post.player_skins_ids');
        if (empty(trim($player_id))) {
            return result(0, '', '缺少玩家信息');
        }

        $redis = new Redis();
        $redisInfo = $redis->get('exchangeToMoney'.$player_id);
        if($redisInfo){
            if($player_skins_ids){
                foreach ($player_skins_ids as $k=>$v){
                    $re = in_array($player_skins_ids[$k],$redisInfo);
                    if($re){
//                        $this->setExchangeToMoney($redisInfo,$player_skins_ids[$k],$player_id);
                        return result(0, '', '饰品兑换中...请稍后再操作');
                    }else{
                        array_push($redisInfo,$player_skins_ids[$k]);
                        $redis->set('exchangeToMoney'.$player_id,$redisInfo,3600);
                    }
                }
            }else{

            }
        }else{
            foreach ($player_skins_ids as $k=>$v){
                $redis->set('exchangeToMoney'.$player_id,$player_skins_ids,3600);
            }
        }
        $battleRule = new BattleRule();
        $battleRule->query($player_id);

        Db::startTrans();
        try {
            $total_price = 0;
            foreach ($player_skins_ids as $k => $k) {
                $player_skins_id = $player_skins_ids[$k];
                if (empty(trim($player_skins_id))) {
                    return result(0, '', '缺少皮肤信息');
                }
                $skinInfo = Db::table('player_skins')
                    ->where(['id' => $player_skins_id, 'player_id' => $player_id, 'status' => 1])
                    ->find();
                if (!$skinInfo) {
                    return result(0, '', '皮肤信息不存在');
                }
                $price = $skinInfo['price'];

                //修改皮肤状态为已兑换
                Db::table('player_skins')
                    ->where(['id' => $player_skins_id, 'player_id' => $player_id])
                    ->setField('status', 3);
                //操作余额表
                $total_amount = Db::table('player')
                    ->where(['id' => $player_id, 'flag' => 1])
                    ->value('total_amount');
                $balance = new Balance();
                $re = $balance->opBalance($player_id, $price, $total_amount, 1);
                if (!$re) {
                    return result(0, '', '兑换失败');
                }
                $total_amount += $price;
                $total_price += $price;
            }
            Db::commit();
            $redisInfo = $redis->get('exchangeToMoney'.$player_id);
            foreach ($player_skins_ids as $k=>$v){
                foreach ($redisInfo as $key=>$val){
                    if($redisInfo[$key] == $player_skins_ids[$k]){
                        unset($redisInfo[$key]);
                    }
                }
            }
            $redisInfo = array_merge($redisInfo);
            $redis->set('exchangeToMoney'.$player_id,$redisInfo,3600);
            $battleRule = new BattleRule();
            $battleRule->editAssets($player_id,'',$total_price,-$total_price,'');
            $re = ['total_amount' => (float)sprintf("%.2f", $total_amount)];
            return result(1, $re, '兑换成功');
        } catch (\Exception $e) {
            Db::rollback();
            $redisInfo = $redis->get('exchangeToMoney'.$player_id);
            foreach ($player_skins_ids as $k=>$v){
                foreach ($redisInfo as $key=>$val){
                    if($redisInfo[$key] == $player_skins_ids[$k]){
                        unset($redisInfo[$key]);
                    }
                }
            }
            $redisInfo = array_merge($redisInfo);
            $redis->set('exchangeToMoney'.$player_id,$redisInfo,3600);
            return result(0, '', $e->getMessage());
        }
    }

    //设置兑换信息，在完成兑换或者中间出错时，将本次兑换的饰品信息从缓存中移除掉
    public function setExchangeToMoney($redisInfo=array(),$player_skins_id=null,$player_id=null){
        if(!$redisInfo){
            $redisInfo = array();
        }
        $redisInfo = array_diff($redisInfo,[$player_skins_id]);
        $this->redis->set('exchangeToMoney'.$player_id,$redisInfo,3600);
    }


    //玩家查看取回列表状态（取回助手功能）
    public function getRetrieveStatus()
    {
        $battleRule = new BattleRule();
        $player_id = input('post.player_id');
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);

        $list = Db::table('player_skins')
            ->where(['player_id'=>$player_id,'status'=>2])
            ->where('state','in' ,['sending','waiting_delivery','waiting_receive'])
            ->where('orderId','neq',null)
            ->select();

        foreach ($list as $k=>$v){
            $res = $this->orderDetail($list[$k]['orderId']);
            $res = json_decode($res,true);
//            if($list[$k]['state'] != $res['data']['statusName']){
            $state = $res['data']['statusName'];
            $orderStatus = $res['data']['status'];
            if($orderStatus == '11'){
                //卖家取消订单，皮肤退回玩家背包
                $update['state'] = $state;
                $update['status'] = 1;
//                $update['plat_order'] = '';
//                $update['steam_receive_url'] = '';
//                $update['steamAvatarAnother'] = '';
//                $update['steamCreateTimeAnother'] = '';
//                $update['steamNameAnother'] = '';
                Db::table('player_skins')->where('id',$list[$k]['id'])->update($update);
            }
            if($orderStatus == '3'){
                //offerId, 只有3状态下才会返回， 拼接到steam接受报价的地址 https://steamcommunity.com/tradeoffer/{trdadeOfferId}
                //卖家已发货，待收货状态，获取卖家信息显示在页面
                $offerId = $res['data']['offerInfoDTO']['transferId'];

                $tradeOfferId =  $res['data']['offerInfoDTO']['tradeOfferId'];

                $res = $this->orderStatus($offerId);
                $res = json_decode($res,true);
                $update['state'] = $state;
                $update['steam_receive_url'] = 'https://steamcommunity.com/tradeoffer/'.$tradeOfferId;
                $update['steamAvatarAnother'] = $res['data']['offerInfo']['steamAvatarAnother'];
                $update['steamCreateTimeAnother'] = date('Y-m-d H:i:s', $res['data']['offerInfo']['steamCreateTimeAnother']);
                $update['steamNameAnother'] = $res['data']['offerInfo']['steamNameAnother'];
                Db::table('player_skins')->where('id',$list[$k]['id'])->update($update);
            }
            if($orderStatus == '10'){
                //收货成功的状态
                $update['state'] = $state;
                $update['receive_time'] = currentTime();
                $update['buy_price']     = $res['data']['price'];
                $sellData = json_decode($this->sellOrderDetail($list[$k]['orderId']),true)['data'];
                $update['buy_cny_price'] = number_format(($sellData['price'] + $sellData['fee']),2,'.','');
                Db::table('player_skins')->where('id',$list[$k]['id'])->update($update);
                $battleRule->editAssets($list[$k]['player_id'],'','',$list[$k]['price'],'');
            }
//            }
        }

        $list1 = Db::table('player_skins')
            ->where(['player_id'=>$player_id,'status'=>2])
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where('state','in' ,['sending','waiting_delivery','waiting_receive'])
            ->where('orderId','neq',null)
            ->select();

        $total = Db::table('player_skins')
            ->where(['player_id'=>$player_id,'status'=>2])
            ->where('state','in' ,['sending','waiting_delivery','waiting_receive'])
            ->where('orderId','neq',null)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list1];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }


    //购买订单详情
    public function orderDetail($orderId=null,$outTradeNo=null)
    {
        $delInfo = devInfo();
        $url = '/open/order/v2/buy/detail';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN',
            'orderId'  => $orderId,
            "outTradeNo" => $outTradeNo,
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post');
        return $re;
    }

    //出售订单详情，用于查看出售价格（人民币）
    public function sellOrderDetail($orderId=null){
        $delInfo = devInfo();
        $url = '/open/order/v2/seller/detail';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN',
            'orderId'  => $orderId
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post');
        return $re;
    }


    //
    public function orderStatus($offerId=null)
    {
        $delInfo = devInfo();
        $url = '/open/offer/v1/status';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN',
            'id'  => $offerId
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post');
        return $re;
    }




    //玩家信息
    public function playerInfo()
    {
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '缺少用户id');
        }
        $playerInfo = Db::table('player')
            ->where(['flag' => 1, 'id' => $player_id])
            ->find();
        if(!$playerInfo){
            return result(0, '', '用户不存在');
        }
        $playerInfo['img'] = $playerInfo['img'] ? mainName().$playerInfo['img'] : '';
        $playerInfo['last_login_info'] = Db::table('player_login_history')
            ->where('player_id',$player_id)
            ->field('player_id,time,ip,position')
            ->order('time','desc')
            ->find();
        $playerInfo['myInviter'] = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img')
            ->join('invitation i','i.inviter_id = p.id')
            ->where('i.invitees_id',$player_id)
            ->find();
        $playerInfo['myInviter']['img'] = $playerInfo['myInviter']['img'] ? mainName().$playerInfo['myInviter']['img'] : '';
        unset($playerInfo['password']);
        if ($playerInfo) {
            return result(1, $playerInfo, '');
        } else {
            return result(0, '', '无数据');
        }
    }

    //邮箱绑定
    public function bindEmail(){
        $player_id = input('post.player_id');
        $email = input('post.email');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $result = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$result){
            return result(0, '', '邮箱格式错误');
        }
        Db::table('player')->where('id',$player_id)->setField('email',$email);
        return result(1,'' , '');
    }

    //修改手机号，接收验证码
    public function editMobileSendCode(){
        $player_id = input('post.player_id');
        $mobile = input('post.mobile');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        if(!preg_match('/^1[3456789]{1}\d{9}$/', $mobile)) {
            return result(0, '', '手机号格式不正确');
        }
        $exist = Db::table('player')
            ->where(['mobile'=>$mobile,'flag'=>1])
            ->find();
        if(!$exist){
            return result(0, '', '该手机号不存在');
        }

        $code_len = 6;
        $code = array_merge(range(1, 9));//需要用到的数字或字母
        $keyCode = array_rand($code, $code_len);//真正的验证码对应的$code的键值
        if ($code_len == 1) {
            $keyCode = array($keyCode);
        }
        shuffle($keyCode);//打乱数组
        $verifyCode = "";
        foreach ($keyCode as $key) {
            $verifyCode .= $code[$key];//真正验证码
        }

        if ($verifyCode) {
            $data['code'] = $verifyCode;
            $data['type'] = 3;//类型，1:登录，2：注册,3:修改手机号
            $data['mobile'] = trim($mobile);
            $data['create_time'] = date('Y-m-d H:i:s');
            Db::table('code')->insert($data);

            Session::set('editCode', $verifyCode);
            Session::set('mobile', $mobile);
            Session::set('EStartCodeTime', time());

            $Dxbao = new Dxbao();
            return $Dxbao->sendMsg($mobile, $verifyCode);
        }else{
            return result(0, '', '错误，稍后再试');
        }
    }

    //修改手机号
    public function editMobile(){
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $post       = input('post.');
        $mobile     = isset($post['mobile']) ? trim($post['mobile']) : '';
        $newMobile  = isset($post['new_mobile']) ? trim($post['new_mobile']) : '';
        $code       = isset($post['code']) ? trim($post['code']) : '';

        if (empty($newMobile)) {
            return result(0, '', '请输入新手机号');
        } else if (!preg_match('/^1[3456789]{1}\d{9}$/', $newMobile)) {
            return result(0, '', '手机号格式不正确');
        } else if (Db::table('player')->where(['mobile' => $newMobile, 'flag' => 1])->find()) {
            return result(0, '', '手机号已注册');
        } else if (empty($code)) {
            return result(0, '', '请入验证码');
        }

        $editCode = Session::get('editCode');
        if ($editCode != $code) {
            return result(0, '', '验证码错误');
        }
        //有效期10分钟10*60
        $EStartCodeTime = Session::get('EStartCodeTime');
        if ($EStartCodeTime) {
            //从获取验证码到现在过了多久时间（秒）
            $long = time() - $EStartCodeTime;
            if ($long > 10 * 60) {
                return result(0, '', '验证码已失效');
            }
        }

        Db::table('player')
            ->where(['mobile'=>$mobile,'flag'=>1])
            ->setField('mobile',$newMobile);
        return result(1, '', '');
    }



    //修改密码
    public function editPass(){
        $player_id = input('post.player_id');
        $password = input('post.password');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $player_pass = Db::table('player')->where(['id'=>$player_id])->value('password');
        if($player_pass == md5($password)){
            return result(0, '', '新密与旧密码相同，请重新设置');
        }else{
            Db::table('player')->where('id',$player_id)->setField('password',md5($password));
            return result(1,'' , '');
        }
    }

    //设置偏好
    //开启/关闭声音
    public function voice(){
        $player_id = input('post.player_id');
        $voice = input('post.voice');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        Db::table('player')->where('id',$player_id)->setField('voice',$voice);
        return result(1,'' , '');
    }

    //开启/关闭活动通知
    public function notice(){
        $player_id = input('post.player_id');
        $notice = input('post.notice');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        Db::table('player')->where('id',$player_id)->setField('notice',$notice);
        return result(1,'' , '');
    }

    //修改头像
    public function editHeadImage(){
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $file = $_FILES;
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('headerImage',$file,$key);
            Db::table('player')->where('id',$player_id)->setField('img',$url);
            $url = mainName().$url;
            return result(1,$url , '');
        }else{
            return result(0, '', '文件不存在');
        }
    }


    //修改昵称
    public function editNickname(){
        $player_id = input('post.player_id');
        $name = input('post.name');
        if(empty(trim($name))){
            return result(0, '', '请输入您的昵称');
        }
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        Db::table('player')->where('id',$player_id)->setField('name',$name);
        return result(1,'' , '');
    }

//    //设置推荐码
//    public function setInvital(){
//        $player_id = input('post.player_id');
//        $name = input('post.name');
//        if(empty(trim($name))){
//            return result(0, '', '请输入您的昵称');
//        }
//        if(!$player_id){
//            return result(0, '', '参数错误');
//        }
//        Db::table('player')->where('id',$player_id)->setField('name',$name);
//        return result(1,'' , '');
//    }


    //用户充值流水
    public function recharge(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $list = Db::table('recharge')
            ->where('player_id',$player_id)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('create_time','desc')
            ->select();
        $total = Db::table('recharge')
            ->where('player_id',$player_id)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //用户饰品商城，仅购买记录
    public function skinBought(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $list = Db::table('player_skins')
            ->field('id,create_time,name,img,price,order')
            ->where(['player_id'=>$player_id,'way'=>5])
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('create_time','desc')
            ->select();
        $total = Db::table('player_skins')
            ->where(['player_id'=>$player_id,'way'=>5])
            ->where('player_id',$player_id)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }


    //余额流水
    public function balanceDetail(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $list = Db::table('balance')
            ->where('player_id',$player_id)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->order('create_time','desc')
            ->select();
        $total = Db::table('balance')
            ->where('player_id',$player_id)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }


    //绑定推荐人
    public function bindInviter(){
        $player_id   = input('post.player_id');
        $invite_code = input('post.invite_code');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $create_time = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->value('create_time');
        $time = date('Y-m-d H:i:s',strtotime("-3 day"));
        if($create_time<$time){
            return result(0, '', '已超过3日有效期，无法绑定');
        }
        $inviter = Db::table('invitation')->where(['invitees_id'=>$player_id])->find();

        if($inviter){
            return result(0, '', '您已绑定推荐人');
        }
        if($invite_code){
            $exist_invite_code = Db::table('player')
                ->where(['invite_code'=>$invite_code,'flag'=>1])
                ->find();
            if(!$exist_invite_code){
                return result(0, '', '邀请码错误');
            }
        }
        //判断是否为自己的下级
        $invitees = Db::table('invitation')
            ->where(['invitees_id'=>$exist_invite_code['id'],'inviter_id'=>$player_id])
            ->find();
        if($invitees){
            return result(0, '', '绑定错误，下线用户');
        }

        $invitation['invitees_id'] = $player_id;
        $invitation['inviter_id'] = $exist_invite_code['id'];
        $invitation['source']  = '自行绑定';
        $invitation['create_time'] = currentTime();
        Db::table('invitation')->insert($invitation);
        return result(1, '', '');
    }

    //充值赠送
//    public function giveAboutRecharge(){
//        $player_id = input('post.player_id');
//        if($player_id <= 0){
//            return result(0, '', '用户信息有误');
//        }
//        $state = Db::table('player')->where('id',$player_id)->value('state');
//        $new = false;
//        if($state == 1){
//            $new = true;
//        }
//        $giveInfo = Db::table('recharge_give')->select();
//        $firstGive = Db::table('recharge_first')
//            ->where('start_time','<=',currentTime())
//            ->where('end_time','>=',currentTime())
//            ->find();
//        if($giveInfo || $firstGive){
//            $res = ['giveInfo'=>$giveInfo,'firstGive'=>$firstGive,'new'=>$new];
//            return result(1, $res, '');
//        }else{
//            $res = ['giveInfo'=>[],'firstGive'=>[],'new'=>$new];
//            return result(0, $res, '无数据');
//        }
//    }

    public function giveAboutRecharge(){
        $player_id = input('post.player_id');
        $is_alter = input('post.is_alter', false);
        if($player_id <= 0){
            return result(0, '', '用户信息有误');
        }
        $state = Db::table('player')->where('id',$player_id)->value('state');
        $new = false;
        if($state == 1){
            $new = true;
        }
        $recharge_model = Db::table('recharge_activity');

        if ($is_alter) {
            $recharge_activity = $recharge_model->order('id','desc')->where("recommend", 1)->limit(1)->find();
        } else {
            $recharge_activity = $recharge_model->select();
        }

        if( $recharge_activity){
            $res = ['recharge_activity'=>$recharge_activity,'new'=>$new];
            return result(1, $res, '');
        }else{
            $res = ['giveInfo'=>[],'firstGive'=>[],'new'=>$new];
            return result(0, $res, '无数据');
        }
    }

    public function getPlayerInfo(){
        $player_id = input('post.player_id');
        $info = Db::table('player')
            ->where(['flag'=>1,'type'=>1,'id'=>$player_id])
            ->find();
        unset($info['password']);
        unset($info['tradeUrl']);
        return result(1, ($info ? $info : []), '');
    }

    public function getActivityInfo()
    {
        $id = input('post.id');
        $info = Db::table('recharge_activity')
            ->where(['id' => $id])
            ->find();

        return result(1, ($info ? $info : []), '');
    }





}