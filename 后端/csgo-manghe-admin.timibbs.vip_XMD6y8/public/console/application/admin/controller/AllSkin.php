<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;

class AllSkin extends Controller
{
//    //市场皮肤列表
//    public function skinList()
//    {
//        //市场商品搜索接口V2
//        $url = '/open/product/v2/search';
//        $request_url = $this->requestHost . $url;
//
//        $page = input('post.page', 1);
//        $pageSize = input('post.pageSize', 10);
//
//        $params = [
//            'app-key' => $this->apiKey,
//            'language' => 'zh_CN',
//            'appId' => '730',              //游戏id
//            'delivery' => '',              //发货模式 1人工 2自动
//            'page' => $page,
//            'limit' => $pageSize,
//            'keyword' => '',               //查询关键词
////            'category' => '',            //CSGO类别,WearCategory0-1-2-3-4(0崭新-1略有磨损-2久经沙场-3破损不堪-4战痕累累)
//            'orderBy' => '',               //排序类型:0 更新时间 1 价格升序 2 价格降序
//        ];
//        $url = $request_url . '?' . http_build_query($params);
//        $re = httpRequest($url, 'get');
//        return $re;
//    }
//
//    //从市场添加到库
//    public function addSkin()
//    {
//        $skinInfo = input('post.');
////        dd($skinInfo);
//        $skinInfo['price'] = $skinInfo['priceInfo']['price'];
//        $skinInfo['priceInfo'] = serialize($skinInfo['priceInfo']);
//        $skin = Db::table('all_skin')->where(['itemId', $skinInfo['itemId'],'flag'=>1])->find();
//        if ($skin) {
//            return result(0, '', '该饰品已存在');
//        }
//        Db::table('all_skin')->insert($skinInfo);
//        return result(1, '', '');
//    }

