<?php

include_once dirname(__FILE__) . "/../phplibs/php_head.php";

$page_false = true;
@$edit = params_security($get["edit"]);
@$id = params_security($get["id"]);
$row = [];

if ($edit == "true" && $id != "") {
    $query = "select * from department where id='$id'";
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
            <form role="form" action="department_edit_end.php" method="post" enctype="multipart/form-data">
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
                  <label for="name">局處名稱<span class="required">*</span></label>
                  <input type="text" name="name" id="name" class="form-control ckeditor" data-toggle="tooltip" required value="<?php echo !empty($row["name"]) ? $row["name"] : "" ?>" >
                </div>
                <div class="form-group ">

                  <label for="sort">順序<span class="required">*</span></label>
                  <input type="number" name="sort" id="sort" class="form-control" data-toggle="tooltip" required min="1" max="99" value="<?php echo !empty($row["sort"]) ? $row["sort"] : ""; ?>" >

                </div>

                <div id="demoWheel"></div>

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
</body>
</html>
