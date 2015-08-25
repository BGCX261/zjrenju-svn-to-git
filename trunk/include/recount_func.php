<?
function ReCountTopic($tid)
{
	global $cfg;
	$sql = "SELECT COUNT(*) FROM $cfg[tb_posts] WHERE tid='$tid'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row($result);
	$sql = "UPDATE $cfg[tb_topics] SET posts='$row[0]' WHERE tid='$tid' LIMIT 1";
	RenDB_Query($sql,true);
}

function ReCountForum($fid)
{
	global $cfg;
	$sql = "SELECT COUNT(*) FROM $cfg[tb_topics] WHERE fid='$fid'";
	$result = RenDB_Query($sql);
	if($row = RenDB_Fetch_Row($result))
		$topics=$row[0];
	else $topics=0;

	$sql = "SELECT COUNT(*) FROM $cfg[tb_posts] WHERE fid='$fid'";
	$result = RenDB_Query($sql);
	if($row = RenDB_Fetch_Row($result))
		$posts=$row[0];
	else $posts=0;

	$sql = "SELECT author,postdate FROM $cfg[tb_posts] WHERE fid='$fid' ORDER BY pid DESC LIMIT 1";
	$result = RenDB_Query($sql);
	if($row = RenDB_Fetch_Array($result))
	{
		$lastpost=$row['postdate'];
		$lastposter=$row['author'];
	}
	else
	{
		$lastpost=0;
		$lastposter='';
	}

	$sql = "UPDATE $cfg[tb_forums] SET topics='$topics',posts='$posts',lastpost='$lastpost',lastposter='$lastposter'  WHERE  fid='$fid' LIMIT 1";
	RenDB_Query($sql,true);
}

?>