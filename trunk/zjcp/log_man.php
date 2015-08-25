<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

if(!isset($action)) $action='list';
if(!in_array($action,array('list','view')))
	ACP_MessageBox($str['act_err']);

$logfile=array(
	1=>'errors.php',
	2=>'wrongpass.php',
	3=>'syslog.php',
	5=>'adminlog.php'
	);

$logname=array(
	1=>'程序错误记录',
	2=>'密码错误记录',
	3=>'系统运行记录',
	5=>'后台管理记录'
	);

switch($action)
{
case 'list':
	ACP_ShowHeader('记录管理');
	eval ('echo "'.LoadTemplate('log_man').'";');
	ACP_ShowFooter();
	break;

case 'view':
	if(!isset($logid)||$logid<1||$logid>5) ACP_MessageBox($str['act_err']);
	$logid=intval($logid);

	if(($line=file('../log/'.$logfile[$logid]))!==FALSE)
	{
		unset($line[0]);
		$linecount=count($line);
		$line=implode('',$line);
		ACP_ShowHeader('查看记录');
		eval ('echo "'.LoadTemplate('log_view').'";');
		ACP_ShowFooter();
	}
	else ACP_MessageBox('无法打开文件');
	break;
}

?>