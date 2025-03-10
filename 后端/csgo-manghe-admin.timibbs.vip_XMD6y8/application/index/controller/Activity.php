<?php


namespace app\index\controller;


use think\Db;

class Activity
{
    //判断当前是否有可领取红包，最近期的一个
    public function existEnvelope(){
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }

        $envelope_ids = Db::table('envelope_history')->where(['player_id'=>$player_id])->column('envelope_id');

        // 获取邀请人的邀请码
        $invite_code = '';
        $inviter_id = Db::table('invitation')->where(['invitees_id' => $player_id])->value('inviter_id');
        if ($inviter_id){
            $invite_code = Db::table('player')->where('id', $inviter_id)->value('invite_code');
        }

        $cond = "status=1 and flag=1 and (invite_code='' or invite_code='{$invite_code}')";
        $envelope = Db::table('envelope')
            ->field('id,name,money,create_time')
            ->order(Db::raw("invite_code desc,id desc"))
            ->whereNotIn('id', $envelope_ids)
            ->where($cond)
            ->find();

        if ($envelope) {
            $envelope['status'] = 0;
            $envelope['statusStr'] = '暂未抢';
            return result(1, $envelope, '');
        }

        return result(0, '', '');
    }

    //抢红包
    public function envelope(){
        $player_id = input('post.player_id');
        $envelope_id = input('post.envelope_id');
        $password = input('post.password');

        $date = date('Y-m-d');
        $range = $date . ' 00:00:00' . ',' . $date . ' 23:59:59';
        $whereTime[] = ['create_time', 'between', $range];
        $total = Db::table('recharge')
            ->where('player_id',$player_id)
            ->where('status',3)
            ->where($whereTime)
            ->sum('money');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        if(empty(trim($password))){
            return result(0, '', '请输入红包口令');
        }
        $envelope = Db::table('envelope')->where('id',$envelope_id)->where('flag',1)->find();

        if(!$envelope){
            return result(0, '', '红包信息不存在');
        }
        if($envelope['password'] != md5($password)){
            return result(0, '', '口令有误');
        }

        if ($envelope['zong'] > 0 && $envelope['zong'] > $total) {
            return result(0, '', '单日充值金额未满'.$envelope['zong'].'，不能领取');
        }

        // 红包绑定了默认邀请码 and 当前用户没有被邀请 则强制绑定默认邀请人
        $inviter_id = Db::table('invitation')->where('invitees_id', $player_id)->column('inviter_id');

        if ($envelope['default_invite_code'] && !$inviter_id) {
            $inviter_id = Db::table('player')
                ->where(['invite_code' => $envelope['default_invite_code'], 'flag' => 1, 'type' => 1])
                ->value('id');
            $invitation = [
                'inviter_id' => $inviter_id,
                'invitees_id' => $player_id,
                'create_time' => currentTime(),
                'source' => '领取红包强制绑定'
            ];
            $inviter_id && Db::table('invitation')->insert($invitation);
        }


        // 专属邀请码
        if ($envelope['invite_code']) {
            $inviter_id = Db::table('player')->where('invite_code', $envelope['invite_code'])->column('id');
            $invitation = Db::table('invitation')->where(['inviter_id' => $inviter_id, 'invitees_id' => $player_id])->find();
            if (empty($invitation)) {
                return result(0, '', '专属邀请码,您无法领取');
            }
        }

        $a=5;
        date_default_timezone_set('Asia/Shanghai');

        $history_id = Db::table('envelope_history')->where(['player_id'=>$player_id,'envelope_id'=>$envelope_id])->value('id');
        if($history_id>0){
            return result(0, '', '红包已抢过了');
        }

         if($envelope['state'] == 1){
            $date = time();//当前时间
            $create_time = strtotime($envelope['create_time']);
            $today= date("Y/m/d H:i:s",$create_time+($envelope['end']*24*60*60));
            $todayy=strtotime($today);
            if($date > $todayy){
                return result(0, '', '红包已过期');
            }
        }
        
        if(($envelope['num'] >= $envelope['total_num']) || ($envelope['status'] == 2)){
            return result(0, '', '红包抢光了');
        }
        Db::startTrans();
        try {
            //先增1,再记录抢到的记录信息
            Db::table('envelope')->where('id',$envelope_id)->setInc('num',1);
            header("Content-Type: text/html;charset=utf-8");//防止中文乱码
            $money = $envelope['balance'];    //余额
            $num   = $envelope['total_num'];  // 分成8个红包
            $i = $envelope['num'] + 1;        //当前第几个红包
            //type,1:随机，2：平分
            if($envelope['type'] == 1){
                $min_money   = 0.01;          //每个人最少能收到0.01元
                $res = $this->lottery($money, $num, $min_money,$i,$envelope_id,$player_id);
            }else if($envelope['type'] == 2){
                $res = $this->equally($envelope,$player_id,$i);
            }else{
                return result(0, '', '红包信息错误');
            }

            Db::commit();
            return result(1, $res, '抢到第'.$i.'个红包');
        }catch (\Exception $e){
            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }

    function equally($envelope,$player_id,$i){

        $envelope_history = [
            'envelope_id' => $envelope['id'],
            'player_id'   => $player_id,
            'amount'      => $envelope['equally'],
            'time'        => currentTime()
        ];
        Db::table('envelope_history')->insert($envelope_history);
        $balance = new Balance();
        $total_amount = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->value('total_amount');

//        $b = number_format(($envelope['balance'] - $envelope['equally']), 2, '.', '');//保留2位小数
        if($i == $envelope['total_num']){
            Db::table('envelope')->where('id',$envelope['id'])->setField('status',2);
            Db::table('envelope')->where('id',$envelope['id'])->setDec('balance',$envelope['balance']);
            $balance->opBalance($player_id,$envelope['balance'],$total_amount,6);
            $gotMoney = $envelope['balance'];
        }else{
            Db::table('envelope')->where('id',$envelope['id'])->setDec('balance',$envelope['equally']);
            $balance->opBalance($player_id,$envelope['equally'],$total_amount,6);
            $gotMoney = $envelope['equally'];
        }
        $res = [
            'gotMoney' => $gotMoney, //抢到的金额
//                'balance'  => $b,        //剩余金额
        ];
        return $res;
    }


    // 产生一个随机浮点数
    function random_float($min = 0, $max = 1)
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }

    // 微信随机红包模拟算法
    function lottery($sum_money, $num, $min_money,$i,$envelope_id,$player_id)
    {
        if ($sum_money < $num * $min_money) {
            return '钱不够';
        }
        $list = [];
        if($i<=$num){
            // 剩余的可分配金额，需要确保剩下的人每人都至少可以拿到保底的钱
            $remain = $sum_money - array_sum($list) - ($num - $i + 1) * $min_money;
            if ($i < $num) {  // 前面的人随机获得
                // 每轮抽取的金额范围：0 至 剩余金额平均值的两倍
                $get = $this->random_float(0, $remain / ($num - $i + 1) * 2);
            } else {  // 最后一个人拿全部剩下的
                $get = $remain;
            }
            if($i == $num){
                $gotMoney = $sum_money;
            }else{
                // 最后再将每个人保底的钱加上
                $gotMoney = round(round($get, 2) + $min_money, 2);
            }
//            echo '第'.$i.'个红包：'.$gotMoney.' 元，余额：'.($sum_money - $gotMoney).' 元';
            Db::table('envelope')->where('id',$envelope_id)->setDec('balance',$gotMoney);
            //记录
            $envelope_history = [
                'envelope_id' => $envelope_id,
                'player_id'   => $player_id,
                'amount'      => $gotMoney,
                'time'        => currentTime()
            ];
            Db::table('envelope_history')->insert($envelope_history);
            $balance = new Balance();
            $total_amount = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->value('total_amount');
            $balance->opBalance($player_id,$gotMoney,$total_amount,6);
            $b = number_format(($sum_money - $gotMoney), 2, '.', '');//保留2位小数
            if($b <= 0){
                Db::table('envelope')->where('id',$envelope_id)->setField('status',2);
            }
            $res = [
                'gotMoney' => $gotMoney, //抢到的金额
//                'balance'  => $b,        //剩余金额
            ];
            return $res;
        }
//        return $list;
    }


    //获取单个红包记录
    public function getDetails(){
        $player_id = input('post.player_id');
        $envelope_id = input('post.envelope_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $envelope = Db::table('envelope')
            ->where('id',$envelope_id)
            ->field('id,name,money,create_time,total_num')
            ->find();
        if(!$envelope){
            return result(0, '', '红包信息不存在');
        }
        $envelope['my_envelope'] = Db::table('envelope_history')
            ->where(['envelope_id'=>$envelope_id,'player_id'=>$player_id])->value('amount');
        $details = Db::table('player')
            ->alias('p')
            ->field('p.id as player_id,p.name,p.img,h.amount,h.time')
            ->join('envelope_history h','h.player_id = p.id')
             ->where('h.envelope_id',$envelope_id)
            ->select();
        if(count($details)>0){
            foreach ($details as $k=>$v){
                $details[$k]['img'] = $details[$k]['img'] ? mainName().$details[$k]['img'] : '';
            }
        }
        $envelope['details'] = $details;
        return result(1, $envelope, '');
    }
}