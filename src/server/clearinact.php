<?php
//Clean inactivity log
//
    $path = "inactivity.log";
    unlink($path);
   $handle = fopen($path,"w");
   fwrite($handle,"[]");
   fclose($handle);
?>
