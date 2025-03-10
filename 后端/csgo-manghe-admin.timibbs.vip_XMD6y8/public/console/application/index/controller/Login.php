<?php


namespace app\index\controller;


use think\Db;
use think\facade\Session;

class Login
{
    public function login(){
        $data = input('post.');
        if(isset($data['type']) && ($data['type'] == 'login_steam')){
            session_write_close();
            $this->login_steam();
            return 'Lucky';
        }
        $account  = input('post.account');
        $password = input('post.password');
        //1.过滤内容中html标记
        $account=strip_tags($account);
        //2.转换成HTML实体
        $account=htmlentities($account);
        if(trim(empty($account))){
            return result(0,'','请输入账号');
        }
        if(empty(trim($password))){
            return result(0,'','请输入密码');
        }
        $playerInfo_m = Db::table('player')
            ->where(['mobile'=>$account,'password'=>md5($password)])
            ->where('flag',1)
            ->find();
        $playerInfo_e = Db::table('player')
            ->where(['email'=>$account,'password'=>md5($password)])
            ->find();
        if(!$playerInfo_e && !$playerInfo_m){
            return result(0,'','账号或者密码有误');
        }
        $_SESSION['player'] = $playerInfo_m ? $playerInfo_m : $playerInfo_e ;
        $playerInfo = [];
        if($playerInfo_m){
            $playerInfo_m['last_login_time'] = currentTime();
            Db::table('player')->update($playerInfo_m);
            unset($playerInfo_m['password']);
            unset($playerInfo_m['tradeUrl']);
            $playerInfo = $playerInfo_m;
        }elseif ($playerInfo_e){
            $playerInfo_e['last_login_time'] = currentTime();
            Db::table('player')->update($playerInfo_e);
            unset($playerInfo_e['password']);
            unset($playerInfo_m['tradeUrl']);
            $playerInfo = $playerInfo_e;
        }
        $history = [
            'player_id' =>$playerInfo['id'],
            'time'      => currentTime(),
            'ip'        => ip(),
            'position'  => get_ip_city(ip())
        ];
        Db::table('player_login_history')->insert($history);
        $playerInfo['img'] = $playerInfo['img'] ? mainName().$playerInfo['img'] : '';
        return result(1,$playerInfo,'登录成功');
    }

    public $data;
    public function steam_login(){
        if(!isset($_POST['steam_login'])){
            if($_GET){
                $_GET = [
                    "openid_ns"             => "http://specs.openid.net/auth/2.0",
                    "openid_mode"           =>  "id_res",
                    "openid_op_endpoint"    => "https://steamcommunity.com/openid/login",
                    "openid_claimed_id"     =>  "https://steamcommunity.com/openid/id/76561198980429158",
                    "openid_identity"       =>  "https://steamcommunity.com/openid/id/76561198980429158",
                    "openid_return_to"      =>  "https://89skins.com/index/Login/steam_login",
                    "openid_response_nonce" =>  "2021-05-21T09:36:32ZCX+F/drqEjn93BkQQwjPDtTlgIY=",
                    "openid_assoc_handle"   =>  "1234567890",
                    "openid_signed"         => "signed,op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle",
                    "openid_sig"            =>  "Gp0xBrUR2aLSe2+Jxv6GVkDXjrs=",
                ];
                $steamId = substr(strrchr(rtrim($_GET['openid_claimed_id'], '/'), '/'), 1);
                //如果数据库存在有相同steamId的用户，则将直接登录有该steamId的账号，没有则使用steamId新建账号
                $player = Db::table('player')
                    ->where(['steamId'=>$steamId,'flag'=>1])
                    ->find();
                if($player){
                    $last_login_time = currentTime();
                    Db::table('player')->where(['steamId'=>$steamId,'flag'=>1])->setField('last_login_time',$last_login_time);
                    unset($player['password']);
                    unset($player['tradeUrl']);
                    $player['last_login_time'] = $last_login_time;
                    $history = [
                        'player_id' =>$player['id'],
                        'time'      => currentTime(),
                        'ip'        => ip(),
                        'position'  => get_ip_city(ip())
                    ];
                    Db::table('player_login_history')->insert($history);
                    $player['img'] = $player['img'] ? mainName().$player['img'] : '';
                    return redirect('https://89skins.com/#/Index',['id'=>$player['id']],302);
//                    return result(1,$player,'登录成功');
                    return;
                }else{
                    $delInfo = devInfo();
                    $url = '/open/user/steam-info/';
                    $request_url = $delInfo['requestHost'] . $url;
                    $params = [
                        'app-key' => $delInfo['apiKey'],
                        'language' => 'zh_CN',
                        'appId'    => 730,
                        'steamId'  => $steamId,
                        'tradeUrl' => '',
                        'type'     => 1,//不同检测场景，1为购买，2为出售,示例值(1)
                    ];
                    $url = $request_url . '?' . http_build_query($params);
                    $re_query = httpRequest($url, 'get','');
                    $re_query = json_decode($re_query,true);
                    if($re_query['success'] == false){
                        return result(0, '', $re_query['errorMsg']);
                    }
                    $steamInfo = $re_query['data']['steamInfo'];

                    $insertData['name'] = $steamInfo['nickName'];
                    $insertData['img'] = $steamInfo['avatar'];
                    $insertData['steamId'] = $steamInfo['steamId'];
                    $insertData['mobile']       = '';
                    $insertData['password']     = '';
                    $insertData['total_amount'] = 0;
                    $insertData['create_time']  = date('Y-m-d H:i:s');
                    $register = new Register();
                    $insertData['invite_code']  = $register->makeCode(6);
                    $insertData['invite_url']   = $register->makeInviteUrl($insertData['invite_code']);
                    $insertData['union'] = 0;
                    $player_id = Db::table('player')->insertGetId($insertData);
                    $insertData['player_id'] = $player_id;
                    return result(1, $insertData, '登录成功');
                }

            }
        }

        //已登录用户跳转登录和注册页时跳转首页
        if(isset($this->data['member']['memberId'])){
            return redirect("/");
        }

        if (isset($_POST['steam_login'])){
//        if (isset($_GET['steam_login'])){
            //steam第三方
            @require_once dirname(__FILE__) . '/../../../public/plugins/steam/steamauth.php';
            dd(1122111);
        }

        if(isset($_SESSION['steamid']))
        {
            @require_once dirname(__FILE__) . '/../../../public/plugins/steam/userInfo.php';
            $this->data['steamid'] = $steamprofile['steamid'];
            $this->data['steamprofile'] = json_encode($steamprofile,true);
        }
        dd($authUrl);
        dd($this->data);
        return view('u7buy.signin')
            ->with($this->data);

    }

