<?php
/**
 * api/hesapla.php - LED Ekran Olcu / Modul Hesaplayici
 * Yaklasik modul sayisi ve watt degeri hesaplar.
 * TeknikLED v0.1.0 - CODEGA
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz istek'], 405);
}

$g = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

$piksel    = strtoupper(trim((string)($g['piksel'] ?? 'P2.5')));
$genislik  = max(1, (int)($g['genislik_cm'] ?? 0));   // cm
$yukseklik = max(1, (int)($g['yukseklik_cm'] ?? 0));  // cm

// Modul katalogu: [modul_genislik_mm, modul_yukseklik_mm, watt_per_module]
$moduller = [
    'P1.86' => ['g' => 320, 'y' => 160, 'w' => 35],
    'P2'    => ['g' => 320, 'y' => 160, 'w' => 32],
    'P2.5'  => ['g' => 320, 'y' => 160, 'w' => 30],
    'P3'    => ['g' => 192, 'y' => 192, 'w' => 25],
    'P4'    => ['g' => 256, 'y' => 128, 'w' => 24],
    'P5'    => ['g' => 320, 'y' => 160, 'w' => 22],
    'P6'    => ['g' => 192, 'y' => 192, 'w' => 20],
    'P8'    => ['g' => 256, 'y' => 128, 'w' => 18],
    'P10'   => ['g' => 320, 'y' => 160, 'w' => 15],
];

if (!isset($moduller[$piksel])) {
    json_yanit(['ok' => false, 'mesaj' => 'Gecersiz piksel tipi', 'secenekler' => array_keys($moduller)], 400);
}
if ($genislik < 10 || $yukseklik < 10) {
    json_yanit(['ok' => false, 'mesaj' => 'Olcu en az 10 cm olmali'], 400);
}
if ($genislik > 2000 || $yukseklik > 2000) {
    json_yanit(['ok' => false, 'mesaj' => 'Olcu cok buyuk (max 2000 cm)'], 400);
}

$m = $moduller[$piksel];
$genMm = $genislik * 10;
$yukMm = $yukseklik * 10;

$yatay = (int) ceil($genMm / $m['g']);
$dikey = (int) ceil($yukMm / $m['y']);
$toplamModul = $yatay * $dikey;

$gercekGenMm = $yatay * $m['g'];
$gercekYukMm = $dikey * $m['y'];

$metrekare = round(($gercekGenMm / 1000) * ($gercekYukMm / 1000), 2);

// Ortalama watt (modul basi watt * adet * 0.6 ortalama parlaklik faktoru)
$ortWatt = (int) round($toplamModul * $m['w'] * 0.6);
$maxWatt = $toplamModul * $m['w'];

// Piksel cozunurlugu
$pikselAral = (float) str_replace(['P', ','], ['', '.'], $piksel);
$cozGen = (int) ($gercekGenMm / $pikselAral);
$cozYuk = (int) ($gercekYukMm / $pikselAral);

json_yanit([
    'ok' => true,
    'piksel'          => $piksel,
    'istenen_genislik_cm'  => $genislik,
    'istenen_yukseklik_cm' => $yukseklik,
    'modul_olcu_mm'   => $m['g'] . ' x ' . $m['y'],
    'yatay_modul'     => $yatay,
    'dikey_modul'     => $dikey,
    'toplam_modul'    => $toplamModul,
    'gercek_olcu_cm'  => ($gercekGenMm / 10) . ' x ' . ($gercekYukMm / 10),
    'metrekare'       => $metrekare,
    'cozunurluk'      => $cozGen . ' x ' . $cozYuk,
    'ortalama_watt'   => $ortWatt,
    'max_watt'        => $maxWatt,
    'aciklama'        => 'Bu bir tahmindir. Kesin teklif icin bize ulasin.',
]);
