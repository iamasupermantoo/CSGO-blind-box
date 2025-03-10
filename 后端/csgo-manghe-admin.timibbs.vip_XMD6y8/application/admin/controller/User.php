<?php


namespace app\admin\controller;


use app\index\controller\Balance;
use app\index\controller\BattleRule;
use app\index\controller\User as IndexUser;
use think\Db;

class User
{
    public function playerList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $group = input('post.group');
        $vip = input('post.vip');
        $anchor = input('post.anchor');
        $searchKey = input('post.searchKey');
        $whereS = [];
        trim($searchKey) ? $whereS[] = ['name|mobile','like','%'.$searchKey.'%'] : '';
        $where = [];
        if($group !== ''){
            $where = ['group'=>$group];
        }
        $whereV = [];
        if($vip !== ''){
            $whereV = ['group_vip'=>$vip];
        }

        $whereA = [];
        if ($anchor !== '') {
            $whereA = ['anchor' => $anchor];
        }

        $list = Db::table('player')
            ->where(['flag'=>1,'type'=>1])
            ->where($where)
            ->where($whereV)
            ->where($whereS)
            ->where($whereA)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('player')
            ->where($where)
            ->where($whereV)
            ->where($whereS)
            ->where($whereA)
            ->where(['flag'=>1,'type'=>1])
            ->count();
        if($total>0){
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //玩家详情
    public function playerInfo(){
        $player_id = input('post.player_id');
        $info = Db::table('player')
            ->where(['flag'=>1,'type'=>1,'id'=>$player_id])
            ->find();
        if($info){
            if(($info['group'] == 0) && ($info['group_vip'] > 0)){
                $info['group'] = 2;
            }
            $info['img'] = $info['img'] ? mainName().$info['img'] : '';
            //线上充值
            $info['online_recharge'] = Db::table('recharge')
                ->where('mode','IN', ['weixin','zhifubao'])
                ->where('player_id',$player_id)
                ->where('status',3)
                ->sum('coin');
            //平台充值
            $info['plat_recharge'] = Db::table('recharge')
                ->where('mode','plat')
                ->where('player_id',$player_id)
                ->where('status',3)
                ->sum('coin');
            $query = new BattleRule();
            $queryInfo = $query->query($player_id);
            $info['queryInfo'] = $queryInfo;

            $inviterInfo = Db::table('player')
                ->alias('t1')
                ->field('t1.name,t1.mobile')
                ->join('invitation t2', 't1.id = t2.inviter_id')
                ->where('t2.invitees_id', $player_id)
                ->find();
            $info['inviter_name'] = isset($inviterInfo['name']) ? $inviterInfo['name'] : '--';

            return result(1,$info,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //编辑玩家
    public function editPlayer(){
        $post = input('post.');

        $playerInfo = [
            'id'       => $post['id'],
            'name'     => $post['name'],
            'img'      => $post['img'],
            'username' => $post['username'],
            'mobile'   => $post['mobile'],

            'email'    => $post['email'],
            'tradeUrl' => $post['tradeUrl'],
            'type'     => $post['type'],//类型：1：正常玩家，2：机器人
            'status'   => $post['status'],//1:
            'allow_to_steam' => $post['allow_to_steam'],//是否允许取回，1：是，2：否:
        ];
        Db::table('player')->update($playerInfo);
        return result(1,'','');
    }

    //修改头像
    public function editHeadImage(){
        $player_id = input('post.id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $file = $_FILES;
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('headerImage',$file,$key);
            Db::table('player')->where('id',$player_id)->setField('img',$url);
            $url = mainName().$url;
            return result(1,$url , '');
        }else{
            return result(0, '', '文件不存在');
        }
    }


    //用户背包
    public function playerPackege(){
        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('player_skins')
            ->where('player_id',$player_id)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('id','desc')
            ->select();
        $total = Db::table('player_skins')
            ->where('player_id',$player_id)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['statusStr'] = $this->getStatusStr($list[$k]['status']);
                $list[$k]['wayStr'] = $this->getWayStr($list[$k]['way']);
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','无数据');
        }
    }

    public function getStatusStr($status)
    {
        switch ($status) {
            case 1:
                return '正常';
                break;
            case 2:
                return '已发货';
                break;
            case 3:
                return '已兑换';
                break;
            case 4:
                return '等待发货';
                break;
            default:
                return 'unknown error';
        }
    }

    //1：盲盒，2：对战，3：幸运饰品，4：免费皮肤，5：商城购买
    public function getWayStr($way)
    {
        switch ($way) {
            case 1:
                return '盲盒';
                break;
            case 2:
                return '对战';
                break;
            case 3:
                return '幸运饰品';
                break;
            case 4:
                return '免费皮肤';
                break;
            case 5:
                return '商城购买';
                break;
            default:
                return 'unknown error';
        }
    }

    //用户充值记录
    public function rechargeList(){

        $player_id = input('post.player_id');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
//        $where[] =

        $list = Db::table('recharge')
            ->where('player_id',$player_id)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('id','desc')
            ->select();
        $total = Db::table('recharge')
            ->where('player_id',$player_id)
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['statusStr'] = $this->getRechargeStr($list[$k]['status']);
            }
            $re = ['total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //状态，1：未支付，2：待支付，3：已支付，4:支付失败
    public function getRechargeStr($status){
        switch ($status) {
            case 1:
                return '未支付';
                break;
            case 2:
                return '待支付';
                break;
            case 3:
                return '已支付';
                break;
            case 4:
                return '支付失败';
                break;
            default:
                return 'unknown error';
        }
    }


    //线下用户充值明细以及总额
    public function offlineRecharge(){
        $player_id = input('post.player_id');
        $status    = input('post.status');
        $startTime = input('post.startTime');
        $endTime   = input('post.endTime');

        $whereTime = [];
        if(!empty($startTime) && empty($endTime)){
            $whereTime[] = ['r.create_time','>=',$startTime.' 00:00:00'];
        }elseif (empty($startTime) && !empty($endTime)){
            $whereTime[] = ['r.create_time','<=',$endTime.' 23:59:59'];
        }else if((!empty($startTime) && !empty($endTime))){
            $range = $startTime.' 00:00:00' . ',' . $endTime.' 23:59:59';
            $whereTime[] = ['r.create_time', 'between', $range];
        }
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $where = $status ? ['r.status'=>$status] : '';


        $list = Db::table('player')
            ->alias('p')
            ->field('p.id as player_id,p.name,r.money,r.mode,r.mobile,r.mode,r.order_num,r.create_time,r.status')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('recharge r','r.player_id = i.invitees_id')
            ->order('r.create_time','desc')
            ->where('i.inviter_id',$player_id)
            ->where($where)
            ->where($whereTime)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('player')
            ->alias('p')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('recharge r','r.player_id = i.invitees_id')
            ->where('i.inviter_id',$player_id)
            ->where($where)
            ->where($whereTime)
            ->count();
        $sumSuccess = Db::table('player')
            ->alias('p')
            ->field('p.id as player_id,p.name,r.money,r.mode,r.mobile,r.mode,r.order_num,r.create_time,r.status')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('recharge r','r.player_id = i.invitees_id')
            ->where('i.inviter_id',$player_id)
//            ->where($where)
            ->where('r.status',3)
            ->where($whereTime)
            ->sum('r.money');

        $sum = Db::table('player')
            ->alias('p')
            ->field('p.id as player_id,p.name,r.money,r.mode,r.mobile,r.mode,r.order_num,r.create_time,r.status')
            ->join('invitation i','i.invitees_id = p.id')
            ->join('recharge r','r.player_id = i.invitees_id')
            ->where('i.inviter_id',$player_id)
//            ->where($where)
            ->where($whereTime)
            ->sum('r.money');
        $sum = number_format($sum, 2, '.', '');
        $sumSuccess = number_format($sumSuccess, 2, '.', '');
        if($total > 0){
            foreach ($list as $k=>$v){
                $list[$k]['statusStr'] = $this->getRechargeStr($list[$k]['status']);
            }
            $re = ['sum'=>$sum,'sumSuccess'=>$sumSuccess,'total'=>$total,'list'=>$list];
            return result(1,$re,'');
        }else{
            $re = ['sum'=>$sum,'sumSuccess'=>$sumSuccess,'total'=>$total,'list'=>$list];
            return result(0,$re,'无数据');
        }
    }


    //editGroup
    public function editGroup(){
        $player_id = input('post.player_id');
        $group     = input('post.group');
//        $group_vip = input('post.group_vip');
        $allow     = input('post.allow');
        $tradeUrl  = input('post.tradeUrl');
        $lxdh  = input('post.lxdh');
        $lxpeople  = input('post.lxpeople');
        $union     = input('post.union');
        $anchor    = input('post.anchor');
        $invite_code    = input('post.invite_code');
        $total_amount    = input('post.total_amount');


        $extra_gift_recharge    = input('post.extra_gift_recharge');

        if(!$player_id){
            return result(0, '', '参数错误');
        }
        if($group<=0){
            $group_vip = '';
        }else if($group == 1){
            $group_vip = '';
        }else{
            $group = '';
            $group_vip = 1;
        }
        $tradeUrlFind = Db::table('player')->where('id',$player_id)->value('tradeUrl');
        $update = [
            'group'=>$group,
            'group_vip'=>$group_vip,
            'allow' => $allow ? $allow : 1,
            'tradeUrl' => $tradeUrl,
            'lxdh' => $lxdh,
            'lxpeople' => $lxpeople,
            'union' => $union,
            'anchor' => $anchor,
            'extra_gift_recharge' => $extra_gift_recharge,
            'invite_code' => $invite_code,
            'total_amount' => $total_amount,
        ];
        if($tradeUrlFind == $tradeUrl){
            unset($update['tradeUrl']);
        }else{
            if(!empty(trim($tradeUrl))){
                $re = $this->bindSteam($player_id,$tradeUrl);
                $re = json_decode($re->getContent(),true);
                if($re['status'] == 0){
                    return result(0,'',$re['msg']);
                }
            }
        }

        Db::table('player')->where('id',$player_id)->update($update);
        $player = Db::table('player')->where('id',$player_id)->find();
        if($anchor == 1){
            $role = Db::table('union_role')->where('account',$player['mobile'])->find();
            if(!$role){
                $unionData = [
                    'name'       => '主播管理员',
                    'account'    => $player['mobile'],
                    'password'   => md5('123456'),
                    'info'       => 'a:2:{s:7:"checked";a:7:{i:0;i:1;i:1;i:101;i:2;i:302;i:3;i:303;i:4;i:305;i:5;i:5;i:6;i:501;}s:11:"halfChecked";a:1:{i:0;i:3;}}',
                    'menu'       => 'a:3:{i:0;a:5:{s:2:"id";i:1;s:5:"title";s:12:"监控中心";s:4:"icon";s:3:"";s:8:"disabled";b:1;s:8:"children";a:1:{i:0;a:5:{s:2:"id";i:101;s:5:"title";s:9:"工作台";s:4:"path";s:18:"/dashboard/console";s:8:"disabled";b:1;s:4:"name";s:9:"工作台";}}}i:2;a:4:{s:2:"id";i:3;s:5:"title";s:12:"主播管理";s:4:"icon";s:3:"";s:8:"children";a:3:{i:0;a:4:{s:2:"id";i:302;s:5:"title";s:12:"主播列表";s:4:"path";s:13:"/user/account";s:4:"name";s:12:"主播列表";}i:1;a:4:{s:2:"id";i:303;s:5:"title";s:12:"成员列表";s:4:"path";s:12:"/user/member";s:4:"name";s:12:"成员列表";}i:2;a:4:{s:2:"id";i:305;s:5:"title";s:12:"充值信息";s:4:"path";s:14:"/user/recharge";s:4:"name";s:12:"充值信息";}}}i:1;a:4:{s:2:"id";i:5;s:5:"title";s:12:"取回信息";s:4:"icon";s:3:"";s:8:"children";a:1:{i:0;a:4:{s:2:"id";i:501;s:5:"title";s:12:"取回列表";s:4:"path";s:11:"/jcsj/index";s:4:"name";s:12:"取回列表";}}}}',
                    'date'       => currentTime(),
                    'des'        => '拥有部分权限',
                    'allow'      => '主播权限',
                    'allowState' => '3',
                    'status'     => '1',
                    'player_id'  => $player_id,
                    'union_id'   => $player['union']
                ];
                Db::table('union_role')->insert($unionData);
            }else{
                Db::table('union_role')->where('account',$player['mobile'])->setField('flag',1);
            }
        }else{
            Db::table('union_role')->where('account',$player['mobile'])->setField('flag',0);
        }
        return result(1,'','');
    }

    //后台绑定Steam账号
    public function bindSteam($player_id=null,$tradeUrl=null)
    {
        $player_id = input('post.player_id');
        $tradeUrl = input('post.tradeUrl');

        $player = [
            'tradeUrl' => $tradeUrl,
            'steamId' => ''
        ];
        Db::table('player')->where('id', $player_id)->update($player);
        return result(1, '', '成功');


        $delInfo = devInfo();
        $url = '/open/v1/seller/steam/bind/';
        $request_url = $delInfo['requestHost'] . $url;
        $params = [
            'app-key' => $delInfo['apiKey'],
            'language' => 'zh_CN'
        ];

        $data = [
            "apikey" => $delInfo['apiKey'],
            "tradeUrl" => $tradeUrl,
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post', json_encode($data));
        $re = json_decode($re, true);
        if (isset($re['success']) && $re['success'] == true) {
            $player = [
                'tradeUrl' => $tradeUrl,
                'steamId' => ''
            ];
            Db::table('player')->where('id', $player_id)->update($player);
            return result(1, '', $re['data']);
        }
        return result(0, '', $re['errorMsg']);
    }


    public function recharge(){
        $player_id = input('post.player_id');
        $recharge  = input('post.recharge');
        if(!$player_id){
            return result(0, '', '参数错误');
        }
        $battleRule = new BattleRule();
        $battleRule->query($player_id);
        if(((float)$recharge)<0.01){
            return result(0, '', '充值金额错误');
        }
        $rechargeInfo = [
            'order_num'   => '',
            'create_time' => currentTime(),
            'money'       => $recharge,
            'coin'        => $recharge,
            'player_id'   => $player_id,
            'mobile'      => '',
            'mode'        => 'plat',
            'status'      => 3
        ];
        $recharge_id = Db::table('recharge')->insertGetId($rechargeInfo);
        $rechargeInfo['id'] = $recharge_id;

        $balance = new Balance();
        $total_amount = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->value('total_amount');
        $re = $balance->opBalance($player_id,$recharge,$total_amount,6);
        $rechargeInfo['total_amount'] = $total_amount;
        if($re){
            $battleRule->editAssets($player_id,$recharge,'','','',$rechargeInfo);
            return result(1,'','');
        }else{
            return result(0,'','');
        }
    }

    //获取工会列表
    public function getUnion(){
        $list = Db::table('union')
            ->field('id,name')
            ->select();
        if($list){
            $re = ['list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','无数据');
        }
    }

    public function delPlayerPackege()
    {
        $id = input('post.id');
        if ($id < 1) {
            return result(0, '', '删除失败');
        } else {
            Db::table('player_skins')->where('id', $id)->setField('flag', 0);
            return result(1, '', '删除成功');
        }
    }
    
    public function updatePssward()
    {
        $account  = input('post.account');
        $confirm_new_password = input('post.confirm_new_password');
        $new_password = input('post.new_password');
        $old_password = input('post.old_password');
        if(!$new_password||!$new_password||$new_password!=$confirm_new_password){
            return result(0, '', '确认密码不一样');
        }
        $admin = Db::table('admin')
            ->where(['account'=>$account,'password'=>md5($old_password),'flag'=>1])
            ->find();
        
        if ($admin) {
            Db::table('admin')->where(['account'=>$account,'flag'=>1])->update(['password'=>md5($new_password)]);
            return result(1, '', '修改密码成功');
        } else {
            
            return result(0, '', '旧密码不正确');
        }
    }


    public function sendSkinToSteam()
    {
        $player_skins_id = input('post.id');
        $player_id = input('post.player_id');
        $itemId = input('post.item_id');

        if (!$player_id || !$player_skins_id || !$itemId) {
            return result(0, '', '参数错误');
        }

        $indexUser = new IndexUser();
        $ret = $indexUser->sendSkinToSteam($player_skins_id, $player_id, $itemId);
        return $ret;
    }


}