    //找回密码
    public function findPassword(){
        $post     = input('post.');
        $account  = isset($post['account']) ? trim($post['account']) : '';
        $code     = isset($post['code']) ? trim($post['code']) : '';
        $password = isset($post['password']) ? trim($post['password']) : '';
        //1.过滤内容中html标记
        $account=strip_tags($account);
        //2.转换成HTML实体
        $account=htmlentities($account);
//        $account = 15881150667;
//        $code = 215863;
//        $password = 123456;
        if (empty($account)) {
            return result(0, '', '请输入手机号');
//        } else if (!preg_match('/^1[3456789]{1}\d{9}$/', $account)) {
//            return result(0, '', '手机号格式不正确');
        } else if (!preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/', $account)) {
            return result(0, '', '邮箱格式不正确');
        }else if (empty($code)) {
            return result(0, '', '请入验证码');
        } else if (empty($password)) {
            return result(0, '', '请入验密码');
        }

        $findCode = Session::get('findCode');
        if ($findCode != $code) {
            return result(0, '', '验证码错误');
        }
        //有效期10分钟10*60
        $FStartCodeTime = Session::get('FStartCodeTime');
        if ($FStartCodeTime) {
            //从获取验证码到现在过了多久时间（秒）
            $long = time() - $FStartCodeTime;
            if ($long > 10 * 60) {
                return result(0, '', '验证码已失效');
            }
        }
        $re = Db::table('player')
            ->where(['email'=>$account,'flag'=>1,'password'=>md5($password)])
            ->find();
        if($re){
            return result(0, '', '新密码与旧密码相同');
        }
        Db::table('player')->where(['email' => $account, 'flag' => 1])->setField('password', md5($password));
        Session::set('RegisterCode', '');
        Session::set('mobile', '');
        Session::set('RStartCodeTime', '');
        return result(1, '', '操作成功');
    }

    //找回密码获取验证码
    public function sendCodeByFindPass(){
        $account  = input('post.account');
        //1.过滤内容中html标记
        $account=strip_tags($account);
        //2.转换成HTML实体
        $account=htmlentities($account);
        if(trim(empty($account))){
            return result(0,'','请输入账号');
        }
//        $playerInfo_m = Db::table('player')
//            ->where(['mobile'=>$account])
//            ->where('flag',1)
//            ->find();
//        if(!$playerInfo_m){
//            return result(0,'','账号不存在');

        $playerInfo_e = Db::table('player')
            ->where(['email'=>$account])
            ->find();
        if(!$playerInfo_e){
            return result(0,'','账号不存在');
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
            $data['type'] = 3;//类型，1:登录，2：注册，3；找回密码
            $data['mobile'] = trim($account);
            $data['create_time'] = date('Y-m-d H:i:s');
            Db::table('code')->insert($data);

            Session::set('findCode', $verifyCode);
            Session::set('mobile', $account);
            Session::set('FStartCodeTime', time());

            $email = new Email();
            return $email->sendMsg($account, $verifyCode);
//            $Dxbao = new Dxbao();
//            return $Dxbao->sendMsg($account, $verifyCode);
        }
    }


}