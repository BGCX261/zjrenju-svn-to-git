<?php
if (!defined('RBB_ADMINCP')) die('Fatal error.');
SetNoUseCache();

setcookie('admin_name','',time()-36000);
setcookie('admin_pass','',time()-36000);
header('Location: index.php');

?>