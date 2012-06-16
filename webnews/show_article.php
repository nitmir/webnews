<?php
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/
?>
<div style="font-family: <?php echo $font_family; ?>">
<table cellspacing="2" cellpadding="2" border="0" width="100%">
	<tr>
<?php
                if (!is_requested("art_group") || (strcmp(get_request("art_group"), $_SESSION["newsgroup"]) == 0)) {
?>
		<td nowrap="nowrap">
			<form action="newsgroups.php">
				<input type="hidden" name="compose" value="reply">
				<input type="hidden" name="mid" value="<?php echo $article_id ?>">
				<input type="submit" value="<?php echo $messages_ini["control"]["reply"]; ?>" style="<?php echo $form_style_bold; ?>"></form></td>
<?php
		}
?>
		<td nowrap="nowrap">
			<form action="newsgroups.php">
				<input type="hidden" name="mid" value="<?php echo $article_id; ?>">
				<input type="hidden" name="renew" value="0">
<?php
				if (isset($_SESSION["search_txt"])) {
?>
				<input type="submit" value="<?php echo $messages_ini["control"]["return_search"]; ?>" style="<?php echo $form_style_bold; ?>"></form></td>
<?php
				} else {
?>
				<input type="submit" value="<?php echo $messages_ini["control"]["return"]; ?>" style="<?php echo $form_style_bold; ?>"></form></td>
				<td nowrap="nowrap" width="100%">
<?php
				}
				
	if (!$nntp->connect()) {
		echo "<b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
		echo $nntp->get_error_message()."<br>";
		echo '	</tr></table>';
	} else {
		if (is_requested("art_group")) {
			$group_info = $nntp->join_group(get_request("art_group"));
		} else {
			$group_info = $nntp->join_group($_SESSION["newsgroup"]);
		}
		
		if ($group_info == NULL) {
			echo "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
			echo $nntp->get_error_message()."<br>";
			echo '	</tr></table>';
		} else {
			$MIME_Message = $nntp->get_article($article_id);
			$raw_body = $nntp->get_raw_body($_GET['article_id']);
			if ($MIME_Message == NULL) {
				echo "<b>".$messages_ini["error"]["article_fail"]."$article_id </b><br>";
				echo $nntp->get_error_message()."<br>";
				echo '	</tr></table>';
			} else {
			    $message_node = NULL;
			    if ($thread_search_size > 0) {
    			    $header = $MIME_Message->get_main_header();
			    if(isset($header['x-cancel-lock'])&&x_cancel_lock($raw_body)==$header['x-cancel-lock']){
?>		
			<script type="text/javascript">
			function check(){
			var ok = confirm('<?php echo $messages_ini["control"]["cancel_message_confirm"]; ?>');
				if(ok == true){
				document.cancel.submit();
				  }
				}
			</script>
			<form action="newsgroups.php" name="cancel">
				<input type="hidden" name="cancel_id" value="<?php echo $article_id ?>">
				<input type="hidden" name="cancel_group" value="<?php echo get_request('art_group') ?>">
				<input type="button" value="<?php echo $messages_ini["control"]["cancel_message"]; ?>" style="<?php echo $form_style_bold; ?>" onClick="check();">
			</form>
		<?php } ?>
		</td>
		<td nowrap="nowrap" width="100%">
			<a href="newsgroups.php?art_group=<?php echo get_request('art_group') ?>&article_id=<?php echo $article_id ?>&raw=1" title="<?php echo $messages_ini["help"]["source"]; ?>"><small><?php echo $messages_ini["control"]["source"]; ?></small></a>
		</td>
	</tr>
</table>

<?php
//	$nntp = new NNTP($nntp_server, $user, $pass);
	
	
			
	    		    if (!isset($header["references"]) || (strlen($header["references"]) == 0)) {
			            $ref = $header["message-id"];
			        } else {
			            $ref = preg_split("/\s+/", trim($header["references"]));
			            $ref = $ref[0];
			        }
			    
                    // Search through +/- n messages only
			        $message_node = $nntp->get_message_thread(max(0,$article_id - $thread_search_size), $article_id + $thread_search_size, $ref);
				if(!$message_node){
					echo ($nntp->get_error_message());
					echo ': '. ($article_id - $thread_search_size) .'-'. ($article_id + $thread_search_size);
				}else{
			        	$message_node->set_show_all_children(TRUE);
			        	$message_node->compact_tree();
				}
			    }

				include("webnews/article_template.php");				
			}
		}	
	}
?>

</div>
