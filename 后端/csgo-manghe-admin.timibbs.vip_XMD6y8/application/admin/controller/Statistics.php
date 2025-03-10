<?php


namespace app\admin\controller;

//统计
use think\Db;

class Statistics
{
    //会员
    public function member(){
        $startTime = date("Y-m-d",strtotime("-6 day"));
        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['create_time', 'between', $range];
        }
        $info = Db::table('player')
            ->field('id,name,mobile,create_time')
            ->where(['flag'=>1,'type'=>1])
            ->where($whereTime)
            ->select();
        $total = Db::table('player')
            ->where(['flag'=>1,'type'=>1])
            ->count();
        $day = [];
        $n = 0;
        for($i=6;$i>=0;$i--){
            $num = 0;
            foreach ($info as $k=>$v){
                //从近7天内的第一天开始
                $start = date("Y-m-d",strtotime("-".$i." day")).' 00:00:00';
                $end   = date("Y-m-d",strtotime("-".$i." day")).' 23:59:59';

                $in = (($info[$k]['create_time'] >= $start) && ($info[$k]['create_time'] <= $end));
                if($in){
                    $num +=1;
                }
            }
            $day[$n] = $num;
            $n++;
        }
        if($total>0){
            $res['analyticsData']['subscribers'] = $total;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = 'Subscribers';
        }else{
            $res['analyticsData']['subscribers'] = 0;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = 'Subscribers';
        }
        return result(1,$res,'');
    }

    //充值
    public function recharge(){
        $startTime = date("Y-m-d",strtotime("-30 day"));
        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['create_time', 'between', $range];
        }
        $info = Db::table('recharge')
            ->where($whereTime)
            ->where(['status'=>3])
//            ->where('mode','IN', ['weixin','zhifubao','ckpay','plat'])
            ->select();
        $total = Db::table('recharge')
            ->where(['status'=>3])
//            ->where('mode','IN', ['weixin','zhifubao','ckpay','plat'])
            ->sum('money');
        $day = $xaxis = [];
        $n = 0;


        for($i=30;$i>=0;$i--){
            $money = 0;
            foreach ($info as $k=>$v){
                //从近7天内的第一天开始
                $start = date("Y-m-d",strtotime("-".$i." day")).' 00:00:00';
                $end   = date("Y-m-d",strtotime("-".$i." day")).' 23:59:59';
                $in = (($info[$k]['create_time'] >= $start) && ($info[$k]['create_time'] <= $end));
                if($in){
                    $money +=$info[$k]['money'];
                }
            }
            $day[$n] = $money;
            $xaxis[] = $n;
            $n++;
        }


        if($total>0){
            $res['analyticsData']['revenue'] = $total;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = '每日累充';
        }else{
            $res['analyticsData']['revenue'] = 0;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = '每日累充';
        }
        $res['xaxis'] = $xaxis;

        return result(1,$res,'');
    }

    //平台支出,扎比特取回成功总和
    public function expenditure(){
        $startTime = date("Y-m-d",strtotime("-6 day"));
        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['receive_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['receive_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['receive_time', 'between', $range];
        }
        $info = Db::table('player_skins')
            ->field('id,name,buy_price,receive_time')
            ->where($whereTime)
            ->where(['state'=>'success'])
            ->select();
        $total = Db::table('player_skins')
            ->where(['state'=>'success'])
            ->sum('buy_price');

        $day = [];
        $n = 0;
        for($i=6;$i>=0;$i--){
            $money = 0;
            foreach ($info as $k=>$v){
                //从近7天内的第一天开始
                $start = date("Y-m-d",strtotime("-".$i." day")).' 00:00:00';
                $end   = date("Y-m-d",strtotime("-".$i." day")).' 23:59:59';
                $in = (($info[$k]['receive_time'] >= $start) && ($info[$k]['receive_time'] <= $end));
                if($in){
                    $money +=$info[$k]['buy_price'];
                }
            }
            $day[$n] = $money;
            $n++;
        }
        if($total>0){
            $res['analyticsData']['sales'] = $total;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = 'Sales';
        }else{
            $res['analyticsData']['sales'] = 0;
            $res['series'][0]['data']  = $day;
            $res['series'][0]['name']  = 'Sales';
        }
        return result(1,$res,'');
    }

    //盈亏,仅计算玩家背包正常的饰品总价值
    public function totalValue(){
        $total = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id')
            ->where(['ps.status'=>1,'p.group'=>0,'p.type'=>1])
            ->sum('ps.price');
        $res['analyticsData']['orders'] = $total;
        $res['series'][0]['name']  = 'Orders';
        return result(1,$res,'');
    }

    //当月与上月收入比较
    public function contrast(){

        $startTime = date("Y-m-01",time());
        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['create_time', 'between', $range];
        }
        //当月收入
        $thisMonthList = Db::table('recharge')
            ->field(['id,create_time,money'])
            ->where($whereTime)
            ->where(['status'=>3])
