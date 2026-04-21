-- =================================================================
-- TeknikLED v0.1.3 -> v0.1.4 Migration
-- Slider sistemi + urun gorselleri gercek Wix fotograflariyla guncellenir
-- =================================================================

SET NAMES utf8mb4;

-- 1. _migrations tablosu (updater takibi icin)
CREATE TABLE IF NOT EXISTS `_migrations` (
    `ad` VARCHAR(100) NOT NULL PRIMARY KEY,
    `uygulama_tarihi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sonuc` VARCHAR(20) NOT NULL DEFAULT 'basarili'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- v0.1.3'u zaten uygulanmis olarak isaretle (eski kurulumlarda kayit yoksa)
INSERT IGNORE INTO `_migrations` (`ad`, `sonuc`) VALUES ('v0.1.3.sql', 'basarili');

-- 2. slider tablosu
CREATE TABLE IF NOT EXISTS `slider` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `baslik_tr` VARCHAR(200) NULL,
    `baslik_en` VARCHAR(200) NULL,
    `baslik_ar` VARCHAR(200) NULL,
    `aciklama_tr` TEXT NULL,
    `aciklama_en` TEXT NULL,
    `aciklama_ar` TEXT NULL,
    `gorsel` VARCHAR(255) NOT NULL,
    `buton_metin_tr` VARCHAR(100) NULL,
    `buton_metin_en` VARCHAR(100) NULL,
    `buton_metin_ar` VARCHAR(100) NULL,
    `buton_url` VARCHAR(255) NULL,
    `sira` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sira_aktif` (`sira`, `aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Slider seed (4 kayit - sadece tablo bossa ekle)
INSERT INTO `slider` (`baslik_tr`, `baslik_en`, `baslik_ar`, `aciklama_tr`, `aciklama_en`, `aciklama_ar`, `gorsel`, `buton_metin_tr`, `buton_metin_en`, `buton_metin_ar`, `buton_url`, `sira`, `aktif`)
SELECT * FROM (
  SELECT 'Modüler Karkas Sistemi' AS b_tr, 'Modular Frame System' AS b_en, 'نظام الإطار المعياري' AS b_ar,
         'Tasarım tescilli, 1.20 mm galvaniz sac, dünyada bir ilk modüler karkas sistemi.' AS a_tr,
         'Design-registered, 1.20 mm galvanized steel, a world-first modular frame system.' AS a_en,
         'إطار معياري مسجل التصميم، فولاذ مجلفن 1.20 ملم.' AS a_ar,
         'slider/01-karkas.png' AS g, 'İncele' AS bt_tr, 'Explore' AS bt_en, 'استكشف' AS bt_ar,
         '/kategori/moduler-karkas' AS burl, 1 AS s, 1 AS ak
  UNION ALL SELECT 'LED Masalar', 'LED Tables', 'طاولات LED',
         '96x192, 96x224, 96x256, 96x288 ve özel ölçülerde P1.86/P2.5 LED masa üretimi.',
         '96x192, 96x224, 96x256, 96x288 and custom dimensions with P1.86/P2.5 LED tables.',
         'طاولات LED بمقاسات مخصصة.',
         'slider/02-led-masa.png', 'Modelleri Gör', 'View Models', 'عرض النماذج', '/kategori/led-masa', 2, 1
  UNION ALL SELECT 'LED Kürsü', 'LED Podium', 'منبر LED',
         'P1.86 ve P2.5 LED modül seçenekli dijital kürsü. Konferans, toplantı ve fuar salonları için.',
         'Digital podium with P1.86 and P2.5 LED module options.',
         'منبر رقمي.',
         'slider/03-led-kursu.png', 'Teklif Al', 'Get Quote', 'اطلب عرضاً', '/teklif', 3, 1
  UNION ALL SELECT 'Metal Kürsü', 'Metal Podium', 'منبر معدني',
         'Şık, dayanıklı, deri kaplama. Tasarım tescilli metal kürsü üretimi.',
         'Sleek, durable, leather-coated. Design-registered metal podium.',
         'منبر معدني متين.',
         'slider/04-metal-kursu.png', 'Detaylar', 'Details', 'التفاصيل', '/urun/metal-kursu-standart', 4, 1
) AS yeni
WHERE NOT EXISTS (SELECT 1 FROM `slider` LIMIT 1);

-- 4. URUN VE KATEGORI GORSELLERINI GERCEK WIX FOTOGRAFLARI ILE GUNCELLE
-- (v0.1.3'te placeholder render'lar vardı, simdi gercek urun fotograflari)

UPDATE `urunler` SET `ana_gorsel` = 'urunler/modkarkas-standart.png' WHERE `slug` = 'moduler-karkas-standart';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/modkarkas-ozel.png'     WHERE `slug` = 'moduler-karkas-ozel';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledmasa-96x192.png'     WHERE `slug` = 'led-masa-96x192';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledmasa-96x224.png'     WHERE `slug` = 'led-masa-96x224';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledmasa-96x256.png'     WHERE `slug` = 'led-masa-96x256';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledmasa-96x288.png'     WHERE `slug` = 'led-masa-96x288';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledkursu-p186.png'      WHERE `slug` = 'led-kursu-p186';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledkursu-p25.png'       WHERE `slug` = 'led-kursu-p25';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledposter-tek.png'      WHERE `slug` = 'led-poster-tek-tarafli';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/ledposter-cift.png'     WHERE `slug` = 'led-poster-cift-tarafli';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/cnckasa-9696.png'       WHERE `slug` = 'cnc-kasa-96x96x8';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/cnckasa-128.png'        WHERE `slug` = 'cnc-kasa-128x128';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/cnckasa-ozel.png'       WHERE `slug` = 'cnc-kasa-ozel';
UPDATE `urunler` SET `ana_gorsel` = 'urunler/metalkursu-std.png'     WHERE `slug` = 'metal-kursu-standart';

-- Kategori kapaklari (gercek banner'lar)
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/moduler-karkas.png' WHERE `slug` = 'moduler-karkas';
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/led-masa.png'       WHERE `slug` = 'led-masa';
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/led-kursu.png'      WHERE `slug` = 'led-kursu';
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/led-poster.png'     WHERE `slug` = 'led-poster';
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/cnc-kasa.png'       WHERE `slug` = 'cnc-kasa';
UPDATE `kategoriler` SET `gorsel` = 'kategoriler/metal-kursu.png'    WHERE `slug` = 'metal-kursu';

-- Referans gorsellerini .jpg uzantilarina guncelle
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/konferans-salonu.jpg' WHERE `slug` = 'konferans-salonu-projesi';
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/avm-led-duvar.jpg'    WHERE `slug` = 'avm-led-duvar';
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/fuar-standi.jpg'      WHERE `slug` = 'fuar-standi';
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/magaza-vitrin.png'    WHERE `slug` = 'magaza-vitrin';
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/stadyum-skorbord.jpg' WHERE `slug` = 'stadyum-skorbord';
UPDATE `referanslar` SET `ana_gorsel` = 'referanslar/belediye-toplanti.jpg' WHERE `slug` = 'belediye-toplanti';
