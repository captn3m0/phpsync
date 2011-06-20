<?php
function get_image($id3,$albumName,$filename)
{
	global $settings,$rootx;
	//first check if thumbnail is already present
	if(file_exists($rootx."/pics/".$albumName.".jpg"))
		return $rootx."/pics/".$albumName.".jpg";
	// uses the 1st found img
	//Should maybe use the largest one?
	if(isset($id3['PIC']))
		$idimg=$id3['PIC'][0];
	elseif(isset($id3['APIC']))
		$idimg=$id3['APIC'][0];
	else
		$idimg=false;
	$img=false;
	if($idimg):
			$ext=ext_from_mime($idimg['image_mime']);
			file_put_contents("temp".$ext,$idimg['data']);
			$img="temp".$ext;
		else:
			$dir=realpath(dirname($filename));$img=false;
			//Search for folder.jpg
			if(file_exists($dir."/folder.jpg"))
				$img=($dir."/folder.jpg");
			elseif(file_exists($dir."/folder.png"))
				$img=($dir."/folder.png");
			elseif(file_exists($dir."/folder.jpeg"))
				$img=($dir."/folder.jpeg");
		endif;
	 /**
	  * Earlier implementation using phpgd
	  * if($img){
		 $image_width=$settings['image_width'];
		 $image_height=floor(imagesy($img)*$image_width/imagesx($img));
		 $im=imagecreatetruecolor($image_width,$image_height);
		 imagecopyresized($im, $img, 0, 0, 0, 0, $image_width, $image_height, imagesx($img), imagesy($img));
		 imagejpeg($im,$rootx."./pics/"."$albumName.jpg");
		 @unlink("temp");
		 return "pics/$albumName.jpg";	
	 }
	 else{
		return "pics/unknown.jpg";
	 }
	 */
	 //Now using image magick
	 if($img){
			`convert -sample 100x100 "$img" "./pics/$albumName.jpg"`;
		}
		else{
			copy("./pics/unknown.jpg","./pics/$albumName.jpg");
		}
	return $rootx."/pics/$albumName.jpg";
}
?>
