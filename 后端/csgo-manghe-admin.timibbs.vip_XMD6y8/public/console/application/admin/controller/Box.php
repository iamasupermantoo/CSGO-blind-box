<?php


namespace app\admin\controller;


use app\index\controller\Opp;
use phpDocumentor\Reflection\Types\Null_;
use think\Db;
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
class Box
{
    protected $requestHost = 'https://app.zbt.com';

    //市场皮肤列表
    public function skinList($keyword=null)
    {
        //市场商品搜索接口V2
        $url = '/open/product/v2/search';
        $request_url = $this->requestHost . $url;

        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $keyword = trim(input('post.keyword')) ? trim(input('post.keyword')) : $keyword;

        $params = [
            'app-key' => devInfo()['apiKey'],
            'language' => 'zh_CN',
            'appId' => '730',              //游戏id
            'delivery' => '',              //发货模式 1人工 2自动
            'page' => $page,
            'limit' => $pageSize,
            'keyword' => $keyword,               //查询关键词
//            'category' => '',            //CSGO类别,WearCategory0-1-2-3-4(0崭新-1略有磨损-2久经沙场-3破损不堪-4战痕累累)
            'orderBy' => '',               //排序类型:0 更新时间 1 价格升序 2 价格降序
            'minPrice' => '0.01'           //价格区间最小值,单位（美元）
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'get');
        return $re;
    }

    //从市场添加到库
    public function addSkin()
    {
        $skinInfo = input('post.');
        $skinInfo['price']        = $skinInfo['priceInfo']['price'];
        $skinInfo['market_price'] = $skinInfo['price'];
        $skinInfo['is_exist']     = 1;
        $skinInfo['priceInfo']    = serialize($skinInfo['priceInfo']);

        $skin = Db::table('all_skin')->where(['itemId'=>$skinInfo['itemId'],'flag'=>1])->find();
        //查询改价比例
        $exchange = Db::table('set_exchange_rate')->find();
        if(trim($exchange['bili'])<=0){
            $exchange['bili'] = 1;
        }
        //将平台的饰品价格*改价比例
        $skinInfo['price'] = keep_decimal($skinInfo['price'] * $exchange['bili']);
        if ($skin) {
            //已经存在了的修改以下信息，
            Db::table('all_skin')->where(['itemId'=>$skinInfo['itemId'],'flag'=>1])->update($skinInfo);
            return result(1, '', '该饰品已存在,平台价格已同步更新');
        }else{
            Db::table('all_skin')->insert($skinInfo);
            return result(1, '', '添加成功');
        }

    }

    //后台同步扎比特饰品价格
    public function sync(){
        $all_skins = Db::table('all_skin')
            ->field('id,itemId,itemName,market_price,marketHashName')
            ->where('flag',1)
            ->order('market_price')
            ->select();
        //查询改价比例
        $exchange = Db::table('set_exchange_rate')->find();
        if(trim($exchange['bili'])<=0){
            $exchange['bili'] = 1;
        }
        $marketHashNameList = [];
        if($all_skins){
            foreach ($all_skins as $k=>$v){
                $marketHashNameList[] = $all_skins[$k]['marketHashName'];
            }
        }
        $url = '/open/product/price/info';
        $request_url = $this->requestHost . $url;
        $params = [
            'app-key'  => devInfo()['apiKey'],
            'language' => 'zh_CN'

        ];
        $data = [
            "appId"    => 730,
            "marketHashNameList" => $marketHashNameList
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'post',json_encode($data));
        $re = json_decode($re,true);
        if($re['success']){
            $data = $re['data'];
            foreach ($data as $k=>$v){
                $itemId       = $data[$k]['itemId'];
                $market_price = $data[$k]['price'];
                $update['price'] = keep_decimal($market_price * $exchange['bili']);
                $update['market_price'] = $market_price;
                $update['is_exist']     = 1;

                if($market_price == null){
                    $update['market_price'] = 0;
                    $update['is_exist']     = 0;
                    unset($update['price']);
                }
                Db::table('all_skin')->where(['itemId'=>$itemId,'flag'=>1])->update($update);
                unset($data[$k]);
            }

            return result(1, '', '同步更新成功');
        }else{
            return result(0, '', $re['errorMsg']);
        }



    }

