<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use League\CommonMark\Node\Block\Document;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

   // HTML içeriğini al
$htmlContent = Http::get('https://www.fullhdfilmizlesene.de/filmrobot/?tarz=&tur=&yil=&imdb=6x&hd=')->body();

// DOMDocument oluştur
$dom = new DOMDocument();
$dom->loadHTML($htmlContent, LIBXML_NOERROR);

// DOMXPath oluştur
$xpath = new DOMXPath($dom);

// Tüm film öğelerini seç
$filmElements = $xpath->query('//li[@class="film"]');

// Her film öğesi için adı ve URL'si al
foreach ($filmElements as $filmElement) {
    // Film adını ve URL'sini seç
    $filmName = $xpath->query('.//a[@class="tt"]', $filmElement)->item(0)->nodeValue;
    $filmURL = $xpath->query('.//a[@class="tt"]/@href', $filmElement)->item(0)->nodeValue;

    // Sonuçları yazdır
    echo "Film Adı: " . $filmName . "\n";
    echo "Film URL'si: " . $filmURL . "\n\n";
}
});
