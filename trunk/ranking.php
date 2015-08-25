<?php
require_once('./include/common.php');
SetNoUseCache();

//if(!$udata['is_member']) MessageBox($str["act_noguest"]);

if(!isset($page) )$page = 1;
$page = intval( $page );
if(!isset($orderby)) $orderby=0;

$lks=array();
$lks[0] = "<a href=\"ranking.php?page=$page&orderby=0\">积分</a>";
//$lks[1] = "<a href=\"ranking.php?page=$page&orderby=1\">论坛积分</a>";
$lks[2] = "<a href=\"ranking.php?page=$page&orderby=2\">对局数</a>";
$lks[3] = "<a href=\"ranking.php?page=$page&orderby=3\">注册时间</a>";
//$lks[4] = "<a href=\"ranking.php?page=$page&orderby=4\">贴子数</a>";
$lks[5] = "<a href=\"ranking.php?page=$page&orderby=5\">胜率</a>";
$lks[6] = "<a href=\"ranking.php?page=$page&orderby=6\">用户名</a>";
switch($orderby)
{
//case 1:
//	$order='credit DESC';
//	$lks[1] =HLTxt('论坛积分');
//	break;
case 2:
	$order='g_w+g_d+g_l DESC';
	$lks[2] =HLTxt('对局数');
	break;
case 3:
	$order='u_id ASC';
	$lks[3] =HLTxt('注册时间');
	break;
//case 4:
////	$order='posts DESC';
//	$lks[4] =HLTxt('贴子数');
//	break;
case 5:
	$order='IF(g_w+g_d+g_l=0,0,g_w*100/(g_w+g_d+g_l)) DESC';
	$lks[5] =HLTxt('胜率');
	break;
case 6:
	$order='u_name ASC';
	$lks[6] =HLTxt('用户名');
	break;
default:
	$order='g_w+g_d+g_l=0,skill DESC';
	$lks[0] =HLTxt('积分');
	break;
}

//上榜人数
$sql = "SELECT COUNT(*) FROM {$cfg['tb_members']}";
$result = RenDB_Query($sql);
$row = RenDB_Fetch_Row( $result );
$pageinfo =  MakePageBar( "ranking.php?orderby={$orderby}", $row[0], $cfg['mperpage'], $page );

$sql = "SELECT * FROM {$cfg['tb_members']} ORDER BY {$order} LIMIT {$pageinfo['start']},{$cfg['mperpage']}";

$result = RenDB_Query($sql);
$mlist = '';
$mnum = 0;

if( RenDB_Num_Rows($result) > 0  )
{
	$member_cell = LoadTemplate('m_cell');
	while( $mdata = RenDB_Fetch_Array( $result ) )
	{
		$morder= $pageinfo['start']+$mnum+1;
		$mname=MemberLink($mdata['u_name']);
		$mgrade=$mdata['skill'];
		$mrate=$mdata['g_w']+$mdata['g_d']+$mdata['g_l']==0 ? 0 :
			round($mdata['g_w']*100/($mdata['g_w']+$mdata['g_d']+$mdata['g_l']),1);
		$mrate.='%';
		$minfo="{$mdata['g_w']}胜{$mdata['g_d']}平{$mdata['g_l']}负";
		$mregtime=TimeToDate($mdata['reg_date']);

		eval( "\$mlist .= \"{$member_cell}\";");
		$mnum ++;
	}
}
else $glist  = "<tr><td colspan=\"7\">(空)</td></tr>";

ShowHeader('<img src="./images/ranking.gif" /> 用户排名');
eval ('echo "'.LoadTemplate("ranking").'";');
ShowFooter();

?>