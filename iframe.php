<?php
include 'conndb.php';
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
<style>
#content {width:100%;}
</style>
</head>

<body>
<div id="content">
    <?php if(!isMobile){?>
    <div class="container" style="padding:0px; min-height: 400px;">
        <div class="grid-stack grid-stack-instance-8326 grid-stack-animate" data-gs-width="12" data-gs-animate="yes">
        </div>
    </div>
    <?php }else{?>
    <div id="iframe_c">
    <?php
        $rs=SelectSqlDB("select A.*,B.title,B.iframe_pc,B.iframe_mb,B.iframe_sq,B.iframe_1x1,B.iframe_1x2,B.iframe_2x1,B.iframe_2x2 from inner_layout A left join chart B on B.id=A.chart_id where B.isenable=1 order by A.id");
        foreach($rs["data"] as $k=>$rst){
            $url = "";
            if($rst["width"]=="2" && $rst["height"]=="2"){  //1x1
                $url=$rst["iframe_1x1"];
            }
            if($rst["width"]=="4" && $rst["height"]=="2"){  //1x2
                $url=$rst["iframe_1x2"];
            }
            if($rst["width"]=="2" && $rst["height"]=="4"){  //2x1
                $url=$rst["iframe_2x1"];
            }
            if($rst["width"]=="4" && $rst["height"]=="4"){  //2x2
                $url=$rst["iframe_2x2"];
            }
            if($url==""){$url=$rst["iframe_sq"];}
            if($url==""){$url=$rst["iframe_mb"];}
            if($url==""){$url=$rst["iframe_pc"];}
            echo '<div><iframe src="'.$url.'" frameborder="0" scrolling="no"></iframe></div>';
        }
    ?>
    </div>
    <?php }?>
    
</div>

<script src="gridstack/js/underscore-min.js"></script>
<script src="gridstack/js/jquery-ui.min.js"></script>
<script src="gridstack/js/gridstack.min.js"></script>
<script type="text/javascript">
var serialization = [
<?php
  $rs=SelectSqlDB("select A.*,B.title,B.iframe_pc,B.iframe_mb,B.iframe_sq,B.iframe_1x1,B.iframe_1x2,B.iframe_2x1,B.iframe_2x2 from inner_layout A left join chart B on B.id=A.chart_id where B.isenable=1 order by A.id");
  foreach($rs["data"] as $rst){
    $url = "";   $class = "";
    if($rst["width"]=="2" && $rst["height"]=="2"){  //1x1
        $url=$rst["iframe_1x1"];
        $class = "";
    }
    if($rst["width"]=="4" && $rst["height"]=="2"){  //1x2
        $url=$rst["iframe_1x2"];
        $class = "iframe2w";
    }
    if($rst["width"]=="2" && $rst["height"]=="4"){  //2x1
        $url=$rst["iframe_2x1"];
        $class = "iframe2h";
    }
    if($rst["width"]=="4" && $rst["height"]=="4"){  //2x2
        $url=$rst["iframe_2x2"];
        $class = "iframe2x";
    }
    if($url==""){$url=$rst["iframe_sq"];}
    if($url==""){$url=$rst["iframe_mb"];}
    if($url==""){$url=$rst["iframe_pc"];}

    echo "{id:".$rst["chart_id"].", x: ".$rst["x"].", y: ".$rst["y"].", width: ".$rst["width"].", height: ".$rst["height"].", title: '".$rst["title"]."',class: '".$class."', content: '".$url."'},";
  }
?>
];
serialization = GridStackUI.Utils.sort(serialization);

$(function(){
    <?php if(!isMobile){?>
    var options = {
        cellHeight: 288,
        verticalMargin: 10,
        always_show_resize_handle: false
    };

    //load data
    grid = $('.grid-stack').gridstack(options).data('gridstack');
    _.each(serialization, function (node) {
        grid.add_widget($('<div data-chart="'+ node.id +'" id="iframe_b" data-gs-no-move="yes" data-gs-no-resize="yes"><div class="grid-stack-item-content '+ node.class +'" style="padding:0px;overflow:hidden;background:transparent;"><iframe src="'+ node.content +'" frameborder="0" scrolling="no"></iframe></div></div>'), 
            node.x, node.y, node.width, node.height);
    });
    <?php }?>
})
</script>
</body>
</html>
