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

SET FOREIGN_KEY_CHECKS = 1;

-- =================================================================
-- BASLANGIC VERILERI
-- =================================================================

-- Kategoriler
INSERT INTO `kategoriler` (`slug`, `ad_tr`, `ad_en`, `ad_ar`, `aciklama_tr`, `aciklama_en`, `aciklama_ar`, `ikon`, `sira`) VALUES
('moduler-karkas', 'Modüler Karkas Sistemi', 'Modular Frame System', 'نظام الإطار المعياري', '1.20 mm galvaniz sac, montaja hazırlanmış profiller, tasarım tescilli karkas sistemi.', 'Design-registered frame system with 1.20 mm galvanized steel and pre-assembled profiles.', 'نظام إطار مسجل التصميم مع الفولاذ المجلفن 1.20 ملم والمقاطع المجمعة مسبقاً.', 'grid', 1),
('led-masa', 'LED Masa', 'LED Table', 'طاولة LED', 'Özel ölçü LED masa imalatı; 96x224, 96x256 ve istediğiniz ebatlarda.', 'Custom-size LED table manufacturing; 96x224, 96x256 and any dimension you need.', 'تصنيع طاولات LED بمقاسات مخصصة؛ 96x224، 96x256 وأي أبعاد تحتاجها.', 'layout', 2),
('led-kursu', 'LED Kürsü', 'LED Podium', 'منبر LED', 'P1.86 ve P2.5 LED modül seçenekli dijital kürsü; konferans, toplantı ve fuar salonları için.', 'Digital podium with P1.86 and P2.5 LED module options; for conference, meeting and fair halls.', 'منبر رقمي مع خيارات وحدة LED P1.86 و P2.5؛ لقاعات المؤتمرات والاجتماعات والمعارض.', 'award', 3),
('led-poster', 'LED Poster Kasa', 'LED Poster Case', 'إطار ملصق LED', 'Vitrin ve showroomlar için ultra ince LED poster ekran kasaları.', 'Ultra-thin LED poster display cases for showcases and showrooms.', 'صناديق عرض ملصقات LED فائقة النحافة للعروض وصالات العرض.', 'image', 4),
('cnc-kasa', 'CNC LED Modül Kasaları', 'CNC LED Module Cases', 'صناديق وحدات LED CNC', 'Universal P10/P8/P5/P4/P3/P2.5 RGB panel uyumlu CNC kasalar.', 'CNC cases compatible with universal P10/P8/P5/P4/P3/P2.5 RGB panels.', 'صناديق CNC متوافقة مع لوحات RGB العالمية P10/P8/P5/P4/P3/P2.5.', 'box', 5);

-- Ornek urunler
INSERT INTO `urunler` (`kategori_id`, `slug`, `urun_kodu`, `ad_tr`, `ad_en`, `ad_ar`, `ozet_tr`, `ozet_en`, `ozet_ar`, `aciklama_tr`, `piksel`, `olcu`, `agirlik`, `vitrin`, `aktif`, `sira`) VALUES
(1, 'moduler-karkas-standart', 'TL-MK-001', 'Modüler Karkas Standart', 'Standard Modular Frame', 'إطار معياري قياسي', '1.20 mm galvaniz sac, tasarım tescilli modüler karkas.', 'Design-registered modular frame made of 1.20 mm galvanized steel.', 'إطار معياري مسجل التصميم من الفولاذ المجلفن 1.20 ملم.', '<p>1.20 mm galvaniz sac malzemeden imal edilen, tasarım tescilli modüler karkas sistemimiz; kolay montaj, şık tasarım ve sağlam yapısıyla öne çıkmaktadır.</p>', NULL, 'Modüler', 'Degiskendir', 1, 1, 1),
(2, 'led-masa-96x256', 'TL-LM-256', 'LED Masa 96x256', 'LED Table 96x256', 'طاولة LED 96x256', '96x256 cm ölçülerinde özel üretim LED masa.', '96x256 cm custom-made LED table.', 'طاولة LED مصنوعة خصيصاً بمقاس 96x256 سم.', '<p>Konferans salonları ve toplantı alanları için özel tasarım LED masa.</p>', 'P2.5', '96x256 cm', 'Ürüne göre', 1, 1, 1),
(3, 'led-kursu-p186', 'TL-LK-186', 'LED Kürsü P1.86', 'LED Podium P1.86', 'منبر LED P1.86', 'P1.86 LED modüllü yüksek çözünürlüklü dijital kürsü.', 'High-resolution digital podium with P1.86 LED module.', 'منبر رقمي عالي الدقة مع وحدة LED P1.86.', '<p>Okullar, üniversiteler, belediye toplantı salonları ve fuar organizasyonları için ideal.</p>', 'P1.86', 'Standart', 'Ürüne göre', 1, 1, 1),
(3, 'led-kursu-p25', 'TL-LK-250', 'LED Kürsü P2.5', 'LED Podium P2.5', 'منبر LED P2.5', 'P2.5 LED modüllü dayanıklı ve ekonomik dijital kürsü.', 'Durable and economical digital podium with P2.5 LED module.', 'منبر رقمي متين واقتصادي مع وحدة LED P2.5.', '<p>Ekonomik bütçeli projeler icin ideal dijital kürsü cözümü.</p>', 'P2.5', 'Standart', 'Ürüne göre', 1, 1, 2),
(4, 'led-poster-ince-seri', 'TL-LP-001', 'LED Poster İnce Seri', 'LED Poster Slim Series', 'سلسلة ملصقات LED نحيفة', 'Ultra ince vitrin LED poster kasaları.', 'Ultra-thin showcase LED poster cases.', 'صناديق عرض ملصقات LED فائقة النحافة.', '<p>Magazalar, AVM\'ler ve showroom\'lar icin ince, yüksek parlaklikli poster LED kasaları.</p>', 'P2.5', '640x1920 mm', '15 kg', 1, 1, 1),
(5, 'cnc-kasa-96x96x8', 'TL-CN-9608', 'CNC Kasa 96x96x8', 'CNC Case 96x96x8', 'صندوق CNC 96x96x8', '96x96x8 cm universal CNC LED ekran kasası.', '96x96x8 cm universal CNC LED display case.', 'صندوق عرض LED CNC عالمي 96x96x8 سم.', '<p>Universal P10/P8/P5/P4/P3/P2.5 RGB panellere uyumlu. DKP sac, firin boyali.</p>', 'Universal', '96x96x8 cm', '5 kg', 1, 1, 1);

