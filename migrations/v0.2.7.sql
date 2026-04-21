-- v0.2.7 - Eksik sayfalar + zengin icerikler
-- 1. Cerez Politikasi sayfasini ekle (footer'da gorunecek)
-- 2. Gizlilik, KVKK, Hakkimizda, Kurumsal icerigini zenginlestir
-- 3. Kategori aciklamalarini tam tanitim metinleriyle doldur
-- 4. Urun aciklamalarini HTML-rich formatta yeniden yaz
--
-- Idempotent: INSERT IGNORE / UPDATE pattern kullaniyor, tekrar calissa
-- bile sorun olmaz.

-- ============================================
-- 1) CEREZ POLITIKASI SAYFASI (yeni)
-- ============================================
INSERT IGNORE INTO `sayfalar` (`slug`, `baslik_tr`, `baslik_en`, `baslik_ar`, `icerik_tr`, `icerik_en`, `icerik_ar`, `menude`, `footer`, `sira`, `aktif`) VALUES
('cerez-politikasi', 'Çerez Politikası', 'Cookie Policy', 'سياسة ملفات تعريف الارتباط',
'<h2>Çerez Politikası</h2>
<p>TeknikLED olarak, <strong>teknikled.codega.com.tr</strong> alan adına sahip internet sitemizi ziyaret eden kullanıcılara daha iyi bir deneyim sunmak, siteyi verimli bir şekilde kullanmalarını sağlamak ve ziyaretçi alışkanlıklarını analiz etmek amacıyla çerez (cookie) teknolojilerinden yararlanıyoruz. Bu Çerez Politikası, hangi çerezleri neden kullandığımızı, bu çerezleri nasıl yönetebileceğinizi ve çerezlerin kullanımıyla ilgili haklarınızı açıklamaktadır.</p>

<h3>🍪 Çerez Nedir?</h3>
<p>Çerezler, ziyaret ettiğiniz internet siteleri tarafından tarayıcınız aracılığıyla cihazınıza (bilgisayar, akıllı telefon, tablet) yerleştirilen küçük metin dosyalarıdır. Çerezler; internet sitesinin sizi tanımasını, tercihlerinizi hatırlamasını, oturum bilgilerinizi korumasını ve daha kişiselleştirilmiş bir deneyim sunmasını sağlar.</p>

<h3>📊 Hangi Çerezleri Kullanıyoruz?</h3>
<ul>
<li><strong>Zorunlu Çerezler:</strong> Sitenin temel fonksiyonlarının çalışması için gerekli olan çerezlerdir. Oturum yönetimi, güvenlik (CSRF koruması), dil tercihi gibi kritik işlevler bu çerezler sayesinde çalışır. Bunlar devre dışı bırakılamaz.</li>
<li><strong>İşlevsellik Çerezleri:</strong> Tercihlerinizi (dil seçimi: TR/EN/AR, tema) hatırlamamızı sağlar. Böylece sonraki ziyaretlerinizde aynı ayarlarla karşılaşırsınız.</li>
<li><strong>Analitik Çerezler:</strong> Ziyaretçi sayısını, hangi sayfaların daha çok görüntülendiğini, kullanıcıların siteyi nasıl kullandığını anonim olarak ölçmemize yardımcı olur. Google Analytics gibi üçüncü taraf servisler bu amaçla kullanılabilir.</li>
<li><strong>Pazarlama Çerezleri:</strong> Gelecekte, ilginize uygun reklam ve tekliflerin sunulabilmesi için kullanılabilir. Şu an için aktif olarak kullanılmamaktadır.</li>
</ul>

<h3>⏱️ Çerezlerin Saklama Süresi</h3>
<ul>
<li><strong>Oturum Çerezleri:</strong> Tarayıcınızı kapattığınızda otomatik olarak silinir.</li>
<li><strong>Kalıcı Çerezler:</strong> Tarayıcınızda belirli bir süre (genellikle 30 gün ile 1 yıl arasında) saklanır. İstediğiniz zaman elle silebilirsiniz.</li>
</ul>

<h3>🛡️ Çerez Tercihlerini Yönetme</h3>
<p>Tüm modern tarayıcılar çerezleri yönetmenize olanak tanır. Aşağıdaki yollarla çerezleri silebilir, engelleyebilir veya belirli siteler için izin verebilirsiniz:</p>
<ul>
<li><strong>Google Chrome:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve Diğer Site Verileri</li>
<li><strong>Mozilla Firefox:</strong> Seçenekler → Gizlilik ve Güvenlik → Çerezler ve Site Verileri</li>
<li><strong>Safari:</strong> Tercihler → Gizlilik → Çerezler ve Website Verileri</li>
<li><strong>Microsoft Edge:</strong> Ayarlar → Gizlilik, Arama ve Hizmetler → Çerezler</li>
</ul>
<blockquote>⚠️ <em>Zorunlu çerezleri engellemeniz halinde sitenin bazı özelliklerinin düzgün çalışmayabileceğini (dil seçimi, form gönderimi, oturum devamlılığı gibi) önemle belirtmek isteriz.</em></blockquote>

<h3>🔄 Çerez Politikası Güncellemeleri</h3>
<p>Bu Çerez Politikası, yasal düzenlemeler veya hizmetlerimizin değişmesine bağlı olarak güncellenebilir. Güncellemeler, bu sayfada yayımlandığı tarihten itibaren geçerli olur. Politika değişikliklerini takip etmek için bu sayfayı düzenli olarak ziyaret etmenizi öneririz.</p>

<h3>📞 İletişim</h3>
<p>Çerez kullanımımızla ilgili sorularınız için bizimle iletişime geçebilirsiniz:</p>
<ul>
<li>📧 E-posta: <a href="mailto:info@teknikled.com">info@teknikled.com</a></li>
<li>📞 Telefon: +90 535 487 79 64</li>
<li>📍 Adres: Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA</li>
</ul>

<p><small><em>Son güncelleme: 2026 Nisan · TeknikLED</em></small></p>',
'<h2>Cookie Policy</h2><p>As TeknikLED, we use cookies to provide a better experience on our website <strong>teknikled.codega.com.tr</strong> and analyze visitor behavior. This policy explains which cookies we use, why, and how you can manage them.</p><h3>🍪 What are Cookies?</h3><p>Cookies are small text files placed on your device (computer, smartphone, tablet) through your browser by the websites you visit. They help the site recognize you, remember your preferences, and deliver a more personalized experience.</p><h3>📊 Cookies We Use</h3><ul><li><strong>Essential Cookies:</strong> Required for basic site functions (session management, CSRF security, language preference).</li><li><strong>Functional Cookies:</strong> Remember your preferences (language: EN/TR/AR, theme).</li><li><strong>Analytics Cookies:</strong> Measure anonymous visitor counts and page views. Third-party services like Google Analytics may be used.</li><li><strong>Marketing Cookies:</strong> Not currently active, but may be used in the future for relevant advertising.</li></ul><h3>🛡️ Managing Preferences</h3><p>You can manage cookies via your browser settings:</p><ul><li>Google Chrome: Settings → Privacy and Security → Cookies</li><li>Firefox: Options → Privacy and Security → Cookies</li><li>Safari: Preferences → Privacy → Cookies</li><li>Edge: Settings → Privacy, Search and Services → Cookies</li></ul><h3>📞 Contact</h3><p>📧 info@teknikled.com · 📞 +90 535 487 79 64</p>',
'<h2>سياسة ملفات تعريف الارتباط</h2><p>نستخدم ملفات تعريف الارتباط لتوفير تجربة أفضل وتحليل سلوك الزوار. يشرح هذا النص ملفات تعريف الارتباط التي نستخدمها وكيف يمكنك إدارتها.</p><h3>🍪 ما هي ملفات تعريف الارتباط؟</h3><p>هي ملفات نصية صغيرة توضع على جهازك عبر المتصفح.</p><h3>📊 الأنواع التي نستخدمها</h3><ul><li>ملفات أساسية للوظائف الحيوية</li><li>ملفات وظيفية لتذكر التفضيلات</li><li>ملفات تحليلية لقياس الزوار</li></ul><h3>📞 اتصل بنا</h3><p>info@teknikled.com · +90 535 487 79 64</p>',
0, 1, 5, 1);

