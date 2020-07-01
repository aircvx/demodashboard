<?php

//未來考慮加上管理者自訂權限版本
$child["all"]=[
    "客戶首頁"=>"../login.php",
    "銷售員首頁"=>"../seller/login.php",
    "管理員管理"=>[
        "管理員列表"=>"admin_list.php",
        "輪盤設定"=>"roulette_add.php",
    ],
    "業務管理"=>[
        "業務列表"=>"seller_list.php",
        "績效報表"=>"report_list.php",
    ],
    "客戶管理"=>[
        "客戶列表"=>"member_list.php",
        "積分紀錄"=>"voucher_list.php",
        "條數紀錄"=>"record_list.php",
    ],
    "產品管理"=>"item_list.php",
    "商城管理"=>"ticket_list.php",
    "企業介紹"=>"self_add.php",
    "消息通知"=>"news_list.php",

];
$child["default"]=[
    "客戶首頁"=>"../login.php",
    "銷售員首頁"=>"../seller/login.php",
    "客戶管理"=>[
        "客戶列表"=>"member_list.php",
        "積分紀錄"=>"voucher_list.php",
        "條數紀錄"=>"record_list.php",
    ],
    "產品管理"=>"item_list.php",
    "商城管理"=>"ticket_list.php",
    "企業介紹"=>"self_add.php",
    "消息通知"=>"news_list.php",

];
/*權限 */

$child=$child["all"];

/*權限 */


?>