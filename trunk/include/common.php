<?php

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
function GetMicrotime()
{
	 $t=explode(' ', microtime());
	 return intval(($t[1]+$t[0])*1000);
}

$startTime = GetMicrotime();
//
define('IN_RBB', true);
set_magic_quotes_runtime(0);
//var_dump($name);

//注册变量
if ( !get_cfg_var('register_globals') || !get_magic_quotes_gpc() )
{
	if ( is_array( $HTTP_POST_VARS ) )
		foreach( $HTTP_POST_VARS as $key=>$value )
			if(!is_array($value))$$key=addslashes($value);
	if ( is_array( $HTTP_GET_VARS ) )
		foreach($HTTP_GET_VARS as $key=>$value )
			if(!is_array($value))$$key=addslashes($value);
}

//echo "<PRE>";
//print_r($GLOBALS);
//print_r($log_name);
//echo "</PRE>";

//初始化特殊变量
$cfg=array();
$boards=array();
$str=array();
$color=array();
//载入全局数据缓存
require_once('./cache/global.php');

//压缩页面

if($cfg['gzip_ouput']&&!defined('RBB_NO_GZIP'))
	ob_start('ob_gzhandler');
else ob_start();

//MessageBox('dsaffffff');


//语言设置
$langs=array('zh'=>'简体中文','en'=>'English');
if( isset($lang_pack))
{
	setcookie( 'cook_lang', $lang_pack, time()+2592000 );
}
elseif( isset($_COOKIE["cook_lang"]))
{
	$lang_pack = $_COOKIE["cook_lang"];
}
else $lang_pack='';

if( !array_key_exists( $lang_pack, $langs ) ||!file_exists("./lang/{$lang_pack}/lang.php") )
{
	$lang_pack = array_keys($langs);
	$lang_pack=$lang_pack[0];
}

require_once("./lang/{$lang_pack}/lang.php");


//检查是否临时关闭
if($cfg['closebb']!='')exit($cfg['closebb']);

//取得ip

if  (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))    
{  
	$userip  =  $_SERVER["HTTP_X_FORWARDED_FOR"];  
}  
elseif  (isset($_SERVER["HTTP_CLIENT_IP"]))    
{  
	$userip  =  $_SERVER["HTTP_CLIENT_IP"];  
}  
else    
{  
	$userip  =  $_SERVER["REMOTE_ADDR"];  
}            

//检查banip
$cell=explode('.',$userip);
unset($cell[3]);
if(in_array($userip,$RBB_CACHE['banip_S']) || in_array(implode('.',$cell),$RBB_CACHE['banip_D']))
{header("HTTP/1.1 403");exit();}


require_once('./config.php');//设置
require_once('./include/functions.php');//基本函数
//require_once('./cache/ranking.php');//积分线

if($cfg['debug_mode'])//调试模式
	require_once('./include/debug_mode.php');

//连接数据库
RenDB_Connect($cfg['pconnect']);

//全局变量
$fakename='@'.$userip;
$nowtime=time();
$o_deadline=$nowtime-$cfg['span_online'];
if(isset($udata))unset($udata); //必须保留

if(isset($_COOKIE['cook_name'],$_COOKIE['cook_pass']))
{
	//从cookies登陆
	$cook_name=addslashes($_COOKIE['cook_name']);
	$cook_pass=addslashes($_COOKIE['cook_pass']);
	//$sql="SELECT * FROM $cfg[tb_members] WHERE u_name='$cook_name' AND u_pass='$cook_pass' LIMIT 1";
	$sql="SELECT m.*,o.* FROM $cfg[tb_members] m LEFT JOIN $cfg[tb_onlines] o ON o.fake_name=m.u_name AND o.o_time>=$o_deadline WHERE m.u_name='$cook_name' AND m.u_pass='$cook_pass' LIMIT 1";
	$result=RenDB_Query($sql);
	if(RenDB_Num_Rows($result))
	{
		$udata=RenDB_Fetch_Array($result);
		if($udata['u_id']>0)
		{
			$udata['is_member']=true;
		}
        else unset($udata);

	}
	if( empty($udata))//过客
	{	            
		WriteBBLog("密码错误  IP:$userip 用户名:'$cook_name'",'wrongpass');
		setcookie('cook_name','',$nowtime-360000);
		setcookie('cook_pass','',$nowtime-360000);
	}
}

if(empty($udata))
{
	//初始化过客数据
	$udata=array(
		'u_id'=>0,
		'u_name'=>'',
		'is_member'=>false,
		'fake_name'=>$fakename,
		'o_time'=>0,
		'act_check'=>0,
		);

	//检查在线列表
	$sql="SELECT * FROM $cfg[tb_onlines] WHERE fake_name='$udata[fake_name]' AND o_time>='$o_deadline' LIMIT 1";
	$result=RenDB_Query($sql);
	if(RenDB_Num_Rows($result))
	{
		$odata=RenDB_Fetch_Array($result);
		$udata['o_time']=$odata['o_time'];
		$udata['u_stand']=$odata['u_stand'];
		//$udata['view_page']=$odata['view_page'];
		$udata['last_action']=$odata['last_action'];
	}
}

//检查banacc
if($udata['is_member'] && ($udata['ban_before']==-1||$udata['ban_before']>$nowtime))
{
	setcookie('cook_name','',$nowtime-360000);	
	setcookie('cook_pass','',$nowtime-360000);
	MessageBox($str['acc_banned']);
	//if($udata['ban_before']==-1) MessageBox('您的账号已被冻结');
	//MessageBox('您的账号被冻结到 '.TimeToDate($udata['ban_before']));
}
UpdateOnline();

/*
//这段代码粘到common.php末尾，每运行一次自动生成1000个测试用账号
//用完记得删除
$prefix=sprintf('%X',time()%100000000);
for($i=0;$i<1000;$i++)
{
	$sql ="INSERT INTO $cfg[tb_members] SET ";
	$sql.="u_name		='{$prefix}_$i',";
	$sql.="u_avatar		='default.gif',";
	$sql.="u_pass		='".md5(rand())."',";
	$sql.="reg_pass		='".md5(rand())."',";
	$sql.="reg_ip		='$userip',";
	$sql.="reg_date		='".rand(100000000,1065720964)."',";
	$sql.="last_visit	='".rand(100000000,1065720964)."',";
	$sql.="credit	='".rand(0,10000)."',";
	$sql.="posts	='".rand(0,300)."',";
	$sql.="skill	='".rand(0,1000)."',";
	$sql.="g_w	='".rand(0,1000)."',";
	$sql.="g_d	='".rand(0,1000)."',";
	$sql.="g_l	='".rand(0,1000)."',";
	$sql.="g_to	='".rand(0,1000)."',";
	$sql.="have_new_pm	='1',";
	$sql.="act_check	='".md5(rand())."'";
	RenDB_Query($sql);
}
*/


/*
//这段代码粘到common.php末尾，每运行一次自动生成5个"在线用户"
//用完记得删除
$prefix=sprintf('%X',time()%100000);
for($i=0;$i<50;$i++)
{
	$sql ="INSERT INTO `$cfg[tb_onlines]` SET ";
	$sql.="fake_name		='{$prefix}_$i',";
	$sql.="u_stand		='".rand(0,3)."',";
	$sql.="o_time		='".time()."'";
	RenDB_Query($sql);
}
*/
?>
