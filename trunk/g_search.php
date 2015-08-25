<?php
define('RBB_NO_GZIP',true);
require_once('./include/common.php');

if( !isset($action) || !in_array( $action, array('input','list') ) )
	ErrorBox( $str['act_err'] );

if(!$udata['is_member']) ErrorBox($str['act_noguest']);

switch($action)
{
case 'input':
	$openingoption='';
	for($i=1;$i<=26;$i++)
		$openingoption.="<option value=\"$i\">$openingname[$i]</option>";
	ShowHeader('<img src="./images/search.gif" /> 搜索棋局');
	eval ('echo "'.LoadTemplate('g_search_form').'";');
	ShowFooter();
	break;
case 'list':

	if(!isset($rules))$rules=-1;
	if(!isset($search_time))$search_time=-1;
	if(!isset($opening))$opening=-1;
	if(!isset($search_all_user))$search_all_user=1;
	if(!isset($u_name))$u_name='';
	if(!isset($game_status))$game_status=0;
	if(!isset($cp_id))$cp_id=0;
	if(!isset($group_id))$group_id=0;
	if(!isset($packdown)||$packdown!='1')$packdown=0;

	$rules=intval($rules);
	$timeline=$nowtime-$search_time*60;
	$opening=intval($opening);
	$search_all_user=$search_all_user?1:0;
	if(!$search_all_user && $u_name=='')
		MessageBox('请输入用户名');
	$game_status=intval($game_status);
	$cp_id=intval($cp_id);
	$group_id=intval($group_id);


	//生成SQL
	$order_sql='gid DESC';
	$where_sql=array();
	if($rules!=-1) $where_sql[]="rules='$rules'";
	if($search_time!=-1) $where_sql[]="startdate>='$timeline'";
	if($opening!=-1) $where_sql[]="opening='$opening'";
	if(!$search_all_user) 
	{
		$where_sql[]="(b_name='$u_name' OR w_name='$u_name')";
		//$order_sql="startdate>='$nowtime',status>0, turn_name<>'$udata[u_name]',l_time";
		$order_sql="startdate>='$nowtime',status>0, turn_name<>'$udata[u_name]',l_time DESC";
		//if($game_status==2) $order_sql.=' DESC';
	}

	switch($game_status)
	{
	case 1: $where_sql[]="status=0";break;
	case 2: $where_sql[]="status>0";break;
	case 3: $where_sql[]="status=1";break;
	case 4: $where_sql[]="status=2";break;
	case 5: $where_sql[]="status IN(3,5,7)";break;
	case 6: $where_sql[]="status IN(1,4,6,8)";break;
	}

	if($cp_id)$where_sql[]="cp_id='$cp_id'";
	if($group_id)$where_sql[]="group_id='$group_id'";

	if(!empty($where_sql)) $where_sql=' WHERE '.implode(' AND ',$where_sql);
	else $where_sql='';

	//$tablename=$old_game?$cfg['tb_oldgames']:$cfg['tb_games'];

	
	//$sql="SELECT * FROM `$cfg[tb_games]` $where_sql ORDER BY gid DESC LIMIT";

	//echo "$row[0]";
	if($packdown)
	{
		require_once('./include/tar.class.php');
		header("Content-type: /tar.gz");
		$filename=md5(uniqid(rand()));
		header("Content-Disposition: attachment; filename=$filename.tar.gz");
		$tar=new tar();

		$sql="SELECT * FROM `$cfg[tb_games]` $where_sql";
		$result = RenDB_Query($sql);
		while( $gdata=RenDB_Fetch_Array( $result ) )
		{
			$tar->addPosFile($gdata);
		}
		$tar->addInfo('Generated by RBB, '.TimeToDate(time(),true));
		$tar->echoTar();
	}
	else
	{
		SetNoUseCache();
		$sql = "SELECT COUNT(*) FROM `$cfg[tb_games]` $where_sql";
		$result=RenDB_Query($sql);
		$row=RenDB_Fetch_Row( $result );
		if( !isset($page) ) $page = 1;
		$page=intval( $page );

		$encodename=urlencode($u_name);
		$pageinfo=MakePageBar( "g_search.php?action=list&rules=$rules&search_time=$search_time&opening=$opening&search_all_user=$search_all_user&u_name=$encodename&game_status=$game_status&cp_id=$cp_id&group_id=$group_id", $row[0], $cfg['gperpage'], $page );
		$packdownlink=$row[0]?"[<a href=\"g_search.php?action=list&rules=$rules&search_time=$search_time&opening=$opening&search_all_user=$search_all_user&u_name=$encodename&game_status=$game_status&cp_id=$cp_id&group_id=$group_id&packdown=1\">全部打包下载</a>]"
			:'';

		$sql="SELECT * FROM `$cfg[tb_games]` $where_sql ORDER BY $order_sql LIMIT {$pageinfo['start']},{$cfg['gperpage']}";
		$result = RenDB_Query($sql);
		$glist = '';
		$gnum = 0;

		if( RenDB_Num_Rows($result) > 0  )
		{
			require_once('./include/g_func.php');
			$game_cell = LoadTemplate('g_cell');
			while( $gdata = RenDB_Fetch_Array( $result ) )
			{
				$mcount = $gdata['mcount'];
				$curside = ( $mcount + 1 ) % 2;
				$times[0] = $gdata['w_time'];
				$times[1] = $gdata['b_time'];
				$draws[0] = $gdata['w_draw'];
				$draws[1] = $gdata['b_draw'];
				$undos[0] = $gdata['w_undo'];
				$undos[1] = $gdata['b_undo'];

				if( IsSameName( $udata['u_name'], $gdata['b_name']))
					$myside=1;
				else if(IsSameName( $udata['u_name'], $gdata['w_name']))
					$myside = 0;
				else $myside=-1;

				$turnside = GetTurnSide();
				if( ($timeinfo=GetTimeOutInfo()) === true ) continue;
			
				$gidtxt=$gdata['cp_id']?($gdata['startdate']>$nowtime)?$gdata['gid'].HLTxt('*'):HLTxt($gdata['gid']):$gdata['gid'];
				$gidtxt="<a href=\"g_view.php?gid=$gdata[gid]\">$gidtxt</a>";
				
				$grules=$cfg['rules'][$gdata['rules']];


				$gblack =$gdata['b_name'];
				$gwhite =$gdata['w_name'];
				if($gdata['status'])
				{
					$gturn='';
					$gtimelimit='finished';
					switch( $gdata['status'] )
					{
					case 2:
						$gblack=HLTxt($gblack,1);
						$gwhite=HLTxt($gwhite,1);
						break;
					case 3:
					case 5:
					case 7:
						$gblack=HLTxt($gblack);
						break;

					case 1:
					case 4:
					case 6:
					case 8:
						$gwhite=HLTxt($gwhite);
						break;
					}
					/*
					$gtimelimit=Time2HMS($gdata['add_time']);
					if($gdata['step_time']>0)
						$gtimelimit.='/'.Time2HMS($gdata['step_time']);
						*/
				}
				else//未开始或进行中
				{
					$gturn=$gdata['turn_name'];
					if($gdata['startdate']>$nowtime)
					{
							//$gtimelimit=Time2HMS($gdata['add_time']);
						$gtimelimit='nostart';
					}
					else if($myside==$turnside)//自己
					{
						$gturn=HLTxt($gturn);

					//	else
					//	{
						//if($gdata['step_time']>0)
							//$timeinfo[0]=min($timeinfo[0],$timeinfo[1]);
						$gtimelimit=Time2HMS($timeinfo[0]);
						if($gdata['step_time']>0)
							$gtimelimit.='<br />'.Time2HMS($timeinfo[1]);
						$gtimelimit.=HLTxt('*');
					//	}
					}
					else if($myside!=-1)//对手
					{
						$gtimelimit=Time2HMS($times[$myside]);
						//if($gdata['step_time']>0)
						//	$gtimelimit.='/'.Time2HMS($gdata['step_time']);
					}
					else //别人
					{
						//if($undos[!$myside]) $gturn.=HLTxt('(-)');
						//if($draws[!$myside]) $gturn.=HLTxt('(+)',1);
						//$gtimelimit=Time2HMS($times[$turnside]);
						$gtimelimit=Time2HMS($timeinfo[0]);
						if($gdata['step_time']>0)
							$gtimelimit.='<br />'.Time2HMS($timeinfo[1]);
					}
				}
			
				$gblack = '<a href="m_view.php?&name='.urlencode($gdata['b_name'])."\">$gblack</a>";
				$gwhite = '<a href="m_view.php?&name='.urlencode($gdata['w_name'])."\">$gwhite</a>";
				
				if($gturn=='') $gturn='-';
				else $gturn='<a href="m_view.php?&name='.urlencode($gdata['turn_name'])."\">$gturn</a>";

				if($turnside!=-1)
				{
					if($undos[!$myside]) $gturn.=HLTxt('(悔)');
					if($draws[!$myside]) $gturn.=HLTxt('(和)',1);
				}


				$gopening=$gdata['rules']<=1?$openingname[$gdata['opening']]:'-';

				eval( "\$glist .= \"$game_cell \";");
				$gnum ++;
			}
		}

		if($gnum==0) $glist  = "<tr bgcolor=\"$color[cell]\"><td colspan=\"8\">没找到</td></tr>";


		//echo sprintf ( "%01.3f" , GetMicrotime() - $mt0 );

		ShowHeader('<img src="./images/renju.gif" /> 搜索棋局');
		eval ('echo "'.LoadTemplate('g_search_result').'";');
		ShowFooter();
	}
	break;
}

?>