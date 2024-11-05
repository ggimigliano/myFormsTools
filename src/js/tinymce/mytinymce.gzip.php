<?php
error_reporting(0);
include_once(__DIR__.'/../../myFields.php');
include_once(__DIR__.'/tinymce.gzip.php');

// Handle incoming request if it's a script call
if (TinyMCE_Compressor::getParam("js")) {
    
  //  $cachedir=__DIR__. "/../../datacache/tinymce_cache";
  //  if(!is_dir($cachedir)) @mkdir($cachedir,0777,1);
    
    
	$tinyMCECompressor = new TinyMCE_Compressor(array(
                                	  "languages" => array("it","de","en_GB","es","fr_FR"),
                                	//  "cache_dir" => $cachedir,
                                	  "disk_cache"=> false,
                                	  "compress"=> true,
                                	  "expires"   => "30d"
                                	));

	// Handle request, compress and stream to client
	$tinyMCECompressor->handleRequest();
}

?>