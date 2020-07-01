<div id="logo">
    <a href="index.php"><img src="images/logo.png" alt="" class="imglogo"/></a>
    <?php if(isChrome){ ?>
    <div class="selectdiv">
    <ul>
        <li>
            <a href="#">局處 <i class="fas fa-caret-square-right fa_green"></i></a>
            <ul>
            <?php
                $rs = SelectSqlDB( "select D.id,D.name from department D left join chart C on C.department_id=D.id 
                                    where C.isenable=1
                                    group by D.id,D.name,D.sort
                                    having COUNT(C.id)>0
                                    order by D.sort" );
                foreach ( $rs["data"] as $rst ) {
                    echo '<li><a href="depart.php?dep=' . $rst["id"] . '">' . $rst["name"] . '</a></li>';
                }
            ?>
            </ul>
        </li>
        <li>
            <a href="#">類別 <i class="fas fa-caret-square-right fa_blue"></i></a>
            <ul>
            <?php
                foreach($Type as $k=>$v){
                    echo '<li><a href="type.php?typ=' . $k . '">' . $v . '</a></li>';
                }	
            ?>
            </ul>
        </li>
        <li>
            <a href="#">搜尋 <i class="fas fa-search fa_white"></i></a>
            <form name="sform" method="post" action="search.php">
            <ul>
              <li class="searchli" style="padding:10px;">
                <input type="text" id="keyword" name="keyword" required style="width:90%;"><br>
                <input type="submit" value="搜尋">
              </li>
            </ul>
            </form>
        </li>
        <li><a href="/"><i class="fas fa-home"></i></a></li>
    </ul>
    </div>
    <?php }?>
</div>
<!--
<div id="menu">
  <span class="toggle">≡</span>
  <nav class="nav">

    <div class="scrolllist" id="s1">
		<a class="abtn aleft" href="#left" title="Prev"></a>
		<div class="imglist_w">
			<ul class="imglist">
				<li><a href="#">SEARCH</a></li>
                <li><a href="javascript:;"><img src="images/icon01.png" alt="">安全資訊</a>
                	<ul>
                    	<li>1999話務受理案件及滿意度</li>
                    </ul>
                </li>
                <li><a href="javascript:;"><img src="images/icon02.png" alt="">市政資訊</a>
                	<ul>
                    	<li>menu1</li>
                    </ul>
                </li>
                <li><a href="javascript:;"><img src="images/icon03.png" alt="">民生資訊</a>
                	<ul>
                    	<li>menu1</li>
                    </ul>
                </li>
			</ul>
		</div>
		<a class="abtn aright" href="#right" title="Next"></a>
	</div>

  </nav>
</div>-->
    <!--<div id="tablestyle"><span class="select">本日精選</span></div>
    </div>-->