    //盲盒列表admin
    public function boxList()
    {
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $box_rarity_id = input('post.box_rarity_id');

        $where = $box_rarity_id ? ['box.rarity' => $box_rarity_id] : '';

        $data = [];
        $list = Db::table('box')
            ->field('box.*,br.rarity_name')
            ->join('box_rarity br', 'br.id = box.rarity', 'LEFT')
            ->where($where)
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('box')
            ->field('box.*,br.rarity_name')
            ->join('box_rarity br', 'br.id = box.rarity', 'LEFT')
            ->where($where)
            ->count();
        if ($list) {
//            $list['total'] = $total;
            foreach ($list as $k => $v) {
//                $totalProbability = Db::table('box_skins')
//                    ->where(['flag' => 1, 'box_id' => $list[$k]['id']])
//                    ->sum('probability');
                $sumInfo = Db::table('box_skins')
                    ->field(['sum(probability) as totalProbability,sum(stock) as total_stock,sum(stock_group) as total_stock_group,sum(stock_vip) as total_stock_vip'])
                    ->where(['flag' => 1, 'box_id' => $list[$k]['id']])
                    ->select();
                $list[$k]['totalProbability'] = $sumInfo[0]['totalProbability'];
                $list[$k]['total_stock'] = $sumInfo[0]['total_stock'];
                $list[$k]['total_stock_group'] = $sumInfo[0]['total_stock_group'];
                $list[$k]['total_stock_vip'] = $sumInfo[0]['total_stock_vip'];
            }
            $data['total'] = $total;
            $data['list'] = $list;
            return result(1, $data, '');
        } else {
            return result(0, '', '无数据');
        }
    }

    //盲盒详情
    public function boxInfo()
    {
        $box_id = input('post.box_id');
        $info = Db::table('box')
            ->field('box.*,br.rarity_name')
            ->join('box_rarity br', 'br.id=box.rarity', 'left')
            ->where('box.id', $box_id)
            ->find();

        //盲盒皮肤总概率
//        $totalProbability = Db::table('all_skin')
//            ->alias('as')
//            ->join('box_skins bs','bs.skin_id = as.id')
//            ->where(['bs.box_id' => $box_id, 'bs.flag' => 1])
//            ->sum('probability');

        //查询盲盒的饰品有多少个颜色分类
        $skins_type = Db::table('box_skins_type')
            ->alias('bst')
            ->field('bst.id as box_skins_type_id,bst.name')
            ->join('box_skins bs','bs.type_id=bst.id')
            ->where(['bs.box_id'=>$box_id])
            ->group('bst.id')
            ->order('bst.order','asc')
            ->select();
        //查询盲盒的饰品颜色分类概率
        if($skins_type){
            foreach ($skins_type as $k=>$v){
                $probability = Db::table('box_skins_probability')
                    ->where(['box_id'=>$box_id,'box_skins_type_id'=>$skins_type[$k]['box_skins_type_id']])
                    ->value('probability');
                $skins_type[$k]['probability'] = $probability>0 ? $probability : '';
            }
        }

        $mend = Db::table('mend')
            ->where('box_id',$box_id)
            ->find();

        $info['img_main'] = mainName(). $info['img_main'];
        $info['img_active'] = mainName(). $info['img_active'];
        $info['skins_type'] = $skins_type;
//        $info['totalProbability'] = $totalProbability;
        $info['mend'] = $mend;
        if ($info) {
            return result(1, $info, '');
        } else {
            return result(0, '', '');
        }
    }

    //盲盒类型
    public function getBoxType()
    {
        $rarity = Db::table('box_type')
            ->select();
        return result(1, $rarity, '');
    }


