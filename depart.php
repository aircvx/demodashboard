<?php
	include('conndb.php');
	
	$depid = $get["dep"];
	$id = $get["id"];
    if($depid==""){header("location: index.php"); exit;}
    
	$rs = SelectSqlDB( "select * from department where id='".$depid."'" );
	if($rs["count"]==0){header("location: index.php"); exit;}
	$depart=$rs["data"][0]["name"];
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
            <a href="/">首頁</a> >> <?=$depart?>
            <div class="selectdiv" align="right">
                <select name="id" id="id" onChange="location.href='detail.php?id='+this.value;">
                    <option value="">請選擇報表議題</option>
                <?php
                    $rs=SelectSqlDB("select id,title from chart where department_id='".$depid."' and isenable=1 order by sort,id desc");
                    foreach($rs["data"] as $rst){
                        echo '<option value="'.$rst["id"].'">'.$rst["title"].'</option>';
                    }
                ?>
                </select>
            </div>
        
            <!--div id="tablestyle">
                <div class="select title_d2 color_g">
                    <img src="images/titleico_2.png"  alt=""><span><?=$type?>快覽</span>
                </div>
            </div-->
        </div>
        
        <div id="iframe_c">
        <?php
            $rs=SelectSqlDB("select * from chart where department_id='".$depid."' and isenable=1 order by sort,id desc");
            
            foreach($rs["data"] as $k=>$rst){
                $type=array_search($rst["type"], $Type);
                if($rst["iframe_sq"]!=""){$link=$rst["iframe_sq"];}else{$link=$rst["iframe_pc"];}
                echo '<div><a class="title" href="detail.php?typ='.$type.'&id='.$rst["id"].'">'.$rst["title2"].'</a><iframe src="'.$link.'" frameborder="0" scrolling="no"></iframe></div>';
                if($k%4==3){echo '<ph>';}
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
