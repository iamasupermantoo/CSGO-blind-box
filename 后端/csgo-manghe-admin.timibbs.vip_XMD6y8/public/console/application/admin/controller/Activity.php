<?php


namespace app\admin\controller;


use think\Db;

class Activity
{
    //红包列表
    public function envelopeList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $status    = input('post.status');
        $startTime = input('post.startTime');
        $endTime   = input('post.endTime');

        $where = $status ? ['status'=>$status] : '';
        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['r.create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['r.create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['r.create_time', 'between', $range];
        }

        $list = Db::table('envelope')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where($where)
            ->where($whereTime)
            ->where('flag',1)
            ->order('id','desc')
            ->select();
        $total = Db::table('envelope')
            ->where($where)
            ->where($whereTime)
            ->where('flag',1)
            ->count();

        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['typeStr']   = ($list[$k]['type'] == 1 ) ? '随机' : (($list[$k]['type'] == 2) ? '平均' : '');
                $list[$k]['statusStr'] = ($list[$k]['status'] == 1 ) ? '未抢完' : (($list[$k]['type'] == 2) ? '已抢完' : '');
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1, $re, '');
        }
        $re = ['total'=>$total,'list'=>$list];
        return result(0, $re, '');
    }

    //新增
    public function addEnvelope(){
        $data = input('post.');

    //     date_default_timezone_set('Asia/Shanghai');
    //   $today= date("Y/m/d H:i:s",time()-($data['end']*24*60*60));
        $envelope = [
            'type' => $data['type'],
            'money' => $data['money'],
            'name' => $data['name'],
            'total_num' => $data['total_num'],
            'pass' => $data['pass'],
            'password' => md5($data['pass']),
            'balance' => $data['money'],
            'create_time' => currentTime(),
            'zong' => $data['zong'],
            'end' => $data['end'],
            'invite_code' => $data['invite_code'],
            'default_invite_code' => $data['default_invite_code'],
        ];
        if($data['type'] == 2){
            $envelope['equally'] = number_format(($data['money']/$data['total_num']),2,'.','');
        }
        Db::table('envelope')->insert($envelope);
        return result(1, '', '');
    }

    //修改
    public function editEnvelope(){

        $data = input('post.');
        $envelope = [
            'id' => $data['dataId'],
            'type' => $data['type'],
            'money' => $data['money'],
            'name' => $data['name'],
            'total_num' => $data['total_num'],
            'pass' => $data['pass'],
            'password' => md5($data['pass']),
            'zong' => $data['zong'],
            'end' => $data['end'],
            'invite_code' => $data['invite_code'],
            'default_invite_code' => $data['default_invite_code'],
        ];

        if($data['type'] == 2){
            $envelope['equally'] = number_format(($data['money']/$data['total_num']),2,'.','');
        }
        Db::table('envelope')->where('id',$data['dataId'])->update($envelope);
        return result(1, '', '');
    }


    public function delEnvelope(){
        $id = input('post.id');
        if($id<1){
            return result(0, '', '删除失败');
        }else{
            Db::table('envelope')->where('id',$id)->setField('flag',0);
            return result(1, '', '删除成功');
        }
    }


    //充值活动
    public function rechargeActivity(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('recharge_activity')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('recharge_activity')->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = $list[$k]['img'] ? unserialize($list[$k]['img']) : '';
                $list[$k]['img'] = $list[$k]['img'] ? array_merge($list[$k]['img'],[]) :'';
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1, $re, '');
        }else{
            $re = ['total'=>0,'list'=>[]];
            return result(0, $re, '');
        }
    }

    //添加/修改充值活动
    public function addActivity(){
        $data = json_decode(input('post.data'),true);
        $data['start_time'] = isset($data['start_time']) ? str_replace("T"," ",$data['start_time']) : '';
        $data['end_time']   = isset($data['end_time'])   ? str_replace("T"," ",$data['end_time']) : '';

        $insertData = $data;
        $files = $_FILES;
        $imgS = [];
        if($files){
            $upload = New Upload();
            foreach ($files as $k=>$v){
                $url = $upload->upload('headerImage',$files[$k],$k);
                $imgS[] = $url;
            }
        }
//        $insertData['img'] = $insertData['img'] ? $insertData['img']: [];
        if(isset($insertData['id']) && ($insertData['id']>0)){
            if($imgS){
                foreach($imgS as $k=>$v){
                    $insertData['img'][count($insertData['img'])+$k] = $imgS[$k];
                }
            }
            $activity = Db::table('recharge_activity')
                ->where('type',$insertData['type'])
                ->where('id','neq',$insertData['id'])
                ->find();
            if($activity){
                return result(0, '', '已存在相同类型活动');
            }
            $insertData['img'] = serialize($insertData['img']);
            Db::table('recharge_activity')->where('id',$insertData['id'])->update($insertData);
        }else{
            $activity = Db::table('recharge_activity')->where('type',$insertData['type'])->find();
            if($activity){
                return result(0, '', '已存在相同类型活动');
            }
            $insertData['img'] = $imgS;
            $insertData['img'] = serialize($insertData['img']);
            $insertData['create_time'] = currentTime();
            Db::table('recharge_activity')->insert($insertData);
        }
        return result(1, '', '');
    }


    //删除充值活动
    public function delRechargeActivity(){
        $id = input('post.id');
        Db::table('recharge_activity')->where('id',$id)->delete();
        return result(1, '', '');
    }

















}