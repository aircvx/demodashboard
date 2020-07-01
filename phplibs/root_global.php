<?php
//子涵改版
//2019009027更新, 異動token_validation與file_upload,
/*
https://docs.google.com/document/d/1wwEkIx4J_-e5pzz0D9DoLB1BZkOkwh1Lpzm9RcTwzQc/edit?usp=sharing;
*/
function file_upload($limitedext,$upload_dir,$new_file,$option = []){
    // 判斷欄位是否指定上傳檔案…
	$callback_arr = ["up_state"=>1, "up_name"=>"", "up_message"=>"該檔案欄位為選填" ];
	
	$phpFileUploadErrors = array(
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini/上傳的檔案大小超過 php.ini 當中 upload_max_filesize 參數的設定',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form/上傳的檔案大小超過 HTML 表單中 MAX_FILE_SIZE 的限制',
		3 => 'The uploaded file was only partially uploaded/只有檔案的一部分被上傳',
		4 => 'No file was uploaded/無已上傳檔案',
		6 => 'Missing a temporary folder/遺失暫存資料夾',
		7 => 'Failed to write file to disk./寫入硬碟失敗',
		8 => 'A PHP extension stopped the file upload.',
	);
 
    if ($new_file['error'] > 0 and $new_file['error'] != 4) {
		$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>$phpFileUploadErrors[$new_file['error']] ];		
	}else{
		$file_tmp = $new_file['tmp_name'];
		$width = (isset($option["width"])) ? $option["width"] : 0;
		$height = (isset($option["height"])) ? $option["height"] : 0;
		$valign = (isset($option["valign"])) ? $option["valign"] : "middle";
		$file_name = (isset($option["file_name"])) ? $option["file_name"] : gen_uuid();
		if( strlen($file_tmp) > 0 ){
			if (is_uploaded_file($file_tmp) ){
				
			  $ext = ".".strtolower(pathinfo($new_file['name'], PATHINFO_EXTENSION));	
			  $file_name = $file_name.$ext;	 
			  
			  
			  if (in_array($ext,$limitedext)) {	
				if($width == 0 and $height == 0){ //如果不轉檔
					if($ext == ".jpg" OR $ext == ".jpeg" OR $ext == ".bmp" OR $ext == ".png"){
						if(is_image($file_tmp)){
							image_fix_orientation($file_tmp); //手機上傳要轉向							
							$img = new SimpleImage($file_tmp);				
							$img->maxarea(2000,2000);
							$img->save($upload_dir.$file_name);					
							if(is_file($upload_dir.$file_name)){
								$callback_arr = ["up_state"=>1, "up_name"=>$file_name, "up_message"=>"上傳成功" ] ;	
							}else{
								$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案寫入失敗,請檢查權限" ] ;	
							}
						}else{
							$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案損毀請重新上傳" ] ;
						}	
					}else{
						if (move_uploaded_file($file_tmp,$upload_dir.$file_name)) {
							if(is_file($upload_dir.$file_name)){
								$callback_arr = ["up_state"=>1, "up_name"=>$file_name, "up_message"=>"上傳成功" ] ;	
							}else{
								$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案寫入失敗,請檢查權限" ] ;	
							}
						}else{
							$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案寫入失敗,請檢查權限" ] ;	
						}	
					}
				}else if($ext == ".jpg" OR $ext == ".jpeg" OR $ext == ".bmp" OR $ext == ".png" OR $ext == ".gif"){ //如果要轉檔且判斷是圖檔
					if(is_image($file_tmp)){
						image_fix_orientation($file_tmp); //手機上傳要轉向	
						$img = new SimpleImage($file_tmp);
						if($width > 0 and $height > 0){
							$img->minareafill($width,$height,$valign);
						}else if($width > 0){				
							$img->resizeToWidth($width);
						}else if($height > 0){				
							$img->resizeToHeight($height);
						}
						
						
						$img->save($upload_dir.$file_name);		
						if(is_file($upload_dir.$file_name)){
							$callback_arr = ["up_state"=>1, "up_name"=>$file_name, "up_message"=>"上傳成功" ] ;	
						}else{
							$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案寫入失敗,請檢查權限" ] ;	
						}
					}else{
						$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案損毀請重新上傳" ] ;
					}
				}
					
			 }else{
				$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"檔案類型非系統允許" ] ;	 
			 }
			}else{
				
				$callback_arr = ["up_state"=>0, "up_name"=>"", "up_message"=>"非正常上傳管道" ] ;
			}
		}		
	}  

	return $callback_arr;	
}

// 座標點encode編碼
//$points = [['lat' =>24.117026, 'lng' => 120.725259],['lat' =>24.100588, 'lng' => 120.691821],['lat' =>24.088205, 'lng' => 120.730402],['lat' =>24.102702, 'lng' => 120.700694]];
function map_encode( $points_e ){
	$precision = 5;	
	foreach ($points_e as $key => $value) {
		$points[] = [$value["lat"],$value["lng"]];
	}	
    $points = map_flatten($points);
    $encodedString = '';
    $index = 0;
    $previous = array(0,0);
    foreach ( $points as $number ) {
        $number = (float)($number);
        $number = (int)round($number * pow(10, $precision));
        $diff = $number - $previous[$index % 2];
        $previous[$index % 2] = $number;
        $number = $diff;
        $index++;
        $number = ($number < 0) ? ~($number << 1) : ($number << 1);
        $chunk = '';
        while ( $number >= 0x20 ) {
            $chunk .= chr((0x20 | ($number & 0x1f)) + 63);
            $number >>= 5;
        }
        $chunk .= chr($number + 63);
        $encodedString .= $chunk;
    }
    return $encodedString;
}

function map_flatten( $array ){
    $flatten = array();
    array_walk_recursive(
        $array, // @codeCoverageIgnore
        function ($current) use (&$flatten) {
            $flatten[] = $current;
        }
    );
    return $flatten;
}

// 返回Polygon中心點
//$points = [['lat' =>24.117026, 'lng' => 120.725259],['lat' =>24.100588, 'lng' => 120.691821],['lat' =>24.088205, 'lng' => 120.730402],['lat' =>24.102702, 'lng' => 120.700694]];
function getCentroid($points_e){
	foreach ($points_e as $key => $value) {
		$coord[] = [$value["lat"],$value["lng"]];
	}		
	$centroid = array_reduce( $coord, function ($x,$y) use ($coord) {
		$len = count($coord);
		return [$x[0] + $y[0]/$len, $x[1] + $y[1]/$len];
	}, array(0,0));
	return $centroid;
}

/* 地理定位計算, 確認涵蓋位置
$myplace = ['lat' =>24.106607204769766  , 'lng' => 120.70810058178711];
$poly = [['lat' =>24.117026, 'lng' => 120.725259],['lat' =>24.100588, 'lng' => 120.691821],['lat' =>24.088205, 'lng' => 120.730402],['lat' =>24.102702, 'lng' => 120.700694]];
$distance = 100; //(公尺)
$x = isLocationOnEdgeOrPath($myplace, $poly, false, $distance);
$x = containsLocation($myplace,$poly, false);
*/	
function containsLocation($point,$polygon,$geodesic){$size=count($polygon);if($size==0){return false;}
$lat3=map_toRadians($point['lat']);$lng3=map_toRadians($point['lng']);$prev=$polygon[$size-1];$lat1=map_toRadians($prev['lat']);$lng1=map_toRadians($prev['lng']);$nIntersect=0;foreach($polygon as $point2){$dLng3=map_wrap($lng3-$lng1,-3.141592653589793,3.141592653589793);if($lat3==$lat1&&$dLng3==0){return true;}
$lat2=map_toRadians($point2['lat']);$lng2=map_toRadians($point2['lng']);if(map_intersects($lat1,$lat2,map_wrap($lng2-$lng1,-3.141592653589793,3.141592653589793),$lat3,$dLng3,$geodesic)){++$nIntersect;}
$lat1=$lat2;$lng1=$lng2;}
return($nIntersect&1)!=0;}

function isLocationOnEdgeOrPath($point,$poly,$closed,$toleranceEarth){$size=count($poly);if($size==0){return false;}
$tolerance=$toleranceEarth/6371009;$havTolerance=map_hav($tolerance);$lat3=map_toRadians($point['lat']);$lng3=map_toRadians($point['lng']);$prev=$poly[$closed?$size-1:0];$lat1=map_toRadians($prev['lat']);$lng1=map_toRadians($prev['lng']);$minAcceptable=$lat3-$tolerance;$maxAcceptable=$lat3+$tolerance;$y1=map_mercator($lat1);$y3=map_mercator($lat3);$xTry=array();foreach($poly as $point2){$lat2=map_toRadians($point2['lat']);$y2=map_mercator($lat2);$lng2=map_toRadians($point2['lng']);if(max($lat1,$lat2)>=$minAcceptable&&min($lat1,$lat2)<=$maxAcceptable){$x2=map_wrap($lng2-$lng1,-3.141592653589793,3.141592653589793);$x3Base=map_wrap($lng3-$lng1,-3.141592653589793,3.141592653589793);$xTry[0]=$x3Base;$xTry[1]=$x3Base+2*3.141592653589793;$xTry[2]=$x3Base-2*3.141592653589793;foreach($xTry as $x3){$dy=$y2-$y1;$len2=$x2*$x2+$dy*$dy;$t=$len2<=0?0:map_clamp(($x3*$x2+($y3-$y1)*$dy)/$len2,0,1);$xClosest=$t*$x2;$yClosest=$y1+$t*$dy;$latClosest=map_inversemap_mercator($yClosest);$havDist=map_havDistance($lat3,$latClosest,$x3-$xClosest);if($havDist<$havTolerance){return true;}}}
$lat1=$lat2;$lng1=$lng2;$y1=$y2;}
return false;}
function map_intersects($lat1,$lat2,$lng2,$lat3,$lng3,$geodesic){if(($lng3>=0&&$lng3>=$lng2)||($lng3<0&&$lng3<$lng2)){return false;}
if($lat3<=-3.141592653589793/2){return false;}
if($lat1<=-3.141592653589793/2||$lat2<=-3.141592653589793/2||$lat1>=3.141592653589793/2||$lat2>=3.141592653589793/2){return false;}
if($lng2<=-3.141592653589793){return false;}
$linearLat=($lat1*($lng2-$lng3)+$lat2*$lng3)/$lng2;if($lat1>=0&&$lat2>=0&&$lat3<$linearLat){return false;}
if($lat1<=0&&$lat2<=0&&$lat3>=$linearLat){return true;}
if($lat3>=3.141592653589793/2){return true;}
return $geodesic?tan($lat3)>=map_tanLatGC($lat1,$lat2,$lng2,$lng3):map_mercator($lat3)>=map_mercatorLatRhumb($lat1,$lat2,$lng2,$lng3);}
function map_mercatorLatRhumb($lat1,$lat2,$lng2,$lng3){return(map_mercator($lat1)*($lng2-$lng3)+map_mercator($lat2)*$lng3)/$lng2;}
function map_tanLatGC($lat1,$lat2,$lng2,$lng3){return(tan($lat1)*sin($lng2-$lng3)+tan($lat2)*sin($lng3))/sin($lng2);}
function map_toRadians($degrees){return($degrees*3.141592653589793)/180;}
function map_hav($x){$sinHalf=sin($x*0.5);return $sinHalf*$sinHalf;}
function map_mercator($lat){return log(tan($lat*0.5+3.141592653589793/4));}
function map_clamp($x,$low,$high){return $x<$low?$low:($x>$high?$high:$x);}
function map_wrap($n,$min,$max){return($n>=$min&&$n<$max)?$n:(map_modd($n-$min,$max-$min)+$min);}
function map_inversemap_mercator($y){return 2*atan(exp($y))-3.141592653589793/2;}
function map_havDistance($lat1,$lat2,$dLng){return map_hav($lat1-$lat2)+map_hav($dLng)*cos($lat1)*cos($lat2);}
function map_modd($x,$m){return(($x%$m)+$m)%$m;}



