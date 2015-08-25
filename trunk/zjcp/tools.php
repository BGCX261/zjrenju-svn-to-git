<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

if(!isset($action)) $action='list';
if(!in_array($action,array('list','rebuild_cache','optimize'
	,'pm_batch_del')))
		ACP_MessageBox($str['act_err']);

switch($action)
{
case 'list':
	ACP_ShowHeader('实用工具');
	eval ('echo "'.LoadTemplate('tools').'";');
	ACP_ShowFooter();
	break;
case 'rebuild_cache';
	require_once('../include/cache_func.php');
	Rebuild_Global_Cache(true);
	ACP_WriteLog( '刷新系统缓存');
	ACP_MessageBox('系统缓存刷新完毕');
	break;
case 'optimize';
	$sql ="OPTIMIZE TABLE $cfg[tb_members],$cfg[tb_pms],$cfg[tb_newgames],$cfg[tb_games],$cfg[tb_onlines],$cfg[tb_banips],$cfg[tb_settings],$cfg[tb_chats],$cfg[tb_competitions],$cfg[tb_groups],$cfg[tb_players]";
	RenDB_Query($sql,true);
	ACP_WriteLog( '整理数据库');
	ACP_MessageBox("数据库整理完毕");
	break;

case 'pm_batch_del':
	if(!isset($dateline)) ACP_MessageBox( $str['act_err'] );
	$dateline2=time()-$dateline*86400;
	
	$sql="DELETE FROM `$cfg[tb_pms]` WHERE sendtime<'$dateline2'";
	if(isset($keepnewpm))
		$sql.=" AND isnew=0";
	RenDB_Query($sql);
	if(RenDB_Affected_Rows())
	{
		ACP_MessageBox('共删掉了'.RenDB_Affected_Rows().'条短消息');
	}
	ACP_MessageBox('没有符合条件的短消息');
	break;
}
?>