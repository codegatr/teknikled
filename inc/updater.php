<?php
/**
 * inc/updater.php - ZIP Tabanli Guncelleme Motoru
 * GitHub Releases uzerinden guncelleme indirir ve uygular.
 * config.php ve uploads/ dokunulmaz.
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

class Updater {
    public const MANIFEST = __DIR__ . '/../manifest.json';
    public const UPDATES_DIR = __DIR__ . '/../updates';
    public const KORUNANLAR = ['config.php', '.htaccess.custom', 'uploads', 'updates'];

    /** Mevcut versiyonu doner */
    public static function mevcutVersiyon(): string {
        if (!is_file(self::MANIFEST)) return '0.0.0';
        $m = json_decode(file_get_contents(self::MANIFEST), true);
        return $m['version'] ?? '0.0.0';
    }

    /** GitHub'dan son release bilgisini getirir */
    public static function sonRelease(): array {
        if (!defined('UPDATE_GITHUB_REPO') || UPDATE_GITHUB_REPO === '') {
            return ['basari' => false, 'hata' => 'Guncelleme deposu tanimlanmamis'];
        }

        $url = 'https://api.github.com/repos/' . UPDATE_GITHUB_REPO . '/releases/latest';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'TeknikLED-Updater',
            CURLOPT_HTTPHEADER     => ['Accept: application/vnd.github+json'],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $yanit = curl_exec($ch);
        $kod   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($kod !== 200 || !$yanit) {
            return ['basari' => false, 'hata' => 'GitHub\'a erisilemedi (HTTP ' . $kod . ')'];
        }

        $r = json_decode($yanit, true);
        if (!is_array($r)) {
            return ['basari' => false, 'hata' => 'Gecersiz yanit'];
        }

        return [
            'basari'    => true,
            'versiyon'  => ltrim($r['tag_name'] ?? '', 'v'),
            'ad'        => $r['name'] ?? '',
            'aciklama'  => $r['body'] ?? '',
            'tarih'     => $r['published_at'] ?? '',
            'zip_url'   => $r['zipball_url'] ?? '',
            'asset_url' => $r['assets'][0]['browser_download_url'] ?? null,
        ];
    }

    /** ZIP indir (asset varsa onu, yoksa zipball) */
    public static function indir(string $url): array {
        if (!is_dir(self::UPDATES_DIR)) @mkdir(self::UPDATES_DIR, 0755, true);

        $hedef = self::UPDATES_DIR . '/update_' . date('Ymd_His') . '.zip';
        $ch = curl_init($url);
        $fp = fopen($hedef, 'wb');
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'TeknikLED-Updater',
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        $kod = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($kod !== 200 || !is_file($hedef) || filesize($hedef) < 1024) {
            @unlink($hedef);
            return ['basari' => false, 'hata' => 'Indirme basarisiz (HTTP ' . $kod . ')'];
        }

        return ['basari' => true, 'dosya' => $hedef];
    }

    /** ZIP'i cikar ve uygula (dosyalar + migrationlar) */
    public static function uygula(string $zipDosya): array {
        if (!class_exists('ZipArchive')) {
            return ['basari' => false, 'hata' => 'ZipArchive sinifi yok'];
        }

        $tmp = self::UPDATES_DIR . '/extract_' . date('Ymd_His');
        @mkdir($tmp, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipDosya) !== true) {
            return ['basari' => false, 'hata' => 'ZIP acilamadi'];
        }
        $zip->extractTo($tmp);
        $zip->close();

        // Release zipball genelde tek alt klasor icerir
        $items = array_values(array_diff(scandir($tmp) ?: [], ['.', '..']));
        $kaynak = $tmp;
        if (count($items) === 1 && is_dir($tmp . '/' . $items[0])) {
            $kaynak = $tmp . '/' . $items[0];
        }

        // Kopyala (korunan dosyalar haric)
        $sayac = self::kopyala($kaynak, __DIR__ . '/..');

        // Migrationlari uygula (dosya kopyalamasindan SONRA, cunku yeni migration
        // dosyalari kopyalama sirasinda gelmis olur)
        $migrationSonuc = self::migrationlariUygula();

        // Temizle
        self::sil($tmp);
        @unlink($zipDosya);

        return [
            'basari'      => true,
            'kopyalanan'  => $sayac,
            'migration'   => $migrationSonuc,
        ];
    }

    /**
     * migrations/ klasorundeki SQL dosyalarini sirali calistirir.
     * _migrations tablosunda takip edilir, iki kez calismaz.
     * Dosya adlari: v0.1.3.sql, v0.1.4.sql, v0.1.4-hotfix.sql gibi olmali.
     * Donus: ['uygulanan' => [...], 'atlanan' => [...], 'hata' => '...' | null]
     */
    public static function migrationlariUygula(): array {
        $sonuc = ['uygulanan' => [], 'atlanan' => [], 'hata' => null];

        $dir = __DIR__ . '/../migrations';
        if (!is_dir($dir)) {
            return $sonuc;  // Migration klasoru yoksa sessiz gec
        }

        // DB ve _migrations tablosu
        try {
            if (!function_exists('db')) {
                require_once __DIR__ . '/db.php';
            }
            db()->exec(
                "CREATE TABLE IF NOT EXISTS `_migrations` (
                    `ad` VARCHAR(100) NOT NULL PRIMARY KEY,
                    `uygulama_tarihi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `sonuc` VARCHAR(20) NOT NULL DEFAULT 'basarili'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (Throwable $e) {
            $sonuc['hata'] = 'Migration tablosu olusturulamadi: ' . $e->getMessage();
            return $sonuc;
        }

        $dosyalar = glob($dir . '/*.sql') ?: [];
        sort($dosyalar);  // v0.1.3.sql < v0.1.4.sql < v0.1.5.sql

        foreach ($dosyalar as $yol) {
            $ad = basename($yol);

            // Zaten uygulanmis mi?
            try {
                $var = db_deger('SELECT 1 FROM `_migrations` WHERE `ad` = :a', ['a' => $ad]);
            } catch (Throwable $e) {
                $sonuc['hata'] = 'Migration sorgu hatasi: ' . $e->getMessage();
                return $sonuc;
            }

            if ($var) {
                $sonuc['atlanan'][] = $ad;
                continue;
            }

            $sql = @file_get_contents($yol);
            if (!$sql) {
                $sonuc['atlanan'][] = $ad . ' (bos dosya)';
                continue;
            }

            try {
                // Migration SQL'i guvenli calistir: query() ile rowset'leri tuketmek
                // unbuffered query hatalarini onler (ileride SELECT, CALL gibi
                // sonuc donduren statement gelirse bile)
                $stmt = db()->query($sql);
                if ($stmt !== false) {
                    // Tum rowset'leri tuket (SELECT, CALL sonuclari varsa temizle)
                    do {
                        $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } while ($stmt->nextRowset());
                    $stmt->closeCursor();
                    $stmt = null;  // acik cursor kalmasin
                }
                db_ekle('_migrations', ['ad' => $ad, 'sonuc' => 'basarili']);
                $sonuc['uygulanan'][] = $ad;
            } catch (Throwable $e) {
                // Hatayi tabloya da yaz ki kullanici gorebilsin
                try {
                    db_ekle('_migrations', ['ad' => $ad, 'sonuc' => 'hata']);
                } catch (Throwable $_) {}
                $sonuc['hata'] = $ad . ' hatasi: ' . $e->getMessage();
                return $sonuc;  // Hatali migration'da dur, sonrakileri calistirma
            }
        }

        return $sonuc;
    }

    private static function kopyala(string $kaynak, string $hedef): int {
        $sayac = 0;
        $ri = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($kaynak, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($ri as $dosya) {
            $bagimli = substr($dosya->getPathname(), strlen($kaynak) + 1);
            $bagimli = str_replace('\\', '/', $bagimli);

            // Korunan yollar atlanir
            $atla = false;
            foreach (self::KORUNANLAR as $kr) {
                if ($bagimli === $kr || str_starts_with($bagimli, $kr . '/')) {
                    $atla = true;
                    break;
                }
            }
            if ($atla) continue;

            $hedefYol = $hedef . '/' . $bagimli;
            if ($dosya->isDir()) {
                if (!is_dir($hedefYol)) @mkdir($hedefYol, 0755, true);
            } else {
                @mkdir(dirname($hedefYol), 0755, true);
                if (@copy($dosya->getPathname(), $hedefYol)) $sayac++;
            }
        }
        return $sayac;
    }

    private static function sil(string $yol): void {
        if (!is_dir($yol)) {
            @unlink($yol);
            return;
        }
        $ri = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($yol, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($ri as $d) {
            $d->isDir() ? @rmdir($d->getPathname()) : @unlink($d->getPathname());
        }
        @rmdir($yol);
    }

    /** Versiyon karsilastir */
    public static function yeniVarMi(string $uzak, ?string $yerel = null): bool {
        $yerel ??= self::mevcutVersiyon();
        return version_compare($uzak, $yerel, '>');
    }
}
