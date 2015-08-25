<?php
define('IN_RBB_G_VIEW',true);
require_once('./include/common.php');
require_once('./include/g_func.php');
SetNoUseCache();

$gbtns=array('','','','','');

if(!isset($gid))ErrorBox( $str['act_err'] );
$gid = intval( $gid );

//读棋局
//$sql = "SELECT * FROM `$cfg[tb_games]` WHERE `gid`='$gid' LIMIT 1";
$sql ="SELECT g.*, cp.cp_name,ol1.o_time AS b_otime ,ol2.o_time AS w_otime, m1.skill AS b_skill,m1.u_avatar AS b_avatar, m2.skill AS w_skill,m2.u_avatar AS w_avatar ";
$sql .=" FROM `$cfg[tb_games]` AS g";
$sql .=" LEFT JOIN `$cfg[tb_members]` AS m1 ON m1.u_name=g.b_name ";
$sql .=" LEFT JOIN `$cfg[tb_members]` AS m2 ON m2.u_name=g.w_name ";
$sql .=" LEFT JOIN `$cfg[tb_competitions]` AS cp ON cp.cp_id=g.cp_id";
$sql .=" LEFT JOIN `$cfg[tb_onlines]` AS ol1 ON ol1.fake_name=g.b_name";
$sql .=" LEFT JOIN `$cfg[tb_onlines]` AS ol2 ON ol2.fake_name=g.w_name";

if(isset($next_turn)&&$next_turn)
{
	//查找下一局
	if( !$udata['is_member']) ErrorBox( $str['act_noguest'] );
	$sql .=" WHERE turn_name='$udata[u_name]' ORDER BY g.gid<='$gid',g.gid LIMIT 1";
}
else
{
	$sql .=" WHERE g.gid='$gid' LIMIT 1";
}


$result = RenDB_Query($sql);

if( RenDB_Num_Rows($result) < 1 )
//	if(isset($next_turn)&&$next_turn)
		MessageBox( $str['g_not_found'] );
 //	else MessageBox( $str['g_not_found'] );
	
$gdata = RenDB_Fetch_Array( $result );
$gid=$gdata['gid'];

$nostart=($gdata['startdate']>$nowtime);
//print_r($gdata);
//整理棋局数据
$mcount = $gdata['mcount'];
$curside = ( $mcount + 1 ) % 2;
$names[0] = $gdata['w_name'];
$names[1] = $gdata['b_name'];
$draws[0] = $gdata['w_draw'];
$draws[1] = $gdata['b_draw'];
$undos[0] = $gdata['w_undo'];
$undos[1] = $gdata['b_undo'];
$times[0] = $gdata['w_time'];
$times[1] = $gdata['b_time'];
$step5 = $gdata['step5'];
//echo $gdata['opening'];
//echo $gdata['turn_name'];
if($gdata['status']||$nostart) $turnside=-1;
else $turnside = GetTurnSide();

if( IsSameName( $udata['u_name'], $gdata['b_name'] ) )
	$myside = 1;
else if( IsSameName( $udata['u_name'] , $gdata['w_name'] ) )
	$myside = 0;
else $myside = -1;


//$myside = $turnside;//for debug

$ismyturn = ( $turnside !=-1 && $turnside == $myside );

if(!$gdata['status']&&!$nostart)
{
	$timeinfo = GetTimeOutInfo();
	if( $timeinfo === true )
	{
		header("Location: g_view.php?gid=$gid");
		exit();
	}
}

$panel = '';

//棋局信息

$ginfos ="<a href=\"g_down.php?gid=$gid\"><img src=\"./images/btn/download.gif\" alt=\"Save\"></a> ";
$ginfos.=$cfg['rules'][$gdata['rules']];
$ginfos.='|'.Time2HMS($gdata['add_time']);
$ginfos.=$gdata['step_time']?'/'.Time2HMS($gdata['step_time']):'';
$ginfos.='|'.TimeToDate($gdata['startdate']).'|';


