-- =================================================================
-- TeknikLED v0.1.0 - MySQL Schema
-- PHP 8.3+ / MySQL 5.7+ / utf8mb4
-- CODEGA - codega.com.tr
-- =================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------
-- Yoneticiler
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `yoneticiler`;
CREATE TABLE `yoneticiler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad_soyad` VARCHAR(100) NOT NULL,
    `kullanici_adi` VARCHAR(50) NOT NULL UNIQUE,
    `eposta` VARCHAR(120) NOT NULL UNIQUE,
    `sifre_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('super','editor') NOT NULL DEFAULT 'editor',
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `son_giris` DATETIME NULL,
    `son_ip` VARCHAR(45) NULL,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Kategoriler (cok dilli)
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `kategoriler`;
CREATE TABLE `kategoriler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `ad_tr` VARCHAR(150) NOT NULL,
    `ad_en` VARCHAR(150) NULL,
    `ad_ar` VARCHAR(150) NULL,
    `aciklama_tr` TEXT NULL,
    `aciklama_en` TEXT NULL,
    `aciklama_ar` TEXT NULL,
    `ikon` VARCHAR(50) NULL,
    `gorsel` VARCHAR(255) NULL,
    `sira` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `seo_baslik_tr` VARCHAR(200) NULL,
    `seo_baslik_en` VARCHAR(200) NULL,
    `seo_baslik_ar` VARCHAR(200) NULL,
    `seo_aciklama_tr` VARCHAR(300) NULL,
    `seo_aciklama_en` VARCHAR(300) NULL,
    `seo_aciklama_ar` VARCHAR(300) NULL,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_aktif_sira` (`aktif`, `sira`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Urunler (cok dilli)
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `urunler`;
CREATE TABLE `urunler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kategori_id` INT UNSIGNED NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `urun_kodu` VARCHAR(50) NULL,
    `ad_tr` VARCHAR(200) NOT NULL,
    `ad_en` VARCHAR(200) NULL,
    `ad_ar` VARCHAR(200) NULL,
    `ozet_tr` VARCHAR(500) NULL,
    `ozet_en` VARCHAR(500) NULL,
    `ozet_ar` VARCHAR(500) NULL,
    `aciklama_tr` LONGTEXT NULL,
    `aciklama_en` LONGTEXT NULL,
    `aciklama_ar` LONGTEXT NULL,
    `ozellikler_tr` LONGTEXT NULL,    -- JSON: [{baslik, deger}]
    `ozellikler_en` LONGTEXT NULL,
    `ozellikler_ar` LONGTEXT NULL,
    `ana_gorsel` VARCHAR(255) NULL,
    `galeri` LONGTEXT NULL,            -- JSON array of file paths
    `piksel` VARCHAR(20) NULL,         -- P1.86, P2.5, P3 vb.
    `olcu` VARCHAR(100) NULL,
    `agirlik` VARCHAR(50) NULL,
    `vitrin` TINYINT(1) NOT NULL DEFAULT 0,
    `yeni` TINYINT(1) NOT NULL DEFAULT 0,
    `sira` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `goruntulenme` INT UNSIGNED NOT NULL DEFAULT 0,
    `seo_baslik_tr` VARCHAR(200) NULL,
    `seo_baslik_en` VARCHAR(200) NULL,
    `seo_baslik_ar` VARCHAR(200) NULL,
    `seo_aciklama_tr` VARCHAR(300) NULL,
    `seo_aciklama_en` VARCHAR(300) NULL,
    `seo_aciklama_ar` VARCHAR(300) NULL,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`kategori_id`) REFERENCES `kategoriler`(`id`) ON DELETE RESTRICT,
    INDEX `idx_aktif_vitrin` (`aktif`, `vitrin`),
    INDEX `idx_kategori` (`kategori_id`, `aktif`, `sira`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Referanslar (projeler)
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `referanslar`;
CREATE TABLE `referanslar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `musteri_tr` VARCHAR(200) NOT NULL,
    `musteri_en` VARCHAR(200) NULL,
    `musteri_ar` VARCHAR(200) NULL,
    `lokasyon` VARCHAR(150) NULL,
    `proje_tarihi` DATE NULL,
    `sektor` VARCHAR(100) NULL,
    `aciklama_tr` TEXT NULL,
    `aciklama_en` TEXT NULL,
    `aciklama_ar` TEXT NULL,
    `ana_gorsel` VARCHAR(255) NULL,
    `galeri` LONGTEXT NULL,
    `vitrin` TINYINT(1) NOT NULL DEFAULT 0,
    `sira` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_aktif_sira` (`aktif`, `sira`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Sayfalar (CMS - hakkimizda, gizlilik vs.)
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `sayfalar`;
CREATE TABLE `sayfalar` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `baslik_tr` VARCHAR(200) NOT NULL,
    `baslik_en` VARCHAR(200) NULL,
    `baslik_ar` VARCHAR(200) NULL,
    `icerik_tr` LONGTEXT NULL,
    `icerik_en` LONGTEXT NULL,
    `icerik_ar` LONGTEXT NULL,
    `menude` TINYINT(1) NOT NULL DEFAULT 0,
    `footer` TINYINT(1) NOT NULL DEFAULT 0,
    `sira` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `seo_baslik_tr` VARCHAR(200) NULL,
    `seo_baslik_en` VARCHAR(200) NULL,
    `seo_baslik_ar` VARCHAR(200) NULL,
    `seo_aciklama_tr` VARCHAR(300) NULL,
    `seo_aciklama_en` VARCHAR(300) NULL,
    `seo_aciklama_ar` VARCHAR(300) NULL,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Teklif talepleri
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `teklifler`;
CREATE TABLE `teklifler` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `urun_id` INT UNSIGNED NULL,
    `ad_soyad` VARCHAR(150) NOT NULL,
    `firma` VARCHAR(200) NULL,
    `eposta` VARCHAR(150) NOT NULL,
    `telefon` VARCHAR(30) NOT NULL,
    `sehir` VARCHAR(100) NULL,
    `konu` VARCHAR(200) NULL,
    `mesaj` TEXT NOT NULL,
    `olcu_bilgisi` VARCHAR(200) NULL,
    `adet` INT UNSIGNED NULL,
    `durum` ENUM('yeni','incelendi','teklif_verildi','kazanildi','kaybedildi','iptal') NOT NULL DEFAULT 'yeni',
    `notlar` TEXT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `dil` VARCHAR(5) NOT NULL DEFAULT 'tr',
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`urun_id`) REFERENCES `urunler`(`id`) ON DELETE SET NULL,
    INDEX `idx_durum` (`durum`, `olusturma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Iletisim mesajlari
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `iletisim_mesajlari`;
CREATE TABLE `iletisim_mesajlari` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ad_soyad` VARCHAR(150) NOT NULL,
    `eposta` VARCHAR(150) NOT NULL,
    `telefon` VARCHAR(30) NULL,
    `konu` VARCHAR(200) NULL,
    `mesaj` TEXT NOT NULL,
    `okundu` TINYINT(1) NOT NULL DEFAULT 0,
    `ip` VARCHAR(45) NULL,
    `dil` VARCHAR(5) NOT NULL DEFAULT 'tr',
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_okundu` (`okundu`, `olusturma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Ayarlar (anahtar-deger)
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `ayarlar`;
CREATE TABLE `ayarlar` (
    `anahtar` VARCHAR(100) NOT NULL PRIMARY KEY,
    `deger` LONGTEXT NULL,
    `grup` VARCHAR(50) NOT NULL DEFAULT 'genel',
    `tip` ENUM('text','textarea','number','email','url','bool','json','html') NOT NULL DEFAULT 'text',
    `aciklama` VARCHAR(255) NULL,
    `guncelleme` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------
-- Log
-- -----------------------------------------------------------------
DROP TABLE IF EXISTS `log_kayitlari`;
CREATE TABLE `log_kayitlari` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `yonetici_id` INT UNSIGNED NULL,
    `olay` VARCHAR(100) NOT NULL,
    `detay` TEXT NULL,
    `ip` VARCHAR(45) NULL,
    `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_yonetici` (`yonetici_id`),
    INDEX `idx_olay` (`olay`, `olusturma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `slider` (
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

CREATE TABLE IF NOT EXISTS `_migrations` (
    `ad` VARCHAR(100) NOT NULL PRIMARY KEY,
    `uygulama_tarihi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sonuc` VARCHAR(20) NOT NULL DEFAULT 'basarili'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- =================================================================
-- BASLANGIC VERILERI
-- =================================================================

-- Kategoriler (6 ana kategori)
INSERT INTO `kategoriler` (`slug`, `ad_tr`, `ad_en`, `ad_ar`, `aciklama_tr`, `aciklama_en`, `aciklama_ar`, `ikon`, `gorsel`, `sira`) VALUES
('moduler-karkas', 'Modüler Karkas Sistemi', 'Modular Frame System', 'نظام الإطار المعياري', '1.20 mm galvaniz sac, montaja hazır profiller, tasarım tescilli karkas sistemi. Her ölçüde LED panel uygulamasına uyum sağlar.', 'Design-registered frame system with 1.20 mm galvanized steel and pre-assembled profiles. Compatible with every LED panel size.', 'نظام إطار مسجل التصميم مع الفولاذ المجلفن 1.20 ملم والمقاطع المجمعة مسبقاً. متوافق مع جميع أحجام لوحات LED.', 'grid', 'kategoriler/moduler-karkas.png', 1),
('led-masa', 'LED Masa', 'LED Table', 'طاولة LED', 'Özel ölçü LED masa imalatı. 96x192, 96x224, 96x256, 96x288 ve istediğiniz ebatlarda P1.86/P2.5 seçenekleriyle.', 'Custom-size LED table manufacturing. 96x192, 96x224, 96x256, 96x288 and any dimension with P1.86/P2.5 options.', 'تصنيع طاولات LED بمقاسات مخصصة. 96x192، 96x224، 96x256، 96x288 وأي أبعاد مع خيارات P1.86/P2.5.', 'layout', 'kategoriler/led-masa.png', 2),
('led-kursu', 'LED Kürsü', 'LED Podium', 'منبر LED', 'P1.86 ve P2.5 LED modül seçenekli dijital kürsü. Konferans, toplantı ve fuar salonları için.', 'Digital podium with P1.86 and P2.5 LED module options. For conference, meeting and fair halls.', 'منبر رقمي مع خيارات وحدة LED P1.86 و P2.5. لقاعات المؤتمرات والاجتماعات والمعارض.', 'award', 'kategoriler/led-kursu.png', 3),
('led-poster', 'LED Poster Kasa', 'LED Poster Case', 'إطار ملصق LED', 'Vitrin ve showroomlar için ultra ince LED poster ekran kasaları. Tek ve çift taraflı seçenekler.', 'Ultra-thin LED poster display cases for showcases and showrooms. Single and double-sided options.', 'صناديق عرض ملصقات LED فائقة النحافة للعروض وصالات العرض. خيارات أحادية وثنائية الوجه.', 'image', 'kategoriler/led-poster.png', 4),
('cnc-kasa', 'CNC LED Modül Kasaları', 'CNC LED Module Cases', 'صناديق وحدات LED CNC', 'Universal P10/P8/P5/P4/P3/P2.5 RGB panel uyumlu CNC kasalar. DKP sac, fırın boyalı.', 'CNC cases compatible with universal P10/P8/P5/P4/P3/P2.5 RGB panels. DKP steel, powder-coated.', 'صناديق CNC متوافقة مع لوحات RGB العالمية. فولاذ DKP، طلاء بالفرن.', 'box', 'kategoriler/cnc-kasa.png', 5),
('metal-kursu', 'Metal Kürsü', 'Metal Podium', 'منبر معدني', 'Konferans, toplantı ve seminer salonları için dayanıklı metal kürsü üretimi.', 'Durable metal podium manufacturing for conference, meeting and seminar halls.', 'تصنيع منبر معدني متين.', 'award', 'kategoriler/metal-kursu.png', 6);

-- Urunler (14 urun)
INSERT INTO `urunler` (`kategori_id`, `slug`, `urun_kodu`, `ad_tr`, `ad_en`, `ad_ar`, `ozet_tr`, `ozet_en`, `ozet_ar`, `aciklama_tr`, `ozellikler_tr`, `ana_gorsel`, `piksel`, `olcu`, `agirlik`, `vitrin`, `yeni`, `sira`, `aktif`) VALUES

-- Modüler Karkas
(1, 'moduler-karkas-standart', 'TL-MK-001', 'Modüler Karkas Standart', 'Standard Modular Frame', 'إطار معياري قياسي',
 '1.20 mm galvaniz sac, tasarım tescilli modüler karkas. Kolay montaj, şık tasarım.',
 'Design-registered modular frame made of 1.20 mm galvanized steel.',
 'إطار معياري مسجل التصميم.',
 '<p>TeknikLed Modüler Karkas Sistemi, iç mekân LED panel uygulamalarında montaj sürecini hızlandırmak, kusursuz hizalama sağlamak ve uzun ömürlü bir taşıyıcı altyapı oluşturmak amacıyla geliştirilmiştir. <strong>Tamamı kendi üretimimizdir.</strong></p><h3>Neden TeknikLED Modüler Karkas?</h3><ul><li>⚙️ Modüler yapı, her ölçüde LED panele uyum</li><li>⏱️ Hızlı ve pratik montaj, işçilik tasarrufu</li><li>📐 Mükemmel hizalama, pürüzsüz yüzey</li><li>🧱 Yüksek taşıma kapasitesi, uzun ömür</li><li>🔧 Bakım ve panel değişimi kolaylığı</li></ul>',
 '[{"baslik":"Malzeme","deger":"1.20 mm Galvaniz Sac"},{"baslik":"Tasarım","deger":"Tescilli Modüler Sistem"},{"baslik":"Montaj","deger":"Hazır Profil"},{"baslik":"Uygulama","deger":"Her ölçüde iç mekân LED panel"},{"baslik":"Garanti","deger":"2 Yıl Yapısal"},{"baslik":"Üretim","deger":"%100 Yerli"}]',
 'urunler/modkarkas-standart.png', NULL, 'Modüler / Değişken', 'Ölçüye bağlı', 1, 1, 1, 1),

(1, 'moduler-karkas-ozel', 'TL-MK-002', 'Modüler Karkas Özel Ölçü', 'Custom-Size Modular Frame', 'إطار معياري مخصص',
 'Projenizin ölçülerine özel üretim, tasarım tescilli karkas sistemi.',
 'Custom production for your project dimensions.',
 'إنتاج مخصص.',
 '<p>Her ölçü ve geometrik şekle uygun özel üretim modüler karkas sistemleri. Mimariye özel projeler için tasarlanır ve üretilir. Ölçünüzü paylaşın, karkas tasarımımızı 48 saat içinde sunalım.</p>',
 '[{"baslik":"Üretim","deger":"Projeye Özel"},{"baslik":"Teslim","deger":"7-14 gün"},{"baslik":"Malzeme","deger":"1.20 / 1.50 / 2.00 mm"},{"baslik":"Destek","deger":"Keşif ve danışmanlık dahil"}]',
 'urunler/modkarkas-ozel.png', NULL, 'Özel', 'Ölçüye bağlı', 0, 0, 2, 1),

-- LED Masa
(2, 'led-masa-96x192', 'TL-LM-192', 'LED Masa 96x192', 'LED Table 96x192', 'طاولة LED 96x192',
 '96x192 cm özel üretim LED masa. P1.86 ve P2.5 seçenekleri.',
 '96x192 cm custom-made LED table.',
 'طاولة LED مصنوعة خصيصاً.',
 '<p>Kompakt ölçüleriyle dar sunum platformları, podyum önü ve küçük konferans salonları için ideal LED masa çözümü. <strong>Kendi imalatımızdır.</strong></p>',
 '[{"baslik":"Ölçü","deger":"96 x 192 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~450W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x192.png', 'P1.86 / P2.5', '96x192 cm', '~60 kg', 1, 0, 1, 1),

(2, 'led-masa-96x224', 'TL-LM-224', 'LED Masa 96x224', 'LED Table 96x224', 'طاولة LED 96x224',
 '96x224 cm standart LED masa. Orta ölçekli konferans salonları için.',
 '96x224 cm standard LED table.',
 'طاولة LED قياسية.',
 '<p>Konferans salonları ve toplantı alanları için özel tasarım LED masa. Tamamı <strong>kendi tesislerimizde</strong> üretilmektedir.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 224 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~520W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x224.png', 'P1.86 / P2.5', '96x224 cm', '~70 kg', 1, 0, 2, 1),

(2, 'led-masa-96x256', 'TL-LM-256', 'LED Masa 96x256', 'LED Table 96x256', 'طاولة LED 96x256',
 '96x256 cm geniş format LED masa. Büyük konferans salonları için.',
 '96x256 cm wide-format LED table.',
 'طاولة LED بتنسيق واسع.',
 '<p>Geniş format konferans ve toplantı salonları için ideal LED masa çözümü. Protokol masası ve yönetici sunumları için tercih edilir.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 256 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~600W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x256.png', 'P1.86 / P2.5', '96x256 cm', '~80 kg', 1, 0, 3, 1),

(2, 'led-masa-96x288', 'TL-LM-288', 'LED Masa 96x288', 'LED Table 96x288', 'طاولة LED 96x288',
 '96x288 cm büyük format LED masa. Prestijli etkinlikler için.',
 '96x288 cm large-format LED table.',
 'طاولة LED كبيرة.',
 '<p>En büyük standart ölçümüz. Prestijli etkinlikler, büyük lansman alanları ve sahne önü kullanımlar için.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 288 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Enerji","deger":"~680W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x288.png', 'P1.86 / P2.5', '96x288 cm', '~90 kg', 1, 0, 4, 1),

-- LED Kürsü
(3, 'led-kursu-p186', 'TL-LK-186', 'LED Kürsü P1.86', 'LED Podium P1.86', 'منبر LED P1.86',
 'P1.86 LED modüllü yüksek çözünürlüklü dijital kürsü.',
 'High-resolution digital podium with P1.86 LED module.',
 'منبر رقمي عالي الدقة.',
 '<p>Premium konferans salonları, büyük toplantı salonları ve prestijli etkinlikler için dijital sunum kürsüsü. P1.86 mm piksel aralığı ile yakın mesafeden dahi pürüzsüz görüntü.</p><h3>Kullanım Alanları</h3><ul><li>🎓 Okul, Üniversite Konferans Salonları</li><li>🏛 Belediye Toplantı Salonları</li><li>🏢 Fuar ve Organizasyon Firmaları</li><li>🎤 Kurumsal lansman etkinlikleri</li></ul>',
 '[{"baslik":"Piksel","deger":"P1.86 mm"},{"baslik":"LED Tipi","deger":"SMD Nationstar/Kinglight"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Görüş Açısı","deger":"160°/140°"},{"baslik":"Kontrol","deger":"Linsn / Novastar"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledkursu-p186.png', 'P1.86', 'Standart / Özel', '~45 kg', 1, 1, 1, 1),

(3, 'led-kursu-p25', 'TL-LK-250', 'LED Kürsü P2.5', 'LED Podium P2.5', 'منبر LED P2.5',
 'P2.5 LED modüllü ekonomik dijital kürsü. Standart konferans salonları için.',
 'Economical digital podium with P2.5 LED module.',
 'منبر رقمي اقتصادي.',
 '<p>Standart konferans, toplantı ve fuar salonları için ekonomik ve dayanıklı dijital kürsü. En yaygın tercih edilen model.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"LED Tipi","deger":"SMD Full Color RGB"},{"baslik":"Parlaklık","deger":"≥ 700 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Piksel Yoğunluğu","deger":"160.000 dots/m²"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledkursu-p25.png', 'P2.5', 'Standart / Özel', '~42 kg', 1, 0, 2, 1),

-- LED Poster
(4, 'led-poster-tek-tarafli', 'TL-LP-001', 'LED Poster Tek Taraflı', 'Single-Sided LED Poster', 'إطار ملصق LED أحادي',
 'Ultra ince vitrin LED poster kasası. Şık ayaklı tasarım.',
 'Ultra-thin showcase LED poster case.',
 'صندوق ملصق LED رفيع.',
 '<p>Mağazalar, AVM ve showroom için <strong>ultra ince</strong>, yüksek parlaklıklı poster LED kasası. Plug-and-play USB içerik yükleme.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"Ekran","deger":"640 x 1920 mm"},{"baslik":"Çözünürlük","deger":"256 x 768 px"},{"baslik":"Parlaklık","deger":"1500-2500 nits"},{"baslik":"Kalınlık","deger":"< 80 mm"},{"baslik":"İçerik","deger":"USB / WiFi / LAN"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledposter-tek.png', 'P2.5', '640x1920 mm', '15 kg', 1, 1, 1, 1),

(4, 'led-poster-cift-tarafli', 'TL-LP-002', 'LED Poster Çift Taraflı', 'Double-Sided LED Poster', 'إطار ملصق LED ثنائي',
 'Çift taraflı LED poster kasa. Koridor ve geçit alanlar için.',
 'Double-sided LED poster case.',
 'صندوق ملصق ثنائي الوجه.',
 '<p>Her iki yönden görülmesi gereken koridor ve geçit alanları için çift taraflı LED poster. Asma tavan veya zemin montaj.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"Ekran (Her Yüz)","deger":"640 x 1920 mm"},{"baslik":"Parlaklık","deger":"1500-2500 nits"},{"baslik":"Kalınlık","deger":"< 120 mm"},{"baslik":"Montaj","deger":"Tavan / Zemin / Duvar"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledposter-cift.png', 'P2.5', '640x1920 mm (x2)', '28 kg', 1, 1, 2, 1),

-- CNC Kasa
(5, 'cnc-kasa-96x96x8', 'TL-CN-9696', 'CNC Kasa 96x96x8', 'CNC Case 96x96x8', 'صندوق CNC 96x96x8',
 '96x96x8 cm universal CNC LED ekran kasası. P2.5-P10 uyumlu.',
 '96x96x8 cm universal CNC LED case.',
 'صندوق CNC عالمي.',
 '<p>Universal <strong>P10/P8/P5/P4/P3/P2.5 RGB</strong> panellere uyumlu CNC kasa. DKP sac, fırın boyalı. Fuar ve mobil LED ekran için ideal.</p>',
 '[{"baslik":"Dış Ölçü","deger":"96 x 96 x 8 cm"},{"baslik":"Panel Uyumu","deger":"P2.5-P10 RGB"},{"baslik":"Malzeme","deger":"DKP Sac, Fırın Boyalı"},{"baslik":"Ağırlık","deger":"~5 kg"},{"baslik":"Üretim","deger":"CNC Hassas Kesim"}]',
 'urunler/cnckasa-9696.png', 'Universal', '96x96x8 cm', '5 kg', 1, 0, 1, 1),

(5, 'cnc-kasa-128x128', 'TL-CN-128', 'CNC Kasa 128x128x8', 'CNC Case 128x128x8', 'صندوق CNC 128x128x8',
 'Büyük format 128x128x8 cm CNC kasa. Video wall için.',
 'Large format CNC case.',
 'صندوق CNC كبير.',
 '<p>Büyük ekran kurulumları için 128x128x8 cm CNC kasa. Fuar standları, video wall ve mobil tur için.</p>',
 '[{"baslik":"Dış Ölçü","deger":"128 x 128 x 8 cm"},{"baslik":"Panel Uyumu","deger":"P2.5-P10"},{"baslik":"Malzeme","deger":"DKP Sac, Fırın Boyalı"},{"baslik":"Ağırlık","deger":"~8 kg"}]',
 'urunler/cnckasa-128.png', 'Universal', '128x128x8 cm', '8 kg', 0, 0, 2, 1),

(5, 'cnc-kasa-ozel', 'TL-CN-OZL', 'CNC Kasa Özel Ölçü', 'Custom CNC Case', 'صندوق CNC مخصص',
 'İstediğiniz ebatlarda özel üretim CNC LED kasa.',
 'Custom-produced CNC LED case.',
 'صندوق CNC مخصص.',
 '<p>Projenize özel ebatlarda CNC kasa üretimi. İstediğiniz ölçü, renk ve montaj tipi.</p>',
 '[{"baslik":"Üretim","deger":"Projeye Özel"},{"baslik":"Min Ölçü","deger":"64x64x8 cm"},{"baslik":"Max Ölçü","deger":"200x200x12 cm"},{"baslik":"Renk","deger":"RAL kartından"},{"baslik":"Teslim","deger":"7-14 gün"}]',
 'urunler/cnckasa-ozel.png', 'Universal', 'Özel', 'Değişken', 0, 0, 3, 1),

-- Metal Kürsü
(6, 'metal-kursu-standart', 'TL-MT-001', 'Metal Kürsü Standart', 'Standard Metal Podium', 'منبر معدني قياسي',
 'Dayanıklı metal kürsü. Dijital entegrasyon için hazır altyapı.',
 'Durable metal podium.',
 'منبر معدني متين.',
 '<p>Konferans, toplantı ve seminer salonları için dayanıklı metal kürsü. Fırın boyalı yüzey, sade kurumsal tasarım. LED modül veya mikrofon sistemi sonradan entegre edilebilir.</p>',
 '[{"baslik":"Malzeme","deger":"Metal, fırın boyalı"},{"baslik":"Yükseklik","deger":"115 cm"},{"baslik":"Üst Ölçü","deger":"60 x 40 cm"},{"baslik":"Ağırlık","deger":"~25 kg"},{"baslik":"Renk","deger":"RAL seçeneği"},{"baslik":"Garanti","deger":"2 Yıl Yapısal"}]',
 'urunler/metalkursu-std.png', NULL, 'H:115 cm / Üst:60x40 cm', '25 kg', 1, 0, 1, 1);

-- Referanslar
INSERT INTO `referanslar` (`slug`, `musteri_tr`, `musteri_en`, `musteri_ar`, `lokasyon`, `sektor`, `proje_tarihi`, `aciklama_tr`, `ana_gorsel`, `vitrin`, `sira`, `aktif`) VALUES
('konferans-salonu-projesi', 'Konferans Salonu Projesi', 'Conference Hall Project', 'مشروع قاعة المؤتمرات', 'İstanbul', 'Eğitim / Kurumsal', '2025-11-15',
 '4x2 metre LED duvar ve P1.86 LED kürsü ile tam donanımlı konferans salonu kurulumu. Canlı yayın altyapısı entegrasyonu.',
 'referanslar/konferans-salonu.png', 1, 1, 1),
('avm-led-duvar', 'AVM Orta Alan LED Duvar', 'Mall Atrium LED Wall', 'جدار LED للمركز التجاري', 'Konya', 'AVM / Perakende', '2025-09-20',
 '12 m² P2.5 iç mekân LED duvar kurulumu. Modüler karkas sistemi ile montaj.',
 'referanslar/avm-led-duvar.png', 1, 2, 1),
('fuar-standi', 'Uluslararası Fuar Standı', 'International Fair Stand', 'جناح المعرض', 'Ankara', 'Fuar / Organizasyon', '2025-10-05',
 '6x3 m LED ekran, LED kürsü ve mobil CNC kasa ile fuar standı. Kurulum 48 saatte tamamlandı.',
 'referanslar/fuar-standi.png', 1, 3, 1),
('magaza-vitrin', 'Mağaza Vitrin Projesi', 'Store Window Project', 'مشروع واجهة المتجر', 'İstanbul Bağdat Cd.', 'Perakende / Vitrin', '2025-12-01',
 '3 adet çift taraflı LED poster kasa ile mağaza vitrini tanıtım sistemi.',
 'referanslar/magaza-vitrin.png', 1, 4, 1),
('stadyum-skorbord', 'Stadyum Skorbord Sistemi', 'Stadium Scoreboard', 'لوحة النتائج', 'Konya', 'Spor Tesisleri', '2025-08-18',
 '18x6 m P10 dış mekân LED skorbord için CNC kasa ve karkas üretimi.',
 'referanslar/stadyum-skorbord.png', 1, 5, 1),
('belediye-toplanti', 'Belediye Meclis Salonu', 'Municipality Council Hall', 'قاعة مجلس البلدية', 'Karatay / Konya', 'Kamu / Belediye', '2025-07-10',
 'Meclis toplantı salonu için 3x2 m LED duvar ve 2 adet P2.5 LED kürsü.',
 'referanslar/belediye-toplanti.png', 1, 6, 1);

-- Sayfalar
INSERT INTO `sayfalar` (`slug`, `baslik_tr`, `baslik_en`, `baslik_ar`, `icerik_tr`, `menude`, `footer`, `sira`) VALUES
('hakkimizda', 'Hakkımızda', 'About Us', 'من نحن',
 '<h2>TeknikLED Hakkında</h2><p>TeknikLED, Konya merkezli LED ekran çözümleri üreten bir firmadır. <strong>Tasarım tescilli modüler karkas sistemi</strong>, LED masa, LED kürsü, LED poster kasa ve CNC LED modül kasaları konularında uzmanlaşmış, %100 yerli üretim yapan bir markadır.</p><h3>Uzmanlık Alanlarımız</h3><ul><li>🏗️ <strong>Modüler Karkas Sistemi:</strong> 1.20 mm galvaniz sac, tasarım tescilli taşıyıcı altyapı</li><li>🖥️ <strong>LED Masa:</strong> 96x192, 96x224, 96x256, 96x288 ve özel ölçülerde üretim</li><li>🎤 <strong>LED Kürsü:</strong> P1.86 ve P2.5 modül seçenekleri</li><li>🪧 <strong>LED Poster Kasa:</strong> Tek ve çift taraflı ultra ince poster ekranları</li><li>📦 <strong>CNC LED Modül Kasaları:</strong> Universal P2.5-P10 panel uyumlu</li><li>🎯 <strong>Metal Kürsü:</strong> Dayanıklı, estetik konferans kürsüleri</li></ul><h3>Neden TeknikLED?</h3><ul><li>✅ %100 yerli üretim, Konya tesisimizde</li><li>✅ Tasarım tescilli modüler karkas</li><li>✅ 2 yıl parça ve yapısal garanti</li><li>✅ Projenize özel keşif ve danışmanlık</li><li>✅ Kurulum, montaj ve satış sonrası destek</li><li>✅ Türkiye geneli teslimat</li></ul>',
 1, 1, 1),
('kurumsal', 'Kurumsal', 'Corporate', 'الشركة',
 '<h2>Kurumsal Yapımız</h2><p>TeknikLED olarak LED ekran teknolojilerinde <strong>tam entegre üretim tesisimiz</strong> ve uzman ekibimizle faaliyet göstermekteyiz. Tasarımdan üretime, kurulumdan satış sonrası hizmete kadar tüm süreçleri kendi bünyemizde yürütüyoruz.</p><h3>Misyon</h3><p>Yerli üretim gücümüzle Türkiye ve Ortadoğu pazarında kaliteli, güvenilir ve ekonomik LED ekran çözümleri sunmak.</p><h3>Vizyon</h3><p>Modüler karkas sistemimizin tescil tescilli tasarımı ile sektörde standart belirleyen, dünya çapında tanınan bir marka olmak.</p><h3>Değerlerimiz</h3><ul><li>Kalite odaklı üretim</li><li>Müşteri memnuniyeti</li><li>Yerli ve milli ürün</li><li>Sürekli gelişim ve AR-GE</li></ul>',
 1, 1, 2),
('gizlilik', 'Gizlilik Politikası', 'Privacy Policy', 'سياسة الخصوصية',
 '<h2>Gizlilik Politikası</h2><p>Kişisel verileriniz 6698 sayılı KVKK kapsamında korunmaktadır. Sitemize ilettiğiniz bilgiler yalnızca sizinle iletişim kurmak ve teklif talebinize yanıt vermek için kullanılır.</p>', 0, 1, 10),
('kvkk', 'KVKK Aydınlatma Metni', 'GDPR Notice', 'إشعار حماية البيانات',
 '<h2>KVKK Aydınlatma Metni</h2><p>6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında, web sitemiz üzerinden iletmiş olduğunuz ad soyad, telefon, e-posta ve mesaj bilgileri; yalnızca talep ettiğiniz hizmet veya teklif kapsamında değerlendirilmek üzere TeknikLED tarafından işlenmektedir. Verileriniz üçüncü şahıslarla paylaşılmamaktadır.</p>', 0, 1, 11);

-- Ayarlar
INSERT INTO `ayarlar` (`anahtar`, `deger`, `grup`, `tip`, `aciklama`) VALUES
('firma_adi', 'TeknikLED', 'genel', 'text', 'Firma adı'),
('telefon', '+90 535 487 79 64', 'iletisim', 'text', 'Telefon'),
('telefon_2', '', 'iletisim', 'text', 'İkinci telefon'),
('whatsapp', '+905354877964', 'iletisim', 'text', 'WhatsApp (ülke kodlu)'),
('eposta', 'info@teknikled.com', 'iletisim', 'email', 'E-posta'),
('adres_tr', 'Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA', 'iletisim', 'textarea', 'Adres TR'),
('adres_en', 'Fevzicakmak District, Medcezir St. No:8/B23 Karatay / KONYA / TURKEY', 'iletisim', 'textarea', 'Adres EN'),
('adres_ar', 'حي فوزي تشاكماك، شارع مدجزير رقم 8/B23 كاراتاي / قونيا / تركيا', 'iletisim', 'textarea', 'Adres AR'),
('harita_iframe', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3159.5!2d32.5!3d37.87!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!5e0!3m2!1str!2str!4v1700000000000" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>', 'iletisim', 'textarea', 'Google Maps iframe'),
('facebook', '', 'sosyal', 'url', 'Facebook'),
('instagram', '', 'sosyal', 'url', 'Instagram'),
('linkedin', '', 'sosyal', 'url', 'LinkedIn'),
('youtube', '', 'sosyal', 'url', 'YouTube'),
('slogan_tr', 'LED Teknolojisinde Güvenilir Çözüm Ortağınız', 'genel', 'text', 'Slogan TR'),
('slogan_en', 'Your Trusted Partner in LED Technology', 'genel', 'text', 'Slogan EN'),
('slogan_ar', 'شريككم الموثوق في تقنية LED', 'genel', 'text', 'Slogan AR'),
('hero_baslik_tr', 'LED Teknolojisinde Yeni Nesil Çözümler', 'anasayfa', 'text', 'Hero başlık TR'),
('hero_baslik_en', 'Next Generation Solutions in LED Technology', 'anasayfa', 'text', 'Hero başlık EN'),
('hero_baslik_ar', 'حلول الجيل القادم في تقنية LED', 'anasayfa', 'text', 'Hero başlık AR'),
('hero_alt_tr', 'Tasarım tescilli modüler karkas, LED masa, LED kürsü, LED poster kasa ve CNC kasa üretimi. Konya merkezli, %100 yerli üretim.', 'anasayfa', 'textarea', 'Hero alt TR'),
('hero_alt_en', 'Design-registered modular frame, LED table, LED podium, LED poster case and CNC case manufacturing. Konya-based, 100% local production.', 'anasayfa', 'textarea', 'Hero alt EN'),
('hero_alt_ar', 'تصنيع الإطارات المعيارية المسجلة وطاولات LED ومنابر LED وصناديق الملصقات.', 'anasayfa', 'textarea', 'Hero alt AR'),
('bakim_modu', '0', 'sistem', 'bool', 'Bakım modu (1=aktif)'),
('analytics_id', '', 'sistem', 'text', 'Google Analytics / GTM ID');

-- Varsayilan yonetici (sifre: admin123 - kurulumda degisir)
INSERT INTO `yoneticiler` (`ad_soyad`, `kullanici_adi`, `eposta`, `sifre_hash`, `rol`, `aktif`) VALUES
('Sistem Yoneticisi', 'admin', 'admin@teknikled.com', '$2y$10$8K1p.3zD8iQ7WQ9YvN0bPeYZKY4J3v1x.VzKz7HvQXqB0YnZpXrgK', 'super', 1);

-- Slider seed (4 kayit)
INSERT INTO `slider` (`baslik_tr`, `baslik_en`, `baslik_ar`, `aciklama_tr`, `aciklama_en`, `aciklama_ar`, `gorsel`, `buton_metin_tr`, `buton_metin_en`, `buton_metin_ar`, `buton_url`, `sira`, `aktif`) VALUES
('Modüler Karkas Sistemi', 'Modular Frame System', 'نظام الإطار المعياري',
 'Tasarım tescilli, 1.20 mm galvaniz sac, dünyada bir ilk modüler karkas sistemi.',
 'Design-registered, 1.20 mm galvanized steel, a world-first modular frame system.',
 'إطار معياري مسجل التصميم، فولاذ مجلفن 1.20 ملم.',
 'slider/01-karkas.png', 'İncele', 'Explore', 'استكشف', '/kategori/moduler-karkas', 1, 1),
('LED Masalar', 'LED Tables', 'طاولات LED',
 '96x192, 96x224, 96x256, 96x288 ve özel ölçülerde P1.86/P2.5 LED masa üretimi.',
 '96x192, 96x224, 96x256, 96x288 and custom dimensions with P1.86/P2.5 LED tables.',
 'طاولات LED بمقاسات مخصصة.',
 'slider/02-led-masa.png', 'Modelleri Gör', 'View Models', 'عرض النماذج', '/kategori/led-masa', 2, 1),
('LED Kürsü', 'LED Podium', 'منبر LED',
 'P1.86 ve P2.5 LED modül seçenekli dijital kürsü. Konferans, toplantı ve fuar salonları için.',
 'Digital podium with P1.86 and P2.5 LED module options.',
 'منبر رقمي.',
 'slider/03-led-kursu.png', 'Teklif Al', 'Get Quote', 'اطلب عرضاً', '/teklif', 3, 1),
('Metal Kürsü', 'Metal Podium', 'منبر معدني',
 'Şık, dayanıklı, deri kaplama. Tasarım tescilli metal kürsü üretimi.',
 'Sleek, durable, leather-coated. Design-registered metal podium.',
 'منبر معدني متين.',
 'slider/04-metal-kursu.png', 'Detaylar', 'Details', 'التفاصيل', '/urun/metal-kursu-standart', 4, 1);
