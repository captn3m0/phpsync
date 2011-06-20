<?php
require_once "audioinfo.php";
$au = new AudioInfo;
$info= $au->Info("test\instrumental.mp3");
file_put_contents("a.jpg",$au->info['id3v2']['PIC'][0]['data']);
print_r($au->info['id3v2']['PIC']);
// $img=imagecreatefromjpeg("a.jpg");
// $im=imagecreatetruecolor(200,200);
// imagecopyresized($im, $img, 0, 0, 0, 0, 200, 200, imagesx($img), imagesy($img));
// imagejpeg($im,"c.jpg");
?>