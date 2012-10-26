<?php
if(is_requested("return")){
	header("Location: ");
	die();
}
if(isset($_GET['success'])&&$_GET['success']==1){
	echo '<font color="green">'.$messages_ini["text"]["success_password"].'</font></br><a href="/">Se connecter</a>';
	die();
}

if($_POST['mdp_request'] == 'ok'){
	dbconn();
	$result = mysql_query("SELECT * FROM users WHERE mail='".mysql_real_escape_string($_POST['email'])."'") or exit(mysql_error());
	
	if($data = mysql_fetch_assoc($result)){		
		$secret=$x_webnews;
		$t=time();
		$ft=dechex($t);
		$uniquid=sha1($ft.$secret.$data['id'].$data['mail']);
		$lien = 'https://news.crans.org/?forget_password=1&key='.$uniquid.'&id='.$data['id'].'&ft='.$ft;
		
		//mail
		$to  =  $data['mail'];//. ', '; // note the comma

		// subject
		$subject = 'Réinitialisation de mot de passe du WebNews.';

		// message
		$message = '
		<html>
		<head>
		  <title>Récupération de mot de passe :</title>
		</head>
		<body>
			<p>Bonjour '.htmlspecialchars($data['nom']).',<br/>
			Tu as demandé une réinitialisation de ton mot de passe sur le webnews.<br/>
			Pour confirmer cette demande, suis ce lien : <a href="'.$lien.'">'.$lien.'</a> .<br/>
			À bientôt !<br/>
			<br/>
			(Ne réponds pas à ce mail directement, l\'adresse d\'expédition n\'est pas lue.)</p>
		</body>
		</html>
		';

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

		/* Additional headers
		$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";*/
		$headers .= 'From: WebNews <nobody@crans.org>' . "\r\n";
		/*$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
		$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";*/

		// Mail it
		mail($to, $subject, $message, $headers);	

?>
		<span style="color: green;">Lien envoyé par mail.</span><br/>
<?php
	}else{?>
	<span style="color: red;">Adresse email incorrecte.</span><br/>
		
<?php
	}
}else if(is_requested('key')&&is_requested('ft')&&is_requested('id')&&get_request('key') != '')
{
	$ft=get_request('ft');
	$id=get_request('id');
	$key=get_request('key');
	$t=hexdec($ft);
	$secret=$x_webnews;
	
	dbconn();
	$query=mysql_query("SELECT mail FROM users WHERE id=".intval($id)) or exit(mysql_error());
	$data=mysql_fetch_assoc($query);
	
	$hash=sha1($ft.$secret.$id.$data['mail']);

	if($hash == $key&&$t + $link_validity >= time())
	{
		if(is_requested("change_password_button")){
			if(strlen($_POST['new_password1'])>=$password_min_length){
				if($_POST['new_password1']==$_POST['new_password2']&&$id>0){
					mysql_query("UPDATE users SET pass='".sha1crypt($_POST['new_password1'])."' WHERE id='".intval($id)."'")or die(mysql_error());
					header("Location:index.php?forget_password=1&success=1");
					die();
				}else{
					echo '<font color="red">'.$messages_ini["text"]["password_nomatch"].'</font></br>';
				}

			}else{
				echo '<font color="red">'.$messages_ini["text"]["password_too_short"].'</font></br>';
			}
		}
			
?>
Choisir un nouveau mot de passe : 
<form action="index.php" method="post">
<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td>
			<table>
				<tr>
					<td>
						<input type="hidden" name="forget_password" value="1"/>
						<input type="hidden" name="key" value="<?php echo get_request('key')?>"/>
						<input type="hidden" name="ft" value="<?php echo get_request('ft')?>"/>
						<input type="hidden" name="id" value="<?php echo get_request('id')?>"/>
						<?php echo $messages_ini["text"]["new_password1"] ?></td><td><input name="new_password1" type="password" value=""></td>
				</tr>
				<tr>
					<td><?php echo $messages_ini["text"]["new_password2"] ?></td><td><input name="new_password2" type="password" value=""></td>
				</tr>
				<tr>
					<td align="right"><input type="submit" name="change_password_button" value="<?php echo $messages_ini["control"]["set"]; ?>" style="<?php echo $form_style_bold; ?>"></td>
					<td><input type="submit" name="return" value="retour" style="<?php echo $form_style_bold; ?>"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
	</div>
</center>
</body>
</html>
<?php
	exit();

	}else{
?>
		<span style="color: red;">Ce lien n'est plus valide...</span><br/>
		
<?php
	}
}
?><div style="font-family: <?php echo $font_family; ?>">
		Pour récupérer ton mot de passe, tape ton adresse email. </br>
		<form method="post" action="index.php">
			Mail : <input type="text" name="email" value=""/><br/>
			<input type="hidden" name="mdp_request" value="ok"/>
			<input type="hidden" name="forget_password" value="1"/>
			<input type="submit" value="Envoyer"/>
			<input type="submit" name="return" value="retour" style="<?php echo $form_style_bold; ?>"></td>
		</form>
	</div>
</div>