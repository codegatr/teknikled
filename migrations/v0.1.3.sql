-- =================================================================
-- TeknikLED v0.1.2 -> v0.1.3 Migration
-- MEVCUT kurulumlar icin icerik guncelleme SQL'i
-- =================================================================
--
-- NE YAPAR:
--  - Eski urun/kategori/referans verilerini temizler
--  - Yeni 14 urun + 6 kategori + 6 referans ekler
--  - Yoneticiler, iletisim_mesajlari, teklifler KORUNUR
--
-- NASIL CALISTIRILIR:
--  phpMyAdmin: Veritabanini sec -> SQL sekmesi -> icerigi yapistir -> Git
--
-- =================================================================

SET NAMES utf8mb4;

-- 1. Eski icerik temizle
DELETE FROM `urunler`;
DELETE FROM `referanslar`;
DELETE FROM `kategoriler`;
ALTER TABLE `urunler` AUTO_INCREMENT = 1;
ALTER TABLE `referanslar` AUTO_INCREMENT = 1;
ALTER TABLE `kategoriler` AUTO_INCREMENT = 1;

-- 2. KATEGORILER (6)
INSERT INTO `kategoriler` (`slug`, `ad_tr`, `ad_en`, `ad_ar`, `aciklama_tr`, `aciklama_en`, `aciklama_ar`, `ikon`, `gorsel`, `sira`, `aktif`) VALUES
('moduler-karkas', 'Modüler Karkas Sistemi', 'Modular Frame System', 'نظام الإطار المعياري',
 '1.20 mm galvaniz sac, montaja hazır profiller, tasarım tescilli karkas sistemi. Her ölçüde LED panel uygulamasına uyum sağlar.',
 'Design-registered frame system with 1.20 mm galvanized steel and pre-assembled profiles.',
 'نظام إطار مسجل التصميم.',
 'grid', 'kategoriler/moduler-karkas.png', 1, 1),
('led-masa', 'LED Masa', 'LED Table', 'طاولة LED',
 'Özel ölçü LED masa imalatı. 96x192, 96x224, 96x256, 96x288 ve istediğiniz ebatlarda P1.86/P2.5 seçenekleriyle.',
 'Custom-size LED table manufacturing.',
 'تصنيع طاولات LED بمقاسات مخصصة.',
 'layout', 'kategoriler/led-masa.png', 2, 1),
('led-kursu', 'LED Kürsü', 'LED Podium', 'منبر LED',
 'P1.86 ve P2.5 LED modül seçenekli dijital kürsü. Konferans, toplantı ve fuar salonları için.',
 'Digital podium with P1.86 and P2.5 LED module options.',
 'منبر رقمي.',
 'award', 'kategoriler/led-kursu.png', 3, 1),
('led-poster', 'LED Poster Kasa', 'LED Poster Case', 'إطار ملصق LED',
 'Vitrin ve showroomlar için ultra ince LED poster ekran kasaları. Tek ve çift taraflı seçenekler.',
 'Ultra-thin LED poster display cases.',
 'صناديق عرض ملصقات LED.',
 'image', 'kategoriler/led-poster.png', 4, 1),
('cnc-kasa', 'CNC LED Modül Kasaları', 'CNC LED Module Cases', 'صناديق وحدات LED CNC',
 'Universal P10/P8/P5/P4/P3/P2.5 RGB panel uyumlu CNC kasalar. DKP sac, fırın boyalı.',
 'CNC cases compatible with universal panels.',
 'صناديق CNC عالمية.',
 'box', 'kategoriler/cnc-kasa.png', 5, 1),
('metal-kursu', 'Metal Kürsü', 'Metal Podium', 'منبر معدني',
 'Konferans, toplantı ve seminer salonları için dayanıklı metal kürsü üretimi.',
 'Durable metal podium manufacturing.',
 'تصنيع منبر معدني متين.',
 'award', 'kategoriler/metal-kursu.png', 6, 1);