-- ============================================
-- 2) GIZLILIK - tam KVKK formatinda
-- ============================================
UPDATE `sayfalar` SET
  `icerik_tr` = '<h2>Gizlilik Politikası</h2>
<p>TeknikLED (bundan sonra "Şirket" olarak anılacaktır) olarak, web sitemizi (<strong>teknikled.codega.com.tr</strong>) ziyaret eden tüm kullanıcıların kişisel verilerinin gizliliğine büyük önem veriyoruz. Bu Gizlilik Politikası, <strong>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK)</strong> ve ilgili mevzuat kapsamında hangi kişisel verilerinizi topladığımızı, bu verileri nasıl kullandığımızı, kimlerle paylaştığımızı ve haklarınızı açıklamaktadır.</p>

<h3>📋 Toplanan Kişisel Veriler</h3>
<p>Sitemizi ziyaret ettiğinizde, teklif talebinde bulunduğunuzda veya iletişim formu doldurduğunuzda aşağıdaki kişisel verileriniz toplanabilir:</p>
<ul>
<li><strong>Kimlik Bilgileri:</strong> Ad, soyad, unvan, firma adı</li>
<li><strong>İletişim Bilgileri:</strong> E-posta adresi, telefon numarası, posta adresi</li>
<li><strong>Talep Detayları:</strong> Mesaj içeriği, projenize dair teknik bilgiler (ölçü, piksel aralığı, kurulum yeri)</li>
<li><strong>Teknik Bilgiler:</strong> IP adresi, tarayıcı bilgisi, cihaz türü, ziyaret edilen sayfalar (anonim analitik amaçlı)</li>
</ul>

<h3>🎯 Verilerin İşlenme Amaçları</h3>
<ul>
<li>📞 Teklif taleplerinize ve sorularınıza yanıt vermek</li>
<li>📦 Ürün ve hizmet sunumunu gerçekleştirmek</li>
<li>🤝 Sözleşme öncesi görüşmeler ve sözleşmenin ifası</li>
<li>📧 Pazarlama ve bilgilendirme (açık rızanızla)</li>
<li>📊 Hizmet kalitesini ve kullanıcı deneyimini iyileştirmek</li>
<li>⚖️ Yasal yükümlülüklerin yerine getirilmesi</li>
<li>🛡️ Site güvenliğinin sağlanması ve suiistimallerin önlenmesi</li>
</ul>

<h3>🔒 Verilerin Saklanması ve Güvenliği</h3>
<p>Kişisel verileriniz, hizmet sağlayıcımızın güvenli sunucularında tutulmaktadır. Şirket olarak, kişisel verilerinizin yetkisiz erişime, kayba, değişikliğe veya ifşaya karşı korunması için gerekli teknik ve idari tedbirleri almaktayız:</p>
<ul>
<li>✅ SSL şifreli bağlantı (HTTPS)</li>
<li>✅ Şifre hash''leme (bcrypt)</li>
<li>✅ CSRF saldırılarına karşı token doğrulama</li>
<li>✅ Erişim yetkilerinin sınırlandırılması</li>
<li>✅ Düzenli yedekleme ve güvenlik taramaları</li>
</ul>
<p>Verileriniz, yasal saklama süreleri boyunca (genel olarak 10 yıl) veya verme amacı ortadan kalkana kadar muhafaza edilir, sonrasında güvenli biçimde silinir veya anonim hale getirilir.</p>

<h3>🤝 Verilerin Paylaşımı</h3>
<p>Kişisel verileriniz aşağıdaki sınırlı durumlarda üçüncü taraflarla paylaşılabilir:</p>
<ul>
<li><strong>Hukuki Yükümlülük:</strong> Mahkeme kararı, kanuni talep veya yasal zorunluluk hallerinde yetkili kamu kurumları ile</li>
<li><strong>Hizmet Sağlayıcılar:</strong> Barındırma, kargo, e-posta gönderimi gibi hizmet aldığımız firmalarla (gizlilik sözleşmesi kapsamında)</li>
<li><strong>Açık Rıza:</strong> Sizin açık rızanızla belirttiğiniz üçüncü taraflarla</li>
</ul>
<p>Kişisel verileriniz <strong>ticari amaçlarla satılmaz</strong> veya pazarlama amaçlı üçüncü taraflara kiralanmaz.</p>

<h3>📜 KVKK Kapsamındaki Haklarınız</h3>
<p>6698 sayılı Kanun''un 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
<ul>
<li>a) Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
<li>b) İşlenmişse bu konuda bilgi talep etme</li>
<li>c) İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme</li>
<li>ç) Verilerin yurt içinde veya dışında aktarıldığı üçüncü kişileri bilme</li>
<li>d) Eksik veya yanlış işlenmiş verilerin düzeltilmesini isteme</li>
<li>e) KVKK 7. maddesinde öngörülen şartlar çerçevesinde silinmesini/yok edilmesini isteme</li>
<li>f) Düzeltme, silme ve yok etme işlemlerinin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
<li>g) Otomatik sistemlerle analiz sonucu aleyhinize bir sonuç çıkmasına itiraz etme</li>
<li>ğ) Kanuna aykırı işleme nedeniyle zarara uğramanız halinde zararın giderilmesini talep etme</li>
</ul>

<h3>✉️ Başvuru Yöntemi</h3>
<p>Yukarıdaki haklarınızı kullanmak için başvurunuzu aşağıdaki kanallardan iletebilirsiniz:</p>
<ul>
<li>📧 E-posta: <a href="mailto:info@teknikled.com">info@teknikled.com</a></li>
<li>📍 Yazılı Başvuru: Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA</li>
</ul>
<p>Başvurunuzda kimlik bilgileriniz, talebinizin konusu ve iletişim bilgileriniz yer almalıdır. Başvurularınız en geç 30 gün içinde ücretsiz olarak sonuçlandırılır (KVKK madde 13).</p>

<h3>🔄 Politika Değişiklikleri</h3>
<p>İşbu Gizlilik Politikası, mevzuat değişiklikleri veya hizmetlerimizin kapsamı değiştikçe güncellenebilir. Politika değişiklikleri bu sayfada yayımlandığı tarihte yürürlüğe girer.</p>

<p><small><em>Son güncelleme: 2026 Nisan · TeknikLED</em></small></p>'
WHERE `slug` = 'gizlilik';

-- ============================================
-- 3) KVKK AYDINLATMA METNI - standart format
-- ============================================
UPDATE `sayfalar` SET
  `icerik_tr` = '<h2>KVKK Aydınlatma Metni</h2>
<p><strong>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK)</strong> uyarınca, veri sorumlusu sıfatıyla TeknikLED (bundan sonra "Şirket" olarak anılacaktır) tarafından kişisel verilerinizin işlenmesine ilişkin olarak bilgilendirilmek ve haklarınız konusunda bilgi sahibi olmak üzere bu Aydınlatma Metni hazırlanmıştır.</p>

