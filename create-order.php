<?php
/**
 * TATVAM - Secure Cashfree Order Generation API
 * Registers pending transaction and returns Cashfree payment_session_id
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize post variables
$customer_name  = trim($_POST['name'] ?? filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$customer_email = filter_var($_POST['email'] ?? filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL), FILTER_VALIDATE_EMAIL);
$customer_phone = trim($_POST['phone'] ?? filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$product_slug   = trim($_POST['product_slug'] ?? filter_input(INPUT_POST, 'product_slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

if (!$customer_name || !$customer_email || !$customer_phone || !$product_slug) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all details correctly.']);
    exit;
}


try {
    // 1. Fetch target product details
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$product_slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Selected product not found.']);
        exit;
    }

    $amount = (float)$product['price'];

    // 2. Generate unique order ID for Cashfree
    $cf_order_id = 'tatvam_' . time() . '_' . bin2hex(random_bytes(4));

    // 3. Determine Cashfree API URL based on environment
    $is_sandbox = (CASHFREE_ENV === 'TEST');
    $api_base   = $is_sandbox
        ? 'https://sandbox.cashfree.com/pg'
        : 'https://api.cashfree.com/pg';

    $payment_session_id = null;

    // 4. Call Cashfree Orders API
    $payload = json_encode([
        'order_id'       => $cf_order_id,
        'order_amount'   => $amount,
        'order_currency' => 'INR',
        'customer_details' => [
            'customer_id'    => 'cust_' . preg_replace('/[^a-zA-Z0-9]/', '', $customer_email),
            'customer_name'  => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => preg_replace('/[^0-9]/', '', $customer_phone),
        ],
        'order_meta' => [
            'return_url'   => SITE_URL . '/thank-you.php?order_id={order_id}',
            'notify_url'   => SITE_URL . '/cashfree-webhook.php',
        ],
        'order_note' => 'TATVAM Ebook: ' . $product['title'],
    ]);

    $ch = curl_init($api_base . '/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY,
        'x-api-version: 2023-08-01',
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $cf_data = json_decode($response, true);
        $payment_session_id = $cf_data['payment_session_id'] ?? null;
        if (!$payment_session_id) {
            error_log("Cashfree: payment_session_id missing. Response: $response");
            echo json_encode(['success' => false, 'message' => 'Payment session creation failed. Please retry.']);
            exit;
        }
    } else {
        error_log("Cashfree Order API Failed. HTTP: $http_code. Response: $response");
        echo json_encode(['success' => false, 'message' => 'Payment gateway error. Please retry.']);
        exit;
    }

    // 5. Register pending order in database with exact local timestamp (IST)
    $current_now = date('Y-m-d H:i:s');
    $order_stmt = $db->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, product_id, amount, payment_status, razorpay_order_id, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)");
    $order_stmt->execute([
        $customer_name,
        $customer_email,
        $customer_phone,
        $product['id'],
        $amount,
        $cf_order_id,
        $current_now,
    ]);

    $db_order_id = $db->lastInsertId();

    echo json_encode([
        'success'            => true,
        'sandbox'            => $is_sandbox,
        'db_order_id'        => $db_order_id,
        'cf_order_id'        => $cf_order_id,
        'payment_session_id' => $payment_session_id,
        'environment'        => CASHFREE_ENV,
        'product_name'       => $product['title'],
        'customer_name'      => $customer_name,
        'customer_email'     => $customer_email,
        'customer_phone'     => $customer_phone,
        'cover_image'        => $product['cover_image'],
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Execution Error: ' . $e->getMessage()]);
}
