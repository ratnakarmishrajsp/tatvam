<?php
/**
 * TATVAM - Order Confirmation and Signature Verification Endpoint
 * Confirms payment, generates secure download links, triggers email delivery, and displays receipt
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/smtp-helper.php';
require_once __DIR__ . '/includes/meta-capi-helper.php';

$payment_verified = false;
$order_id = null;
$customer_name = "";
$customer_email = "";
$product_title = "";
$download_link = "";

// 1. Cashfree Payment Verification
// Cashfree redirects back with ?order_id=tatvam_xxx in the URL (set in return_url)
if (!empty($_GET['order_id']) || !empty($_POST['cf_order_id'])) {
    $cf_order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_SPECIAL_CHARS)
                ?? filter_input(INPUT_POST, 'cf_order_id', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($cf_order_id) {
        // Fetch order from DB
        $stmt = $db->prepare("SELECT orders.*, products.title, products.slug FROM orders JOIN products ON orders.product_id = products.id WHERE orders.razorpay_order_id = ?");
        $stmt->execute([$cf_order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // If already paid, just display the confirmation
            if ($order['payment_status'] === 'paid') {
                $payment_verified = true;
                $customer_name  = $order['customer_name'];
                $customer_email = $order['customer_email'];
                $product_title  = $order['title'];
                $download_link  = SITE_URL . "/download.php?token=" . $order['download_token'];
            } else {
                // Verify payment status via Cashfree API
                $api_base = (CASHFREE_ENV === 'TEST')
                    ? 'https://sandbox.cashfree.com/pg'
                    : 'https://api.cashfree.com/pg';

                $ch = curl_init($api_base . '/orders/' . $cf_order_id . '/payments');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'x-client-id: ' . CASHFREE_APP_ID,
                    'x-client-secret: ' . CASHFREE_SECRET_KEY,
                    'x-api-version: 2023-08-01',
                ]);
                $response  = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code === 200) {
                    $payments = json_decode($response, true);
                    // Check if any payment has SUCCESS status
                    $cf_payment_id = null;
                    foreach ((array)$payments as $payment) {
                        if (($payment['payment_status'] ?? '') === 'SUCCESS') {
                            $payment_verified = true;
                            $cf_payment_id = $payment['cf_payment_id'] ?? null;
                            break;
                        }
                    }
                } else {
                    error_log("Cashfree payment verify failed. HTTP: $http_code. Response: $response");
                }

                if ($payment_verified) {
                    // Generate a secure download token (expires in 7 days)
                    $download_token = bin2hex(random_bytes(16));
                    $token_expiry   = date('Y-m-d H:i:s', strtotime('+7 days'));

                    // Update order record with payment details
                    $update_stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', razorpay_payment_id = ?, download_token = ?, token_expiry = ? WHERE id = ?");
                    $update_stmt->execute([
                        $cf_payment_id ?? ('cf_pay_' . bin2hex(random_bytes(6))),
                        $download_token,
                        $token_expiry,
                        $order['id'],
                    ]);

                    // Prepare order attributes for rendering
                    $customer_name  = $order['customer_name'];
                    $customer_email = $order['customer_email'];
                    $product_title  = $order['title'];
                    $download_link  = SITE_URL . "/download.php?token=" . $download_token;

                    // Send email notification to user
                    sendEbookEmail($customer_email, $customer_name, $product_title, $download_link);

                    // Trigger Meta CAPI "Purchase" event
                    sendMetaCapiEvent('Purchase', [
                        'email'    => $customer_email,
                        'phone'    => $order['customer_phone'],
                        'name'     => $customer_name,
                        'value'    => $order['amount'],
                        'currency' => 'INR',
                    ]);
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Order Confirmed | TATVAM</title>
    
    <?php include_once __DIR__ . '/includes/meta-pixel-header.php'; ?>
    <?php if ($payment_verified): ?>
    <script>
        if (typeof fbq === 'function') {
            fbq('track', 'Purchase', {
                value: <?php echo (float)($order['amount'] ?? 199.00); ?>,
                currency: 'INR',
                content_name: '<?php echo addslashes($product_title); ?>'
            });
        }
    </script>
    <?php endif; ?>

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Canvas Confetti CDN -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>

    <!-- Master CSS -->
    <link rel="stylesheet" href="styles.css?v=2.3">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%); overflow-x: hidden;">

    <!-- BACKGROUND LIGHTING -->
    <div class="bg-canvas">
        <div class="aurora aurora-1" style="opacity: 0.15;"></div>
        <div class="aurora aurora-2" style="opacity: 0.15;"></div>
    </div>

    <div class="glass-card thank-you-card" style="border-color: rgba(251, 191, 36, 0.4); box-shadow: var(--shadow-glow-gold); position: relative; z-index: 10;">
        <?php if ($payment_verified): ?>
            <div style="font-size: 3.5rem; color: var(--color-gold); margin-bottom: var(--space-sm);">
                <i data-lucide="check-circle" style="width: 64px; height: 64px; filter: drop-shadow(0 0 15px var(--color-gold));"></i>
            </div>
            <h1 class="gradient-gold">Payment Successful!</h1>
            <p style="font-size: 1.1rem; margin-bottom: var(--space-md); color: var(--color-text-white);">Dhanyawad, <strong><?php echo htmlspecialchars($customer_name); ?></strong>! Aapka order update ho gaya hai.</p>
            
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: var(--space-md); text-align: left;">
                <p style="margin-bottom: 0.5rem; font-size: 0.95rem; color: var(--color-text-slate);"><strong>Product:</strong> <span style="color: var(--color-text-white); font-weight: 500;"><?php echo htmlspecialchars($product_title); ?></span></p>
                <p style="margin-bottom: 0.5rem; font-size: 0.95rem; color: var(--color-text-slate);"><strong>Email:</strong> <span style="color: var(--color-text-white); font-weight: 500;"><?php echo htmlspecialchars($customer_email); ?></span></p>
                <p style="font-size: 0.95rem; color: var(--color-text-slate);"><strong>Link Expiration:</strong> <span style="color: var(--color-gold); font-weight: 500;">7 Days (Aap unlimited times download kar sakte hain)</span></p>
            </div>

            <p style="font-size: 0.95rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">Humne download link aapke registered email address par send kar di hai. Agar email inbox me na dikhe, to please <strong>Spam folder</strong> check karein.</p>
            
            <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                <a href="<?php echo htmlspecialchars($download_link); ?>" class="btn btn-primary" style="width: 100%;"><i data-lucide="download"></i> Instant Download Ebook</a>
                <a href="index.html" class="btn btn-secondary" style="width: 100%;">Return to Store</a>
            </div>
        <?php else: ?>
            <div style="font-size: 3.5rem; color: #EF4444; margin-bottom: var(--space-sm);">
                <i data-lucide="alert-triangle" style="width: 64px; height: 64px; filter: drop-shadow(0 0 15px #EF4444);"></i>
            </div>
            <h1 style="color: #EF4444;">Verification Failed</h1>
            <p style="font-size: 1.1rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">Aapke payment details properly verify nahi ho sake. Agar aapka bank account se money deduct ho chuki hai, to please support desk par issue notify karein.</p>
            
            <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                <a href="mailto:support@tatvam.shop" class="btn btn-primary" style="width: 100%;"><i data-lucide="mail"></i> Email Support Desk</a>
                <a href="index.html" class="btn btn-secondary" style="width: 100%;">Return to Store</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        window.addEventListener('load', () => {
            lucide.createIcons();
            // Trigger beautiful success confetti burst
            if (typeof confetti !== 'undefined' && <?php echo $payment_verified ? 'true' : 'false'; ?>) {
                const duration = 3 * 1000;
                const end = Date.now() + duration;

                (function frame() {
                    confetti({
                        particleCount: 3,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 },
                        colors: ['#FFE896', '#FBBF24', '#7C3AED', '#3B82F6']
                    });
                    confetti({
                        particleCount: 3,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 },
                        colors: ['#FFE896', '#FBBF24', '#7C3AED', '#3B82F6']
                    });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
            }
        });
    </script>
</body>
</html>
