<?php
require_once('./include/common.php');
require_once('./include/g_func.php');
SetNoUseCache();

if( !$udata['is_member']) ErrorBox( $str['act_noguest'] );
if(!isset($checkcode)|| $checkcode!=$udata['act_check'])
	MessageBox('验证码不对，请返回刷新页面重试');
if( !isset($gid) || !isset($action)
	||!in_array( $action, array('go','swap','undo1','undo2','draw','resign') ) )
		ErrorBox( $str['act_err'] );


//读棋局
$gid = intval( $gid );
$sql = "SELECT * FROM {$cfg['tb_games']} WHERE `gid`='{$gid}' LIMIT 1";
$result = RenDB_Query($sql);
if( RenDB_Num_Rows($result) < 1 )
	ErrorBox('棋局不存在');
if(!$gdata=RenDB_Fetch_Array( $result ))
	ErrorBox('棋局不存在');
if($gdata['startdate']>$nowtime) MessageBox('这盘棋还没开始');
if($gdata['status']) MessageBox('这盘棋已经结束');

$mcount = $gdata['mcount'];
$gdata['moves'] = substr( $gdata['moves'], 0, $mcount);
$curside = ( $mcount + 1 ) % 2;
$names[0] = $gdata['w_name'];
$names[1] = $gdata['b_name'];
$draws[0] = $gdata['w_draw'];
$draws[1] = $gdata['b_draw'];
$undos[0] = $gdata['w_undo'];
$undos[1] = $gdata['b_undo'];
$times[0] = $gdata['w_time'];
$times[1] = $gdata['b_time'];

$turnside=GetTurnSide();

//超时检查
$timeinfo = GetTimeOutInfo();
if( $timeinfo===true )
{
	header("Location: g_view.php?gid=$gid");
	exit;
}
$tremain = $timeinfo[0];


if( IsSameName( $udata['u_name'], $gdata['b_name'] ) )
	$myside = 1;
else if( IsSameName( $udata['u_name'] , $gdata['w_name'] ) )
	$myside = 0;
else ErrorBox('这不是你的棋局');

//$myside = $turnside;//for debug

$stones = array_fill( 1, 225, false );
for( $i=0; $i< $mcount; $i++ )
{
	$p = ord($gdata['moves']{$i});
	if( $p >= 1 && $p <= 225 )
		$stones[$p] = true;
}

