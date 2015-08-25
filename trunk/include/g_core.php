<?php
//判断禁手赢棋，自动防冲四

define ("FSP_DEAD",	0);
define ("FSP_1",	1);
define ("FSP_2",	2);
define ("FSP_d3",	3);
define ("FSP_3",	4);
define ("FSP_d4",	5);
define ("FSP_4",	6);
define ("FSP_5",	7);
define ("FSP_L",	8);
define ("FSP_44",	9);

define ("FMP_L",	16);

define ("FMP_5",	15);

//define ("FMP_4",	14);
define ("FMP_44",	13);

define ("FMP_433",	12);
//define ("FMP_43",	11);

//define ("FMP_d4p",	9);
//define ("FMP_d4",	8);

define ("FMP_33",	7);
//define ("FMP_3p",	6);
//define ("FMP_3",	5);
//define ("FMP_pp",	4);
//define ("FMP_d3",	3);
//define ("FMP_2",	2);

//define ("FMP_1",	1);
define ("FMP_DEAD",	0);

class RBoard
{
	var $gene;
	var $layer1;
	var $layer2;

	function RBoard()
	{
		////////////////////////////////////
		$this->gene = Array
		(
		Array(1,1,1,1,1),//	-----	0
		Array(1,2,2,2,0),//	----0	1
		Array(2,2,2,0,2),//	---0-	2
		Array(3,4,4,0,0),//	---00	3
		Array(2,2,0,2,2),//	--0--	4
		Array(3,4,0,4,0),//	--0-0	5
		Array(4,4,0,0,4),//	--00-	6
		Array(5,6,0,0,0),//	--000	7
		Array(2,0,2,2,2),//	-0---	8
		Array(3,0,4,4,0),//	-0--0	9
		Array(4,0,4,0,4),//	-0-0-	10
		Array(5,0,6,0,0),//	-0-00	11
		Array(4,0,0,4,4),//	-00--	12
		Array(5,0,0,6,0),//	-00-0	13
		Array(6,0,0,0,6),//	-000-	14
		Array(7,0,0,0,0),//	-0000	15
		Array(0,2,2,2,1),//	0----	16
		Array(0,3,3,3,0),//	0---0	17
		Array(0,4,4,0,3),//	0--0-	18
		Array(0,5,5,0,0),//	0--00	19
		Array(0,4,0,4,3),//	0-0--	20
		Array(0,5,0,5,0),//	0-0-0	21
		Array(0,6,0,0,5),//	0-00-	22
		Array(0,7,0,0,0),//	0-000	23
		Array(0,0,4,4,3),//	00---	24
		Array(0,0,5,5,0),//	00--0	25
		Array(0,0,6,0,5),//	00-0-	26
		Array(0,0,7,0,0),//	00-00	27
		Array(0,0,0,6,5),//	000--	28
		Array(0,0,0,7,0),//	000-0	29
		Array(0,0,0,0,7),//	0000-	30
		Array(0,0,0,0,0),//	00000	31
		//----------*2--------------
		Array(1,1,1,1,0),//	-----x	0
		Array(1,1,1,1,0),//	----0x	1
		Array(2,2,2,0,1),//	---0-x	2
		Array(3,3,3,0,0),//	---00x	3
		Array(2,2,0,2,1),//	--0--x	4
		Array(3,3,0,3,0),//	--0-0x	5
		Array(4,4,0,0,3),//	--00-x	6
		Array(5,5,0,0,0),//	--000x	7
		Array(2,0,2,2,1),//	-0---x	8
		Array(3,0,3,3,0),//	-0--0x	9
		Array(4,0,4,0,3),//	-0-0-x	10
		Array(5,0,5,0,0),//	-0-00x	11
		Array(4,0,0,4,3),//	-00--x	12
		Array(5,0,0,5,0),//	-00-0x	13
		Array(6,0,0,0,5),//	-000-x	14
		Array(7,0,0,0,0),//	-0000x	15
		Array(0,2,2,2,1),//	0----x	16
		Array(0,3,3,3,0),//	0---0x	17
		Array(0,4,4,0,3),//	0--0-x	18
		Array(0,5,5,0,0),//	0--00x	19
		Array(0,4,0,4,3),//	0-0--x	20
		Array(0,5,0,5,0),//	0-0-0x	21
		Array(0,6,0,0,5),//	0-00-x	22
		Array(0,7,0,0,0),//	0-000x	23
		Array(0,0,4,4,3),//	00---x	24
		Array(0,0,5,5,0),//	00--0x	25
		Array(0,0,6,0,5),//	00-0-x	26
		Array(0,0,7,0,0),//	00-00x	27
		Array(0,0,0,6,5),//	000--x	28
		Array(0,0,0,7,0),//	000-0x	29
		Array(0,0,0,0,7),//	0000-x	30
		Array(0,0,0,0,0),//	00000x	31
		//----------*3--------------
		Array(0,1,1,1,1),//	x-----	0
		Array(1,2,2,2,0),//	x----0	1
		Array(1,2,2,0,2),//	x---0-	2
		Array(3,4,4,0,0),//	x---00	3
		Array(1,2,2,0,2),//	x--0--	4
		Array(3,4,0,4,0),//	x--0-0	5
		Array(3,4,0,0,4),//	x--00-	6
		Array(5,6,0,0,0),//	x--000	7
		Array(1,0,2,2,2),//	x-0---	8
		Array(3,0,4,4,0),//	x-0--0	9
		Array(3,0,4,0,4),//	x-0-0-	10
		Array(5,0,6,0,0),//	x-0-00	11
		Array(3,0,0,4,4),//	x-00--	12
		Array(5,0,0,6,0),//	x-00-0	13
		Array(5,0,0,0,6),//	x-000-	14
		Array(7,0,0,0,0),//	x-0000	15
		Array(1,1,1,1,1),//	x0----	16
		Array(0,3,3,3,0),//	x0---0	17
		Array(0,3,3,0,3),//	x0--0-	18
		Array(0,5,5,0,0),//	x0--00	19
		Array(0,3,0,3,3),//	x0-0--	20
		Array(0,5,0,5,0),//	x0-0-0	21
		Array(0,5,0,0,5),//	x0-00-	22
		Array(0,7,0,0,0),//	x0-000	23
		Array(0,0,3,3,3),//	x00---	24
		Array(0,0,5,5,0),//	x00--0	25
		Array(0,0,5,0,5),//	x00-0-	26
		Array(0,0,7,0,0),//	x00-00	27
		Array(0,0,0,5,5),//	x000--	28
		Array(0,0,0,7,0),//	x000-0	29
		Array(0,0,0,0,7),//	x0000-	30
		Array(0,0,0,0,0),//	x00000	31
		//----------*4--------------
		Array(0,0,0,0,0),//	x-----x	0
		Array(1,1,1,1,0),//	x----0x	1
		Array(1,1,1,0,1),//	x---0-x	2
		Array(3,3,3,0,0),//	x---00x	3
		Array(1,1,0,1,1),//	x--0--x	4
		Array(3,3,0,3,0),//	x--0-0x	5
		Array(3,3,0,0,3),//	x--00-x	6
		Array(5,5,0,0,0),//	x--000x	7
		Array(1,0,1,1,1),//	x-0---x	8
		Array(3,0,3,3,0),//	x-0--0x	9
		Array(3,0,3,0,3),//	x-0-0-x	10
		Array(5,0,5,0,0),//	x-0-00x	11
		Array(3,0,0,3,3),//	x-00--x	12
		Array(5,0,0,5,0),//	x-00-0x	13
		Array(5,0,0,0,5),//	x-000-x	14
		Array(7,0,0,0,0),//	x-0000x	15
		Array(0,1,1,1,1),//	x0----x	16
		Array(0,3,3,3,0),//	x0---0x	17
		Array(0,3,3,0,3),//	x0--0-x	18
		Array(0,5,5,0,0),//	x0--00x	19
		Array(0,3,0,3,3),//	x0-0--x	20
		Array(0,5,0,5,0),//	x0-0-0x	21
		Array(0,5,0,0,5),//	x0-00-x	22
		Array(0,7,0,0,0),//	x0-000x	23
		Array(0,0,3,3,3),//	x00---x	24
		Array(0,0,5,5,0),//	x00--0x	25
		Array(0,0,5,0,5),//	x00-0-x	26
		Array(0,0,7,0,0),//	x00-00x	27
		Array(0,0,0,5,5),//	x000--x	28
		Array(0,0,0,7,0),//	x000-0x	29
		Array(0,0,0,0,7),//	x0000-x	30
		Array(0,0,0,0,0),//	x00000x	31
		);
		///////////////////////////////////

		$line = array_fill( 0, 15, 2);
		$this->layer1 = array_fill(0, 15, $line);

		$line = array_fill( 0, 15, 0);
		$map = array_fill(0, 15, $line);
		$this->layer2 = array_fill(0, 2, array_fill(0,4,$map) );
	}
	
