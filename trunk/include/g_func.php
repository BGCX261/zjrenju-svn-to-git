<?php


function UpdateGameData( $gdata,$status=0 )
{
	global $cfg,$names;
	$gdata['turn_name']=$names[GetTurnSide()];

	$gdata['moves'] = mysql_escape_string($gdata['moves']);

    $sql ="UPDATE $cfg[tb_games] SET ";
	$sql.="opening='$gdata[opening]',";
	$sql.="b_name='$gdata[b_name]',";
	$sql.="w_name='$gdata[w_name]',";
	$sql.="b_time='$gdata[b_time]',";
	$sql.="w_time='$gdata[w_time]',";
	$sql.="l_time='$gdata[l_time]',";
	$sql.="mcount='$gdata[mcount]',";
	$sql.="moves ='$gdata[moves]',";
	$sql.="step5 ='$gdata[step5]',";
	$sql.="pos5_a='$gdata[pos5_a]',";
	$sql.="pos5_b='$gdata[pos5_b]',";
	$sql.="b_undo='$gdata[b_undo]',";
	$sql.="w_undo='$gdata[w_undo]',";
	$sql.="b_draw='$gdata[b_draw]',";
	$sql.="w_draw='$gdata[w_draw]',";
	$sql.="swaped='$gdata[swaped]',";
	
	if($status)
	{
		$sql.="status='$status',";
		$sql.="turn_name=''";
	}
	else $sql.="turn_name='$gdata[turn_name]'";


	$sql.=" WHERE gid='$gdata[gid]' LIMIT 1";
	RenDB_Query($sql);
	return ( RenDB_Affected_Rows() > 0 );
}

function GetTurnSide()
{
	global $gdata;
	if( $gdata['status']) return -1;

	$curside= 1-$gdata['mcount']%2;
	switch( $gdata['rules'] )
	{
	case 0: //Sakata
		if($gdata['mcount']<3 )return 1;
		if($gdata['mcount']==4)return 0;
		if($gdata['mcount']==5 && !$gdata['swaped'])return 1;
		return $curside ;
	case 1: //RIF
		if($gdata['mcount']<3)return 1;
		if($gdata['mcount']==4)
			return $gdata['step5']==2 ?0:1;
		return $curside ;
		break;
	case 2: //两次交换
		if($gdata['mcount']<3)return 1;
		if($gdata['mcount']==3 && !$gdata['swaped'])return 0;
		if($gdata['mcount']<5)return 1;
		return $curside ;
	}
}

