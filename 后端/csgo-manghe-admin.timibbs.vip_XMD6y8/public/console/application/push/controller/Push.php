<?php
namespace app\push\controller;

use app\index\controller\Battle;
use think\Db;

class Push{

    // private $address = '192.168.101.12';//本地使用
//    private $address = '192.168.31.72';//本地使用
    private $address = '127.0.0.1';//服务器使用

    public function push($battleInfo=null,$state=null){
        error_reporting(E_ALL);
        //端口
        $service_port = 7654;
        //本地
        $address = $this->address;
        //创建 TCP/IP socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            return "socket创建失败原因: " . socket_strerror($socket) . "\n";
        } else {
//            echo "OK，HE HE.\n";
        }
        $result = socket_connect($socket, $address, $service_port);
        if ($result < 0) {
            return "SOCKET连接失败原因: ($result) " . socket_strerror($result) . "\n";
        } else {
//            echo "OK.\n";
        }
        //发送命令
//        $in = "HEAD / HTTP/1.1\r\n";
//        $in .= "Connection: Close\r\n\r\n";
//        $out = '';
//        echo "Send Command..........";
//        $in = "sun\n";

//        $res = "HEAD / HTTP/1.1\r\n";
        $battleInfo['player_info'] = unserialize($battleInfo['player_info']);
//        $battleInfo['boxInfo'] = json_decode($battleInfo['boxInfo'],true);
        $res['info'] = $battleInfo;
        $res['state'] = $state;

        $res = json_encode($res);

        socket_write($socket, $res, strlen($res));
        return true;
        echo "OK.\n";
        echo "Reading Backinformatin:\n\n";
        while ($out = socket_read($socket, 2048)) {
            echo $out;
        }
        echo "Close socket........";
        socket_close($socket);
        echo "OK,He He.\n\n";
        return 1;
        exit();
    }


    public function push1($skins_info=null,$state=null){
        error_reporting(E_ALL);
        //端口
        $service_port = 7655;
        //本地
        $address = $this->address;

        //创建 TCP/IP socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            return "socket创建失败原因: " . socket_strerror($socket) . "\n";
        } else {
//            echo "OK，HE HE.\n";
        }
        $result = socket_connect($socket, $address, $service_port);
        if ($result < 0) {
            return "SOCKET连接失败原因: ($result) " . socket_strerror($result) . "\n";
        } else {
//            echo "OK.\n";
        }
        $res['info'] = $skins_info;
        $res['state'] = $state;
        $res = json_encode($res);
        socket_write($socket, $res, strlen($res));
        return true;
    }
}