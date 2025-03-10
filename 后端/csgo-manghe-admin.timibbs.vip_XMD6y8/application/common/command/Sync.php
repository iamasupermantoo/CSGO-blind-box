<?php
namespace app\common\command;
use app\common\controller\Recommend;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use app\admin\controller\Box;
use think\Db;
use think\Exception;

/**
 * Class Sync
 *
 * @package app\common\command
 */
class Sync extends Command
{
    protected $requestHost = 'https://app.zbt.com';

    protected function configure()
    {
        $this->setName('sync')
            // 配置一个参数
            ->addArgument('option', Argument::OPTIONAL, "cron job options")
            ->setDescription('同步数据');
    }

    ## sudo -Hu www php /www/wwwroot/xxx/think sync sync_box_price
    ## /www/wwwroot/xxx 为网站路径
    ## sync_box_price 为执行方法
    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('option'));
        switch ($name) {
            case 'sync_box_price': // 自动匹配
                $output->writeln('同步箱子价格');
                $startTime = microtime(true);

                $all_skins = Db::table('all_skin')
                    ->field('id,itemId,itemName,market_price,marketHashName')
                    ->where('flag',1)
                    ->order('market_price')
//                    ->limit(10)
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

                $marketHashNameList = array_chunk($marketHashNameList, 200);

                $url = '/open/product/price/info';
                $request_url = $this->requestHost . $url;
                $params = [
                    'app-key'  => devInfo()['apiKey'],
                    'language' => 'zh_CN'
                ];

                try {

                    $datas = [];

                    foreach ($marketHashNameList as $marketHashNameAry) {
                        $data = [
                            "appId" => 730,
                            "marketHashNameList" => $marketHashNameAry
                        ];
                        $url = $request_url . '?' . http_build_query($params);
                        $re = httpRequest($url, 'post', json_encode($data));

                        $re = json_decode($re, true);

                        if ($re['success']) {
                            $datas = array_merge($re['data'], $datas);
                        } else {
                            throw new Exception($re['errorMsg']);
                        }
                    }

                    foreach ($datas as $k => $v) {
                        $itemId = $datas[$k]['itemId'];
                        $market_price = $datas[$k]['price'];
                        $update['price'] = keep_decimal($market_price * $exchange['bili']);
                        $update['market_price'] = $market_price;
                        $update['is_exist'] = 1;

                        if ($market_price == null) {
                            $update['market_price'] = 0;
                            $update['is_exist'] = 0;
                            unset($update['price']);
                        }
                        Db::table('all_skin')->where(['itemId' => $itemId, 'flag' => 1])->update($update);
                        unset($datas[$k]);
                    }

                    $output->writeln('----It takes ' . round(microtime(true) - $startTime, 3) . ' seconds');

                } catch (\Exception $ex) {
                    $output->writeln('----It Error: ' . $ex->getMessage());
                }
                break;

            default:
                $output->writeln('This operation is not supported');
                break;
        }
    }
}