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
					echo '<font color="green">Email validé avec succès, vous pouvez maintenant vous connecter.</font></br>';
				}else{
					echo 'Lien invalide : vous avez peut être déjà validé votre mail ?</br>';
				}
			}
			else{?>
        <?php if( isset($_GET['invalid'] ) ) { ?>
            <p style="color: red; font-weight: bold;">Identifiants incorrects</p>
        <?php } //fin invalide ?>
		<form action="newsgroups.php" method="post">
		<table>
            <tr>
            <td colspan="2">
                Se connecter avec son compte : 
                <ul>
                <?php
                foreach($CAS as $key => $value){
                echo '<li><a href="newsgroups.php?cas='.$key.'">'.$key.'</a></li>';
                }
                ?>
                </ul>
            </td>
            </tr>
            <tr>
            <td colspan="2" style="white-space:nowrap;">Ou utilisez l'ancienne authentification si vous aviez créé un compte : </td>
            </tr>
            <tr>
			<td>Email: </td><td><input type="text" size="40" name="mail" style="font-family: Tahoma, Sans-Serif; font-size: 75%" value=""></td>
			</tr>
			<tr>
			<td>Pass: </td><td><input type="password" name="pass" value="" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
			<tr>
			<td colspan="2"><input type="Submit" name="Login" value="Login" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
		</table>
		</form>
		<?php }?>
		</td>		
		<td width="100%">
			&nbsp;
		</td>
		<td align="right" valign="top" rowspan="2">
			<img src="images/webnews/webnews.gif" border="0" width="40" height="40" alt="">
		</td>
		<td align="right" valign="top" nowrap="nowrap" rowspan="2"><font size="-2">
			Web-News v.1.6.3<br>by <a href="http://web-news.sourceforge.net/webnews.html" target="new">Terence Yim</a></font>
		</td>
	</tr>
	
	
</table>
</div>
