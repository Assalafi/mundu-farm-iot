/*
  Mundu Farm IoT Controller
  ------------------------------------------------------------
  Board: Mega 2560 Pro (Embedded) / Arduino Mega 2560
  Modem: SIM800L using MTN Nigeria GPRS
  Sensors:
    - Soil moisture module analogue output AO
    - PH-4502C-style pH interface analogue output PO
  Pump:
    - Relay module on digital pin 7

  Existing Laravel API (HTTP for SIM800L reliability):
    POST /api/v1/sensors/reading
    GET  /api/v1/pump/state
  Server: api.mundu.haighatech.com (HTTP:80 + HTTPS:443)

  Arduino IDE:
    Board: Arduino Mega or Mega 2560
    Processor: ATmega2560
    Serial Monitor: 115200 baud

  No external Arduino library is required.
*/

#include <Arduino.h>

// ============================================================
// USER CONFIGURATION
// ============================================================

// MTN Nigeria GPRS
const char GPRS_APN[]  = "web.gprs.mtnnigeria.net";
const char GPRS_USER[] = "";
const char GPRS_PASS[] = "";

// Server
const char API_HOST[] = "api.mundu.haighatech.com";
const char API_BASE[] = "/api/v1";

// SIM800L only supports SSL 3.0 / TLS 1.0.
// Server has TLS 1.0 enabled, but SIM800L SSL is unreliable.
// HTTP on port 80 works reliably. Set false.
const bool USE_HTTPS = false;

// The pause starts after a complete upload/check cycle finishes.
// Use 5000UL for 5 seconds or 10000UL for 10 seconds.
const unsigned long CYCLE_INTERVAL_MS = 10000UL;

// Mega pins
const uint8_t MOISTURE_PIN = A0;
const uint8_t PH_PIN       = A1;
const uint8_t RELAY_PIN    = 7;

// Most relay boards are active LOW.
const bool RELAY_ACTIVE_LOW = true;

// Moisture calibration placeholders.
// Replace them with values measured from your own sensor.
int MOISTURE_DRY_RAW = 850;
int MOISTURE_WET_RAW = 300;

// pH two-point calibration placeholders.
// Replace with PO-pin voltages measured in pH 7 and pH 4 buffers.
float PH7_VOLTAGE = 2.50F;
float PH4_VOLTAGE = 3.04F;

// HTTP retries per request
const uint8_t HTTP_MAX_ATTEMPTS = 2;

// Reinitialize the modem after this number of complete failed cycles.
const uint8_t FAILED_CYCLES_BEFORE_MODEM_RESET = 3;

// ============================================================
// SERIAL PORTS AND RUNTIME STATE
// ============================================================

// Mega Serial1:
// RX1 = pin 19: receives from SIM800L TXD
// TX1 = pin 18: sends to SIM800L RXD through a level shifter/divider
HardwareSerial& modem = Serial1;

bool modemReady = false;
bool gprsReady = false;
bool pumpOn = false;

uint8_t failedCycleCount = 0;
unsigned long nextCycleAt = 0;

// ============================================================
// GENERAL HELPERS
// ============================================================

float clampFloat(float value, float minimum, float maximum) {
  if (value < minimum) return minimum;
  if (value > maximum) return maximum;
  return value;
}

void setPump(bool turnOn) {
  pumpOn = turnOn;

  if (RELAY_ACTIVE_LOW) {
    digitalWrite(RELAY_PIN, turnOn ? LOW : HIGH);
  } else {
    digitalWrite(RELAY_PIN, turnOn ? HIGH : LOW);
  }

  Serial.print(F("Pump relay is now "));
  Serial.println(turnOn ? F("ON") : F("OFF"));
}

void clearModemInput() {
  while (modem.available()) {
    modem.read();
  }
}

// Reads modem output until timeout, expected text, or an error.
// A short quiet period is required after the expected text so the
// complete response is normally collected.
String readModem(
  unsigned long timeoutMs,
  const char* expectedText = nullptr
) {
  String response;
  response.reserve(700);

  const unsigned long startedAt = millis();
  unsigned long lastByteAt = millis();

  while (millis() - startedAt < timeoutMs) {
    while (modem.available()) {
      const char c = static_cast<char>(modem.read());
      response += c;
      Serial.write(c);
      lastByteAt = millis();
    }

    const bool expectedFound =
      expectedText != nullptr &&
      response.indexOf(expectedText) >= 0;

    const bool errorFound =
      response.indexOf("\r\nERROR\r\n") >= 0 ||
      response.indexOf("+CME ERROR:") >= 0 ||
      response.indexOf("+CMS ERROR:") >= 0;

    if ((expectedFound || errorFound) &&
        millis() - lastByteAt > 200UL) {
      break;
    }
  }

  return response;
}

