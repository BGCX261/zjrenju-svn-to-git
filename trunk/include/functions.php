<?php

function RenDB_Connect($pconnect=0)
{
	global $cfg, $rendb_count;
	$rendb_count = 0;
	if( $pconnect )
	{
		@mysql_pconnect( $cfg['db_host'], $cfg['db_user'], $cfg['db_pass'] ) 
			or die( 'DATABASE ERROR.' );
	}
	else
	{
		@mysql_connect( $cfg['db_host'], $cfg['db_user'], $cfg['db_pass'] ) 
			or die( 'DATABASE ERROR.' );
	}
	@mysql_select_db( $cfg['db_name'] ) 
		or die( 'DATABASE ERROR.' );
	mysql_query("set names utf8");
}

function RenDB_Query($sql, $unbuffer=false)
{
	global $rendb_count;
	$rendb_count++;
	if($unbuffer) $result= @mysql_unbuffered_query($sql) or die( 'Database error.'.$sql );
	else $result= @mysql_query($sql) or die( 'Database error.'.$sql );
	return $result;
}

function RenDB_Fetch_Array($result)
{
	return @mysql_fetch_array($result,MYSQL_ASSOC);
}
function RenDB_Fetch_Row($result)
{
	return @mysql_fetch_row($result);
}
function RenDB_Num_Rows($result)
{
	return @mysql_num_rows($result);
}
function RenDB_Insert_ID()
{
	return @mysql_insert_id();
}
function RenDB_Affected_Rows()
{
	return @mysql_affected_rows();
}

function LoadTemplate( $temp )
{
	global $lang_pack;
	return str_replace("\"", "\\\"", implode( '', file( "./temp/{$temp}.html" )));
}

