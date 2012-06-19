<?php
/*		
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

	ob_start();
?>
<!DOCTYPE HTML SYSTEM>
<html>
<head>
	<style type="text/css">
		a {text-decoration: none; color: #0044B3;}
		a:hover {text-decoration: underline; color: <?php echo $over_link_color; ?>;}
		a:visited {color: #0044B3;}
		a:visited:hover {text-decoration: underline; color: <?php echo $over_link_color; ?>;}
	</style>
	<title><?php echo $messages_ini["text"]["title"]?></title>
</head>
<body bgcolor="#ffffff" text="#000000" 
	<?php if (isset($on_load_script)) {echo "onLoad=\"$on_load_script\"";} ?>
	style="background: #fff url(<?php echo $image_base ?>fond.png) no-repeat center center;background-attachment: fixed; min-height: 413px;"
>
<table cellpadding="0" border="0" align="center" width="95%">
	<tr bgcolor="<?php echo $primary_color; ?>">
		<th>
			<font face="<?php echo $font_family; ?>" size="+1"><?php echo $messages_ini["text"]["header1"]; ?>
			<br></font>
			<font face="<?php echo $font_family; ?>" size="<?php echo $font_size; ?>"><?php echo $messages_ini["text"]["header2"]; ?></font>
		</th>
	</tr>
	<tr>
		<td>
<?php
		//~ echo $content_page;
		if(isset($nntp->error_message)&&$nntp->error_message!=''){
			echo '<center><font color="red">'.$nntp->error_message.'</font><center>';
		}
		include($content_page);
?>
		</td>
	</tr>
</table>
</body>
</html>

<?php
	ob_end_flush();
?>
