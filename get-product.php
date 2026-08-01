<?php
/**
 * TATVAM - Product Details API Endpoint
 * Returns product pricing and details as JSON for landing pages
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'positive-thinking';

try {
    $stmt = $db->prepare("SELECT id, title, slug, price, original_price, cover_image FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode([
            'success'        => true,
            'id'             => $product['id'],
            'title'          => $product['title'],
            'price'          => (float)$product['price'],
            'original_price' => (float)$product['original_price'],
            'cover_image'    => $product['cover_image']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
