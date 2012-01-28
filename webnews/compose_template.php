<?
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/
?>
<?/*<style type='text/css'>
/*html, body {height:100%;}/* essentiel pour dimensionner en hauteur *//*
textarea        {
width:70% ;
height:260px;
margin:left; /* exemple pour centrer *//*
display:block;/* pour effectivement centrer ! *//*
}*//*
</style>*/?>
<font face="<? echo $font_family; ?>">
<?
	if (is_requested("group")) {
		$group = get_request("group");
	} else {
		unset($group);
	}

	if (is_requested("add_file")) {
		$_SESSION["attach_count"]++;
	}
	if (isset($_SESSION["attach_count"])) {
?>
<form action="newsgroups.php" method="post" enctype="multipart/form-data">
<?
	} else {
?>
<form action="newsgroups.php" method="post">
<?	}
?>
	<input type="hidden" name="compose" value="post">

	<table cellspacing="3" cellpadding="0" border="0" width="95%">
<?
	if (isset($error_messages)) {
		echo "<tr>";
		echo "<td colspan=\"2\"><font size=\"-1\" color=\"".$error_color."\"><b>";
		foreach ($error_messages as $msg) {
			echo "$msg<br>";
		}
		
		echo "</b></font><br></tr>";
		echo "</td>";
	}
?>
		<tr>
			<td width="12%"><font size="-1"><b><? echo $messages_ini["text"]["subject"]; ?>:</b></font></td>
			<td><input type="text" name="subject" size="60" value="<? echo utf8($subject); ?>" style="<? echo $form_style; ?>"></td>
		</tr>
		<tr>
			<td><font size="-1"><b><? echo $messages_ini["text"]["name"]; ?>:</b></font></td>
			<td><input type="text" name="name" size="60" value="<? echo $name; ?>" style="<? echo $form_style; ?>" readonly="readonly"></td>
		</tr>
		<tr>
			<td><font size="-1"><b><? echo $messages_ini["text"]["email"]; ?>:</b></font></td>
			<td><input type="text" name="email" size="60" value="<? echo $email; ?>" style="<? echo $form_style; ?>" readonly="readonly"></td>
		</tr>
		<tr>
<?
		echo "<td valign=\"top\" rowspan=\"";
		if (isset($_SESSION["attach_count"])) {
			echo ($_SESSION["attach_count"] + 1);
		} else {
			echo "1";
		}
		echo "\">";
?>		
		<font size="-1"><b><? echo $messages_ini["text"]["attachments"]; ?>:</b></font></td>
<?	
	if (isset($_SESSION["attach_count"])) {
		for ($i = 1;$i <= $_SESSION["attach_count"];$i++) {
			if ($i != 1) {
				echo "<tr>";
			}
?>
			<td><input type="file" name="file<? echo $i; ?>" size="32" style="<? echo $form_style; ?>"></td>
<?
			if ($i != 1) {
				echo "</tr>";
			}
		}
		echo "<tr>";
	}
?>
			<td>
				<input type="submit" name="add_file" value="<? echo $messages_ini["control"]["add_file"]; ?>" style="<? echo $form_style_bold; ?>">
			</td>
		</tr>
		<tr>
			<td valign="top"><font size="-1"><b><? echo $messages_ini["text"]["newsgroups"]; ?>:</b></font></td>
			<td>
				<?
					if ($allow_cross_post) {
						$count = 1;
						while (list($key, $value) = each($newsgroups_list)) {
							echo "<input name=\"groups[]\" type=\"checkbox\" value=\"$value\"";
							if (isset($groups)) {
								if (in_array($value, $groups)) {
									echo "checked";
								}
							} elseif (strcmp($value,$_SESSION["newsgroup"]) == 0) {
								echo " checked";
							}
							echo "><font face=\"$font_family\" size=\"-1\"><b>$value</b></font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							if (($count++ % 2) == 0) {
								echo "<br>";
							}
						}
						reset($newsgroups_list);
					} else {
						echo "<input name=\"groups[]\" type=\"radio\" value=\"".$_SESSION["newsgroup"]."\" checked>";
						echo "<font face=\"$font_family\" size=\"-1\"><b>".$_SESSION["newsgroup"]."</b></font>";
					}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><font size="-1"><b><? echo $messages_ini["text"]["message"]; ?>:</b></font></td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="message" rows="25" cols="100" wrap="virtual" style="<? echo $form_style; ?>"><? echo $message; ?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br>
				<?
					if (isset($reply_id)) {
						echo "<input type=\"hidden\" name=\"reply_id\" value=\"$reply_id\">";
					}

					if (isset($_SESSION["attach_count"])) {
						echo "<input type=\"hidden\" name=\"attachment\" value=\"1\">";
					}
				?>
				<input type="submit" name="post" value="<? echo $messages_ini["control"]["post"]; ?>" style="<? echo $form_style_bold; ?>">
				<input type="submit" name="cancel" value="<? echo $messages_ini["control"]["cancel"]; ?>" style="<? echo $form_style_bold; ?>">
			</td>
		</tr>
	</table>
</form>
</font>
