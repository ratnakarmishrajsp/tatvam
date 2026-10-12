<?php
/**
 * TATVAM - Traffic & Funnel Analytics Tracker Endpoint
 * Logs pageviews, Buy Now button clicks, and abandoned drop-offs.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$action = filter_input(INPUT_POST, 'event_type', FILTER_SANITIZE_SPECIAL_CHARS) ?? filter_input(INPUT_GET, 'event_type', FILTER_SANITIZE_SPECIAL_CHARS);
$slug   = filter_input(INPUT_POST, 'page_slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? filter_input(INPUT_GET, 'page_slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'positive-thinking';

if (in_array($action, ['pageview', 'buy_click', 'heartbeat'])) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $stmt = $db->prepare("INSERT INTO site_analytics (event_type, page_slug, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$action, $slug, $ip, $agent]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid event type']);
}
