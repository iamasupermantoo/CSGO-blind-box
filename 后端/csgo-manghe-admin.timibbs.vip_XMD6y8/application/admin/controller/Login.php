<?php


namespace app\admin\controller;


use think\Db;

class Login
{
    public function login(){
        $account  = input('post.account');
        $password = input('post.password');
        // echo md5('Nswn4jDm4.');
        $exist = Db::table('admin')
            ->where(['account'=>$account,'flag'=>1])
            ->find();
        if(!$exist){
            return result(0, '', '账号不存在');
        }
        $admin = Db::table('admin')
            ->where(['account'=>$account,'password'=>md5($password),'flag'=>1])
            ->find();
        
        
        if($admin){
            $_SESSION['admin'] = serialize($admin);
            return result(1, '', '登录成功');
        }else{
            return result(0, '', '账号或密码有误');
        }
    }
}