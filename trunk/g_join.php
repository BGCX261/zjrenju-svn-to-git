<?php
require_once('./include/common.php');

if( !isset($gid) || !isset($action)
	||!in_array( $action, array('join','select','del') ) )
		ErrorBox( $str['act_err'] );

if( !$udata['is_member'] ) ErrorBox( $str['act_noguest'] );

//读棋局
$gid = intval( $gid );
$sql = "SELECT ng.*,m.u_name,m.skill,m.g_w,m.g_d,m.g_l,m.g_to FROM `$cfg[tb_newgames]` AS ng LEFT JOIN `$cfg[tb_members]` AS m ON m.u_name=ng.host_name WHERE gid='$gid' LIMIT 1";
$result = RenDB_Query($sql);
if( RenDB_Num_Rows($result) < 1 )
	MessageBox('操作失败，棋局可能已开始或被删除');
$gdata = RenDB_Fetch_Array( $result );

//超时检测
if( $gdata['app_count'] > 0 )
{
	require_once( './include/g_func.php' );
	if( GetNewGameTimeOutInfo() == 0 ) MessageBox('本局因超时被删除');
}

//分离申请列表
if( $gdata['app_count'] > 0)
{
	$applist=explode( '|', $gdata['app_list'] ) ;
	foreach( $applist as $k=>$v )
		$applist[$k] = explode(',', $v );
}
else $applist = array();

switch( $action )
{
case 'join':
	if( IsSameName( $gdata['host_name'], $udata['u_name'] ) ) MessageBox( '不能和自己对局' );
	if( $userip==$gdata['u_ip']) MessageBox( 'IP 限制，你不能加入这一局' );
	//if( $gdata['status'] != 0 || $gdata['w_name']!='' )  MessageBox( '白棋的位置已经有人了' );
	foreach( $applist as $k=>$v )
		if( IsSameName($v[0], $udata['u_name'] ) ) MessageBox( '你已经在等待队列中了' );
	
	if( $gdata['app_count'] >= $cfg['maxapply'] ) MessageBox( '这一桌已经满人了');
	
	$mytout = GetBBTout( $udata );
	
	//积分和掉线率
	if($gdata['skill_range']!=-1 &&( $udata['skill']<$gdata['skill']-$gdata['skill_range'] || $udata['skill']>$gdata['skill']+$gdata['skill_range']))
		MessageBox('您的积分不符合桌主要求');
	if($mytout> $gdata['tout_max'])
		MessageBox('您的掉线率不符合桌主要求');

	if($gdata['app_list']!='')$gdata['app_list'].='|';
	$gdata['app_list'] .= "$udata[u_name],$udata[skill],$mytout";
	if( $gdata['app_count'] == 0 ) $gdata['l_time'] = $nowtime;
	$gdata['app_count'] ++;

	$sql="UPDATE `$cfg[tb_newgames]` SET l_time='$gdata[l_time]', app_count='$gdata[app_count]', app_list='$gdata[app_list]' WHERE gid='$gid'";
	$result = RenDB_Query($sql);
	if( RenDB_Affected_Rows() != 1 )
		MessageBox($str['act_fail']);

	if(!isset($page))$page = 1;
	if(!isset($rules))$rules=-1;
	$rules=intval($rules);
	$redirect="Location: room_new.php?rules=$rules&page=$page";
	if(isset($byname)&&$byname!='')$redirect.="&byname=".urlencode($byname);
	header($redirect);
	exit();

case 'select':
	if( !isset($tarname) ) ErrorBox( $str['act_err'] );
	//if( /*$gdata['status'] != 0 */$gdata['mcount']<3 ) ErrorBox( '请先摆好开局' );
	//if( $gdata['w_name'] != '' ) ErrorBox( '白棋的位置已经有人了' );
	//$tarname = InputStrFilter( $tarname );
	if( !IsSameName( $gdata['host_name'], $udata['u_name'] ) ) ErrorBox( '这不是你建的棋局' );
	if( IsSameName( $gdata['host_name'], $tarname ) ) MessageBox( '不能和自己对局' );
	$inapp = false;
	foreach( $applist as $k=>$v )
		if( IsSameName($v[0], $tarname ) ) 
		{
			$inapp = true;
			break;
		}

	if( !$inapp ) ErrorBox( $tarname.'不在等待队列中' );

    $sql ="DELETE FROM $cfg[tb_newgames] WHERE gid='$gid'";
	RenDB_Query($sql);

	//随机
	if( $gdata['host_color']==2)
		$gdata['host_color']=intval(rand())%2;

	if( $gdata['host_color']==1)
	{
		$bname = $gdata['host_name'];
		$wname = $tarname;
	}
	else
	{
		$bname = $tarname; 
		$wname = $gdata['host_name'];
	}

	$sql ="INSERT INTO $cfg[tb_games] SET rules='$gdata[rules]', b_name='$bname', w_name='$wname', b_time='$gdata[add_time]', w_time='$gdata[add_time]', l_time='$nowtime', step_time='$gdata[step_time]', add_time='$gdata[add_time]', startdate='$nowtime', turn_name='$bname'";

	//for($i=0;$i<3;$i++)
	RenDB_Query($sql);
	if( RenDB_Affected_Rows()==1 )
	{
		header('Location: room_mynew.php');
		exit();
	}
	break;
case 'del':
	if( !IsSameName( $gdata['host_name'], $udata['u_name'] ) ) ErrorBox( '这不是你建的棋局' );
    $sql ="DELETE FROM {$cfg['tb_newgames']} WHERE gid='{$gid}'";
	RenDB_Query($sql);

	if( RenDB_Affected_Rows() ==1 )
	{
		header('Location: room_mynew.php');
		exit();
	}
	break;
}

MessageBox($str['act_fail']);

?>
