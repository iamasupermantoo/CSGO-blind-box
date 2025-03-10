<?php


namespace app\index\controller;


use think\Db;

class System
{
    //测试账号数据初始化
    public function init($player_id=null,$mobile=null){
        $testMobile = [
          '15881150667','15008248362','18728575161','13881669685'
        ];
        $res = in_array($mobile,$testMobile);
        if(!$res){
            // return result(0,'','非测试账号');
        }
        if (empty(trim($mobile))){
            if(empty(trim($player_id))){
                return result(0,'','用户参数错误');
            }
            $mobile = Db::table('player')->where('id',$player_id)->value('mobile');
            if (!$mobile){
                return result(0,'','查无此用户!');
            }
        }else{
            $player_id = Db::table('player')->where(['mobile'=>$mobile,'flag'=>1])->value('id');
        }
        if(!$player_id){
            return result(0,'','查无此用户');
        }
        Db::table('player')->where('id',$player_id)->setField('total_amount',0);
        Db::table('player_skins')->where('player_id',$player_id)->setField('status',3);//或者删除
        Db::table('battle_player')->where('player_id',$player_id)->setField('state',1);
        Db::table('recharge')->where('player_id',$player_id)->setField('state',1);
        Db::table('data')->where('player_id',$player_id)->delete();

        $battleRule = new BattleRule();
        $battleRule->clear($player_id);
        return result(1,'','');
    }

    //

}