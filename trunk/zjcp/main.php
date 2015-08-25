<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

$phpversion=phpversion();
$mysqlversion=mysql_get_server_info();
ACP_ShowHeader('面版首页');
eval ('echo "'.LoadTemplate('main').'";');
ACP_ShowFooter();

?>