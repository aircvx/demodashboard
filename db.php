<?php
define('SQL_DB', 'demodb');
define('SQL_USER',     'aircvx1');
define('SQL_PASSWD',   '1qaz@WSX');
$serverName = "demodbserver122.database.windows.net";


$connectionInfo = array("Database" => SQL_DB, "UID" => SQL_USER, "PWD" => SQL_PASSWD, "CharacterSet" => "UTF-8", 'ReturnDatesAsStrings' => 1);
//$link = sqlsrv_connect(SQL_SERVER, $connectionInfo);
$link = sqlsrv_connect($serverName, $connectionInfo);
if (!$link) {
    die(print_r(sqlsrv_errors(), true));
}

//DB Function
function InsertDB($tbname, $data)
{
    //global $link;
    $connectionInfo = array("Database" => SQL_DB, "UID" => SQL_USER, "PWD" => SQL_PASSWD, "CharacterSet" => "UTF-8", 'ReturnDatesAsStrings' => 1);
    //$link = sqlsrv_connect(SQL_SERVER, $connectionInfo);
    $link = sqlsrv_connect($serverName, $connectionInfo);

    $sql = "insert into $tbname";
    $t = "";
    foreach ($data as $k => $v) {
        $t .= "[$k],";
    }
    $t = substr($t, 0, strlen($t) - 1);
    $sql .= "($t) values";

    $t = "";
    foreach ($data as $k => $v) {
        $t .= "?,"; //"$v,";
    }
    $t = substr($t, 0, strlen($t) - 1);
    $sql .= "($t)";

    if (!sqlsrv_query($link, $sql, array_values($data))) {
        $sql = "insert into $tbname";
        $t = "";
        foreach ($data as $k => $v) {
            $t .= "[$k],";
        }
        $t = substr($t, 0, strlen($t) - 1);
        $sql .= "($t) values";

        $t = "";
        foreach ($data as $k => $v) {
            $t .= "'$v',";
        }
        $t = substr($t, 0, strlen($t) - 1);
        $sql .= "($t)";

        echo print_r(sqlsrv_errors(), true) . "<br>" . $sql;
        exit;
    }
    return $sql;
}

function UpdateDB($tbname, $data, $conditions = "")
{
    //global $link;
    $connectionInfo = array("Database" => SQL_DB, "UID" => SQL_USER, "PWD" => SQL_PASSWD, "CharacterSet" => "UTF-8", 'ReturnDatesAsStrings' => 1);
    //$link = sqlsrv_connect(SQL_SERVER, $connectionInfo);
    $link = sqlsrv_connect($serverName, $connectionInfo);

    $sql = "update $tbname set ";
    $t = "";
    foreach ($data as $k => $v) {
        $t .= "[$k]=?,"; //"[$k]=$v,";
    }
    $t = substr($t, 0, strlen($t) - 1);
    $sql .= "$t";

    if ($conditions != "") {
        $sql .= " where $conditions";
    }

    if (ini_get("magic_quotes_gpc") == "1") {
        $sql = stripslashes($sql);
    }

    $sql2 = "update $tbname set ";
    $t = "";
    foreach ($data as $k => $v) {
        $t .= "[$k]='$v',";
    }
    $t = substr($t, 0, strlen($t) - 1);
    $sql2 .= "$t";

    if ($conditions != "") {
        $sql2 .= " where $conditions";
    }

    //sqlsrv_query($link, $sql, array_values($data)) or die(print_r( sqlsrv_errors(), true)."<br>$sql");
    if (!sqlsrv_query($link, $sql, array_values($data))) {
        echo print_r(sqlsrv_errors(), true) . "<br>" . $sql2;
        exit;
    }
    return $sql2;
}

function DeleteDB($tbname, $conditions = "")
{
    //global $link;
    $connectionInfo = array("Database" => SQL_DB, "UID" => SQL_USER, "PWD" => SQL_PASSWD, "CharacterSet" => "UTF-8", 'ReturnDatesAsStrings' => 1);
    //$link = sqlsrv_connect(SQL_SERVER, $connectionInfo);
    $link = sqlsrv_connect($serverName, $connectionInfo);

    $sql = "delete from $tbname";
    if ($conditions != "") {
        $sql .= " where $conditions";
    }

    if (ini_get("magic_quotes_gpc") == "1") {
        $sql = stripslashes($sql);
    }

    //sqlsrv_query($link, $sql) or die(print_r( sqlsrv_errors(), true)."<br>$sql");
    if (!sqlsrv_query($link, $sql)) {
        echo print_r(sqlsrv_errors(), true) . "<br>" . $sql;
        exit;
    }
    return $sql;
}

function SelectSqlDB($sql)
{
    //global $link;
    $connectionInfo = array("Database" => SQL_DB, "UID" => SQL_USER, "PWD" => SQL_PASSWD, "CharacterSet" => "UTF-8", 'ReturnDatesAsStrings' => 1);
    //$link = sqlsrv_connect(SQL_SERVER, $connectionInfo);
    $link = sqlsrv_connect($serverName, $connectionInfo);
    
    $rs = sqlsrv_query($link, $sql) or die(print_r(sqlsrv_errors(), true) . "<br>". $sql);
    $rst["sql"] = $sql;
    $rst["count"] = 0; //sqlsrv_num_rows($rs);
    $rst["data"] = array();
    while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
        $rst["data"][] = $r;
        $rst["count"]++;
    }
    return $rst;
}
