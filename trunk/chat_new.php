<?php
require_once('./include/common.php');

if( !isset($gid,$message)) ErrorBox( $str['act_err'] );
$gid=intval($gid);

require_once('./include/txt_func.php');
$message=BBInputFilter($message,1);
$message=CSubStr( $message, 0, 253 );
if($message=='') MessageBox($str['req_content']);

$sql="SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE gid='$gid' LIMIT 1";
$result=RenDB_Query($sql);
$row=RenDB_Fetch_Row( $result );
if(!$row[0]) MessageBox($str['g_not_found']);

$sql="SELECT COUNT(*) FROM `$cfg[tb_chats]` WHERE gid='$gid'";
$result=RenDB_Query($sql);
$row=RenDB_Fetch_Row( $result );
if($row[0]>25)
{
	$sql="SELECT * FROM `$cfg[tb_chats]` WHERE gid='$gid' ORDER BY chat_id LIMIT 3,1";
	$result=RenDB_Query($sql);
	if($chatdata=RenDB_Fetch_Array($result))
	{
		$sql="DELETE FROM `$cfg[tb_chats]` WHERE gid='$gid' AND chat_id<'$chatdata[chat_id]'";
		RenDB_Query($sql,true);
	}
}

//$author=$udata['is_member']?$udata['u_name']:'Guest';
$sql="INSERT INTO `$cfg[tb_chats]` SET gid='$gid', chat_message='$message', chat_author='$udata[u_name]', chat_date='$nowtime'";
RenDB_Query($sql,true);
header("Location: g_view.php?gid=$gid");

?>
