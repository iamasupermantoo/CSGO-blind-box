<?php


namespace app\index\controller;


use think\Db;
ini_set("error_reporting","E_ALL & ~E_NOTICE");
class Pay
{
    //是否沙盒环境
    public $is_sandbox = false;
    //沙盒地址
    private $sandurl = 'https://openapi.alipaydev.com/gateway.do';
    //正式地址
    private $url =     'https://openapi.alipay.com/gateway.do';
    //商户appid
    private $appid ;
    //商户应用私钥
    private $rsaPrivateKey;
    //商户支付宝公钥 验签使用
    private $alipayPublicKey;
    private $charset = 'utf-8';
    private $payInfo;

    public function __construct(){
        $payInfo = Db::table('pay')
            ->where(['type'=>'alipay','flag'=>1,'status'=>1])
            ->find();
        $this->appid = $payInfo['app_id'];
        $this->payInfo = $payInfo;
    }

    public function recharge()
    {
        //
        $mode = input('post.mode'); //用户选择的支付方式，支付宝/微信
        $money = input('post.money');//充值的金额（钻）
        $player_id = input('post.player_id');
        $mobile = input('post.mobile');
        $order_num = makeOder('C'); //创建订单号
        $coin = $money;

        if ((float)$money <= 0) {
            return result(0, '', '金额有误');
        }

        //根据汇率，得出需要支付的金额
        $exchange_rate = (float)Db::table('set_exchange_rate')->value('exchange_rate');
        $money = (float)sprintf("%.2f", ((float)$money * $exchange_rate));
        $player = Db::table('player')
            ->where(['id' => $player_id, 'mobile' => $mobile])
            ->find();

//        $money = 0.01;
        if (!$player) {
            return result(0, '', '用户信息有误');
        }

        //获取支付配置信息
//        $payInfo = Db::table('pay')->where('mode',$mode)->find();
        $payInfo = $this->payInfo;
        //存入充值记录
        $rechargeInfo = [
            'order_num' => $order_num,
            'create_time' => currentTime(),
            'money' => $money,
            'player_id' => $player_id,
            'mobile' => $mobile,
            'mode' => $mode,
            'coin' => $coin
        ];
        $rechargeId = Db::table('recharge')->insertGetId($rechargeInfo);
        $re = [];
        if ($mode == 'zhifubao') {  // 支付宝
            $re = $this->zhiPay($order_num, $payInfo, $payInfo['type'], $rechargeId, $player_id);
//            $re = $this->goskingpay($order_num, $payInfo, $payInfo['type'], $rechargeId, $player_id, $mode);
        } else if ($mode == 'weixin') {
            $wx = new WxPay();
            $re = $wx->wPay($order_num, $payInfo, $payInfo['type'], $rechargeId, $player_id);
        }
        return json($re);
    }

    //支付宝
    public function zhiPay($order_num=null,$payInfo=null,$type=null,$rechargeId=null,$player_id=null){
        //回调地址
        $notify_url = mainName1().'index/Pay/notify';
        //是否是沙箱测试
        if($this->is_sandbox){
            $url = $this->sandurl;
        }else {
            $url = $this->url;
        }
        //订单是否已创建
        $order = Db::table('recharge')
            ->where(['id'=>$rechargeId,'order_num'=>$order_num,'status'=>1,'player_id'=>$player_id,'mode'=>'zhifubao'])
            ->find();
        if(!$order){
            return ['status'=>0,'msg'=>'充值发生错误，请稍后再试'];
        }
        //商户appid
        $this->appid = $payInfo['app_id'];
        //商户 支付宝公钥 验签使用,一般较短
        $this->alipayPublicKey = $payInfo['public_key'];
        //商户 应用私钥 一般较长，尾部带’=‘号
        $this->rsaPrivateKey = $payInfo['private_key'];
        //请求参数的集合 json_encode
        $biz_content = [
            'out_trade_no' => $order_num,//订单号
            //'seller_id' => ''
            'total_amount' => $order['money'],//订单金额
            //'discountable_amount' => '',
            'subject' => 'ZmSkins.com',//标题
            //'goods_detail' => '',
            //'body' => $order['body'],
            //'operator_id' => '',
            //'store_id' => '',
            //'disable_pay_channels' => '',
            //'enable_pay_channels' => '',
            //'terminal_id'=> '',
            //'extend_params' => '',
            //'timeout_express' => '',
            //'settle_info' => '',
            //'business_params' => '',
            //'qr_code_timeout_express' => '',
        ];
        //参数
        $param = [
            'app_id' => $payInfo['app_id'],					  //支付宝分配给开发者的应用ID
            'method' => 'alipay.trade.precreate',			  //接口名称
//            'format' => 'JSON',						      //紧支持JSON
            'charset' => 'utf-8',					          //请求使用的编码格式
            'sign_type' => 'RSA2',						      //商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2
            'sign' => '',								      //商户请求参数的签名串
            'timestamp' => currentTime(),                     //发送请求的时间，格式"yyyy-MM-dd HH:mm:ss"
            'version' => '1.0',								  //调用的接口版本，固定为1.0
            'notify_url' => $notify_url,					  //支付宝服务器主动通知商户服务器里指定的页面http/https路径
//            'return_url' => $return_url,
            //'app_auth_token' => '',						  //app_auth_token
            'biz_content' => json_encode($biz_content),		  //请求参数的集合，最大长度不限，除公共参数外所有请求参数都必须放在这个参数中传递，具体参照各产品快速接入文档
        ];

        //组合生成签名参数
        $signdata = [];
        $signdata['app_id']      = $param['app_id'];
        $signdata['method']      = $param['method'];
        $signdata['charset']     = $param['charset'];
        $signdata['sign_type']   = $param['sign_type'];
        $signdata['timestamp']   = $param['timestamp'];
        $signdata['version']     = $param['version'];
        $signdata['notify_url']  = $param['notify_url'];
        $signdata['biz_content'] = $param['biz_content'];
        //生成签名
        $sign = $this->generateSign($signdata, 'RSA2');
        $param['sign'] = $sign;
        $content = $this->curlPost($url,$param);
        //file_put_contents('alipay.log', $content, FILE_APPEND);
        $return  = json_decode($content, true);
        $return = $return['alipay_trade_precreate_response'];
        if($return['code'] == 10000){
            //二维码地址
            $url = $return['qr_code'];
            return ['status'=>1,'msg'=>'操作成功','data'=>$url];
        }else {
            return ['status'=>0,'msg'=>'商户信息错误，请联系客服!'];
//            $this->error('商户信息错误，请联系客服！');//出错核实商户appid  商户应用私钥，支付宝公钥
        }
    }

