<?php
require_once('./include/common.php');
//require_once('./include/g_func.php');

//检查身份
if( !$udata['is_member']) ErrorBox( $str['act_noguest'] );

//检查动作
if( !isset($action) ||!in_array( $action, array('new1','new2') ) )
	ErrorBox( $str['act_err'] );

//$roombar=MakeRoomBar(0);

switch( $action )
{
case 'new1':
	//检查局数限制
	$sql="SELECT COUNT(*) FROM `$cfg[tb_newgames]` WHERE host_name='$udata[u_name]'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	if( $row[0] >= $cfg['maxnewgame'] ) MessageBox("您的新桌已经到了上限($cfg[maxnewgame])");

/*
	$sql="SELECT COUNT(*) FROM $cfg[tb_games] WHERE b_name='$udata[u_name]' OR w_name='$udata[u_name]'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	echo $row[0];
	if( $row[0] >= $cfg['maxgame'] ) MessageBox("您进行中的的棋局已经到了上限($cfg[maxgame])");
*/
	$greate = $cfg['maxnewgame']-$row[0];
	$gnumoption='';
	for( $i=2; $i<= $greate;$i++ )
		$gnumoption.="<option value={$i}>{$i}</option>";

	ShowHeader('<img src="./images/renju_new.gif" /> 创建新局');
	eval ("echo \"".LoadTemplate("g_form")."\";");
	ShowFooter();
	break;
case 'new2':
	if( !isset($timeadd,$timestep,$skill_range,$rules,$gcreate,$maxtout,$hostcolor))
		ErrorBox( $str['act_err'] );

	$sql="SELECT COUNT(*) FROM {$cfg['tb_newgames']} WHERE host_name='{$udata['u_name']}'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	if( $row[0] >= $cfg['maxnewgame'] ) MessageBox("您的新桌已经到了上限($cfg[maxnewgame])");

/*	$sql="SELECT COUNT(*) FROM $cfg[tb_games] WHERE b_name='$udata[u_name]' OR w_name='$udata[u_name]'";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	if( $row[0] >= $cfg['maxgame'] ) MessageBox("您进行中的棋局已经到了上限($cfg[maxgame])");
*/
	$rules = intval( $rules );
	if( $rules < 0 || $rules > 2 ) $rules = 0;
	$timeadd = intval( $timeadd );
	$timestep = intval( $timestep );
	$skill_range = intval( $skill_range );
	$gcreate = intval($gcreate);
	$maxtout = intval($maxtout);
	$hostcolor = intval($hostcolor);

	if( $gcreate < 1 ) $gcreate = 1;
	if( $gcreate > $cfg['maxnewgame'] - $row[0] ) $gcreate = $cfg['maxnewgame'] - $row[0];

	$mytout = GetBBTout( $udata );
	//var_dump($mytout);
	if( $maxtout < $mytout ) $maxtout=$mytout;
	if( $maxtout < 0 || $maxtout > 100 ) $maxtout = 100;
	if( $hostcolor < 0 || $hostcolor >2 ) $hostcolor = 2;

	if( $timestep<0 ) $timestep = 0;
	if( ($timestep > 21600 || $timestep < 1440) && $timestep!=0)
		MessageBox('每步限时应该在1-15天之间');
	if($timeadd > 525600 || $timeadd< 10 ) 
		MessageBox('总限时应该在10分钟-365天之间');
	if($timestep>$timeadd)$timestep=$timeadd;

	$timeadd *= 60;
	$timestep *= 60;

	//$timeadd = intval($timeadd /60);
	//$timestep = intval($timestep /60);

	if($skill_range<-1) $skill_range=max($skill_range,-1);

	if($skill_range>9999) $skill_range=min($skill_range,9999);

	//$myinfo = "$udata[u_name],$udata[skill],$mytout";
	////////////////////////////////////////////////////////////////
	//创建游戏
	//$nowtime = time();
	$sql ="INSERT INTO `$cfg[tb_newgames]` SET ";
	$sql.="rules='$rules', ";
	$sql.="host_name='$udata[u_name]',";
	$sql.="host_color='$hostcolor',";
	$sql.="skill_range='$skill_range',";
	$sql.="tout_max='$maxtout',";
	$sql.="step_time='$timestep',";
	$sql.="add_time='$timeadd',";
	$sql.="u_ip='$userip',";
	$sql.="l_time='$nowtime'";

	//$gcreate = 10000;
	$cinfo = '';
	$created = 0;
	for( $i=1;$i<=$gcreate;$i++)
	{
		$cinfo .= "No.{$i} 创建...";
		$result = RenDB_Query($sql);
		$gid = RenDB_Insert_ID();
		if(	$gid != 0 )
		{
			$created++;
			$cinfo.= "成功! <br />";
		}
		else
		{
			$cinfo.= "失败.<br />";
		}
	}

	$lks = array();
	if( $created )
	{
		$cinfo.= "<br />您创建了{$created}个新局,请等待对手的加入. ";
		$cinfo.= MakeBBButton( "pm_new.php?action=invite1",'邀请朋友',500,220);
		$cinfo.= "<br />";

		$lks[] = array( "查看" , 'room_mynew' );
		if( $row[0] + $gcreate < $cfg['maxnewgame'])
			$lks[] = array( "继续创建" , 'g_new','action'=>'new1' );
	}

	MessageBox( $cinfo , $lks );
	break;
}
?>