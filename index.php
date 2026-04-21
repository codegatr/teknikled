<?php
/**
 * index.php - TeknikLED Ana Router ve Goruntuleyici
 * TeknikLED v0.1.0 - CODEGA
 *
 * Rotalar:
 *   /                          -> ana sayfa
 *   /urunler                   -> tum urunler
 *   /urunler/{kategori-slug}   -> kategori urunleri
 *   /urun/{urun-slug}          -> urun detayi
 *   /referanslar               -> referans listesi
 *   /referans/{slug}           -> referans detayi
 *   /hakkimizda                -> hakkimizda
 *   /sayfa/{slug}              -> dinamik CMS sayfasi
 *   /iletisim                  -> iletisim
 *   /teklif                    -> teklif formu
 *   /dil/{tr|en|ar}            -> dil secimi
 *   /{tr|en|ar}/...            -> dil prefix'li rotalar
 */

declare(strict_types=1);

// ---- CONFIG ----
if (!is_file(__DIR__ . '/config.php')) {
    // Kurulum yapilmamis
    if (is_file(__DIR__ . '/install.php')) {
        header('Location: install.php');
        exit;
    }
    die('config.php bulunamadi. Lutfen config.sample.php dosyasini config.php olarak kopyalayin.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/i18n.php';

// Oturum ve dil
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
I18n::init();

// Hata gosterimi
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

// Bakim modu kontrol
if ((int) ayar('bakim_modu', 0) === 1) {
    http_response_code(503);
    echo '<h1>Bakım Modu</h1><p>Site şu anda bakımda, en kısa sürede tekrar yayındayız.</p>';
    exit;
}

// Rotayi parcala
$rota = trim((string)($_GET['rota'] ?? ''), '/');
$parcalar = $rota === '' ? [] : explode('/', $rota);

// Ilk parca dil ise cikar (i18n zaten ayarladi)
if (!empty($parcalar[0]) && in_array($parcalar[0], ['tr','en','ar'], true)) {
    array_shift($parcalar);
}

$ilk  = $parcalar[0] ?? '';
$iki  = $parcalar[1] ?? '';

// =================================================================
// DIL DEGISTIRME
// =================================================================
if ($ilk === 'dil' && in_array($iki, ['tr','en','ar'], true)) {
    $_SESSION['lang'] = $iki;
    $geri = $_SERVER['HTTP_REFERER'] ?? SITE_URL;
    yonlendir($geri);
}

// =================================================================
// MENU VERILERI (tum sayfalarda kullanilir)
// =================================================================
$lang = dil();
$adKol   = 'ad_' . $lang;
$ozKol   = 'ozet_' . $lang;
$acKol   = 'aciklama_' . $lang;
$baslikKol = 'baslik_' . $lang;
$icerikKol = 'icerik_' . $lang;
$musKol    = 'musteri_' . $lang;

$kategoriler = db_liste('SELECT id, slug, ad_tr, ad_en, ad_ar, ikon FROM kategoriler WHERE aktif = 1 ORDER BY sira, id');
$menuSayfalari = db_liste('SELECT slug, baslik_tr, baslik_en, baslik_ar FROM sayfalar WHERE aktif = 1 AND menude = 1 ORDER BY sira, id');
$footerSayfalari = db_liste('SELECT slug, baslik_tr, baslik_en, baslik_ar FROM sayfalar WHERE aktif = 1 AND footer = 1 ORDER BY sira, id');

// =================================================================
// ROUTER
// =================================================================
$tipi   = 'anasayfa';
$veri   = [];
$metaBaslik = ayar('firma_adi', 'TeknikLED');
$metaAciklama = '';

if ($ilk === '') {
    $tipi = 'anasayfa';
    $veri['vitrin_urunler'] = db_liste(
        'SELECT u.*, k.slug AS kategori_slug FROM urunler u
         JOIN kategoriler k ON k.id = u.kategori_id
         WHERE u.aktif = 1 AND u.vitrin = 1 ORDER BY u.sira, u.id LIMIT 8'
    );
    $veri['referanslar'] = db_liste('SELECT * FROM referanslar WHERE aktif = 1 AND vitrin = 1 ORDER BY sira, id LIMIT 6');
    $metaBaslik = ayar('firma_adi') . ' - ' . ayar('slogan_' . $lang, ayar('slogan_tr'));
    $metaAciklama = ayar('hero_alt_' . $lang, ayar('hero_alt_tr'));
}
elseif ($ilk === 'urunler') {
    if ($iki !== '') {
        // Kategori urunleri
        $kat = db_satir('SELECT * FROM kategoriler WHERE slug = :s AND aktif = 1', ['s' => $iki]);
        if (!$kat) { $tipi = '404'; }
        else {
            $tipi = 'kategori';
            $veri['kategori'] = $kat;
            $veri['urunler'] = db_liste(
                'SELECT u.*, k.slug AS kategori_slug FROM urunler u
                 JOIN kategoriler k ON k.id = u.kategori_id
                 WHERE u.aktif = 1 AND u.kategori_id = :kid ORDER BY u.sira, u.id',
                ['kid' => $kat['id']]
            );
            $metaBaslik = ($kat[$adKol] ?? $kat['ad_tr']) . ' - ' . ayar('firma_adi');
            $metaAciklama = kisalt((string)($kat[$acKol] ?? $kat['aciklama_tr'] ?? ''), 160);
        }
    } else {
        $tipi = 'urunler';
        $veri['urunler'] = db_liste(
            'SELECT u.*, k.slug AS kategori_slug, k.ad_tr AS kategori_ad_tr, k.ad_en AS kategori_ad_en, k.ad_ar AS kategori_ad_ar
             FROM urunler u JOIN kategoriler k ON k.id = u.kategori_id
             WHERE u.aktif = 1 ORDER BY k.sira, u.sira, u.id'
        );
        $metaBaslik = t('menu.urunler') . ' - ' . ayar('firma_adi');
    }
}
elseif ($ilk === 'urun' && $iki !== '') {
    $u = db_satir(
        'SELECT u.*, k.slug AS kategori_slug, k.ad_tr AS kategori_ad_tr, k.ad_en AS kategori_ad_en, k.ad_ar AS kategori_ad_ar
         FROM urunler u JOIN kategoriler k ON k.id = u.kategori_id
         WHERE u.slug = :s AND u.aktif = 1',
        ['s' => $iki]
    );
    if (!$u) { $tipi = '404'; }
    else {
        $tipi = 'urun';
        $veri['urun'] = $u;
        $veri['iliskili'] = db_liste(
            'SELECT u.*, k.slug AS kategori_slug FROM urunler u
             JOIN kategoriler k ON k.id = u.kategori_id
             WHERE u.aktif = 1 AND u.kategori_id = :kid AND u.id <> :id
             ORDER BY u.sira, u.id LIMIT 4',
            ['kid' => $u['kategori_id'], 'id' => $u['id']]
        );
        // Goruntulenme artir
        db()->prepare('UPDATE urunler SET goruntulenme = goruntulenme + 1 WHERE id = :id')
            ->execute(['id' => $u['id']]);

        $metaBaslik = ($u[$adKol] ?? $u['ad_tr']) . ' - ' . ayar('firma_adi');
        $metaAciklama = kisalt((string)($u[$ozKol] ?? $u['ozet_tr'] ?? ''), 160);
    }
}
elseif ($ilk === 'referanslar') {
    $tipi = 'referanslar';
    $veri['referanslar'] = db_liste('SELECT * FROM referanslar WHERE aktif = 1 ORDER BY sira, id');
    $metaBaslik = t('menu.referanslar') . ' - ' . ayar('firma_adi');
}
elseif ($ilk === 'referans' && $iki !== '') {
    $r = db_satir('SELECT * FROM referanslar WHERE slug = :s AND aktif = 1', ['s' => $iki]);
    if (!$r) { $tipi = '404'; }
    else {
        $tipi = 'referans';
        $veri['referans'] = $r;
        $metaBaslik = ($r[$musKol] ?? $r['musteri_tr']) . ' - ' . ayar('firma_adi');
    }
}
elseif ($ilk === 'hakkimizda' || $ilk === 'sayfa') {
    $slug = $ilk === 'hakkimizda' ? 'hakkimizda' : $iki;
    $s = db_satir('SELECT * FROM sayfalar WHERE slug = :s AND aktif = 1', ['s' => $slug]);
    if (!$s) { $tipi = '404'; }
    else {
        $tipi = 'sayfa';
        $veri['sayfa'] = $s;
        $metaBaslik = ($s[$baslikKol] ?? $s['baslik_tr']) . ' - ' . ayar('firma_adi');
    }
}
elseif ($ilk === 'iletisim') {
    $tipi = 'iletisim';
    $metaBaslik = t('menu.iletisim') . ' - ' . ayar('firma_adi');
}
elseif ($ilk === 'teklif') {
    $tipi = 'teklif';
    if (!empty($_GET['urun'])) {
        $veri['urun'] = db_satir('SELECT id, ad_tr, ad_en, ad_ar, urun_kodu FROM urunler WHERE slug = :s',
            ['s' => $_GET['urun']]);
    }
    $metaBaslik = t('menu.teklif') . ' - ' . ayar('firma_adi');
}
elseif ($ilk === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo _sitemap_xml();
    exit;
}
else {
    $tipi = '404';
    http_response_code(404);
}

// =================================================================
// HTML CIKTI
// =================================================================
$rtl = I18n::rtl();
$dir = I18n::dir();
$langHtml = dil();

?><!DOCTYPE html>
<html lang="<?= e($langHtml) ?>" dir="<?= e($dir) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($metaBaslik) ?></title>
<meta name="description" content="<?= e($metaAciklama ?: ($metaBaslik)) ?>">
<meta name="theme-color" content="#ffffff">
<link rel="canonical" href="<?= e(SITE_URL . ($rota ? '/' . $rota : '/')) ?>">
<link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<?php if ($rtl): ?>
<link rel="stylesheet" href="<?= e(asset('css/rtl.css')) ?>">
<?php endif; ?>
<!-- Open Graph -->
<meta property="og:title" content="<?= e($metaBaslik) ?>">
<meta property="og:description" content="<?= e($metaAciklama) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e(SITE_URL . '/' . $rota) ?>">
<meta property="og:image" content="<?= e(asset('img/logo.png')) ?>">
<?php if ($ga = ayar('analytics_id')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
</head>
<body class="sayfa-<?= e($tipi) ?> dil-<?= e($langHtml) ?>">

<!-- UST BILGI BARI -->
<div class="ust-bar">
  <div class="sarmal">
    <div class="ust-iletisim">
      <?php if ($tel = ayar('telefon')): ?>
        <a href="tel:<?= e(preg_replace('/\s+/', '', $tel)) ?>"><span class="ikon">📞</span> <?= e($tel) ?></a>
      <?php endif; ?>
      <?php if ($em = ayar('eposta')): ?>
        <a href="mailto:<?= e($em) ?>"><span class="ikon">✉</span> <?= e($em) ?></a>
      <?php endif; ?>
    </div>
    <div class="dil-secici">
      <?php foreach (I18n::dilListesi() as $kod => $bilgi): ?>
        <a href="<?= e(I18n::cevirUrl($kod)) ?>"
           class="<?= $kod === $langHtml ? 'aktif' : '' ?>"
           title="<?= e($bilgi['ad']) ?>"><?= e($bilgi['kisa']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ANA BASLIK -->
<header class="site-bas">
  <div class="sarmal bas-sarmal">
    <a href="<?= e(url()) ?>" class="logo-alan">
      <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e(ayar('firma_adi', 'TeknikLED')) ?>">
    </a>
    <nav class="ana-menu" id="anaMenu">
      <a href="<?= e(url()) ?>" class="<?= $tipi === 'anasayfa' ? 'aktif' : '' ?>"><?= e(t('menu.anasayfa')) ?></a>
      <a href="<?= e(url('urunler')) ?>" class="<?= in_array($tipi, ['urunler','kategori','urun'], true) ? 'aktif' : '' ?>"><?= e(t('menu.urunler')) ?></a>
      <a href="<?= e(url('referanslar')) ?>" class="<?= in_array($tipi, ['referanslar','referans'], true) ? 'aktif' : '' ?>"><?= e(t('menu.referanslar')) ?></a>
      <?php foreach ($menuSayfalari as $ms): ?>
        <a href="<?= e(url('sayfa/' . $ms['slug'])) ?>" class="<?= $tipi === 'sayfa' && !empty($veri['sayfa']) && $veri['sayfa']['slug'] === $ms['slug'] ? 'aktif' : '' ?>"><?= e($ms[$baslikKol] ?? $ms['baslik_tr']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url('iletisim')) ?>" class="<?= $tipi === 'iletisim' ? 'aktif' : '' ?>"><?= e(t('menu.iletisim')) ?></a>
      <a href="<?= e(url('teklif')) ?>" class="menu-cta"><?= e(t('menu.teklif')) ?></a>
    </nav>
    <button class="menu-btn" id="menuBtn" aria-label="Menu">☰</button>
  </div>
</header>

<main class="ana-icerik">
<?php
// Rota bazli view yukle
switch ($tipi) {
    case 'anasayfa':    _view_anasayfa($veri, $kategoriler, $adKol, $ozKol, $acKol, $musKol); break;
    case 'urunler':     _view_urunler($veri, $adKol, $ozKol); break;
    case 'kategori':    _view_kategori($veri, $adKol, $ozKol, $acKol); break;
    case 'urun':        _view_urun($veri, $adKol, $ozKol, $acKol); break;
    case 'referanslar': _view_referanslar($veri, $musKol, $acKol); break;
    case 'referans':    _view_referans($veri, $musKol, $acKol); break;
    case 'sayfa':       _view_sayfa($veri, $baslikKol, $icerikKol); break;
    case 'iletisim':    _view_iletisim(); break;
    case 'teklif':      _view_teklif($veri, $adKol); break;
    case '404':         default: _view_404(); break;
}
?>
</main>

<!-- FOOTER -->
<footer class="site-alt">
  <div class="sarmal alt-sarmal">
    <div class="alt-blok">
      <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e(ayar('firma_adi')) ?>" class="alt-logo">
      <p class="alt-slogan"><?= e(ayar('slogan_' . $langHtml, ayar('slogan_tr'))) ?></p>
    </div>
    <div class="alt-blok">
      <h4><?= e(t('footer.urunler')) ?></h4>
      <ul>
      <?php foreach ($kategoriler as $k): ?>
        <li><a href="<?= e(url('urunler/' . $k['slug'])) ?>"><?= e($k[$adKol] ?? $k['ad_tr']) ?></a></li>
      <?php endforeach; ?>
      </ul>
    </div>
    <div class="alt-blok">
      <h4><?= e(t('footer.kurumsal')) ?></h4>
      <ul>
      <?php foreach ($footerSayfalari as $fs): ?>
        <li><a href="<?= e(url('sayfa/' . $fs['slug'])) ?>"><?= e($fs[$baslikKol] ?? $fs['baslik_tr']) ?></a></li>
      <?php endforeach; ?>
        <li><a href="<?= e(url('iletisim')) ?>"><?= e(t('menu.iletisim')) ?></a></li>
      </ul>
    </div>
    <div class="alt-blok">
      <h4><?= e(t('footer.iletisim')) ?></h4>
      <address>
        <?php if ($adr = ayar('adres_' . $langHtml, ayar('adres_tr'))): ?>
          <p>📍 <?= nl2br(e($adr)) ?></p>
        <?php endif; ?>
        <?php if ($tel = ayar('telefon')): ?>
          <p>📞 <a href="tel:<?= e(preg_replace('/\s+/', '', $tel)) ?>"><?= e($tel) ?></a></p>
        <?php endif; ?>
        <?php if ($ep = ayar('eposta')): ?>
          <p>✉ <a href="mailto:<?= e($ep) ?>"><?= e($ep) ?></a></p>
        <?php endif; ?>
      </address>
      <div class="sosyal">
        <?php foreach (['facebook','instagram','linkedin','youtube'] as $sm): ?>
          <?php if ($u = ayar($sm)): ?>
            <a href="<?= e($u) ?>" target="_blank" rel="noopener"><?= e(strtoupper($sm[0])) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="alt-alt">
    <div class="sarmal">
      <span>&copy; <?= date('Y') ?> <?= e(ayar('firma_adi', 'TeknikLED')) ?>. <?= e(t('footer.haklar')) ?></span>
      <span class="yapimci">
        <a href="https://codega.com.tr" target="_blank" rel="noopener"><?= e(t('footer.yapimci')) ?></a>
      </span>
    </div>
  </div>
</footer>

<!-- WhatsApp yuzen buton -->
<?php if ($wp = ayar('whatsapp')): ?>
<a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wp)) ?>" class="wa-yuzen" target="_blank" rel="noopener" aria-label="WhatsApp">
  <svg viewBox="0 0 24 24" width="28" height="28" fill="#fff"><path d="M20.52 3.48A12 12 0 0012 0C5.37 0 0 5.37 0 12a12 12 0 001.64 6.06L0 24l6.08-1.6A12 12 0 0012 24c6.63 0 12-5.37 12-12a11.94 11.94 0 00-3.48-8.52zM12 21.8a9.8 9.8 0 01-5-1.37l-.36-.21-3.61.95.96-3.52-.23-.37A9.8 9.8 0 1121.8 12 9.82 9.82 0 0112 21.8zm5.38-7.36c-.3-.15-1.76-.87-2.03-.97s-.47-.15-.67.15-.77.97-.94 1.16-.35.22-.64.07a8.07 8.07 0 01-2.38-1.47 9 9 0 01-1.65-2.06c-.17-.3 0-.46.13-.6.13-.13.3-.35.44-.52a2 2 0 00.3-.5.56.56 0 00-.03-.52c-.08-.15-.67-1.6-.92-2.2s-.5-.5-.67-.5h-.58a1.1 1.1 0 00-.8.38 3.35 3.35 0 00-1.05 2.5 5.82 5.82 0 001.22 3.1c.15.2 2.1 3.22 5.1 4.52.72.3 1.27.5 1.7.64a4.12 4.12 0 001.87.12 3.07 3.07 0 002-1.42 2.5 2.5 0 00.18-1.42c-.07-.12-.27-.2-.57-.35z"/></svg>
</a>
<?php endif; ?>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
<?php

// =================================================================
// VIEW FONKSIYONLARI
// =================================================================
function _view_anasayfa(array $veri, array $kategoriler, string $adKol, string $ozKol, string $acKol, string $musKol): void {
    $lang = dil();
    $heroBaslik = ayar('hero_baslik_' . $lang, ayar('hero_baslik_tr'));
    $heroAlt    = ayar('hero_alt_' . $lang, ayar('hero_alt_tr'));

    // Slider kayitlari (aktif olanlar)
    try {
        $sliderler = db_liste('SELECT * FROM slider WHERE aktif = 1 ORDER BY sira ASC, id ASC');
    } catch (Throwable $e) {
        $sliderler = [];  // slider tablosu yoksa sessiz gec
    }
    $baslikKol   = 'baslik_' . $lang;
    $aciklamaKol = 'aciklama_' . $lang;
    $butonKol    = 'buton_metin_' . $lang;
    ?>
    <?php if (!empty($sliderler)): ?>
    <!-- SLIDER -->
    <section class="tl-slider" id="tlSlider">
      <div class="tl-slider-ic">
        <?php foreach ($sliderler as $i => $s):
            $baslik   = $s[$baslikKol]   ?? $s['baslik_tr']   ?? '';
            $aciklama = $s[$aciklamaKol] ?? $s['aciklama_tr'] ?? '';
            $butonMet = $s[$butonKol]    ?? $s['buton_metin_tr'] ?? '';
        ?>
          <div class="tl-slide <?= $i === 0 ? 'aktif' : '' ?>" data-idx="<?= $i ?>">
            <img src="<?= e(upload($s['gorsel'])) ?>" alt="<?= e($baslik) ?>" class="tl-slide-gorsel" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
            <div class="tl-slide-icerik">
              <div class="tl-slide-kutusu">
                <?php if ($baslik): ?><h2><?= e($baslik) ?></h2><?php endif; ?>
                <?php if ($aciklama): ?><p><?= e($aciklama) ?></p><?php endif; ?>
                <?php if ($butonMet && $s['buton_url']): ?>
                  <a href="<?= e($s['buton_url']) ?>" class="btn btn-renk"><?= e($butonMet) ?></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (count($sliderler) > 1): ?>
        <button class="tl-slider-ok tl-slider-ok-sol" aria-label="Onceki">‹</button>
        <button class="tl-slider-ok tl-slider-ok-sag" aria-label="Sonraki">›</button>
        <div class="tl-slider-noktalar">
          <?php foreach ($sliderler as $i => $_): ?>
            <button class="tl-nokta <?= $i === 0 ? 'aktif' : '' ?>" data-idx="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <script>
    (function() {
      var slider = document.getElementById('tlSlider');
      if (!slider) return;
      var slideler = slider.querySelectorAll('.tl-slide');
      var noktalar = slider.querySelectorAll('.tl-nokta');
      if (slideler.length <= 1) return;

      var mevcut = 0;
      var zamanli;

      function goster(idx) {
        if (idx < 0) idx = slideler.length - 1;
        if (idx >= slideler.length) idx = 0;
        slideler[mevcut].classList.remove('aktif');
        if (noktalar[mevcut]) noktalar[mevcut].classList.remove('aktif');
        mevcut = idx;
        slideler[mevcut].classList.add('aktif');
        if (noktalar[mevcut]) noktalar[mevcut].classList.add('aktif');
      }
      function ileri()  { goster(mevcut + 1); }
      function geri()   { goster(mevcut - 1); }
      function sifirla() {
        clearInterval(zamanli);
        zamanli = setInterval(ileri, 5500);
      }

      var sol = slider.querySelector('.tl-slider-ok-sol');
      var sag = slider.querySelector('.tl-slider-ok-sag');
      if (sol) sol.addEventListener('click', function(){ geri(); sifirla(); });
      if (sag) sag.addEventListener('click', function(){ ileri(); sifirla(); });

      noktalar.forEach(function(n) {
        n.addEventListener('click', function() {
          goster(parseInt(n.dataset.idx, 10)); sifirla();
        });
      });

      // Swipe destegi (mobilde)
      var baslangicX = 0;
      slider.addEventListener('touchstart', function(e) { baslangicX = e.touches[0].clientX; }, {passive: true});
      slider.addEventListener('touchend', function(e) {
        var fark = e.changedTouches[0].clientX - baslangicX;
        if (Math.abs(fark) > 50) {
          if (fark < 0) ileri(); else geri();
          sifirla();
        }
      });

      sifirla();
    })();
    </script>
    <?php endif; ?>

    <section class="hero">
      <canvas class="hero-canvas" id="heroCanvas"></canvas>
      <div class="sarmal hero-sarmal">
        <div class="hero-metin">
          <div class="hero-rozet">TeknikLED · P1.86 / P2.5 · Konya</div>
          <h1><?= e($heroBaslik) ?></h1>
          <p class="hero-alt"><?= e($heroAlt) ?></p>
          <div class="hero-btn">
            <a href="<?= e(url('teklif')) ?>" class="btn btn-renk btn-buyuk"><?= e(t('home.hero_cta')) ?> →</a>
            <a href="<?= e(url('urunler')) ?>" class="btn btn-anahat btn-buyuk"><?= e(t('menu.urunler')) ?></a>
          </div>
        </div>
        <div class="hero-gorsel">
          <div class="hero-rgb">
            <span class="rgb-r">R</span><span class="rgb-g">G</span><span class="rgb-b">B</span>
          </div>
        </div>
      </div>
    </section>

    <script>
    // Hero LED Matrix canvas animasyonu
    (function() {
      var canvas = document.getElementById('heroCanvas');
      if (!canvas || !canvas.getContext) return;
      var ctx = canvas.getContext('2d');
      var W = 0, H = 0, dpr = window.devicePixelRatio || 1;
      var cols = 0, rows = 0;
      var cellSize = 24;
      var dots = [];
      var colors = ['#ff3b6b', '#00ff99', '#00d4ff'];

      function boyutla() {
        var rect = canvas.getBoundingClientRect();
        W = rect.width; H = rect.height;
        canvas.width = W * dpr;
        canvas.height = H * dpr;
        ctx.scale(dpr, dpr);
        cols = Math.ceil(W / cellSize);
        rows = Math.ceil(H / cellSize);
        olustur();
      }
      function olustur() {
        dots = [];
        for (var r = 0; r < rows; r++) {
          for (var c = 0; c < cols; c++) {
            dots.push({
              x: c * cellSize + cellSize / 2,
              y: r * cellSize + cellSize / 2,
              base: 0.03 + Math.random() * 0.08,
              phase: Math.random() * Math.PI * 2,
              speed: 0.0005 + Math.random() * 0.0015,
              color: colors[Math.floor(Math.random() * 3)],
              size: 1 + Math.random() * 1.5
            });
          }
        }
      }
      function ciz(t) {
        ctx.clearRect(0, 0, W, H);
        for (var i = 0; i < dots.length; i++) {
          var d = dots[i];
          var a = d.base + Math.sin(t * d.speed + d.phase) * d.base;
          if (a < 0.02) continue;
          ctx.beginPath();
          ctx.arc(d.x, d.y, d.size, 0, Math.PI * 2);
          ctx.fillStyle = d.color;
          ctx.globalAlpha = Math.min(a, 0.5);
          ctx.fill();
        }
        ctx.globalAlpha = 1;
      }
      function dongu(t) {
        ciz(t);
        requestAnimationFrame(dongu);
      }
      window.addEventListener('resize', boyutla);
      boyutla();
      requestAnimationFrame(dongu);
    })();
    </script>

    <!-- ISTATISTIK -->
    <section class="istatistik-bolumu">
      <div class="sarmal">
        <div class="istatistik-izgara">
          <div class="istatistik-kart fade-in-up">
            <span class="istatistik-sayi" data-hedef="100" data-son="+">0</span>
            <div class="istatistik-etiket"><?= e(dil() === 'en' ? 'Completed Projects' : (dil() === 'ar' ? 'مشاريع مكتملة' : 'Tamamlanan Proje')) ?></div>
          </div>
          <div class="istatistik-kart fade-in-up">
            <span class="istatistik-sayi" data-hedef="6" data-son="">0</span>
            <div class="istatistik-etiket"><?= e(dil() === 'en' ? 'Product Categories' : (dil() === 'ar' ? 'فئات المنتجات' : 'Ürün Kategorisi')) ?></div>
          </div>
          <div class="istatistik-kart fade-in-up">
            <span class="istatistik-sayi" data-hedef="100" data-son="%">0</span>
            <div class="istatistik-etiket"><?= e(dil() === 'en' ? 'Local Production' : (dil() === 'ar' ? 'إنتاج محلي' : 'Yerli Üretim')) ?></div>
          </div>
          <div class="istatistik-kart fade-in-up">
            <span class="istatistik-sayi" data-hedef="2" data-son=" <?= e(dil() === 'en' ? 'Yr' : 'Yıl') ?>">0</span>
            <div class="istatistik-etiket"><?= e(dil() === 'en' ? 'Warranty' : (dil() === 'ar' ? 'الضمان' : 'Parça Garantisi')) ?></div>
          </div>
        </div>
      </div>
    </section>

    <!-- KATEGORILER -->
    <section class="bolum kategoriler-bolumu">
      <div class="sarmal">
        <div class="bolum-bas fade-in-up">
          <h2><?= e(t('home.urun_kategori')) ?></h2>
          <p><?= e(t('home.vitrin_urun')) ?></p>
        </div>
        <div class="kategori-izgara">
          <?php foreach ($kategoriler as $i => $k): ?>
            <a href="<?= e(url('urunler/' . $k['slug'])) ?>" class="kat-kart fade-in-up" data-num="<?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?>">
              <div class="kat-ikon"><?= e($k['ikon'] ?: '◈') ?></div>
              <h3><?= e($k[$adKol] ?? $k['ad_tr']) ?></h3>
              <?php if (!empty($k['aciklama_tr']) || !empty($k[$acKol])): ?>
                <p><?= e(mb_substr($k[$acKol] ?? $k['aciklama_tr'] ?? '', 0, 100)) ?>...</p>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- NEDEN BIZ -->
    <section class="bolum neden-bolumu">
      <div class="sarmal">
        <div class="bolum-bas fade-in-up">
          <h2><?= e(t('home.nedensize')) ?></h2>
        </div>
        <div class="neden-izgara">
          <?php
          $nedenIkonlar = ['⚡', '🏭', '🛡️', '🎯'];
          foreach ([1,2,3,4] as $i): ?>
            <div class="neden-kart fade-in-up">
              <div class="neden-ikon neden-<?= $i ?>"><?= $nedenIkonlar[$i-1] ?></div>
              <h3><?= e(t('home.ozellik' . $i . '_b')) ?></h3>
              <p><?= e(t('home.ozellik' . $i . '_a')) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- VITRIN URUNLER -->
    <?php if (!empty($veri['vitrin_urunler'])): ?>
    <section class="bolum urun-bolumu">
      <div class="sarmal">
        <div class="bolum-bas">
          <h2><?= e(t('home.vitrin_urun')) ?></h2>
          <a href="<?= e(url('urunler')) ?>" class="bolum-hepsi"><?= e(t('genel.tumunu_gor')) ?> →</a>
        </div>
        <div class="urun-izgara">
          <?php foreach ($veri['vitrin_urunler'] as $u): ?>
            <article class="urun-kart">
              <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-gorsel">
                <?php if ($u['ana_gorsel']): ?>
                  <img src="<?= e(upload($u['ana_gorsel'])) ?>" alt="<?= e($u[$adKol] ?? $u['ad_tr']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="urun-placeholder">◈</div>
                <?php endif; ?>
                <?php if ($u['yeni']): ?><span class="urun-rozet"><?= e(t('genel.yeni')) ?></span><?php endif; ?>
              </a>
              <div class="urun-gvd">
                <h3><a href="<?= e(url('urun/' . $u['slug'])) ?>"><?= e($u[$adKol] ?? $u['ad_tr']) ?></a></h3>
                <?php if ($u['piksel']): ?><span class="urun-etiket"><?= e($u['piksel']) ?></span><?php endif; ?>
                <p><?= e(kisalt($u[$ozKol] ?? $u['ozet_tr'] ?? '', 90)) ?></p>
                <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-btn"><?= e(t('genel.detay')) ?></a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- REFERANSLAR -->
    <?php if (!empty($veri['referanslar'])): ?>
    <section class="bolum referans-bolumu">
      <div class="sarmal">
        <div class="bolum-bas">
          <h2><?= e(t('home.referanslar')) ?></h2>
          <a href="<?= e(url('referanslar')) ?>" class="bolum-hepsi"><?= e(t('genel.tumunu_gor')) ?> →</a>
        </div>
        <div class="referans-izgara">
          <?php foreach ($veri['referanslar'] as $r): ?>
            <a href="<?= e(url('referans/' . $r['slug'])) ?>" class="referans-kart">
              <div class="referans-gorsel">
                <?php if ($r['ana_gorsel']): ?>
                  <img src="<?= e(upload($r['ana_gorsel'])) ?>" alt="<?= e($r[$musKol] ?? $r['musteri_tr']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="referans-placeholder">🏢</div>
                <?php endif; ?>
              </div>
              <div class="referans-ust">
                <h3><?= e($r[$musKol] ?? $r['musteri_tr']) ?></h3>
                <?php if ($r['lokasyon']): ?><small>📍 <?= e($r['lokasyon']) ?></small><?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- CTA BANDI -->
    <section class="cta-band">
      <div class="sarmal cta-sarmal">
        <div>
          <h2><?= e(t('teklif.baslik')) ?></h2>
          <p><?= e(t('teklif.alt')) ?></p>
        </div>
        <a href="<?= e(url('teklif')) ?>" class="btn btn-beyaz"><?= e(t('home.hero_cta')) ?></a>
      </div>
    </section>
    <?php
}

function _view_urunler(array $veri, string $adKol, string $ozKol): void {
    ?>
    <section class="bolum">
      <div class="sarmal">
        <div class="sayfa-bas">
          <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e(t('menu.urunler')) ?></span></nav>
          <h1><?= e(t('menu.urunler')) ?></h1>
        </div>
        <?php if (empty($veri['urunler'])): ?>
          <p class="bos"><?= e(t('genel.sonuc_yok')) ?></p>
        <?php else: ?>
          <div class="urun-izgara">
            <?php foreach ($veri['urunler'] as $u): ?>
              <article class="urun-kart">
                <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-gorsel">
                  <?php if ($u['ana_gorsel']): ?>
                    <img src="<?= e(upload($u['ana_gorsel'])) ?>" alt="<?= e($u[$adKol] ?? $u['ad_tr']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="urun-placeholder">◈</div>
                  <?php endif; ?>
                </a>
                <div class="urun-gvd">
                  <small class="urun-kat"><?= e($u['kategori_' . $adKol] ?? $u['kategori_ad_tr']) ?></small>
                  <h3><a href="<?= e(url('urun/' . $u['slug'])) ?>"><?= e($u[$adKol] ?? $u['ad_tr']) ?></a></h3>
                  <?php if ($u['piksel']): ?><span class="urun-etiket"><?= e($u['piksel']) ?></span><?php endif; ?>
                  <p><?= e(kisalt($u[$ozKol] ?? $u['ozet_tr'] ?? '', 90)) ?></p>
                  <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-btn"><?= e(t('genel.detay')) ?></a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_kategori(array $veri, string $adKol, string $ozKol, string $acKol): void {
    $kat = $veri['kategori'];
    ?>
    <section class="bolum">
      <div class="sarmal">
        <div class="sayfa-bas">
          <nav class="ekmek">
            <a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> /
            <a href="<?= e(url('urunler')) ?>"><?= e(t('menu.urunler')) ?></a> /
            <span><?= e($kat[$adKol] ?? $kat['ad_tr']) ?></span>
          </nav>
          <h1><?= e($kat[$adKol] ?? $kat['ad_tr']) ?></h1>
          <?php if ($ac = $kat[$acKol] ?? $kat['aciklama_tr']): ?>
            <p class="sayfa-alt"><?= e($ac) ?></p>
          <?php endif; ?>
        </div>
        <?php if (empty($veri['urunler'])): ?>
          <p class="bos"><?= e(t('genel.sonuc_yok')) ?></p>
        <?php else: ?>
          <div class="urun-izgara">
            <?php foreach ($veri['urunler'] as $u): ?>
              <article class="urun-kart">
                <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-gorsel">
                  <?php if ($u['ana_gorsel']): ?>
                    <img src="<?= e(upload($u['ana_gorsel'])) ?>" alt="<?= e($u[$adKol] ?? $u['ad_tr']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="urun-placeholder">◈</div>
                  <?php endif; ?>
                </a>
                <div class="urun-gvd">
                  <h3><a href="<?= e(url('urun/' . $u['slug'])) ?>"><?= e($u[$adKol] ?? $u['ad_tr']) ?></a></h3>
                  <?php if ($u['piksel']): ?><span class="urun-etiket"><?= e($u['piksel']) ?></span><?php endif; ?>
                  <p><?= e(kisalt($u[$ozKol] ?? $u['ozet_tr'] ?? '', 90)) ?></p>
                  <a href="<?= e(url('urun/' . $u['slug'])) ?>" class="urun-btn"><?= e(t('genel.detay')) ?></a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_urun(array $veri, string $adKol, string $ozKol, string $acKol): void {
    $u = $veri['urun'];
    $galeri = !empty($u['galeri']) ? json_decode($u['galeri'], true) : [];
    $ozellikler = !empty($u['ozellikler_' . dil()]) ? json_decode($u['ozellikler_' . dil()], true) : [];
    if (!is_array($ozellikler) && !empty($u['ozellikler_tr'])) {
        $ozellikler = json_decode($u['ozellikler_tr'], true);
    }
    $ozellikler = is_array($ozellikler) ? $ozellikler : [];
    ?>
    <section class="bolum urun-detay">
      <div class="sarmal">
        <nav class="ekmek">
          <a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> /
          <a href="<?= e(url('urunler')) ?>"><?= e(t('menu.urunler')) ?></a> /
          <a href="<?= e(url('urunler/' . $u['kategori_slug'])) ?>"><?= e($u['kategori_' . $adKol] ?? $u['kategori_ad_tr']) ?></a> /
          <span><?= e($u[$adKol] ?? $u['ad_tr']) ?></span>
        </nav>

        <div class="urun-detay-izgara">
          <div class="urun-galeri">
            <div class="galeri-ana">
              <?php if ($u['ana_gorsel']): ?>
                <img src="<?= e(upload($u['ana_gorsel'])) ?>" alt="<?= e($u[$adKol] ?? $u['ad_tr']) ?>" id="anaGrsl">
              <?php else: ?>
                <div class="urun-placeholder buyuk">◈</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($galeri) && is_array($galeri)): ?>
              <div class="galeri-kucuk">
                <?php if ($u['ana_gorsel']): ?>
                  <img src="<?= e(upload($u['ana_gorsel'])) ?>" onclick="document.getElementById('anaGrsl').src=this.src" class="aktif">
                <?php endif; ?>
                <?php foreach ($galeri as $g): ?>
                  <img src="<?= e(upload($g)) ?>" onclick="document.getElementById('anaGrsl').src=this.src">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="urun-bilgi">
            <?php if ($u['urun_kodu']): ?>
              <small class="urun-kod"><?= e(t('genel.urun_kodu')) ?>: <?= e($u['urun_kodu']) ?></small>
            <?php endif; ?>
            <h1><?= e($u[$adKol] ?? $u['ad_tr']) ?></h1>

            <div class="urun-etiketler">
              <?php if ($u['piksel']): ?><span class="etiket etiket-mavi"><?= e(t('genel.piksel')) ?>: <?= e($u['piksel']) ?></span><?php endif; ?>
              <?php if ($u['olcu']): ?><span class="etiket"><?= e(t('genel.olcu')) ?>: <?= e($u['olcu']) ?></span><?php endif; ?>
              <?php if ($u['agirlik']): ?><span class="etiket"><?= e($u['agirlik']) ?></span><?php endif; ?>
            </div>

            <?php if ($oz = $u[$ozKol] ?? $u['ozet_tr']): ?>
              <p class="urun-ozet"><?= e($oz) ?></p>
            <?php endif; ?>

            <div class="urun-btn-grubu">
              <a href="<?= e(url('teklif') . '?urun=' . $u['slug']) ?>" class="btn btn-renk btn-buyuk"><?= e(t('genel.teklif_al')) ?></a>
              <?php if ($wp = ayar('whatsapp')): ?>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wp)) ?>?text=<?= e(urlencode(($u[$adKol] ?? $u['ad_tr']) . ' hakkinda bilgi istiyorum')) ?>" class="btn btn-wa" target="_blank" rel="noopener">💬 WhatsApp</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if ($acIcerik = $u[$acKol] ?? $u['aciklama_tr']): ?>
          <div class="urun-aciklama">
            <h2><?= e(t('genel.detay')) ?></h2>
            <div class="icerik"><?= $acIcerik /* HTML */ ?></div>
          </div>
        <?php endif; ?>

        <?php if (!empty($ozellikler)): ?>
          <div class="urun-ozellikler">
            <h2><?= e(t('genel.ozellikler')) ?></h2>
            <table class="ozellik-tablo">
              <?php foreach ($ozellikler as $o): ?>
                <tr>
                  <th><?= e($o['baslik'] ?? '') ?></th>
                  <td><?= e($o['deger'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endif; ?>

        <?php if (!empty($veri['iliskili'])): ?>
          <div class="iliskili-urun">
            <h2><?= e(t('home.vitrin_urun')) ?></h2>
            <div class="urun-izgara">
              <?php foreach ($veri['iliskili'] as $r): ?>
                <article class="urun-kart">
                  <a href="<?= e(url('urun/' . $r['slug'])) ?>" class="urun-gorsel">
                    <?php if ($r['ana_gorsel']): ?>
                      <img src="<?= e(upload($r['ana_gorsel'])) ?>" alt="<?= e($r[$adKol] ?? $r['ad_tr']) ?>" loading="lazy">
                    <?php else: ?>
                      <div class="urun-placeholder">◈</div>
                    <?php endif; ?>
                  </a>
                  <div class="urun-gvd">
                    <h3><a href="<?= e(url('urun/' . $r['slug'])) ?>"><?= e($r[$adKol] ?? $r['ad_tr']) ?></a></h3>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_referanslar(array $veri, string $musKol, string $acKol): void {
    ?>
    <section class="bolum">
      <div class="sarmal">
        <div class="sayfa-bas">
          <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e(t('menu.referanslar')) ?></span></nav>
          <h1><?= e(t('menu.referanslar')) ?></h1>
        </div>
        <?php if (empty($veri['referanslar'])): ?>
          <p class="bos"><?= e(t('genel.sonuc_yok')) ?></p>
        <?php else: ?>
          <div class="referans-izgara">
            <?php foreach ($veri['referanslar'] as $r): ?>
              <a href="<?= e(url('referans/' . $r['slug'])) ?>" class="referans-kart">
                <div class="referans-gorsel">
                  <?php if ($r['ana_gorsel']): ?>
                    <img src="<?= e(upload($r['ana_gorsel'])) ?>" alt="<?= e($r[$musKol] ?? $r['musteri_tr']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="referans-placeholder">🏢</div>
                  <?php endif; ?>
                </div>
                <div class="referans-ust">
                  <h3><?= e($r[$musKol] ?? $r['musteri_tr']) ?></h3>
                  <?php if ($r['lokasyon']): ?><small>📍 <?= e($r['lokasyon']) ?></small><?php endif; ?>
                  <?php if ($r['sektor']): ?><small class="etiket etiket-mavi"><?= e($r['sektor']) ?></small><?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_referans(array $veri, string $musKol, string $acKol): void {
    $r = $veri['referans'];
    $galeri = !empty($r['galeri']) ? json_decode($r['galeri'], true) : [];
    ?>
    <section class="bolum">
      <div class="sarmal">
        <nav class="ekmek">
          <a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> /
          <a href="<?= e(url('referanslar')) ?>"><?= e(t('menu.referanslar')) ?></a> /
          <span><?= e($r[$musKol] ?? $r['musteri_tr']) ?></span>
        </nav>
        <div class="sayfa-bas">
          <h1><?= e($r[$musKol] ?? $r['musteri_tr']) ?></h1>
          <div class="ref-meta">
            <?php if ($r['lokasyon']): ?><span>📍 <?= e($r['lokasyon']) ?></span><?php endif; ?>
            <?php if ($r['sektor']): ?><span>🏭 <?= e($r['sektor']) ?></span><?php endif; ?>
            <?php if ($r['proje_tarihi']): ?><span>📅 <?= e(tarih($r['proje_tarihi'], 'Y')) ?></span><?php endif; ?>
          </div>
        </div>
        <?php if ($r['ana_gorsel']): ?>
          <div class="ref-ana-gorsel"><img src="<?= e(upload($r['ana_gorsel'])) ?>" alt=""></div>
        <?php endif; ?>
        <?php if ($ac = $r[$acKol] ?? $r['aciklama_tr']): ?>
          <div class="icerik"><?= nl2br(e($ac)) ?></div>
        <?php endif; ?>
        <?php if (!empty($galeri) && is_array($galeri)): ?>
          <div class="ref-galeri">
            <?php foreach ($galeri as $g): ?>
              <img src="<?= e(upload($g)) ?>" alt="" loading="lazy">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_sayfa(array $veri, string $baslikKol, string $icerikKol): void {
    $s = $veri['sayfa'];
    ?>
    <section class="bolum">
      <div class="sarmal sarmal-dar">
        <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e($s[$baslikKol] ?? $s['baslik_tr']) ?></span></nav>
        <div class="sayfa-bas">
          <h1><?= e($s[$baslikKol] ?? $s['baslik_tr']) ?></h1>
        </div>
        <div class="icerik cms">
          <?= $s[$icerikKol] ?? $s['icerik_tr'] /* HTML */ ?>
        </div>
      </div>
    </section>
    <?php
}

function _view_iletisim(): void {
    ?>
    <section class="bolum">
      <div class="sarmal">
        <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e(t('menu.iletisim')) ?></span></nav>
        <div class="sayfa-bas">
          <h1><?= e(t('iletisim.baslik')) ?></h1>
          <p class="sayfa-alt"><?= e(t('iletisim.alt')) ?></p>
        </div>
        <div class="iletisim-izgara">
          <div class="iletisim-bilgi">
            <?php if ($adr = ayar('adres_' . dil(), ayar('adres_tr'))): ?>
              <div class="il-kart"><strong>📍 <?= e(t('genel.adres')) ?></strong><p><?= nl2br(e($adr)) ?></p></div>
            <?php endif; ?>
            <?php if ($tel = ayar('telefon')): ?>
              <div class="il-kart"><strong>📞 <?= e(t('genel.telefon')) ?></strong><p><a href="tel:<?= e(preg_replace('/\s+/', '', $tel)) ?>"><?= e($tel) ?></a></p></div>
            <?php endif; ?>
            <?php if ($ep = ayar('eposta')): ?>
              <div class="il-kart"><strong>✉ <?= e(t('genel.eposta')) ?></strong><p><a href="mailto:<?= e($ep) ?>"><?= e($ep) ?></a></p></div>
            <?php endif; ?>
          </div>
          <form class="il-form" id="iletisimForm" data-api="<?= e(SITE_URL) ?>/api/iletisim.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="dil" value="<?= e(dil()) ?>">
            <div class="form-satir">
              <div class="form-alan">
                <label><?= e(t('genel.ad_soyad')) ?> *</label>
                <input type="text" name="ad_soyad" required>
              </div>
              <div class="form-alan">
                <label><?= e(t('genel.eposta')) ?> *</label>
                <input type="email" name="eposta" required>
              </div>
            </div>
            <div class="form-satir">
              <div class="form-alan">
                <label><?= e(t('genel.telefon')) ?></label>
                <input type="tel" name="telefon">
              </div>
              <div class="form-alan">
                <label><?= e(t('genel.konu')) ?></label>
                <input type="text" name="konu">
              </div>
            </div>
            <div class="form-alan">
              <label><?= e(t('genel.mesaj')) ?> *</label>
              <textarea name="mesaj" rows="5" required></textarea>
            </div>
            <!-- Honeypot -->
            <div style="position:absolute;left:-9999px;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <button type="submit" class="btn btn-renk"><?= e(t('genel.gonder')) ?></button>
            <div class="form-durum" aria-live="polite"></div>
          </form>
        </div>
        <?php if ($harita = ayar('harita_iframe')): ?>
          <div class="harita-kutu"><?= $harita /* iframe */ ?></div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_teklif(array $veri, string $adKol): void {
    $seciliUrun = $veri['urun'] ?? null;
    ?>
    <section class="bolum">
      <div class="sarmal sarmal-dar">
        <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e(t('menu.teklif')) ?></span></nav>
        <div class="sayfa-bas">
          <h1><?= e(t('teklif.baslik')) ?></h1>
          <p class="sayfa-alt"><?= e(t('teklif.alt')) ?></p>
        </div>
        <?php if ($seciliUrun): ?>
          <div class="secili-urun">
            <strong><?= e(t('menu.urunler')) ?>:</strong> <?= e($seciliUrun[$adKol] ?? $seciliUrun['ad_tr']) ?>
            <?php if ($seciliUrun['urun_kodu']): ?><small>(<?= e($seciliUrun['urun_kodu']) ?>)</small><?php endif; ?>
          </div>
        <?php endif; ?>
        <form class="il-form" id="teklifForm" data-api="<?= e(SITE_URL) ?>/api/teklif.php">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="dil" value="<?= e(dil()) ?>">
          <?php if ($seciliUrun): ?><input type="hidden" name="urun_id" value="<?= e((string)$seciliUrun['id']) ?>"><?php endif; ?>

          <div class="form-satir">
            <div class="form-alan">
              <label><?= e(t('genel.ad_soyad')) ?> *</label>
              <input type="text" name="ad_soyad" required>
            </div>
            <div class="form-alan">
              <label><?= e(t('genel.firma')) ?></label>
              <input type="text" name="firma">
            </div>
          </div>

          <div class="form-satir">
            <div class="form-alan">
              <label><?= e(t('genel.eposta')) ?> *</label>
              <input type="email" name="eposta" required>
            </div>
            <div class="form-alan">
              <label><?= e(t('genel.telefon')) ?> *</label>
              <input type="tel" name="telefon" required>
            </div>
          </div>

          <div class="form-satir">
            <div class="form-alan">
              <label><?= e(t('genel.sehir')) ?></label>
              <input type="text" name="sehir">
            </div>
            <div class="form-alan">
              <label><?= e(t('teklif.olcu')) ?></label>
              <input type="text" name="olcu_bilgisi" placeholder="<?= e(t('teklif.olcu_ph')) ?>">
            </div>
          </div>

          <div class="form-satir">
            <div class="form-alan">
              <label><?= e(t('genel.adet')) ?></label>
              <input type="number" name="adet" min="1" placeholder="<?= e(t('teklif.adet_ph')) ?>">
            </div>
            <div class="form-alan">
              <label><?= e(t('genel.konu')) ?></label>
              <input type="text" name="konu">
            </div>
          </div>

          <div class="form-alan">
            <label><?= e(t('genel.mesaj')) ?> *</label>
            <textarea name="mesaj" rows="6" required></textarea>
          </div>

          <!-- Honeypot -->
          <div style="position:absolute;left:-9999px;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

          <button type="submit" class="btn btn-renk btn-buyuk"><?= e(t('genel.teklif_al')) ?></button>
          <div class="form-durum" aria-live="polite"></div>
        </form>
      </div>
    </section>
    <?php
}

function _view_404(): void {
    ?>
    <section class="bolum dort-yuz">
      <div class="sarmal sarmal-dar">
        <div class="dort-yuz-ic">
          <div class="dy-kod">404</div>
          <h1><?= e(t('404.baslik')) ?></h1>
          <p><?= e(t('404.aciklama')) ?></p>
          <a href="<?= e(url()) ?>" class="btn btn-renk"><?= e(t('404.anasayfa')) ?></a>
        </div>
      </div>
    </section>
    <?php
}

function _sitemap_xml(): string {
    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    $diller = ['tr', 'en', 'ar'];
    $sabit = ['', 'urunler', 'referanslar', 'iletisim', 'teklif', 'hakkimizda'];

    foreach ($diller as $d) {
        foreach ($sabit as $s) {
            $u = $d === DEFAULT_LANG ? SITE_URL . '/' . $s : SITE_URL . '/' . $d . '/' . $s;
            $xml .= '<url><loc>' . e(rtrim($u, '/')) . '</loc><priority>0.8</priority></url>' . "\n";
        }
    }

    foreach (db_liste('SELECT slug FROM urunler WHERE aktif = 1') as $u) {
        foreach ($diller as $d) {
            $loc = $d === DEFAULT_LANG ? SITE_URL . '/urun/' . $u['slug'] : SITE_URL . '/' . $d . '/urun/' . $u['slug'];
            $xml .= '<url><loc>' . e($loc) . '</loc><priority>0.7</priority></url>' . "\n";
        }
    }
    foreach (db_liste('SELECT slug FROM kategoriler WHERE aktif = 1') as $u) {
        foreach ($diller as $d) {
            $loc = $d === DEFAULT_LANG ? SITE_URL . '/urunler/' . $u['slug'] : SITE_URL . '/' . $d . '/urunler/' . $u['slug'];
            $xml .= '<url><loc>' . e($loc) . '</loc><priority>0.6</priority></url>' . "\n";
        }
    }

    $xml .= '</urlset>';
    return $xml;
}
