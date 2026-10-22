<?php
function generateQR(string $text, string $filename): string|false
{
    $libPath = __DIR__ . '/../libs/phpqrcode/qrlib.php';

    if (!function_exists('imagecreate')) {
        throw new Exception("GD library is not enabled on the server. Please enable it in php.ini.");
    }

    if (!file_exists($libPath)) {
        return false; 
    }

    require_once $libPath;

    $dir = __DIR__ . '/../qrcodes/';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $outFile = $dir . $filename . '.png';
    QRcode::png($text, $outFile, QR_ECLEVEL_M, 8, 2);

    if (file_exists($outFile) && filesize($outFile) > 0) {
        return 'qrcodes/' . $filename . '.png';
    }

    return false;
}