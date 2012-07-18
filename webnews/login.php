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
	
	
	
	function validate_mail($token){
		global $delete_account_after;
		$time=time() - $delete_account_after;
                mysql_query("DELETE FROM users WHERE valid='non' AND inscription<".$time)or die(mysql_error());
		if($token==''){return false;}
		$query=mysql_query("SELECT * FROM users WHERE valid='non' AND url='".mysql_real_escape_string($token)."'");
		if(mysql_num_rows($query)<1){
			return false;
		}else{
			mysql_query("UPDATE users SET valid='oui', url='' WHERE url='".mysql_real_escape_string($token)."'")or die(mysql_error());
			return true;
		}
	}
	
	function inscription_mail($mail,$token,$nom){
		// Sujet
     $to  = $mail; // notez la virgule
     $subject = 'Inscription au webnews';

     // message
     $message = '
Pour confirmer cotre inscription et pouvoir vous connecter, merci de suivre le lien suivant : 
http'.(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on'?'s':'').'://'.$_SERVER['HTTP_HOST'].'/?confirm='.$token.'

NB: Les news du Cr@ns sont également accessibles via un lecteur externe
(par ex. Thunderbird) à l\'aide des identitifants suivants:
Utilisateur : Vivelapa
Mot de passe : ranoia!

-- 
Le Web-news
     ';

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
     $headers  = 'MIME-Version: 1.0' . "\r\n";
     $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";

     // En-têtes additionnels
     $headers .= 'To: '.$nom.' <'.$mail.'>' . "\r\n";
     $headers .= 'From: Web-news<nobody@crans.org>' . "\r\n";

     // Envoi
     mail($to, $subject, $message, $headers);
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
		global $delete_account_after;
		if (is_loged()){
			return true;
		}
		$query=mysql_query("SELECT * FROM users WHERE mail='".mysql_real_escape_string($mail)."' AND valid='oui'");
		if(mysql_num_rows($query)!=1){
			return false;
		}else{
			$data=mysql_fetch_assoc($query);
			if(validpass($pass,$data['pass'])){
				$_SESSION['auth']=true;
				$_SESSION['nom']=$data['nom'];
				$_SESSION['id']=$data['id'];
				$_SESSION['mail']=$data['mail'];
				mysql_query("UPDATE users SET last_login='".time()."' WHERE id='".$_SESSION['id']."'")or die(mysql_error());
				$time=time() - $delete_account_after;
				mysql_query("DELETE FROM users WHERE valid='non' AND inscription<".$time)or die(mysql_error());
				return true;
			}else{
				return false;
			}
		}
	}
	
	function logout(){
		$_SESSION['auth']=false;
		foreach($_SESSION as $key => $value){
			unset($_SESSION[$key]);
		}
	}
	
?>