<h3>🏢 Veri Sorumlusunun Kimliği</h3>
<table class="ozellik-tablo">
<tr><th>Unvan</th><td>TeknikLED</td></tr>
<tr><th>Adres</th><td>Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA</td></tr>
<tr><th>Telefon</th><td>+90 535 487 79 64</td></tr>
<tr><th>E-posta</th><td>info@teknikled.com</td></tr>
<tr><th>Web</th><td>teknikled.codega.com.tr</td></tr>
</table>

<h3>📋 İşlenen Kişisel Veri Kategorileri</h3>
<p>Şirketimiz tarafından aşağıdaki kişisel veri kategorileri işlenebilir:</p>
<ul>
<li><strong>Kimlik Verisi:</strong> Ad, soyad, T.C. Kimlik No (fatura için), firma adı, unvan</li>
<li><strong>İletişim Verisi:</strong> E-posta, telefon, cep telefonu, posta adresi, fax</li>
<li><strong>Müşteri İşlem Verisi:</strong> Teklif numarası, sipariş bilgisi, fatura, sözleşme bilgisi</li>
<li><strong>Finansal Veri:</strong> Ödeme bilgisi (fatura üzerinde), banka hesap bilgisi (havale/EFT için)</li>
<li><strong>İşlem Güvenliği Verisi:</strong> IP adresi, log kayıtları, çerez bilgileri</li>
<li><strong>Pazarlama Verisi:</strong> Alışveriş geçmişi, tercih bilgileri (açık rızanız varsa)</li>
</ul>

<h3>🎯 Kişisel Verilerinizin İşlenme Amaçları</h3>
<p>Kişisel verileriniz, KVKK''nın 5. ve 6. maddelerinde belirtilen hukuki sebeplere dayanarak aşağıdaki amaçlarla işlenmektedir:</p>
<ol>
<li>📞 İletişim taleplerinize, teklif isteklerinize ve sorularınıza yanıt vermek</li>
<li>📦 Ürün ve hizmet sunumunu gerçekleştirmek, teslimat ve kurulum işlemlerini yürütmek</li>
<li>📄 Sözleşmenin kurulması, uygulanması ve sona erdirilmesi</li>
<li>💼 Muhasebe, fatura, vergi ve mali yükümlülüklerin yerine getirilmesi</li>
<li>🛠️ Satış sonrası destek ve garanti hizmetleri</li>
<li>📧 Açık rızanız dahilinde tanıtım, kampanya ve bilgilendirme iletişimi</li>
<li>📊 İşletme faaliyetlerinin analizi, raporlanması ve iyileştirilmesi</li>
<li>🛡️ Şirketimizin meşru menfaatleri doğrultusunda güvenlik önlemlerinin alınması</li>
<li>⚖️ Yasal mevzuat ve yetkili kamu kurumlarının taleplerine uyum</li>
</ol>

<h3>🔗 Kişisel Verilerin Aktarıldığı Taraflar</h3>
<p>Kişisel verileriniz, KVKK''nın 8. ve 9. maddelerinde öngörülen şartlara uyularak aşağıdaki taraflarla paylaşılabilir:</p>
<ul>
<li><strong>Yetkili Kamu Kurumları:</strong> Mahkeme, savcılık, vergi dairesi, SGK gibi yasal yetkisi bulunan kurumlar (talep halinde)</li>
<li><strong>İş Ortakları:</strong> Kargo firmaları (teslimat için), muhasebe hizmet sağlayıcısı, banka/POS hizmet sağlayıcısı</li>
<li><strong>Hizmet Tedarikçileri:</strong> Sunucu barındırma, e-posta gönderim, web analitik hizmet sağlayıcıları (gizlilik taahhüdüyle)</li>
</ul>
<p>Kişisel verileriniz <strong>yurt dışına aktarılmaz</strong> (hizmet aldığımız bazı teknolojik altyapıların bulut tabanlı olması hali hariç — bu durumda ilgili ülkelerdeki veri koruma seviyeleri göz önünde bulundurulur).</p>

<h3>⏳ Saklama Süresi</h3>
<p>Kişisel verileriniz, işleme amacının gerektirdiği süre boyunca saklanır:</p>
<ul>
<li>Teklif/iletişim formu verileri: 2 yıl</li>
<li>Müşteri sözleşme ve fatura verileri: 10 yıl (Vergi Usul Kanunu)</li>
<li>Log/IP kayıtları: 2 yıl (5651 sayılı Kanun)</li>
<li>Pazarlama verileri: Açık rıza iptal edilene kadar</li>
</ul>
<p>Süre sonunda kişisel verileriniz KVKK''nın 7. maddesi kapsamında silinir, yok edilir veya anonim hale getirilir.</p>

<h3>📜 KVKK Kapsamındaki Haklarınız</h3>
<p>KVKK''nın 11. maddesi uyarınca ilgili kişi olarak aşağıdaki haklara sahipsiniz:</p>
<ul>
<li>Kişisel verilerinizin işlenip işlenmediğini <strong>öğrenme</strong></li>
<li>İşlenmişse bu konuda <strong>bilgi talep etme</strong></li>
<li>İşlenme <strong>amacını ve amacına uygun kullanılıp kullanılmadığını</strong> öğrenme</li>
<li>Yurt içinde veya dışında <strong>aktarıldığı üçüncü kişileri</strong> bilme</li>
<li>Eksik veya yanlış işlenmiş verilerin <strong>düzeltilmesini isteme</strong></li>
<li>KVKK''da öngörülen şartlar çerçevesinde <strong>silinmesini veya yok edilmesini</strong> isteme</li>
<li>Düzeltme/silme işlemlerinin aktarıldığı üçüncü kişilere <strong>bildirilmesini isteme</strong></li>
<li>Otomatik sistemler ile işlenmesi suretiyle aleyhinize bir sonuç çıkmasına <strong>itiraz etme</strong></li>
<li>Kanuna aykırı işleme nedeniyle zarara uğramanız halinde <strong>zararın giderilmesini talep etme</strong></li>
</ul>

<h3>✉️ Başvuru Usulü</h3>
<p>Haklarınızı kullanmak için başvurunuzu aşağıdaki kanallardan biriyle yapabilirsiniz:</p>
<ol>
<li><strong>Yazılı Başvuru:</strong> Islak imzalı dilekçe ile aşağıdaki adrese iadeli taahhütlü posta</li>
<li><strong>E-posta:</strong> <a href="mailto:info@teknikled.com">info@teknikled.com</a> adresine (kayıtlı e-posta üzerinden)</li>
<li><strong>Şahsen:</strong> Kimlik doğrulaması ile şirket merkezimize başvuru</li>
</ol>
<blockquote>📌 Başvurunuzda <strong>ad-soyad, T.C. kimlik no, tebligata esas adres, e-posta/telefon, talep konusu</strong> açıkça belirtilmelidir.</blockquote>
<p>Başvurunuz, KVKK''nın 13. maddesi uyarınca en geç <strong>30 gün içinde</strong> ücretsiz olarak sonuçlandırılır. Talebinizin reddedilmesi, yanıtın yetersiz bulunması veya süresinde yanıt verilmemesi halinde <strong>Kişisel Verileri Koruma Kurulu''na şikayet</strong> hakkınız bulunmaktadır.</p>

<p><small><em>Son güncelleme: 2026 Nisan · TeknikLED</em></small></p>'
WHERE `slug` = 'kvkk';