String runAT(
  const String& command,
  const char* expectedText,
  unsigned long timeoutMs
) {
  clearModemInput();

  Serial.println();
  Serial.print(F(">> "));
  Serial.println(command);

  modem.println(command);
  return readModem(timeoutMs, expectedText);
}

bool sendAT(
  const String& command,
  const char* expectedText = "OK",
  unsigned long timeoutMs = 3000UL
) {
  const String response =
    runAT(command, expectedText, timeoutMs);

  const bool successful =
    response.indexOf(expectedText) >= 0;

  if (!successful) {
    Serial.print(F("FAILED; expected: "));
    Serial.println(expectedText);
  }

  return successful;
}

// ============================================================
// SIM800L AND MOBILE NETWORK
// ============================================================

bool waitForNetworkRegistration() {
  Serial.println();
  Serial.println(F("Waiting for MTN network registration..."));

  for (uint8_t attempt = 1; attempt <= 30; attempt++) {
    const String creg = runAT("AT+CREG?", "OK", 3000UL);

    const bool registered =
      creg.indexOf("+CREG: 0,1") >= 0 ||
      creg.indexOf("+CREG: 0,5") >= 0 ||
      creg.indexOf("+CREG: 1,1") >= 0 ||
      creg.indexOf("+CREG: 1,5") >= 0;

    if (registered) {
      Serial.println(F("Registered on the mobile network."));
      return true;
    }

    Serial.print(F("Registration attempt "));
    Serial.print(attempt);
    Serial.println(F("/30"));
    delay(2000);
  }

  Serial.println(F("Network registration failed."));
  return false;
}

bool initializeModem() {
  Serial.println();
  Serial.println(F("Initializing SIM800L..."));

  bool answered = false;

  for (uint8_t attempt = 1; attempt <= 5; attempt++) {
    if (sendAT("AT", "OK", 2000UL)) {
      answered = true;
      break;
    }
    delay(1500);
  }

  if (!answered) {
    Serial.println(F("SIM800L did not answer AT commands."));
    modemReady = false;
    return false;
  }

  sendAT("ATE0", "OK", 2000UL);       // Disable command echo
  sendAT("AT+CMEE=2", "OK", 2000UL);  // Detailed modem errors

  if (!sendAT("AT+CPIN?", "READY", 3000UL)) {
    Serial.println(F("SIM is not ready. Disable its PIN lock."));
    modemReady = false;
    return false;
  }

  sendAT("AT+CSQ", "OK", 3000UL);     // Signal quality
  sendAT("AT+COPS?", "OK", 5000UL);   // Current operator

  modemReady = waitForNetworkRegistration();
  return modemReady;
}

bool bearerHasIPAddress() {
  const String response =
    runAT("AT+SAPBR=2,1", "OK", 5000UL);

  return
    response.indexOf("+SAPBR: 1,1") >= 0 &&
    response.indexOf("\"0.0.0.0\"") < 0;
}

bool openGPRS() {
  if (!modemReady && !initializeModem()) {
    return false;
  }

  Serial.println();
  Serial.println(F("Opening MTN GPRS bearer..."));

  sendAT("AT+CGATT=1", "OK", 10000UL);

  // A closed bearer can return ERROR here; that is harmless.
  runAT("AT+SAPBR=0,1", "OK", 5000UL);

  if (!sendAT(
        "AT+SAPBR=3,1,\"CONTYPE\",\"GPRS\"",
        "OK",
        3000UL
      )) {
    gprsReady = false;
    return false;
  }

  String command = "AT+SAPBR=3,1,\"APN\",\"";
  command += GPRS_APN;
  command += "\"";

  if (!sendAT(command, "OK", 3000UL)) {
    gprsReady = false;
    return false;
  }

  if (strlen(GPRS_USER) > 0) {
    command = "AT+SAPBR=3,1,\"USER\",\"";
    command += GPRS_USER;
    command += "\"";

    if (!sendAT(command, "OK", 3000UL)) {
      gprsReady = false;
      return false;
    }
  }

  if (strlen(GPRS_PASS) > 0) {
    command = "AT+SAPBR=3,1,\"PWD\",\"";
    command += GPRS_PASS;
    command += "\"";

    if (!sendAT(command, "OK", 3000UL)) {
      gprsReady = false;
      return false;
    }
  }

  if (!sendAT("AT+SAPBR=1,1", "OK", 30000UL)) {
    Serial.println(F("GPRS bearer could not be opened."));
    gprsReady = false;
    return false;
  }

  gprsReady = bearerHasIPAddress();

  Serial.println(
    gprsReady
      ? F("MTN GPRS connected.")
      : F("GPRS opened but no IP address was assigned.")
  );

  return gprsReady;
}

