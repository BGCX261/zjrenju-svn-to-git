<?php

error_reporting(E_ALL);
set_magic_quotes_runtime(0);

if ( !get_cfg_var('register_globals') || !get_magic_quotes_gpc() )
{
	if ( is_array( $HTTP_POST_VARS ) )
		foreach( $HTTP_POST_VARS as $key=>$value )
			$$key=addslashes($value);
	if ( is_array( $HTTP_GET_VARS ) )
		foreach($HTTP_GET_VARS as $key=>$value )
			$$key=addslashes($value);
}

define('RBB_ADMINCP', true );

header("Content-Language: charset=zh-cn");
header("Content-type: text/html; charset=utf-8");

if(!file_exists('../config.php')) die('配置文件不存在，请先运行<a href="../install/">install</a>目录进行配置。');
if(file_exists('../install')) die('请先删除install目录。');

require_once('../cache/global.php');//全局数据缓存


//取得ip
$userip=getenv('HTTP_X_FORWARDED_FOR');
if ( $userip == '' || ip2long( $userip ) == -1 ) 
	$userip=getenv('REMOTE_ADDR');
if( $userip=='' ) $userip = '127.0.0.1';
else $userip=substr( $userip, 0, 15 );



//检查banip
$cell=explode('.',$userip);
unset($cell[3]);
if(in_array($userip,$RBB_CACHE['banip_S']) || in_array(implode('.',$cell),$RBB_CACHE['banip_D']))
{header("HTTP/1.1 403");exit();}


$nowtime=time();

//包含必要的文件头,一定要放在注册全局变量之后
require_once('../config.php');//设置
//require_once('../cache/ranking.php');//积分线
require_once('../cache/onlinerec.php');//在线纪录
require_once('../include/functions.php');//基本函数

require_once('./admin_special.php');

//连接数据库
RenDB_Connect();

if(isset($admin_log_name,$admin_log_pass))
{
	setcookie('admin_name',$admin_log_name);
	setcookie('admin_pass',$admin_log_pass);
	ACP_MessageBox("登陆成功");
	//header('Location: index.php');
	exit();
}

//登陆
$admin_logged=false;
if(isset($_COOKIE['admin_name'],$_COOKIE['admin_pass']))
{
	if( In_Names($_COOKIE['admin_name'],$cfg['admins']) )
	{
		$admin_name=$_COOKIE['admin_name'];
		$admin_pass=md5($_COOKIE['admin_pass']);
		$sql="SELECT * FROM `$cfg[tb_members]` WHERE u_name='$admin_name' AND u_pass='$admin_pass' LIMIT 1";
		$result=RenDB_Query($sql);
		if( RenDB_Num_Rows($result)==1)
		{
			$udata=RenDB_Fetch_Array($result);
			if( $udata['u_name']==$admin_name )
			{
				$admin_logged=true;
				$udata['fake_name']= $udata['u_name'];
			}
		}
	}

	if(!$admin_logged)
	{
		$udata['fake_name']='@'.$userip;
		ACP_WriteLog( "密码错误  用户名:'$_COOKIE[admin_name]'");
	}
}

if(!$admin_logged)
{
	setcookie('admin_name','',time()-36000);
	setcookie('admin_pass','',time()-36000);
	eval ('echo "'.LoadTemplate('login').'";');
	exit();
}


//检查模块是否存在
if( !isset( $mode ) ) $mode='main';

if( !in_array( $mode, $cfg_cp['allmodes']) ) 
	ACP_MessageBox( "模块{$mode}不存在" );
require_once ('./'.$mode.'.php');

?>