-- Sayfalar
INSERT INTO `sayfalar` (`slug`, `baslik_tr`, `baslik_en`, `baslik_ar`, `icerik_tr`, `menude`, `footer`, `sira`) VALUES
('hakkimizda', 'Hakkımızda', 'About Us', 'من نحن', '<h2>TeknikLED Hakkında</h2><p>TeknikLED, Konya merkezli LED ekran cözümleri üreten bir firmadır. Modüler karkas sistemleri, LED masa, LED kürsü ve LED poster kasaları konusunda uzmanlasmistir.</p>', 1, 1, 1),
('kurumsal', 'Kurumsal', 'Corporate', 'الشركة', '<h2>Kurumsal Yapımız</h2><p>Kalite odakli üretim anlayisimiz ile sektörde öne cikan bir marka olmayi hedefliyoruz.</p>', 1, 1, 2),
('gizlilik', 'Gizlilik Politikası', 'Privacy Policy', 'سياسة الخصوصية', '<h2>Gizlilik Politikası</h2><p>Kisisel verileriniz 6698 sayili KVKK kapsaminda korunmaktadir.</p>', 0, 1, 10),
('kvkk', 'KVKK Aydınlatma Metni', 'GDPR Notice', 'إشعار حماية البيانات', '<h2>KVKK Aydinlatma Metni</h2><p>6698 sayili Kisisel Verilerin Korunmasi Kanunu...</p>', 0, 1, 11);

-- Ayarlar
INSERT INTO `ayarlar` (`anahtar`, `deger`, `grup`, `tip`, `aciklama`) VALUES
('firma_adi', 'TeknikLED', 'genel', 'text', 'Firma adı'),
('telefon', '+90 535 487 79 64', 'iletisim', 'text', 'Telefon'),
('telefon_2', '', 'iletisim', 'text', 'İkinci telefon'),
('whatsapp', '+905354877964', 'iletisim', 'text', 'WhatsApp (ülke kodlu, +905...)'),
('eposta', 'info@teknikled.com', 'iletisim', 'email', 'E-posta'),
('adres_tr', 'Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA', 'iletisim', 'textarea', 'Adres TR'),
('adres_en', 'Fevzicakmak District, Medcezir St. No:8/B23 Karatay / KONYA / TURKEY', 'iletisim', 'textarea', 'Adres EN'),
('adres_ar', 'حي فوزي تشاكماك، شارع مدجزير رقم 8/B23 كاراتاي / قونيا / تركيا', 'iletisim', 'textarea', 'Adres AR'),
('harita_iframe', '<iframe src="https://www.google.com/maps/embed?pb=" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>', 'iletisim', 'textarea', 'Google Maps iframe kodu'),
('facebook', '', 'sosyal', 'url', 'Facebook'),
('instagram', '', 'sosyal', 'url', 'Instagram'),
('linkedin', '', 'sosyal', 'url', 'LinkedIn'),
('youtube', '', 'sosyal', 'url', 'YouTube'),
('slogan_tr', 'Teknoloji ve Bilişim', 'genel', 'text', 'Slogan TR'),
('slogan_en', 'Technology and IT', 'genel', 'text', 'Slogan EN'),
('slogan_ar', 'التكنولوجيا والمعلوماتية', 'genel', 'text', 'Slogan AR'),
('hero_baslik_tr', 'LED Teknolojisinde Yeni Nesil Çözümler', 'anasayfa', 'text', 'Hero başlık TR'),
('hero_baslik_en', 'Next Generation Solutions in LED Technology', 'anasayfa', 'text', 'Hero başlık EN'),
('hero_baslik_ar', 'حلول الجيل القادم في تقنية LED', 'anasayfa', 'text', 'Hero başlık AR'),
('hero_alt_tr', 'Tasarım tescilli modüler karkas, LED masa, LED kürsü ve poster kasa üretimi', 'anasayfa', 'textarea', 'Hero alt TR'),
('hero_alt_en', 'Design-registered modular frame, LED table, LED podium and poster case manufacturing', 'anasayfa', 'textarea', 'Hero alt EN'),
('hero_alt_ar', 'تصنيع الإطارات المعيارية المسجلة التصميم وطاولات LED ومنابر LED وصناديق الملصقات', 'anasayfa', 'textarea', 'Hero alt AR'),
('bakim_modu', '0', 'sistem', 'bool', 'Bakım modu (1=aktif)'),
('analytics_id', '', 'sistem', 'text', 'Google Analytics / GTM ID');

-- Varsayilan yonetici (sifre: admin123 - kurulumda degisir)
-- password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `yoneticiler` (`ad_soyad`, `kullanici_adi`, `eposta`, `sifre_hash`, `rol`, `aktif`) VALUES
('Sistem Yoneticisi', 'admin', 'admin@teknikled.com', '$2y$10$8K1p.3zD8iQ7WQ9YvN0bPeYZKY4J3v1x.VzKz7HvQXqB0YnZpXrgK', 'super', 1);
