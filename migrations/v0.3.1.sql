-- v0.3.1 - Cozumler + Icerikler (blog/haber) + Markalar
-- Temas Teknoloji benzeri tam donanim: 4 yeni CMS entity
-- (cozumler, icerikler = blog+haber birlesik, markalar)

-- =========================================================
-- 1) COZUMLER TABLOSU
-- =========================================================
CREATE TABLE IF NOT EXISTS `cozumler` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug`           VARCHAR(100) NOT NULL UNIQUE,
  `ad_tr`          VARCHAR(200) NOT NULL,
  `ad_en`          VARCHAR(200) NULL,
  `ad_ar`          VARCHAR(200) NULL,
  `ozet_tr`        VARCHAR(500) NULL,
  `ozet_en`        VARCHAR(500) NULL,
  `ozet_ar`        VARCHAR(500) NULL,
  `aciklama_tr`    LONGTEXT NULL,
  `aciklama_en`    LONGTEXT NULL,
  `aciklama_ar`    LONGTEXT NULL,
  `ikon`           VARCHAR(20) NULL,
  `gorsel`         VARCHAR(200) NULL,
  `ilgili_urunler` VARCHAR(500) NULL COMMENT 'virgulle ayrilmis urun slug listesi',
  `vitrin`         TINYINT(1) NOT NULL DEFAULT 0,
  `sira`           INT NOT NULL DEFAULT 0,
  `aktif`          TINYINT(1) NOT NULL DEFAULT 1,
  `olusturma`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `guncelleme`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2) ICERIKLER (blog + haber birlesik)
-- =========================================================
CREATE TABLE IF NOT EXISTS `icerikler` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tip`          ENUM('blog', 'haber') NOT NULL DEFAULT 'blog',
  `slug`         VARCHAR(150) NOT NULL UNIQUE,
  `baslik_tr`    VARCHAR(300) NOT NULL,
  `baslik_en`    VARCHAR(300) NULL,
  `baslik_ar`    VARCHAR(300) NULL,
  `ozet_tr`      VARCHAR(500) NULL,
  `ozet_en`      VARCHAR(500) NULL,
  `ozet_ar`      VARCHAR(500) NULL,
  `icerik_tr`    LONGTEXT NULL,
  `icerik_en`    LONGTEXT NULL,
  `icerik_ar`    LONGTEXT NULL,
  `kapak`        VARCHAR(200) NULL,
  `yazar`        VARCHAR(100) NULL,
  `etiketler`    VARCHAR(300) NULL COMMENT 'virgulle ayrilmis',
  `goruntulenme` INT UNSIGNED NOT NULL DEFAULT 0,
  `yayin_tarihi` DATETIME NULL,
  `aktif`        TINYINT(1) NOT NULL DEFAULT 1,
  `olusturma`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `guncelleme`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `ix_tip_aktif` (`tip`, `aktif`),
  INDEX `ix_yayin` (`yayin_tarihi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3) MARKALAR (tedarikci / teknoloji ortaklari)
-- =========================================================
CREATE TABLE IF NOT EXISTS `markalar` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ad`        VARCHAR(100) NOT NULL,
  `logo`      VARCHAR(200) NOT NULL,
  `web_url`   VARCHAR(300) NULL,
  `aciklama`  VARCHAR(300) NULL,
  `sira`      INT NOT NULL DEFAULT 0,
  `aktif`     TINYINT(1) NOT NULL DEFAULT 1,
  `olusturma` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SEED: 8 COZUM
-- =========================================================
INSERT IGNORE INTO `cozumler` (`slug`, `ad_tr`, `ad_en`, `ad_ar`, `ozet_tr`, `aciklama_tr`, `ikon`, `ilgili_urunler`, `vitrin`, `sira`) VALUES
('toplanti-odasi', 'Toplantı Odası Çözümleri', 'Meeting Room Solutions', 'حلول غرف الاجتماعات',
 'Kurumsal toplantı odaları için LED masa, konferans kürsüsü ve duvar ekranı çözümleri. Hibrit toplantılara tam uyum.',
 '<h2>Toplantı Odası için Profesyonel LED Çözümler</h2><p>Modern kurumsal toplantı odaları artık basit bir projeksiyon ekranının ötesine geçmiş durumda. Hibrit çalışma modelinin yaygınlaşmasıyla birlikte, <strong>her ölçekteki toplantı odası</strong> — küçük 4-6 kişilik takım huddle''larından büyük yönetim kurulu salonlarına kadar — kaliteli görsel ve ses altyapısına ihtiyaç duymaktadır.</p><h3>Önerdiğimiz Ürünler</h3><ul><li><strong>LED Masa (P1.86):</strong> Sunum yapan kişinin önünde, ekip üyelerinin görebileceği ikincil ekran</li><li><strong>LED Kürsü P1.86:</strong> Yönetim kurulu sunumları ve resmi açıklamalar için</li><li><strong>Modüler Karkas + LED Panel:</strong> 3×2 m veya 4×2.5 m duvar ekranı, video konferans arka planı</li></ul><h3>Avantajlar</h3><ul><li>🎥 <strong>Video Konferans Uyumlu:</strong> Zoom/Teams/Meet yansıtmada flickersız görüntü</li><li>📊 <strong>Dual-screen Sunum:</strong> Hem sunum hem katılımcı görünümü aynı anda</li><li>🔇 <strong>Sessiz Çalışma:</strong> Fansız tasarım, toplantı kaydı sırasında gürültü yok</li><li>💡 <strong>Ayarlanabilir Parlaklık:</strong> Ortam ışığına göre otomatik adapte</li></ul><h3>Örnek Kurulum</h3><p>12 kişilik yönetim kurulu odası için: duvarda 3×2 m modüler karkaslı LED panel + masa üstünde 96×192 LED masa + kürsü pozisyonunda P1.86 dijital kürsü. Toplam kurulum 7-10 iş günü, kapsamlı eğitim dahil.</p>',
 '💼', 'led-masa-96x192,led-kursu-p186,moduler-karkas-standart', 1, 1),

('meclis-salonu', 'Meclis ve Konferans Salonları', 'Council & Conference Halls', 'قاعات المجالس والمؤتمرات',
 'Belediye meclisi, kamu kurumları ve üniversite konferans salonları için tasarım tescilli kürsü ve modüler LED duvar çözümleri.',
 '<h2>Meclis Salonu ve Konferans Merkezi LED Altyapısı</h2><p>Belediye meclis salonları, kamu kurumu brifing odaları, üniversite konferans amfileri ve kültür merkezleri gibi protokol mekanları için sunduğumuz entegre LED çözümler, <strong>resmiyet ve teknolojinin dengesini</strong> yakalar.</p><h3>Çözüm Paketi</h3><ul><li><strong>LED Kürsü P1.86 Premium Seri:</strong> Tasarım tescilli, mikrofon entegre, okuma aydınlatmalı. Devlet ihale şartnamelerinde yerli üretim puanı için uygun.</li><li><strong>Ana Salon LED Duvar:</strong> Modüler karkas üzerine P2.5 LED panel, kürsünün arkasında kurumsal logo veya dinamik içerik.</li><li><strong>Stenograf Ekranı:</strong> 96×192 LED masa meclis üyelerinin yerine konferans bildirimleri için.</li></ul><h3>Kamu İhale Uyumluluğu</h3><ul><li>✅ Tasarım Tescil Belgesi (T.P.E.)</li><li>✅ CE İşareti (EMC uyumluluğu)</li><li>✅ TSE uyumluluk (metal yapı)</li><li>✅ %100 Yerli Üretim (sanayi sicil belgesi)</li><li>✅ Garanti ve servis kapsamı sözleşmeli</li></ul><h3>Örnek Projeler</h3><p>Karatay/Konya Belediyesi Meclis Salonu, Çumra TSO Konferans Salonu, üniversite dönüşüm projeleri.</p>',
 '🏛️', 'led-kursu-p186,moduler-karkas-standart,metal-kursu-standart', 1, 2),

('cami-kulliye', 'Cami ve Külliye Çözümleri', 'Mosque & Complex Solutions', 'حلول المساجد',
 'Cami vaaz kürsüsü, LED Kuran-ı Kerim ayet levhası, ezan ve vaaz dijital bilgilendirme ekranları.',
 '<h2>Cami ve Külliye İçin Dijital Çözümler</h2><p>Dini mekanların estetik ve manevi atmosferine saygı gösteren, ancak modern cemaatin ihtiyaçlarını karşılayan özel tasarlanmış LED ve metal çözümler sunuyoruz.</p><h3>Önerdiğimiz Ürünler</h3><ul><li><strong>Metal Vaaz Kürsüsü:</strong> Klasik hat yazısı ile lazer kazıma, fırın boyalı, dini motifli opsiyonlar</li><li><strong>LED Ayet Ekranı:</strong> P2.5 veya P4 paneller ile dijital hat yazısı, Kur''an-ı Kerim ayetleri</li><li><strong>Vakit Bilgilendirme Kürsüsü:</strong> Namaz vakitleri, hutbe bilgisi, duyurular</li><li><strong>Cenaze/Duyuru Levhası:</strong> Dış mekan P5-P8 LED ekran</li></ul><h3>Tasarım Hassasiyeti</h3><p>Dini mekan tasarımında koyu ve sakin renkler, altın varaklı detaylar, mermer zemine uyum için özel RAL kodları ile üretim yapabiliyoruz. İstanbul ve Konya''daki tarihi külliye restorasyon projelerinde çalıştık.</p>',
 '🕌', 'metal-kursu-standart,led-poster-tek-tarafli,cnc-kasa-128x128', 1, 3),

('stadyum-skorbord', 'Stadyum ve Spor Tesisleri', 'Stadium & Sports Facilities', 'الملاعب والمرافق الرياضية',
 'Stadyum skorbord sistemi, saha kenarı LED şerit ekran, spor salonu bilgi panosu ve fan engagement LED duvar.',
 '<h2>Stadyum ve Spor Tesisi LED Çözümleri</h2><p>Açık ve kapalı alan spor tesisleri için yüksek parlaklık, dayanıklı ve uzaktan kontrol edilebilir LED ekran sistemleri.</p><h3>Ürün Portföyü</h3><ul><li><strong>Ana Skorbord:</strong> P6-P8 dış mekan LED, 6×4 m ila 10×6 m arası</li><li><strong>Saha Kenarı Şerit:</strong> Reklam içeriği için P10 şerit ekran (opsiyonel)</li><li><strong>Kapalı Salon Skorbord:</strong> P4-P5 iç mekan LED, basketbol/voleybol/hentbol</li><li><strong>Fan Engagement Duvar:</strong> 180° kavisli LED duvar (modüler karkas üzerine)</li></ul><h3>Teknik Özellikler</h3><ul><li>🌞 Gün ışığında 6000+ nit parlaklık</li><li>💧 IP65 dış mekan tam koruma</li><li>⚡ Uzaktan yönetim (WiFi/LAN, skor ve reklam içerik)</li><li>🔄 7/24 yayın desteği</li></ul>',
 '🏟️', 'moduler-karkas-ozel,cnc-kasa-128x128,led-poster-cift-tarafli', 0, 4),

('fuar-standi', 'Fuar Standları ve Etkinlikler', 'Fair Stands & Events', 'أجنحة المعارض والفعاليات',
 'Yurt içi/dışı fuarlar, etkinlik sahneleri, lansman organizasyonları için hızlı kurulan modüler LED sistemleri.',
 '<h2>Fuar Standı ve Etkinlik LED Çözümleri</h2><p>Geçici kurulum gerektiren etkinlikler için <strong>tasarım tescilli modüler karkas sistemimiz</strong> benzersiz bir avantaj sunar. 3-4 saatte kurulum, sökme imkanı ile tekrar kullanılabilir altyapı.</p><h3>Avantajlar</h3><ul><li>⚡ <strong>Hızlı Kurulum:</strong> 3×2 m standı 2 kişilik ekip 3 saatte kurar</li><li>📦 <strong>Taşınabilir:</strong> Özel flight case''lerle sektörde lider taşıma çözümü</li><li>🔄 <strong>Yeniden Kullanılabilir:</strong> Tek yatırım, yüzlerce etkinlik</li><li>🎨 <strong>Özelleştirilebilir:</strong> Boyut, renk ve LED modül tercihi müşteriye</li></ul><h3>Kiralama Seçeneği</h3><p>Tek seferlik etkinlikler için kiralama hizmeti de veriyoruz. Kurulum+sökme+nakliye dahil paket fiyatlar.</p>',
 '🎪', 'moduler-karkas-standart,moduler-karkas-ozel,led-poster-cift-tarafli', 1, 5),

('avm-dijital-yonlendirme', 'AVM ve Perakende', 'Mall & Retail', 'مراكز التسوق والتجزئة',
 'Alışveriş merkezi yönlendirme kürsüleri, mağaza vitrin LED posterleri, atrium LED duvar ve digital menuboard.',
 '<h2>AVM ve Perakende LED Çözümleri</h2><p>Alışveriş merkezlerindeki yoğun yaya trafiğinden maksimum fayda sağlamak için tasarlanmış dijital dokunuş noktaları.</p><h3>Öneriler</h3><ul><li><strong>Atrium LED Duvar:</strong> Orta alanda 5-10 m² LED yüzey, reklam ve marka iletişimi</li><li><strong>Yönlendirme Kürsüleri:</strong> Kat planları, mağaza indeksi, etkinlik takvimi</li><li><strong>Mağaza Vitrin LED Poster:</strong> Ultra ince çift taraflı, vitrin bütünlüğü</li><li><strong>Digital Menuboard:</strong> Food court''lar için özel</li></ul><h3>ROI Hesabı</h3><p>Reklamveren kiralama ile LED yatırımı ortalama 14-18 ayda kendini amorti eder. Detaylı model için bizimle görüşün.</p>',
 '🛍️', 'led-poster-cift-tarafli,led-poster-tek-tarafli,moduler-karkas-standart', 1, 6),

('stüdyo-yayin', 'TV Stüdyoları ve Yayıncılık', 'TV Studios & Broadcasting', 'الاستوديوهات التلفزيونية',
 'Canlı yayın stüdyo arka planı, podcast seti, kurumsal video prodüksiyon altyapısı için kamera uyumlu LED ekranlar.',
 '<h2>TV Stüdyosu ve Yayıncılık Altyapısı</h2><p>Kamera çekimi için özel tasarlanmış yüksek tazeleme hızlı LED panellerimiz, flicker-free ve moire efektsiz görüntü sunar.</p><h3>Kritik Özellikler</h3><ul><li>📺 <strong>3840 Hz Tazeleme Hızı:</strong> En hassas kamerada bile flickersız</li><li>🎬 <strong>HDR Uyumlu:</strong> Profesyonel renk alanı sRGB %98+, DCI-P3 %85+</li><li>🎥 <strong>Çoklu Kamera Açısı Desteği:</strong> Geniş görüş açısı 170°</li><li>💡 <strong>Studio Lighting Uyumlu:</strong> Ayarlanabilir parlaklık, beyaz dengesi kalibrasyonu</li></ul><h3>Kurulum Tipleri</h3><ul><li>Podcast seti: LED masa 96×192 (P1.86)</li><li>Haber stüdyosu arka planı: Modüler karkas + P2.5 panel 4×2.5 m</li><li>Kurumsal video: Portatif 3×2 m altyapı</li></ul>',
 '📺', 'led-masa-96x192,led-masa-96x224,moduler-karkas-standart', 0, 7),

('dini-cenaze', 'Cenaze Bilgilendirme Ekranları', 'Funeral Information Displays', 'شاشات معلومات الجنازة',
 'Cami avlularında cenaze namazı bilgilendirmesi için LED ekran, mezarlık yönlendirme ve hazire bilgilendirme panoları.',
 '<h2>Cenaze ve Mezarlık Bilgilendirme Ekranları</h2><p>Dini hassasiyete uygun, siyah gövdeli, dış mekan dayanıklı LED ekran ve bilgilendirme panosu çözümleri.</p><h3>Özellikleri</h3><ul><li>⚫ <strong>Sade Tasarım:</strong> Matte siyah gövde, dini mekan estetiğine uyum</li><li>📋 <strong>İçerik Yönetimi:</strong> Cenaze bilgileri (isim, vakit, yer) kolayca güncellenir</li><li>🌧️ <strong>IP65 Dış Mekan:</strong> Yağmur, güneş ve toza karşı tam koruma</li><li>⏰ <strong>Otomatik Saat:</strong> Namaz vakitlerine göre otomatik açılma/kapanma</li></ul><h3>Kullanım Yerleri</h3><ul><li>Cami cenaze alanı</li><li>Belediye mezarlık yönetim ofisleri</li><li>Cemevi organizasyon panoları</li></ul>',
 '🕯️', 'led-poster-tek-tarafli,cnc-kasa-96x96x8', 0, 8);

-- =========================================================
-- SEED: ICERIKLER - 6 blog + 4 haber
-- =========================================================
INSERT IGNORE INTO `icerikler` (`tip`, `slug`, `baslik_tr`, `ozet_tr`, `icerik_tr`, `yazar`, `etiketler`, `yayin_tarihi`, `aktif`) VALUES
('blog', 'led-ekran-piksel-araligi-nedir',
 'LED Ekran Piksel Aralığı Nedir? P1.86, P2.5, P4 Farkları',
 'Piksel aralığı LED ekranınızın çözünürlüğünü ve izleme mesafesini belirleyen en önemli teknik değerdir. Bu yazıda P1.86, P2.5, P4 gibi değerlerin ne anlama geldiğini anlatıyoruz.',
 '<h2>Piksel Aralığı ve LED Ekran Seçimi</h2><p>LED ekran satın alırken karşılaştığınız "P2.5" veya "P1.86" gibi ifadeler, bu ekranların piksel aralığını belirtir. Piksel aralığı (pixel pitch), iki yan yana LED piksel arasındaki mesafeyi milimetre cinsinden ifade eder.</p><h3>Ne Kadar Küçük O Kadar İyi mi?</h3><p>Kısa cevap: Hayır! Piksel aralığı, <strong>izleme mesafesine göre</strong> seçilmelidir. Çok yakından izlenen bir ekranda küçük piksel aralığı (P1.86), uzaktan izlenen bir ekranda ise daha büyük piksel aralığı (P4, P6) daha ekonomiktir.</p><h3>Pratik Kural</h3><blockquote>İzleme mesafesi (metre) ≈ Piksel aralığı (mm) × 1</blockquote><p>Yani:</p><ul><li><strong>P1.86:</strong> 1.86 m ve daha yakın — toplantı odası, stüdyo, LED masa</li><li><strong>P2.5:</strong> 2.5 m ve daha yakın — iç mekan konferans salonu, lobi</li><li><strong>P4:</strong> 4 m ve daha uzak — büyük salonlar, dış duvar</li><li><strong>P8/P10:</strong> 8-10 m ve daha uzak — stadyum, billboard, sahne</li></ul><h3>Maliyet Etkisi</h3><p>P1.86 ekran, P4 ekrandan yaklaşık 3-4 kat daha pahalıdır (aynı fiziksel boyutta). Çünkü aynı alanda çok daha fazla LED bulunur. Gereksiz yüksek çözünürlük seçmek yerine, <strong>gerçek izleme mesafenize göre</strong> seçim yapmak en doğrusu.</p>',
 'TeknikLED Teknik Ekibi', 'teknik,led-ekran,piksel-araligi,p186,p25',
 '2026-02-15 10:00:00', 1),

('blog', 'moduler-karkas-nedir',
 'Modüler Karkas Sistemi Nedir? Neden Tercih Edilmeli?',
 'Geleneksel LED ekran iskeletleri yerine modüler karkas sistemi kullanmanın avantajları, tasarım tescilli sistemimizin benzersiz yönleri ve kurulum kolaylığı.',
 '<h2>Modüler Karkas: LED Ekran Altyapısında Yeni Standart</h2><p>Büyük boyutlu LED ekranları tek parça olarak üretmek ve taşımak pratik değildir. Bu yüzden sektörde "modüler" sistem adı verilen, küçük parçaların birleştirilmesiyle her boyutta ekran yapılabilen çözümler kullanılır.</p><h3>Geleneksel Yöntemin Sorunları</h3><ul><li>🔧 <strong>Kaynak Gerektirir:</strong> Sahada kaynak yapmak ağır bir iş; montaj uzun sürer</li><li>📐 <strong>Boyut Esnek Değil:</strong> Değişiklik için yeniden üretim gerekir</li><li>🚚 <strong>Taşıma Zor:</strong> Tek parça büyük yapıyı nakletmek zordur</li><li>🔄 <strong>Geri Dönüştürülemez:</strong> Etkinlik sonrası söküp saklayamazsınız</li></ul><h3>TeknikLED Modüler Karkas Avantajları</h3><p>Tasarım tescilli sistemimiz şu özelliklerle öne çıkar:</p><ul><li>✅ <strong>1.20 mm galvaniz sac</strong> — hafif ama dayanıklı</li><li>✅ <strong>Gizli vidalı bağlantı</strong> — %60 daha hızlı montaj</li><li>✅ <strong>Demontabl yapı</strong> — sökülür, taşınır, yeniden kurulur</li><li>✅ <strong>Universal uyum</strong> — farklı LED panel boyutları</li><li>✅ <strong>Türkiye''de üretim</strong> — kısa teslim süresi</li></ul><h3>Ne Zaman Modüler Karkas?</h3><p>Şu durumlarda modüler karkas mutlaka tercih edilmelidir: fuar standları, taşınabilir sahne kurulumları, geçici etkinlikler, büyük boyutlu duvar ekranları (4 m² üzeri), özel geometrili kurulumlar (kavisli, kemerli, köşeli).</p>',
 'TeknikLED', 'moduler-karkas,tasarim-tescilli,led-altyapi',
 '2026-02-10 14:30:00', 1),

('blog', 'avm-led-ekran-secimi',
 'AVM ve Mağazalar için Doğru LED Ekran Seçimi',
 'Alışveriş merkezi yönetimi veya mağaza sahibi olarak LED ekran yatırımı yaparken dikkat etmeniz gereken 7 kritik faktör.',
 '<h2>AVM ve Mağaza LED Ekran Rehberi</h2><p>Perakendede dijital dokunuş noktaları son 5 yılda patladı. Ancak yanlış seçim, yatırımınızın boşa gitmesine neden olabilir. İşte dikkat etmeniz gereken 7 kritik kriter:</p><h3>1. Konum Analizi</h3><p>Ekran nerede olacak? Atrium, mağaza girişi, vitrin, food court kuyruğu — her biri farklı çözüm gerektirir. Yaya trafiği yönü, bakış açısı ve ışık koşulları belirleyici.</p><h3>2. Parlaklık</h3><p>Vitrinde güneş altındaysa en az 2500 nit, iç mekan mağaza girişi için 1000-1500 nit yeterli. Yetersiz parlaklık = görülmeyen mesaj.</p><h3>3. Piksel Aralığı</h3><p>İzleme mesafesi 2 metreden azsa P1.86 veya P2 seçin. 3-5 metre için P2.5 ideal. Uzaktan bakılacaksa P4-P5 yeterli, maliyeti düşük.</p><h3>4. İçerik Yönetimi</h3><p>Ne sıklıkla içerik değiştireceksiniz? Tek bir uzaktan yönetim yazılımı ile tüm ekranları yönetebilecek misiniz? (Biz Novastar, Colorlight ve Linsn sistemlerini destekliyoruz.)</p><h3>5. Servis ve Garanti</h3><p>Ekran arızalanırsa ne kadar hızlı müdahale alırsınız? AVM gibi 24/7 çalışan yerlerde 24 saat içinde servis kritiktir.</p><h3>6. Enerji Tüketimi</h3><p>Günde 12 saat çalışan 10 m² bir ekran, ayda 400-600 kWh harcar. Elektrik faturasını hesaba katın.</p><h3>7. Yatırım Geri Dönüş</h3><p>Reklamveren kiralama yoluna gidecek misiniz? Yıllık cirolu hesap yapın.</p>',
 'TeknikLED Satış Ekibi', 'avm,perakende,rehber,yatirim',
 '2026-01-28 11:00:00', 1),

('blog', 'led-ekran-bakim-ipuclari',
 'LED Ekran Bakım ve Uzun Ömür İpuçları',
 'LED ekranların yaklaşık 100,000 saat çalışma ömrü vardır. Ancak doğru bakım yapılmazsa bu süre yarıya iner. 10 pratik bakım ipucu.',
 '<h2>LED Ekran Ömrünü Nasıl Uzatırsınız?</h2><p>LED ekran üreticisi olarak en sık aldığımız soru: "Ekranım ne kadar dayanır?" Kaliteli bir LED ekran teorik olarak 100,000 saat (yaklaşık 11 yıl sürekli çalışma) ömürlüdür. Ancak ortalama ömür pratikte 50-70 bin saat civarındadır — bakımın etkisi büyük.</p><h3>Düzenli Bakım</h3><ol><li>🧽 <strong>Haftalık toz alma:</strong> Yumuşak fırça veya mikrofiber bez. Kesinlikle ıslak bez kullanmayın.</li><li>🔌 <strong>Güç kaynağı kontrol:</strong> 6 ayda bir güç kaynağının sesine, ısısına bakın. Aşırı ısı fan arızası demek.</li><li>📡 <strong>Kontrol kartı güncelleme:</strong> Yılda bir firmware güncellemesi yapılmalı (biz ücretsiz hizmet sunuyoruz).</li><li>🌡️ <strong>Sıcaklık izleme:</strong> Çalışma sıcaklığı 40°C üzerine çıkmasın. İç mekan klimalı olmalı.</li><li>⚡ <strong>Stabilizatör:</strong> Elektrik dalgalanmaları LED''e zarar verir, mutlaka stabilizatörle bağlayın.</li></ol><h3>Yıllık Profesyonel Bakım</h3><p>Yılda bir kez profesyonel bakım almanızı öneriyoruz. Bu kapsamda yapılan işler:</p><ul><li>Her modülün ışık yoğunluğu ölçümü</li><li>Soğuk/sıcak piksel taraması</li><li>Güç kaynağı multimetre testi</li><li>Kablo bağlantı noktaları sıkılık kontrolü</li><li>Yazılım kalibrasyonu</li></ul><h3>Erken Uyarı Sinyalleri</h3><p>Şu belirtilerden biri görünürse hemen servis alın: Rengi farklılaşmış modül, yanıp sönen satır, kısmi karartı, anormal ses, aşırı ısınma.</p>',
 'TeknikLED Servis Ekibi', 'bakim,servis,led,uzun-omur',
 '2026-01-15 09:00:00', 1),

('blog', 'konferans-salonu-led-altyapi',
 'Konferans Salonu için Kapsamlı LED Altyapı Kılavuzu',
 'Bir konferans salonu LED altyapısı tasarlarken dikkat edilmesi gereken teknik ve mimari faktörler, örnek kurulumlar.',
 '<h2>Konferans Salonu LED Altyapısı: A''dan Z''ye Rehber</h2><p>Bir üniversite auditoryumu, bakanlık brifing odası veya kurumsal konferans salonu tasarlıyorsanız, LED altyapısı ses/ışık/kamera sistemleriyle birlikte en baştan planlanmalıdır.</p><h3>Temel Bileşenler</h3><ul><li><strong>Ana ekran (kürsünün arkası):</strong> Konuşmacının arkasında dev LED duvar, sunum veya kurumsal içerik</li><li><strong>Konuşmacı kürsüsü:</strong> LED veya metal kürsü, gerekirse LED modül entegreli</li><li><strong>Katılımcı ekranları:</strong> Salonda yan duvarlarda veya sıra başında küçük LED paneller</li><li><strong>Stream ekranı (arka):</strong> Konuşmacının görebileceği "takibe alınan içerik" ekranı</li></ul><h3>Teknik Hesaplamalar</h3><p>100 kişilik salon için ana ekran boyutu: <strong>genişlik 4.5-5 m, yükseklik 2.5-3 m</strong>. Piksel aralığı ön sıra mesafesine göre P2.5 ideal.</p><h3>Entegrasyon</h3><p>LED sisteminiz ses miksör, kamera, video konferans sistemi ve aydınlatma ile entegre çalışmalı. Anahtar teslim projede biz entegrasyonu da üstleniyoruz.</p>',
 'TeknikLED', 'konferans,kilavuz,salon,altyapi',
 '2026-01-05 16:00:00', 1),

('blog', 'cami-dijital-cozumler',
 'Cami ve Külliyelerde Dijital Çözümlerin Yeri',
 'Dini mekanlarda dijital içerik sunumunun doğru yapılması için pratik öneriler ve örnekler.',
 '<h2>Dini Mekanlarda Dijital Sunum: Doğru Yaklaşım</h2><p>Cami, külliye ve dini toplanma mekanlarında dijital ekranlar, cemaate daha iyi hizmet verirken mekanın maneviyatına zarar vermeden kullanılabilir.</p><h3>Nereye, Ne?</h3><ul><li><strong>Son cemaat mahalli:</strong> Vakit bilgilendirme LED</li><li><strong>Cenaze alanı:</strong> Cenaze bilgileri dış mekan LED</li><li><strong>Kütüphane/eğitim birimi:</strong> Dijital Kur''an-ı Kerim sunumu</li><li><strong>Vaaz kürsüsü:</strong> LED entegreli veya metal kürsü</li></ul><h3>Tasarım Hassasiyetleri</h3><p>Renkler koyu ve mat olmalı, kromajlı yüzeyler dini mekanda uygun değildir. Yazılar geleneksel hat stilleriyle özenli hazırlanmalı. LED parlaklığı ortam ışığına göre kısılmalı.</p>',
 'TeknikLED', 'cami,dini-mekan,dijital,kullure',
 '2025-12-18 13:00:00', 1),

-- HABERLER
('haber', 'teknikled-karatay-belediye-meclis-salonu',
 'Karatay Belediyesi Meclis Salonu LED Altyapı Projesi Teslim Edildi',
 'Ocak 2026''da teslim edilen projede tasarım tescilli LED kürsü ve 4×2.5 m ana ekran entegre edildi.',
 '<h2>Karatay Belediyesi Projesi Tamamlandı</h2><p>Konya Karatay Belediyesi yeni meclis salonu için TeknikLED tarafından geliştirilen LED altyapı Ocak 2026 tarihinde meclis başkanlığına teslim edildi. Proje kapsamında:</p><ul><li>1 adet LED kürsü P1.86 Premium Seri (başkan pozisyonu)</li><li>4×2.5 m modüler karkas + P2.5 LED duvar (meclis arka duvarı)</li><li>2 adet 96×288 LED masa (stenograf ve genel sekreter)</li><li>Tüm entegrasyon ve eğitim</li></ul><p>Toplam kurulum süresi 12 iş günü. İhale öncesi kapsamlı keşif ve 3D yerleşim çizimi yapıldı. Belediye meclis üyelerinin geri bildirimleri son derece olumlu.</p>',
 'TeknikLED', 'proje,referans,belediye,konya',
 '2026-01-30 10:00:00', 1),

('haber', 'teknikled-ar-ge-yeni-modul',
 'Ar-Ge Yatırımlarımız: Yeni Modül Tasarımı Duyuruldu',
 'Haziran 2026''da piyasaya sürülecek yeni nesil modüler karkas modülümüz için patent başvurumuz kabul edildi.',
 '<h2>Yeni Nesil Modüler Karkas: AR-02</h2><p>TeknikLED Ar-Ge ekibimizin 14 ay süren çalışması sonucunda geliştirdiği yeni nesil modüler karkas modülü AR-02 için tasarım tescil başvurumuz Türk Patent ve Marka Kurumu tarafından kabul edildi.</p><h3>AR-02''nin Getirdikleri</h3><ul><li>%30 daha hafif (8.5 kg → 6.0 kg/modül)</li><li>Genişletilmiş yük kapasitesi (60 kg/m²)</li><li>Yeni "hızlı klips" bağlantı sistemi (vida kullanımını %40 azaltır)</li><li>Entegre kablo kanalı</li></ul><p>Ürünümüz Haziran 2026''da piyasaya sürülecek. Ön sipariş avantajları için bizimle iletişime geçebilirsiniz.</p>',
 'TeknikLED Ar-Ge', 'ar-ge,yenilik,patent,moduler-karkas',
 '2026-02-05 11:00:00', 1),

('haber', 'teknikled-fuar-katilim-2026',
 '2026 IPAF Fuarına Katılıyoruz — İstanbul 15-18 Mayıs',
 'TeknikLED olarak İstanbul Lütfi Kırdar ISS''te düzenlenecek IPAF 2026 Fuarında yerimizi aldık.',
 '<h2>IPAF 2026 Fuarındayız</h2><p>15-18 Mayıs 2026 tarihleri arasında İstanbul Lütfi Kırdar Uluslararası Kongre ve Sergi Sarayı''nda düzenlenecek <strong>IPAF 2026 Profesyonel Ses, Işık ve Sahne Teknolojileri Fuarı</strong>nda stand açıyoruz.</p><p>Stand No: <strong>B-142</strong> (B Salonu, ana geçiş üstü)</p><h3>Standımızda Göreceklerinizr</h3><ul><li>Modüler karkas kurulum canlı demonstrasyon</li><li>Yeni nesil LED masa modelleri</li><li>P1.86 Premium LED kürsü</li><li>Özel tasarım stand ve etkinlik paketleri</li><li>Fuar günlerine özel indirimli fiyatlar</li></ul><p>Davet etmek istediğiniz iş ortaklarınız için ücretsiz fuar davetiye sağlıyoruz. Bizimle iletişime geçin.</p>',
 'TeknikLED', 'fuar,etkinlik,istanbul,ipaf',
 '2026-03-01 09:30:00', 1),

('haber', 'teknikled-ekibi-genisletti',
 'TeknikLED Ekibi Büyüyor: 6 Yeni Uzman Katıldı',
 '2026''da hedeflediğimiz üretim artışı için Ar-Ge, satış ve üretim departmanlarına 6 yeni uzman katıldı.',
 '<h2>Ekibimiz Büyüyor</h2><p>2026 yılı hedeflediğimiz üretim kapasitesi artışı ve yeni pazar açılımları için TeknikLED olarak kadromuza 6 yeni uzman profesyoneli kattık. Yeni ekip arkadaşlarımız:</p><ul><li>2 elektronik mühendisi (Ar-Ge)</li><li>1 kurumsal satış müdürü (İstanbul)</li><li>1 yönetici asistanı</li><li>2 montaj ustabaşı</li></ul><p>Büyüyen ekip, hem sipariş cevap süresi hem de saha destek kapasitesi açısından önemli bir artış sağlayacak. 2026''da Türkiye geneli 24 saat içinde saha desteği hedefimize daha da yaklaşıyoruz.</p>',
 'İnsan Kaynakları', 'ekip,buyume,kurumsal',
 '2026-02-20 15:00:00', 1);

-- =========================================================
-- SEED: MARKALAR - Kullanici admin panelden ekleyecek
-- Logo dosyalari elimizde olmadigi icin seed etmiyoruz.
-- Admin -> Markalar'dan 'Yeni Marka' ile logolari tek tek yukleyin.
-- Onerilen: Novastar, Colorlight, Linsn, Kystar, MBI, Nationstar,
-- Unilumin, Samsung, LG, Cree, Meanwell, Huidu
-- =========================================================

-- =========================================================
-- AYARLAR: yeni seksiyonlar icin bazi baslik/aciklama
-- =========================================================
INSERT IGNORE INTO `ayarlar` (`anahtar`, `deger`, `grup`, `tip`, `aciklama`) VALUES
('cozumler_baslik_tr', 'Kullanım Alanlarına Özel LED Çözümler', 'anasayfa', 'text', 'Cozumler section baslik TR'),
('cozumler_baslik_en', 'LED Solutions by Application Area',      'anasayfa', 'text', 'Cozumler section baslik EN'),
('cozumler_baslik_ar', 'حلول LED حسب مجال التطبيق',             'anasayfa', 'text', 'Cozumler section baslik AR'),
('blog_baslik_tr',     'Son Yazılar ve Haberler',                'anasayfa', 'text', 'Blog section baslik TR'),
('blog_baslik_en',     'Latest Articles & News',                  'anasayfa', 'text', 'Blog section baslik EN'),
('blog_baslik_ar',     'أحدث المقالات والأخبار',                 'anasayfa', 'text', 'Blog section baslik AR'),
('markalar_baslik_tr', 'Güçlü Teknoloji Ortakları',              'anasayfa', 'text', 'Markalar section baslik TR'),
('markalar_baslik_en', 'Strong Technology Partners',              'anasayfa', 'text', 'Markalar section baslik EN'),
('markalar_baslik_ar', 'شركاء تكنولوجيون أقوياء',                'anasayfa', 'text', 'Markalar section baslik AR');
