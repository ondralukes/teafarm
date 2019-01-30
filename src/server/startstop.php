<?php require "../lcktincl.php";?>
<?php
  if(isset($_GET["state"])){
      $handle = fopen("started.info","w");
      if($_GET["state"] == "0"){
   fwrite($handle, "0");
   } else {
   fwrite($handle, "1");
   }
   fclose($handle);
  }
?>