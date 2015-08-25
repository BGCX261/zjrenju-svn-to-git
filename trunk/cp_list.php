<?php
require_once('./include/common.php');
SetNoUseCache();

if( !isset($page) ) $page = 1;
$page=intval( $page );

$sql = "SELECT COUNT(*) FROM `$cfg[tb_competitions]`";
$result = RenDB_Query($sql);
$row = RenDB_Fetch_Row( $result );
$pageinfo =  MakePageBar( "cp_list.php?", $row[0], $cfg['gperpage'], $page );


$sql="SELECT * FROM `$cfg[tb_competitions]` ORDER BY cp_id DESC LIMIT {$pageinfo['start']},{$cfg['gperpage']}";

$result = RenDB_Query($sql);
$cplist='';
if( RenDB_Num_Rows($result) > 0  )
{
	while($cpdata=RenDB_Fetch_Array( $result ) )
	{
		$cplist.="<tr bgcolor=\"$color[cell]\"><td>";
		if(!file_exists("./cpdata/$cpdata[cp_id].html"))
			$cplist.=$cpdata['cp_name'];
		else $cplist.="<a  href=\"cp_view.php?cp_id=$cpdata[cp_id]\">$cpdata[cp_name]</a>";

		$cplist.=' '.TimeToDate($cpdata['starttime']);
			
		if($cpdata['endtime'])
			$cplist.=' - '.TimeToDate($cpdata['endtime']);


		$cplist.='<hr />';
		$cplist.=$cpdata['description'];
		$cplist.="</td></tr>";

	}
}
else $cplist  = "<tr bgcolor=\"$color[cell]\"><td>(空)</td></tr>";


//echo sprintf ( "%01.3f" , GetMicrotime() - $mt0 );

ShowHeader('<img src="./images/renju.gif" /> '.$str['cp_view']);
eval ('echo "'.LoadTemplate('cp_list').'";');
ShowFooter();

?>
