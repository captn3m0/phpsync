<?php
require_once "../lib/backgroundWorker.php";
$bw=new backgroundWorker("../linux/copy.php");
$bw->callback=function ($id,$status) {
	global $bw;
	echo "Worker $id says $status\n";
};
$bw->start("../archives/ffmpeg.rar /tmp/Filename");
$bw->start("bw-cp-test.php /tmp/Filename2");
usleep(5*1000000);//1 seconds
$bw->killString='Done';
$bw->recheck();
$bw->killAll();
?>
