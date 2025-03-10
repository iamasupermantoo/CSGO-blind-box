<?php
// 应用公共文件
function dd($data)
{
    dump($data);
    die();
}

function result($status = null, $data = null, $msgStr = null)
{
    if ($status == 1) {
        $msg = $msgStr ? $msgStr : '操作成功';
        $res = ['status' => $status, 'msg' => $msg];
        if ($data) {
            $res['data'] = $data;
        }
    } else {
        $msg = $msgStr ? $msgStr : '数据不存在或操作错误';
        $res = ['status' => $status, 'msg' => $msg];
        if ($data) {
            $res['data'] = $data;
        }
    }
    return json($res);
}

//http请求
function httpRequest($url = null, $method = null, $data = null)
{
    // php curl 发起get或者post请求
    // curl 初始化
    $curl = curl_init();    // curl 设置
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  // 校验证书节点
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);// 校验证书主机
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)
        )
    );

// 判断 $data get  or post
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  // 以文件流的形式 把参数返回进来
// 如果这一行 不写你就收不到 返回值

// 执行
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function curl_post_https($url, $data, $header)
{ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    if ($header) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($curl, CURLINFO_HEADER_OUT, true); /* 请求头 */
    curl_setopt($curl, CURLOPT_HEADER, false); /* 返回头 */
    #curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    #curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    #curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    dd($tmpInfo);
    //通过curl_getinfo()可以得到请求头的信息
    #var_dump(curl_getinfo($curl));

    if (curl_errno($curl)) {
        echo 'Errno' . curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}

function currentTime()
{
    return date('Y-m-d H:i:s',time());
}

//生成订单号
function makeOder($prefix = null,$trimNum=null)
{
    $prefix = $prefix ? $prefix : '';
    $rand = rand(100000, 999999);
    if($trimNum){
        $length = strlen($rand) - $trimNum;
        $rand = substr($rand,0 ,$length);
    }
    $order_id_main = $prefix . date('YmHis') . $rand;
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for ($i = 0; $i < $order_id_len; $i++) {
        $order_id_sum += (int)(substr($order_id_main, $i, 1));
    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    return $osn;
}

function devInfo()
{
    $info = [
        'requestHost' => 'https://app.zbt.com',
        'apiKey' =>\think\Db::table('zbt')->where(['status'=>1,'flag'=>1])->find()['apiKey']
    ];
    return $info;
}

/**
 * 获取客户端ip
 */
function ip() {
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    return $res;
    //dump(phpinfo());//所有PHP配置信息
}


/**
 * @param $ip
 * @return mixed
 * 获取ip所在地址
 */
function get_ip_city($ip){
    $ch = curl_init();
    $url = 'https://whois.pconline.com.cn/ipJson.jsp?ip='.$ip;
    //用curl发送接收数据
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //请求为https
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $location = curl_exec($ch);
    curl_close($ch);
    //转码
    $location = mb_convert_encoding($location, 'utf-8','GB2312');
    //var_dump($location);
    //截取{}中的字符串
    $location = substr($location, strlen('({')+strpos($location, '({'),(strlen($location) - strpos($location, '})'))*(-1));
    //将截取的字符串$location中的‘，’替换成‘&’   将字符串中的‘：‘替换成‘=’
    $location = str_replace('"',"",str_replace(":","=",str_replace(",","&",$location)));
    //php内置函数，将处理成类似于url参数的格式的字符串  转换成数组
    parse_str($location,$ip_location);
    return $ip_location['addr'];
}


/**
 * 根据key删除数组中指定元素
 * @param  array  $arr  数组
 * @param  string/int  $key  键（key）
 * @return array
 */
function array_remove_by_key($arr, $key){
    if(!array_key_exists($key, $arr)){
        return $arr;
    }
    $keys = array_keys($arr);
    $index = array_search($key, $keys);
    if($index !== FALSE){
        array_splice($arr, $index, 1);
    }
    return $arr;
}


/**
 * 获取符合字段和字段值的数组集合
 * @param array $arr 待过滤数组
 * @param string $field 要查找的字段
 * @param $value 要查找的字段值
 * @return array 返回所有符合要求的数组集合
 */
function arrayFilterFieldValue($arr, $field, $value)
{
    $arr = array_filter($arr, function ($row) use ($field, $value) {
        if (isset($row[$field])) {
            return $row[$field] == $value;
        }

    });

    return $arr;
}

/**
 * @return string
 * 判断当前环境是http还是https
 */
function is_http(){
    $http = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http;
}


function get_local_ip()
{
//    $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
////获取操作系统为win2000/xp、win7的本机IP真实地址
//    exec("ipconfig", $out, $stats);
//    if (!empty($out)) {
//        foreach ($out AS $row) {
//            if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
//                $tmpIp = explode(":", $row);
//                if (preg_match($preg, trim($tmpIp[1]))) {
//                    return trim($tmpIp[1]);
//                }
//            }
//        }
//    }
////获取操作系统为linux类型的本机IP真实地址
//    exec("ifconfig", $out, $stats);
//    if (!empty($out)) {
//        if (isset($out[1]) && strstr($out[1], 'addr:')) {
//            $tmpArray = explode(":", $out[1]);
//            $tmpIp = explode("", $tmpArray[1]);
//            if (preg_match($preg, trim($tmpIp[0]))) {
//                return trim($tmpIp[0]);
//            }
//        }
//    }
//    return '127.0.0.1';

    return gethostbyname(null);

}

function get_port(){
    return ':'.$_SERVER['SERVER_PORT'].'/';
}

//http+ip+端口
function mainName(){
//    return is_http().get_local_ip().(get_port() == ':80' ? '' : get_port());
//    return is_http().$_SERVER['SERVER_NAME'].(get_port() == ':80' ? '' : get_port());
//    return is_http().'dmskins.com/';
}

//用于 支付回调/邀请链接生成/定时任务访问域名
function mainName1(){
//    return is_http().get_local_ip().(get_port() == ':80' ? '' : get_port());
    return is_http().$_SERVER['SERVER_NAME'].(get_port() == ':80' ? '' : get_port());
//    return is_http().'dmskins.com/';
//    return is_http().'csgo.fzxbwl.com/';
}

//任务请求地址
function taskUrl(){
    return 'csgo.com:81/';
}

function socketAddress(){
    return get_local_ip();
//    return is_http().'dmskins.com/';
//    return is_http().'csgo.fzxbwl.com/';
}



/**
 * 二维数组排序，$arr是数据，$keys是排序的健值，$order是排序规则，0是升序，1是降序
 * arr 二维数组
 * key根据那个键排序
 * order是排序规则
 */
function array_sort($arr, $keys, $order=0) {
    if (!is_array($arr)) {
        return false;
    }
    $keysvalue = array();
    foreach($arr as $key => $val) {
        $keysvalue[$key] = $val[$keys];
    }
    if($order == 0){
        asort($keysvalue);
    }else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach($keysvalue as $key => $vals) {
        $keysort[$key] = $key;
    }
    $new_array = array();
    foreach($keysort as $key => $val) {
        $new_array[$key] = $arr[$val];
    }
    return $new_array;
}


//判断二维数组中是否存在某个值
function deep_in_array($value, $array)
{
    foreach ($array as $item) {
        if (!is_array($item)) {
            if ($item == $value) {
                return true;
            } else {
                continue;
            }
        }
        if (in_array($value, $item)) {
            return $item;
        } else if (deep_in_array($value, $item)) {
            return $item;
        }
    }
    return false;
}


//生成随机字符，用于token之类的
function randStr($len=null){
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}


//二维数组排序
function arraySort($array,$keys,$sort='asc') {
    $newArr = $valArr = array();
    foreach ($array as $key=>$value) {
        $valArr[$key] = $value[$keys];
    }
    ($sort == 'asc') ?  asort($valArr) : arsort($valArr);
    reset($valArr);
    foreach($valArr as $key=>$value) {
        $newArr[$key] = $array[$key];
    }
    return $newArr;
}


//判断url地址是否正确
function is_url($url){
    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        return true;
    }else{
//        echo 'url 地址不正确';
        return false;
    }
}

//保留2位小数
function keep_decimal($number){
    $number = number_format($number,'2','.','');
    return $number;
}


/**
 * 讲数组转换为string
 *
 * @param $para 数组
 * @param $sort 是否需要排序
 * @param $encode 是否需要URL编码
 * @return string
 */
function createLinkString($para, $sort=false, $encode=false) {
    if($para == NULL || !is_array($para))
        return "";

    $linkString = "";
    if ($sort) {
        $para = argSort ( $para );
    }

//	while ( list ( $key, $value ) = each ( $para ) ) {  //each废弃函数
    foreach ($para as $key => $value) {
        if ($encode) {
            $value = urlencode ( $value );
        }
        $linkString .= $key . "=" . $value . "&";
    }
    // 去掉最后一个&字符
    $linkString = substr ( $linkString, 0, -1 );

    return $linkString;
}





