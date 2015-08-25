<?php

define('RBB_NO_GZIP',true);
require_once('./include/common.php');
require_once('./include/g_func.php');

if(!isset($gid))ErrorBox( $str['act_err'] );
$gid = intval( $gid );

//读棋局
//$sql = "SELECT * FROM `$cfg[tb_games]` WHERE `gid`='$gid' LIMIT 1";
$sql ="SELECT * FROM `$cfg[tb_games]` WHERE `gid`='$gid' LIMIT 1";
$result=RenDB_Query($sql);
if( RenDB_Num_Rows($result) < 1 )
	ErrorBox( '棋局不存在,可能已经被删除' );
$gdata=RenDB_Fetch_Array( $result );

if(!isset($filetype)||!in_array($filetype,array('pos')))
	$filetype='pos';

$filename="$gdata[gid]#$gdata[rules]#$gdata[b_name]#$gdata[w_name]#$gdata[swaped]#$gdata[status].$filetype";
//echo $filename;
switch($filetype)
{
case 'pos':
	header("Content-type: x-file/pos");
	header("Content-Disposition: attachment; filename=$filename");
	for($i=0; $i<$gdata['mcount']; $i++)
	{
		$pos=ord($gdata['moves']{$i});
		$x=($pos-1) % 15;
		$y=14-intval(($pos-1) / 15 );
		$gdata['moves']{$i}=chr(15*$y+$x);
		
	}
	print( chr($gdata['mcount']));
	print($gdata['moves']);
	break;
}

?>