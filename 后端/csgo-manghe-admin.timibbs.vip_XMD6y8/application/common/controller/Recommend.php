<?php

namespace app\common\controller;

use think\Db;


use app\index\controller\Balance;

class Recommend
{
    protected static $user_lists;

    //定时执行 每周结算
    public static function run($player_id = null)
    {
        $where = ['type' => 1, 'flag' => 1];

        if ($player_id) $where['id'] = explode(',', $player_id);

        $player_ids = Db::table('player')
            ->where($where)
            ->column('id');

        if (empty($player_ids)) exit('没有需要结算的用户');

        Db::startTrans();
        try {

            $logs = [];
            $total_amount = 0;

            $itime = Db::table('invite_commission')->order('id', 'asc')->value('time');
            $itime = $itime ? $itime : date('Y-m-d 00:00:00', strtotime('last week monday'));

            foreach ($player_ids as $player_id) {

                $stime = Db::table('invite_settlement')->where(['status' => 1, 'inviter_id' => $player_id])->order('id', 'desc')->value('time');

                $stime = $stime ? $stime : $itime;
                $etime = date('Y-m-d 23:59:59');

                // 充值人数
                $num = Db::table('invite_commission')
                    ->where('inviter_id', $player_id)
                    ->where('time', '>=', $stime)
                    ->where('time', '<=', $etime)
                    ->group('invitees_id')
                    ->count();
                $totalInfo = Db::table('invite_commission')
                    ->field(['sum(money) as total_money,sum(recharge) as recharge'])
                    ->where('inviter_id', $player_id)
                    ->where('time', '>=', $stime)
                    ->where('time', '<=', $etime)
                    ->select();
                $ratio = Db::table('invite_set')->value('ratio');
                $settlement = [
                    'time' => currentTime(),
                    'num' => $num,
                    'recharge' => floatval($totalInfo[0]['recharge']),
                    'ratio' => $ratio,
                    'total_money' => floatval($totalInfo[0]['total_money']),
                    'inviter_id' => $player_id
                ];

                $settlement_id = Db::table('invite_settlement')->insertGetId($settlement);
                $re = self::settlementToAddMoney($player_id, $settlement['total_money']);
                if ($re) {
                    Db::table('invite_settlement')->where('id', $settlement_id)->setField('status', 1);
                    $logs[] = "用户:{$player_id},充值金额:{$settlement['recharge']},结算金额:{$settlement['total_money']},邀请人数:{$settlement['num']},结算周期:{$stime}~{$etime}";
                    $total_amount += $settlement['total_money'];
                }
            }

            Db::commit();

            array_walk($logs, function ($v) {
                echo $v . PHP_EOL;
            });

            $total = count($logs);
            exit("汇总=> 结算总人数:{$total},结算总金额:{$total_amount}" . PHP_EOL . '======End======');
        } catch (\Exception $e) {
            Db::rollback();
            exit('结算异常:' . $e->getMessage());
        }
    }


    // 结算,给邀请人账户增加余额，group账户不做操作
    protected static function settlementToAddMoney($player_id = null, $total_money = null)
    {
        $player = Db::table('player')->where('id', $player_id)->find();
        if (!$player['group']) {
            $balance = new Balance();
            $re = $balance->opBalance($player_id, $total_money, $player['total_amount'], 9);
            return $re;
        } else {
            return true;
        }
    }

}