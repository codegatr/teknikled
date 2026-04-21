# TeknikLED Kurumsal Site

![Versiyon](https://img.shields.io/badge/v-0.1.0-blue) ![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4) ![License](https://img.shields.io/badge/license-Proprietary-red)

TeknikLED (teknikled.com) için geliştirilmiş kurumsal + teklif odaklı PHP web sitesi. Tek dosya mimarisi, sıfır framework, PDO/MySQL, DirectAdmin shared hosting için optimize edilmiştir.

**Geliştirici:** [CODEGA](https://codega.com.tr) — Konya

---

## İçindekiler

- Özellikler
- Gereksinimler
- Kurulum
- Dosya Yapısı
- Yönetim Paneli
- Çoklu Dil (TR / EN / AR)
- Güncelleme Sistemi (GitHub Releases)
- SMTP / E-posta
- API Endpointleri
- Güvenlik
- Geliştirici Notları

---

## Özellikler

- **Kurumsal + Teklif odaklı:** 5 ana kategori (Modüler Karkas, LED Masa, LED Kürsü, LED Poster Kasa, CNC LED Modül Kasaları), ürün detayı, galeri, teklif formu, referans projeleri, CMS sayfalar.
- **3 Dil desteği:** Türkçe (varsayılan), İngilizce, Arapça (RTL desteği ile).
- **Açık/beyaz tema:** RGB aksan renkleri (kırmızı / yeşil / mavi — logodaki LED harflerine uygun).
- **Admin paneli:** Ürün, kategori, referans, teklif, mesaj, CMS sayfa, ayar, yönetici, log, güncelleme yönetimi; tek dosyada (`yonetim.php`).
- **Kurulum sihirbazı:** 4 adımlı `install.php` ile DB + admin + config.php oluşturur.
- **ZIP tabanlı güncelleme:** GitHub Releases üzerinden tek tıkla otomatik güncelleme; `config.php`, `uploads/`, `updates/` **asla ezilmez**.
- **Sıfır bağımlılık:** PHPMailer yok — çekirdek SMTP implementasyonu dahili. Composer yok, framework yok.
- **SEO dostu:** URL rewrite (`.htaccess`), otomatik `sitemap.xml`, 3 dil için ayrı canonical, Open Graph.
- **Güvenli:** CSRF her form'da, prepared statements, honeypot, rate limit, bcrypt şifre, oturum 8 saat.
- **LED Hesaplayıcı:** `api/hesapla.php` — piksel aralığı, ölçü ve modül sayısı hesaplaması.

---

## Gereksinimler

- PHP **8.3+**
- MySQL / MariaDB **5.7+** (utf8mb4)
- Uzantılar: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `fileinfo`, `curl`, `ZipArchive`
- Apache + `mod_rewrite` (DirectAdmin / cPanel / Plesk uyumlu)
- `uploads/`, `updates/` yazılabilir
- Kurulumda ana dizin yazılabilir (`config.php` oluşması için)

---

## Kurulum

### 1. Dosyaları yükle

ZIP paketini indirip tüm içeriği site kök dizinine (`public_html/` veya `domains/teknikled.com/public_html/`) çıkarın.

```bash
unzip teknikled-v0.1.0.zip -d public_html/
```

### 2. Veritabanı oluştur

DirectAdmin / cPanel panelinizden yeni bir MySQL veritabanı + kullanıcı oluşturun. Örnek:
- Veritabanı: `kullanici_teknikled`
- Kullanıcı: `kullanici_tlusr`
- Şifre: güçlü bir şifre

### 3. Kurulum sihirbazını çalıştır

Tarayıcıdan `https://teknikled.com/install.php` adresine gidin. 4 adımda:

1. **Gereksinim kontrolü** — tüm uzantılar yeşil olmalı.
2. **Veritabanı bilgileri** — oluşturduğunuz DB bilgilerini girin.
3. **Site + Admin bilgileri** — site URL, firma adı, admin hesabı.
4. **Tamamlandı!**

### 4. Güvenlik adımları (ZORUNLU)

Kurulum bittikten sonra **derhal**:

```bash
rm install.php             # Kurulum dosyasını sil
chmod 644 config.php       # Config izinlerini kilitle
```

### 5. SMTP'yi tamamla

`config.php` dosyasını açıp SMTP bilgilerinizi girin:

```php
define('SMTP_HOST', 'mail.teknikled.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'info@teknikled.com');
define('SMTP_PASS', 'eposta_sifreniz');
define('SMTP_SECURE', 'tls');  // tls | ssl | ''
define('MAIL_TO_SALES', 'satis@teknikled.com');  // Teklif bildirim adresi
```

---

## Dosya Yapısı

```
teknikled/
├── index.php              # Public router + tüm görünümler (980 satır)
├── yonetim.php            # Admin paneli (2000+ satır, modüler)
├── install.php            # Kurulum sihirbazı (kurulumdan sonra silinmelidir)
├── config.php             # DB/SMTP/güvenlik tanımları (install.php tarafından üretilir)
├── config.sample.php      # Örnek config (versiyon kontrolünde)
├── manifest.json          # Versiyon bilgisi (updater kullanır)
├── schema.sql             # Veritabanı şeması + başlangıç verileri
├── .htaccess              # URL rewrite + güvenlik header'ları
├── README.md              # Bu dosya
│
├── inc/                   # Core PHP sınıfları (dışa kapalı)
│   ├── db.php             # PDO wrapper (db_ekle, db_liste, db_satir, db_deger, ...)
│   ├── helpers.php        # e(), slug(), csrf_token, ayar(), dosya_yukle, dil_alan, ...
│   ├── auth.php           # Auth sınıfı — giriş/çıkış/korumalı/rol
│   ├── i18n.php           # I18n sınıfı — TR/EN/AR + RTL + URL çeviri
│   ├── mailer.php         # Sıfır bağımlılık SMTP (STARTTLS/SSL/AUTH LOGIN)
│   └── updater.php        # GitHub Releases ZIP updater
│
├── lang/                  # Çeviri dosyaları
│   ├── tr.php
│   ├── en.php
│   └── ar.php
│
├── api/                   # AJAX endpoint'leri
│   ├── teklif.php         # Teklif formu POST (CSRF, honeypot, rate-limit, SMTP bildirim)
│   ├── iletisim.php       # İletişim formu POST
│   └── hesapla.php        # LED modül/watt hesaplayıcı
│
├── assets/
│   ├── css/
│   │   ├── style.css      # Light tema + RGB aksan (public)
│   │   ├── rtl.css        # Arapça RTL override
│   │   └── yonetim.css    # Admin paneli
│   ├── js/
│   │   ├── app.js         # Mobil menü + AJAX form submit
│   │   └── yonetim.js     # Admin: dil sekmeleri, resim önizleme, slug, galeri sil
│   └── img/
│       ├── logo.png       # Site logosu
│       ├── favicon.png    # Tarayıcı ikonu
│       └── placeholder.png
│
├── uploads/               # Yüklenen görseller (güncellemede korunur)
│   ├── .htaccess          # PHP çalıştırma engeli
│   ├── urunler/
│   ├── kategoriler/
│   └── referanslar/
│
└── updates/               # ZIP güncellemeleri (güncellemede korunur)
    └── .htaccess          # Tamamen dışa kapalı
```

---

## Yönetim Paneli

URL: `https://teknikled.com/yonetim.php`

Modüller:

| Modül | Açıklama |
|---|---|
| **Panel** | İstatistikler, son teklifler, son mesajlar |
| **Ürünler** | Tam CRUD — TR/EN/AR, ana görsel + galeri, özellikler (JSON), piksel/ölçü/ağırlık, vitrin/yeni rozeti, SEO |
| **Kategoriler** | Çoklu dil + ikon + sıra + durum + SEO |
| **Referanslar** | Proje referansları — müşteri, lokasyon, sektör, tarih, galeri |
| **Teklifler** | Durum yönetimi (yeni / incelendi / teklif verildi / kazanıldı / kaybedildi / iptal), notlar, filtreleme, sayfalama |
| **Mesajlar** | İletişim mesajları, okundu/okunmamış toggle |
| **Sayfalar** | CMS — hakkımızda, kurumsal, KVKK, gizlilik, vb. |
| **Ayarlar** | Grup bazlı ayarlar (genel, iletişim, sosyal, anasayfa, sistem) |
| **Yöneticiler** | Super rol — admin CRUD, şifre, rol yönetimi |
| **Güncelleme** | GitHub Releases son sürümü kontrol et + tek tıkla uygula |
| **Log** | Tüm admin işlemleri denetim günlüğü |
| **Profil** | Kendi şifre/bilgi güncelleme |

**Roller:**
- `super` — tüm modüllere erişim (yöneticiler, güncelleme, log dahil)
- `editor` — içerik yönetimi (yöneticiler, log, güncelleme görmez)

---

## Çoklu Dil (TR / EN / AR)

URL yapısı:

- TR (varsayılan): `https://teknikled.com/urunler` (prefix yok)
- EN: `https://teknikled.com/en/products` → aslında `https://teknikled.com/en/urunler`
- AR: `https://teknikled.com/ar/urunler` (RTL otomatik aktif)

Dil seçimi:
1. URL prefix'i (`/en/` veya `/ar/`)
2. Session (`$_SESSION['lang']`)
3. Varsayılan (`DEFAULT_LANG`)

Arapça seçildiğinde `<html dir="rtl">` otomatik, `rtl.css` yüklenir, form alanları ve layout tersine çevrilir.

Yeni çeviri eklemek için `lang/tr.php`, `lang/en.php`, `lang/ar.php` dosyalarına aynı anahtarı eklemeniz yeterlidir. Veritabanı içeriği (ürün, kategori, vb.) admin panelden üç dilde girilir.

---

## Güncelleme Sistemi (GitHub Releases)

TeknikLED, GitHub Releases üzerinden ZIP tabanlı güncelleme sistemine sahiptir.

### Yeni sürüm yayımlama (geliştirici tarafı)

1. Yerel değişiklikleri tamamla, `manifest.json` içindeki `"version"` alanını güncelle (ör. `0.1.0` → `0.2.0`).
2. Değişiklikleri commit + push et.
3. GitHub üzerinde **Releases → Draft a new release** tıkla.
4. Tag: `v0.2.0` (veya `0.2.0`).
5. ZIP paketi oluştur:
   ```bash
   cd /path/to/teknikled
   zip -r teknikled-v0.2.0.zip . \
     -x "config.php" \
     -x "uploads/*" -x "uploads/**" \
     -x "updates/*" -x "updates/**" \
     -x ".git/*" -x "*.DS_Store"
   ```
6. ZIP'i release'a asset olarak yükle.
7. **Publish release**.

### Kullanıcı tarafı uygulaması

Admin paneli → **Güncelleme** → **Güncelleme Kontrol Et** → yeni sürüm varsa **İndir ve Uygula**.

### Korunan dosyalar (ASLA ezilmez)

- `config.php` — DB/SMTP bilgileri
- `uploads/` — yüklenen görseller
- `updates/` — ZIP arşivi
- `.htaccess.custom` — özel Apache yapılandırması (varsa)

Güncelleme akışı: ZIP indir → `updates/` geçici klasöre çıkar → korunan yollar hariç tüm dosyaları kök dizine kopyala → geçici klasörleri temizle.

---

## SMTP / E-posta

`inc/mailer.php` — sıfır bağımlılık SMTP implementasyonu. Desteklenenler:

- `tls` (STARTTLS, port 587)
- `ssl` (port 465)
- `''` (boş — düz SMTP, PHP `mail()` fallback)

Teklif ve iletişim formları otomatik olarak `MAIL_TO_SALES` adresine HTML şablonlu bildirim gönderir.

Teklif bildirim şablonu: RGB gradient başlık + tablolu veri + admin paneline link.

---

## API Endpointleri

| Endpoint | Metot | Açıklama |
|---|---|---|
| `/api/teklif.php` | POST | Teklif formu gönderimi (CSRF + honeypot + rate-limit + SMTP) |
| `/api/iletisim.php` | POST | İletişim formu gönderimi |
| `/api/hesapla.php` | POST / GET | LED modül/watt/çözünürlük hesaplama |

Hesaplayıcı örneği:

```
POST /api/hesapla.php
piksel=P2.5&genislik_cm=384&yukseklik_cm=256
```

Yanıt:
```json
{
  "ok": true,
  "piksel": "P2.5",
  "modul_olcu_mm": "320 x 160",
  "yatay_modul": 12, "dikey_modul": 16,
  "toplam_modul": 192,
  "metrekare": 9.83,
  "cozunurluk": "1536 x 1024 px",
  "ortalama_watt": 3456, "max_watt": 5760
}
```

---

## Güvenlik

- **CSRF:** Her form'da `csrf_token()` ile oluşturulan 32 byte rastgele token + `csrf_dogrula()` kontrolü.
- **SQL Injection:** Tüm sorgular PDO prepared statements kullanır. `db_ekle/guncelle/sil` helper'ları parametre bind eder.
- **XSS:** Tüm kullanıcı girdileri `e()` (`htmlspecialchars`) ile escape edilir. Admin HTML girdisi sadece TextArea bazında (CMS sayfalar ve ürün açıklamaları).
- **Dosya yükleme:** `finfo` MIME type kontrolü (`image/jpeg|png|webp|gif`), boyut limiti (`UPLOAD_MAX_MB`), rastgele ad (tarih + `bin2hex(random_bytes(4))`).
- **Şifre:** `password_hash(PASSWORD_DEFAULT)` → bcrypt. Brute force yavaşlatma (`usleep(400000-900000)`).
- **Oturum:** 8 saat timeout, `session_regenerate_id(true)` girişte.
- **Admin koruması:** `Auth::korumali()` her admin sayfasında.
- **Honeypot:** Form'larda gizli `website` alanı. Bot doldururursa OK döner, veri kaydedilmez.
- **Rate limit:** Teklif ve iletişim formlarında session bazlı 30 saniyelik throttle.
- **Hassas klasörler:** `.htaccess` ile `/inc/`, `/updates/`, `/uploads/*.php` erişimi engellenir.
- **Header'lar:** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` aktif.

---

## Geliştirici Notları

### Veritabanı helper'ları (`inc/db.php`)

```php
db();                                         // PDO instance
db_satir('SELECT * FROM urunler WHERE id = :id', ['id' => 5]);
db_liste('SELECT * FROM urunler WHERE aktif = 1');
db_deger('SELECT COUNT(*) FROM urunler');
db_ekle('urunler', ['ad_tr' => 'X', 'slug' => 'x']);
db_guncelle('urunler', ['ad_tr' => 'Y'], 'id = :id', ['id' => 5]);
db_sil('urunler', 'id = :id', ['id' => 5]);
```

### i18n

```php
t('menu.urunler');            // Çeviri getir
dil();                        // Aktif dil kodu (tr|en|ar)
I18n::rtl();                  // bool — RTL mi
I18n::cevirUrl('en');         // Mevcut URL'yi EN'e çevir
dil_alan($urun, 'ad');        // ad_tr / ad_en / ad_ar fallback
```

### URL helper'ları

```php
url('urunler/moduler-karkas');  // Dil prefix ekler, SITE_URL koyar
asset('css/style.css');         // /assets/css/style.css
upload('urunler/abc.jpg');      // /uploads/urunler/abc.jpg
```

### Log

```php
log_yaz('urun_ekle', 'ID: 15', Auth::mevcutAdmin()['id']);
```

### Flash mesaj

```php
flash_ekle('ok', 'Kayıt başarılı.');       // ok | hata | info | warn
// Admin layout'ta otomatik gösterilir
```

---

## Sürüm Geçmişi

- **v0.1.0** (2026-04-20) — İlk sürüm. Kurumsal + teklif, TR/EN/AR, admin panel, updater.

---

## Lisans

© 2026 TeknikLED / CODEGA. Tüm hakları saklıdır. Bu yazılım TeknikLED için özel olarak geliştirilmiştir.

---

## İletişim / Destek

- **Site:** [codega.com.tr](https://codega.com.tr)
- **Repo:** [github.com/codegatr/teknikled](https://github.com/codegatr/teknikled)
