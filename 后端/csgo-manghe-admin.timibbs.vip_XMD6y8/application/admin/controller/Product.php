<?php


namespace app\admin\controller;

use think\Db;

class Product
{
    protected $requestHost = 'https://app.zbt.com';
    protected $apiKey;
    public function __construct()
    {
        $this->apiKey = Db::table('zbt')->where(['status'=>1,'flag'=>1])->find()['apiKey'];
    }

    //上架商品
    public function shelf()
    {
        $url = '/open/v1/sale/items/';
        $request_url = $this->requestHost . $url;
        $params = [
            'app-key' => $this->apiKey,
            'language' => 'zh_CN'
        ];
        $data = [
            'appId' => 0,
            'outTradeNo' => '',
            'steamId' => 0,
            'itemPriceDataList' => array(
                [
                    'price' => '',
                    'token' => ''
                ]
            )
        ];
        $request_url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($request_url, 'post', json_encode($data));
        dd($re);
    }

    //商品列表
    public function skinList1()
    {
        //查看在售列表
        $url = '/open/v1/sell-list';
        $request_url = $this->requestHost . $url;
        $params = [
            'app-key' => $this->apiKey,
            'language' => 'zh_CN',
            'appId' => '730',            //游戏id
            'delivery' => '',              //发货模式 1人工 2自动
            'limit' => '1',
            'page' => '1'
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'get');
        return $re;
    }


    //市场皮肤列表
    public function skinList()
    {
        //市场商品搜索接口V2
        $url = '/open/product/v2/search';
        $request_url = $this->requestHost . $url;
        $params = [
            'app-key' => $this->apiKey,
            'language' => 'zh_CN',
            'appId' => '730',           //游戏id
            'delivery' => '',              //发货模式 1人工 2自动
            'page' => '1',
            'limit' => '10',
            'keyword' => '',              //查询关键词
//            'category' => '',            //CSGO类别,WearCategory0-1-2-3-4(0崭新-1略有磨损-2久经沙场-3破损不堪-4战痕累累)
            'orderBy' => '',              //排序类型:0 更新时间 1 价格升序 2 价格降序
        ];
        $url = $request_url . '?' . http_build_query($params);
        $re = httpRequest($url, 'get');
        return $re;
    }

    //后台选择皮肤存入箱子，设置概率等信息
    public function putInBox()
    {
        $insert['box_id'] = input('post.box_id', 1);      //
        $insert['price'] = input('post.price', 0.88);       //手动设置的价格
        $insert['probability'] = input('post.probability', 2); //中奖的概率（%）
        $skinInfo = input('post.skinInfo');
        $json = '{"priceInfo":{"price":"0.88","autoDeliverPrice":"0.88","manualDeliverPrice":"0.87"},"appId":"1","itemId":"22256","itemName":"\u5f20\u96ea\u6885","age":"27","imageUrl":"\u8ba1\u7b97\u673a\u79d1\u5b66\u4e0e\u6280\u672f"}';
        $skinInfo = json_decode($json, true);
        if (!$insert['box_id']) {
            return result(0, '', '错误，盒子id不存在');
        }
        if ($insert['price'] <= 0) {
            return result(0, '', '错误，价格不能为0');
        }
        if ($insert['price'] < $skinInfo['priceInfo']['price']) {
            return result(0, '', '价格低于市场价，请重新设置');
        }
        if ($insert['probability'] <= 0) {
            return result(0, '', '中奖概率不能为0');
        }

        $insert['appId'] = $skinInfo['appId'];
        $insert['itemId'] = $skinInfo['itemId'];
        $insert['name'] = $skinInfo['itemName'];
        $insert['img'] = $skinInfo['imageUrl'];
        $insert['priceInfo'] = isset($skinInfo['priceInfo']) ? json_encode($skinInfo['priceInfo']) : '';

        $find = Db::table('box_skins')
            ->where(['box_id' => $insert['box_id'], 'itemId' => $insert['itemId'], 'appId' => $insert['appId'],'flag'=>1])
            ->find();
        //计算总概率
        $probability = Db::table('box_skins')
            ->where(['box_id'=>$insert['box_id'],'flag'=>1])
            ->sum('probability');
        //设置后的总中奖概率
        $totalProbability = $probability + $insert['probability'];
        if($totalProbability > 100){
            return result(0, '', '错误，中奖概率超过100%');
        }
        if ($find) {
            return result(0, '', '已存在相同名称饰品');
        } else {
            Db::table('box_skins')->insertGetId($insert);
            $res = ['totalProbability'=>$totalProbability];
            return result(1, $res, '设置成功，当前盲盒中奖总概率：'.$totalProbability.'%');
        }

    }


}