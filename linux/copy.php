#! /usr/bin/php
<?php
  //Dummy Copy Script 
  copy($argv[1],$argv[2]);
  //file_put_contents("debuf","../".$argv[1]."|".$argv[2]);
  unlink($argv[1]);
  echo "Done";
?>
