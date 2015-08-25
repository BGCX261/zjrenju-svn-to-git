<?
function Add_Win($dr,$w)
{
	return round(32*($w-(1/(pow(10,(-$dr/400))+1))),1);
}
echo '假设你的分数比对方高<br /><table border="1"><td>分差</td><td>胜</td><td>平</td><td>负</td>';
for($dr=0;$dr<=1075;$dr+=25)
{
	echo '<tr>';
	echo "<td>$dr</td>"; 
	echo '<td>'.Add_Win($dr,1).'</td>';
	echo '<td>'.Add_Win($dr,0.5).'</td>';
	echo '<td>'.Add_Win($dr,0).'</td>';
	echo "</tr> ";
}
echo '</table>';
?>