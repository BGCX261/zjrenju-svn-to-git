<?php
require_once('./include/common.php');
SetNoUseCache();

if(!isset($cp_id)) MessageBox($str['act_err']);
$cp_id=intval($cp_id);
if(!file_exists("./cpdata/$cp_id.html"))
	MessageBox($str['page_not_found']);

ShowHeader('<img src="./images/renju.gif" /> '.$str['cp_view']);
echo implode( '', file( "./cpdata/$cp_id.html" ));
ShowFooter();

?>
