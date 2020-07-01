	<script src="vendor/bower_components/jquery/dist/jquery.min.js"></script>
	<!-- Bootstrap 3.3.7 -->
	<script src="vendor/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	<!-- Slimscroll -->
	<script src="vendor/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
	<!-- FastClick -->
	<script src="vendor/bower_components/fastclick/lib/fastclick.js"></script>
	<!-- AdminLTE App -->
	<script src="vendor/dist/js/adminlte.min.js"></script>
	<!-- iCheck -->
	<script src="vendor/plugins/iCheck/icheck.min.js"></script>
	
	<script src="js/global.js?t=<?php echo time()?>"></script>
  	<script src="js/main_other_backend.js?t=<?php echo time()?>"></script>

  <?php 
      $inclue_str=str_replace(array('.php', '-'),array('.js', '_'),basename($_SERVER['SCRIPT_NAME']));
      if(is_file('js/'.$inclue_str)){
        echo '<script src="js/'.$inclue_str.'?t='.time().'"></script>';
      }
  ?>