/*
列出間隔, +1 year為變數, 啟始跟結束日必須為yyyy-mm-dd格式
print_r(date_range("2018-08-01", "2020-01-01","+1 year"));
列出間隔, +1 month為變數, 啟始跟結束日必須為yyyy-mm-dd格式
print_r(date_range("2018-08-01", "2020-01-01","+1 month"));
列出間隔, +1 day為變數, 啟始跟結束日必須為yyyy-mm-dd格式
print_r(date_range("2018-08-01", "2020-09-01","+1 day"));
列出間隔, +1 hour為變數, 啟始跟結束日必須為yyyy-mm-dd H:i:ss格式
print_r(date_range("2018-08-01 23:59:00", "2018-08-03 00:00:00","+1 hour"));
列出間隔, +1 minute為變數, 啟始跟結束日必須為yyyy-mm-dd H:i:ss格式
print_r(date_range("2018-08-01 23:59:00", "2018-08-03 00:00:00","+1 minute"));
列出間隔, +1 second為變數, 啟始跟結束日必須為yyyy-mm-dd H:i:ss格式
print_r(date_range("2018-08-01 23:59:00", "2018-08-03 00:00:00","+1 second"));
*/
function date_range($first, $last, $step = '+1 day', $format = 'Y-m-d')
{
    $dates   = array();
	if (strpos ($step, "year") !== false) {
		$first = date("Y-01-01 00:00:00",strtotime($first));
		$format = "Y";
	}else if (strpos ($step, "month") !== false) {
		$first = date("Y-m-01 00:00:00",strtotime($first));
		$format = "Y-m";
	}else if (strpos ($step, "day") !== false) {
		$first = date("Y-m-d 00:00:00",strtotime($first));
		$format = "Y-m-d";
	}else if (strpos ($step, "hour") !== false) {
		$first = date("Y-m-d H:00:00",strtotime($first));
		$format = "Y-m-d H:i:s";
	}else if (strpos ($step, "minute") !== false) {
		$first = date("Y-m-d H:i:00",strtotime($first));
		$format = "Y-m-d H:i:s";
	}else if (strpos ($step, "second") !== false) {
		$first = date("Y-m-d H:i:s",strtotime($first));
		$format = "Y-m-d H:i:s";
	} 

    $current = strtotime($first);
    $last    = strtotime($last);

    while ($current <= $last) {
        $dates[] = date($format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}


/*
AES通常要跟原始值對稱使用,也就是原始uuid跟aes(uuid)都要傳到後台驗證
適合用於需要登入才能使用的功能
echo '<input type="hidden" name="uuid" value="'.$uuid.'">';
echo '<input type="hidden" name="encrypt" value="'.aes_encrypt($uuid).'">'; 
*/
function aes_validation($no_encrypt, $encrypt){
	if (!is_null($no_encrypt) and !is_null($encrypt)) {
		if(aes_encrypt($no_encrypt) == $encrypt){
			return "1";
		}else{
			return "0";
		}
	}else{
		return "0";
	}
}
function aes_encrypt($input){
	$key ='09251029090925102909'; 
	$data = openssl_encrypt($input, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    $data = base64_encode($data);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);		
	return $data;
}

function aes_decrypt($input){
	$key ='09251029090925102909'; 	
    $data = str_replace(array('-','_'),array('+','/'),$input);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }		
	$decrypted = openssl_decrypt(base64_decode($data), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	return $decrypted;
}

/*
md5方式用於不需要登入就能做的方式, 能避免按鍵精靈之類機器攻擊
註冊, 忘記密碼, 留言, 登入
PHP環境建立token方式如下(如果client不是PHP環境,則改用JS呼叫)
$temp = gettoken_value();
echo '<input type="hidden" name="value" value="'.$temp["value"].'">';
echo '<input type="hidden" name="token" value="'.$temp["token"].'">'; 
*/
function gettoken_value()
{
	$str = gen_uuid();
    return array(
        "value" => $str,
        "token" => md5(md5($str))
    );
}

//如果要驗證token需匯入資料表
//https://drive.google.com/file/d/1j4hZsZz_GLEUmj9xPTTdbSV0qc75hdw4/view?usp=sharing
function token_validation($value, $token){
	
	if (!empty($value) and !empty($token)) {
		/*$query = "INSERT INTO validation (token,pub_date) SELECT '".$token."',GETDATE() FROM dual WHERE not exists (select * from validation where token = '".$token."');";
		SelectSqlDB($query);
		$affected_rows = $mysqli->affected_rows	;
		
		$query = "delete FROM validation WHERE pub_date <= DATE_SUB(CURDATE(),INTERVAL 2 DAY);";
		SelectSqlDB($query);
		
		if($affected_rows > 0){ //如果能insert token代表這組token沒被用過		
			if($token == md5(md5($value))){
				return "1";
			}else{
				return "0";
			}		
		}else{
			return "0";
        }	*/
        if($token == md5(md5($value))){
            return "1";
        }else{
            return "0";
        }
	}else{
		return "0";
	}
}


//手機圖片橫向修正, 旋轉完再縮圖										
//image_fix_orientation($file_tmp);	
//$img = new SimpleImage($file_tmp)
function image_fix_orientation($filename) {
	if (@imagecreatefromjpeg($filename) !== false){
		$exif = @exif_read_data($filename);
		if (!empty($exif['Orientation'])) {
			$image = imagecreatefromjpeg($filename);
			switch ($exif['Orientation']) {
				case 3:
					$image = imagerotate($image, 180, 0);
				break;

				case 6:
					$image = imagerotate($image, -90, 0);
					break;

				case 8:
					$image = imagerotate($image, 90, 0);
					break;
			}

			imagejpeg($image, $filename, 90);
		}
	}
}	

//資料夾與檔案複製, 只能在CLI模式下執行
function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result=false;
 
        if (is_file($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if (!file_exists($dest)) {
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
                }
                $__dest=$dest."/".basename($source);
            } else {
                $__dest=$dest;
            }
            $result=copy($source, $__dest);
            chmod($__dest,$options['filePermission']);
 
        } elseif(is_dir($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if ($source[strlen($source)-1]=='/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest=$dest.basename($source);
                    @mkdir($dest);
                    chmod($dest,$options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                }
            }
 
            $dirHandle=opendir($source);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                     if(!is_dir($source."/".$file)) {
                        $__dest=$dest."/".$file;
                    } else {
                        $__dest=$dest."/".$file;
                    }
                    //echo "$source/$file ||| $__dest<br />";
                    $result=smartCopy($source."/".$file, $__dest, $options);
                }
            }
            closedir($dirHandle);
 
        } else {
            $result=false;
        }
        return $result;
}

/* 下載時隱藏檔案真實路徑
if($uuid)
echo hide_file($uuid.".mp4","demo.mp4");
echo hide_file(真實路徑及檔名,假檔名);
*/
function hide_file($file,$filename){	
	header("Content-type:application");
	header("Content-Disposition: attachment; filename=".$filename);	
	readfile($file);	
	exit(0);
}


/*
image_synthesis("小張圖片",小張圖片X軸,小張圖片Y軸,"大張圖片", "背景是小圖還是大圖 ,"輸出檔名");
*/
function image_synthesis($small,$small_x = 0,$small_y = 0, $big,$background = "small",$filename){
	
	list($width, $height) = getimagesize($big);
	$out = imagecreatetruecolor($width, $height);
	//1. 建立一塊純黑色圖片
	if($background == "small"){
	//2. 如果背景是小圖，通常前景大圖是有局部透明，作為遮照使用	
		imagecreatefrom($small,$out,$small_x,$small_y); 
		imagecreatefrom($big,$out,0,0);
	}else{	
	//3. 大張圖當底圖（通常用於浮水印之類）		
		imagecreatefrom($big,$out,0,0);	
		imagecreatefrom($small,$out,$small_x,$small_y);	
	}

	imagejpeg($out, $filename, 100);
}

function imagecreatefrom($src,$out,$x,$y){
	$srcIm = NULL;
	list($width, $height, $type, $attr) = getimagesize($src);
	switch ($type) {
		case IMAGETYPE_JPEG:
			$srcIm = imagecreatefromjpeg($src);
			break;
		case IMAGETYPE_PNG:
			$srcIm = imagecreatefrompng($src);
			break;
		case IMAGETYPE_GIF:
			$srcIm = imagecreatefromgif($src);
			break;
		case IMAGETYPE_WBMP:
			$srcIm = imagecreatefromwbmp($src);
			break;
		default:
			$srcIm = NULL;
	}
	imagecopyresampled($out, $srcIm, $x, $y, 0, 0, $width, $height, $width, $height); 		
}

//列出月份區間 dateMonths("2016-01","2016-07")
function dateMonths($start_date,$end_date,$explode='-',$addOne=false){
    $start_int = strtotime($start_date);
    $end_int = strtotime($end_date);
    if($start_int > $end_int){
        $tmp = $start_date;
        $start_date = $end_date;
        $end_date = $tmp;
    }

    $start_arr = explode($explode,$start_date);
    $start_year = intval($start_arr[0]);
    $start_month = intval($start_arr[1]);


    $end_arr = explode($explode,$end_date);
    $end_year = intval($end_arr[0]);
    $end_month = intval($end_arr[1]);


    $data = array();
    $data[] = $start_date;


    $tmp_month = $start_month;
    $tmp_year = $start_year;


    while (!(($tmp_month == $end_month) && ($tmp_year == $end_year))) {
        $tmp_month ++;
        if($tmp_month > 12){
            $tmp_month = 1;
            $tmp_year++;
        }
        $data[] = $tmp_year.$explode.str_pad($tmp_month,2,'0',STR_PAD_LEFT);
    }


    if($addOne == true){
        $tmp_month ++;
        if($tmp_month > 12){
            $tmp_month = 1;
            $tmp_year++;
        }
        $data[] = $tmp_year.$explode.str_pad($tmp_month,2,'0',STR_PAD_LEFT);
    }


    return $data;
}

//指定日期加減 getNewDate("1957-05-06",-1);
function getNewDate($day,$plus_day){
	$date	= new DateTime($day);
	$date->add(DateInterval::createFromDateString("$plus_day days"));
	return $date->format('Y-m-d') ;
}


//google縮址 原網址, API key
function google_shorturl($longUrl,$apiKey){
	$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
	$jsonData = json_encode($postData);	
	$callback = curl_page('https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey,$jsonData,array('Content-type:application/json'));
	$json = json_decode($callback,true);
	return $json["id"];
}

// $str為字串，$int為3時，返回前3個字；$int為-3時，刪除最後三個字
// fun本身會去除HTML, $mode需視情況判斷是否帶入any
function str_remove($str,$int,$mode = ""){
	return mb_substr(replace_trim(strip_html_tags($str),$mode),0,$int,"UTF-8");
}

//列出主機上傳限制 memory_limit > post_max_size > upload_max_filesize
function dump_serversize(){
	$array = array("memory_limit = " . ini_get('memory_limit') . "","post_max_size = " . ini_get('post_max_size') . "","upload_max_filesize = " . ini_get('upload_max_filesize') . "");
	return $array;
}


/*
help:
params_security(變數); 一般字串
params_security(變數,"int"); 數字
params_security(變數,"text"); textarea使用
params_security(變數,"html"); ckeditor使用
params_security(變數,"json"); json使用
params_security(變數,"xml"); xml使用
params_security(變數,"plus"); 最少過濾，後台可選擇使用
params_security(變數,"none"); 完全不過濾，後台斟酌使用
*/
function params_security($params,$type = ""){
	global $mysqli;
	$newLineArray = array("\r\n","\n\r","\n","\r");
	if($type == "int"){
		$temp = intval(strip_html_tags(replace_trim(rawurldecode($params))));
	}else if($type == "text"){
		$temp = strip_tags(str_replace($newLineArray,"<br />",remove_emoji(trim(rawurldecode($params))) ), "<br>");
	}else if($type == "html"){
		$temp = preg_html(remove_emoji(replace_trim(rawurldecode($params))));
	}else if($type == "json"){
		$temp = remove_emoji(trim(rawurldecode(stripslashes($params))));	
	}else if($type == "xml"){
		$temp = remove_emoji(trim(rawurldecode(stripslashes($params))));
	}else if($type == "plus"){
		$temp = trim(rawurldecode(stripslashes($params)));		
	}else if($type == "none"){
		$temp = trim($params);			
	}else{
		$temp = strip_html_tags(remove_emoji(replace_trim(rawurldecode($params))));
	}
	return no_ascii($temp);
}

//有些符號無法被印出的也都不要
function no_ascii($str){
	return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $str);
}



//如果跟其他function放起使用，如strip_html_tags，最好放最外層
//爬蟲下來的$html或json，再解析節點後，最好用這參數過濾一次
function mysqli_escape($foo)
{
    global $mysqli;
    if (PHP_VERSION >= 6 || !get_magic_quotes_gpc())
        return mysqli_real_escape_string($mysqli, RemoveXSS(inject_check($foo)));
    //如果要進DB，先解開預設的addslashes，改用mysqli_real_escape_string
    else
        return mysqli_real_escape_string($mysqli, RemoveXSS(inject_check(stripslashes($foo))));
}

//多維陣列排序, 使用：usort($json_array, 'soryArraybyKey'); // soryArraybyKey是function名稱, key是寫死在裡面的
/* 這function還不能共用，要客製，使用時獨立出來
function soryArraybyKey( $a, $b){
if( !isset( $a['is_photo']) && !isset( $b['is_photo'])){
return 0;
}
if( !isset( $a['is_photo'])){
return -1;
}
if( !isset( $b['is_photo'])){
return 1;
}
if( $a['is_photo'] == $b['is_photo']){
return 0;
}
return (($a['is_photo'] > $b['is_photo']) ? -1 : 1); //desc
//return (($a['is_photo'] > $b['is_photo']) ? 1 : -1); //asc
}
*/

//隨機產生英文與數字，參數為長度, 第二參數能決定類型
function random_str($length,$type = "all")
{
    $password = '';
	if($type == "all")
    $word     = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
	else if($type == "en")
	$word     = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	else if($type == "int")
	$word     = '23456789';

    $len      = strlen($word);
    
    for ($i = 0; $i < $length; $i++) {
        //$password .= $word[rand() % $len];
		$password .= $word[rand(0, $len - 1)];
    }
    
    return $password;
}

//個資法, substr_cut("123456789",3);
function substr_cut($user_name,$x = "")
{
	$strlen   = mb_strlen($user_name, 'utf-8');

	$x = ($x == "") ? round($strlen /3) : $x ;
	$len = mb_strlen($user_name, 'utf-8') - $x - $x ;
	$xing = ($strlen == 2) ? "*" : "";
	for ($i=0;$i<$len;$i++) {
		$xing .= '*';
	}    
    $firstStr = mb_substr($user_name, 0, $x, 'utf-8');
    $lastStr  = mb_substr($user_name, 0-$x, $x, 'utf-8');
	
	if($strlen == 1)
		$show_str = $user_name;
	else if($strlen == 2)
		$show_str = $firstStr . $xing ;
	else
		$show_str = $firstStr . $xing . $lastStr;
    return $show_str;
}

// echo DateDiff("2015-01-01 19:00:00","2015-01-01 18:00:00", $unit = "h");
function DateDiff($date1, $date2, $unit = "")
{
    switch ($unit) {
        case 's':
            $dividend = 1;
            break;
        case 'i': //分
            $dividend = 60;
            break;
        case 'h': //時
            $dividend = 3600;
            break;
        case 'd': //天
            $dividend = 86400;
            break;
        default:
            $dividend = 86400;
    }
    $time1 = strtotime($date1);
    $time2 = strtotime($date2);
    if ($time1 && $time2)
        return (float) ($time1 - $time2) / $dividend;
    return false;
}

function gen_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
    // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), 
    // 16 bits for "time_mid"
        mt_rand(0, 0xffff), 
    // 16 bits for "time_hi_and_version",
        
    // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000, 
    // 16 bits, 8 bits for "clk_seq_hi_res",
        
    // 8 bits for "clk_seq_low",
        
    // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000, 
    // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}




//如果不希望textarea內顯示<br>原始碼時，可使用
function br2nl($text)
{
	$breaks = array("<br />","<br>","<br/>","<p />","<p>","<p/>","</p>");  	
    return str_ireplace($breaks, "\r\n", $text); 
}

