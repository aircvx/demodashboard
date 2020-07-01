<?php
	include('conndb.php');
	
	$keyword = $post["keyword"];
	if($keyword==""){header("location: index.php"); exit;}
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
            <a href="/">首頁</a> >> "<?=$keyword?>"搜尋結果
        </div>
        
        <div id="iframe_c">
        <?php
            $rs=SelectSqlDB("select * from chart where isenable=1 and (title like '%".$keyword."%' or title2 like '%".$keyword."%' or type like '%".$keyword."%') order by sort,id desc");
            
            foreach($rs["data"] as $k=>$rst){
                $type=array_search($rst["type"], $Type);
                if($rst["iframe_sq"]!=""){$link=$rst["iframe_sq"];}else{$link=$rst["iframe_pc"];}
                echo '<div><a class="title" href="detail.php?typ='.$type.'&id='.$rst["id"].'">'.$rst["title2"].'</a><iframe src="'.$link.'" frameborder="0" scrolling="no"></iframe></div>';
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
