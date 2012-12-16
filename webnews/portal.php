<?php
foreach($newsgroups_list as $group){
	if(!isset($_SESSION['unread'][$group])||is_requested("unread")){
		if(!isset($saw)){
			$saw=array();
			$query=mysql_query("SELECT * FROM post WHERE user_id='".$_SESSION["id"]."'");
			while($data=mysql_fetch_assoc($query)){
				$saw[$data['group']][$data['group_id']]=true;
			}
		}
		$nntp->connect();
		$info=$nntp->join_group($group);
		if($info['end_id']<= $_SESSION['unread_id'][$group]){
			if($_SESSION['unread'][$group]==0&&$_SESSION['unread_id'][$group]!=$_SESSION['read_all_id'][$group]){
				saw_all($group);
			}
			continue;
		}
		$range=max(0,$info['end_id'] - $maxunread,isset($_SESSION['unread_id'][$group])?$_SESSION['unread_id'][$group]:0).'-';
		if(!$array=$nntp->get_article_list($group,$range)){
			continue;
		}
		$size=count($array);
		$_SESSION['unread_id'][$group]=$info['end_id'];
		$i=0;
		for($j=$size -1;isset($array[$j]);$j--){
			if(isset($_SESSION['read_all_id'][$group])&&$array[$j]<=$_SESSION['read_all_id'][$group]){
				break;
			}
			if(!isset($saw[$group][$array[$j]])){
				$i++;
			}
		}
		if(isset($_SESSION['unread'][$group])&&$_SESSION['unread'][$group]>0){
			$_SESSION['unread'][$group]+=$i;
			$_SESSION['renews'][$group]=true;
		}else{
			$_SESSION['unread'][$group]=$i;
			$_SESSION['renews'][$group]=true;
		}
		if($_SESSION['unread'][$group]==0&&$_SESSION['unread_id'][$group]!=$_SESSION['read_all_id'][$group]){
			saw_all($group);
		}
	}
}
if(is_requested("unread")){
	$url=preg_replace('/(\?|&)unread=1/','',$_SERVER['REQUEST_URI']);
	header("Location: ".construct_url($url));
	die();
}

if(is_requested("sawall_allgroups")){
	$url=preg_replace('/(\?|&)sawall_allgroups=1/','',$_SERVER['REQUEST_URI']);
	array_map('saw_all',$newsgroups_list);
	header("Location: ".construct_url($url));
	die();
}

	$display_counter=0;
	$get="";
	$i=0;
	if(count($_GET)>0){
		foreach($_GET as $key => $value){
			$get.=($i==0?'?':'&').$key.'='.$value;
			$i++;
		}
	}
	echo '<table><tr><td colspan="2" align="center">== <a href="'.$get.($i==0?'?':'&').'unread=1'.'" title="'.$messages_ini["help"]["refresh"].'"><small>'.$messages_ini["control"]["refresh"].'</small></a> ==</td></tr>';
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
	echo '<tr><td colspan="2" align="center">== <a href="'.$get.($i==0?'?':'&').'sawall_allgroups=1'.'" title="'.$messages_ini["help"]["sawall"].'"><small>'.$messages_ini["control"]["sawall"].'</small></a> ==</td></tr></table>';
?>
