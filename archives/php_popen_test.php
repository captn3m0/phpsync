<?php
include ('backgroundWorker.php');
$bw=new backgroundWorker("./wait_2.php");
$bw->killString='Done';
$bw->callback=function ($id,$status) {
	global $bw;
	echo "Worker $id says $status\n";
};
$bw->start("STANDUP");
$bw->start("SITDOWN");
$bw->start("SITDOWN");
$bw->start("SITDOWN");
$bw->start("SITDOWN");
usleep(4*1000000);//1 seconds
$bw->recheck();
