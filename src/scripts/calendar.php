<?
include_once(__DIR__.'/../../vendor/autoload.php');


use Gimi\myFormsTools\PckmyFields\myDate;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\mySelect;

$dizionario=new Gimi\myFormsTools\myDizionario($_GET['lang']);
$dizionario->set_case(false);
				


function  print_calendar($mon,$year,$value,$min,$max)
	{
		global $dates,  $start_day,$dizionario;
		$mesi=array(1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre');
		foreach ($mesi as &$mese) $mese=ucfirst($dizionario->trasl($mese));
		
		if ($min) {$annomin=explode('-',$min);
				   $annomin=$annomin[0];
				  }
			 else $annomin=date('Y')-5;  
		if ($max) {$annomax=explode('-',$max);
				   $annomax=$annomax[0];
				  }
			 else $annomax=date('Y')+5;  	  
		//echo "$annomin $annomax";	 
        for ($i=$annomin; $i<=$annomax;$i++) $anni[$i]=$i;			 
        $Anni=new mySelect('year',$year,$anni);
		$Anni->SetReloadJS('completo');
		$Anni->clean_value();
		if ($year!=$Anni->get_value()) {$year=$annomax;
										$Anni->set_value($annomax);
										}
		
		$no_days_in_month =  myDate::giorni_mese($mon,$year);
	#	$first_day = 1; 
		$start_day =  myDate::get_numero_giorno_settimana("1/$mon/$year");
		
		
		//If month's first day does not start with first Sunday, fill table cell with a space
		for ($i = 1; $i <= $start_day;$i++)	$dates[1][$i] = " ";
		$row = 1;
		$col = $start_day+1;
		$num = 1;
		while($num<=31)
			{
				if ($num > $no_days_in_month)
					 break;
				else
					{
						$dates[$row][$col] = $num;
						if (($col + 1) > 7)
							{
								$row++;
								$col = 1;
							}
						else
							$col++;
						$num++;
					}//if-else
			}//while
			
			
		$mon_num = $mon;
	#	$temp_yr = $next_yr = $prev_yr = $year;

		$prev = $mon_num - 1;
		$next = $mon_num + 1;

		//If January is currently displayed, month previous is December of previous year
		if ($mon_num == 1)
			{
				$prev_yr = $year - 1;
				$prev = 12;
			}
    
		//If December is currently displayed, month next is January of next year
		if ($mon_num == 12)
			{
				$next_yr = $year + 1;
				$next = 1;
			}


		echo "
		<TABLE  CELLSPACING=0 width=100%  border=0 class=none> 
		<TR ALIGN='center' class=meseAnno>
			<TD colspan=3 width='43%'>";
	
		if  (!$min || sprintf("%04d-%02d-%02d", $prev_yr,$prev,31)>=$min) echo "<a href='calendar.php?lang=$_GET[lang]&month=$prev&year=$prev_yr&query=".base64_encode(serialize($_GET))."' STYLE=\"text-decoration: none;font-size:70%;vertical-align:bottom;line-height:75%\"><B>{$dizionario->trasl('Mese precedente')}<br><<<<&nbsp;</B></A>";
																	else echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		
		echo "</TD>
			<TD COLSPAN=1 width='14%'><B>".ucfirst($mesi[(int)$mon])." ".$Anni->get_Html()."</B></TD>
			<TD colspan=3 width='43%'>";
		
		if  (!$max || sprintf("%04d-%02d-%02d", $next_yr,$next,1)<=$max) echo "<a href='calendar.php?lang=$_GET[lang]&month=$next&year=$next_yr&query=".base64_encode(serialize($_GET))."' STYLE=\"text-decoration: none;font-size:70%;vertical-align:bottom;line-height:75%\"><B>{$dizionario->trasl('Mese successivo')}<br>&nbsp;>>>></B></A>";
																	else echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		
																	
		echo "</TD>
		</TR>
		</table>
		<TABLE  CELLSPACING=0 width=100%  border=0>	
		<TR ALIGN='center' class='giorniSettimana'>";																	
																	
		$d=new mydate('','1/4/2007');
		$d->set_lingua($_GET['lang']);
		$i=0;
		while($i<7) {$giorno_sett=ucfirst(substr($d->get_nome_giorno_settimana($d->sposta_data($i++)),0,3));
					 echo "<TD><B>$giorno_sett</B></TD>";
					}
		echo "
		</tr>
		<TR><TD COLSPAN=7> </TR>";
				
		$end = ($start_day > 4)? 6:5;
		$count=0;
		for ($row=1;$row<=$end;$row++)
			{echo "<tr>";
				for ($col=1;$col<=7;$col++)
						{
						if ($dates[$row][$col] == "") $dates[$row][$col] = " ";
						if (!strcmp($dates[$row][$col]," ")) $count++;
						
						$t =$dates[$row][$col];	
						$effetto='';
						$classe="class='giorno'";
						$day=$t;
						if (trim((string) $t)!='') {
							$data=sprintf("%04d-%02d-%02d", $year,$mon,$t);
							if ($data==$value) {$classe="class='giornoevidenziato'";
												$t="<b><i>$t</i></b>";
												}
							if  ((!$min || $data>=$min) && (!$max || $data<=$max) )	
								{$dataF=sprintf("%02d/%02d/%04d",$day,$mon ,$year);
								 $t="<a href=#>$t</a>";
								 $effetto="onmouseover=\"this.className='giornoevidenziato';\" onmouseout=\"this.className='giorno'\" onclick=\"assegna('$dataF')\"";
								}
							}	
						echo "<TD $effetto $classe align=center>$t</TD>";
					}// for -col
				
				echo "</TR>";
										
			}// for - row
		
		while (++$row<=7) echo "<tr><td>&nbsp;</td></tr>";	
		echo "\n</TABLE>";
	}



function riformatta_data($data) {
 $data=explode('/',$data);	
 return sprintf("%04d-%02d-%02d", $data[2],$data[1],$data[0]);	
}	
	

if ($_GET['old_val']) $_GET['value']=$_GET['old_val'];


//echo "<pre>"; print_r($_GET);

if ($_GET['query']) {
					 $_GET=array_merge((array) unserialize(base64_decode($_GET['query'])),$_GET);
					 unset($_GET['query']);	
					}
					
$month=$year=$day='';					
foreach ($_GET as $id=>$val) {  $$id=$val;
								if (preg_match("#^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$#", $val)) $_GET[$id]=riformatta_data($val);
							  }
$d=new MyDate('',$_GET['value']);
$d->set_notnull();
if ($d->Errore()) $_GET['value']=date('Y-m-d');				
if (!$month) {
			 $d=explode('-',$_GET['value']);
			 $month=$d[1];
			 }			
if (!$_GET['css']) $css= '/'.myField::get_MyFormsPath().'css/calendar.css';
			  else $css=$_GET['css'];
	
//echo "<pre>"; print_r($_GET);
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-it" lang="it-it">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-88591" />
'."
	<title>".($_GET['titolo']?$_GET['titolo']:'.')."</title>
<link rel=\"stylesheet\" href=\"$css\">
<script>
function assegna(valore) {
	var elemento = opener.document.getElementById('{$_GET['id']}');
	elemento.value=valore;
	elemento.focus();
	close();
}
</script>

</head>
<body  onload=\"focus();\" topmargin='0' leftmargin='0' rightmargin='0' bottommargin='0' class='giorno'>
";
	
if (!$year) sscanf($_GET['value'],"%04d-%2d-%2d", $year, $month, $day);	
print_calendar($month,$year,$_GET['value'],$_GET['min'],$_GET['max']);
?>
</body>
</html>