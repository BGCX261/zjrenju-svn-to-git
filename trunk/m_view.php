<?php
require_once('./include/common.php');
include_once('./include/txt_func.php');
SetNoUseCache();

if( !isset($name) ) ErrorBox( $str['act_err'] );

$sql ="SELECT * FROM $cfg[tb_members] WHERE u_name='$name' LIMIT 1";
$result = RenDB_Query($sql);

unset($mdata);
if( RenDB_Num_Rows($result)==1 )
   	$mdata=RenDB_Fetch_Array( $result );

if(empty($mdata)) MessageBox( "{$name} 不是会员" );

$baninfo='';
if($mdata['ban_before']>$nowtime || $mdata['ban_before']==-1)
	MessageBox( "{$name} 账号已被冻结" );

switch($mdata['u_gender'])
{
case 1:$m_gender='男';break;
case 2:$m_gender='女';break;
default:$m_gender='保密';
}

$m_from=$mdata['u_from']=='' ? '----': $mdata['u_from'];

//$cfg['qq_types']=explode('|',$cfg['qq_types']);
//$m_qq_type=isset($cfg['qq_types'][$mdata['qq_type']]) ? $cfg['qq_types'][$mdata['qq_type']] :'腾讯ＱＱ';

if($mdata['u_qq']!='')
	$m_qq = $mdata['u_qq'];
else $m_qq='----';

$m_email = $mdata['show_email']? "<a href=\"mailto:$mdata[u_email]\">$mdata[u_email]</a>" : '保密';
$m_website = $mdata['u_website']==''?'无':"<a href=\"$mdata[u_website]\" target=\"_blank\">$mdata[u_website]</a>";


$m_avatar= MakeBBAvatar($mdata['u_avatar']);
//$m_grade= MakeBBGrade($mdata);
$m_tout= GetBBTout($mdata).'%';
$m_rate=$mdata['g_w']+$mdata['g_d']+$mdata['g_l']==0 ? 0 :
			round($mdata['g_w']*100/($mdata['g_w']+$mdata['g_d']+$mdata['g_l']),1);
$m_rate.='%';

$mdata['reg_date']= TimeToDate($mdata['reg_date']);
$mdata['last_visit']= TimeToDate($mdata['last_visit']);

$buttons='';
$encodename=urlencode($mdata['u_name']);
if($udata['is_member'] && !IsSameName($name,$udata['u_name']))
{

	$buttons.=MakeBBButton( "room_new.php?byname=$encodename", '搜索新局');
	$buttons.=MakeBBButton( "g_search.php?action=list&search_all_user=0&u_name=$encodename", '搜索棋局');
	$buttons.=MakeBBButton( "pm_new.php?action=new1&sendto=$encodename",'发送消息', 500,220 );
	$buttons.=MakeBBButton( "pm_new.php?action=invite1&sendto=$encodename",'邀请下棋',500,220);
	if(!In_Names($mdata['u_name'],$udata['friends']))
		$buttons .=MakeBBButton( "m_fb.php?action=f_add&tname=$encodename",'加为好友');
	if(!In_Names($mdata['u_name'],$udata['blacklist']))
		$buttons .=MakeBBButton("m_fb.php?action=b_add&tname=$encodename",'加入黑名单');
}

$m_rec = "{$mdata['g_w']}胜{$mdata['g_d']}平{$mdata['g_l']}负";

if( $mdata['u_bio']=='' ) $mdata['u_bio'] ='(空)';
else $mdata['u_bio']=BBCoding($mdata['u_bio']);

ShowHeader('会员信息');
eval ('echo "'.LoadTemplate('m_view').'";');
ShowFooter();
?>
