<?php
//$escape[]="value";//跳脫增加
//$add["arts_uuid"]="uuid";//增加額外參數
//$where="";//where條件
//query_code("update","資料表名稱",$_post,$escape,[],"where arts_uuid='$uuid'");
//$query = query_code("insert","file_list",$tmp,$escape,$add);
function query_code($type="",$table,$val,$escape=[],$add=[],$where=""){ //mysql_產生器
	$query="";
	if(sizeof($val)>0){
		$que1=[];
		$qu_title=[];

		if($type=="update"){
		
			$query="update  $table set ";
		
			foreach($val as $t=>$rst){
			
				if(!in_array($t,$escape)){
					$que1[$t]=$t."='$rst'";
				}
			}
			if(sizeof($add)>0){
				foreach($add as $t=>$rst){
					$que1[$t]=$t."='$rst'";
				}
			}
			
			$query=$query.implode(",",array_values($que1));
			$query=$query." ".$where.";";
		}else{
			$query="insert into $table ";
		
				foreach($val as $t=>$rst){
					if(!in_array($t,$escape)){
						$que1[$t]="'$rst'";
					}
				}
				if(sizeof($add)>0){
					foreach($add as $j=>$ast){
						
						if(preg_match("/_uuid/i",$j)){//uuid特別處理
							$que1[$j]="$ast";
						}else{
							$que1[$j]="'$ast'";
						}

					}
				}

				$up="(".implode(",",array_keys($que1)).")";
				$down="(".implode(",",array_values($que1)).")";
		
			if($where){
				$query= $query.$up." select ".$down." from $table ".$where." ;";
			}else $query = $query.$up." values ".$down.";";
			
		}
	}else return [];
	
	return $query;
}	
function callBack($state,$message){ //call_back回傳
	/*callBack 代碼*\
		0=>失敗,
		1=>成功,
		
	\* */
	$callback = array("state" => $state,"message" => $message);
	return json_encode($callback, JSON_UNESCAPED_UNICODE); 
}
/*
$search_key=["d"=>"$keyword","b"=>["$start_date","$end_date"]];
$search_str=["INSTR(order_firm,'$keyword')>0","DATE(order_date) between '$start_date' and '$end_date'"];
$where_str =  mulit_search($search_key,$search_str);
if(!$where_str){
	$where_str = "1";	   
}
*/
function mulit_search($key,$search_str){ //複合式搜尋 $key為搜尋字,$search_str SQL語法
	$where_str=[];
	

	foreach($key as $k=>$rst){
		$i=array_search($k,array_keys($key)); 

			if(is_array($rst)){
				if(!empty($search_str[$i])){
					$where_str[]="(".$search_str[$i].")";
				}
			}else{

				if($rst!="" && !empty($search_str[$i])){
					$where_str[]="(".$search_str[$i].")";
				}
			}
	}
	
	$where_str2 = implode(" and ",$where_str);
	return $where_str2;
}

function clearStoredResults(){ //清除多重語法mysqli快取
    global $mysqli;

    do {
         if ($res = $mysqli->store_result()) {
           $res->free();
         }
        } while ($mysqli->more_results() && $mysqli->next_result());        

}

function pre($string){
	return "\n<br><pre>$string</br>\n";
}

function unique_array($array) { //陣列去重複
	$temp_array = array(); 
	foreach($array as $val) { 
			if (!in_array($val, $temp_array)) { 
					$temp_array[]=$val;
			
			 } 
	}
	 return $temp_array; 
} 