    // 畅想支付宝
    public function cxpay($order_num = null, $payInfo = null, $type = null, $rechargeId = null, $player_id = null, $goods_num = 5)
    {

        //回调地址
        $notify_url = mainName1() . 'index/Pay/cxkapay_notify';

        $cxka_api_key = '51e2aeadb52c9dfe9d0994b4efb9585a';
        $cxka_api_secret = 'ecb8462824ae8f28bc6b4775413f05035k6qhab51u';
        $cxka_domain = 'https://www.gosking.net';
        $api_pay_url = "https://payapi.cxka.com/api/v3/create_pay";

        $goods_id = 32797;
        $is_mobile = 0;
        //生成签名
        $sign = strtolower(md5("app_key={$cxka_api_key}&api_domain={$cxka_domain}&goods_id={$goods_id}&goods_num={$goods_num}&order_id={$order_num}&is_mobile={$is_mobile}&key={$cxka_api_secret}"));

        $pay_url = $api_pay_url . '?app_key=' . $cxka_api_key . '&goods_id=' . $goods_id . '&goods_num=' . $goods_num . '&is_mobile=' . $is_mobile . '&order_id=' . $order_num . '&api_domain=' . $cxka_domain . '&sign=' . $sign;

        if (!empty($notify_url)) {
            $pay_url .= '&callback_url=' . $notify_url;
        }
        $pay_url .= '&ip_address=' . ip();

        //订单是否已创建
        $order = Db::table('recharge')
            ->where(['id' => $rechargeId, 'order_num' => $order_num, 'status' => 1, 'player_id' => $player_id, 'mode' => 'cxpay'])
            ->find();

        if (!$order) {
            return ['status'=>0,'msg'=>'充值发生错误，请稍后再试'];
        }

        $content = httpRequest($pay_url, 'get');
        $return = json_decode($content, true);

        if ($return['code'] != 200) {
            return ['status' => 0, 'msg' => $return['msg']];
        }

        $mobile_pay_url = $return['data']['pay_url'] . '&pid=' . $return['data']['pay_list'][0]['id'];
        $pc_pay_url = $return['data']['qr_code'] . '?url=' . $return['data']['pay_list'][0]['pay_url'];

        //组合生成签名参数
        return ['status' => 1, 'msg' => 'tz', 'data' => $pc_pay_url];
    }


    //呱呱支付宝
    public function dspay_zfb($order_num = null, $payInfo = null, $type = null, $rechargeId = null, $player_id = null)
    {
        //回调地址
        $notify_url = mainName1() . 'index/Pay/ds_pay_notify';
        //是否是沙箱测试
        if ($this->is_sandbox) {
            $url = $this->sandurl;
        } else {
            $url = $this->url;
        }
        //订单是否已创建
        $order = Db::table('recharge')
            ->where(['id' => $rechargeId, 'order_num' => $order_num, 'status' => 1, 'player_id' => $player_id, 'mode' => 'zhifubao'])
            ->find();
        if (!$order) {
            return ['status' => 0, 'msg' => '充值发生错误，请稍后再试'];
        }

        //组合生成签名参数
        return ['status' => 1, 'msg' => 'tz', 'data' => 'http://www.gosking.net/dspay?money=' . $order['money'] . '&orderid=' . $order_num . '&no=' . $notify_url];
    }


