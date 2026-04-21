<?php
/**
 * api/teklif.php - Teklif Formu AJAX Endpoint
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/../inc/i18n.php';
require_once __DIR__ . '/../inc/mailer.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
I18n::init();

// Sadece POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz istek'], 405);
}

// Honeypot (bot tespiti)
if (!empty($_POST['website'])) {
    // Bot gibi davran: OK don ama kaydetme
    json_yanit(['ok' => true, 'mesaj' => t('teklif.form_ok')]);
}

// CSRF kontrol
if (!csrf_dogrula($_POST['csrf'] ?? null)) {
    json_yanit(['ok' => false, 'mesaj' => 'Oturum suresi dolmus, sayfayi yenileyin.'], 403);
}

// IP bazli rate limit (basit, session)
$simdi = time();
if (!empty($_SESSION['son_teklif_ts']) && ($simdi - (int)$_SESSION['son_teklif_ts']) < 30) {
    json_yanit(['ok' => false, 'mesaj' => 'Cok sik gonderiyorsunuz, lutfen bekleyin.'], 429);
}

// Girdi temizleme
$girdi = [
    'urun_id'       => !empty($_POST['urun_id']) ? (int)$_POST['urun_id'] : null,
    'ad_soyad'      => trim((string)($_POST['ad_soyad'] ?? '')),
    'firma'         => trim((string)($_POST['firma'] ?? '')) ?: null,
    'eposta'        => trim((string)($_POST['eposta'] ?? '')),
    'telefon'       => trim((string)($_POST['telefon'] ?? '')),
    'sehir'         => trim((string)($_POST['sehir'] ?? '')) ?: null,
    'konu'          => trim((string)($_POST['konu'] ?? '')) ?: null,
    'mesaj'         => trim((string)($_POST['mesaj'] ?? '')),
    'olcu_bilgisi'  => trim((string)($_POST['olcu_bilgisi'] ?? '')) ?: null,
    'adet'          => !empty($_POST['adet']) ? max(1, (int)$_POST['adet']) : null,
    'dil'           => in_array($_POST['dil'] ?? '', ['tr','en','ar'], true) ? $_POST['dil'] : dil(),
];

// Zorunlu alan kontrolu
if ($girdi['ad_soyad'] === '' || $girdi['eposta'] === '' || $girdi['telefon'] === '' || $girdi['mesaj'] === '') {
    json_yanit(['ok' => false, 'mesaj' => t('genel.zorunlu')], 400);
}

// E-posta format
if (!filter_var($girdi['eposta'], FILTER_VALIDATE_EMAIL)) {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz e-posta'], 400);
}

// Uzunluk sinirlari
if (mb_strlen($girdi['ad_soyad']) > 150 || mb_strlen($girdi['mesaj']) > 5000) {
    json_yanit(['ok' => false, 'mesaj' => 'Alan cok uzun'], 400);
}

// IP / UA
$girdi['ip']         = istemci_ip();
$girdi['user_agent'] = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
$girdi['durum']      = 'yeni';

// Urun id gecersizse null
if ($girdi['urun_id']) {
    $varMi = db_deger('SELECT id FROM urunler WHERE id = :id AND aktif = 1', ['id' => $girdi['urun_id']]);
    if (!$varMi) $girdi['urun_id'] = null;
}

try {
    $id = db_ekle('teklifler', $girdi);
    $_SESSION['son_teklif_ts'] = $simdi;

    // Urun adini cek (e-posta bildirim icin)
    $urunAd = null;
    if ($girdi['urun_id']) {
        $u = db_satir('SELECT ad_tr, urun_kodu FROM urunler WHERE id = :id', ['id' => $girdi['urun_id']]);
        if ($u) $urunAd = $u['ad_tr'] . ($u['urun_kodu'] ? ' (' . $u['urun_kodu'] . ')' : '');
    }

    // E-posta bildirimi (hata olsa bile form OK dondurur)
    try {
        $bildirimVeri = [
            'Teklif No'  => '#' . $id,
            'Ad Soyad'   => $girdi['ad_soyad'],
            'Firma'      => $girdi['firma'],
            'E-posta'    => $girdi['eposta'],
            'Telefon'    => $girdi['telefon'],
            'Sehir'      => $girdi['sehir'],
            'Urun'       => $urunAd,
            'Olcu'       => $girdi['olcu_bilgisi'],
            'Adet'       => $girdi['adet'],
            'Konu'       => $girdi['konu'],
            'Mesaj'      => $girdi['mesaj'],
            'Dil'        => strtoupper($girdi['dil']),
            'IP'         => $girdi['ip'],
        ];
        $alici = defined('MAIL_TO_SALES') && MAIL_TO_SALES ? MAIL_TO_SALES : SITE_EMAIL;
        Mailer::gonder($alici, 'Yeni Teklif Talebi #' . $id . ' - ' . $girdi['ad_soyad'],
                       Mailer::teklifBildirimi($bildirimVeri));
    } catch (Throwable $e) {
        // Sessiz hata - kullanici etkilenmez
    }

    log_yaz('teklif_alindi', 'Teklif #' . $id . ' - ' . $girdi['ad_soyad']);

    json_yanit(['ok' => true, 'mesaj' => t('teklif.form_ok'), 'id' => $id]);
} catch (Throwable $e) {
    if (DEBUG) json_yanit(['ok' => false, 'mesaj' => 'Hata: ' . $e->getMessage()], 500);
    json_yanit(['ok' => false, 'mesaj' => t('genel.hata')], 500);
}
