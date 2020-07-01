<?php

include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (empty(@$post)) {
    $callback = array("type" => "非正常模式A", "state" => "0", "message" => "非正常模式A");
    echo json_encode($callback, JSON_UNESCAPED_UNICODE);
    exit;
}

@$email = params_security($post["email"]); //帳戶
@$name = params_security($post["name"]); //使用者名稱
@$passwd1 = params_security($post["passwd1"]); //密碼
@$status = params_security($post["status"]); //狀態

@$edit = params_security($post["edit"]);
@$id = params_security($post["id"]);

//@$admin_stop = params_security($post["admin_stop"]);//權限

$query = "select * from users";
$rs = SelectSqlDB($query);
if ($rs["count"] <= 0) {
    exit("<script>alert('查無資料表，請通知管理員');history.go(-2);</script>");
}
$admin_arr = []; //所有管理員
$admin_arr = $rs["data"];
$_account = []; //所有管理員帳號
$admin_id = []; //所有管理員帳號
foreach ($rs["data"] as $row) {
    $_account[$row["email"]] = $row;
    $admin_id[$row["id"]] = $row;
}

if (@$edit != "") { //修改

    if (!array_key_exists($id, $admin_id)) {
        exit("<script>alert('查無會員資料');history.go(-1);</script>");
    }
    if ($_SESSION["admin"]["id"] == "1") {
        //最高管理員僅可以改名稱
        $fields = ["name"=>$name, "updated_at"=>date("Y-m-d H:i:s")];

        if ($admin_id[$id]["id"] != "1") {
            if ($passwd1 != "") { //有密碼
                $fields["password"] = password_hash($passwd1, PASSWORD_DEFAULT);
            }
        }
		UpdateDB("users", $fields, "id='".$id."'");

        exit("<script>alert('異動成功'); history.go(-2);</script>");

    } else {
        //一般身份
        $fields = ["name"=>$name, "status"=>$status, "updated_at"=>date("Y-m-d H:i:s")];

        if ($passwd1 != "") { //有密碼
			$fields["password"] = password_hash($passwd1, PASSWORD_DEFAULT);
        }

        if (!UpdateDB("users", $fields, "id='".$id."'")) {
            exit("<script>alert('異動失敗，請通知管理員');history.go(-2);</script>");
        } else {
            exit("<script>alert('異動成功');history.go(-2);</script>");
        }

    }
} else {
    //新增
    if ($name=="" || $email == "" || $passwd1 == "") {
        exit("<script>alert('必填欄位，請勿為空!');history.go(-1);</script>");
    }

    if (array_key_exists($email, $_account)) {
        exit("<script>alert('該管理帳號已有人使用，請更換帳號!');history.go(-1);</script>");
    }
    
	$rtn=InsertDB("users", ["name"=>$name, "email"=>$email, "password"=>password_hash($passwd1, PASSWORD_DEFAULT), "status"=>$status, "created_at"=>date("Y-m-d H:i:s"), "updated_at"=>date("Y-m-d H:i:s")]);

    if (!$rtn) {
        exit("<script>alert('異動失敗，請通知管理員');history.go(-2);</script>");
    } else {
        if ($ROOT_mode["permission"] == 1) {
            if (!empty($child)) {
                $new_arr = []; //將key包進去
                $new_arr[$email] = !empty($child["default"]) ? $child["default"] : $child["all"];
                $filename = dirname(__FILE__) . "/../phplibs/left_array.json";
                if (!is_file($filename)) { //沒有則建立
                    $file = fopen($filename, "w+");
                }
                if (filesize($filename)) { //有檔案
                    $readfile = file_get_contents($filename);
                    $reas = json_decode($readfile, true);

                    $arr = [];
                    $all_name = array_keys($reas);

                    $reas[$email] = $new_arr[$email];
                    $reas["admin"] = $child["all"];
                    if (sizeof($reas)) {
                        $handle = fopen($filename, "w+");
                        fwrite($handle, json_encode($reas));
                        fclose($handle);
                    }

                } else {
                    if (sizeof($new_arr)) {
                        foreach ($_account as $k => $rst) {
                            if ($k != $email) {
                                $new_arr[$rst["email"]] = $child["default"];
                            }
                        }
                        $new_arr["admin"] = $child["all"];
                        fwrite($file, json_encode($new_arr));
                        fclose($file);
                    }
                }
            }
        }

        exit("<script>alert('帳號建立成功');history.go(-2);</script>");
    }

}
