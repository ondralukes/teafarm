<?php
//Add command on user request
//
    if(isset($_GET["cmd"])){
    $path = "cmd.data";
   $handle = fopen($path,"r");
   $rawcmds = fread($handle, filesize($path))   ;
   fclose($handle);
   $cmds = json_decode($rawcmds);
   $cmd=new cmd();
   $cmd->value=$_GET["cmd"];
   $cmd->cached=false;
   $cmd->time=time()+$_GET["time"];
   $cmd->repeat = $_GET["repeat"];
   for($i = 0;true;$i++){
    $ok = true;
    foreach ($cmds as $value) {
      if($value->id == $i){
       $ok= false;
      }
    }
    if($ok) {
      $cmd->id = $i;
      break;
    }
   }
   $cmds[] = $cmd;
    //sort by time
   usort($cmds,cmp);
    $handle = fopen($path,"w");
   fwrite($handle, json_encode($cmds));
   fclose($handle);
  }
  function cmp($a,$b){
    return strcmp($a->time,$b->time);
  }
class cmd
{
   public $value ="";
   public $time = 0;
   public $id = 0;
   public $cached = false;
   public $repeat = 0;
}

?>
