<?php
require_once('./include/common.php');

if( !isset( $_GET['action'] ) || !in_array( $_GET['action'], array('show','login','logout') ) )
	ErrorBox( $str['act_err'] );


switch($_GET['action'])
{
case 'show':
	ShowHeader("<img src=\"./images/stan0.gif\" /> 用户登陆");
	eval ('echo "'.LoadTemplate('login_form').'";');
	ShowFooter();
	break;
case 'login':
	if( !isset($_POST['log_name'],$_POST['log_pass'])) ErrorBox($str['act_err']);
	$log_pass = $_POST['log_pass'];
	$log_name=trim($_POST['log_name']);

	//删除旧的在线记录
	$sql ="DELETE FROM $cfg[tb_onlines] WHERE o_time<=$o_deadline OR o_time>'".time()."'";
	RenDB_Query($sql,true);

	$row['u_id']=0;
	$sql="SELECT u_id,ban_before FROM $cfg[tb_members] WHERE u_name='$log_name' AND u_pass=MD5('$log_pass') LIMIT 1";
	$result=RenDB_Query($sql);
	if(RenDB_Num_Rows($result))
	{
		$row=RenDB_Fetch_Array($result);
	}

	if(!$row['u_id'])
	{
		WriteBBLog("密码错误 IP:$userip 用户名:'$log_name'",'wrongpass');
		MessageBox('账号或密码无效');
	}

	if($row['ban_before']>$nowtime)
	{
		MessageBox('你的账号被冻结到 '.TimeToDate($row['ban_before']));
	}

	//删除online
	$sql ="DELETE FROM $cfg[tb_onlines] WHERE fake_name='$udata[fake_name]'";
	RenDB_Query($sql,true);

	//更新lastlogin和actcheck
	$actcheck=md5(uniqid(rand()));
	$sql="UPDATE $cfg[tb_members] SET last_visit='$nowtime', act_check='$actcheck' WHERE u_name='$log_name' LIMIT 1";
	RenDB_Query($sql,true);

	//更新cookies
	//print("setcookie");
	setcookie('cook_name',$log_name);
	setcookie('cook_pass',md5($log_pass));

	//header("Location: index.php");

	$lks[] = array( "回到主页" , 'index' );
	MessageBox( "登陆成功." , $lks );

	//exit();
case 'logout':
	if(!isset($checkcode)||$checkcode!=$udata['act_check'])
		MessageBox('验证码不对，请返回刷新页面重试');
	if($udata['is_member'])
	{
		$sql ="DELETE FROM $cfg[tb_onlines] WHERE fake_name='$udata[u_name]'";
		RenDB_Query($sql,true);
	}

	//setcookie('cook_name','',$nowtime-36000);
	//setcookie('cook_pass','',$nowtime-36000);
	setcookie('cook_name','',$nowtime-360000);	
	setcookie('cook_pass','',$nowtime-360000);	
	//header("Location: index.php");
	//break;
	$lks[] = array( "回到主页" , 'index' );
	MessageBox( "退出成功,您现在是游客" , $lks );

}
?>