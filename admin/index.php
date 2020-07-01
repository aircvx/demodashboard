<?php
include_once dirname(__FILE__) . "/../phplibs/php_head.php";

if (isset($_SESSION['admin'])) {
    echo "<script> document.location.href = 'init.php' </script>";
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>高雄市政府智慧市政儀表板後端管理系統</title>
  <?php
include_once "vendor/backend/backend_css_include.php";
?>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
   <b>高雄市政府智慧市政儀表板<br>後端管理系統</b>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg ">登入資訊</p>

    <form action="index_end.php" method="post">
      <div class="form-group has-feedback">
        <input type="email" name="account" class="form-control" required placeholder="帳號(Email)">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="passwd" class="form-control" required placeholder="密碼">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <!-- <div class="col-xs-8">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox"> Remember Me
            </label>
          </div>
        </div> -->
        <!-- /.col -->
        <div class="col-xs-12">
          <button type="submit" class="btn btn-primary btn-block btn-flat">登入</button>
        </div>
        <!-- /.col -->
      </div>
    </form>
  </div>

  <!-- /.login-box-body -->
</div>
<p class="text-center text-muted ">高雄市政府智慧市政儀表板 Copyright &copy; 2019. All Rights Reserved.</p>
<!-- /.login-box -->

<?php
include_once "vendor/backend/backend_javascript_include.php";
?>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' /* optional */
    });
  });
</script>
</body>
</html>
