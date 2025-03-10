<?php


namespace app\index\controller;


use think\Db;

class Lucky
{
    //幸运饰品列表
    public function skinList()
    {
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $recommend = input('post.recommend');             //是否为推荐1：是，2：否
        $new = input('post.new');                         //是否为新品1：是，2：否
        $type_id = input('post.type_id');
        $subclass_id = input('post.subclass_id');

        $newW = $new ? ['s.new' => $new] : '';
        $recommendW = $recommend ? ['s.recommend' => $recommend] : '';
//        $type_idW = ($type_id > 2) ? ['s.type_id' => $type_id] : '';
        $type_idW = $type_id ? ['s.type_id' => $type_id] : '';
        $subclass_idW = $subclass_id ? ['s.subclass_id' => $subclass_id] : '';

        $list = Db::table('all_skin')
            ->alias('as')
            ->join('lucky_skin s', 's.skin_id = as.id', 'left')
            ->join('lucky_skin_type st', 'st.id = s.type_id', 'left')
            ->join('lucky_skin_subclass sts', 'sts.id = s.subclass_id', 'left')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->field('as.*,s.type_id,st.name as type_name,s.subclass_id,sts.name as subclass_name')
            ->where($newW)
            ->where($recommendW)
            ->where($type_idW)
            ->where($subclass_idW)
            ->where(['s.flag' => 1,'as.flag'=>1])
            ->order('as.price','desc')
            ->select();
        $total = Db::table('all_skin')
            ->alias('as')
            ->join('lucky_skin s', 's.skin_id = as.id', 'left')
            ->where(['s.flag' => 1,'as.flag'=>1])
            ->where($newW)
            ->where($recommendW)
            ->where($type_idW)
            ->where($subclass_idW)
            ->count();
        if ($total > 0) {
            $res = ['total' => $total, 'list' => $list];
            return result(1, $res, '');
        } else {
            $res = ['total' => 0, 'list' => []];
            return result(0, $res, '无数据');
        }
    }

    //皮肤（type）类型列表，用于幸运皮肤筛选
    public function luckyTypeList(){
        $list = Db::table('lucky_skin_type')
            ->where(['flag'=>1])
            ->order('order','asc')
            ->select();
        return result(1, $list, '');
    }

