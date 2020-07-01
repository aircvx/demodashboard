<?php

if(!isset($_SESSION)){ session_start(); }
include_once("left_array.php");
//$mysqli = new mysqli("localhost","root",'=hH7w@$C3fvKyQuZ',"pintech_sjtv");

$ROOT_mode=[];
$ROOT_mode=[
	"test"=>0,
	"verify"=>0, //登入驗證格式 0:recaptcha,1:數字驗證
	"permission"=>0, //權限
	"this_project"=>"eye"//1:有權限
];
$_AdminLogo="../assets/images/favicon.ico";//後台管理員LOGO

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


/*權限 */
if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
	//後台
	if (strpos ($_SERVER['PHP_SELF'], "/admin/") !== false) {
		
		if (strpos ($_SERVER['REQUEST_URI'], "index_end.php") == false AND strpos ($_SERVER['REQUEST_URI'], "LOGOUT.php") == false){ 
			//只要不是上面網址就執行
			if(!array_key_exists('admin',$_SESSION)) {
				if(basename($_SERVER['PHP_SELF'])!="index.php"  && basename($_SERVER['PHP_SELF'])!="home.php" ){
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('停留時間過久，請重新登入1');document.location.href = 'index.php'</script>");
				}
			}else{
				if(!empty($_SESSION['this_project'])){
				
					if($_SESSION['this_project']!=$ROOT_mode["this_project"]){
						unset($_SESSION['admin']);	
						unset($_SESSION['this_project']);	
						exit("<script>alert('停留時間過久，請重新登入2');document.location.href = 'index.php'</script>");
					}
				}else{
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('停留時間過久，請重新登入3');document.location.href = 'index.php'</script>");
				}
				if(empty($_SESSION['admin']['name'])){
					if(basename($_SERVER['PHP_SELF'])!="index.php" ){
						unset($_SESSION['admin']);	
						unset($_SESSION['this_project']);
						exit("<script>alert('停留時間過久，請重新登入4');document.location.href = 'index.php'</script>");
					}
				}

				$account = $_SESSION['admin']['name'];
				$rs = SelectSqlDB("select * from [users]");
				if($rs["count"]>0){
					$admin_arr=[];
					$page_false=false; //鎖住可觀看頁面，非權限內
					foreach($rs["data"] as $rst){
						$admin_arr[$rst["name"]]=$rst;
					}
					
					if(!array_key_exists($account, $admin_arr)){
						$page_false=true;
						unset($_SESSION['admin']);
						unset($_SESSION['this_project']);	
						exit("<script>alert('查無此帳號!'); document.location.href = 'index.php'</script>");
					}else{
						if($admin_arr[$account]["status"] == "0"){
							unset($_SESSION['admin']);	
							unset($_SESSION['this_project']);	
							exit("<script>alert('查無此帳號!'); document.location.href = 'index.php'</script>");
						}
					}
				
					if($ROOT_mode["permission"]==1){ //有權限
						
						$leftJson = dirname(__FILE__)."/../phplibs/left_array.json"; //沒有權限json則建立
						
						if(!is_file($leftJson)){ 
							$file = fopen($leftJson,"w+");
							foreach($admin_arr as $k=>$rst){
								if($k!="admin"){
									$new_arr[$rst["admin_account"]]=$child["default"];
								}
							}
							
							$new_arr["admin"]=$child["all"];
						
							
							if(sizeof($new_arr)){ 
								fwrite($file,json_encode($new_arr));
								fclose($file);
							}
							/*權限*/
							
							$child2=$child["all"];

							/*權限*/
						}else{ //有權限json檔
							$readfile = file_get_contents($leftJson);
							$child2=$child["default"];
							if(is_file($leftJson)){
								$reas=json_decode($readfile, true);
								if($account=="admin"){
									$child2=$child["all"];
								}else{
									if(array_key_exists($account,$reas)){
										$child2=$reas[$account];
									}
								}
							}
							
							$page_false=true; //鎖住可觀看頁面，非權限內
							foreach($child2 as $c=>$d){
								if(is_array($d)){
									foreach($d as $k=>$v){
										$File_T=explode("_list.php",$v)[0];
										
										if(mb_strpos(basename($_SERVER["PHP_SELF"]),$File_T)!==false){
											$page_false=false;
										}
									
									}
								}else{
									$File_T=explode("_list.php",$d)[0];
									if(mb_strpos(basename($_SERVER["PHP_SELF"]),$File_T)!==false){
										$page_false=false;
									}
								
								}
							}
						}
					}else{
						$child2=$child["all"];
					}
						


				}else{
					
					unset($_SESSION['admin']);	
					unset($_SESSION['this_project']);	
					exit("<script>alert('查無資料!');document.location.href = 'index.php'</script>");
				}
				
				
				
			}

		}
	}
}

?>