	function UpdateLine( $x, $y, $side, $line )
	{
		//正方向增量和起点
		$fep = $this->GetFEP($x,$y,$line);
		if( $fep===false ) return;
		extract( $fep );

		//映射到1维数组，并加上端点，简化计算
		$L1 = array_fill( 0, 17, -1 );
		$L2 = array_fill(0, 17, 0 );

		$n=0;
		$xx = $xb;
		$yy = $yb;
		while(true) 
		{
			if($xx < 0 || $xx > 14 || $yy < 0 || $yy > 14  )
				break;
			$L1[$n+1] = $this->layer1[$xx][$yy];
			$xx += $xplus;
			$yy += $yplus;
			$n ++;
		}


		for($n=1;true;$n++ )
		{
		
			if( $L1[$n] == -1 ) break;//端点
			if( $L1[$n-1] == $side&&$side==1 ) continue;//前面是自己

			$blank = 0; $k = 0;

			for( $i = 0; $i <= 4 ; $i++ )
			{
				$l1_n_i = &$L1[$n+$i];
				if( $l1_n_i != 2 &&  $l1_n_i != $side )
				{
					$n+=$i;
					if( $L1[$n] == -1 ) break 2;
					continue 2;
				}				
				else if( $l1_n_i == $side )
				{
					$k |= 1 << ( 4 - $i );
				}
				else if( $l1_n_i == 2 )
				{
					$blank++;
					if($blank==1) $blank1dis = $i;
				}
				else if	( $l1_n_i == -1 )
				{
					break 2;
				}
			}
			
			if( $L1[$n+5] == $side && $side==1)//这个区右边还有自己的棋子
			{
				if( $blank == 1)
				{
					$L2[$n+$blank1dis] = FSP_L;//长连
				}
			}
			else 
			{
				if( $L1[$n-1] != 2 ) $k |= 64;//左边界
				else if( $L1[$n-2] == $side ) $k |= 64;//因为$n-1是空格，所以$n-2不会越界
				
				if( $L1[$n+5] != 2 ) $k |= 32;//右边界
				else if( $L1[$n+6] == $side )	$k |= 32;
				
				for( $i = 0; $i < 5; $i++ )
				{
					$j = $this->gene[$k][$i];
						$m = $L2[$n+$i];
					if( $j == FSP_d4 && $m == FSP_d4 )//同一线的四四
						$L2[$n+$i] = FSP_44;
					else if( $j > $m ) $L2[$n+$i] = $j;
				}
			}
				//}
			if( $blank >= 1 ) $n+=$blank1dis;
		}

		//将L2映射回layer2
		$n=0;
		$t_l2_l=&$this->layer2[$side][$line];
		$xx =$xb;$yy=$yb;
		while(1) 
		{
			if($xx < 0 || $xx > 14 || $yy < 0 || $yy > 14  )
				break;
			$t_l2_l[$xx][$yy] = $L2[$n+1];
			$xx += $xplus;
			$yy += $yplus;
			$n ++;
		}
	}

