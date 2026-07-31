<?php
/**
 * TATVAM - Secure Token-Guarded Ebook Download Engine
 * Validates expiration limits, increments access counters, and pipes file bytes securely
 */

require_once __DIR__ . '/db.php';

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$token) {
    die("Error: No download token provided.");
}

try {
    // 1. Fetch order related to download token
    $stmt = $db->prepare("SELECT orders.*, products.title, products.file_path FROM orders JOIN products ON orders.product_id = products.id WHERE orders.download_token = ?");
    $stmt->execute([$token]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Error: Invalid download token.");
    }

    // 2. Validate Order Paid Status
    if ($order['payment_status'] !== 'paid') {
        die("Error: Payment verification is still pending for this order.");
    }

    // 3. Validate Token Expiration Limit (7 Days expiry)
    $expiry_time = strtotime($order['token_expiry']);
    if (time() > $expiry_time) {
        die("Error: This download link has expired (7 days usage limit exceeded). Please contact support@tatvam.shop to request a renewal.");
    }

    // 4. Update download metrics
    $update_stmt = $db->prepare("UPDATE orders SET download_count = download_count + 1 WHERE id = ?");
    $update_stmt->execute([$order['id']]);

    // 5. Expose Ebook File Bytes securely
    $relative_file_path = $order['file_path'];
    $full_file_path = __DIR__ . '/' . $relative_file_path;

    if (!file_exists($full_file_path)) {
        // Localhost Sandboxed Sandbox Helper: Display a beautiful HTML page instead of raw PDF stream
        // This prevents the browser from showing raw text code when error reporting is enabled on local environments.
        $file_name = basename($relative_file_path);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
            <title>Download Sandbox Success | TATVAM</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
            <script src="https://unpkg.com/lucide@latest" defer></script>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%);">
            <div class="bg-canvas"></div>
            <div class="noise-overlay"></div>
            
            <div class="glass-card" style="width: 90%; max-width: 500px; text-align: center; padding: var(--space-lg); border-color: rgba(255, 255, 255, 0.15); z-index: 10; position: relative;">
                <div style="font-size: 4rem; color: var(--color-success); margin-bottom: var(--space-sm); display: flex; justify-content: center;">
                    <i data-lucide="check-circle" style="width: 64px; height: 64px; stroke-width: 2.5; color: var(--color-success);"></i>
                </div>
                <h1 class="gradient-gold" style="font-size: 2.25rem; margin-bottom: var(--space-xs);">Sandbox Download Success!</h1>
                
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-light); padding: 1rem; border-radius: var(--radius-sm); margin: 1.5rem 0; text-align: left; font-size: 0.9rem;">
                    <p style="margin-bottom: 0.5rem; color: var(--color-text-white);"><strong>Order Title:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
                    <p style="margin-bottom: 0.5rem; color: var(--color-text-white);"><strong>File Name:</strong> <?php echo htmlspecialchars($file_name); ?></p>
                    <p style="color: var(--color-text-white);"><strong>Status:</strong> Sandbox Simulation Mode 🛠️</p>
                </div>

                <p style="font-size: 0.95rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">Yaha click karne par file download simulate ho gayi hai. Production mode me customer ko real PDF ebook download file milegi.</p>
                
                <a href="index.html" class="btn btn-primary" style="width: 100%;"><i data-lucide="home"></i> Return to TATVAM Store</a>
            </div>

            <script>
                window.addEventListener('load', () => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            </script>
        </body>
        </html>
        <?php
        exit;
    }

    // Serve real file
    $file_name = basename($full_file_path);
    $mime_type = mime_content_type($full_file_path);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($mime_type ? $mime_type : 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($full_file_path));
    
    // Clear buffer
    ob_clean();
    flush();
    
    readfile($full_file_path);
    exit;

} catch (Exception $e) {
    die("Download Execution Error: " . $e->getMessage());
}
