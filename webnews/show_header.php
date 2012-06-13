<?php
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

	dbconn();

	$sort_by_list = array("subject", "from", "date");
	if (is_requested("sign")) {
		$sign = get_request("sign");
	}
	if (is_requested("sort")) {
		$sort = get_request("sort");
	}
	if ((isset($renew)&&$renew) || (isset($change_mpp)&&$change_mpp)) {
		$page = 1;
	} else if (is_requested("page")) {
		$page = intval(get_request("page"));
		if (isset($_SESSION["search_txt"])) {
		    $renew = 0;
		} else {
		    $renew = 1;
		}
	} else if (isset($_SESSION["last_page"])) {
		$page = $_SESSION["last_page"];
	} else {
		$page = 1;
	}

	if (is_requested("search")) {
		unset($_SESSION["search_txt"]);
		$do_search = TRUE;
		$page = 1;
	}

/*
	if (is_requested("sch_option")) {
		$_SESSION["sch_option"] = !$_SESSION["sch_option"];
	}
*/
	if (is_requested("option")) {
		$_SESSION["more_option"] = !(isset($_SESSION["more_option"])?$_SESSION["more_option"]:false);
	}

	if (isset($_COOKIE["wn_pref_mpp"])) {
		$message_per_page = $_COOKIE["wn_pref_mpp"];
	}

	$_SESSION["last_page"] = $page;

	if (!$nntp->connect()) {
		$_SESSION["result"] = null;
		echo "<b>".$messages_ini["error"]["nntp_fail"]."</b><br>";
		echo $nntp->get_error_message()."<br>";
		exit;
	} else {
		$group_info = $nntp->join_group($_SESSION["newsgroup"]);

		if ($group_info == NULL) {
			$_SESSION["result"] = null;
			echo "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
			echo $nntp->get_error_message()."<br>";
			exit;
		} else {			
			if ((isset($renew)&&$renew) || (isset($do_search)&&$do_search) || ($_SESSION["result"] == null)) {
				$renew = 1;
				$_SESSION["result"] = null;
				if ($group_info["count"] > 0) {
					$_SESSION["article_list"] = $nntp->get_article_list($_SESSION["newsgroup"]);
					
					/*$i=0;
					$query=mysql_query("SELECT * FROM post WHERE user_id='".$_SESSION["id"]."' AND `group`='".$_SESSION["newsgroup"]."'")or die(mysql_error());
					while($data=mysql_fetch_assoc($query)){
						$saw[$data['group_id']]=true;
					}
					for($j=sizeof($_SESSION["article_list"])-1;isset($_SESSION["article_list"][$j]);$j--){
						if(isset($_SESSION['read_all_id'][$_SESSION["newsgroup"]])&&$_SESSION["article_list"][$j]<=$_SESSION['read_all_id'][$_SESSION["newsgroup"]]){
							break;
						}
						if(!isset($saw[$_SESSION["article_list"][$j]])){
							$i++;
						}
					}*/
					if ($_SESSION["article_list"] === FALSE) {
						unset($_SESSION["article_list"]);
						echo "<b>".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </b><br>";
						echo $nntp->get_error_message()."<br>";
						exit;
					}				
					
					if (isset($do_search)&&$do_search) {
						$search_txt = get_request("search_txt");
						if (get_magic_quotes_gpc()) {
							$search_txt = stripslashes($search_txt);
						} 
						$search_pat = make_search_pattern($search_txt);
						$flat_tree = TRUE;
						$_SESSION["search_txt"] = htmlescape($search_txt);
					} else {
						$search_pat = "//";
						$flat_tree = FALSE;
						unset($_SESSION["search_txt"]);
					}					
					
					if ((strcmp($message_per_page, "all") == 0) || (isset($do_search)&&$do_search)) {
						// Search through all messages
						$start_id = 0;
						$end_id = sizeof($_SESSION["article_list"]) - 1;
					} else {
						$end_id = sizeof($_SESSION["article_list"]) - $message_per_page*($page - 1) - 1;
						$start_id = $end_id - $message_per_page + 1;
					}
					if ($start_id < 0) {
						$start_id = 0;
					}

					$result = $nntp->get_message_summary($_SESSION["article_list"][$start_id], $_SESSION["article_list"][$end_id], $search_pat, $flat_tree);
					if (isset($result)&&$result) {
						$result[0]->compact_tree();						
						$need_sort = TRUE;
						krsort($result[1], SORT_NUMERIC);
						reset($result[1]);
					}
		
					// Set the tree sorting setting as previous group and force sorting
					if (!isset($sort) && isset($_SESSION["sort_by"]) && $need_sort) {
						$sort = $_SESSION["sort_by"];
						$_SESSION["sort_by"] = -1;
					}
				
					$_SESSION["result"] = $result;
				} else {
					$_SESSION["article_list"] = array();
					$_SESSION["result"] = array(new MessageTreeNode(NULL), array());
				}
			}
		}
		// Quit sooner to release resources
		$nntp->quit();
	}