function ShowHeader($location,$refresh='',$span_ref=3)
{
    global $cfg,$udata,$color,$fid,$str,$nowtime;

	//$autorefresh='';
	$autorefresh=$refresh==''?'':"<meta http-equiv=\"refresh\" content=\"$span_ref;url={$refresh}\">";
	//$menus ="$cfg[rbb_name]<br />";
	//$menus .='<hr /> ';
	//$menus ="<a href=\"index.php\">$cfg[rbb_name]</a><hr />";
	$menus ="";
	
	if($udata['is_member'])
	{
		//用户属性
		$userbar ="<img src=\"./images/stan1.gif\" />";
		$userbar .=MemberLink($udata['u_name']);


		//$menus .=" ";
		$userbar .=" [<a href=\"loginout.php?action=logout&checkcode=$udata[act_check]\">$str[logout]</a>]";
		//$menus .='<img src="./images/logout.gif" /> <a href="g_new.php?action=new1">'.$str['logout'].'</a><br />';
		//$menus .='<hr /> ';
		if(In_Names($udata['u_name'],$cfg['admins']))$userbar .="[<a target=\"_blank\" href=\"zjcp/\">面板</a>]";

		$menus .='<a href="g_new.php?action=new1"><img src="./images/btn/menu_new.gif" /></a><br />';
		$menus .='<a href="room_new.php"><img src="./images/btn/menu_search_new.gif" /></a><br />';
		$menus .='<a href="g_search.php?action=input"><img src="./images/btn/menu_search.gif" /></a><br />';
		$menus .='<hr /> ';
		$menus .='<a href="cp_list.php"><img src="./images/btn/menu_cp.gif" /></a><br />';
		$menus .='<hr /> ';
	}
	else
	{
		$userbar= "<img src=\"./images/stan0.gif\" />Guest";
		$menus.= '<a href="loginout.php?action=show"><img src="./images/btn/menu_login.gif" /></a><br /> ';

		if($cfg['register_enable']) 
			$menus.= '<a href="m_man.php?action=reg1"><img src="./images/btn/menu_reg.gif" /></a><br /> ';
		if($cfg['send_email']) 
			$menus.= '<a href="m_man.php?action=sendpass1">'.$str['get_pass'].'</a><br /> ';
		$menus .='<hr /> ';

	}
	
	if($udata['is_member'])
	{
		//$mt0 = GetMicrotime();
		$sql="SELECT gid FROM $cfg[tb_newgames] WHERE host_name='$udata[u_name]' AND app_count>0 LIMIT 1";
		$result=RenDB_Query($sql);
		$have_new_game=RenDB_Num_Rows($result);

		$sql="SELECT gid FROM $cfg[tb_games] WHERE turn_name='$udata[u_name]' AND startdate<='$nowtime' LIMIT 1";
		$result=RenDB_Query($sql);
		$have_turn_game=RenDB_Num_Rows($result);
		//echo sprintf ( "%01.3f" , GetMicrotime() - $mt0 );

		//$menus .='<a href="room_mynew.php">'.$str['my_newgame'].'</a>';
		$menus .= $have_new_game ? '<a href="room_mynew.php"><img src="./images/btn/menu_mynew_1.gif" /></a><br />'
			:'<a href="room_mynew.php"><img src="./images/btn/menu_mynew.gif" /></a><br />';
		
		$menus .='<a href="g_search.php?action=list&search_all_user=0&game_status=1&u_name='.urlencode($udata['u_name']).'">';
		$menus .= $have_turn_game ?  '<img src="./images/btn/menu_mygame_1.gif" /></a><br />':'<img src="./images/btn/menu_mygame.gif" /></a><br />';

		$menus .='<a href="g_search.php?action=list&search_all_user=0&game_status=2&u_name='.urlencode($udata['u_name']).'"><img src="./images/btn/menu_myold.gif" /></a><br />';
		//$menus .='<img src="./images/renju_old.gif" /> <a href="room_old.php?myonly=1">我的旧谱</a><br />';
		$menus .='<a href="pm_view.php">';
		$menus .= $udata['have_new_pm']? '<img src="./images/btn/menu_pm_1.gif" /></a><br />':'<img src="./images/btn/menu_pm.gif" /></a><br />';
		$menus .='<a href="m_fb.php?action=view"><img src="./images/btn/menu_friend.gif" /></a><br />';
		$menus .='<a href="m_man.php?action=ed1"><img src="./images/btn/menu_setting.gif" /></a><br /> ';
		$menus .='<hr /> ';
	}

	$menus .='<a href="ranking.php"><img src="./images/btn/menu_ranking.gif" /></a><br /> ';
	//$menus.= '<img src="./images/help.gif" /> <a href="help.php"> '.$str['help_doc'].'</a><br /> ';

	//有上角的按钮
	$gbuttons='';
	if(defined('IN_RBB_G_VIEW'))
	{
		global $gbtns,$ginfos,$gid;
		foreach($gbtns AS $k=>$v)
		{
			if($v!='')$gbuttons.="<a href=\"$v\"><img src=\"./images/btn/btn_$k.gif\"></a>";
			//else $gbuttons.="<img src=\"./images/btn/btn_$k_gray.gif\">";
		}
		global $gid;
		if($udata['is_member']&&$have_turn_game) $location.=" <a href=\"g_view.php?gid=$gid&next_turn=1\"><img src=\"./images/btn/next.gif\" alt=\"Next\"></a>";
	}
	else $ginfos='';


	eval ( 'echo "'.LoadTemplate('header').'";');
}

function ShowFooter()
{
	global $startTime,$color,$rendb_count,$cfg;
	$passtime = GetMicrotime()-$startTime;
	eval ( 'echo "'.LoadTemplate('footer').'";');
	exit;
}

function SimplyBox( $msg, $close=false )
{
	global $color,$str;
	if( $close )
	{
		$url = "JavaScript:window.close()";
		$lktxt = $str['close_window'];
	}
	else 
	{
		$url = "JavaScript:history.back(-1)";
		$lktxt = $str['go_back'];
	}
	eval ('echo "'.LoadTemplate('simplybox').'";');
	exit();
}

function MessageBox( $message, $links=array(), $helpid='' )
{
	SetNoUseCache();
	global $cfg,$color,$str;
	$urls=array();
	if(count($links)==0)
		$urls[0] = array($str['go_back'],'JavaScript:history.back(-1)');
	else
	{
		foreach( $links as $lnk )
		{
			$u="$lnk[1].php";
			$flag=false;
			foreach( $lnk as $k => $v )
			{
				if(!is_string($k))continue;
				if($flag) $u .="&$k=$v";
				else
				{
					$u .="?$k=$v";
					$flag=true;
				}
			}
			$urls[] = array( $lnk[0], $u);
		}
	}
	$fixurl = '';
	foreach( $urls as $url )
		$fixurl .= "[<a href=\"$url[1]\">$url[0]</a>]<br />";

	$helplink = $helpid=='' ? '' : "[<a href=\"help.html#{$helpid}\" target=\"_blank\" >$str[help]</a>]";
	ShowHeader('<img src="./images/info.gif" /> '.$str['general_msg'] ,$urls[0][1] );
	eval ('echo "'.LoadTemplate('msgbox').'";');
	ShowFooter();
}

