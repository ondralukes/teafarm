<?php require "../lcktincl.php";?>
<?php
    $path = "inactivity.log";
    unlink($path);
   $handle = fopen($path,"w");
   fwrite($handle,"[]");
   fclose($handle);
?>