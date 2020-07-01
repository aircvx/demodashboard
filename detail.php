<?php
	include('conndb.php');
	
	$id = $get["id"];
	if($id==""){header("location: index.php"); exit;}
	//$type = $Type[$typ];

	//viewcounts
	if($id!=""){
		SelectSqlDB( "update chart set viewcounts=viewcounts+1 where id='".$id."'" );
	}
?>
<!doctype html>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>高雄市智慧市政儀表版</title>
<link rel="stylesheet" href="css/css.css">
<link rel="stylesheet" href="css/packery-docs.css" media="screen">
<link href="css/all.css" rel="stylesheet">
<link rel="stylesheet" href="gridstack/css/gridstack.css">
<script defer src="js/all.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $(".toggle").click(function() {
      $(this).toggleClass("active");
      $(".nav").slideToggle();
  });
  $(".nav > ul > li:has(ul) > a").append('<div class="arrow-bottom"></div>');
	$(".selectdiv > ul > li:has(ul) > a").append('<div class="arrow-bottom"></div>');
});
</script>
</head>

<body>

<div id="content">
    
	<?php include("header.php");?>

    <div class="container" style="padding:0px; min-height: 400px;">
        <div id="newsdiv">
        <?php
            $rs=SelectSqlDB("select A.*,B.id as dept_id,B.name as dept_name from chart A left join department B on B.id=A.department_id where A.id='".$id."' and A.isenable=1");
            $chart=$rs["data"][0];
            //echo '<a href="/">首頁</a> >> <a href="type.php?typ='.$typ.'">'.$type.'</a> >> '.$chart["title"];
            echo '<a href="/">首頁</a> >> '.$chart["title"];
        ?>
        </div>

        <div id="stitle" class="">
            <div style="float:left;">
            <img src="images/stitleico.png">
                <?php echo $chart["title"];?>
            </div>
            <div style="float:right;color:#fff;font-weight:normal;font-size:.8em;">
            <?php
                $_type=explode(",",$chart["type"]);
                foreach($_type as $t){
                    echo '<a href="type.php?typ='.array_search($t, $Type).'">'.$t.'</a> | ';
                }

                echo '<a href="depart.php?dep='.$chart["dept_id"].'">'.$chart["dept_name"].'</a>';
            ?>
            </div>
        </div>
        
        <div id="iframe_a">
        <?php
            $rs=SelectSqlDB("select * from chart where id='".$id."' and isenable=1");

            foreach($rs["data"] as $k=>$rst){
                if(isMobile){
                    if($rst["iframe_mb"]!=""){$link=$rst["iframe_mb"];}else{$link=$rst["iframe_pc"];}
                }else{
                    $link=$rst["iframe_pc"];
                }
                
                echo '<div><iframe src="'.$link.'" frameborder="0" scrolling="no"></iframe></div>';
                
                //if($k%4==3){echo '<ph>';}
            }
        ?>
        </div>
    </div>

</div>

<?php include("footer.php");?>

<script src="js/packery-docs.min.js"></script>
<script type="text/javascript" src="js/scrolllist.js"></script>

</body>
</html>
