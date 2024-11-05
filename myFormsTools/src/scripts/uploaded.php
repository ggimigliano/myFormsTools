<?
include_once(__DIR__.'/../../vendor/autoload.php');


use Gimi\myFormsTools\PckmyFields\myUploadText;


$f=new myUploadText($_GET['name'],$_POST[$_GET['name']]);
$f->set_ext($_GET['ext'])->set_Descrizione($_GET['desc']?$_GET['desc']:$_GET['name']);

header("Pragma: 0",true);
header("Expires: 0");
header("Cache-Control: private, must-revalidate",true);
header("Content-Transfer-Encoding: binary");
header("Date: ".gmdate('D, d M Y H:i:s', time()) . ' GMT');
header("Content-Disposition: attachment; filename=\"{$f->get_Descrizione()}.{$f->get_ext()}\"");
header("Content-Type: ".myUploadText::get_MymeType($f->get_ext()));
header("Content-Length: ".strlen($f->get_value()));
die($f->get_value());
?>