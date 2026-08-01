<?php
/**
 * TATVAM - Cashfree Webhook Handler
 * Receives async payment notifications from Cashfree and updates order status.
 * Configure this URL in Cashfree Dashboard → Developers → Webhooks:
 *   https://tatvam.shop/cashfree-webhook.php
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/smtp-helper.php';
require_once __DIR__ . '/includes/meta-capi-helper.php';

// Only accept POST requests from Cashfree
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Read raw payload
$raw_body  = file_get_contents('php://input');
$payload   = json_decode($raw_body, true);

if (!$payload) {
    http_response_code(400);
    exit('Invalid payload');
}

// ---------------------------------------------------------------
// 1. SIGNATURE VERIFICATION
// Cashfree sends: x-webhook-signature, x-webhook-timestamp headers
// ---------------------------------------------------------------
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
$received_signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if ($timestamp && $received_signature) {
    $message           = $timestamp . $raw_body;
    $expected_signature = base64_encode(hash_hmac('sha256', $message, CASHFREE_SECRET_KEY, true));

    if (!hash_equals($expected_signature, $received_signature)) {
        error_log('Cashfree Webhook: Signature mismatch. Possible spoofing attempt.');
        http_response_code(403);
        exit('Forbidden: Signature mismatch');
    }
}

// ---------------------------------------------------------------
// 2. PROCESS PAYMENT EVENT
// ---------------------------------------------------------------
$event_type    = $payload['type'] ?? '';
$payment_data  = $payload['data'] ?? [];
$order_data    = $payment_data['order'] ?? [];
$payment_info  = $payment_data['payment'] ?? [];

$cf_order_id   = $order_data['order_id'] ?? null;
$payment_status = $payment_info['payment_status'] ?? null;
$cf_payment_id  = $payment_info['cf_payment_id'] ?? null;

// Only process successful payment events
if ($event_type !== 'PAYMENT_SUCCESS_WEBHOOK' || !$cf_order_id || $payment_status !== 'SUCCESS') {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'event' => $event_type]);
    exit;
}

try {
    // Fetch order from DB
    $stmt = $db->prepare("SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id WHERE orders.razorpay_order_id = ?");
    $stmt->execute([$cf_order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        error_log("Cashfree Webhook: Order not found in DB for cf_order_id: $cf_order_id");
        http_response_code(200);
        echo json_encode(['status' => 'order_not_found']);
        exit;
    }

    // Skip if already marked as paid (idempotency)
    if ($order['payment_status'] === 'paid') {
        http_response_code(200);
        echo json_encode(['status' => 'already_processed']);
        exit;
    }

    // Generate secure download token (7-day expiry)
    $download_token = bin2hex(random_bytes(16));
    $token_expiry   = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Update order record
    $update_stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', razorpay_payment_id = ?, download_token = ?, token_expiry = ? WHERE id = ?");
    $update_stmt->execute([
        (string)$cf_payment_id,
        $download_token,
        $token_expiry,
        $order['id'],
    ]);

    $download_link = SITE_URL . '/download.php?token=' . $download_token;

    // Send ebook delivery email
    sendEbookEmail($order['customer_email'], $order['customer_name'], $order['title'], $download_link);

    // Trigger Meta CAPI Purchase event
    sendMetaCapiEvent('Purchase', [
        'email'    => $order['customer_email'],
        'phone'    => $order['customer_phone'],
        'name'     => $order['customer_name'],
        'value'    => $order['amount'],
        'currency' => 'INR',
    ]);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'order_id' => $cf_order_id]);

} catch (Exception $e) {
    error_log('Cashfree Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