//驗證是否為json格式，下列為正確json時..
// if(validate_json($someJson)){}
function validate_json($string = NULL)
{
    return is_string($string) && is_array(json_decode($string)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

//清除html與style與惡意字元，原生的只能去除html
//如果傳入第2個參數,就表示要保留特定html(但一樣去除style)
function strip_html_tags($text,$tag = "")
{
	global $mysqli;
	if($tag != ""){
		$text = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', strip_tags($text,$tag));	    
		if (PHP_VERSION >= 6 || !get_magic_quotes_gpc())
			return mysqli_real_escape_string($mysqli, $text);
		//如果要進DB，先解開預設的addslashes，改用mysqli_real_escape_string
		else
			return mysqli_real_escape_string($mysqli, stripslashes($text));		
	}else{
		$text = preg_replace(array(
			// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			// Add line breaks before and after blocks
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu'
		), array(
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0"
		), $text);
		return strip_tags($text);
	}
}
function get_userip()
{
    $ip = null;
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // Get IP from Cloudflare
    else if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP']; // Get IP from share internet
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // Get IP from proxy
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ip = $_SERVER['HTTP_X_FORWARDED']; // Get IP from proxy
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_FORWARDED_FOR']; // Get IP from proxy
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ip = $_SERVER['HTTP_FORWARDED']; // Get IP from proxy
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ip = $_SERVER['REMOTE_ADDR'];
    return preg_replace("/\*|'|\"/isU", "", $ip);
}

//刪除目錄與底下所有檔案, SureRemoveDir($log , true); // 第二個參數: true 連 2011 目錄也刪除
function SureRemoveDir($dir, $DeleteMe)
{
    if (!$dh = @opendir($dir))
        return;
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..')
            continue;
        if (!@unlink($dir . '/' . $obj))
            SureRemoveDir($dir . '/' . $obj, true);
    }
    if ($DeleteMe) {
        closedir($dh);
        @rmdir($dir);
    }
}
function youtube_id_from_url($link)
{
    $regexstr = '~
            # Match Youtube link and embed code
            (?:                             # Group to match embed codes
                (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
                |(?:                        # Group to match if older embed
                    (?:<object .*>)?      # Match opening Object tag
                    (?:<param .*</param>)*  # Match all param tags
                    (?:<embed [^>]*src=")?  # Match embed tag to the first quote of src
                )?                          # End older embed code group
            )?                              # End embed code groups
            (?:                             # Group youtube url
                https?:\/\/                 # Either http or https
                (?:[\w]+\.)*                # Optional subdomains
                (?:                         # Group host alternatives.
                youtu\.be/                  # Either youtu.be,
                | youtube\.com              # or youtube.com
                | youtube-nocookie\.com     # or youtube-nocookie.com
                )                           # End Host Group
                (?:\S*[^\w\-\s])?           # Extra stuff up to VIDEO_ID
                ([\w\-]{11})                # $1: VIDEO_ID is numeric
                [^\s]*                      # Not a space
            )                               # End group
            "?                              # Match end quote if part of src
            (?:[^>]*>)?                       # Match any extra stuff up to close brace
            (?:                             # Group to match last embed code
                </iframe>                 # Match the end of the iframe
                |</embed></object>          # or Match the end of the older embed
            )?                              # End Group of last bit of embed code
            ~ix';
    
    preg_match($regexstr, $link, $matches);
    if (isset($matches[1]))
        return $matches[1];
    else
        return "--";
}
//如果代入any這參數, 字串間的空白也都會消失
function replace_trim($str, $mode = "")
{
    $str = trim($str);
    //去掉開始和結束的空白
    $str = preg_replace('/\s(?=\s)/', '', $str);
    //去掉跟隨別的擠在一塊的空白
    $str = preg_replace('/[\n\r\t]/', ' ', $str);
    //最後，去掉非space 的空白，用一個空格代替
    if ($mode == "any") {
        $str = str_replace(' ', '', $str);
        $str = str_replace('　', '', $str);
        $str = str_replace('&nbsp;', '', $str);
		$str = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/",'',$str);
    }
    return $str;
}

//從字串中提取數字,如果要新增比對符號...findNum($str,".,%"), 可用逗點隔開要新增比對字符

function findNum($str = '',$arr = "")
{
    $str = trim($str);
    if (empty($str)) {
        return '';
    }
    $temp   = array(
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0'
    );
	if($arr != ""){
		$arr_temp = explode(",",$arr);
		foreach ($arr_temp as $key => $value){
			array_push($temp, $value);
		}
	}
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array($str[$i], $temp)) {
            $result .= $str[$i];
        }
    }
    return $result;
}

// if(is_image("10.jpg"))
function is_image($path)
{
    /* 判斷是否為jpg, 速度快, 但還沒找到判斷png的
    if (!is_resource($file = fopen($path, 'rb'))) {
    return FALSE;
    }
    // check for the existence of the EOI segment header at the end of the file
    if (0 !== fseek($file, -2, SEEK_END) || "\xFF\xD9" !== fread($file, 2)) {
    fclose($file);
    return FALSE;
    }
    fclose($file);
    return TRUE;
    */
    $mimetype = exif_imagetype($path);
   if ($mimetype == IMAGETYPE_GIF || $mimetype == IMAGETYPE_JPEG || $mimetype == IMAGETYPE_PNG || $mimetype == IMAGETYPE_BMP)
   {
      return true;
   }else{
      return false;
   }	
}

//get_params($url,"name") 擷取參數
//get_params($url,"","PHP_URL_HOST") 擷取HOST
function get_params($url, $params, $type = "PHP_URL_QUERY")
{
	if($type == "PHP_URL_QUERY"){
		parse_str(parse_url($url, PHP_URL_QUERY), $params_output);
		return isset($params_output[$params]) ? $params_output[$params] : "--";
	}else if($type == "PHP_URL_HOST"){
		$parse = parse_url($url);
		return $parse['host'];
	}
}
//如果要上傳檔案，post_str就必須是陣列形式，如果不是上傳檔案，可以用字串或陣列
//curl_page("網址",array('name' => 'lucien','FILE' => '@'.dirname(__FILE__)."/gemini.gif")); 
function curl_page($path,$post_str = "none", $HTTPHEADER = array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4'))
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $path);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //這邊設1, 才不會直接輸出
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //超過30秒沒返回就失敗
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //連線10秒
	if($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')){
		$HTTPHEADER = array('X-FORWARDED-FOR:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'', 'CLIENT-IP:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'');
	}		
    curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER); //构造IP
    //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    curl_setopt($ch, CURLOPT_REFERER, $path); //构造?路
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if ($post_str != "none") {
        curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
		if (is_array($post_str))
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_str)); //POST參數，如果接到的是array形式
		else
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str); //POST參數，如果接到的是字串形式
    }
    $retValue = curl_exec($ch);
    curl_close($ch);
    return $retValue;
}

//確認遠端檔案是否存在
function getHeaders($url, $HTTPHEADER = array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4'))
{
	$headers = array();
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT , 15);	
	if($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')){
		$HTTPHEADER = array('X-FORWARDED-FOR:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'', 'CLIENT-IP:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'');
	}	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER);
	$output = curl_exec($ch);
	curl_close($ch);
	
	$headers = [];
	$output = rtrim($output);
	$data = explode("\n",$output);
	$headers['status'] = $data[0];
	array_shift($data);

	foreach($data as $part){

		//some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
		$middle = explode(":",$part,2);

		//Supress warning message if $middle[1] does not exist, Thanks to @crayons
		if ( !isset($middle[1]) ) { $middle[1] = null; }

		$headers[trim($middle[0])] = trim($middle[1]);
	}	
	return $headers;
}

function curl_file($url, $path, $HTTPHEADER = array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4'))
{

    $fp = fopen($path, 'w+');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT , 20);
    curl_setopt($ch, CURLOPT_FILE, $fp);
	if($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')){
		$HTTPHEADER = array('X-FORWARDED-FOR:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'', 'CLIENT-IP:'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'');
	}	
    curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER); //构造IP
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    curl_setopt($ch, CURLOPT_REFERER, $url); //构造?路    
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    if (filesize($path) > 0)
        return true;
}

//讀取csv
function inputCsv($handle)
{
    $out = array();
    $n   = 0;
    while ($data = fgetcsv($handle, 10000)) {
        $num = count($data);
        for ($i = 0; $i < $num; $i++) {
            $out[$n][$i] = $data[$i];
        }
        $n++;
    }
    return $out;
}
//西元轉民國, $in_txt是指間格符號
function dateTo_c($in_date, $in_txt = ""){
	$date = new DateTime($in_date);
	$date->modify("-1911 year");
	return str_replace("-", $in_txt , ltrim($date->format("Y-m-d"),"0"));
}
//民國轉西元, $in_txt是指間格符號
function dateTo_ad($date,$in_txt = "."){
	$date = explode($in_txt, $date);
	$year = (int)$date[0] + 1911 ;
	return $year."-".$date[1]."-".$date[2];
}
//全型半型互轉, 0是半轉全, 1是全轉半
function cover_width_small($strs, $types = '1')
{
    $nt = array(
        "(",
        ")",
        "[",
        "]",
        "{",
        "}",
        ".",
        ",",
        ";",
        ":",
        "-",
        "?",
        "!",
        "@",
        "#",
        "$",
        "%",
        "&",
        "|",
        "\\",
        "/",
        "+",
        "=",
        "*",
        "~",
        "`",
        "'",
        "\"",
        "<",
        ">",
        "^",
        "_",
        "0",
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "a",
        "b",
        "c",
        "d",
        "e",
        "f",
        "g",
        "h",
        "i",
        "j",
        "k",
        "l",
        "m",
        "n",
        "o",
        "p",
        "q",
        "r",
        "s",
        "t",
        "u",
        "v",
        "w",
        "x",
        "y",
        "z",
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T",
        "U",
        "V",
        "W",
        "X",
        "Y",
        "Z",
        " "
    );
    $wt = array(
        "（",
        "）",
        "〔",
        "〕",
        "｛",
        "｝",
        "﹒",
        "，",
        "；",
        "：",
        "－",
        "？",
        "！",
        "＠",
        "＃",
        "＄",
        "％",
        "＆",
        "｜",
        "＼",
        "／",
        "＋",
        "＝",
        "＊",
        "～",
        "、",
        "、",
        "＂",
        "＜",
        "＞",
        "︿",
        "＿",
        "０",
        "１",
        "２",
        "３",
        "４",
        "５",
        "６",
        "７",
        "８",
        "９",
        "ａ",
        "ｂ",
        "ｃ",
        "ｄ",
        "ｅ",
        "ｆ",
        "ｇ",
        "ｈ",
        "ｉ",
        "ｊ",
        "ｋ",
        "ｌ",
        "ｍ",
        "ｎ",
        "ｏ",
        "ｐ",
        "ｑ",
        "ｒ",
        "ｓ",
        "ｔ",
        "ｕ",
        "ｖ",
        "ｗ",
        "ｘ",
        "ｙ",
        "ｚ",
        "Ａ",
        "Ｂ",
        "Ｃ",
        "Ｄ",
        "Ｅ",
        "Ｆ",
        "Ｇ",
        "Ｈ",
        "Ｉ",
        "Ｊ",
        "Ｋ",
        "Ｌ",
        "Ｍ",
        "Ｎ",
        "Ｏ",
        "Ｐ",
        "Ｑ",
        "Ｒ",
        "Ｓ",
        "Ｔ",
        "Ｕ",
        "Ｖ",
        "Ｗ",
        "Ｘ",
        "Ｙ",
        "Ｚ",
        "　"
    );
    
    if ($types == '0') {
        // narrow to wide
        $strtmp = str_replace($nt, $wt, $strs);
    } else {
        // wide to narrow
        $strtmp = str_replace($wt, $nt, $strs);
    }
    return $strtmp;
}
//HEX TO RGB
function hexToRGB($hexStr)
{
    $colorVal          = hexdec($hexStr);
    $rgbArray['red']   = 0xFF & ($colorVal >> 0x10);
    $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
    $rgbArray['blue']  = 0xFF & $colorVal;
    return $rgbArray;
}

function inject_check($sql_str){
	//return $sql_str = preg_replace('/select|insert|\sand|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|rename|drop|create|truncate|alter|commit|rollback|merge|call|explain|lock|grant|revoke|savepoint|transaction|set/i', '', $sql_str);
	return $sql_str = preg_replace('/select|insert|\sand|update|delete|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|rename|drop|create|truncate|alter|commit|rollback|merge|call|explain|lock|grant|revoke|savepoint|transaction|set/i', '', $sql_str);
}

function RemoveXSS($val){
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
    
    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        
        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }
    
    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array(
        'javascript',
        'vbscript',
        'expression',
        'applet',
        'meta',
        'xml',
        'blink',
        'link',
        'style',
        'script',
        'embed',
        'object',
        'iframe',
        'frame',
        'frameset',
        'ilayer',
        'layer',
        'bgsound',
        'title',
        'base'
    );
    $ra2 = array(
        'onabort',
        'onactivate',
        'onafterprint',
        'onafterupdate',
        'onbeforeactivate',
        'onbeforecopy',
        'onbeforecut',
        'onbeforedeactivate',
        'onbeforeeditfocus',
        'onbeforepaste',
        'onbeforeprint',
        'onbeforeunload',
        'onbeforeupdate',
        'onblur',
        'onbounce',
        'oncellchange',
        'onchange',
        'onclick',
        'oncontextmenu',
        'oncontrolselect',
        'oncopy',
        'oncut',
        'ondataavailable',
        'ondatasetchanged',
        'ondatasetcomplete',
        'ondblclick',
        'ondeactivate',
        'ondrag',
        'ondragend',
        'ondragenter',
        'ondragleave',
        'ondragover',
        'ondragstart',
        'ondrop',
        'onerror',
        'onerrorupdate',
        'onfilterchange',
        'onfinish',
        'onfocus',
        'onfocusin',
        'onfocusout',
        'onhelp',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onlayoutcomplete',
        'onload',
        'onlosecapture',
        'onmousedown',
        'onmouseenter',
        'onmouseleave',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup',
        'onmousewheel',
        'onmove',
        'onmoveend',
        'onmovestart',
        'onpaste',
        'onpropertychange',
        'onreadystatechange',
        'onreset',
        'onresize',
        'onresizeend',
        'onresizestart',
        'onrowenter',
        'onrowexit',
        'onrowsdelete',
        'onrowsinserted',
        'onscroll',
        'onselect',
        'onselectionchange',
        'onselectstart',
        'onstart',
        'onstop',
        'onsubmit',
        'onunload',
        'eval',
        'behaviour',
        'style',
        'class'
    );
    $ra  = array_merge($ra1, $ra2);
    
    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val         = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    
    return $val;
}

