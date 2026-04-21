<?php
/**
 * install.php - TeknikLED Kurulum Sihirbazi
 * Kurulum tamamlandiktan sonra bu dosyayi silmelisiniz!
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

// Kurulum tamamlanmissa ve config.php varsa, calistigi yerde durdur
if (is_file(__DIR__ . '/config.php') && !isset($_GET['yeniden'])) {
    // Eger DB'de yonetici varsa kurulumu reddet
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/inc/db.php';
    try {
        $say = db_deger('SELECT COUNT(*) FROM yoneticiler');
        if ((int)$say > 0) {
            die('Kurulum zaten tamamlanmis. Guvenlik icin install.php dosyasini silin. <a href="index.php">Anasayfa</a> | <a href="yonetim.php">Yonetim</a>');
        }
    } catch (Throwable $e) {
        // DB yoksa devam et
    }
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$adim = isset($_GET['adim']) ? (int)$_GET['adim'] : 1;
$hata = '';
$basari = '';

// Gerekli PHP uzantilari
$gereksinimler = [
    'PHP 8.3+'    => version_compare(PHP_VERSION, '8.3', '>='),
    'PDO'         => extension_loaded('pdo'),
    'PDO MySQL'   => extension_loaded('pdo_mysql'),
    'mbstring'    => extension_loaded('mbstring'),
    'openssl'     => extension_loaded('openssl'),
    'json'        => extension_loaded('json'),
    'fileinfo'    => extension_loaded('fileinfo'),
    'ZipArchive'  => class_exists('ZipArchive'),
    'curl'        => extension_loaded('curl'),
    'uploads/ yazilabilir' => is_writable(__DIR__ . '/uploads') || (is_dir(__DIR__ . '/uploads') === false && is_writable(__DIR__)),
    'Ana dizin yazilabilir' => is_writable(__DIR__),
];
$tumBasarili = !in_array(false, $gereksinimler, true);

// Adim 2: Veritabani ayarlari formu submit
if ($adim === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbh = trim($_POST['db_host'] ?? 'localhost');
    $dbn = trim($_POST['db_name'] ?? '');
    $dbu = trim($_POST['db_user'] ?? '');
    $dbp = (string)($_POST['db_pass'] ?? '');

    if ($dbh === '' || $dbn === '' || $dbu === '') {
        $hata = 'Lutfen zorunlu alanlari doldurun.';
    } else {
        try {
            $pdo = new PDO("mysql:host={$dbh};dbname={$dbn};charset=utf8mb4", $dbu, $dbp, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            // Test basarili - bilgileri session'da tut
            session_start();
            $_SESSION['kurulum_db'] = compact('dbh','dbn','dbu','dbp');
            header('Location: install.php?adim=3');
            exit;
        } catch (PDOException $e) {
            $hata = 'Baglanti basarisiz: ' . $e->getMessage();
        }
    }
}

// Adim 3: Site bilgileri + admin formu
if ($adim === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    if (empty($_SESSION['kurulum_db'])) {
        header('Location: install.php?adim=2');
        exit;
    }

    $siteUrl   = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $siteAd    = trim($_POST['site_ad'] ?? 'TeknikLED');
    $siteMail  = trim($_POST['site_mail'] ?? '');
    $admAd     = trim($_POST['adm_ad'] ?? '');
    $admKul    = trim($_POST['adm_kul'] ?? '');
    $admMail   = trim($_POST['adm_mail'] ?? '');
    $admSif    = (string)($_POST['adm_sif'] ?? '');
    $admSif2   = (string)($_POST['adm_sif2'] ?? '');

    if ($siteUrl === '' || $siteAd === '' || $admAd === '' || $admKul === '' || $admMail === '' || $admSif === '') {
        $hata = 'Lutfen zorunlu alanlari doldurun.';
    } elseif ($admSif !== $admSif2) {
        $hata = 'Sifreler uyusmuyor.';
    } elseif (strlen($admSif) < 8) {
        $hata = 'Sifre en az 8 karakter olmali.';
    } elseif (!filter_var($admMail, FILTER_VALIDATE_EMAIL)) {
        $hata = 'Gecersiz admin e-posta';
    } else {
        try {
            $db = $_SESSION['kurulum_db'];
            $pdo = new PDO("mysql:host={$db['dbh']};dbname={$db['dbn']};charset=utf8mb4", $db['dbu'], $db['dbp'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Schema calistir
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            if ($schema === false) throw new Exception('schema.sql okunamadi');

            // Statement'leri ayir (basit parse)
            $ifadeler = array_filter(array_map('trim', preg_split('/;\s*\n/', $schema)));
            foreach ($ifadeler as $ifade) {
                $ifade = trim($ifade);
                if ($ifade === '' || str_starts_with($ifade, '--')) continue;
                // Tek satir yorum satirlarini temizle
                $satirlar = explode("\n", $ifade);
                $satirlar = array_filter($satirlar, fn($s) => !str_starts_with(trim($s), '--'));
                $temiz = implode("\n", $satirlar);
                if (trim($temiz) === '') continue;
                $pdo->exec($temiz);
            }

            // Varsayilan admin'i kaldir, yenisini ekle
            $pdo->exec("DELETE FROM yoneticiler WHERE kullanici_adi = 'admin'");
            $hash = password_hash($admSif, PASSWORD_DEFAULT);
            $st = $pdo->prepare("INSERT INTO yoneticiler (ad_soyad, kullanici_adi, eposta, sifre_hash, rol, aktif) VALUES (?, ?, ?, ?, 'super', 1)");
            $st->execute([$admAd, $admKul, $admMail, $hash]);

            // Site ayarlarini guncelle
            $pdo->prepare("UPDATE ayarlar SET deger = ? WHERE anahtar = 'firma_adi'")->execute([$siteAd]);
            if ($siteMail !== '') {
                $pdo->prepare("UPDATE ayarlar SET deger = ? WHERE anahtar = 'eposta'")->execute([$siteMail]);
            }

            // config.php olustur
            $secret = bin2hex(random_bytes(16));
            $configIcerik = configOlustur($db, $siteUrl, $siteAd, $siteMail ?: 'info@teknikled.com', $secret);

            if (file_put_contents(__DIR__ . '/config.php', $configIcerik) === false) {
                throw new Exception('config.php yazilamadi. Ana dizin yazma izinleri yeterli mi?');
            }

            unset($_SESSION['kurulum_db']);
            header('Location: install.php?adim=4');
            exit;
        } catch (Throwable $e) {
            $hata = 'Kurulum hatasi: ' . $e->getMessage();
        }
    }
}

function configOlustur(array $db, string $url, string $ad, string $mail, string $secret): string {
    $t = function ($s) { return str_replace(["'", "\\"], ["\\'", "\\\\"], (string)$s); };
    return <<<PHP
<?php
/**
 * TeknikLED Yapilandirma Dosyasi
 * install.php tarafindan otomatik olusturuldu
 * DIKKAT: Guncelleme ZIP paketleri bu dosyayi ICERMEZ.
 */

