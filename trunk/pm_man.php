<?
require_once('./include/common.php');

if( !isset( $action ) || !in_array( $action, array('del') ) )
	ErrorBox( $str['act_err'] );
if( !$udata['is_member']) ErrorBox($str['act_noguest']);

switch( $action )
{
case 'del':
	if(!isset($box)||($box!='in'&&$box!='out'))
		MessageBox($str['act_err']);
	if(!isset($_POST['select_pms']) || !is_array($_POST['select_pms']))
		MessageBox('您没有选中任何消息');

	if(count($_POST['select_pms'])>$cfg['pmperpage']) ErrorBox($str['act_err']);

	$select_pms=array();
	foreach($_POST['select_pms'] as $v)
		$select_pms[]="pmid='".intval($v)."'";
	$select_pms=implode(' OR ',$select_pms);

	if($box=='in')
		$sql="DELETE FROM $cfg[tb_pms] WHERE sendto='$udata[u_name]' AND ( $select_pms )";
	else $sql="DELETE FROM $cfg[tb_pms] WHERE comefrom='$udata[u_name]' AND ( $select_pms )";
	RenDB_Query($sql,true);
	if(RenDB_Affected_Rows())
		MessageBox('删除成功');
	break;
}
MessageBox( $str['act_fail'] );

?>