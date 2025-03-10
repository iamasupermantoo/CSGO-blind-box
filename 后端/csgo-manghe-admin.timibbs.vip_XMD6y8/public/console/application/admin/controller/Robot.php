<?php


namespace app\admin\controller;


use think\Db;

class Robot
{
    public function robotList(){
        $page     = input('post.page');
        $pageSize = input('post.pageSize');
        $list = Db::table('player')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where(['flag'=>1,'type'=>2])
            ->select();
        $total = Db::table('player')
            ->where(['flag'=>1,'type'=>2])
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] :'';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }

    //新增
    public function addRobot(){
        $data = json_decode(input('post.data'),true);
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $total_amount = isset($data['total_amount']) ? $data['total_amount'] : '';

        $insertData['mobile']       = $mobile;
        $insertData['name']         = $name;
        $insertData['total_amount'] = $total_amount;
        $insertData['create_time']  = currentTime();
        $insertData['type']         = 2;
        $insertData['group']        = 1;
        $insertData['union']        = 0;

        $robot = Db::table('player')->where(['name'=>$name,'type'=>2])->find();
        if($robot){
            return result(0, '', 'Robot：【'.$name.'】已存在');
        }
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        if($url){
            $insertData['img'] = $url;
        }
        Db::table('player')->insertGetId($insertData);
        return result(1, '', '');
    }

    //编辑
    public function edit(){
        $data = json_decode(input('post.data'),true);
        $id = isset($data['id']) ? $data['id'] : '';
        if(!trim($id)){
            return result(0, '', '缺少机器人id');
        }
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $total_amount = isset($data['total_amount']) ? $data['total_amount'] : '';

        $update['mobile']       = $mobile;
        $update['name']         = $name;
        $update['total_amount'] = $total_amount;
        $update['create_time']  = currentTime();
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        if($url){
            $update['img'] = $url;
        }
        Db::table('player')->where('id',$id)->update($update);
        return result(1, '', '');
    }

    //删除
    public function del(){
        $id = input('post.id');
        Db::table('player')->where(['id'=>$id,'type'=>2])->setField('flag',2);
        return result(1, '', '');
    }


    //机器人任务
    public function getTask(){
        $page     = input('post.page');
        $pageSize = input('post.pageSize');
        $list = Db::table('task')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->order('type','asc')
            ->select();
        $total = Db::table('task')
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0, '', '无数据');
        }
    }

    //新增机器人任务
    public function addTask(){
        $data = input('post.');
        $insertData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'min'  => $data['min'],
            'max'  => $data['max'],
            'create_time' => currentTime()
        ];
        $taskS = [];

        if(empty($data['id'])){
            if($data['type'] == 1){
                $taskS = Db::table('task')
                    ->where('type',$insertData['type'])
                    ->select();
                if(count($taskS)>=3){
                    return result(0, '', '当前任务已上限');
                }
            }else if($data['type'] == 2){
                $taskS = Db::table('task')
                    ->where('type',$insertData['type'])
                    ->select();
                if(count($taskS)>=1){
                    return result(0, '', '当前任务已上限');
                }
            }
            $orderNums = [];
            if($taskS){
                foreach ($taskS as $k=>$v){
                    $orderNums[] = $taskS[$k]['orderNum'];
                }
            }
            $range = range(1,3);
            $re = array_diff($range,$orderNums);
            $re = array_values($re);
            $insertData['orderNum'] = $re[0];
            Db::table('task')->insert($insertData);
        }else{
            $insertData['id'] = $data['id'];
            Db::table('task')->where('id',$data['id'])->update($insertData);
        }
        return result(1, '', '');
    }

    // 编辑余额
    public function editBalance(){
        $balance = input('post.balance', 0, 'intval');
        if (empty($balance)) {
            return result(0, '', '参数异常!');
        }
        if ($balance < 0) {
            Db::table('player')->where(['type' => 2])->setDec('total_amount', abs($balance));
        } else {
            Db::table('player')->where(['type' => 2])->setInc('total_amount', $balance);
        }

        return result(1, '', '');
    }


}