declare(strict_types=1);

// ========== VERITABANI ==========
define('DB_HOST', '{$t($db['dbh'])}');
define('DB_NAME', '{$t($db['dbn'])}');
define('DB_USER', '{$t($db['dbu'])}');
define('DB_PASS', '{$t($db['dbp'])}');
define('DB_CHARSET', 'utf8mb4');

// ========== SITE ==========
define('SITE_URL', '{$t($url)}');
define('SITE_NAME', '{$t($ad)}');
define('SITE_EMAIL', '{$t($mail)}');
define('DEFAULT_LANG', 'tr');

// ========== GUVENLIK ==========
define('APP_SECRET', '{$secret}');
define('ADMIN_KEY', 'teknikled_yonetim_2026');

// ========== E-POSTA (SMTP) - lutfen duzenleyin ==========
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', '{$t($mail)}');
define('SMTP_FROM_NAME', '{$t($ad)}');
define('SMTP_SECURE', 'tls');
define('MAIL_TO_SALES', '{$t($mail)}');

// ========== ORTAM ==========
define('DEBUG', false);
define('TIMEZONE', 'Europe/Istanbul');
define('UPLOAD_MAX_MB', 10);

// ========== GUNCELLEME SISTEMI ==========
define('UPDATE_CHECK_ENABLED', true);
define('UPDATE_GITHUB_REPO', 'codegatr/teknikled');

// Ortam
date_default_timezone_set(TIMEZONE);
mb_internal_encoding('UTF-8');

PHP;
}

