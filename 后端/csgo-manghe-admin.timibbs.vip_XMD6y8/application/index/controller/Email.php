<?php


namespace app\index\controller;

use phpmailer\PHPMailer;

class Email
{
    public function sendMsg($email, $code)
    {

        include_once $_SERVER ['DOCUMENT_ROOT'] . '/../vendor/phpmailer/Exception.php';
        include_once $_SERVER ['DOCUMENT_ROOT'] . '/../vendor/phpmailer/PHPMailer.php';
        include_once $_SERVER ['DOCUMENT_ROOT'] . '/../vendor/phpmailer/SMTP.php';

        $host = 'smtp.163.com';
        $port = '465';
        $user = 'kaixianggo@163.com';
        $pass = 'YADNWSYDJMNMFOBQ';
        $email_name = 'gosking';
        $secure = 'ssl'; // tls ,ssl

        $subject = '平台账号';
        $body = '您的验证码是：' . $code;

        $mail = new PHPMailer(); //PHPMailer对象
        $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码

        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug = 0; // 关闭SMTP调试功能   2:调试 0: 关闭调试
        $mail->SMTPAuth = true; // 启用 SMTP 验证功能
        $mail->SMTPSecure = $secure; // 使用安全协议
        $mail->Host = $host; // SMTP 服务器
        $mail->Port = $port; // SMTP服务器的端口号
        $mail->Username = $user; // SMTP服务器用户名
        $mail->Password = $pass; // SMTP服务器密码
        $mail->SetFrom($user, $email_name);
        $replyEmail = $user;
        $replyName = $email_name;
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";
        $mail->MsgHTML($body);
        $mail->AddAddress($email);

        if ($mail->Send()) {
            return result(1, '', '邮箱发送成功');
        } else {
            return result(0, '', $mail->ErrorInfo);
        }
    }
}