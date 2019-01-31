<?php
//Delete command when done
//
if(!isset($_GET["apikey"])) exit;
if($_GET["apikey"] != "4aafee89afb5fe37a31895bbff116458") exit;
    $path = "cmd.data";
   $handle = fopen($path,"r");
   $rawcmds = fread($handle, filesize($path));
   fclose($handle);
   $cmds = json_decode($rawcmds);
   if(count($cmds)==0){
   exit;
   }
   echo($cmds[0]->repeat);
   if($cmds[0]->repeat==0){
   unset($cmds[0]);
  } else {
    $cmds[0]->cached = false;
    $cmds[0]->time = time()+$cmds[0]->repeat;
   }
    usort($cmds,cmp);
   $cmds = array_values($cmds);
    $handle = fopen($path,"w");
   fwrite($handle, json_encode($cmds));
   fclose($handle);
   
  function cmp($a,$b){
    return strcmp($a->time,$b->time);
  }
?>
