<?
require_once('./include/common.php');
SetNoUseCache();

if( !isset( $action ) || !in_array( $action, array('view','f_add','f_del','b_add','b_del') ) )
	ErrorBox( $str['act_err'] );

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

if( $action!='view')
{
	if(!isset($tname)) ErrorBox($str['act_err']);
	if(empty($tname)) ErrorBox('名字不能为空');
	if(strlen($tname)>12) ErrorBox('无效的名字');
}

//只有会员能改资料
if( !$udata['is_member']) ErrorBox( $str["act_noguest"] );

switch( $action )
{
case 'view':
	$onlines=array();
	if(!empty($udata['friends'])||!empty($udata['blacklist']) )
	{
		$sql ="SELECT * FROM $cfg[tb_onlines] WHERE o_time>'$o_deadline'";
		$result = RenDB_Query($sql);
		while($row=RenDB_Fetch_Array( $result ) )
		{
			$onlines[]=$row['fake_name'];
		}
	}

	if(!empty($udata['friends']))
	{
		$friends=explode('|',$udata['friends']);
		asort($friends);
		$f_list='';
		foreach($friends as $n)
		{
			$this_name=MemberLink($n);
			foreach($onlines as $n2)
				if(IsSameName($n,$n2))
				{
					$this_name='<b>'.$this_name.'</b>';
					break;
				}
			$f_list.=$this_name."[<a href=\"m_fb.php?action=f_del&tname=$n\">删</a>] ";
		}
	}
	else $f_list='(空)';

	if(!empty($udata['blacklist']))
	{
		$blacklist=explode('|',$udata['blacklist']);
		asort($blacklist);
		$b_list='';
		foreach($blacklist as $n)
		{
			$this_name=MemberLink($n);
			foreach($onlines as $n2)
				if(IsSameName($n,$n2))
				{
					$this_name='<b>'.$this_name.'</b>';
					break;
				}
			$b_list.=$this_name."[<a href=\"m_fb.php?action=b_del&tname=$n\">删</a>] ";
		}
	}
	else $b_list='(空)';

	$addbox="<input type=text size=20 maxlength=12 name=\"tname\"> <input type=\"submit\" value=\"添加\">";
	$add_box_f=strlen($udata['friends']) >= $cfg['max_friends']?'您的好友列表已满': $addbox;
	$add_box_b=strlen($udata['blacklist']) >= $cfg['max_blacklist']?'您的黑名单已满': $addbox;
	ShowHeader('<img src="./images/profile.gif" /> 我的好友');
	eval ('echo "'.LoadTemplate('m_fb').'";');
	ShowFooter();
	break;
case 'f_add':
	if(!IsMember($tname)) ErrorBox('用户不存在');
	if(IsSameName($tname,$udata['u_name'])) MessageBox('不能加自己');
	if(strlen($udata['friends']) >= $cfg['max_friends']) MessageBox('您的好友列表已经满了');
	if(In_Names($tname,$udata['friends'])) MessageBox('该用户已经在好友列表中了');
	
	if(In_Names($tname,$udata['blacklist']))
	{
		//从黑名单中删除
		$blacklist=explode('|',$udata['blacklist']);
		foreach($blacklist as $k=>$n)
		{
			if(IsSameName($tname,$n))
			{
				unset($blacklist[$k]);
				break;
			}
		}
		$udata['blacklist']=implode('|',$blacklist);
	}
	if(!empty($udata['friends']))$udata['friends'].='|';
	$udata['friends'].=$tname;
	$sql="UPDATE $cfg[tb_members] SET friends='$udata[friends]',blacklist='$udata[blacklist]' WHERE u_id='$udata[u_id]' LIMIT 1";
	RenDB_Query($sql,true);
	header("Location: m_fb.php?action=view");
	exit();
	break;
case 'f_del':
	if(!In_Names($tname,$udata['friends'])) MessageBox('该用户不在好友列表中');
	$friends=explode('|',$udata['friends']);
	foreach($friends as $k=>$n)
		if( IsSameName($n,$tname))
		{
			unset($friends[$k]);
			break;
		}
	$udata['friends']=implode('|',$friends);
	$sql="UPDATE $cfg[tb_members] SET friends='$udata[friends]' WHERE u_id='$udata[u_id]' LIMIT 1";
	RenDB_Query($sql,true);
	header("Location: m_fb.php?action=view");
	exit();
	break;
case 'b_add':
	if(!IsMember($tname)) ErrorBox('用户不存在');
	if(IsSameName($tname,$udata['u_name'])) MessageBox('不能加自己');
	if(strlen($udata['blacklist']) >= $cfg['max_blacklist']) MessageBox('您的黑名单已经满了');
	if(In_Names($tname,$udata['blacklist'])) MessageBox('该用户已经在黑名单中了');
	if(In_Names($tname,$udata['friends']))
	{
		//从黑名单中删除
		$friends=explode('|',$udata['friends']);
		foreach($friends as $k=>$n)
		{
			if(IsSameName($tname,$n))
			{
				unset($friends[$k]);
				break;
			}
		}
		$udata['friends']=implode('|',$friends);
	}	
	
	if(!empty($udata['blacklist']))$udata['blacklist'].='|';
	$udata['blacklist'].=$tname;
	$sql="UPDATE $cfg[tb_members] SET friends='$udata[friends]',blacklist='$udata[blacklist]' WHERE u_id='$udata[u_id]' LIMIT 1";
	RenDB_Query($sql,true);
	header("Location: m_fb.php?action=view");
	exit();
	break;
case 'b_del':
	if(!In_Names($tname,$udata['blacklist'])) MessageBox('该用户不在黑名单中');
	$blacklist=explode('|',$udata['blacklist']);
	foreach($blacklist as $k=>$n)
		if( IsSameName($n,$tname))
		{
			unset($blacklist[$k]);
			break;
		}
	$udata['blacklist']=implode('|',$blacklist);
	$sql="UPDATE $cfg[tb_members] SET blacklist='$udata[blacklist]' WHERE u_id='$udata[u_id]' LIMIT 1";
	RenDB_Query($sql,true);
	header("Location: m_fb.php?action=view");
	exit();
	break;

}

ErrorBox( $str['act_fail'] );

?>