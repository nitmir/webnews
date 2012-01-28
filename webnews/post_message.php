<?
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/
?>
<?
//	$nntp = new NNTP($nntp_server, $user, $pass);
	$reply_references = "";

	if (!$nntp->connect()) {
		echo "<font face=\"".$font_family."\"><b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
		echo $nntp->get_error_message()."<br>";
	} else {
		if (is_requested("reply_id")) {	
			$reply_id = get_request("reply_id");
			if (isset($_SESSION["result"]) && $_SESSION["result"]) {				
				$ref_list = $_SESSION["result"][1];
				
				foreach ($ref_list[$reply_id][1] as $ref) {
					$reply_references = $reply_references." ".$ref;
				}
				
				$reply_references = $reply_references." ".$ref_list[$reply_id][0];
			} else {
				$group_info = $nntp->join_group($_SESSION["newsgroup"]);
				
				if ($group_info == NULL) {					
					$error_messages[] = "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]."</b><br>".$nntp->get_error_message();
				} else {
					$MIME_Message = $nntp->get_header($reply_id);
					$header = $MIME_Message->get_main_header();
		
					if ($header == NULL) {
						$error_messages[] = $messages_ini["error"]["header_fail"]."$reply_id. ".$nntp->get_error_message();
					} else {
						$reply_references = $header["references"]." ".$header["message-id"];
					}	
				}
			}
			$reply_references = trim($reply_references);
		}
		
		$header = array();
		
		// Copy the request parameter
		if (is_requested("subject")) {
			$subject = get_request("subject");
		}
		if (is_requested("groups")) {
			$groups = get_request("groups");
		}
		if (is_requested("name")) {
			$name = get_request("name");
		}
		if (is_requested("email")) {
			$email = get_request("email");
		}
		if (is_requested("attachment")) {
			$attachment = get_request("attachment");
		}
		if (is_requested("message")) {
			$message = get_request("message");
			$message = $message."\n".$signature;
			$message = wordwrap($message, 80, "\r\n", true);
		}
		// Done

		if (is_requested("post")) {
			if (!isset($subject) || (strlen($subject) == 0)) {
				$subject = "(no subject)";
			}
	
			if (isset($groups) && (sizeof($groups) != 0)) {
				foreach ($groups as $group) {
					if (in_array($group, $newsgroups_list)) {
						$news[] = $group;
					}
				}
			} else {
				$error_messages[] = $messages_ini["error"]["no_newsgroup"];
			}
	
			if (!isset($name) || (strlen($name) == 0)) {
				$error_messages[] = $messages_ini["error"]["no_name"];
			}
			
			if (!isset($email) || (strlen($email) == 0) || !validate_email($email)) {
				$error_messages[] = $messages_ini["error"]["no_email"];
			} /*else if (!check_email_list($email)) {
				$error_messages[] = "Your e-mail address is not in the authorized list. Please contact the administrator.";
			}
	*/
			$files = array();
			if (isset($attachment)) {
				$file_size = 0;
				foreach ($_FILES as $file) {
					if (is_uploaded_file($file['tmp_name'])) {
						$files[] = $file;
						$file_size += filesize($file['tmp_name']);
						if ($file_size > $upload_file_limit) {
							$error_messages[] = $messages_ini["error"]["exceed_size"].($upload_file_limit >> 10)."Kb";
							break;
						}
					}
				}
			}
			
			// Strip all the slashes
			if (get_magic_quotes_gpc()) {
				$subject = stripslashes($subject);
				$name = stripslashes($name);
				$email = stripslashes($email);
				$message = stripslashes($message);
			}
	
			if (!isset($error_messages)||sizeof($error_messages) == 0) {
?>
				<form action="newsgroups.php">
				<font face="Tahoma, Sans-Serif">
					<table cellspacing="2" cellpadding="2" border="0" width="95%">
						<tr>
							<td nowrap="true">
									<input type="hidden" name="renew" value="1">
									<input type="submit" value="<? echo $messages_ini["control"]["return"]; ?>" style="<? echo $form_style_bold; ?>"></td>
						</tr>
					</table>
				</font>
<?
				// Save the name and email in the session
				$_SESSION["wn_name"] = $name;
				$_SESSION["wn_email"] = $email;
				
				if ($MIME_Message = $nntp->post_article($subject, $name, $email, $news, $reply_references, $message, $files)) {
					echo "<center><font face=\"".$font_family."\" size=\"$font_size\"><b>".$messages_ini["text"]["posted"]."</b></font></center><br>";

                    $message_node = NULL;
					include("webnews/article_template.php");
				} else {
					echo "<font face=\"".$font_family."\"><b>".$messages_ini["error"]["post_fail"]."</b><br>";
					echo $nntp->get_error_message()."<br>";
				}
				
				unset($_SESSION["attach_count"]);
			}
?>
			</form>
<?
		}
		if (is_requested("add_file") || (isset($error_messages)&&sizeof($error_messages) != 0)) {
			$subject = htmlescape($subject);
			$name = htmlescape($name);
			$email = htmlescape($email);
			include("webnews/compose_template.php");
		}
	}
?>
