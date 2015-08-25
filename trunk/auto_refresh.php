<?php
require_once('./include/common.php');
SetNoUseCache();

if($cfg['span_refresh_game']<=0) exit();
if(!$udata['is_member']) exit();
if(!isset($gid,$statcode))exit();
$gid=intval($gid);

$sql="SELECT * FROM `$cfg[tb_games]` WHERE `gid`='$gid' LIMIT 1";
$result=RenDB_Query($sql);
if(RenDB_Num_Rows($result)<1)
	//echo("<html><body onload=\"top.document.location.href='g_view.php?gid=$gid'\"></body></html>");
	exit();

$gdata = RenDB_Fetch_Array( $result );
//print_r($gdata);
//整理棋局数据
//$mcount = $gdata['mcount'];
//$curside = ( $mcount + 1 ) % 2;
$names[0] = $gdata['w_name'];
$names[1] = $gdata['b_name'];
$draws[0] = $gdata['w_draw'];
$draws[1] = $gdata['b_draw'];
$undos[0] = $gdata['w_undo'];
$undos[1] = $gdata['b_undo'];

require('./include/g_func.php');
if($gdata['status']||($gdata['startdate']>$nowtime)) $turnside=-1;
else $turnside = GetTurnSide();

if( IsSameName( $udata['u_name'], $gdata['b_name'] ) )
	$myside = 1;
else if( IsSameName( $udata['u_name'] , $gdata['w_name'] ) )
	$myside = 0;
else die();


//$gdata=RenDB_Fetch_Row($result);
if($statcode!="{$turnside}{$gdata['swaped']}{$undos[0]}{$undos[1]}{$draws[0]}{$draws[1]}-{$gdata['mcount']}") //刷新
	echo("<html><body onload=\"top.document.location.href='g_view.php?gid=$gid'\"></body></html>");
else
{
	$span=intval($cfg['span_refresh_game']);
	echo("<html><head><meta http-equiv=\"refresh\" content=\"$span;url=auto_refresh.php?gid=$gid&statcode=$statcode\"></head></html>");
}

?>