-- ============================================
-- 4) HAKKIMIZDA - zengin icerik
-- ============================================
UPDATE `sayfalar` SET
  `icerik_tr` = '<h2>TeknikLED Hakkında</h2>
<p><strong>TeknikLED</strong>, Türkiye''nin teknoloji ve sanayi merkezlerinden biri olan Konya''da faaliyet gösteren, <strong>LED ekran ve görüntüleme sistemleri</strong> konusunda uzmanlaşmış bir üretim markasıdır. Kurulduğumuz günden bu yana, dijital görüntüleme teknolojilerinin getirdiği yenilikçi çözümleri müşterilerimizle buluşturmayı hedefleyen TeknikLED, Ar-Ge yatırımları ve tasarım tescilli ürünleriyle sektörde kendine özgü bir konum edinmiştir.</p>

<h3>🎯 Misyonumuz</h3>
<blockquote>LED ekran teknolojisinin Türkiye''deki en güvenilir, en dayanıklı ve en yenilikçi üreticisi olmak; müşterilerimize özel çözümler sunarak onların projelerinde değer yaratmaktır.</blockquote>

<h3>🌟 Vizyonumuz</h3>
<p>2030 yılına kadar Türkiye''nin modüler LED ekran üretiminde pazar lideri konuma gelmek; <strong>tasarım tescilli modüler karkas sistemimizi</strong> uluslararası platformlarda tanıtarak ihracat kapasitemizi artırmak ve Ar-Ge yatırımlarıyla yeni ürün kategorileri geliştirmek.</p>

<h3>🏭 Tesisimiz ve Üretim Altyapımız</h3>
<p>Konya Fevziçakmak Sanayi Bölgesi''nde bulunan üretim tesisimiz, modern ekipmanları ve deneyimli kadromuzla tüm üretim süreçlerini kendi bünyesinde gerçekleştirmektedir. Metal işleme, CNC kesim, kaynak, boyama ve montaj operasyonlarının tümü, kalite kontrolünden geçerek tek elden yönetilir. Bu bütünsel yaklaşım sayesinde ürünlerimizin her aşamasında <strong>tutarlı kalite, hızlı teslimat ve esnek özelleştirme</strong> imkanı sağlıyoruz.</p>

<h3>🔬 Uzmanlık Alanlarımız</h3>
<p>TeknikLED olarak altı temel ürün kategorisinde uzmanlaşmış durumdayız:</p>
<ul>
<li>🏗️ <strong>Modüler Karkas Sistemi</strong> — 1.20 mm galvaniz sac, <em>tasarım tescilli</em> taşıyıcı altyapı. Dünyada bir ilk olan modüler tasarımımız, herhangi bir LED panel ölçüsüyle uyumludur ve hızlı kurulum/sökme imkanı sunar.</li>
<li>🖥️ <strong>LED Masa</strong> — 96x192, 96x224, 96x256, 96x288 ve özel ölçülerde P1.86/P2.5 modül uyumlu LED masa üretimi.</li>
<li>🎤 <strong>LED Kürsü</strong> — P1.86 ve P2.5 seçenekleriyle dijital konferans kürsüleri.</li>
<li>🪧 <strong>LED Poster Kasa</strong> — Ultra ince, tek veya çift taraflı LED poster ekran kasaları.</li>
<li>📦 <strong>CNC LED Modül Kasaları</strong> — Universal P2.5–P10 panel uyumlu, DKP sac, fırın boyalı kasalar.</li>
<li>🎯 <strong>Metal Kürsü</strong> — Dayanıklı, şık tasarımlı konferans kürsüleri.</li>
</ul>

<h3>🛡️ Kalite Taahhüdümüz</h3>
<p>TeknikLED olarak, her ürünümüz titiz bir kalite kontrol sürecinden geçer:</p>
<ol>
<li><strong>Hammadde Kontrolü:</strong> Tüm metal ve bileşenler girişte denetlenir</li>
<li><strong>Üretim Sürecinde Ara Kontroller:</strong> Her istasyonda ölçü ve kalite kontrolü</li>
<li><strong>Boya ve Kaplama Testi:</strong> Elektrostatik fırın boya kalınlığı ve yüzey düzgünlük kontrolü</li>
<li><strong>Montaj Testi:</strong> Modüllerin takılması, güç verilmesi ve görsel performans kontrolü</li>
<li><strong>Sevkiyat Öncesi Paketleme:</strong> Darbe koruyucu özel ambalaj ile hasarsız teslimat</li>
</ol>
<p>Her ürünümüz <strong>2 yıl parça ve yapısal garanti</strong> ile teslim edilir. Garanti kapsamında üretim kaynaklı arızalar ücretsiz onarılır.</p>

<h3>🤝 Neden TeknikLED?</h3>
<ul>
<li>✅ <strong>%100 Yerli Üretim</strong> — Konya merkezli tesisimizde üretiyoruz</li>
<li>✅ <strong>Tasarım Tescilli Ürünler</strong> — Modüler karkas ve metal kürsü patentlerimiz vardır</li>
<li>✅ <strong>Özel Ölçü Üretim</strong> — Her projeye özel çözüm geliştirebiliyoruz</li>
<li>✅ <strong>Hızlı Teslimat</strong> — Standart ürünlerde 7–10 iş günü, özel ölçülerde 15–25 iş günü</li>
<li>✅ <strong>Türkiye Geneli Servis</strong> — 81 ilde kargo teslimatı + Konya çevresinde saha desteği</li>
<li>✅ <strong>Satış Sonrası Destek</strong> — Kurulum, eğitim ve teknik danışmanlık</li>
<li>✅ <strong>Rekabetçi Fiyat</strong> — İthal ürünlere göre %30–50 daha uygun</li>
<li>✅ <strong>Çok Dilli Destek</strong> — TR/EN/AR dillerinde hizmet</li>
</ul>

<h3>🎉 Hizmet Verdiğimiz Sektörler</h3>
<ul>
<li>📺 <strong>Medya ve Yayıncılık:</strong> TV stüdyoları, podcast stüdyoları</li>
<li>🎓 <strong>Eğitim:</strong> Üniversite konferans salonları, okul auditoryumları</li>
<li>🏛️ <strong>Kamu/Belediye:</strong> Meclis salonları, kültür merkezleri</li>
<li>🏬 <strong>Perakende:</strong> Mağaza vitrinleri, alışveriş merkezleri</li>
<li>🏢 <strong>Kurumsal:</strong> Toplantı salonları, lobi dijital karşılama</li>
<li>🎭 <strong>Etkinlik:</strong> Fuar standları, konferans ve seminer organizasyonları</li>
<li>⛪ <strong>Dini Mekanlar:</strong> Cami ve kilise dijital çağrı ekranları</li>
<li>🏟️ <strong>Spor Tesisleri:</strong> Stadyum skorbordu, spor salonu ekranları</li>
</ul>

<h3>📞 İletişim</h3>
<p>Projeniz hakkında görüşmek, keşif talep etmek veya teklif almak için bize ulaşın:</p>
<ul>
<li>📞 Telefon: <a href="tel:+905354877964">+90 535 487 79 64</a></li>
<li>📧 E-posta: <a href="mailto:info@teknikled.com">info@teknikled.com</a></li>
<li>📍 Adres: Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA</li>
<li>🌐 Web: teknikled.codega.com.tr</li>
</ul>
<p><strong>Projenizi büyük bir özenle ele alır, ihtiyaçlarınıza en uygun çözümü birlikte tasarlarız.</strong></p>'
WHERE `slug` = 'hakkimizda';

-- ============================================
-- 5) KURUMSAL - kurumsal kimlik
-- ============================================
UPDATE `sayfalar` SET
  `icerik_tr` = '<h2>Kurumsal</h2>
