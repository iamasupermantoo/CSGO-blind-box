<?php


namespace app\union\controller;

use Gregwar\Captcha\CaptchaBuilder;
use think\Db;

class Login
{
    public function getCaptcha(){
        $builder = new CaptchaBuilder();
        $builder->build();
        $text = $builder->getPhrase(); // 验证码文本
        $pic = $builder->inline();// base64 captcha
        $captcha['captchaId'] = $text;
        $captcha['picPath'] = $pic;
        return result(1,$captcha,'');
    }

    public function login(){
        $account   = input('post.username');
        $password  = input('post.password');
        $captcha   = input('post.captcha');
        $captchaId = input('post.captchaId');
        if($captcha != $captchaId){
            return result(0,'','验证失败');
        }
        $role = Db::table('union_role')
            ->field('id,name,account,menu,allowState')
            ->where(['account'=>$account,'password'=>md5($password),'flag'=>1])
            ->find();
        if($role){
            $role['menu']  = $role['menu'] ? unserialize($role['menu']) : '';
            $role['menu']  = array_merge($role['menu'],[]);
            $data['user']  = $role;
            $data['token'] = randStr(32);
            return result(1,$data,'登录成功');
        }else{
            return result(0,'','用户不存在或密码有误');
        }

    }
}