    //所有饰品列表
    public function allSkinList(){
        $page = input('post.page', 1);
        $pageSize = input('post.pageSize', 10);
        $searchKey = input('post.searchKey');
        $value = input('post.value');
        $where = $value ? [$value=>1] : '';
        $whereS = trim($searchKey) ? [['itemName','like','%'.trim($searchKey).'%']] : [];
        
        $list = Db::table('all_skin')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->where($where)
            ->where($whereS)
            ->where('flag',1)
            ->order('is_exist','asc')
            ->order('price','asc')
            ->select();

        $total = Db::table('all_skin')
            ->where($where)
            ->where($whereS)
            ->where('flag', 1)
            ->count();
        if ($total>0) {
            $data['total'] = $total;
            $data['list']  = $list;
            return result(1,$data,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //获取某个饰品在幸运皮肤中的属性
    public function getLuckyInfo(){
        $skin_id = input('post.skin_id');
        $info = Db::table('lucky_skin')
            ->where('skin_id',$skin_id)
            ->find();
        if($info){
            return result(1,$info,'');
        }else{
            return result(0,'','');
        }

    }


    //添加饰品
    public function addSkins(){

        $baseInfo = json_decode(input('post.baseInfo'), true);
        $luckyInfo = json_decode(input('post.luckyInfo'), true);
        $stock = input('post.stock');

        $skinInfo = [
            'appId' => 666666,
            'itemId' => 666666,
            'priceInfo' => '',
            'itemName' => $baseInfo['itemName'],
            'marketHashName' => '',
            'shortName' => $baseInfo['itemName'],
            'type' => 'other',
            'typeName' => '其它',
            'quality' => 'normal',
            'qualityName' => '普通',
            'qualityColor' => '#B2B2B2',
            'rarity' => 'rarity_rare_weapon',
            'rarityName' => '保密',
            'rarityColor' => '#d32ce6',
            'exterior' => 'other',
            'exteriorName' => '其它',
            'price' => $baseInfo['price'],
            'market_price' => $baseInfo['price'],
            'lucky' => $baseInfo['lucky'],
            'sale' => $baseInfo['sale'],
            'stock' => $stock
        ];

        $file = $_FILES;
        $url = '';
        if ($file) {
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('skins       ', $file, $key);
        }
        if ($url) {
            $skinInfo['imageUrl'] = $url;
        }

        Db::table('all_skin')->insert($skinInfo);
        return result(1, '', '');
    }

    //删除饰品
    public function delSkin(){
        $id = input('post.id');
//        Db::table('all_skin')
//            ->where('id',$id)
//            ->setField('flag',0);
        return result(1, '', '');
    }

     //修改皮肤信息(修改是否为为幸运皮肤)
    public function edit(){
        $baseInfo = json_decode(input('post.baseInfo'), true);
        $luckyInfo = json_decode(input('post.luckyInfo'), true);
        $stock = input('post.stock');
        //修改价格和是否为幸运饰品
        $update = [
//            'id'=>$baseInfo['skin_id'],
            'price' => $baseInfo['price'],
            'lucky' => $baseInfo['lucky'],
            'sale'  => $baseInfo['sale'],
            'stock' => $stock
        ];
        Db::table('all_skin')->where('id',$baseInfo['skin_id'])->update($update);
        if ($baseInfo['lucky'] > 0){
            $insertTableSkin = [
                'type_id'     => isset($luckyInfo['type_id']) ? $luckyInfo['type_id'] : '',
                'subclass_id' => isset($luckyInfo['subclass_id']) ? $luckyInfo['subclass_id'] : '',
                'recommend'   => isset($luckyInfo['recommend']) ? $luckyInfo['recommend'] : '',
                'new'         => isset($luckyInfo['new']) ? $luckyInfo['new'] : '',
                'skin_id'     => isset($baseInfo['skin_id']) ? $baseInfo['skin_id'] : '',
            ];
            $subclass = Db::table('lucky_skin')
                ->where(['skin_id'=>$baseInfo['skin_id']])
                ->find();
            if($subclass){
                Db::table('lucky_skin')->where('skin_id',$baseInfo['skin_id'])->update($insertTableSkin);
            }else{
                Db::table('lucky_skin')->insert($insertTableSkin);
            }
        }else{
            Db::table('all_skin')->where('id',$baseInfo['skin_id'])->setField('lucky',0);
            $lucky = Db::table('lucky_skin')->where('skin_id',$baseInfo['skin_id'])->find();
            if($lucky){
                Db::table('lucky_skin')->where('skin_id',$baseInfo['skin_id'])->delete();
            }
        }
        return result(1, '', '');
    }


    //饰品分类列表
    public function skinTypeList(){
        $list = Db::table('box_skins_type')
            ->order('order','asc')
            ->select();
        $total = Db::table('box_skins_type')
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1, $res, '');
        }else{
            return result(0, '$res', '无数据');
        }
    }

    //添加盲饰品分类
    public function addSkinType(){
        $typeInfo = input('post.data');
        $typeInfo = json_decode($typeInfo,true);
        if(!trim($typeInfo['name'])){
            return result(0, '', '请输入分类名称');
        }
        $type = Db::table('box_skins_type')->where('name',$typeInfo['name'])->find();
        if($type){
            return result(0, '', '已存在相同分类名称');
        }
        $typeInfo['create_time'] = currentTime();

        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('skinsBackground',$file,$key);
        }
        if($url){
            $typeInfo['img'] = $url;
        }
        Db::table('box_skins_type')->insert($typeInfo);
        return result(1,'','');
    }

    //编辑饰品分类
    public function editSkinType(){
        $typeInfo = json_decode(input('post.data'),true);
        $id = $typeInfo['id'];
        if(!$id){
            return result(0, '', '分类信息错误');
        }
        if(!trim($typeInfo['name'])){
            return result(0, '', '请输入分类名称');
        }
        $type = Db::table('box_skins_type')->where('name',$typeInfo['name'])->select();
        if(count($type)>1){
            return result(0, '', '已存在相同分类名称');
        }
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('skinsBackground',$file,$key);
        }
        if($url){
            $typeInfo['img'] = $url;
        }
        Db::table('box_skins_type')->where('id',$id)->update($typeInfo);
        return result(1,'','');
    }


    //删除盲饰品分类
    public function delSkinType(){
        $id = input('post.id');
        if(!$id){
            return result(0, '$res', '分类信息错误');
        }
        Db::table('box_skins_type')->where('id',$id)->delete();
        return result(1,'','');
    }

}