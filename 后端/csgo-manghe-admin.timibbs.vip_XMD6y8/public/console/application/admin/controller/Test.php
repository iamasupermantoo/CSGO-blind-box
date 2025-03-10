<?php


namespace app\admin\controller;


use think\Db;

class Test
{
    //操作设置
    public function test(){
        $list = Db::table('player_skins')
            ->field('id,name,price,buy_price,receive_time,orderId,state,plat_order')
            ->where('state','success')
            ->select();
        if($list){
            $user = new \app\index\controller\User();
            foreach ($list as $k=>$v){
                $orderId = $list[$k]['orderId'];
                $outTradeNo = $list[$k]['plat_order'];
                // $re = json_decode($user->orderDetail($orderId,$outTradeNo),true);
                // $buy_price = $re['data']['price'];
                // Db::table('player_skins')->where('id',$list[$k]['id'])->setField('buy_price',$buy_price);
                $re = json_decode($user->sellOrderDetail($orderId,$outTradeNo),true);
                $buy_price = $re['data']['price'] + $re['data']['fee'];
                dd($buy_price);
            }
        }else{
            return result(0,'','');
        }
        return result(1,'','');

    }

    public function token(){
        $user_name = 'admin';
        $password  = '123456';
        $time = time();
        $header = array('typ' => 'JWT');
        $array = array(
//            'iss'       =>'auth', // 权限验证作者
            'iat'       => $time, // 时间戳
            'exp'       => 3600, // token有效期，1小时
            'user_name' => $user_name, // 用户名
            'password'  => $password
        );
        $str = base64_encode(json_encode($header)).base64_encode(json_encode($array)); // 数组转成字符
        dump($str);
        $str = urlencode($str); // 通过url转码
        $information['token'] = $str;
        $information ['username'] = $user_name; // 返回用户名

        return result(1,$information,'');
    }
}