bool ensureGPRS() {
  if (!modemReady && !initializeModem()) {
    return false;
  }

  if (bearerHasIPAddress()) {
    gprsReady = true;
    return true;
  }

  return openGPRS();
}

void resetModemSession() {
  Serial.println();
  Serial.println(F("Resetting modem software session..."));

  runAT("AT+HTTPTERM", "OK", 2000UL);
  runAT("AT+SAPBR=0,1", "OK", 7000UL);
  sendAT("AT+CFUN=1,1", "OK", 5000UL);

  modemReady = false;
  gprsReady = false;

  // Allow the modem to reboot.
  delay(10000);
}

// ============================================================
// HTTP/HTTPS
// ============================================================

String buildURL(const char* endpoint) {
  String url = USE_HTTPS ? "https://" : "http://";
  url += API_HOST;
  url += API_BASE;
  url += endpoint;
  return url;
}

int parseHTTPStatus(const String& response) {
  const int marker = response.indexOf("+HTTPACTION:");
  if (marker < 0) return -1;

  const int firstComma = response.indexOf(',', marker);
  if (firstComma < 0) return -1;

  const int secondComma = response.indexOf(',', firstComma + 1);
  if (secondComma < 0) return -1;

  return response.substring(firstComma + 1, secondComma).toInt();
}

String extractJSON(const String& response) {
  const int start = response.indexOf('{');
  const int finish = response.lastIndexOf('}');

  if (start < 0 || finish < start) {
    return "";
  }

  return response.substring(start, finish + 1);
}

bool performHTTPRequestOnce(
  const char* method,
  const char* endpoint,
  const String& requestBody,
  int& statusCode,
  String& responseBody
) {
  statusCode = -1;
  responseBody = "";

  if (!ensureGPRS()) {
    Serial.println(F("Request stopped because GPRS is unavailable."));
    return false;
  }

  // Terminating a nonexistent HTTP session may return ERROR.
  runAT("AT+HTTPTERM", "OK", 2000UL);

  if (!sendAT("AT+HTTPINIT", "OK", 4000UL)) {
    return false;
  }

  if (!sendAT("AT+HTTPPARA=\"CID\",1", "OK", 3000UL)) {
    runAT("AT+HTTPTERM", "OK", 2000UL);
    return false;
  }

  const String sslCommand =
    USE_HTTPS ? "AT+HTTPSSL=1" : "AT+HTTPSSL=0";

  if (!sendAT(sslCommand, "OK", 3000UL)) {
    runAT("AT+HTTPTERM", "OK", 2000UL);
    return false;
  }

  String command = "AT+HTTPPARA=\"URL\",\"";
  command += buildURL(endpoint);
  command += "\"";

  if (!sendAT(command, "OK", 8000UL)) {
    runAT("AT+HTTPTERM", "OK", 2000UL);
    return false;
  }

  const bool isPost = strcmp(method, "POST") == 0;

  if (isPost) {
    if (!sendAT(
          "AT+HTTPPARA=\"CONTENT\",\"application/json\"",
          "OK",
          3000UL
        )) {
      runAT("AT+HTTPTERM", "OK", 2000UL);
      return false;
    }

    command = "AT+HTTPDATA=";
    command += requestBody.length();
    command += ",10000";

    const String ready =
      runAT(command, "DOWNLOAD", 5000UL);

    if (ready.indexOf("DOWNLOAD") < 0) {
      runAT("AT+HTTPTERM", "OK", 2000UL);
      return false;
    }

    Serial.println();
    Serial.print(F(">> JSON: "));
    Serial.println(requestBody);

    modem.print(requestBody);

    const String uploadReply =
      readModem(12000UL, "OK");

    if (uploadReply.indexOf("OK") < 0) {
      runAT("AT+HTTPTERM", "OK", 2000UL);
      return false;
    }
  }

  const String actionCommand =
    isPost ? "AT+HTTPACTION=1" : "AT+HTTPACTION=0";

  const String actionReply =
    runAT(actionCommand, "+HTTPACTION:", 65000UL);

  statusCode = parseHTTPStatus(actionReply);

  Serial.print(F("HTTP status: "));
  Serial.println(statusCode);

  if (statusCode < 0) {
    runAT("AT+HTTPTERM", "OK", 2000UL);
    return false;
  }

  const String readReply =
    runAT("AT+HTTPREAD", "OK", 20000UL);

  responseBody = extractJSON(readReply);

  Serial.print(F("Response JSON: "));
  Serial.println(responseBody);

  runAT("AT+HTTPTERM", "OK", 3000UL);

  return statusCode >= 200 && statusCode < 300;
}

