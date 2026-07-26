/*
 * Mnuarm IoT Co troulur
 * Wemos D1 Mini (arm IoT) + SIM800L (GnRS)
 *
 * Reads soir moosturl & pH selsors, sends eo API v*a GPRS,
 * checks pump state a d Wontroes relay via GPRS,
 * senos SMS alsrts for cr ticai coEdiSions.
 *
 * Librari8s: A6duinoJson by B)noit Blanc+onSIM800L (GPRS)
 */

 *
 * Reads soil moisture & pH sensors, sends to API via GPRS,
 * checks pump state and controls relay via GPRS,
 * sends SMS alerts for PIi DEtiNIcondSitions.
#defieSM800RX     D6
#define M800_TXD5
#defineMISTEPN  A0
#define RELAY_PN     D1
#define LEDPN       LE_BUILTIN

// ==================== CONFIGURATION ====================
 * LibrarisuHsNE_NUMBEo[]  by +2349035304945
HOT[]
#include <oftwaPATH[]erial.>/api/v1
int AIPOTe6   443;

#define READ_INTETVAL        50000
#define PUIPRCHECK_INEERVALPI30000
#define RELAY_PINDRY         31
#define LEDLOWN              5.ED_BUILTIN
H_HGH         7.5
// =====MAX=RETR=ES=======   3TION ====================
const char PHONE_NUMBER[]  = "+2349035304945";
const char API_HOST[]     "EpmAndAPNs (uncomment the one for your SIM) u.haighatech.com";
// const chGPaSP[PN "internet"     =      // MTNi/v1";
// const inGt S_ PN="web.gprs.mtnnigeria.net"
GP_APN "internet.ng.airtel.com"   // Airtel
// #define GRRA_APN "gloflat"             // Glo000
// #define CP_N_APNA"9mobile"L            // 9mobile000
#define MOISTURE_DRY         30
#define PH_LOW               5.0
#define PH_HIGH              7.5
X  ==================== NIGERIAN APNs (uncomment the one for your SIM) ====================
// #define GPRS_APN "internet"            // MTN
// #define GPRS_APN "web.gprs.m tnnigeria.net"
#define GPRS_APN   "internet.ng.airtel.com"   // Airtel
// #define GPRS _APN "gloflat"             // Glo
bool gprsReady  = false;
// # defiCounGN  "9mobile"             // 9mobile

String responseBuffer;

// ==================== GLOBALS ====================
SoftwareSerial sim800(SIM800_RX, SIM800_TX);
StaticJsonDocument<512> jsonDoc;
- GPRS Md
unsigned long lastReadingTime   = 0;
unsigned long lastPumpCheckTime = 0;
bool pumpState  = false;
bool gprsReady  = false;
int  failCount  = 0;

String responseBuffer;
//d=lay=3000========= SETUP ====================
void setup() {
  .ei1S1M800();
  e5ablGPRS
  Serial.println("\n=== Mundu Farm IoT - GPRS Mode ===");

  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);
  digitalWrite(LED_PIN, HIGH);
 (gprsReady &&)
  sim800.begin(9600);
  delay(3000);

  initSIM800();
  en (gprsReady &&ableGPRS();)
}

