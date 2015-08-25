<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

if(!isset($action))$action='show';
if(!in_array($action,array('show','update')))
	ACP_MessageBox($str['act_err']);

switch($action)
{
case 'show':
	
	$slist='';
	//读出setting中的数据
	$sql="SELECT * FROM $cfg[tb_settings] ORDER BY set_order,set_id";
	$result=RenDB_Query($sql);
	$crsfix=1;
	while($set=RenDB_Fetch_Array($result))
	{
		//$slist.='<form action="index.php" method="post">';
		$slist.='<form action="index.php?mode=all_set&action=update&set_id='.$set['set_id'].'" method="post">';
		$crsfix=3-$crsfix;
		$cr=$color["cell$crsfix"];
		$slist.="<tr bgcolor=\"$cr\">";
		$slist.= "<td><a name=\"set_$set[set_id]\">$set[set_note]</a></td>";
		$slist.= "<td><input type=\"text\" size=\"50\" maxlength=\"1024\" name=\"set_value\" value=\"$set[set_value]\"></td>";
		$slist.= "<td><input type=\"submit\" value=\"更改\"></td>";
		$slist.='</tr>';
		//$slist.='<input type="hidden" name="mode" value="all_set">';
		//$slist.='<input type="hidden" name="action" value="update">';
		//$slist.='<input type="hidden" name="set_id" value="'.$set['set_id'].'">';
		$slist.="</form>\n";
	}

	ACP_ShowHeader('参数设置');
	eval ('echo "'.LoadTemplate('all_set').'";');
	ACP_ShowFooter();
	break;
case 'update':
	if(!isset($set_id,$set_value))
		ACP_MessageBox($str['act_err']);
	$set_value=trim($set_value);
	$sql="UPDATE $cfg[tb_settings] SET set_value='$set_value' WHERE set_id='$set_id' LIMIT 1";
	RenDB_Query($sql,true);
	ACP_WriteLog( "更改设置 ID:$set_id 值:$set_value");

	Header("Location: index.php?mode=all_set&/#set_$set_id");
	exit();
	break;
}
?>