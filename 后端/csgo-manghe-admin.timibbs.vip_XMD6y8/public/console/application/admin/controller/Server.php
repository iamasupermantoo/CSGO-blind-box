<?php
namespace app\admin\controller;
use think\Db;

$sk = new Sock("localhost", 9522);

$sk->run();

class Sock
{
    public $master;

    public function __construct($address, $port)
    {
        //create a socket
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);

        //bind socket with address and port
        socket_bind($this->master, $address, $port);

        //listen!
        socket_listen($this->master);

        echo ('Server Started : '.date('Y-m-d H:i:s') ."\n");
        echo ('Listening on   : '.$address.' port '.$port ."\n");
    }

    function run()
    {
        while (true)
        {
            if ($client = socket_accept($this->master))
                //if a socket is accepted
            {
                $len = 0;
                $buffer = '';

                do{
                    $l = socket_recv($client, $buf, 1000, 0);
                    $len += $l;
                    $buffer .= $buf;
                }
                while($l == 1000);

                //handshake
                $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);

                $key  = trim(substr($buf,0,strpos($buf,"\r\n")));

                $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));

                $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
                $new_message .= "Upgrade: websocket\r\n";
                $new_message .= "Sec-WebSocket-Version: 13\r\n";
                $new_message .= "Connection: Upgrade\r\n";
                $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";

                socket_write($client, $new_message, strlen($new_message));
                //handshake over

                while(true)
                    //now listen the message sent by client
                    //because we know client is about to sent a message refer to index.php
                {
                    $buf = '';
                    $len = 0;
                    $buffer = '';

                    do{
                        $l = socket_recv($client, $buf, 1000, 0);
                        $len += $l;
                        $buffer .= $buf;
                    }while($l == 1000);

                    echo $this->decode($buffer)."\n";

                    break;
                }

            }
        }
    }

    function decode($str){
        $mask = array();
        $data = '';
        $msg = unpack('H*', $str);
        $head = substr($msg[1], 0, 2);

        if ($head == '81')
        {
            $len = substr($msg[1], 2, 2);
            $len = hexdec($len);

            if(substr($msg[1], 2, 2) == 'fe')
            {
                $len = substr($msg[1], 4, 4);
                $len = hexdec($len);
                $msg[1] = substr($msg[1],4);
            }
            else if(substr($msg[1], 2, 2) == 'ff'){
                $len = substr($msg[1], 4, 16);
                $len = hexdec($len);
                $msg[1] = substr($msg[1], 16);
            }

            $mask[] = hexdec(substr($msg[1], 4, 2));
            $mask[] = hexdec(substr($msg[1], 6, 2));
            $mask[] = hexdec(substr($msg[1], 8, 2));
            $mask[] = hexdec(substr($msg[1], 10, 2));
            $s = 12;
            $n = 0;
        }
        else
        {
            return 'decode failed';
        }

        $e = strlen($msg[1]) - 2;

        for ($i = $s; $i <= $e; $i += 2)
        {
            $data .= chr($mask[$n%4]^hexdec(substr($msg[1], $i, 2)));
            $n ++;
        }
        $ins['msg'] = json_encode($data);
        Db::table('ztest')->insert($ins);
        return $data;
    }

}

?>