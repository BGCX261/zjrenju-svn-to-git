<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');

$cfg_cp['allmodes']=array('main','all_set','log_man','ip_man',
	'logout','m_man', 'tools','cp_man');

$cfg['dateformat']='y-m-d H:i';
$str['act_err']='无法识别的操作';
$str['act_fail']='抱歉，这个操作没有成功';



function ACP_ShowHeader($location,$refresh='')
{
    global $cfg,$udata,$color;
	$autorefresh=$refresh==''?'':"<meta http-equiv=\"refresh\" content=\"3;url={$refresh}\">";

	$menus ='□ <a href="index.php?mode=main">面版首页</a><br />';
	$menus .='<hr />';
	$menus .='□ <a href="index.php?mode=all_set">参数设置</a><br />';
	$menus .='<hr />';
	$menus .='□ <a href="index.php?mode=cp_man">比赛设置</a><br />';
	$menus .='□ <a href="index.php?mode=m_man">账号管理</a><br />';
	$menus .='□ <a href="index.php?mode=ip_man">屏蔽ＩＰ</a><br />';
	$menus .='<hr />';
	$menus .='□ <a href="index.php?mode=log_man">记录管理</a><br />';
	$menus .='<hr />';
	$menus .='□ <a href="index.php?mode=tools">实用工具</a><br />';
	$menus .='<hr />';
	$logout_link='[<a href="index.php?mode=logout">退出</a>]';
	
	eval ( 'echo "'.LoadTemplate('header').'";');
}

function ACP_ShowFooter()
{
    global $color,$cfg;
	eval ( 'echo "'.LoadTemplate('footer').'";');
	exit();
}


function ACP_MessageBox( $message, $links=array())
{
	//SetNoUseCache();
	global $cfg,$color;
	$urls=array();
	if(count($links)==0)
		$urls[0] = array('返回','JavaScript:history.back(-1)');
	else
	{
		foreach( $links as $lnk )
		{
			$u="index.php?mode=$lnk[1]";
			$flag=false;
			foreach( $lnk as $k => $v )
			{
				if(!is_string($k))continue;
				if($flag) $u .="&$k=$v";
				else
				{
					$u .="?$k=$v";
					$flag=true;
				}
			}
			$urls[] = array( $lnk[0], $u);
		}
	}
	
	$fixurl = '';
	foreach( $urls as $url )
		$fixurl .= "[<a href=\"$url[1]\">$url[0]</a>]<br />";

	//$helplink = "[<a href=\"index.php\" >帮助</a>]";
	$helplink = '';

	ACP_ShowHeader('提示信息',$urls[0][1]);
	eval ( 'echo "'.LoadTemplate('msgbox').'";');
	ACP_ShowFooter();
}

function ACP_WriteLog($log)
{
	WriteBBLog($log,'adminlog',true);
}
?>