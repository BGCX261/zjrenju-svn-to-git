<?php
require_once('./include/common.php');
require_once( './include/g_func.php' );
SetNoUseCache();

if(!$udata['is_member']) ErrorBox( $str['act_noguest'] );

//$roombar=MakeRoomBar(2);

if( !isset($page) ) $page = 1;
$page = intval( $page );

$sql = "SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE b_name='$udata[u_name]' OR w_name='$udata[u_name]'";
$result=RenDB_Query($sql);
$row=RenDB_Fetch_Row( $result );

$pageinfo = MakePageBar( "room_my.php?", $row[0], $cfg['gperpage'], $page );

$sql = "SELECT * FROM $cfg[tb_games] WHERE b_name='$udata[u_name]' OR w_name='$udata[u_name]' ORDER BY startdate>='$nowtime',status>0,IF(turn_name='$udata[u_name]',0,1) LIMIT {$pageinfo['start']},{$cfg['gperpage']}";

$result = RenDB_Query($sql);
$glist = '';
$gnum = 0;
$shownum = 0;

if( RenDB_Num_Rows($result) > 0  )
{
	require_once( './include/g_func.php' );
	$game_cell = LoadTemplate('g_cell_my');
	//$nowtime = time();
	while( $gdata = RenDB_Fetch_Array( $result ) )
	{
		$gnum ++;
		$mcount = $gdata['mcount'];
		$curside = ( $mcount + 1 ) % 2;
		$times[0] = $gdata['w_time'];
		$times[1] = $gdata['b_time'];
		$draws[0] = $gdata['w_draw'];
		$draws[1] = $gdata['b_draw'];
		$undos[0] = $gdata['w_undo'];
		$undos[1] = $gdata['b_undo'];

		if( IsSameName( $udata['u_name'], $gdata['b_name'] ) )
			$myside = 1;
		else $myside = 0;
		$turnside = GetTurnSide();

		if(!$gdata['status'])
			$timeinfo = GetTimeOutInfo();
		//if( $timeinfo === true )continue;

		$gidtxt=$gdata['cp_id']?HLTxt($gdata['gid']):$gdata['gid'];
		$grules= $gdata['rules']==1 ? 'RIF':'Sakata';
		
		if(IsSameName($gdata['b_name'],$udata['u_name']))
		{
			$gcolor='<img src="./images/black.gif" alt="黑">';
			$gopp = MemberLink($gdata['w_name']);
		}
		else
		{
			$gcolor='<img src="./images/white.gif" alt="白">'; 
			$gopp = MemberLink($gdata['b_name']);
		}


		$gblack = MemberLink( $gdata['b_name'] );
		//if( $gdata['w_name'] !='')
		$gwhite = MemberLink( $gdata['w_name'] );
		$gmcount = $mcount;


		//$bgcolor = $color[ $gnum % 2 ];
		$glink = "<a href=\"g_view.php?gid={$gdata['gid']}\">查看</a>";

		if($gdata['startdate']>$nowtime)
		{
			$ginfo='还未开始';
			$gtremain='开始时间: '.TimeToDate($gdata['startdate']);
			$gtstep='';

		}
		elseif( $gdata['status'])
		{
			$ginfo='已经结束';
			switch( $gdata['status'] )
			{
			case 1:$gtremain='黑禁手';break;
			case 2:$gtremain='和棋';break;
			case 3:$gtremain='黑胜';break;
			case 4:$gtremain='白胜';break;
			case 5:$gtremain='白投子';break;
			case 6:$gtremain='黑投子';break;
			case 7:$gtremain='白超时';break;
			case 8:$gtremain='黑超时';
			}
			$gtstep='';
		}
		else if( $turnside == $myside )
		{
			$ginfo=HLTxt('该你走');
			$gtremain = //$timeinfo[1] ==0 ? 
				'剩时: '.Time2HMS($timeinfo[0]).HLTxt(' *');
			$gtstep = $gdata['step_time']?'本步: '.Time2HMS($timeinfo[1]).HLTxt(' *'):'';
		}
		else 
		{
			$ginfo='该对手走<br />';
			if( $undos[!$myside] > 0 ) $ginfo.=HLTxt('悔? ');
			if( $draws[!$myside] > 0 ) $ginfo.=HLTxt('和? ');
			$gtremain='剩时: '.Time2HMS($times[$myside]);
			$gtstep='';
		}

		$shownum ++;
		eval( "\$glist .= \"$game_cell \";");
	}
}
if($shownum==0)
	$glist="<tr bgcolor=\"$color[cell]\"><td colspan=\"8\">(空)</td></tr>";

ShowHeader('<img src="./images/renju.gif" /> 我的棋局');
eval ('echo "'.LoadTemplate('room_my').'";');
ShowFooter();

?>