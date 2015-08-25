<?php
require_once('./include/common.php');

ShowHeader('<img src="./images/help.gif" /> 帮助文档');
eval ("echo \"".LoadTemplate("help")."\";");
ShowFooter();
?>