<?php
/*		
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

	$content = 4;
	$title = "Newsgroup";

	// Import the NNTP Utility
	require("webnews/nntp.php");
	require("config/webnews.cfg.php");
	require_once("webnews/login.php");
	
	// Increase the maximum running time to 2 mins (default is 30sec)
	set_time_limit(120);

	// Start the session before output anything
	session_name($session_name);
	session_start();

	dbconn();
	if(isset($_GET['logout'])){
		logout();
	}
	//~ print_r($_POST);
	//~ die();
	if(isset($_POST['mail'])&&isset($_POST['pass'])){
		if(login($_POST['mail'],$_POST['pass'])){
			header("Location: ".$_SERVER['PHP_SELF']);
			exit;
		}
	}
	if(!is_loged()){
		header("Location: ".construct_url($logout_url));
		exit;
	}

	if (is_requested("set")) {	// Save the advanced options into cookies
		$expire = 2147483647;	// Maximum integer
		setcookie("wn_pref_lang", get_request("language"), $expire);
		setcookie("wn_pref_mpp", get_request("msg_per_page"), $expire);
		
		if ($_COOKIE["wn_pref_mpp"] != get_request("msg_per_page")) {
			$change_mpp = TRUE;
		} else {
			$change_mpp = FALSE;
		}

		$_COOKIE["wn_pref_lang"] = get_request("language");
		$_COOKIE["wn_pref_mpp"] = get_request("msg_per_page");
	}

	// Read the messages file
	if (isset($_COOKIE["wn_pref_lang"])) {
		$text_ini = "config/messages_".$_COOKIE["wn_pref_lang"].".ini";
	}
	$messages_ini = read_ini_file($text_ini, true);

	// Perform logout
	if (is_requested("logout")) {
		$user = "";
		$pass = "";
		unset($_SESSION["auth"]);
		$_SESSION["logout"] = TRUE;
		unset($_SESSION["result"]);		// Destroy the subject tree.
			
		header("Location: ".construct_url($logout_url));
		exit;
	} 
	//~ else if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
		//~ $user = $_SERVER['PHP_AUTH_USER'];
		//~ $pass = $_SERVER['PHP_AUTH_PW'];
		//~ $user = "Vivelapa";
		//~ $pass = "ranoia!";

	//~ }

	//~ if ($auth_level > 1) {
		//~ if (($auth_level == 3) || (is_requested("compose") && ($auth_level == 2))) {
			//~ // Do HTTP Basic authentication
			//~ if ($_SESSION["logout"] || !isset($_SERVER['PHP_AUTH_USER'])) {
				//~ unset($_SESSION["logout"]);
				//~ header('WWW-Authenticate: Basic realm="'.$realm.'"');
				//~ header('HTTP/1.0 401 Unauthorized');
				//~ echo $messages_ini["authorization"]["login"];
				//~ exit;
			//~ } else {
				//~ // $_SESSION["auth"] must be checked firsr to avoid making too many connections
				//~ if ($_SESSION["auth"] || verify_login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
					//~ $user = $_SERVER['PHP_AUTH_USER'];
					//~ $pass = $_SERVER['PHP_AUTH_PW'];
					//~ $_SESSION["auth"] = TRUE;
				//~ } else {
					//~ header('WWW-Authenticate: Basic realm="'.$realm.'"');
					//~ header('HTTP/1.0 401 Unauthorized');
					//~ echo $messages_ini["authorization"]["login"];
					//~ exit;
				//~ }
			//~ }
			//~ // Authentication done
		//~ }
	//~ } else {
		$user = "Vivelapa";
		$pass = "ranoia!";
		//~ $_SESSION["auth"] = TRUE;
	//~ }

	// Create the NNTP object
	$nntp = new NNTP($nntp_server, $user, $pass, $proxy_server, $proxy_port, $proxy_user, $proxy_pass);
	// The quit() function will be called when the script terminate.
	register_shutdown_function(create_function('', 'global $nntp; $nntp->quit();'));

	// Load the newsgroups_list
	if (!isset($_SESSION["newsgroups_list"])) {	// Need to update the newsgroups_list first
		$_SESSION["newsgroups_list"] = array();
		foreach ($newsgroups_list as $group) {
			if (strpos($group, "*") !== FALSE) {	// Group name have wildmat, expand it.
				if (!$nntp->connect()) {
					unset($_SESSION["newsgroups_list"]);
					echo "<b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
					echo $nntp->get_error_message()."<br>";
					exit;
				}				

				$group_list = $nntp->get_group_list($group);
				if ($group_list !== FALSE) {
					sort($group_list);
					$_SESSION["newsgroups_list"] = array_merge($_SESSION["newsgroups_list"], $group_list);
				}
			} else {
				$_SESSION["newsgroups_list"][] = $group;
			}
		}		
	}
	$newsgroups_list = $_SESSION["newsgroups_list"];

	if (is_requested("cancel")) {
		// Back to show header
		$renew = 0;
		$content_page = "webnews/show_header.php";
	} else if (is_requested("attachment_id") && is_requested("message_id")) {
		$attachment_id = get_request("attachment_id");
		$message_id = get_request("message_id");
		$content_page = "webnews/attachment_handler.php";
	} else if (is_requested("compose")) {
		$compose = get_request("compose");
		if (strcasecmp($compose, "post") == 0) {
			// Do add_file or post
			$content_page = "webnews/post_message.php";
		} else {
			$content_page = "webnews/compose_message.php";
		}
	} else if (is_requested("preferences")) {
		$content_page = "webnews/preferences.php";
	} else {
		$renew = 0;
		if (is_requested("group") 
				&& in_array(get_request("group"), $newsgroups_list) 
				&& strcmp(get_request("group"), $_SESSION["newsgroup"])) {
			$_SESSION["newsgroup"] = get_request("group");
			$renew = 1;
		} else {
			if (!isset($_SESSION["newsgroup"])) {
				$_SESSION["newsgroup"] = $default_group;
				$renew = 1;
			}
		}

		if (is_requested("rss_feed")) {
			$content_page = "webnews/rss.php";
		} else if (is_requested("article_id")) {
			$article_id = get_request("article_id");
			$content_page = "webnews/show_article.php";
		} else {
			$content_page = "webnews/show_header.php";

			if (is_requested("renew")) {
				$renew = get_request("renew");
			} else if (isset($change_mpp)&&$change_mpp && !isset($_SESSION["search_txt"])) {
				$renew = 1;
			}

			if (is_requested("mid")) {
				$mid = get_request("mid");
				$on_load_script = "location = '#".$mid."';";
			}
		}
	}

	include ($template);
?>
