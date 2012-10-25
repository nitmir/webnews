<?php
if(is_requested("change_password_button")){
	$query=mysql_query("SELECT * FROM users WHERE id='".$_SESSION['id']."'")or die(mysql_error());
	$data=mysql_fetch_assoc($query);
	if(validpass($_POST['current_password'],$data['pass'])){
		if(strlen($_POST['new_password1'])>=$password_min_length){
			if($_POST['new_password1']==$_POST['new_password2']&&isset($_SESSION['id'])){
				mysql_query("UPDATE users SET pass='".sha1crypt($_POST['new_password1'])."' WHERE id='".$_SESSION['id']."'")or die(mysql_error());
				header("Location: newsgroups.php?change_password=1&success=1");
				die();
			}else{
				echo '<font color="red">'.$messages_ini["text"]["password_nomatch"].'</font></br>';
			}

		}else{
			echo '<font color="red">'.$messages_ini["text"]["password_too_short"].'</font></br>';
		}
	}else{
		echo '<font color="red">'.$messages_ini["text"]["bad_password"].'</font></br>';
	}
}
if(isset($_GET['success'])&&$_GET['success']==1){
	echo '<font color="green">'.$messages_ini["text"]["success_password"].'</font></br>';
}
?><div style="font-family: <?php echo $font_family; ?>">
<form action="newsgroups.php" method="post">
<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td>
			<table>
				<tr>
					<td><?php echo $messages_ini["text"]["current_password"] ?></td>
					<td>
						<input type="hidden" value="1" name="change_password">
						<input name="current_password" type="password" value="">
					</td>
				</tr>
				<tr>
					<td><?php echo $messages_ini["text"]["new_password1"] ?></td><td><input name="new_password1" type="password" value=""></td>
				</tr>
				<tr>
					<td><?php echo $messages_ini["text"]["new_password2"] ?></td><td><input name="new_password2" type="password" value=""></td>
				</tr>
				<tr>
					<td align="right"><input type="submit" name="change_password_button" value="<?php echo $messages_ini["control"]["set"]; ?>" style="<?php echo $form_style_bold; ?>"></td>
					<td><input type="button" value="<?php echo $messages_ini["control"]["return"]; ?>" style="<?php echo $form_style_bold; ?>" onclick="document.location.href='newsgroups.php';"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
</div>