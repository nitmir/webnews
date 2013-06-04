<?php
require("config/webnews.cfg.php");
require_once 'CAS.php';
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

	function init_cas($use_cas=null, $cas=null){
		global $CAS;
		if($use_cas===null){$use_cas=isset($_SESSION['use_cas'])&&isset($_SESSION['cas'])&&$_SESSION['use_cas'];}
		if($use_cas&&$cas===null){$cas=$_SESSION['cas'];}
		if($use_cas){
			phpCAS::setDebug();
			phpCAS::client(SAML_VERSION_1_1, $CAS[$cas]['host'], $CAS[$cas]['port'], $CAS[$cas]['context']);
			phpCAS::setCasServerCACert($CAS[$cas]['root_cert']);
			phpCAS::handleLogoutRequests(true, array($CAS[$cas]['host']));
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
Pour confirmer votre inscription et pouvoir vous connecter, merci de suivre le lien suivant : 
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
	    return $salt.'$'.base64_encode(sha1($salt.'$'.$password,true));
	}
	//~ echo  sha1crypt('salsa')."\n";
	function validpass($pass,$passhash){
		$salt=substr($passhash,0,13);
		$calc=($salt.sha1($salt.$pass));
		$calc2=($salt.base64_encode(sha1($salt.$pass,true)));
		if($calc==$passhash||$calc2==$passhash){
			return true;
		}else{
			return false;
		}
	}
	
	function login($mail,$pass,$use_cas=false){
		global $delete_account_after;
		if (is_loged()){
			return true;
		}
        if($use_cas){
            init_cas();
            phpCAS::forceAuthentication();
            foreach (phpCAS::getAttributes() as $key => $value) {
                $_SESSION[$key]=$value;
            }
            $where="mail='".phpCAS::getUser()."@".$_SESSION['cas']."' OR mail='".$_SESSION['mail']."'";
            if(array_key_exists('mailAlias', $_SESSION)){
                if (is_array($_SESSION['mailAlias'])) {
                    foreach ($_SESSION['mailAlias'] as $mail){
                        $where.=" OR mail='".$mail."'";
                    }
                }else{
                    $where.=" OR mail='".$_SESSION['mailAlias']."'";
                }
            }
            
        }else{
            $where="mail='".mysql_real_escape_string($mail)."' AND valid='oui'";
        }
		$query=mysql_query("SELECT * FROM users WHERE ".$where." ORDER BY mail")or die(mysql_error());
		if(mysql_num_rows($query)<1){
            if($use_cas&&phpCAS::checkAuthentication()){
              mysql_query("INSERT INTO users (nom,mail,pass,inscription,url,valid) VALUES ('".mysql_real_escape_string($_SESSION['cn'])."','".mysql_real_escape_string(phpCAS::getUser()."@".$_SESSION['cas'])."','!','".time()."','','oui')")or die(mysql_error());  
              $query=mysql_query("SELECT * FROM users WHERE ".$where." ORDER BY mail")or die(mysql_error());
            }else{
                return false;
            }
		}
        $data=mysql_fetch_assoc($query);
        if(validpass($pass,$data['pass'])||($use_cas&&phpCAS::checkAuthentication())){
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
	
	function logout(){
		$_SESSION['auth']=false;
        if(isset($_SESSION['use_cas'])&&$_SESSION['use_cas']){
            $use_cas=true;
            init_cas();
        }
		foreach($_SESSION as $key => $value){
			unset($_SESSION[$key]);
		}
        if($use_cas){
             phpCAS::logout();
        }
            

	function LogoutRequest(){
		if(isset($_POST['logoutRequest'])){
			$cas=implode('.',array_slice(explode('.', gethostbyaddr($_SERVER['REMOTE_ADDR'])),1));
			init_cas(true, $cas);
		}
	}
	
?>