    /**
     * 建立跳转请求表单
     * @param string $url 数据提交跳转到的URL
     * @param array $data 请求参数数组
     * @param string $method 提交方式：post或get 默认post
     * @return string 提交表单的HTML文本
     */
    protected function buildRequestForm($url, $data, $method = 'post')
    {
        $html = "<form id='requestForm' name='requestForm' action='" . $url . "' method='" . $method . "'>";
        while (list ($key, $val) = each($data)) {
            $html .= "<input type='hidden' name='" . $key . "' value='" . $val . "' />";
        }
        $html .= "<input type='submit' value='确定' style='display:none;'></form>";

        $html .= "<script>document.forms['requestForm'].submit();</script>";
        return $html;
    }

    public function gspay()
    {
        $money = input('get.amount');
        $mode = input('get.mode');
        $order_num = input('get.orderno');

        //回调地址
        $notify_url = mainName1() . 'index/Pay/goskingpay_notify';
        $return_url = mainName1();

        include_once $_SERVER['DOCUMENT_ROOT'] . '/../extend/gspay/epay.config.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/../extend/gspay/lib/epay_submit.class.php';

        $paycode = $mode == 'qq' ? 'qqpay' : 'alipay';   //自己商户后台 接口处 看id

        // 以下部分不用修改
        $param = array(
            'pid' => trim($alipay_config['partner']),
            'type' => $paycode,
            'notify_url' => $notify_url,
            'return_url' => $return_url,
            'out_trade_no' => $order_num,
            'name' => 'gosking',
            'money' => $money,
            'sitename' => 'gosking',
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($param);
        echo $html_text;
    }



    // gosking支付
    public function goskingpay($order_num = null, $payInfo = null, $type = null, $rechargeId = null, $player_id = null, $mode)
    {
        //订单是否已创建
        $order = Db::table('recharge')
            ->where(['id' => $rechargeId, 'order_num' => $order_num, 'status' => 1, 'player_id' => $player_id, 'mode' => $mode])
            ->find();
        if (!$order) {
            return ['status' => 0, 'msg' => '充值发生错误，请稍后再试'];
        }

        $params = [
            'amount' => $order['money'],
            'orderno' => $order_num,
            'mode' => $mode,
        ];

        // 支付地址
        $payurl = mainName1() . '/index/Pay/gspay?' . http_build_query($params);

        //组合生成签名参数
        return ['status' => 1, 'msg' => 'tz', 'data' => $payurl];
    }

    public function generateSign($params, $signType = "RSA")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }


    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    /**
     * 获取链接参数
     * @param $url
     * @return array
     */
    protected function getUrlParams($url)
    {
        $params = [];
        if (empty($url)) return $params;
        $urlArr = explode('?', $url);
        if (!empty($urlArr[1])) {
//        $paramsArr = explode('&', $urlArr[1]);
//        foreach ($paramsArr as $v) {
//            $item = explode('=', $v);
//            $params[$item[0]] = $item[1];
//        }
            parse_str($urlArr[1], $params);
        }
        return $params;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }

    public function curlPost($url = '', $postData = '', $options = array())
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