//以下都是過濾安全HTML使用的，命名為preg_html，來源：http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/
function preg_html($t, $C=1, $S=array()){
    $C = is_array($C) ? $C : array();
    if(!empty($C['valid_xhtml'])){
     $C['elements'] = empty($C['elements']) ? '*-acronym-big-center-dir-font-isindex-s-strike-tt' : $C['elements'];
     $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 2;
     $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 2;
    }
    // config eles
    $e = array('a'=>1, 'abbr'=>1, 'acronym'=>1, 'address'=>1, 'applet'=>1, 'area'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'blockquote'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'caption'=>1, 'center'=>1, 'cite'=>1, 'code'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'dd'=>1, 'del'=>1, 'details'=>1, 'dfn'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'dt'=>1, 'em'=>1, 'embed'=>1, 'fieldset'=>1, 'figcaption'=>1, 'figure'=>1, 'font'=>1, 'footer'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'isindex'=>1, 'kbd'=>1, 'keygen'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'link'=>1, 'main'=>1, 'map'=>1, 'mark'=>1, 'menu'=>1, 'meta'=>1, 'meter'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'output'=>1, 'p'=>1, 'param'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'small'=>1, 'source'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'style'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'time'=>1, 'tr'=>1, 'track'=>1, 'tt'=>1, 'u'=>1, 'ul'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1); // 118 incl. deprecated & some Ruby
    
    if(!empty($C['safe'])){
     unset($e['applet'], $e['audio'], $e['canvas'], $e['embed'], $e['iframe'], $e['object'], $e['script'], $e['video']);
    }
    $x = !empty($C['elements']) ? str_replace(array("\n", "\r", "\t", ' '), '', $C['elements']) : '*';
    if($x == '-*'){$e = array();}
    elseif(strpos($x, '*') === false){$e = array_flip(explode(',', $x));}
    else{
     if(isset($x[1])){
      preg_match_all('`(?:^|-|\+)[^\-+]+?(?=-|\+|$)`', $x, $m, PREG_SET_ORDER);
      for($i=count($m); --$i>=0;){$m[$i] = $m[$i][0];}
      foreach($m as $v){
       if($v[0] == '+'){$e[substr($v, 1)] = 1;}
       if($v[0] == '-' && isset($e[($v = substr($v, 1))]) && !in_array('+'. $v, $m)){unset($e[$v]);}
      }
     }
    }
    $C['elements'] =& $e;
    // config attrs
    $x = !empty($C['deny_attribute']) ? strtolower(str_replace(array("\n", "\r", "\t", ' '), '', $C['deny_attribute'])) : '';
    $x = array_flip((isset($x[0]) && $x[0] == '*') ? str_replace('/', 'data-', explode('-', str_replace('data-', '/', $x))) : explode(',', $x. (!empty($C['safe']) ? ',on*' : '')));
    $C['deny_attribute'] = $x;
    // config URLs
    $x = (isset($C['schemes'][2]) && strpos($C['schemes'], ':')) ? strtolower($C['schemes']) : 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, tel, telnet'. (empty($C['safe']) ? ', app, javascript; *: data, javascript, ' : '; *:'). 'file, http, https';
    $C['schemes'] = array();
    foreach(explode(';', trim(str_replace(array(' ', "\t", "\r", "\n"), '', $x), ';')) as $v){
     $x = $x2 = null; list($x, $x2) = explode(':', $v, 2);
     if($x2){$C['schemes'][$x] = array_flip(explode(',', $x2));}
    }
    if(!isset($C['schemes']['*'])){
     $C['schemes']['*'] = array('file'=>1, 'http'=>1, 'https'=>1);
     if(empty($C['safe'])){$C['schemes']['*'] += array('data'=>1, 'javascript'=>1);}
    }
    if(!empty($C['safe']) && empty($C['schemes']['style'])){$C['schemes']['style'] = array('!'=>1);}
    $C['abs_url'] = isset($C['abs_url']) ? $C['abs_url'] : 0;
    if(!isset($C['base_url']) or !preg_match('`^[a-zA-Z\d.+\-]+://[^/]+/(.+?/)?$`', $C['base_url'])){
     $C['base_url'] = $C['abs_url'] = 0;
    }
    // config rest
    $C['and_mark'] = empty($C['and_mark']) ? 0 : 1;
    $C['anti_link_spam'] = (isset($C['anti_link_spam']) && is_array($C['anti_link_spam']) && count($C['anti_link_spam']) == 2 && (empty($C['anti_link_spam'][0]) or hl_regex($C['anti_link_spam'][0])) && (empty($C['anti_link_spam'][1]) or hl_regex($C['anti_link_spam'][1]))) ? $C['anti_link_spam'] : 0;
    $C['anti_mail_spam'] = isset($C['anti_mail_spam']) ? $C['anti_mail_spam'] : 0;
    $C['balance'] = isset($C['balance']) ? (bool)$C['balance'] : 1;
    $C['cdata'] = isset($C['cdata']) ? $C['cdata'] : (empty($C['safe']) ? 3 : 0);
    $C['clean_ms_char'] = empty($C['clean_ms_char']) ? 0 : $C['clean_ms_char'];
    $C['comment'] = isset($C['comment']) ? $C['comment'] : (empty($C['safe']) ? 3 : 0);
    $C['css_expression'] = empty($C['css_expression']) ? 0 : 1;
    $C['direct_list_nest'] = empty($C['direct_list_nest']) ? 0 : 1;
    $C['hexdec_entity'] = isset($C['hexdec_entity']) ? $C['hexdec_entity'] : 1;
    $C['hook'] = (!empty($C['hook']) && function_exists($C['hook'])) ? $C['hook'] : 0;
    $C['hook_tag'] = (!empty($C['hook_tag']) && function_exists($C['hook_tag'])) ? $C['hook_tag'] : 0;
    $C['keep_bad'] = isset($C['keep_bad']) ? $C['keep_bad'] : 6;
    $C['lc_std_val'] = isset($C['lc_std_val']) ? (bool)$C['lc_std_val'] : 1;
    $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 1;
    $C['named_entity'] = isset($C['named_entity']) ? (bool)$C['named_entity'] : 1;
    $C['no_deprecated_attr'] = isset($C['no_deprecated_attr']) ? $C['no_deprecated_attr'] : 1;
    $C['parent'] = isset($C['parent'][0]) ? strtolower($C['parent']) : 'body';
    $C['show_setting'] = !empty($C['show_setting']) ? $C['show_setting'] : 0;
    $C['style_pass'] = empty($C['style_pass']) ? 0 : 1;
    $C['tidy'] = empty($C['tidy']) ? 0 : $C['tidy'];
    $C['unique_ids'] = isset($C['unique_ids']) && (!preg_match('`\W`', $C['unique_ids'])) ? $C['unique_ids'] : 1;
    $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 0;
    
    if(isset($GLOBALS['C'])){$reC = $GLOBALS['C'];}
    $GLOBALS['C'] = $C;
    $S = is_array($S) ? $S : hl_spec($S);
    if(isset($GLOBALS['S'])){$reS = $GLOBALS['S'];}
    $GLOBALS['S'] = $S;
    
    $t = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f]`', '', $t);
    if($C['clean_ms_char']){
     $x = array("\x7f"=>'', "\x80"=>'&#8364;', "\x81"=>'', "\x83"=>'&#402;', "\x85"=>'&#8230;', "\x86"=>'&#8224;', "\x87"=>'&#8225;', "\x88"=>'&#710;', "\x89"=>'&#8240;', "\x8a"=>'&#352;', "\x8b"=>'&#8249;', "\x8c"=>'&#338;', "\x8d"=>'', "\x8e"=>'&#381;', "\x8f"=>'', "\x90"=>'', "\x95"=>'&#8226;', "\x96"=>'&#8211;', "\x97"=>'&#8212;', "\x98"=>'&#732;', "\x99"=>'&#8482;', "\x9a"=>'&#353;', "\x9b"=>'&#8250;', "\x9c"=>'&#339;', "\x9d"=>'', "\x9e"=>'&#382;', "\x9f"=>'&#376;');
     $x = $x + ($C['clean_ms_char'] == 1 ? array("\x82"=>'&#8218;', "\x84"=>'&#8222;', "\x91"=>'&#8216;', "\x92"=>'&#8217;', "\x93"=>'&#8220;', "\x94"=>'&#8221;') : array("\x82"=>'\'', "\x84"=>'"', "\x91"=>'\'', "\x92"=>'\'', "\x93"=>'"', "\x94"=>'"'));
     $t = strtr($t, $x);
    }
    if($C['cdata'] or $C['comment']){$t = preg_replace_callback('`<!(?:(?:--.*?--)|(?:\[CDATA\[.*?\]\]))>`sm', 'hl_cmtcd', $t);}
    $t = preg_replace_callback('`&amp;([a-zA-Z][a-zA-Z0-9]{1,30}|#(?:[0-9]{1,8}|[Xx][0-9A-Fa-f]{1,7}));`', 'hl_ent', str_replace('&', '&amp;', $t));
    if($C['unique_ids'] && !isset($GLOBALS['hl_Ids'])){$GLOBALS['hl_Ids'] = array();}
    if($C['hook']){$t = $C['hook']($t, $C, $S);}
    if($C['show_setting'] && preg_match('`^[a-z][a-z0-9_]*$`i', $C['show_setting'])){
     $GLOBALS[$C['show_setting']] = array('config'=>$C, 'spec'=>$S, 'time'=>microtime());
    }
    // main
    $t = preg_replace_callback('`<(?:(?:\s|$)|(?:[^>]*(?:>|$)))|>`m', 'hl_tag', $t);
    $t = $C['balance'] ? hl_bal($t, $C['keep_bad'], $C['parent']) : $t;
    $t = (($C['cdata'] or $C['comment']) && strpos($t, "\x01") !== false) ? str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05"), array('', '', '&', '<', '>'), $t) : $t;
    $t = $C['tidy'] ? hl_tidy($t, $C['tidy'], $C['parent']) : $t;
    unset($C, $e);
    if(isset($reC)){$GLOBALS['C'] = $reC;}
    if(isset($reS)){$GLOBALS['S'] = $reS;}
    return $t;
    }
    
    function hl_attrval($a, $t, $p){
    // check attr val against $S
    static $ma = array('accesskey', 'class', 'itemtype', 'rel');
    $s = in_array($a, $ma) ? ' ' : ($a == 'srcset' ? ',': '');
    $r = array();
    $t = !empty($s) ? explode($s, $t) : array($t);
    foreach($t as $tk=>$tv){
     $o = 1; $tv = trim($tv); $l = strlen($tv);
     foreach($p as $k=>$v){
      if(!$l){continue;}
      switch($k){
       case 'maxlen': if($l > $v){$o = 0;}
       break; case 'minlen': if($l < $v){$o = 0;}
       break; case 'maxval': if((float)($tv) > $v){$o = 0;}
       break; case 'minval': if((float)($tv) < $v){$o = 0;}
       break; case 'match': if(!preg_match($v, $tv)){$o = 0;}
       break; case 'nomatch': if(preg_match($v, $tv)){$o = 0;}
       break; case 'oneof':
        $m = 0;
        foreach(explode('|', $v) as $n){if($tv == $n){$m = 1; break;}}
        $o = $m;
       break; case 'noneof':
        $m = 1;
        foreach(explode('|', $v) as $n){if($tv == $n){$m = 0; break;}}
        $o = $m;
       break; default:
       break;
      }
      if(!$o){break;}
     }
     if($o){$r[] = $tv;}
    }
    if($s == ','){$s = ', ';} 
    $r = implode($s, $r);
    return (isset($r[0]) ? $r : (isset($p['default']) ? $p['default'] : 0));
    }
    
    function hl_bal($t, $do=1, $in='div'){
    // balance tags
    // by content
    $cB = array('blockquote'=>1, 'form'=>1, 'map'=>1, 'noscript'=>1); // Block
    $cE = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1); // Empty
    $cF = array('a'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'button'=>1, 'canvas'=>1, 'del'=>1, 'details'=>1, 'div'=>1, 'dd'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'header'=>1, 'iframe'=>1, 'ins'=>1, 'li'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'section'=>1, 'style'=>1, 'td'=>1, 'th'=>1, 'video'=>1); // Flow; later context-wise dynamic move of ins & del to $cI
    $cI = array('abbr'=>1, 'acronym'=>1, 'address'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'caption'=>1, 'cite'=>1, 'code'=>1, 'data'=>1, 'datalist'=>1, 'dfn'=>1, 'dt'=>1, 'em'=>1, 'figcaption'=>1, 'font'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hgroup'=>1, 'i'=>1, 'kbd'=>1, 'label'=>1, 'legend'=>1, 'mark'=>1, 'meter'=>1, 'output'=>1, 'p'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rt'=>1, 's'=>1, 'samp'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1); // Inline
    $cN = array('a'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'address'=>array('address'=>1, 'article'=>1, 'aside'=>1, 'header'=>1, 'keygen'=>1, 'footer'=>1, 'nav'=>1, 'section'=>1), 'button'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'fieldset'=>1, 'form'=>1, 'iframe'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'fieldset'=>array('fieldset'=>1), 'footer'=>array('header'=>1, 'footer'=>1), 'form'=>array('form'=>1), 'header'=>array('header'=>1, 'footer'=>1), 'label'=>array('label'=>1), 'main'=>array('main'=>1), 'meter'=>array('meter'=>1), 'noscript'=>array('script'=>1), 'pre'=>array('big'=>1, 'font'=>1, 'img'=>1, 'object'=>1, 'script'=>1, 'small'=>1, 'sub'=>1, 'sup'=>1), 'progress'=>array('progress'=>1), 'rb'=>array('ruby'=>1), 'rt'=>array('ruby'=>1), 'time'=>array('time'=>1), ); // Illegal
    $cN2 = array_keys($cN);
    $cS = array('colgroup'=>array('col'=>1), 'datalist'=>array('option'=>1), 'dir'=>array('li'=>1), 'dl'=>array('dd'=>1, 'dt'=>1), 'hgroup'=>array('h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1), 'menu'=>array('li'=>1), 'ol'=>array('li'=>1), 'optgroup'=>array('option'=>1), 'option'=>array('#pcdata'=>1), 'rbc'=>array('rb'=>1), 'rp'=>array('#pcdata'=>1), 'rtc'=>array('rt'=>1), 'ruby'=>array('rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1), 'select'=>array('optgroup'=>1, 'option'=>1), 'script'=>array('#pcdata'=>1), 'table'=>array('caption'=>1, 'col'=>1, 'colgroup'=>1, 'tfoot'=>1, 'tbody'=>1, 'tr'=>1, 'thead'=>1), 'tbody'=>array('tr'=>1), 'tfoot'=>array('tr'=>1), 'textarea'=>array('#pcdata'=>1), 'thead'=>array('tr'=>1), 'tr'=>array('td'=>1, 'th'=>1), 'ul'=>array('li'=>1)); // Specific - immediate parent-child
    if($GLOBALS['C']['direct_list_nest']){$cS['ol'] = $cS['ul'] = $cS['menu'] += array('menu'=>1, 'ol'=>1, 'ul'=>1);}
    $cO = array('address'=>array('p'=>1), 'applet'=>array('param'=>1), 'audio'=>array('source'=>1, 'track'=>1), 'blockquote'=>array('script'=>1), 'details'=>array('summary'=>1), 'fieldset'=>array('legend'=>1, '#pcdata'=>1),  'figure'=>array('figcaption'=>1),'form'=>array('script'=>1), 'map'=>array('area'=>1), 'object'=>array('param'=>1, 'embed'=>1), 'video'=>array('source'=>1, 'track'=>1)); // Other
    $cT = array('colgroup'=>1, 'dd'=>1, 'dt'=>1, 'li'=>1, 'option'=>1, 'p'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1); // Omitable closing
    // block/inline type; a/ins/del both type; #pcdata: text
    $eB = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'del'=>1, 'details'=>1, 'dir'=>1, 'dl'=>1, 'div'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'ins'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hr'=>1, 'isindex'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'section'=>1, 'style'=>1, 'table'=>1, 'ul'=>1);
    $eI = array('#pcdata'=>1, 'a'=>1, 'abbr'=>1, 'acronym'=>1, 'applet'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'cite'=>1, 'code'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'del'=>1, 'dfn'=>1, 'em'=>1, 'embed'=>1, 'figcaption'=>1, 'font'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'kbd'=>1, 'label'=>1, 'link'=>1, 'map'=>1, 'mark'=>1, 'meta'=>1, 'meter'=>1, 'object'=>1, 'output'=>1, 'progress'=>1, 'q'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'select'=>1, 'script'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'textarea'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1);
    $eN = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'big'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'fieldset'=>1, 'font'=>1, 'footer'=>1, 'form'=>1, 'header'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'meter'=>1, 'nav'=>1, 'object'=>1, 'progress'=>1, 'ruby'=>1, 'script'=>1, 'select'=>1, 'small'=>1, 'sub'=>1, 'sup'=>1, 'textarea'=>1, 'time'=>1); // Exclude from specific ele; $cN values
    $eO = array('area'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'dd'=>1, 'dt'=>1, 'hgroup'=>1, 'keygen'=>1, 'legend'=>1, 'li'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'script'=>1, 'source'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'thead'=>1, 'th'=>1, 'tr'=>1, 'track'=>1); // Missing in $eB & $eI
    $eF = $eB + $eI;
    
    // $in sets allowed child
    $in = ((isset($eF[$in]) && $in != '#pcdata') or isset($eO[$in])) ? $in : 'div';
    if(isset($cE[$in])){
     return (!$do ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $t));
    }
    if(isset($cS[$in])){$inOk = $cS[$in];}
    elseif(isset($cI[$in])){$inOk = $eI; $cI['del'] = 1; $cI['ins'] = 1;}
    elseif(isset($cF[$in])){$inOk = $eF; unset($cI['del'], $cI['ins']);}
    elseif(isset($cB[$in])){$inOk = $eB; unset($cI['del'], $cI['ins']);}
    if(isset($cO[$in])){$inOk = $inOk + $cO[$in];}
    if(isset($cN[$in])){$inOk = array_diff_assoc($inOk, $cN[$in]);}
    
    $t = explode('<', $t);
    $ok = $q = array(); // $q seq list of open non-empty ele
    ob_start();
    
    for($i=-1, $ci=count($t); ++$i<$ci;){
     // allowed $ok in parent $p
     if($ql = count($q)){
      $p = array_pop($q);
      $q[] = $p;
      if(isset($cS[$p])){$ok = $cS[$p];}
      elseif(isset($cI[$p])){$ok = $eI; $cI['del'] = 1; $cI['ins'] = 1;}
      elseif(isset($cF[$p])){$ok = $eF; unset($cI['del'], $cI['ins']);}
      elseif(isset($cB[$p])){$ok = $eB; unset($cI['del'], $cI['ins']);}
      if(isset($cO[$p])){$ok = $ok + $cO[$p];}
      if(isset($cN[$p])){$ok = array_diff_assoc($ok, $cN[$p]);}
     }else{$ok = $inOk; unset($cI['del'], $cI['ins']);}
     // bad tags, & ele content
     if(isset($e) && ($do == 1 or (isset($ok['#pcdata']) && ($do == 3 or $do == 5)))){
      echo '&lt;', $s, $e, $a, '&gt;';
     }
     if(isset($x[0])){
      if(strlen(trim($x)) && (($ql && isset($cB[$p])) or (isset($cB[$in]) && !$ql))){
       echo '<div>', $x, '</div>';
      }
      elseif($do < 3 or isset($ok['#pcdata'])){echo $x;}
      elseif(strpos($x, "\x02\x04")){
       foreach(preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $x, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $v){
        echo (substr($v, 0, 2) == "\x01\x02" ? $v : ($do > 4 ? preg_replace('`\S`', '', $v) : ''));
       }
      }elseif($do > 4){echo preg_replace('`\S`', '', $x);}
     }
     // get markup
     if(!preg_match('`^(/?)([a-z1-6]+)([^>]*)>(.*)`sm', $t[$i], $r)){$x = $t[$i]; continue;}
     $s = null; $e = null; $a = null; $x = null; list($all, $s, $e, $a, $x) = $r;
     // close tag
     if($s){
      if(isset($cE[$e]) or !in_array($e, $q)){continue;} // Empty/unopen
      if($p == $e){array_pop($q); echo '</', $e, '>'; unset($e); continue;} // Last open
      $add = ''; // Nesting - close open tags that need to be
      for($j=-1, $cj=count($q); ++$j<$cj;){  
       if(($d = array_pop($q)) == $e){break;}
       else{$add .= "</{$d}>";}
      }
      echo $add, '</', $e, '>'; unset($e); continue;
     }
     // open tag
     // $cB ele needs $eB ele as child
     if(isset($cB[$e]) && strlen(trim($x))){
      $t[$i] = "{$e}{$a}>";
      array_splice($t, $i+1, 0, 'div>'. $x); unset($e, $x); ++$ci; --$i; continue;
     }
     if((($ql && isset($cB[$p])) or (isset($cB[$in]) && !$ql)) && !isset($eB[$e]) && !isset($ok[$e])){
      array_splice($t, $i, 0, 'div>'); unset($e, $x); ++$ci; --$i; continue;
     }
     // if no open ele, $in = parent; mostly immediate parent-child relation should hold
     if(!$ql or !isset($eN[$e]) or !array_intersect($q, $cN2)){
      if(!isset($ok[$e])){
       if($ql && isset($cT[$p])){echo '</', array_pop($q), '>'; unset($e, $x); --$i;}
       continue;
      }
      if(!isset($cE[$e])){$q[] = $e;}
      echo '<', $e, $a, '>'; unset($e); continue;
     }
     // specific parent-child
     if(isset($cS[$p][$e])){
      if(!isset($cE[$e])){$q[] = $e;}
      echo '<', $e, $a, '>'; unset($e); continue;
     }
     // nesting
     $add = '';
     $q2 = array();
     for($k=-1, $kc=count($q); ++$k<$kc;){
      $d = $q[$k];
      $ok2 = array();
      if(isset($cS[$d])){$q2[] = $d; continue;}
      $ok2 = isset($cI[$d]) ? $eI : $eF;
      if(isset($cO[$d])){$ok2 = $ok2 + $cO[$d];}
      if(isset($cN[$d])){$ok2 = array_diff_assoc($ok2, $cN[$d]);}
      if(!isset($ok2[$e])){
       if(!$k && !isset($inOk[$e])){continue 2;}
       $add = "</{$d}>";
       for(;++$k<$kc;){$add = "</{$q[$k]}>{$add}";}
       break;
      }
      else{$q2[] = $d;}
     }
     $q = $q2;
     if(!isset($cE[$e])){$q[] = $e;}
     echo $add, '<', $e, $a, '>'; unset($e); continue;
    }
    
    // end
    if($ql = count($q)){
     $p = array_pop($q);
     $q[] = $p;
     if(isset($cS[$p])){$ok = $cS[$p];}
     elseif(isset($cI[$p])){$ok = $eI; $cI['del'] = 1; $cI['ins'] = 1;}
     elseif(isset($cF[$p])){$ok = $eF; unset($cI['del'], $cI['ins']);}
     elseif(isset($cB[$p])){$ok = $eB; unset($cI['del'], $cI['ins']);}
     if(isset($cO[$p])){$ok = $ok + $cO[$p];}
     if(isset($cN[$p])){$ok = array_diff_assoc($ok, $cN[$p]);}
    }else{$ok = $inOk; unset($cI['del'], $cI['ins']);}
    if(isset($e) && ($do == 1 or (isset($ok['#pcdata']) && ($do == 3 or $do == 5)))){
     echo '&lt;', $s, $e, $a, '&gt;';
    }
    if(isset($x[0])){
     if(strlen(trim($x)) && (($ql && isset($cB[$p])) or (isset($cB[$in]) && !$ql))){
      echo '<div>', $x, '</div>';
     }
     elseif($do < 3 or isset($ok['#pcdata'])){echo $x;}
     elseif(strpos($x, "\x02\x04")){
      foreach(preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $x, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $v){
       echo (substr($v, 0, 2) == "\x01\x02" ? $v : ($do > 4 ? preg_replace('`\S`', '', $v) : ''));
      }
     }elseif($do > 4){echo preg_replace('`\S`', '', $x);}
    }
    while(!empty($q) && ($e = array_pop($q))){echo '</', $e, '>';}
    $o = ob_get_contents();
    ob_end_clean();
    return $o;
    }
    
    function hl_cmtcd($t){
    // comment/CDATA sec handler
    $t = $t[0];
    global $C;
    if(!($v = $C[$n = $t[3] == '-' ? 'comment' : 'cdata'])){return $t;}
    if($v == 1){return '';}
    if($n == 'comment' && $v < 4){
     if(substr(($t = preg_replace('`--+`', '-', substr($t, 4, -3))), -1) != ' '){$t .= ' ';}
    }
    else{$t = substr($t, 1, -1);}
    $t = $v == 2 ? str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $t) : $t;
    return str_replace(array('&', '<', '>'), array("\x03", "\x04", "\x05"), ($n == 'comment' ? "\x01\x02\x04!--$t--\x05\x02\x01" : "\x01\x01\x04$t\x05\x01\x01"));
    }
    
    function hl_ent($t){
    // entitity handler
    global $C;
    $t = $t[1];
    static $U = array('quot'=>1,'amp'=>1,'lt'=>1,'gt'=>1);
    static $N = array('fnof'=>'402', 'Alpha'=>'913', 'Beta'=>'914', 'Gamma'=>'915', 'Delta'=>'916', 'Epsilon'=>'917', 'Zeta'=>'918', 'Eta'=>'919', 'Theta'=>'920', 'Iota'=>'921', 'Kappa'=>'922', 'Lambda'=>'923', 'Mu'=>'924', 'Nu'=>'925', 'Xi'=>'926', 'Omicron'=>'927', 'Pi'=>'928', 'Rho'=>'929', 'Sigma'=>'931', 'Tau'=>'932', 'Upsilon'=>'933', 'Phi'=>'934', 'Chi'=>'935', 'Psi'=>'936', 'Omega'=>'937', 'alpha'=>'945', 'beta'=>'946', 'gamma'=>'947', 'delta'=>'948', 'epsilon'=>'949', 'zeta'=>'950', 'eta'=>'951', 'theta'=>'952', 'iota'=>'953', 'kappa'=>'954', 'lambda'=>'955', 'mu'=>'956', 'nu'=>'957', 'xi'=>'958', 'omicron'=>'959', 'pi'=>'960', 'rho'=>'961', 'sigmaf'=>'962', 'sigma'=>'963', 'tau'=>'964', 'upsilon'=>'965', 'phi'=>'966', 'chi'=>'967', 'psi'=>'968', 'omega'=>'969', 'thetasym'=>'977', 'upsih'=>'978', 'piv'=>'982', 'bull'=>'8226', 'hellip'=>'8230', 'prime'=>'8242', 'Prime'=>'8243', 'oline'=>'8254', 'frasl'=>'8260', 'weierp'=>'8472', 'image'=>'8465', 'real'=>'8476', 'trade'=>'8482', 'alefsym'=>'8501', 'larr'=>'8592', 'uarr'=>'8593', 'rarr'=>'8594', 'darr'=>'8595', 'harr'=>'8596', 'crarr'=>'8629', 'lArr'=>'8656', 'uArr'=>'8657', 'rArr'=>'8658', 'dArr'=>'8659', 'hArr'=>'8660', 'forall'=>'8704', 'part'=>'8706', 'exist'=>'8707', 'empty'=>'8709', 'nabla'=>'8711', 'isin'=>'8712', 'notin'=>'8713', 'ni'=>'8715', 'prod'=>'8719', 'sum'=>'8721', 'minus'=>'8722', 'lowast'=>'8727', 'radic'=>'8730', 'prop'=>'8733', 'infin'=>'8734', 'ang'=>'8736', 'and'=>'8743', 'or'=>'8744', 'cap'=>'8745', 'cup'=>'8746', 'int'=>'8747', 'there4'=>'8756', 'sim'=>'8764', 'cong'=>'8773', 'asymp'=>'8776', 'ne'=>'8800', 'equiv'=>'8801', 'le'=>'8804', 'ge'=>'8805', 'sub'=>'8834', 'sup'=>'8835', 'nsub'=>'8836', 'sube'=>'8838', 'supe'=>'8839', 'oplus'=>'8853', 'otimes'=>'8855', 'perp'=>'8869', 'sdot'=>'8901', 'lceil'=>'8968', 'rceil'=>'8969', 'lfloor'=>'8970', 'rfloor'=>'8971', 'lang'=>'9001', 'rang'=>'9002', 'loz'=>'9674', 'spades'=>'9824', 'clubs'=>'9827', 'hearts'=>'9829', 'diams'=>'9830', 'apos'=>'39',  'OElig'=>'338', 'oelig'=>'339', 'Scaron'=>'352', 'scaron'=>'353', 'Yuml'=>'376', 'circ'=>'710', 'tilde'=>'732', 'ensp'=>'8194', 'emsp'=>'8195', 'thinsp'=>'8201', 'zwnj'=>'8204', 'zwj'=>'8205', 'lrm'=>'8206', 'rlm'=>'8207', 'ndash'=>'8211', 'mdash'=>'8212', 'lsquo'=>'8216', 'rsquo'=>'8217', 'sbquo'=>'8218', 'ldquo'=>'8220', 'rdquo'=>'8221', 'bdquo'=>'8222', 'dagger'=>'8224', 'Dagger'=>'8225', 'permil'=>'8240', 'lsaquo'=>'8249', 'rsaquo'=>'8250', 'euro'=>'8364', 'nbsp'=>'160', 'iexcl'=>'161', 'cent'=>'162', 'pound'=>'163', 'curren'=>'164', 'yen'=>'165', 'brvbar'=>'166', 'sect'=>'167', 'uml'=>'168', 'copy'=>'169', 'ordf'=>'170', 'laquo'=>'171', 'not'=>'172', 'shy'=>'173', 'reg'=>'174', 'macr'=>'175', 'deg'=>'176', 'plusmn'=>'177', 'sup2'=>'178', 'sup3'=>'179', 'acute'=>'180', 'micro'=>'181', 'para'=>'182', 'middot'=>'183', 'cedil'=>'184', 'sup1'=>'185', 'ordm'=>'186', 'raquo'=>'187', 'frac14'=>'188', 'frac12'=>'189', 'frac34'=>'190', 'iquest'=>'191', 'Agrave'=>'192', 'Aacute'=>'193', 'Acirc'=>'194', 'Atilde'=>'195', 'Auml'=>'196', 'Aring'=>'197', 'AElig'=>'198', 'Ccedil'=>'199', 'Egrave'=>'200', 'Eacute'=>'201', 'Ecirc'=>'202', 'Euml'=>'203', 'Igrave'=>'204', 'Iacute'=>'205', 'Icirc'=>'206', 'Iuml'=>'207', 'ETH'=>'208', 'Ntilde'=>'209', 'Ograve'=>'210', 'Oacute'=>'211', 'Ocirc'=>'212', 'Otilde'=>'213', 'Ouml'=>'214', 'times'=>'215', 'Oslash'=>'216', 'Ugrave'=>'217', 'Uacute'=>'218', 'Ucirc'=>'219', 'Uuml'=>'220', 'Yacute'=>'221', 'THORN'=>'222', 'szlig'=>'223', 'agrave'=>'224', 'aacute'=>'225', 'acirc'=>'226', 'atilde'=>'227', 'auml'=>'228', 'aring'=>'229', 'aelig'=>'230', 'ccedil'=>'231', 'egrave'=>'232', 'eacute'=>'233', 'ecirc'=>'234', 'euml'=>'235', 'igrave'=>'236', 'iacute'=>'237', 'icirc'=>'238', 'iuml'=>'239', 'eth'=>'240', 'ntilde'=>'241', 'ograve'=>'242', 'oacute'=>'243', 'ocirc'=>'244', 'otilde'=>'245', 'ouml'=>'246', 'divide'=>'247', 'oslash'=>'248', 'ugrave'=>'249', 'uacute'=>'250', 'ucirc'=>'251', 'uuml'=>'252', 'yacute'=>'253', 'thorn'=>'254', 'yuml'=>'255');
    if($t[0] != '#'){
     return ($C['and_mark'] ? "\x06" : '&'). (isset($U[$t]) ? $t : (isset($N[$t]) ? (!$C['named_entity'] ? '#'. ($C['hexdec_entity'] > 1 ? 'x'. dechex($N[$t]) : $N[$t]) : $t) : 'amp;'. $t)). ';';
    }
    if(($n = ctype_digit($t = substr($t, 1)) ? intval($t) : hexdec(substr($t, 1))) < 9 or ($n > 13 && $n < 32) or $n == 11 or $n == 12 or ($n > 126 && $n < 160 && $n != 133) or ($n > 55295 && ($n < 57344 or ($n > 64975 && $n < 64992) or $n == 65534 or $n == 65535 or $n > 1114111))){
     return ($C['and_mark'] ? "\x06" : '&'). "amp;#{$t};";
    }
    return ($C['and_mark'] ? "\x06" : '&'). '#'. (((ctype_digit($t) && $C['hexdec_entity'] < 2) or !$C['hexdec_entity']) ? $n : 'x'. dechex($n)). ';';
    }
    
    function hl_prot($p, $c=null){
    // check URL scheme
    global $C;
    $b = $a = '';
    if($c == null){$c = 'style'; $b = $p[1]; $a = $p[3]; $p = trim($p[2]);}
    $c = isset($C['schemes'][$c]) ? $C['schemes'][$c] : $C['schemes']['*'];
    static $d = 'denied:';
    if(isset($c['!']) && substr($p, 0, 7) != $d){$p = "$d$p";}
    if(isset($c['*']) or !strcspn($p, '#?;') or (substr($p, 0, 7) == $d)){return "{$b}{$p}{$a}";} // All ok, frag, query, param
    if(preg_match('`^([^:?[@!$()*,=/\'\]]+?)(:|&#(58|x3a);|%3a|\\\\0{0,4}3a).`i', $p, $m) && !isset($c[strtolower($m[1])])){ // Denied prot
     return "{$b}{$d}{$p}{$a}";
    }
    if($C['abs_url']){
     if($C['abs_url'] == -1 && strpos($p, $C['base_url']) === 0){ // Make url rel
      $p = substr($p, strlen($C['base_url']));
     }elseif(empty($m[1])){ // Make URL abs
      if(substr($p, 0, 2) == '//'){$p = substr($C['base_url'], 0, strpos($C['base_url'], ':')+1). $p;}
      elseif($p[0] == '/'){$p = preg_replace('`(^.+?://[^/]+)(.*)`', '$1', $C['base_url']). $p;}
      elseif(strcspn($p, './')){$p = $C['base_url']. $p;}
      else{
       preg_match('`^([a-zA-Z\d\-+.]+://[^/]+)(.*)`', $C['base_url'], $m);
       $p = preg_replace('`(?<=/)\./`', '', $m[2]. $p);
       while(preg_match('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', $p)){
        $p = preg_replace('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', '', $p);
       }
       $p = $m[1]. $p;
      }
     }
    }
    return "{$b}{$p}{$a}";
    }
    
    function hl_regex($p){
    // check regex
    if(empty($p)){return 0;}
    if($v = function_exists('error_clear_last') && function_exists('error_get_last')){error_clear_last();}
    else{
     if($t = ini_get('track_errors')){$o = isset($php_errormsg) ? $php_errormsg : null;}
     else{ini_set('track_errors', 1);}
     unset($php_errormsg);
    }
    if(($d = ini_get('display_errors'))){ini_set('display_errors', 0);}
    preg_match($p, '');
    if($v){$r = error_get_last() == null ? 1 : 0; }
    else{
     $r = isset($php_errormsg) ? 0 : 1;
     if($t){$php_errormsg = isset($o) ? $o : null;}
     else{ini_set('track_errors', 0);}
    }
    if($d){ini_set('display_errors', 1);}
    return $r;
    }
    
    function hl_spec($t){
    // final $spec
    $s = array();
    if(!function_exists('hl_aux1')){function hl_aux1($m){
     return substr(str_replace(array(";", "|", "~", " ", ",", "/", "(", ")", '`"'), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", '"'), $m[0]), 1, -1);
    }}
    $t = str_replace(array("\t", "\r", "\n", ' '), '', preg_replace_callback('/"(?>(`.|[^"])*)"/sm', 'hl_aux1', trim($t))); 
    for($i = count(($t = explode(';', $t))); --$i>=0;){
     $w = $t[$i];
     if(empty($w) or ($e = strpos($w, '=')) === false or !strlen(($a =  substr($w, $e+1)))){continue;}
     $y = $n = array();
     foreach(explode(',', $a) as $v){
      if(!preg_match('`^([a-z:\-\*]+)(?:\((.*?)\))?`i', $v, $m)){continue;}
      if(($x = strtolower($m[1])) == '-*'){$n['*'] = 1; continue;}
      if($x[0] == '-'){$n[substr($x, 1)] = 1; continue;}
      if(!isset($m[2])){$y[$x] = 1; continue;}
      foreach(explode('/', $m[2]) as $m){
       if(empty($m) or ($p = strpos($m, '=')) == 0 or $p < 5){$y[$x] = 1; continue;}
       $y[$x][strtolower(substr($m, 0, $p))] = str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08"), array(";", "|", "~", " ", ",", "/", "(", ")"), substr($m, $p+1));
      }
      if(isset($y[$x]['match']) && !hl_regex($y[$x]['match'])){unset($y[$x]['match']);}
      if(isset($y[$x]['nomatch']) && !hl_regex($y[$x]['nomatch'])){unset($y[$x]['nomatch']);}
     }
     if(!count($y) && !count($n)){continue;}
     foreach(explode(',', substr($w, 0, $e)) as $v){
      if(!strlen(($v = strtolower($v)))){continue;}
      if(count($y)){if(!isset($s[$v])){$s[$v] = $y;} else{$s[$v] = array_merge($s[$v], $y);}}
      if(count($n)){if(!isset($s[$v]['n'])){$s[$v]['n'] = $n;} else{$s[$v]['n'] = array_merge($s[$v]['n'], $n);}}
     }
    }
    return $s;
    }
    
    function hl_tag($t){
    // tag/attribute handler
    global $C;
    $t = $t[0];
    // invalid < >
    if($t == '< '){return '&lt; ';}
    if($t == '>'){return '&gt;';}
    if(!preg_match('`^<(/?)([a-zA-Z][a-zA-Z1-6]*)([^>]*?)\s?>$`m', $t, $m)){
     return str_replace(array('<', '>'), array('&lt;', '&gt;'), $t);
    }elseif(!isset($C['elements'][($e = strtolower($m[2]))])){
     return (($C['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
    }
    // attr string
    $a = str_replace(array("\n", "\r", "\t"), ' ', trim($m[3]));
    // tag transform
    static $eD = array('acronym'=>1, 'applet'=>1, 'big'=>1, 'center'=>1, 'dir'=>1, 'font'=>1, 'isindex'=>1, 's'=>1, 'strike'=>1, 'tt'=>1); // Deprecated
    if($C['make_tag_strict'] && isset($eD[$e])){
     $trt = hl_tag2($e, $a, $C['make_tag_strict']);
     if(!$e){return (($C['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');}
    }
    // close tag
    static $eE = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1); // Empty ele
    if(!empty($m[1])){
     return (!isset($eE[$e]) ? (empty($C['hook_tag']) ? "</$e>" : $C['hook_tag']($e)) : (($C['keep_bad'])%2 ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : ''));
    }
    
    // open tag & attr
    static $aN = array('abbr'=>array('td'=>1, 'th'=>1), 'accept'=>array('form'=>1, 'input'=>1), 'accept-charset'=>array('form'=>1), 'action'=>array('form'=>1), 'align'=>array('applet'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'div'=>1, 'embed'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'allowfullscreen'=>array('iframe'=>1), 'alt'=>array('applet'=>1, 'area'=>1, 'img'=>1, 'input'=>1), 'archive'=>array('applet'=>1, 'object'=>1), 'async'=>array('script'=>1), 'autocomplete'=>array('form'=>1, 'input'=>1), 'autofocus'=>array('button'=>1, 'input'=>1, 'keygen'=>1, 'select'=>1, 'textarea'=>1), 'autoplay'=>array('audio'=>1, 'video'=>1), 'axis'=>array('td'=>1, 'th'=>1), 'bgcolor'=>array('embed'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1), 'border'=>array('img'=>1, 'object'=>1, 'table'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellpadding'=>array('table'=>1), 'cellspacing'=>array('table'=>1), 'challenge'=>array('keygen'=>1), 'char'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charoff'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charset'=>array('a'=>1, 'script'=>1), 'checked'=>array('command'=>1, 'input'=>1), 'cite'=>array('blockquote'=>1, 'del'=>1, 'ins'=>1, 'q'=>1), 'classid'=>array('object'=>1), 'clear'=>array('br'=>1), 'code'=>array('applet'=>1), 'codebase'=>array('applet'=>1, 'object'=>1), 'codetype'=>array('object'=>1), 'color'=>array('font'=>1), 'cols'=>array('textarea'=>1), 'colspan'=>array('td'=>1, 'th'=>1), 'compact'=>array('dir'=>1, 'dl'=>1, 'menu'=>1, 'ol'=>1, 'ul'=>1), 'content'=>array('meta'=>1), 'controls'=>array('audio'=>1, 'video'=>1), 'coords'=>array('a'=>1, 'area'=>1), 'crossorigin'=>array('img'=>1), 'data'=>array('object'=>1), 'datetime'=>array('del'=>1, 'ins'=>1, 'time'=>1), 'declare'=>array('object'=>1), 'default'=>array('track'=>1), 'defer'=>array('script'=>1), 'dirname'=>array('input'=>1, 'textarea'=>1), 'disabled'=>array('button'=>1, 'command'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'optgroup'=>1, 'option'=>1, 'select'=>1, 'textarea'=>1), 'download'=>array('a'=>1), 'enctype'=>array('form'=>1), 'face'=>array('font'=>1), 'flashvars'=>array('embed'=>1), 'for'=>array('label'=>1, 'output'=>1), 'form'=>array('button'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'object'=>1, 'output'=>1, 'select'=>1, 'textarea'=>1), 'formaction'=>array('button'=>1, 'input'=>1), 'formenctype'=>array('button'=>1, 'input'=>1), 'formmethod'=>array('button'=>1, 'input'=>1), 'formnovalidate'=>array('button'=>1, 'input'=>1), 'formtarget'=>array('button'=>1, 'input'=>1), 'frame'=>array('table'=>1), 'frameborder'=>array('iframe'=>1), 'headers'=>array('td'=>1, 'th'=>1), 'height'=>array('applet'=>1, 'canvas'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'high'=>array('meter'=>1), 'href'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hreflang'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'icon'=>array('command'=>1), 'ismap'=>array('img'=>1, 'input'=>1), 'keyparams'=>array('keygen'=>1), 'keytype'=>array('keygen'=>1), 'kind'=>array('track'=>1), 'label'=>array('command'=>1, 'menu'=>1, 'option'=>1, 'optgroup'=>1, 'track'=>1), 'language'=>array('script'=>1), 'list'=>array('input'=>1), 'longdesc'=>array('img'=>1, 'iframe'=>1), 'loop'=>array('audio'=>1, 'video'=>1), 'low'=>array('meter'=>1), 'marginheight'=>array('iframe'=>1), 'marginwidth'=>array('iframe'=>1), 'max'=>array('input'=>1, 'meter'=>1, 'progress'=>1), 'maxlength'=>array('input'=>1, 'textarea'=>1), 'media'=>array('a'=>1, 'area'=>1, 'link'=>1, 'source'=>1, 'style'=>1), 'mediagroup'=>array('audio'=>1, 'video'=>1), 'method'=>array('form'=>1), 'min'=>array('input'=>1, 'meter'=>1), 'model'=>array('embed'=>1), 'multiple'=>array('input'=>1, 'select'=>1), 'muted'=>array('audio'=>1, 'video'=>1), 'name'=>array('a'=>1, 'applet'=>1, 'button'=>1, 'embed'=>1, 'fieldset'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'keygen'=>1, 'map'=>1, 'object'=>1, 'output'=>1, 'param'=>1, 'select'=>1, 'textarea'=>1), 'nohref'=>array('area'=>1), 'noshade'=>array('hr'=>1), 'novalidate'=>array('form'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'object'=>array('applet'=>1), 'open'=>array('details'=>1), 'optimum'=>array('meter'=>1), 'pattern'=>array('input'=>1), 'ping'=>array('a'=>1, 'area'=>1), 'placeholder'=>array('input'=>1, 'textarea'=>1), 'pluginspage'=>array('embed'=>1), 'pluginurl'=>array('embed'=>1), 'poster'=>array('video'=>1), 'pqg'=>array('keygen'=>1), 'preload'=>array('audio'=>1, 'video'=>1), 'prompt'=>array('isindex'=>1), 'pubdate'=>array('time'=>1), 'radiogroup'=>array('command'=>1), 'readonly'=>array('input'=>1, 'textarea'=>1), 'rel'=>array('a'=>1, 'area'=>1, 'link'=>1), 'required'=>array('input'=>1, 'select'=>1, 'textarea'=>1), 'rev'=>array('a'=>1), 'reversed'=>array('ol'=>1), 'rows'=>array('textarea'=>1), 'rowspan'=>array('td'=>1, 'th'=>1), 'rules'=>array('table'=>1), 'sandbox'=>array('iframe'=>1), 'scope'=>array('td'=>1, 'th'=>1), 'scoped'=>array('style'=>1), 'scrolling'=>array('iframe'=>1), 'seamless'=>array('iframe'=>1), 'selected'=>array('option'=>1), 'shape'=>array('a'=>1, 'area'=>1), 'size'=>array('font'=>1, 'hr'=>1, 'input'=>1, 'select'=>1), 'sizes'=>array('link'=>1), 'span'=>array('col'=>1, 'colgroup'=>1), 'src'=>array('audio'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'script'=>1, 'source'=>1, 'track'=>1, 'video'=>1), 'srcdoc'=>array('iframe'=>1), 'srclang'=>array('track'=>1), 'srcset'=>array('img'=>1), 'standby'=>array('object'=>1), 'start'=>array('ol'=>1), 'step'=>array('input'=>1), 'summary'=>array('table'=>1), 'target'=>array('a'=>1, 'area'=>1, 'form'=>1), 'type'=>array('a'=>1, 'area'=>1, 'button'=>1, 'command'=>1, 'embed'=>1, 'input'=>1, 'li'=>1, 'link'=>1, 'menu'=>1, 'object'=>1, 'ol'=>1, 'param'=>1, 'script'=>1, 'source'=>1, 'style'=>1, 'ul'=>1), 'typemustmatch'=>array('object'=>1), 'usemap'=>array('img'=>1, 'input'=>1, 'object'=>1), 'valign'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'value'=>array('button'=>1, 'data'=>1, 'input'=>1, 'li'=>1, 'meter'=>1, 'option'=>1, 'param'=>1, 'progress'=>1), 'valuetype'=>array('param'=>1), 'vspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'width'=>array('applet'=>1, 'canvas'=>1, 'col'=>1, 'colgroup'=>1, 'embed'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'wmode'=>array('embed'=>1), 'wrap'=>array('textarea'=>1)); // Ele-specific
    static $aNA = array('aria-activedescendant'=>1, 'aria-atomic'=>1, 'aria-autocomplete'=>1, 'aria-busy'=>1, 'aria-checked'=>1, 'aria-controls'=>1, 'aria-describedby'=>1, 'aria-disabled'=>1, 'aria-dropeffect'=>1, 'aria-expanded'=>1, 'aria-flowto'=>1, 'aria-grabbed'=>1, 'aria-haspopup'=>1, 'aria-hidden'=>1, 'aria-invalid'=>1, 'aria-label'=>1, 'aria-labelledby'=>1, 'aria-level'=>1, 'aria-live'=>1, 'aria-multiline'=>1, 'aria-multiselectable'=>1, 'aria-orientation'=>1, 'aria-owns'=>1, 'aria-posinset'=>1, 'aria-pressed'=>1, 'aria-readonly'=>1, 'aria-relevant'=>1, 'aria-required'=>1, 'aria-selected'=>1, 'aria-setsize'=>1, 'aria-sort'=>1, 'aria-valuemax'=>1, 'aria-valuemin'=>1, 'aria-valuenow'=>1, 'aria-valuetext'=>1); // ARIA
    static $aNE = array('allowfullscreen'=>1, 'checkbox'=>1, 'checked'=>1, 'command'=>1, 'compact'=>1, 'declare'=>1, 'defer'=>1, 'default'=>1, 'disabled'=>1, 'hidden'=>1, 'inert'=>1, 'ismap'=>1, 'itemscope'=>1, 'multiple'=>1, 'nohref'=>1, 'noresize'=>1, 'noshade'=>1, 'nowrap'=>1, 'open'=>1, 'radio'=>1, 'readonly'=>1, 'required'=>1, 'reversed'=>1, 'selected'=>1); // Empty
    static $aNO = array('onabort'=>1, 'onblur'=>1, 'oncanplay'=>1, 'oncanplaythrough'=>1, 'onchange'=>1, 'onclick'=>1, 'oncontextmenu'=>1, 'oncopy'=>1, 'oncuechange'=>1, 'oncut'=>1, 'ondblclick'=>1, 'ondrag'=>1, 'ondragend'=>1, 'ondragenter'=>1, 'ondragleave'=>1, 'ondragover'=>1, 'ondragstart'=>1, 'ondrop'=>1, 'ondurationchange'=>1, 'onemptied'=>1, 'onended'=>1, 'onerror'=>1, 'onfocus'=>1, 'onformchange'=>1, 'onforminput'=>1, 'oninput'=>1, 'oninvalid'=>1, 'onkeydown'=>1, 'onkeypress'=>1, 'onkeyup'=>1, 'onload'=>1, 'onloadeddata'=>1, 'onloadedmetadata'=>1, 'onloadstart'=>1, 'onlostpointercapture'=>1, 'onmousedown'=>1, 'onmousemove'=>1, 'onmouseout'=>1, 'onmouseover'=>1, 'onmouseup'=>1, 'onmousewheel'=>1, 'onpaste'=>1, 'onpause'=>1, 'onplay'=>1, 'onplaying'=>1, 'onpointercancel'=>1, 'ongotpointercapture'=>1, 'onpointerdown'=>1, 'onpointerenter'=>1, 'onpointerleave'=>1, 'onpointermove'=>1, 'onpointerout'=>1, 'onpointerover'=>1, 'onpointerup'=>1, 'onprogress'=>1, 'onratechange'=>1, 'onreadystatechange'=>1, 'onreset'=>1, 'onsearch'=>1, 'onscroll'=>1, 'onseeked'=>1, 'onseeking'=>1, 'onselect'=>1, 'onshow'=>1, 'onstalled'=>1, 'onsubmit'=>1, 'onsuspend'=>1, 'ontimeupdate'=>1, 'ontoggle'=>1, 'ontouchcancel'=>1, 'ontouchend'=>1, 'ontouchmove'=>1, 'ontouchstart'=>1, 'onvolumechange'=>1, 'onwaiting'=>1, 'onwheel'=>1); // Event
    static $aNP = array('action'=>1, 'cite'=>1, 'classid'=>1, 'codebase'=>1, 'data'=>1, 'href'=>1, 'itemtype'=>1, 'longdesc'=>1, 'model'=>1, 'pluginspage'=>1, 'pluginurl'=>1, 'src'=>1, 'srcset'=>1, 'usemap'=>1); // Need scheme check; excludes style, on*
    static $aNU = array('accesskey'=>1, 'class'=>1, 'contenteditable'=>1, 'contextmenu'=>1, 'dir'=>1, 'draggable'=>1, 'dropzone'=>1, 'hidden'=>1, 'id'=>1, 'inert'=>1, 'itemid'=>1, 'itemprop'=>1, 'itemref'=>1, 'itemscope'=>1, 'itemtype'=>1, 'lang'=>1, 'role'=>1, 'spellcheck'=>1, 'style'=>1, 'tabindex'=>1, 'title'=>1, 'translate'=>1, 'xmlns'=>1, 'xml:base'=>1, 'xml:lang'=>1, 'xml:space'=>1); // Univ; excludes on*, aria*
    
    if($C['lc_std_val']){
     // predef attr vals for $eAL & $aNE ele
     static $aNL = array('all'=>1, 'auto'=>1, 'baseline'=>1, 'bottom'=>1, 'button'=>1, 'captions'=>1, 'center'=>1, 'chapters'=>1, 'char'=>1, 'checkbox'=>1, 'circle'=>1, 'col'=>1, 'colgroup'=>1, 'color'=>1, 'cols'=>1, 'data'=>1, 'date'=>1, 'datetime'=>1, 'datetime-local'=>1, 'default'=>1, 'descriptions'=>1, 'email'=>1, 'file'=>1, 'get'=>1, 'groups'=>1, 'hidden'=>1, 'image'=>1, 'justify'=>1, 'left'=>1, 'ltr'=>1, 'metadata'=>1, 'middle'=>1, 'month'=>1, 'none'=>1, 'number'=>1, 'object'=>1, 'password'=>1, 'poly'=>1, 'post'=>1, 'preserve'=>1, 'radio'=>1, 'range'=>1, 'rect'=>1, 'ref'=>1, 'reset'=>1, 'right'=>1, 'row'=>1, 'rowgroup'=>1, 'rows'=>1, 'rtl'=>1, 'search'=>1, 'submit'=>1, 'subtitles'=>1, 'tel'=>1, 'text'=>1, 'time'=>1, 'top'=>1, 'url'=>1, 'week'=>1);
     static $eAL = array('a'=>1, 'area'=>1, 'bdo'=>1, 'button'=>1, 'col'=>1, 'fieldset'=>1, 'form'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'script'=>1, 'select'=>1, 'table'=>1, 'td'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1, 'track'=>1, 'xml:space'=>1);
     $lcase = isset($eAL[$e]) ? 1 : 0;
    }
    
    $depTr = 0;
    if($C['no_deprecated_attr']){
     // depr attr:applicable ele
     static $aND = array('align'=>array('caption'=>1, 'div'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1), 'bgcolor'=>array('table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1), 'border'=>array('object'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellspacing'=>array('table'=>1), 'clear'=>array('br'=>1), 'compact'=>array('dl'=>1, 'ol'=>1, 'ul'=>1), 'height'=>array('td'=>1, 'th'=>1), 'hspace'=>array('img'=>1, 'object'=>1), 'language'=>array('script'=>1), 'name'=>array('a'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'map'=>1), 'noshade'=>array('hr'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'size'=>array('hr'=>1), 'vspace'=>array('img'=>1, 'object'=>1), 'width'=>array('hr'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1));
     static $eAD = array('a'=>1, 'br'=>1, 'caption'=>1, 'div'=>1, 'dl'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'map'=>1, 'object'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'script'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1, 'ul'=>1);
     $depTr = isset($eAD[$e]) ? 1 : 0;
    }
    
    // attr name-vals
    if(strpos($a, "\x01") !== false){$a = preg_replace('`\x01[^\x01]*\x01`', '', $a);} // No comment/CDATA sec
    $mode = 0; $a = trim($a, ' /'); $aA = array();
    while(strlen($a)){
     $w = 0;
     switch($mode){
      case 0: // Name
       if(preg_match('`^[a-zA-Z][^\s=/]+`', $a, $m)){
        $nm = strtolower($m[0]);
        $w = $mode = 1; $a = ltrim(substr_replace($a, '', 0, strlen($m[0])));
       }
      break; case 1:
       if($a[0] == '='){ // =
        $w = 1; $mode = 2; $a = ltrim($a, '= ');
       }else{ // No val
        $w = 1; $mode = 0; $a = ltrim($a);
        $aA[$nm] = '';
       }
      break; case 2: // Val
       if(preg_match('`^((?:"[^"]*")|(?:\'[^\']*\')|(?:\s*[^\s"\']+))(.*)`', $a, $m)){
        $a = ltrim($m[2]); $m = $m[1]; $w = 1; $mode = 0;
        $aA[$nm] = trim(str_replace('<', '&lt;', ($m[0] == '"' or $m[0] == '\'') ? substr($m, 1, -1) : $m));
       }
      break;
     }
     if($w == 0){ // Parse errs, deal with space, " & '
      $a = preg_replace('`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $a);
      $mode = 0;
     }
    }
    if($mode == 1){$aA[$nm] = '';}
    
    // clean attrs
    global $S;
    $rl = isset($S[$e]) ? $S[$e] : array();
    $a = array(); $nfr = 0; $d = $C['deny_attribute'];
    foreach($aA as $k=>$v){
     if(((isset($d['*']) ? isset($d[$k]) : !isset($d[$k])) && (isset($aN[$k][$e]) or isset($aNU[$k]) or (isset($aNO[$k]) && !isset($d['on*'])) or (isset($aNA[$k]) && !isset($d['aria*'])) or (!isset($d['data*']) && preg_match('`data-((?!xml)[^:]+$)`', $k))) && !isset($rl['n'][$k]) && !isset($rl['n']['*'])) or isset($rl[$k])){
      if(isset($aNE[$k])){$v = $k;}
      elseif(!empty($lcase) && (($e != 'button' or $e != 'input') or $k == 'type')){ // Rather loose but ?not cause issues
       $v = (isset($aNL[($v2 = strtolower($v))])) ? $v2 : $v;
      }
      if($k == 'style' && !$C['style_pass']){
       if(false !== strpos($v, '&#')){
        static $sC = array('&#x20;'=>' ', '&#32;'=>' ', '&#x45;'=>'e', '&#69;'=>'e', '&#x65;'=>'e', '&#101;'=>'e', '&#x58;'=>'x', '&#88;'=>'x', '&#x78;'=>'x', '&#120;'=>'x', '&#x50;'=>'p', '&#80;'=>'p', '&#x70;'=>'p', '&#112;'=>'p', '&#x53;'=>'s', '&#83;'=>'s', '&#x73;'=>'s', '&#115;'=>'s', '&#x49;'=>'i', '&#73;'=>'i', '&#x69;'=>'i', '&#105;'=>'i', '&#x4f;'=>'o', '&#79;'=>'o', '&#x6f;'=>'o', '&#111;'=>'o', '&#x4e;'=>'n', '&#78;'=>'n', '&#x6e;'=>'n', '&#110;'=>'n', '&#x55;'=>'u', '&#85;'=>'u', '&#x75;'=>'u', '&#117;'=>'u', '&#x52;'=>'r', '&#82;'=>'r', '&#x72;'=>'r', '&#114;'=>'r', '&#x4c;'=>'l', '&#76;'=>'l', '&#x6c;'=>'l', '&#108;'=>'l', '&#x28;'=>'(', '&#40;'=>'(', '&#x29;'=>')', '&#41;'=>')', '&#x20;'=>':', '&#32;'=>':', '&#x22;'=>'"', '&#34;'=>'"', '&#x27;'=>"'", '&#39;'=>"'", '&#x2f;'=>'/', '&#47;'=>'/', '&#x2a;'=>'*', '&#42;'=>'*', '&#x5c;'=>'\\', '&#92;'=>'\\');
        $v = strtr($v, $sC);
       }
       $v = preg_replace_callback('`(url(?:\()(?: )*(?:\'|"|&(?:quot|apos);)?)(.+?)((?:\'|"|&(?:quot|apos);)?(?: )*(?:\)))`iS', 'hl_prot', $v);
       $v = !$C['css_expression'] ? preg_replace('`expression`i', ' ', preg_replace('`\\\\\S|(/|(%2f))(\*|(%2a))`i', ' ', $v)) : $v;
      }elseif(isset($aNP[$k]) or isset($aNO[$k])){
       $v = str_replace("­", ' ', (strpos($v, '&') !== false ? str_replace(array('&#xad;', '&#173;', '&shy;'), ' ', $v) : $v)); # double-quoted char: soft-hyphen; appears here as "­" or hyphen or something else depending on viewing software
       if($k == 'srcset'){
        $v2 = '';
        foreach(explode(',', $v) as $k1=>$v1){
         $v1 = explode(' ', ltrim($v1), 2);
         $k1 = isset($v1[1]) ? trim($v1[1]) : '';
         $v1 = trim($v1[0]);
         if(isset($v1[0])){$v2 .= hl_prot($v1, $k). (empty($k1) ? '' : ' '. $k1). ', ';}
        }
        $v = trim($v2, ', ');
       }
       if($k == 'itemtype'){
        $v2 = '';
        foreach(explode(' ', $v) as $v1){
         if(isset($v1[0])){$v2 .= hl_prot($v1, $k). ' ';}
        }
        $v = trim($v2, ' ');
       }
       else{$v = hl_prot($v, $k);}
       if($k == 'href'){ // X-spam
        if($C['anti_mail_spam'] && strpos($v, 'mailto:') === 0){
         $v = str_replace('@', htmlspecialchars($C['anti_mail_spam']), $v);
        }elseif($C['anti_link_spam']){
         $r1 = $C['anti_link_spam'][1];
         if(!empty($r1) && preg_match($r1, $v)){continue;}
         $r0 = $C['anti_link_spam'][0];
         if(!empty($r0) && preg_match($r0, $v)){
          if(isset($a['rel'])){
           if(!preg_match('`\bnofollow\b`i', $a['rel'])){$a['rel'] .= ' nofollow';}
          }elseif(isset($aA['rel'])){
           if(!preg_match('`\bnofollow\b`i', $aA['rel'])){$nfr = 1;}
          }else{$a['rel'] = 'nofollow';}
         }
        }
       }
      }
      if(isset($rl[$k]) && is_array($rl[$k]) && ($v = hl_attrval($k, $v, $rl[$k])) === 0){continue;}
      $a[$k] = str_replace('"', '&quot;', $v);
     }
    }
    if($nfr){$a['rel'] = isset($a['rel']) ? $a['rel']. ' nofollow' : 'nofollow';}
    
    // rqd attr
    static $eAR = array('area'=>array('alt'=>'area'), 'bdo'=>array('dir'=>'ltr'), 'command'=>array('label'=>''), 'form'=>array('action'=>''), 'img'=>array('src'=>'', 'alt'=>'image'), 'map'=>array('name'=>''), 'optgroup'=>array('label'=>''), 'param'=>array('name'=>''), 'style'=>array('scoped'=>''), 'textarea'=>array('rows'=>'10', 'cols'=>'50'));
    if(isset($eAR[$e])){
     foreach($eAR[$e] as $k=>$v){
      if(!isset($a[$k])){$a[$k] = isset($v[0]) ? $v : $k;}
     }
    }
    
    // depr attr
    if($depTr){
     $c = array();
     foreach($a as $k=>$v){
      if($k == 'style' or !isset($aND[$k][$e])){continue;}
      $v = str_replace(array('\\', ':', ';', '&#'), '', $v);
      if($k == 'align'){
       unset($a['align']);
       if($e == 'img' && ($v == 'left' or $v == 'right')){$c[] = 'float: '. $v;}
       elseif(($e == 'div' or $e == 'table') && $v == 'center'){$c[] = 'margin: auto';}
       else{$c[] = 'text-align: '. $v;}
      }elseif($k == 'bgcolor'){
       unset($a['bgcolor']);
       $c[] = 'background-color: '. $v;
      }elseif($k == 'border'){
       unset($a['border']); $c[] = "border: {$v}px";
      }elseif($k == 'bordercolor'){
       unset($a['bordercolor']); $c[] = 'border-color: '. $v;
      }elseif($k == 'cellspacing'){
       unset($a['cellspacing']); $c[] = "border-spacing: {$v}px";
      }elseif($k == 'clear'){
       unset($a['clear']); $c[] = 'clear: '. ($v != 'all' ? $v : 'both');
      }elseif($k == 'compact'){
       unset($a['compact']); $c[] = 'font-size: 85%';
      }elseif($k == 'height' or $k == 'width'){
       unset($a[$k]); $c[] = $k. ': '. ($v[0] != '*' ? $v. (ctype_digit($v) ? 'px' : '') : 'auto');
      }elseif($k == 'hspace'){
       unset($a['hspace']); $c[] = "margin-left: {$v}px; margin-right: {$v}px";
      }elseif($k == 'language' && !isset($a['type'])){
       unset($a['language']);
       $a['type'] = 'text/'. strtolower($v);
      }elseif($k == 'name'){
       if($C['no_deprecated_attr'] == 2 or ($e != 'a' && $e != 'map')){unset($a['name']);}
       if(!isset($a['id']) && !preg_match('`\W`', $v)){$a['id'] = $v;}
      }elseif($k == 'noshade'){
       unset($a['noshade']); $c[] = 'border-style: none; border: 0; background-color: gray; color: gray';
      }elseif($k == 'nowrap'){
       unset($a['nowrap']); $c[] = 'white-space: nowrap';
      }elseif($k == 'size'){
       unset($a['size']); $c[] = 'size: '. $v. 'px';
      }elseif($k == 'vspace'){
       unset($a['vspace']); $c[] = "margin-top: {$v}px; margin-bottom: {$v}px";
      }
     }
     if(count($c)){
      $c = implode('; ', $c);
      $a['style'] = isset($a['style']) ? rtrim($a['style'], ' ;'). '; '. $c. ';': $c. ';';
     }
    }
    // unique ID
    if($C['unique_ids'] && isset($a['id'])){
     if(preg_match('`\s`', ($id = $a['id'])) or (isset($GLOBALS['hl_Ids'][$id]) && $C['unique_ids'] == 1)){unset($a['id']);
     }else{
      while(isset($GLOBALS['hl_Ids'][$id])){$id = $C['unique_ids']. $id;}
      $GLOBALS['hl_Ids'][($a['id'] = $id)] = 1;
     }
    }
    // xml:lang
    if($C['xml:lang'] && isset($a['lang'])){
     $a['xml:lang'] = isset($a['xml:lang']) ? $a['xml:lang'] : $a['lang'];
     if($C['xml:lang'] == 2){unset($a['lang']);}
    }
    // for transformed tag
    if(!empty($trt)){
     $a['style'] = isset($a['style']) ? rtrim($a['style'], ' ;'). '; '. $trt : $trt;
    }
    // return with empty ele /
    if(empty($C['hook_tag'])){
     $aA = '';
     foreach($a as $k=>$v){$aA .= " {$k}=\"{$v}\"";}
     return "<{$e}{$aA}". (isset($eE[$e]) ? ' /' : ''). '>';
    }
    else{return $C['hook_tag']($e, $a);}
    }
    
    function hl_tag2(&$e, &$a, $t=1){
    // transform tag
    if($e == 'big'){$e = 'span'; return 'font-size: larger;';}
    if($e == 's' or $e == 'strike'){$e = 'span'; return 'text-decoration: line-through;';}
    if($e == 'tt'){$e = 'code'; return '';}
    if($e == 'center'){$e = 'div'; return 'text-align: center;';}
    static $fs = array('0'=>'xx-small', '1'=>'xx-small', '2'=>'small', '3'=>'medium', '4'=>'large', '5'=>'x-large', '6'=>'xx-large', '7'=>'300%', '-1'=>'smaller', '-2'=>'60%', '+1'=>'larger', '+2'=>'150%', '+3'=>'200%', '+4'=>'300%');
    if($e == 'font'){
     $a2 = '';
     while(preg_match('`(^|\s)(color|size)\s*=\s*(\'|")?(.+?)(\\3|\s|$)`i', $a, $m)){
      $a = str_replace($m[0], ' ', $a);
      $a2 .= strtolower($m[2]) == 'color' ? (' color: '. str_replace(array('"', ';', ':'), '\'', trim($m[4])). ';') : (isset($fs[($m = trim($m[4]))]) ? (' font-size: '. $fs[$m]. ';') : '');
     }
     while(preg_match('`(^|\s)face\s*=\s*(\'|")?([^=]+?)\\2`i', $a, $m) or preg_match('`(^|\s)face\s*=(\s*)(\S+)`i', $a, $m)){
      $a = str_replace($m[0], ' ', $a);
      $a2 .= ' font-family: '. str_replace(array('"', ';', ':'), '\'', trim($m[3])). ';';
     }
     $e = 'span'; return ltrim(str_replace('<', '', $a2));
    }
    if($e == 'acronym'){$e = 'abbr'; return '';}
    if($e == 'dir'){$e = 'ul'; return '';}
    if($t == 2){$e = 0; return 0;}
    return '';
    }
    
    function hl_tidy($t, $w, $p){
    // tidy/compact HTM
    if(strpos(' pre,script,textarea', "$p,")){return $t;}
    if(!function_exists('hl_aux2')){function hl_aux2($m){
     return $m[1]. str_replace(array("<", ">", "\n", "\r", "\t", ' '), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), $m[3]). $m[4];
    }}
    $t = preg_replace(array('`(<\w[^>]*(?<!/)>)\s+`', '`\s+`', '`(<\w[^>]*(?<!/)>) `'), array(' $1', ' ', '$1'), preg_replace_callback(array('`(<(!\[CDATA\[))(.+?)(\]\]>)`sm', '`(<(!--))(.+?)(-->)`sm', '`(<(pre|script|textarea)[^>]*?>)(.+?)(</\2>)`sm'), 'hl_aux2', $t));
    if(($w = strtolower($w)) == -1){
     return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
    }
    $s = strpos(" $w", 't') ? "\t" : ' ';
    $s = preg_match('`\d`', $w, $m) ? str_repeat($s, $m[0]) : str_repeat($s, ($s == "\t" ? 1 : 2));
    $N = preg_match('`[ts]([1-9])`', $w, $m) ? $m[1] : 0;
    $a = array('br'=>1);
    $b = array('button'=>1, 'command'=>1, 'input'=>1, 'option'=>1, 'param'=>1, 'track'=>1);
    $c = array('audio'=>1, 'canvas'=>1, 'caption'=>1, 'dd'=>1, 'dt'=>1, 'figcaption'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'isindex'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'object'=>1, 'p'=>1, 'pre'=>1, 'style'=>1, 'summary'=>1, 'td'=>1, 'textarea'=>1, 'th'=>1, 'video'=>1);
    $d = array('address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'colgroup'=>1, 'datalist'=>1, 'details'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'iframe'=>1, 'main'=>1, 'map'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'optgroup'=>1, 'rbc'=>1, 'rtc'=>1, 'ruby'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'table'=>1, 'tbody'=>1, 'tfoot'=>1, 'thead'=>1, 'tr'=>1, 'ul'=>1);
    $T = explode('<', $t);
    $X = 1;
    while($X){
     $n = $N;
     $t = $T;
     ob_start();
     if(isset($d[$p])){echo str_repeat($s, ++$n);}
     echo ltrim(array_shift($t));
     for($i=-1, $j=count($t); ++$i<$j;){
      $r = ''; list($e, $r) = explode('>', $t[$i]);
      $x = $e[0] == '/' ? 0 : (substr($e, -1) == '/' ? 1 : ($e[0] != '!' ? 2 : -1));
      $y = !$x ? ltrim($e, '/') : ($x > 0 ? substr($e, 0, strcspn($e, ' ')) : 0);
      $e = "<$e>"; 
      if(isset($d[$y])){
       if(!$x){
        if($n){echo "\n", str_repeat($s, --$n), "$e\n", str_repeat($s, $n);}
        else{++$N; ob_end_clean(); continue 2;}
       }
       else{echo "\n", str_repeat($s, $n), "$e\n", str_repeat($s, ($x != 1 ? ++$n : $n));}
       echo $r; continue;
      }
      $f = "\n". str_repeat($s, $n);
      if(isset($c[$y])){
       if(!$x){echo $e, $f, $r;}
       else{echo $f, $e, $r;}
      }elseif(isset($b[$y])){echo $f, $e, $r;
      }elseif(isset($a[$y])){echo $e, $f, $r;
      }elseif(!$y){echo $f, $e, $f, $r;
      }else{echo $e, $r;}
     }
     $X = 0;
    }
    $t = str_replace(array("\n ", " \n"), "\n", preg_replace('`[\n]\s*?[\n]+`', "\n", ob_get_contents()));
    ob_end_clean();
    if(($l = strpos(" $w", 'r') ? (strpos(" $w", 'n') ? "\r\n" : "\r") : 0)){
     $t = str_replace("\n", $l, $t);
    }
    return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
    }
    
function hl_version(){
// version
    return '1.2.5';
}




function remove_emoji($text){ //過濾ios符號
 return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u', '', $text);
}


?>