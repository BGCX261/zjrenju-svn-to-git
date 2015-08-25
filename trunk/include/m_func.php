<?php
//get acc count of special ip
function UserCountOfIP($ip)
{
	global $cfg;
	$sql = "SELECT COUNT(*) FROM $cfg[tb_members] WHERE reg_ip='$ip'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	return $row[0];
}

//check if user name already existed
function IsMember( $name )
{
	global $cfg;
	$sql ="SELECT COUNT(*) FROM $cfg[tb_members] WHERE u_name='$name' LIMIT 1";
	$result = RenDB_Query($sql);

	if( RenDB_Num_Rows($result) != 0 )
	{
		$row = RenDB_Fetch_Row( $result );
		return $row[0];
	}
	return 0;
}

//check user name
function Check_U_Name($name)
{
	$len = strlen( $name );
	if($len<3) return '用户名太短了';
	if($len>12) return '用户名太长了';

	//check bad char
	if (!preg_match('/^[A-Za-z0-9]+[ _\-]{0,1}[A-Za-z0-9]+$/', $name)) 
		return '用户名不符合规范,请更换';

	//check bad word
	$disallowname='　|  |邀棋信息|管理员|会员|版主|斑竹|游客|fuck|shit|admin|moderator|guest|member';
	$disallowname=explode('|',$disallowname);
	$lowname=strtolower($name);
	foreach($disallowname as $v)
	{
		if(strpos($lowname, $v)!==false)
			return '系统不允许注册此用户名';
	}
	return true;
}

//check password
function Check_U_Pass($pass)
{
	$len = strlen( $pass );
	if($len<3) return '密码太短了';
	if($len>32) return '密码太长了';

	if ( !eregi( '^[A-Za-z0-9]+$', $pass ) )
		return '密码只能用英文字母和数字';
	return true;
}

function Check_U_Email($email)
{
	return ( eregi("^[0-9a-z]+([._-][0-9a-z]+)*_?@[0-9a-z]+([._-][0-9a-z]+)*[.][0-9a-z]{2}[0-9a-z]?[0-9a-z]?$", $email) ?
		true : 'Email 不正确');
}

//删除一个会员，并清理贴子棋局pm等
function DeleteMember($u_name)
{
	global $cfg;

	//$sql="UPDATE `$cfg[tb_topics]` SET author='' WHERE author='$u_name'";
	//RenDB_Query($sql,true);

	//$sql="UPDATE `$cfg[tb_posts]` SET author='' WHERE author='$u_name'";
	//RenDB_Query($sql,true);

	$sql="UPDATE `$cfg[tb_chats]` SET author='' WHERE author='$u_name'";
	RenDB_Query($sql,true);

	$sql="DELETE FROM `$cfg[tb_newgames]` WHERE host_name='$u_name'";
	RenDB_Query($sql,true);

	$sql="DELETE FROM `$cfg[tb_pms]` WHERE sendto='$u_name' OR comefrom='$u_name'";
	RenDB_Query($sql,true);

	$sql="DELETE FROM `$cfg[tb_games]` WHERE w_name='$u_name' OR b_name='$u_name' ";
	RenDB_Query($sql,true);


	$sql="DELETE FROM `$cfg[tb_members]` WHERE u_name='$u_name'";
	RenDB_Query($sql,true);
}
?>