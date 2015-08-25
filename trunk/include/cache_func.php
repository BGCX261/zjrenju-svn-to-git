<?php

function Rebuild_Global_Cache($insubdir=false)
{
	global $cfg;

	$buf ="<?php\n";
	//缓存banip
	$sql = "SELECT * FROM `$cfg[tb_banips]` ";
	$result = RenDB_Query($sql);
		
	$banip_S=array();
	$banip_D=array();
	if(RenDB_Num_Rows($result)>0 )
	{
		while($row=RenDB_Fetch_Array( $result ))
		{
			$cell=explode('.',$row['ban_ip']);
			if($cell[3]=='*') 
			{
				unset($cell[3]);
				$banip_D[]='\''.implode('.',$cell).'\'';
			}
			else $banip_S[]="'$row[ban_ip]'";
		}
	}

	$banip_S=implode(', ',$banip_S);
	$banip_D=implode(', ',$banip_D);

	$buf .="\$RBB_CACHE['banip_S'] = array($banip_S);\n";
	$buf .="\$RBB_CACHE['banip_D'] = array($banip_D);\n";
		
	/*
	//缓存论坛数据
	$buf .="\n";
	$sql ="SELECT * FROM $cfg[tb_forums] ORDER BY displayorder";
	$result = RenDB_Query($sql);

	$num=0;
	while( $row=RenDB_Fetch_Array( $result ) )
	{
		//$displayorder=$row['displayorder'];
		unset($row['topics']);
		unset($row['posts']);
		unset($row['lastpost']);
		unset($row['lastposter']);
		unset($row['displayorder']);
		//unset($row['fid']);
		unset($row['description']);
		$fd=array();
		foreach( $row as $k=>$v )
			$fd[]='"'.addslashes($k).'"=>"'.addslashes($v).'"';
		$fd=implode(', ',$fd);
		$buf .="\$RBB_CACHE['forums'][$num] = array($fd);\n";
		$num++;
	}
	if($num==0) $buf .="\$RBB_CACHE['forums']=array();\n";
	*/
	//缓存设置
	$buf .="\n";
	$sql ="SELECT * FROM $cfg[tb_settings]";
	$result = RenDB_Query($sql);
	while( $row=RenDB_Fetch_Array( $result ) )
	{
		/*
		if(strpos($row['set_value'],'||')!==false)
		{
			$row['set_value']=explode('||',$row['set_value']);
			foreach($row['set_value'] as $k=>$v)
			{
				$row['set_value'][$k]=str_replace('=>','"=>"',$v);
			}
			$buf .="\$cfg['$row[set_name]']=array(\"".implode('","',$row['set_value'])."\");\n";
		}
		else */
		$row['set_value']=addslashes($row['set_value']);
		$buf .="\$cfg['$row[set_name]']=\"$row[set_value]\";\n";
	}

	$buf .="?>";

	$gc_file=$insubdir ? '../cache/global.php':'./cache/global.php';
	$fr = @fopen($gc_file, 'w' );
	@flock($fr, LOCK_EX);
	@fwrite($fr, $buf);
	@fclose($fr );
}
?>