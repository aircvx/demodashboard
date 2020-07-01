<?php
	include_once(dirname(__FILE__)."/../conndb.php");
	include_once(dirname(__FILE__)."/root.php");	
	include_once(dirname(__FILE__)."/root_global.php");

	if(preg_match("/_list/",basename($_SERVER["REQUEST_URI"])) && 
		basename(dirname($_SERVER["REQUEST_URI"]))=="admin"){
		include_once(dirname(__FILE__)."/page.class.php");	
	}
	
	//include_once(dirname(__FILE__)."/SimpleImage_areafill.php");
	include_once(dirname(__FILE__)."/backend_other.php");

	/************************************************\
	 * Process
	allpage need include php_head.php
	only index.php,news.php,registed.php donesn't login
	login.php->main_other(check_function)->login_end.php
	
	\***********************************************/
?>