<?php


namespace app\index\controller;


use think\Db;

class Balance
{
    //操作账户余额表
    public function opBalance($player_id=null,$amount=null,$total_amount=null,$type=null){
        //类型：1：皮肤兑换T币（增加），2：消费（废弃），3：充值，4：对战失败0.01，5：对战存在多个平局赢家平分输家的钱，6.后台充值，7.幸运失败0.01,8:充值赠送 9:邀请奖励
        //消费类型,-1:购买盲盒，-2：对战，-3：幸运饰品，-4：商城购买饰品
        if($type > 0 ){
            $total_amount += $amount;
        }else{
            if($total_amount < $amount){
                return false;
            }
            $total_amount -= $amount;
        }
        $balanceInfo = [
            'player_id'    => $player_id,
            'amount'       => ($type < 0 ? '-' : '') . $amount,    //本次增加/减少金额
            'total_amount' => $total_amount,                       //操作后，账户余额
            'type'         => $type,                               //类型：
            'create_time'  => currentTime()
        ];
        Db::startTrans();
        try {
            if($type < 0){
                Db::table('player')->where('id',$player_id)->setDec('total_amount',$amount);
            }else {
                Db::table('player')->where('id',$player_id)->setInc('total_amount',$amount);
            }
                Db::table('balance')->insert($balanceInfo);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }

    }


    //生成流水号(暂未使用)
    public function make_number()
    {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'I', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        dump($year_code[intval(date('Y')) - 2015]);
        dump(strtoupper(dechex(date('m'))) . date('d'));
        return $year_code[intval(date('Y')) - 2015] .
            strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }


    //账户流水
    public function balanceList(){

    }



}