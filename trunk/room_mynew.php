<?php
require_once('./include/common.php');
require_once( './include/g_func.php' );
SetNoUseCache();

if(!$udata['is_member']) ErrorBox( $str['act_noguest'] );
//$roombar=MakeRoomBar(1);

if( !isset($page) ) $page = 1;
$page = intval( $page );

$ngblock = '';

//未开始的
$sql = "SELECT * FROM {$cfg['tb_newgames']} WHERE host_name='{$udata['u_name']}' LIMIT {$cfg['maxnewgame']}";
//$sql = "SELECT * FROM {$cfg['tb_newgames']} WHERE host_name='{$udata['u_name']}'";
$result = RenDB_Query($sql);

$nglist = '';
$gnum = 0;
if( RenDB_Num_Rows($result) > 0  )
{
	$game_cell = LoadTemplate('g_cell_mynew');
	while( $gdata = RenDB_Fetch_Array( $result ) )
	{
		if( $gdata['app_count'] > 0 )
		{
			//超时检测
			$gtremain = GetNewGameTimeOutInfo();
			if( $gtremain == 0 ) continue;
			$gtremain =Time2HMS($gtremain).HLTxt('*');

			$applist = explode( '|', $gdata['app_list'] ) ;
			//array_shift( $applist );

			$gchallenge = "<select name=\"tarname\" onChange=\"document.mn_$gnum.submit()\"><option>====== ($gdata[app_count]) ======</option>";
			foreach( $applist as $k=>$v )
			{
				$v = explode(',', $v );
				if(In_Names($v[0],$udata['friends'])) $sty='style="background:#9999ff"';
				else if(In_Names($v[0],$udata['blacklist'])) $sty='style="background:#999999"';
				else $sty='';
				$gchallenge.="<option $sty value=\"$v[0]\">$v[0]($v[1]) {$v[2]}% </option>";
			}
			$gchallenge .= '</select>';
		}
		else
		{
			$gchallenge = '-';
			$gtremain = '-';
		}

		$grules=$cfg['rules'][$gdata['rules']];

		switch($gdata['host_color'])
		{
		case 0:$gcolor = '<img src="./images/white.gif">'; break;
		case 1:$gcolor = '<img src="./images/black.gif">'; break;
		default: $gcolor ='随机';
		}
		$skillmin=$udata['skill']-$gdata['skill_range'];
		$skillmax=$udata['skill']+$gdata['skill_range'];
		$greq =$gdata['skill_range']==-1?'':"($skillmin-$skillmax) ";
		$greq.= $gdata['tout_max']>=100?'':"$gdata[tout_max]%";
		if($greq=='')$greq='-';

		$gtimelimit=Time2HMS($gdata['add_time']);
		if($gdata['step_time'])$gtimelimit.='<br />'.Time2HMS($gdata['step_time']);

		eval( "\$nglist .= \"$game_cell \";");
		$gnum ++;
	}
}

if($gnum==0)  $nglist="<tr bgcolor=\"$color[cell]\"><td colspan=\"8\">(空)</td></tr>";

ShowHeader('<img src="./images/renju_new.gif" /> 我的新桌');
eval ('echo "'.LoadTemplate('room_mynew').'";');
ShowFooter();

?>