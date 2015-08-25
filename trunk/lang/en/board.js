function MoveBack()
{
	if( curstep <= 1 ) return;
	postd = eval( "document.all.stone" + curstep );
	postd.src=imgair;
	curstep = curstep - 1;
	postd = eval( "document.all.stone" + curstep );
	if( curstep % 2 ) postd.src = black_f;
	else postd.src = white_f;
	if( step5 >0)document.all.stone230.src = imgair;
	if( step5 >1)document.all.stone231.src = imgair;

	document.all.bf_status.value = curstep.toString(10) + "/" + mcount.toString(10);
}

function MoveForward()
{
	if( curstep >= mcount ) return;
	postd = eval( "document.all.stone" + curstep );
	if( curstep % 2 ) postd.src = black;
	else postd.src = white;
	curstep = curstep+1;
	postd = eval( "document.all.stone" + curstep );
	if( curstep % 2 ) postd.src = black_f;
	else postd.src = white_f;

	if( curstep >= mcount )
	{
		if( step5 >0)document.all.stone230.src = pos5a;
		if( step5 >1)document.all.stone231.src = pos5b;
	}
	document.all.bf_status.value = curstep.toString(10) + "/" + mcount.toString(10);
}

function MoveToBegin()
{
	while( curstep > 1 )
		MoveBack();
}

function MoveToEnd()
{
	while( curstep < mcount )
		MoveForward();
}

function PosClicked( pos )
{
	if( curstep < mcount)
	{
		MoveToEnd();
		return;
	}
	x = ( parseInt( pos ) - 1 ) % 15 + 65;
	y = 15 - Math.floor( ( parseInt( pos ) - 1 ) / 15 );

	strpos = String.fromCharCode( x ) + y.toString(10);
	
	if( mcount>2)
	{
		if( !window.confirm( strpos+"?" ) )
			return;
	}
	document.location.href ="g_man.php?action=go&gid="+thisgid+"&pos=" + pos+"&checkcode="+checkcode;
}

function ConfirmUndo( backto )
{
	mto=window.prompt("Enter the step number:", backto );
	if(mto)
	{
		if(mto<1 || mto>mcount) 
		{
			window.alert("Can not back to step "+mto);
			return;
		}
		document.location.href = "g_man.php?action=undo1&gid="+thisgid+"&mto=" + mto+"&checkcode="+checkcode;
	}
}
function ConfirmResign( )
{
	if( window.confirm( "Resign?" ) )
		document.location.href = "g_man.php?gid="+thisgid+"&action=resign"+"&checkcode="+checkcode;
}
function ConfirmDraw( )
{
	if( window.confirm( "Make draw game?" ) )
		document.location.href = "g_man.php?gid="+thisgid+"&action=draw"+"&checkcode="+checkcode;
}
function ConfirmUndo2( backto )
{
	if( window.confirm( "Back to step "+backto+"?" ) )
		document.location.href= "g_man.php?gid="+thisgid+"&action=undo2"+"&checkcode="+checkcode;
}
function ConfirmSwap()
{
	if( window.confirm( "swap color?" ) )
		document.location.href = "g_man.php?gid="+thisgid+"&action=swap&checkcode="+checkcode;
}