	function L2toL3( $x, $y, $side )
	{
		if( $this->layer1[$x][$y] != 2 )//已有棋子了
		{
			return FMP_DEAD;
		}	
	
		$mmm = array_fill( 0, 10, 0 );

		for( $i=0; $i<=3; $i++ )
			$mmm[ $this->layer2[$side][$i][$x][$y] ]++;

		if( $side == 1 )
		{
			if( $mmm[FSP_5] )
			{
				return FMP_5;
			}
			else if($mmm[FSP_L])return FMP_L;
			else if($mmm[FSP_44]||($mmm[FSP_4]+$mmm[FSP_d4])>=2)
			{
				return FMP_44;
			}
			else if( ( $mmm[FSP_4] || $mmm[FSP_d4]  ) &&  $mmm[FSP_3]>=2 )
			{
				return FMP_433;
			}
			else if($mmm[FSP_3]>=2)
			{
				return FMP_33;
			}
			/*
			else if($mmm[FSP_4])
			{
				return FMP_4;
			}
			else if($mmm[FSP_d4])
			{
				return FMP_d4;
			}
			*/
			else return FMP_DEAD;
		}
		else//白方
		{
			if($mmm[FSP_5])
			{
				return FMP_5;
			}
			/*
			else if($mmm[FSP_44]||$mmm[FSP_4]+$mmm[FSP_d4]>=2)
			{
				return FMP_44;
			}
			else if($mmm[FSP_4])
			{
				return FMP_4;
			}
			else if($mmm[FSP_d4])
			{
				return FMP_d4;
			}
			*/
			else return FMP_DEAD;
		}
	}

