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
elseif ($ilk === 'urunler' || $ilk === 'kategori') {
    if ($iki !== '') {
        // Kategori urunleri (/urunler/{slug} veya /kategori/{slug} calisir)
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
elseif ($ilk === 'cozumler') {
    $tipi = 'cozumler';
    $veri['cozumler'] = db_liste('SELECT * FROM cozumler WHERE aktif = 1 ORDER BY sira, id');
    $metaBaslik = (dil() === 'en' ? 'Solutions' : (dil() === 'ar' ? 'الحلول' : 'Çözümler')) . ' - ' . ayar('firma_adi');
}
elseif ($ilk === 'cozum' && $iki !== '') {
    $c = db_satir('SELECT * FROM cozumler WHERE slug = :s AND aktif = 1', ['s' => $iki]);
    if (!$c) { $tipi = '404'; }
    else {
        $tipi = 'cozum';
        $veri['cozum'] = $c;
        // ilgili_urunler virgulle ayrilmis slug'lardan liste cek
        $iliskili = [];
        if (!empty($c['ilgili_urunler'])) {
            $slugs = array_filter(array_map('trim', explode(',', $c['ilgili_urunler'])));
            if ($slugs) {
                $in  = implode(',', array_fill(0, count($slugs), '?'));
                $iliskili = db_liste(
                    "SELECT u.*, k.slug AS kategori_slug, k.ad_tr AS kategori_ad_tr
                     FROM urunler u JOIN kategoriler k ON k.id = u.kategori_id
                     WHERE u.aktif = 1 AND u.slug IN ($in)
                     ORDER BY FIELD(u.slug, $in)",
                    array_merge(array_values($slugs), array_values($slugs))
                );
            }
        }
        $veri['iliskili_urunler'] = $iliskili;
        $veri['diger_cozumler'] = db_liste(
            'SELECT slug, ad_tr, ad_en, ad_ar, ozet_tr, ozet_en, ozet_ar, ikon, gorsel FROM cozumler
             WHERE aktif = 1 AND id <> :id ORDER BY sira LIMIT 4',
            ['id' => $c['id']]
        );
        $metaBaslik = ($c[$adKol] ?? $c['ad_tr']) . ' - ' . ayar('firma_adi');
        $metaAciklama = kisalt((string)($c['ozet_' . dil()] ?? $c['ozet_tr'] ?? ''), 160);
    }
}
elseif ($ilk === 'blog') {
    $tipi = 'blog_liste';
    $veri['yazilar'] = db_liste(
        "SELECT * FROM icerikler WHERE tip = 'blog' AND aktif = 1 ORDER BY yayin_tarihi DESC, id DESC"
    );
    $metaBaslik = 'Blog - ' . ayar('firma_adi');
    if ($iki !== '') {
        // /blog/X yonlendirsin /yazi/X'e, ama yazi rotasini birlesik tut
        $y = db_satir("SELECT * FROM icerikler WHERE tip = 'blog' AND slug = :s AND aktif = 1", ['s' => $iki]);
        if (!$y) { $tipi = '404'; }
        else {
            $tipi = 'yazi';
            $veri['yazi'] = $y;
            // goruntulenme artir
            try { db()->prepare('UPDATE icerikler SET goruntulenme = goruntulenme + 1 WHERE id = :id')->execute(['id' => $y['id']]); } catch (Throwable $_) {}
            $metaBaslik = ($y['baslik_' . dil()] ?? $y['baslik_tr']) . ' - ' . ayar('firma_adi');
            $metaAciklama = kisalt((string)($y['ozet_' . dil()] ?? $y['ozet_tr'] ?? ''), 160);
        }
    }
}
elseif ($ilk === 'haberler') {
    $tipi = 'haber_liste';
    $veri['haberler'] = db_liste(
        "SELECT * FROM icerikler WHERE tip = 'haber' AND aktif = 1 ORDER BY yayin_tarihi DESC, id DESC"
    );
    $metaBaslik = (dil() === 'en' ? 'News' : (dil() === 'ar' ? 'أخبار' : 'Haberler')) . ' - ' . ayar('firma_adi');
}
elseif ($ilk === 'haber' && $iki !== '') {
    $h = db_satir("SELECT * FROM icerikler WHERE tip = 'haber' AND slug = :s AND aktif = 1", ['s' => $iki]);
    if (!$h) { $tipi = '404'; }
    else {
        $tipi = 'haber';
        $veri['haber'] = $h;
        try { db()->prepare('UPDATE icerikler SET goruntulenme = goruntulenme + 1 WHERE id = :id')->execute(['id' => $h['id']]); } catch (Throwable $_) {}
        $metaBaslik = ($h['baslik_' . dil()] ?? $h['baslik_tr']) . ' - ' . ayar('firma_adi');
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
    <a href="<?= e(url()) ?>" class="logo-alan" aria-label="<?= e(ayar('firma_adi', 'TeknikLED')) ?>">
      <span class="logo-txt">
        <span class="logo-satir">
          <span class="logo-teknik">Teknik</span><span class="logo-led"><span class="rgb-r">L</span><span class="rgb-g">E</span><span class="rgb-b">D</span></span>
        </span>
        <span class="logo-cizgi"></span>
      </span>
    </a>
    <nav class="ana-menu" id="anaMenu">
      <a href="<?= e(url()) ?>" class="<?= $tipi === 'anasayfa' ? 'aktif' : '' ?>"><?= e(t('menu.anasayfa')) ?></a>
      <a href="<?= e(url('urunler')) ?>" class="<?= in_array($tipi, ['urunler','kategori','urun'], true) ? 'aktif' : '' ?>"><?= e(t('menu.urunler')) ?></a>
      <a href="<?= e(url('cozumler')) ?>" class="<?= in_array($tipi, ['cozumler','cozum'], true) ? 'aktif' : '' ?>"><?= e(dil() === 'en' ? 'Solutions' : (dil() === 'ar' ? 'الحلول' : 'Çözümler')) ?></a>
      <a href="<?= e(url('referanslar')) ?>" class="<?= in_array($tipi, ['referanslar','referans'], true) ? 'aktif' : '' ?>"><?= e(t('menu.referanslar')) ?></a>
      <a href="<?= e(url('blog')) ?>" class="<?= in_array($tipi, ['blog_liste','yazi'], true) ? 'aktif' : '' ?>">Blog</a>
      <a href="<?= e(url('haberler')) ?>" class="<?= in_array($tipi, ['haber_liste','haber'], true) ? 'aktif' : '' ?>"><?= e(dil() === 'en' ? 'News' : (dil() === 'ar' ? 'أخبار' : 'Haberler')) ?></a>
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
    case 'cozumler':    _view_cozumler($veri, $adKol, $ozKol); break;
    case 'cozum':       _view_cozum($veri, $adKol, $ozKol, $acKol); break;
    case 'blog_liste':  _view_icerik_liste($veri['yazilar'] ?? [], 'blog'); break;
    case 'haber_liste': _view_icerik_liste($veri['haberler'] ?? [], 'haber'); break;
    case 'yazi':        _view_icerik_detay($veri['yazi'] ?? [], 'blog'); break;
    case 'haber':       _view_icerik_detay($veri['haber'] ?? [], 'haber'); break;
    case '404':         default: _view_404(); break;
}
?>
</main>

<!-- FOOTER -->
<footer class="site-alt">
  <div class="sarmal alt-sarmal">
    <div class="alt-blok alt-blok-marka">
      <div class="logo-txt logo-txt-buyuk">
        <span class="logo-satir">
          <span class="logo-teknik">Teknik</span><span class="logo-led"><span class="rgb-r">L</span><span class="rgb-g">E</span><span class="rgb-b">D</span></span>
        </span>
        <span class="logo-cizgi"></span>
      </div>
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
    $heroBaslik = ayar('hero_baslik_' . $lang, '') ?: ayar('hero_baslik_tr', '');
    if ($heroBaslik === '') {
        $heroBaslik = $lang === 'en'
            ? 'Light, Engineered.'
            : ($lang === 'ar' ? 'الضوء، مهندس' : 'Işığın Mühendisliği');
    }
    $heroAlt = ayar('hero_alt_' . $lang, '') ?: ayar('hero_alt_tr', '');
    if ($heroAlt === '') {
        $heroAlt = $lang === 'en'
            ? 'Design-registered modular frame, LED table, LED podium, LED poster and CNC case manufacturing. Konya-based, 100% local production.'
            : ($lang === 'ar' ? 'تصنيع الإطارات المعيارية المسجلة وطاولات LED ومنابر LED.' : 'Tasarım tescilli modüler karkas, LED masa, LED kürsü, LED poster kasa ve CNC kasa üretimi. Konya merkezli, %100 yerli üretim.');
    }

    // Slider kayitlari (aktif olanlar)
    try {
        $sliderler = db_liste('SELECT * FROM slider WHERE aktif = 1 ORDER BY sira ASC, id ASC');
    } catch (Throwable $e) {
        $sliderler = [];
    }
    // Ana sayfa icin cozumler, son icerikler ve markalar (v0.3.1)
    try { $vitrinCozumler = db_liste('SELECT * FROM cozumler WHERE aktif = 1 AND vitrin = 1 ORDER BY sira, id LIMIT 6'); }
    catch (Throwable $e) { $vitrinCozumler = []; }
    try { $sonYazilar = db_liste("SELECT * FROM icerikler WHERE aktif = 1 ORDER BY yayin_tarihi DESC, id DESC LIMIT 4"); }
    catch (Throwable $e) { $sonYazilar = []; }
    try { $markalar = db_liste('SELECT * FROM markalar WHERE aktif = 1 ORDER BY sira, id'); }
    catch (Throwable $e) { $markalar = []; }

    $baslikKol   = 'baslik_' . $lang;
    $aciklamaKol = 'aciklama_' . $lang;
    $butonKol    = 'buton_metin_' . $lang;

    // Her kategori icin buyuk fotograf (varsa kategoriler/slug.png, yoksa slider fallback)
    $kategoriGorseller = [
        'moduler-karkas' => 'slider/01-karkas.png',
        'led-masa'       => 'slider/02-led-masa.png',
        'led-poster'     => 'slider/03-led-poster.png',
        'metal-kursu'    => 'slider/04-metal-kursu.png',
        'led-kursu'      => 'urunler/ledkursu-p186.png',
        'cnc-kasa'       => 'urunler/cnckasa-128.png',
    ];
    ?>

    <!-- =============================================
         V0.3.0 - APPLE SCROLL-SNAP SAYFASI
         Her bolum 100vh, sayfa bolum bolum kayar.
         Urun fotoğraflari merkez sahnede.
         ============================================= -->

    <div class="ana-sayfa-v3">

      <!-- ========== 1. HERO - CINEMATIC ========== -->
      <section class="v3-hero">
        <div class="v3-hero-bg">
          <?php if (!empty($sliderler)): ?>
            <img src="<?= e(upload($sliderler[0]['gorsel'])) ?>"
                 alt="<?= e($sliderler[0][$baslikKol] ?? $sliderler[0]['baslik_tr']) ?>"
                 class="v3-hero-img" loading="eager">
          <?php else: ?>
            <img src="<?= e(upload('slider/01-karkas.png')) ?>" alt="TeknikLED" class="v3-hero-img">
          <?php endif; ?>
          <div class="v3-hero-vignette"></div>
        </div>
        <div class="v3-hero-icerik">
          <div class="v3-marka"><?= e(ayar('firma_adi', 'TeknikLED')) ?> · <?= e(dil() === 'en' ? 'Since 2020' : (dil() === 'ar' ? 'منذ 2020' : 'Konya')) ?></div>
          <h1 class="v3-hero-baslik"><?= e($heroBaslik) ?></h1>
          <p class="v3-hero-alt"><?= e($heroAlt) ?></p>
          <div class="v3-hero-btn">
            <a href="<?= e(url('teklif')) ?>" class="v3-btn v3-btn-birincil"><?= e(t('home.hero_cta')) ?></a>
            <a href="#kategori-1" class="v3-btn v3-btn-ikincil"><?= e(t('menu.urunler')) ?></a>
          </div>
        </div>
        <a href="#kategori-1" class="v3-kaydir" aria-label="Scroll">
          <span class="v3-kaydir-metin"><?= e(dil() === 'en' ? 'EXPLORE' : (dil() === 'ar' ? 'استكشف' : 'KEŞFET')) ?></span>
          <span class="v3-kaydir-cizgi"></span>
        </a>
      </section>

      <!-- ========== 2-7. KATEGORI SECTION'LARI (her biri 100vh) ========== -->
      <?php foreach ($kategoriler as $i => $k):
        $num    = $i + 1;
        $gorsel = $kategoriGorseller[$k['slug']] ?? 'kategoriler/' . $k['slug'] . '.png';
        $alternate = $i % 2 === 0; // cift index sola, tek saga
        $kadAci = $k[$acKol] ?? $k['aciklama_tr'] ?? '';
        $kadAd  = $k[$adKol] ?? $k['ad_tr'];
      ?>
      <section id="kategori-<?= $num ?>" class="v3-kategori <?= $alternate ? 'v3-kat-sol' : 'v3-kat-sag' ?>">
        <div class="v3-kat-gorsel-alan">
          <div class="v3-kat-gorsel-ic">
            <img src="<?= e(upload($gorsel)) ?>"
                 alt="<?= e($kadAd) ?>"
                 class="v3-kat-gorsel"
                 loading="lazy">
            <div class="v3-kat-gorsel-glow"></div>
          </div>
        </div>
        <div class="v3-kat-metin-alan">
          <div class="v3-kat-metin-ic">
            <div class="v3-kat-rozet">
              <span class="v3-kat-num"><?= sprintf('%02d', $num) ?></span>
              <span class="v3-kat-cizgi"></span>
              <span class="v3-kat-etiket"><?= e(dil() === 'en' ? 'CATEGORY' : (dil() === 'ar' ? 'فئة' : 'KATEGORİ')) ?></span>
            </div>
            <h2 class="v3-kat-baslik"><?= e($kadAd) ?></h2>
            <?php if ($kadAci): ?>
              <div class="v3-kat-aciklama">
                <?= $kadAci /* HTML allowed */ ?>
              </div>
            <?php endif; ?>
            <div class="v3-kat-ozellikler">
              <?php
              // Kategoriye gore ozellik rozet'leri (hardcoded - teknik spec)
              $ozellikler = match($k['slug']) {
                'moduler-karkas' => ['TASARIM TESCİLLİ', '1.20 MM GALVANİZ', 'MODÜLER'],
                'led-masa'       => ['P1.86 / P2.5', '96×192 → 96×288', 'ÖZEL ÖLÇÜ'],
                'led-kursu'      => ['P1.86 PREMIUM', 'MİKROFON ENTEGRE', 'HDMI/USB'],
                'led-poster'     => ['TEK/ÇİFT TARAFLI', '8 CM ULTRA İNCE', 'VİTRİN HAZIR'],
                'cnc-kasa'       => ['UNIVERSAL P2.5-P10', 'DKP SAC', 'FIRIN BOYALI'],
                'metal-kursu'    => ['2 MM DKP SAC', 'TASARIM TESCİLLİ', 'SONRADAN LED'],
                default          => []
              };
              foreach ($ozellikler as $oz): ?>
                <span class="v3-kat-spec"><?= e($oz) ?></span>
              <?php endforeach; ?>
            </div>
            <a href="<?= e(url('urunler/' . $k['slug'])) ?>" class="v3-btn v3-btn-birincil">
              <?= e(dil() === 'en' ? 'Explore Products' : (dil() === 'ar' ? 'استكشف' : 'Ürünleri Gör')) ?>
              <span class="v3-btn-ok">→</span>
            </a>
          </div>
        </div>
      </section>
      <?php endforeach; ?>

      <!-- ========== 8. STATS / RAKAMLAR ========== -->
      <section class="v3-stats">
        <div class="v3-stats-ustbas">
          <span class="v3-ustbas-etiket"><?= e(dil() === 'en' ? 'NUMBERS' : (dil() === 'ar' ? 'أرقام' : 'RAKAMLAR')) ?></span>
        </div>
        <h2 class="v3-stats-baslik"><?= e(dil() === 'en' ? 'Built on trust,<br>proven by results.' : (dil() === 'ar' ? 'مبني على الثقة.' : 'Güvenle kuruldu,<br>sonuçla kanıtlandı.')) ?></h2>
        <div class="v3-stats-izgara">
          <div class="v3-stat">
            <span class="v3-stat-sayi" data-hedef="100" data-son="+">0</span>
            <span class="v3-stat-etiket"><?= e(dil() === 'en' ? 'Completed Projects' : (dil() === 'ar' ? 'مشاريع' : 'Tamamlanan Proje')) ?></span>
          </div>
          <div class="v3-stat">
            <span class="v3-stat-sayi" data-hedef="6" data-son="">0</span>
            <span class="v3-stat-etiket"><?= e(dil() === 'en' ? 'Product Categories' : (dil() === 'ar' ? 'فئات' : 'Ürün Kategorisi')) ?></span>
          </div>
          <div class="v3-stat">
            <span class="v3-stat-sayi" data-hedef="100" data-son="%">0</span>
            <span class="v3-stat-etiket"><?= e(dil() === 'en' ? 'Local Production' : (dil() === 'ar' ? 'إنتاج محلي' : 'Yerli Üretim')) ?></span>
          </div>
          <div class="v3-stat">
            <span class="v3-stat-sayi" data-hedef="2" data-son=" <?= e(dil() === 'en' ? 'YR' : 'YIL') ?>">0</span>
            <span class="v3-stat-etiket"><?= e(dil() === 'en' ? 'Warranty' : (dil() === 'ar' ? 'ضمان' : 'Parça Garantisi')) ?></span>
          </div>
        </div>
      </section>

      <!-- ========== 9. REFERANSLAR ========== -->
      <?php if (!empty($veri['referanslar'])): ?>
      <section class="v3-referanslar">
        <div class="v3-ref-bas">
          <span class="v3-ustbas-etiket"><?= e(dil() === 'en' ? 'PROJECTS' : (dil() === 'ar' ? 'مشاريع' : 'PROJELER')) ?></span>
          <h2 class="v3-ref-baslik"><?= e(dil() === 'en' ? 'Trusted by visionary projects.' : (dil() === 'ar' ? 'موثوق بمشاريع رائدة' : 'Vizyon sahibi projelerin tercihi.')) ?></h2>
        </div>
        <div class="v3-ref-izgara">
          <?php foreach ($veri['referanslar'] as $r): ?>
            <a href="<?= e(url('referans/' . $r['slug'])) ?>" class="v3-ref-kart">
              <div class="v3-ref-gorsel">
                <?php if ($r['ana_gorsel']): ?>
                  <img src="<?= e(upload($r['ana_gorsel'])) ?>" alt="<?= e($r[$musKol] ?? $r['musteri_tr']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="v3-ref-placeholder">◈</div>
                <?php endif; ?>
              </div>
              <div class="v3-ref-icerik">
                <h3><?= e($r[$musKol] ?? $r['musteri_tr']) ?></h3>
                <?php if ($r['lokasyon']): ?>
                  <span class="v3-ref-loc">📍 <?= e($r['lokasyon']) ?></span>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <a href="<?= e(url('referanslar')) ?>" class="v3-btn v3-btn-ikincil v3-ref-hepsi">
          <?= e(dil() === 'en' ? 'View All Projects' : (dil() === 'ar' ? 'عرض المشاريع' : 'Tüm Projeleri Gör')) ?> →
        </a>
      </section>
      <?php endif; ?>

      <!-- ========== COZUMLER (v0.3.1) ========== -->
      <?php if (!empty($vitrinCozumler)): ?>
      <section class="v3-cozumler">
        <div class="v3-cozumler-bas">
          <span class="v3-ustbas-etiket"><?= e($lang === 'en' ? 'SOLUTIONS' : ($lang === 'ar' ? 'حلول' : 'ÇÖZÜMLER')) ?></span>
          <h2 class="v3-coz-baslik"><?= e(ayar('cozumler_baslik_' . $lang, 'Kullanım Alanlarına Özel LED Çözümler')) ?></h2>
        </div>
        <div class="v3-coz-izgara">
          <?php foreach ($vitrinCozumler as $cz): ?>
            <a href="<?= e(url('cozum/' . $cz['slug'])) ?>" class="v3-coz-kart">
              <span class="v3-coz-ikon"><?= e($cz['ikon'] ?: '◈') ?></span>
              <h3 class="v3-coz-kart-baslik"><?= e($cz['ad_' . $lang] ?? $cz['ad_tr']) ?></h3>
              <?php if (!empty($cz['ozet_' . $lang] ?? $cz['ozet_tr'])): ?>
                <p class="v3-coz-kart-ozet"><?= e(kisalt($cz['ozet_' . $lang] ?? $cz['ozet_tr'], 110)) ?></p>
              <?php endif; ?>
              <span class="v3-coz-kart-ok"><?= e($lang === 'en' ? 'Explore' : ($lang === 'ar' ? 'استكشف' : 'Keşfet')) ?> →</span>
            </a>
          <?php endforeach; ?>
        </div>
        <a href="<?= e(url('cozumler')) ?>" class="v3-btn v3-btn-ikincil v3-ref-hepsi">
          <?= e($lang === 'en' ? 'All Solutions' : ($lang === 'ar' ? 'جميع الحلول' : 'Tüm Çözümler')) ?> →
        </a>
      </section>
      <?php endif; ?>

      <!-- ========== MARKALAR (v0.3.1) ========== -->
      <?php if (!empty($markalar)): ?>
      <section class="v3-markalar">
        <div class="v3-markalar-bas">
          <span class="v3-ustbas-etiket"><?= e($lang === 'en' ? 'PARTNERS' : ($lang === 'ar' ? 'شركاء' : 'TEKNOLOJİ ORTAKLARI')) ?></span>
          <h2 class="v3-mar-baslik"><?= e(ayar('markalar_baslik_' . $lang, 'Güçlü Teknoloji Ortakları')) ?></h2>
          <p class="v3-mar-alt"><?= e($lang === 'en' ? 'World-leading brands powering our LED systems.' : ($lang === 'ar' ? 'علامات تجارية رائدة' : 'LED sistemlerimizde kullandığımız dünya lideri teknoloji markaları.')) ?></p>
        </div>
        <div class="v3-marka-izgara">
          <?php foreach ($markalar as $m): ?>
            <?php $iccontent = '<div class="v3-marka-kart"><img src="' . e(upload($m['logo'])) . '" alt="' . e($m['ad']) . '" loading="lazy"></div>'; ?>
            <?php if (!empty($m['web_url'])): ?>
              <a href="<?= e($m['web_url']) ?>" target="_blank" rel="noopener" class="v3-marka-link" title="<?= e($m['ad']) ?>">
                <?= $iccontent ?>
              </a>
            <?php else: ?>
              <div class="v3-marka-link" title="<?= e($m['ad']) ?>"><?= $iccontent ?></div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <!-- ========== SON YAZILAR (v0.3.1) ========== -->
      <?php if (!empty($sonYazilar)): ?>
      <section class="v3-icerik-bolum">
        <div class="v3-ic-bolum-bas">
          <span class="v3-ustbas-etiket"><?= e($lang === 'en' ? 'JOURNAL' : ($lang === 'ar' ? 'مجلة' : 'GÜNLÜK')) ?></span>
          <h2 class="v3-ic-baslik"><?= e(ayar('blog_baslik_' . $lang, 'Son Yazılar ve Haberler')) ?></h2>
        </div>
        <div class="v3-ic-izgara">
          <?php foreach ($sonYazilar as $it):
            $url = $it['tip'] === 'blog' ? url('blog/' . $it['slug']) : url('haber/' . $it['slug']);
            $tarih = !empty($it['yayin_tarihi']) ? date('d.m.Y', strtotime($it['yayin_tarihi'])) : '';
          ?>
            <a href="<?= e($url) ?>" class="v3-ic-kart">
              <?php if (!empty($it['kapak'])): ?>
                <div class="v3-ic-gorsel"><img src="<?= e(upload($it['kapak'])) ?>" alt="<?= e($it['baslik_' . $lang] ?? $it['baslik_tr']) ?>" loading="lazy"></div>
              <?php else: ?>
                <div class="v3-ic-placeholder"><?= $it['tip'] === 'blog' ? '📖' : '📰' ?></div>
              <?php endif; ?>
              <div class="v3-ic-gvd">
                <div class="v3-ic-meta">
                  <span class="v3-ic-tip v3-ic-tip-<?= e($it['tip']) ?>"><?= $it['tip'] === 'blog' ? 'BLOG' : 'HABER' ?></span>
                  <?php if ($tarih): ?><span class="v3-ic-tarih"><?= e($tarih) ?></span><?php endif; ?>
                </div>
                <h3 class="v3-ic-baslik-kart"><?= e($it['baslik_' . $lang] ?? $it['baslik_tr']) ?></h3>
                <?php if (!empty($it['ozet_' . $lang] ?? $it['ozet_tr'])): ?>
                  <p class="v3-ic-ozet"><?= e(kisalt($it['ozet_' . $lang] ?? $it['ozet_tr'], 110)) ?></p>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="v3-ic-alt-btn">
          <a href="<?= e(url('blog')) ?>" class="v3-btn v3-btn-ikincil"><?= e($lang === 'en' ? 'All Articles' : ($lang === 'ar' ? 'جميع المقالات' : 'Tüm Yazılar')) ?> →</a>
          <a href="<?= e(url('haberler')) ?>" class="v3-btn v3-btn-ikincil"><?= e($lang === 'en' ? 'All News' : ($lang === 'ar' ? 'جميع الأخبار' : 'Tüm Haberler')) ?> →</a>
        </div>
      </section>
      <?php endif; ?>

      <!-- ========== 10. CTA - BUYUK KAPANIŞ ========== -->
      <section class="v3-cta">
        <div class="v3-cta-ic">
          <h2 class="v3-cta-baslik"><?= e(dil() === 'en' ? 'Ready to bring your project to life?' : (dil() === 'ar' ? 'جاهز لبدء مشروعك؟' : 'Projenize hayat vermeye hazır mısınız?')) ?></h2>
          <p class="v3-cta-alt"><?= e(dil() === 'en' ? 'Tell us about your vision. We respond within 24 hours.' : (dil() === 'ar' ? 'أخبرنا برؤيتك' : 'Vizyonunuzu bizimle paylaşın. 24 saat içinde yanıtlıyoruz.')) ?></p>
          <a href="<?= e(url('teklif')) ?>" class="v3-btn v3-btn-birincil v3-btn-buyuk">
            <?= e(t('home.hero_cta')) ?>
            <span class="v3-btn-ok">→</span>
          </a>
        </div>
      </section>

    </div>
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

function _view_cozumler(array $veri, string $adKol, string $ozKol): void {
    $cozumler = $veri['cozumler'] ?? [];
    ?>
    <section class="bolum">
      <div class="sarmal">
        <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e(dil() === 'en' ? 'Solutions' : (dil() === 'ar' ? 'الحلول' : 'Çözümler')) ?></span></nav>
        <div class="sayfa-bas">
          <h1><?= e(dil() === 'en' ? 'Solutions by Application' : (dil() === 'ar' ? 'حلول حسب التطبيق' : 'Kullanım Alanına Özel Çözümler')) ?></h1>
          <p><?= e(dil() === 'en' ? 'Tailored LED and structural solutions for every sector and environment.' : (dil() === 'ar' ? 'حلول مخصصة لكل قطاع' : 'Her sektöre ve her mekâna özel LED ve yapısal çözümler.')) ?></p>
        </div>
        <div class="cozum-izgara">
          <?php foreach ($cozumler as $c): ?>
            <a href="<?= e(url('cozum/' . $c['slug'])) ?>" class="cozum-kart">
              <?php if (!empty($c['gorsel'])): ?>
                <div class="cozum-kart-gorsel">
                  <img src="<?= e(upload($c['gorsel'])) ?>" alt="<?= e($c[$adKol] ?? $c['ad_tr']) ?>" loading="lazy">
                </div>
              <?php else: ?>
                <div class="cozum-kart-ikon"><?= e($c['ikon'] ?: '◈') ?></div>
              <?php endif; ?>
              <h3><?= e($c[$adKol] ?? $c['ad_tr']) ?></h3>
              <?php if (!empty($c[$ozKol] ?? $c['ozet_tr'])): ?>
                <p><?= e($c[$ozKol] ?? $c['ozet_tr']) ?></p>
              <?php endif; ?>
              <span class="cozum-kart-ok"><?= e(dil() === 'en' ? 'Details' : (dil() === 'ar' ? 'تفاصيل' : 'Detaylar')) ?> →</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}

function _view_cozum(array $veri, string $adKol, string $ozKol, string $acKol): void {
    $c = $veri['cozum'];
    $iliskili = $veri['iliskili_urunler'] ?? [];
    $diger = $veri['diger_cozumler'] ?? [];
    $lang = dil();
    ?>
    <section class="cozum-hero">
      <div class="sarmal">
        <nav class="ekmek">
          <a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> /
          <a href="<?= e(url('cozumler')) ?>"><?= e($lang === 'en' ? 'Solutions' : ($lang === 'ar' ? 'الحلول' : 'Çözümler')) ?></a> /
          <span><?= e($c[$adKol] ?? $c['ad_tr']) ?></span>
        </nav>
        <div class="cozum-hero-ic">
          <span class="cozum-hero-ikon"><?= e($c['ikon'] ?: '◈') ?></span>
          <h1><?= e($c[$adKol] ?? $c['ad_tr']) ?></h1>
          <?php if (!empty($c[$ozKol] ?? $c['ozet_tr'])): ?>
            <p class="cozum-hero-ozet"><?= e($c[$ozKol] ?? $c['ozet_tr']) ?></p>
          <?php endif; ?>
          <div class="cozum-hero-btn">
            <a href="<?= e(url('teklif')) ?>" class="btn btn-renk"><?= e(t('home.hero_cta')) ?></a>
            <a href="<?= e(url('iletisim')) ?>" class="btn btn-anahat"><?= e(t('menu.iletisim')) ?></a>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($c[$acKol] ?? $c['aciklama_tr'])): ?>
    <section class="bolum">
      <div class="sarmal sarmal-dar">
        <div class="icerik cms">
          <?= $c[$acKol] ?? $c['aciklama_tr'] /* HTML allowed */ ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($iliskili)): ?>
    <section class="bolum urun-bolumu">
      <div class="sarmal">
        <div class="bolum-bas">
          <h2><?= e($lang === 'en' ? 'Recommended Products' : ($lang === 'ar' ? 'منتجات موصى بها' : 'Bu Çözüm İçin Önerilen Ürünler')) ?></h2>
        </div>
        <div class="urun-izgara">
          <?php foreach ($iliskili as $u): ?>
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
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($diger)): ?>
    <section class="bolum">
      <div class="sarmal">
        <div class="bolum-bas">
          <h2><?= e($lang === 'en' ? 'Other Solutions' : ($lang === 'ar' ? 'حلول أخرى' : 'Diğer Çözümler')) ?></h2>
          <a href="<?= e(url('cozumler')) ?>" class="bolum-hepsi"><?= e(t('genel.tumunu_gor')) ?> →</a>
        </div>
        <div class="cozum-izgara">
          <?php foreach ($diger as $d): ?>
            <a href="<?= e(url('cozum/' . $d['slug'])) ?>" class="cozum-kart">
              <?php if (!empty($d['gorsel'])): ?>
                <div class="cozum-kart-gorsel"><img src="<?= e(upload($d['gorsel'])) ?>" alt="<?= e($d[$adKol] ?? $d['ad_tr']) ?>" loading="lazy"></div>
              <?php else: ?>
                <div class="cozum-kart-ikon"><?= e($d['ikon'] ?: '◈') ?></div>
              <?php endif; ?>
              <h3><?= e($d[$adKol] ?? $d['ad_tr']) ?></h3>
              <?php if (!empty($d[$ozKol] ?? $d['ozet_tr'])): ?><p><?= e($d[$ozKol] ?? $d['ozet_tr']) ?></p><?php endif; ?>
              <span class="cozum-kart-ok"><?= e(t('genel.detay')) ?> →</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
    <?php
}

