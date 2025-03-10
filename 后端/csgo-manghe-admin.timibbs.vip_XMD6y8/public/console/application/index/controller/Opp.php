<?php


namespace app\index\controller;


use think\Db;

class Opp{
    //给箱子补货
    public $text = '';
    public function mend($box_id=null,$text=null){
        if(trim($box_id)<0){
            return;
        }
        $this->text = $text;
        $stockInfo = Db::table('box_skins')
            ->field("id,set_$text as $text")
            ->where('box_id',$box_id)
            ->select();
        $sum = array_sum(array_map(function($val){return $val[$this->text];},$stockInfo));
        if($sum <= 0){
            return false;
        }
        if($stockInfo){
            foreach ($stockInfo as $k=>$v){
                Db::table('box_skins')->where('id',$stockInfo[$k]['id'])->update($stockInfo[$k]);
            }
        }
    }

}