-- 3. URUNLER (14)
INSERT INTO `urunler` (`kategori_id`, `slug`, `urun_kodu`, `ad_tr`, `ad_en`, `ad_ar`, `ozet_tr`, `ozet_en`, `ozet_ar`, `aciklama_tr`, `ozellikler_tr`, `ana_gorsel`, `piksel`, `olcu`, `agirlik`, `vitrin`, `yeni`, `sira`, `aktif`) VALUES

(1, 'moduler-karkas-standart', 'TL-MK-001', 'Modüler Karkas Standart', 'Standard Modular Frame', 'إطار معياري قياسي',
 '1.20 mm galvaniz sac, tasarım tescilli modüler karkas. Kolay montaj, şık tasarım.',
 'Design-registered modular frame.',
 'إطار معياري مسجل.',
 '<p>TeknikLed Modüler Karkas Sistemi, iç mekân LED panel uygulamalarında montaj sürecini hızlandırmak, kusursuz hizalama sağlamak ve uzun ömürlü bir taşıyıcı altyapı oluşturmak amacıyla geliştirilmiştir. <strong>Tamamı kendi üretimimizdir.</strong></p><h3>Neden TeknikLED Modüler Karkas?</h3><ul><li>Modüler yapı, her ölçüde LED panele uyum</li><li>Hızlı ve pratik montaj, işçilik tasarrufu</li><li>Mükemmel hizalama, pürüzsüz yüzey</li><li>Yüksek taşıma kapasitesi, uzun ömür</li><li>Bakım ve panel değişimi kolaylığı</li></ul>',
 '[{"baslik":"Malzeme","deger":"1.20 mm Galvaniz Sac"},{"baslik":"Tasarım","deger":"Tescilli Modüler Sistem"},{"baslik":"Montaj","deger":"Hazır Profil"},{"baslik":"Uygulama","deger":"Her ölçüde iç mekân LED panel"},{"baslik":"Garanti","deger":"2 Yıl Yapısal"},{"baslik":"Üretim","deger":"%100 Yerli"}]',
 'urunler/modkarkas-standart.png', NULL, 'Modüler / Değişken', 'Ölçüye bağlı', 1, 1, 1, 1),
(1, 'moduler-karkas-ozel', 'TL-MK-002', 'Modüler Karkas Özel Ölçü', 'Custom-Size Modular Frame', 'إطار معياري مخصص',
 'Projenizin ölçülerine özel üretim, tasarım tescilli karkas sistemi.',
 'Custom production.',
 'إنتاج مخصص.',
 '<p>Her ölçü ve geometrik şekle uygun özel üretim modüler karkas sistemleri. Mimariye özel projeler için tasarlanır ve üretilir. Ölçünüzü paylaşın, karkas tasarımımızı 48 saat içinde sunalım.</p>',
 '[{"baslik":"Üretim","deger":"Projeye Özel"},{"baslik":"Teslim","deger":"7-14 gün"},{"baslik":"Malzeme","deger":"1.20 / 1.50 / 2.00 mm"},{"baslik":"Destek","deger":"Keşif ve danışmanlık dahil"}]',
 'urunler/modkarkas-ozel.png', NULL, 'Özel', 'Ölçüye bağlı', 0, 0, 2, 1),

(2, 'led-masa-96x192', 'TL-LM-192', 'LED Masa 96x192', 'LED Table 96x192', 'طاولة LED 96x192',
 '96x192 cm özel üretim LED masa. P1.86 ve P2.5 seçenekleri.',
 '96x192 cm LED table.',
 'طاولة LED.',
 '<p>Kompakt ölçüleriyle dar sunum platformları, podyum önü ve küçük konferans salonları için ideal LED masa çözümü. <strong>Kendi imalatımızdır.</strong></p>',
 '[{"baslik":"Ölçü","deger":"96 x 192 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~450W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x192.png', 'P1.86 / P2.5', '96x192 cm', '~60 kg', 1, 0, 1, 1),
(2, 'led-masa-96x224', 'TL-LM-224', 'LED Masa 96x224', 'LED Table 96x224', 'طاولة LED 96x224',
 '96x224 cm standart LED masa.',
 '96x224 cm LED table.',
 'طاولة LED.',
 '<p>Konferans salonları ve toplantı alanları için özel tasarım LED masa. Tamamı <strong>kendi tesislerimizde</strong> üretilmektedir.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 224 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~520W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x224.png', 'P1.86 / P2.5', '96x224 cm', '~70 kg', 1, 0, 2, 1),
