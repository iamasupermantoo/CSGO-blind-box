<?php
if (empty($_SESSION['steam_uptodate']) or empty($_SESSION['steam_personaname'])) {
	require 'SteamConfig.php';
	$url = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$_SESSION['steamid']); 
	$content = json_decode($url, true);
	$_SESSION['steam_steamid'] = $content['response']['players'][0]['steamid'];
	
	$_SESSION['steam_personaname'] = $content['response']['players'][0]['personaname'];
	
}

$steamprofile['steamid'] = $_SESSION['steam_steamid'];

$steamprofile['personaname'] = $_SESSION['steam_personaname'];


// Version 4.0
?>
    
