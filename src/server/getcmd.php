<?php
//Send command to client
//
if(!isset($_GET["apikey"])) exit;
if($_GET["apikey"] != "4aafee89afb5fe37a31895bbff116458") exit;
$inputraw = file_get_contents('php://input');
$handle = fopen("clientdata","w");
fwrite($handle, file_get_contents('php://input'));
fclose($handle);
//register time
       $handle = fopen("lastrequest.time","r");
   $lrt = intval(fread($handle, filesize("lastrequest.time")));
   fclose($handle);
        $handle = fopen("lastrequest.time","w");
   fwrite($handle, "".time());
   fclose($handle);
   if(time()-$lrt > 300){
     $handle = fopen("inactivity.log","r");
   $iacts = json_decode(fread($handle, filesize("inactivity.log")));
   fclose($handle);
   $iact = new inactivity();
   $iact->start = $lrt;
   $iact->end = time();
   $iacts[] = $iact;
   $handle = fopen("inactivity.log","w");
   fwrite($handle, json_encode($iacts));
   fclose($handle);
   }
            //check if on
   $path = "started.info";
   $handle = fopen($path,"r");
   if(fread($handle, filesize($path))==0){
     fclose($handle);
     echo("STOPPED");
     exit;
   }
    fclose($handle);


$path = "cmd.data";
   $handle = fopen($path,"r");
   $rawcmds = fread($handle, filesize($path));
   fclose($handle);
   $cmds = json_decode($rawcmds);
   if(count($cmds)==0){
   exit;
   }
   $time = $cmds[0]->time-time();
   if($time < 0)$time="0";
   echo("<".$cmds[0]->value."><".$time."><".$cmds[0]->id.">");
   $cmds[0]->cached = true;
   //unset($cmds[0]);
   //$cmds = array_values($cmds);
    $handle = fopen($path,"w");
   fwrite($handle, json_encode($cmds));
   fclose($handle);
   class inactivity
{
   public $start = 0;
   public $end = 0;
}
?>