    //盒子类型列表
    public function boxType()
    {
        $list = Db::table('box_type')
            ->order('order','asc')
            ->select();
        $total = Db::table('box_type')
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '$res', '无数据');
        }
    }

    //添加盲盒分类
    public function addBoxType(){
        $typeInfo = input('post.typeInfo');
        if(!trim($typeInfo['type_name'])){
            return result(0, '$res', '请输入分类名称');
        }
        $type = Db::table('box_type')->where('type_name',$typeInfo['type_name'])->find();
        if($type){
            return result(0, '$res', '已存在相同分类名称');
        }
        $typeInfo['create_time'] = currentTime();
        Db::table('box_type')->insert($typeInfo);
        return result(1,'','');
    }

    //编辑盲盒分类
    public function editBoxType(){
        $id = input('post.id');
        if(!$id){
            return result(0, '$res', '分类信息错误');
        }
        $typeInfo = input('post.typeInfo');
        if(!trim($typeInfo['type_name'])){
            return result(0, '$res', '请输入分类名称');
        }
        $type = Db::table('box_type')->where('type_name',$typeInfo['type_name'])->select();
        if(count($type)>1){
            return result(0, '$res', '已存在相同分类名称');
        }
        Db::table('box_type')->where('id',$id)->update($typeInfo);
        return result(1,'','');
    }

    //删除盲盒分类
    public function delBoxType(){
        $id = input('post.id');
        if(!$id){
            return result(0, '$res', '分类信息错误');
        }
        Db::table('box_type')->where('id',$id)->delete();
        return result(1,'','');
    }





    //盒子稀有度类型
    public function boxRarity()
    {
        $rarity = Db::table('box_rarity')
            ->select();
        return result(1, $rarity, '');
    }


    //添加盲盒
    public function addBox()
    {
        $post = input('post.');
        if(!trim($post['name'])){
            return result(0, '', '请输入盲盒名称');
        }
        $boxInfo = [
            'name' => trim($post['name']),
            'create_time' => currentTime()
//            'price' => $post['price'],
//            'img_main' => $post['img_main'],
//            'img_active' => $post['img_active'],
//            'skin_ids' => $post['skin_ids'],
//            'type' => $post['type'],
//            'sold' => $post['sold'],
//            'battle' => $post['battle']
        ];
        $box = Db::table('box')->where('name',$boxInfo['name'])->find();
        if($box){
            return result(0, '', '已存在相同名称盲盒');
        }
        Db::table('box')->insert($boxInfo);
        return result(1, '', '');
    }

    //修改盒子信息
    public function editBox()
    {
        $fileUrl = [];
        if ($_FILES) {
            $upload = new Upload();
            $keys = array_keys($_FILES);
            foreach ($keys as $k => $v) {
                $file[$keys[$k]] = $_FILES[$keys[$k]];
                $fileUrl[$keys[$k]] = $upload->upload('box', $file,$keys[$k]);
            }
        }
        $post = json_decode(input('post.')['data'], true);

        $boxInfo = [
            'id' => isset($post['box_id']) ? $post['box_id'] : '',
            'name' => isset($post['name']) ? $post['name'] : '',
            'price' => isset($post['price']) ? $post['price'] : '',
            'img_main' => isset($fileUrl['img_main']) ? $fileUrl['img_main'] : '',
            'img_active' => isset($fileUrl['img_active']) ? $fileUrl['img_active'] : '',
            'flag' => isset($post['flag']) ? $post['flag'] : '',
            'skin_ids' => isset($post['skin_ids']) ? $post['skin_ids'] : '',
            'type' => isset($post['type']) ? $post['type'] : '',
//            'sold'       => isset($post['sold']) ? $post['sold'] : '',
            'battle' => isset($post['battle']) ? $post['battle'] : '',
            'rarity' => isset($post['rarity']) ? $post['rarity'] : '',
        ];

        //补充库存设置
        $mend = [
            'billie'      => isset($post['billie']) ? $post['billie'] : '',
            'vip_billie'  => isset($post['vip_billie']) ? $post['vip_billie'] : '',
            'box_id'      => isset($post['box_id']) ? $post['box_id'] : '',
            'create_time' => currentTime()
        ];

        Db::startTrans();
        try {
            //设置颜色分类概率
            $is_insert = isset($post['skins_type']) ? $post['skins_type'] : [];

            if($is_insert){
                $is_delete = Db::table('box_skins_probability')->where('box_id',$post['box_id'])->select();

                //循环中已操作更新，只记录下需要删除和新增的数据即可
                foreach ($is_insert as $k=>$v){

                    foreach ($is_delete as $key=>$val){
                        if(($is_insert[$k]['box_skins_type_id'] == $is_delete[$key]['box_skins_type_id'])){
                            //更新,移除被更新数据，剩余的插入数据库
                            if($is_insert[$k]['probability'] !=  $is_delete[$key]['probability']){
                                $update['probability'] = $is_insert[$k]['probability'];
                                Db::table('box_skins_probability')->where('id', $is_delete[$key]['id'])->update($update);
                                $is_insert[$k]['state'] = 'update';//标记更新状态
                            }else{
                                $is_insert[$k]['state'] = 'ignore';//标记忽略状态
                            }
                            unset($is_delete[$key]);
                        }
                    }
                }
                $is_delete = array_values($is_delete);
                //删除
                if($is_delete){
                    foreach ($is_delete as $k=>$v){
                        $delete_id = $is_delete[$k]['id'];
                        Db::table('box_skins_probability')->where('id', $delete_id)->delete();
                    }
                }
                //插入
                if($is_insert){
                    foreach ($is_insert as $k=>$v){
                        if((!isset($is_insert[$k]['state'])) && ($is_insert[$k]['probability']>0)){
                            $insert['probability'] = $is_insert[$k]['probability'];
                            $insert['box_skins_type_id'] = $is_insert[$k]['box_skins_type_id'];
                            $insert['box_id'] = $post['box_id'];
                            Db::table('box_skins_probability')->insert($insert);
                        }
                    }
                }
            }

            if (!$boxInfo['img_main']) {
                unset($boxInfo['img_main']);
            }
            if (!$boxInfo['img_active']) {
                unset($boxInfo['img_active']);
            }
            Db::table('box')->update($boxInfo);
            $mendInfo = Db::table('mend')->where('box_id',$post['box_id'])->find();
            if(!$mendInfo){
                Db::table('mend')->insert($mend);
            }else{
                Db::table('mend')->where('box_id',$mend['box_id'])->update($mend);
            }
            Db::commit();
            return result(1, '', '');
        }catch (\Exception $e){
            Db::rollback();
            return result(0, '', $e->getMessage());
        }


    }

    //删除盒子
    public function del()
    {
        $id = input('post.id ');
        Db::table('box')->where('id', $id)->delete();
        return result(1, '', '');
    }

    //盲盒皮肤列表
    public function boxSkinList()
    {
        $box_id = input('post.box_id');
        $page   = input('post.page', 1);
        $pageSize  = input('post.pageSize', 10);
        $searchKey = input('post.searchKey');
        $where = [];
        trim($searchKey) ? $where[] = ['as.itemName','like','%'.$searchKey.'%'] : '';

        $list = Db::table('all_skin')
            ->alias('as')
            ->join('box_skins bs', 'bs.skin_id=as.id', 'left')
            ->join('box_skins_type bst', 'bst.id = bs.type_id', 'left')
            ->field('as.*,bs.stock,bs.probability,bs.delivery,bs.box_id,bs.create_time,bs.stock,bs.set_stock,bs.stock_group,bs.stock_vip,bs.set_stock_vip,bs.type_id as plat_type_id ,bst.name as plat_type_name,bs.id as box_skins_id')
            ->where(['bs.box_id' => $box_id, 'bs.flag' => 1])
            ->where($where)
            ->order('as.price','desc')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->select();
        $total = Db::table('all_skin')
            ->alias('as')
            ->join('box_skins bs', 'bs.skin_id=as.id', 'left')
            ->join('box_skins_type bst', 'bst.id = bs.type_id', 'left')
            ->where(['bs.box_id' => $box_id, 'bs.flag' => 1])
            ->where($where)
            ->count();
        if ($total > 0) {
//            foreach ($list as $k=>$v){
//                $list[$k]['priceInfo'] = unserialize($list[$k]['priceInfo']);
//            }
            $res = ['total' => $total, 'list' => $list];
            return result(1, $res, '');
        } else {
            return result(0, '', '无数据');
        }
    }

    //把一个皮肤添加到某个盲盒下面
    public function putSkinToBox()
    {
        $skin_info = input('post.skin_info');
        $box_skin = [
            'box_id' => $skin_info['box_id'],
            'skin_id' => $skin_info['skin_id'],
        ];
        $skin = Db::table('box_skins')
            ->where($box_skin)
            ->where('flag',1)
            ->find();
        if($skin){
            return result(0, '', '饰品已存在');
        }
        $box_skin['create_time'] = currentTime();
        Db::table('box')->where('id',$box_skin['box_id'])->setField('skin_exist',1);
        Db::table('box_skins')->insert($box_skin);
        return result(1, '', '');
    }

    //批量添加到盲盒
    public function addToBoxByBatch(){
        $box_id    = input('post.box_id');
        $skin_info = input('post.skin_info');
        if(count($skin_info)>0){
            foreach ($skin_info as $k=>$v){
                $box_skin['box_id'] = $box_id;
                $box_skin['skin_id'] = $skin_info[$k]['id'];
                $skin = Db::table('box_skins')
                    ->where($box_skin)
                    ->where('flag',1)
                    ->find();
                if(!$skin){
                    $box_skin['create_time'] = currentTime();
                    Db::table('box')->where('id',$box_skin['box_id'])->setField('skin_exist',1);
                    Db::table('box_skins')->insert($box_skin);
                }
            }
            return result(1, '', '批量添加成功');
        }
    }

    //修改皮肤信息
    public function editSkin()
    {
        $skin_info = input('post.skin_info');
        $box_skin = [
            //手动设置信息
            'id' => $skin_info['box_skins_id'],
            'stock' => $skin_info['stock'],//库存
            'stock_group' => $skin_info['stock_group'],//概率组库存
            'stock_vip' => $skin_info['stock_vip'],//vip库存
            'set_stock' => $skin_info['set_stock'],
            'set_stock_vip' => $skin_info['set_stock_vip'],
//            'buyPrice' => $skin_info['buyPrice'],//购买价格
//            'maxPrice' => $skin_info['appId'],//最大购买价格，可选，会导致buyPrice失效，商品价格<=最大购买价格，可以购买成功
//            'delivery' => $skin_info['delivery'],//发货：1人工 2自动
            'probability' => $skin_info['probability'],//概率（虚标）
            'type_id' => $skin_info['type_id']
        ];
        Db::table('box_skins')
            ->where('id', $skin_info['box_skins_id'])
            ->update($box_skin);
        Db::table('all_skin')
            ->where('id', $skin_info['skin_id'])
            ->setField('price', $skin_info['price']);
        return result(1, '', '');
    }

    //删除皮肤
    public function delSkin()
    {
        $id = input('post.id');
        $box_id = input('post.box_id');
        Db::table('box_skins')->where(['id'=>$id,'box_id'=>$box_id])->delete();
        $skin = Db::table('box_skins')->where(['box_id'=>$box_id])->find();
        if(!$skin){
            Db::table('box')->where('id',$box_id)->setField('skin_exist',0);
        }
        return result(1, '', '');
    }

    //皮肤类型
    public function skinType()
    {
        $list = Db::table('box_skins_type')
            ->order('order','asc')
            ->select();
        return result(1, $list, '');
    }


    public function test()
    {
        dd(1111);
    }

    public function mendStock(){
        $box_id = input('post.box_id');
        $mend = new Opp();
        $mend->mend($box_id,'stock');
        $mend->mend($box_id,'stock_vip');
        return result(1, '', '');
    }


}