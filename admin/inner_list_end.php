<?php

include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (empty(@$post)) {
    $callback = array("type" => "非正常模式A", "state" => "0", "message" => "非正常模式A");
    echo json_encode($callback, JSON_UNESCAPED_UNICODE);
    exit;
}

if($post["edit"]!="edit" || $post["data"]==""){exit('資料更新失敗!');}
//exit(htmlspecialchars_decode($post["data"], ENT_QUOTES));

//clear layout data
DeleteDB("inner_layout", "1=1");

//insert new data
foreach(json_decode(htmlspecialchars_decode($post["data"], ENT_QUOTES), true) as $item){
    $fields = [
        "chart_id" => $item["id"],
        "x" => $item["x"],
        "y" => $item["y"],
        "width" => $item["w"],
        "height" => $item["h"],
        "updated_at" => date("Y-m-d H:i:s")
    ];

    InsertDB("inner_layout", $fields);
}

exit('資料已更新!');