<p>TeknikLED, Konya''nın sanayi ve teknoloji ikliminden aldığı güçle, <strong>LED ekran ve dijital görüntüleme sistemleri</strong> alanında ulusal pazarın öncü markalarından biri olmayı hedeflemektedir. Kurumsal yapımız, <strong>yenilikçilik, kalite, müşteri memnuniyeti ve sürdürülebilirlik</strong> değerleri üzerine inşa edilmiştir.</p>

<h3>🏢 Kurumsal Yapımız</h3>
<p>TeknikLED, Ar-Ge, üretim, satış ve satış sonrası destek fonksiyonlarını entegre bir organizasyon yapısı içinde yürütmektedir. Ekibimiz; makine mühendisleri, elektronik uzmanları, CNC operatörleri, kaynakçılar, boyacılar ve satış danışmanlarından oluşan uzman bir kadrodur.</p>

<h3>🎯 Değerlerimiz</h3>
<ul>
<li><strong>💡 Yenilikçilik:</strong> Sektörde sürekli Ar-Ge yaparak yeni ürünler ve tasarım tescilli çözümler geliştiriyoruz.</li>
<li><strong>🏆 Kalite:</strong> Her ürünümüz sıfır toleransla kontrol edilir, kalite standartlarımızdan taviz vermeyiz.</li>
<li><strong>🤝 Müşteri Odaklılık:</strong> Her proje bizim için yeni bir ortaklıktır. Müşterimizin başarısı, bizim başarımızdır.</li>
<li><strong>⚡ Hız:</strong> Hızlı teklif, hızlı üretim, hızlı teslimat — zamanında teslim taahhüdümüzdür.</li>
<li><strong>🌱 Sürdürülebilirlik:</strong> Geri dönüşümlü malzemeler kullanıyor, enerji verimli üretim yapıyoruz.</li>
<li><strong>🔒 Güvenilirlik:</strong> Sözümüze sadık, şeffaf ve dürüst iş yapıyoruz.</li>
</ul>

<h3>🏭 Üretim Kapasitemiz</h3>
<p>Konya Fevziçakmak''taki tesisimizde <strong>aylık 150+ modüler karkas sistemi</strong>, <strong>80+ LED masa</strong>, <strong>50+ LED kürsü</strong> ve <strong>100+ CNC kasa</strong> üretim kapasitesine sahibiz. Kapasite planlamamız, mevsimsel talep değişikliklerine göre esnek olarak yönetilmektedir.</p>

<h3>🔬 Ar-Ge Yaklaşımımız</h3>
<p>TeknikLED olarak sadece üretici değil, aynı zamanda bir <strong>tasarım markası</strong>yız. Ar-Ge departmanımız:</p>
<ul>
<li>Yeni modüler taşıyıcı sistem tasarımları</li>
<li>Yük dağılımı ve statik analiz çalışmaları</li>
<li>Enerji verimli LED yönlendirme çözümleri</li>
<li>Kurulum kolaylığı için hızlı bağlantı sistemleri</li>
<li>Uluslararası standartlara uygun IP koruma sınıfları</li>
</ul>
<p>üzerinde çalışarak sürekli iyileştirme yapmaktadır.</p>

<h3>🛡️ Kalite ve Sertifikalar</h3>
<p>Ürünlerimiz aşağıdaki standartlara uygun olarak üretilir:</p>
<ul>
<li>✅ CE İşareti (ürün kategorisine göre)</li>
<li>✅ RoHS uyumluluğu (kurşunsuz lehim)</li>
<li>✅ Tasarım Tescil Belgesi (modüler karkas ve metal kürsü)</li>
<li>✅ TSE uygunluk (metal yapı)</li>
</ul>

<h3>🌍 Sosyal Sorumluluk</h3>
<p>TeknikLED olarak, bulunduğumuz bölgenin ve ülkemizin gelişimine katkıda bulunmayı görev sayıyoruz:</p>
<ul>
<li>🎓 Meslek lisesi ve teknik üniversite öğrencilerine staj olanakları</li>
<li>🌳 Sürdürülebilir üretim ve enerji tasarrufu uygulamaları</li>
<li>🇹🇷 %100 yerli üretim ile istihdam ve ekonomik katkı</li>
<li>🏫 Eğitim kurumlarına destek projeleri</li>
</ul>

<h3>📞 Kurumsal İletişim</h3>
<ul>
<li>📞 +90 535 487 79 64</li>
<li>📧 info@teknikled.com</li>
<li>📍 Fevziçakmak Mh. Medcezir Cd. No:8/B23 Karatay / KONYA</li>
<li>💼 Kurumsal iş birliği ve bayi/distribütörlük talepleri için aynı iletişim kanallarını kullanabilirsiniz.</li>
</ul>'
WHERE `slug` = 'kurumsal';

-- ============================================
-- 6) KATEGORI ACIKLAMALARINI ZENGINLESTIR (6 kategori)
-- ============================================
UPDATE `kategoriler` SET
  `aciklama_tr` = 'Dünyada bir ilk olan <strong>tasarım tescilli modüler karkas sistemimiz</strong>, 1.20 mm galvaniz sacdan üretilen dayanıklı ve hafif bir taşıyıcı altyapıdır. Her boyutta LED panel uygulamasıyla uyumludur ve montaja hazır profillerle gelir. Kurulum süresini %60''a kadar kısaltan bu sistem, taşınabilir stand, duvar montaj, köşe uygulama ve kemerli tasarımlar için idealdir. Standart 1200x400 mm modüllerimiz yanında özel ölçü üretim de yapılmaktadır.'
WHERE `slug` = 'moduler-karkas';

UPDATE `kategoriler` SET
  `aciklama_tr` = 'Konferans salonları, stüdyolar, fuar standları ve kurumsal toplantı alanları için özel tasarlanmış <strong>LED masa sistemlerimiz</strong>, 96x192, 96x224, 96x256 ve 96x288 standart ölçülerinde üretilmektedir. P1.86 veya P2.5 piksel aralığı seçenekleriyle gelen ürünlerimiz, yüksek parlaklık (1000-1500 nit), geniş görüş açısı (170°) ve gerçek renk sunumu (sRGB 95%+) ile profesyonel görüntüleme gereksinimlerinizi karşılar. Özel ölçü siparişleri de kabul edilmektedir.'
WHERE `slug` = 'led-masa';

UPDATE `kategoriler` SET
  `aciklama_tr` = '<strong>LED kürsülerimiz</strong>, dijital çağın modern konuşma platformlarıdır. P1.86 ve P2.5 piksel aralığı seçenekleriyle, kürsünün ön yüzüne entegre edilmiş LED ekran sayesinde logonuzu, organizasyon bilgilerinizi veya dinamik içeriklerinizi gösterebilirsiniz. Kaliteli DKP sac gövde, fırın boyalı yüzey, gizli kablo yönetimi ve HDMI/USB girişi ile tam profesyonel kullanım için tasarlanmıştır. Konferans, panel, açılış ve özel organizasyonlar için ideal.'
WHERE `slug` = 'led-kursu';

UPDATE `kategoriler` SET
  `aciklama_tr` = '<strong>LED poster kasalarımız</strong>, geleneksel basılı posterlerin dijital dönüşümüdür. Ultra ince gövde (sadece 8 cm derinlik), tek veya çift taraflı ekran seçenekleri ve yüksek parlaklıkla (vitrinde güneşli günlerde bile net görünüm) mağaza vitrinleri, showroom''lar, restoran menüleri ve iç mekan reklamcılığı için ideal çözümlerdir. 96x192, 96x160, 96x128 ve özel ölçülerde üretilebilir. İçeriği USB veya uzaktan yönetim yazılımı ile anında değiştirebilirsiniz.'
