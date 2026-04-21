<?php
/**
 * inc/mailer.php - Basit SMTP Mail Gonderici
 * PHPMailer olmadan cekirdek SMTP implementasyonu
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

class Mailer {
    /**
     * Basit SMTP mail gonderimi
     * @return array ['basari'=>bool, 'hata'=>string|null]
     */
    public static function gonder(string $alici, string $konu, string $htmlIcerik, string $aliciAdi = ''): array {
        if (!defined('SMTP_HOST') || SMTP_HOST === '') {
            // SMTP yapilandirilmamis - mail() kullan (fallback)
            return self::mailFallback($alici, $konu, $htmlIcerik);
        }

        $secure = SMTP_SECURE ?: '';
        $transport = '';
        if ($secure === 'ssl') $transport = 'ssl://';
        $host = $transport . SMTP_HOST;
        $port = (int) SMTP_PORT;

        try {
            $sock = @fsockopen($host, $port, $errno, $errstr, 15);
            if (!$sock) {
                return ['basari' => false, 'hata' => 'SMTP baglanti hatasi: ' . $errstr];
            }
            stream_set_timeout($sock, 15);

            // Helper kapatma + hata dön
            $finish = function (?string $hata = null) use ($sock): array {
                @fwrite($sock, "QUIT\r\n");
                @fclose($sock);
                return ['basari' => $hata === null, 'hata' => $hata];
            };

            // Helper: satir gonder / oku
            $send = function (string $cmd) use ($sock) {
                fwrite($sock, $cmd . "\r\n");
            };
            $read = function () use ($sock): string {
                $yanit = '';
                while ($satir = fgets($sock, 515)) {
                    $yanit .= $satir;
                    if (!isset($satir[3]) || $satir[3] === ' ') break;
                }
                return $yanit;
            };

            // Karsilama
            $gelen = $read();
            if (!str_starts_with($gelen, '220')) return $finish('Sunucu karsilama hatasi');

            $send('EHLO ' . (parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost'));
            $ehloYanit = $read();
            if (!str_starts_with($ehloYanit, '250')) return $finish('EHLO hatasi');

            // TLS
            if ($secure === 'tls') {
                $send('STARTTLS');
                $tlsYanit = $read();
                if (!str_starts_with($tlsYanit, '220')) return $finish('STARTTLS hatasi');
                if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                    return $finish('TLS crypto hatasi');
                }
                $send('EHLO ' . (parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost'));
                $read();
            }

            // AUTH LOGIN
            $send('AUTH LOGIN');
            $ayn = $read();
            if (!str_starts_with($ayn, '334')) return $finish('AUTH baslatilamadi');
            $send(base64_encode(SMTP_USER));
            $ayn = $read();
            if (!str_starts_with($ayn, '334')) return $finish('Kullanici reddedildi');
            $send(base64_encode(SMTP_PASS));
            $ayn = $read();
            if (!str_starts_with($ayn, '235')) return $finish('Sifre reddedildi');

            // Mail from
            $send('MAIL FROM: <' . SMTP_FROM . '>');
            if (!str_starts_with($read(), '250')) return $finish('MAIL FROM reddedildi');

            // Rcpt to
            $send('RCPT TO: <' . $alici . '>');
            $rcptYanit = $read();
            if (!str_starts_with($rcptYanit, '250') && !str_starts_with($rcptYanit, '251')) {
                return $finish('RCPT TO reddedildi');
            }

            // Data
            $send('DATA');
            if (!str_starts_with($read(), '354')) return $finish('DATA reddedildi');

            $from  = SMTP_FROM_NAME . ' <' . SMTP_FROM . '>';
            $to    = ($aliciAdi ? $aliciAdi . ' <' . $alici . '>' : $alici);
            $mid   = '<' . bin2hex(random_bytes(8)) . '@' . parse_url(SITE_URL, PHP_URL_HOST) . '>';

            $basliklar = [
                'From: ' . $from,
                'To: ' . $to,
                'Subject: =?UTF-8?B?' . base64_encode($konu) . '?=',
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
                'Date: ' . date('r'),
                'Message-ID: ' . $mid,
                'X-Mailer: TeknikLED v0.1.0',
            ];

            // Nokta kacisi (satir sonu sonrasi tek nokta, SMTP ozel)
            $icerikGuvenli = preg_replace('/^\./m', '..', $htmlIcerik);

            $send(implode("\r\n", $basliklar) . "\r\n\r\n" . $icerikGuvenli . "\r\n.");
            $finYanit = $read();
            if (!str_starts_with($finYanit, '250')) return $finish('Mail kabul edilmedi');

            return $finish(null);
        } catch (Throwable $e) {
            return ['basari' => false, 'hata' => $e->getMessage()];
        }
    }

    /** SMTP ayarlanmamissa PHP mail() */
    private static function mailFallback(string $alici, string $konu, string $icerik): array {
        $basliklar  = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>' . "\r\n";
        $basliklar .= 'MIME-Version: 1.0' . "\r\n";
        $basliklar .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        $basliklar .= 'X-Mailer: TeknikLED' . "\r\n";

        $basari = @mail($alici, '=?UTF-8?B?' . base64_encode($konu) . '?=', $icerik, $basliklar);
        return ['basari' => $basari, 'hata' => $basari ? null : 'mail() fonksiyonu basarisiz'];
    }

    /** Teklif e-postasi sablonu */
    public static function teklifBildirimi(array $veri): string {
        $satirlar = [];
        foreach ($veri as $k => $v) {
            if ($v === null || $v === '') continue;
            $satirlar[] = '<tr><td style="padding:8px;border:1px solid #e2e8f0;background:#f8f9fa;font-weight:600;">'
                . e(ucfirst(str_replace('_', ' ', (string)$k)))
                . '</td><td style="padding:8px;border:1px solid #e2e8f0;">' . e((string)$v) . '</td></tr>';
        }

        $tablo = implode('', $satirlar);
        $base  = rtrim(SITE_URL, '/');
        $tarih = date('d.m.Y H:i');

        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><title>Yeni Teklif Talebi</title></head>
<body style="margin:0;padding:20px;background:#f1f5f9;font-family:Arial,sans-serif;color:#1a1a1a;">
  <table cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;">
    <tr>
      <td style="background:linear-gradient(90deg,#e53e3e,#38a169,#3182ce);padding:20px;color:#ffffff;text-align:center;">
        <h1 style="margin:0;font-size:22px;">TeknikLED - Yeni Teklif Talebi</h1>
        <p style="margin:8px 0 0;font-size:13px;opacity:.95;">{$tarih}</p>
      </td>
    </tr>
    <tr>
      <td style="padding:24px;">
        <p style="margin:0 0 16px;">Web sitenizden yeni bir teklif talebi ulasti:</p>
        <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:14px;">
          {$tablo}
        </table>
        <p style="margin:20px 0 0;font-size:13px;color:#6c757d;">
          Yonetim paneline erisim: <a href="{$base}/yonetim.php" style="color:#3182ce;">{$base}/yonetim.php</a>
        </p>
      </td>
    </tr>
    <tr>
      <td style="background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#6c757d;">
        TeknikLED &copy; {$tarih} &middot; CODEGA
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