function ErrorBox( $message, $links = array(), $helpid='')
{
	global $str;
	$message = "$str[error]: $message";
	MessageBox( $message, $links, $helpid );
}

function TimeToDate($time,$nohl=false)
{
	global $cfg, $color,$nowtime,$udata;
	$ret = date($cfg['dateformat'], $time);
	if(!$nohl && $time+$cfg['span_new']>$nowtime)
		return $time<=$nowtime ? HLTxt($ret):HLTxt($ret,2);
	return $ret;
}

function MemberLink($name)
{
	if($name=='') return 'Guest';
	return "<a href=\"m_view.php?name=".urlencode($name)."\">$name</a>";
}


function In_Names($n, $names)
{
	if(empty($names)) return false;
	$names=explode('|',strtolower($names));
	return in_array(strtolower($n),$names);
}

function IsSameName( $n1, $n2 )
{
	return (strcasecmp($n1, $n2)==0);
}

function MakeBBButton( $url, $txt, $cx = 0, $cy = 0 )
{
	if( strtolower(substr($url,0,11)) == 'javascript:')
		return "<button type=\"button\" onclick=\"$url\" >{$txt}</button>";
	if( $cx != 0 )
		return "<button type=\"button\" onclick=\"window.open( '{$url}', '_blank', 'width={$cx},height={$cy}' )\" >{$txt}</button>";
	else return "<button type=\"button\" onclick=\"document.location.href ='{$url}'\">{$txt}</button>";

}


function MakeBBAvatar($avatar)
{
	global $cfg;
	if( $avatar =='' ) $avatar = "default.gif";
	if( strpos( $avatar,'/', 0 ) === FALSE )
		$avatar = "./images/avatar/$avatar";
	return "<table width=\"$cfg[avatar_width]\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"table-layout: fixed\"><tr height=\"$cfg[avatar_height]\" ><td align=\"center\"><img src=\"$avatar\"></td></tr></table>";
}


function Time2HMS( $time )
{
	$time = max(0, $time );
	if($time>=86400)
		$ret=round($time/86400,1).$GLOBALS['str']['day'];
	else 
		$ret=sprintf('%02d:%02d:%02d',($time%86400)/3600, ($time%3600)/60,$time%60);

	//$ret=sprintf('%02d,%02d:%02d:%02d',$time/86400,($time%86400)/3600, ($time%3600)/60,$time%60);
	if($time>=86400)$ret='<font color="#000099">'.$ret.'</font>'; 
	//elseif($time>=3600)$ret='<font color="#004400">'.$ret.'</font>'; 
	else $ret='<font color="#990000">'.$ret.'</font>';
	return $ret;
}

/*
function GetBBGrade( &$ud )
{
	if(empty($ud['skill'])) return 0;
	global $bb_grades;
	$grade = 0;
	for($i=1;$i<10;$i++)
	{
		if( $ud['skill'] >=$bb_grades[$i] )
			if($bb_grades[$i]>$bb_grades[$i-1])$grade = $i;
		else break;
	}
	return $grade;
}
*/
/*
function MakeBBGrade( &$ud )
{
	//global $color;
	if(!is_array($ud))
		$grade=intval($ud);
	else if( empty($ud['u_name'])) return 'N/A';
	else $grade = GetBBGrade( $ud );
	//$color=sprintf('%2X%2X%2X',$grade*12+80,0,0);
	$grade=$grade==0? '1k':"{$grade}d";
	$ret= "<b>$grade</b>";
	if(is_array($ud))
		$ret.="($ud[skill])";
	return $ret;
}
*/

function GetBBTout( &$ud )
{
	if( empty($ud['u_name'])) return 0;
	$g_count=$ud['g_to']+$ud['g_w']+$ud['g_d']+$ud['g_l'];
	return $g_count <=0? 0: round(100*$ud['g_to']/$g_count,1);
}
/*
function MakeBBTout( &$ud )
{
	global $color;
	if( empty($ud['u_id'])) return 'N/A';
	return GetBBTout( $ud ).'%';
}
*/

