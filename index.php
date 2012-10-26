<?php
require("config/webnews.cfg.php");
require("webnews/util.php");
require_once("webnews/login.php");

session_name($session_name);
session_start();

if(is_loged()){
	header("Location: newsgroups.php");
	exit;
}

if (isset($_COOKIE["wn_pref_lang"])) {
        $text_ini = "config/messages_".$_COOKIE["wn_pref_lang"].".ini";
        setlocale (LC_TIME, $locale_time_list[$_COOKIE["wn_pref_lang"]]);
}
$messages_ini = read_ini_file($text_ini, true);

if (is_requested("forget_password")) {
	$content_page='webnews/mdp.php';
}else{
	$content_page='webnews/index.php';
}
include($template);
?>