(2, 'led-masa-96x256', 'TL-LM-256', 'LED Masa 96x256', 'LED Table 96x256', 'طاولة LED 96x256',
 '96x256 cm geniş format LED masa.',
 '96x256 cm LED table.',
 'طاولة LED.',
 '<p>Geniş format konferans ve toplantı salonları için ideal LED masa çözümü. Protokol masası ve yönetici sunumları için tercih edilir.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 256 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~600W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x256.png', 'P1.86 / P2.5', '96x256 cm', '~80 kg', 1, 0, 3, 1),
(2, 'led-masa-96x288', 'TL-LM-288', 'LED Masa 96x288', 'LED Table 96x288', 'طاولة LED 96x288',
 '96x288 cm büyük format LED masa.',
 '96x288 cm large LED table.',
 'طاولة LED كبيرة.',
 '<p>En büyük standart ölçümüz. Prestijli etkinlikler, büyük lansman alanları ve sahne önü kullanımlar için.</p>',
 '[{"baslik":"Ölçü","deger":"96 x 288 cm"},{"baslik":"Piksel","deger":"P1.86 / P2.5"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Enerji","deger":"~680W"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledmasa-96x288.png', 'P1.86 / P2.5', '96x288 cm', '~90 kg', 1, 0, 4, 1),

(3, 'led-kursu-p186', 'TL-LK-186', 'LED Kürsü P1.86', 'LED Podium P1.86', 'منبر LED P1.86',
 'P1.86 LED modüllü yüksek çözünürlüklü dijital kürsü.',
 'High-resolution digital podium.',
 'منبر رقمي.',
 '<p>Premium konferans salonları, büyük toplantı salonları ve prestijli etkinlikler için dijital sunum kürsüsü. P1.86 mm piksel aralığı ile yakın mesafeden dahi pürüzsüz görüntü.</p><h3>Kullanım Alanları</h3><ul><li>Okul, Üniversite Konferans Salonları</li><li>Belediye Toplantı Salonları</li><li>Fuar ve Organizasyon Firmaları</li><li>Kurumsal lansman etkinlikleri</li></ul>',
 '[{"baslik":"Piksel","deger":"P1.86 mm"},{"baslik":"LED Tipi","deger":"SMD Nationstar/Kinglight"},{"baslik":"Parlaklık","deger":"≥ 800 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Görüş Açısı","deger":"160°/140°"},{"baslik":"Kontrol","deger":"Linsn / Novastar"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledkursu-p186.png', 'P1.86', 'Standart / Özel', '~45 kg', 1, 1, 1, 1),
(3, 'led-kursu-p25', 'TL-LK-250', 'LED Kürsü P2.5', 'LED Podium P2.5', 'منبر LED P2.5',
 'P2.5 LED modüllü ekonomik dijital kürsü.',
 'Economical digital podium.',
 'منبر اقتصادي.',
 '<p>Standart konferans, toplantı ve fuar salonları için ekonomik ve dayanıklı dijital kürsü. En yaygın tercih edilen model.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"LED Tipi","deger":"SMD Full Color RGB"},{"baslik":"Parlaklık","deger":"≥ 700 nits"},{"baslik":"Yenileme","deger":"3840 Hz"},{"baslik":"Piksel Yoğunluğu","deger":"160.000 dots/m²"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledkursu-p25.png', 'P2.5', 'Standart / Özel', '~42 kg', 1, 0, 2, 1),

(4, 'led-poster-tek-tarafli', 'TL-LP-001', 'LED Poster Tek Taraflı', 'Single-Sided LED Poster', 'إطار ملصق أحادي',
 'Ultra ince vitrin LED poster kasası.',
 'Ultra-thin LED poster case.',
 'صندوق ملصق.',
 '<p>Mağazalar, AVM ve showroom için <strong>ultra ince</strong>, yüksek parlaklıklı poster LED kasası. Plug-and-play USB içerik yükleme.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"Ekran","deger":"640 x 1920 mm"},{"baslik":"Çözünürlük","deger":"256 x 768 px"},{"baslik":"Parlaklık","deger":"1500-2500 nits"},{"baslik":"Kalınlık","deger":"< 80 mm"},{"baslik":"İçerik","deger":"USB / WiFi / LAN"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledposter-tek.png', 'P2.5', '640x1920 mm', '15 kg', 1, 1, 1, 1),
