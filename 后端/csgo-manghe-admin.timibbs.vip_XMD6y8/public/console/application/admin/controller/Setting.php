<?php


namespace app\admin\controller;


use think\Db;

class Setting
{
    public function chargeSetList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('set_charge')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('set_charge')->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }

    }
    //充值设置
    public function setCharge(){
        $data = json_decode(input('post.data'),true);
        $info = [
            'money'       => $data['money'],
            'create_time' => currentTime()
        ];
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('setting',$file,$key);
        }
        if($url){
            $info['img'] = $url;
        }
        Db::table('set_charge')->insert($info);
        return result(1, '', '');
    }

    //修改充值设置
    public function editCharge(){
        $data = json_decode(input('post.data'),true);
        $id = $data['id'];
        $info = [
            'money' => $data['money'],
            'create_time'     => currentTime()
        ];
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        if($url){
            $info['img'] = $url;
        }
        Db::table('set_charge')->where('id',$id)->update($info);
        return result(1, '', '');
    }

    //删除充值设置
    public function delCharge(){
        $id   = input('post.id');
        Db::table('set_charge')->where('id',$id)->delete();
        return result(1, '', '');
    }


    //汇率
    public function getExchangeRate(){
        $list = Db::table('set_exchange_rate')->select();
        $res = ['total'=>count($list),'list'=>$list];
        return result(1, $res, '');
    }
    //充值汇率设置
    public function exchangeRate(){
        $exchange_rate = input('post.exchange_rate');
        $bili = input('post.bili');
        if(empty(trim($exchange_rate)) || ($exchange_rate <= 0) || ($bili <= 0)){
            return result(0, '', '值有误，请检查');
        }
        $rate = Db::table('set_exchange_rate')->find();
        $data['exchange_rate'] = $exchange_rate;
        $data['bili'] = $bili;
        //修改比例后，统一把平台饰品价格修改
        $all_skins = Db::table('all_skin')->field('id,market_price')->where('flag',1)->select();
        if(count($all_skins)>0){
            foreach ($all_skins as $k=>$v){
                $market_price = $all_skins[$k]['market_price'];
                if($market_price>0){
                    $price = keep_decimal($market_price * $bili);
                    Db::table('all_skin')->where('id',$all_skins[$k]['id'])->setField('price',$price);
                }
            }
        }
        if($rate){
            Db::table('set_exchange_rate')->where('id',$rate['id'])->update($data);
        }else{
            $data['create_time'] = currentTime();
            Db::table('set_exchange_rate')->insert($data);
        }
        return result(1, '', '');
    }

    //充值赠送
    public function giveList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('recharge_give')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('recharge_give')->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }

    //新增赠送比例
    public function setChargeGive(){
        $data = json_decode(input('post.data'),true);
        $info = [
            'money'       => $data['money'],
            'billie'      => $data['billie'],
            'create_time' => currentTime()
        ];
        Db::table('recharge_give')->insert($info);
        return result(1, '', '');
    }

    //修改赠送比例
    public function editChargeGive(){
        $data = json_decode(input('post.data'),true);
        $id = $data['id'];
        $info = [
            'money'       => $data['money'],
            'billie'      => $data['billie'],
            'create_time' => currentTime()
        ];
        Db::table('recharge_give')->where('id',$id)->update($info);
        return result(1, '', '');
    }

    //删除赠送比例
    public function delChargeGive(){
        $id   = input('post.id');
        Db::table('recharge_give')->where('id',$id)->delete();
        return result(1, '', '');
    }

    //网站背景图
    public function getImg(){
        $re = Db::table('set_background')
            ->where('flag',1)
            ->find();
        if($re){
            return result(1, $re, '');
        }else{
            return result(0, '', '无数据');
        }
    }

    //新增背景图
    public function addBackground(){
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        $re = Db::table('set_background')->find();
        if($re){
            $update['img'] = $url;
            $update['create_time'] = currentTime();
            $update['flag'] = 1;
            Db::table('set_background')->where('id',$re['id'])->update($update);
        }else{
            $insert['img'] = $url;
            $insert['create_time'] = currentTime();
            Db::table('set_background')->insert($insert);
        }
        return result(1, '', '');
    }

    //删除背景图
    public function del(){
        $id = input('post.id');
        Db::table('set_background')->where('id',$id)->setField('flag',0);
        return result(1, '', '');
    }


    //oss存储设置
    public function storageList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('file_storage')
            ->where('flag',1)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('file_storage')->where('flag',1)->count();
        if($total>0){
            $re = ['total'=>$total,'list'=>$list];
            return result(1, $re, '');
        }else{
            $re = ['total'=>0,'list'=>[]];
            return result(1, $re, '无数据');
        }
    }

    //新增/编辑储存设置
    public function editStorage(){
        $post = input('post.');
        if($post['id']>0){
            Db::table('file_storage')->where('id',$post['id'])->update($post);
        }else{
            Db::table('file_storage')->insert($post);
        }
        return result(1, '', '');
    }

    //修改储存状态
    public function editStorageStatus(){
        $id = input('post.id');
        $status = input('post.status');
        if($id>0){
            if($status){
                $status = 1;
                Db::table('file_storage')->where('status',1)->setField('status',0);
                Db::table('file_storage')->where('id',$id)->setField('status',$status);
            }else{
                $status = 0;
                Db::table('file_storage')->where('id',$id)->setField('status',$status);
            }
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }

    //删除储存配置
    public function delStorage(){
        $id = input('post.id');
        if($id>0){
            Db::table('file_storage')->where('id',$id)->setField('flag',0);
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }

    //扎比特信息
    public function zbtList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $list = Db::table('zbt')
            ->where('flag',1)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('zbt')->where('flag',1)->count();
        if($total>0){
            $re = ['total'=>$total,'list'=>$list];
            return result(1, $re, '');
        }else{
            $re = ['total'=>0,'list'=>[]];
            return result(1, $re, '无数据');
        }
    }
    //新增/编辑扎比特设置
    public function editZbt(){
        $post = input('post.');
        if($post['id']>0){
            Db::table('zbt')->where('id',$post['id'])->update($post);
        }else{
            Db::table('zbt')->insert($post);
        }
        return result(1, '', '');
    }
    //修改扎比特状态
    public function editZbtStatus(){
        $id = input('post.id');
        $status = input('post.status');
        if($id>0){
            if($status){
                $status = 1;
                Db::table('zbt')->where('status',1)->setField('status',0);
                Db::table('zbt')->where('id',$id)->setField('status',$status);
            }else{
                $status = 0;
                Db::table('zbt')->where('id',$id)->setField('status',$status);
            }
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }
    //删除扎比特配置
    public function delZbt(){
        $id = input('post.id');
        if($id>0){
            Db::table('zbt')->where('id',$id)->setField('flag',0);
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }

    //区间规则设置
    public function gameSetList(){
        $model = input('post.model');
        $list = [];
        if($model == 'open'){
            $list = Db::table('data_set_open')
                ->where('flag',1)
                ->select();
        }else if ($model == 'battle'){
            $list = Db::table('data_set')
                ->where('flag',1)
                ->select();
        }
        if($list){
            $re = ['total'=>count($list),'model'=>$model,'list'=>$list];
            return result(1,$re,'');
        }else{
            $re = ['total'=>0,'model'=>$model,'list'=>[]];
            return result(0,$re,'');
        }
    }

    //修改区间规则
    public function editGame(){
        $id   = input('post.id');
        $type = input('post.type');
        $data = input('post.');
        $model = input('post.model');
        if($model == 'open'){
            unset($data['model']);
            if($id>0){
                $data_set = Db::table('data_set_open')->where('type',$type)->where('flag',1)->select();
                if(count($data_set) > 1){
                    return result(0,'','已存在相同设置');
                }else{
                    if($data['max_percent'] == 0){
                        $data['bili2'] = 0;
                        $data['bili3'] = 0;
                        $data['bili4'] = 0;
                        $data['percent2'] = 0;
                        $data['percent3'] = 0;
                        $data['percent4'] = 0;
                        $data['limit_times'] = 0;
                        $data['up_percent'] = 0;
                        $data['recharge_up_percent'] = 0;
                    }
                    Db::table('data_set_open')->where('id',$id)->update($data);
                    $data_set_update['min_percent'] = $data['min_percent'];
                    $data_set_update['max_percent'] = $data['max_percent'];
                    $data_set_update['limit_times'] = $data['limit_times'];
                    Db::table('data_set')->where('type',$type)->where('flag',1)->update($data_set_update);
                    return result(1,'','');
                }
            }else{
                $data_set = Db::table('data_set_open')->where('type',$type)->where('flag',1)->select();
                if($data_set){
                    return result(0,'','已存在相同设置');
                }else{
                    if($data['max_percent'] == 0){
                        $data['bili2'] = 0;
                        $data['bili3'] = 0;
                        $data['bili4'] = 0;
                        $data['percent2'] = 0;
                        $data['percent3'] = 0;
                        $data['percent4'] = 0;
                        $data['limit_times'] = 0;
                        $data['up_percent'] = 0;
                        $data['recharge_up_percent'] = 0;
                    }
                    Db::table('data_set_open')->insert($data);
                    return result(1,'','');
                }
            }
        }else if ($model == 'battle'){
            unset($data['model']);
            if($id>0){
                $data_set = Db::table('data_set')->where('type',$type)->where('flag',1)->select();
                if(count($data_set) > 1){
                    return result(0,'','已存在相同设置');
                }else{
                    Db::table('data_set')->where('id',$id)->update($data);
                    $data_set_open_update['min_percent'] = $data['min_percent'];
                    $data_set_open_update['max_percent'] = $data['max_percent'];
                    $data_set_open_update['limit_times'] = $data['limit_times'];
                    Db::table('data_set_open')->where('type',$type)->where('flag',1)->update($data_set_open_update);
                    return result(1,'','');
                }
            }else{
                $data_set = Db::table('data_set')->where('type',$type)->where('flag',1)->select();
                if($data_set){
                    return result(0,'','已存在相同设置');
                }else{
                    Db::table('data_set')->insert($data);
                    return result(1,'','');
                }
            }
        }else{
            return result(0,'','错误');
        }
    }

    //删除区间规则
    public function delGameSet(){
        $model = input('post.model');
        $id    = input('post.id');
        return result(0,'','不可刪除');
        if(empty(trim($id))){
            return result(0,'','参数有误');
        }
        if($model == 'open'){
            Db::table('data_set_open')->where('id',$id)->setField('flag',0);
            return result(1,'','');
        }else if ($model == 'battle'){
            Db::table('data_set')->where('id',$id)->setField('flag',0);
            return result(1,'','');
        }else{
            return result(0,'','错误');
        }
    }

    //获取开箱模式--库存/资产区间
    public function getModel(){
        $model = Db::table('open_model')->find();
        if($model){
            return result(1,$model,'');
        }else{
            return result(0,'','');
        }
    }

    public function editModel(){
        $id = input('post.id');
        $status = input('post.status');
        if($status){
            $model = 'range';
        }else{
            $model = 'stock';
        }
        if($id>0){
            Db::table('open_model')->where('id',$id)->setField('model',$model);
            return result(1,'','');
        }else{
            $insert['model'] = $model;
            Db::table('open_model')->insert($insert);
            return result(1,'','');
        }
    }

    //获取邀请佣金信息
    public function getInviteInfo(){
        $list = Db::table('invite_set')->select();
        if($list){
            $re = ['total'=>count($list),'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','');
        }
    }
    //新增编辑邀请佣金
    public function editInviteInfo(){
        $data = input('post.data');
        if($data['id']>0){
            Db::table('invite_set')->where('id',$data['id'])->update($data);
        }else{
            Db::table('invite_set')->insert($data);
        }
        return result(1,'','');
    }

    //短信宝
    public function dxbList(){
        $list = Db::table('set_dxb')->where('flag',1)->select();
        if($list){
            $re = ['total'=>count($list),'list'=>$list];
            return result(1,$re,'');
        }else{
            return result(0,'','');
        }
    }

    //新增/编辑短信宝
    public function editDxb(){
        $id = input('post.id');
        $post = input('post.');
        $status = input('post.status');
        if($id>0){
            $update['account']  = $post['account'];
            $update['password'] = $post['password'];
            $update['remarks']  = $post['remarks'];
            Db::table('set_dxb')->where('id',$id)->update($update);
            return result(1,'','');
        }else{
            $insert['account']  = $post['account'];
            $insert['password'] = $post['password'];
            $insert['remarks']  = $post['remarks'];
            $insert['create_time']  = currentTime();
            Db::table('set_dxb')->insert($insert);
            return result(1,'','');
        }
    }

    //修改启用状态
    public function editDxbStatus(){
        $id = input('post.id');
        $status = input('post.status');
        if($id>0){
            if($status){
                $status = 1;
                Db::table('set_dxb')->where('status',1)->setField('status',0);
                Db::table('set_dxb')->where('id',$id)->setField('status',$status);
            }else{
                $status = 0;
                Db::table('set_dxb')->where('id',$id)->setField('status',$status);
            }
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }

    //删除
    public function delSDxb(){
        $id = input('post.id');
        if($id>0){
            Db::table('set_dxb')->where('id',$id)->setField('flag',0);
            return result(1, '', '');
        }else{
            return result(0, '', '');
        }
    }

}