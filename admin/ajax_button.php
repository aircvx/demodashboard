<?php
/*----------------------
save,order,delete_small,delete
------------------------*/
include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (empty(@$post)) {
    $callback = array("type" => "非正常模式A", "state" => "0", "message" => "非正常模式A");
    echo json_encode($callback, JSON_UNESCAPED_UNICODE);
    exit;
}

@$method = params_security($post["method"]);
if (@$method == "") {
    $callback = array("type" => "非正常模式B", "state" => "0", "message" => "非正常模式B");
    echo json_encode($callback, JSON_UNESCAPED_UNICODE);
    exit;
}
/*------------------
儲存備註
-------------------*/
if ($method == "save_note") {
    @$tables = params_security($post["tables"]); //table
    @$set_field = params_security($post["set_field"]); //修改欄位
    @$where_field = params_security($post["where_field"]); //where條件
    @$set_field_encrypt = params_security($post["set_field_encrypt"], "plus"); //id加密
    @$set_field_content = params_security($post["set_field_content"]); //內容
    $id = "";

    if (!empty($tables) and !empty($where_field) and !empty($set_field) and !empty($set_field_encrypt)) {

        $query = "select * from `$tables`  ";
        $rs = SelectSqlDB($query);
        $count = $rs["count"];

        if ($count > 0) {
            foreach ($rs["data"] as $row) {
                if (aes_validation($row[$where_field], str_remove($set_field_encrypt, -3))) { //第一個為id流水號
                    $id = $row[$where_field];
                }
            }

        }

        if ($id) {
            UpdateDB($tables, [$set_field => $set_field_content], "[" . $where_field . "] = '" . $id . "'");
            echo callBack("1", "更新成功");
            exit;
        } else {
            echo callBack("0", "更新失敗A1");
            exit;
        }

    } else {
        echo callBack("0", "更新失敗A2");
        exit;
    }
}
/*------------------
開啟/關閉 按鈕
-------------------*/
if ($method == "open") {
    @$self_uuid = params_security($post["self_uuid"]); //帶uuid
    @$self_table = params_security($post["self_table"]); //table
    @$where_field = params_security($post["where_field"]); //where_column
    @$self_col = params_security($post["self_col"]); //開關條件
    @$self_val = params_security($post["self_val"]); //值

    if (!empty($self_uuid) and !empty($self_table) and !empty($self_col) and !empty($where_field) and $self_val != "") {
        UpdateDB($self_table, [$self_col => $self_val], "[" . $where_field . "]='" . $self_uuid . "'");
        echo callBack("1", "更新成功");
    } else {
        echo callBack("1", "更新失敗");
    }
    exit;
}

/*------------------
排序
-------------------*/
if ($method == "orders") {
    @$set_field = params_security($post["set_field"]);
    @$tables = params_security($post["tables"]);
    @$set_field_int = params_security($post["set_field_int"]);
    @$where_field = params_security($post["where_field"]);
    @$where_field_int = params_security($post["where_field_int"]);

    if (!empty($set_field) and !empty($tables) and !empty($where_field)) {
        if ($set_field_int < 0 || $set_field_int > 999) {
            echo callBack("0", "請介於1~999之間");
            exit;
        }

        UpdateDB($tables, [$set_field => $set_field_int], "[" . $where_field . "]='" . $where_field_int . "'");
        echo callBack("1", "變更成功");
    } else {
        echo callBack("0", "驗證錯誤");
    }
    exit;
}

/*------------------
刪除照片
-------------------*/
if ($method == "de_lete_photo") {

    @$id = params_security($post["id"]);
    @$tables = params_security($post["tables"]);
    @$field = params_security($post["field"], "text");
    @$photo = params_security($post["photo"]);

    if (!empty($id) and !empty($tables) and !empty($field)) {
        //$query = "update $tables set $photo = '' where $field = $id ";
        //$query = "update $tables set `$photo` =''  where find_in_set($field,'".$id."') > 0";
        $rs = UpdateDB($tables, [$photo => ''], "[" . $field . "]='" . $id . "'");
        if ($rs) {
            echo callBack("1", "刪除成功");
        }

        exit;
    } else {
        echo callBack("0", "刪除失敗");
        exit;
    }
}

/*------------------
刪除資料
-------------------*/
if ($method == "de_lete") {
    @$encrypt = params_security($post["encrypt"], "plus");
    @$tables = params_security($post["tables"]);
    @$field = params_security($post["field"]);
    @$page = params_security($post["page"]);
    $id = [];

    if (!empty($encrypt) && !empty($tables) && !empty($field)) {

        $query = "select * from [".$tables."] ";
        $rs = SelectSqlDB($query);
        $id = []; $arr = []; $admin_arr = [];

        if ($rs["count"] > 0) {
            foreach ($rs["data"] as $row) {
                if ($page == "admin_list.php" && $ROOT_mode["permission"] == 1) {
                    $admin_arr[$row["id"]] = $row;
                }
                foreach ($row as $k => $res) {
                    if (mb_strpos($k, "id") !== false) {
                        foreach (explode(",", $encrypt) as $aes) {
                            if (aes_validation($res, str_remove($aes, -3))) { //第一個為id流水號
                                array_push($id, $res); //_id
                                if ($page == "admin_list.php" && $ROOT_mode["permission"] == 1) {
                                    $arr[$row["email"]] = $row;
                                }
                            }
                        }
                    }

                }

            }
        }

        $id = join(",", $id);
        if ($id) {

            if ($page == "admin_list.php" && $ROOT_mode["permission"] == 1) { //刪除管理員，需要刪除權限
                $filename = dirname(__FILE__) . "/../phplibs/left_array.json";
                if (!is_file($filename)) { //沒有則建立
                    $file = fopen($filename, "w+");
                }
                if (filesize($filename)) { //有檔案
                    $readfile = file_get_contents($filename);
                    $reas = json_decode($readfile, true);

                    $reas2 = [];
                    foreach ($reas as $k => $row) {

                        if (!array_key_exists($k, $arr)) {
                            $reas2[$k] = $row;
                        }
                        $reas2["admin"] = $child["all"];
                    }

                    if (sizeof($reas2)) {

                        $handle = fopen($filename, "w+");
                        fwrite($handle, json_encode($reas2));
                        fclose($handle);
                    }

                } else {
                    $new_arr = [];
                    $file = fopen($filename, "w+");
                    foreach ($admin_arr as $k => $rst) {
                        if ($k != "admin" && !array_key_exists($k, $arr)) {
                            $new_arr[$rst["email"]] = $child["default"];
                        }
                    }

                    $new_arr["admin"] = $child["all"];

                    $filename = dirname(__FILE__) . "/../phplibs/left_array.json";
                    if (sizeof($new_arr)) {
                        fwrite($file, json_encode($new_arr));
                        fclose($file);
                    }

                }
            }

            DeleteDB($tables, "[" . $field . "]='".$id."'");
            echo callBack("1", "資料已刪除!");
            exit;

        } else {
            echo callBack("1", "刪除失敗!");
            exit;

        }

    } else {
        echo "<script>alert('刪除失敗!')</script>";
        echo "<script>history.go(-1);</script>";
        exit;
    }
}
