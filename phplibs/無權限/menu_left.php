<?php




echo'<ul class="nav nav-main">';
$BarTitle="列表"; //主標題
$BarSubTitle="資料管理"; //副標題
$list_array=[];

foreach ($child as $a => $b){
	if(!is_array($b)){
		$list_array[$a]=$b;
	}
}

foreach ( $child as $a => $b){
	$expanded="";
	
	if(is_array($b)){
					
		//$BarTitle=$a;
		if(in_array(basename($_SERVER["PHP_SELF"]),array_values($b))){
			$expanded ="nav-expanded";
			$BarTitle=$a;
		
		}else{

			foreach(array_values($b) as $rst){
				
				
				$File_T=explode("_list.php",$rst)[0];
					
					if(preg_match("/".$File_T."/",basename($_SERVER["PHP_SELF"]))){
					
						$t="";
						$t=array_search($rst,array_values($b));
						if(!empty(array_keys($b)[$t])){
							$expanded ="nav-expanded";
							$BarTitle=array_keys($b)[$t];
							$BarSubTitle=$BarTitle."內容";
						
						}

					}
				
			}
			
			
			
		}
		
		echo'<li class="nav-parent '.$expanded.'">';
		
		//nav-expanded
		echo'		<a>';
		

		if(preg_grep("/admin/",array_values($b))){
			echo'			<i class="fa fa-user" aria-hidden="true"></i>';
		}else echo'			<i class="fa fa-list-alt" aria-hidden="true"></i>';
		
		echo'			<span>'.$a.'</span>';
		echo'		</a>';
		
		echo '<ul class="nav nav-children" >';
		while (list($key, $val) = each($b)) {
			$active="";
			$tmp=explode("_list.php",$val)[0];
			if(basename($_SERVER["PHP_SELF"])==$val){
				$BarSubTitle=$key;
				$active=" active ";
				
			}
			if(mb_strpos(basename($_SERVER["PHP_SELF"]),$tmp)!==false){
				$active=" active ";
			}
			
			echo '<li ><a href="'.$val.'" class="'.$active.'">'.$key.'</a></li>';
		}
		echo '</ul>';
		
		echo '</li>';
		
	}else{
		
		if(basename($_SERVER["PHP_SELF"])==$b){
			
			$BarTitle=$a;
	
			$BarSubTitle=$a."管理";
			$expanded ="nav-expanded";
			if($t=explode("-",$a)){
				$BarTitle=$t[0];
			}
		}
		
		
			$File_T=explode("_list.php",$b)[0];
			
			
			if(mb_strpos(basename($_SERVER["PHP_SELF"]),$File_T)!==false){
				$t="";
				$t=array_search($b,array_values($list_array));
				
				if(!empty(array_keys($list_array)[$t])){
					$expanded ="nav-expanded";
					$BarTitle=array_keys($list_array)[$t];
					$BarSubTitle=$BarTitle."內容";
					
				}
			}
		
		
		
		
		echo'<li class="nav-parent '.$expanded.'">';
		if(preg_match("/..\//",$b)){
			echo '<a href="'.$b.'" target="_blank">';
			echo'			<i class="fa fa-home" aria-hidden="true"></i>';
		}else {
			echo '<a href="'.$b.'">';
			echo'			<i class="fa fa-list-alt" aria-hidden="true"></i>';
		}
	
		echo'<span>'.$a.'</span>';
		echo'		</a>';
		
		echo '</li>';
	}
	
			
}
	
echo'</ul>';



?>