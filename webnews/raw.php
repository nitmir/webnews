<?php
/*
	This PHP script is licensed under the GPL

*/

	if (!$nntp->connect()) {
		echo "<b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
		echo $nntp->get_error_message()."<br>";
		exit;
	} else {
		$group_info = $nntp->join_group($_SESSION["newsgroup"]);

		if ($group_info == NULL) {
			echo "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
			echo $nntp->get_error_message()."<br>";
			exit;
		} else {
			$message = $nntp->get_raw_article($article_id);
			if($message == NULL) {
				echo "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
				echo $nntp->get_error_message()."<br>";
				exit;
			}
		}
	}

	ob_clean();
	header("Content-Type: text/plain");
	print_r($message);
	exit(0);
?>