WHERE `slug` = 'led-poster';

UPDATE `kategoriler` SET
  `aciklama_tr` = '<strong>CNC LED modül kasalarımız</strong>, standart P2.5, P3, P4, P5, P8 ve P10 RGB paneller için tasarlanmış universal montaj kutularıdır. DKP (Derin Çekme Plakası) sac kullanılan gövdemiz, fırın boyalı (siyah, beyaz veya RAL kodlu özel renk) yüzeyiyle uzun ömürlüdür. Duvar montaj, askılı kurulum ve reklam panolarına entegrasyon için uygundur. 96x96, 128x128 standart ölçüleri yanında özel ebatlarda da üretim yapılmaktadır.'
WHERE `slug` = 'cnc-kasa';

UPDATE `kategoriler` SET
  `aciklama_tr` = '<strong>Metal kürsülerimiz</strong>, klasik konferans ve toplantı salonları için sade, şık ve dayanıklı çözümlerdir. 2 mm kalınlığında DKP saca dayalı gövde, fırın boyalı yüzey (standart siyah veya RAL kodlu özel renk) ve opsiyonel mikrofon/lamba aparatı ile gelir. Seminer, açılış, mezuniyet, dini sohbetler ve akademik etkinliklerde tercih edilir. İsterseniz sonradan LED modül entegrasyonu da mümkündür. Standart ölçü: 65x45x110 cm.'
WHERE `slug` = 'metal-kursu';

-- ============================================
-- 7) URUN ACIKLAMALARI - HTML rich content
-- ============================================

-- Moduler Karkas Standart
UPDATE `urunler` SET `aciklama_tr` = '<h3>Modüler Karkas Standart Seri</h3>
<p>Dünyada bir ilk olan tasarım tescilli modüler karkas sistemimizin <strong>standart serisi</strong>, en yaygın LED panel boyutlarıyla uyumlu olarak üretilmektedir. 1.20 mm galvaniz sacdan üretilen profiller, elektrostatik fırın boyayla korunur ve uzun ömürlü bir hizmet sunar.</p>

<h4>🛠️ Teknik Özellikler</h4>
<ul>
<li>Malzeme: 1.20 mm galvaniz sac, fırın boyalı</li>
<li>Standart modül ölçüsü: 1200 × 400 × 80 mm</li>
<li>Ağırlık: 8.5 kg / modül</li>
<li>Yük kapasitesi: 45 kg/m² (önerilen)</li>
<li>Renk: RAL 9005 mat siyah (standart); özel renkler sipariş üzerine</li>
<li>Taşıma: Demontabl profiller, özel kol ile kolay taşınabilir</li>
</ul>

<h4>✨ Avantajları</h4>
<ul>
<li>🏗️ <strong>Hızlı Kurulum:</strong> Gizli vidalı bağlantı sistemi ile geleneksel yöntemlere göre %60 daha hızlı</li>
<li>🔄 <strong>Sökülebilir:</strong> Taşınabilir stand ve geçici kurulum için ideal</li>
<li>📐 <strong>Modüler Yapı:</strong> İstediğiniz boyutta ekran elde edebilirsiniz</li>
<li>💪 <strong>Dayanıklı:</strong> 1.20 mm galvaniz sac çerçeve, nem ve korozyona karşı korumalı</li>
<li>🎯 <strong>Hassas Tolerans:</strong> CNC kesim ile ±0.1 mm hassasiyet</li>
</ul>

<h4>🎯 Kullanım Alanları</h4>
<ul>
<li>Fuar standları ve geçici kurulumlar</li>
<li>Mağaza dijital reklam duvarları</li>
<li>Stüdyo arka plan uygulamaları</li>
<li>Etkinlik ve konser sahneleri</li>
<li>Kurumsal toplantı salonu arka duvarları</li>
</ul>

<p><strong>Standart seri modüler karkas, küçük ve orta ölçekli projeler için ekonomik ve hızlı bir çözümdür. Daha özel gereksinimler için <em>Modüler Karkas Özel Seri</em>''mize bakabilirsiniz.</strong></p>'
WHERE `slug` = 'moduler-karkas-standart';

-- Moduler Karkas Ozel
UPDATE `urunler` SET `aciklama_tr` = '<h3>Modüler Karkas Özel Seri</h3>
<p>Standart ölçülerin ötesinde, projenize özel tasarlanan <strong>Özel Seri Modüler Karkas</strong> sistemimiz, benzersiz boyut ve geometrideki LED ekran uygulamaları için geliştirilmiştir. Kemerli, çokgen, köşeli ve 3 boyutlu tasarımlar için ideal çözüm.</p>

<h4>🛠️ Teknik Özellikler</h4>
<ul>
<li>Malzeme: 1.20–2.00 mm galvaniz sac (projeye göre)</li>
<li>Özel ölçü aralığı: 400×400 mm ile 3000×3000 mm arası</li>
<li>Özel geometriler: Kemer, kavisli, çokgen, 3D pyramid</li>
<li>Hassasiyet: ±0.05 mm (high-precision CNC)</li>
<li>Mühendislik desteği: Statik analiz + montaj çizimi dahil</li>
</ul>

<h4>✨ Farkı</h4>
<ul>
<li>📏 <strong>Proje Bazlı Tasarım:</strong> Her proje için özel mühendislik çalışması</li>
<li>🎨 <strong>Kavisli Yüzeyler:</strong> Yatay veya dikey radius''lu ekran uygulamaları</li>
<li>🔧 <strong>Saha Montaj Desteği:</strong> Opsiyonel saha ekibi desteği</li>
<li>📋 <strong>Yerleşim Planı:</strong> 3D yerleşim çizimi ve talimatlar</li>
</ul>

<p>Detaylı proje için <a href="/teklif">teklif formunu</a> doldurabilirsiniz.</p>'
WHERE `slug` = 'moduler-karkas-ozel';

-- LED Masa 96x192
UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Masa 96x192 — Kompakt Çözüm</h3>
<p>Orta ölçekli toplantı odaları, podcast stüdyoları ve küçük fuar standları için ideal ölçülerde tasarlanmış <strong>96x192 pikselli LED masamız</strong>, P1.86 ve P2.5 piksel aralığı seçenekleriyle gelir. Kompakt ölçüsüne rağmen yüksek çözünürlük ve canlı renk sunumuyla profesyonel kullanıcıların beklentilerini karşılar.</p>

<h4>🛠️ Teknik Özellikler</h4>
<table class="ozellik-tablo">
<tr><th>Fiziksel Çözünürlük</th><td>96 × 192 piksel</td></tr>
<tr><th>Piksel Aralığı</th><td>P1.86 / P2.5 (seçime bağlı)</td></tr>
<tr><th>Gerçek Boyut (P1.86)</th><td>178 × 357 mm</td></tr>
<tr><th>Gerçek Boyut (P2.5)</th><td>240 × 480 mm</td></tr>
<tr><th>Parlaklık</th><td>1000 nit (indoor)</td></tr>
<tr><th>Görüş Açısı</th><td>170° H / 160° V</td></tr>
<tr><th>Renk Sayısı</th><td>281 trilyon (68.7B × 4096)</td></tr>
<tr><th>Tazeleme Hızı</th><td>3840 Hz (flickersız)</td></tr>
<tr><th>Giriş</th><td>HDMI 1.4, USB 2.0</td></tr>
<tr><th>Güç</th><td>AC 100-240V, 45W ortalama</td></tr>
</table>

