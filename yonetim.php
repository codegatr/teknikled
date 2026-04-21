<?php
/**
 * yonetim.php - TeknikLED Yonetim Paneli
 * TeknikLED v0.1.0 - CODEGA
 *
 * Modüller:
 *   is=giris          -> Giris ekrani
 *   is=cikis          -> Cikis
 *   is=panel          -> Ana dashboard
 *   is=urunler        -> Urun listesi
 *   is=urun           -> Urun ekle/duzenle (id=?)
 *   is=urun-sil       -> Urun sil
 *   is=kategoriler    -> Kategori listesi
 *   is=kategori       -> Kategori ekle/duzenle
 *   is=referanslar    -> Referans listesi
 *   is=referans       -> Referans ekle/duzenle
 *   is=teklifler      -> Teklif listesi
 *   is=teklif         -> Teklif detay
 *   is=mesajlar       -> Iletisim mesajlari
 *   is=mesaj          -> Mesaj detay
 *   is=sayfalar       -> CMS sayfalar
 *   is=sayfa          -> Sayfa ekle/duzenle
 *   is=ayarlar        -> Site ayarlari
 *   is=yoneticiler    -> Yonetici listesi (super)
 *   is=yonetici       -> Yonetici ekle/duzenle (super)
 *   is=log            -> Log kayitlari
 *   is=guncelle       -> Sistem guncelleme
 */

declare(strict_types=1);

// Output buffering: yonlendir() cagrilarinin "headers already sent"
// hatasi vermemesi icin tum ciktiyi buffer'a al. Normal sonlanmada
// flush olur, yonlendir() icin ise temizlenir.
ob_start();

if (!is_file(__DIR__ . '/config.php')) {
    header('Location: install.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/i18n.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/mailer.php';
require_once __DIR__ . '/inc/updater.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (DEBUG) { error_reporting(E_ALL); ini_set('display_errors', '1'); }
else { error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); ini_set('display_errors', '0'); }

$is = $_GET['is'] ?? 'panel';

// ---- GIRIS / CIKIS ----
if ($is === 'cikis') {
    Auth::cikis();
    yonlendir(SITE_URL . '/yonetim.php?is=giris');
}

if ($is === 'giris') {
    _yp_giris();
    exit;
}

// Diger tum is'ler giris gerektirir
Auth::korumali();

// ---- Modul yukle ----
$aksiyonlar = [
    'panel'        => '_yp_panel',
    'urunler'      => '_yp_urunler',
    'urun'         => '_yp_urun_form',
    'urun-sil'     => '_yp_urun_sil',
    'kategoriler'  => '_yp_kategoriler',
    'kategori'     => '_yp_kategori_form',
    'kategori-sil' => '_yp_kategori_sil',
    'referanslar'  => '_yp_referanslar',
    'referans'     => '_yp_referans_form',
    'referans-sil' => '_yp_referans_sil',
    'slider'       => '_yp_slider',
    'slider-form'  => '_yp_slider_form',
    'slider-sil'   => '_yp_slider_sil',
    'teklifler'    => '_yp_teklifler',
    'teklif'       => '_yp_teklif_detay',
    'teklif-sil'   => '_yp_teklif_sil',
    'mesajlar'     => '_yp_mesajlar',
    'mesaj'        => '_yp_mesaj_detay',
    'mesaj-sil'    => '_yp_mesaj_sil',
    'sayfalar'     => '_yp_sayfalar',
    'sayfa'        => '_yp_sayfa_form',
    'sayfa-sil'    => '_yp_sayfa_sil',
    'cozumler'     => '_yp_cozumler',
    'cozum'        => '_yp_cozum_form',
    'cozum-sil'    => '_yp_cozum_sil',
    'icerikler'    => '_yp_icerikler',
    'icerik'       => '_yp_icerik_form',
    'icerik-sil'   => '_yp_icerik_sil',
    'markalar'     => '_yp_markalar',
    'marka'        => '_yp_marka_form',
    'marka-sil'    => '_yp_marka_sil',
    'ayarlar'      => '_yp_ayarlar',
    'yoneticiler'  => '_yp_yoneticiler',
    'yonetici'     => '_yp_yonetici_form',
    'yonetici-sil' => '_yp_yonetici_sil',
    'log'          => '_yp_log',
    'guncelle'     => '_yp_guncelle',
    'profil'       => '_yp_profil',
];

if (!isset($aksiyonlar[$is])) $is = 'panel';

// Ortak sayfa ici
_yp_layout_bas($is);
$fn = $aksiyonlar[$is];
$fn();
_yp_layout_son();

// =================================================================
// LAYOUT
// =================================================================
function _yp_layout_bas(string $is): void {
    $adm = Auth::mevcutAdmin();
    $menu = [
        'panel'       => ['📊', 'Panel'],
        'urunler'     => ['📦', 'Urunler'],
        'kategoriler' => ['🏷', 'Kategoriler'],
        'cozumler'    => ['💡', 'Cozumler'],
        'referanslar' => ['🏢', 'Referanslar'],
        'icerikler'   => ['📝', 'Blog/Haber'],
        'markalar'    => ['🎨', 'Markalar'],
        'slider'      => ['🎞', 'Slider'],
        'teklifler'   => ['📋', 'Teklifler', _yp_teklif_yeni_sayi()],
        'mesajlar'    => ['✉', 'Mesajlar', _yp_mesaj_okunmamis_sayi()],
        'sayfalar'    => ['📄', 'Sayfalar'],
        'ayarlar'     => ['⚙', 'Ayarlar'],
    ];
    if ($adm['rol'] === 'super') {
        $menu['yoneticiler'] = ['👤', 'Yoneticiler'];
        $menu['guncelle']    = ['⬆', 'Guncelleme'];
        $menu['log']         = ['📜', 'Log'];
    }

    ?><!DOCTYPE html>
    <html lang="tr">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Yonetim - <?= e(ayar('firma_adi', 'TeknikLED')) ?></title>
      <link rel="stylesheet" href="<?= e(asset('css/yonetim.css')) ?>">
      <link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
    </head>
    <body>
    <div class="yp-layout">
      <aside class="yp-sidebar" id="ypSidebar">
        <div class="yp-logo">
          <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e(ayar('firma_adi', 'TeknikLED')) ?>">
          <p>Yonetim v<?= e(Updater::mevcutVersiyon()) ?></p>
        </div>
        <nav class="yp-menu">
          <div class="yp-menu-bas">ICERIK</div>
          <?php foreach ($menu as $kod => $bilgi): ?>
            <a href="?is=<?= e($kod) ?>" class="<?= $is === $kod || str_starts_with($is, $kod) ? 'aktif' : '' ?>">
              <span class="ikn"><?= $bilgi[0] ?></span>
              <span><?= e($bilgi[1]) ?></span>
              <?php if (!empty($bilgi[2])): ?>
                <span class="yp-rozet yp-rozet-yeni" style="margin-left:auto; font-size:0.7rem;"><?= (int)$bilgi[2] ?></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
          <div class="yp-menu-bas">HESAP</div>
          <a href="?is=profil" class="<?= $is === 'profil' ? 'aktif' : '' ?>">
            <span class="ikn">👤</span><span>Profilim</span>
          </a>
          <a href="?is=cikis">
            <span class="ikn">↪</span><span>Cikis</span>
          </a>
          <a href="<?= e(SITE_URL) ?>" target="_blank">
            <span class="ikn">🌐</span><span>Siteyi Goruntule</span>
          </a>
        </nav>
      </aside>

      <main class="yp-ana">
        <div class="yp-ustbar">
          <div style="display:flex; align-items:center; gap:12px;">
            <button class="yp-mobil-btn" id="ypMobilBtn">☰</button>
            <h1>TeknikLED Yonetim Paneli</h1>
          </div>
          <div class="yp-ustbar-sag">
            <span class="yp-admin-kisayol">
              👤 <strong><?= e($adm['ad_soyad']) ?></strong>
              <span class="yp-rozet <?= $adm['rol'] === 'super' ? 'yp-rozet-inc' : 'yp-rozet-aktif' ?>"><?= e($adm['rol']) ?></span>
            </span>
          </div>
        </div>
        <div class="yp-icerik">
    <?php

    // Flash mesajlar
    foreach (flash_al() as $f) {
        echo '<div class="yp-uyari ' . e($f['tip']) . '">' . e($f['mesaj']) . '</div>';
    }
}

function _yp_layout_son(): void {
    ?>
        </div>
      </main>
    </div>
    <script src="<?= e(asset('js/yonetim.js')) ?>"></script>
    </body>
    </html>
    <?php
}

function _yp_teklif_yeni_sayi(): int {
    try { return (int) db_deger("SELECT COUNT(*) FROM teklifler WHERE durum = 'yeni'"); }
    catch (Throwable $e) { return 0; }
}
function _yp_mesaj_okunmamis_sayi(): int {
    try { return (int) db_deger("SELECT COUNT(*) FROM iletisim_mesajlari WHERE okundu = 0"); }
    catch (Throwable $e) { return 0; }
}

// =================================================================
// 1) GIRIS EKRANI
// =================================================================
function _yp_giris(): void {
    if (Auth::giriliMi()) yonlendir(SITE_URL . '/yonetim.php?is=panel');

    $hata = '';
    $sureDoldu = !empty($_GET['sure_doldu']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) {
            $hata = 'Guvenlik dogrulamasi basarisiz, sayfayi yenileyin.';
        } else {
            $k = trim($_POST['kullanici'] ?? '');
            $s = (string)($_POST['sifre'] ?? '');
            if ($k === '' || $s === '') {
                $hata = 'Kullanici adi ve sifre gerekli.';
            } elseif (Auth::giris($k, $s)) {
                yonlendir(SITE_URL . '/yonetim.php?is=panel');
            } else {
                $hata = 'Kullanici adi veya sifre hatali.';
            }
        }
    }
    ?><!DOCTYPE html>
    <html lang="tr">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Giris - <?= e(ayar('firma_adi', 'TeknikLED')) ?> Yonetim</title>
      <link rel="stylesheet" href="<?= e(asset('css/yonetim.css')) ?>">
      <link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
    </head>
    <body class="yp-giris">
      <div class="yp-giris-kutu">
        <div class="yp-giris-bas">
          <img src="<?= e(asset('img/logo.png')) ?>" alt="<?= e(ayar('firma_adi', 'TeknikLED')) ?>">
          <div class="yp-giris-rgb"></div>
          <h1>Yonetim Paneli</h1>
          <p>Lutfen giris yapin</p>
        </div>
        <?php if ($hata): ?><div class="yp-uyari hata">⚠ <?= e($hata) ?></div><?php endif; ?>
        <?php if ($sureDoldu): ?><div class="yp-uyari warn">⏲ Oturum suresi doldu. Lutfen tekrar giris yapin.</div><?php endif; ?>
        <form method="POST">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <div class="yp-alan">
            <label>Kullanici Adi / E-posta</label>
            <input type="text" name="kullanici" required autofocus value="<?= e($_POST['kullanici'] ?? '') ?>">
          </div>
          <div class="yp-alan">
            <label>Sifre</label>
            <input type="password" name="sifre" required>
          </div>
          <button type="submit" class="yp-btn yp-btn-buyuk">Giris Yap</button>
        </form>
        <p style="margin-top:20px; text-align:center; font-size:0.82rem; color:#64748b;">
          <a href="<?= e(SITE_URL) ?>">← Siteyi goruntule</a>
        </p>
      </div>
    </body>
    </html>
    <?php
}

// =================================================================
// 2) DASHBOARD
// =================================================================
function _yp_panel(): void {
    $stUrun   = (int) db_deger('SELECT COUNT(*) FROM urunler');
    $stUrunA  = (int) db_deger('SELECT COUNT(*) FROM urunler WHERE aktif = 1');
    $stKat    = (int) db_deger('SELECT COUNT(*) FROM kategoriler WHERE aktif = 1');
    $stRef    = (int) db_deger('SELECT COUNT(*) FROM referanslar WHERE aktif = 1');
    $stTeklif = (int) db_deger('SELECT COUNT(*) FROM teklifler');
    $stTekY   = _yp_teklif_yeni_sayi();
    $stMesaj  = (int) db_deger('SELECT COUNT(*) FROM iletisim_mesajlari');
    $stMesY   = _yp_mesaj_okunmamis_sayi();
    $sonTeklifler = db_liste('SELECT * FROM teklifler ORDER BY id DESC LIMIT 5');
    $sonMesajlar = db_liste('SELECT * FROM iletisim_mesajlari ORDER BY id DESC LIMIT 5');
    ?>
    <div class="yp-istatistikler">
      <div class="yp-stat b">
        <div class="yp-stat-adet"><?= $stUrun ?></div>
        <div class="yp-stat-etiket">Toplam Urun (<?= $stUrunA ?> aktif)</div>
      </div>
      <div class="yp-stat g">
        <div class="yp-stat-adet"><?= $stKat ?></div>
        <div class="yp-stat-etiket">Aktif Kategori</div>
      </div>
      <div class="yp-stat y">
        <div class="yp-stat-adet"><?= $stRef ?></div>
        <div class="yp-stat-etiket">Referans</div>
      </div>
      <div class="yp-stat r">
        <div class="yp-stat-adet"><?= $stTeklif ?> <small style="font-size:0.7em; color:#E53E3E;"><?= $stTekY ?> yeni</small></div>
        <div class="yp-stat-etiket">Teklif Talebi</div>
      </div>
      <div class="yp-stat b">
        <div class="yp-stat-adet"><?= $stMesaj ?> <small style="font-size:0.7em; color:#3182CE;"><?= $stMesY ?> yeni</small></div>
        <div class="yp-stat-etiket">Iletisim Mesaji</div>
      </div>
    </div>

    <div class="yp-satir">
      <div class="yp-panel">
        <div class="yp-panel-bas">
          <h2>📋 Son Teklifler</h2>
          <a href="?is=teklifler" class="yp-btn yp-btn-kucuk yp-btn-anahat">Tumu</a>
        </div>
        <div class="yp-panel-gvd sifir">
          <?php if (empty($sonTeklifler)): ?>
            <p style="padding:20px; color:#64748b;">Henuz teklif yok.</p>
          <?php else: ?>
            <table class="yp-tablo">
              <thead><tr><th>Ad</th><th>Durum</th><th>Tarih</th></tr></thead>
              <tbody>
              <?php foreach ($sonTeklifler as $t): ?>
                <tr>
                  <td><a href="?is=teklif&id=<?= (int)$t['id'] ?>"><?= e($t['ad_soyad']) ?></a></td>
                  <td><span class="yp-rozet yp-rozet-<?= e(substr($t['durum'], 0, 4)) ?>"><?= e($t['durum']) ?></span></td>
                  <td style="color:#64748b;"><?= e(tarih($t['olusturma'])) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <div class="yp-panel">
        <div class="yp-panel-bas">
          <h2>✉ Son Mesajlar</h2>
          <a href="?is=mesajlar" class="yp-btn yp-btn-kucuk yp-btn-anahat">Tumu</a>
        </div>
        <div class="yp-panel-gvd sifir">
          <?php if (empty($sonMesajlar)): ?>
            <p style="padding:20px; color:#64748b;">Henuz mesaj yok.</p>
          <?php else: ?>
            <table class="yp-tablo">
              <thead><tr><th>Ad</th><th>Konu</th><th>Tarih</th></tr></thead>
              <tbody>
              <?php foreach ($sonMesajlar as $m): ?>
                <tr style="<?= !$m['okundu'] ? 'font-weight:600;' : '' ?>">
                  <td><a href="?is=mesaj&id=<?= (int)$m['id'] ?>"><?= e($m['ad_soyad']) ?></a></td>
                  <td><?= e(kisalt($m['konu'] ?: $m['mesaj'], 40)) ?></td>
                  <td style="color:#64748b;"><?= e(tarih($m['olusturma'])) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="yp-panel">
      <div class="yp-panel-bas"><h2>ℹ Sistem Bilgisi</h2></div>
      <div class="yp-panel-gvd">
        <table class="yp-tablo">
          <tr><th style="width:30%">Versiyon</th><td>v<?= e(Updater::mevcutVersiyon()) ?></td></tr>
          <tr><th>PHP</th><td><?= PHP_VERSION ?></td></tr>
          <tr><th>MySQL</th><td><?= e(db()->getAttribute(PDO::ATTR_SERVER_VERSION)) ?></td></tr>
          <tr><th>Site URL</th><td><a href="<?= e(SITE_URL) ?>" target="_blank"><?= e(SITE_URL) ?></a></td></tr>
          <tr><th>Default Dil</th><td><?= e(DEFAULT_LANG) ?></td></tr>
        </table>
      </div>
    </div>
    <?php
}

