<?php
namespace app\admin\controller;

class Worker{
    //监听socket
    protected $socket = NULL;

    //所有的socket连接
    protected $allSockets = array();

    //连接事件回调
    public $onConnect = NULL;

    //断线事件回调
    public $onClose = NULL;

    //接收消息事件回调
    public $onMessage = NULL;

    public function __construct($socket_address) {
        //创建一个socket监听
        $this->socket = stream_socket_server($socket_address);

        //设置为非阻塞
        stream_set_blocking($this->socket, 0);

        //将socket监听加入allSockets
        $this->allSockets[(int)$this->socket] = $this->socket;
    }

    public function run() {
        while(true) {
            //不监听可写事件与带外数据事件
            $write = $except = array();
            //监听所有的socket事件
            $read = $this->allSockets;
            //整个进程阻塞在这里，持续监听可读事件
            //此处参数均为引用传递，在函数中会改变传值
            stream_select($read, $write, $except, 60);

            //处理所有可读事件
            foreach ($read as $index => $socket) {
                //如果是监听socket，此处表示有新的连接
                if ($socket === $this->socket) {
                    //通过stream_socket_accept获取新的连接
                    $new_conn_socket = stream_socket_accept($socket);

                    if ($this->onConnect) {
                        //触发连接事件的回调,并将当前连接传递给回掉函数
                        call_user_func($this->onConnect, $socket);
                    }
                    //记录此socket连接,以便于sream_select监听可读事件
                    $this->allSockets[(int)$new_conn_socket] = $new_conn_socket;
                } else
                    //如果可读事件不为监听socket，则表示对应客户端有数据发过来
                {
                    //从连接中读取数据
                    $buffer = fread($socket, 65535);
                    //如果数据为空，表示客户端已经断开连接
                    if ('' === $buffer || false === $buffer) {
                        //尝试触发onClose回调
                        if ($this->onClose) {
                            call_user_func($this->onClose, $socket);
                        }
                        fclose($socket);
                        //关闭socket连接并从allSockets中删除
                        unset($this->allSockets[(int)$socket]);
                        continue;
                    }
                    //表示一个正常的连接，已经读取到消息，交给回掉函数处理
                    if ($this->onMessage) {
                        call_user_func($this->onMessage, $socket, $buffer);
                    }
                }
            }
        }
    }
}

$worker = new Worker('tcp://localhost:9865');

$worker->onConnect = function ($conn) {
    echo '新的连接来了';
};
$worker->onClose = function ($conn) {
    echo '连接断开了';
};
$worker->onMessage = function ($conn, $message) {
    $http_resonse = "HTTP/1.1 200 OK\r\n";
    $http_resonse .= "Connection: keep-alive\r\n";
    $http_resonse .= "Server: php socket server\r\n";
    $http_resonse .= "Content-length: 11\r\n\r\n";
    $http_resonse .= "hello world";
    fwrite($conn, $http_resonse);
};

$worker->run();