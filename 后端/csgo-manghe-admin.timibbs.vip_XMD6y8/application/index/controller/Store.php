<?php


namespace app\index\controller;

use think\Db;

class Store
{
    //商城饰品列表
    public function skinList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $order = input('post.order','desc');

        $minPrice = input('post.minPrice');
        $maxPrice = input('post.maxPrice');

        if(($minPrice>0) && ($maxPrice>0)){
            $range = $minPrice . ',' . $maxPrice;
            $where[] = ['price', 'between', $range];
        }else if(($minPrice>0) && ($maxPrice == 0)){
            $where[] = ['price' ,'>=', $minPrice];
        }else if(($minPrice == 0) && ($maxPrice > 0)){
            $where[] = ['price' ,'<=', $maxPrice];
        }else{
            $where = '';
        }

        $list = Db::table('all_skin')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where($where)
            ->where(['flag'=>1,'sale'=>1])
            ->order(['price'=> $order])
            ->select();

        $total = Db::table('all_skin')
            ->where($where)
            ->where(['flag'=>1,'sale'=>1])
            ->count();
        if ($total>0) {
            $data['total'] = $total;
            $data['list']  = $list;
            return result(1,$data,'');
        }else{
            return result(0,'','无数据');
        }
    }


    //购买饰品
    public function buy(){
        $skin_info = input('post.skin_info');
        $player_id = input('post.player_id');

//        $player_id = 1;
//        $skin_info = [
//            [
//                'id'=>7,
//                'name'=>'名称1',
//                'price' => 0.22,
//                'num' => 1
//            ],
//            [
//                'id'=>8,
//                'name'=>'名称2',
//                'price' => 0.21,
//                'num' => 2
//            ]
//        ];

            $player_info = Db::table('player')->where(['id'=>$player_id,'flag'=>1])->find();
            if(!$player_info){
                return result(0,'','玩家信息不存在');
            }
            $battleRule = new BattleRule();
            $battleRule->query($player_id);

            if($skin_info){
                $total = 0.00;
                $buyInfo = [];
                foreach ($skin_info as $k => $v){
                    $num = $skin_info[$k]['num'];
                    $one = Db::table('all_skin')
                        ->where(['id'=>$skin_info[$k]['id'],'flag'=>1,'sale'=>1])
                        ->find();
                    if(!$one){
                        return result(0,'','购买商品出错，请稍后再试');
                    }
                    for ($i = 0;$i < $num;$i++){
                        $total += (float)$one['price'];
                        $buyInfo[] = $one;
                    }
                    if((float)$one['price'] != (float)$skin_info[$k]['price']){
                        return result(0,'','购买商品出错，请稍后再试');
                    }
                }
                if((float)$player_info['total_amount'] < $total){
                    return result(0,'','余额不足');
                }
            Db::startTrans();
            try {
                foreach ($buyInfo as $k => $v){
                    $player_skin = [
                        'player_id'   => $player_id,
                        'appId'       => $buyInfo[$k]['appId'],
                        'itemId'      => $buyInfo[$k]['itemId'],
                        'name'        => $buyInfo[$k]['itemName'],
                        'img'         => $buyInfo[$k]['imageUrl'],
                        'price'       => $buyInfo[$k]['price'],
                        'priceInfo'   => $buyInfo[$k]['priceInfo'],
                        'create_time' => currentTime(),
                        'way'         => 5,
                        'order'       => makeOder('B')
                    ];
                    //扣库存
                    $stock = Db::table('all_skin')->where('id',$buyInfo[$k]['id'])->value('stock');
                    if($stock<1){
                        return result(0,'','商品库存不足');
                    }
                    Db::table('player_skins')->insert($player_skin);
                    Db::table('all_skin')->where('id',$buyInfo[$k]['id'])->setDec('stock',1);

                }
                $balance = New Balance();
                $re = $balance->opBalance($player_id,$total,(float)$player_info['total_amount'],-4);
                $battleRule = new BattleRule();
                $battleRule->editAssets($player_id,'',-$total,$total,'');
                if($re){
                    Db::commit();
                    $total_amount['total_amount'] = (float)sprintf("%.2f", ($player_info['total_amount'] - $total));
                    return result(1,$total_amount,'购买成功');
                }else{
                    Db::rollback();
                    return result(0,'','');
                }
            }catch (\Exception $e){
                Db::rollback();
                return result(0,'',$e->getMessage());
            }
        }
    }
}