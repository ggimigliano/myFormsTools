<?php
use Gimi\myFormsTools\PckmyPlugins\myAjaxFileManagerPlugin;
use Gimi\myFormsTools\PckmySessions\mySessions;

header('Content-Type: text/html; charset=UTF-8');
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT &  ~E_WARNING);
if(isset($_SERVER[__FILE__]) && is_array($_SERVER[__FILE__])) return $_SERVER[__FILE__];
 
if(!class_exists('myAjaxFileManagerPlugin_start_stop',false)) {
    class myAjaxFileManagerPlugin_start_stop{
                                static $me;
                                 public function __construct($start,$stop){
                                    if($start) eval($start);
                                    $this->stop=$stop;
                                    self::$me=$this;
                                }
                                
                                 public function __destruct(){
                                    if($this->stop) eval($this->stop);
                                }
                            }
}

include_once __DIR__.'/../../../../../myPlugins/myAjaxFileManagerPlugin.php';
include_once __DIR__.'/../../../../../mySessions.php';
$myAjaxLoader=new myAjaxFileManagerPlugin('');
$s=new mySessions(__DIR__);
$myPars=$s->get('par');


$myConfig=array('default_language' 	=> "it", //default language file name
                'icon_theme'	=> "ico", //ico or ico_dark you can cusatomize just putting a folder inside filemanager/img
                'convert_spaces'  	=> true, //convert all spaces on files name and folders name with $replace_with variable
                'replace_with'  	    => "_", //convert all spaces on files name and folders name this value

                'default_view' => 1,
                'transliteration'=>false, //NO se true in alcuni casi cancella tutto! 

                'googledoc_enabled'=>FALSE,
                'viewerjs_enabled'=>FALSE,
                'java_upload'=>FALSE,
                'aviary_active'=>false);
$myConfig=array_merge($config,$myConfig);


if(!$myPars || $_GET['check']) {
              $myPars=$myAjaxLoader->get_params();
              if(!$myPars) die('!');
              $s->set('par',$myPars);
            }
            
$_GET=array_merge($myPars,$_GET);
$myConfig=array_merge($myConfig,$myPars);



//$ext = array_merge($ext_img, $ext_file, $ext_misc, $ext_video,$ext_music); //allowed extensions

//require_once $_SESSION['RF']['language_file']=realpath(__DIR__."/../lang/$default_language.php");
new myAjaxFileManagerPlugin_start_stop($myPars['codice'][0],$myPars['codice'][1]);    


return $_SERVER[__FILE__]=array_merge(
                                $myConfig,
                                array(
                                    'MaxSizeUpload' => ((int)(ini_get('post_max_size')) < $myConfig['MaxSizeUpload'])? (int)(ini_get('post_max_size')) : $myConfig['MaxSizeUpload'],
                                    'ext'=> array_merge(
                                        $myConfig['ext_img'],
                                        $myConfig['ext_file'],
                                        $myConfig['ext_misc'],
                                        $myConfig['ext_video'],
                                        $myConfig['ext_music']
                                    ),
                                    // For a list of options see: https://developers.aviary.com/docs/web/setup-guide#constructor-config
                                    'aviary_defaults_config' => array(
                                        'apiKey'     => $myConfig['aviary_apiKey'],
                                        'apiVersion' => 3,
                                        'language'   => 'it',
                                        'theme'      => 'light',
                                        'tools'      => 'all'
                                    ),
                                )
                        );



?>