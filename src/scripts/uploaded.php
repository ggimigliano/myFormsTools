<?

include_once(__DIR__.'/../PckmyFields/myTag.php');
include_once(__DIR__.'/../PckmyFields/myField.php');
include_once(__DIR__.'/../PckmyFields/mySelect.php');
include_once(__DIR__.'/../PckmyFields/myUploadText.php');


use Gimi\myFormsTools\PckmyFields\myUploadText;
error_reporting(0);
$file=@unserialize(myUploadText::ppnDecrypt(isset($_GET['file'])?$_GET['file']:''));
if(!$file) exit; 
if(isset($file['fromPOST']) && isset($_POST[$file['fromPOST']])) $f=@gzuncompress(@base64_decode($_POST[$file['fromPOST']])); 


header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header("Content-Transfer-Encoding: binary");
header("Date: ".gmdate('D, d M Y H:i:s', time()) . ' GMT');
header('Content-Disposition: attachment; filename="'. rawurlencode($file['name']).'"');
header("Content-Disposition: ".(isset($_GET['inline'])?"inline":"attachment").'; filename="'. rawurlencode($file['name']).'"');

header("Content-Type: ".$file['type']);
header("Content-Length: ".$file['size']);

if($f) die($f);
  else readfile($file['tmp_name']);
?>