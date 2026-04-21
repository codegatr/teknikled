<?php
/**
 * inc/i18n.php - Cok Dilli Altyapi
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

class I18n {
    private static array $strings = [];
    private static string $aktif = 'tr';

    public static function init(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $izinli = ['tr', 'en', 'ar'];

        $rota = $_GET['rota'] ?? '';
        $ilk  = explode('/', $rota)[0] ?? '';

        // URL prefix'i her zaman belirleyicidir.
        // Prefix varsa -> o dil; prefix yoksa -> DAIMA varsayilan dil (TR).
        // Boylece "TR'ye don" bayragi calisir (cunku TR URL'si prefix'siz olur).
        if (in_array($ilk, $izinli, true)) {
            $dil = $ilk;
        } else {
            $dil = DEFAULT_LANG;
        }

        self::$aktif = $dil;
        $_SESSION['lang'] = $dil;

        $dosya = __DIR__ . '/../lang/' . $dil . '.php';
        if (is_file($dosya)) {
            self::$strings = require $dosya;
        }
    }

    public static function aktif(): string {
        return self::$aktif;
    }

    public static function rtl(): bool {
        return self::$aktif === 'ar';
    }

    public static function dir(): string {
        return self::rtl() ? 'rtl' : 'ltr';
    }

    public static function t(string $anahtar, array $parametreler = []): string {
        $deger = self::$strings[$anahtar] ?? $anahtar;
        foreach ($parametreler as $k => $v) {
            $deger = str_replace(':' . $k, (string)$v, $deger);
        }
        return $deger;
    }

    public static function dilListesi(): array {
        return [
            'tr' => ['ad' => 'Türkçe',  'kisa' => 'TR', 'bayrak' => '🇹🇷'],
            'en' => ['ad' => 'English', 'kisa' => 'EN', 'bayrak' => '🇬🇧'],
            'ar' => ['ad' => 'العربية', 'kisa' => 'AR', 'bayrak' => '🇸🇦'],
        ];
    }

    /** Mevcut rotayi baska dilde url olarak uretir */
    public static function cevirUrl(string $hedefDil): string {
        $izinli = ['tr', 'en', 'ar'];
        if (!in_array($hedefDil, $izinli, true)) $hedefDil = DEFAULT_LANG;

        $rota = $_GET['rota'] ?? '';
        $parcalar = explode('/', $rota);

        // Ilk parca dil ise cikar
        if (!empty($parcalar[0]) && in_array($parcalar[0], $izinli, true)) {
            array_shift($parcalar);
        }

        $kalan = implode('/', $parcalar);
        $base = rtrim(SITE_URL, '/');

        if ($hedefDil === DEFAULT_LANG) {
            return $kalan === '' ? $base . '/' : $base . '/' . $kalan;
        }
        return $kalan === '' ? $base . '/' . $hedefDil : $base . '/' . $hedefDil . '/' . $kalan;
    }
}

/** Kisayol */
function t(string $anahtar, array $parametreler = []): string {
    return I18n::t($anahtar, $parametreler);
}

function dil(): string {
    return I18n::aktif();
}
