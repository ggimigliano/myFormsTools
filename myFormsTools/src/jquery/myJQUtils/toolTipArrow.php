<?php
function hexParti($c){
	$c=strtr(strtolower($c),array('white'=>'ffffff',
								  'black'=>'000000',
								  'red'=>'ff0000',
								  'blue'=>'00ff00',
								  'maroon'=>'800000',
						  	  	  'purple'=>'800080',
						  	  	  'fuchsia'=>'ff00ff',
						  	  	  'green'=>'008000',
						  	  	  'lime'=>'00ff00',
						  	  	  'olive'=>'808000',
						  	  	  'yellow'=>'ffff00',
						  	  	  'navy'=>'000080',
						  	  	  'teal'=>'008080',
						  	  	  'aqua'=>'00ffff',
								  'silver'=>'c0c0c0',
								  'gray'=>'808080 '
								));
	$c=str_replace('#','',$c);
	return array(hexdec(substr($c,0,2)),hexdec(substr($c,2,2)),hexdec(substr($c,4)));
}

function imageBoldLine($resource, $x1, $y1, $x2, $y2, $Color, $BoldNess=2, $func='imageLine')
{
	$center = round($BoldNess/2);
	for($i=0;$i<$BoldNess;$i++)
	{
		$a = $center-$i; if($a<0){$a -= $a;}
		for($j=0;$j<$BoldNess;$j++)
		{
			$b = $center-$j; if($b<0){$b -= $b;}
			$c = sqrt($a*$a + $b*$b);
			if($c<=$BoldNess)
			{
				$func($resource, $x1 +$i, $y1+$j, $x2 +$i, $y2+$j, $Color);
			}
		}
	}
}

$lato=$_GET['l'];
$spessore=$_GET['b'];
$c=hexParti($_GET['c']);
$s=hexParti($_GET['s']);

// Create the image handle, set the background to white
$im = imagecreatetruecolor($lato, $lato);



imagefill($im, 0, 0, imagecolortransparent($im,  imagecolorallocate($im,($s[0]+$c[0])%256, ($s[1]+$c[1])%256, ($s[2]+$c[2])%256)));




$c=hexParti($_GET['c']);
$colore = imagecolorallocate($im, $c[0], $c[1], $c[2]);


$lato--;
imageLine($im, 0, 0, $lato, $lato, $colore);
imageLine($im, 0, $lato,$lato, 0,  $colore);

imageLine($im, $spessore,0, round($lato/2), round($lato/2)-$spessore, $colore);
imageLine($im, round($lato/2), round($lato/2)-$spessore, $lato-$spessore, 0,  $colore);

imageLine($im, $spessore,$lato, round($lato/2), round($lato/2)+$spessore, $colore);
imageLine($im, round($lato/2), round($lato/2)+$spessore, $lato-$spessore, $lato,  $colore);


$sfondo = imagecolorallocate($im, $s[0], $s[1], $s[2]);

imagefilltoborder($im, $spessore/2, 0,  $colore,  $colore);
imagefilltoborder($im, $spessore/2, $lato, $colore, $colore);

imagefilltoborder($im, $lato /2, 0,  $colore,  $sfondo);
imagefilltoborder($im, $lato/2, $lato, $colore, $sfondo);
// Output and free memory

ob_start();
	imagegif($im);
	$image=ob_get_clean();
imagedestroy($im);
header('Content-Type: image/gif');
header("Pragma:");
header("Cache-Control: max-age=3600");
header("Expires: " . gmdate("D, d M Y H:i:s",time()+3600) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . " GMT");
header("Content-Length: ".strlen($image));
echo $image;
exit;
?>