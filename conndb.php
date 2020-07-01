<?php
define('ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

header("Expires: " . gmdate("D, d M Y H:i:s", strtotime("+1 Month", time())) . " GMT");
$start_time = microtime(true);
ini_set("display_errors", "1"); //0:不顯示錯誤
ini_set("memory_limit", "512M");
ini_set('short_open_tag', true);
ini_set('zlib.output_handler', '');
ini_set('zlib.output_compression', 0);
ini_set('output_handler', '');
ini_set('output_buffering', false);
ini_set('implicit_flush', true);
ini_set('default_socket_timeout', 10);
date_default_timezone_set("Asia/Taipei");
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING & ~E_STRICT);

session_cache_expire(60);
session_start();

//以下請視情況設定
$config = array();
$config["url"] = "http://localhost"; //網頁網址
$config["dir"] = dirname(__FILE__); //網頁根目錄位置
$config["sdir"] = ""; //網頁相對路徑
$config["admin_mail"] = ""; //管理者EMAIL
ini_set("sendmail_from", $config["admin_mail"]);
ini_set("SMTP", "localhost"); //SMTP

include_once(ROOT."db.php");

//編碼:UTF-8
header("Content-Type:text/html; charset=utf-8");

//時差設定
$time_offset = 8 * 60 * 60;

//處理GET資料
$get = array();
if (count($_GET) > 0) {
    foreach ($_GET as $k => $v) {
        if (!is_array($v)) {
            $get[$k] = htmlspecialchars(trim($v), ENT_QUOTES);
        } else {
            $get[$k] = $v;
        }
    }
}

//處理POST資料
$post = array();
if (count($_POST) > 0) {
    foreach ($_POST as $k => $v) {
        if (!is_array($v)) {
            $post[$k] = htmlspecialchars(trim($v), ENT_QUOTES);
        } else {
            $post[$k] = $v;
        }
    }
}

//資料類型
$Type = [
    "Security"    => "安全資訊",
    "Municipal"   => "市政資訊",
    "People"      => "民生資訊",
    "Information" => "資訊服務",
    "Monitor"     => "資訊監測",
];

foreach ($Type as $k => $v) {
    $rs = SelectSqlDB("select * from chart where type=N'".$v."' and isenable='1'");
    if ($rs["count"] == 0) {
        unset($Type[$k]);
    }
}

//check user device
$useragent = $_SERVER['HTTP_USER_AGENT'];
//if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|mobile|android/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $useragent)) {
    define("isMobile", true);
} else {
    define("isMobile", false);
}
//var_dump(isMobile);

if(preg_match("/(chrome|edge)/i", $useragent)) {
    define("isChrome", true);
} else {
    define("isChrome", false);
}
//var_dump(isChrome);

//分頁目前頁數
if (trim($get["p"]) != "") {
    $page = trim($get["p"]);
} elseif (trim($post["p"]) != "") {
    $page = trim($post["p"]);
} else {
    $page = 1;
}

//view counts
$rs=SelectSqlDB("select * from viewcounts where [date]='".date('Y-m-d')."'");
if($rs["count"]==0){
  InsertDB("viewcounts",["date"=>date("Y-m-d"), "counts"=>1]);
}else{
  UpdateDB("viewcounts",["counts"=>$rs["data"][0]["counts"]+1],"[date]='".date("Y-m-d")."'");
}

// Create a new CSRF token.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
}