// =================================================================
// 3) KATEGORILER
// =================================================================
function _yp_kategoriler(): void {
    $q = trim($_GET['q'] ?? '');
    $sql = 'SELECT * FROM kategoriler WHERE 1=1';
    $p = [];
    if ($q !== '') {
        $sql .= ' AND (ad_tr LIKE :q1 OR ad_en LIKE :q2 OR slug LIKE :q3)';
        $p['q1'] = '%' . $q . '%';
        $p['q2'] = '%' . $q . '%';
        $p['q3'] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY sira, id';
    $liste = db_liste($sql, $p);
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>🏷 Kategoriler</h2>
        <a href="?is=kategori" class="yp-btn">+ Yeni Kategori</a>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="kategoriler">
          <input type="text" name="q" placeholder="Ara..." value="<?= e($q) ?>">
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>
        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Henuz kategori yok.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Adi (TR)</th><th>Slug</th><th>Sira</th><th>Durum</th><th>Islem</th></tr></thead>
          <tbody>
          <?php foreach ($liste as $k): ?>
            <tr>
              <td><?= (int)$k['id'] ?></td>
              <td><strong><?= e($k['ad_tr']) ?></strong><br><small style="color:#64748b;"><?= e($k['ad_en'] ?: '-') ?></small></td>
              <td><code><?= e($k['slug']) ?></code></td>
              <td><?= (int)$k['sira'] ?></td>
              <td><span class="yp-rozet <?= $k['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $k['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="?is=kategori&id=<?= (int)$k['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <form method="POST" action="?is=kategori-sil&id=<?= (int)$k['id'] ?>" data-onay="Bu kategoriyi silmek istediginizden emin misiniz? Iliskili urunler varsa silinemez." style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_kategori_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $kat = $id ? db_satir('SELECT * FROM kategoriler WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$kat) { flash_ekle('hata', 'Kategori bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=kategoriler'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'slug'          => slug($_POST['slug'] ?: $_POST['ad_tr']),
                'ad_tr'         => trim($_POST['ad_tr'] ?? ''),
                'ad_en'         => trim($_POST['ad_en'] ?? '') ?: null,
                'ad_ar'         => trim($_POST['ad_ar'] ?? '') ?: null,
                'aciklama_tr'   => trim($_POST['aciklama_tr'] ?? '') ?: null,
                'aciklama_en'   => trim($_POST['aciklama_en'] ?? '') ?: null,
                'aciklama_ar'   => trim($_POST['aciklama_ar'] ?? '') ?: null,
                'ikon'          => trim($_POST['ikon'] ?? '') ?: null,
                'sira'          => (int)($_POST['sira'] ?? 0),
                'aktif'         => !empty($_POST['aktif']) ? 1 : 0,
            ];

            // Gorsel yukle
            if (!empty($_FILES['gorsel']['tmp_name'])) {
                $r = dosya_yukle($_FILES['gorsel'], 'kategoriler');
                if (!empty($r['hata'])) { flash_ekle('hata', $r['hata']); }
                else { $veri['gorsel'] = $r['yol']; }
            }

            try {
                if ($id) {
                    db_guncelle('kategoriler', $veri, 'id = :id', ['id' => $id]);
                    flash_ekle('ok', 'Kategori guncellendi.');
                    log_yaz('kategori_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                } else {
                    $id = db_ekle('kategoriler', $veri);
                    flash_ekle('ok', 'Kategori eklendi.');
                    log_yaz('kategori_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                }
                yonlendir(SITE_URL . '/yonetim.php?is=kategoriler');
            } catch (Throwable $e) {
                flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage());
            }
        }
        $kat = array_merge($kat ?: [], $_POST);
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2><?= $id ? 'Kategoriyi Duzenle' : 'Yeni Kategori' ?></h2>
        <a href="?is=kategoriler" class="yp-btn yp-btn-anahat yp-btn-kucuk">← Listeye Don</a>
      </div>
      <div class="yp-panel-gvd">
      <form method="POST" enctype="multipart/form-data" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="yp-diller" data-hedef="kat">
          <button type="button" data-dil="tr" class="aktif">🇹🇷 Turkce</button>
          <button type="button" data-dil="en">🇬🇧 Ingilizce</button>
          <button type="button" data-dil="ar">🇸🇦 Arapca</button>
        </div>

        <div class="yp-dil-panel aktif" data-dil-panel="kat" data-dil-kod="tr">
          <div class="yp-alan">
            <label>Adi (TR) *</label>
            <input type="text" name="ad_tr" required value="<?= e($kat['ad_tr'] ?? '') ?>" data-slug-kaynak="slug">
          </div>
          <div class="yp-alan">
            <label>Aciklama (TR)</label>
            <textarea name="aciklama_tr"><?= e($kat['aciklama_tr'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="yp-dil-panel" data-dil-panel="kat" data-dil-kod="en">
          <div class="yp-alan"><label>Name (EN)</label><input type="text" name="ad_en" value="<?= e($kat['ad_en'] ?? '') ?>"></div>
          <div class="yp-alan"><label>Description (EN)</label><textarea name="aciklama_en"><?= e($kat['aciklama_en'] ?? '') ?></textarea></div>
        </div>
        <div class="yp-dil-panel" data-dil-panel="kat" data-dil-kod="ar">
          <div class="yp-alan"><label>الاسم (AR)</label><input type="text" name="ad_ar" dir="rtl" value="<?= e($kat['ad_ar'] ?? '') ?>"></div>
          <div class="yp-alan"><label>الوصف (AR)</label><textarea name="aciklama_ar" dir="rtl"><?= e($kat['aciklama_ar'] ?? '') ?></textarea></div>
        </div>

        <div class="yp-satir-3">
          <div class="yp-alan"><label>Slug</label><input type="text" id="slug" name="slug" value="<?= e($kat['slug'] ?? '') ?>"></div>
          <div class="yp-alan"><label>Ikon (emoji veya simge)</label><input type="text" name="ikon" value="<?= e($kat['ikon'] ?? '') ?>" placeholder="🔲 / ◈ / ⚡"></div>
          <div class="yp-alan"><label>Sira</label><input type="number" name="sira" value="<?= e((string)($kat['sira'] ?? 0)) ?>"></div>
        </div>

        <div class="yp-alan">
          <label>Kategori Gorseli</label>
          <div class="yp-resim-yukle">
            <div class="yp-resim-onizleme" id="katOnz">
              <?php if (!empty($kat['gorsel'])): ?><img src="<?= e(upload($kat['gorsel'])) ?>" alt=""><?php else: ?>yok<?php endif; ?>
            </div>
            <input type="file" name="gorsel" accept="image/*" data-onizleme="katOnz">
          </div>
        </div>

        <div class="yp-alan-onay">
          <input type="checkbox" name="aktif" id="aktif" <?= !isset($kat['aktif']) || $kat['aktif'] ? 'checked' : '' ?>>
          <label for="aktif">Aktif</label>
        </div>

        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>
    <?php
}

function _yp_kategori_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) {
        flash_ekle('hata', 'CSRF hatasi');
        yonlendir(SITE_URL . '/yonetim.php?is=kategoriler');
    }
    $id = (int)($_GET['id'] ?? 0);
    try {
        $u = (int) db_deger('SELECT COUNT(*) FROM urunler WHERE kategori_id = :id', ['id' => $id]);
        if ($u > 0) {
            flash_ekle('hata', 'Bu kategoride ' . $u . ' urun var. Onceki urunleri tasiyin/silin.');
        } else {
            db_sil('kategoriler', 'id = :id', ['id' => $id]);
            flash_ekle('ok', 'Kategori silindi.');
            log_yaz('kategori_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
        }
    } catch (Throwable $e) {
        flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage());
    }
    yonlendir(SITE_URL . '/yonetim.php?is=kategoriler');
}

// =================================================================
// 4) URUNLER
// =================================================================
function _yp_urunler(): void {
    $q = trim($_GET['q'] ?? '');
    $kat = (int)($_GET['kat'] ?? 0);
    $sql = 'SELECT u.*, k.ad_tr AS kat_ad FROM urunler u LEFT JOIN kategoriler k ON k.id = u.kategori_id WHERE 1=1';
    $p = [];
    if ($q !== '') { $sql .= ' AND (u.ad_tr LIKE :q1 OR u.urun_kodu LIKE :q2 OR u.slug LIKE :q3)'; $p['q1'] = '%' . $q . '%'; $p['q2'] = '%' . $q . '%'; $p['q3'] = '%' . $q . '%'; }
    if ($kat > 0) { $sql .= ' AND u.kategori_id = :k'; $p['k'] = $kat; }
    $sql .= ' ORDER BY u.sira, u.id DESC';
    $liste = db_liste($sql, $p);
    $kategoriler = db_liste('SELECT id, ad_tr FROM kategoriler ORDER BY sira, id');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>📦 Urunler</h2>
        <a href="?is=urun" class="yp-btn">+ Yeni Urun</a>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="urunler">
          <input type="text" name="q" placeholder="Urun ara..." value="<?= e($q) ?>">
          <select name="kat">
            <option value="0">Tum Kategoriler</option>
            <?php foreach ($kategoriler as $kt): ?>
              <option value="<?= (int)$kt['id'] ?>" <?= $kat === (int)$kt['id'] ? 'selected' : '' ?>><?= e($kt['ad_tr']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>

        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Urun bulunamadi.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead>
            <tr><th>#</th><th>Gorsel</th><th>Adi</th><th>Kategori</th><th>Kod</th><th>Piksel</th><th>Durum</th><th>Islem</th></tr>
          </thead>
          <tbody>
          <?php foreach ($liste as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?php if ($u['ana_gorsel']): ?><img src="<?= e(upload($u['ana_gorsel'])) ?>" class="yp-tb-resim"><?php else: ?><div style="width:48px;height:48px;background:#f1f5f9;border-radius:6px;"></div><?php endif; ?></td>
              <td><strong><?= e($u['ad_tr']) ?></strong><?php if ($u['vitrin']): ?> <span class="yp-rozet yp-rozet-yeni">VITRIN</span><?php endif; ?><br><small style="color:#64748b;"><?= e($u['slug']) ?></small></td>
              <td><?= e($u['kat_ad'] ?? '-') ?></td>
              <td><code><?= e($u['urun_kodu'] ?: '-') ?></code></td>
              <td><?= e($u['piksel'] ?: '-') ?></td>
              <td><span class="yp-rozet <?= $u['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $u['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="?is=urun&id=<?= (int)$u['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <form method="POST" action="?is=urun-sil&id=<?= (int)$u['id'] ?>" data-onay="Bu urunu silmek istediginizden emin misiniz?" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_urun_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $u = $id ? db_satir('SELECT * FROM urunler WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$u) { flash_ekle('hata', 'Urun bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=urunler'); }
    $kategoriler = db_liste('SELECT id, ad_tr FROM kategoriler WHERE aktif = 1 ORDER BY sira, id');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            // Ozellikler - JSON
            $ozellikler = [];
            foreach (['tr','en','ar'] as $d) {
                $bas = $_POST['ozellik_baslik_' . $d] ?? [];
                $deg = $_POST['ozellik_deger_' . $d] ?? [];
                $ozListe = [];
                foreach ($bas as $i => $b) {
                    $b = trim((string)$b); $dv = trim((string)($deg[$i] ?? ''));
                    if ($b !== '' || $dv !== '') $ozListe[] = ['baslik' => $b, 'deger' => $dv];
                }
                $ozellikler[$d] = !empty($ozListe) ? json_encode($ozListe, JSON_UNESCAPED_UNICODE) : null;
            }

            $veri = [
                'kategori_id'     => (int)($_POST['kategori_id'] ?? 0),
                'slug'            => slug($_POST['slug'] ?: $_POST['ad_tr']),
                'urun_kodu'       => trim($_POST['urun_kodu'] ?? '') ?: null,
                'ad_tr'           => trim($_POST['ad_tr'] ?? ''),
                'ad_en'           => trim($_POST['ad_en'] ?? '') ?: null,
                'ad_ar'           => trim($_POST['ad_ar'] ?? '') ?: null,
                'ozet_tr'         => trim($_POST['ozet_tr'] ?? '') ?: null,
                'ozet_en'         => trim($_POST['ozet_en'] ?? '') ?: null,
                'ozet_ar'         => trim($_POST['ozet_ar'] ?? '') ?: null,
                'aciklama_tr'     => $_POST['aciklama_tr'] ?? null,
                'aciklama_en'     => $_POST['aciklama_en'] ?? null,
                'aciklama_ar'     => $_POST['aciklama_ar'] ?? null,
                'ozellikler_tr'   => $ozellikler['tr'],
                'ozellikler_en'   => $ozellikler['en'],
                'ozellikler_ar'   => $ozellikler['ar'],
                'piksel'          => trim($_POST['piksel'] ?? '') ?: null,
                'olcu'            => trim($_POST['olcu'] ?? '') ?: null,
                'agirlik'         => trim($_POST['agirlik'] ?? '') ?: null,
                'vitrin'          => !empty($_POST['vitrin']) ? 1 : 0,
                'yeni'            => !empty($_POST['yeni']) ? 1 : 0,
                'sira'            => (int)($_POST['sira'] ?? 0),
                'aktif'           => !empty($_POST['aktif']) ? 1 : 0,
                'seo_baslik_tr'   => trim($_POST['seo_baslik_tr'] ?? '') ?: null,
                'seo_aciklama_tr' => trim($_POST['seo_aciklama_tr'] ?? '') ?: null,
            ];

            // Ana gorsel
            if (!empty($_FILES['ana_gorsel']['tmp_name'])) {
                $r = dosya_yukle($_FILES['ana_gorsel'], 'urunler');
                if (!empty($r['hata'])) flash_ekle('hata', 'Ana gorsel: ' . $r['hata']);
                else $veri['ana_gorsel'] = $r['yol'];
            }

            // Galeri
            $galeri = $id && !empty($u['galeri']) ? (json_decode($u['galeri'], true) ?: []) : [];

            // Silinecek galeri dosyalari
            if (!empty($_POST['galeri_silinenler'])) {
                $silinenler = explode(',', $_POST['galeri_silinenler']);
                $galeri = array_values(array_filter($galeri, fn($g) => !in_array($g, $silinenler, true)));
            }

            // Yeni galeri dosyalari
            if (!empty($_FILES['galeri']['tmp_name'][0])) {
                foreach ($_FILES['galeri']['tmp_name'] as $i => $tmp) {
                    if (!$tmp) continue;
                    $dosya = [
                        'tmp_name' => $tmp,
                        'size'     => $_FILES['galeri']['size'][$i],
                        'error'    => $_FILES['galeri']['error'][$i],
                    ];
                    $r = dosya_yukle($dosya, 'urunler');
                    if (empty($r['hata'])) $galeri[] = $r['yol'];
                }
            }
            $veri['galeri'] = !empty($galeri) ? json_encode($galeri, JSON_UNESCAPED_UNICODE) : null;

            try {
                if ($id) {
                    db_guncelle('urunler', $veri, 'id = :id', ['id' => $id]);
                    flash_ekle('ok', 'Urun guncellendi.');
                    log_yaz('urun_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                } else {
                    $id = db_ekle('urunler', $veri);
                    flash_ekle('ok', 'Urun eklendi.');
                    log_yaz('urun_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                }
                yonlendir(SITE_URL . '/yonetim.php?is=urun&id=' . $id);
            } catch (Throwable $e) {
                flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage());
            }
        }
        $u = array_merge($u ?: [], $_POST);
    }

    $galeri = !empty($u['galeri']) ? (json_decode($u['galeri'], true) ?: []) : [];
    $ozTr = !empty($u['ozellikler_tr']) ? (json_decode($u['ozellikler_tr'], true) ?: []) : [];
    $ozEn = !empty($u['ozellikler_en']) ? (json_decode($u['ozellikler_en'], true) ?: []) : [];
    $ozAr = !empty($u['ozellikler_ar']) ? (json_decode($u['ozellikler_ar'], true) ?: []) : [];
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2><?= $id ? 'Urunu Duzenle' : 'Yeni Urun' ?></h2>
        <a href="?is=urunler" class="yp-btn yp-btn-anahat yp-btn-kucuk">← Listeye Don</a>
      </div>
      <div class="yp-panel-gvd">
      <form method="POST" enctype="multipart/form-data" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" id="galeriSilinenler" name="galeri_silinenler" value="">

        <div class="yp-diller" data-hedef="urun">
          <button type="button" data-dil="tr" class="aktif">🇹🇷 TR</button>
          <button type="button" data-dil="en">🇬🇧 EN</button>
          <button type="button" data-dil="ar">🇸🇦 AR</button>
        </div>

        <?php foreach (['tr' => 'Turkce', 'en' => 'English', 'ar' => 'العربية'] as $d => $dAd): ?>
          <div class="yp-dil-panel <?= $d === 'tr' ? 'aktif' : '' ?>" data-dil-panel="urun" data-dil-kod="<?= $d ?>">
            <div class="yp-alan">
              <label>Urun Adi (<?= strtoupper($d) ?>) <?= $d === 'tr' ? '*' : '' ?></label>
              <input type="text" name="ad_<?= $d ?>" <?= $d === 'tr' ? 'required data-slug-kaynak="slug"' : '' ?> value="<?= e($u['ad_' . $d] ?? '') ?>" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>>
            </div>
            <div class="yp-alan">
              <label>Kisa Ozet (<?= strtoupper($d) ?>)</label>
              <textarea name="ozet_<?= $d ?>" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>><?= e($u['ozet_' . $d] ?? '') ?></textarea>
            </div>
            <div class="yp-alan">
              <label>Detayli Aciklama (<?= strtoupper($d) ?>) <small>(HTML destekli)</small></label>
              <textarea name="aciklama_<?= $d ?>" rows="6" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>><?= e($u['aciklama_' . $d] ?? '') ?></textarea>
            </div>

            <div class="yp-alan">
              <label>Ozellikler (<?= strtoupper($d) ?>)</label>
              <div id="ozellikListe_<?= $d ?>">
                <?php
                $ozVar = ${'oz' . ucfirst($d)};
                foreach ($ozVar as $o): ?>
                  <div class="yp-ozellik-satir">
                    <input type="text" name="ozellik_baslik_<?= $d ?>[]" placeholder="Baslik" value="<?= e($o['baslik'] ?? '') ?>">
                    <input type="text" name="ozellik_deger_<?= $d ?>[]" placeholder="Deger" value="<?= e($o['deger'] ?? '') ?>">
                    <button type="button" class="yp-ozellik-sil" onclick="this.parentElement.remove()">×</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="yp-btn yp-btn-kucuk yp-btn-anahat" onclick="ypOzellikEkle('<?= $d ?>')">+ Ozellik Ekle</button>
            </div>
          </div>
        <?php endforeach; ?>

        <h3 style="margin:24px 0 12px; padding-top:16px; border-top:1px solid #e2e8f0;">📊 Genel Ozellikler</h3>

        <div class="yp-satir-3">
          <div class="yp-alan">
            <label>Kategori *</label>
            <select name="kategori_id" required>
              <option value="">-- seciniz --</option>
              <?php foreach ($kategoriler as $k): ?>
                <option value="<?= (int)$k['id'] ?>" <?= ($u['kategori_id'] ?? 0) == $k['id'] ? 'selected' : '' ?>><?= e($k['ad_tr']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="yp-alan">
            <label>Urun Kodu</label>
            <input type="text" name="urun_kodu" value="<?= e($u['urun_kodu'] ?? '') ?>" placeholder="TL-LM-256">
          </div>
          <div class="yp-alan">
            <label>Slug (URL)</label>
            <input type="text" id="slug" name="slug" value="<?= e($u['slug'] ?? '') ?>">
          </div>
        </div>

        <div class="yp-satir-3">
          <div class="yp-alan">
            <label>Piksel Araligi</label>
            <input type="text" name="piksel" value="<?= e($u['piksel'] ?? '') ?>" placeholder="P2.5">
          </div>
          <div class="yp-alan">
            <label>Olcu</label>
            <input type="text" name="olcu" value="<?= e($u['olcu'] ?? '') ?>" placeholder="96x256 cm">
          </div>
          <div class="yp-alan">
            <label>Agirlik</label>
            <input type="text" name="agirlik" value="<?= e($u['agirlik'] ?? '') ?>" placeholder="15 kg">
          </div>
        </div>

        <h3 style="margin:24px 0 12px; padding-top:16px; border-top:1px solid #e2e8f0;">🖼 Gorseller</h3>

        <div class="yp-alan">
          <label>Ana Gorsel</label>
          <div class="yp-resim-yukle">
            <div class="yp-resim-onizleme" id="anaOnz">
              <?php if (!empty($u['ana_gorsel'])): ?><img src="<?= e(upload($u['ana_gorsel'])) ?>"><?php else: ?>yok<?php endif; ?>
            </div>
            <input type="file" name="ana_gorsel" accept="image/*" data-onizleme="anaOnz">
          </div>
        </div>

        <div class="yp-alan">
          <label>Galeri (coklu)</label>
          <input type="file" name="galeri[]" accept="image/*" multiple>
          <?php if (!empty($galeri)): ?>
            <div class="yp-galeri-grid">
              <?php foreach ($galeri as $g): ?>
                <div class="yp-galeri-ogesi">
                  <img src="<?= e(upload($g)) ?>" alt="">
                  <button type="button" class="yp-sil-x" onclick="ypGaleriSil(this, '<?= e($g) ?>')">×</button>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <h3 style="margin:24px 0 12px; padding-top:16px; border-top:1px solid #e2e8f0;">🔎 SEO</h3>

        <div class="yp-alan">
          <label>SEO Baslik (TR)</label>
          <input type="text" name="seo_baslik_tr" value="<?= e($u['seo_baslik_tr'] ?? '') ?>">
        </div>
        <div class="yp-alan">
          <label>SEO Aciklama (TR)</label>
          <textarea name="seo_aciklama_tr"><?= e($u['seo_aciklama_tr'] ?? '') ?></textarea>
        </div>

        <h3 style="margin:24px 0 12px; padding-top:16px; border-top:1px solid #e2e8f0;">⚙ Durum</h3>

        <div class="yp-satir-3">
          <div class="yp-alan"><label>Sira</label><input type="number" name="sira" value="<?= e((string)($u['sira'] ?? 0)) ?>"></div>
          <div class="yp-alan-onay" style="margin-top:28px;">
            <input type="checkbox" name="aktif" id="aktif" <?= !isset($u['aktif']) || $u['aktif'] ? 'checked' : '' ?>>
            <label for="aktif">Aktif</label>
          </div>
          <div class="yp-alan-onay" style="margin-top:28px;">
            <input type="checkbox" name="vitrin" id="vitrin" <?= !empty($u['vitrin']) ? 'checked' : '' ?>>
            <label for="vitrin">Vitrin (anasayfada goster)</label>
          </div>
        </div>
        <div class="yp-alan-onay">
          <input type="checkbox" name="yeni" id="yeni" <?= !empty($u['yeni']) ? 'checked' : '' ?>>
          <label for="yeni">"Yeni" rozeti goster</label>
        </div>

        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>
    <?php
}

function _yp_urun_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=urunler'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('urunler', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Urun silindi.');
        log_yaz('urun_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) {
        flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage());
    }
    yonlendir(SITE_URL . '/yonetim.php?is=urunler');
}

// =================================================================
// 5) REFERANSLAR
// =================================================================
function _yp_referanslar(): void {
    $q = trim($_GET['q'] ?? '');
    $sql = 'SELECT * FROM referanslar WHERE 1=1';
    $p = [];
    if ($q !== '') { $sql .= ' AND (musteri_tr LIKE :q1 OR lokasyon LIKE :q2 OR sektor LIKE :q3)'; $p['q1'] = '%' . $q . '%'; $p['q2'] = '%' . $q . '%'; $p['q3'] = '%' . $q . '%'; }
    $sql .= ' ORDER BY sira, id DESC';
    $liste = db_liste($sql, $p);
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>🏢 Referanslar</h2>
        <a href="?is=referans" class="yp-btn">+ Yeni Referans</a>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="referanslar">
          <input type="text" name="q" placeholder="Ara..." value="<?= e($q) ?>">
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>

        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Henuz referans yok.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Gorsel</th><th>Musteri</th><th>Lokasyon</th><th>Sektor</th><th>Tarih</th><th>Durum</th><th>Islem</th></tr></thead>
          <tbody>
          <?php foreach ($liste as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?php if ($r['ana_gorsel']): ?><img src="<?= e(upload($r['ana_gorsel'])) ?>" class="yp-tb-resim"><?php else: ?><div style="width:48px;height:48px;background:#f1f5f9;border-radius:6px;"></div><?php endif; ?></td>
              <td><strong><?= e($r['musteri_tr']) ?></strong><?php if ($r['vitrin']): ?> <span class="yp-rozet yp-rozet-yeni">VITRIN</span><?php endif; ?></td>
              <td><?= e($r['lokasyon'] ?: '-') ?></td>
              <td><?= e($r['sektor'] ?: '-') ?></td>
              <td><?= $r['proje_tarihi'] ? e(tarih($r['proje_tarihi'], 'Y')) : '-' ?></td>
              <td><span class="yp-rozet <?= $r['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $r['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="?is=referans&id=<?= (int)$r['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <form method="POST" action="?is=referans-sil&id=<?= (int)$r['id'] ?>" data-onay="Referansi silmek istediginizden emin misiniz?" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_referans_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $r = $id ? db_satir('SELECT * FROM referanslar WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$r) { flash_ekle('hata', 'Referans bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=referanslar'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'slug'         => slug($_POST['slug'] ?: $_POST['musteri_tr']),
                'musteri_tr'   => trim($_POST['musteri_tr'] ?? ''),
                'musteri_en'   => trim($_POST['musteri_en'] ?? '') ?: null,
                'musteri_ar'   => trim($_POST['musteri_ar'] ?? '') ?: null,
                'lokasyon'     => trim($_POST['lokasyon'] ?? '') ?: null,
                'proje_tarihi' => trim($_POST['proje_tarihi'] ?? '') ?: null,
                'sektor'       => trim($_POST['sektor'] ?? '') ?: null,
                'aciklama_tr'  => trim($_POST['aciklama_tr'] ?? '') ?: null,
                'aciklama_en'  => trim($_POST['aciklama_en'] ?? '') ?: null,
                'aciklama_ar'  => trim($_POST['aciklama_ar'] ?? '') ?: null,
                'vitrin'       => !empty($_POST['vitrin']) ? 1 : 0,
                'sira'         => (int)($_POST['sira'] ?? 0),
                'aktif'        => !empty($_POST['aktif']) ? 1 : 0,
            ];

            if (!empty($_FILES['ana_gorsel']['tmp_name'])) {
                $rz = dosya_yukle($_FILES['ana_gorsel'], 'referanslar');
                if (!empty($rz['hata'])) flash_ekle('hata', 'Ana gorsel: ' . $rz['hata']);
                else $veri['ana_gorsel'] = $rz['yol'];
            }

            // Galeri
            $galeri = $id && !empty($r['galeri']) ? (json_decode($r['galeri'], true) ?: []) : [];
            if (!empty($_POST['galeri_silinenler'])) {
                $silinenler = explode(',', $_POST['galeri_silinenler']);
                $galeri = array_values(array_filter($galeri, fn($g) => !in_array($g, $silinenler, true)));
            }
            if (!empty($_FILES['galeri']['tmp_name'][0])) {
                foreach ($_FILES['galeri']['tmp_name'] as $i => $tmp) {
                    if (!$tmp) continue;
                    $dosya = ['tmp_name' => $tmp, 'size' => $_FILES['galeri']['size'][$i], 'error' => $_FILES['galeri']['error'][$i]];
                    $rz = dosya_yukle($dosya, 'referanslar');
                    if (empty($rz['hata'])) $galeri[] = $rz['yol'];
                }
            }
            $veri['galeri'] = !empty($galeri) ? json_encode($galeri, JSON_UNESCAPED_UNICODE) : null;

            try {
                if ($id) { db_guncelle('referanslar', $veri, 'id = :id', ['id' => $id]); flash_ekle('ok', 'Referans guncellendi.'); log_yaz('referans_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                else { $id = db_ekle('referanslar', $veri); flash_ekle('ok', 'Referans eklendi.'); log_yaz('referans_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                yonlendir(SITE_URL . '/yonetim.php?is=referans&id=' . $id);
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
        $r = array_merge($r ?: [], $_POST);
    }

    $galeri = !empty($r['galeri']) ? (json_decode($r['galeri'], true) ?: []) : [];
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2><?= $id ? 'Referansi Duzenle' : 'Yeni Referans' ?></h2>
        <a href="?is=referanslar" class="yp-btn yp-btn-anahat yp-btn-kucuk">← Listeye Don</a>
      </div>
      <div class="yp-panel-gvd">
      <form method="POST" enctype="multipart/form-data" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" id="galeriSilinenler" name="galeri_silinenler" value="">

        <div class="yp-diller" data-hedef="ref">
          <button type="button" data-dil="tr" class="aktif">🇹🇷 TR</button>
          <button type="button" data-dil="en">🇬🇧 EN</button>
          <button type="button" data-dil="ar">🇸🇦 AR</button>
        </div>

        <?php foreach (['tr','en','ar'] as $d): ?>
          <div class="yp-dil-panel <?= $d === 'tr' ? 'aktif' : '' ?>" data-dil-panel="ref" data-dil-kod="<?= $d ?>">
            <div class="yp-alan">
              <label>Musteri Adi (<?= strtoupper($d) ?>) <?= $d === 'tr' ? '*' : '' ?></label>
              <input type="text" name="musteri_<?= $d ?>" <?= $d === 'tr' ? 'required data-slug-kaynak="slug"' : '' ?> value="<?= e($r['musteri_' . $d] ?? '') ?>" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>>
            </div>
            <div class="yp-alan">
              <label>Aciklama (<?= strtoupper($d) ?>)</label>
              <textarea name="aciklama_<?= $d ?>" rows="4" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>><?= e($r['aciklama_' . $d] ?? '') ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="yp-satir-3">
          <div class="yp-alan"><label>Slug</label><input type="text" id="slug" name="slug" value="<?= e($r['slug'] ?? '') ?>"></div>
          <div class="yp-alan"><label>Lokasyon</label><input type="text" name="lokasyon" value="<?= e($r['lokasyon'] ?? '') ?>" placeholder="Konya / Istanbul"></div>
          <div class="yp-alan"><label>Sektor</label><input type="text" name="sektor" value="<?= e($r['sektor'] ?? '') ?>" placeholder="Egitim / AVM / Belediye"></div>
        </div>

        <div class="yp-satir">
          <div class="yp-alan"><label>Proje Tarihi</label><input type="date" name="proje_tarihi" value="<?= e($r['proje_tarihi'] ?? '') ?>"></div>
          <div class="yp-alan"><label>Sira</label><input type="number" name="sira" value="<?= e((string)($r['sira'] ?? 0)) ?>"></div>
        </div>

        <div class="yp-alan">
          <label>Ana Gorsel</label>
          <div class="yp-resim-yukle">
            <div class="yp-resim-onizleme" id="refOnz">
              <?php if (!empty($r['ana_gorsel'])): ?><img src="<?= e(upload($r['ana_gorsel'])) ?>"><?php else: ?>yok<?php endif; ?>
            </div>
            <input type="file" name="ana_gorsel" accept="image/*" data-onizleme="refOnz">
          </div>
        </div>

        <div class="yp-alan">
          <label>Galeri (coklu)</label>
          <input type="file" name="galeri[]" accept="image/*" multiple>
          <?php if (!empty($galeri)): ?>
            <div class="yp-galeri-grid">
              <?php foreach ($galeri as $g): ?>
                <div class="yp-galeri-ogesi">
                  <img src="<?= e(upload($g)) ?>" alt="">
                  <button type="button" class="yp-sil-x" onclick="ypGaleriSil(this, '<?= e($g) ?>')">×</button>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="yp-satir">
          <div class="yp-alan-onay" style="margin-top:28px;">
            <input type="checkbox" name="aktif" id="aktif" <?= !isset($r['aktif']) || $r['aktif'] ? 'checked' : '' ?>>
            <label for="aktif">Aktif</label>
          </div>
          <div class="yp-alan-onay" style="margin-top:28px;">
            <input type="checkbox" name="vitrin" id="vitrin" <?= !empty($r['vitrin']) ? 'checked' : '' ?>>
            <label for="vitrin">Vitrin (anasayfada goster)</label>
          </div>
        </div>

        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>
    <?php
}

function _yp_referans_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=referanslar'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('referanslar', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Referans silindi.');
        log_yaz('referans_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=referanslar');
}

// =================================================================
// 5b) SLIDER
// =================================================================
function _yp_slider(): void {
    $liste = db_liste('SELECT * FROM slider ORDER BY sira ASC, id DESC');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>🎞 Slider</h2>
        <a href="?is=slider-form" class="yp-btn">+ Yeni Slider</a>
      </div>
      <div class="yp-tablo-kap">
        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Henuz slider yok.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Gorsel</th><th>Baslik</th><th>Buton</th><th>Sira</th><th>Durum</th><th>Islem</th></tr></thead>
          <tbody>
          <?php foreach ($liste as $s): ?>
            <tr>
              <td><?= (int)$s['id'] ?></td>
              <td><?php if ($s['gorsel']): ?><img src="<?= e(upload($s['gorsel'])) ?>" class="yp-tb-resim" style="width:80px;height:auto;"><?php else: ?><div style="width:80px;height:48px;background:#f1f5f9;border-radius:6px;"></div><?php endif; ?></td>
              <td><strong><?= e($s['baslik_tr'] ?: '-') ?></strong><?php if ($s['aciklama_tr']): ?><br><small style="color:#64748b;"><?= e(mb_substr($s['aciklama_tr'], 0, 60)) ?>...</small><?php endif; ?></td>
              <td><?php if ($s['buton_metin_tr']): ?><code><?= e($s['buton_metin_tr']) ?></code><br><small><?= e($s['buton_url'] ?: '-') ?></small><?php else: ?>-<?php endif; ?></td>
              <td><?= (int)$s['sira'] ?></td>
              <td><span class="yp-rozet <?= $s['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $s['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="?is=slider-form&id=<?= (int)$s['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <form method="POST" action="?is=slider-sil&id=<?= (int)$s['id'] ?>" data-onay="Slider kaydini silmek istediginizden emin misiniz?" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_slider_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $s = $id ? db_satir('SELECT * FROM slider WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$s) { flash_ekle('hata', 'Slider bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=slider'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'baslik_tr'      => trim($_POST['baslik_tr'] ?? '') ?: null,
                'baslik_en'      => trim($_POST['baslik_en'] ?? '') ?: null,
                'baslik_ar'      => trim($_POST['baslik_ar'] ?? '') ?: null,
                'aciklama_tr'    => trim($_POST['aciklama_tr'] ?? '') ?: null,
                'aciklama_en'    => trim($_POST['aciklama_en'] ?? '') ?: null,
                'aciklama_ar'    => trim($_POST['aciklama_ar'] ?? '') ?: null,
                'buton_metin_tr' => trim($_POST['buton_metin_tr'] ?? '') ?: null,
                'buton_metin_en' => trim($_POST['buton_metin_en'] ?? '') ?: null,
                'buton_metin_ar' => trim($_POST['buton_metin_ar'] ?? '') ?: null,
                'buton_url'      => trim($_POST['buton_url'] ?? '') ?: null,
                'sira'           => (int)($_POST['sira'] ?? 0),
                'aktif'          => !empty($_POST['aktif']) ? 1 : 0,
            ];

            if (!empty($_FILES['gorsel']['tmp_name'])) {
                $rz = dosya_yukle($_FILES['gorsel'], 'slider');
                if (!empty($rz['hata'])) flash_ekle('hata', 'Gorsel: ' . $rz['hata']);
                else $veri['gorsel'] = $rz['yol'];
            } elseif ($id && !empty($s['gorsel'])) {
                // Mevcut gorsel korunur (veri dizisine eklemiyoruz)
            } else {
                flash_ekle('hata', 'Slider icin gorsel gereklidir.');
                yonlendir(SITE_URL . '/yonetim.php?is=slider-form' . ($id ? '&id=' . $id : ''));
            }

            try {
                if ($id) { db_guncelle('slider', $veri, 'id = :id', ['id' => $id]); flash_ekle('ok', 'Slider guncellendi.'); log_yaz('slider_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                else { $id = db_ekle('slider', $veri); flash_ekle('ok', 'Slider eklendi.'); log_yaz('slider_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                yonlendir(SITE_URL . '/yonetim.php?is=slider');
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas"><h2>🎞 Slider <?= $id ? 'Duzenle' : 'Ekle' ?></h2></div>
      <form method="POST" enctype="multipart/form-data" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="yp-alan-grup">
          <h3>Gorsel</h3>
          <?php if ($id && !empty($s['gorsel'])): ?>
            <img src="<?= e(upload($s['gorsel'])) ?>" style="max-width:300px; height:auto; border-radius:8px; margin-bottom:10px;">
          <?php endif; ?>
          <label>Slider Gorseli (onerilen: 1600x600 px)</label>
          <input type="file" name="gorsel" accept="image/*">
        </div>

        <div class="yp-alan-grup">
          <h3>Baslik</h3>
          <div class="yp-kolon-3">
            <div class="yp-alan"><label>TR</label><input type="text" name="baslik_tr" value="<?= e($s['baslik_tr'] ?? '') ?>" maxlength="200"></div>
            <div class="yp-alan"><label>EN</label><input type="text" name="baslik_en" value="<?= e($s['baslik_en'] ?? '') ?>" maxlength="200"></div>
            <div class="yp-alan"><label>AR</label><input type="text" name="baslik_ar" value="<?= e($s['baslik_ar'] ?? '') ?>" maxlength="200" dir="rtl"></div>
          </div>
        </div>

        <div class="yp-alan-grup">
          <h3>Aciklama</h3>
          <div class="yp-kolon-3">
            <div class="yp-alan"><label>TR</label><textarea name="aciklama_tr" rows="3"><?= e($s['aciklama_tr'] ?? '') ?></textarea></div>
            <div class="yp-alan"><label>EN</label><textarea name="aciklama_en" rows="3"><?= e($s['aciklama_en'] ?? '') ?></textarea></div>
            <div class="yp-alan"><label>AR</label><textarea name="aciklama_ar" rows="3" dir="rtl"><?= e($s['aciklama_ar'] ?? '') ?></textarea></div>
          </div>
        </div>

        <div class="yp-alan-grup">
          <h3>Buton (opsiyonel)</h3>
          <div class="yp-kolon-3">
            <div class="yp-alan"><label>Buton Metni TR</label><input type="text" name="buton_metin_tr" value="<?= e($s['buton_metin_tr'] ?? '') ?>" maxlength="100" placeholder="Detay, Incele, Teklif Al..."></div>
            <div class="yp-alan"><label>Buton Metni EN</label><input type="text" name="buton_metin_en" value="<?= e($s['buton_metin_en'] ?? '') ?>" maxlength="100"></div>
            <div class="yp-alan"><label>Buton Metni AR</label><input type="text" name="buton_metin_ar" value="<?= e($s['buton_metin_ar'] ?? '') ?>" maxlength="100" dir="rtl"></div>
          </div>
          <div class="yp-alan"><label>Buton URL</label><input type="text" name="buton_url" value="<?= e($s['buton_url'] ?? '') ?>" maxlength="255" placeholder="/kategori/led-masa veya https://..."></div>
        </div>

        <div class="yp-kolon-2">
          <div class="yp-alan"><label>Sira</label><input type="number" name="sira" value="<?= (int)($s['sira'] ?? 0) ?>"></div>
          <div class="yp-alan"><label><input type="checkbox" name="aktif" value="1" <?= !isset($s['aktif']) || $s['aktif'] ? 'checked' : '' ?>> Aktif</label></div>
        </div>

        <div class="yp-form-altlik">
          <button type="submit" class="yp-btn">Kaydet</button>
          <a href="?is=slider" class="yp-btn yp-btn-anahat">Iptal</a>
        </div>
      </form>
    </div>
    <?php
}

function _yp_slider_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=slider'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('slider', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Slider silindi.');
        log_yaz('slider_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=slider');
}

// =================================================================
// 6) TEKLIFLER
// =================================================================
function _yp_teklifler(): void {
    $durum = trim($_GET['durum'] ?? '');
    $q = trim($_GET['q'] ?? '');
    $sayfa = max(1, (int)($_GET['s'] ?? 1));
    $limit = 30;
    $ofset = ($sayfa - 1) * $limit;

    $kosul = ' WHERE 1=1';
    $p = [];
    if (in_array($durum, ['yeni','incelendi','teklif_verildi','kazanildi','kaybedildi','iptal'], true)) {
        $kosul .= ' AND durum = :d'; $p['d'] = $durum;
    }
    if ($q !== '') {
        $kosul .= ' AND (ad_soyad LIKE :q1 OR eposta LIKE :q2 OR telefon LIKE :q3 OR firma LIKE :q4)';
        $p['q1'] = '%' . $q . '%';
        $p['q2'] = '%' . $q . '%';
        $p['q3'] = '%' . $q . '%';
        $p['q4'] = '%' . $q . '%';
    }

    $toplam = (int) db_deger('SELECT COUNT(*) FROM teklifler' . $kosul, $p);
    $liste = db_liste('SELECT t.*, u.ad_tr AS urun_ad FROM teklifler t LEFT JOIN urunler u ON u.id = t.urun_id' . $kosul . ' ORDER BY t.id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$ofset, $p);
    $sayfaSayisi = (int) ceil($toplam / $limit);

    $durumRenk = ['yeni' => 'yeni', 'incelendi' => 'inc', 'teklif_verildi' => 'ver', 'kazanildi' => 'kaz', 'kaybedildi' => 'kayb', 'iptal' => 'iptal'];
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>📋 Teklifler <small style="color:#64748b; font-weight:normal;">(<?= $toplam ?>)</small></h2>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="teklifler">
          <input type="text" name="q" placeholder="Ara (ad, eposta, tel, firma)..." value="<?= e($q) ?>">
          <select name="durum">
            <option value="">Tum Durumlar</option>
            <option value="yeni" <?= $durum === 'yeni' ? 'selected' : '' ?>>Yeni</option>
            <option value="incelendi" <?= $durum === 'incelendi' ? 'selected' : '' ?>>Incelendi</option>
            <option value="teklif_verildi" <?= $durum === 'teklif_verildi' ? 'selected' : '' ?>>Teklif Verildi</option>
            <option value="kazanildi" <?= $durum === 'kazanildi' ? 'selected' : '' ?>>Kazanildi</option>
            <option value="kaybedildi" <?= $durum === 'kaybedildi' ? 'selected' : '' ?>>Kaybedildi</option>
            <option value="iptal" <?= $durum === 'iptal' ? 'selected' : '' ?>>Iptal</option>
          </select>
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>

        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Teklif bulunamadi.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Ad Soyad</th><th>Firma</th><th>Iletisim</th><th>Urun</th><th>Adet</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($liste as $t): ?>
            <tr style="<?= $t['durum'] === 'yeni' ? 'background:#fef2f2;' : '' ?>">
              <td>#<?= (int)$t['id'] ?></td>
              <td><strong><?= e($t['ad_soyad']) ?></strong></td>
              <td><?= e($t['firma'] ?: '-') ?></td>
              <td><small><?= e($t['eposta']) ?><br><?= e($t['telefon']) ?></small></td>
              <td><?= e($t['urun_ad'] ?? '-') ?></td>
              <td><?= e((string)($t['adet'] ?? '-')) ?></td>
              <td><span class="yp-rozet yp-rozet-<?= e($durumRenk[$t['durum']] ?? 'yeni') ?>"><?= e($t['durum']) ?></span></td>
              <td><small><?= e(tarih($t['olusturma'], 'd.m.Y H:i')) ?></small></td>
              <td><a href="?is=teklif&id=<?= (int)$t['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Detay</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($sayfaSayisi > 1): ?>
          <div class="yp-sayfalama">
            <?php for ($i = 1; $i <= $sayfaSayisi; $i++): ?>
              <a href="?is=teklifler&s=<?= $i ?>&durum=<?= e($durum) ?>&q=<?= e($q) ?>" class="<?= $i === $sayfa ? 'aktif' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_teklif_detay(): void {
    $id = (int)($_GET['id'] ?? 0);
    $t = db_satir('SELECT t.*, u.ad_tr AS urun_ad, u.urun_kodu FROM teklifler t LEFT JOIN urunler u ON u.id = t.urun_id WHERE t.id = :id', ['id' => $id]);
    if (!$t) { flash_ekle('hata', 'Teklif bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=teklifler'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $yeniDurum = in_array($_POST['durum'] ?? '', ['yeni','incelendi','teklif_verildi','kazanildi','kaybedildi','iptal'], true) ? $_POST['durum'] : $t['durum'];
            db_guncelle('teklifler',
                ['durum' => $yeniDurum, 'notlar' => trim($_POST['notlar'] ?? '')],
                'id = :id', ['id' => $id]
            );
            flash_ekle('ok', 'Teklif guncellendi.');
            log_yaz('teklif_guncelle', "ID: $id durum: $yeniDurum", Auth::mevcutAdmin()['id']);
            yonlendir(SITE_URL . '/yonetim.php?is=teklif&id=' . $id);
        }
    }

    $durumRenk = ['yeni' => 'yeni', 'incelendi' => 'inc', 'teklif_verildi' => 'ver', 'kazanildi' => 'kaz', 'kaybedildi' => 'kayb', 'iptal' => 'iptal'];
    $mailto = 'mailto:' . $t['eposta'] . '?subject=' . rawurlencode('RE: Teklif Talebiniz #' . $t['id'] . ' - TeknikLED');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>📋 Teklif #<?= (int)$t['id'] ?> <span class="yp-rozet yp-rozet-<?= e($durumRenk[$t['durum']] ?? 'yeni') ?>"><?= e($t['durum']) ?></span></h2>
        <div style="display:flex; gap:8px;">
          <a href="<?= e($mailto) ?>" class="yp-btn yp-btn-kucuk">✉ E-posta ile Yanitla</a>
          <a href="?is=teklifler" class="yp-btn yp-btn-kucuk yp-btn-anahat">← Listeye Don</a>
        </div>
      </div>
      <div class="yp-panel-gvd">

      <div class="yp-satir">
        <div>
          <h3 style="font-size:1rem; margin-bottom:12px;">👤 Musteri Bilgileri</h3>
          <table class="yp-tablo" style="margin-bottom:20px;">
            <tr><th>Ad Soyad</th><td><strong><?= e($t['ad_soyad']) ?></strong></td></tr>
            <tr><th>Firma</th><td><?= e($t['firma'] ?: '-') ?></td></tr>
            <tr><th>E-posta</th><td><a href="mailto:<?= e($t['eposta']) ?>"><?= e($t['eposta']) ?></a></td></tr>
            <tr><th>Telefon</th><td><a href="tel:<?= e(preg_replace('/\s+/', '', $t['telefon'])) ?>"><?= e($t['telefon']) ?></a></td></tr>
            <tr><th>Sehir</th><td><?= e($t['sehir'] ?: '-') ?></td></tr>
            <tr><th>Dil</th><td><?= e(strtoupper($t['dil'])) ?></td></tr>
          </table>
        </div>
        <div>
          <h3 style="font-size:1rem; margin-bottom:12px;">📦 Talep Detaylari</h3>
          <table class="yp-tablo" style="margin-bottom:20px;">
            <tr><th>Urun</th><td><?= e($t['urun_ad'] ?: '-') ?><?php if ($t['urun_kodu']): ?> <small>(<?= e($t['urun_kodu']) ?>)</small><?php endif; ?></td></tr>
            <tr><th>Olcu</th><td><?= e($t['olcu_bilgisi'] ?: '-') ?></td></tr>
            <tr><th>Adet</th><td><?= e((string)($t['adet'] ?: '-')) ?></td></tr>
            <tr><th>Konu</th><td><?= e($t['konu'] ?: '-') ?></td></tr>
            <tr><th>Gelis</th><td><small><?= e(tarih($t['olusturma'], 'd.m.Y H:i')) ?><br>IP: <?= e($t['ip'] ?? '-') ?></small></td></tr>
          </table>
        </div>
      </div>

      <h3 style="font-size:1rem; margin:20px 0 12px;">💬 Mesaj</h3>
      <div style="background:#f8fafc; padding:16px; border-radius:8px; border-left:3px solid #3182CE; white-space:pre-wrap; line-height:1.7;"><?= e($t['mesaj']) ?></div>

      <form method="POST" style="margin-top:24px; padding-top:20px; border-top:1px solid #e2e8f0;">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <h3 style="font-size:1rem; margin-bottom:12px;">✏ Durum ve Notlar</h3>
        <div class="yp-satir">
          <div class="yp-alan">
            <label>Durum</label>
            <select name="durum">
              <option value="yeni" <?= $t['durum'] === 'yeni' ? 'selected' : '' ?>>🔴 Yeni</option>
              <option value="incelendi" <?= $t['durum'] === 'incelendi' ? 'selected' : '' ?>>👁 Incelendi</option>
              <option value="teklif_verildi" <?= $t['durum'] === 'teklif_verildi' ? 'selected' : '' ?>>📧 Teklif Verildi</option>
              <option value="kazanildi" <?= $t['durum'] === 'kazanildi' ? 'selected' : '' ?>>✅ Kazanildi</option>
              <option value="kaybedildi" <?= $t['durum'] === 'kaybedildi' ? 'selected' : '' ?>>❌ Kaybedildi</option>
              <option value="iptal" <?= $t['durum'] === 'iptal' ? 'selected' : '' ?>>⊘ Iptal</option>
            </select>
          </div>
          <div></div>
        </div>
        <div class="yp-alan">
          <label>Ic Notlar</label>
          <textarea name="notlar" rows="4" placeholder="Musteri ile ilgili dahili notlariniz..."><?= e($t['notlar'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="yp-btn">💾 Guncelle</button>
        <form method="POST" action="?is=teklif-sil&id=<?= (int)$t['id'] ?>" data-onay="Bu teklifi tamamen silmek istediginizden emin misiniz?" style="display:inline-block; margin-left:8px;">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <button class="yp-btn yp-btn-sil" type="submit">🗑 Teklifi Sil</button>
        </form>
      </form>

      </div>
    </div>
    <?php
}

function _yp_teklif_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=teklifler'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('teklifler', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Teklif silindi.');
        log_yaz('teklif_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=teklifler');
}

// =================================================================
// 7) MESAJLAR
// =================================================================
function _yp_mesajlar(): void {
    $q = trim($_GET['q'] ?? '');
    $filt = $_GET['filt'] ?? '';
    $sayfa = max(1, (int)($_GET['s'] ?? 1));
    $limit = 30;
    $ofset = ($sayfa - 1) * $limit;

    $kosul = ' WHERE 1=1';
    $p = [];
    if ($filt === 'okunmamis') { $kosul .= ' AND okundu = 0'; }
    elseif ($filt === 'okundu') { $kosul .= ' AND okundu = 1'; }
    if ($q !== '') {
        $kosul .= ' AND (ad_soyad LIKE :q1 OR eposta LIKE :q2 OR konu LIKE :q3 OR mesaj LIKE :q4)';
        $p['q1'] = '%' . $q . '%';
        $p['q2'] = '%' . $q . '%';
        $p['q3'] = '%' . $q . '%';
        $p['q4'] = '%' . $q . '%';
    }

    $toplam = (int) db_deger('SELECT COUNT(*) FROM iletisim_mesajlari' . $kosul, $p);
    $liste = db_liste('SELECT * FROM iletisim_mesajlari' . $kosul . ' ORDER BY id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$ofset, $p);
    $sayfaSayisi = (int) ceil($toplam / $limit);
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>✉ Iletisim Mesajlari <small style="color:#64748b; font-weight:normal;">(<?= $toplam ?>)</small></h2>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="mesajlar">
          <input type="text" name="q" placeholder="Ara..." value="<?= e($q) ?>">
          <select name="filt">
            <option value="">Tumu</option>
            <option value="okunmamis" <?= $filt === 'okunmamis' ? 'selected' : '' ?>>Okunmamis</option>
            <option value="okundu" <?= $filt === 'okundu' ? 'selected' : '' ?>>Okunmus</option>
          </select>
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>

        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Mesaj bulunamadi.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Ad Soyad</th><th>E-posta</th><th>Konu</th><th>Ozet</th><th>Tarih</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($liste as $m): ?>
            <tr style="<?= !$m['okundu'] ? 'background:#eff6ff; font-weight:500;' : '' ?>">
              <td>#<?= (int)$m['id'] ?><?php if (!$m['okundu']): ?> <span class="yp-rozet yp-rozet-yeni" style="font-size:0.68rem;">YENI</span><?php endif; ?></td>
              <td><?= e($m['ad_soyad']) ?></td>
              <td><small><?= e($m['eposta']) ?></small></td>
              <td><?= e($m['konu'] ?: '-') ?></td>
              <td><small><?= e(kisalt($m['mesaj'], 80)) ?></small></td>
              <td><small><?= e(tarih($m['olusturma'], 'd.m.Y H:i')) ?></small></td>
              <td><a href="?is=mesaj&id=<?= (int)$m['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Ac</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($sayfaSayisi > 1): ?>
          <div class="yp-sayfalama">
            <?php for ($i = 1; $i <= $sayfaSayisi; $i++): ?>
              <a href="?is=mesajlar&s=<?= $i ?>&filt=<?= e($filt) ?>&q=<?= e($q) ?>" class="<?= $i === $sayfa ? 'aktif' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_mesaj_detay(): void {
    $id = (int)($_GET['id'] ?? 0);
    $m = db_satir('SELECT * FROM iletisim_mesajlari WHERE id = :id', ['id' => $id]);
    if (!$m) { flash_ekle('hata', 'Mesaj bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=mesajlar'); }

    // Otomatik okundu isaretle
    if (!$m['okundu']) {
        db_guncelle('iletisim_mesajlari', ['okundu' => 1], 'id = :id', ['id' => $id]);
        $m['okundu'] = 1;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['is_alt'] ?? '') === 'okunmamis') {
        if (csrf_dogrula($_POST['csrf'] ?? null)) {
            db_guncelle('iletisim_mesajlari', ['okundu' => 0], 'id = :id', ['id' => $id]);
            flash_ekle('ok', 'Mesaj okunmamis olarak isaretlendi.');
            yonlendir(SITE_URL . '/yonetim.php?is=mesajlar');
        }
    }

    $mailto = 'mailto:' . $m['eposta'] . '?subject=' . rawurlencode('RE: ' . ($m['konu'] ?: 'Mesajiniz') . ' - TeknikLED');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>✉ Mesaj #<?= (int)$m['id'] ?></h2>
        <div style="display:flex; gap:8px;">
          <a href="<?= e($mailto) ?>" class="yp-btn yp-btn-kucuk">✉ Yanitla</a>
          <a href="?is=mesajlar" class="yp-btn yp-btn-kucuk yp-btn-anahat">← Listeye Don</a>
        </div>
      </div>
      <div class="yp-panel-gvd">
        <table class="yp-tablo" style="margin-bottom:20px;">
          <tr><th style="width:30%">Ad Soyad</th><td><strong><?= e($m['ad_soyad']) ?></strong></td></tr>
          <tr><th>E-posta</th><td><a href="mailto:<?= e($m['eposta']) ?>"><?= e($m['eposta']) ?></a></td></tr>
          <tr><th>Telefon</th><td><?= $m['telefon'] ? '<a href="tel:' . e(preg_replace('/\s+/', '', $m['telefon'])) . '">' . e($m['telefon']) . '</a>' : '-' ?></td></tr>
          <tr><th>Konu</th><td><?= e($m['konu'] ?: '-') ?></td></tr>
          <tr><th>Dil / IP</th><td><?= e(strtoupper($m['dil'])) ?> / <?= e($m['ip'] ?? '-') ?></td></tr>
          <tr><th>Tarih</th><td><?= e(tarih($m['olusturma'], 'd.m.Y H:i:s')) ?></td></tr>
        </table>

        <h3 style="font-size:1rem; margin:20px 0 10px;">💬 Mesaj Icerigi</h3>
        <div style="background:#f8fafc; padding:18px; border-radius:8px; border-left:3px solid #3182CE; white-space:pre-wrap; line-height:1.7;"><?= e($m['mesaj']) ?></div>

        <div style="margin-top:20px; display:flex; gap:8px; flex-wrap:wrap;">
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="is_alt" value="okunmamis">
            <button class="yp-btn yp-btn-anahat">Okunmamis Olarak Isaretle</button>
          </form>
          <form method="POST" action="?is=mesaj-sil&id=<?= (int)$m['id'] ?>" data-onay="Bu mesaji silmek istediginizden emin misiniz?" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="yp-btn yp-btn-sil">🗑 Sil</button>
          </form>
        </div>
      </div>
    </div>
    <?php
}

function _yp_mesaj_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=mesajlar'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('iletisim_mesajlari', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Mesaj silindi.');
        log_yaz('mesaj_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=mesajlar');
}

// =================================================================
// 8) SAYFALAR (CMS)
// =================================================================
function _yp_sayfalar(): void {
    $liste = db_liste('SELECT * FROM sayfalar ORDER BY sira, id');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>📄 Sayfalar (CMS)</h2>
        <a href="?is=sayfa" class="yp-btn">+ Yeni Sayfa</a>
      </div>
      <div class="yp-panel-gvd">
        <?php if (empty($liste)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Sayfa yok.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Baslik</th><th>Slug</th><th>Menu</th><th>Footer</th><th>Durum</th><th>Islem</th></tr></thead>
          <tbody>
          <?php foreach ($liste as $s): ?>
            <tr>
              <td><?= (int)$s['id'] ?></td>
              <td><strong><?= e($s['baslik_tr']) ?></strong></td>
              <td><code><?= e($s['slug']) ?></code></td>
              <td><?= $s['menude'] ? '<span class="yp-rozet yp-rozet-aktif">Evet</span>' : '-' ?></td>
              <td><?= $s['footer'] ? '<span class="yp-rozet yp-rozet-aktif">Evet</span>' : '-' ?></td>
              <td><span class="yp-rozet <?= $s['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $s['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="<?= e(SITE_URL . '/sayfa/' . $s['slug']) ?>" target="_blank" class="yp-btn yp-btn-kucuk yp-btn-anahat">🔗 Ac</a>
                <a href="?is=sayfa&id=<?= (int)$s['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <form method="POST" action="?is=sayfa-sil&id=<?= (int)$s['id'] ?>" data-onay="Sayfayi silmek istediginizden emin misiniz?" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

function _yp_sayfa_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $s = $id ? db_satir('SELECT * FROM sayfalar WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$s) { flash_ekle('hata', 'Sayfa bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=sayfalar'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'slug'       => slug($_POST['slug'] ?: $_POST['baslik_tr']),
                'baslik_tr'  => trim($_POST['baslik_tr'] ?? ''),
                'baslik_en'  => trim($_POST['baslik_en'] ?? '') ?: null,
                'baslik_ar'  => trim($_POST['baslik_ar'] ?? '') ?: null,
                'icerik_tr'  => $_POST['icerik_tr'] ?? null,
                'icerik_en'  => $_POST['icerik_en'] ?? null,
                'icerik_ar'  => $_POST['icerik_ar'] ?? null,
                'menude'     => !empty($_POST['menude']) ? 1 : 0,
                'footer'     => !empty($_POST['footer']) ? 1 : 0,
                'sira'       => (int)($_POST['sira'] ?? 0),
                'aktif'      => !empty($_POST['aktif']) ? 1 : 0,
            ];
            try {
                if ($id) { db_guncelle('sayfalar', $veri, 'id = :id', ['id' => $id]); flash_ekle('ok', 'Sayfa guncellendi.'); log_yaz('sayfa_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                else { $id = db_ekle('sayfalar', $veri); flash_ekle('ok', 'Sayfa eklendi.'); log_yaz('sayfa_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                yonlendir(SITE_URL . '/yonetim.php?is=sayfa&id=' . $id);
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
        $s = array_merge($s ?: [], $_POST);
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2><?= $id ? 'Sayfayi Duzenle' : 'Yeni Sayfa' ?></h2>
        <a href="?is=sayfalar" class="yp-btn yp-btn-anahat yp-btn-kucuk">← Listeye Don</a>
      </div>
      <div class="yp-panel-gvd">
      <form method="POST" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="yp-diller" data-hedef="sf">
          <button type="button" data-dil="tr" class="aktif">🇹🇷 TR</button>
          <button type="button" data-dil="en">🇬🇧 EN</button>
          <button type="button" data-dil="ar">🇸🇦 AR</button>
        </div>

        <?php foreach (['tr','en','ar'] as $d): ?>
          <div class="yp-dil-panel <?= $d === 'tr' ? 'aktif' : '' ?>" data-dil-panel="sf" data-dil-kod="<?= $d ?>">
            <div class="yp-alan">
              <label>Baslik (<?= strtoupper($d) ?>) <?= $d === 'tr' ? '*' : '' ?></label>
              <input type="text" name="baslik_<?= $d ?>" <?= $d === 'tr' ? 'required data-slug-kaynak="slug"' : '' ?> value="<?= e($s['baslik_' . $d] ?? '') ?>" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>>
            </div>
            <div class="yp-alan">
              <label>Icerik (<?= strtoupper($d) ?>) <small>(HTML destekli)</small></label>
              <textarea name="icerik_<?= $d ?>" rows="14" <?= $d === 'ar' ? 'dir="rtl"' : '' ?>><?= e($s['icerik_' . $d] ?? '') ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="yp-satir">
          <div class="yp-alan"><label>Slug</label><input type="text" id="slug" name="slug" value="<?= e($s['slug'] ?? '') ?>"></div>
          <div class="yp-alan"><label>Sira</label><input type="number" name="sira" value="<?= e((string)($s['sira'] ?? 0)) ?>"></div>
        </div>

        <div class="yp-satir-3">
          <div class="yp-alan-onay">
            <input type="checkbox" name="aktif" id="aktif" <?= !isset($s['aktif']) || $s['aktif'] ? 'checked' : '' ?>>
            <label for="aktif">Aktif</label>
          </div>
          <div class="yp-alan-onay">
            <input type="checkbox" name="menude" id="menude" <?= !empty($s['menude']) ? 'checked' : '' ?>>
            <label for="menude">Ust Menude Goster</label>
          </div>
          <div class="yp-alan-onay">
            <input type="checkbox" name="footer" id="footer" <?= !empty($s['footer']) ? 'checked' : '' ?>>
            <label for="footer">Footer'da Goster</label>
          </div>
        </div>

        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>
    <?php
}

function _yp_sayfa_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=sayfalar'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('sayfalar', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Sayfa silindi.');
        log_yaz('sayfa_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=sayfalar');
}

// =================================================================
// 10) COZUMLER (v0.3.1)
// =================================================================
function _yp_cozumler(): void {
    $cozumler = db_liste('SELECT * FROM cozumler ORDER BY sira, id');
    ?>
    <div class="yp-bas">
      <h1>Cozumler</h1>
      <a href="?is=cozum" class="yp-btn">+ Yeni Cozum</a>
    </div>
    <div class="yp-tablo-sarmal">
      <table class="yp-tablo">
        <thead><tr><th>Ad</th><th>Slug</th><th>Sira</th><th>Vitrin</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php if (!$cozumler): ?>
          <tr><td colspan="6" style="padding:30px; text-align:center; color:#64748b;">Henuz cozum yok.</td></tr>
        <?php else: foreach ($cozumler as $c): ?>
          <tr>
            <td><?= e($c['ikon']) ?> <strong><?= e($c['ad_tr']) ?></strong></td>
            <td style="font-family:monospace; font-size:.85em; color:#64748b;"><?= e($c['slug']) ?></td>
            <td><?= (int)$c['sira'] ?></td>
            <td><?= $c['vitrin'] ? 'Evet' : '—' ?></td>
            <td><?= $c['aktif'] ? '<span class="yp-rozet yp-rozet-ok">Aktif</span>' : '<span class="yp-rozet yp-rozet-uyari">Pasif</span>' ?></td>
            <td>
              <a href="?is=cozum&id=<?= (int)$c['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
              <form method="POST" action="?is=cozum-sil&id=<?= (int)$c['id'] ?>" data-onay="Bu cozumu silmek istediginize emin misiniz?" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="yp-btn yp-btn-kucuk yp-btn-kirmizi">Sil</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php
}

function _yp_cozum_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $c = $id ? db_satir('SELECT * FROM cozumler WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$c) { flash_ekle('hata', 'Cozum bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=cozumler'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'slug'           => slug_temizle($_POST['slug'] ?? $_POST['ad_tr'] ?? ''),
                'ad_tr'          => trim((string)($_POST['ad_tr'] ?? '')),
                'ad_en'          => trim((string)($_POST['ad_en'] ?? '')),
                'ad_ar'          => trim((string)($_POST['ad_ar'] ?? '')),
                'ozet_tr'        => trim((string)($_POST['ozet_tr'] ?? '')),
                'ozet_en'        => trim((string)($_POST['ozet_en'] ?? '')),
                'ozet_ar'        => trim((string)($_POST['ozet_ar'] ?? '')),
                'aciklama_tr'    => (string)($_POST['aciklama_tr'] ?? ''),
                'aciklama_en'    => (string)($_POST['aciklama_en'] ?? ''),
                'aciklama_ar'    => (string)($_POST['aciklama_ar'] ?? ''),
                'ikon'           => trim((string)($_POST['ikon'] ?? '')),
                'ilgili_urunler' => trim((string)($_POST['ilgili_urunler'] ?? '')),
                'vitrin'         => !empty($_POST['vitrin']) ? 1 : 0,
                'sira'           => (int)($_POST['sira'] ?? 0),
                'aktif'          => !empty($_POST['aktif']) ? 1 : 0,
            ];
            if (!empty($_FILES['gorsel']['name'])) {
                $r = dosya_yukle($_FILES['gorsel'], 'cozumler');
                if ($r['basari']) $veri['gorsel'] = $r['yol'];
                else flash_ekle('hata', 'Gorsel: ' . $r['hata']);
            }
            try {
                if ($id) {
                    db_guncelle('cozumler', $veri, 'id = :id', ['id' => $id]);
                    flash_ekle('ok', 'Cozum guncellendi.');
                    log_yaz('cozum_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                } else {
                    $yeniId = db_ekle('cozumler', $veri);
                    flash_ekle('ok', 'Cozum eklendi.');
                    log_yaz('cozum_ekle', 'ID: ' . $yeniId, Auth::mevcutAdmin()['id']);
                }
                yonlendir(SITE_URL . '/yonetim.php?is=cozumler');
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
    }
    ?>
    <div class="yp-bas"><h1><?= $id ? 'Cozum Duzenle' : 'Yeni Cozum' ?></h1></div>
    <form method="POST" enctype="multipart/form-data" class="yp-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="yp-form-izgara">
        <div>
          <label>Ad (TR) *</label>
          <input type="text" name="ad_tr" value="<?= e($c['ad_tr'] ?? '') ?>" required>
        </div>
        <div>
          <label>Slug <small>(bos birak, otomatik olustur)</small></label>
          <input type="text" name="slug" value="<?= e($c['slug'] ?? '') ?>">
        </div>
        <div>
          <label>Ad (EN)</label>
          <input type="text" name="ad_en" value="<?= e($c['ad_en'] ?? '') ?>">
        </div>
        <div>
          <label>Ad (AR)</label>
          <input type="text" name="ad_ar" value="<?= e($c['ad_ar'] ?? '') ?>">
        </div>
        <div>
          <label>Ikon <small>(emoji)</small></label>
          <input type="text" name="ikon" value="<?= e($c['ikon'] ?? '') ?>" placeholder="💼">
        </div>
        <div>
          <label>Sira</label>
          <input type="number" name="sira" value="<?= e((string)($c['sira'] ?? 0)) ?>">
        </div>
      </div>
      <label>Ozet (TR)</label>
      <textarea name="ozet_tr" rows="2"><?= e($c['ozet_tr'] ?? '') ?></textarea>
      <label>Ozet (EN)</label>
      <textarea name="ozet_en" rows="2"><?= e($c['ozet_en'] ?? '') ?></textarea>
      <label>Ozet (AR)</label>
      <textarea name="ozet_ar" rows="2" dir="rtl"><?= e($c['ozet_ar'] ?? '') ?></textarea>
      <label>Aciklama (TR) <small>(HTML destekli)</small></label>
      <textarea name="aciklama_tr" rows="10"><?= e($c['aciklama_tr'] ?? '') ?></textarea>
      <label>Aciklama (EN)</label>
      <textarea name="aciklama_en" rows="6"><?= e($c['aciklama_en'] ?? '') ?></textarea>
      <label>Aciklama (AR)</label>
      <textarea name="aciklama_ar" rows="6" dir="rtl"><?= e($c['aciklama_ar'] ?? '') ?></textarea>
      <label>Ilgili urun slug'lari <small>(virgulle ayrilmis, ornek: led-masa-96x192,led-kursu-p186)</small></label>
      <input type="text" name="ilgili_urunler" value="<?= e($c['ilgili_urunler'] ?? '') ?>">
      <label>Gorsel <?php if (!empty($c['gorsel'])): ?><small>(mevcut: <?= e($c['gorsel']) ?>)</small><?php endif; ?></label>
      <input type="file" name="gorsel" accept="image/*">
      <div class="yp-form-checkbox-satir">
        <label><input type="checkbox" name="vitrin" value="1" <?= !empty($c['vitrin']) ? 'checked' : '' ?>> Ana sayfa vitrin</label>
        <label><input type="checkbox" name="aktif" value="1" <?= !isset($c['aktif']) || !empty($c['aktif']) ? 'checked' : '' ?>> Aktif</label>
      </div>
      <div class="yp-form-btn">
        <button type="submit" class="yp-btn">Kaydet</button>
        <a href="?is=cozumler" class="yp-btn yp-btn-anahat">Iptal</a>
      </div>
    </form>
    <?php
}

function _yp_cozum_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=cozumler'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('cozumler', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Cozum silindi.');
        log_yaz('cozum_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=cozumler');
}

// =================================================================
// 11) ICERIKLER (blog + haber, v0.3.1)
// =================================================================
function _yp_icerikler(): void {
    $filtre_tip = $_GET['tip'] ?? '';
    $sql = 'SELECT * FROM icerikler WHERE 1=1';
    $params = [];
    if ($filtre_tip === 'blog' || $filtre_tip === 'haber') {
        $sql .= ' AND tip = :t';
        $params['t'] = $filtre_tip;
    }
    $sql .= ' ORDER BY yayin_tarihi DESC, id DESC';
    $items = db_liste($sql, $params);
    ?>
    <div class="yp-bas">
      <h1>Blog ve Haberler</h1>
      <a href="?is=icerik&tip=blog" class="yp-btn">+ Yeni Blog</a>
      <a href="?is=icerik&tip=haber" class="yp-btn">+ Yeni Haber</a>
    </div>
    <div class="yp-filtre" style="margin-bottom:16px;">
      <a href="?is=icerikler" class="yp-btn yp-btn-kucuk <?= $filtre_tip === '' ? '' : 'yp-btn-anahat' ?>">Hepsi</a>
      <a href="?is=icerikler&tip=blog" class="yp-btn yp-btn-kucuk <?= $filtre_tip === 'blog' ? '' : 'yp-btn-anahat' ?>">Blog</a>
      <a href="?is=icerikler&tip=haber" class="yp-btn yp-btn-kucuk <?= $filtre_tip === 'haber' ? '' : 'yp-btn-anahat' ?>">Haber</a>
    </div>
    <div class="yp-tablo-sarmal">
      <table class="yp-tablo">
        <thead><tr><th>Tip</th><th>Baslik</th><th>Yazar</th><th>Yayin</th><th>Goruntu</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php if (!$items): ?>
          <tr><td colspan="7" style="padding:30px; text-align:center; color:#64748b;">Henuz icerik yok.</td></tr>
        <?php else: foreach ($items as $it): ?>
          <tr>
            <td><span class="yp-rozet <?= $it['tip'] === 'blog' ? 'yp-rozet-info' : 'yp-rozet-uyari' ?>"><?= strtoupper($it['tip']) ?></span></td>
            <td><strong><?= e($it['baslik_tr']) ?></strong><br><small style="color:#64748b; font-family:monospace;"><?= e($it['slug']) ?></small></td>
            <td><?= e($it['yazar'] ?? '—') ?></td>
            <td><?= !empty($it['yayin_tarihi']) ? date('d.m.Y H:i', strtotime($it['yayin_tarihi'])) : '—' ?></td>
            <td><?= (int)$it['goruntulenme'] ?></td>
            <td><?= $it['aktif'] ? '<span class="yp-rozet yp-rozet-ok">Aktif</span>' : '<span class="yp-rozet yp-rozet-uyari">Pasif</span>' ?></td>
            <td>
              <a href="?is=icerik&id=<?= (int)$it['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
              <form method="POST" action="?is=icerik-sil&id=<?= (int)$it['id'] ?>" data-onay="Silinsin mi?" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="yp-btn yp-btn-kucuk yp-btn-kirmizi">Sil</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php
}

function _yp_icerik_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $it = $id ? db_satir('SELECT * FROM icerikler WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$it) { flash_ekle('hata', 'Icerik bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=icerikler'); }
    $tip_default = $_GET['tip'] ?? $it['tip'] ?? 'blog';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $tip = in_array($_POST['tip'] ?? '', ['blog','haber'], true) ? $_POST['tip'] : 'blog';
            $veri = [
                'tip'          => $tip,
                'slug'         => slug_temizle($_POST['slug'] ?? $_POST['baslik_tr'] ?? ''),
                'baslik_tr'    => trim((string)($_POST['baslik_tr'] ?? '')),
                'baslik_en'    => trim((string)($_POST['baslik_en'] ?? '')),
                'baslik_ar'    => trim((string)($_POST['baslik_ar'] ?? '')),
                'ozet_tr'      => trim((string)($_POST['ozet_tr'] ?? '')),
                'ozet_en'      => trim((string)($_POST['ozet_en'] ?? '')),
                'ozet_ar'      => trim((string)($_POST['ozet_ar'] ?? '')),
                'icerik_tr'    => (string)($_POST['icerik_tr'] ?? ''),
                'icerik_en'    => (string)($_POST['icerik_en'] ?? ''),
                'icerik_ar'    => (string)($_POST['icerik_ar'] ?? ''),
                'yazar'        => trim((string)($_POST['yazar'] ?? '')),
                'etiketler'    => trim((string)($_POST['etiketler'] ?? '')),
                'yayin_tarihi' => !empty($_POST['yayin_tarihi']) ? $_POST['yayin_tarihi'] : date('Y-m-d H:i:s'),
                'aktif'        => !empty($_POST['aktif']) ? 1 : 0,
            ];
            if (!empty($_FILES['kapak']['name'])) {
                $r = dosya_yukle($_FILES['kapak'], 'icerikler');
                if ($r['basari']) $veri['kapak'] = $r['yol'];
                else flash_ekle('hata', 'Kapak: ' . $r['hata']);
            }
            try {
                if ($id) {
                    db_guncelle('icerikler', $veri, 'id = :id', ['id' => $id]);
                    flash_ekle('ok', 'Icerik guncellendi.');
                    log_yaz('icerik_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                } else {
                    $yeniId = db_ekle('icerikler', $veri);
                    flash_ekle('ok', 'Icerik eklendi.');
                    log_yaz('icerik_ekle', 'ID: ' . $yeniId, Auth::mevcutAdmin()['id']);
                }
                yonlendir(SITE_URL . '/yonetim.php?is=icerikler');
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
    }
    ?>
    <div class="yp-bas"><h1><?= $id ? 'Icerik Duzenle' : 'Yeni Icerik' ?></h1></div>
    <form method="POST" enctype="multipart/form-data" class="yp-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="yp-form-izgara">
        <div>
          <label>Tip *</label>
          <select name="tip" required>
            <option value="blog" <?= $tip_default === 'blog' ? 'selected' : '' ?>>Blog</option>
            <option value="haber" <?= $tip_default === 'haber' ? 'selected' : '' ?>>Haber</option>
          </select>
        </div>
        <div>
          <label>Yayin Tarihi</label>
          <input type="datetime-local" name="yayin_tarihi" value="<?= e(!empty($it['yayin_tarihi']) ? date('Y-m-d\TH:i', strtotime($it['yayin_tarihi'])) : date('Y-m-d\TH:i')) ?>">
        </div>
        <div style="grid-column: 1 / -1;">
          <label>Baslik (TR) *</label>
          <input type="text" name="baslik_tr" value="<?= e($it['baslik_tr'] ?? '') ?>" required>
        </div>
        <div>
          <label>Slug</label>
          <input type="text" name="slug" value="<?= e($it['slug'] ?? '') ?>">
        </div>
        <div>
          <label>Yazar</label>
          <input type="text" name="yazar" value="<?= e($it['yazar'] ?? '') ?>">
        </div>
        <div>
          <label>Baslik (EN)</label>
          <input type="text" name="baslik_en" value="<?= e($it['baslik_en'] ?? '') ?>">
        </div>
        <div>
          <label>Baslik (AR)</label>
          <input type="text" name="baslik_ar" value="<?= e($it['baslik_ar'] ?? '') ?>" dir="rtl">
        </div>
      </div>
      <label>Ozet (TR)</label>
      <textarea name="ozet_tr" rows="2"><?= e($it['ozet_tr'] ?? '') ?></textarea>
      <label>Ozet (EN)</label>
      <textarea name="ozet_en" rows="2"><?= e($it['ozet_en'] ?? '') ?></textarea>
      <label>Ozet (AR)</label>
      <textarea name="ozet_ar" rows="2" dir="rtl"><?= e($it['ozet_ar'] ?? '') ?></textarea>
      <label>Icerik (TR) <small>(HTML destekli)</small></label>
      <textarea name="icerik_tr" rows="15"><?= e($it['icerik_tr'] ?? '') ?></textarea>
      <label>Icerik (EN)</label>
      <textarea name="icerik_en" rows="8"><?= e($it['icerik_en'] ?? '') ?></textarea>
      <label>Icerik (AR)</label>
      <textarea name="icerik_ar" rows="8" dir="rtl"><?= e($it['icerik_ar'] ?? '') ?></textarea>
      <label>Etiketler <small>(virgulle ayrilmis)</small></label>
      <input type="text" name="etiketler" value="<?= e($it['etiketler'] ?? '') ?>">
      <label>Kapak Gorseli <?php if (!empty($it['kapak'])): ?><small>(mevcut: <?= e($it['kapak']) ?>)</small><?php endif; ?></label>
      <input type="file" name="kapak" accept="image/*">
      <div class="yp-form-checkbox-satir">
        <label><input type="checkbox" name="aktif" value="1" <?= !isset($it['aktif']) || !empty($it['aktif']) ? 'checked' : '' ?>> Aktif</label>
      </div>
      <div class="yp-form-btn">
        <button type="submit" class="yp-btn">Kaydet</button>
        <a href="?is=icerikler" class="yp-btn yp-btn-anahat">Iptal</a>
      </div>
    </form>
    <?php
}

function _yp_icerik_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=icerikler'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('icerikler', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Icerik silindi.');
        log_yaz('icerik_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=icerikler');
}

// =================================================================
// 12) MARKALAR (v0.3.1)
// =================================================================
function _yp_markalar(): void {
    $markalar = db_liste('SELECT * FROM markalar ORDER BY sira, id');
    ?>
    <div class="yp-bas">
      <h1>Markalar</h1>
      <a href="?is=marka" class="yp-btn">+ Yeni Marka</a>
    </div>
    <div class="yp-tablo-sarmal">
      <table class="yp-tablo">
        <thead><tr><th>Logo</th><th>Ad</th><th>Web</th><th>Sira</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php if (!$markalar): ?>
          <tr><td colspan="6" style="padding:30px; text-align:center; color:#64748b;">Henuz marka yok.</td></tr>
        <?php else: foreach ($markalar as $m): ?>
          <tr>
            <td>
              <?php if (!empty($m['logo'])): ?>
                <img src="<?= e(upload($m['logo'])) ?>" alt="<?= e($m['ad']) ?>" style="max-height:32px; max-width:80px;">
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><strong><?= e($m['ad']) ?></strong></td>
            <td>
              <?php if (!empty($m['web_url'])): ?>
                <a href="<?= e($m['web_url']) ?>" target="_blank" rel="noopener" style="font-size:.85em;"><?= e(parse_url($m['web_url'], PHP_URL_HOST) ?: $m['web_url']) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= (int)$m['sira'] ?></td>
            <td><?= $m['aktif'] ? '<span class="yp-rozet yp-rozet-ok">Aktif</span>' : '<span class="yp-rozet yp-rozet-uyari">Pasif</span>' ?></td>
            <td>
              <a href="?is=marka&id=<?= (int)$m['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
              <form method="POST" action="?is=marka-sil&id=<?= (int)$m['id'] ?>" data-onay="Silinsin mi?" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="yp-btn yp-btn-kucuk yp-btn-kirmizi">Sil</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php
}

function _yp_marka_form(): void {
    $id = (int)($_GET['id'] ?? 0);
    $m = $id ? db_satir('SELECT * FROM markalar WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$m) { flash_ekle('hata', 'Marka bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=markalar'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'ad'       => trim((string)($_POST['ad'] ?? '')),
                'web_url'  => trim((string)($_POST['web_url'] ?? '')),
                'aciklama' => trim((string)($_POST['aciklama'] ?? '')),
                'sira'     => (int)($_POST['sira'] ?? 0),
                'aktif'    => !empty($_POST['aktif']) ? 1 : 0,
            ];
            if (!empty($_FILES['logo']['name'])) {
                $r = dosya_yukle($_FILES['logo'], 'markalar');
                if ($r['basari']) $veri['logo'] = $r['yol'];
                else flash_ekle('hata', 'Logo: ' . $r['hata']);
            } elseif (!$id) {
                // Yeni eklenirken logo sart - flash yaz ve forma geri don
                flash_ekle('hata', 'Logo zorunludur.');
                yonlendir(SITE_URL . '/yonetim.php?is=marka');
            }
            try {
                if ($id) {
                    db_guncelle('markalar', $veri, 'id = :id', ['id' => $id]);
                    flash_ekle('ok', 'Marka guncellendi.');
                    log_yaz('marka_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
                } else {
                    $yeniId = db_ekle('markalar', $veri);
                    flash_ekle('ok', 'Marka eklendi.');
                    log_yaz('marka_ekle', 'ID: ' . $yeniId, Auth::mevcutAdmin()['id']);
                }
                yonlendir(SITE_URL . '/yonetim.php?is=markalar');
            } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
        }
    }
    ?>
    <div class="yp-bas"><h1><?= $id ? 'Marka Duzenle' : 'Yeni Marka' ?></h1></div>
    <form method="POST" enctype="multipart/form-data" class="yp-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="yp-form-izgara">
        <div>
          <label>Ad *</label>
          <input type="text" name="ad" value="<?= e($m['ad'] ?? '') ?>" required>
        </div>
        <div>
          <label>Web URL</label>
          <input type="url" name="web_url" value="<?= e($m['web_url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div>
          <label>Sira</label>
          <input type="number" name="sira" value="<?= e((string)($m['sira'] ?? 0)) ?>">
        </div>
        <div>
          <label>Logo <?php if (!empty($m['logo'])): ?><small>(mevcut: <?= e($m['logo']) ?>)</small><?php endif; ?></label>
          <input type="file" name="logo" accept="image/*" <?= $id ? '' : 'required' ?>>
        </div>
      </div>
      <label>Aciklama <small>(opsiyonel)</small></label>
      <input type="text" name="aciklama" value="<?= e($m['aciklama'] ?? '') ?>">
      <div class="yp-form-checkbox-satir">
        <label><input type="checkbox" name="aktif" value="1" <?= !isset($m['aktif']) || !empty($m['aktif']) ? 'checked' : '' ?>> Aktif</label>
      </div>
      <div class="yp-form-btn">
        <button type="submit" class="yp-btn">Kaydet</button>
        <a href="?is=markalar" class="yp-btn yp-btn-anahat">Iptal</a>
      </div>
    </form>
    <?php
}

function _yp_marka_sil(): void {
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=markalar'); }
    $id = (int)($_GET['id'] ?? 0);
    try {
        db_sil('markalar', 'id = :id', ['id' => $id]);
        flash_ekle('ok', 'Marka silindi.');
        log_yaz('marka_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
    } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    yonlendir(SITE_URL . '/yonetim.php?is=markalar');
}

// =================================================================
// 9) AYARLAR
// =================================================================
function _yp_ayarlar(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $kayit = $_POST['ayar'] ?? [];
            $bool_anahtarlar = db_liste("SELECT anahtar FROM ayarlar WHERE tip = 'bool'");
            $bool_keys = array_column($bool_anahtarlar, 'anahtar');

            $degisenler = 0;
            foreach ($kayit as $anah => $deger) {
                $anah = (string)$anah;
                // Gecerli anahtar kontrolu
                $varMi = db_deger('SELECT 1 FROM ayarlar WHERE anahtar = :a', ['a' => $anah]);
                if (!$varMi) continue;
                $deger = is_array($deger) ? json_encode($deger, JSON_UNESCAPED_UNICODE) : (string)$deger;
                db_guncelle('ayarlar', ['deger' => $deger], 'anahtar = :a', ['a' => $anah]);
                $degisenler++;
            }
            // Bool ayarlar submit'te icerilmez eger checkbox isaretli degilse
            foreach ($bool_keys as $bk) {
                if (!isset($kayit[$bk])) {
                    db_guncelle('ayarlar', ['deger' => '0'], 'anahtar = :a', ['a' => $bk]);
                }
            }
            flash_ekle('ok', "$degisenler ayar guncellendi.");
            log_yaz('ayarlar_guncelle', $degisenler . ' ayar', Auth::mevcutAdmin()['id']);
            yonlendir(SITE_URL . '/yonetim.php?is=ayarlar');
        }
    }

    $tumu = db_liste('SELECT * FROM ayarlar ORDER BY grup, anahtar');
    $gruplar = [];
    foreach ($tumu as $a) { $gruplar[$a['grup']][] = $a; }

    $grupAdlari = [
        'genel'    => ['🏢 Genel', '#3182CE'],
        'iletisim' => ['📞 Iletisim', '#38A169'],
        'sosyal'   => ['🌐 Sosyal Medya', '#805AD5'],
        'anasayfa' => ['🏠 Anasayfa', '#E53E3E'],
        'sistem'   => ['⚙ Sistem', '#64748b'],
    ];
    ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <?php foreach ($gruplar as $grup => $ayarlar): ?>
        <div class="yp-panel">
          <div class="yp-panel-bas">
            <h2 style="color:<?= e($grupAdlari[$grup][1] ?? '#1e293b') ?>"><?= e($grupAdlari[$grup][0] ?? ucfirst($grup)) ?></h2>
          </div>
          <div class="yp-panel-gvd">
            <?php foreach ($ayarlar as $a): ?>
              <div class="yp-alan">
                <label>
                  <?= e($a['aciklama'] ?: $a['anahtar']) ?>
                  <small style="color:#64748b; font-weight:normal;"> (<?= e($a['anahtar']) ?>)</small>
                </label>
                <?php switch ($a['tip']):
                  case 'textarea':
                    $rtlAttr = str_ends_with($a['anahtar'], '_ar') ? ' dir="rtl"' : '';
                ?>
                  <textarea name="ayar[<?= e($a['anahtar']) ?>]" rows="3"<?= $rtlAttr ?>><?= e($a['deger'] ?? '') ?></textarea>
                <?php break;
                  case 'html':
                ?>
                  <textarea name="ayar[<?= e($a['anahtar']) ?>]" rows="5" style="font-family:monospace;"><?= e($a['deger'] ?? '') ?></textarea>
                <?php break;
                  case 'bool':
                ?>
                  <div class="yp-alan-onay" style="margin:0;">
                    <input type="checkbox" name="ayar[<?= e($a['anahtar']) ?>]" value="1" <?= !empty($a['deger']) && $a['deger'] !== '0' ? 'checked' : '' ?> id="a_<?= e($a['anahtar']) ?>">
                    <label for="a_<?= e($a['anahtar']) ?>">Aktif</label>
                  </div>
                <?php break;
                  case 'email':
                ?>
                  <input type="email" name="ayar[<?= e($a['anahtar']) ?>]" value="<?= e($a['deger'] ?? '') ?>">
                <?php break;
                  case 'url':
                ?>
                  <input type="url" name="ayar[<?= e($a['anahtar']) ?>]" value="<?= e($a['deger'] ?? '') ?>" placeholder="https://...">
                <?php break;
                  case 'number':
                ?>
                  <input type="number" name="ayar[<?= e($a['anahtar']) ?>]" value="<?= e($a['deger'] ?? '') ?>">
                <?php break;
                  case 'json':
                ?>
                  <textarea name="ayar[<?= e($a['anahtar']) ?>]" rows="4" style="font-family:monospace;"><?= e($a['deger'] ?? '') ?></textarea>
                <?php break;
                  default:
                    $rtlAttr2 = str_ends_with($a['anahtar'], '_ar') ? ' dir="rtl"' : '';
                ?>
                  <input type="text" name="ayar[<?= e($a['anahtar']) ?>]" value="<?= e($a['deger'] ?? '') ?>"<?= $rtlAttr2 ?>>
                <?php endswitch; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div style="margin:20px 0;">
        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Tum Ayarlari Kaydet</button>
      </div>
    </form>
    <?php
}

// =================================================================
// 10) YONETICILER (super rol)
// =================================================================
function _yp_yoneticiler(): void {
    if (!Auth::roldeMi('super')) { flash_ekle('hata', 'Bu bolume erisim yetkiniz yok.'); yonlendir(SITE_URL . '/yonetim.php?is=panel'); }
    $liste = db_liste('SELECT * FROM yoneticiler ORDER BY id');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>👥 Yoneticiler</h2>
        <a href="?is=yonetici" class="yp-btn">+ Yeni Yonetici</a>
      </div>
      <div class="yp-panel-gvd">
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Ad Soyad</th><th>Kullanici</th><th>E-posta</th><th>Rol</th><th>Son Giris</th><th>Durum</th><th>Islem</th></tr></thead>
          <tbody>
          <?php foreach ($liste as $y): ?>
            <tr>
              <td><?= (int)$y['id'] ?></td>
              <td><strong><?= e($y['ad_soyad']) ?></strong></td>
              <td><code><?= e($y['kullanici_adi']) ?></code></td>
              <td><small><?= e($y['eposta']) ?></small></td>
              <td><span class="yp-rozet <?= $y['rol'] === 'super' ? 'yp-rozet-inc' : 'yp-rozet-aktif' ?>"><?= e($y['rol']) ?></span></td>
              <td><small><?= $y['son_giris'] ? e(tarih($y['son_giris'], 'd.m.Y H:i')) : '-' ?><?php if ($y['son_ip']): ?><br><?= e($y['son_ip']) ?><?php endif; ?></small></td>
              <td><span class="yp-rozet <?= $y['aktif'] ? 'yp-rozet-aktif' : 'yp-rozet-pasif' ?>"><?= $y['aktif'] ? 'Aktif' : 'Pasif' ?></span></td>
              <td class="yp-tb-islem">
                <a href="?is=yonetici&id=<?= (int)$y['id'] ?>" class="yp-btn yp-btn-kucuk yp-btn-anahat">Duzenle</a>
                <?php if ((int)$y['id'] !== (int)Auth::mevcutAdmin()['id']): ?>
                  <form method="POST" action="?is=yonetici-sil&id=<?= (int)$y['id'] ?>" data-onay="Bu yoneticiyi silmek istediginizden emin misiniz?" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <button class="yp-btn yp-btn-kucuk yp-btn-sil">Sil</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

function _yp_yonetici_form(): void {
    if (!Auth::roldeMi('super')) { flash_ekle('hata', 'Yetkisiz erisim'); yonlendir(SITE_URL . '/yonetim.php?is=panel'); }
    $id = (int)($_GET['id'] ?? 0);
    $y = $id ? db_satir('SELECT * FROM yoneticiler WHERE id = :id', ['id' => $id]) : [];
    if ($id && !$y) { flash_ekle('hata', 'Yonetici bulunamadi'); yonlendir(SITE_URL . '/yonetim.php?is=yoneticiler'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'ad_soyad'      => trim($_POST['ad_soyad'] ?? ''),
                'kullanici_adi' => trim($_POST['kullanici_adi'] ?? ''),
                'eposta'        => trim($_POST['eposta'] ?? ''),
                'rol'           => in_array($_POST['rol'] ?? '', ['super','editor'], true) ? $_POST['rol'] : 'editor',
                'aktif'         => !empty($_POST['aktif']) ? 1 : 0,
            ];
            $sifre = (string)($_POST['sifre'] ?? '');

            if ($veri['ad_soyad'] === '' || $veri['kullanici_adi'] === '' || $veri['eposta'] === '') {
                flash_ekle('hata', 'Lutfen zorunlu alanlari doldurun.');
            } elseif (!filter_var($veri['eposta'], FILTER_VALIDATE_EMAIL)) {
                flash_ekle('hata', 'Gecersiz e-posta');
            } elseif (!$id && strlen($sifre) < 8) {
                flash_ekle('hata', 'Sifre en az 8 karakter olmali.');
            } elseif ($id && $sifre !== '' && strlen($sifre) < 8) {
                flash_ekle('hata', 'Sifre degistirilecekse en az 8 karakter olmali.');
            } else {
                if ($sifre !== '') $veri['sifre_hash'] = password_hash($sifre, PASSWORD_DEFAULT);
                try {
                    if ($id) { db_guncelle('yoneticiler', $veri, 'id = :id', ['id' => $id]); flash_ekle('ok', 'Yonetici guncellendi.'); log_yaz('yonetici_guncelle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                    else { $id = db_ekle('yoneticiler', $veri); flash_ekle('ok', 'Yonetici eklendi.'); log_yaz('yonetici_ekle', 'ID: ' . $id, Auth::mevcutAdmin()['id']); }
                    yonlendir(SITE_URL . '/yonetim.php?is=yoneticiler');
                } catch (Throwable $e) { flash_ekle('hata', 'Kayit hatasi: ' . $e->getMessage()); }
            }
            $y = array_merge($y ?: [], $_POST);
        }
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2><?= $id ? 'Yoneticiyi Duzenle' : 'Yeni Yonetici' ?></h2>
        <a href="?is=yoneticiler" class="yp-btn yp-btn-anahat yp-btn-kucuk">← Listeye Don</a>
      </div>
      <div class="yp-panel-gvd">
      <form method="POST" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="yp-alan">
          <label>Ad Soyad *</label>
          <input type="text" name="ad_soyad" required value="<?= e($y['ad_soyad'] ?? '') ?>">
        </div>
        <div class="yp-satir">
          <div class="yp-alan">
            <label>Kullanici Adi *</label>
            <input type="text" name="kullanici_adi" required value="<?= e($y['kullanici_adi'] ?? '') ?>">
          </div>
          <div class="yp-alan">
            <label>E-posta *</label>
            <input type="email" name="eposta" required value="<?= e($y['eposta'] ?? '') ?>">
          </div>
        </div>
        <div class="yp-satir">
          <div class="yp-alan">
            <label>Sifre <?= $id ? '<small>(degistirmek istemiyorsaniz bos birakin)</small>' : '*' ?></label>
            <input type="password" name="sifre" <?= $id ? '' : 'required minlength="8"' ?>>
          </div>
          <div class="yp-alan">
            <label>Rol *</label>
            <select name="rol">
              <option value="editor" <?= ($y['rol'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor (sinirli yetki)</option>
              <option value="super"  <?= ($y['rol'] ?? '') === 'super'  ? 'selected' : '' ?>>Super (tum yetki)</option>
            </select>
          </div>
        </div>
        <div class="yp-alan-onay">
          <input type="checkbox" name="aktif" id="aktif" <?= !isset($y['aktif']) || $y['aktif'] ? 'checked' : '' ?>>
          <label for="aktif">Aktif</label>
        </div>
        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>
    <?php
}

function _yp_yonetici_sil(): void {
    if (!Auth::roldeMi('super')) { flash_ekle('hata', 'Yetkisiz'); yonlendir(SITE_URL . '/yonetim.php?is=panel'); }
    if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=yoneticiler'); }
    $id = (int)($_GET['id'] ?? 0);
    if ($id === (int)Auth::mevcutAdmin()['id']) {
        flash_ekle('hata', 'Kendinizi silemezsiniz.');
    } else {
        try {
            db_sil('yoneticiler', 'id = :id', ['id' => $id]);
            flash_ekle('ok', 'Yonetici silindi.');
            log_yaz('yonetici_sil', 'ID: ' . $id, Auth::mevcutAdmin()['id']);
        } catch (Throwable $e) { flash_ekle('hata', 'Silme hatasi: ' . $e->getMessage()); }
    }
    yonlendir(SITE_URL . '/yonetim.php?is=yoneticiler');
}

// =================================================================
// 11) PROFIL
// =================================================================
function _yp_profil(): void {
    $adm = Auth::mevcutAdmin();
    $y = db_satir('SELECT * FROM yoneticiler WHERE id = :id', ['id' => $adm['id']]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); }
        else {
            $veri = [
                'ad_soyad' => trim($_POST['ad_soyad'] ?? ''),
                'eposta'   => trim($_POST['eposta'] ?? ''),
            ];
            $mevcutSifre = (string)($_POST['mevcut_sifre'] ?? '');
            $yeniSifre = (string)($_POST['yeni_sifre'] ?? '');
            $yeniSifre2 = (string)($_POST['yeni_sifre2'] ?? '');

            if ($veri['ad_soyad'] === '' || !filter_var($veri['eposta'], FILTER_VALIDATE_EMAIL)) {
                flash_ekle('hata', 'Gecerli ad soyad ve e-posta girin.');
            } elseif ($yeniSifre !== '') {
                if (!password_verify($mevcutSifre, $y['sifre_hash'])) {
                    flash_ekle('hata', 'Mevcut sifre hatali.');
                } elseif ($yeniSifre !== $yeniSifre2) {
                    flash_ekle('hata', 'Yeni sifreler uyusmuyor.');
                } elseif (strlen($yeniSifre) < 8) {
                    flash_ekle('hata', 'Yeni sifre en az 8 karakter olmali.');
                } else {
                    $veri['sifre_hash'] = password_hash($yeniSifre, PASSWORD_DEFAULT);
                    db_guncelle('yoneticiler', $veri, 'id = :id', ['id' => $adm['id']]);
                    $_SESSION['admin']['ad_soyad'] = $veri['ad_soyad'];
                    $_SESSION['admin']['eposta']   = $veri['eposta'];
                    flash_ekle('ok', 'Profil ve sifre guncellendi.');
                    log_yaz('profil_sifre_guncelle', '', $adm['id']);
                    yonlendir(SITE_URL . '/yonetim.php?is=profil');
                }
            } else {
                db_guncelle('yoneticiler', $veri, 'id = :id', ['id' => $adm['id']]);
                $_SESSION['admin']['ad_soyad'] = $veri['ad_soyad'];
                $_SESSION['admin']['eposta']   = $veri['eposta'];
                flash_ekle('ok', 'Profil guncellendi.');
                log_yaz('profil_guncelle', '', $adm['id']);
                yonlendir(SITE_URL . '/yonetim.php?is=profil');
            }
        }
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas"><h2>👤 Profil Ayarlarim</h2></div>
      <div class="yp-panel-gvd">
      <form method="POST" class="yp-form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="yp-alan"><label>Ad Soyad</label><input type="text" name="ad_soyad" required value="<?= e($y['ad_soyad']) ?>"></div>
        <div class="yp-satir">
          <div class="yp-alan"><label>Kullanici Adi (degistirilemez)</label><input type="text" value="<?= e($y['kullanici_adi']) ?>" disabled></div>
          <div class="yp-alan"><label>Rol</label><input type="text" value="<?= e($y['rol']) ?>" disabled></div>
        </div>
        <div class="yp-alan"><label>E-posta</label><input type="email" name="eposta" required value="<?= e($y['eposta']) ?>"></div>

        <h3 style="margin:24px 0 12px; padding-top:16px; border-top:1px solid #e2e8f0; font-size:1rem;">🔒 Sifre Degistir <small style="color:#64748b; font-weight:normal;">(bos birakabilirsiniz)</small></h3>
        <div class="yp-alan"><label>Mevcut Sifre</label><input type="password" name="mevcut_sifre" autocomplete="current-password"></div>
        <div class="yp-satir">
          <div class="yp-alan"><label>Yeni Sifre</label><input type="password" name="yeni_sifre" autocomplete="new-password" minlength="8"></div>
          <div class="yp-alan"><label>Yeni Sifre (tekrar)</label><input type="password" name="yeni_sifre2" autocomplete="new-password" minlength="8"></div>
        </div>

        <button type="submit" class="yp-btn yp-btn-buyuk">💾 Kaydet</button>
      </form>
      </div>
    </div>

    <div class="yp-panel">
      <div class="yp-panel-bas"><h2>ℹ Hesap Bilgileri</h2></div>
      <div class="yp-panel-gvd">
        <table class="yp-tablo">
          <tr><th style="width:30%">Son Giris</th><td><?= $y['son_giris'] ? e(tarih($y['son_giris'], 'd.m.Y H:i')) : '-' ?></td></tr>
          <tr><th>Son IP</th><td><code><?= e($y['son_ip'] ?: '-') ?></code></td></tr>
          <tr><th>Olusturma</th><td><?= e(tarih($y['olusturma'], 'd.m.Y H:i')) ?></td></tr>
        </table>
      </div>
    </div>
    <?php
}

// =================================================================
// 12) LOG
// =================================================================
function _yp_log(): void {
    if (!Auth::roldeMi('super')) { flash_ekle('hata', 'Yetkisiz'); yonlendir(SITE_URL . '/yonetim.php?is=panel'); }
    $sayfa = max(1, (int)($_GET['s'] ?? 1));
    $limit = 50;
    $ofset = ($sayfa - 1) * $limit;
    $olay = trim($_GET['olay'] ?? '');

    $kosul = ' WHERE 1=1'; $p = [];
    if ($olay !== '') { $kosul .= ' AND l.olay = :o'; $p['o'] = $olay; }

    $toplam = (int) db_deger('SELECT COUNT(*) FROM log_kayitlari l' . $kosul, $p);
    $kayitlar = db_liste('SELECT l.*, y.kullanici_adi FROM log_kayitlari l LEFT JOIN yoneticiler y ON y.id = l.yonetici_id' . $kosul . ' ORDER BY l.id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$ofset, $p);
    $sayfaSayisi = (int) ceil($toplam / $limit);
    $olaylar = db_liste('SELECT DISTINCT olay FROM log_kayitlari ORDER BY olay');
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas">
        <h2>📜 Islem Gunlugu <small style="color:#64748b; font-weight:normal;">(<?= $toplam ?>)</small></h2>
      </div>
      <div class="yp-panel-gvd">
        <form class="yp-filtre" method="GET">
          <input type="hidden" name="is" value="log">
          <select name="olay">
            <option value="">Tum Olaylar</option>
            <?php foreach ($olaylar as $o): ?>
              <option value="<?= e($o['olay']) ?>" <?= $olay === $o['olay'] ? 'selected' : '' ?>><?= e($o['olay']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="yp-btn yp-btn-anahat yp-btn-kucuk">Filtrele</button>
        </form>

        <?php if (empty($kayitlar)): ?>
          <p style="padding:30px; text-align:center; color:#64748b;">Kayit yok.</p>
        <?php else: ?>
        <table class="yp-tablo">
          <thead><tr><th>#</th><th>Olay</th><th>Yonetici</th><th>Detay</th><th>IP</th><th>Tarih</th></tr></thead>
          <tbody>
          <?php foreach ($kayitlar as $l): ?>
            <tr>
              <td><?= (int)$l['id'] ?></td>
              <td><code><?= e($l['olay']) ?></code></td>
              <td><?= e($l['kullanici_adi'] ?? 'sistem') ?></td>
              <td><small><?= e($l['detay'] ?? '-') ?></small></td>
              <td><code><?= e($l['ip'] ?? '-') ?></code></td>
              <td><small><?= e(tarih($l['olusturma'], 'd.m.Y H:i:s')) ?></small></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($sayfaSayisi > 1): ?>
          <div class="yp-sayfalama">
            <?php
              $baslangic = max(1, $sayfa - 5);
              $bitis = min($sayfaSayisi, $sayfa + 5);
              if ($baslangic > 1) echo '<a href="?is=log&s=1&olay=' . e($olay) . '">1...</a>';
              for ($i = $baslangic; $i <= $bitis; $i++):
            ?>
              <a href="?is=log&s=<?= $i ?>&olay=<?= e($olay) ?>" class="<?= $i === $sayfa ? 'aktif' : '' ?>"><?= $i ?></a>
            <?php endfor; if ($bitis < $sayfaSayisi) echo '<a href="?is=log&s=' . $sayfaSayisi . '&olay=' . e($olay) . '">...' . $sayfaSayisi . '</a>'; ?>
          </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

// =================================================================
// 13) GUNCELLEME
// =================================================================
function _yp_guncelle(): void {
    if (!Auth::roldeMi('super')) { flash_ekle('hata', 'Yetkisiz'); yonlendir(SITE_URL . '/yonetim.php?is=panel'); }

    $mevcut = Updater::mevcutVersiyon();
    $uzak = null;
    $hata = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_dogrula($_POST['csrf'] ?? null)) { flash_ekle('hata', 'CSRF hatasi'); yonlendir(SITE_URL . '/yonetim.php?is=guncelle'); }

        $aks = $_POST['aks'] ?? '';
        if ($aks === 'kontrol') {
            // Sonraki yuklemede uzak bilgisi gosterilecek (basit yol)
            yonlendir(SITE_URL . '/yonetim.php?is=guncelle&kontrol=1');
        } elseif ($aks === 'uygula') {
            $zipUrl = $_POST['zip_url'] ?? '';
            if (!$zipUrl) { flash_ekle('hata', 'ZIP URL alinamadi.'); yonlendir(SITE_URL . '/yonetim.php?is=guncelle'); }
            try {
                $ind = Updater::indir($zipUrl);
                if (empty($ind['basari'])) { flash_ekle('hata', 'Indirme: ' . ($ind['hata'] ?? 'bilinmeyen hata')); }
                else {
                    $uyg = Updater::uygula($ind['dosya']);
                    if (empty($uyg['basari'])) { flash_ekle('hata', 'Uygulama: ' . ($uyg['hata'] ?? 'bilinmeyen hata')); }
                    else {
                        // Migration sonucu
                        $mig = $uyg['migration'] ?? ['uygulanan' => [], 'atlanan' => [], 'hata' => null];
                        $migMsj = '';
                        if (!empty($mig['uygulanan'])) {
                            $migMsj .= ' ' . count($mig['uygulanan']) . ' migration uygulandi (' . implode(', ', $mig['uygulanan']) . ').';
                        }
                        if (!empty($mig['hata'])) {
                            $migMsj .= ' MIGRATION HATASI: ' . $mig['hata'];
                        }
                        $bildirim = 'Guncelleme uygulandi. ' . (int)($uyg['kopyalanan'] ?? 0) . ' dosya yenilendi.';
                        if (!empty($uyg['yapimci']) && (int)$uyg['yapimci'] > 0) {
                            $bildirim .= ' ' . (int)$uyg['yapimci'] . ' yapimci dosyasi (uploads/) guncellendi.';
                        }
                        $bildirim .= $migMsj;
                        if (!empty($mig['hata'])) {
                            flash_ekle('hata', $bildirim);
                        } else {
                            flash_ekle('ok', $bildirim . ' Lutfen panelinizi yenileyin.');
                        }
                        log_yaz('sistem_guncelle', 'Dosya: ' . (int)($uyg['kopyalanan'] ?? 0) . ' | Migration: ' . count($mig['uygulanan'] ?? []), Auth::mevcutAdmin()['id']);
                    }
                }
            } catch (Throwable $e) { flash_ekle('hata', 'Hata: ' . $e->getMessage()); }
            yonlendir(SITE_URL . '/yonetim.php?is=guncelle');
        }
    }

    if (!empty($_GET['kontrol'])) {
        $uzak = Updater::sonRelease();
        if (empty($uzak['basari'])) $hata = $uzak['hata'] ?? 'Bilinmeyen hata';
    }
    ?>
    <div class="yp-panel">
      <div class="yp-panel-bas"><h2>⬆ Sistem Guncellemesi</h2></div>
      <div class="yp-panel-gvd">
        <table class="yp-tablo" style="margin-bottom:20px;">
          <tr><th style="width:30%">Mevcut Versiyon</th><td><strong>v<?= e($mevcut) ?></strong></td></tr>
          <tr><th>GitHub Deposu</th><td><?php if (defined('UPDATE_GITHUB_REPO') && UPDATE_GITHUB_REPO): ?><a href="https://github.com/<?= e(UPDATE_GITHUB_REPO) ?>/releases" target="_blank"><?= e(UPDATE_GITHUB_REPO) ?></a><?php else: ?><em>tanimlanmamis</em><?php endif; ?></td></tr>
          <tr><th>Son Kontrol</th><td><?= !empty($_GET['kontrol']) ? date('d.m.Y H:i') : '-' ?></td></tr>
        </table>

        <form method="POST" style="margin-bottom:20px;">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="aks" value="kontrol">
          <button class="yp-btn">🔄 Guncelleme Kontrol Et</button>
        </form>

        <?php if ($hata): ?>
          <div class="yp-uyari hata">⚠ <?= e($hata) ?></div>
        <?php elseif ($uzak && !empty($uzak['basari'])): ?>
          <div class="yp-uyari <?= Updater::yeniVarMi($uzak['versiyon']) ? 'info' : 'ok' ?>">
            <strong>Uzak Versiyon:</strong> v<?= e($uzak['versiyon']) ?>
            <?php if (Updater::yeniVarMi($uzak['versiyon'])): ?>
              — <strong>Yeni guncelleme mevcut!</strong>
            <?php else: ?>
              — Sisteminiz guncel.
            <?php endif; ?>
          </div>

          <div class="yp-panel" style="background:#f8fafc;">
            <div class="yp-panel-gvd">
              <h3 style="font-size:1rem; margin-bottom:10px;"><?= e($uzak['ad'] ?: 'Release') ?></h3>
              <p style="color:#64748b; margin-bottom:12px;">Yayim tarihi: <?= e(tarih($uzak['tarih'])) ?></p>
              <?php if (!empty($uzak['aciklama'])): ?>
                <pre style="background:#fff; padding:14px; border-radius:6px; white-space:pre-wrap; font-family:monospace; font-size:0.85rem; max-height:300px; overflow:auto; border:1px solid #e2e8f0;"><?= e($uzak['aciklama']) ?></pre>
              <?php endif; ?>

              <?php if (Updater::yeniVarMi($uzak['versiyon'])): ?>
                <div class="yp-uyari warn" style="margin-top:14px;">
                  ⚠ <strong>Dikkat:</strong> Guncelleme uygulandiginda dosyalar yeni surumle degistirilir.
                  Korunan dosyalar (ASLA ezilmez): <code>config.php</code>, <code>uploads/</code>, <code>updates/</code>, <code>.htaccess.custom</code>
                </div>
                <form method="POST" data-onay="Guncellemeyi simdi uygulamak istediginizden emin misiniz?">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="aks" value="uygula">
                  <input type="hidden" name="zip_url" value="<?= e($uzak['asset_url'] ?: $uzak['zip_url']) ?>">
                  <button class="yp-btn yp-btn-buyuk yp-btn-yesil">⬇ Indir ve Uygula</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="yp-uyari info" style="margin-top:20px;">
          <strong>ℹ Manuel guncelleme:</strong> GitHub Releases sayfasindan ZIP indirip <code>updates/</code> klasorune yukleyin,
          sonra SSH ile <code>php -r "require 'inc/updater.php'; Updater::uygula('updates/dosya.zip');"</code> calistirabilirsiniz.
        </div>
      </div>
    </div>
    <?php
}