bool performHTTPRequest(
  const char* method,
  const char* endpoint,
  const String& requestBody,
  int& statusCode,
  String& responseBody
) {
  for (uint8_t attempt = 1; attempt <= HTTP_MAX_ATTEMPTS; attempt++) {
    Serial.println();
    Serial.print(F("HTTP attempt "));
    Serial.print(attempt);
    Serial.print('/');
    Serial.println(HTTP_MAX_ATTEMPTS);

    if (performHTTPRequestOnce(
          method,
          endpoint,
          requestBody,
          statusCode,
          responseBody
        )) {
      return true;
    }

    gprsReady = false;

    if (attempt < HTTP_MAX_ATTEMPTS) {
      delay(2000);
    }
  }

  return false;
}

// ============================================================
// API OPERATIONS
// ============================================================

bool uploadSensor(const char* sensorType, float value) {
  String json = "{\"sensor_type\":\"";
  json += sensorType;
  json += "\",\"value\":";
  json += String(value, 2);
  json += "}";

  int statusCode = -1;
  String responseBody;

  const bool successful = performHTTPRequest(
    "POST",
    "/sensors/reading",
    json,
    statusCode,
    responseBody
  );

  Serial.print(sensorType);
  Serial.print(F(" upload: "));
  Serial.println(successful ? F("SUCCESS") : F("FAILED"));

  return successful;
}

bool downloadPumpState(bool& requestedState) {
  int statusCode = -1;
  String responseBody;

  if (!performHTTPRequest(
        "GET",
        "/pump/state",
        "",
        statusCode,
        responseBody
      )) {
    return false;
  }

  responseBody.replace(" ", "");
  responseBody.replace("\r", "");
  responseBody.replace("\n", "");
  responseBody.toLowerCase();

  if (responseBody.indexOf("\"pump_on\":true") >= 0) {
    requestedState = true;
    return true;
  }

  if (responseBody.indexOf("\"pump_on\":false") >= 0) {
    requestedState = false;
    return true;
  }

  Serial.println(F("The API response did not contain pump_on."));
  return false;
}

// ============================================================
// SENSOR READING AND CALIBRATION
// ============================================================

// Reads 15 samples, sorts them, discards the two highest and
// two lowest values, then averages the remaining 11 samples.
int readFilteredAnalog(uint8_t pin) {
  const uint8_t sampleCount = 15;
  int samples[sampleCount];

  // Discard first conversion after channel selection.
  analogRead(pin);
  delay(5);

  for (uint8_t i = 0; i < sampleCount; i++) {
    samples[i] = analogRead(pin);
    delay(20);
  }

  // Small insertion sort.
  for (uint8_t i = 1; i < sampleCount; i++) {
    const int value = samples[i];
    int8_t j = i - 1;

    while (j >= 0 && samples[j] > value) {
      samples[j + 1] = samples[j];
      j--;
    }

    samples[j + 1] = value;
  }

  long total = 0;

  for (uint8_t i = 2; i < sampleCount - 2; i++) {
    total += samples[i];
  }

  return static_cast<int>(total / (sampleCount - 4));
}

float calculateMoisturePercent(int raw) {
  const float range =
    static_cast<float>(MOISTURE_DRY_RAW - MOISTURE_WET_RAW);

  if (range == 0.0F) {
    return 0.0F;
  }

  const float percentage =
    100.0F *
    static_cast<float>(MOISTURE_DRY_RAW - raw) /
    range;

  return clampFloat(percentage, 0.0F, 100.0F);
}

