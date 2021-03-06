<?php
/*
	Web-News v.1.6.3 NNTP<->WWW gateway
	
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/

/******************************************************************/
/*	SERVER SETTINGS                                               */
/*	This part configurate the server settings                     */
/******************************************************************/

	//Mysql Server setting
	//$mysql_host="127.0.0.1";
	//$mysql_user="news";
	//$mysql_pass="pass";
	//$mysql_db="news";
	//$x_webnews="secret" //ne pas modifier, est utilisé pour générer le hash du header X-Webnews
	include('mysql.conf.php'); //contient les même cinq champs que ci-dessus avec le bon mot de passe

	$link_validity = 1800; //durée durant laquelle un lien pour générer un nouveau mot de passe en valide
	// NNTP Server setting
	$nntp_server = "news.crans.org";
	$user = "Vivelapa";
	$pass = "ranoia!";
	$signature = '';
	$sawafter=3600*24*30*6; // 6 month, put 0 for never, déconseillé car empêche la purge de la base de donnée.
	$maxunread=500; // Nombre maximal de messages à rechercher pour les marquer non lu
	
	// Proxy Server settings. Set it to empty string for not using it
	$proxy_server = "";
	$proxy_port = "";
	$proxy_user = "";
	$proxy_pass = "";
	
	// Session name. Set it to a unique string that can represent your site.
	// Notice that no space is allowed in the name.
	$session_name = "news_crans";

	// List of subscribed newsgroups
	//~ $newsgroups_list = array("hku.cs.test", "hku.cc.test", "comp.lang.*", "tw.bbs.comp.linux");
        $newsgroups_list = array(
                                                "crans.general",
                                                "crans.crans",
                                                "crans.crans.annonces",
                                                "crans.tele",
                                                "crans.web-sympas",
                                                "crans.radio-ragots",
                                                "crans.petites-annonces",
                                                "crans.politique",
                                                "crans.culture",
                                                "crans.dino",
                                                "crans.sports",
                                                "crans.stages",
                                                "crans.informatique",
                                                "crans.informatique.*",
                                                "crans.ratp",
                                                "tac.dpt.*",
                                                "tac.bde",
						"tac.crous",
						"crans.cns",
						"crans.club.*",
                                                "crans.test",
						"tac.test"
						);
        $default_group = "crans.general";


	mb_internal_encoding("UTF-8"); // encodage des messages pour l'affichage et l'envoie, à priori, laisser UTF-8

/******************************************************************/
/*	SECURITY SETTINGS                                             */
/*	This part configurate the security settings                   */
/******************************************************************/
	// auth_level = 1  ------  No need to perform authentication
	// auth_level = 2  ------  Perform authentication only when posting message
	// auth_level = 3  ------  Perform authentication in any operation
	$auth_level = 2;
	
	// The URL of the page shown after user logout
	// It can be a relative or absolute address
	// If protocol other than HTTP or HTTPS is used, please use absolute path
	// You can also use the variable "$_SERVER['HTTP_HOST']" to extract the current host name
	// e.g. $logout_url = "ftp://".$_SERVER['HTTP_HOST']."/mypath";
	$logout_url = "";
	
	// Realm to be used in the user authetication
	$realm = "Web-News";
	
	$restrict_mail_domain=array("crans.org", "ens-cachan.fr", "crans.ens-cachan.fr"); //seul ces domaines sont acceptés pour les adresses mail à l'inscription, commenter pour désactiver
	$password_min_length = 6;
    
$phpcas_path = '../../source/';

///////////////////////////////////////
// Basic Config of the phpCAS client //
///////////////////////////////////////

// Full Hostname of your CAS Server
$CAS=array(
    'crans.org' => array( 
        'host' => 'cas.crans.org', 
        'context' => '/cas', 
        'port' => 443, 
        'root_cert' => '/etc/ssl/certs/cacert.org.pem'
    ),
    'ens-cachan.fr' => array( 
        'host' => 'cas.ens-cachan.fr', 
        'context' => '', 
        'port' => 443, 
        'root_cert' => '/etc/ssl/certs/UTN_USERFirst_Hardware_Root_CA.pem'
    ),
);


