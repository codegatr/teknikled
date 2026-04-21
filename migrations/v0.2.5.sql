-- v0.2.5 - Slider yuksek cozunurluklu (1920x699) banner'lar ile guncellendi

-- Slider kayitlarini yeni hires banner'lara yonlendir ve
-- 3. satiri LED Kursu yerine LED Poster yapar (LED Kursu banner'i yok,
-- LED Poster banner'i ise 'istenilen olcularde' sloganiyla cok guclu)

UPDATE `slider` SET
  `gorsel`    = 'slider/01-karkas.png',
  `baslik_tr` = 'Modüler Karkas Sistemi',
  `baslik_en` = 'Modular Frame System',
  `baslik_ar` = 'نظام الإطار المعياري',
  `aciklama_tr` = 'Dünyada ilk. Tasarım tescilli, 1.20 mm galvaniz sac modüler karkas sistemi.',
  `aciklama_en` = 'World-first. Design-registered, 1.20 mm galvanized steel modular frame system.',
  `aciklama_ar` = 'الأول في العالم. إطار معياري مسجل التصميم.',
  `buton_metin_tr` = 'İncele',
  `buton_metin_en` = 'Explore',
  `buton_metin_ar` = 'استكشف',
  `buton_url` = '/urunler/moduler-karkas',
  `sira` = 1
WHERE `sira` = 1;

UPDATE `slider` SET
  `gorsel`    = 'slider/02-led-masa.png',
  `baslik_tr` = 'LED Masa Sistemleri',
  `baslik_en` = 'LED Table Systems',
  `baslik_ar` = 'أنظمة طاولات LED',
  `aciklama_tr` = 'İstenilen ölçülerde LED masalar. 96x192, 96x224, 96x256, 96x288 P1.86/P2.5.',
  `aciklama_en` = 'LED tables in custom dimensions. 96x192, 96x224, 96x256, 96x288 P1.86/P2.5.',
  `aciklama_ar` = 'طاولات LED بمقاسات مخصصة.',
  `buton_metin_tr` = 'Modelleri Gör',
  `buton_metin_en` = 'View Models',
  `buton_metin_ar` = 'عرض النماذج',
  `buton_url` = '/urunler/led-masa',
  `sira` = 2
WHERE `sira` = 2;

UPDATE `slider` SET
  `gorsel`    = 'slider/03-led-poster.png',
  `baslik_tr` = 'LED Poster Sistemleri',
  `baslik_en` = 'LED Poster Systems',
  `baslik_ar` = 'أنظمة ملصقات LED',
  `aciklama_tr` = 'İstenilen ölçülerde LED posterler. Tek ve çift taraflı, 96x192''den 64x96''ya kadar.',
  `aciklama_en` = 'LED posters in custom dimensions. Single and double-sided, from 96x192 down to 64x96.',
  `aciklama_ar` = 'ملصقات LED بمقاسات مخصصة.',
  `buton_metin_tr` = 'Detaylar',
  `buton_metin_en` = 'Details',
  `buton_metin_ar` = 'التفاصيل',
  `buton_url` = '/urunler/led-poster',
  `sira` = 3
WHERE `sira` = 3;

UPDATE `slider` SET
  `gorsel`    = 'slider/04-metal-kursu.png',
  `baslik_tr` = 'Pro Serisi Kürsüler',
  `baslik_en` = 'Pro Series Podiums',
  `baslik_ar` = 'منابر السلسلة الاحترافية',
  `aciklama_tr` = 'Şık, dayanıklı, deri kaplama. Tasarım tescilli metal kürsü.',
  `aciklama_en` = 'Sleek, durable, leather-coated. Design-registered metal podium.',
  `aciklama_ar` = 'منبر معدني متين.',
  `buton_metin_tr` = 'Teklif Al',
  `buton_metin_en` = 'Get Quote',
  `buton_metin_ar` = 'اطلب عرضاً',
  `buton_url` = '/teklif',
  `sira` = 4
WHERE `sira` = 4;