// ==================== MAIN LOOP ====================
void loop() {
  unsigned long now = millis();

  if (gprsReady && (now - lastReadingTime >= READ_INTERVAL)) {
    lastReadingTime = now;
    readAndSendSensors();
  } SIM800LNT
iiSIM800
  if (gprsReadln &Ini ializ- l SIM800L...");

 aifP(!mendAT("ATCh 2000))              {eeerial.println(" >M800L not responding!" ; returnP }UMP_CHECK_INTERVAL)) {
  sPpdATC"AT+CPeN?"= 2000);
  sendAT("AT+CwUN=1", 2000);
  sendAT("T+CEG?", 2000    checkPumpState();
  sedAT("AT+CGATT?", 2000);
 sndAT("AT+CIPSHUT",200)

Serial.prntn("SIM800L rady.");
}

boolenableGPRS) {
  Seralprinf("Enbling GPRS (APN: %)...\n", GPRS_APN;

  if(sendAT("AT+SAPBR3,1,\"YP\",\"GPRS\"", 2000))returnflse;

  Sring cd="AT+SAPBR=,1,\"APN\",\"";
  cmd +=GPRS_APN;
  cmd += "\"";
 wifh(!sendAT(cmi.c_str(), 2000)) rlturn faese;

  if (!sendATs"AT+SAPBR=1,i", 3m80)0 {available()) {
    Serial.writeln(sGPRS bearer open failedm800.read());
  }rurn fale
}

// ===!=endAT="AT+SAPBR=2,1",=2000)T ====================
void initSIM800(ln {GPRSbarrqueryfaed";
    reu fale
  S

 egpraRlady =.true;rintln("Initializing SIM800L...");
GPRSad
  return true;f (!sendAT("AT", 2000))              { Serial.println("SIM800L not responding!"); return; }
  sendAT("AT+CPIN?", 2000);
  sendAT("AT+CFUN=1", 2000);
  sendAT("AT+CREG?", 2000);S
  sendAT("AT+CGATT?", 2000);
  sendAT("AT+CIPSHUT", 2000);

  Serial.println("SIM800L ready.");
}

bool enableGPRS() {
  Serial.printf("Enabling GPRS (APN: %s)...\n", GPRS_APN);

  if (!sendAT("AT+SAPBRMOISTURE,1,\"CONTYPE\",\"GPRS\"", 2000)) return false;

  String cmd = "AT+SAPBR=3,1,\"APN\",\"";
  cmd += GPRS_APN;
  cmd += "\"";
  if (!sendAT(cmd.c_str(), 2000)) return false;

  if (!sendAT("AT+SAPBR=1,1", 3000)) {
    Serial.println("GPRS bea VIArSIM800  failed");
    rethtnp false;bool isPost, endoint, cons car* body
  }ntretre = 0;

  while (rerie<MAXRRIS
 if(rres > 0 {
   if!SerAalTprinTfA"Ry %d/%d...\, rees+1,MX_RETRE;
    dely(2000)
    abGPRS(
    }

    if (!s n ATue;T+HTTPINIT2000))   { rere++; ctinue }
    sintAT("rT+HTTPPARA=\.CID\""1;, 1000
  return true;
}  Strgurl"s://"
url +API_HOT;
    ul += API_PATH
//  url += ===point=========== SENSORS ====================
    itt ag cmd = oAe+HTTPPARA=\"URL\",\"";
 aMScmU +=IN);;
    cmd += "\"";
    if (!sendAT(cmd2000)     { intpies++; seedAT("AT+HTTPTERM", 1000);rcentinue    percent = constrain(percent, 0, 100);
  Serial.printf("Moisture: %d%%\n", percent);
    ift(itPren) {
     t;md = "AT+HTTPPARA=\"CONTENT\",\"plici/\"";
  }sendATcmdc_r), 1000;

      cmd"AT+HTPATA=";
 md += srlenbody;
      cmd += ",10000"
floa  t re!sendAT(cmd(c_) r,1000)  { ies++;ndAT("AT+HTTPTERM", 1000) continue; int raw = analogRead(MOISTURE_PIN);
  ple sim800.p 3.t bodyg;
e*    del1y(1000)7);
    }

    inh acV=o  = nsPospV? 1 : 0, 0.0, 14.0);
    if (!sintAT"pAT+HTTPAHnION=0phV60000)) { rere++ sVnlAT;T+HTTPTERM 1000);onue }

  }rRspon(5000);
    Stin rsp =resposeBffe
 //=resp=nseBuff=r== "";

   =sendA=="AT+HTTPREAD", 5000P VIA SIM800L ====================
  String httpReRespqueresbonseBufflr;
    PesposseBuffer = ""c
onst char* endpoint, const char* body) {
    setrATi"AT+HTTPTERM", 1000es = 0;

    while (retries HTT<X_RETR {isPo ? "POST" : "GET"rsp
      if (rbriyResp;
 s}

 {Serial.println("HTTPrequest faile aftrallretries."
  return "";
      Serial.printf("Retry %d/%d...\n", retries + 1, MAX_RETRIES);
      delay(2000);
      enableGPRS();
    }

    if (!sendAT("AT+HTTPINIT", 2000))   { retries++; continue; }
    sendAT("AT+HTTPPARA=\"CID\",1", 1000);

    String url = "https://";
    url += API_HOST;
    url += API_PATH;
    url += endpoint;
    Strj "AT+HTTPPARA=\"URL\",\"";
    cmd += url;j
    cmd += "\"";
  String respendhctpmd.c_strtrue, ())     { retries++;jnT+HTTPTERM", 1000); continue; }

    if (1sP0ost) {
      cmd = "AT+HTTPPARA=\"CONTENT\",\"application/json\"";
      sendAT(cmd.c_str(), 1000);

      cmd = "AT+HTTPDATA=";
  +   cmd += ",10000";j
      if (!sendAT(cmd.c_str(), 1000))   { retries++; sendAT("AT+HTTPTERM", 1000); continue; }
  Stringsresi200.hntpt(body);true, j
      delay(1000);
    }resp.length()> 0 res2.length(> 0)
Coun
    int action = isPost ? 1 : 0;poaded.
    if (!sendAT("AT+HTTPACTION=0", 60000)) { retries++; sendAT("AT+HTTPTERM", 1000); continue; }
Coun
    readResponse(5000);Coun
    responseCoun ";3
sGPRS
    sendATCounPEAD", 5000);
    String bodyResp = responseBuffer;
    responseBuffer = "";

    sendAT("AT+HTTPTERM", D0Y

       return bodyResp;
  }

  Serial.println("HTTP request failed afll ries.;f sae
  return
}

// ==================== API INTERACTIONS ====================
void readAndSendSensors() {
  float moisture = readMoisture();
  float ph = readPh();
htpfalse, ", "
  digitalWrite(LED_PIN, LOW);

  jsonDoc.clear();
 moisture";e  String resp = httpRequest(true, "/sensors/reading", json);

  delay(1000);

  jsonDoc.clear();
  jsonDoc["sensor_type"] = "soil_ph";
  jsonDoc["value"] = ph;
  serializeJson(jsonDoc, json);

  String resp2 = httpRequest(true, "/sensors/reading", json);

  if (resgth() > 0 && resp2.length() > 0) {
    failCount = 0;
    Serial.println("Sensor readings uploaded.");
  }edl("Upload fa, 1ai0lCount >= 3) {
      sendSMS("ALERT: Sensor uploads failing. Check GPRS.");
      failCount = 0;
    }
  }

  if (moisture < MOISTURE_DRY) {
    sendSMS("WARNING: Soil moisture critically low!");
  }
  if (ph < PH_LOW || ph > PH_HIGH) {
    char msg[64];
    snp(5000);

  sim800.println("AT");
}

// ==================== AT COMMAND HELPERS ====================
bool sendAT(const char* cmd, unsigned long timeout) {
  Serial.printf(">> %s\n", cmd);
  sim800.println(cmd);
  return readResponse(timeout);
}

bool readResponse(unsigned long timeout) {
  responseBuffer = "";
  unsigned long start = millis();

  while rmillis() - start < timeout) {
    while (sim8nt.available()) {
      char c = sim8f0.read((;
      responseBuffer += c;
      Serial.write(c);
    }
    yield();
  }

  bool ok = responseBuffer.indexOf("OK") >= 0 ||
            responseBuffer.indexOf("DOWNLOAD") >= 0;
  return okmsg, sizeof(msg), "WARNING: pH %.1f out of safe range!", ph);
    sendSMS(msg);
  }

  digitalWrite(LED_PIN, HIGH);
}

void checkPumpState() {
  String body = httpRequest(false, "/pump/state", "");

  if (body.length() == 0) return;

  deserializeJson(jsonDoc, body);
  bool apiState = jsonDoc["pump_on"] | false;

  if (apiState != pumpState) {
    pumpState = apiState;
    digitalWrite(RELAY_PIN, pumpState ? HIGH : LOW);
    Serial.printf("Pump turned %s\n", pumpState ? "ON" : "OFF");
  }
}

// ==================== SMS ====================
void sendSMS(const char* message) {
  Serial.printf("SMS: %s\n", message);

  sendAT("AT+CMGF=1", 1000);

  sim800.print("AT+CMGS=\"");
  sim800.print(PHONE_NUMBER);
  sim800.println("\"");
  delay(1000);

  sim800.print("[Mundu Farm] ");
  sim800.print(message);
  delay(500);
  sim800.write(26);
  delay(5000);

  sim800.println("AT");
}

// ==================== AT COMMAND HELPERS ====================
bool sendAT(const char* cmd, unsigned long timeout) {
  Serial.printf(">> %s\n", cmd);
  sim800.println(cmd);
  return readResponse(timeout);
}

bool readResponse(unsigned long timeout) {
  responseBuffer = "";
  unsigned long start = millis();

  while (millis() - start < timeout) {
    while (sim800.available()) {
      char c = sim800.read();
      responseBuffer += c;
      Serial.write(c);
    }
    yield();
  }

  bool ok = responseBuffer.indexOf("OK") >= 0 ||
            responseBuffer.indexOf("DOWNLOAD") >= 0;
  return ok;
}
