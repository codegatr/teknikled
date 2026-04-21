<?php
/**
 * api/iletisim.php - Iletisim Formu AJAX Endpoint
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz istek'], 405);
}

// Honeypot
if (!empty($_POST['website'])) {
    json_yanit(['ok' => true, 'mesaj' => t('iletisim.form_ok')]);
}

// CSRF
if (!csrf_dogrula($_POST['csrf'] ?? null)) {
    json_yanit(['ok' => false, 'mesaj' => 'Oturum suresi dolmus, sayfayi yenileyin.'], 403);
}

// Rate limit
$simdi = time();
if (!empty($_SESSION['son_iletisim_ts']) && ($simdi - (int)$_SESSION['son_iletisim_ts']) < 30) {
    json_yanit(['ok' => false, 'mesaj' => 'Cok sik gonderiyorsunuz, lutfen bekleyin.'], 429);
}

$girdi = [
    'ad_soyad' => trim((string)($_POST['ad_soyad'] ?? '')),
    'eposta'   => trim((string)($_POST['eposta'] ?? '')),
    'telefon'  => trim((string)($_POST['telefon'] ?? '')) ?: null,
    'konu'     => trim((string)($_POST['konu'] ?? '')) ?: null,
    'mesaj'    => trim((string)($_POST['mesaj'] ?? '')),
    'dil'      => in_array($_POST['dil'] ?? '', ['tr','en','ar'], true) ? $_POST['dil'] : dil(),
    'ip'       => istemci_ip(),
];

if ($girdi['ad_soyad'] === '' || $girdi['eposta'] === '' || $girdi['mesaj'] === '') {
    json_yanit(['ok' => false, 'mesaj' => t('genel.zorunlu')], 400);
}
if (!filter_var($girdi['eposta'], FILTER_VALIDATE_EMAIL)) {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz e-posta'], 400);
}
if (mb_strlen($girdi['mesaj']) > 5000) {
    json_yanit(['ok' => false, 'mesaj' => 'Mesaj cok uzun'], 400);
}

try {
    $id = db_ekle('iletisim_mesajlari', $girdi);
    $_SESSION['son_iletisim_ts'] = $simdi;

    try {
        $bildirimVeri = [
            'Mesaj No'  => '#' . $id,
            'Ad Soyad'  => $girdi['ad_soyad'],
            'E-posta'   => $girdi['eposta'],
            'Telefon'   => $girdi['telefon'],
            'Konu'      => $girdi['konu'],
            'Mesaj'     => $girdi['mesaj'],
            'Dil'       => strtoupper($girdi['dil']),
            'IP'        => $girdi['ip'],
        ];
        $alici = defined('MAIL_TO_SALES') && MAIL_TO_SALES ? MAIL_TO_SALES : SITE_EMAIL;
        Mailer::gonder($alici, 'Yeni Iletisim Mesaji #' . $id . ' - ' . $girdi['ad_soyad'],
                       Mailer::teklifBildirimi($bildirimVeri));
    } catch (Throwable $e) {
        // sessiz hata
    }

    log_yaz('iletisim_alindi', 'Iletisim #' . $id . ' - ' . $girdi['ad_soyad']);

    json_yanit(['ok' => true, 'mesaj' => t('iletisim.form_ok'), 'id' => $id]);
} catch (Throwable $e) {
    if (DEBUG) json_yanit(['ok' => false, 'mesaj' => 'Hata: ' . $e->getMessage()], 500);
    json_yanit(['ok' => false, 'mesaj' => t('genel.hata')], 500);
}
