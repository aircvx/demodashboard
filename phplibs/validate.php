<?php
if(!isset($_SESSION)){ session_start(); }
 $img_height = 30;  // 圖形高度
 $img_width = 100;   // 圖形寬度
 $mass = 10;        // 雜點的數量，數字愈大愈不容易辨識

 $num= "";              // rand後所存的地方
 $num_max = 4;      // 產生4個驗證碼
 
 for( $i=0; $i<$num_max; $i++ )
 {
     $num .= rand(0,9);
 }

 function gen_passw($len = 0)
 {
 $ranges = array
 (
 //1 => array(97, 122), // a-z (lowercase)
 //1 => array(65, 90), // A-Z (uppercase)
 1 => array(48, 57) // 0-9 (numeral)
 //假如你連符號也要進去 可以把符號的ASCII 
 //CODE範圍找出來
 );
 $File_Psw = "";
 for ($i=0; $i<$len; $i++)
 {
 $r = mt_rand(1,count($ranges));
 //$File_Psw .= chr(mt_rand($ranges[$r][0], $ranges[$r][1]));
 $File_Psw = random_str($len-1); //random_str這個在root_global.php內～～～
 }
 return $File_Psw;
 }
 //呼叫gen_passw函數 裡面指定你要的字串長度
 $num = gen_passw($num_max) ;
 

 $_SESSION["validate"] = $num;  // 將產生的驗證碼寫入到session
 
 // 創造圖片，定義圖形和文字顏色
 Header("Content-type: image/PNG");
 srand((double)microtime()*1000000);
 $im = imagecreate($img_width,$img_height);
 $black = ImageColorAllocate($im, 0,0,0);         // (0,0,0)文字為黑色
 $gray = ImageColorAllocate($im, 196,153,180); // (200,200,200)背景是灰色
 imagefill($im,0,0,$gray);

 // 隨機給予兩條虛線，起干擾作用
 $style = array($black, $black, $black, $black, $black, $gray, $gray, $gray, $gray, $gray);
 imagesetstyle($im, $style);
 $y1=rand(0,$img_height);
 $y2=rand(0,$img_height);
 $y3=rand(0,$img_height);
 $y4=rand(0,$img_height);
 //imageline($im, 0, $y1, $img_width, $y3, IMG_COLOR_STYLED);
 //imageline($im, 0, $y2, $img_width, $y4, IMG_COLOR_STYLED);

 // 在圖形產上黑點，起干擾作用;
 for( $i=0; $i<$mass; $i++ )
 {
 //imagesetpixel($im, rand(0,$img_width), rand(0,$img_height), $black);
 }

 // 將數字隨機顯示在圖形上,文字的位置都按一定波動範圍隨機生成
 $strx=rand(3,8);
 for( $i=0; $i<$num_max; $i++ )
 {
      $strpos=rand(1,8);
      imagestring($im,5,$strx,$strpos, substr($num,$i,1), $black);
      $strx+=rand(20,14);
 }
 ImagePNG($im);
 ImageDestroy($im);
 echo $num ;
 
function random_str($length){
    $password = '';
    $word = 'ABCDEFGHJKMNPQRTWXY346789';
    $len = strlen($word);

    for ($i = 0; $i <= $length; $i++) {
        $password .= $word[rand() % $len];
    }

    return $password;
}  
?>