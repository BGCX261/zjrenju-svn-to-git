<?php
function myErrorHandler ($errno, $errstr, $errfile, $errline)
{
	global $cfg;
	if(($line=file('./log/errors.php'))===FALSE)return;

	$logkeep=max(10,$GLOBALS['cfg']['max_log']);
	$logkeep=min(500,$logkeep);
	unset($line[0]);
	$line=array_slice($line,0,$logkeep-1);
	array_unshift($line,TimeToDate(time(),true)." #{$errno} $errstr {$errfile}[{$errline}]\r\n");
	$line="<?php die(); ?>\r\n".implode('',$line);
	
	@$fr = fopen( './log/errors.php', 'w' );
	@flock($fr, LOCK_EX);
	@fwrite($fr,$line);
	@fclose($fr);
}
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_error_handler("myErrorHandler");
?>