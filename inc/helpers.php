<?php
/**
 * inc/helpers.php - Genel Yardimci Fonksiyonlar
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

/** Guvenli HTML ciktisi */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** URL slug olustur (pure ASCII) */
function slug(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $tr = ['ı','ğ','ü','ş','ö','ç','İ','Ğ','Ü','Ş','Ö','Ç'];
    $en = ['i','g','u','s','o','c','i','g','u','s','o','c'];
    $s = str_replace($tr, $en, $s);
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'urun';
}

/** CSRF token uret/getir */
function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** CSRF dogrula */
function csrf_dogrula(?string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return !empty($_SESSION['csrf']) && !empty($token) && hash_equals($_SESSION['csrf'], $token);
}

/** JSON yanit ver ve cik */
function json_yanit(array $veri, int $kod = 200): never {
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($veri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Istemci IP'si */
function istemci_ip(): string {
    $basliklar = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($basliklar as $b) {
        if (!empty($_SERVER[$b])) {
            $ip = explode(',', $_SERVER[$b])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

/** URL olustur (dil dahil) */
function url(string $yol = '', ?string $lang = null): string {
    $l = $lang ?? ($_SESSION['lang'] ?? DEFAULT_LANG);
    $yol = ltrim($yol, '/');
    $base = rtrim(SITE_URL, '/');
    if ($l === DEFAULT_LANG) {
        return $yol === '' ? $base . '/' : $base . '/' . $yol;
    }
    return $yol === '' ? $base . '/' . $l : $base . '/' . $l . '/' . $yol;
}

/** Asset URL */
function asset(string $yol): string {
    return rtrim(SITE_URL, '/') . '/assets/' . ltrim($yol, '/');
}

/** Upload URL */
function upload(string $yol): string {
    if (!$yol) return asset('img/placeholder.png');
    if (str_starts_with($yol, 'http')) return $yol;
    return rtrim(SITE_URL, '/') . '/uploads/' . ltrim($yol, '/');
}

/** Tarihi biçimlendir */
function tarih(string $tarih, string $format = 'd.m.Y H:i'): string {
    $ts = strtotime($tarih);
    return $ts ? date($format, $ts) : '-';
}

/** Metni kısalt */
function kisalt(string $s, int $uzunluk = 150, string $son = '...'): string {
    $s = strip_tags($s);
    if (mb_strlen($s) <= $uzunluk) return $s;
    return mb_substr($s, 0, $uzunluk) . $son;
}

/** Log kaydı */
function log_yaz(string $olay, ?string $detay = null, ?int $yoneticiId = null): void {
    try {
        db_ekle('log_kayitlari', [
            'yonetici_id' => $yoneticiId,
            'olay'        => $olay,
            'detay'       => $detay,
            'ip'          => istemci_ip(),
        ]);
    } catch (Throwable $e) {
        // Log yazmak sistem calismasini engellemesin
    }
}

/** Ayar getirme (onbelleklenmis) */
function ayar(string $anahtar, mixed $vars = ''): mixed {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db_liste('SELECT anahtar, deger FROM ayarlar') as $r) {
                $cache[$r['anahtar']] = $r['deger'];
            }
        } catch (Throwable $e) {
            // tablo yoksa sorun yok
        }
    }
    return $cache[$anahtar] ?? $vars;
}

/** Dosya yukleme (guvenli) */
function dosya_yukle(array $dosya, string $altKlasor = 'urunler'): array {
    $izinli = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxBayt = UPLOAD_MAX_MB * 1024 * 1024;

    if (!isset($dosya['tmp_name']) || !is_uploaded_file($dosya['tmp_name'])) {
        return ['hata' => 'Dosya yuklenemedi'];
    }
    if ($dosya['size'] > $maxBayt) {
        return ['hata' => 'Dosya cok buyuk (max ' . UPLOAD_MAX_MB . ' MB)'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($dosya['tmp_name']);
    if (!in_array($mime, $izinli, true)) {
        return ['hata' => 'Izin verilmeyen dosya tipi: ' . $mime];
    }

    $uzanti = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    };

    $ad = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $uzanti;
    $hedef = __DIR__ . '/../uploads/' . $altKlasor . '/' . $ad;

    if (!is_dir(dirname($hedef))) {
        @mkdir(dirname($hedef), 0755, true);
    }

    if (!move_uploaded_file($dosya['tmp_name'], $hedef)) {
        return ['hata' => 'Dosya tasinamadi'];
    }

    return ['yol' => $altKlasor . '/' . $ad, 'ad' => $ad];
}

/** Guvenli yonlendirme */
function yonlendir(string $url, int $kod = 302): never {
    header('Location: ' . $url, true, $kod);
    exit;
}

/** Flash mesaj */
function flash_ekle(string $tip, string $mesaj): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'][] = ['tip' => $tip, 'mesaj' => $mesaj];
}

function flash_al(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $m = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $m;
}

/** Mevcut dile gore kolon secer */
function dil_kolon(string $base, ?string $lang = null): string {
    $l = $lang ?? ($_SESSION['lang'] ?? DEFAULT_LANG);
    return $base . '_' . $l;
}

/** Coklu dil alanindan uygun olani secer (fallback: TR) */
function dil_alan(array $row, string $base, ?string $lang = null): string {
    $l = $lang ?? ($_SESSION['lang'] ?? DEFAULT_LANG);
    $anahtar = $base . '_' . $l;
    if (!empty($row[$anahtar])) return $row[$anahtar];
    return $row[$base . '_tr'] ?? '';
}
