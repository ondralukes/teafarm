//Clean inactivity log
//
<?php require "../lcktincl.php";?>    //ignore this line
<?php
    $path = "inactivity.log";
    unlink($path);
   $handle = fopen($path,"w");
   fwrite($handle,"[]");
   fclose($handle);
?>