$mems[0]=array('u_name'=>$gdata['w_name'],'skill'=>$gdata['w_skill'],'u_avatar'=>$gdata['w_avatar'],'online'=>$nowtime-$gdata['w_otime']<= $cfg['span_online']);
$mems[1]=array('u_name'=>$gdata['b_name'],'skill'=>$gdata['b_skill'],'u_avatar'=>$gdata['b_avatar'],'online'=>$nowtime-$gdata['b_otime']<= $cfg['span_online']);
while($row=RenDB_Fetch_Array( $result ))
{
	if(IsSameName($row['u_name'],$gdata['b_name'])) $mems[1]=$row;
	else if(IsSameName($row['u_name'],$gdata['w_name'])) $mems[0]=$row;
}

$mtemp = LoadTemplate('g_panel_m');


for($side=1;$side>=0;$side--)
{
	//$et= $turnside==$side ? '_f':'';
	if($mems[$side]['online'])
		$m_sideimg= "<img src=\"./images/stan1.gif\">";
	else $m_sideimg= "<img src=\"./images/stan0.gif\">";

//	$m_sideimg= "<img src=\"./images/{$cursideimg}{$et}.gif\">"
	//	:"<img src=\"./images/white{$et}.gif\">";

	if(empty($mems[$side]['u_name']))
	{
		$m_avatar=MakeBBAvatar('');
		$m_name=$names[$side].' ('.HLTxt('Guest').')';
		$m_grade='N/A';
	}
	else
	{
		$m_avatar=MakeBBAvatar($mems[$side]['u_avatar']);
		$m_name=MemberLink($names[$side]).'('.$mems[$side]['skill'].')';
	}

	if($gdata['status'])
	{
		$m_time='';
		$m_time_step='';
	}
	elseif($turnside==$side )
	{
		$m_time= '剩时: '.Time2HMS($timeinfo[0]).HLTxt('*');
		//if( $timeinfo[1] <= 0 ) 
		//	$m_time= $m_time.HLTxt(' *');
		//$m_time_step=;
		$m_time_step=$gdata['step_time']?'本步: '.Time2HMS($timeinfo[1]).HLTxt('*'):'';
	}
	else 
	{
		$m_time='剩时: '.Time2HMS($times[$side]);
		//$m_time_step=$gdata['step_time']?"单步: $gsteptime":'单步: N/A' ;
		$m_time_step='';
	}

	eval( "\$panel .= \"{$mtemp}\";");
}

$panel .="<table width=\"100%\" border=0 cellspacing=0 cellpadding=5 ><tr><td>";

if($gdata['cp_name']) $panel.="<a href=\"cp_view.php?cp_id=$gdata[cp_id]\">".HLTxt($gdata['cp_name']).'</a><br />';

if( $myside!=-1 && !$gdata['status'] && !$nostart)
{
	if($ismyturn) $panel.=HLTxt('轮到你走');
	else $panel.= '轮到对手';

	if(($undos[1-$myside]!=0) && empty($gdata['cp_name'])) 
	{
		$panel.="<br />对手提出回到第{$undos[1-$myside]}手(前)";
		$panel.="[<a href=\"JavaScript:ConfirmUndo2('{$undos[1-$myside]}')\">同意</a>]";
	}
	if($draws[1-$myside]==1)
	{
		$panel.='<br />对手提出和棋';
	}
	if($undos[$myside]!=0) 
		$panel.="<br />你提出回到第{$undos[$myside]}手(前)";
	if($draws[$myside]==1)
		$panel.='<br />你提出和棋';

	$panel.='<br />';

	//交换
	if( !$gdata['swaped'] && ($gdata['rules']==1||$gdata['rules']==2) && $mcount==3 && $myside==0 
		|| !$gdata['swaped'] && $gdata['rules']==0 && $mcount==5 && $myside==1 
		|| !($gdata['swaped']&2) && $gdata['rules']==2 && $mcount==5 && $myside==0)
		//$panel.= "[<a href=\"JavaScript:ConfirmSwap()\">交换</a>]";
		$gbtns[0]="JavaScript:ConfirmSwap()";

	//悔棋
	if( $undos[$myside]==0 && $mcount>0 && empty($gdata['cp_name']))
		//$panel.= "[<a href=\"JavaScript:ConfirmUndo('{$mcount}')\">悔棋</a>]";
		$gbtns[1]="JavaScript:ConfirmUndo('{$mcount}')";

	//工具条 - 和棋
	if( $draws[$myside]==0 && $mcount>$cfg['open_mcount'])
		//$panel.= "[<a href=\"JavaScript:ConfirmDraw()\">和棋</a>]";
		$gbtns[2]="JavaScript:ConfirmDraw()";
	//认输
	if( $mcount>$cfg['open_mcount'])
		//$panel.= "[<a href=\"JavaScript:ConfirmResign()\">认输</a>]";
		$gbtns[3]="JavaScript:ConfirmResign()";
}
else
{
	if($gdata['status'])
	{
		$panel.="终局,共{$mcount}步,";
		switch( $gdata['status'] )
		{
		case 1:$panel.='黑禁手';break;
		case 2:$panel.='和棋';break;
		case 3:$panel.='黑胜';break;
		case 4:$panel.='白胜';break;
		case 5:$panel.='白投子';break;
		case 6:$panel.='黑投子';break;
		case 7:$panel.='白超时';break;
		case 8:$panel.='黑超时';
		}
	}
	elseif($nostart)
	{
		$panel.="该局将于 ".TimeToDate($gdata['startdate'])." 开始";
	}
	else
	{
		$panel.= " 目前共走了{$mcount}步";
	}
}


