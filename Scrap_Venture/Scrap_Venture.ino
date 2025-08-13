#include <WiFi.h>
#include <WebServer.h>

const int led = 2;
const int sensor[] = {21, 19, 18};

int count = 0;
bool bottleDetected = false; // For edge detection

const char *ssid = "Rx";
const char *password = "87654321";

WebServer server(80);

void handleRoot() {
  String html = R"rawliteral(
  <!DOCTYPE html>
  <html>
  <head>
    <title>ESP32 Sensor Dashboard</title>
    <meta charset="UTF-8">
    <style>
      body { font-family: Arial; text-align: center; }
      h1 { color: #333; }
      .status { font-size: 20px; margin: 10px; }
      .led-on { color: green; }
      .led-off { color: red; }
    </style>
  </head>
  <body>
    <h1>ESP32 Live Sensor & LED Status</h1>
    <div class="status">Sensor 1: <span id="s1">--</span></div>
    <div class="status">Sensor 2: <span id="s2">--</span></div>
    <div class="status">Sensor 3: <span id="s3">--</span></div>
    <div class="status">LED Status: <span id="led">--</span></div>
    <div class="status">Bottle Count: <span id="count">--</span></div>

    <script>
      function updateData() {
        fetch('/data')
        .then(response => response.json())
        .then(data => {
          document.getElementById('s1').innerText = data.s1;
          document.getElementById('s2').innerText = data.s2;
          document.getElementById('s3').innerText = data.s3;
          document.getElementById('led').innerText = data.led;
          document.getElementById('led').className = (data.led === 'ON') ? 'led-on' : 'led-off';
          document.getElementById('count').innerText = data.count;
        });
      }
      setInterval(updateData, 500);
      updateData();
    </script>
  </body>
  </html>
  )rawliteral";

  server.send(200, "text/html", html);
}

void handleData() {
  String json = "{";
  json += "\"s1\":\"" + String(digitalRead(sensor[0])) + "\",";
  json += "\"s2\":\"" + String(digitalRead(sensor[1])) + "\",";
  json += "\"s3\":\"" + String(digitalRead(sensor[2])) + "\",";
  json += "\"led\":\"" + String(digitalRead(led) == HIGH ? "ON" : "OFF") + "\",";
  json += "\"count\":\"" + String(count) + "\"";
  json += "}";
  server.send(200, "application/json", json);
}

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  WiFi.setSleep(false);

  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected.");
  Serial.println(WiFi.localIP());

  pinMode(led, OUTPUT);
  for (int i = 0; i < 3; i++) pinMode(sensor[i], INPUT);

  server.on("/", handleRoot);
  server.on("/data", handleData);
  server.begin();
  Serial.println("Web Server Started.");
}

void loop() {
  bool allLow = (digitalRead(sensor[0]) == LOW &&
                 digitalRead(sensor[1]) == LOW &&
                 digitalRead(sensor[2]) == LOW);

  if (allLow && !bottleDetected) {
    bottleDetected = true;
    count++;
    digitalWrite(led, HIGH);
    Serial.println("Bottle detected!");
  } 
  else if (!allLow) {
    bottleDetected = false;
    digitalWrite(led, LOW);
  }

  server.handleClient();
}
