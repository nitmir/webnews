<?php
//~ header("Location:newsgroups.php");
//~ require("webnews/nntp.php");
require("config/webnews.cfg.php");
//~ require_once("webnews/login.php");
//~ include("webnews/show_header.php");

session_name($session_name);
session_start();

?>


<html>
<head>
	<style type="text/css">
		A {text-decoration: none; }
		A:hover {text-decoration: underline; color: FF0000;}
	</style>
	<title>Web-News v.1.6.3</title>
</head>
<body bgcolor="#ffffff" text="#000000" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0" >
<table cellpadding="0" border="0" align="center" width="95%">
	<tr bgcolor="C1DFFA">
		<th>
			<font face="Tahoma, Sans-Serif" size="+1">Welcome to Web-News			<br></font>
			<font face="Tahoma, Sans-Serif" size="-1">A Web-based News Reader</font>
		</th>
	</tr>
	<tr>
		<td>

<form action="newsgroups.php" method="post">
<font face="Tahoma, Sans-Serif">

<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td nowrap="true" width="1%">
			
		</td>
		<td nowrap="true" align="left">
		<table>
			<tr>
			<td>Email: </td><td><input type="text" size="40" name="mail" style="font-family: Tahoma, Sans-Serif; font-size: 75%" value=""></td>
			</tr>
			<tr>
			<td>Pass: </td><td><input type="password" name="pass" value="" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
			</tr>
			<tr>
			<td colspan="2"><input type="Submit" name="Login" value="Login" style="font-family: Tahoma, Sans-Serif; font-size: 75%; font-weight: bold"></td>
		</table>
		</td>		
		<td width="100%">
			&nbsp;
		</td>
		<td align="right" valign="top" rowspan="2">
			<img src="images/webnews/webnews.gif" border="0" width="40" height="40">
		</td>
		<td align="right" valign="top" nowrap="true" rowspan="2"><font size="-2">
			Web-News v.1.6.3<br>by <a href="http://web-news.sourceforge.net/webnews.html" target="new">Terence Yim</a></font>
		</td>
	</tr>
	
	
</table>

</form>
</font>
		</td>
	</tr>
</table>
</body>
</html>