//对话
$sql="SELECT * FROM `$cfg[tb_chats]` WHERE gid='$gid' ORDER BY chat_id DESC";
$result=RenDB_Query($sql);
//$rows=RenDB_Num_Rows($result);
$chat_message='';
while($chatdata=RenDB_Fetch_Array($result))
{
	$chat_message.=$chatdata['chat_author']==''?'Guest  ':"$chatdata[chat_author]  ";
	$chat_message.=TimeToDate($chatdata['chat_date'],true);
	$chat_message.="\r\n";
	$chat_message.=$chatdata['chat_message'];
	$chat_message.="\r\n-----------------\r\n";
}

$panel .= '<br />';

$panel.="<textarea rows=\"8\" cols=\"35\" readonly>$chat_message </textarea>";
$panel.="<form method=\"post\" action=\"chat_new.php?&gid=$gid\"><input type=\"textarea\" size=\"27\" maxlength=\"253\" name=\"message\" /> <input type=\"submit\" value=\"chat\"></form>";

$panel .= '</td></tr></table>';


//自动刷新
if($cfg['span_refresh_game']>0 && $nowtime-$gdata['l_time']<1800 && $myside>=0 && $mems[1-$myside]['online'])
{
	$statcode=urlencode("{$turnside}{$gdata['swaped']}{$undos[0]}{$undos[1]}{$draws[0]}{$draws[1]}-{$gdata['mcount']}");
	//echo $statcode;
	$frame_auto="<iframe frameborder=\"0\" width=\"0\" height=\"0\" src=\"auto_refresh.php?gid=$gid&statcode=$statcode\"></iframe>";
}
else $frame_auto='';

/*
//公有数据
$checkcode=$udata['act_check'];
$moves='';
for( $i=0; $i< $mcount; $i++ )
{
	$pos =ord($gdata['moves']{$i});
	$moves.=chr(intval(($pos-1)%15)+65);
	$moves.=chr(($pos-1)/15+65);
}

$canmove= $ismyturn ? '1' : '0';


$pos5a=$gdata['pos5_a'];
$pos5b=$gdata['pos5_b'];

ShowHeader("<img src=\"./images/renju.gif\" /> 第{$gid}桌");
eval ( 'echo "'.LoadTemplate('board_java').'";' );
ShowFooter();
*/


//根据设置显示棋盘
if( isset($boardstyle))
{
	setcookie( 'board_style', $boardstyle, $nowtime+2592000 );
}
elseif( isset($_COOKIE["board_style"]))
{
	$boardstyle = $_COOKIE["board_style"];
}
else $boardstyle = 'java';

if(!array_key_exists( $boardstyle, $boards ) )
	$boardstyle = 'java';

$board_options = '';
if($boardstyle!='java')
    $board_options.="<option value=\"java\">JAVA APPLET</option>";
    
foreach( $boards as $k => $sty)
{
	if( $boardstyle != $k )
		$board_options .= "<option value=\"{$k}\">{$sty['name']}</option>";
}
//$thismode='g_view.php';

//$mtsign=$ismyturn?HLTxt('轮到你走'):'';
/*
	ShowHeader("<img src=\"./images/renju.gif\" /> 第{$gid}桌 $mtsign","g_view.php?gid=$gid",$cfg['span_refresh_game']);
else */