    /**
     *
     * @DateTime 2018-11-16 12:05:26
     * 签名函数
     *
     * @param      <type>  $data      The data
     * @param      string  $signType  The sign type
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    protected function sign($data, $signType = "RSA") {
        $priKey = $this->rsaPrivateKey ? $this->rsaPrivateKey : Db::table('pay')->where('mode','zhifubao')->value('private_key');
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    public function goskingpay_notify($data = null)
    {
        header("Content-Type: text/html;charset=utf-8");//防止中文乱码


        include_once $_SERVER['DOCUMENT_ROOT'] . '/../extend/gspay/epay.config.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/../extend/gspay/lib/epay_notify.class.php';

        $data = $_REQUEST;

        file_put_contents('goskingpay_notify.log', '请求参数:' . var_export($data, true) . '\r\n', FILE_APPEND);

        // 计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_GET['out_trade_no'];

            //51云支付2.0交易号

            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];

            //支付方式
            $type = $_GET['type'];


            if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，51云支付即时到账支付系统发送该交易状态通知


                //查询待支付状态的订单
                $order = Db::name("recharge")->where('order_num', $out_trade_no)
                    ->where('status', 1)
                    ->find();

                if (!empty($order)) {

                    $time = time();
                    Db::startTrans();
                    try {
                        $player = Db::table('player')->where(['id' => $order['player_id'], 'flag' => 1])->find();
                        if (!$player) {
                            $str = "zfb-用户信息错误" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }
                        $order = Db::name("recharge")->where('order_num', $out_trade_no)->find();
                        if ($order['status'] == 3) {
                            $str = "zfb-重复操作" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }

                        //修改订单状态
                        Db::name("recharge")
                            ->where(['order_num' => $out_trade_no, 'status' => 1])
                            ->setField('status', 3);
                        $battleRule = new BattleRule();
                        $battleRule->query($order['player_id']);//先查询一次，避免初始状态无数据
                        //给账户增加金额
                        $balance = new Balance();
                        $total_amount = Db::table('player')->where(['id' => $order['player_id'], 'flag' => 1])->value('total_amount');
                        $re = $balance->opBalance($order['player_id'], $order['coin'], $total_amount, 3);
                        //修改缓存数据
                        $order['total_amount'] = $player['total_amount'];
                        $battleRule->editAssets($order['player_id'], $order['coin'], $order['coin'], '', '', $order);
                        //上级，邀请人id，用于充值给推荐人增加佣金，以及被推荐人充值时，增加额外的充值赠送比例
                        $inviter_id = Db::table('invitation')->where('invitees_id', $order['player_id'])->value('inviter_id');

                        //充值赠送
                        if ($player['state'] == 1) {
                            //是新户的情况，执行新户活动，如果返回false，表示没有活动或者活动过期，本次操作普通赠送
                            $is_true = $this->firstRecharge($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                            if (!$is_true) {
                                $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                            }
                        } else {
                            $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                        }
                        //新户->老用户
                        Db::table('player')->where('id', $order['player_id'])->setField('state', 2);
                        if ($re) {
                            Db::table('recharge')->where(['order_num' => $data['orderid'], 'mode' => 'zhifubao'])->setField('status', 3);
                            //查询是否有推荐人，增加推荐人的佣金明细
//                            $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                            if ($inviter_id > 0) {
                                $commission = new Invite();
                                $re = $commission->addCommission($inviter_id, $order['player_id'], $order['coin']);
                                if (!$re) {
                                    $str = "zfb-佣金记录错误" . currentTime() . "\n";
                                    file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                                    exit();
                                }
                            }
                        } else {
                            $str = "zfb-充值出错" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        print_r($e->getTraceAsString());
                        file_put_contents('goskingpay_notify.log', 'ckpay-支付通知数据库操作错误:' . currentTime() . $e->getMessage() . '\r\n', FILE_APPEND);
                        exit;
                    }

                    echo "success";        //请不要修改或删除
                    exit;
                } else {
                    echo 'fail';
                    file_put_contents('goskingpay_notify.log', '未找到相应订单' . currentTime() . '\r\n', FILE_APPEND);
                    exit;
                }

                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            }
        }  else {
            //验证失败
            echo "fail";
            file_put_contents('goskingpay_notify.log', '签名失败' . currentTime() . '\r\n', FILE_APPEND);
        }
    }

    public function cxkapay_notify($data = null)
    {
        header("Content-Type: text/html;charset=utf-8");//防止中文乱码

        $data = $_REQUEST;

        $cxka_api_secret = 'ecb8462824ae8f28bc6b4775413f05035k6qhab51u';

        $ori_sign = $data['sign'];
        unset($data['sign']);
        $str = "mch_id=" . $data['mch_id'] . "&total_fee=" . $data['total_fee'] . "&result_code=" . $data['result_code'] . "&trade_no=" . $data['trade_no'] . "&out_trade_no=" . $data['out_trade_no'] . "&time_end=" . $data['time_end'] . "&pay_type=" . $data['pay_type'];

        $str .= "&key=" . $cxka_api_secret;
        $string = md5($str);
        $sign = strtolower($string);

        if ($sign != $ori_sign) {
            echo 'fail';
            file_put_contents('ckpaynotify.log', 'cxpay- 签名错误:' . currentTime() . $data['result_code'] . '\r\n', FILE_APPEND);
            exit;
        }

        if ($data['result_code'] != 'SUCCESS') {
            echo 'fail';
            file_put_contents('ckpaynotify.log', 'cxpay-支付通知数据库操作错误:' . currentTime() . $data['result_code'] . '\r\n', FILE_APPEND);
            exit;
        }

        //查询待支付状态的订单
        $order = Db::name("recharge")->where('order_num', $data['out_trade_no'])
            ->where('status', 1)
            ->find();

        if (!empty($order)) {

            $time = time();
            Db::startTrans();
            try {
                $player = Db::table('player')->where(['id' => $order['player_id'], 'flag' => 1])->find();
                if (!$player) {
                    $str = "zfb-用户信息错误" . currentTime() . "\n";
                    file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                    exit();
                }
                $order = Db::name("recharge")->where('order_num', $data['out_trade_no'])->find();
                if ($order['status'] == 3) {
                    $str = "zfb-重复操作" . currentTime() . "\n";
                    file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                    exit();
                }

                //修改订单状态
                Db::name("recharge")
                    ->where(['order_num' => $data['out_trade_no'], 'status' => 1])
                    ->setField('status', 3);
                $battleRule = new BattleRule();
                $battleRule->query($order['player_id']);//先查询一次，避免初始状态无数据
                //给账户增加金额
                $balance = new Balance();
                $total_amount = Db::table('player')->where(['id' => $order['player_id'], 'flag' => 1])->value('total_amount');
                $re = $balance->opBalance($order['player_id'], $order['coin'], $total_amount, 3);
                //修改缓存数据
                $order['total_amount'] = $player['total_amount'];
                $battleRule->editAssets($order['player_id'], $order['coin'], $order['coin'], '', '', $order);
                //上级，邀请人id，用于充值给推荐人增加佣金，以及被推荐人充值时，增加额外的充值赠送比例
                $inviter_id = Db::table('invitation')->where('invitees_id', $order['player_id'])->value('inviter_id');

                //充值赠送
                if ($player['state'] == 1) {
                    //是新户的情况，执行新户活动，如果返回false，表示没有活动或者活动过期，本次操作普通赠送
                    $is_true = $this->firstRecharge($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                    if (!$is_true) {
                        $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                    }
                } else {
                    $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                }
                //新户->老用户
                Db::table('player')->where('id', $order['player_id'])->setField('state', 2);
                if ($re) {
                    Db::table('recharge')->where(['order_num' => $data['orderid'], 'mode' => 'zhifubao'])->setField('status', 3);
                    //查询是否有推荐人，增加推荐人的佣金明细
//                            $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                    if ($inviter_id > 0) {
                        $commission = new Invite();
                        $re = $commission->addCommission($inviter_id, $order['player_id'], $order['coin']);
                        if (!$re) {
                            $str = "zfb-佣金记录错误" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }
                    }
                } else {
                    $str = "zfb-充值出错" . currentTime() . "\n";
                    file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                    exit();
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                print_r($e->getTraceAsString());
                file_put_contents('ckpaynotify.log', 'ckpay-支付通知数据库操作错误:' . currentTime() . $e->getMessage() . '\r\n', FILE_APPEND);
                exit;
            }

            echo "success";
            exit;

        } else {
            var_dump('未找到相应订单');
            file_put_contents('ckpaynotify.log', '未找到相应订单' . currentTime() . '\r\n', FILE_APPEND);
            exit;
        }
    }

    public function ds_pay_notify($data=null){
        header("Content-Type: text/html;charset=utf-8");//防止中文乱码
        $data = $_REQUEST;

        //查询待支付状态的订单
        $order = Db::name("recharge")->where('order_num', $data['orderid'])
            ->where('status', 1)
            ->find();


        if(!empty($order)){
            $rst1 = $this->orderquery($order, 'TRADE_SUCCESS');
            // file_put_contents('notify.log', $rst1.'$rst1-zfb'.currentTime()."\n",FILE_APPEND);
            // var_dump($rst1);

            $time = time();
            Db::startTrans();
            try {
                $player = Db::table('player')->where(['id'=>$order['player_id'],'flag'=>1])->find();
                if(!$player){
                    $str = "zfb-用户信息错误".currentTime()."\n";
                    file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                    exit();
                }
                $order = Db::name("recharge")->where('order_num', $data['orderid'])->find();
                if($order['status'] == 3){
                    $str = "zfb-重复操作".currentTime()."\n";
                    file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                    exit();
                }
                //修改订单状态
                Db::name("recharge")
                    ->where(['order_num'=>$data['orderid'],'status'=>1])
                    ->setField('status',3);
                $battleRule = new BattleRule();
                $battleRule->query($order['player_id']);//先查询一次，避免初始状态无数据
                //给账户增加金额
                $balance = new Balance();
                $total_amount = Db::table('player')->where(['id'=>$order['player_id'],'flag'=>1])->value('total_amount');
                $re = $balance->opBalance($order['player_id'],$order['coin'],$total_amount,3);
                //修改缓存数据
                $order['total_amount'] = $player['total_amount'];
                $battleRule->editAssets($order['player_id'],$order['coin'],$order['coin'],'','',$order);
                //上级，邀请人id，用于充值给推荐人增加佣金，以及被推荐人充值时，增加额外的充值赠送比例
                $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                //充值赠送
                if($player['state'] == 1){
                    //是新户的情况，执行新户活动，如果返回false，表示没有活动或者活动过期，本次操作普通赠送
                    $is_true = $this->firstRecharge($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
                    if(!$is_true){
                        $this->give($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
                    }
                }else{
                    $this->give($order['coin'],$order['player_id'],($total_amount + $order['coin']),$order['create_time'],$inviter_id);
                }
                //新户->老用户
                Db::table('player')->where('id',$order['player_id'])->setField('state',2);
                if($re){
                    Db::table('recharge')->where(['order_num'=> $data['orderid'],'mode'=>'zhifubao'])->setField('status',3);
                    //查询是否有推荐人，增加推荐人的佣金明细
//                            $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                    if($inviter_id > 0){
                        $commission = new Invite();
                        $re = $commission->addCommission($inviter_id,$order['player_id'],$order['coin']);
                        if(!$re){
                            $str = "zfb-佣金记录错误".currentTime()."\n";
                            file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                            exit();
                        }
                    }
                }else{
                    $str = "zfb-充值出错".currentTime()."\n";
                    file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                    exit();
                }
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                file_put_contents('alipaynotify.log', 'zfb-支付通知数据库操作错误:'.currentTime() . $e->getMessage() . '\r\n', FILE_APPEND);
                exit;
            }


//                    if($order['type'] ==1 ){
//                        //发送成功短信通知
//                        $yunxin = new Yunxin;
//                        $arr = [];
//                        $arr[] = $match_member['name'];
//                        $arr[] = $match['title'];
//                        $arr[] = date("Y年m月d日H:i", $match['mhstart']);
//                        $arr[] = $match_member['idno'];
//                        $yunxin->sendSMSTemplate( 9294589, [$match_member['telephone']], $arr);
//                    }

            echo  "OK";exit;

        }else {
            var_dump('未找到相应订单');
            file_put_contents('alipaynotify.log', '未找到相应订单'.currentTime().'\r\n' , FILE_APPEND );
            exit;
        }
    }



    public function notify($data=null){
        header("Content-Type: text/html;charset=utf-8");//防止中文乱码
        $postStr = file_get_contents('php://input');
        // $postStr = 'gmt_create=2021-12-02+21%3A20%3A39&charset=GBK&seller_email=service%40bochyun.com&subject=ZmSkins.com&sign=Tms%2F3rNF%2BkjbFLq2og15Xx482Iw5%2BpOiM2%2F5%2FFSddC25dTha%2Bf1zTw%2Brs%2FtMIU4Hu4WwyMTTzFO%2Fku9QJVeOr3zPSmS3RC3SzhYVhRgKV8xSkBAzsfa5FF8hlyAnEsbeP8qkFwJOVvLqCB%2Bnd0%2Fz%2B08T4RJ3RWOUA3mbVcOQRWvRY6nh8MobapJzGS3DxVNPJ53dVeXx%2FECvIRrYNPeh5QIU%2FMWV8dx7nbb4rPowKYqyzCxI0lPtxr4Z2OVheClH8uzmeYlefiDOTezGFgXtiEQXUegsFHLnaX3BnzSJ2d%2FYqAb79FdjHiB%2Bc7B98kGcahRMFj14oqRnzV3ZiB%2BDGg%3D%3D&buyer_id=2088602105961464&invoice_amount=0.01&notify_id=2021120200222212045061465711496615&fund_bill_list=%5B%7B%22amount%22%3A%220.01%22%2C%22fundChannel%22%3A%22ALIPAYACCOUNT%22%7D%5D&notify_type=trade_status_sync&trade_status=TRADE_SUCCESS&receipt_amount=0.01&buyer_pay_amount=0.01&app_id=2021001140619973&sign_type=RSA2&seller_id=2088731759496564&gmt_payment=2021-12-02+21%3A20%3A44&notify_time=2021-12-02+21%3A20%3A45&version=1.0&out_trade_no=C20211221203548144949&total_amount=0.01&trade_no=2021120222001461465732926608&auth_app_id=2021001140619973&buyer_logon_id=970***%40qq.com&point_amount=0.00';
        file_put_contents('notify.log', $postStr.'-----$rst1-zfb'.currentTime()."\n",FILE_APPEND);
        parse_str($postStr,$data);
        //验签
        //组合验签数据
        $param = $data;
        unset($param['sign']);
        unset($param['sign_type']);
        $rst = $this->rsaCheck($param, $data['sign'] , $data['sign_type']);
        // file_put_contents('recharge-notify.log', $rst.'$rst-zfb'.currentTime()."\n",FILE_APPEND);
//        $rst = 1;
        if($rst){
            //查询待支付状态的订单
            $order = Db::name("recharge")->where('order_num', $data['out_trade_no'])
                ->where('status', 1)
                ->find();
            // var_dump($order);
            if(!empty($order)){
                $rst1 = $this->orderquery($order, 'TRADE_SUCCESS');
                file_put_contents('notify.log', $rst1.'$rst1-zfb'.currentTime()."\n",FILE_APPEND);
                // var_dump($rst1);
                if($rst1){
                    $time = time();
                    Db::startTrans();
                    try {
                        $player = Db::table('player')->where(['id'=>$order['player_id'],'flag'=>1])->find();
                        if(!$player){
                            $str = "zfb-用户信息错误".currentTime()."\n";
                            file_put_contents('recharge-notify.log', $str,FILE_APPEND);
                            exit();
                        }
                        $order = Db::name("recharge")->where('order_num', $data['out_trade_no'])->find();
                        if ($order['status'] == 3) {
                            $str = "zfb-重复操作" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }
                        //修改订单状态
                        Db::name("recharge")
                            ->where(['order_num' => $data['out_trade_no'], 'status' => 1])
                            ->setField('status', 3);
                        $battleRule = new BattleRule();
                        $battleRule->query($order['player_id']);//先查询一次，避免初始状态无数据
                        //给账户增加金额
                        $balance = new Balance();
                        $total_amount = Db::table('player')->where(['id' => $order['player_id'], 'flag' => 1])->value('total_amount');
                        $re = $balance->opBalance($order['player_id'], $order['coin'], $total_amount, 3);
                        //修改缓存数据
                        $order['total_amount'] = $player['total_amount'];
                        $battleRule->editAssets($order['player_id'], $order['coin'], $order['coin'], '', '', $order);
                        //上级，邀请人id，用于充值给推荐人增加佣金，以及被推荐人充值时，增加额外的充值赠送比例
                        $inviter_id = Db::table('invitation')->where('invitees_id', $order['player_id'])->value('inviter_id');
                        //充值赠送
                        if ($player['state'] == 1) {
                            //是新户的情况，执行新户活动，如果返回false，表示没有活动或者活动过期，本次操作普通赠送
                            $is_true = $this->firstRecharge($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                            if (!$is_true) {
                                $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                            }
                        } else {
                            $this->give($order['coin'], $order['player_id'], ($total_amount + $order['coin']), $order['create_time'], $inviter_id);
                        }
                        //新户->老用户
                        Db::table('player')->where('id', $order['player_id'])->setField('state', 2);
                        if ($re) {
                            Db::table('recharge')->where(['order_num' => $data['out_trade_no'], 'mode' => 'zhifubao'])->setField('status', 3);
                            //查询是否有推荐人，增加推荐人的佣金明细
//                            $inviter_id = Db::table('invitation')->where('invitees_id',$order['player_id'])->value('inviter_id');
                            if ($inviter_id > 0) {
                                $commission = new Invite();
                                $re = $commission->addCommission($inviter_id, $order['player_id'], $order['coin']);
                                if (!$re) {
                                    $str = "zfb-佣金记录错误" . currentTime() . "\n";
                                    file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                                    exit();
                                }
                            }
                        } else {
                            $str = "zfb-充值出错" . currentTime() . "\n";
                            file_put_contents('recharge-notify.log', $str, FILE_APPEND);
                            exit();
                        }
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        file_put_contents('alipaynotify.log', 'zfb-支付通知数据库操作错误:' . currentTime() . $e->getMessage() . '\r\n', FILE_APPEND);
                        exit;
                    }


//                    if($order['type'] ==1 ){
//                        //发送成功短信通知
//                        $yunxin = new Yunxin;
//                        $arr = [];
//                        $arr[] = $match_member['name'];
//                        $arr[] = $match['title'];
//                        $arr[] = date("Y年m月d日H:i", $match['mhstart']);
//                        $arr[] = $match_member['idno'];
//                        $yunxin->sendSMSTemplate( 9294589, [$match_member['telephone']], $arr);
//                    }

                    echo "success";
                    exit;
                } else {
                    file_put_contents('alipaynotify.log', '查询订单状态错误' . currentTime() . '\r\n', FILE_APPEND);
                }

            } else {
                file_put_contents('alipaynotify.log', '未找到相应订单' . currentTime() . '\r\n', FILE_APPEND);
                exit;
            }
        } else {
            file_put_contents('alipaynotify.log', '验签失败' . currentTime() . '\r\n', FILE_APPEND);
            exit;
        }
    }

    /**
     *
     * @DateTime 2018-11-16 12:06:12
     * 验签函数
     *
     * @param      <type>  $data        The data    带签名数据
     * @param      <type>  $sign        The sign	要校对的签名结果
     * @param      string  $type        The type
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function rsaCheck($data, $sign,$type = 'RSA'){
//        $public_key = $this->alipayPublicKey;
        $payInfo = Db::table('pay')->where('mode','zhifubao')->find();
        $public_key = $payInfo['public_key'];
        $search = [
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $public_key=str_replace($search,"",$public_key);
        $public_key=$search[0] . PHP_EOL . wordwrap($public_key, 64, "\n", true) . PHP_EOL . $search[1];
        $res=openssl_get_publickey($public_key);
        if($res)
        {
            if($type == 'RSA'){
                $result = (bool)openssl_verify($this->getSignContent($data), base64_decode($sign), $res);
            }elseif($type == 'RSA2'){
                $result = (bool)openssl_verify($this->getSignContent($data), base64_decode($sign), $res,OPENSSL_ALGO_SHA256);
            }
            openssl_free_key($res);
        }else{
            return false;
        }
        return true;
    }

    /**
     *
     * @DateTime 2018-11-16 13:39:15
     * 支付查询接口
     *
     * @param  order
     * @param  status  要验证的状态  WAIT_BUYER_PAY-交易创建等待买家付款 TRADE_CLOSED-未付款交易超时关闭或支付完成后全额退款  TRADE_SUCCESS-交易支付成功 TRADE_FINISHED-交易结束不可退款
     */
    public function orderquery($order, $status)
    {

        return true;
        $time = time();
        $url = '';
        $biz_content = [
            'out_trade_no' => $order['order_num'],
//            'trade_no' => $order['trade_no'],
            //'org_pid' => '',
        ];

        $param = [
            'app_id' => $this->appid,
            'method' => 'alipay.trade.query',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'sign' => '',
            'timestamp' => date('Y-m-d H:i:s', $time),
            'version' => '1.0',
            'biz_content' => json_encode($biz_content),
        ];

        //组合签名数组
        $signdata = [];
        $signdata['app_id'] = $param['app_id'];
        $signdata['method'] = $param['method'];
        $signdata['charset'] = $param['charset'];
        $signdata['sign_type'] = $param['sign_type'];
        $signdata['timestamp'] = $param['timestamp'];
        $signdata['version'] = $param['version'];
        $signdata['biz_content'] = $param['biz_content'];

        //生成签名
        $sign = $this->generateSign($signdata, 'RSA2');
        $param['sign'] = $sign;

        $content = $this->curlPost($this->url, $param);
        $return = json_decode($content, true);

        if ($return['alipay_trade_query_response']['code'] == 10000) {
            if ($return['alipay_trade_query_response']['trade_status'] == $status) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function chargeInfoList()
    {
        $list = Db::table('set_charge')->select();
        $exchange_rate = (float)Db::table('set_exchange_rate')->value('exchange_rate');
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['img'] = $list[$k]['img'] ? mainName() . $list[$k]['img'] : '';
                $list[$k]['rmb'] = (float)sprintf("%.2f", (($list[$k]['money'] * $exchange_rate)));
            }
            $res = ['exchange_rate' => $exchange_rate, 'list' => $list];
            return result(1, $res, '');
        } else {
            return result(1, '', '无数据');
        }
    }

