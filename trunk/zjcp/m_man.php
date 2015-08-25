<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');

if(!isset($action))$action='show';
if(!in_array($action,array('show','banacc','ed1','ed2','del','batch_del1','batch_del2')))
	ACP_MessageBox($str['act_err']);

require_once('../include/m_func.php');

switch($action)
{
case 'show':
	SetNoUseCache();

	//列出目前被冻结的账号
	$ban_accs='';
	$sql="SELECT u_name,ban_before FROM `$cfg[tb_members]` WHERE ban_before>'$nowtime' OR ban_before=-1 ORDER BY IF(ban_before=-1,9999999999,ban_before)";
	$result=RenDB_Query($sql);
	$count=RenDB_Num_Rows($result);
	while($row=RenDB_Fetch_Array($result))
	{
		if($row['ban_before']>0)
		{
			$deadline=TimeToDate($row['ban_before'],true);
			$ban_accs.="<option value=\"$row[u_name]\">$deadline | $row[u_name] </option>";
		}
		else
		{
			$ban_accs.="<option value=\"$row[u_name]\">永久冻结 | $row[u_name] </option>";
		}
	}

	if($ban_accs!='') $ban_accs="<select onchange=\"document.banacc.ban_name.value=this.value;\"><option>目前共{$count}人被冻结</option>$ban_accs</select>";
	else $ban_accs='目前没有人被冻结';

	ACP_ShowHeader('账号管理');
	eval ('echo "'.LoadTemplate('m_man').'";');
	ACP_ShowFooter();
	break;
case 'banacc':
	SetNoUseCache();
	if(!isset($ban_name)||!isset($ban_time)) ACP_MessageBox( $str['act_err']);
	if(!IsMember($ban_name)) ACP_MessageBox('此用户不存在');
	if(In_Names($ban_name,$cfg['admins'])) ACP_MessageBox("$ban_name 是超级管理员");

	$ban_time=intval($ban_time);
	if($ban_time!=0 && $ban_time!=-1 && $ban_time<1440 || $ban_time>43200) ACP_MessageBox('时间超出范围');

	if($ban_time==-1)
	{
		$banbefore=-1;
	}
	else
	{
		$ban_time*=60;
		$banbefore=$ban_time+$nowtime;
	}
	$sql ="UPDATE $cfg[tb_members] SET ban_before='$banbefore' WHERE u_name='$ban_name' LIMIT 1";
	RenDB_Query($sql,true);


	if( $banbefore==-1)
	{
		ACP_WriteLog("冻结账号 $ban_name ");
		ACP_MessageBox("$ban_name 永久冻结");
	}
	if($ban_time<=0 ) 
	{
		ACP_WriteLog("解冻账号 $ban_name ");
		ACP_MessageBox("$ban_name 解冻完毕");
	}
	else
	{
		ACP_WriteLog("冻结账号 $ban_name ");
		ACP_MessageBox("$ban_name 冻结到 ".TimeToDate($banbefore));
	}
	break;
case 'ed1':
	if(!isset($u_name)) ACP_MessageBox( $str['act_err']);
	$u_name=trim($u_name);
	

	$sql="SELECT * FROM `$cfg[tb_members]` WHERE u_name='$u_name' LIMIT 1";
	$result=RenDB_Query($sql);
	if(!($mdata=RenDB_Fetch_Array($result)))
		ACP_MessageBox( '该用户不存在' );

	if(IN_Names($u_name,$cfg['admins'])) $m_stan='管理员';
	else $m_stan='';

	$m_stan=HLTxt($m_stan);

	if(empty($mdata['u_avatar']))$mdata['u_avatar']='default.gif';
    if( strpos( $mdata['u_avatar'],'/', 0 ) === false )
        $avatar_url="../images/avatar/$mdata[u_avatar]";
	else
		$avatar_url=$mdata['u_avatar'];

	$check_show_email= $mdata['show_email'] ?'checked':'';

	//$qq_options='';
	//foreach(explode('|',$cfg['qq_types']) as $k=>$v)
	//{
	//	$qq_options.= $k==$mdata['qq_type'] ? "<option selected value=\"$k\">$v</option>" : "<option value=\"$k\">$v</option>";
	//}
	
	$check_gender=array('','','');
	switch( $mdata['u_gender'])
	{
	case 1:$check_gender[1]='checked';break;
	case 2:$check_gender[2]='checked';break;
	default:$check_gender[0]='checked';
	}

	if($mdata['u_website']=='')$mdata['u_website']="http://";
	//generate avatar list
    $avatarlist='';
	$handle=@opendir( '../images/avatar' );
	if($handle)
    {
		while ( ( $file=@readdir( $handle ) ) !== false )
		{
	        if (!is_dir($file))
			{
				$file1=explode('.',$file);
				if( !in_array($file1[1], array('jpg','gif','png')))
					continue;
				if( $file1[0] == "m_{$mdata['u_id']}")
			  		$avatarlist = "<option style=\"color:$color[hl1]\" value=\"$file\">我的头像</option>\n".$avatarlist;
            	else if( substr($file1[0],0,2) !='m_') 
					$avatarlist .= "<option value = \"$file\">$file</option>\n";
			}
         }
	    @closedir($handle);
	}
	$encode_name=urlencode($mdata['u_name']);

	ACP_ShowHeader('编辑会员资料');
	eval ('echo "'.LoadTemplate('m_form').'";');
	ACP_ShowFooter();
	break;
case 'ed2':
	SetNoUseCache();
	if(!isset($u_name,$new_pass,$new_pass2,
		$u_email,$u_gender,$u_skill,$g_w,$g_d,$g_l,$g_to,
		$u_from,$u_qq,$u_website,$u_friends,$u_blacklist,$u_avatar,$u_bio))
			ACP_MessageBox( $str['act_err'] );
	

	$sql="SELECT * FROM `$cfg[tb_members]` WHERE u_name='$u_name' LIMIT 1";
	$result=RenDB_Query($sql);
	if(!($mdata=RenDB_Fetch_Array($result)))
		ACP_MessageBox( '该用户不存在' );

	$show_email=isset($show_email)?1:0;
	//$posts=intval($u_posts);
	//$credit=intval($u_credit);
	$skill=floatval($u_skill);
	$g_w=intval($g_w);
	$g_d=intval($g_d);
	$g_l=intval($g_l);
	$g_to=intval($g_to);
	$u_gender=intval($u_gender);
	if($u_gender<0||$u_gender>2)$u_gender=0;

	//check new password
	if($new_pass!=''||$new_pass2!='')
	{
		if($new_pass!=$new_pass2)
			ACP_MessageBox('两次输入的密码不一致');

		if(($code=Check_U_Pass($new_pass))!==true)
			ACP_MessageBox($code);

		$new_pass=md5($new_pass);
	}
	else
	{
		$new_pass=$mdata['u_pass'];
	}

	if(($code=Check_U_Email($u_email))!==true)
		ACP_MessageBox($code);

	//检查主页
	if($u_website=='http://')$u_website='';

	//检查QQ
	//$qq_type=intval($qq_type);
	//if(!array_key_exists($qq_type,explode('|',$cfg['qq_types'])))$qq_type=0;


	require_once('../include/txt_func.php');
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
	$friends=BBInputFilter($u_friends,1);
	$friends=CSubStr( $friends, 0, 253 );
	$blacklist=BBInputFilter($u_blacklist,1);
	$blacklist=CSubStr( $blacklist, 0, 253 );
	
	//insert into database
	$sql="UPDATE $cfg[tb_members] SET ";
	$sql.="u_pass	='$new_pass',";

	$sql.="u_email		='$u_email',";
	$sql.="show_email	='$show_email',";
	$sql.="u_gender	='$u_gender', ";
	//$sql.="posts	='$posts', ";
	//$sql.="credit	='$credit', ";
	$sql.="skill	='$skill', ";
	$sql.="g_w	='$g_w', ";
	$sql.="g_d	='$g_d', ";
	$sql.="g_l	='$g_l', ";
	$sql.="g_to	='$g_to', ";

	$sql.="u_from	='$u_from', ";
	$sql.="u_qq	='$u_qq', ";
	//$sql.="qq_type	='$qq_type', ";
	$sql.="u_website	='$u_website',";
	$sql.="friends	='$friends',";
	$sql.="blacklist	='$blacklist',";

	$sql.="u_avatar	='$u_avatar', ";
	$sql.="u_bio	='$u_bio'";
//	$sql.="u_sig	='$u_sig' ";
	$sql.="WHERE u_id=$mdata[u_id]";

	$result = RenDB_Query($sql);
	if(	RenDB_Affected_Rows() > 0 )//insert succeed
	{
		setcookie('cook_name',$mdata['u_name']);
		setcookie('cook_pass',$new_pass);
			
		//$lks[0] = array( '查看资料', 'm_view', 'name'=>urlencode($mdata['u_name']));
		ACP_MessageBox( '更新成功');
	}

	ACP_MessageBox('资料并没有改变');
	break;
case 'batch_del1':
	SetNoUseCache();
	if(!isset($dateline,$gameline))
		ACP_MessageBox( $str['act_err'] );

	$dateline=intval($dateline);
	$gameline=intval($gameline);
	//$postline=intval($postline);

	if($dateline<=0 || $gameline<0 )
		ACP_MessageBox( '输入超出范围' );

	$dateline2=time()-$dateline*86400;

	//滤掉掉管理员
	$admins=explode('|',$cfg['admins']);
	foreach( $admins as $k=>$v)
		$admins[$k]=" AND u_name<>'$v' ";
	$admins=implode('',$admins);

	$sql="SELECT COUNT(*) FROM `$cfg[tb_members]` WHERE last_visit<'$dateline2' AND g_w+g_d+g_l<'$gameline' $admins";
//	echo $sql;
	$result=RenDB_Query($sql);
	$row=RenDB_Fetch_Row($result);

	$confirm_text= $row[0] ?
		"您确定要删除{$dateline}天没登陆且对局少于{$gameline}的会员吗?(符合条件的账号数:$row[0]) [<a href=\"index.php?mode=m_man&action=batch_del2&dateline=$dateline&gameline=$gameline\">确定</a>] [<a href=\"JavaScript:history.back(-1)\">取消</a>]<br />"
		:"没有符合条件的账号 [<a href=\"JavaScript:history.back(-1)\">返回</a>]";

	$warning_text='';
	if($row[0] )
	{
		if($dateline<30 )
			$warning_text.='建议日期改到30天以上.&nbsp;';
		if($gameline>50) $warning_text.='建议对局数改小一些.&nbsp;';
		//if($postline>50) $warning_text.='建议对发贴数改小一些.&nbsp;';
		if($warning_text!='')
			$warning_text=HLtxt($warning_text);
	}

	ACP_ShowHeader('确认批量删除会员');
	eval ('echo "'.LoadTemplate('m_batch_del1').'";');
	ACP_ShowFooter();
	break;
case 'batch_del2':
	if(!isset($dateline,$gameline))
		ACP_MessageBox( $str['act_err'] );

	$dateline=intval($dateline);
	$gameline=intval($gameline);
	//$postline=intval($postline);

	if($dateline<=0 || $gameline<0 )
		ACP_MessageBox( '输入超出范围' );

	$dateline2=time()-$dateline*86400;

	//滤掉掉管理员
	$admins=explode('|',$cfg['admins']);
	foreach( $admins as $k=>$v)
		$admins[$k]=" AND u_name<>'$v' ";
	$admins=implode('',$admins);

	$sql="SELECT u_name FROM `$cfg[tb_members]` WHERE last_visit<'$dateline2' AND g_w+g_d+g_l<'$gameline' $admins LIMIT 100";
	$result=RenDB_Query($sql);
	while($row=RenDB_Fetch_Row($result))
	{
		//对符合条件的会员的贴子、主题和棋局改成游客的
		DeleteMember($row[0]); 
	}

	$sql="SELECT COUNT(*) FROM `$cfg[tb_members]` WHERE last_visit<'$dateline2'  AND g_w+g_d+g_l<'$gameline' $admins";
	$result=RenDB_Query($sql);
	$row=RenDB_Fetch_Row($result);
	if($row[0]>0)
	{
		header("Location: index.php?mode=m_man&action=batch_del2&dateline=$dateline&gameline=$gameline");
		exit();
	}

	ACP_MessageBox( '删除完毕' );
	break;
case 'del':
	if(!isset($u_name))	ACP_MessageBox( $str['act_err'] );
	if(In_Names($u_name,$cfg['admins'])) ACP_MessageBox( '不能删除管理员' );
	if(!IsMember($u_name)) ACP_MessageBox( $u_name.' 不是会员' );
	DeleteMember($u_name); 
	$lks[0]=array('返回','m_man');
	ACP_MessageBox( '删除成功',$lks );
	break;
}
?>