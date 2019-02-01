<?php
//Data for AJAX in UI
//
    $handle = fopen("lastrequest.time","r");
   $lastreq = intval(fread($handle, filesize("lastrequest.time")));
   $delay = time()-$lastreq;
   fclose($handle);
   $path = "started.info";
   $handle = fopen($path,"r");
   if(fread($handle, filesize($path))=="1"){
   $state = True;
   } else {
   $state = False;
   }
   fclose($handle);
   $handle = fopen("inactivity.log","r");
   $iacts = json_decode(fread($handle, filesize("inactivity.log")));
   fclose($handle);
   $path = "cmd.data";
   $handle = fopen($path,"r");
   $rawcmds = fread($handle, filesize($path));
   fclose($handle);
   $cmds = json_decode($rawcmds);
   $obj = new stdClass();
   $obj->lastseen = $delay;
   $obj->cmds = $cmds;
   $obj->state = $state;
   $obj->iacts = $iacts;
   echo(json_encode($obj));
?>
