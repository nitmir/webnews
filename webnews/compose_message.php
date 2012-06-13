<?php
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

	unset($_SESSION["attach_count"]);
	if ((strcasecmp($compose,"reply") == 0) && is_requested("mid")) {
//		$nntp = new NNTP($nntp_server, $user, $pass);
		$reply_id = get_request("mid");
		
		if (!$nntp->connect()) {
			echo "<font face=\"$font_family\"><b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
			echo $nntp->get_error_message()."<br>";
		} else {
			$group_info = $nntp->join_group($_SESSION["newsgroup"]);
			
			if ($group_info == NULL) {				
				echo "<font face=\"$font_family\"><b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
				echo $nntp->get_error_message()."<br>";
			} else {
				$MIME_Message = $nntp->get_article($reply_id);
				$header = $MIME_Message->get_main_header();
				
				$subject = htmlescape($header["subject"]);
				if (strcasecmp(substr($subject, 0, 3), "Re:") != 0) {
					$subject = "Re: ".$subject;
				}

				$message = "";
				foreach ($MIME_Message->get_all_parts() as $part) {
					if (stristr($part["header"]["content-type"], "text")) {
						$message .= decode_message_content($part);
					}
				}
				
				$message = wordwrap($message, 75, "\r\n", true);
                                $message = preg_replace("/\n-- \r\n(.*)/s","\n",$message);
				$message = preg_replace("/(.*\r\n)/", "&gt; $1", htmlescape($message));
				$message = ($header["from"]["name"])." ".$messages_ini["text"]["wrote"].":\r\n\r\n".($message);
			}
			// Quit sooner to release resources		
			$nntp->quit();
		}
	}
	
	$name = $_SESSION["nom"];
	$email = $_SESSION["mail"];
	//~ $name = "Valentin Samir";
	//~ $email = "valentin.samir@crans.org";


	//~ if (strcmp($_COOKIE["wn_pref_sign".$user], "1") == 0) {
		//~ if (strcmp($_COOKIE["wn_pref_sign_txt".$user], "") != 0) {
			//~ $message .= "\r\n\r\n--\r\n".$_COOKIE["wn_pref_sign_txt".$user];
		//~ }
	//~ }
	

	include("webnews/compose_template.php");
?>
