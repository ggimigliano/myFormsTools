<?php

namespace Gimi\myFormsTools\PckmyUtils;

abstract class myUtilFunc {
	public static  function get_class($what) {
		#echo str_replace('\\','/',get_class($what)).'<br>';
		return basename(str_replace('\\','/',get_class($what)));
	}
}

