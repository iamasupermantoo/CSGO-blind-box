<?php


namespace app\admin\controller;
use app\index\controller\Battle;
use app\index\controller\Box;

use think\Db;

class Task
{
    public function writeContent(){
//        file_put_contents("Content.txt",currentTime().":我执行了一次\n",FILE_APPEND);//记录日志
//        file_put_contents("Content.txt", "");
        dd(file_get_contents("Content.txt"));
    }

    //开启/重启任务
    public function start(){
        $id       = input('post.id');
        $type     = input('post.type');
        $status   = input('post.status');
        if($type == 1){
            if($status){
                Db::table('task')->where(['id '=>$id])->setField('status',1);
            }else{
                Db::table('task')->where(['id '=>$id])->setField('status',0);
                return result(1,'','');
            }
            $taskInfo = Db::table('task')->where(['id '=>$id])->find();
            $type     = $taskInfo['type'];
            $orderNum = $taskInfo['orderNum'];
            if($orderNum == 1){
                $this->battleTask1($type,$orderNum);
            }else if($orderNum == 2){
                $this->battleTask2($type,$orderNum);
            }else if($orderNum == 3){
                $this->battleTask3($type,$orderNum);
            }else{
                return ['status'=>0,'msg'=>'暂无任务'];
            }
            return result(1,'','');
        }else if($type == 2){
            $re = $this->openBoxTask($id,$status ? 1:2);
            $re = json_decode($re->getContent(),true);
            if($re['status'] == 1){
                return result(1,'','');
            }else{
                return result(0,'','');
            }
        }

    }

    //对战任务
    function battleTask1($type=null,$orderNum=null){
        ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
        $type = $type ? $type : 1;
        $orderNum = $orderNum ? $orderNum : 1;
        $taskInfo = Db::table('task')->where(['type'=>$type,'orderNum'=>$orderNum])->find();
        $time = rand($taskInfo['min'],$taskInfo['max']);
        $url = mainName1().'/admin/Task/battleTask1';
        if($taskInfo['status'] == 1){
//            file_put_contents("check.txt",currentTime().":我执行了一次\n",FILE_APPEND);//记录日志
            $battle = Db::table('battle')
                ->where('status',1)
                ->whereOr('status',2)
//                ->order('status','desc')
                ->select();
            if($battle){
                $k = rand(0,count($battle)-1);
                if($battle[0]['status'] == 2){
                     $k = 0;
                }
                $rand = rand(1,5);
//                dump(count($battle));
//                dd($rand);
                if((count($battle) >= 1) && ($rand > 1)){
                    if($battle[$k]['status'] == 1){
                        //加入对战
                        $boxInfo = json_decode($battle[$k]['boxInfo'],true);
                        $this->addBattle($battle[$k]['id'],$boxInfo,$orderNum);
                    }else if($battle[$k]['status'] == 2){
                        $box_num = json_decode($battle[$k]['boxInfo'],true)[0]['num'];
                        $create_time = $battle[$k]['create_time'];
                        $update_time = $battle[$k]['update_time'];
                        $difference = strtotime($update_time) - strtotime($create_time);//时间差
                        if($difference >= ($box_num*5)){
                            //将有以外情况下一直处于进行中的对战强制结束
                            $this->sleepSetBattleStatus(0,$battle[$k]['id']);
                        }
                    }
                }else{
                    $this->createBattle($orderNum);
                }
            }else{
                $this->createBattle();
            }
            // echo 'ok';
            sleep($time);
            file_get_contents($url);
        }else{
            return result(0, '', '任务已关闭');
        }
    }

    //对战任务
    function battleTask2($type=null,$orderNum=null){
        ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
        $type = $type ? $type : 1;
        $orderNum = $orderNum ? $orderNum : 2;
        $taskInfo = Db::table('task')->where(['type'=>$type,'orderNum'=>$orderNum])->find();
        $time = rand($taskInfo['min'],$taskInfo['max']);
        $url = mainName1().'/admin/Task/battleTask2';
        if($taskInfo['status'] == 1){
            $battle = Db::table('battle')
                ->where('status',1)
//                ->order('status','desc')
                ->select();
            if($battle){
                $k = rand(0,count($battle)-1);
                $rand = rand(1,4);
                if((count($battle)>=5) && ($rand>=1)){
                    if($battle[$k]['status'] == 1){
                        //加入对战
                        $boxInfo = json_decode($battle[$k]['boxInfo'],true);
                        $this->addBattle($battle[$k]['id'],$boxInfo,$orderNum);
                    }else if($battle[$k]['status'] == 2){
                        $this->sleepSetBattleStatus(0,$battle[$k]['id']);
                    }
                }else{
                    $this->createBattle();
                }
            }else{
                $this->createBattle();
            }
            // echo 'ok';
            sleep($time);
            file_get_contents($url);
        }else{
            return result(0, '', '任务已关闭');
        }
    }

    //对战任务
    function battleTask3($type=null,$orderNum=null){
        ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
        $type = $type ? $type : 1;
        $orderNum = $orderNum ? $orderNum : 3;
        $taskInfo = Db::table('task')->where(['type'=>$type,'orderNum'=>$orderNum])->find();
        $time = rand($taskInfo['min'],$taskInfo['max']);
        $url = mainName1().'/admin/Task/battleTask3';
        if($taskInfo['status'] == 1){
//            file_put_contents("check.txt",currentTime().":我执行了一次\n",FILE_APPEND);//记录日志
            $battle = Db::table('battle')
                ->where('status',1)
                ->whereOr('status',2)
//                ->order('status','desc')
                ->select();
            if($battle){
                $k = rand(0,count($battle)-1);
                $rand = rand(1,4);
                if((count($battle)>=5) && ($rand>=1)){
                    if($battle[$k]['status'] == 1){
                        //加入对战
                        $boxInfo = json_decode($battle[$k]['boxInfo'],true);
                        $this->addBattle($battle[$k]['id'],$boxInfo,$orderNum);
                    }else if($battle[$k]['status'] == 2){
                        $this->sleepSetBattleStatus(0,$battle[$k]['id']);
                    }
                }else{
                    $this->createBattle();
                }
            }else{
                $this->createBattle();
            }
            // echo 'ok';
            sleep($time);
            file_get_contents($url);
        }else{
            return result(0, '', '任务已关闭');
        }
    }

