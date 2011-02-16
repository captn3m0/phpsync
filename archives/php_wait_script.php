#!/usr/bin/php
<?php
  $fout=fopen("Debug.txt","a");
  $counter=1;
  while($command=fread(STDIN,100))
  {
    fwrite(STDOUT,"Started Task $counter at ".date(DATE_RSS)."\n");
    /**INSERT WORK PORTION BELOW*/
    {
		/*
		 * Sleeping Part to simulate work*/
		 if($counter==1)
			usleep(1*1000000);//1 seconds
		else
			usleep(0.5*1000000);//0.5 seconds		
		//fwrite($fout,$command."\n");//this is the work that the command is doing in bg
	}
	/**WORK PORTION ENDS HERE*/
    fwrite(STDOUT,"Completed Task $counter at ".date(DATE_RSS)." [$command]\n");
    //fwrite($fout,"Completed Task $counter at ".date(DATE_RSS)." [$command]\n");
    $counter++;
  }
  return 1;
?>