?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TeknikLED Kurulum Sihirbazi</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg,#0f172a,#1e293b); min-height: 100vh; padding: 40px 20px; color: #1e293b; }
  .kutu { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
  .ust { padding: 30px; text-align: center; border-bottom: 1px solid #e2e8f0; }
  .ust .rgb { font-size: 2rem; font-weight: 900; letter-spacing: -2px; }
  .ust .rgb .r { color: #E53E3E; }
  .ust .rgb .g { color: #38A169; }
  .ust .rgb .b { color: #3182CE; }
  .ust h1 { font-size: 1.3rem; margin-top: 10px; }
  .ust p { color: #64748b; font-size: 0.9rem; margin-top: 4px; }

  .adimlar { display: flex; justify-content: center; gap: 10px; padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
  .adim-nokta { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; background: #e2e8f0; color: #64748b; }
  .adim-nokta.aktif { background: #3182CE; color: #fff; }
  .adim-nokta.tamam { background: #38A169; color: #fff; }
  .adim-ayrac { flex: 0 0 auto; align-self: center; color: #cbd5e1; }

  .gvd { padding: 30px; }
  h2 { margin-bottom: 16px; font-size: 1.15rem; }
  .ipucu { color: #64748b; font-size: 0.9rem; margin-bottom: 20px; }

  .gereksinim { display: flex; justify-content: space-between; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 0.92rem; }
  .gereksinim:last-child { border: 0; }
  .tamam-ikon { color: #38A169; font-weight: bold; }
  .hata-ikon { color: #E53E3E; font-weight: bold; }

  .alan { margin-bottom: 14px; }
  .alan label { display: block; font-weight: 500; margin-bottom: 4px; font-size: 0.88rem; }
  .alan input { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.92rem; font-family: inherit; }
  .alan input:focus { outline: none; border-color: #3182CE; box-shadow: 0 0 0 3px rgba(49,130,206,0.15); }
  .alan small { color: #64748b; font-size: 0.82rem; }

  .satir { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 560px) { .satir { grid-template-columns: 1fr; } }

  .btn { display: inline-block; padding: 11px 26px; border-radius: 8px; background: linear-gradient(90deg,#E53E3E,#38A169,#3182CE); background-size: 200% 100%; color: #fff; font-weight: 600; border: none; cursor: pointer; font-family: inherit; font-size: 0.95rem; transition: all 180ms; text-decoration: none; }
  .btn:hover { background-position: 100% 0; }
  .btn-anahat { background: transparent; color: #1e293b; border: 1px solid #e2e8f0; }
  .btn-anahat:hover { background: #f8fafc; }
  .btn-satir { display: flex; justify-content: space-between; gap: 10px; margin-top: 20px; }

  .uyari { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 0.9rem; }
  .uyari.hata { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
  .uyari.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
  .uyari.info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
</style>
</head>
<body>
<div class="kutu">

  <div class="ust">
    <div class="rgb"><span class="r">R</span><span class="g">G</span><span class="b">B</span></div>
    <h1>TeknikLED Kurulum Sihirbazi</h1>
    <p>v0.1.0 &middot; PHP <?= PHP_VERSION ?></p>
  </div>

  <div class="adimlar">
    <div class="adim-nokta <?= $adim > 1 ? 'tamam' : ($adim === 1 ? 'aktif' : '') ?>">1</div>
    <div class="adim-ayrac">―</div>
    <div class="adim-nokta <?= $adim > 2 ? 'tamam' : ($adim === 2 ? 'aktif' : '') ?>">2</div>
    <div class="adim-ayrac">―</div>
    <div class="adim-nokta <?= $adim > 3 ? 'tamam' : ($adim === 3 ? 'aktif' : '') ?>">3</div>
    <div class="adim-ayrac">―</div>
    <div class="adim-nokta <?= $adim >= 4 ? 'aktif' : '' ?>">4</div>
  </div>

  <div class="gvd">
  <?php if ($hata): ?><div class="uyari hata">⚠ <?= htmlspecialchars($hata) ?></div><?php endif; ?>
  <?php if ($basari): ?><div class="uyari ok">✓ <?= htmlspecialchars($basari) ?></div><?php endif; ?>

  <?php if ($adim === 1): ?>
    <h2>Adim 1: Sistem Gereksinimleri</h2>
    <p class="ipucu">Sunucunuzun TeknikLED icin uygun olup olmadigini kontrol ediyoruz.</p>
    <div>
    <?php foreach ($gereksinimler as $ad => $durum): ?>
      <div class="gereksinim">
        <span><?= htmlspecialchars($ad) ?></span>
        <span class="<?= $durum ? 'tamam-ikon' : 'hata-ikon' ?>"><?= $durum ? '✓ OK' : '✗ EKSIK' ?></span>
      </div>
    <?php endforeach; ?>
    </div>
    <div class="btn-satir">
      <span></span>
      <?php if ($tumBasarili): ?>
        <a href="install.php?adim=2" class="btn">Devam Et →</a>
      <?php else: ?>
        <a href="install.php?adim=1" class="btn-anahat btn">Yeniden Kontrol</a>
      <?php endif; ?>
    </div>

  <?php elseif ($adim === 2): ?>
    <h2>Adim 2: Veritabani Baglantisi</h2>
    <p class="ipucu">DirectAdmin veya hosting panelinizde olusturdugunuz MySQL veritabani bilgilerini girin.</p>
    <form method="POST">
      <div class="alan">
        <label>Sunucu (Host) *</label>
        <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
        <small>Genellikle "localhost"tur.</small>
      </div>
      <div class="alan">
        <label>Veritabani Adi *</label>
        <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
      </div>
      <div class="satir">
        <div class="alan">
          <label>Kullanici Adi *</label>
          <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
        </div>
        <div class="alan">
          <label>Sifre</label>
          <input type="password" name="db_pass" value="">
        </div>
      </div>
      <div class="btn-satir">
        <a href="install.php?adim=1" class="btn-anahat btn">← Geri</a>
        <button type="submit" class="btn">Devam Et →</button>
      </div>
    </form>

  <?php elseif ($adim === 3): ?>
    <h2>Adim 3: Site ve Yonetici Bilgileri</h2>
    <p class="ipucu">Sitenin genel bilgilerini ve admin hesabinizi olusturun.</p>
    <form method="POST">
      <h3 style="margin:20px 0 10px; font-size:1rem;">Site Bilgileri</h3>
      <div class="alan">
        <label>Site URL *</label>
        <input type="url" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? 'https://teknikled.com') ?>" required>
        <small>Sonunda / olmadan, tam adres (https://... seklinde).</small>
      </div>
      <div class="satir">
        <div class="alan">
          <label>Firma Adi *</label>
          <input type="text" name="site_ad" value="<?= htmlspecialchars($_POST['site_ad'] ?? 'TeknikLED') ?>" required>
        </div>
        <div class="alan">
          <label>Iletisim E-postasi</label>
          <input type="email" name="site_mail" value="<?= htmlspecialchars($_POST['site_mail'] ?? 'info@teknikled.com') ?>">
        </div>
      </div>

      <h3 style="margin:24px 0 10px; font-size:1rem;">Yonetici Hesabi</h3>
      <div class="alan">
        <label>Ad Soyad *</label>
        <input type="text" name="adm_ad" value="<?= htmlspecialchars($_POST['adm_ad'] ?? '') ?>" required>
      </div>
      <div class="satir">
        <div class="alan">
          <label>Kullanici Adi *</label>
          <input type="text" name="adm_kul" value="<?= htmlspecialchars($_POST['adm_kul'] ?? '') ?>" required>
        </div>
        <div class="alan">
          <label>E-posta *</label>
          <input type="email" name="adm_mail" value="<?= htmlspecialchars($_POST['adm_mail'] ?? '') ?>" required>
        </div>
      </div>
      <div class="satir">
        <div class="alan">
          <label>Sifre * (min 8 karakter)</label>
          <input type="password" name="adm_sif" minlength="8" required>
        </div>
        <div class="alan">
          <label>Sifre (tekrar) *</label>
          <input type="password" name="adm_sif2" minlength="8" required>
        </div>
      </div>
      <div class="btn-satir">
        <a href="install.php?adim=2" class="btn-anahat btn">← Geri</a>
        <button type="submit" class="btn">Kurulumu Tamamla →</button>
      </div>
    </form>

  <?php elseif ($adim === 4): ?>
    <h2>✓ Kurulum Tamamlandi!</h2>
    <div class="uyari ok">
      <strong>Basarili!</strong> TeknikLED basariyla kuruldu.
    </div>
    <p class="ipucu">Guvenlik icin simdi yapmaniz gerekenler:</p>
    <div class="gereksinim">
      <span>1. <strong>install.php</strong> dosyasini sunucudan SILIN</span>
      <span class="hata-ikon">ONEMLI</span>
    </div>
    <div class="gereksinim">
      <span>2. <strong>config.php</strong> dosyasinin izinlerini 644'e ayarlayin</span>
      <span>ONERILIR</span>
    </div>
    <div class="gereksinim">
      <span>3. SMTP bilgilerinizi config.php'de tamamlayin</span>
      <span>GEREKLI</span>
    </div>
    <div class="btn-satir">
      <a href="index.php" class="btn-anahat btn">Anasayfa</a>
      <a href="yonetim.php" class="btn">Yonetim Paneline Git →</a>
    </div>

  <?php endif; ?>
  </div>
</div>
</body>
</html>
