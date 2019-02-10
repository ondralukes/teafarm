<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title></title>
  </head>
  <body>
  <div id="state"></div>
  <span id="time"></span>
  <br>
  <span id="inactivity" style="background-color: yellow;"></span>
  <table id="cmds">
  <thead><th>Command</th><th>Scheduled time</th><th>Cached</th><th>Repeat</th><th>Cancel</th></thead>
  </table>
      <br><br>
      Enter Command:
      <br>
      ~Mn - Move to n steps
       <br>
      ~Wn - Water for n ms
      <br>
      ~W(n) - Water n ml
      <br>
      Command:
      <br>
         <input type="text" id="cmd">
         <br>
         Delay:
         <br>
         <input type="text" id="delayH" placeholder="HH" size="4" oninput="updateintimes(0,0)">
         <input type="text" id="delayM" placeholder="MM" size="2" oninput="updateintimes(0,0)">
         <input type="text" id="delayS" placeholder="SS" size="2" oninput="updateintimes(0,0)">
         <br>
         <input type="text" id="delay" oninput="updateintimes(0,1)">
         <br>
         Repeat after:
         <br>
         <input type="text" id="repeatH" placeholder="HH" size="4" oninput="updateintimes(1,0)">
         <input type="text" id="repeatM" placeholder="MM" size="2" oninput="updateintimes(1,0)">
         <input type="text" id="repeatS" placeholder="SS" size="2" oninput="updateintimes(1,0)">
         <br>
         <input type="text" id="repeat" oninput="updateintimes(1,1)">
            <button onclick="submit()">OK</button>

      <script>
        function updateintimes(id,f){

          var h=[document.getElementById("delayH"),document.getElementById("repeatH")];
          var m=[document.getElementById("delayM"),document.getElementById("repeatM")];
          var s=[document.getElementById("delayS"),document.getElementById("repeatS")];
          var t=[document.getElementById("delay"),document.getElementById("repeat")];
          if(isNaN(parseInt(h[id].value))){h[id].value="0";}
          if(isNaN(parseInt(m[id].value))){m[id].value="0";}
          if(isNaN(parseInt(s[id].value))){s[id].value="0";}
          if(isNaN(parseInt(t[id].value))){t[id].value="0";}
          var total = 0;
          if(f==0){
            total = parseInt(h[id].value)*3600 +parseInt(m[id].value)*60+parseInt(s[id].value);
          } else {
            total = parseInt(t[id].value);
          }
          var hval = Math.floor(total / 3600);
          var mval = Math.floor((total - hval *3600)/60);
          var sval = total - hval *3600 -mval*60;
          h[id].value = hval;
          m[id].value = mval;
          s[id].value = sval;
          t[id].value = total;
        }
      String.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
}
function submit(){
  var cmd = document.getElementById("cmd").value;
          while(1){
            if(cmd.indexOf("(") == -1) break;
            var start = cmd.indexOf("(");
            var end = cmd.indexOf(")");
            var ml = parseFloat(cmd.substring(start+1,end-start));
            var ms = Math.floor(((ml+6.5)/3.9)*1000);
            cmd = cmd.replace("("+ml+")",ms);
          }

          request("setcmd.php?cmd=" + cmd +"&time="+document.getElementById("delay").value+"&repeat="+document.getElementById("repeat").value);
          document.getElementById("cmd").value = "";
}
      function main(){
            setInterval(loadDoc,1000);
      }
      function request(msg){
        var xhttp = new XMLHttpRequest();

        xhttp.open("GET", msg, true);
        xhttp.send();
      }
          function loadDoc() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var data = JSON.parse(this.responseText);
      if(data.lastseen > 300){
        document.getElementById("time").style.backgroundColor = "red";
      } else {
        document.getElementById("time").style.backgroundColor = "white";
      }
      if(data.iacts.length > 0){
        document.getElementById("inactivity").innerHTML = "Detected inactivity:<button onclick=\"request('clearinact.php')\">Clear</button><br>";
        for (var i = 0; i < data.iacts.length; i++) {
          var sdate = new Date();
          sdate.setTime(data.iacts[i].start*1000);
          var edate = new Date();
          edate.setTime(data.iacts[i].end*1000);
          document.getElementById("inactivity").innerHTML += "From: "+sdate.toUTCString() + " To: " + edate.toUTCString() + " Duration:" + ((data.iacts[i].end - data.iacts[i].start)+"").toHHMMSS() + "<br>";
        }
      }else{
        document.getElementById("inactivity").innerHTML = "";
      }
      document.getElementById("time").innerHTML = "Offline Time:" + (data.lastseen+"").toHHMMSS();

      var i = 0;
      var table = document.getElementById("cmds") ;
      if(data.cmds.length < table.rows.length-1){
           for(i = data.cmds.length+1;i<table.rows.length;++i){
           table.deleteRow(i);
           }
      }
      for(i = 0;i<data.cmds.length;++i){
      if(table.rows.length < i+2){
      document.getElementById("cmds").insertRow(table.rows.length);
      table.rows[i+1].insertCell(0);
      table.rows[i+1].insertCell(1);
      table.rows[i+1].insertCell(2);
      table.rows[i+1].insertCell(3);
      table.rows[i+1].insertCell(4);
      }
      if(data.state){
        table.rows[i+1].cells[4].innerHTML = "<button disabled>Cancel</button>";
      } else {
        table.rows[i+1].cells[4].innerHTML = "<button onclick=\"request('removecmd.php?id="+i+"')\">Cancel</button>";
      }
      if(data.cmds[i].repeat==0){
        table.rows[i+1].cells[3].innerHTML = "NO";
      } else {
        table.rows[i+1].cells[3].innerHTML = data.cmds[i].repeat.toHHMMSS();
      }
      if(data.cmds[i].cached){
        table.rows[i+1].cells[2].innerHTML = "YES";
      } else {
        table.rows[i+1].cells[2].innerHTML = "NO";
      }
      table.rows[i+1].cells[0].innerHTML = data.cmds[i].value;
      var date = new Date();
      date.setTime(data.cmds[i].time*1000);
      table.rows[i+1].cells[1].innerHTML = date.toUTCString();
      }
     if(data.state){
       document.getElementById("state").innerHTML = "STARTED<br><button onclick=\"request('startstop.php?state=0')\">STOP</button>";
       document.body.style.backgroundColor = "white";
     } else {
       document.getElementById("state").innerHTML = "STOPPED<br><button onclick=\"request('startstop.php?state=1')\">START</button>";
       document.body.style.backgroundColor = "red";
     }
    }
  };
  xhttp.open("GET", "datadownload.php", true);
  xhttp.send();
}
window.onload = main;

      </script>
  </body>
</html>
