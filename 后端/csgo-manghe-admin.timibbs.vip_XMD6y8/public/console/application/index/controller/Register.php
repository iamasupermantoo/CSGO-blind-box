<?php


namespace app\index\controller;

use think\Db;
use think\facade\Session;

class Register
{

    //注册获取验证码
    public function getCode()
    {
        $account = input('post.account');
        $_SESSION['RegisterCode'] = '';
        if (empty(trim($account))) {
            return result(0, '', '请输入手机号');
        } else if (!preg_match('/^1[3456789]{1}\d{9}$/', $account)) {
            return result(0, '', '手机号格式不正确');
        } else if (Db::table('player')->where(['mobile' => $account, 'flag' => 1])->find()) {
            return result(0, '', '用户已存在');
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
            $data['type'] = 2;//类型，1:登录，2：注册
            $data['mobile'] = trim($account);
            $data['create_time'] = date('Y-m-d H:i:s');
            Db::table('code')->insert($data);

            Session::set('RegisterCode', $verifyCode);
            Session::set('mobile', $account);
            Session::set('RStartCodeTime', time());

            $Dxbao = new Dxbao();
            return $Dxbao->sendMsg($account, $verifyCode);
        }
    }


    //校验验证码 + 注册 + 登录
    public function checkCode()
    {
        $post     = input('post.');
        $account  = isset($post['account']) ? trim($post['account']) : '';
        $code     = isset($post['code']) ? trim($post['code']) : '';
        $password = isset($post['password']) ? trim($post['password']) : '';
        $invite_code = isset($post['invite_code']) ? trim($post['invite_code']) : '';
        $type     = isset($post['type']) ? trim($post['type']) : '';

        if (empty($account)) {
            return result(0, '', '请输入手机号');
        } else if (!preg_match('/^1[3456789]{1}\d{9}$/', $account)) {
            return result(0, '', '手机号格式不正确');
        } else if (Db::table('player')->where(['mobile' => $account, 'flag' => 1])->find()) {
            return result(0, '', '手机号已注册');
        } else if (empty($code)) {
            return result(0, '', '请入验证码');
        } else if (empty($password)) {
            return result(0, '', '请入验密码');
        }

        $inviter = [];
        if(trim($invite_code)){
            $inviter = Db::table('player')
                ->field('id,mobile,union,invite_code')
                ->where(['invite_code'=>$invite_code,'flag'=>1])
                ->find();
            $exist_invite_code = $inviter['invite_code'];
            if(empty($exist_invite_code)){
                return result(0, '', '邀请码错误');
            }
        }

        $RegisterCode = Session::get('RegisterCode');
        
        if ($RegisterCode != $code) {
            return result(0, '', '验证码错误');
        }
        //有效期10分钟10*60
        $RStartCodeTime = Session::get('RStartCodeTime');
        if ($RStartCodeTime) {
            //从获取验证码到现在过了多久时间（秒）
            $long = time() - $RStartCodeTime;
            if ($long > 10 * 60) {
                return result(0, '', '验证码已失效');
            }
        }

        $insertData['mobile']       = $account;
        $insertData['name']         = $this->newStar($account);
        $insertData['password']     = md5($password);
        $insertData['total_amount'] = 0;
        $insertData['create_time']  = date('Y-m-d H:i:s');
        $insertData['img']  = $this->getFilePath();
        $insertData['invite_code']  = $this->makeCode(6);
        $insertData['invite_url']   = $this->makeInviteUrl($insertData['invite_code']);
        $insertData['union']        = isset($inviter['union']) ? $inviter['union'] : '';//自动入公会
        $player_id = Db::table('player')->insertGetId($insertData);
        if($invite_code){
            $inviter_id = Db::table('player')
                ->where(['invite_code'=>$invite_code,'flag'=>1,'type'=>1])
                ->value('id');
            $invitation = [
                'inviter_id'  => $inviter_id,
                'invitees_id' => $player_id,
                'create_time' => currentTime(),
                'source'      => ($type == 'input') ? '邀请码注册' : '邀请链接注册'
            ];
            Db::table('invitation')->insert($invitation);
        }

        if ($player_id) {
            Session::set('RegisterCode', '');
            Session::set('mobile', '');
            Session::set('RStartCodeTime', '');
            $insertData['player_id'] = $player_id;
            return result(1, $insertData, '注册成功');
        } else {
            return result(0, '', '注册失败');
        }
    }


    public function getFilePath(){
        $arr = [];//存放文件名
        $path = ROOT_PATH . 'public/' . 'images/user';//路径
        //创建文件夹
        if (!is_dir(ROOT_PATH . 'public/' . 'images/user/')) {
            mkdir(ROOT_PATH . 'public/' . 'images/user/', 0777, true);
        }
        $handler = opendir($path);//当前目录中的文件夹下的文件夹
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                array_push($arr, $filename);
            }
        }
        $length = count($arr);
        if($length>0){
            $rand = rand(0,($length-1));
            $filename = $arr[$rand];
            //拼接一个头像地址
            $headImagePath = '/images/user/'.$filename;
            return $headImagePath;
        }
    }

    //生成邀请码
    public function makeCode($len=null, $chars=null){
        if (is_null($chars)){
//            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $chars = "0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        $re = Db::table('player')
            ->where(['invite_code'=>$str,'flag'=>1,'type'=>1])
            ->find();
        if($re){
            $this->makeCode($len);
        }else{
            return $str;
        }
    }

    //生成邀请链接
    public function makeInviteUrl($inviteCode){
        // $inviteCode = 'MYRczEphbi';
        $url = '/index/invite/reg?code='.$inviteCode;
        return $url;
    }
    
    
    public function newStar($tel=null){
        //1.字符串截取法
        $new_tel = substr($tel, 0, 3).'****'.substr($tel, 7);
        return $new_tel;
    }




}