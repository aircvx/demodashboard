<?php
include("conndb.php");

//getChart
if($get['act']=="getChart"){
    $filter="1=1 and isenable=1";

    if($get["department"]!=""){$filter.=" and department_id='".(int)$get["department"]."'";}
    if($get["type"]!=""){$filter.=" and type=N'".$Type[$get["type"]]."'";}
    $rs=SelectSqlDB("select id,title from chart where ".$filter." order by [sort] asc");

    exit(json_encode($rs["data"]));
}

//getDepartment
if($get['act']=="getDepartment"){
    $rs=SelectSqlDB("select id,name from department order by [sort] asc");
    exit(json_encode($rs["data"]));
}
