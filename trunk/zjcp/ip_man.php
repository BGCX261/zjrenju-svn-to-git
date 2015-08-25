<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

if(!isset($action))$action='show';
if(!in_array($action,array('show','unban','ban')))
	ACP_MessageBox($str['act_err']);

switch($action)
{
case 'show':

	$ban_ips='';
	$sql="SELECT * FROM `$cfg[tb_banips]` ORDER BY ban_time DESC";
	$result=RenDB_Query($sql);
	while($row=RenDB_Fetch_Array($result))
	{
		$row['ban_time']=TimeToDate($row['ban_time'],true);
		$ban_ips.="<option value=\"$row[ban_ip]\">$row[ban_time] | $row[ban_ip] | $row[ban_reason]</option>";
	}

	if($ban_ips!='') $ban_ips="<select style=\"width: 90%\" name=\"ban_ip\">$ban_ips</select> <input type=\"submit\" value=\"解禁\" >";
	else $ban_ips='目前没有IP被屏蔽';

	ACP_ShowHeader('屏蔽ＩＰ');
	eval ('echo "'.LoadTemplate('ip_man').'";');
	ACP_ShowFooter();
	break;
case 'ban':
	if(!isset($ban_ip,$ban_reason)) ACP_MessageBox( $str['act_err']);
	$ban_ip=trim($ban_ip);
	if ( !eregi( '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.(\*|[0-9]{1,3})$', $ban_ip ) )
		ACP_MessageBox( 'IP格式不正确');

	$mycell=explode('.',$userip);
	//检查是不是自己的IP
	$cell=explode('.',$ban_ip);
	if($cell[3]=='*')
	{
		if($mycell[0]==$cell[0] && $mycell[1]==$cell[1] && $mycell[2]==$cell[2])
			ACP_MessageBox('不能屏蔽自己的IP');
	}
	else if($ban_ip==$userip) ACP_MessageBox('不能屏蔽自己的IP');
	
	require_once('../include/txt_func.php');
	$ban_reason=CSubStr($ban_reason,0,40);

	$sql="SELECT COUNT(*) FROM `$cfg[tb_banips]` WHERE ban_ip='$ban_ip'";
	$result=RenDB_Query($sql);
	if($row=RenDB_Fetch_Row($result))
		if($row[0]) ACP_MessageBox( $ban_ip.' 已经在屏蔽列表中');
	

	$sql="INSERT INTO `$cfg[tb_banips]` SET ban_ip='$ban_ip',ban_time='$nowtime', ban_reason='$ban_reason'";
	RenDB_Query($sql);
	ACP_WriteLog("屏蔽IP $ban_ip ");
	Header("Location: index.php?mode=ip_man");
	exit();

	break;
case 'unban':
	if(!isset($ban_ip)) ACP_MessageBox( $str['act_err']);
	$sql="DELETE FROM `$cfg[tb_banips]` WHERE ban_ip='$ban_ip'";
	RenDB_Query($sql,true);
	ACP_WriteLog("解禁IP $ban_ip ");
	Header("Location: index.php?mode=ip_man");
	exit();
	
	break;
}
?>