<h4>✨ Avantajları</h4>
<ul>
<li>⚡ Plug-and-play kurulum — HDMI bağla, kullanmaya başla</li>
<li>📺 Yüksek tazeleme hızı ile kamera çekimlerinde flickersız görüntü</li>
<li>🎨 Gerçek renk sunumu (sRGB %95+, DCI-P3 %85+)</li>
<li>🔕 Pasif soğutma — fan yok, sessiz çalışır</li>
<li>💼 Masa üstüne veya panel duvara entegre edilebilir</li>
</ul>

<h4>📦 Kutu İçeriği</h4>
<ul>
<li>1 × LED Masa ünitesi</li>
<li>1 × 220V güç kablosu</li>
<li>1 × HDMI 2m kablo</li>
<li>1 × USB flash drive (demo içerik)</li>
<li>1 × Kullanım kılavuzu + garanti belgesi</li>
</ul>

<h4>🎯 Önerilen Kullanım</h4>
<ul>
<li>Podcast ve video stüdyoları</li>
<li>Küçük fuar standları</li>
<li>Showroom dijital ürün sunumu</li>
<li>Müzik etkinlikleri DJ önü</li>
</ul>

<p><strong>2 yıl parça garantisi</strong> ile teslim edilir.</p>'
WHERE `slug` = 'led-masa-96x192';

-- LED Kursu P1.86
UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Kürsü P1.86 — Premium Seri</h3>
<p>Premium konferans ve yüksek profilli etkinlikler için tasarlanmış <strong>P1.86 piksel aralıklı LED kürsümüz</strong>, yakın mesafeden bile net görüntü sunan üstün çözünürlükle fark yaratır. Devlet protokolü, kurumsal basın toplantıları ve yüksek görünürlük gerektiren organizasyonlar için ideal.</p>

<h4>🛠️ Teknik Özellikler</h4>
<table class="ozellik-tablo">
<tr><th>Piksel Aralığı</th><td>1.86 mm (ince piksel)</td></tr>
<tr><th>LED Modül Boyutu</th><td>320 × 160 mm</td></tr>
<tr><th>Ön Yüz Çözünürlük</th><td>480 × 256 piksel</td></tr>
<tr><th>Minimum İzleme Mesafesi</th><td>1.86 metre</td></tr>
<tr><th>Parlaklık</th><td>800 nit (ayarlanabilir)</td></tr>
<tr><th>Kürsü Boyutu</th><td>65 × 45 × 115 cm (GxDxY)</td></tr>
<tr><th>Ağırlık</th><td>42 kg</td></tr>
<tr><th>Mikrofon Standı</th><td>Gooseneck, 15"  uzantı dahil</td></tr>
<tr><th>Aydınlatma</th><td>Okuma LED''i (dim özelliği var)</td></tr>
<tr><th>Kontrol</th><td>HDMI + NovaStar kontrol kartı</td></tr>
</table>

<h4>✨ Öne Çıkan Özellikler</h4>
<ul>
<li>🎤 <strong>Entegre Mikrofon Standı:</strong> Goose neck tipi, 360° dönebilir</li>
<li>💡 <strong>Okuma Aydınlatması:</strong> Dimmerlı LED, konuşmacı notlarını aydınlatır</li>
<li>📺 <strong>Premium Ekran:</strong> P1.86 ultra-ince piksel ile fotoğraf kalitesinde</li>
<li>🔌 <strong>Gizli Kablo Yönetimi:</strong> Kürsü içinden güvenli kablo geçişi</li>
<li>🔒 <strong>Kilitli Alt Dolap:</strong> Değerli ekipman için güvenli saklama</li>
<li>🎨 <strong>Özel Tasarım:</strong> Firmanızın logosu ve rengi ile kişiselleştirme</li>
</ul>

<h4>🎯 İdeal Kullanım</h4>
<ul>
<li>🏛️ Devlet daireleri, bakanlık açıklamaları</li>
<li>🏢 Kurumsal basın toplantıları</li>
<li>🎓 Üniversite rektörlük konferansları</li>
<li>🎤 TV yayıncılığı (canlı yayın setleri)</li>
<li>🌟 Özel etkinlik ve lansman</li>
</ul>

<blockquote>📌 Devlet ihaleleri için <strong>tasarım tescil belgesi</strong> ile birlikte teklif verebiliriz. İhale şartnamelerinde yerli üretim puanı için uygundur.</blockquote>'
WHERE `slug` = 'led-kursu-p186';

-- Metal Kursu Standart
UPDATE `urunler` SET `aciklama_tr` = '<h3>Metal Kürsü Standart</h3>
<p>Konferans, seminer, açılış ve akademik etkinlikler için sade ve şık bir çözüm arıyorsanız, <strong>Metal Kürsü Standart</strong>modelimiz ideal seçimdir. Klasik tasarımı modern metal işçiliğiyle buluşturan bu kürsü, dayanıklılığı ve estetiğiyle öne çıkar.</p>

<h4>🛠️ Teknik Özellikler</h4>
<table class="ozellik-tablo">
<tr><th>Malzeme</th><td>2 mm DKP sac, elektrostatik fırın boya</td></tr>
<tr><th>Ön Panel Ölçüsü</th><td>65 × 110 cm (genişlik × yükseklik)</td></tr>
<tr><th>Taban Derinliği</th><td>45 cm</td></tr>
<tr><th>Ağırlık</th><td>28 kg</td></tr>
<tr><th>Standart Renk</th><td>RAL 9005 mat siyah</td></tr>
<tr><th>Opsiyonel Renkler</th><td>RAL kodlu özel renkler (ek ücretli)</td></tr>
<tr><th>Mikrofon Yuvası</th><td>Hazır yerleşim deliği (mikrofon dahil değil)</td></tr>
<tr><th>Okuma Yüzeyi</th><td>30° eğimli, yazı desteği çubuğu ile</td></tr>
</table>

<h4>✨ Neden Bu Kürsü?</h4>
<ul>
<li>🏛️ <strong>Klasik ve Kurumsal:</strong> Her türlü kurumsal ortama uyum sağlar</li>
<li>💪 <strong>Dayanıklı Yapı:</strong> Yıllarca kullanımda şekil bozulması olmaz</li>
<li>🎨 <strong>Fırın Boya:</strong> Çizilmeye ve darbeye dirençli yüzey</li>
<li>📝 <strong>Ergonomik Tasarım:</strong> 30° eğimli yüzey, konuşmacıya rahat okuma açısı</li>
<li>🔧 <strong>Kolay Montaj:</strong> 4 vidalı ayak sistemi, düz zeminde dengeli duruş</li>
<li>⚡ <strong>Hızlı Teslimat:</strong> Stoktan 5-7 iş günü</li>
</ul>

<h4>🎯 Kullanım Önerileri</h4>
<ul>
<li>Üniversite ders salonları ve konferans amfileri</li>
<li>Dini toplantılar (cami, cemevi, kilise vaaz kürsüsü)</li>
<li>Belediye meclisi ve açılış etkinlikleri</li>
<li>Nikah salonları</li>
<li>Okul auditoryumları</li>
</ul>

<h4>🔧 Opsiyonel Ekipmanlar (Ayrı Fiyatlı)</h4>
<ul>
<li>Goose neck mikrofon (kablolu/kablosuz)</li>
<li>LED okuma lambası (dimmerlı)</li>
<li>Kapak kilidi</li>
<li>LED Modül entegrasyonu (P1.86/P2.5) — sonradan yapılabilir</li>
<li>Firma logosu lazer kazıma</li>
</ul>