// Control panel display section
	if (isset($_SESSION["result"])) {
		$root_node =& $_SESSION["result"][0];
		$ref_list =& $_SESSION["result"][1];
		
		if (!isset($_SESSION["sort_by"])) {
			$_SESSION["sort_by"] = 2;
			$last_sort = -1;
			$_SESSION["sort_asc"] = 0;
			$last_sort_dir = 0;
		} else {
			$last_sort = $_SESSION["sort_by"];
			$last_sort_dir = $_SESSION["sort_asc"];
			if (isset($sort)) {				
				$_SESSION["sort_by"] = intval($sort);
				if ($_SESSION["sort_by"] == $last_sort) {
					$_SESSION["sort_asc"] = ($_SESSION["sort_asc"] == 1)?0:1;
				}
			} else {
				$_SESSION["sort_by"] = $last_sort;
			}
		}
			
		if (($_SESSION["sort_by"] != $last_sort) || ($_SESSION["sort_asc"] != $last_sort_dir)){
			$root_node->deep_sort_message($sort_by_list[$_SESSION["sort_by"]], $_SESSION["sort_asc"]);
		}
		
		if (isset($sign) && isset($mid)) {
			$message_id = $ref_list[$mid][0];
			$references = $ref_list[$mid][1];
			$node =& $root_node;
			
			// Search the reference list only when the expand node is not a child of the root
			if (!$node->get_child($message_id)) {	
				if (sizeof($references) != 0) {
					foreach ($references as $ref) {
						$child =& $node->get_child($ref);
						if ($child != NULL) {
							$node =& $child;
						}
					}
				}
			}

			$node =& $node->get_child($message_id);

			if (isset($node)&&$node) {
				if (strcasecmp($sign, "minus") == 0) {
					$node->set_show_children(FALSE);
				} else if (strcasecmp($sign, "plus") == 0) {
					$node->set_show_all_children(TRUE);
				}
			}
		}
		
		if (isset($_SESSION["search_txt"])) {
			if (sizeof($root_node->get_children()) == 0) {
				$info_msg["msg"] = $messages_ini["text"]["sch_notfound"]." - ".$_SESSION["search_txt"];
			} else {
				$info_msg["msg"] = $messages_ini["text"]["sch_found1"]." ".sizeof($root_node->get_children())." ".$messages_ini["text"]["sch_found2"]." - ".$_SESSION["search_txt"].".";
			}
		}
?>

<form action="newsgroups.php">
<font face="<?php echo $font_family; ?>">

<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td nowrap="true" width="1%">
			<font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["search"]; ?>:&nbsp;</b></font>
		</td>
		<td nowrap="true" align="left">
			<input type="text" size="40" name="search_txt" style="<?php echo $form_style; ?>" value="<?php echo isset($_SESSION["search_txt"])?$_SESSION["search_txt"]:""; ?>">
			<input type="submit" name="search" value="<?php echo $messages_ini["control"]["search"]; ?>" style="<?php echo $form_style_bold; ?>">
<?php
/*
			if ($_SESSION["sch_option"]) {
				echo "<font size=\"($font_size - 1)\"><a href=\"newsgroups.php?sch_option=1\">Hide Search Options</a></font>";
			} else {
				echo "<font size=\"($font_size - 1)\"><a href=\"newsgroups.php?sch_option=1\">Search Options</a></font>";
			}
*/
?>
		</td>		
		<td width="100%">
			&nbsp;
		</td>
		<td align="right" valign="top" rowspan="2">
			<img src="<?php echo $image_base."webnews.gif"; ?>" border="0" width="40" height="40">
		</td>
		<td align="right" valign="top" nowrap="true" rowspan="2"><font size="-2">
			Web-News v.1.6.3<br>by <a href="http://web-news.sourceforge.net/webnews.html" target="new">Terence Yim</a></font></br>
			<a href="?logout=true">logout</a>
		</td>
	</tr>
	<tr>
		<td nowrap="true" width="1%">
			<font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["newsgroup"]; ?>:&nbsp;</b></font>
		</td>
		<td nowrap="true" align="left">
			<select name="group" style="<?php echo $form_style_bold; ?>">
				<?php
					while (list($key, $value) = each($newsgroups_list)) {
						echo "<option value=\"$value\"";
						if (strcmp($value, $_SESSION["newsgroup"]) == 0) {
							echo " selected";
						}
						echo ">$value\r\n";
					}
					reset($newsgroups_list);
				?>
			</select>
			<input type="submit" value="<?php echo $messages_ini["control"]["go"]; ?>" style="<?php echo $form_style_bold; ?>"> <a href="?portal"><font size="<?php echo $font_size; ?>">voir toute la liste</font></a>
		</td>
		<td width="100%">
			&nbsp;
		</td>
	</tr>
<?php
	if (isset($_SESSION["more_option"])&&$_SESSION["more_option"]) {
?>
	<tr>
		<td nowrap="true" width="1%">
			<font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["language"]; ?>:&nbsp;</b></font>
		</td>
		<td colspan="4" width="100%">
			<select name="language" style="$_SESSION["more_option"]<?php echo $form_style_bold; ?>">
<?php
			foreach ($locale_list as $key=>$value) {
				echo "<option value=\"$key\"";
				if (strcmp($_COOKIE["wn_pref_lang"], $key) == 0) {
					echo "selected";
				}
				echo ">";
				echo $value."\n";
			}
?>
			</select>
			&nbsp;
			<font size="<?php echo $font_size; ?>"><b><?php echo $messages_ini["text"]["messages_per_page"]; ?>:&nbsp;</b></font>
			<select name="msg_per_page" style="<?php echo $form_style_bold; ?>">
<?php
				foreach ($message_per_page_choice as $i) {
					echo "<option value=\"$i\"";
					if (strcmp($message_per_page, $i) == 0) {
						echo " selected";
					}
					if (strcmp($i, "all") == 0) {
						echo ">".$messages_ini["text"]["all"];
					} else {
						echo ">$i";
					}
				}
?>
			</select>
			<input type="submit" name="set" value="<?php echo $messages_ini["control"]["set"]; ?>" style="<?php echo $form_style_bold; ?>">
		</td>
	</tr>
<?php
	}
?>
	<tr>
		<td nowrap="true" colspan="2">
			<font size="<?php echo $font_size; ?>">
<?php
				if (isset($_SESSION["search_txt"])) {
					echo "<a href=\"newsgroups.php?renew=1\" title=\"".$messages_ini["help"]["return"]."\">".$messages_ini["control"]["return"]."</a>";
				} else {
					echo "<a href=\"newsgroups.php?renew=1\" title=\"".$messages_ini["help"]["new_news"]."\">".$messages_ini["control"]["new_news"]."</a>";
				}
?>
				|
				<a href="newsgroups.php?compose=1" title="<?php echo $messages_ini["help"]["compose"]; ?>"><?php echo $messages_ini["control"]["compose"]; ?></a>
				|
				<a href="newsgroups.php?expand=1" title="<?php echo $messages_ini["help"]["expand"]; ?>"><?php echo $messages_ini["control"]["expand"]; ?></a>
				|
				<a href="newsgroups.php?collapse=1" title="<?php echo $messages_ini["help"]["collapse"]; ?>"><?php echo $messages_ini["control"]["collapse"]; ?></a>
				|
				<a href="newsgroups.php?sawall=1" title="<?php echo $messages_ini["help"]["sawall"]; ?>"><?php echo $messages_ini["control"]["sawall"]; ?></a>
				|
				<a href="newsgroups.php?rss_feed=<?php echo $message_per_page; ?>&group=<?php echo urlencode($_SESSION["newsgroup"]); ?>" target="_blank" title="<?php echo $messages_ini["help"]["rss_feed"]; ?>">
					<?php echo $messages_ini["control"]["rss_feed"]; ?></a>
				|
				<a href="newsgroups.php?option=1" 
<?php
				if (isset($_SESSION["more_option"])&&$_SESSION["more_option"]) {
					echo "title=\"".$messages_ini["help"]["less_option"]."\">".$messages_ini["control"]["less_option"]."</a>";
				} else {
					echo "title=\"".$messages_ini["help"]["more_option"]."\">".$messages_ini["control"]["more_option"]."</a>";
				}
?>
			</font>
		</td>
		<td colspan="3" align="right">
<?php
	/*if (($auth_level > 1) && $_SESSION["auth"]) {
?>
			<b><font size="<?php echo $font_size; ?>"><?php echo $messages_ini["text"]["login"].$user; ?>.</font></b>
			<input type="submit" name="logout" value="<?php echo $messages_ini["control"]["logout"]; ?>" style="<?php echo $form_style_bold; ?>">
<?php
	} else {*/
		echo "&nbsp;";		
	//~ }
?>
		</td>
	</tr>
<?php
	if (isset($info_msg["msg"])&&strlen($info_msg["msg"]) != 0) {
		echo "<tr><td colspan=\"5\" align=\"center\" colspan=\"5\">";
		echo "<b><font size=\"".$font_size."\"";
		if (array_key_exists("color", $info_msg)) {
			echo "color=\"#".$info_msg["color"]."\"";
		}
		echo ">";
		echo $info_msg["msg"];
		echo "</b></font></td></tr>";
	}
?>
</table>

<?php // Begin tree display section ?>
<table cellpadding="0" cellspacing="1" border="0" width="100%">
<tr bgcolor="<?php echo $primary_color; ?>">
	<?php
		if ($_SESSION["sort_asc"]) {
			$arrow_img = $image_base."sort_arrow_up.gif";
			$arrow_alt = '▲';
		} else {
			$arrow_img = $image_base."sort_arrow_down.gif";
			$arrow_alt = '▼';
		}

		echo "<td width=\"65%\"><font size=\"$font_size\" nowrap=\"true\"><b>";
		echo "<a href=\"newsgroups.php?renew=0&sort=0\">".$messages_ini["text"]["subject"]."</a>";
		if ($_SESSION["sort_by"] == 0) {
			echo "&nbsp;<img src=\"$arrow_img\" border=\"0\" align=\"absbottom\" alt=\"$arrow_alt\">";
		}
		echo "</b></font></td>";

		echo "<td width=\"23%\"><font size=\"$font_size\" nowrap=\"true\"><b>";
		echo "<a href=\"newsgroups.php?renew=0&sort=1\">".$messages_ini["text"]["sender"]."</a>";
		if ($_SESSION["sort_by"] == 1) {
			echo "&nbsp;<img src=\"$arrow_img\" border=\"0\" align=\"absbottom\" alt=\"$arrow_alt\">";
		}
		echo "</b></font></td>";

		echo "<td width=\"12%\"><font size=\"$font_size\" nowrap=\"true\"><b>";
		echo "<a href=\"newsgroups.php?renew=0&sort=2\">".$messages_ini["text"]["date"]."</a>";
		if ($_SESSION["sort_by"] == 2) {
			echo "&nbsp;<img src=\"$arrow_img\" border=\"0\" align=\"absbottom\" alt=\"$arrow_alt\">";
		}
		echo "</b></font></td>";
	?>
</tr>
<tr>
	<td colspan="3"><font size="<?php echo ($font_size-1); ?>">&nbsp;</font></td>
</tr>

<?php
		if (is_requested("expand")) {
			$_SESSION["expand_all"] = TRUE;
			$need_expand = TRUE;
		} elseif (is_requested("collapse")) {
			$_SESSION["expand_all"] = FALSE;
			$need_expand = TRUE;
		} elseif ($renew) {
			$need_expand = TRUE;
			if (!isset($_SESSION["expand_all"])) {
				$_SESSION["expand_all"] = $default_expanded;
			}
		}
		if(is_requested("sawall")){
			$_SESSION["sawall"]=true;
		}else{
			$_SESSION["sawall"]=false;
		}
			//~ print_r($root_node->get_children());

		if (isset($need_expand)&&$need_expand) {
			$root_node->set_show_all_children($_SESSION["expand_all"]);
			$root_node->set_show_children(TRUE);
		}

		$display_counter = 0;
		if (isset($_SESSION["search_txt"]) && (strcasecmp($message_per_page, "all") != 0)) {
			$nodes = array_slice($root_node->get_children(), ($page - 1)*$message_per_page, $message_per_page);
			display_tree($nodes, 0);
		} else {
			display_tree($root_node->get_children(), 0);
		}
	}

