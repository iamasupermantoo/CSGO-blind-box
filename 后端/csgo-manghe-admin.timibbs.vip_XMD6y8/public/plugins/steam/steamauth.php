<?php
ob_start();
session_start();

function logoutbutton() {
    echo "<form action='' method='get'><button name='logout' type='submit'>Logout</button></form>"; //logout button
}

function loginbutton($buttonstyle = "square") {
    $button['rectangle'] = "01";
    $button['square'] = "02";
    $button = "<a href='?steam_login'>login</a>";

    echo $button;
}
if (isset($_POST['steam_login'])){
//if (isset($_GET['steam_login'])){
    require 'openid.php';
    try {
        require 'SteamConfig.php';
        $openid = new LightOpenID($steamauth['domainname']);
        if(!$openid->mode) {
            $openid->identity = 'https://steamcommunity.com/openid';
//            $openid->identity = 'https://steamcommunity.com/openid/login';
            $authUrl = $openid->authUrl();
            return;
//            header('Location: ' . $openid->authUrl());
        } elseif ($openid->mode == 'cancel') {
            echo 'User has canceled authentication!';
        } else {
            if($openid->validate()) {
                $id = $openid->identity;
                $ptn = "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
                preg_match($ptn, $id, $matches);

                $_SESSION['steamid'] = $matches[1];
                if (!headers_sent()) {
                    header('Location: '.$steamauth['loginpage']);
                    exit;
                } else {
                    ?>
                    <script type="text/javascript">
                        window.location.href="<?=$steamauth['loginpage']?>";
                    </script>
                    <noscript>
                        <meta http-equiv="refresh" content="0;url=<?=$steamauth['loginpage']?>" />
                    </noscript>
                    <?php
                    exit;
                }
            } else {
                echo "User is not logged in.\n";
            }
        }
    } catch(ErrorException $e) {
        echo $e->getMessage();
    }
}

if (isset($_GET['logout'])){
    require 'SteamConfig.php';
    session_unset();
    session_destroy();
    header('Location: '.$steamauth['logoutpage']);
    exit;
}

if (isset($_GET['update'])){
    unset($_SESSION['steam_uptodate']);
    require 'userInfo.php';
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// Version 4.0

?>