//检查无手两打是否对称
function IsSamePos_5B()
{
	global $gdata,$pos;

	$map1 = array_fill( 0, 14, array_fill( 0, 14, 0 )  );
	$map2 = $map1;
	$l1=15; $r1=-1; $t1=15; $b1=-1;
	for( $i=0; $i<4; $i++)
	{
		$p = ord( $gdata['moves'][$i] ) - 1;
		if( $p>=0 && $p<225 )
		{
			$x = $p % 15;
			$y = intval($p / 15);
			$map1[$x][$y] = 2 - $i%2;
			$l1 = min( $x, $l1 );
			$r1 = max( $x, $r1 );
			$t1 = min( $y, $t1 );
			$b1 = max( $y, $b1 );
		}
	}
	$map2 = $map1;
	$l2=$l1; $r2=$r1; $t2=$t1; $b2=$b1;

	$p = $gdata['pos5_a'] - 1;
	if( $p>=0 && $p<225 )
	{
		$x = $p % 15;
		$y = intval($p / 15);
		$map1[$x][$y] = 1;
		$l1 = min( $x, $l1 );
		$r1 = max( $x, $r1 );
		$t1 = min( $y, $t1 );
		$b1 = max( $y, $b1 );
	}

	$p = $pos - 1;
	if( $p>=0 && $p<225 )
	{
		$x = $p % 15;
		$y = intval($p / 15);
		$map2[$x][$y] = 1;
		$l2 = min( $x, $l2 );
		$r2 = max( $x, $r2 );
		$t2 = min( $y, $t2 );
		$b2 = max( $y, $b2 );
	}

	$w1 = $r1 - $l1;
	$h1 = $b1 - $t1;
	$w2 = $r2 - $l2;
	$h2 = $b2 - $t2;
	//检查棋块大小是否相同
	//var_dump($w1);
	//var_dump($h1);
	if( ($w1 != $w2 || $h1 != $h2 ) && ($w1 != $h2 || $h1 != $w2 ) )
		return false;
	$map1 = TopLeft_Stand( $map1, $l1, $t1, $w1, $h1 );
	$map2 = TopLeft_Stand( $map2, $l2, $t2, $w2, $h2 );

	if( IsSameMap( $map1, $map2, $w1, $h1 ) )
		return true;
	//var_dump($map2);
	$mapt = $map2;
	//左右翻转
	for( $x=0; $x<=$w1; $x++)
	for( $y=0; $y<=$h1; $y++)
		$mapt[$x][$y] = $map2[$w1-$x][$y];

	if( IsSameMap( $map1, $mapt, $w1, $h1 ) )
		return true;

	//上下翻转
	for( $x=0; $x<=$w1; $x++)
	for( $y=0; $y<=$h1; $y++)
		$mapt[$x][$y] = $map2[$x][$h1-$y];

	if( IsSameMap( $map1, $mapt, $w1, $h1 ) )
		return true;

	//上下左右翻转
	for( $x=0; $x<=$w1; $x++)
	for( $y=0; $y<=$h1; $y++)
		$mapt[$x][$y] = $map2[$w1-$x][$h1-$y];

	if( IsSameMap( $map1, $mapt, $w1, $h1 ) )
		return true;
	return false;
}
//检查无手两打是否对称子函数 - 棋块移动到左上角并立起来
function TopLeft_Stand( &$map, $l, $t, &$w, &$h )
{
	if($w>$h) 
	{
		for( $x=0; $x<=$w; $x++)
		for( $y=0; $y<=$h; $y++)
		{
			//if( !isset($map2[$y])) $map2[$y] = array();
			$map2[$y][$x] = $map[$x+$l][$y+$t];
		}
		$temp = $w;
		$w = $h;
		$h = $temp;
	}
	else
	{
		for( $x=0; $x<=$w; $x++)
		for( $y=0; $y<=$h; $y++)
		{
			//if( !isset($map2[$x])) $map2[$x] = array();
			$map2[$x][$y] = $map[$x+$l][$y+$t];
		}
	}
	return $map2;
}
//检查无手两打是否对称子函数 - 判断两个MAP是否相等
function IsSameMap( &$map1, &$map2, $w, $h )
{
	for( $x=0; $x<=$w; $x++)
	for( $y=0; $y<=$h; $y++)
		if( $map1[$x][$y] != $map2[$x][$y]) return false;
	return true;
}

//清除双方的请求
function ClearReqInfo()
{
	global $gdata;
	$gdata['w_undo'] = 0;
	$gdata['b_undo'] = 0;
	$gdata['w_draw'] = 0;
	$gdata['b_draw'] = 0;
}


//结束游戏的子函数
/*
function Add_Win($d)
{
	$d=min($d,500);
	$d=max($d,-500);	
	return round(10-$d/50,1);
}
*/
function Add_Win($dr,$w)
{
	return round(32*($w-(1/(pow(10,(-$dr/400))+1))),1);
}

