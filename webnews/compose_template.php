<?php
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/
?>
<?php /*<style type='text/css'>
/*html, body {height:100%;}/* essentiel pour dimensionner en hauteur *//*
textarea        {
width:70% ;
height:260px;
margin:left; /* exemple pour centrer *//*
display:block;/* pour effectivement centrer ! *//*
}*//*
</style>*/?>
<div style="font-family: <?php echo $font_family; ?>">
<?php
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
<?php
	} else {
?>
<form action="newsgroups.php" method="post">
<?php	}
?>
	<input type="hidden" name="compose" value="post">

	<table cellspacing="3" cellpadding="0" border="0" width="95%">
<?php
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
			<td width="12%"><font size="-1"><b><?php echo $messages_ini["text"]["subject"]; ?>:</b></font></td>
			<td><input type="text" name="subject" size="60" value="<?php echo ($subject); ?>" style="<?php echo $form_style; ?>"></td>
		</tr>
		<tr>
			<td><font size="-1"><b><?php echo $messages_ini["text"]["name"]; ?>:</b></font></td>
			<td><input type="text" name="name" size="60" value="<?php echo $name; ?>" style="<?php echo $form_style; ?>" readonly="readonly"></td>
		</tr>
		<tr>
			<td><font size="-1"><b><?php echo $messages_ini["text"]["email"]; ?>:</b></font></td>
			<td><input type="text" name="email" size="60" value="<?php echo $email; ?>" style="<?php echo $form_style; ?>" readonly="readonly"></td>
		</tr>
		<tr>
<?php
		echo "<td valign=\"top\" rowspan=\"";
		if (isset($_SESSION["attach_count"])) {
			echo ($_SESSION["attach_count"] + 1);
		} else {
			echo "1";
		}
		echo "\">";
?>		
		<font size="-1"><b><?php echo $messages_ini["text"]["attachments"]; ?>:</b></font></td>
<?php	
	if (isset($_SESSION["attach_count"])) {
		for ($i = 1;$i <= $_SESSION["attach_count"];$i++) {
			if ($i != 1) {
				echo "<tr>";
			}
?>
			<td><input type="file" name="file<?php echo $i; ?>" size="32" style="<?php echo $form_style; ?>"></td>
<?php
			if ($i != 1) {
				echo "</tr>";
			}
		}
		echo "<tr>";
	}
?>
			<td>
				<input type="submit" name="add_file" value="<?php echo $messages_ini["control"]["add_file"]; ?>" style="<?php echo $form_style_bold; ?>">
			</td>
		</tr>
		<tr>
			<td valign="top"><font size="-1"><b><?php echo $messages_ini["text"]["newsgroups"]; ?>:</b></font></td>
			<td>
				<?php
					if(isset($header['followup-to'])){
						$to=$header['followup-to'];
					}else{
						$to=$_SESSION["newsgroup"];
					}
					if ($allow_cross_post) {
						$count = 1;
						echo '<table><tr>';
						while (list($key, $value) = each($newsgroups_list)) {
							echo "<td><input name=\"groups[]\" type=\"checkbox\" value=\"$value\"";
							if (isset($groups)) {
								if (in_array($value, $groups)) {
									echo "checked";
								}
							} elseif (strcmp($value,$to) == 0) {
								echo " checked";
							}
							echo "><font face=\"$font_family\" size=\"-1\"><b>$value</b></font></td>";
							if (($count++ % 2) == 0) {
								echo "</tr><tr>";
							}
						}
						echo '</tr></table>';
						reset($newsgroups_list);
					} else {
						echo "<input name=\"groups[]\" type=\"radio\" value=\"".$to."\" checked>";
						echo "<font face=\"$font_family\" size=\"-1\"><b>".$to."</b></font>";
					}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><font size="-1"><b><?php echo $messages_ini["text"]["message"]; ?>:</b></font></td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="message" rows="25" cols="100" wrap="virtual" style="<?php echo $form_style; ?>"><?php echo $message; ?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br>
				<?php
					if (isset($reply_id)) {
						echo "<input type=\"hidden\" name=\"reply_id\" value=\"$reply_id\">";
					}

					if (isset($_SESSION["attach_count"])) {
						echo "<input type=\"hidden\" name=\"attachment\" value=\"1\">";
					}
				?>
				<input type="submit" name="post" value="<?php echo $messages_ini["control"]["post"]; ?>" style="<?php echo $form_style_bold; ?>">
				<input type="submit" name="cancel" value="<?php echo $messages_ini["control"]["cancel"]; ?>" style="<?php echo $form_style_bold; ?>">
			</td>
		</tr>
	</table>
</form>
</div>
