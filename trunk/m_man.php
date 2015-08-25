<?
require_once('./include/common.php');

if( !isset( $action ) || !in_array( $action, array('reg1','reg2','ed1','ed2','sendpass1','sendpass2') ) )
	ErrorBox( $str['act_err'] );

//ed1,ed2 for member only
if( !$udata['is_member'] && ( $action=='ed1' || $action=='ed2'))
	ErrorBox( $str["act_noguest"] );

require_once('./include/m_func.php');

switch( $action )
{
case 'reg1':
	if( !$cfg['register_enable']) MessageBox('目前暂停注册新账号');
	if( $cfg['max_user_per_ip']!=-1 && UserCountOfIP($userip) > $cfg['max_user_per_ip'] )
		MessageBox('你的ip不能再注册新账号了');

	//$u_name = '';
	ShowHeader('<img src="./images/stan0.gif" /> 账号注册');
	eval ('echo "'.LoadTemplate('reg_form').'";');
	ShowFooter();
	break;
case 'reg2':
	SetNoUseCache();
	if( !$cfg['register_enable']) MessageBox('目前暂停注册新账号');
	if(!isset($reg_name)||!isset($reg_pass)||!isset($reg_pass2)||!isset($reg_email))
		ErrorBox( $str['act_err'] );
	$show_email=isset($show_email)?1:0;

	if( $cfg['max_user_per_ip']!=-1 &&  UserCountOfIP($userip) > $cfg['max_user_per_ip'] )
		MessageBox('你的ip不能再注册新账号了');

	if(($code=Check_U_Name($reg_name))!==true)
		MessageBox($code);

	if( IsMember( $reg_name ) )
		MessageBox('该用户名已经被注册了');

	if(($code=Check_U_Email($reg_email))!==true)
		MessageBox($code);

	if($reg_pass!=$reg_pass2)
		MessageBox('两次输入的密码不一致');

	if(($code=Check_U_Pass($reg_pass))!==true)
		MessageBox($code);

	//insert into database
	$checkcode=md5(uniqid(rand()));
	$password=md5($reg_pass);
	$sql ="INSERT INTO $cfg[tb_members] SET ";
	$sql.="u_name		='$reg_name',";
	$sql.="u_avatar		='default.gif',";
	$sql.="u_pass		='$password',";
	$sql.="u_email		='$reg_email',";
	$sql.="show_email	='$show_email',";
	$sql.="reg_pass		='$reg_pass',";
	$sql.="reg_ip		='$userip',";
	$sql.="reg_date		='$nowtime',";
	$sql.="last_visit	='$nowtime',";
	$sql.="have_new_pm	='1',";
	$sql.="act_check	='$checkcode'";

	$result=RenDB_Query($sql);
	if(	$row=RenDB_Insert_ID() )//insert succeed
	{
		//send welcome message
		$admins=explode('|',$cfg['admins']);

		$sql ="INSERT INTO $cfg[tb_pms] SET ";
		$sql.="sendto='$reg_name',";
		$sql.="comefrom='$admins[0]',";
		$sql.="sendtime='$nowtime',";
		$sql.="message='$cfg[welcome_msg]',";
		$sql.="u_stand='4'";

		RenDB_Query($sql);

		setcookie('cook_name',$reg_name);
		setcookie('cook_pass',md5($reg_pass));

		eval('$mail_msg= "'.LoadTemplate('email_register').'";');
		//echo $mail_msg;
		
		//die($cfg['email_subject'].$mail_msg);
		if($cfg['send_email'])
			mail($reg_email,$cfg['email_subject'],$mail_msg,"From: $cfg[rbb_email]" );
		//die( $mail_msg);
		$lks[0] = array( '返回主页', 'index');
		MessageBox( '恭喜, 注册成功!<br /><br /><b>请记住你的初始密码</b><br />现在自动登陆...', $lks );
	}
	break;

case 'ed1':
	if(empty($udata['u_avatar']))$udata['u_avatar']='default.gif';
    if( strpos( $udata['u_avatar'],'/', 0 ) === false )
        $avatar_url="./images/avatar/$udata[u_avatar]";
	else
		$avatar_url=$udata['u_avatar'];

	$check_show_email= $udata['show_email'] ?'checked':'';

	//$qq_options='';
	//foreach(explode('|',$cfg['qq_types']) as $k=>$v)
	//{
	//	$qq_options.= $k==$udata['qq_type'] ? "<option selected value=\"$k\">$v</option>" : "<option value=\"$k\">$v</option>";
	//}
	
	$check_gender=array('','','');
	switch( $udata['u_gender'])
	{
	case 1:$check_gender[1]='checked';break;
	case 2:$check_gender[2]='checked';break;
	default:$check_gender[0]='checked';
	}

	if($udata['u_website']=='')$udata['u_website']="http://";
	//generate avatar list
    $avatarlist='';
	$handle=@opendir( './images/avatar' );
	if($handle)
    {
		while ( ( $file=@readdir( $handle ) ) !== false )
		{
	        if (!is_dir($file))
			{
				$file1=explode('.',$file);
				if( !in_array($file1[1], array('jpg','gif','png')))
					continue;
				if( $file1[0] == "m_{$udata['u_id']}")
			  		$avatarlist = "<option style=\"color:$color[hl1]\" value=\"$file\">我的头像</option>\n".$avatarlist;
            	else if( substr($file1[0],0,2) !='m_') 
					$avatarlist .= "<option value = \"$file\">$file</option>\n";
			}
         }
	    @closedir($handle);
	}

	if($cfg['upload_enable'])
		$upface_link='[<a href="JavaScript:UploadAvatar()">上传头像</a>]';
	else $upface_link='';

	ShowHeader('<img src="./images/profile.gif" /> 个人设置');
	eval ('echo "'.LoadTemplate('m_form').'";');
	ShowFooter();
case 'ed2':
	SetNoUseCache();
	if(!isset($old_pass,$new_pass,$new_pass2,$new_email,$u_avatar,$u_bio,
		$u_qq,$u_from,$u_website,$u_gender))
		ErrorBox( $str['act_err'] );

	$show_email=isset($show_email)?1:0;

	$u_gender=intval($u_gender);
	if($u_gender<0||$u_gender>2)$u_gender=0;


	//check new password
	if($new_pass!=''||$new_pass2!='')
	{
		//check old password
		if($old_pass=='') MessageBox('请填写原密码');
		if(md5($old_pass)!=$udata['u_pass']) MessageBox('原密码错误');

		if($new_pass!=$new_pass2)
			MessageBox('两次输入的密码不一致');

		if(($code=Check_U_Pass($new_pass))!==true)
			MessageBox($code);

		$new_pass=md5($new_pass);
	}
	else
	{
		$new_pass=$udata['u_pass'];
	}

	if(($code=Check_U_Email($new_email))!==true)
		MessageBox($code);

	//检查主页
	if($u_website=='http://')$u_website='';

	//检查QQ
	//$qq_type=intval($qq_type);
	//if(!array_key_exists($qq_type,explode('|',$cfg['qq_types'])))$qq_type=0;


	require_once('./include/txt_func.php');
	$u_qq=BBInputFilter($u_qq,1);
	$u_qq=CSubStr( $u_qq, 0, 60 );
	$u_website=BBInputFilter($u_website,1);
	$u_website=CSubStr( $u_website, 0, 120 );
	$u_from=BBInputFilter($u_from,1);
	$u_from=CSubStr( $u_from, 0, 20 );
	$u_bio=BBInputFilter($u_bio,10);
	$u_bio=CSubStr( $u_bio, 0, $cfg['maxbio'] );
	//$u_sig=BBInputFilter($u_sig,5);
	//$u_sig=CSubStr( $u_sig, 0, $cfg['maxsig'] );
	$u_avatar=BBInputFilter($u_avatar,1);
	$u_avatar=CSubStr( $u_avatar, 0, 120 );
	

	//insert into database
	$sql="UPDATE $cfg[tb_members] SET ";
	$sql.="u_pass	='$new_pass',";
	$sql.="u_email		='$new_email',";
	$sql.="show_email	='$show_email',";
	$sql.="u_bio	='$u_bio',";
	//$sql.="u_sig	='$u_sig',";
	$sql.="u_avatar	='$u_avatar', ";
	$sql.="u_gender	='$u_gender', ";
	$sql.="u_qq	='$u_qq', ";
	//$sql.="qq_type	='$qq_type', ";
	$sql.="u_from	='$u_from', ";
	$sql.="u_website='$u_website' ";
	$sql.="WHERE u_id=$udata[u_id]";

	$result = RenDB_Query($sql);
	if(	RenDB_Affected_Rows() > 0 )//insert succeed
	{
		setcookie('cook_name',$udata['u_name']);
		setcookie('cook_pass',$new_pass);
			
		$lks[0] = array( '查看资料', 'm_view', 'name'=>urlencode($udata['u_name']));
		MessageBox( '更新成功' ,$lks);
	}

	MessageBox('资料并没有改变');
	break;
case 'sendpass1':
	
	if(!$cfg['send_email']) MessageBox('管理员关闭了系统邮件，目前不能使用此功能');

	ShowHeader('<img src="./images/stan0.gif" /> 取回密码');
	eval ("echo \"".LoadTemplate("sendpass_form")."\";");
	ShowFooter();
	break;
case 'sendpass2':
	SetNoUseCache();

	if(!$cfg['send_email']) ErrorBox( $str['act_err'] );
	if( !isset($u_name,$u_email))
		ErrorBox( $str['act_err'] );

	if( In_Names( $u_name,$cfg['admins']))
	{
		WriteBBLog( "试图取回管理员 {$u_name} 的密码",'wrongpass');
		ErrorBox( $str['act_err'] );
	}

	if(($code=Check_U_Email($u_email))!==true)
		MessageBox($code);

	$sql = "SELECT u_id FROM `$cfg[tb_members]` WHERE u_name='$u_name' AND u_email='$u_email' LIMIT 1";
	$result = RenDB_Query($sql);
	if( RenDB_Num_Rows($result) != 1 )
	{
		ErrorBox('Email 和 用户名不匹配');
	}

	$row = RenDB_Fetch_Array( $result );
	if($row['u_id']<1) MessageBox( '发送失败' );

	//generate checkcode for new pass
	$passcheck=md5(uniqid(rand(),true));
	$newpass=substr(md5(uniqid(rand(),true)),0,8);
	//$newpass=uniqid(rand(),true);

	//write into database
	$sql = "UPDATE `$cfg[tb_members]` SET new_pass='$newpass', new_pass_check='$passcheck' WHERE u_id='$row[u_id]' LIMIT 1";
	RenDB_Query($sql,true);

	$nowtime=TimeToDate($nowtime,true);
	//send email
	eval('$mail_msg= "'.LoadTemplate('email_send_pass').'";');
	//echo $mail_msg;
	
	//die($cfg['email_subject'].$mail_msg);
	if(mail($u_email,$cfg['email_subject'],$mail_msg,
		"From: $cfg[rbb_email]" ))
	{
		MessageBox('新的密码已经发到您的邮箱，请查收');
	}
	else
	{
		MessageBox('发送失败');
	}
	break;
case 'confirm_pass':
	SetNoUseCache();

	if( !isset( $u_id,$passcheck,$newpass ))
		ErrorBox( $str['act_err'] );

	if($passcheck==''||$newpass=='') ErrorBox( $str['act_err'] );

	$sql="UPDATE `$cfg[tb_members]` SET u_pass=MD5(new_pass), new_pass='',new_pass_check='' WHERE u_id='$u_id' AND new_pass='$newpass' AND new_pass_check='$passcheck' LIMIT 1";
	RenDB_Query($sql);
	if( RenDB_Affected_Rows())
	{
		$lks[0]=array('登陆','index');
		MessageBox('新密码已经激活，请用新密码登陆',$lks);
	}
	else
	{				
		$lks[0]=array('登陆','index');
		WriteBBLog("验证码错误(找回密码) u_id='$u_id' ", 'wrongpass');
		ErrorBox('验证码不对，可能是已经过期，请重新发送密码',$lks);
	}
	break;
}

ErrorBox( $str['act_fail'] );

?>