    //皮肤（subclass）小分类列表，用于幸运皮肤筛选
    public function subclassList(){
        $type_id = input('post.type_id');
        $where = $type_id ? ['skin_type_id'=>$type_id] : '';
        $list = Db::table('lucky_skin_subclass')
            ->where($where)
            ->select();
        if(count($list)>0){
            $res  = ['total'=>count($list),'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '', '无数据');
        }
    }


    //幸运饰品开奖
    public function getSkin_1()
    {
        $skin_id = input('post.skin_id');
        $player_id = input('post.player_id');
        $probability = input('post.probability');
        if (empty(trim($skin_id))) {
            return result(0, '', '缺少饰品信息');
        }
        if (empty(trim($player_id))) {
            return result(0, '', '缺少玩家信息');
        }
        if (empty(trim($probability))) {
            return result(0, '', '开奖信息有误');
        }

        $skinInfo = Db::table('all_skin')
            ->where(['id' => $skin_id, 'flag' => 1])
            ->find();
        if (!$skinInfo) {
            return result(0, '', '饰品信息不存在');
        }
        $playerTotalAmount = Db::table('player')
            ->where('id', $player_id)
            ->value('total_amount');
        $price = $skinInfo['price'] * ($probability / 100);
        if($price < 0.01){
            $price = 0.01;
        }else{
            $price = sprintf("%.2f", $price);
        }
        if ($playerTotalAmount < $price) {
            return result(0, '', '账户余额不足');
        }

        Db::startTrans();
        try {
            $balance = new Balance();
            $re = $balance->opBalance($player_id, $price, $playerTotalAmount, -3);
            if (!$re) {
                return result(0, '', '操作失败');
            }
            //生成随机范围
            $rand = rand(1, 100 * 100);
            if ($rand <= ($probability * 100)) {
                $player_skin = [
                    'player_id' => $player_id,
                    'appId' => $skinInfo['appId'],
                    'itemId' => $skinInfo['itemId'],
                    'order_num' => makeOder('L'),
                    'name' => $skinInfo['itemName'],
                    'img' => $skinInfo['imageUrl'],
                    'price' => $skinInfo['price'],
                    'priceInfo' => $skinInfo['priceInfo'],
                    'probability' => $probability,
                    'open_number' => $rand,
                    'open_number_md' => md5($rand),
                    'type_id' => '',
//                'delivery'    => $skinInfo['delivery'],
//                'buyPrice'    => $skinInfo['buyPrice'],
//                'maxPrice'    => $skinInfo['maxPrice'],
                    'create_time' => currentTime(),
                    'way' => 3,
                ];
                $player_skin['player_skin_id'] = Db::table('player_skins')->insertGetId($player_skin);
                Db::commit();
                $player_skin['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price));
                return result(1, $player_skin, '恭喜中奖');
            } else {
                Db::commit();
                $res['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price));
                return result(1, $res, '未中奖');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }




    //
    public function getSkin(){
        $skin_id = input('post.skin_id');
        $player_id = input('post.player_id');
        $probability = input('post.probability');
        if (empty(trim($skin_id))) {
            return result(0, '', '缺少饰品信息');
        }
        if (empty(trim($player_id))) {
            return result(0, '', '缺少玩家信息');
        }
        if (empty(trim($probability))) {
            return result(0, '', '开奖信息有误');
        }
        if (!empty(session('get_skin')) && time() - session('get_skin') < 3) {
            return result(0, '', '操作频繁,请稍后再试!');
        }

        $battleRule = new BattleRule();
        $battleRule->query($player_id);

//        $skinInfo = Db::table('all_skin')
//            ->where(['id' => $skin_id, 'flag' => 1])
//            ->find();
        $skinInfo = Db::table('all_skin')
            ->alias('as')
            ->field('as.*')
            ->join('lucky_skin ls','ls.skin_id = as.id')
            ->where(['as.id' => $skin_id, 'as.flag' => 1,'ls.flag' => 1])
            ->find();

        if (!$skinInfo) {
            session('get_skin',time());
            return result(0, '', '饰品信息不存在');
        }
//        $playerTotalAmount = Db::table('player')
//            ->where('id', $player_id)
//            ->value('total_amount');
        $player = Db::table('player')
            ->where(['id'=>$player_id,'flag'=>1])
            ->find();

        $group = ($player['group']>0) ? $player['group'] : '';//是否主播
        $playerTotalAmount = (float)$player['total_amount'];
        $price = $skinInfo['price'] * ($probability / 100);
        if($price == 0){
            session('get_skin',time());
            return result(0, '', '失败');
        }
        if($price < 0.01){
            $price = 0.01;
        }else{
            $price = sprintf("%.2f", $price);
        }
        if ($playerTotalAmount < $price) {
            session('get_skin',time());
            return result(0, '', '账户余额不足');
        }
        Db::startTrans();
        try {
            $balance = new Balance();
            $re = $balance->opBalance($player_id, $price, $playerTotalAmount, -3);
            if (!$re) {
                session('get_skin',time());
                return result(0, '', '操作失败');
            }
            if($group>0){
                $pool = Db::table('pool_group')->where('skin_id',$skin_id)->find();
            }else{
                $pool = Db::table('pool')->where('skin_id',$skin_id)->find();
            }

            if(empty($pool['id'])){
                $insert['pool']  = 0;
                $insert['sales'] = 0;
                $insert['skin_id'] = $skin_id;
                if($group>0){
                    $pool['id'] = Db::table('pool_group')->insertGetId($insert);
                }else{
                    $pool['id'] = Db::table('pool')->insertGetId($insert);
                }
                $pool['pool']  = '0.00';
                $pool['sales'] = '0.00';
                $pool['skin_id'] = $skin_id;
            }

            if($pool['sales']<=0){
                if($group>0){
                    Db::table('pool_group')->where('id',$pool['id'])->setInc('sales',$price);
                }else{
                    Db::table('pool')->where('id',$pool['id'])->setInc('sales',$price);
                }
                $balance->opBalance($player_id, 0.01, number_format(($playerTotalAmount - $price),2,'.',''), 7);

                $battleRule->editAssets($player_id,'',-$price+0.01,'',1);
                Db::commit();
                $res['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price + 0.01));
                session('get_skin',time());
                return result(1, $res, '未中奖');
            }
            //累计出售金额+本次开奖销售收入金额
            $sale_value = (float)$pool['sales'] + (float)$price;
            //累计已中饰品价值+本次饰品价值
            $pool_price = (float)$pool['pool'] + (float)$skinInfo['price'];

            if($pool_price<=0){
                if($group>0){
                    Db::table('pool_group')->where('id',$pool['id'])->setInc('sales',$price);
                }else{
                    Db::table('pool')->where('id',$pool['id'])->setInc('sales',$price);
                }
                $battleRule->editAssets($player_id,'',-$price+0.01,'',1);
                Db::commit();
                $res['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price));
                session('get_skin',time());
                return result(1, $res, '未中奖');
            }

            if(($sale_value/$pool_price) > (1+0.3)) {
                //当前状态判断正常几率
                $rand_normal = rand(1, 10000);
                $range = $probability * 100;
                //中奖
                if($rand_normal <= $range){
                    //生成随机范围
                    $rand = rand(1, $probability * 100);
                    $player_skin = [
                        'player_id' => $player_id,
                        'appId' => $skinInfo['appId'],
                        'itemId' => $skinInfo['itemId'],
                        'order_num' => makeOder('L'),
                        'name' => $skinInfo['itemName'],
                        'img' => $skinInfo['imageUrl'],
                        'price' => $skinInfo['price'],
                        'priceInfo' => $skinInfo['priceInfo'],
                        'probability' => $probability,
                        'open_number' => $rand,
                        'open_number_md' => md5($rand),
                        'type_id' => '',
                        'create_time' => currentTime(),
                        'way' => 3,
                    ];
                    $player_skin['player_skin_id'] = Db::table('player_skins')->insertGetId($player_skin);
                    $update = [
                        'pool' =>  number_format(($pool['pool'] + $skinInfo['price']),2,'.',''),
                        'sales' =>  number_format(($pool['sales'] + $price),2,'.','')
                    ];
                    if($group>0){
                        Db::table('pool_group')->where('id',$pool['id'])->update($update);
                    }else{
                        Db::table('pool')->where('id',$pool['id'])->update($update);
                    }
                    $player_skin['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price));
                    $history['skin_id'] = $skin_id;
                    $history['player_id'] = $player_id;
                    $history['type'] = 3;
                    $history['num'] = 1;
                    Db::table('battle_player')->insert($history);
                    $battleRule->editAssets($player_id,'',-$price,$skinInfo['price'],1);
                    Db::commit();
                    session('get_skin',time());
                    return result(1, $player_skin, '恭喜中奖');
                }
                $balance->opBalance($player_id, 0.01, number_format(($playerTotalAmount - $price),2,'.',''), 7);
                Db::table('pool')->where('id',$pool['id'])->setInc('sales',$price);

                $history['skin_id'] = $skin_id;
                $history['player_id'] = $player_id;
                $history['type'] = 3;
                $history['num'] = 1;
                Db::table('battle_player')->insert($history);
                $battleRule->editAssets($player_id,'',-$price+0.01,'',1);

                Db::commit();
                $res['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price + 0.01));
                session('get_skin',time());

                return result(1, $res, '未中奖');

            } else {
                if($group>0){
                    Db::table('pool_group')->where('id',$pool['id'])->setInc('sales',$price);
                }else{
                    Db::table('pool')->where('id',$pool['id'])->setInc('sales',$price);
                }
                $balance->opBalance($player_id, 0.01, number_format(($playerTotalAmount - $price),2,'.',''), 7);

                $history['skin_id'] = $skin_id;
                $history['player_id'] = $player_id;
                $history['type'] = 3;
                $history['num'] = 1;
                Db::table('battle_player')->insert($history);
                $battleRule->editAssets($player_id,'',-$price+0.01,'',1);

                Db::commit();
                $res['total_amount'] = (float)sprintf("%.2f", ($playerTotalAmount - $price + 0.01));

                session('get_skin',time());
                return result(1, $res, '未中奖');
            }
        } catch (\Exception $e) {

            session('get_skin',time());

            Db::rollback();
            return result(0, '', $e->getMessage());
        }
    }
}