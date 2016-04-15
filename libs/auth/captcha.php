<?php

session_start();

$alpha ="0123456789";
$secret = ""; 
for($i=0;$i<5;$i++) {
    $secret.= $alpha[rand(0,strlen($alpha)-1)]; 
}
$_SESSION['secret']=$secret;

$im = imagecreate(80, 31);
imageColorAllocate($im,255,255,255);
$textcolor = imagecolorallocate($im, 0,0,0);
imagestring($im,5,10,10,$secret, $textcolor );
imageGif($im); 
header("Content-Type: image/gif");