<?php
/*
	This PHP script is licensed under the GPL
	
	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

	$header = $MIME_Message->get_main_header();
	$parts = $MIME_Message->get_all_parts();
	if (is_requested("art_group")) {
		$group = get_request("art_group");
	} else {
		$group = $_SESSION["newsgroup"];
	}
?>

<font face="<?php echo $font_family; ?>">
<table cellpadding="5" cellspacing="0" border="0" align="left" width="100%">
	<tr>
		<td bgcolor="<?php echo $primary_color; ?>" width="15%" valign="top"><font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["subject"]; ?></b></font></td>
		<td bgcolor="<?php echo $secondary_color; ?>"><font size="<?php echo $font_size; ?>"><?php echo htmlescape(utf8($header["subject"]));?></td>
	</tr>
	<tr>
		<td bgcolor="<?php echo $primary_color; ?>" width="15%" valign="top"><font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["from"]; ?></b></font></td>
		<td bgcolor="<?php echo $secondary_color; ?>"><font size="<?php echo $font_size; ?>">
<?php
		if (is_requested("post") || $_SESSION["auth"]) {
			echo "<a href=\"mailto:".htmlescape(utf8($header["from"]["email"]))."\">";
		}
		echo htmlescape(utf8($header["from"]["name"]));
		
		if (is_requested("post") || $_SESSION["auth"]) {
			echo htmlescape(" <".utf8($header["from"]["email"]).">")."</a>";
		}

		if($nntp->validate_article($_GET['article_id'],$header['x-webnews'])){
			echo ' (depuis le webnews)';
		}
?>
		</td>
	</tr>
	<tr>
		<td bgcolor="<?php echo $primary_color; ?>" width="15%" valign="top"><font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["date"]; ?></b></font></td>
		<td bgcolor="<?php echo $secondary_color; ?>"><font size="<?php echo $font_size; ?>"><?php echo $header["date"]; ?></td>
	</tr>
	<tr>
		<td bgcolor="<?php echo $primary_color; ?>" width="15%" valign="top"><font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["newsgroups"]; ?></b></font></td>
		<td bgcolor="<?php echo $secondary_color; ?>"><font size="<?php echo $font_size; ?>"><?php echo utf8($header["newsgroups"]); ?></td>
	</tr>
<!--
	<tr>
		<td bgcolor="<?php echo $primary_color; ?>" width="15%" valign="top"><font size="<?php echo $font_size; ?>"><b>Content-Type</b></font></td>
		<td bgcolor="<?php echo $secondary_color; ?>"><font size="<?php echo $font_size; ?>"><?php echo $header["content-type"]; ?></td>
	</tr>
-->
<?php
	if (sizeof($parts) > 1) {	// We've got attachment
		echo "<tr>\r\n";
		echo "<td bgcolor=\"$primary_color\" width=\"15%\" valign=\"top\"><font size=\"$font_size\"><b>".$messages_ini["text"]["attachments"]."</b></font></td>\r\n";
		echo "<td bgcolor=\"$secondary_color\"><font size=\"$font_size\">\r\n";
		$attach_file = "";
		for ($i = 1;$i < sizeof($parts);$i++) {
			if (($i != 1) && (($i - 1) % 5 == 0)) {
				$attach_file .= "<br>\r\n";
			}
			if (strcmp($parts[$i]["filename"], "") != 0) {
				$attach_file .= "<a href=\"newsgroups.php?art_group=".urlencode($group)."&message_id=".$article_id."&attachment_id=".$i."\" target=\"_blank\">".$parts[$i]["filename"]."</a>,&nbsp;";
			} else {
				$attach_file .= "<a href=\"newsgroups.php?art_group=".urlencode($group)."&message_id=".$article_id."&attachment_id=".$i."\" target=\"_blank\">".$messages_ini["text"]["no_name"]." $i</a>,&nbsp;";
			}
		}
		if (strlen($attach_file) > 0) {
			$attach_file = substr($attach_file, 0, strlen($attach_file) - 7);
		}
		echo $attach_file;
		echo "</td>\r\n";
		echo "</tr>\r\n";
	}

	$count = 0;
	

	foreach ($parts as $part) {
		if (stristr($part["header"]["content-type"], "text/html")) {	// HTML
			$body = filter_html(decode_message_content($part));

			// Replace the image link for internal resources
			$content_map = $MIME_Message->get_content_map();
			$search_array = array();
			$replace_array = array();
			foreach ($content_map as $cid => $aid) {
				$cid = substr($cid, 1, strlen($cid) - 2);
				$search_array[] = "cid:".$cid;
				$replace_array[] = "newsgroups.php?art_group=".urlencode($group)."&message_id=".$article_id."&attachment_id=".$aid;
			}
	
			$body = str_replace($search_array, $replace_array, $body);
			
			echo "<tr><td colspan=\"2\"><div>".$body."</div><br></td></tr>";
		} elseif (stristr($part["header"]["content-type"], "text")) {	// Treat all other form of text as plain text
			echo "<tr><td colspan=\"2\"><font size=\"$font_size\"><br>";
			$body = decode_message_content($part);
			$body = htmlescape($body);
			$body = preg_replace(array("/\n-- \r\n(.*)/s","/\r\n/", "/(^&gt;.*)/m", "/\t/", "/  /"),
										array("\n<font color=\"grey\">--\r\n$1</font>","<br>\r\n", "<i>$1</i>", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "&nbsp;&nbsp;"),
										add_html_links($body));
			echo utf8($body)."<br></td></tr>";
		} elseif (preg_match("/^image\/(gif|jpeg|pjpeg)/i", $part["header"]["content-type"])) {
			echo "<tr><td colspan=\"2\" align=\"center\">";
			echo "<hr width=\"100%\"><br>";
			echo "<img src=\"newsgroups.php?art_group=".urlencode($group)."&message_id=$article_id&attachment_id=$count\" border=\"0\">";
			echo "<br></td></tr>\r\n";

		}
		$count++;
	}	
    if ($message_node) {
?>
<tr><td colspan=2">
<table cellpadding="0" cellspacing="1" border="0" align="left" width="100%">
<tr>
    <td colspan="3" bgcolor="<?php echo $primary_color;?>"><b><font size="<?php echo $font_size;?>">
        <?php echo $messages_ini["text"]["recent_thread"];?>
    </font></b></td>    
</tr>
<tr>
    <td><font size="<?php echo ($font_size - 1);?>">&nbsp;</font></td>
</tr>
<?php
        display_tree($message_node->get_children(), 0, "", FALSE, $article_id);
?>
</table>
</td></tr>
<?php
    }
?>
</table>
</font>
<br><br>
