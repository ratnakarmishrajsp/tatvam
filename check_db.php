<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "D:/Documents/tatvam.in/db.php";

// Test 1: exact slug in DB
$testSlugs = [
    'AI digital product income system ',
    'AI digital product income system',
    'ai-digital-product',
    'ai digital product',
    'AI Digital Product Income System'
];

foreach ($testSlugs as $slug) {
    $stmt = $db->prepare("SELECT id, title, slug, price, original_price FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Slug: [" . $slug . "] => Found: " . ($prod ? "YES (Price: " . $prod['price'] . ")" : "NO") . "\n";
}
