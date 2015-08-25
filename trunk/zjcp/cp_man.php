<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');

if(!isset($action)) $action='list';
if(!in_array($action,array('list','show','update','new1','new2','del','makepage')))
	ACP_MessageBox($str['act_err']);

switch($action)
{
case 'list':
	SetNoUseCache();
	
	if( !isset($page) ) $page = 1;
	$page=intval( $page );

	$sql = "SELECT COUNT(*) FROM `$cfg[tb_competitions]`";
	$result = RenDB_Query($sql);
	$row = RenDB_Fetch_Row( $result );
	$pageinfo = MakePageBar( "index.php?mode=cp_man&action=list", $row[0], $cfg['gperpage'], $page );


	$sql="SELECT * FROM `$cfg[tb_competitions]` ORDER BY cp_id DESC LIMIT {$pageinfo['start']},{$cfg['gperpage']}";

	$result = RenDB_Query($sql);
	$cplist='';
	if( RenDB_Num_Rows($result) > 0  )
	{
		while($cpdata=RenDB_Fetch_Array( $result ) )
		{
			$cplist.="<tr bgcolor=\"$color[cell]\"><td>";
			$cplist.="[<a href=\"JavaScript:ConfirmDelCP('$cpdata[cp_id]')\">".HLTxt('删')."</a>] ";
			$cplist.="<a href=\"index.php?mode=cp_man&action=show&cp_id=$cpdata[cp_id]\">$cpdata[cp_id]: $cpdata[cp_name]</a> <br />时间: ";
			//$cplist.='<hr />';
			//$cplist.=$cpdata['description'];
			$cplist.=TimeToDate($cpdata['starttime']);
			
			if($cpdata['endtime'])
				$cplist.=' - '.TimeToDate($cpdata['endtime']);
			//if(!file_exists("../cpdata/$cpdata[cp_id].html"))
			//	$cplist.=' (没有报表)';
			//else $cplist.=" [<a target=\"_blank\" href=\"../cpdata/$cpdata[cp_id].html\">查看报表</a>]";

			$cplist.=" [<a  href=\"index.php?mode=cp_man&action=makepage&cp_id=$cpdata[cp_id]\">生成(刷新)报表</a>]";

			$cplist.="</td></tr>";

		}
	}
	else $cplist  = "<tr bgcolor=\"$color[cell]\"><td>(空)</td></tr>";

	ACP_ShowHeader('比赛设置');
	eval ('echo "'.LoadTemplate('cp_man').'";');
	ACP_ShowFooter();
	break;
case 'show':
	if(!isset($cp_id)) 
		ACP_MessageBox($str['act_err']);
	$cp_id=intval($cp_id);

	$sql ="SELECT * FROM `$cfg[tb_competitions]` WHERE cp_id='$cp_id' LIMIT 1";
	$result = RenDB_Query($sql);
	if( RenDB_Num_Rows($result) != 1 )
		ACP_MessageBox('比赛不存在');
	$cpdata=RenDB_Fetch_Array($result);

	//$cpsta_sel=array('','','');
	//$cpsta_sel[$cpdata['cp_status']]='checked';
	$str_enddate=$cpdata['endtime']?date('H:i:s m/d/Y',$cpdata['endtime']):'';

	ACP_ShowHeader('比赛管理');
	eval ('echo "'.LoadTemplate('cp_man_1').'";');
	ACP_ShowFooter();
	break;
case 'update':
	SetNoUseCache();
	if(!isset($cp_id,$cp_name,$description,$enddate))
		ACP_MessageBox($str['act_err']);
	
	$cp_id = intval( $cp_id );
	if($enddate=='')$enddate=0;
	else 
	{
		if(($enddate=strtotime($enddate))===-1)
			ACP_MessageBox('时间不正确');
	}
	//if($cp_status<0||$cp_status>2)$cp_status=0;
	
	require_once('../include/txt_func.php');
	$description=CSubStr($description,0,65533);
	$cp_name=CSubStr($cp_name,0,253);
	
	$sql ="UPDATE `$cfg[tb_competitions]` SET ";
	$sql.="cp_name='$cp_name', ";
	$sql.="description='$description', ";
	$sql.="endtime='$enddate' ";
	$sql.="WHERE cp_id='$cp_id' LIMIT 1";
	$result = RenDB_Query($sql);
	if( !RenDB_Affected_Rows())
		ACP_MessageBox('更改失败，可能是数据并没有改变');

	ACP_WriteLog("更改比赛 ID:$cp_id");

	Header("Location: index.php?mode=cp_man");
	exit();
	break;
case 'del':
	if(!isset($cp_id)) 
		ACP_MessageBox($str['act_err']);
	$cp_id=intval($cp_id);
	$sql="DELETE FROM `$cfg[tb_competitions]` WHERE cp_id='$cp_id'";
	RenDB_Query($sql,true);
	$sql="DELETE FROM `$cfg[tb_groups]` WHERE cp_id='$cp_id'";
	RenDB_Query($sql,true);
	$sql="DELETE FROM `$cfg[tb_players]` WHERE cp_id='$cp_id'";
	RenDB_Query($sql,true);
	
	$sql="DELETE FROM `$cfg[tb_games]` WHERE cp_id='$cp_id' AND mcount=0";
	RenDB_Query($sql,true);

 	$sql="UPDATE `$cfg[tb_games]` SET cp_id=0,group_id=0 WHERE cp_id='$cp_id'";
	RenDB_Query($sql,true);

	ACP_WriteLog("删除比赛 ID:$cp_id");
	Header("Location: index.php?mode=cp_man");
	exit();
	break;
/*case 'add':
	$sql="INSERT INTO `$cfg[tb_competitions]` SET cp_name='(新的比赛)' ";
	$result = RenDB_Query($sql);
	if( RenDB_Insert_id())
	{
		ACP_WriteLog("添加比赛 ID:".RenDB_Insert_id());
		Header("Location: index.php?mode=cp_man");
		exit();
	}
	ACP_MessageBox('添加失败');
	break;
*/
case 'new1':
	$str_nowtime=date('H:i:s m/d/Y',$nowtime);
	ACP_ShowHeader('新建比赛');
	eval ('echo "'.LoadTemplate('cp_form').'";');
	ACP_ShowFooter();
	break;

case 'new2':
	if(!isset($cp_name,$description,$group_player,$timestep,$timeadd,$startdate,$rules))
		ACP_MessageBox($str['act_err']);

	$rules = intval( $rules );
	if( $rules < 0 || $rules > 2 ) ACP_MessageBox('规则不正确');

	if( $timestep<0 ) $timestep = 0;
	if( ($timestep > 21600 || $timestep < 1440) && $timestep!=0)
		ACP_MessageBox('每步限时应该在1天-15天之间');
	if($timeadd > 525600 || $timeadd< 10 ) 
		ACP_MessageBox('总限时应该在10分钟-365天之间');
	if($timestep>$timeadd)$timestep=$timeadd;

	$timeadd *= 60;
	$timestep *= 60;

	$startdate=strtotime($startdate);
	if($startdate<$nowtime)
		ACP_MessageBox('开始时间不能早于现在');

	require_once('../include/txt_func.php');
	$description=CSubStr($description,0,65533);
	$cp_name=CSubStr($cp_name,0,253);

	$group_player=str_replace("\r\n","\n",$group_player);
	$group_player=explode("\n\n",$group_player);

	require_once('../include/m_func.php');
	$players=array();
	foreach($group_player as $k=>$v)
	{
		$v=explode("\n",$v);
		foreach($v as $k1=>$v1)
		{
			$v1=trim($v1);
			$v[$k1]=$v1;
			if($v1=='')unset($v[$k1]);
			elseif(!IsMember($v1)) ACP_MessageBox("{$v1}不是注册用户");
		}
		if(count($v)==0) continue;
		if(count($v)<2) ACP_MessageBox(chr(65+$k).'组人数不够');
		$players[]=$v;
	}


	if(count($players)==0)
		ACP_MessageBox("没有选手?");
		
	$allplayer=array();
	foreach($players AS $v)
		foreach($v AS $v1)
			$allplayer[]=$v1;

	foreach($allplayer AS $k=>$v)
		foreach($allplayer AS $k1=>$v1)
			if(IsSameName($v1,$v) && $k!=$k1)
				ACP_MessageBox("名单重复: $v");
	/*
	echo('<pre>');
	print_r($allplayer);
	echo('</pre>');
	die();
	*/

	//创建比赛
	$sql="INSERT INTO `$cfg[tb_competitions]` SET cp_name='$cp_name',description='$description',starttime='$startdate' ";
	RenDB_Query($sql);
	if( !($cp_id=RenDB_Insert_id())) ACP_MessageBox('创建比赛失败');

	$ptse=count($allplayer)-1;
	$ptsm=$ptse*2;
	foreach($players as $k=>$v)
	{
		//创建组
		$sql="INSERT INTO `$cfg[tb_groups]` SET cp_id='$cp_id' ";
		RenDB_Query($sql);
		if( !($group_id=RenDB_Insert_id())) 
			ACP_MessageBox('创建分组失败');
		foreach($v as $v1)
		{
			//创建选手
			$sql="INSERT INTO `$cfg[tb_players]` SET cp_id='$cp_id', group_id='$group_id',u_name='$v1',ptsm='$ptsm',ptse='$ptse'";
			RenDB_Query($sql);
			if( !($player_id=RenDB_Insert_id())) 
				ACP_MessageBox('创建选手失败');
			foreach($v as $v2)
			{
				if(IsSameName($v2,$v1)) continue;
				//创建对称的两盘棋
				$sql ="INSERT INTO `$cfg[tb_games]` SET cp_id='$cp_id', group_id='$group_id',rules='$rules', b_name='$v1', w_name='$v2', b_time='$timeadd', w_time='$timeadd', l_time='$startdate', step_time='$timestep', add_time='$timeadd', startdate='$startdate', turn_name='$v1' ";
				RenDB_Query($sql);
				if( !($gid=RenDB_Insert_id())) 
					ACP_MessageBox('创建棋局失败');
			}
		}
	}
	Header("Location: index.php?mode=cp_man");
	exit();

case 'makepage':
	if(!isset($cp_id))ACP_MessageBox($str['act_err']);
	$cp_id=intval($cp_id);


	$sql ="SELECT * FROM `$cfg[tb_competitions]` WHERE cp_id='$cp_id' LIMIT 1";
	$result = RenDB_Query($sql);
	if( RenDB_Num_Rows($result) != 1 )
		ACP_MessageBox('比赛不存在');
	$cpdata=RenDB_Fetch_Array($result);

	//eval ('$buf= "'.LoadTemplate('cp_page_head').'";');
	
	$sql="SELECT gid FROM `$cfg[tb_games]` WHERE cp_id='$cp_id' AND status=0 LIMIT 1";
	$result3=RenDB_Query($sql);
	if(RenDB_Num_Rows($result3))
		$str_status='ongoing';
	else 
	{
		$str_status='finished';
		if(!$cpdata['endtime'])
		{
			$sql="UPDATE `$cfg[tb_competitions]` SET endtime='$nowtime' WHERE cp_id='$cp_id' ";
			RenDB_Query($sql,true);
		}
	}

	$sql="SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE cp_id='$cp_id'";
	$result3=RenDB_Query($sql);
	$row=RenDB_Fetch_Row($result3);
	$total=$row[0];

	$sql="SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE cp_id='$cp_id' AND status=0";
	$result3=RenDB_Query($sql);
	$row=RenDB_Fetch_Row($result3);
	$finished=$total-$row[0];

	$buf='<table style="word-wrap: break-word" width="100%" border="0" cellpadding="4"><tr><td>';
	$buf.="<h2>$cpdata[cp_name]</h2>";
	$buf.="<h5>".TimeToDate($cpdata['starttime']);
	if($cpdata['endtime'])
		$buf.=' - '.TimeToDate($cpdata['endtime']);
	$buf.="</h5>";
	$buf.="<h5>Totally $total games, $finished finished. [<a href=\"g_search.php?&action=list&cp_id=$cp_id\">view</a>] [<a href=\"g_search.php?&action=list&cp_id=$cp_id&packdown=1\">download</a>]  </h5>";
			
	$buf.='<h5>Last update: '.TimeToDate($nowtime,true).'</h5>';
	$buf.=$cpdata['description'];

	$sql ="SELECT * FROM `$cfg[tb_groups]` WHERE cp_id='$cp_id' ORDER BY group_id";
	$result=RenDB_Query($sql);
	$num=0;
	while($gpdata=RenDB_Fetch_Array($result))
	{
		$sql="SELECT * FROM `$cfg[tb_players]` WHERE group_id='$gpdata[group_id]' ORDER BY player_id";
		$result1=RenDB_Query($sql);
		$players=array();
		while($row=RenDB_Fetch_Array($result1))
			$players[]=$row;
		
		//判断是否结束
		$sql="SELECT gid FROM `$cfg[tb_games]` WHERE group_id='$gpdata[group_id]' AND status=0 LIMIT 1";
		$result3=RenDB_Query($sql);
		if(RenDB_Num_Rows($result3))
			$str_status='ongoing';
		else $str_status='finished';


		$sql="SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE group_id='$gpdata[group_id]'";
		$result3=RenDB_Query($sql);
		$row=RenDB_Fetch_Row($result3);
		$total=$row[0];

		$sql="SELECT COUNT(*) FROM `$cfg[tb_games]` WHERE group_id='$gpdata[group_id]' AND status=0";
		$result3=RenDB_Query($sql);
		$row=RenDB_Fetch_Row($result3);
		$finished=$total-$row[0];
	
		$buf.='<hr width="0" /><table width="100%" border="0"><tr><td>';
		$buf.='<b>Group '.chr(65+$num)." ($str_status)</b>";
		$buf.="</td><td align=\"right\">$finished/$total finished.[<a href=\"g_search.php?&action=list&group_id=$gpdata[group_id]\">view</a>] [<a href=\"g_search.php?&action=list&group_id=$gpdata[group_id]&packdown=1\">download</a>]</td></tr></table>";
		//生成表格
		$buf.="<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"4\" bordercolor=\"$color[border]\">";
		
		//表头
		$buf.='<tr align="center"><td width="30">No</td><td>Name</td>';
		
		foreach($players as $k=>$v)
		{
			$uid=$k+1;
			$buf.="<td>$uid</td>";
		}
			
		$buf.="<td width=\"30\"><a href=\"group_view.php?group_id=$gpdata[group_id]&orderby=0\">Pts</a></td>";
		$buf.="<td width=\"30\"><a href=\"group_view.php?group_id=$gpdata[group_id]&orderby=1\">Berg</a></td>";
		$buf.="<td width=\"30\"><a href=\"group_view.php?group_id=$gpdata[group_id]&orderby=2\">PtsM</a></td>";
		$buf.="<td width=\"30\"><a href=\"group_view.php?group_id=$gpdata[group_id]&orderby=3\">PtsE</a></td>";
		$buf.="<td width=\"30\">Pl</td>";
		$buf.="</tr>";

		foreach($players as $k=>$v)
		{
			$uid=$k+1;
			$g_w=0;
			$g_d=0;
			$g_l=0;
			$pts=0;
			$berg=0;
			$ptsm=$v['points'];
			$ptse=$v['points'];
			$pl=1;


			$buf.="<tr align=\"center\"><td>$uid</td>";
			$buf.='<td>'.MemberLink($v['u_name']).'</td>';
			foreach($players as $k1=>$v1)
			{

				if($v==$v1) $buf.="<td>-</td>";
				else 
				{
					if($v1['points']>$v['points'])$pl++;

					$sql="SELECT * FROM `$cfg[tb_games]` WHERE group_id='$gpdata[group_id]' AND (w_name='$v[u_name]' AND b_name='$v1[u_name]' OR b_name='$v[u_name]' AND w_name='$v1[u_name]') ORDER BY gid LIMIT 2";
					$result3=RenDB_Query($sql);
					$points=0;
					$games='';
					while($gdata=RenDB_Fetch_Array($result3))
					{
						$uid1=$k1+1;
						if($gdata['status'])
						{
							$isblack=IsSameName($gdata['b_name'],$v['u_name']);
							switch($gdata['status'])
							{
							case 2:
								$berg+=$v1['points']/2;
								$points+=0.5;
								$uid1=HLTxt($uid1,1);
								$g_d++;
								break;
							case 3:
							case 5:
							case 7:
								if($isblack)
								{
									$berg+=$v1['points'];
									$points+=1;
									$uid1=HLTxt($uid1);
									$g_w++;
								}
								else $g_l++;
								break;
							case 1:
							case 4:
							case 6:
							case 8:
								if(!$isblack)
								{
									$berg+=$v1['points'];
									$points+=1;
									$uid1=HLTxt($uid1);
									$g_w++;
								}
								else $g_l++;
								break;
							}
							$uid1='<b>'.$uid1.'</b>';
						}
						else
						{
							$ptsm+=1;
							$ptse+=0.5;
						}
						//if($games!='')$games.='|';
						$games.="<br /><a href=\"g_view.php?gid=$gdata[gid]\">($uid1)</a>";						
					}
					$pts+=$points;
					if($points!=0)$points="<b>$points</b>";
					$buf.="<td>$points$games</td>";
				}
			}

			//if($v['berg']!=$berg)//更新berg
			//{
			$sql="UPDATE `$cfg[tb_players]` SET g_w='$g_w',g_d='$g_d',g_l='$g_l',points='$pts', berg='$berg',ptsm='$ptsm',ptse='$ptse' WHERE group_id='$gpdata[group_id]' AND u_name='$v[u_name]' LIMIT 1";
			RenDB_Query($sql,true);
			//}

			$berg=sprintf('%2.1f',$berg);
			$ptsm=sprintf('%2.1f',$ptsm);
			$ptse=sprintf('%2.1f',$ptse);
			$buf.="<td>$pts</td><td>$berg</td><td>$ptsm</td><td>$ptse</td><td>$pl</td>";
			$buf.='</tr>';
		}
		
		$buf.='</table>';
		$num++;
	}

	
	$buf.='</td></tr></table>';
	if(!($fr=fopen("../cpdata/$cpdata[cp_id].html", 'w' )))
		ACP_MessageBox('无法打开文件');
	flock( $fr, LOCK_EX);
	fwrite( $fr, $buf );
	fclose( $fr );
	Header("Location: index.php?mode=cp_man");
	exit();

	break;
}
?>