function _view_icerik_liste(array $items, string $tip): void {
    $lang = dil();
    $baslikKol = 'baslik_' . $lang;
    $ozetKol = 'ozet_' . $lang;
    $sayfaBaslik = $tip === 'blog'
        ? ($lang === 'en' ? 'Blog' : ($lang === 'ar' ? 'المدونة' : 'Blog'))
        : ($lang === 'en' ? 'News' : ($lang === 'ar' ? 'أخبار' : 'Haberler'));
    $altBaslik = $tip === 'blog'
        ? ($lang === 'en' ? 'Insights, guides and technical notes on LED technology.' : ($lang === 'ar' ? 'مقالات تقنية' : 'LED teknolojisi üzerine makaleler, rehberler ve teknik notlar.'))
        : ($lang === 'en' ? 'Company announcements, project milestones and news.' : ($lang === 'ar' ? 'إعلانات الشركة' : 'Şirket duyuruları, proje kilometre taşları ve haberler.'));
    ?>
    <section class="bolum">
      <div class="sarmal">
        <nav class="ekmek"><a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> / <span><?= e($sayfaBaslik) ?></span></nav>
        <div class="sayfa-bas">
          <h1><?= e($sayfaBaslik) ?></h1>
          <p><?= e($altBaslik) ?></p>
        </div>
        <?php if (empty($items)): ?>
          <div class="bilgi-kutu"><?= e($lang === 'en' ? 'No content yet.' : ($lang === 'ar' ? 'لا يوجد محتوى' : 'Henüz içerik yok.')) ?></div>
        <?php else: ?>
          <div class="icerik-izgara">
            <?php foreach ($items as $it):
              $url = $tip === 'blog' ? url('blog/' . $it['slug']) : url('haber/' . $it['slug']);
              $tarih = !empty($it['yayin_tarihi']) ? date('d.m.Y', strtotime($it['yayin_tarihi'])) : '';
            ?>
              <a href="<?= e($url) ?>" class="icerik-kart">
                <div class="icerik-kart-gorsel">
                  <?php if (!empty($it['kapak'])): ?>
                    <img src="<?= e(upload($it['kapak'])) ?>" alt="<?= e($it[$baslikKol] ?? $it['baslik_tr']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="icerik-kart-placeholder"><?= $tip === 'blog' ? '📖' : '📰' ?></div>
                  <?php endif; ?>
                </div>
                <div class="icerik-kart-gvd">
                  <div class="icerik-kart-meta">
                    <span class="icerik-kart-tip"><?= $tip === 'blog' ? 'BLOG' : 'HABER' ?></span>
                    <?php if ($tarih): ?><span class="icerik-kart-tarih"><?= e($tarih) ?></span><?php endif; ?>
                  </div>
                  <h3><?= e($it[$baslikKol] ?? $it['baslik_tr']) ?></h3>
                  <?php if (!empty($it[$ozetKol] ?? $it['ozet_tr'])): ?>
                    <p><?= e(kisalt($it[$ozetKol] ?? $it['ozet_tr'], 140)) ?></p>
                  <?php endif; ?>
                  <span class="icerik-kart-ok"><?= e($lang === 'en' ? 'Read' : ($lang === 'ar' ? 'اقرأ' : 'Devamını Oku')) ?> →</span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
}

