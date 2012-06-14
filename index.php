<?php
//~ header("Location:newsgroups.php");
//~ require("webnews/nntp.php");
require("config/webnews.cfg.php");
require("webnews/util.php");
require_once("webnews/login.php");
//~ include("webnews/show_header.php");

session_name($session_name);
session_start();

if(is_loged()){
	header("Location: newsgroups.php");
	exit;
}

?>


<html>
<head>
	<style type="text/css">
		A {text-decoration: none; }
		A:hover {text-decoration: underline; color: FF0000;}
	</style>
	<title>Web-News v.1.6.3</title>
        <style type="text/css">
                a {color: #0044B3;}
                a:visited {color: #0044B3;}
        </style>
</head>
<body bgcolor="#ffffff" text="#000000" topmargin="0" leftmargin="0" rightmargin="0" 
        bottommargin="0" marginwidth="0" marginheight="0" 
        style="background: #fff url(<?php echo $image_base ?>fond.png) no-repeat center center;background-attachment: fixed; min-height: 500px;"
>

<table cellpadding="0" border="0" align="center" width="95%">
	<tr bgcolor="C1DFFA">
		<th>
			<font face="Tahoma, Sans-Serif" size="+1">Welcome to Web-News			<br></font>
			<font face="Tahoma, Sans-Serif" size="-1">A Web-based News Reader</font>
		</th>
	</tr>
	<tr>
		<td>

<div style="font-family: <?php echo $font_family; ?>">
<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td nowrap="nowrap" width="1%">
			
		</td>
		<td nowrap="nowrap" align="left">
		<?php
			dbconn();
			if(isset($_GET['confirm'])){
				if(validate_mail($_GET['confirm'])){
					echo '<font color="green">Email validé avec succé, vous pouvez vous connecter</font></br>';
				}else{
					echo 'Lien invalide : vous avec peut être déjà validé votre mail ?</br>';
				}
			}
			if(isset($_GET['inscription'])){
			//~ print_r($_POST);
			if(isset($_POST['mail'])&&isset($_POST['pass'])&&isset($_POST['pass2'])&&isset($_POST['nom'])){
				$_POST['mail']=trim($_POST['mail']);
				$_POST['nom']=trim($_POST['nom']);
				if(validate_email($_POST['mail'])){
					if(strlen($_POST['pass'])>5){
						if($_POST['pass']==$_POST['pass2']){
							$query=mysql_query("SELECT * FROM users WHERE mail='".mysql_real_escape_string($_POST['mail'])."'");
							if(mysql_num_rows($query)>0){
								echo '<font color="red">Mail déjà utilisé</font></br>';
							}else{
								$token=sha1($_POST['mail'].time());
								mysql_query("INSERT INTO users (nom,mail,pass,inscription,url) VALUES ('".mysql_real_escape_string($_POST['nom'])."','".mysql_real_escape_string($_POST['mail'])."','".sha1crypt($_POST['pass'])."','".time()."','".$token."')");
								inscription_mail($_POST['mail'],$token,$_POST['nom']);
								echo '<font color="green">Inscription effectuée avec succes, un mail de confirmation a été envoyé à l\'adresse '.$_POST['mail'].'. </font><a href="./">Retour<a></br>';
								$valid=true;
							}
						}else{
							echo '<font color="red">Les mots de passe ne concordent pas</font></br>';
						}
					
					}else{
						echo '<font color="red">Mot de passe trop court</font></br>';
					}
				}else{
					echo '<font color="red">Email invalide&nbsp;: doit être un email ';
					for($i=0;isset($restrict_mail_domain[$i]);$i++){
						echo '@'.$restrict_mail_domain[$i];
						if(isset($restrict_mail_domain[$i+1])){echo ' ou ';}
					}
					echo '</font></br>';
				
				}
			}
			if(!isset($valid)||!$valid){
			?>
		<form action="index.php?inscription" method="post">
		<table style="white-space:nowrap;">
			<tr>
			<td>Email: </td><td><input type="text"  name="mail" style="font-family: Tahoma, Sans-Serif; font-size: 75%;font-weight: bold" value="<?php echo isset($_POST['mail'])?$_POST['mail']:''; ?>"></td><td>(Un mail de confirmation sera envoyé)</td>
			</tr>
			<tr>
			<td>Nom: </td><td><input type="text" name="nom" value="<?php echo isset($_POST['nom'])?$_POST['nom']:''; ?>" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td><td>(nom à afficher pour l'expéditeur)</td>
			</tr>
			<tr>
			<td>Pass: </td><td><input type="password" name="pass" value="<?php echo isset($_POST['pass'])?$_POST['pass']:''; ?>" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td><td></td>
			</tr>
			<tr>
			<td>Pass 2:</td><td><input type="password" name="pass2" value="<?php echo isset($_POST['pass2'])?$_POST['pass2']:''; ?>" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td><td> (confirmation) </td>
			</tr>
			<tr>
			<td colspan="3"><input type="Submit" name="Envoyer" value="Envoyer" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
			
		</table>
		</form>
			<?php }}
			else{?>
		<form action="newsgroups.php" method="post">
		<table>
			<tr>
			<td>Email: </td><td><input type="text" size="40" name="mail" style="font-family: Tahoma, Sans-Serif; font-size: 75%" value=""></td>
			</tr>
			<tr>
			<td>Pass: </td><td><input type="password" name="pass" value="" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
			<tr>
			<td colspan="2"><input type="Submit" name="Login" value="Login" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
			<tr>
			<td colspan="2"><a href="?inscription">Inscription</a></td>
			</tr>
		</table>
		</form>
		<?php }?>
		</td>		
		<td width="100%">
			&nbsp;
		</td>
		<td align="right" valign="top" rowspan="2">
			<img src="images/webnews/webnews.gif" border="0" width="40" height="40">
		</td>
		<td align="right" valign="top" nowrap="nowrap" rowspan="2"><font size="-2">
			Web-News v.1.6.3<br>by <a href="http://web-news.sourceforge.net/webnews.html" target="new">Terence Yim</a></font>
		</td>
	</tr>
	
	
</table>
</div>		</td>
	</tr>
</table>
</body>
</html>

