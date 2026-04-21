<?php
/**
 * TeknikLED Yapilandirma Dosyasi
 * -----------------------------------------------
 * Bu dosyayi config.php olarak kopyalayin ve kendi degerlerinizi girin.
 * DIKKAT: Guncelleme ZIP paketleri config.php'yi ICERMEZ, asla ezilmez.
 */

declare(strict_types=1);

// =========================================================
// VERITABANI
// =========================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'kullanici_teknikled');
define('DB_USER', 'kullanici_tlusr');
define('DB_PASS', 'gizli_sifre_buraya');
define('DB_CHARSET', 'utf8mb4');

// =========================================================
// SITE
// =========================================================
define('SITE_URL', 'https://teknikled.com');      // Sonunda / olmayacak
define('SITE_NAME', 'TeknikLED');
define('SITE_EMAIL', 'info@teknikled.com');
define('DEFAULT_LANG', 'tr');                     // tr | en | ar

// =========================================================
// GUVENLIK
// =========================================================
// 32 karakterlik rastgele bir dize olustur: bin2hex(random_bytes(16))
define('APP_SECRET', 'BURAYA_32_KARAKTERLIK_RASTGELE_DIZE_GELSIN');

// Admin panel URL segmenti (yonetim.php dosyasini yeniden adlandirmak icin degil,
// sadece giris kilidi icin ek anahtar)
define('ADMIN_KEY', 'teknikled_yonetim_2026');

// =========================================================
// E-POSTA (SMTP)
// =========================================================
define('SMTP_HOST', 'smtp.teknikled.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'info@teknikled.com');
define('SMTP_PASS', 'eposta_sifresi');
define('SMTP_FROM', 'info@teknikled.com');
define('SMTP_FROM_NAME', 'TeknikLED');
define('SMTP_SECURE', 'tls');                     // tls | ssl | ''
define('MAIL_TO_SALES', 'satis@teknikled.com');   // Teklif bildirimleri

// =========================================================
// ORTAM
// =========================================================
define('DEBUG', false);                            // Canlida daima false
define('TIMEZONE', 'Europe/Istanbul');
define('UPLOAD_MAX_MB', 10);

// =========================================================
// GUNCELLEME SISTEMI
// =========================================================
define('UPDATE_CHECK_ENABLED', true);
define('UPDATE_GITHUB_REPO', 'codegatr/teknikled');

// Ortam ayarla
date_default_timezone_set(TIMEZONE);
mb_internal_encoding('UTF-8');