(4, 'led-poster-cift-tarafli', 'TL-LP-002', 'LED Poster Çift Taraflı', 'Double-Sided LED Poster', 'إطار ملصق ثنائي',
 'Çift taraflı LED poster kasa.',
 'Double-sided poster.',
 'ملصق ثنائي.',
 '<p>Her iki yönden görülmesi gereken koridor ve geçit alanları için çift taraflı LED poster. Asma tavan veya zemin montaj.</p>',
 '[{"baslik":"Piksel","deger":"P2.5 mm"},{"baslik":"Ekran (Her Yüz)","deger":"640 x 1920 mm"},{"baslik":"Parlaklık","deger":"1500-2500 nits"},{"baslik":"Kalınlık","deger":"< 120 mm"},{"baslik":"Montaj","deger":"Tavan / Zemin / Duvar"},{"baslik":"Garanti","deger":"2 Yıl"}]',
 'urunler/ledposter-cift.png', 'P2.5', '640x1920 mm (x2)', '28 kg', 1, 1, 2, 1),

(5, 'cnc-kasa-96x96x8', 'TL-CN-9696', 'CNC Kasa 96x96x8', 'CNC Case 96x96x8', 'صندوق CNC 96x96x8',
 '96x96x8 cm universal CNC LED ekran kasası.',
 'Universal CNC LED case.',
 'صندوق CNC.',
 '<p>Universal <strong>P10/P8/P5/P4/P3/P2.5 RGB</strong> panellere uyumlu CNC kasa. DKP sac, fırın boyalı. Fuar ve mobil LED ekran için ideal.</p>',
 '[{"baslik":"Dış Ölçü","deger":"96 x 96 x 8 cm"},{"baslik":"Panel Uyumu","deger":"P2.5-P10 RGB"},{"baslik":"Malzeme","deger":"DKP Sac, Fırın Boyalı"},{"baslik":"Ağırlık","deger":"~5 kg"},{"baslik":"Üretim","deger":"CNC Hassas Kesim"}]',
 'urunler/cnckasa-9696.png', 'Universal', '96x96x8 cm', '5 kg', 1, 0, 1, 1),
(5, 'cnc-kasa-128x128', 'TL-CN-128', 'CNC Kasa 128x128x8', 'CNC Case 128x128x8', 'صندوق CNC 128x128x8',
 'Büyük format 128x128x8 cm CNC kasa.',
 'Large CNC case.',
 'صندوق كبير.',
 '<p>Büyük ekran kurulumları için 128x128x8 cm CNC kasa. Fuar standları, video wall ve mobil tur için.</p>',
 '[{"baslik":"Dış Ölçü","deger":"128 x 128 x 8 cm"},{"baslik":"Panel Uyumu","deger":"P2.5-P10"},{"baslik":"Malzeme","deger":"DKP Sac, Fırın Boyalı"},{"baslik":"Ağırlık","deger":"~8 kg"}]',
 'urunler/cnckasa-128.png', 'Universal', '128x128x8 cm', '8 kg', 0, 0, 2, 1),
(5, 'cnc-kasa-ozel', 'TL-CN-OZL', 'CNC Kasa Özel Ölçü', 'Custom CNC Case', 'صندوق مخصص',
 'İstediğiniz ebatlarda özel üretim CNC LED kasa.',
 'Custom CNC case.',
 'صندوق مخصص.',
 '<p>Projenize özel ebatlarda CNC kasa üretimi. İstediğiniz ölçü, renk ve montaj tipi.</p>',
 '[{"baslik":"Üretim","deger":"Projeye Özel"},{"baslik":"Min Ölçü","deger":"64x64x8 cm"},{"baslik":"Max Ölçü","deger":"200x200x12 cm"},{"baslik":"Renk","deger":"RAL kartından"},{"baslik":"Teslim","deger":"7-14 gün"}]',
 'urunler/cnckasa-ozel.png', 'Universal', 'Özel', 'Değişken', 0, 0, 3, 1),