/******************************************************************/
/*	PAGE DISPLAY SETTINGS                                         */
/*	This part set the limit constants                             */
/******************************************************************/
	// Page splitting settings
	$message_per_page = 25;
	//~ $message_per_page_choice = array(25, 30, 50, 75, 100, 1000, "all");
	$message_per_page_choice = array(25, 30, 50, 75, 100, 1000);
	$pages_per_page = 20;

// 	Default language and time
	$text_ini = "config/messages_fr_fr.ini";
	setlocale (LC_TIME, 'fr_FR.utf8'); 

	$locale_list = array("fr_fr" => "Français",
				"en_us" => "English (US)",
				//"zh_tw" => "Chinese (Traditional)",
				//"zh_cn" => "Chinese (Simplified)",
				//"it_it" => "Italian"
			);
	$locale_time_list = array('fr_fr' => 'fr_FR.utf8',
					'en_us' =>'en_US.utf8' 
			);
	// Filter the javascript or jscript
	$filter_script = true;


	

/******************************************************************/
/*	DEFAULT/LIMIT VALUES SETTINGS                                 */
/*	This part set the the default values or limits                */
/******************************************************************/
	// TRUE if the message tree is all expanded when first loaded, FALSE otherwise
	$default_expanded = TRUE;
	
	// TRUE if posting across several subscribed newsgroups is allowed
	$allow_cross_post = FALSE;

	// Upload file size limit
	$upload_file_limit = 10*1048576;	//10M

	// The length limit for the subject and sender
	$subject_length_limit = 100;
	$sender_length_limit = 20;

	// Path to the images
	$image_base = "images/webnews/";	
	
	// Number of messages to search through for showing threads in read article
	// If set to <= 0, no threads would be show
	// The larger the number, the more complete would be the thread tree, but takes longer time to load
	$thread_search_size = 500;      // Actual search window size would be $thread_search_size*2 + 1

	$delete_account_after=24*3600; //on supprime les comptes non validé après 24h
	
/******************************************************************/
/*	COLOUR AND FONT SETTINGS                                  */
/*	This part set the colour scheme and the font style        */
/******************************************************************/
	// Notice that the background color, text, link, active link and visited link color are controlled 
	// in the <BODY> tag of template.php. They are not set in here
	$today_color = "ff0000";			// Colour of the date display if the date is today
	$week_color = "00aa00";				// Colour of the date display if the date is within a week
	$error_color= "ff0000";				// Colour of the error messages

	// Primary colour is the deepest colour and tertiary colour is the lightest colour
	$primary_color = "C1DFFA";
	$secondary_color = "EAF6FF";
	$tertiary_color = "FFFFFF";

	// The color of the link text when mouse hover.
	$over_link_color = "FF0000";
	
	$font_family = "Tahoma, Sans-Serif";
	$font_size = "3 ";
	$form_style = "font-family: ".$font_family."; font-size: 75%";
	$form_style_bold = $form_style."; font-weight: bold";

	$quote_colors=array('#729fcf','#ad7fa8','#8ae234','#ff5842','#fcaf3e','#e9b96e','#f8e5c6');

/******************************************************************/
/*	TEMPLATE SETTINGS                                             */
/******************************************************************/
	// The template script should contain at least 3 statement as:
	//
	// ob_start();
	// include($content_page);
	// ob_end_flush();
	//
	// If you want to support autoscroll, please also include the following in the BODY tag
	//
	// if (isset($on_load_script)) {
	//		echo "onLoad=\"$on_load_script\"";
	//	}
	$template = "template.php";
	
		$template_list = array(
		'default' => 'template.php',
		'list' => 'template_list.php',
		);

//	template2.php includes a fancy welcome header
//	$template = "template2.php";

?>
