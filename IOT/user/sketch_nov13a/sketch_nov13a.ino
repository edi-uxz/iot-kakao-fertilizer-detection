#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include "DHT.h" // Wajib install library "DHT sensor library" di Library Manager

// ======================================================
// 1. KONFIGURASI PIN SENSOR
// ======================================================
#define DHTPIN D4          // Pin DATA sensor DHT11/22 di hubungkan ke D4
#define DHTTYPE DHT11      // Ganti jadi DHT22 jika kamu pakai yang warna putih
#define PH_PIN A0          // Sensor pH dihubungkan ke pin Analog A0

DHT dht(DHTPIN, DHTTYPE);

// ======================================================
// 2. KONFIGURASI WIFI & SERVER
// ======================================================
const char* ssid = "realme C30s";
const char* password = "987654321";

const char* local_host = "192.168.72.214"; 
String local_path = "/jurnal_project/kirim_data.php";

const char* ts_host = "api.thingspeak.com";
String ts_api_key = "9A79PYWMS8PFQR02"; 

// ======================================================

void setup() {
  Serial.begin(115200);
  dht.begin(); // Mengaktifkan sensor DHT
  
  Serial.print("\nMenghubungkan ke ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\n✅ WiFi Terhubung!");
}

void loop() {
  // --- BACA DATA ASLI DARI SENSOR ---
  float suhu = dht.readTemperature(); 
  float lembap = dht.readHumidity(); 
  
  // Baca pH (Nilai analog 0-1024 dikonversi ke skala 0-14)
  int nilaiAnalogPH = analogRead(PH_PIN);
  float ph = nilaiAnalogPH * (14.0 / 1024.0); 

  // Validasi: Jika sensor DHT lepas, jangan kirim data rusak (NaN)
  if (isnan(suhu) || isnan(lembap)) {
    Serial.println("⚠️ Gagal baca sensor DHT! Periksa kabel.");
    return; // Skip pengiriman jika sensor error
  }

  Serial.print("\nData Lapangan -> Suhu: "); Serial.print(suhu);
  Serial.print("C, Lembap: "); Serial.print(lembap);
  Serial.print("%, pH: "); Serial.println(ph);

  if (WiFi.status() == WL_CONNECTED) {
    
    // --- 1. KIRIM KE LARAGON ---
    WiFiClient clientLocal;
    if (clientLocal.connect(local_host, 80)) {
      String url = local_path + "?suhu=" + String(suhu) + 
                                "&kelembapan=" + String(lembap) + 
                                "&ph=" + String(ph);

      clientLocal.print(String("GET ") + url + " HTTP/1.1\r\n" +
                        "Host: " + String(local_host) + "\r\n" +
                        "User-Agent: ESP8266\r\n" +
                        "Connection: close\r\n\r\n");
      
      clientLocal.stop();
      Serial.println("✅ OK: Laragon");
    }

    delay(1000); 

    // --- 2. KIRIM KE THINGSPEAK ---
    WiFiClient clientTS;
    if (clientTS.connect(ts_host, 80)) {
      String ts_url = "/update?api_key=" + ts_api_key + 
                      "&field1=" + String(suhu) + 
                      "&field2=" + String(lembap) + 
                      "&field3=" + String(ph);

      clientTS.print(String("GET ") + ts_url + " HTTP/1.1\r\n" +
                     "Host: " + String(ts_host) + "\r\n" +
                     "User-Agent: ESP8266\r\n" +
                     "Connection: close\r\n\r\n");
      
      clientTS.stop();
      Serial.println("✅ OK: ThingSpeak");
    }
  }

  Serial.println("Menunggu 5 detik...");
  delay(5000); 
}