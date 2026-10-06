# Mamamat Laravel Backend API

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue.style=for-the-badge)

Mamamat mobil uygulamasının (React Native Expo) kullandığı RESTful API backend servisi. Akıllı sokak hayvanı mama kaplarını takip eder, bakiyeyi yönetir, SMS doğrulaması yapar ve anlık bildirim gönderir.

---

## Özellikler

* Kullanıcılar telefon numarasıyla giriş yapar. SMS şirketi üzerinden tek kullanımlık şifre (OTP) gönderilir, kod doğrulanınca üyelik otomatik oluşturulur.
* Haritadaki mama kaplarının canlı doluluk oranı ve konumu (enlem/boylam) görüntülenir.
* Kullanıcılar bakiyelerinden düşerek mama kabına takviye yapabilir.
* Geçmiş takviyeler tarih, adet ve tutar bilgisiyle listelenir.
* Expo Push ile tek bir cihaza ya da tüm kullanıcılara bildirim gönderilir, okundu durumu takip edilir.
* Kullanıcılar arkadaşlarına SMS ile davet yollar, davetler kayıt altında tutulur.

---

## Mimari ve teknolojiler

* Framework: Laravel 10
* Dil: PHP 8.1+
* Veritabanı: MySQL / MariaDB
* Dış servisler: SMS API (doğrulama ve davet SMS'leri), Expo Push API (mobil bildirimler)
* Service layer: `SmsService` ve `PushNotificationService` dış entegrasyonları ayrı tutar.
* Modeller (PSR-12): `User`, `Feeder`, `GecmisKullanimlar`, `NotifiId`, `Notification`, `ArkadasiniDavetEt`
* Helper layer: Geriye dönük %100 uyumlu `fHelper`

---

## Proje dizin yapısı

```text
mamamat-laravel-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controller katmanı
│   │   │   ├── ApiController.php
│   │   │   ├── ArkadasiniDavetEtController.php
│   │   │   ├── FeederController.php
│   │   │   ├── GecmisKullanimlarController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── UserController.php
│   │   │   └── WalletController.php
│   │   └── Helper/
│   │       └── fHelper.php       # Static helper sarmalayıcısı
│   ├── Mail/
│   │   └── EmailVerificationMail.php
│   ├── Models/                   # Eloquent Veritabanı Modelleri
│   │   ├── ArkadasiniDavetEt.php
│   │   ├── Feeder.php
│   │   ├── GecmisKullanimlar.php
│   │   ├── NotifiId.php
│   │   ├── Notification.php
│   │   └── User.php
│   └── Services/                 # Servis Katmanı
│       ├── PushNotificationService.php
│       └── SmsService.php
├── config/                       # Uygulama ve servis konfigürasyonları
│   └── services.php
├── database/                     # Migrations & Seeders
└── routes/
    └── api.php                   # API Route tanımları
```

---

## Kurulum ve çalıştırma

### 1. Gereksinimler
* PHP >= 8.1
* Composer
* MySQL / MariaDB

### 2. Depoyu klonlayın ve bağımlılıkları yükleyin
```bash
git clone https://github.com/fatihates0/mamamat-laravel-backend.git
cd mamamat-laravel-backend
composer install
```

### 3. Çevre değişkenlerini ayarlayın
`.env.example` dosyasını `.env` olarak kopyalayın:
```bash
cp .env.example .env
php artisan key:generate
```

`.env` dosyasında veritabanı ayarlarını girin:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mamamat_db
DB_USERNAME=root
DB_PASSWORD=
```

SMS servisi ayarları. Geliştirme ortamında SMS gönderimini kapatmak için `SMS_ENABLED=false` yapın:
```env
SMS_ENABLED=true
VATAN_SMS_CUSTOMER_NO=42215
VATAN_SMS_USERNAME=905078721533
VATAN_SMS_PASSWORD=your_password
VATAN_SMS_ORIGINATOR=8505902979
```

### 4. Migration'ları çalıştırın
```bash
php artisan migrate
```

### 5. Sunucuyu başlatın
```bash
php artisan serve
```
Uygulama varsayılan olarak `http://127.0.0.1:8000` adresinde çalışır.

---

## API endpoint dokümantasyonu

### Kullanıcı işlemleri (`/api/create/user`, `/api/get/user`, `/api/update/user`)

| Method | Endpoint | Açıklama |
| :--- | :--- | :--- |
| `POST` | `/api/create/user/getOrCreateUser` | Telefon numarasına SMS OTP kodu gönderir, kullanıcı yoksa oluşturur. |
| `POST` | `/api/create/user/smsVerification` | SMS doğrulama kodunu kontrol eder. |
| `POST` | `/api/get/user/data` | Kullanıcının profil bilgilerini getirir. |
| `POST` | `/api/update/user/updateUserData` | Kullanıcı bilgilerini günceller. |

---

### Mama kabı işlemleri (`/api/feeder`)

| Method | Endpoint | Açıklama |
| :--- | :--- | :--- |
| `POST` | `/api/feeder/getFeeders` | Aktif tüm mama kaplarının konum ve doluluk bilgilerini döndürür. |
| `POST` | `/api/feeder/getFeederDetail` | Belirli bir mama kabının detay verisini getirir. |
| `POST` | `/api/feeder/takviyeYap` | Kullanıcı bakiyesinden düşerek mama kabına takviye yapar. |

---

### Cüzdan işlemleri (`/api/wallet`)

`POST /api/wallet/bakiyeYukle` kullanıcı hesabına bakiye tanımlar.

---

### Kullanım geçmişi (`/api/get/user`, `/api/create/user`)

| Method | Endpoint | Açıklama |
| :--- | :--- | :--- |
| `POST` | `/api/create/user/kullanimOlustur` | Geçmiş kullanım kaydı ekler. |
| `POST` | `/api/get/user/gecmisKullanimlar` | Kullanıcının geçmiş mama takviyelerini listeler. |

---

### Bildirim işlemleri (`/api/saveNotifiID`, `/api/create/user`, `/api/get/user`, `/api/update/user`)

| Method | Endpoint | Açıklama |
| :--- | :--- | :--- |
| `POST` | `/api/saveNotifiID` | Kullanıcının Expo Push Token cihaz kimliğini kaydeder. |
| `POST` | `/api/create/user/sendAllUserNotification` | Kayıtlı tüm kullanıcılara toplu push bildirim gönderir. |
| `POST` | `/api/get/user/notifications` | Kullanıcının okunmamış bildirimlerini listeler. |
| `POST` | `/api/update/user/readNotification` | Bildirimleri okundu (`status: 1`) olarak işaretler. |

---

### Arkadaşını davet et (`/api/create/user`)

`POST /api/create/user/arkadasiniDavetEt` belirtilen telefon numarasına davet SMS'i gönderir.

---

## Kontrol komutları

Geliştirme sırasında yapıyı şu Laravel komutlarıyla kontrol edebilirsiniz:

```bash
# Route listesi kontrolü
php artisan route:list

# Class autoload yenileme
composer dump-autoload
```

---

## Lisans

Bu proje [MIT Lisansı](LICENSE) ile lisanslanmıştır.