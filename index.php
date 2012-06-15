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

$content_page='webnews/index.php';
include($template);
?>