//自动刷新
//if($cfg['span_refresh_game']>0 && $nowtime-$gdata['l_time']<3600 && IsSameName($udata['u_name'],$names[1-$turnside]))
//	$frame_auto="<iframe frameborder=\"0\" width=\"0\" height=\"0\" src=\"auto_refresh.php?gid=$gid\"></iframe>";
//else $frame_auto='';


ShowHeader("<img src=\"./images/renju.gif\" /> 第{$gid}桌");

//公有数据
$checkcode=$udata['act_check'];
//$codeinfo="{$names[1]} vs {$names[0]}";
$moves='';
for( $i=0; $i< $mcount; $i++ )
{
	$pos = ord($gdata['moves']{$i});
	$moves.=chr(intval(($pos-1)%15)+65);
	$moves.=chr(($pos-1)/15+65);
}

$canmove= $ismyturn ? '1' : '0';

//内建棋盘
if( $boardstyle!='java' )
{
	$sitewidth=$boards[$boardstyle]['width'];
	$boardwidth=$sitewidth * 17;

	$rmdir = './renju/'.$boards[$boardstyle]['id'];

	//棋子
	$stones = array_fill( 1, 225, 0 );
	for( $i=0; $i< $mcount; $i++ )
	{
		$pos = ord($gdata['moves']{$i});
		$stones[$pos] = $i+1;
	}

	if($mcount == 4 && $gdata['step5'] > 0 )
		$stones[$gdata['pos5_a']]=230;
	if($mcount == 4 && $gdata['step5'] > 1 )
		$stones[$gdata['pos5_b']]=231;


	$html_board = '<table cellpadding="0" cellSpacing="0" border ="0" >';
	//$html_board .= "<tr height=\"{$sitewidth}\" ><td width=\"{$boardwidth}\" colspan=\"17\">&nbsp</td></tr>";
	for( $y=0; $y<15; $y++)
	{
		$html_board .= "<tr height=\"{$sitewidth}\">";
		for( $x=0; $x<15; $x++)
		{
			//if( $x>=1 && $x<=15 && $y>=1 && $y<=15 )
			//{
				$pos = ($x) + ($y)*15 + 1;
				$num = $stones[$pos];
				$html_board .= "<td width=\"$sitewidth\">";
				if( $num >=1 && $num <=225)
				{
					$et = ($num == $mcount)? '_f' : '';
					$html_board .= $stones[$pos]%2==0 ?
						"<img id=\"stone{$num}\" src=\"{$rmdir}/white{$et}.gif\">"
						:"<img id=\"stone{$num}\" src=\"{$rmdir}/black{$et}.gif\">" ;
				}
				elseif( $num == 230 )
					$html_board .= $myside==0 ? "<a href=\"JavaScript:PosClicked({$pos})\"><img id=\"stone230\" src=\"{$rmdir}/5a.gif\"></a>"
						:"<img id=\"stone230\" src=\"{$rmdir}/5a.gif\">" ;
				elseif( $num == 231 )
					$html_board .= $myside==0 ? "<a href=\"JavaScript:PosClicked({$pos})\"><img id=\"stone231\" src=\"{$rmdir}/5b.gif\"></a>"
					:"<img id=\"stone231\" src=\"{$rmdir}/5b.gif\">";
				elseif( $canmove && !($myside==0 && $gdata['step5']==2))
					$html_board .="<a href=\"JavaScript:PosClicked({$pos})\"><img src=\"{$rmdir}/air.gif\"></a>";
				else $html_board .="<img src=\"{$rmdir}/air.gif\">";
				$html_board .="</td>\n";
			//}
			//else $html_board .= $x==0||$y==0? "<td width=\"{$sitewidth}\">&nbsp;</td>" :'<td></td>';
		}
		$html_board .= '</tr>';
	}
	//$html_board .= "<tr height=\"{$sitewidth}\" ><td colspan=\"17\"></td></tr></table>";
	$html_board .= "</table>";
	eval ( 'echo "'.LoadTemplate('board_html').'";' );
}
else//外部棋盘
{
	$pos5a=$gdata['pos5_a'];
	$pos5b=$gdata['pos5_b'];

	eval ( 'echo "'.LoadTemplate('board_java').'";' );
}

ShowFooter();
?>
