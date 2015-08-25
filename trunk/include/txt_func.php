<?
//适应中文的substr
function CSubStr($src,$start,$len)
{
	$strlen=strlen($src);
	if ($start>=$strlen)
		return $src;
	$clen=0;
    $tmpstr='';
	for($i=0;$i<$strlen;$i++,$clen++)
	{
		if(ord(substr($src,$i,1))>0xa0)
		{
			if ($clen>=$start)
				$tmpstr.=substr($src,$i,2);
			$i++;
			$clen++;
		}
		else
		{
			if ($clen>=$start)
			$tmpstr.=substr($src,$i,1);
		}
		if ($clen>=$start+$len)
			break;
	}
	return $tmpstr;
}

//输出前的处理
function BBCoding( $src, $code=false, $boardcolor='FFFFFF')
{
	$src = htmlspecialchars($src,ENT_QUOTES,'UTF-8');
	$src = str_replace("\t"," &nbsp;&nbsp;&nbsp;",$src );
	$src = str_replace('  ',' &nbsp;', $src );
	$src = preg_replace('/\n[\s]*\n/','<br />', $src );//连续空行改为单行
	$src = nl2br( $src );

	if( !$code ) return $src;

	//smiles
	$src=str_replace('：）',':)',$src);
	$src=str_replace('：（',':(',$src);

	$src=str_replace(':)','<img src="./images/smiles/smile.gif" />',$src);
	$src=str_replace(':(','<img src="./images/smiles/sad.gif" />',$src);
	$src=str_replace(':o','<img src="./images/smiles/shocked.gif" />',$src);
	$src=str_replace(':O','<img src="./images/smiles/shocked.gif" />',$src);
	$src=str_replace(':D','<img src="./images/smiles/biggrin.gif" />',$src);
	$src=str_replace(':p','<img src="./images/smiles/tongue.gif" />',$src);
	$src=str_replace(':P','<img src="./images/smiles/tongue.gif" />',$src);
	$src=str_replace(':?','<img src="./images/smiles/question.gif" />',$src);
	$src=str_replace(':cool:','<img src="./images/smiles/cool.gif" />',$src);
	$src=str_replace(':mad:','<img src="./images/smiles/mad.gif" />',$src);


	global $color;
	$code_format = array(
		"/\[b](.*)\[\/b]/isU",
		"/\[i](.*)\[\/i]/isU",
		"/\[u](.*)\[\/u]/isU",
		"/\[size=([1-7])](.*)\[\/size]/isU",
		"/\[color=(#?[\da-fA-F]{6})](.*)\[\/color]/isU",
		"/\[color=([a-zA-Z]{3,20})](.*)\[\/color]/isU",
		
		"/\[quote](.*)\[\/quote]/isU",
		"/\[img]([^'\"\?\&\+]*(\.gif|jpg|jpeg|png|bmp))\[\/img]/iU",
		"/\[url]www.([^'\"]*)\[\/url]/iU",
		"/\[url]([^'\"]*)\[\/url]/iU",
		"/\[url=www.([^'\"\s]*)](.*)\[\/url]/iU",
		"/\[url=([^'\"\s]*)](.*)\[\/url]/iU",
		"/\[email]([^'\"\s]*)\[\/email]/iU",
		"/\[email=([^'\"\s]*)](.*)\[\/email]/iU",
	
		"/\[gl]([0-9]{1,10})\[\/gl]/iU",
		"/\[g]([A-O]*)\[\/g]/iU",
		"/\[g=(.*)]([A-O]*)\[\/g]/iU"
		);
	$html_format = array(
		"<b>\\1</b>",
		"<i>\\1</i>",
		"<u>\\1</u>",
		"<font size=\"\\1\">\\2</font>",
		"<font color=\"\\1\">\\2</font>",
		"<font color=\"\\1\">\\2</font>",
		"<blockquote>引用:<br /><font color={$color['quote']}>\\1</font></blockquote>",
		"<img src=\"\\1\" onload=\"if(this.height>100) this.height=100;\" onmouseover=\"this.style.cursor='hand';\" onclick=\"window.open('\\1');\" /> ",
		"<a href=\"http://www.\\1\">www.\\1</a>",
		"<a href=\"\\1\">\\1</a>",
		"<a href=\"http://www.\\1\">\\2</a>",
		"<a href=\"\\1\">\\2</a>",
		"<a href=\"mailto:\\1\">\\1</a>",
		"<a href=\"mailto:\\1\">\\2</a>",

		"[<a href=\"g_view.php?gid=\\1\">第\\1桌</a>]",
		"<p><applet code=\"RBBApplet.class\" width=\"408\" height=\"408\"><param name=\"moves\" value=\"\\1\"><param name=\"bgcolor\" value=\"$boardcolor\"></applet></p>",
		"<p>\\1<br /><applet code=\"RBBApplet.class\" width=\"408\" height=\"408\"><param name=\"moves\" value=\"\\2\"><param name=\"bgcolor\" value=\"$boardcolor\"></applet></p>"
		);
	$src = preg_replace( $code_format, $html_format, $src);

	return $src;
}

//输入的处理
function BBInputFilter($src,$maxline=-1)
{
	global $cfg;

	$src = str_replace("\x0E", '?', $src);
	$src = str_replace('', '?', $src);
	$src = str_replace('　', '  ', $src);//全角空格变为2个半角空格
	//脏话过滤

//	if(is_string($cfg['badwords']))
	//	$cfg['badwords']=$cfg['badwords']==''?array() : explode('|',$cfg['badwords']);

//	foreach($cfg['badwords'] as $bw)
//		$src = str_replace($bw,substr('????????????????????',0,strlen($bw)),$src);
	//行数检查
	if($maxline>0 && substr_count($src,"\n")>=$maxline)
	{
		$src= str_replace("\n",' ',str_replace("\r\n",' ',$src));
	}

	return trim($src);
}
?>