    //创建对战
    public function createBattle(){
        //创建对战
        $findRo = $this->findRo();
        if(!isset($findRo['status'])){
            $Battle = new Battle();
            $mode = rand(0,10);
            // $mode = (($mode>=2) && ($mode<7)) ? 2 : (($mode == 7) ? 3 : 4);
            $mode = ($mode == 3) ? 3 : (($mode==4) ? 4 : 2);
            $battleBoxList= $Battle->battleBoxList();
            $boxs = json_decode($battleBoxList->getContent(),true)['data'];
            $count = count($boxs);
//                    if($count<1){
//                        return ['status'=>0,'msg'=>'没有箱子'];
//                    }
            $lenMax =  rand(1,6);//箱子数量
            //随机选择$lenMax个箱子
            $boxSInfo = [];
            for($i=0;$i<$lenMax;$i++){
                $box = $boxs[rand(0,$count-1)];
                $boxInfo['box_id'] = $box['id'];
                $boxInfo['num'] = 1;
                $boxInfo['name'] = $box['name'];
                $boxSInfo[] = $boxInfo;
            }
            $player_id = $findRo['id'];
            $Battle->createRoom($mode,$boxSInfo,$player_id);
        }
    }

    public function addBattle($battle_id=null,$boxInfo=array(),$orderNum){
        //加入对战
        // $boxInfo = json_decode($battle[$k]['boxInfo'],true);
        $findRo = $this->findRo($boxInfo);
        if(!isset($findRo['status'])){
            $Battle = new Battle();
            $player_id = $findRo['id'];
            $re = $Battle->addBattle($battle_id,$player_id);
            $re = json_decode($re->getContent(),true);
            if(($re['status']==1) && ($re['data']['battle_status']=='start')){
                $this->sleepSetBattleStatus(($findRo['numTotal'] * 5),$battle_id);
            }
            $update['wait'] = ($findRo['numTotal'] * 5) + 5;
            $update['wait_time'] = date('Y-m-d H:i:s',time()+$update['wait']);
        }
    }

    public function sleepSetBattleStatus($sec=null,$battle_id=null){
        sleep($sec);
        $Battle = new Battle();
        $Battle->setBattleStatus($battle_id);
    }


    function config1(){
        return 0;
    }

    function config2(){
        return 0;
    }

    function config3(){
        return 0;
    }


    //随机一个robot加入对战
    public function findRo($boxInfo=array()){
        $total = 0;
        $numTotal = 0;
        if($boxInfo){
            foreach ($boxInfo as $k=>$v){
                $num = $boxInfo[$k]['num'];
                for ($i=0;$i<$num;$i++){
                    $total += $boxInfo[$k]['price'];
                    $numTotal++;
                }
            }
        }
        $list = Db::table('player')
            ->field('id,name,img')
            ->where(['type'=>2])
            ->where('total_amount','>=',$total)
            ->select();
        $total = count($list);//机器人数量
        if($total<1){
            return ['status'=>0,'msg'=>'没有可用rbt'];
        }
        $rand = rand(0,$total-1);
        $info = $list[$rand];
        $info['img'] = $info['img'] ? mainName().$info['img'] : '';
        $info['total'] = $total;
        $info['numTotal'] = $numTotal;
        return $info;
//        return result(1,$info,'');
    }

    public function openBoxTask($id=null,$status=null){
        ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行.
        if($status == 1){
            $status = true;
        }else if ($status == 2){
            $status = false;
        }else if($status == null){
            $status = true;
        }
        if($status){
            if($id == null){
//                Db::table('task')->where('type',2)->setField('status',1);
                $taskInfo = Db::table('task')->where('type',2)->find();
            }else{
                Db::table('task')->where('id',$id)->setField('status',1);
                $taskInfo = Db::table('task')->where('id',$id)->find();
            }
            $url = mainName1().'/admin/Task/openBoxTask';

            if($taskInfo['status'] == 1){
                $findRo = $this->findRo();
                $player_id = $findRo['id'];
                $Box = new Box();
                $boxS = $Box->boxList();
                $boxS = json_decode($boxS->getContent(),true)['data'];//箱子分类列表
                $boxSLen = count($boxS);
                $k = rand(0,$boxSLen-1);
                $box = $boxS['list'][$k]['box_list'];//箱子列表
                $boxLen = count($box);
                $k1 = rand(0,$boxLen-1);
                $boxInfo = $box[$k1];
                $num = rand(1,10);
                $num = $num <=4 ? 1 :($num <= 6 ? 2 :( $num <= 8 ? 3 : ($num <=9 ? 4 : 5)));
                $Box->buyBox($player_id,$boxInfo['id'],$num,1,'',-1,'task');
                $time = rand($taskInfo['min'],$taskInfo['max']);
                sleep($time);
                file_get_contents($url);
            }
        }else{
            if($id == null){
                Db::table('task')->where('type',2)->setField('status',0);
            }else{
                Db::table('task')->where('id',$id)->setField('status',0);
            }
        }
        return result(1,'','');
    }

    public function openConfig(){
        return 1;
    }



}