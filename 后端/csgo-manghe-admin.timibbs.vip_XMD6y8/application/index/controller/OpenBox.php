<?php


namespace app\index\controller;

use think\Db;

class OpenBox
{
    public $multiplier = 1;

    //开箱结果(正常开奖)
    public function getResult($box_id=null,$player_id=null,$box_name=null)
    {
        $box_id    = input('post.box_id',$box_id);
        $player_id = input('post.player_id',$player_id);
        $box_name  = input('post.box_name',$box_name);
        if(!$box_id || !$box_name){
            return result(0, '', '盲盒信息错误');
        }
        if(!$player_id){
            return result(0, '', '玩家信息错误');
        }
        $prizes = Db::table('box_skins')
            ->field('id,appId,itemId,name,img,price,probability,type_id,delivery,buyPrice,maxPrice')
            ->where(['box_id'=>$box_id,'flag'=>1])
            ->select();
        $probabilitys = [];
        $totalmultiplier = 0;
        if($prizes){
            foreach ($prizes as $k=>$v){
                array_push($probabilitys,$prizes[$k]['probability']);
                $totalmultiplier += $prizes[$k]['probability'];
            }
        }
        $min = min($probabilitys);//最小概率百分数（%），eg：0.9

        $this->setMultiplier($min,$this->multiplier);
        //生成随机范围
        $rand = rand(1,$this->multiplier*100);
//        dump($rand);
//        dump('范围：'.$this->multiplier*100);
//        dump('中奖概率：'.$totalmultiplier.'%');
        //中奖范围
        $prizeArr = [];
        $total = 0 ;
        if($probabilitys){
            foreach ($probabilitys as $key=>$val){
                $total += $probabilitys[$key]*$this->multiplier;
                array_push($prizeArr,$probabilitys[$key]*$this->multiplier);
            }
        }
        if($totalmultiplier < 100){
            //添加一个空奖（所有奖品概率加起来为100%）
            $no_prize = [
                'id' => '0',
                'name' => '未中奖',
                'probability' => 100 - $totalmultiplier,//概率（%）
            ];
            array_push($prizes,$no_prize);
        }
        if($prizes){
            $start = 0;
            foreach ($prizes as $k=>$v){
                $prizes[$k]['v'] = $prizes[$k]['probability'] * $this->multiplier;
                $prizes[$k]['open_number'] = $rand;
                $prizes[$k]['open_number_md'] = md5($rand);
                //中奖范围
                if($k == 0){
                    if($rand <=  $prizes[$k]['v']){
                        //中奖奖品
                        if($prizes[$k]['id']>0){
                            $insert['player_id'] = $player_id;
                            $insert['box_id']    = $box_id;
                            $insert['box_name']  = $box_name;
                            $insert['skin_id']   = $prizes[$k]['id'];
                            $insert['skin_name'] = $prizes[$k]['name'];
                            $insert['type']      = 1;
                            $insert['create_time'] = date('Y-m-d H:i:s');
                            return $prizes[$k];
//                            return result(1,$prizes[$k],'恭喜中奖('.$prizes[$k]['name'].')');
                        }else{
                            return $prizes[$k];
//                            return result(1,'','很遗憾，再接再厉');
                        }
                    }
                }else{
                    $start += $prizes[$k-1]['v'];
                    $end   = $start + $prizes[$k]['v'];
                    if(($start < $rand) && ($rand <= $end)){
                        //中奖奖品
                        if($prizes[$k]['id']>0){
                            $insert['player_id'] = $player_id;
                            $insert['box_id'] = $box_id;
                            $insert['box_name'] = $box_name;
                            $insert['skin_id'] = $prizes[$k]['id'];
                            $insert['skin_name'] = $prizes[$k]['name'];
                            $insert['type']      = 1;
                            $insert['create_time'] = date('Y-m-d H:i:s');
                            return $prizes[$k];
//                            return result(1,$prizes[$k],'恭喜中奖('.$prizes[$k]['name'].')');
                        }else{
                            return $prizes[$k];
//                            return result(1,'','很遗憾，再接再厉');
                        }
                    }
                }
            }
        }
    }


    //设置最终乘数
    public function setMultiplier($min=null,$multiplier=null){
        $res = $min * $multiplier;
        if(($res >= 1) && (($res/1) == (int)($res/1) )){
            $this->multiplier =  $multiplier;//最终乘数
        }else{
            $multiplier = $multiplier * 10;
            $this->setMultiplier($min,$multiplier);
        }
    }

}