function EndGame($reason, $show=false)
{
	//if( $reason<1 || $reason > 8 ) die("ERROR:内部错误");
	global $cfg,$gdata;
	ClearReqInfo();

	//步数少且不是比赛--删除
	if( !$gdata['cp_id']&&$gdata['mcount']<=$cfg['open_mcount'])
	{
		$sql ="DELETE FROM `$cfg[tb_games]` WHERE gid='$gdata[gid]'";
		RenDB_Query($sql,true);
		$sql ="DELETE FROM `$cfg[tb_chats]` WHERE gid='$gdata[gid]'";
		RenDB_Query($sql,true);

		//掉线
		if($reason==7)//白超时
		{
			$sql="UPDATE `$cfg[tb_members]` SET g_to=g_to+1 WHERE u_name='$gdata[w_name]'";
			RenDB_Query($sql,true);
		}
		elseif($reason==8)//黑超时
		{
			$sql="UPDATE `$cfg[tb_members]` SET g_to=g_to+1 WHERE u_name='$gdata[b_name]'";
			RenDB_Query($sql,true);
		}

		if($show)
		{
			$lks[0] = array( '查看我的棋局',  'room_my' );
			MessageBox('这盘棋已经结束，但步数太少不予保存',$lks);
		}
		else return;
	}

	UpdateGameData($gdata,$reason);

	//算分
	$sql="SELECT u_name, skill FROM `$cfg[tb_members]` WHERE u_name='$gdata[b_name]' OR u_name='$gdata[w_name]' LIMIT 2";
	$result = RenDB_Query($sql);
	if( RenDB_Num_Rows($result) == 2 )
	{
		$row[0] = RenDB_Fetch_Array($result);
		$row[1] = RenDB_Fetch_Array($result);

		if( IsSameName( $row[0]['u_name'], $gdata['b_name'] ) )
		{
			$b_skill = $row[0]['skill']; 
			$w_skill = $row[1]['skill']; 
		}
		else
		{
			$b_skill = $row[1]['skill']; 
			$w_skill = $row[0]['skill']; 
		}

		switch( $reason )
		{
		case 2: //和棋
			$a=Add_Win($w_skill-$b_skill,0.5);
			$adds[0]=array( $a,0,1,0);
			$adds[1]=array(-$a,0,1,0); //skill,w,d,l
			break;
		case 3:
		case 5://黑胜
		case 7://白超时
			$a=Add_Win($b_skill-$w_skill,1);
			$adds[0]=array(-$a,0,0,1);
			$adds[1]=array( $a,1,0,0); //skill,w,d,l
			break;
		case 4:
		case 6:
		case 1://白胜
		case 8://黑超时
			$a=Add_Win($w_skill-$b_skill,1);
			$adds[0]=array( $a,1,0,0); 
			$adds[1]=array(-$a,0,0,1);
			break;
		}
		$sql="UPDATE `$cfg[tb_members]` SET skill=skill+'{$adds[0][0]}',g_w=g_w+'{$adds[0][1]}',g_d=g_d+'{$adds[0][2]}',g_l=g_l+'{$adds[0][3]}' WHERE u_name='$gdata[w_name]'";
		RenDB_Query($sql,true);
		$sql="UPDATE `$cfg[tb_members]` SET skill=skill+'{$adds[1][0]}',g_w=g_w+'{$adds[1][1]}',g_d=g_d+'{$adds[1][2]}',g_l=g_l+'{$adds[1][3]}' WHERE u_name='$gdata[b_name]'";
		RenDB_Query($sql,true);
	}

	/*
	if($gdata['cp_id'])
	{
		switch( $reason )
		{
		case 2: //和棋
			$adds[0]=array(0.5,0,1,0,-0.5,0);
			$adds[1]=array(0.5,0,1,0,-0.5,0);
			break;
		case 3:
		case 5://黑胜
		case 7://白超时
			$adds[0]=array(0,0,0,1,-1,-0.5);
			$adds[1]=array(1,1,0,0,0,0.5);
			break;
		case 4:
		case 6:
		case 8://黑超时
		case 1://白胜
			$adds[0]=array(1,1,0,0,0,0.5);
			$adds[1]=array(0,0,0,1,-1,-0.5);
			break;
		}

		$sql="UPDATE `$cfg[tb_players]` SET points=points+'{$adds[0][0]}', g_w=g_w+'{$adds[0][1]}', g_d=g_d+'{$adds[0][2]}', g_l=g_l+'{$adds[0][3]}',ptsm=ptsm+'{$adds[0][4]}',ptse=ptse+'{$adds[0][5]}' WHERE group_id='$gdata[group_id]' AND u_name='$gdata[w_name]' LIMIT 1";
		RenDB_Query($sql,true);
		$sql="UPDATE `$cfg[tb_players]` SET points=points+'{$adds[1][0]}', g_w=g_w+'{$adds[1][1]}', g_d=g_d+'{$adds[1][2]}', g_l=g_l+'{$adds[1][3]}',ptsm=ptsm+'{$adds[1][4]}',ptse=ptse+'{$adds[1][5]}' WHERE group_id='$gdata[group_id]' AND u_name='$gdata[b_name]' LIMIT 1";
		RenDB_Query($sql,true);
	}*/

	if( $show) 
	{
		header("Location: g_view.php?gid=$gdata[gid]");
		exit();
	}
}



