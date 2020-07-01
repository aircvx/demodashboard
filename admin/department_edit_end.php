<?php

include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (empty(@$post)) {
    $callback = array("type" => "非正常模式A", "state" => "0", "message" => "非正常模式A");
    echo json_encode($callback, JSON_UNESCAPED_UNICODE);
    exit;
}

$_check = []; //必檢查參數
$escape = [];
$tmp = [];
$add = [];
$All_params = ["value", "token", "edit", "id", "name", "sort"]; //所有參數
$escape_params = []; //額外參數
foreach ($post as $k => $rst) {

    if (preg_match("/_note/", $k)) {
        @${$k} = params_security($rst, "text");
    } elseif (preg_match("/_content/", $k)) {
        @${$k} = params_security($rst, "html");
    } else {
        @${$k} = params_security($rst);
    }

    if ($k == "value" || $k == "token" || $k == "edit" || mb_strpos($k, "id")) {
        $escape[] = $k; //跳脫新增參數
    }

    if ($k != "edit") { //必檢查參數
        if (${$k} == "") { //當值不是空
            $_check[] = $k;
        }

    }
    if (!in_array($k, $All_params)) {
        $escape_params[] = $k;
    }
    $tmp[$k] = ${$k};

}

if (!token_validation($value, $token)) {
    echo "<script>alert('請勿重複送出表單');</script>";
    echo "<script>history.go(-1);</script>";
    exit;
}
if (sizeof($_check)) {
    echo "<script>alert('資料填寫不完全');</script>";
    echo "<script>history.go(-1);</script>";
    exit;
}
if (sizeof($escape_params)) {
    echo "<script>alert('請勿傳送額外參數');</script>";
    echo "<script>history.go(-1);</script>";
    exit;
}

if (@$edit) { //修改

    if(!UpdateDB("department",["name"=>$post["name"],"sort"=>$post["sort"],"updated_at"=>date("Y-m-d H:i:s")],"[id]='".$id."'")){
        exit("<script>alert('資料更新失敗!');history.go(-1)</script>");
    }

} else { //新增

    if(!InsertDB("department",["name"=>$post["name"],"sort"=>$post["sort"],"created_at"=>date("Y-m-d H:i:s"),"updated_at"=>date("Y-m-d H:i:s")])){
        exit("<script>alert('資料更新失敗!');history.go(-1)</script>");
    }
}

exit("<script>alert('資料已更新!');history.go(-2)</script>");