switch( $action )
{
case 'go':
	if($turnside != $myside) ErrorBox('不该你走');

	if(!isset($pos)||$pos<1||$pos>225) ErrorBox('坐标超出范围');
	$pos =intval( $pos);
	if($stones[$pos]) ErrorBox('这一点已经有子了');

	switch($gdata['rules'])
	{
	case 1:
		if($mcount==4 && $gdata['step5'] == 2 && $myside == 0)
		{
			//5C
			if($pos == $gdata['pos5_a'])
				$pos = $gdata['pos5_b'];
			elseif($pos == $gdata['pos5_b'])
				$pos = $gdata['pos5_a'];
			else ErrorBox('act_fail');
			$gdata['mcount']++;
			$gdata['moves'].=chr($pos);
			$gdata['step5'] = 0;
			if( $myside == 0)
				$gdata['w_time'] = $tremain;
			else $gdata['b_time'] = $tremain;
			$gdata['l_time']=$nowtime;
			//$gdata['turn_name']=$names[GetTurnSide()];
			ClearReqInfo();
			break;
		}
		else if($mcount==4 && $gdata['step5'] == 0 && $myside == 1)
		{
			//5A
			$gdata['step5'] = 1;
			$gdata['pos5_a'] = $pos;
			if( $myside == 0)
				$gdata['w_time'] = $tremain;
			else $gdata['b_time'] = $tremain;
			$gdata['l_time']=$nowtime;
			//$gdata['turn_name']=$names[GetTurnSide()];
			ClearReqInfo();
			break;
		}
		elseif($mcount==4 && $gdata['step5'] == 1 && $myside == 1)
		{
			//5A
			//检查对称性
			if( IsSamePos_5B()) MessageBox('请选与第一点不同的点');
			$gdata['step5'] = 2;
			$gdata['pos5_b'] = $pos;
			if( $myside == 0)
				$gdata['w_time'] = $tremain;
			else $gdata['b_time'] = $tremain;
			$gdata['l_time']=$nowtime;
			//$gdata['turn_name']=$names[GetTurnSide()];
			ClearReqInfo();
			break;
		}
		
		$gdata['step5'] = 0;
		//其他的与RBB的处理相同
	default: //RBB
//		if( $mcount< 3)
//		{
			//开局
		if($gdata['rules']!=2)
		{
			if( $mcount==0 && $pos!=113 )$pos = 113;

			if($mcount==1 && !in_array($pos,array(97,98,99,112,114,127,128,129)))
					MessageBox('请走标准开局');

			if($gdata['mcount']==2)
			{
				$tmp_moves=$gdata['moves'].chr($pos);
				$opening=GetOpening($tmp_moves);
				if($opening==0||$gdata['rules']==1&&($opening==13||$opening==26)) MessageBox('请走标准开局');
				$gdata['opening']=$opening;
			}
		}
//			
		if(!$gdata['swaped'] && $gdata['rules']==0 && $mcount==5 && $myside==1)
		{
			//sakata !swaped
			$gdata['w_name']=$names[1];
			$gdata['b_name']=$names[0];
			$names[0]=$gdata['w_name'];
			$names[1]=$gdata['b_name'];
			//$gdata['swaped']=0;

			$gdata['b_time']=$gdata['w_time'];
			$gdata['w_time']=$tremain;
			$gdata['mcount']++;
			$gdata['moves'].=chr($pos);
			$gdata['l_time']=$nowtime;
			ClearReqInfo();
			break;
		}

		if($gdata['rules']==2 && !$gdata['swaped'] && $mcount==3 && $myside==0 )
		{
			//两次交换 !swap1
			$gdata['w_name']=$names[1];
			$gdata['b_name']=$names[0];
			$names[0]=$gdata['w_name'];
			$names[1]=$gdata['b_name'];
			//$gdata['swaped']=0;

			$gdata['w_time']=$gdata['b_time'];
			$gdata['b_time']=$tremain;
			$gdata['mcount']++;
			$gdata['moves'].=chr($pos);
			$gdata['l_time']=$nowtime;
			ClearReqInfo();

			break;
		}

		/*
		if($gdata['rules']==2 && !($gdata['swaped']&2) && $mcount==5 && $myside==0 )
		{
			//两次交换 !swap2
			//sakata !swaped
			//$gdata['w_name']=$names[1];
			//$gdata['b_name']=$names[0];
			//$names[0]=$gdata['w_name'];
			//$names[1]=$gdata['b_name'];
			//$gdata['swaped']|=2;

			//$gdata['w_time']=$gdata['b_time'];
			//$gdata['b_time']=$tremain;
			$gdata['w_time']=$tremain;
			$gdata['mcount']++;
			$gdata['moves'].=chr($pos);
			$gdata['l_time']=$nowtime;
			ClearReqInfo();
			break;
		}
		*/
		
		//检测禁手和赢棋
		//$mt0 = GetMicrotime();
		require_once('./include/g_core.php');

		$rboard = new RBoard();
		$rboard->FromMoves($gdata['moves'],$mcount);

		//当前pos=>x,y
		$x = ($pos-1) % 15;
		$y = intval(($pos-1) / 15 );
			
		$ret=$rboard->Go( $x, $y, $curside );
		if($ret===false)
		{
			WriteBBLog('g_man: ret==false');
			MessageBox('内部错误');
		}

		$gdata['mcount']++;
		$gdata['moves'].=chr($pos);
		$gdata['l_time']=$nowtime;
		if( $turnside == 0)
			$gdata['w_time'] = $tremain;
		else $gdata['b_time'] = $tremain;

		switch($ret)
		{
		case 'win': EndGame(4-$myside, true);break;
		case 'lose': EndGame(3+$myside, true);break;
		case 'foul':EndGame(1, true );break;
		}
		if( $gdata['mcount'] >= $cfg['maxmove'] )
			EndGame( 2, true );

		//$gdata['turn_name']=$names[GetTurnSide()];
		ClearReqInfo();
		break;

	}
	break;

case 'undo1'://提出悔棋
	if( $mcount < 1 ) ErrorBox($str['act_err']);

/*
	if( $mcount < 3 )
	{
		if( $myside==1 )
		{
			$gdata['mcount'] = 0;
			$gdata['turn_name']=$names[GetTurnSide()];
			ClearReqInfo();
			break;
		}
		else ErrorBox($str['act_err']);
	}
*/
	//if($mcount==5 && $gdata['rules']!=1 )

	if(!isset($mto)) ErrorBox($str['act_err']);
	$mto = intval($mto);
	if($mto<1||$mto>$mcount) ErrorBox("不能回到第{$mto}步");
	
	if( $myside==1 ) $gdata['b_undo'] = $mto;
	else $gdata['w_undo'] = $mto;
	break;

case 'undo2'://同意悔棋
	if( $undos[1-$myside]==0 ) ErrorBox('对方没提出悔棋');

	$undos[1-$myside] = max(1,$undos[1-$myside]);
	$undos[1-$myside] = min($mcount,$undos[1-$myside]);

	switch($gdata['rules'])
	{
	case 0:
		if($gdata['mcount']<5||$undos[1-$myside]>5) break;
		if(!$gdata['swaped'])
		{
			$gdata['w_name'] = $names[1];
			$gdata['b_name'] = $names[0];
			$names[0]=$gdata['w_name'];
			$names[1]=$gdata['b_name'];
			$tmp=$gdata['w_time'];
			$gdata['w_time']=$gdata['b_time'];
			$gdata['b_time']=$tmp;
		}
		$gdata['swaped']=0;
		break;
	case 1:
		if($undos[1-$myside]>3 || !$gdata['swaped'])break;
		$gdata['w_name'] = $names[1];
		$gdata['b_name'] = $names[0];
		$names[0]=$gdata['w_name'];
		$names[1]=$gdata['b_name'];
		$tmp=$gdata['w_time'];
		$gdata['w_time']=$gdata['b_time'];
		$gdata['b_time']=$tmp;
		$gdata['swaped']=0;
		break;
	case 2:
		if($undos[1-$myside]>5)break;
		if($undos[1-$myside]>3)
		{
			if($gdata['swaped']&2)
			{
				$gdata['w_name'] = $names[1];
				$gdata['b_name'] = $names[0];
				$names[0]=$gdata['w_name'];
				$names[1]=$gdata['b_name'];
				$tmp=$gdata['w_time'];
				$gdata['w_time']=$gdata['b_time'];
				$gdata['b_time']=$tmp;			
			}
			$gdata['swaped']=$gdata['swaped']&1;
		}
		else
		{
			if($gdata['swaped']==2||$gdata['swaped']==1)
			{
				$gdata['w_name'] = $names[1];
				$gdata['b_name'] = $names[0];
				$names[0]=$gdata['w_name'];
				$names[1]=$gdata['b_name'];
				$tmp=$gdata['w_time'];
				$gdata['w_time']=$gdata['b_time'];
				$gdata['b_time']=$tmp;			
			}
			$gdata['swaped']=0;
		}
		break;
	}

/*	if( $gdata['rules']==1 && $undos[1-$myside] <=3 && $gdata['swaped']
		||$gdata['rules']==0 && 
		||$gdata['rules']==2 && $undos[1-$myside] <=3 && ($gdata['swaped']==1||$gdata['swaped']==2))

	{
		$gdata['w_name'] = $names[1];
		$gdata['b_name'] = $names[0];
		$names[0]=$gdata['w_name'];
		$names[1]=$gdata['b_name'];
		$tmp=$gdata['w_time'];
		$gdata['w_time']=$gdata['b_time'];
		$gdata['b_time']=$tmp;
	}
//	elseif($gdata['rules']==2 && $undos[1-$myside]<=5&& ($gdata['swaped']&2))

*/
	$gdata['mcount']=$undos[1-$myside]-1;
	$gdata['step5']=0;
	$gdata['l_time']=$nowtime;
	//$gdata['turn_name']=$names[GetTurnSide()];
	ClearReqInfo();
	break;
case 'draw'://和棋
	if( $mcount <= $cfg['open_mcount'] ) ErrorBox("步数太少不允许和棋");
	if( $draws[1-$myside] == 1 )//双方同意和棋
	{
		EndGame( 2, true );
	}
	else
	{
		if( $myside==1 ) $gdata['b_draw'] = 1;
		else  $gdata['w_draw'] = 1;
	}
	break;
case 'resign'://认输
	if( $mcount <= $cfg['open_mcount'] ) ErrorBox("第{$cfg['open_mcount']}步前不允许认输");
	EndGame( 5+$myside, true );
	break;
case 'swap':
	//if(!isset($tarcolor)||$tarcolor>1||$tarcolor<0)
	//	MessageBox($str['act_err']);
	//$tarcolor=intval($tarcolor);
	switch($gdata['rules'])
	{
	case 0:
		if(!($mcount==5 && $myside==1 && !$gdata['swaped']))
			MessageBox($str['act_err']);

		$gdata['b_time']=$tremain;
		$gdata['swaped']=1;

		break;
	case 1:
		if(!($mcount==3 && $myside==0 && !$gdata['swaped']))
			MessageBox($str['act_err']);

		$gdata['w_name'] = $names[1];
		$gdata['b_name'] = $names[0];
		$names[0]=$gdata['w_name'];
		$names[1]=$gdata['b_name'];
		$gdata['swaped'] = 1;
		$gdata['w_time'] = $gdata['b_time'];
		$gdata['b_time'] = $tremain;
		break;
	case 2:
		if($mcount==3 && $myside==0 && !($gdata['swaped']&1))
		{
			//$gdata['w_name']=$names[1];
			//$gdata['b_name']=$names[0];
			//$names[0]=$gdata['w_name'];
			//$names[1]=$gdata['b_name'];
			$gdata['w_time']=$tremain;
			$gdata['swaped']=1;
			//$gdata['w_time'] = $gdata['b_time'];
			//$gdata['b_time'] = $tremain;
		}
		elseif(!($gdata['swaped']&2) && $mcount==5 && $myside==0 )
		{
			$gdata['w_name']=$names[1];
			$gdata['b_name']=$names[0];
			$names[0]=$gdata['w_name'];
			$names[1]=$gdata['b_name'];
			$gdata['swaped']|=2;
			$gdata['w_time']=$gdata['b_time'];
			$gdata['b_time']=$tremain;
		}
		else MessageBox($str['act_err']);
		//swaped<0 w 未开始 swaped&1 第一次交换 swap&2 第二次交换
		break;
	default:
		MessageBox($str['act_err']);
	}

	$gdata['l_time'] = $nowtime;
	ClearReqInfo();

	break;
}

UpdateGameData( $gdata );
header("Location: g_view.php?gid={$gid}");
?>
