<?php
include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (empty($post["account"]) || empty($post["passwd"])) {
    exit("<script>alert('帳號密碼請勿空白!'); document.location.href='index.php'</script>");
}
@$email = params_security($post["account"]);
@$passwd = params_security($post["passwd"]);

$rs = SelectSqlDB("select * from users where (id = 1 or status = 1) and email = '" . $email . "' ");

if ($rs["count"] == 0) {
    echo "<script>alert('密碼錯誤或查無帳號!')</script>";
    echo "<script>document.location.href = 'index.php'</script>";
} else {
	$rst=$rs["data"][0];

	if(password_verify($passwd, $rst["password"])){
		//correct
		$_SESSION['admin'] = array();
		$_SESSION['admin'] = $rs["data"][0];
		$_SESSION['this_project'] = $ROOT_mode["this_project"];

		echo "<script>alert('登入成功')</script>";
		echo "<script>document.location.href = 'init.php'</script>";
	}else{
		//wrong
		echo "<script>alert('密碼錯誤或查無帳號!')</script>";
    	echo "<script>document.location.href = 'index.php'</script>";
	}
}

exit;