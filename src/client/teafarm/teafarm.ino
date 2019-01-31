
#include <SPI.h>
#include <Ethernet.h>

byte mac[] = {
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED
};
IPAddress ip(192, 168, 1, 178);
IPAddress myDns(192, 168, 1, 1);

EthernetClient client;

char server[] = "www.ondralukes.cz";
char cmd[50];
char time[50];
char cmdid[50];

//Pin numbers
#define ENDSTOP 6
#define WATER 7
const int in1 =  2;
const int in2 =  3;
const int in3 = 4;
const int in4 = 5;

#define ULONG_MAX 4294967295

int speed = 1000;

int trgpos = 1000;
unsigned long nextcmdt = 0;
unsigned long nextcmdtstart = 0;
char nextcmd[50];
int nextcmdid = -1;
void setup() {
  Serial.begin(9600);
  pinMode(in1, OUTPUT);
  pinMode(in2, OUTPUT);
  pinMode(in3, OUTPUT);
  pinMode(in4, OUTPUT);
  pinMode(WATER, OUTPUT);
  pinMode(ENDSTOP,INPUT_PULLUP);
  delay(1000);
  Ethernet.begin(mac, ip, myDns);
  Serial.print("My IP address: ");
  Serial.println(Ethernet.localIP());
  
}
int pos = -1;
bool httpRequest();
void loop() {
  if(httpRequest()){
    if(strlen(cmd) > 0){
      if(atoi(cmdid)==nextcmdid){
      Serial.println("Command already cached");
      } else {
        if(nextcmdid == -1){
        Serial.println("Caching command");
        } else {
          Serial.println("Overwriting cached command");
          }
 nextcmdt = strtoul(time,NULL,10)*1000;
 nextcmdtstart = millis();
 strcpy(nextcmd,cmd);
nextcmdid=atoi(cmdid);
      }
    } else {
      Serial.print("Forgetting position");
      pos = -1;
      }
  }
  if(nextcmdid != -1){
    if(timeup(nextcmdtstart,nextcmdt)){
      nextcmdid = -1; 
       Serial.print("Executing cached command:");
       Serial.println(nextcmd);
       while(!cmddonereq());
       executecmd();
      }
    }
 delay(5000);
}
bool timeup(unsigned long f,unsigned long d){
   if(f > millis()){
     if(millis()+(ULONG_MAX - f) >= d) return true;
   } else if(millis()-f >=d) return true;
  return false;
}
void executecmd(){
  Serial.print("Executing ");
  Serial.println(cmd);
  char *token;
   
   /* get the first token */
   token = strtok(cmd, "~");
   
   /* walk through other tokens */
   while(true) {
      Serial.println(token);
    
     
   char tmpcmd[50];
   strcpy(tmpcmd,token);
    if(tmpcmd[0] == 'M'){
 int trgpos = atoi(&tmpcmd[1]);
 Serial.println("Received MOVE Command");
 Serial.println(trgpos);
 if(trgpos != pos){
  int i = 0;
  if(pos == -1){
  while(digitalRead(ENDSTOP) == HIGH){
    rotateCounterClockwise();
    
    i++;
  }
  pos = 0;
  Serial.print("Position was ");
  Serial.println(i);
  } else {
    Serial.print("Position was ");
  Serial.println(pos);
    }
  
  Serial.print("Going to ");
  Serial.println(trgpos);
  for(int i = 0;i<abs(pos-trgpos);i++){
    if(pos < trgpos){
     rotateClockwise();
    } else {
       rotateCounterClockwise();
      }
    }
    pos = trgpos;
    moff();
  }
 } else if(tmpcmd[0] == 'W'){
  Serial.println("Received WATER Command");
  int t = atoi(&tmpcmd[1]);
 Serial.println(t);
 digitalWrite(WATER,HIGH);
 delay(t);
 digitalWrite(WATER,LOW);
  }
  if(token == NULL) break;
   token = strtok(NULL, "~");
   }
  }
