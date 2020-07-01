<?php
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
  <link rel="stylesheet" href="vendor/gridstack/css/gridstack.css">
  <style>
  .container {width: 100%; margin:1% auto;}
  </style>
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
            <div class="box-body ">

            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" action="chart_layout_end.php" method="post" enctype="multipart/form-data">
            <?php
              $temp = gettoken_value();
              echo '<input type="hidden" name="value" value="' . $temp["value"] . '">';
              echo '<input type="hidden" name="token" value="' . $temp["token"] . '">';
              echo '<input type="hidden" name="edit" value="' . $edit . '">';
              if ($edit == "true") {
                  echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
              }
            ?>
                <div class="box-body">

                  <div class="container">
                  圖表:<select name="chart" id="chart">
                  <?php
                    $rs1=SelectSqlDB("select * from chart where isenable=1 order by [type],[title]");
                    foreach($rs1["data"] as $rst1){
                      $_type=explode(",",$rst1["type"]);
                      echo '<option value="'.$rst1["id"].'">'.$_type[0].' - '.$rst1["title"].'</option>';
                    }
                  ?>
                  </select>
                  大小:<select name="blocksize" id="blocksize">
                    <option value="1">1x1</option>
                    <option value="2">1x2</option>
                    <option value="3">2x1</option>
                    <option value="4">2x2</option>
                  </select>
                  <button type="button" class="btn btn-primary" onclick="add();">增加</button> | 
                  <button type="button" class="btn btn-success" onclick="save();">儲存</button> | 
                  <button type="button" class="btn btn-danger" onclick="removeall();">全部清除</button>
                </div>
                <div class="container" style="background:#2c3e50; padding:10px; min-height: 400px;">
                  <div class="grid-stack grid-stack-instance-8326 grid-stack-animate" data-gs-width="12" data-gs-animate="yes">
                  </div>
                </div>
                
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                  <!--button type="submit" class="btn btn-primary">送出</button-->
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
<script src="vendor/gridstack/js/underscore-min.js"></script>
<!--script src="vendor/gridstack/js/jquery.js"></script-->
<script src="vendor/gridstack/js/jquery-ui.min.js"></script>
<script src="vendor/gridstack/js/gridstack.min.js"></script>
<script type="text/javascript">
var grid = null;
var nodes = 0;
var serialization = [
<?php
  $rs=SelectSqlDB("select A.*,B.title from chart_layout A left join chart B on B.id=A.chart_id order by A.id");
  foreach($rs["data"] as $rst){
    echo "{id:".$rst["chart_id"].", x: ".$rst["x"].", y: ".$rst["y"].", width: ".$rst["width"].", height: ".$rst["height"].", content: '".$rst["title"]."'},";
  }
?>
];
serialization = GridStackUI.Utils.sort(serialization);

$(function () {
    var options = {
        cellHeight: 150,
        verticalMargin: 10,
        always_show_resize_handle: false
    };
    
    //load data
    grid = $('.grid-stack').gridstack(options).data('gridstack');
    grid.remove_all();

    _.each(serialization, function (node) {
        grid.add_widget($('<div data-chart="'+ node.id +'" data-gs-no-resize="yes"><div class="grid-stack-item-content"><div class="ui-deletable-handle"></div>'+ node.content +'</div></div>'), 
            node.x, node.y, node.width, node.height);
    });
    
    nodes = grid.grid.nodes.length;
    
    $('.ui-deletable-handle').on('click',function(){
      if(confirm('確定刪除該區塊嗎?')){
        grid.remove_widget($(this).parent().parent());
        nodes = grid.grid.nodes.length;
      }
    });
});

function add(){
    var chartId = $('#chart').val();
    var chartTitle = $('#chart option:selected').text();
    var blocksize = $('#blocksize').val();
    var node = $('<div data-chart="'+ chartId +'" data-gs-no-resize="yes"><div class="grid-stack-item-content"><div class="ui-deletable-handle"></div>'+ chartTitle +'</div></div>');

    if(blocksize == "1"){   //1x1
      item = grid.add_widget(node, 0, 0, 2, 2);
    }
    if(blocksize == "2"){   //1x2
      item = grid.add_widget(node, 0, 0, 4, 2);
    }
    if(blocksize == "3"){   //2x1
      item = grid.add_widget(node, 0, 0, 2, 4);
    }
    if(blocksize == "4"){   //2x2
      item = grid.add_widget(node, 0, 0, 4, 4);
    }
    nodes = grid.grid.nodes.length;
    
    $('.ui-deletable-handle').off('click').on('click',function(){
      if(confirm('確定刪除該區塊嗎?')){
        grid.remove_widget($(this).parent().parent());
        nodes = grid.grid.nodes.length;
      }
    });
}

function removeall(){
    if(confirm('確定清除所有區塊嗎(清除後須再點選儲存)?')){
        grid.remove_all();
        nodes = 0;
    }
}

function save(){
    if(!confirm("確定儲存嗎?")){return;}
    //console.log(grid.grid.nodes);

    var data = [];
    for(i=0; i<grid.grid.nodes.length ; i++){
      var _node = grid.grid.nodes[i];
      //console.log(_node.x, _node.y, _node.width, _node.height, _node.el[0].dataset.chart);
      
      data.push({id:_node.el[0].dataset.chart, x:_node.x, y:_node.y, w:_node.width, h:_node.height});
    }
    console.log(data);

    $.post('layout_list_end.php',{edit:'edit', data:JSON.stringify(data)}, function(val){
        alert(val);
    });
}
</script>
</body>
</html>
