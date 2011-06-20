<?php
//error_reporting(E_NONE);
require_once "audioinfo.php";
$au = new AudioInfo;
$rootx=dirname(__FILE__);
$settings=loadsettings();
$playlist_root=dirname($settings['playlist']);
chdir($playlist_root);
$playlist=load($settings['playlist']);
foreach($playlist as $files){
	echo $files;
	$filename=trim($files);
	$ThisFileInfo = $au->Info($filename);
	$artist=file_name_able(getArtist($ThisFileInfo['tags']));
	$album=file_name_able(getAlbum($ThisFileInfo['tags']));
	$title=file_name_able(getTitle($ThisFileInfo['tags']));
	if(!$title)
		$title=substr(basename($filename),0,strrpos(basename($filename),'.'));
	$img=get_image($au->info['id3v2'],basename($files));
	if($settings['conv']):
	$cmd=<<<EOT
$rootx\\lame.exe {$settings['conv']} -S "$filename" "$rootx\\temp-ac43.mp3"
EOT;
	echo $cmd;
	shell_exec($cmd);
	endif;
	
	
}
function load($playlist_file)
{
	$ext=substr($playlist_file,strpos($playlist_file,'.'));
	switch($ext){
		case '.m3u':
			$file=file($playlist_file);
			foreach($file as &$song){
				if(ord($song)==35||empty($song))
					unset($song);
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
	return "Unknown Artist";
}
function getGenre($tags)
{
	if(isset($tags['id3v2']['genre']))
		return $tags['id3v2']['genre'];
	if(isset($tags['id3v1']['genre']))
		return $tags['id3v1']['genre'];
}
function getAlbum($tags)
{
	if(isset($tags['id3v2']['album']))
		return $tags['id3v2']['album'];
	if(isset($tags['id3v1']['album']))
		return $tags['id3v1']['album'];
}
function getTitle($tags)
{
	if(isset($tags['id3v2']['title']))
		return $tags['id3v2']['title'];
	if(isset($tags['id3v1']['title']))
		return $tags['id3v1']['title'];
}
function getYear($tags)
{
	if(isset($tags['id3v2']['year']))
		return $tags['id3v2']['year'];
	if(isset($tags['id3v1']['year']))
		return $tags['id3v1']['year'];
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
function get_image($id3,$image_name)
{
	global $settings,$rootx;
	//uses the 1st found img
	//Should maybe use the largest one?
	if(isset($id3['PIC']))
		$idimg=$id3['PIC'][0];
	elseif(isset($id3['APIC']))
		$idimg=$id3['APIC'][0];
	else
		$idimg=false;
	if($idimg):
		file_put_contents("temp",$idimg['data']);
		$img=img_from_mime($idimg['image_mime'],"temp");
		$image_width=$settings['image_width'];
		$image_height=floor(imagesy($img)*$image_width/imagesx($img));
		$im=imagecreatetruecolor($image_width,$image_height);
		imagecopyresized($im, $img, 0, 0, 0, 0, $image_width, $image_height, imagesx($img), imagesy($img));
		imagejpeg($im,$rootx."\\pics\\"."$image_name.jpg");
		unlink("temp");
		return "pics\\$image_name.jpg";		
	else:
		echo "no img found";
	endif;
}
function loadsettings()
{
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
				$settings['conv']=$argv[++$i];
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