<?php
define('IN_RBB_INDEX',true);
require_once('./include/common.php');
SetNoUseCache();

$announce=implode( '', file( "./news/$lang_pack.html" ));

$lang_link='';
foreach($langs as $k=>$v)
{
	if($lang_pack==$k) continue;
	$lang_link.="[<a href=\"index.php?lang_pack=$k\">$v</a>]";
}

//当前在线列表

$sql ="SELECT * FROM $cfg[tb_onlines] WHERE o_time>$o_deadline";
$result = RenDB_Query($sql);
$onlcount = RenDB_Num_Rows($result);

$onlines = '';
$first=true;
$guestcount=0;
while( $row = RenDB_Fetch_Array( $result ) )
{
	if( $row['u_stand']==0 )
	{
		$guestcount++;
		continue;
	}

	if(!$first)$onlines.=', ';
	$onlines .=MemberLink($row['fake_name']);
	$first=false;
}

if( $guestcount > 0 )
{
	if(!$first)$onlines.=', ';
	$onlines .= "Guest x $guestcount</td>";
}

//删除旧的在线记录
$sql ="DELETE FROM $cfg[tb_onlines] WHERE o_time<=$o_deadline OR o_time>'".time()."'";
RenDB_Query($sql,true);

//在线纪录
require_once('./cache/onlinerec.php');
if( $onlcount > $maxonline[0] )
{
	$maxonline[0] = $onlcount;
	$buf ="<?php \$maxonline=array($onlcount,$nowtime); ?>";
	$fr = @fopen( './cache/onlinerec.php', 'w' );
	@flock( $fr, LOCK_EX);
	@fwrite( $fr, $buf );
	@fclose( $fr );
	WriteBBLog( "在线人数记录: $onlcount");
}
$maxonline[1] = TimeToDate($maxonline[1]);

ShowHeader('<img src="./images/home.gif" /> '.$str['main_page']);
eval ('echo "'.LoadTemplate('main').'";');
ShowFooter();

?>
