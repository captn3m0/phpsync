<?php
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array("file", "./error-output.txt", "a") // stderr is a file to write to
);


$process = proc_open('./php_wait_script.php', $descriptorspec, $pipes);

if (is_resource($process)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    fwrite($pipes[0], "Stand");
    fwrite($pipes[0], "SitDn");
    echo fread($pipes[1],1000);
    
    echo fread($pipes[1],1000);
    
    echo fread($pipes[1],1000);
    echo fread($pipes[1],1000);
    fclose($pipes[0]);
    fclose($pipes[1]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    echo "command returned $return_value\n";
}
?>
