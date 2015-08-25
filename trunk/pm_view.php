<?php
require_once('./include/common.php');
require_once('./include/txt_func.php');
SetNoUseCache();

if( !$udata['is_member'] ) ErrorBox($str['act_noguest']);

if( !isset($page) ) $page = 1;
$page = intval( $page );

//消息总数
//$sql = "SELECT COUNT(*) FROM $cfg[tb_pms] WHERE comefrom='$udata[u_name]' OR sendto='$udata[u_name]'";
$sql = "SELECT COUNT(*) FROM $cfg[tb_pms] WHERE sendto='$udata[u_name]'";
$result = RenDB_Query($sql);
$row = RenDB_Fetch_Row( $result );
$pmcount=$row[0];

$outbox = isset($outbox)?intval($outbox):0;
$link_inbox = $outbox ? "□<a href=\"pm_view.php?page={$page}\">收件箱</a>": HLTxt('■收件箱');
$link_outbox= $outbox ? HLTxt('■发件箱'):"□<a href=\"pm_view.php?page={$page}&outbox=1\">发件箱</a>";
$box_type = $outbox ? 'out':'in';
$cond = $outbox ? " WHERE comefrom='$udata[u_name]' " :" WHERE sendto='$udata[u_name]' ";


$sql = "SELECT COUNT(*) FROM $cfg[tb_pms] $cond";
$result = RenDB_Query($sql);
$row = RenDB_Fetch_Row( $result );
$pageinfo = MakePageBar( "pm_view.php?outbox=$outbox", $row[0], $cfg['pmperpage'], $page );


$sql ="SELECT * FROM $cfg[tb_pms] $cond ORDER BY pmid DESC LIMIT $pageinfo[start],$cfg[pmperpage]";
$result = RenDB_Query($sql);

$pmlist='';
$pmnum=0;
if( RenDB_Num_Rows($result) > 0  )
{
	$pm_cell = LoadTemplate('pm_cell');
	//$endid = 0;
	while( $pmdata = RenDB_Fetch_Array( $result ) )
	{
		$pmdate = TimeToDate( $pmdata['sendtime']);

		//是收件
		$pmnum ++;
		if( $pmnum == $cfg['maxpm'] )
			$endid = $pmdata['pmid'];
		if( $pmnum > $cfg['maxpm'] &&  !$pmdata['isnew'] ) continue;

		$pmnew = $pmdata['isnew'] ? HLTxt('*') : '';

		//$ignore='';
		$reply=$outbox?'&nbsp;':MakeBBButton( "pm_new.php?&action=new1&sendto=$pmdata[comefrom]", '回复', 550,250);

/*
		$ignore= ( !In_Names( $pmdata['comefrom'], $udata['blacklist'] )) ?
			MakeBBButton( "m_man.php?&action=add&name={$pmdata['comefrom']}", '拒收') : '' ;
*/
		
		if($pmdata['u_stand']==4)
		{
			$pmfrom = $outbox? 'To:'.MemberLink( $pmdata['sendto']):'';
			$pmfrom .=' ('.HLTxt('系统消息').')';
			$message= BBCoding($pmdata['message'],true);
		}
		else 
		{
			$pmfrom = $outbox? 'To:'.MemberLink( $pmdata['sendto']):'From:'.MemberLink( $pmdata['comefrom']);
			$message=BBCoding($pmdata['message'],false);
		}

		//if( $pmnum > $cfg['maxpm']  )
		//	$message = "<del>$message</del>";

		eval( "\$pmlist .= \"$pm_cell \";");
	}

/*
	if( $pmnum > $cfg['maxpm'] )
	{
		$sql ="DELETE FROM {$cfg['tb_pms']} WHERE sendto='{$udata['u_name']}' AND pmid<'{$endid}'";
		RenDB_Query($sql,true) ;
	}*/
}

if( $pmnum==0 ) $pmlist="<tr bgcolor=\"$color[cell]\"><td>(空)</td></tr>";

$pmnew=MakeBBButton('pm_new.php?&action=new1', '发消息', 550,250);

if( $udata['have_new_pm'] )
{
	$sql ="UPDATE {$cfg['tb_pms']} SET `isnew` = 0 WHERE `sendto`='{$udata['u_name']}'";
	RenDB_Query($sql,true);

    $sql ="UPDATE {$cfg['tb_members']} SET have_new_pm=0 WHERE u_name='$udata[u_name]' LIMIT 1";
	RenDB_Query($sql,true);

	$udata['have_new_pm'] = 0;
}

ShowHeader('<img src="./images/pm.gif" /> 我的消息');
eval ("echo \"".LoadTemplate("pm_view")."\";");
ShowFooter();

?>
