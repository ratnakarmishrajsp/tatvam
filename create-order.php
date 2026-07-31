<?php
/**
 * TATVAM - Secure Razorpay Order Generation API
 * Registers pending transaction and returns checkout parameters
 */

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize post variables
$customer_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$customer_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$customer_phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
$product_slug = filter_input(INPUT_POST, 'product_slug', FILTER_SANITIZE_SPECIAL_CHARS);

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
    $amount_in_paisa = (int)($amount * 100); // Razorpay calculates in Paisa

    // 2. Localhost Sandbox Detection
    // If credentials are left at default test placeholders, fallback to sandbox simulation
    $is_sandbox = (RAZORPAY_KEY_ID === 'rzp_test_XXXXXXXXXXXXXX');

    $razorpay_order_id = 'order_sim_' . bin2hex(random_bytes(8)); // Simulated Order ID

    if (!$is_sandbox) {
        // Run secure cURL query to Razorpay API for live order creation
        $url = 'https://api.razorpay.com/v1/orders';
        $payload = json_encode([
            'amount' => $amount_in_paisa,
            'currency' => 'INR',
            'receipt' => 'receipt_order_' . time(),
            'payment_capture' => 1
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            $razorpay_order_id = $data['id'];
        } else {
            // Log details and return failure
            error_log("Razorpay Order API Failed. HTTP Code: $http_code. Response: $response");
            echo json_encode(['success' => false, 'message' => 'Payment processor generation failed. Continuing via fallback.']);
            $is_sandbox = true; // Fallback to sandbox simulation
        }
    }

    // 3. Register pending order inside database
    $order_stmt = $db->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, product_id, amount, payment_status, razorpay_order_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $order_stmt->execute([
        $customer_name,
        $customer_email,
        $customer_phone,
        $product['id'],
        $amount,
        $razorpay_order_id
    ]);
    
    // Get last inserted order ID
    $db_order_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'sandbox' => $is_sandbox,
        'db_order_id' => $db_order_id,
        'razorpay_order_id' => $razorpay_order_id,
        'key' => RAZORPAY_KEY_ID,
        'amount' => $amount_in_paisa,
        'product_name' => $product['title'],
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'cover_image' => $product['cover_image']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Execution Error: ' . $e->getMessage()]);
}
