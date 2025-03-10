<?php


namespace app\index\controller;


use think\Db;

class Dxbao
{
    public function sendMsg($phone, $verifyCode){
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $dxb = Db::table('set_dxb')->where(['flag'=>1,'status'=>1])->find();
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $user = $dxb['account']; //短信平台帐号
        $pass = md5($dxb['password']); //短信平台密码
        $content = '【csgo盲盒】您的验证码为'.$verifyCode.'，在10分钟内有效。';//要发送的短信内容
        $phone = $phone;
        $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
        $result = file_get_contents($sendurl);
        return result(1, '', $statusStr[$result]);
    }
}