// Pagination number generation

	if (strcasecmp($message_per_page, "all") != 0) {
		if (isset($_SESSION["search_txt"])) {		// Count from the number of search results
			$page_count = ceil((float)sizeof($root_node->get_children())/(float)$message_per_page);
		} else {
			$page_count = ceil((float)sizeof($_SESSION["article_list"])/(float)$message_per_page);
		}
		$start_page = (ceil($page/$pages_per_page) - 1)*$pages_per_page + 1;
		$end_page = $start_page + $pages_per_page - 1;
		if ($end_page > $page_count) {
			$end_page = $page_count;
		}
	} else {	// Show All
		$page_count = 0;
	}
	if (($page_count != 0) && (($start_page != 1) || ($start_page != $end_page))) {
?>
		<tr bgcolor="#<?php echo $tertiary_color; ?>">
			<td colspan="3">&nbsp;</td>
		</tr>

		<tr bgcolor="#<?php echo $tertiary_color; ?>">
			<td colspan="4" align="center">
				<font size="<?php echo $font_size; ?>">
					<b><?php echo $messages_ini["text"]["page"]; ?></b>
	
	<?php		
		if ($page != 1) {
			echo "<a href=\"newsgroups.php?page=".($page - 1)."\"><img src=\"".$image_base."previous_arrow.gif\" align=\"absmiddle\" border=\"0\" alt=\"◀\"></a>";
		}
		echo "&nbsp;";

		for ($i = $start_page;$i <= $end_page;$i++) {
			if ($page == $i) {
				echo $i;
			} else {
				echo "<a href=\"newsgroups.php?page=$i\">$i</a>";
			}
			echo "&nbsp;";
		}
		
		if ($page != $page_count) {
			echo "<a href=\"newsgroups.php?page=".($page + 1)."\"><img src=\"".$image_base."next_arrow.gif\" align=\"absmiddle\" border=\"0\" alt=\"▶\"></a>";
		}
	
		echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	
	
		if ($start_page != 1) {
				echo "<b><a href=\"newsgroups.php?page=".($start_page - 1)."\">".$messages_ini["text"]["previous"]."$pages_per_page".(isset($messages_ini["text"]["page_quality"])?$messages_ini["text"]["page_quality"]:'')."</a></b>&nbsp;&nbsp;";
		}
		if ($end_page != $page_count) {
			echo "<b><a href=\"newsgroups.php?page=".($end_page + 1)."\">".$messages_ini["text"]["next"]."$pages_per_page".(isset($messages_ini["text"]["page_quality"])?$messages_ini["text"]["page_quality"]:'')."</a></b>\r\n";
		}
	?>
			</font></td>
		</tr>
<?php
	}
?>

</table>
</form>
</font>
