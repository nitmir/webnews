<?php
	echo '== <a href="?portal&unread=1">rafraichir le nombre de messages non lus</a> ==</br>';
	foreach($newsgroups_list as $group){
		if($_SESSION['unread'][$group]>0){echo '<b>';}
		echo '<a href="newsgroups.php?group='.$group.'">'.$group."</a>";
		if($_SESSION['unread'][$group]>0){echo '</b>';}
		echo ' ('.$_SESSION['unread'][$group];
		echo ")</br>\n";
		}
?>
