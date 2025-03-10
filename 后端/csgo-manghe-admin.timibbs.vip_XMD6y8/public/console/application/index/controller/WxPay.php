<?php
namespace app\index\controller;

use think\Db;
ini_set("error_reporting","E_ALL & ~E_NOTICE");
header('Content-type:text/html; Charset=utf-8');

//$mchid = '1604779035';     //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
//$appid = 'wx58bed7ba58320843'; //公众号APPID 通过微信支付商户资料审核后邮件发送
//$apiKey = 'p0rpsjamaw0trjo4wpdb4o4l2nlvhdrh';  //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
//$wxPay = new WxPay($mchid,$appid,$apiKey);
//$outTradeNo = uniqid();   //你自己的商品订单号
//$payAmount = 0.01;     //付款金额，单位:元
//$orderName = '支付测试';  //订单标题
//$notifyUrl = 'https://www.baidu.com/';   //付款成功后的回调地址(不要有问号)
//$payTime = time();   //付款时间
//$arr = $wxPay->createJsBizPackage($payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime);
//
////生成二维码
//$url = 'http://qr.liantu.com/api.php?text='.$arr['code_url'];

//echo "<img src='{$url}' style='width:300px;'>";

//dump($url);

class WxPay
{
//    protected $mchid = '1604779035';
//    protected $appid = 'wx58bed7ba58320843';
//    protected $apiKey = 'p0rpsjamaw0trjo4wpdb4o4l2nlvhdrh';
    protected $mchid = '';
    protected $appid = '';
    protected $apiKey = '';

    public function __construct()
    {
        $payInfo = Db::table('pay')
            ->where(['type'=>'weixin','flag'=>1,'status'=>1])
            ->find();
        $this->mchid = $payInfo['mch_id'];
        $this->appid = $payInfo['app_id'];
        $this->apiKey = $payInfo['api_key'];
    }