	function FromMoves( &$moves, $mcount )
	{
		for( $i=0; $i<$mcount; $i++)
		{
			$p = ord($moves[$i])-1;
			$x = $p % 15;
			$y = intval($p / 15 );
			$this->layer1[$x][$y] = 1-$i%2;
		}
	}

	function GetL3( $x, $y ,$side)
	{
		for( $line =0; $line<4; $line++)
			$this->UpdateLine( $x, $y, $side, $line );
		return $this->L2ToL3($x,$y,$side);
	}

	function IsFoul( $x, $y )
	{
		for( $line =0; $line<4; $line++)
			$this->UpdateLine( $x, $y, 1, $line );

		if( $this->layer1[$x][$y] != 2 )return FALSE;

		switch( $this->GetL3($x,$y,1) )
		{
			case FMP_L:
			case FMP_44:
				return TRUE;
			case FMP_33: 
			case FMP_433:
				//return TRUE;
				return $this->Is33Foul( $x, $y );
			default:
				return FALSE;
		}
	}
	
	function Is33Foul( $x, $y )
	{
		$true3 =0;
		for( $i = 0; $i < 4; $i++ )
		{
			if( $this->IsTrue3( $x, $y, $i ) )
			{
				$true3 ++;
				if( $true3 == 2 ) 
					return TRUE;
			}
		}
		return FALSE;
	}

	function  IsTrue3( $x, $y, $line )
	{
		if( $this->layer2[1][$line][$x][$y] != FSP_3 )
			return FALSE;

		$this->layer1[$x][$y]=1;
		$this->UpdateLine( $x, $y, 1, $line );

		$fep = $this->GetFEP($x,$y,$line);
		if( $fep===false ) return;
		extract( $fep );

		$left_x = $x; $left_y = $y;
		for( ;; )
		{

			switch ( $this->layer1[$left_x][$left_y] )
			{
			case 0:
				break 2;
			case 2:
				if( $this->layer2[1][$line][$left_x][$left_y] == FSP_4 )
				{
					if( ! $this->IsFoul( $left_x, $left_y ) )
					{
						$this->layer1[$x][$y]=2;
						return TRUE;
					}
				}
				break 2;
			}

			if( $left_x == $xb && $left_y == $yb ) break;

			$left_x -= $xplus;
			$left_y -= $yplus;
		}


		$right_x = $x;	$right_y = $y;
		for( ;; )
		{
			switch( $this->layer1[$right_x][$right_y] )
			{
			case 0:
				break 2;
			case 2:
				if( $this->layer2[1][$line][$right_x][$right_y] == FSP_4 )
				{
					if( !$this->IsFoul( $right_x, $right_y ) )
					{
						$this->layer1[$x][$y]=2;
						return TRUE;
					}
				}
				break 2;
			}

			if( $right_x == $xe && $right_y == $ye ) break;

			$right_x += $xplus;
			$right_y += $yplus;
		}

		$this->layer1[$x][$y]=2;
		return FALSE;
	}