function _view_icerik_detay(array $it, string $tip): void {
    if (empty($it)) { _view_404(); return; }
    $lang = dil();
    $baslikKol = 'baslik_' . $lang;
    $icerikKol = 'icerik_' . $lang;
    $ozetKol   = 'ozet_' . $lang;
    $tarih = !empty($it['yayin_tarihi']) ? date('d.m.Y', strtotime($it['yayin_tarihi'])) : '';
    $listeUrl = $tip === 'blog' ? url('blog') : url('haberler');
    $listeAd  = $tip === 'blog'
        ? ($lang === 'en' ? 'Blog' : ($lang === 'ar' ? 'المدونة' : 'Blog'))
        : ($lang === 'en' ? 'News' : ($lang === 'ar' ? 'أخبار' : 'Haberler'));
    ?>
    <section class="bolum">
      <div class="sarmal sarmal-dar">
        <nav class="ekmek">
          <a href="<?= e(url()) ?>"><?= e(t('menu.anasayfa')) ?></a> /
          <a href="<?= e($listeUrl) ?>"><?= e($listeAd) ?></a> /
          <span><?= e($it[$baslikKol] ?? $it['baslik_tr']) ?></span>
        </nav>
        <div class="icerik-detay-bas">
          <div class="icerik-detay-meta">
            <span class="icerik-kart-tip"><?= $tip === 'blog' ? 'BLOG' : 'HABER' ?></span>
            <?php if ($tarih): ?><span class="icerik-kart-tarih"><?= e($tarih) ?></span><?php endif; ?>
            <?php if (!empty($it['yazar'])): ?><span class="icerik-kart-yazar">· <?= e($it['yazar']) ?></span><?php endif; ?>
          </div>
          <h1><?= e($it[$baslikKol] ?? $it['baslik_tr']) ?></h1>
          <?php if (!empty($it[$ozetKol] ?? $it['ozet_tr'])): ?>
            <p class="icerik-detay-ozet"><?= e($it[$ozetKol] ?? $it['ozet_tr']) ?></p>
          <?php endif; ?>
        </div>
        <?php if (!empty($it['kapak'])): ?>
          <div class="icerik-detay-kapak">
            <img src="<?= e(upload($it['kapak'])) ?>" alt="<?= e($it[$baslikKol] ?? $it['baslik_tr']) ?>">
          </div>
        <?php endif; ?>
        <div class="icerik cms">
          <?= $it[$icerikKol] ?? $it['icerik_tr'] ?? '' /* HTML */ ?>
        </div>
        <?php if (!empty($it['etiketler'])): ?>
          <div class="icerik-etiketler">
            <?php foreach (explode(',', $it['etiketler']) as $et): $et = trim($et); if (!$et) continue; ?>
              <span class="icerik-etiket">#<?= e($et) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="icerik-detay-alt">
          <a href="<?= e($listeUrl) ?>" class="btn btn-anahat">← <?= e($lang === 'en' ? 'Back to ' : ($lang === 'ar' ? 'العودة' : 'Geri: ')) ?><?= e($listeAd) ?></a>
        </div>
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
