#include <WiFi.h>
#include <HTTPClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>

#define EXPECTED_NUM_SENSORS 6 //Expected number of sensors that are connected
#define ONE_WIRE_BUS 4 // OneWire GPIO Pin
#define SENSOR_POWER_PIN 15 // GPIO pin that controls the power to the sensors
#define WAKE_UP_INTERVAL_SECONDS 5 * 60 // Time to sleep between readings in seconds (use * 60 for minutes)
#define DEVICE_NAME "COOL NAME" // Name of the unit reporting values

OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

const char* ssid = "WIFI Network"; // Wifi Network
const char* password = "WIFI PASS"; // Wifi Password
const char* server = "SERVER IP"; // Server Address

void setup() {
  Serial.begin(115200);
  Serial.println("Hello World!");
  pinMode(SENSOR_POWER_PIN, OUTPUT);
  digitalWrite(SENSOR_POWER_PIN, HIGH); 
  delay(1000); // Waiting for sensors to power up before Initialization 
  sensors.begin();
  Serial.println("Initializing sensors...");
  sensors.setWaitForConversion(false);  // makes it async
  sensors.requestTemperatures();
  sensors.setWaitForConversion(true);
  delay(2000); // Waiting for sensors to report to controller 

  // Check initialization status
  int maxAttempts = 3; // Maximum number of attempts to check initialization status
  int attemptCount = 0;
  int numDevices = 0;
  bool initializationComplete = false;

  // Get the number of devices found during initialization to test if all expected sensors are reporting
  while (!initializationComplete && attemptCount < maxAttempts) {
    numDevices = sensors.getDeviceCount();
    if (numDevices < EXPECTED_NUM_SENSORS) { // Handle initialization failure: fewer sensors than expected
      Serial.println("Initialization failed. Number of devices found is less than expected. Retrying...");
      attemptCount++;
      delay(500); // Delay before retrying count
    } else if (numDevices > EXPECTED_NUM_SENSORS) { // Handle initialization failure: more sensors than expected
      Serial.println("Initialization failed-successfully. More devices found than expected.");
      break; // Exits the retry loop if more devices found than expected
    } else { // Initialization successful: expected number of sensors found
      initializationComplete = true;
    }
  } 

  if (numDevices == 0) {
    // No devices found, skip to sleep
    Serial.println("Initialization Failed, No Devices Found...");
    Serial.println("Skipping to sleep.");
    digitalWrite(SENSOR_POWER_PIN, LOW);
    esp_sleep_enable_timer_wakeup(WAKE_UP_INTERVAL_SECONDS * 1000000);
    esp_deep_sleep_start();
  } else if (initializationComplete) { // Proceed with other tasks after initialization completes successfully
    Serial.print("Initialization successful. Expected number of sensors (");
    Serial.print(numDevices);
    Serial.println(") found.");
    Serial.println("Initialization Completed");
    
    // Connect to WiFi
    connectToWiFi();
  } else { // Proceed with other tasks after initialization fails
    Serial.print("Initialization failed. No devices found or incorrect number of devices (");
    Serial.print(numDevices);
    Serial.println(") found.");
    Serial.println("Errors may occur.");
    Serial.println("Initialization Completed");
    
    // Connect to WiFi
    connectToWiFi();
  }
}

void loop() {
  readAndSendSensorData();

  Serial.println("Tasks Complete.");
  Serial.println("Going to sleep...");
  digitalWrite(SENSOR_POWER_PIN, LOW);
  esp_sleep_enable_timer_wakeup(WAKE_UP_INTERVAL_SECONDS * 1000000);
  esp_deep_sleep_start();
}

void connectToWiFi() {
  Serial.println("Connecting to WiFi...");
  WiFi.begin(ssid, password);
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 3) {
    delay(1000);
    Serial.print(".");
    attempts++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi connected.");
  } else {
    Serial.println("WiFi connection failed.");
    Serial.println("Skipping to sleep.");
    digitalWrite(SENSOR_POWER_PIN, LOW);
    esp_sleep_enable_timer_wakeup(WAKE_UP_INTERVAL_SECONDS * 1000000);
    esp_deep_sleep_start();
  }
}

bool sendDataToServer(String deviceName, String sensorName, float sensorValue) {
  HTTPClient http;

  String url = "http://" + String(server) + "/receive_temp.php";
  String postData = "device_name=" + deviceName + "&sensor_name=" + sensorName + "&sensor_value=" + String(sensorValue);

  Serial.println("Sending data to server...");
  Serial.println(postData);

  http.begin(url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpResponseCode = http.POST(postData);

  if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
    http.end();
    return true;
  } else {
    Serial.print("Error in HTTP request: ");
    Serial.println(httpResponseCode);
    http.end();
    return false;
  }
}

void readAndSendSensorData() {
  int numDevices = sensors.getDeviceCount();
  DeviceAddress tempDeviceAddress;
  float tempC;

  Serial.println("Acquiring sensor data before sending...");

  for (int i = 0; i < numDevices; i++) {
    sensors.getAddress(tempDeviceAddress, i);
    tempC = sensors.getTempC(&tempDeviceAddress[0]);

    String sensorName = getSensorID(i);

    if (tempC > -40 && tempC < 125) {
      String deviceName = DEVICE_NAME;
      if (WiFi.status() == WL_CONNECTED) {
        sendDataToServer(deviceName, sensorName, tempC);
      } else {
        Serial.println("WiFi not connected. Skipping data transmission.");
      }
    } else {
      Serial.println("Invalid sensor data. Skipping data transmission.");
    }
  }
}

String getSensorID(int index) {
  DeviceAddress sensorAddress;
  if (!sensors.getAddress(sensorAddress, index)) {
    Serial.println("Unable to find address for Device " + String(index));
    return "Unknown";
  }

  String sensorID = "";
  for (uint8_t i = 0; i < 8; i++) {
    if (sensorAddress[i] < 16) sensorID += "0";
    sensorID += String(sensorAddress[i], HEX);
  }

  return sensorID;
}