    //普通充值达标赠送对应钻石
    public function give($money = null, $player_id = null, $total_amount = null, $order_time = null, $inviter_id = null)
    {
        $activity = Db::table('recharge_activity')
            ->where('type', 2)
            ->find();
        $isEffective = $this->isEffective($activity, $order_time);
        if (!$isEffective) {
            return false;
        }
        if ($activity) {
            if ($money >= $activity['money']) {
                //查询推荐人是否有额外的充值赠送比例
                $extra_gift_recharge = 0;
                if ($inviter_id > 0) {
                    $extra_gift_recharge = Db::table('player')->where('id', $inviter_id)->value('extra_gift_recharge');
                    $extra_gift_recharge = $extra_gift_recharge > 0 ? $extra_gift_recharge : 0;
                }

                $amount = number_format((($money * ($activity['billie'] + $extra_gift_recharge)) / 100), '2', '.', '');
                $total_amount = number_format($total_amount, '2', '.', '');
                //账户增加赠送的钻石
                $balance = new Balance();
                $balance->opBalance($player_id, $amount, $total_amount, 8);
                $battleRule = new BattleRule();
                $battleRule->editAssets($player_id, '', $amount, '', '');
            }
        }
    }

    //首充活动
    public function firstRecharge($money = null, $player_id = null, $total_amount = null, $order_time = null, $inviter_id = null)
    {
        //首充活动限制和充值列表相等的才行，进行判断
        $recharge = Db::table('set_charge')->field('money')->select();
        $times = 0;
        foreach ($recharge as $k => $v) {
            if ($recharge[$k]['money'] == $money) {
                $times++;
            }
        }
        if ($times == 0) {
            return false;
        }
        //查询目前是否在活动时间内
        $activity = Db::table('recharge_activity')->where('type', 1)->find();
        $isEffective = $this->isEffective($activity, $order_time);
        if (!$isEffective) {
            return false;
        }
        if (($activity['start_time'] <= $order_time) && ($activity['end_time'] >= $order_time)) {
            //查询推荐人是否有额外的充值赠送比例
            $extra_gift_recharge = 0;
            if ($inviter_id > 0) {
                $extra_gift_recharge = Db::table('player')->where('id', $inviter_id)->value('extra_gift_recharge');
                $extra_gift_recharge = $extra_gift_recharge > 0 ? $extra_gift_recharge : 0;
            }
            //活动范围内;
            //账户增加赠送的金额
            $amount = number_format((($money * ($activity['billie'] + $extra_gift_recharge)) / 100), '2', '.', '');
            $balance = new Balance();
            $balance->opBalance($player_id, $amount, $total_amount, 8);
            $battleRule = new BattleRule();
            $battleRule->editAssets($player_id, '', $amount, '', '');
            return true;
        } else {
//            echo '活动已过期';
            return false;
        }
    }

    //判断活动当前是否在活动期限内
    public function isEffective($info = null, $time = null)
    {
        if ($info['start_time'] && $info['end_time']) {
            if (($time >= $info['start_time']) && ($time <= $info['end_time'])) {
                return true;
            } else {
                return false;
            }
        }
        if (!$info['start_time'] && !$info['end_time']) {
            return true;
        }
        if ($info['start_time'] && !$info['end_time']) {
            if ($time >= $info['start_time']) {
                return true;
            } else {
                return false;
            }
        }
        if (!$info['start_time'] && $info['end_time']) {
            if ($time <= $info['end_time']) {
                return true;
            } else {
                return false;
            }
        }
    }


}