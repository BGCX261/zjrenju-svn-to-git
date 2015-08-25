<?php


//数据库设置，如果不知道怎么设，请向您的空间提供商咨询
$cfg['db_host'] = 'localhost'; //数据库服务器名字，一般是'localhost'
$cfg['db_user'] = 'root'; //数据库用户名
$cfg['db_pass'] = '116674428'; //数据库密码
$cfg['db_name'] = 'zjrenju'; //数据库名字

//表的名字_一般不需更改
$cfg['tb_members'] = 'rbb_members';
$cfg['tb_pms'] = 'rbb_pms';
$cfg['tb_newgames'] = 'rbb_newgames';
$cfg['tb_games'] = 'rbb_games';
$cfg['tb_onlines'] = 'rbb_onlines';
$cfg['tb_banips'] = 'rbb_banips';
$cfg['tb_settings'] = 'rbb_settings';
$cfg['tb_chats'] = 'rbb_chats';
$cfg['tb_competitions'] = 'rbb_competitions';
$cfg['tb_groups'] = 'rbb_groups';
$cfg['tb_players'] = 'rbb_players';

//超级管理员名字，多个管理员用'|'分隔
$cfg['admins']='weigui|rbb';

//RBB的版本
$cfg['rbb_version']='内部测试版';	


//以下这段请不要更改,否则很可能会出问题
//////////////////////////////////////////////////////////////
$cfg['maxapply']='5';	//每个新桌最多申请人数	**不要更改**
$cfg['maxbio']='253';	//最大自我介绍长度		**不要更改**
$cfg['maxsig']='253';	//最大签名长度			**不要更改**
$cfg['maxmsg']='253';	//最大消息长度			**不要更改**
$cfg['max_friends']='240';  //最大好友列表长度	**不要更改**
$cfg['max_blacklist']='240'; //最大黑名单长度	**不要更改**
//$cfg['span_cookie']='360000'; //cookies的保留时间，目前未使用
//////////////////////////////////////////////////////////////



//board style
$boards['800']=array( 'name'=>'HTML 800*600','id'=>'800','width'=>18);
$boards['1024']=array( 'name'=>'HTML 1024*768','id'=>'1024','width'=>32);


//颜色设置
$color['left'] = '';	//左边导航栏的底色
$color['page'] = 'ddeeff';	//页面底色
$color['border'] = 'ccddff'; //表格边框
$color['quote'] = 'aa4455';	//引用的文字
$color['hl1'] = '990000'; //强调的文字
//$color['hl2'] = '990000';
$color['heading'] = $color['left']; //标题栏
//$color['grades']= array('000000','000000','000055','000055','553300','553300','553300','990000','990000','990000');
$color['cell']= '';	//普通表格底色
$color['cell1']= '';	//表格底色1
$color['cell2']= '';  //表格底色2
?>