    /**
     * 发起订单
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public function pay()
    {
        $mode      = input('post.mode','weixin'); //用户选择的支付方式，支付宝/微信
        $money     = input('post.money');//充值的金额
        $player_id = input('post.player_id');
        $mobile    = input('post.mobile');
        $order_num = makeOder('C'); //创建订单号
        $coin      = $money;

        if ((float)$money<=0){
            return result(0, '', '金额有误');
        }
        //根据汇率，得出需要支付的金额
        $exchange_rate = (float)Db::table('set_exchange_rate')->value('exchange_rate');
        $money = (float)sprintf("%.2f",((float)$money * $exchange_rate));
        $player = Db::table('player')
            ->where(['id'=>$player_id,'mobile'=>$mobile])
            ->find();
        if(!$player){
            return result(0, '', '用户信息有误');
        }

//        $mode = 'zhifubao';
        //获取支付配置信息
        $payInfo = Db::table('pay')
            ->where(['mode'=>$mode,'flag'=>1])
            ->find();
        //存入充值记录
        $rechargeInfo = [
            'order_num'   => $order_num,
            'create_time' => currentTime(),
            'money'       => $money,
            'player_id'   => $player_id,
            'mobile'      => $mobile,
            'mode'        => $mode,
            'coin'        => $coin
        ];
        $rechargeId = Db::table('recharge')->insertGetId($rechargeInfo);
//        $rechargeId = 326;
        if($mode == 'weixin'){
            $url = $this->wPay($order_num,$payInfo,$payInfo['type'],$rechargeId);
            return result(1, $url, '');
        }
    }


    public function wPay($order_num=null,$payInfo=null,$type=null,$rechargeId=null,$player_id=null){
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );

        //回调地址
        $notify_url = mainName1().'index.php/index/Wx_Pay/notify';
        //订单是否已创建
        $order = Db::table('recharge')
            ->where(['id'=>$rechargeId,'order_num'=>$order_num,'status'=>1,'player_id'=>$player_id,'mode'=>'weixin'])
            ->find();
        if(!$order){
            return ['status'=>0,'msg'=>'充值发生错误，请稍后再试'];
        }
        $outTradeNo = $order_num;
        $totalFee = $order['money'];//订单金额
        $orderName = 'ZmSkins.com';
        $unified = array(
            'appid' => $config['appid'],
            'attach' => 'pay',       //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notify_url,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
//            'total_fee' => intval($totalFee * 100),    //单位 转为分
            'total_fee' => $totalFee * 100,
            'trade_type' => 'NATIVE'
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        $codeUrl = (array)($unifiedOrder->code_url);

        if(!$codeUrl[0]) exit('get code_url error');
        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => time(),
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
            "code_url" => $codeUrl[0],
        );
        $arr['paySign'] = self::getSign($arr, $config['key']);
//        $url = 'http://qr.liantu.com/api.php?text='.$arr['code_url'];
        $url = $arr['code_url'];
        return ['status'=>1,'msg'=>'操作成功','data'=>$url];
        $res['code_url'] = $url;


//        require_once "../vendor/phpqrcode/phpqrcode.php";
//        $qrcode = new \QRcode();
//        ob_start();
//        $errorLevel = "L";
//        $size = "4";
//        $qrcode->png($url,false,$errorLevel,$size);
//        $imgB = base64_encode(ob_get_contents());
//
//        $data = array(
//            'imgB' =>$imgB
//        );
//        dd($data);
//        ob_end_clean();

        return result(1,$res,'');
    }


    public function notify()
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $postStr = file_get_contents('php://input');
//        $postStr = '<xml><appid><![CDATA[wx58bed7ba58320843]]></appid>
//<attach><![CDATA[pay]]></attach>
//<bank_type><![CDATA[OTHERS]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1604779035]]></mch_id>
//<nonce_str><![CDATA[7OC8MPTcxZXuTGot]]></nonce_str>
//<openid><![CDATA[osp8U5ZjKxowrkbA_dHK_uJdT50U]]></openid>
//<out_trade_no><![CDATA[C20210112082033204564]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[CA67504A76C568DDCCC7CDD9CD1B494D]]></sign>
//<time_end><![CDATA[20210126120833]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[NATIVE]]></trade_type>
//<transaction_id><![CDATA[4200000925202101261686926339]]></transaction_id>
//</xml>';

//        dd($postStr);
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//        file_put_contents('ce-notify.log', $postStr,FILE_APPEND);

        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }
        $arr = (array)$postObj;
        $out_trade_no = $postObj->out_trade_no;
        $order = Db::table('recharge')
           ->where(['order_num'=>$out_trade_no,'mode'=>'weixin'])
           ->find();
       if(!$order){
           $str = "用户".$order['player_id']."订单不存在\n";
           file_put_contents('recharge-notify.log', $str,FILE_APPEND);
       }else{
           if($order['status'] == 3){
               $str = "订单".$out_trade_no."已支付\n";
               file_put_contents('recharge-notify.log', $str,FILE_APPEND);
           }else if ($order['status'] == 1){
               $balance = new Balance();
               $player = Db::table('player')->where(['id'=>$order['player_id'],'flag'=>1])->find();
               if(!$player){
                   $str = "用户信息错误\n";
                   file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                   exit();
               }
               $battleRule = new BattleRule();
               $battleRule->query($order['player_id']);//先查询一次，避免初始状态无数据

               $total_amount = $player['total_amount'];
               $re = $balance->opBalance($order['player_id'],$order['coin'],$total_amount,3);
               //修改缓存数据
               $order['total_amount'] = $player['total_amount'];
               $battleRule->editAssets($order['player_id'],$order['coin'],$order['coin'],'','',$order);
               //上级，邀请人id，用于充值给推荐人增加佣金，以及被推荐人充值时，增加额外的充值赠送比例
               $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
               //充值赠送
               $pay = new Pay();
               if($player['state'] == 1){
                   //是新户的情况，执行新户活动，如果返回false，表示没有活动或者活动过期，本次操作普通赠送
                   $is_true = $pay->firstRecharge($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
                   if(!$is_true){
                       $pay->give($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
                   }
               }else{
                   $pay->give($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
               }
               //新户->老用户
               Db::table('player')->where('id',$order['player_id'])->setField('state',2);
               if($re){
                   Db::table('recharge')->where(['order_num'=>$out_trade_no,'mode'=>'weixin'])->setField('status',3);
                   //查询是否有推荐人，增加推荐人的佣金明细
//                   $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                   if($inviter_id > 0){
                       $commission = new Invite();
                       $re = $commission->addCommission($inviter_id,$order['player_id'],$order['coin']);
                       if(!$re){
                           $str = "佣金记录错误\n";
                           file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                           exit();
                       }
                   }
               }else{
                   $str = "充值出错\n";
                   file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                   exit();
               }
           }
       }
        unset($arr['sign']);
        if (self::getSign($arr, $config['key']) == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $postObj;
        }
    }

    /**
     * curl get
     *
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }



    public function p()
    {
        $data = array(
            'appid' => $this->appid,
            'mch_id' => $this->mchid,
            'nonce_str' => md5(microtime() . 'weixin' . rand(100, 9999)),
            'body' => 'body',
            'out_trade_no' => '11111111112222222222',
            'fee_type' => 'CNY',
            'total_fee' => 0.01,
            'spbill_create_ip' => $_SERVER["REMOTE_ADDR"], // 或者是你服务器的ip（最好是这个）
            'time_start' => date('YmdHis'),
            'time_expire' => date('YmdHis', strtotime('+2 hours')),
            'notify_url' => self::$WeixinConfig['notifyUrl'],
            'trade_type' => 'NATIVE',
            'product_id' => 1
        );
        ksort($data);
        $str = http_build_query($data);
        $str = static::joinAPI_KEY2($str);
        $data['sign'] = strtoupper(md5(urldecode($str)));
        $xml = static::arrToXML($data);
        //请求统一下单订单接口,微信返回的xml
        $result = static::postXmlCurl($xml, self::$WeixinConfig['unifiedOrder']);
// 解析微信返回的xml
        $data = static::xmlToArr($result);
        if ($data['RETURN_CODE'] == 'SUCCESS' && $data['RESULT_CODE'] == 'SUCCESS') {
            return $data['CODE_URL'];  // 这个就是微信给返回的二维码地址
        } else {
            return '';
        }
    }



    //普通充值达标赠送对应钻石
    public function give($money=null,$player_id=null,$total_amount=null,$order_time=null){
        $activity = Db::table('recharge_activity')
            ->where('type',2)
            ->find();
        $isEffective = $this->isEffective($activity,$order_time);
        if(!$isEffective){
            return false;
        }
        if($activity){
            if($money >= $activity['money']){
                $amount = number_format((($money*$activity['billie'])/100),'2','.','');
                $total_amount = number_format($total_amount,'2','.','');
                //账户增加赠送的钻石
                $balance = new Balance();
                $balance->opBalance($player_id,$amount,$total_amount,8);
                $battleRule = new BattleRule();
                $battleRule->editAssets($player_id,'',$amount,'','');
            }
        }
    }

    //首充活动
    public function firstRecharge($money=null,$player_id=null,$total_amount=null,$order_time=null){
        //首充活动限制和充值列表相等的才行，进行判断
        $recharge = Db::table('set_charge')->field('money')->select();
        $times = 0;
        foreach ($recharge as $k=>$v){
            if($recharge[$k]['money'] == $money){
                $times++;
            }
        }
        if($times == 0){
            return false;
        }
        //查询目前是否在活动时间内
        $activity = Db::table('recharge_activity')->where('type',1)->find();
        $isEffective = $this->isEffective($activity,$order_time);
        if(!$isEffective){
            return false;
        }
        if(($activity['start_time'] <= $order_time) && ($activity['end_time'] >= $order_time)){
            //活动范围内;
            //账户增加赠送的钻石
            $amount = number_format((($money*$activity['billie'])/100),'2','.','');
            $balance = new Balance();
            $balance->opBalance($player_id,$amount,$total_amount,8);
            $battleRule = new BattleRule();
            $battleRule->editAssets($player_id,'',$amount,'','');
            return true;
        }else{
//            echo '活动已过期';
            return false;
        }
    }

    //判断活动当前是否在活动期限内
    public function isEffective($info=null,$time=null){
        if($info['start_time'] && $info['end_time']){
            if(($time>=$info['start_time']) && ($time<=$info['end_time'])){
                return true;
            }else{
                return false;
            }
        }
        if(!$info['start_time'] && !$info['end_time']){
            return true;
        }
        if($info['start_time'] && !$info['end_time']){
            if($time>=$info['start_time']){
                return true;
            }else{
                return false;
            }
        }
        if(!$info['start_time'] && $info['end_time']){
            if($time<=$info['end_time']){
                return true;
            }else{
                return false;
            }
        }
    }


}