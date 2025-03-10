<?php


namespace app\index\controller;


use think\Db;

class Invite
{
    //推广链接和推广码
    public function inviteInfo(){
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $info = Db::table('player')
            ->where(['id'=>$player_id,'type'=>1,'flag'=>1])
            ->field('invite_code,invite_url')
            ->find();
            
        if (!empty($info['invite_code']) && !empty($info['invite_url'])){
            $register = new Register();
            $info['invite_url'] = $register->makeInviteUrl($info['invite_code']);
        }    
        $info['invite_url'] = $info['invite_url'] ? mainName1().$info['invite_url'] : '';

        if($info){
            return result(1, $info, '');
        }else{
            return result(0, '', '');
        }
    }

    //我的下线7天/30天/今天/全部
    public function offline(){
        $days = input('post.days');
        $player_id = input('post.player_id');
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $time = '';
        if($days>0){
            if($days == 1){
                $time = date("Y-m-d 00:00:00");
            }else{
                $days -=1;
                $time = date("Y-m-d 00:00:00",strtotime("-$days day"));
            }
        }
        $where = [];
        if($time){
            $where[] = ['i.create_time','>=',$time];
        }
        //人数
        $num = Db::table('player')
            ->alias('p')
            ->join('invitation i','i.invitees_id = p.id')
            ->where('i.inviter_id',$player_id)
            ->where($where)
            ->count();
        //佣金比例
        $ratio = Db::table('invite_set')->value('ratio');
        //佣金金额
        $where1 = [];
        if($time){
            $where1[] = ['time','>=',$time];
        }
        $invite_commission = Db::table('invite_commission')
            ->where('inviter_id',$player_id)
            ->where($where1)
            ->sum('money');
        //结算记录列表
        $list = Db::table('invite_settlement')
            ->where('inviter_id',$player_id)
            ->where($where1)
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->select();
        $total = Db::table('invite_settlement')
            ->where('inviter_id',$player_id)
            ->where($where1)
            ->count();
        $res = [
            'people_num' => $num,
            'ratio' => $ratio,
            'invite_commission' => $invite_commission,
            'total' => $total,
            'list' => $list,
        ];
        return result(1,$res,'');
    }




    //我的下线列表
    public function offlineList(){
        $page      = input('post.page',1);
        $pageSize  = input('post.pageSize',10);
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $list = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img,i.source,i.create_time')
            ->join('invitation i','i.invitees_id = p.id')
            ->limit(($page - 1) * $pageSize,$pageSize)
            ->where('i.inviter_id',$player_id)
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('invitation i','i.invitees_id = p.id')
            ->where('i.inviter_id',$player_id)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] =  $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }


    //用户充值给推荐人记录佣金信息
    public function addCommission($inviter_id=null,$invitees_id=null,$recharge=null){
        //邀请人id，$inviter_id
        //充值用户id（被推荐人），$invitees_id
        //充值金额 ，recharge

        $ratio = Db::table('invite_set')->value('ratio');
        $money = (float)sprintf("%.2f",$recharge * ($ratio/100));
        $money = ($money < 0.01) ? 0.01 : $money;
        $commission = [
            'inviter_id'  => $inviter_id,
            'invitees_id' => $invitees_id,
            'recharge'   => $recharge,
            'time'       => currentTime(),
            'ratio'      => $ratio,
            'money'      => $money
        ];
        $commission_id = Db::table('invite_commission')->insertGetId($commission);
        if($commission_id>0){
            return true;
        }else{
            return false;
        }
    }

    //每周结算
    public function settlement($player_id=null){
        $player_id = input('post.player_id') ? input('post.player_id') : $player_id;
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $unixTime=null;
        $unixTime=is_numeric($unixTime)?$unixTime:time();
        $weekarray=array('7','1','2','3','4','5','6');
        $week =  $weekarray[date('w',$unixTime)];//今天星期几
        if($week != 1){
            return result(0, '', '非周一');
        }

        //周期范围
        $last_monday = date('Y-m-d 00:00:00',strtotime('last week monday'));
        $last_sunday = date('Y-m-d 23:59:59',strtotime('last week sunday'));

        //充值人数
        $num = Db::table('invite_commission')
            ->where('inviter_id',$player_id)
            ->where('time','>=',$last_monday)
            ->where('time','<=',$last_sunday)
            ->group('invitees_id')
            ->count();
        $totalInfo = Db::table('invite_commission')
            ->field(['sum(money) as total_money,sum(recharge) as recharge'])
            ->where('inviter_id',$player_id)
            ->where('time','>=',$last_monday)
            ->where('time','<=',$last_sunday)
            ->select();
        $ratio = Db::table('invite_set')->value('ratio');
        $settlement = [
            'time' => currentTime(),
            'num'  => $num,
            'recharge' => floatval($totalInfo[0]['recharge']),
            'ratio' => $ratio,
            'total_money' => floatval($totalInfo[0]['total_money']),
            'inviter_id' => $player_id
        ];
        Db::startTrans();
        try {
            $settlement_id = Db::table('invite_settlement')->insertGetId($settlement);
            $re = $this->settlementToAddMoney($player_id,$settlement['total_money']);
            if($re){
                Db::table('invite_settlement')->where('id',$settlement_id)->setField('status',1);
            }
            Db::commit();
            return result(1, '', '结算完成');
        }catch (\Exception $e){
            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }

    //结算,给邀请人账户增加余额，group账户不做操作
    public function settlementToAddMoney($player_id=null,$total_money=null){
        $player = Db::table('player')->where('id',$player_id)->find();
        if(!$player['group']){
            $balance = new Balance();
            $re = $balance->opBalance($player_id,$total_money,$player['total_amount']);
            return $re;
        }else{
            return true;
        }
    }

    public function reg(){
        $url = mainName1().'/index.html?code='.$_GET['code'];
        echo '<meta http-equiv="refresh" content="1;url='.$url.'">';
        exit();
    }


}