<?
require_once('./include/common.php');

if( !isset( $action ) || !in_array( $action, array('up1','up2') ) )
	SimplyBox( $str['act_err'], true );

if( !$udata['is_member'] ) SimplyBox( $str['act_noguest'], true );
if( !$cfg['upload_enable'] ) SimplyBox( '上传功能未开放', true );
//if( $udata['credit']<$cfg['avatar_upload_limit']) SimplyBox( '您的论坛积分未达到要求', true );
	
//$remaint = $udata['reg_date'] + $cfg['olduserline'] - time();
//if( $remaint > 0 ) SimplyBox( '注册'.Time2HMS($cfg['olduserline']).'才能上传头像', true );

switch( $action )
{
case 'up1':
	eval ('echo "'.LoadTemplate('m_upload1').'";');
	exit();
	break;
case 'up2':
	//var_dump($_FILES['userface']);
	if(!isset($_FILES['userface'])) SimplyBox($str['act_err']);
	//文件大小
	if( $_FILES['userface']['error'] != UPLOAD_ERR_OK )
	{
		if( $_FILES['userface']['error'] == UPLOAD_ERR_INI_SIZE ||
			$_FILES['userface']['error'] == UPLOAD_ERR_FORM_SIZE)
				SimplyBox( "文件大小超出限制." );
		else SimplyBox( "文件上传失败," );
	}
	if( $_FILES['userface']['size']> $cfg['avatar_size'])SimplyBox( "文件大小超出限制." );
	//文件类型
	if( !in_array($_FILES['userface']['type'], array('image/gif','image/pjpeg','image/png')))
		SimplyBox( "文件类型不符合要求" );
	$imgsize = getimagesize($_FILES['userface']['tmp_name']);
	if( $imgsize === false ) SimplyBox( "您上传的不是有效图像" );
	//再次检测类型
	if( !in_array($imgsize[2],array(0,1,2))) SimplyBox( "图像类型不符合要求" );
	if( $imgsize[0] > $cfg['avatar_width'] || $imgsize[0] > $cfg['avatar_height']) 
		SimplyBox( "图像尺寸超出了{$cfg['avatar_width']}×{$cfg['avatar_height']}的限制" );

	//删除原有文件
	$handle = @opendir( './images/avatar' );
	if ( $handle )
    {
		while ( ( $file = @readdir( $handle ) ) !== false )
		{
	        if(!is_dir($file))
			{
				$file1 =  explode('.',$file);
				if( $file1[0] == "m_{$udata['u_id']}")
					unlink( "./images/avatar/{$file}");
			}
         }
	    @closedir($handle);
	}
	//保存文件
	$fext = explode( '.',$_FILES['userface']['name']);
	$fext = array_pop($fext);
	$facefile = "m_{$udata['u_id']}.{$fext}";
	$facepath = "./images/avatar/{$facefile}";
	if( !move_uploaded_file($_FILES['userface']['tmp_name'],$facepath))
		SimplyBox( '上传失败!');
    chmod($facepath,0644);
	eval ('echo "'.LoadTemplate('m_upload2').'";');
	exit();
}
?>
