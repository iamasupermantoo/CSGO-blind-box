<?php


namespace app\union\controller;

//主播
use think\Db;

class Anchor
{
    //主播列表
    public function getList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $role_id = input('post.login_id');
        $allowState = input('post.allowState');//
        $where = [];
        if($allowState == 2){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $where = ['p.union'=>$role['union_id']];
        }else if($allowState == 3){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $where = ['p.id'=>$role['player_id']];
        }
        $list = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img,p.mobile,p.create_time,u.name as union_name')
            ->join('union u','u.id = p.union')
            ->where(['p.flag'=>1,'p.anchor'=>1])
            ->where($where)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('p.create_time','desc')
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('union u','u.id = p.union')
            ->where(['p.flag'=>1,'p.anchor'=>1])
            ->where($where)
            ->order('p.create_time','desc')
            ->select();
        if($total>0){
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,[],'无数据');
        }
    }


    public function getMemberList(){
        $page     = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);

        $role_id    = input('post.login_id');
        $allowState = input('post.allowState');//

        $whereR = [];
        if($allowState == 2){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $whereR = $role['union_id'] ? ['p.union'=>$role['union_id']] : [];

        }else if($allowState == 3){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $whereR = $role['player_id'] ? ['i.inviter_id'=>$role['player_id']] : [];
        }

        $name = input('post.name');
        $mobile = input('post.mobile');
        $inviter_id = input('post.inviter_id');
        $where = [];
        if(trim($name)){
            $where[] = ['p.name','like','%'.$name.'%'];
        }
        if(trim($mobile)){
//            $where = array_merge($where,['p.mobile'=>$mobile]);
            $where[] = ['p.mobile','like','%'.$mobile.'%'];
        }
        if(trim($inviter_id)){
            $where = array_merge($where,['i.inviter_id'=>$inviter_id]);
        }

        $list = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name,p.img,p.mobile,i.create_time,i.source,p1.name as inviter_name')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('player p1','p1.id = i.inviter_id','left')
            ->where($where)
            ->where($whereR)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('i.create_time','desc')
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('player p1','p1.id = i.inviter_id','left')
            ->where($where)
            ->where($whereR)
            ->count();
        $inviterOption = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name')
            ->where(['p.flag'=>1])
            ->where($whereR)
            ->join('invitation i','i.inviter_id = p.id')
            ->group('i.inviter_id')
            ->select();
        //成员总充值
        $sum_recharge = Db::table('recharge')
            ->alias('r')
            ->join('player p','p.id = r.player_id')
            ->join('invitation i','i.invitees_id = p.id')
            ->where(['r.mode'=>['zhifubao','weixin'],'r.status'=>3])
            ->where($whereR)
            ->where($where)
            ->sum('money');
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = !filter_var($list[$k]['img'],FILTER_VALIDATE_URL) ? mainName1().$list[$k]['img'] : $list[$k]['img'];
                $list[$k]['total_recharge'] = Db::table('recharge')
                    ->where(['player_id'=>$list[$k]['id'],'status'=>3,'mode'=>['zhifubao','weixin']])
                    ->sum('money');
            }
            $re = ['total'=>$total,'sum_recharge'=>$sum_recharge,'list'=>$list,'inviterOption'=>$inviterOption];
            return result(1,$re,'');
        }else{
            $re = ['inviterOption'=>$inviterOption];
            return result(0,$re,'无数据');
        }
    }

    //充值
    public function getRechargeList(){
        $page      = input('post.page', 1);
        $pageSize  = input('post.pageSize', 10);
        $player_id = input('post.id');
        $time      = input('post.search_time');
        $whereTime = [];
        if($time){
            $startTime = $time[0];
            $endTime   = $time[1];
            if(!empty($startTime) && empty($endTime)){
                $whereTime[] = ['create_time','>=',$startTime];
            }elseif (empty($startTime) && !empty($endTime)){
                $whereTime[] = ['create_time','<=',$endTime];
            }else if((!empty($startTime) && !empty($endTime))){
                $range = $startTime . ',' . $endTime;
                $whereTime[] = ['create_time', 'between', $range];
            }
        }
        $list = Db::table('recharge')
            ->where('player_id',$player_id)
            ->where(['mode'=>['zhifubao','weixin']])
            ->where($whereTime)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('recharge')
            ->where('player_id',$player_id)
            ->where(['mode'=>['zhifubao','weixin']])
            ->where($whereTime)
            ->count();
//        $sum = Db::table('recharge')
//            ->where('player_id',$player_id)
//            ->where(['mode'=>['zhifubao','weixin']])
//            ->where($whereTime)
//            ->sum('money');

        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['statusStr'] = ($list[$k]['status'] == 3) ? '充值成功' : '未支付';
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            $re = ['total'=>0,'list'=>[]];
            return result(0,$re,'无数据');
        }
    }

    //取回列表
    public function retrieveList(){
        $page       = input('post.page', 1);
        $pageSize   = input('post.pageSize', 10);
        $inviter_id = input('post.inviter_id');
        $time       = input('post.search_time');

        $role_id = input('post.login_id');
        $allowState = input('post.allowState');//
        $whereR = [];
        if($allowState == 2){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $whereR = ['p.union'=>$role['union_id']];
        }else if($allowState == 3){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $whereR = ['i.inviter_id'=>$role['player_id']];
        }

        $whereTime  = [];
        if($time){
            $startTime = $time[0];
            $endTime   = $time[1];
            if(!empty($startTime) && empty($endTime)){
                $whereTime[] = ['ps.receive_time','>=',$startTime];
            }elseif (empty($startTime) && !empty($endTime)){
                $whereTime[] = ['ps.receive_time','<=',$endTime];
            }else if((!empty($startTime) && !empty($endTime))){
                $range = $startTime . ',' . $endTime;
                $whereTime[] = ['ps.receive_time', 'between', $range];
            }
        }
        $where = [];
        if($inviter_id){
            $where = ['i.inviter_id'=>$inviter_id];
        }
        $list = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id','left')
            ->join('invitation i','i.invitees_id = ps.player_id')
            ->join('player p1','p1.id = i.inviter_id')
            ->field('ps.id,ps.player_id,p.name as player,ps.name,ps.price,ps.buy_price,ps.buy_cny_price,ps.receive_time,ps.state')
            ->where('ps.state','success')
            ->where('p1.anchor',1)
            ->where($whereTime)
            ->where($where)
            ->where($whereR)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('ps.receive_time','desc')
            ->select();
        $total = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id','left')
            ->join('invitation i','i.invitees_id = ps.player_id')
            ->join('player p1','p1.id = i.inviter_id')
            ->where('ps.state','success')
            ->where('p1.anchor',1)
            ->where($whereTime)
            ->where($where)
            ->where($whereR)
            ->count();
        $sum = Db::table('player_skins')
            ->alias('ps')
            ->join('player p','p.id = ps.player_id','left')
            ->join('invitation i','i.invitees_id = ps.player_id')
            ->join('player p1','p1.id = i.inviter_id')
            ->where('ps.state','success')
            ->where('p1.anchor',1)
            ->where($whereTime)
            ->where($where)
            ->where($whereR)
            ->sum('buy_cny_price');
        $inviterOption = Db::table('player')
            ->alias('p')
            ->field('p.id,p.name')
            ->where(['p.flag'=>1])
            ->where('p.anchor',1)
            ->join('invitation i','i.inviter_id = p.id')
            ->where($whereR)
            ->group('i.inviter_id')
            ->select();
        if($total>0){
            $re = ['total'=>$total,'list'=>$list,'sum'=>$sum,'inviterOption'=>$inviterOption];
            return result(1,$re,'');
        }else{
            $re = ['total'=>0,'list'=>[],'sum'=>0,'inviterOption'=>$inviterOption];
            return result(0,$re,'无数据');
        }
    }

    //权限管理
    public function getRoleList(){
        $page      = input('post.page', 1);
        $pageSize  = input('post.pageSize', 10);

        $allowState = input('post.allowState');
        if($allowState > 1){
            $re = ['total'=>0,'list'=>[]];
            return result(0,$re,'无权限');
        }

        $list = Db::table('union_role')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('union_role')
            ->count();
        if($total > 0){
            foreach ($list as $k=>$v){
                $list[$k]['info'] = $list[$k]['info'] ? unserialize($list[$k]['info']) : [];
                $list[$k]['menu'] = $list[$k]['menu'] ? unserialize($list[$k]['menu']) : [];
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            $re = ['total'=>0,'list'=>[]];
            return result(0,$re,'无数据');
        }
    }

    //查看权限信息
    public function roleInfo(){
        $id = input('post.id');
        $data = Db::table('union_role')
            ->where('id',$id)
            ->find();
        if($data['allowState'] == 3){
            $data['img'] = Db::table('player')->where(['mobile'=>$data['account'],'flag'=>1])->value('img');
        }
        if($data){
            return result(1,$data,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //保存权限信息
    public function saveRoleInfo(){
        $post = input('post.');
        $check = $post['check'];
        $halfCheck = $post['halfCheck'];
        $data = $post['data'];

        $allowState = input('post.allowState');
        if($allowState > 1){
            $re = ['total'=>0,'list'=>[]];
            return result(0,$re,'无权限');
        }

        if($data['allowState'] == 3){
            $player_id = Db::table('player')->where(['mobile'=>$data['account'],'flag'=>1,'anchor'=>1])->value('id');
            if(!$player_id){
                return result(0,'','该账号不存在或非主播账号');
            }
            $data['player_id'] = $player_id;
        }

        $checkChildS = [];
        $checkComplete = [];
        $checkedID = [];
        $halfCheckedID = [];
        foreach ($check as $k=>$v){
            if(isset($check[$k]['path'])){
                array_push($checkChildS,$check[$k]);
            }else{
                array_push($checkComplete,$check[$k]);
            }
            array_push($checkedID,$check[$k]['id']);
        }
        $checked = [];
        foreach ($halfCheck as $k=>$v){
            array_push($halfCheckedID,$halfCheck[$k]['id']);
            $children = $halfCheck[$k]['children'];
            $che['id'] = $halfCheck[$k]['id'];
            $che['title'] = $halfCheck[$k]['title'];
            $che['icon'] = $halfCheck[$k]['icon'];
            $che['title'] = $halfCheck[$k]['title'];
            $arr = [];
            foreach ($checkChildS as $key=>$val){
                if(in_array($val,$children)){
                    array_push($arr,$val);
                }
            }
            $arr ? ($che['children'] = $arr) : '';

            $checked[] = $che;
        }

        $menu = array_merge($checkComplete,$checked);
        $menu = arraySort($menu,'id','asc');

        $info['checked']   = $checkedID;
        $info['halfChecked'] = $halfCheckedID;
        $data['menu'] = serialize($menu);
        $data['info'] = serialize($info);
//        $data['password'] = md5(trim($data['password']));

        if(isset($data['id']) && ( $data['id']>0)){
            $roles = Db::table('union_role')->where('account', $data['account'])->select();
            $role = Db::table('union_role')->where('account', $data['account'])->find();
            if(count($roles)>1){
                return result(0,'','已存在相同管理员');
            }
            if($role['password'] !== trim($data['password'])){
                $data['password'] = md5(trim($data['password']));
            }
            Db::table('union_role')->update($data);
        }else{
            $role = Db::table('union_role')->where('account', $data['account'])->find();
            if($role){
                return result(0,'','已存在相同管理员');
            }
            $data['password'] = md5(trim($data['password']));
            $data['date'] = currentTime();
            Db::table('union_role')->insert($data);
        }
        return result(1,'','');
    }


    //个人中心修改密码
    public function editPassword(){
        $id       = input('post.id');
        $password = input('post.password');
        if($password){
            Db::table('union_role')->where('id',$id)->setField('password',md5($password));
        }
        return result(1,'','保存成功');
    }


    //充值信息页面，所有人的充值明细信息
    public function allRecharge(){
        $page      = input('post.page', 1);
        $pageSize  = input('post.pageSize', 10);
        $time      = input('post.search_time');
        $status    = input('post.status');
        $key       = input('post.key');
        $role_id   = input('post.login_id');
        $allowState = input('post.allowState');

        $inviterOption = [];
        if($allowState == 1){
            $inviterOption = Db::table('player')
                ->alias('p')
                ->field('p.id,p.name')
                ->where(['p.flag'=>1])
                ->join('invitation i','i.inviter_id = p.id')
                ->group('i.inviter_id')
                ->select();
        }
        $where = [];
        if($allowState == 2){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $where = ['p.union'=>$role['union_id']];

            $inviterOption = Db::table('player')
                ->alias('p')
                ->field('p.id,p.name')
                ->where(['p.flag'=>1])
                ->where($where)
                ->join('invitation i','i.inviter_id = p.id')
                ->group('i.inviter_id')
                ->select();
        }else if($allowState == 3){
            $role = Db::table('union_role')->where('id',$role_id)->find();
            $where = ['i.inviter_id'=>$role['player_id']];
        }
        $whereK = trim($key) ? [['p.name|p.mobile','like','%'.$key.'%']] : [];
        $whereS = ($status>0) ? ['r.status'=>$status] : [];
        $whereTime = [];
        if($time){
            $startTime = $time[0];
            $endTime   = $time[1];
            if(!empty($startTime) && empty($endTime)){
                $whereTime[] = ['r.create_time','>=',$startTime];
            }elseif (empty($startTime) && !empty($endTime)){
                $whereTime[] = ['r.create_time','<=',$endTime];
            }else if((!empty($startTime) && !empty($endTime))){
                $range = $startTime . ',' . $endTime;
                $whereTime[] = ['r.create_time', 'between', $range];
            }
        }
        $list = Db::table('recharge')
            ->alias('r')
            ->field('r.player_id,p.name,p.mobile,r.create_time,r.money,r.mode,r.status')
            ->join('player p','p.id = r.player_id')
            ->join('invitation i','i.invitees_id = p.id')
            ->where(['r.mode'=>['weixin','zhifubao']])
            ->where($whereTime)
            ->where($whereS)
            ->where($whereK)
            ->where($where)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('r.create_time','desc')
            ->select();
        $select = Db::table('recharge')
            ->alias('r')
            ->field('r.money')
            ->join('player p','p.id = r.player_id')
            ->join('invitation i','i.invitees_id = p.id')
            ->where(['r.mode'=>['weixin','zhifubao']])
            ->where($whereTime)
            ->where($whereS)
            ->where($whereK)
            ->where($where)
            ->select();
        $total = count($select);
        $sum = array_sum(array_map(function ($val){return $val['money'];},$select));


        if($total>0){
            foreach ($list as $k=>$v) {
                $list[$k]['statusStr'] = ($list[$k]['status'] == 3) ? '成功' : '未支付';
            }
            $res = ['total'=>$total,'sum'=>$sum,'inviterOption'=>$inviterOption,'list'=>$list];
            return result(1,$res,'');
        }else{
            $res = ['total'=>0,'sum'=>0,'inviterOption'=>$inviterOption,'list'=>[]];
            return result(1,$res,'');
        }
    }




























}