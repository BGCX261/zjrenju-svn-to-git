<?php
require_once('./include/common.php');
require_once('./include/g_func.php');
SetNoUseCache();

if( !$udata['is_member'] ) ErrorBox($str['act_noguest']);

//$roombar=MakeRoomBar(4);
if( !isset($page) ) $page = 1;
$page = intval( $page );
if(!isset($byname))$byname='';
$encoded_byname=urlencode($byname);

$showall=isset($showall)?intval($showall):0;

//规则选择
if(!isset($rules)||$rules<-1||$rules>2)$rules=-1;
$rules=intval($rules);
$ruleslink=$rules==-1?HLTxt($str['all']):"<a href=\"room_new.php?rules=-1&showall=$showall&page=1&byname=$encoded_byname\">$str[all]</a>";
$ruleslink.='|';
foreach($cfg['rules'] as $k=>$v)
{
	$ruleslink.=$rules==$k?HLTxt($v):"<a href=\"room_new.php?rules=$k&showall=$showall&page=1&byname=$encoded_byname\">$v</a>";
	$ruleslink.='|';
}

$mytout = GetBBTout($udata);
if($showall) $cond='';
else
{
	$cond= "WHERE (ng.skill_range=-1 OR '$udata[skill]'>=m.skill-ng.skill_range AND  '$udata[skill]'<=m.skill+ng.skill_range) AND tout_max>='$mytout' AND host_name<>'$udata[u_name]' AND u_ip<>'$userip'";
	if($byname!='') $cond.=" AND host_name='{$byname}' ";
	if($rules!=-1) $cond.=" AND rules='$rules'";
}

//棋局总数
$sql = "SELECT COUNT(*) FROM `$cfg[tb_newgames]` AS ng LEFT JOIN `$cfg[tb_members]` AS m ON m.u_name=ng.host_name {$cond}";
$result = RenDB_Query($sql);
$row = RenDB_Fetch_Row( $result );

$plprfix="room_new.php?rules=$rules&showall=$showall";
if($byname!='')$plprfix.="&byname=$encoded_byname";
$pageinfo =  MakePageBar( $plprfix, $row[0], $cfg['gperpage'], $page );

//显示内容
$sql = "SELECT ng.*,m.u_name,m.skill,m.g_w,m.g_d,m.g_l,m.g_to FROM `$cfg[tb_newgames]` AS ng LEFT JOIN `$cfg[tb_members]` AS m ON m.u_name=ng.host_name {$cond} ORDER BY app_count LIMIT {$pageinfo['start']},{$cfg['gperpage']}";
$result = RenDB_Query($sql);
$glist = '';
$gnum = 0;

if( RenDB_Num_Rows($result) > 0  )
{
	$game_cell = LoadTemplate('g_cell_new');
	//$nowtime = time();
	while( $gdata = RenDB_Fetch_Array( $result ) )
	{
		if($gdata['app_count'] > 0)
		{
			$gtremain = GetNewGameTimeOutInfo();
			if( $gtremain == 0 ) continue;
			$gtremain = Time2HMS($gtremain).HLTxt('*');
		}
		else $gtremain = '-';

		$grules=$cfg['rules'][$gdata['rules']];

		$applist = explode('|',$gdata['app_list']);
		//$host = explode(',',$applist[0]);
		$tout=GetBBTout($gdata);
		$host = MemberLink($gdata['u_name'])."($gdata[skill]) <b>$tout%</b>";
		
		if( IsSameName( $gdata['host_name'] , $udata['u_name'] )
			||$gdata['u_ip']==$userip)
		{
			$challenger = "-";
		}
		elseif( $gdata['app_count'] >= $cfg['maxapply'] )
		{
			$challenger = "(满)";
		}
		elseif( $mytout <= $gdata['tout_max'] && 
			($gdata['skill_range']==-1 || 
			$udata['skill']>=$gdata['skill']-$gdata['skill_range'] && 
			$udata['skill']<=$gdata['skill']+$gdata['skill_range']))
		{
			$inapp = false;
			if( $gdata['app_count'] > 0)
				foreach( $applist as $k=>$v )
				{
					$v = explode(',', $v );
					if( IsSameName($v[0],$udata['u_name']))
						$inapp = true;
				}

			$challenger = $inapp ? '(joined)' : MakeBBButton("JavaScript:JoinGame('$gdata[gid]')",'join');
		}
		else
		{
			$challenger = "-";
		}

		$gtimelimit=Time2HMS($gdata['add_time']);
		if($gdata['step_time'])$gtimelimit.='<br />'.Time2HMS($gdata['step_time']);

		if( $gdata['host_color'] >= 1)
		{
			$gblack = $host;
			$gwhite = $challenger;
			if( $gdata['host_color'] >1 )
				$gblack=HLTxt('*').$gblack;
		}
		else
		{
			$gblack = $challenger;
			$gwhite = $host;
		}

		eval( "\$glist .= \"$game_cell\";");
		$gnum ++;
	}
}

if($gnum==0)  $glist= "<tr bgcolor=\"$color[cell]\"><td colspan=\"7\">(Empty)</td></tr>";

ShowHeader('<img src="./images/renju_new.gif" /> 查找新桌');
eval ('echo "'.LoadTemplate('room_new').'";');
ShowFooter();

?>