//            ->where('mode','IN', ['weixin','zhifubao'])
            ->select();
        //当月支持
        $whereTimeOut[] = ['receive_time','>=',$startTime.' 00:00:00'];
        $lastMonthList = Db::table('player_skins')
            ->field(['id,receive_time,buy_price'])
            ->where($whereTimeOut)
            ->where(['state'=>'success'])
            ->select();
        //本月天数
        $thisMonth = date("Y-m",time());
        $thisMonthDay = date("t",strtotime($thisMonth));
        //上月天数
        $lastMont = date("Y-m",strtotime('-1 month'));
        $lastMonthDay = date("t",strtotime($lastMont));
        $today = date("Y-m-d");

        //本月数据
        $thisArr = [];
        for($i=1;$i<=$thisMonthDay;$i+=4){
            $thisArr[] = $i;
        }
        $day = [];
        for($i=1;$i<=count($thisArr);$i++){
            $money = 0;
            foreach ($thisMonthList as $k=>$v){
                if($i == 1){
                    $start = date("Y-m-".($thisArr[$i-1]-1)).' 00:00:00';
                }else{
                    $start = date("Y-m-".($thisArr[$i-1])).' 00:00:00';
                }
                if($i < count($thisArr)){
                    $end = date("Y-m-".($thisArr[$i]-1)).' 23:59:59';
                }else{
                    $end = date("Y-m-".$thisMonthDay).' 23:59:59';
                }
                $in = (($thisMonthList[$k]['create_time'] >= $start) && ($thisMonthList[$k]['create_time'] <= $end));
                if($in){
                    $money +=$thisMonthList[$k]['money'];
                }
            }
            $day[$i-1] = $money;
        }


        //上月数据
        $lastArr = [];
        for($i=1;$i<=$lastMonthDay;$i+=4){
            $lastArr[] = $i;
        }
        $day1 = [];
        for($i=1;$i<=count($lastArr);$i++){
            $money1 = 0;
            foreach ($lastMonthList as $k=>$v){
                if($i == 1){
                    $start = date("Y-m-".($lastArr[$i-1]-1)).' 00:00:00';
                }else{
                    $start = date("Y-m-".($lastArr[$i-1])).' 00:00:00';
                }
                if($i < count($lastArr)){
                    $end = date("Y-m-".($lastArr[$i]-1)).' 23:59:59';
                }else{
                    $end = date("Y-m-".$lastMonthDay).' 23:59:59';
                }
                $in = (($lastMonthList[$k]['receive_time'] >= $start) && ($lastMonthList[$k]['receive_time'] <= $end));
                if($in){
                    $money1 +=$lastMonthList[$k]['buy_price'];
                }
            }
            $day1[$i-1] = $money1;
        }


        if($thisMonthList){
            $res['analyticsData']['lastMonth'] = array_sum(array_map(function($val){return $val['money'];},$thisMonthList));
            $res['analyticsData']['thisMonth'] = array_sum(array_map(function($val){return $val['buy_price'];},$lastMonthList));
            $res['series'][0]['data']  = $day;
            $res['series'][1]['data']  = $day1;
            $res['series'][0]['name']  = 'thisMonth';
            $res['series'][1]['name']  = 'lastMonth';
        }else{
            $res['analyticsData']['lastMonth'] = 0;
            $res['analyticsData']['thisMonth'] = 0;
            $res['series'][0]['data']  = $day;
            $res['series'][1]['data']  = $day1;
            $res['series'][0]['name']  = 'thisMonth';
            $res['series'][1]['name']  = 'lastMonth';
        }
        return result(1,$res,'');

//        $lastArr = [];
//        for($i=1;$i<=$lastMonthDay;$i+=4){
//            $lastArr[] = $i;
//        }


    }

}