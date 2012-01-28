<?
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
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
			$rss_feed_count = (int)get_request("rss_feed");
			if ($rss_feed_count <= 0) {
				$rss_feed_count = $message_per_page;
			}

			$article_list = $nntp->get_article_list($_SESSION["newsgroup"]);
			$end_id = sizeof($article_list) - 1;
			$start_id = $end_id - $rss_feed_count + 1;
			if ($start_id < 0) {
				$start_id = 0;
			}
			$message_summary = $nntp->get_summary($article_list[$start_id], $article_list[$end_id]);
			// Sort message by date
			uasort($message_summary, cmp_by_date);
		}
	}

	ob_clean();
	header("Content-Type: application/xml");
	/*echo '<?xml version="1.0" encoding="ISO-8859-1" ?>'."\n";*/
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>
<rss version="2.0">
	<channel>
		<title><? echo $_SESSION["newsgroup"]; ?></title>
<?
		echo "<description>".$messages_ini["text"]["newsgroup"]." ".$_SESSION["newsgroup"];
		$desc = $nntp->get_groups_description($_SESSION["newsgroup"]);
		if (sizeof($desc) > 0) {
			echo " - ".$desc[$_SESSION["newsgroup"]];
		}
		echo "</description>";
		// Quit sooner to release resources
		$nntp->quit();
?>
		<link><? echo construct_url("newsgroups.php?group=".urlencode($_SESSION["newsgroup"])); ?></link>
<?
		echo "<lastBuildDate>".date("D, d M Y H:i:s O", time())."</lastBuildDate>";
?>
		<generator>Web-News v.1.6.3 (NNTP to RSS Engine)</generator> 
		<image>
			<url>http://web-news.sourceforge.net/images/webnews.gif</url>
			<title>Web-News logo</title>
			<link>http://web-news.sourceforge.net</link>
			<width>40</width>
			<height>40</height>
			<description>RSS feed provided by Web-News v.1.6.3 (NNTP to RSS Engine)</description>
		</image>
<?
	foreach ($message_summary as $mid=>$message) {
		echo "<item>\n";
		echo "<title>";
		echo htmlescape($message["subject"]);
		echo "</title>\n";

		$link = htmlescape(construct_url("newsgroups.php?art_group=".urlencode($_SESSION["newsgroup"])."&article_id=$mid"));
		echo "<link>";
		echo $link;
		echo "</link>\n";
		
		echo "<description>";
		echo $messages_ini["text"]["read_article"]."&lt;br&gt;&lt;br&gt;&lt;a href=\"";
		echo $link;
		echo "\"&gt;".$link."&lt;/a&gt;";
		echo "</description>\n";

		if ($_SESSION["auth"]) {
			echo "<author>";
			echo htmlescape($message["from_email"])." (".htmlescape($message["from_name"]).")";
			echo "</author>\n";
		}
		
		echo "<pubDate>";
		echo date("D, d M Y H:i:s O", $message["date"]);
		echo "</pubDate>\n";
		
		echo "</item>\n";
	}
?>
	</channel>
</rss>

<?
	exit(0);

	// Compare function for sorting message by date in ascending order
	function cmp_by_date($msg1, $msg2) {
		if ($msg1["date"] == $msg2["date"]) {
			return 0;
		}
		return ($msg1["date"] < $msg2["date"])?-1:1;
	}
?>
