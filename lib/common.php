<?php
function loadPlaylist($playlist_file)
{
	$ext=substr($playlist_file,strrpos($playlist_file,'.'));
	switch($ext){
		case '.m3u':
			$file=file($playlist_file);
			foreach($file as $k=>$song)
			{
				$file[$k]=trim($song);
				if(ord($song)==ord('#'))
					unset($file[$k]);
				if(empty($file[$k]))
					unset($file[$k]);
			}
			return $file;
			break;
		case '.zpl':
		case '.wpl':
			$file=simplexml_load_file ($playlist_file);
			$file=$file->body->seq;
			$songs=array();
			foreach($file->children() as $song)
				array_push($songs,$song['src']);
			return $songs;
			break;
		case '.xspf':
			$file=simplexml_load_file ($playlist_file);
			if (!$file) {
				echo "Failed loading XML\n";
				foreach(libxml_get_errors() as $error) {
					echo "\t", $error->message;
				}
			}
			$songs=array();
			foreach($file->trackList->track as $track)
				array_push($songs,substr(urldecode($track->location),7));
			//xspf contains file:// encoded urls
			return $songs;
			break;
		case '.plc':
			echo "plc playlists are still under development.";
			//Big Endian encoding support in php!
			die;
			/*
			$file=file($playlist_file,FILE_TEXT);
			print_r($file);exit;
			foreach($file as &$song){
				echo $song."\n";
				$e=explode("|",$song);
				print_r($e);
				$song=$e[1];
				echo $song."\n";
			}
			return $file;
			break;*/
	}
}
function file_name_able($string)
{
	$remove=array('\\','/',':','*','?','"','<','>','|');
	$a=str_replace($remove," ",$string);
	if(is_array($a))
		return str_replace("  ","",$a[0]);
	else
		return str_replace("  ","",$a);
}
function getArtist($tags)
{
	//Tries artist->band->composer
	if(isset($tags['id3v2']['artist']))
		return $tags['id3v2']['artist'];
	if(isset($tags['id3v1']['artist']))
		return $tags['id3v1']['artist'];
	if(isset($tags['id3v2']['band']))
		return $tags['id3v2']['band'];
	if(isset($tags['id3v1']['band']))
		return $tags['id3v1']['band'];
	if(isset($tags['id3v2']['composer']))
		return $tags['id3v2']['composer'];
	if(isset($tags['id3v1']['composer']))
		return $tags['id3v1']['composer'];
	return array("Unknown Artist");
}
function getGenre($tags)
{
	if(isset($tags['id3v2']['genre']))
		return $tags['id3v2']['genre'];
	if(isset($tags['id3v1']['genre']))
		return $tags['id3v1']['genre'];
}
function getYear($tags)
{
	if(isset($tags['id3v2']['year']))
		return $tags['id3v2']['year'];
	if(isset($tags['id3v1']['year']))
		return $tags['id3v1']['year'];
}
function getAlbum($tags)
{
	if(isset($tags['id3v2']['album']))
		return $tags['id3v2']['album'];
	if(isset($tags['id3v1']['album']))
		return $tags['id3v1']['album'];
	return array('');
}
function getTitle($tags)
{
	if(isset($tags['id3v2']['title']))
		return $tags['id3v2']['title'];
	if(isset($tags['id3v1']['title']))
		return $tags['id3v1']['title'];
	return array('');//make it blank
}
function getTrack($tags)
{
	if(isset($tags['id3v2']['track']))
		return $tags['id3v2']['track'];
	if(isset($tags['id3v1']['track']))
		return $tags['id3v1']['track'];
	if(isset($tags['id3v2']['tracknumber']))
		return $tags['id3v2']['tracknumber'];
	if(isset($tags['id3v1']['tracknumber']))
		return $tags['id3v1']['tracknumber'];
}
 function img_from_mime($mime,$fn){
	 switch($mime){
		 case 'image/png':
			 $i= imagecreatefrompng($fn);
			 break;
		 case 'image/jpeg':
		 case 'image/jpg':
			 $i= imagecreatefromjpeg($fn);
			 break;
		 case 'image/gif':
			 $i = imagecreatefromgif($fn); 
			 break;
		 default:
			 die("Unknown Image Format");
	 }
	 return $i;
 }
// function get_image($id3,$image_name,$filename)
// {
	// global $settings,$rootx;
	// //first check if thumbnail is already present
	// if(file_exists($rootx."\\pics\\".$image_name.".jpg"))
		// return $rootx."\\pics\\".$image_name.".jpg";
	// //uses the 1st found img
	// //Should maybe use the largest one?
	// if(isset($id3['PIC']))
		// $idimg=$id3['PIC'][0];
	// elseif(isset($id3['APIC']))
		// $idimg=$id3['APIC'][0];
	// else
		// $idimg=false;
	// $img=false;
	// if($idimg):
		// file_put_contents("temp",$idimg['data']);
		// $img=img_from_mime($idimg['image_mime'],"temp");
	// else:
		// $dir=realpath(dirname($filename));$img=false;
		// //Search for folder.jpg
		// if(file_exists($dir."\\folder.jpg"))
			// $img= imagecreatefromjpeg($dir."\\folder.jpg");
		// elseif(file_exists($dir."\\folder.png"))
			// $img=imagecreatefrompng($dir."\\folder.png");
		// elseif(file_exists($dir."\\folder.jpeg"))
			// $img=imagecreatefromjpeg($dir."\\folder.jpeg");
	// endif;
	// if($img){
		// $image_width=$settings['image_width'];
		// $image_height=floor(imagesy($img)*$image_width/imagesx($img));
		// $im=imagecreatetruecolor($image_width,$image_height);
		// imagecopyresized($im, $img, 0, 0, 0, 0, $image_width, $image_height, imagesx($img), imagesy($img));
		// imagejpeg($im,$rootx."\\pics\\"."$image_name.jpg");
		// @unlink("temp");
		// return "pics\\$image_name.jpg";	
	// }
	// else{
		// return "pics\\"."unknown.jpg";
	// }
// }
function loadsettings()
{
	global $argv;
	$settings=parse_ini_file("settings.ini");
	for($i=1;$i<count($argv);$i++){
		switch($argv[$i]){
			case '-p':
				$settings['playlist']=$argv[++$i];
				break;
			case '-d':
				$settings['drive']=$argv[++$i];
				break;
			case '-c':
				$settings['conv']=$settings[$argv[++$i]];//abr cbr v
				$settings['conv']=str_replace("%B%",$argv[++$i],$settings['conv']);
				if($settings['conv']=='off')
					$settings['conv']=false;				
				break;
			default:
				echo "Unknown Argument String passed, skipping\n";
		}
	}
	return $settings;
}

function ext_from_mime($mime){
	switch($mime){
		case 'image/png':
			return '.png';
			break;
		case 'image/jpeg':
		case 'image/jpg':
			return '.jpg';
			break;
		case 'image/gif':
			return '.gif';
			break;
		default:
			die("Unknown Image Format");
	}
}
