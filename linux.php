#! /usr/bin/php	
<?php
//error_reporting(E_ERROR);
require_once "./lib/audioinfo.php";
require_once "./lib/common.php";
require_once "./linux/image.php";
require_once "./lib/backgroundWorker.php";
$au = new AudioInfo;
$bw=new backgroundWorker("./linux/copy.php");
$listOfIds=array();
$bw->callback=function ($id,$status) {
	global $listOfIds;
	if($status=='Done')
		echo $listOfIds[$id]." copied\n";
	else
		echo $listOfIds[$id]." not copied\n";
};
$bw->killString='Done';
$debug=fopen("debug.txt",'a');
fwrite($debug,"Starting sync at ".time()."\n");
$rootx=dirname(__FILE__);
$settings=loadsettings();
if(!file_exists($settings['playlist']))
  die("Playlist not found");
if(!is_dir($settings['drive']))
  die("Drive not found");
$playlist_root=dirname($settings['playlist']);
$playlist=loadPlaylist($settings['playlist']);
foreach($playlist as $files){
	//$filename=realpath($playlist_root."/".trim($files));
	$filename = $files;
	if(substr($filename,strrpos($filename,'.'))!=='.mp3'){
		continue;
	}
	if($filename):
		$title_f='';
		//Read file information from id3tags
		$ThisFileInfo = $au->Info($filename);
		list($artist)=(getArtist($ThisFileInfo['tags']));
		$artist_f=file_name_able($artist);
		list($album)=getAlbum($ThisFileInfo['tags']);
		$album_f=file_name_able($album);
		list($title)=getTitle($ThisFileInfo['tags']);
		$title_f=file_name_able($title);
		list($track)=getTrack($ThisFileInfo['tags']);
		$track_f=file_name_able($track);
		list($year)=getYear($ThisFileInfo['tags']);
		list($genre)=(getGenre($ThisFileInfo['tags']));
		//print_r($au->info['id3v2']);
		//echo "$artist-$track-$title-$album-$year-$genre";
		//Reading complete		
		if(empty($title_f))
			$title_f=substr(basename($filename),0,strrpos(basename($filename),'.'));
		if(file_exists($settings['drive'].'/Music/'.$artist_f.'-'.$title_f.'.mp3'))
			continue;
		//Now let's see to the image support
		$image_name=(!empty($album))?$album:basename($filename);
		$img=isset($au->info['id3v2'])?get_image($au->info['id3v2'],$image_name,$filename):$rootx."/pics/$image_name.jpg";
		echo basename($filename)." => ";
		if($settings['conv']):
			echo "Converting ";
			$tempFile=uniqid();
			$cmd='lame '.$settings['conv'].' --silent --ti "'.$img.'" --tt "'.$title.'" --ta "'.$artist.'" --tl "'.$album.'" --tn "'.$track.'" --tg "'.$genre.'"  --add-id3v2 "'.$filename.'" "'.$tempFile.'"';
			fwrite($debug,$cmd."\n");
			shell_exec($cmd);
			$workerId=$bw->start($tempFile.' "'.$settings['drive'].'/Music/'.$artist_f.'-'.$title_f.'.mp3"');
			$listOfIds[$workerId]=basename($filename);
		endif;
		echo "[DONE]\n";
		$bw->recheck();
	endif;
}
fclose($debug);
