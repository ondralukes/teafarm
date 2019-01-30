<?php require "../lcktincl.php";?>
<?php
   if(isset($_GET["id"])){
    $path = "cmd.data";
   $handle = fopen($path,"r");
   $rawcmds = fread($handle, filesize($path));
   fclose($handle);
   $cmds = json_decode($rawcmds);
   if(count($cmds)==0){
   exit;
   }
   unset($cmds[$_GET["id"]]);
   $cmds = array_values($cmds);
    $handle = fopen($path,"w");
   fwrite($handle, json_encode($cmds));
   fclose($handle);
   }
?>