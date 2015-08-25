<?php
require_once('./include/common.php');
SetNoUseCache();

if(!isset($group_id)) ErrorBox( $str['act_err'] );
$group_id=intval($group_id);

if(!isset($page) ) $page = 1;
$page=intval( $page );

$orderby= isset($orderby)?intval($orderby):0;

$lks=array();
$lks[0] = "<a href=\"group_view.php?group_id=$group_id&orderby=0\">Pts</a>";
$lks[1] = "<a href=\"group_view.php?group_id=$group_id&orderby=1\">Berg</a>";
$lks[2] = "<a href=\"group_view.php?group_id=$group_id&orderby=2\">PtsM</a>";
$lks[3] = "<a href=\"group_view.php?group_id=$group_id&orderby=3\">PtsE</a>";
$lks[4] = "<a href=\"group_view.php?group_id=$group_id&orderby=4\">Win</a>";
$lks[5] = "<a href=\"group_view.php?group_id=$group_id&orderby=5\">Draw</a>";
$lks[6] = "<a href=\"group_view.php?group_id=$group_id&orderby=6\">Lost</a>";
switch($orderby)
{
case 1:
	$order='berg DESC';
	$lks[1] =HLTxt('Berg');
	break;
case 2:
	$order='ptsm DESC';
	$lks[2] =HLTxt('PtsM');
	break;
case 3:
	$order='ptse DESC';
	$lks[3] =HLTxt('PtsE');
	break;
case 4:
	$order='g_w DESC';
	$lks[4] =HLTxt('Win');
	break;
case 5:
	$order='g_d DESC';
	$lks[5] =HLTxt('Draw');
	break;
case 6:
	$order='g_l DESC';
	$lks[6] =HLTxt('Lost');
	break;
default:
	$order='points DESC';
	$lks[0] =HLTxt('Pts');
	break;
}



$sql="SELECT * FROM `$cfg[tb_players]` WHERE group_id='$group_id' ORDER BY $order";
$result = RenDB_Query($sql);
$pllist='';
$plnum=0;
if( RenDB_Num_Rows($result) > 0  )
{
	$pl_cell=LoadTemplate('player_cell');
	while($pldata=RenDB_Fetch_Array( $result ) )
	{
		$plnum++;
		$u_name=MemberLink($pldata['u_name']);
		eval( "\$pllist.=\"$pl_cell\";");
	}
}
else $pllist  = "<tr bgcolor=\"$color[cell]\"><td clospan=\"5\">($str[empty])</td></tr>";


//echo sprintf ( "%01.3f" , GetMicrotime() - $mt0 );

ShowHeader($str['cp_view']);
eval ('echo "'.LoadTemplate('group_view').'";');
ShowFooter();

?>
