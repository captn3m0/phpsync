<?php
$process_ptr=(popen("./php_wait_script.php &",'w'));
var_dump($process_ptr);
if($process_ptr)
{
  //echo fgets($process_ptr,100);//This keeps it waiting... But if i remove this, our process is killed instantly...
  //  pclose($process_ptr);
  while($cmd=fgets(STDIN,1000))
  {
    fwrite($process_ptr,$cmd);
  }
}
echo "quitting";
?>
