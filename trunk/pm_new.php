<?php
require_once('./include/common.php');
require_once('./include/txt_func.php');

if( !$udata['is_member'] ) SimplyBox($str['act_noguest'] , true );
//if( $udata['isbanned'] ) SimplyBox( '您的账号还没解冻' , true );

if( !isset($action) ||!in_array( $action, array('invite1','invite2','new1','new2') ) )
		ErrorBox( $str['act_err'] );

if( !isset($sendto) ) $sendto = '';
if( !isset($message) ) $message = '';

switch($action)
{
case 'new1':
	$contact_options ='';
	$admin=explode('|',$cfg['admins']);
	$admin=$admin[0];
	$contact_options.="<option value=\"$admin\">$str[admin]</option>";

	if( !empty($udata['friends']) )
	{
		$my_contacts = explode('|', $udata['friends']);
		foreach( $my_contacts as $mc )
			$contact_options .="<option value=\"{$mc}\">$mc</option>";
	}

	$sql ="SELECT * FROM $cfg[tb_onlines] WHERE o_time>$o_deadline";
	$result = RenDB_Query($sql);

	while( $row = RenDB_Fetch_Array( $result ) )
	{
		if( $row['u_stand']==0 )//游客
			continue;
		if( In_names( $row['fake_name'], $udata['friends'])|| IsSameName($row['fake_name'],$udata['u_name']))
			continue;
		$contact_options .="<option style=\"bgcolor:999999#\" value=\"$row[fake_name]\" >$row[fake_name]</option>";
	}

	eval ('echo "'.LoadTemplate('pm_form').'";');
	exit();
case 'invite1':
	//联系人列表
	$contact_options ='';
	if( !empty($udata['friends']) )
	{
		$my_contacts = explode('|', $udata['friends']);
		foreach( $my_contacts as $mc )
			$contact_options .="<option value=\"{$mc}\">$mc</option>";
	}
	//在线会员
	
	$sql ="SELECT * FROM $cfg[tb_onlines] WHERE o_time>$o_deadline";
	$result = RenDB_Query($sql);

	while( $row = RenDB_Fetch_Array( $result ) )
	{
		if( $row['u_stand']==0 )//游客
			continue;
		if( In_names( $row['fake_name'], $udata['friends'])|| IsSameName($row['fake_name'],$udata['u_name']))
			continue;
		$contact_options .="<option style=\"color:999999#\" value=\"$row[fake_name]\" >$row[fake_name]</option>";
	}

	eval ("echo \"".LoadTemplate("pm_form_invite")."\";");
	exit();
	break;
case 'new2':
	$message = BBInputFilter($message,10);
	$message = CSubStr( $message, 0, $cfg['maxmsg'] );
	if($message=='') SimplyBox('请填写内容');
case 'invite2':
	if( $sendto == '' )SimplyBox('请填写收件人');
	if( IsSameName($sendto, $udata['u_name']) ) SimplyBox('不能给自己发消息');

	if($action=='invite2')
	{
		$pm_stand=4; 
		$message='[url=m_view.php?name='.urlencode($udata['u_name'])."]$udata[u_name][/url] 邀请您下棋,点击[url=room_new.php?byname=$udata[u_name]]这里[/url]查看.";
	}
	else
	{
		$pm_stand=$udata['u_stand'];
	}
	//收件人是否存在
	$sql="SELECT blacklist,have_new_pm FROM $cfg[tb_members] WHERE u_name='$sendto'";
	$result=RenDB_Query($sql);
	if(!($meminfo=RenDB_Fetch_Array( $result )))
		SimplyBox('用户不存在');

	//检查是否拒收
	if( In_Names( $udata['u_name'], $meminfo['blacklist']))
		SimplyBox('用户拒收您的消息');

	//存好友名单
	/*if( isset( $_COOKIE['my_contacts']) )
	{
		$my_contacts = explode(',', $_COOKIE['my_contacts']);
	}
	else $my_contacts = array();
	$key = array_search( $sendto, $my_contacts);
	if( $key!==false ) unset( $my_contacts[$key] );
	elseif( count($my_contacts) > 6 )array_pop($my_contacts);
		
	array_unshift( $my_contacts, $sendto );
	setcookie( 'my_contacts', implode( ',',$my_contacts), time() + $cfg['cooktime'] );
	*/

	if(!In_Names($sendto,$cfg['admins']))
	{
		//检查消息是否已满
	//	$sql ="SELECT COUNT(*) FROM $cfg[tb_pms] WHERE sendto='$sendto' OR comefrom='$sendto' LIMIT $cfg[maxpm]";
		$sql ="SELECT COUNT(*) FROM $cfg[tb_pms] WHERE sendto='$sendto' LIMIT $cfg[maxpm]";
		$result = RenDB_Query($sql);
		if( $row = RenDB_Fetch_Row( $result ) )
			if( $row[0] >= $cfg['maxpm'] ) SimplyBox( '对方消息已经满了' );
	}

	//var_dump($meminfo);
	//写入数据库
	$sql ="INSERT INTO `$cfg[tb_pms]` SET ";
	$sql.="sendto='$sendto',";
	$sql.="comefrom='$udata[u_name]',";
	$sql.="sendtime='$nowtime',";
	$sql.="message='$message',";
	$sql.="u_stand='$pm_stand'";

	$result = RenDB_Query($sql);
	if(	$row = RenDB_Insert_ID() )//写入成功
	{
		//通知用户有新消息
		if(!$meminfo['have_new_pm'])
		{
			$sql ="UPDATE $cfg[tb_members] SET have_new_pm=1 WHERE u_name='$sendto' LIMIT 1";
			RenDB_Query($sql,true);
		}

		SimplyBox( '消息发送成功' ,true );
	}
	break;
}
SimplyBox( $str['act_fail']);
?>
