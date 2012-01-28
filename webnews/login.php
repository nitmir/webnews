<?php
	function dbconn(){
	    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

	    if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass))
	    {
		  switch (mysql_errno())
		  {
			case 1040:
			case 2002:
				if ($_SERVER['REQUEST_METHOD'] == "GET")
					die(header('HTTP/1.1 503 Service Unavailable')."<html><head><meta http-equiv=refresh content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table border=0 width=100% height=100%><tr><td><h3 align=center>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
				else
					die(header('HTTP/1.1 503 Service Unavailable')."Too many users. Please press the Refresh button in your browser to retry.");
		default:
		    die(header('HTTP/1.1 503 Service Unavailable')."[" . mysql_errno() . "] dbconn: mysql_connect: " . mysql_error());
	      }
	    }
		//~ echo 'coucou';
	    mysql_select_db($mysql_db)
		or die(header('HTTP/1.1 503 Service Unavailable').'dbconn: mysql_select_db: ' + mysql_error());
	}
	
	
	function is_loged(){
		if(isset($_SESSION['auth'])&&$_SESSION['auth']==true){
			return true;
		}else{
			return false;
		}
	}
	
	function sha1crypt($password){
	    // create a salt that ensures crypt creates an sha1 hash
	    $base64_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ'
			    .'abcdefghijklmnopqrstuvwxyz0123456789+/';
	    $salt='$2$';
	    for($i=0; $i<9; $i++){
		$salt.=$base64_alphabet[rand(0,63)];
	    }
	    // return the crypt sha1 password
	    return $salt.'$'.sha1($salt.'$'.$password);
	}
	//~ echo  sha1crypt('salsa')."\n";
	function validpass($pass,$passhash){
		$salt=substr($passhash,0,13);
		$calc=($salt.sha1($salt.$pass));
		if($calc==$passhash){
			return true;
		}else{
			return false;
		}
	}
	
	function login($mail,$pass){
		if (is_loged()){
			return true;
		}
		$query=mysql_query("SELECT * FROM users WHERE mail='".mysql_real_escape_string($mail)."'");
		if(mysql_num_rows($query)!=1){
			echo 'coocu';
			return false;
		}else{
			$data=mysql_fetch_assoc($query);
			if(validpass($pass,$data['pass'])){
				echo 'couocu';
				$_SESSION['auth']=true;
				$_SESSION['nom']=$data['nom'];
				$_SESSION['id']=$data['id'];
				$_SESSION['mail']=$data['mail'];
				return true;
			}else{
				return false;
			}
		}
	}
	
	function logout(){
		unset($_SESSION);
	}
	
?>