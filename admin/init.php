<?php	
	//這頁只是個導入頁
	include_once(dirname(__FILE__)."/../phplibs/php_head.php");		

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>高雄市政府智慧市政儀表板後端管理系統</title>
  <!-- Tell the browser to be responsive to screen width -->
  <?php 
  include_once("vendor/backend/backend_css_include.php");
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
        <!--li class="header">功能選單</li-->
      
        <?php	
					include_once(dirname(__FILE__)."/../phplibs/menu_left.php");	
				?>
       
    
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>登入資訊</h1>
      <ol class="breadcrumb">
        <li><a href="init.php"><i class="fa fa-dashboard"></i> 網站管理後台</a></li>
        <li class="active">登入資訊</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
       
        <!-- /.col -->
        <div class="col-md-12">
          <div class="box box-primary">
            
            <!-- /.box-header -->
            <div class="box-body no-padding">
              
              <div class="table-responsive mailbox-messages">
              <table class="table table-hover table-striped table-bordered">
              <tbody>
              <tr>
                <td>IP</td>
                <td><?php echo get_userip(); ?></td>
              </tr>
              <tr>
                <td>帳號(Email)</td>
                <td><?php echo $_SESSION['admin']['email']; ?></td>
              </tr>
              <tr>
                <td>名稱</td>
                <td><?php echo $_SESSION['admin']['name']; ?></td>
              </tr>
              </tbody>
              </table>
               
                <!-- /.table -->
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
  	include_once(dirname(__FILE__)."/footer.php");	
  ?>
  

</div>
<!-- ./wrapper -->
<?php 
  include_once("vendor/backend/backend_javascript_include.php");
?>
<!-- jQuery 3 -->

</body>
</html>