function MakePageBar( $url, $itemcount, $itemperpage, $page )
{
	$ret = array( 'pagebar'=>'','antrow'=>0,'start'=>0);
	if( $itemcount <= 0 )
	{
		//$ret['start'] = 0;
		//$ret['antrow'] = 0;
		$ret['pagebar'] ='&nbsp;';
		return $ret;
	}
	$lastpage = intval(($itemcount - 1 )/ $itemperpage) + 1;
	if($page < 0 || $page >= $lastpage)
	{
		$page = $lastpage;
		$ret['antrow'] = $itemcount % $itemperpage;
		if( $ret['antrow'] == 0 ) $ret['antrow'] = $itemperpage;
	}
	else $ret['antrow'] =  $itemperpage;

	$ret['start'] = ( $page - 1 ) * $itemperpage;
	if( $lastpage > 1 )
	{
		//$ret['pagebar'] = '';
		$code = '$ret[\'pagebar\'] .="<a href=\"'.$url.'&page={$i}\">{$i}</a>.";';
		for( $i = 1; $i <= $lastpage; $i++ )
		{
			if( $i <= 5 || abs( $i - $page ) < 2 || $i > $lastpage -1 )
			{
				$cdotcount = 0;
				if( $page != $i ) eval($code);
				else $ret['pagebar'] .=HLTxt($i).'.';
			}
			elseif( $cdotcount < 3)
			{
				$ret['pagebar'] .='.';
				$cdotcount ++;
			}
		}
	}
	if($ret['pagebar']=='')$ret['pagebar']='&nbsp;';
	return $ret;
}

function SetNoUseCache()
{
	//不使用cache
	if(isset($GLOBALS['nousercache']))return;

	$GLOBALS['nousercache']=true;

	$nowtime = gmdate('D, d M Y H:i:s') . ' GMT';
	header('Expires: ' . $nowtime);
	header('Last-Modified: ' . $nowtime);
	header('Pragma: no-cache'); // HTTP/1.0
	
	header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1

}

function UpdateOnline()
{
	global $cfg,$udata,$nowtime,$fakename;

	if(empty($udata['o_time']))
	{
		$udata['u_stand']=$udata['is_member']? 1:0;
		$udata['o_time']=$nowtime;

		$udata['fake_name']=$udata['is_member']?$udata['u_name']:$fakename;
		$sql ="REPLACE $cfg[tb_onlines] SET fake_name='$udata[fake_name]', u_stand='$udata[u_stand]',o_time='$nowtime', last_action='$nowtime'";
		RenDB_Query($sql,true);
	}
	elseif($udata['o_time']<$nowtime-$cfg['span_renew'])
	{
		$sql ="UPDATE $cfg[tb_onlines] SET o_time='$nowtime' WHERE fake_name='$udata[fake_name]' LIMIT 1";
		RenDB_Query($sql,true);

		if($udata['is_member'])
		{
			if($udata['o_time']<$nowtime-3600)
				$udata['act_check']=md5(uniqid(rand()));
			$sql="UPDATE $cfg[tb_members] SET last_visit='$nowtime', act_check='$udata[act_check]' WHERE u_id='$udata[u_id]' LIMIT 1";
			RenDB_Query($sql,true);
		}
	}
}

function WriteBBLog($log, $logname='syslog', $insubdir=false )
{
	global $udata;
	$filename=$insubdir? "../log/$logname.php":"./log/$logname.php";

	if(($line=file($filename))===FALSE)return;

	$logkeep=max(10,$GLOBALS['cfg']['max_log']);
	$logkeep=min(500,$logkeep);
	unset($line[0]);
	$line=array_slice($line,0,$logkeep-1);
	array_unshift($line,TimeToDate(time(),true)." {$log} \tby $udata[fake_name]\r\n");
	$line="<?php die(); ?>\r\n".implode('',$line);
	

	@$fr = fopen( $filename, 'w' );
	@flock($fr, LOCK_EX);
	@fwrite($fr,$line);
	@fclose($fr);
}

function HLTxt($txt,$color=0)
{
	$cr=array('990000','006600','000099');
	return "<font color=\"{$cr[$color]}\">$txt</font>";
}
?>
