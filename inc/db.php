<?php
/**
 * inc/db.php - PDO Veritabani Baglantisi
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

if (!defined('DB_HOST')) {
    http_response_code(500);
    die('Yapilandirma dosyasi yuklenmemis.');
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "' COLLATE 'utf8mb4_unicode_ci'",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $e) {
        if (defined('DEBUG') && DEBUG) {
            die('DB baglanti hatasi: ' . $e->getMessage());
        }
        http_response_code(500);
        die('Veritabani baglanti hatasi. Lutfen yoneticiye bildirin.');
    }
    return $pdo;
}

/** Tek satir getirme kisayolu */
function db_satir(string $sql, array $params = []): ?array {
    $st = db()->prepare($sql);
    $st->execute($params);
    $r = $st->fetch();
    return $r === false ? null : $r;
}

/** Coklu satir getirme kisayolu */
function db_liste(string $sql, array $params = []): array {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

/** Tek deger getirme kisayolu */
function db_deger(string $sql, array $params = []): mixed {
    $st = db()->prepare($sql);
    $st->execute($params);
    $r = $st->fetchColumn();
    return $r === false ? null : $r;
}

/** INSERT - son eklenen id'yi doner */
function db_ekle(string $tablo, array $veri): int {
    $kolonlar = array_keys($veri);
    $yerler   = array_map(fn($k) => ':' . $k, $kolonlar);
    $sql = 'INSERT INTO `' . $tablo . '` (`' . implode('`,`', $kolonlar) . '`) VALUES (' . implode(',', $yerler) . ')';
    $st = db()->prepare($sql);
    $st->execute($veri);
    return (int) db()->lastInsertId();
}

/** UPDATE - etkilenen satir sayisini doner */
function db_guncelle(string $tablo, array $veri, string $kosul, array $kosulParams = []): int {
    $setler = [];
    foreach (array_keys($veri) as $k) {
        $setler[] = '`' . $k . '` = :' . $k;
    }
    $sql = 'UPDATE `' . $tablo . '` SET ' . implode(', ', $setler) . ' WHERE ' . $kosul;
    $st = db()->prepare($sql);
    $st->execute(array_merge($veri, $kosulParams));
    return $st->rowCount();
}

/** DELETE - etkilenen satir sayisini doner */
function db_sil(string $tablo, string $kosul, array $params = []): int {
    $sql = 'DELETE FROM `' . $tablo . '` WHERE ' . $kosul;
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}