// this method makes a HTTP connection to the server:
bool httpRequest() {
  char buf[50];
  memset(cmd,0,sizeof(cmd));
  memset(buf,0,sizeof(buf));
  time[0] = '\0';
  cmdid[0] = '\0';
  int msgpart =0;
  
  // if there's a successful connection:
  Serial.println("connecting");
  if (client.connect(server, 80)) {
    
    // send the HTTP GET request:
    client.println("GET /teafarmserver/getcmd.php?apikey=4aafee89afb5fe37a31895bbff116458 HTTP/1.1");
    client.println("Host: ondralukes.cz:80");
    client.println("Connection: close");
    client.println();
    int t = 0;
    int i = 0;
    bool started = false;
    while(t < 100){
      if (client.available()) {
    char c = client.read();
    Serial.write(c);
    if(c == '>'){
      buf[i] = '\0';
      if(msgpart==0){
        strcpy(cmd,buf);
        } else if(msgpart == 1){
          strcpy(time,buf);
          }else if(msgpart == 2){
          strcpy(cmdid,buf);
          }
          i=0;
        msgpart++;
        started = false;
        if(msgpart==3) break;
      }
    if(started == true){
    buf[i] = c;
    i++;
    }
    if(c == '<'){
      started = true;
      }
    
   }else{
   t++;
   delay(10);
   }
  }
     
    Serial.print("RX: CMD:");
    Serial.print(cmd);
    Serial.print(" TIME:");
    Serial.print(time);
    Serial.print(" ID:");
    Serial.println(cmdid);
    client.stop();
    return true;
  } else {
    Serial.println("connection failed");
    client.stop();
    return false;
  }
  
   
}
bool cmddonereq(){
  Serial.print("Sending DONE...");
  bool ok = client.connect(server, 80);
  if(!ok){
    client.stop();
    Serial.println("FAILED");
    return false;
    
    }
  client.println("GET /teafarmserver/donecmd.php?apikey4aafee89afb5fe37a31895bbff116458 HTTP/1.1");
    client.println("Host: ondralukes.cz:80");
    client.println("Connection: close");
    client.println();
    int t = 0;
    while(t < 100){
      if (client.available()) {
    client.read();
    break;
      }
   t++;
   delay(10);
   }
    client.stop();
    Serial.println("OK");
    return true;
  }
void rotateClockwise() {
  step1();
  step2();
  step3();
  step4();
  step5();
  step6();
  step7();
  step8();
}
void rotateCounterClockwise() {
  step8();
  step7();
  step6();
  step5();
  step4();
  step3();
  step2();
  step1();
}
void moff(){
   digitalWrite(in1, LOW);
  digitalWrite(in2, LOW);
  digitalWrite(in3, LOW);
  digitalWrite(in4, LOW);
  }
void step1(){
  digitalWrite(in1, HIGH);
  digitalWrite(in2, LOW);
  digitalWrite(in3, LOW);
  digitalWrite(in4, LOW);
  delayMicroseconds(speed);
}
void step2(){
  digitalWrite(in1, HIGH);
  digitalWrite(in2, HIGH);
  digitalWrite(in3, LOW);
  digitalWrite(in4, LOW);
  delayMicroseconds(speed);
}
void step3(){
  digitalWrite(in1, LOW);
  digitalWrite(in2, HIGH);
  digitalWrite(in3, LOW);
  digitalWrite(in4, LOW);
  delayMicroseconds(speed);
}
void step4(){
  digitalWrite(in1, LOW);
  digitalWrite(in2, HIGH);
  digitalWrite(in3, HIGH);
  digitalWrite(in4, LOW);
  delayMicroseconds(speed);
}
void step5(){
  digitalWrite(in1, LOW);
  digitalWrite(in2, LOW);
  digitalWrite(in3, HIGH);
  digitalWrite(in4, LOW);
  delayMicroseconds(speed);
}
void step6(){
  digitalWrite(in1, LOW);
  digitalWrite(in2, LOW);
  digitalWrite(in3, HIGH);
  digitalWrite(in4, HIGH);
  delayMicroseconds(speed);
}
void step7(){
  digitalWrite(in1, LOW);
  digitalWrite(in2, LOW);
  digitalWrite(in3, LOW);
  digitalWrite(in4, HIGH);
  delayMicroseconds(speed);
}
void step8(){
  digitalWrite(in1, HIGH);
  digitalWrite(in2, LOW);
  digitalWrite(in3, LOW);
  digitalWrite(in4, HIGH);
  delayMicroseconds(speed);
}
