<?php
//這頁只是個導入頁
include_once dirname(__FILE__) . "/../phplibs/php_head.php";
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
      <span class="logo-lg">後端管理系統</span>
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
              <h3 class="box-title"><?php echo $BarSubTitle; ?></h3>


              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <div class="box-body">
                <!-- Check all button -->

                <div class="pull-right">

                <button type="button" onclick="location.href='admin_edit.php'" class="btn  btn-success btn-sm"><i class="fa fa-plus"></i> 新增</button>

                <button type="button" name="data_del" tables="users" field="id" class="btn  btn-danger btn-sm"><i class="fa"></i> 批次刪除</button>

                  <!-- /.btn-group -->
                </div>
                <!-- /.pull-right -->
              </div>

              <div class="box-body ">
              <div class="table-responsive ">
                <table class="table table-hover table-bordered table-striped">
                <thead>
                <tr>
                    <th width="5%"> <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i>
                    </button></th>
                    <th >名稱</th>
                    <th >帳號(Email)</th>
                    <?php
                    if ($_SESSION["admin"]["id"] == 1) {
                        echo '<th >啟用(關閉/啟用)</th>';
                    }
                    ?>
                    <th >更新時間</th>
                    <th >功能</th>
                </tr>
                </thead>
                  <tbody>

                  <?php
                    $curpage = empty($get['page']) ? 1 : params_security($get['page'], "int"); //目前頁碼
                    $query = "SELECT * from users where 1=1 order by [name] asc";
                    //上面SQL有改為排序版本
                    $showrow = 10; //每頁顯示的列數

                    $url = "?page={page}";
                    //分頁網址，如果有搜尋條件，格式如右 ="?page={page}"

                    $total = 0;

                    if ($result = SelectSqlDB($query)) {
                        $total = $result["count"];
                        if (!empty($get['page']) && $total != 0 && $curpage > ceil($total / $showrow)) {
                            if (empty($total_rows)) {
                                $curpage = 1;
                            } else {
                                $curpage = ceil($total_rows / $showrow);
                            }

                        }
                        //$query .= " LIMIT " . ((($curpage - 1) * $showrow)) . ",$showrow;";
                        echo "<green>第 ".(($curpage - 1) * $showrow +1)." 到 ".($curpage * $showrow)." 筆，總共：$total 筆</green>";  
                        $result["data"] = array_slice($result["data"], (($curpage - 1) * $showrow) ,$showrow );

                        if (count($result["data"]) > 0) {
                            foreach($result["data"] as $row){
                                $txt = "";
                                if ($_SESSION["admin"]["id"] == 1) { //最高管理員才可修改狀態
                                    $txt = 'onchange="set_open(this)" self_table="users" self_col="status" where_field="id" self_uuid="' . $row["id"] . '"';
                                }
                                echo '<tr>';
                                echo '<td>';
                                if ($row["id"] != "1") {echo '<input name="box_list"   type="checkbox" value="' . $row["id"] . '">';}
                                echo '<input type="hidden" name="encrypt_' . $row["id"] . '" value="' . aes_encrypt($row["id"]) . random_str(3) . '" />';
                                echo '   </td>';
                                echo '   <td>' . $row["name"] . '</td>';
                                echo '   <td>' . $row["email"] . '</td>';
                                if ($row["id"] != 1 && $_SESSION["admin"]["id"] == 1) {
                                    echo '	 <td set="open_group">

                                                <input type="radio" id="open1_' . $row["id"] . '" ' . $txt . ' value="0" name="open_' . $row["id"] . '"  ';
                                    if ($row["status"] == "0") {
                                        echo " checked ";
                                    }

                                    if ($row["id"] == "1") {
                                        echo " disabled ";
                                    }

                                    echo ' />
                                                <label for="open1_' . $row["id"] . '">關</label>
                                                &nbsp;
                                                <input type="radio" id="open2_' . $row["id"] . '" ' . $txt . ' value="1"  name="open_' . $row["id"] . '"   ';
                                    if ($row["status"] == "1") {
                                        echo " checked ";
                                    }

                                    if ($row["id"] == "1") {
                                        echo " disabled ";
                                    }

                                    echo '  />
                                                <label for="open2_' . $row["id"] . '">開</label>
                                                ';

                                    echo '</td>';

                                }else{
                                  echo '<td>&nbsp;</td>';
                                }

                                echo '<td>'.$row["updated_at"].'</td>';

                                if ($row["id"] != 1) {

                                    if ($row["id"] == $_SESSION["admin"]["id"] || $_SESSION["admin"]["id"] == 1) {
                                        echo '<td>';
                                        echo '<a href="admin_edit.php?edit=true&id=' . $row["id"] . '"><button type="button" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary"  >修改</button></a>';
                                        if ($_SESSION["admin"]["id"] == 1 && $ROOT_mode["permission"] == 1) { //最高管理員才可修改狀態

                                            echo '<a href="admin_edit.php?edit=true&id=' . $row["id"] . '"><button type="button" class="mb-xs mt-xs mr-xs btn btn-xs btn-blue_green"  >修改權限</button></a>';
                                        }

                                        echo '</td>';
                                    } else {
                                        echo '<td></td>';
                                    }

                                } else {
                                    echo '<td></td>';
                                }

                                echo '</tr>';

                            }
                        }
                    }

                    ?>



                  </tbody>
                </table>
                <!-- /.table -->
              </div>
              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">

                <!-- Check all button -->

                <div class="pull-right">

                  <div class="btn-group">

                    <div class="dataTables_paginate paging_simple_numbers" >
                        <?php
                          if ($total > $showrow) {

                              $page = new page($total, $showrow, $curpage, $url, 2);
                              echo $page->myde_write();
                          }
                        ?>
                    </div>
                  </div>
                  <!-- /.btn-group -->
                </div>
                <!-- /.pull-right -->

            </div>
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
<!-- jQuery 3 -->
<script>

</script>
</body>
</html>
