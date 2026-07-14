<?php
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '5G');
// Target URL (change this to your target)
$url = 'https://www.eshakti.com';

// Create folder to save images
$saveFolder = '/home/inhouse/public_html/imageDownload';
if (!file_exists($saveFolder)) {
    mkdir($saveFolder, 0755, true);
}

// Get the HTML content
$html = file_get_contents($url);

// Match all <img> tags and extract the src
preg_match_all('/<img[^>]+src="([^">]+)"/i', $html, $matches);
$imageUrls = $matches[1];

// Load base URL for resolving relative paths
$baseUrlParts = parse_url($url);
$base = $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'];

foreach ($imageUrls as $imgUrl) {
    // Resolve relative URLs
    $imgUrl = trim($imgUrl);
    if (!preg_match('/^https?:\/\//', $imgUrl)) {
        $imgUrl = rtrim($base, '/') . '/' . ltrim($imgUrl, '/');
    }

    $imgName = basename(parse_url($imgUrl, PHP_URL_PATH));

    $imgPath = $saveFolder . '/' . $imgName;

    // Download and save the image
    $imgData = @file_get_contents($imgUrl);
    if ($imgData !== false) {
        file_put_contents($imgPath, $imgData);
        echo "Downloaded: $imgName\n";
    } else {
        echo "Failed to download: $imgUrl\n";
    }
}
