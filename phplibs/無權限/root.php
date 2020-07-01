<?php

set_time_limit(0);
ini_set('memory_limit', '256M');
//中文;
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');
if(!isset($_SESSION)){ ini_set('session.gc_maxlifetime',200000); session_start(); }

include_once("left_array.php");
//$mysqli = new mysqli("localhost","root",'=hH7w@$C3fvKyQuZ',"pintech_sjtv");

$ROOT_mode=[];
$ROOT_mode=[
	"test"=>0,
	"verify"=>0, //登入驗證格式 0:recaptcha,1:數字驗證
	"this_project"=>"smoketw"
];
$_AdminLogo="../assets/images/favicon.ico";//後台管理員LOGO

if($ROOT_mode["test"]==1){
	
	switch ($ROOT_mode["verify"]){

		case "1":
		
		break;

		default:
	
			$recaptcha=[];
			$recaptcha["sitekey"] ="6LesRQcTAAAAAMgYMB4pW8Jk78CZxn4zFf4Cs_kZ";
			$recaptcha["captcha_secret"] ="6LesRQcTAAAAANQopmFbkJ-UrWnYsRVktotpVYtW";
		break;
	}
	
}else{
	$mysqli = new mysqli("localhost","dogr2487",'dogr2487',"pintech_demo");
}
$mysqli->set_charset("utf8");
$mysqli->query("SET time_zone='".set_mysql_timezone()."';");
//MYSQL時區同步PHP時區
function set_mysql_timezone(){ 
   $now = new DateTime();
   $mins = $now->getOffset() / 60; 
   $sgn = ($mins < 0 ? -1 : 1);
   $mins = abs($mins);
   $hrs = floor($mins / 60);
   $mins -= $hrs * 60; 
   return sprintf('%+d:%02d', $hrs*$sgn, $mins);
}  

//$affected_rows = $mysqli->affected_rows	;	察看影響列數


if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
	//後台
	if (strpos ($_SERVER['PHP_SELF'], "pintech_admin") !== false) {
		
		if (strpos ($_SERVER['REQUEST_URI'], "LOGIN.php") == false AND strpos ($_SERVER['REQUEST_URI'], "LOGOUT.php") == false){ 
			//只要不是上面網址就執行
			if(!array_key_exists('admin',$_SESSION)) {
				if(basename($_SERVER['PHP_SELF'])!="index.php"  && basename($_SERVER['PHP_SELF'])!="home.php" ){
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('停留時間過久，請重新登入,B0');document.location.href = 'index.php'</script>");
				}
			}else{
				if(!empty($_SESSION['this_project'])){
				
					if($_SESSION['this_project']!=$ROOT_mode["this_project"]){
						unset($_SESSION['admin']);	
						unset($_SESSION['this_project']);	
						exit("<script>alert('停留時間過久，請重新登入');document.location.href = 'index.php'</script>");
					}
				}else{
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('停留時間過久，請重新登入');document.location.href = 'index.php'</script>");
				}
				if(empty($_SESSION['admin']['admin_account'])){
					if(basename($_SERVER['PHP_SELF'])!="index.php" ){
						unset($_SESSION['admin']);	
						unset($_SESSION['this_project']);	
						exit("<script>alert('停留時間過久，請重新登入');document.location.href = 'index.php'</script>");
					
							
					}
				}
				$account = $_SESSION['admin']['admin_account'];
				$query  = "select * from admin";
				if($result = $mysqli->query($query)){
					$admin_arr=[];
					$page_false=false; //鎖住可觀看頁面，非權限內
					while($rows = mysqli_fetch_assoc($result)){
						$admin_arr[$rows["admin_account"]]=$rows;
					}
					
					if(!array_key_exists($account,$admin_arr)){
						$page_false=true;
						unset($_SESSION['admin']);	
						unset($_SESSION['this_project']);	
						exit("<script>alert('查無帳號');document.location.href = 'index.php'</script>");
					}else{
						if($admin_arr[$account]["admin_right"]==1){
							unset($_SESSION['admin']);	
							unset($_SESSION['this_project']);	
							exit("<script>alert('您已被停權');document.location.href = 'index.php'</script>");
						}
					}
				


				}else{
					
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('查無資料表');document.location.href = 'index.php'</script>");
				}
				
				
				
			}

		}
	}else{ //前台登入
		
		
	}
}

?>