	/*
	//走一步，并自动防冲四
	//(x,y)- 坐标 side-颜色
	//返回值false -出错
	//ret['code'] - 'normal'|'win'|'foul'|'error'
	//ret['mcount'] - 落子数
	//ret['moves'] - 走法
	function Go( $x, $y, $side )
	{
		if( $x<0||$x>14||$y<0||$y>14||$side<0||$side>1||$this->layer1[$x][$y]!=2 ) 
			return false;

		$ret=array();
		$ret['mcount'] = 1;
		$ret['moves'] =strval(chr($y*15+$x+1));

		$l3 = $this->GetL3($x,$y,$side);

		if($l3==FMP_d4||$l3==FMP_433)
		{
			//记住冲四的那一行
			for($i=0;$i<4;$i++)
			if( FSP_d4 == $this->layer2[$side][$i][$x][$y])
			{
				$line=$i;
				break;
			}
		}

		if( $side == 1)
		{
			switch( $l3 )
			{
			case FMP_L:
			case FMP_44:
				$ret['code']='foul';
				return $ret;
			case FMP_33: 
			case FMP_433:
				if($this->Is33Foul( $x, $y ))
					$ret['code']='foul';
					return $ret;
			}
		}
		
		switch( $l3 )
		{
		case FMP_44:
		case FMP_4:
		case FMP_5:
			$ret['code']='win';
			return $ret;
		case FMP_d4:
		case FMP_433:
			$this->layer1[$x][$y] = $side;
			//查找冲5点
			$p=$this->Find5($x,$y,$side,$line);
			if( $p===false) return false;

			$ret2=$this->Go($p['x'],$p['y'],1-$side);
			if( $ret2===false) return false;

			$ret['code']=$ret2['code'];
			$ret['mcount']=$ret2['mcount']+1;
			$ret['moves'].=$ret2['moves'];
			return $ret;
		}
		$ret['code']='normal';
		return $ret;
	}
	*/

	//ret: 'normal'|'win'|'foul'|'
	function Go( $x, $y, $side )
	{
		if( $x<0||$x>14||$y<0||$y>14||$side<0||$side>1||$this->layer1[$x][$y]!=2 ) 
			return false;

		$l3 = $this->GetL3($x,$y,$side);

		if( $side == 1)
		{
			switch( $l3 )
			{
			case FMP_L:
			case FMP_44:
				return 'foul';
			case FMP_33: 
			case FMP_433:
				if($this->Is33Foul( $x, $y ))
					return 'foul';
			}
		}
		
		if($l3 == FMP_5 ) return 'win';
		return 'normal';
	}

	function Find5( $x, $y, $side, $line )
	{
		$this->UpdateLine( $x, $y, $side, $line );

		$fep = $this->GetFEP($x,$y,$line);
		if( $fep===false ) return false;
		extract( $fep );

		$xx=$xb; 
		$yy=$yb;
		while(1) 
		{
			if($xx < 0 || $xx > 14 || $yy < 0 || $yy > 14  )
				break;
			//trigger_error("$side,$line,$xx,$yy", E_USER_ERROR);
			if($this->layer2[$side][$line][$xx][$yy] == FSP_5 )
				return array('x'=>$xx,'y'=>$yy);

			$xx += $xplus;
			$yy += $yplus;
		}
		return false;
	}

	function GetFEP($x,$y,$line)
	{
		switch($line)
		{
		case 0://横
			$xplus=1;$yplus=0;
			$xb=0;$yb=$y;
			$xe=14;$ye=$y;
			break;
		case 1:
			$xplus=0;$yplus=1;
			$xb=$x;$yb=0;
			$xe=$x;$ye=14;
			break;//竖
		case 2://撇
			$xpy = $x+$y;
			if( $xpy<4 || $xpy>24) return false;
			if( $xpy>14 )
			{
				$xb=14; $yb=$xpy-14;
				$xe=$xpy-14; $ye=14;
			}
			else
			{
				$xb=$xpy; $yb=0;
				$xe=0; $ye=$xpy;
			}
			$xplus=-1;$yplus=1;
			break;
		case 3:
			if( $x>$y )
			{
				if( $x-$y>10 ) return false;
				$xb=$x-$y; $yb=0;
				$xe=14; $ye=14+$y-$x;
			}
			else
			{
				if( $y-$x>10 ) return false;
				$xb=0; $yb=$y-$x;
				$xe=14+$x-$y; $ye=14;
			}
			$xplus=1;$yplus=1;
			break;//捺
		}
		return array('xb'=>$xb,'yb'=>$yb, 'xe'=>$xe, 'ye'=>$ye, 'xplus'=>$xplus, 'yplus'=>$yplus);
	}
}

?>