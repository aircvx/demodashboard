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
              <h3 class="box-title"><?php echo $BarSubTitle; ?></h3>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->

            <div class="box-body no-padding">
              <div class="box-header">
                <!-- Check all button -->

                <div class="pull-right">

                <button type="button" onclick="location.href='chart_edit.php'" class="btn  btn-success btn-sm"><i class="fa fa-plus"></i> 新增</button>

                <button type="button" name="data_del" tables="chart" field="id" class="btn  btn-danger btn-sm"><i class="fa"></i> 批次刪除</button>

                  <!-- /.btn-group -->
                </div>
                <!-- /.pull-right -->
              </div>

              <div class="box-body ">
              <div class="table-responsive ">
                <table class="table table-hover table-bordered table-striped">
                <thead>
                <tr>
                    <th width="5%"> <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button></th>
                    <th width="10%">局處</th>
                    <th width="10%">類型</th>
                    <th width="">圖表名稱</th>
                    <th width="10%">順序(小&gt;大)</th>
                    <th width="6%">顯示</th>
                    <th width="6%">瀏覽數</th>
                    <th width="10%">更新時間</th>
                    <th width="10%">功能</th>
                </tr>
                </thead>
                  <tbody>

                  <?php
                      $curpage = empty($get['page']) ? 1 : params_security($get['page'], "int"); //目前頁碼
                      $query = "SELECT A.*,B.name as deptname from chart A left join department B ON B.id=A.department_id where 1=1 order by [sort] asc, [id] asc";
                      $result = SelectSqlDB($query);

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

                                  echo '<tr>';
                                  echo '<td>';
                                  echo '<input name="box_list"   type="checkbox" value="' . $row["id"] . '">';
                                  echo '<input type="hidden" name="encrypt_' . $row["id"] . '" value="' . aes_encrypt($row["id"]) . random_str(3) . '" />';
                                  echo '   </td>';
                                  echo '	 <td>' . $row["deptname"] . '</td>';
                                  echo '	 <td>' . str_replace(",","<br>",$row["type"]) . '</td>';
                                  echo '	 <td>' . $row["title"] . '</td>';

                                  echo '	 <td>
                                              <div class="input-group">
                                              <input type="number" name="orders" class="form-control" min="1" max="999" required value="' . $row["sort"] . '" onchange="set_orders(this)" title="請輸入1~999之間" data-toggle="tooltip" set_field="sort" where_field_int="' . $row["id"] . '" tables="chart" where_field="id" />
                                              <!--div class="input-group-addon" onclick="set_orders(this)" ><i class="fa fa-sort"></i></div-->
                                              </div>
                                            </td>';
                                  
                                  echo '<td>'.($row["isenable"]=="1"?"Y":"N").'</td>';
                                  echo '<td>'.number_format($row["viewcounts"]).'</td>';
                                  echo '<td>'.$row["updated_at"].'</td>';

                                  echo '<td>';
                                  echo '<a href="chart_edit.php?edit=true&id=' . $row["id"] . '"><button type="button" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary"  >修改</button></a>';
                                  echo '</td>';

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