(6, 'metal-kursu-standart', 'TL-MT-001', 'Metal Kürsü Standart', 'Standard Metal Podium', 'منبر معدني قياسي',
 'Dayanıklı metal kürsü. Dijital entegrasyon için hazır altyapı.',
 'Durable metal podium.',
 'منبر متين.',
 '<p>Konferans, toplantı ve seminer salonları için dayanıklı metal kürsü. Fırın boyalı yüzey, sade kurumsal tasarım. LED modül veya mikrofon sistemi sonradan entegre edilebilir.</p>',
 '[{"baslik":"Malzeme","deger":"Metal, fırın boyalı"},{"baslik":"Yükseklik","deger":"115 cm"},{"baslik":"Üst Ölçü","deger":"60 x 40 cm"},{"baslik":"Ağırlık","deger":"~25 kg"},{"baslik":"Renk","deger":"RAL seçeneği"},{"baslik":"Garanti","deger":"2 Yıl Yapısal"}]',
 'urunler/metalkursu-std.png', NULL, 'H:115 cm / Üst:60x40 cm', '25 kg', 1, 0, 1, 1);

-- 4. REFERANSLAR (6)
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

-- 5. AYARLAR GUNCELLE
UPDATE `ayarlar` SET `deger` = 'LED Teknolojisinde Güvenilir Çözüm Ortağınız' WHERE `anahtar` = 'slogan_tr';
UPDATE `ayarlar` SET `deger` = 'Your Trusted Partner in LED Technology' WHERE `anahtar` = 'slogan_en';
UPDATE `ayarlar` SET `deger` = 'شريككم الموثوق في تقنية LED' WHERE `anahtar` = 'slogan_ar';
UPDATE `ayarlar` SET `deger` = 'Tasarım tescilli modüler karkas, LED masa, LED kürsü, LED poster kasa ve CNC kasa üretimi. Konya merkezli, %100 yerli üretim.' WHERE `anahtar` = 'hero_alt_tr';
UPDATE `ayarlar` SET `deger` = 'Design-registered modular frame, LED table, LED podium, LED poster case and CNC case manufacturing. Konya-based, 100% local production.' WHERE `anahtar` = 'hero_alt_en';

-- 6. HAKKIMIZDA GUNCELLE
UPDATE `sayfalar` SET
  `icerik_tr` = '<h2>TeknikLED Hakkında</h2><p>TeknikLED, Konya merkezli LED ekran çözümleri üreten bir firmadır. <strong>Tasarım tescilli modüler karkas sistemi</strong>, LED masa, LED kürsü, LED poster kasa ve CNC LED modül kasaları konularında uzmanlaşmış, %100 yerli üretim yapan bir markadır.</p><h3>Uzmanlık Alanlarımız</h3><ul><li><strong>Modüler Karkas Sistemi:</strong> 1.20 mm galvaniz sac, tasarım tescilli taşıyıcı altyapı</li><li><strong>LED Masa:</strong> 96x192, 96x224, 96x256, 96x288 ve özel ölçülerde üretim</li><li><strong>LED Kürsü:</strong> P1.86 ve P2.5 modül seçenekleri</li><li><strong>LED Poster Kasa:</strong> Tek ve çift taraflı ultra ince poster ekranları</li><li><strong>CNC LED Modül Kasaları:</strong> Universal P2.5-P10 panel uyumlu</li><li><strong>Metal Kürsü:</strong> Dayanıklı, estetik konferans kürsüleri</li></ul><h3>Neden TeknikLED?</h3><ul><li>%100 yerli üretim, Konya tesisimizde</li><li>Tasarım tescilli modüler karkas</li><li>2 yıl parça ve yapısal garanti</li><li>Projenize özel keşif ve danışmanlık</li><li>Kurulum, montaj ve satış sonrası destek</li><li>Türkiye geneli teslimat</li></ul>'
WHERE `slug` = 'hakkimizda';