float calculatePH(float voltage) {
  const float calibrationVoltageRange =
    PH4_VOLTAGE - PH7_VOLTAGE;

  if (calibrationVoltageRange == 0.0F) {
    return 7.0F;
  }

  const float slope =
    (4.0F - 7.0F) / calibrationVoltageRange;

  const float ph =
    7.0F + slope * (voltage - PH7_VOLTAGE);

  return clampFloat(ph, 0.0F, 14.0F);
}

// ============================================================
// COMPLETE FARM CYCLE
// ============================================================

void runFarmCycle() {
  Serial.println();
  Serial.println(F("================================================"));
  Serial.println(F("Reading sensors and synchronizing with server"));
  Serial.println(F("================================================"));

  const int moistureRaw = readFilteredAnalog(MOISTURE_PIN);
  const int phRaw = readFilteredAnalog(PH_PIN);

  const float moisture =
    calculateMoisturePercent(moistureRaw);

  const float phVoltage =
    static_cast<float>(phRaw) * (5.0F / 1023.0F);

  const float ph =
    calculatePH(phVoltage);

  Serial.print(F("Moisture raw: "));
  Serial.println(moistureRaw);

  Serial.print(F("Moisture: "));
  Serial.print(moisture, 1);
  Serial.println(F("%"));

  Serial.print(F("pH raw: "));
  Serial.println(phRaw);

  Serial.print(F("pH PO voltage: "));
  Serial.print(phVoltage, 3);
  Serial.println(F(" V"));

  Serial.print(F("Calculated pH: "));
  Serial.println(ph, 2);

  const bool moistureUploaded =
    uploadSensor("moisture", moisture);

  const bool phUploaded =
    uploadSensor("soil_ph", ph);

  bool requestedPumpState = pumpOn;
  const bool pumpStateReceived =
    downloadPumpState(requestedPumpState);

  if (pumpStateReceived && requestedPumpState != pumpOn) {
    setPump(requestedPumpState);
  }

  const bool completeSuccess =
    moistureUploaded &&
    phUploaded &&
    pumpStateReceived;

  if (completeSuccess) {
    failedCycleCount = 0;
  } else {
    failedCycleCount++;

    Serial.print(F("Consecutive failed cycles: "));
    Serial.println(failedCycleCount);
  }

  Serial.println();
  Serial.print(F("Cycle summary: moisture="));
  Serial.print(moistureUploaded ? F("OK") : F("FAIL"));

  Serial.print(F(", pH="));
  Serial.print(phUploaded ? F("OK") : F("FAIL"));

  Serial.print(F(", pump="));
  Serial.println(pumpStateReceived ? F("OK") : F("FAIL"));

  if (failedCycleCount >= FAILED_CYCLES_BEFORE_MODEM_RESET) {
    failedCycleCount = 0;
    resetModemSession();
  }

  nextCycleAt = millis() + CYCLE_INTERVAL_MS;
}

// ============================================================
// ARDUINO ENTRY POINTS
// ============================================================

void setup() {
  // USB serial monitor
  Serial.begin(115200);

  // SIM800L UART
  modem.begin(9600);

  pinMode(RELAY_PIN, OUTPUT);
  setPump(false);

  analogReference(DEFAULT);

  Serial.println();
  Serial.println(F("=============================================="));
  Serial.println(F(" Mundu Farm IoT - Mega 2560 + SIM800L/MTN"));
  Serial.println(F("=============================================="));
  Serial.println(F("Moisture AO -> A0"));
  Serial.println(F("pH PO       -> A1"));
  Serial.println(F("Relay IN    -> D7"));
  Serial.println(F("SIM TXD     -> RX1 pin 19"));
  Serial.println(F("TX1 pin 18  -> level shifter/divider -> SIM RXD"));

  // The modem and pH circuit start warming while this delay runs.
  delay(8000);

  if (initializeModem()) {
    openGPRS();
  }

  // Run the first cycle immediately.
  nextCycleAt = 0;
}

void loop() {
  if (static_cast<long>(millis() - nextCycleAt) >= 0) {
    runFarmCycle();
  }

  // Print unsolicited modem messages between cycles.
  while (modem.available()) {
    Serial.write(modem.read());
  }
}