function GetTimeOutInfo()
{
	global $turnside, $gdata, $times,$nowtime;
	if( $gdata['status']||$gdata['startdate']>$nowtime) return false;

	//超时检测
	if( $turnside!=-1 )
	{
		$tspend = $nowtime - $gdata['l_time'];//实际耗时
		

		$tstep=$gdata['step_time']?$gdata['step_time']-$tspend:'9999999999';
		$tremain=$times[$turnside]-$tspend;
		$tstep=min($tstep,$tremain);
		if($tstep<=0 ||$tremain<=0)
		{
			EndGame( 7+$turnside );
			return true;
		}
		else return array($tremain,$tstep);
	}
	return false;
}

function GetNewGameTimeOutInfo()
{
	global $gdata,$cfg,$nowtime;
	$tlimit = $gdata['step_time']>0 ? $gdata['step_time'] : $gdata['add_time'];
	if( $tlimit < $cfg['min_opentime'] ) $tlimit = $cfg['min_opentime'];
	if( $tlimit > $cfg['max_opentime'] ) $tlimit = $cfg['max_opentime'];
	$tremain = $gdata['l_time'] + $tlimit-$nowtime;
	if( $tremain <= 0 ) 
	{
		$sql ="DELETE FROM {$cfg['tb_newgames']} WHERE gid='{$gdata['gid']}'";
		RenDB_Query($sql,true);
		return 0;
	}
	return $tremain;
}

function GetOpening(&$moves)
{
	if(strlen($moves)<3)return 0;
	$p=ord($moves{0});
	if($p!=113) return 0;
	$p=ord($moves{1});
	if($p==113) return 0;
	$x=($p-1)%15;
	$y=intval(($p-1)/15);
	if($x<6||$x>8||$y<6||$y>8) return 0;
	
	$isdo=($x==7||$y==7);

	for($i=0;$i<4;$i++)
	{
		if($x>=7&&$y==6) break;
		$t=$x;
		$x=$y;
		$y=14-$t;
	}


	$p=ord($moves{2});
	$x=($p-1)%15;
	$y=intval(($p-1)/15);
	if($x<5||$x>9||$y<5||$y>9) return 0;

	for($j=0;$j<$i;$j++)
	{
		$t=$x;
		$x=$y;
		$y=14-$t;
		//echo "$x,$y,$j,$i<br />";
	}

	if($isdo)
	{
		if($x<7)$x=14-$x;
		$p=$x+$y*15+1;
		$dos=array(83,84,85,99,100,114,115,128,129,130,143,144,145);
		if(($ret=array_search($p,$dos))===false)return 0;
		return $ret+1;
	}
	else 
	{
		if($x+$y<14)
		{
			$t=$x;
			$x=14-$y;
			$y=14-$t;
		}
		$p=$x+$y*15+1;
		$ios=array(85,100,115,130,145,114,129,144,128,143,127,142,141);
		if(($ret=array_search($p,$ios))===false)return 0;
		return $ret+14;
	}
}

?>