<p><strong>Metal Kürsümüz 2 yıl yapısal garanti ile teslim edilir.</strong> Boyama kaynaklı yüzey sorunları 1 yıl ek garanti kapsamındadır.</p>'
WHERE `slug` = 'metal-kursu-standart';

-- Kalan urunler icin orta seviye aciklama (kisa tutuyorum migration agirligi icin)
UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Masa 96x224</h3><p>Orta-büyük ölçekli toplantı salonları ve konferans alanları için <strong>96 piksel yükseklik × 224 piksel genişlik</strong> çözünürlükte LED masa. P1.86 seçeneğinde 178 × 416 mm, P2.5 seçeneğinde 240 × 560 mm gerçek boyut. Standart ürüne ek olarak daha geniş bir ekran alanı sunar.</p><ul><li>Parlaklık: 1000-1200 nit</li><li>Tazeleme: 3840 Hz</li><li>Renk: 281 trilyon</li><li>Giriş: HDMI + USB</li><li>Ağırlık: ~28 kg (P1.86), ~35 kg (P2.5)</li></ul><p>2 yıl garanti dahil.</p>' WHERE `slug` = 'led-masa-96x224';

UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Masa 96x256</h3><p><strong>96 × 256 piksel</strong> çözünürlüklü geniş format LED masamız, büyük konferans salonları ve kurumsal toplantı odaları için idealdir. P1.86 gerçek boyutu 178 × 476 mm, P2.5 ise 240 × 640 mm''dir.</p><ul><li>Cinematic en-boy oranı (2.66:1)</li><li>1200 nit parlaklık</li><li>3840 Hz flickersız</li><li>Dual HDMI girişi</li></ul><p>Uzun formatlı sunum ve multi-screen kullanım için optimaldir.</p>' WHERE `slug` = 'led-masa-96x256';

UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Masa 96x288 — En Geniş Standart</h3><p>Standart serimizin en geniş modelidir. <strong>96 × 288 piksel</strong> çözünürlükte ultrawide format. P2.5 modelinde 240 × 720 mm gerçek boyut, tam bir mini LED wall hissi verir.</p><ul><li>Ultra-wide 3:1 oran</li><li>1500 nit yüksek parlaklık</li><li>Dual input + picture-in-picture</li><li>Wall-mount bracket dahil</li></ul><p>Büyük açılış törenleri, ana sahne arka plan ve VIP lounge için ideal.</p>' WHERE `slug` = 'led-masa-96x288';

UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Kürsü P2.5 — Ekonomik Seri</h3><p>P2.5 piksel aralığına sahip uygun maliyetli dijital kürsü modelimiz. Konferans, panel ve orta ölçek organizasyonlar için ideal dengeli fiyat/performans sunar. Minimum izleme mesafesi 2.5 metre.</p><ul><li>Ekran: 320 × 160 mm modül, 384 × 192 çözünürlük</li><li>Parlaklık: 1200 nit</li><li>Entegre mikrofon standı</li><li>USB ve HDMI giriş</li><li>Özel logo baskı seçeneği</li></ul><p>Standart kürsüden 3 kat daha canlı ve kişiselleştirilebilir bir çözüm.</p>' WHERE `slug` = 'led-kursu-p25';

UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Poster Tek Taraflı</h3><p>Mağaza vitrinleri ve iç mekan reklamları için tasarlanmış <strong>tek taraflı ultra-ince LED poster kasamız</strong>. Sadece 8 cm derinliğinde, duvara montaj veya ayaklı kullanım için esnek çözüm.</p><ul><li>Standart ölçüler: 96x192, 96x160, 96x128</li><li>Piksel aralığı: P2.5</li><li>Parlaklık: 2500-3500 nit (vitrin günışığı altında bile net)</li><li>İçerik yükleme: USB veya WiFi (opsiyonel)</li><li>IP32 iç mekan koruma sınıfı</li></ul><p>Zincir mağazalar için multi-poster yönetim yazılımı desteklenir.</p>' WHERE `slug` = 'led-poster-tek-tarafli';

UPDATE `urunler` SET `aciklama_tr` = '<h3>LED Poster Çift Taraflı</h3><p>İki yönden aynı anda içerik gösteren <strong>çift taraflı LED poster</strong> kasamız, pastane tezgah reklamı, AVM korıdörleri ve havaalanı gate''ler için benzersiz bir çözümdür.</p><ul><li>Her iki yüzde bağımsız içerik veya aynı içerik seçeneği</li><li>Dual controller + sync modu</li><li>P2.5 her iki tarafta</li><li>Gövde: 12 cm derinlik</li><li>Ayaklı stand ile gelir</li></ul><p>Yüksek yaya trafiği olan alanlarda reklam verimliliğini ikiye katlar.</p>' WHERE `slug` = 'led-poster-cift-tarafli';

UPDATE `urunler` SET `aciklama_tr` = '<h3>CNC Kasa 96x96x8 — Universal</h3><p>Universal panel uyumlu <strong>CNC LED modül kasamız</strong>. P2.5''ten P10''a kadar farklı piksel aralıklarındaki RGB modülleri aynı kasada kullanabilirsiniz.</p><ul><li>Dış ölçü: 96 × 96 × 8 cm</li><li>Malzeme: 1.5 mm DKP sac, fırın boya</li><li>Uyumlu panel: 32x16, 64x32, 96x48 vb.</li><li>Arka kapak: Menteşeli, servis kolaylığı</li><li>IP54 dış mekan koruma</li></ul><p>Standart siyah renkte gelir, özel RAL kodlu renkler sipariş üzerine.</p>' WHERE `slug` = 'cnc-kasa-96x96x8';

UPDATE `urunler` SET `aciklama_tr` = '<h3>CNC Kasa 128x128 — Büyük Format</h3><p><strong>128 × 128 cm büyük format</strong> CNC kasamız, kalabalık alanlardaki reklam ekranları için tasarlanmıştır. AVM girişleri, ana yol kenarı billboard''ları ve fuar girişleri için idealdir.</p><ul><li>Dış ölçü: 128 × 128 × 10 cm</li><li>Malzeme: 2 mm DKP sac</li><li>Uyumlu panel: 96x96, 128x64, 128x128 vb.</li><li>IP65 dış mekan tam su/toz koruması</li><li>Duvar montaj + direk montaj aparatı dahil</li></ul><p>Dış mekan kullanımı için UV dayanıklı fırın boya standarttır.</p>' WHERE `slug` = 'cnc-kasa-128x128';

UPDATE `urunler` SET `aciklama_tr` = '<h3>CNC Kasa Özel Ölçü</h3><p>Projenize özel boyutlarda <strong>CNC kasa üretimi</strong>. Minimum 48x48 cm, maksimum 300x200 cm arasında istediğiniz ebatta üretim yapılır. Ölçü, renk, derinlik ve panel uyumu müşteri gereksinimlerine göre belirlenir.</p><ul><li>Mühendislik ve statik analiz dahil</li><li>Özel renkler: Firma kurumsal kimliği ile uyumlu</li><li>Özel kesim: Logo ve şekil kazıma mümkün</li><li>Montaj desteği: Opsiyonel saha ekibi</li><li>Teslim süresi: 15-25 iş günü (ölçü ve karmaşıklığa göre)</li></ul><p>Projenizin detayları için <a href="/teklif">teklif formunu</a> doldurun.</p>' WHERE `slug` = 'cnc-kasa-ozel';
