<?

header("Content-Language: charset=zh-cn");
header("Content-type: text/html; charset=utf-8");

$cfg['rbb_name']='浙江慢棋系统';
$cfg['dateformat']='y-m-d H:i';
$cfg['rules']=array('SAKATA','RIF','两次交换');

$openingname=array('-',"D1 寒星", "D2 溪月", "D3 疏星","D4 花月", "D5 残月",
	"D6 雨月", "D7 金星","D8 松月", "D9 丘月", "D10 新月","D11 瑞星","D12 山月","D13 游星",
	"I1 长星", "I2 峡月", "I3 恒星", "I4 水月", "I5 流星","I6 云月", "I7 浦月", "I8 岚月",
	"I9 银月", "I10 明星","I11 斜月","I12 名月","I13 彗星");

//单词
$str['admin']='管理员';
$str['error']='错误';
$str['empty']='空';
$str['all']='全部';
$str['day']=' 天';
//错误提示
$str["act_noguest"]='您现在是游客，请先登陆.';
$str['act_err']='无法识别的操作';
$str['act_fail']='抱歉，这个操作没有成功';
$str['page_not_found']='页面不存在';
$str['req_content']='请填写内容';

//head & left
//$str['forum_jump']='版块跳转';/////////////
$str['main_page']='首页';//////////////
$str['logout']='退出';

/*
$str['login']='用户登陆';
$str['register']='账号注册';
$str['get_pass']='取回密码';
//$str['guest']='游客';
$str['g_new']='创建新局';
$str['ng_search']='查找新局';
$str['g_search']='查找棋局';
$str['cp_view']='查看比赛';
$str['ng_my']='我的新局';
$str['g_my']='我的棋局';
$str['og_my']='我的旧局';
$str['pm_my']='我的消息';
$str['friends_my']='我的好友';
$str['m_man']='个人设置';
//$str['search_post']='搜索贴子';
$str['ranking']='用户排名';
$str['help_doc']='帮助文档';
*/
$str['help']='帮助';
$str['close_window']='关闭窗口';
$str['go_back']='返回';
$str['general_msg']='提示信息';


//game
$str['g_not_found']='棋局不存在';

//cp
$str['cp_view']='查看比赛';

?>
