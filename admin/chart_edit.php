<?php

include_once dirname(__FILE__) . "/../phplibs/php_head.php";

$page_false = true;
@$edit = params_security($get["edit"]);
@$id = params_security($get["id"]);
$row = [];

if ($edit == "true" && $id != "") {
    $query = "select * from chart where id='$id'";
    $result = SelectSqlDB($query);
    if ($result["count"] > 0) {
        $row = $result["data"][0];
        $page_false = false;
    }
} else {
    $edit = "";
    $page_false = false;
}
if (sizeof($row) <= 0) {
    $edit = "";
}

if ($page_false) {
    exit("<script>alert('查無此頁');history.go(-1)</script>");
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>高雄市政府智慧市政儀表板後端管理系統</title>
  <!-- Tell the browser to be responsive to screen width -->
<?php
include_once "vendor/backend/backend_css_include.php";
?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini">管理</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg">網站管理系統</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">選單</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->

          <!-- Notifications: style can be found in dropdown.less -->

          <!-- Tasks: style can be found in dropdown.less -->

          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" >
              <span class="hidden-xs"><?php echo $_SESSION['admin']['name']; ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <!-- Menu Body -->
              <li class="user-footer">

                <button type="button" class="btn btn-block btn-info btn-lg" onclick="location.href='LOGOUT.php'" >
                登出
              </button>


              </li>
              <!-- Menu Footer-->

            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->

        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
        </div>
        <div class="info center">
          <p><?php echo $_SESSION['admin']['name']; ?></p>

        </div>
      </div>

      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->

      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">功能選單</li>

        <?php
        include_once dirname(__FILE__) . "/../phplibs/menu_left.php";
        ?>


    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?php echo $BarTitle; ?></h1>
      <ol class="breadcrumb">
        <li><a href="init.php"><i class="fa fa-dashboard"></i> 網站管理後台</a></li>
        <li class="active"><?php echo $BarTitle; ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">

        <!-- /.col -->
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <button type="button" class="btn btn-primary btn-sm" onclick="history.go(-1)"><i class="fa fa-reply"></i></button>&nbsp;&nbsp;
              <h3 class="box-title"><?php echo $BarSubTitle; ?></h3>
              <!-- /.box-tools -->

            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">

            <div class="box-body ">

            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" action="chart_edit_end.php" method="post" enctype="multipart/form-data" onsubmit="return check(this);">
            <?php
              $temp = gettoken_value();
              echo '<input type="hidden" name="value" value="' . $temp["value"] . '">';
              echo '<input type="hidden" name="token" value="' . $temp["token"] . '">';
              echo '<input type="hidden" name="edit" value="' . $edit . '">';
              if ($edit == "true") {
                  echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
              }
            ?>
              <span class=" required">*星號為必填欄位</span>
              <div class="box-body">

              <div class="form-group">
                  <label for="name">圖表名稱<span class="required">*</span></label>
                  <input type="text" name="title" id="title" class="form-control ckeditor" data-toggle="tooltip" required value="<?php echo !empty($row["title"]) ? $row["title"] : "" ?>" >
                </div>
                <div class="form-group">
                  <label for="name">精選圖表名稱<span class="required">*</span></label>
                  <input type="text" name="title2" id="title2" class="form-control ckeditor" data-toggle="tooltip" required value="<?php echo !empty($row["title2"]) ? $row["title2"] : "" ?>" >
                </div>
                <div class="form-group">
                  <label for="name">每日精選<span class="required">*</span></label>
                  <input type="radio" name="istop" id="istopN" class="form-control ckeditor" data-toggle="tooltip" required <?php echo $row["istop"]!="1" ? "checked" : "" ?> value="0" >否
                  <input type="radio" name="istop" id="istopY" class="form-control ckeditor" data-toggle="tooltip" required <?php echo $row["istop"]=="1" ? "checked" : "" ?> value="1" >是
                </div>
                <div class="form-group">
                  <label for="name">所屬局處<span class="required">*</span></label>
                  <select name="depart" id="depart" class="form-control ckeditor" data-toggle="tooltip" required >
                    <option value="">-- 請選擇所屬局處 --</option>
                  <?php
                    $rs1=SelectSqlDB("select * from department order by [sort],[id]");
                    foreach($rs1["data"] as $rst1){
                      echo '<option value="'.$rst1["id"].'" '.($rst1["id"]==$row["department_id"]?"selected":"").'>'.$rst1["name"].'</option>';
                    }
                  ?>
                  </select>
                </div>
                <? $types = explode(",",$row["type"]);?>
                <div class="form-group">
                  <label for="name">資訊類別<span class="required">*</span></label>
                  <input type="checkbox" name="type[]" id="type1"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("安全資訊", $types) ? "checked" : "" ?> value="安全資訊">安全資訊&nbsp;&nbsp; 
                  <input type="checkbox" name="type[]" id="type2"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("市政資訊", $types) ? "checked" : "" ?> value="市政資訊">市政資訊&nbsp;&nbsp;
                  <input type="checkbox" name="type[]" id="type3"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("民生資訊", $types) ? "checked" : "" ?> value="民生資訊">民生資訊&nbsp;&nbsp;
                  <input type="checkbox" name="type[]" id="type4"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("資訊服務", $types) ? "checked" : "" ?> value="資訊服務">資訊服務&nbsp;&nbsp;
                  <input type="checkbox" name="type[]" id="type5"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("資訊監測", $types) ? "checked" : "" ?> value="資訊監測">資訊監測&nbsp;&nbsp;
                  <input type="checkbox" name="type[]" id="type6"  class="form-control ckeditor" data-toggle="tooltip" <?php echo in_array("農業資訊", $types) ? "checked" : "" ?> value="農業資訊">農業資訊&nbsp;&nbsp;
                </div>
                <div class="form-group ">
                  <label for="sort">順序<span class="required">*</span></label>
                  <input type="number" name="sort" id="sort" class="form-control" data-toggle="tooltip" required min="1" max="999" value="<?php echo !empty($row["sort"]) ? $row["sort"] : ""; ?>" >
                </div>
                <div class="form-group">
                  <label for="name">顯示<span class="required">*</span></label>
                  <input type="radio" name="isenable" id="isenableY" class="form-control ckeditor" data-toggle="tooltip" <?php echo $row["isenable"]=="1" ? "checked" : "" ?> value="1" >是
                  <input type="radio" name="isenable" id="isenableN" class="form-control ckeditor" data-toggle="tooltip" <?php echo $row["isenable"]!="1" ? "checked" : "" ?> value="0" >否
                </div>

                <div id="demoWheel"></div>

                <div class="form-group ">
                  <label for="sort">圖表位置(電腦版)<span class="required">*</span></label>
                  <textarea name="iframe_pc" id="iframe_pc" class="form-control" data-toggle="tooltip" rows="5" required ><?php echo $row["iframe_pc"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(手機版)<span class="required">*</span></label>
                  <textarea name="iframe_mb" id="iframe_mb" class="form-control" data-toggle="tooltip" rows="5" required ><?php echo $row["iframe_mb"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(方形)<span class="required">*</span></label>
                  <textarea name="iframe_sq" id="iframe_sq" class="form-control" data-toggle="tooltip" rows="5" required ><?php echo $row["iframe_sq"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(1x1)</label>
                  <textarea name="iframe_1x1" id="iframe_1x1" class="form-control" data-toggle="tooltip" rows="5" ><?php echo $row["iframe_1x1"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(1x2)</label>
                  <textarea name="iframe_1x2" id="iframe_1x2" class="form-control" data-toggle="tooltip" rows="5" ><?php echo $row["iframe_1x2"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(2x1)</label>
                  <textarea name="iframe_2x1" id="iframe_2x1" class="form-control" data-toggle="tooltip" rows="5" ><?php echo $row["iframe_2x1"];?></textarea>
                </div>

                <div class="form-group ">
                  <label for="sort">圖表位置(2x2)</label>
                  <textarea name="iframe_2x2" id="iframe_2x2" class="form-control" data-toggle="tooltip" rows="5" ><?php echo $row["iframe_2x2"];?></textarea>
                </div>

              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary">送出</button>
              </div>
            </form>

              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.box-body -->

          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php
    include_once dirname(__FILE__) . "/footer.php";
  ?>


</div>
<!-- ./wrapper -->
<?php
include_once "vendor/backend/backend_javascript_include.php";
?>
<script>
function check(frm){
  j=0;
  for(i=0;i<6;i++){
    if(frm["type[]"][i].checked){j++;}
  }
  if(j==0){
    alert("請至少選擇一項類別!");
    return false;
  }
  return true;
}
</script>
</body>
</html>