function validateDate($date, $format = 'Y-m-d H:i:s') //檢測日期
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function aes256_encrypt($data) {
    $key="rbXpOy21IMxPkJUKXbeTHcFGtmrhTnEhiCqlh1OTKAM=";
    $encryption_key = base64_decode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    $encrypted = str_replace(array('-','_'),array('+','/'),$encrypted);
    return base64_encode($encrypted.'::'.$iv);
}
function aes256_decrypt($data) {
    $key="rbXpOy21IMxPkJUKXbeTHcFGtmrhTnEhiCqlh1OTKAM=";
    // Remove the base64 encoding from our key
    $encryption_key = base64_decode($key);
    $data = str_replace(array('-','_'),array('+','/'),$data);
    
    // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

function my_assoc($mysqli,$query){
	$size=0;
	$rst=[];
	$result=mysqli_query($mysqli,$query);
	$count=mysqli_num_rows($result);
	if($count){
	   while($row=mysqli_fetch_assoc($result)){
		$rst[]=$row;
	   }
	   $size=sizeof($rst);
	}
	return array("array"=>$rst,"size"=>$size);
}

function convert_to_encoding($str, $old_encoding, $new_encoding)
{	
	if (function_exists('iconv'))
	{
		$str = @iconv($old_encoding, $new_encoding, $str);
	}
	else
	{
		return false;
	}

	return $str;
}
//gbk轉utf8
function foreach_to_utf8($string, $encoding = 'gbk')
{
	
	if (is_array($string) ) 
	{
		foreach($string as $key => $val) 
		{
			$string[$key] = foreach_to_utf8($val,$encoding);
		}
    } 
	else if( is_object($string) )
	{
		foreach($string as $key => $val) 
		{
			$string->$key = foreach_to_utf8($val,$encoding);
		}
	}
	else 
	{
		$string = convert_to_encoding($string, $encoding,'UTF-8');
    }
    return $string;
	
		
}
//utf8轉gbk
function foreach_to_gbk($string, $encoding = 'utf-8')
{
	if (is_array($string) ) 
	{
		foreach($string as $key => $val) 
		{
			$string[$key] = foreach_to_gbk($val,$encoding);
		}
    } 
	else if( is_object($string) )
	{
		foreach($string as $key => $val) 
		{
			$string->$key = foreach_to_gbk($val,$encoding);
		}
	}
	else
	{
		$string = convert_to_encoding($string, $encoding,'GBK//IGNORE');
    }
    return $string;
	
		
}
function is_jSON($string){
	return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

function json_Eshort($arr){
	return json_encode( $arr,JSON_UNESCAPED_UNICODE);
}
function json_Dshort($arr){
	return json_decode( $arr,true);
}

function NOW($format = ""){
	if($format == "") return date("Y-m-d H:i:s");
	else return date($format);
}
function changeDate($date,$format = 'Y-m-d H:i:s'){ //轉換日期
	//轉換日期前要先檢查是否為null
	if($date){
		if(!empty(strtotime($date))){
			return date($format,strtotime($date));
		}else return false;
	}else return false;
	
	
}
/*
$order_=[
    "member_contact","member_shopName","member_shopChapter","member_type","member_point","member_dealer","seller_name","member_contactPhone","member_pathType","member_identity","member_principal","member_phone","member_city","member_country","member_address","member_phone","member_QRcount","member_update","item_content"
]; //要顯示的順序
*/
function order_array($ori_array,$order_){ //轉換陣列中的排序

	// $ori_array 尚未排序的陣列
	// $order_ 要求的排序
	$new_array=[];
	$new_array["state"]="0";
	$new_array["array"]=[];
	foreach($ori_array as $a=>$b){
		 foreach($order_ as $__value){
			 if(array_key_exists($__value,$b)){
				 $new_array["state"]="1";//轉換成功
				 $new_array["array"][$a][$__value]=$b[$__value];
			 }else $new_array["array"][$a][$__value]="";
		 }
	 }
	 return  $new_array;
 }
 function alpha2num($a)  //英文轉數字(A=>0、B=>1、AA=>26...以此類推)
{
    $l = strlen($a);
    $n = 0;
    for($i = 0; $i < $l; $i++)
        $n = $n*26 + ord($a[$i]) - 0x40;
    return $n-1;
}
function num2alpha($n)  //數字轉英文(0=>A、1=>B、26=>AA...以此類推)
{
    for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
        $r = chr($n%26 + 0x41) . $r;
    return $r;
}
?>