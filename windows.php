<?php
//error_reporting(E_NONE);
require_once "./lib/audioinfo.php";
$au = new AudioInfo;
$rootx=dirname(__FILE__);
$settings=loadsettings();
$playlist_root=dirname($settings['playlist']);
//So that we don't have to use absolute path to lame...!
@copy("lame.exe",$playlist_root."\\lame.exe");
chdir($playlist_root);
$playlist=load($settings['playlist']);
foreach($playlist as $files){	
	chdir($playlist_root);
	//echo $files."\n";
	$filename=realpath(trim($files));
	
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
	// echo "$artist-$track-$title-$album-$year-$genre";
	//Reading complete
	
	
	if(empty($title_f))
		$title_f=substr(basename($filename),0,strrpos(basename($filename),'.'));
	
	//Now let's see to the image support
	// $image_name=basename(trim($files));
	// $img=get_image($au->info['id3v2'],$image_name,$filename);
	
	echo basename($filename)." => ";
	if($settings['conv']):
		echo "Converting ";
		$cmd=<<<EOT
lame.exe {$settings['conv']} --silent --tt "$title" --ta "$artist" --tl "$album" --tn "$track" --tg "$genre"  --add-id3v2 "$filename" "temp-ac43.mp3"
EOT;
		//echo $cmd;
		shell_exec($cmd);
		echo "Copying ";
		copy("temp-ac43.mp3",$settings['drive']."\\Music\\$artist_f-$title_f.mp3");
		unlink("temp-ac43.mp3");
	endif;
	echo "[DONE]\n";
}

