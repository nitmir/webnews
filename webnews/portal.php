<?php
	$display_counter=0;
	echo '<table><tr><td colspan="2" align="center">== <a href="?portal&unread=1"><small>rafraîchir</small></a> ==</td></tr>';
	foreach($newsgroups_list as $group){
		if (($display_counter % 2) == 0) {
                              echo "<tr bgcolor=\"#".$secondary_color."\">\r\n";
                } else {
                          echo "<tr>\r\n";
                }
		$display_counter++;
		echo '<td>';
		if($_SESSION['unread'][$group]>0){echo '<b>';}
		echo '<a href="newsgroups.php?group='.$group.'">'.$group."</a>";
		if($_SESSION['unread'][$group]>0){echo '</b>';}
		echo '</td><td>('.max(0,$_SESSION['unread'][$group]);
		echo ")</td></tr>\n";
		}
	echo '</table>';
?>
