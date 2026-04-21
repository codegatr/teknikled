<?php
/**
 * inc/auth.php - Admin Kimlik Dogrulama
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

class Auth {
    public static function giris(string $kullanici, string $sifre): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $y = db_satir(
            'SELECT * FROM yoneticiler WHERE (kullanici_adi = :k1 OR eposta = :k2) AND aktif = 1 LIMIT 1',
            ['k1' => $kullanici, 'k2' => $kullanici]
        );

        if (!$y || !password_verify($sifre, $y['sifre_hash'])) {
            // Brute force yavaşlatma
            usleep(random_int(400000, 900000));
            return false;
        }

        $_SESSION['admin'] = [
            'id'        => (int)$y['id'],
            'ad_soyad'  => $y['ad_soyad'],
            'kullanici' => $y['kullanici_adi'],
            'eposta'    => $y['eposta'],
            'rol'       => $y['rol'],
            'giris_ts'  => time(),
        ];

        // Son giris guncelle
        db_guncelle('yoneticiler',
            ['son_giris' => date('Y-m-d H:i:s'), 'son_ip' => istemci_ip()],
            'id = :id',
            ['id' => $y['id']]
        );

        log_yaz('giris', 'Admin giris yapti', (int)$y['id']);
        session_regenerate_id(true);
        return true;
    }

    public static function cikis(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!empty($_SESSION['admin']['id'])) {
            log_yaz('cikis', 'Admin cikis yapti', (int)$_SESSION['admin']['id']);
        }
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }

    public static function giriliMi(): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return !empty($_SESSION['admin']['id']);
    }

    public static function mevcutAdmin(): ?array {
        if (!self::giriliMi()) return null;
        return $_SESSION['admin'];
    }

    /** Sayfayi korur - giris yoksa yonlendir */
    public static function korumali(): void {
        if (!self::giriliMi()) {
            yonlendir(SITE_URL . '/yonetim.php?is=giris');
        }

        // Oturum 8 saat sonra sonlandir
        if (!empty($_SESSION['admin']['giris_ts']) && (time() - $_SESSION['admin']['giris_ts']) > 28800) {
            self::cikis();
            yonlendir(SITE_URL . '/yonetim.php?is=giris&sure_doldu=1');
        }
    }

    public static function roldeMi(string $rol): bool {
        return self::giriliMi() && $_SESSION['admin']['rol'] === $rol;
    }
}
