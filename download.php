<?php
/**
 * TATVAM - Secure Token-Guarded Ebook Download Engine
 * Validates expiration limits, increments access counters, and pipes file bytes securely
 * Supports both single file_path (legacy) and JSON array of multiple files
 */

require_once __DIR__ . '/db.php';

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
$file_index = isset($_GET['file']) ? (int)$_GET['file'] : -1; // -1 = show list

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

    // 4. Resolve file paths (support legacy single string + JSON array)
    $raw_path = $order['file_path'];
    $decoded = json_decode($raw_path, true);
    if (is_array($decoded)) {
        $file_paths = $decoded;
    } else {
        $file_paths = [$raw_path]; // legacy single file
    }

    // 5. If a specific file index is requested, serve that file directly
    if ($file_index >= 0 && isset($file_paths[$file_index])) {
        // Update download count
        $update_stmt = $db->prepare("UPDATE orders SET download_count = download_count + 1 WHERE id = ?");
        $update_stmt->execute([$order['id']]);

        $relative = $file_paths[$file_index];
        $full = __DIR__ . '/' . $relative;

        if (!file_exists($full)) {
            die("Error: File not found on server. Please contact support@tatvam.shop.");
        }

        $file_name = basename($full);
        $mime_type = mime_content_type($full);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($mime_type ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full));
        ob_clean();
        flush();
        readfile($full);
        exit;
    }

    // 6. Show download list page (default when no file index or multiple files)
    // Update download count once for page visit
    $update_stmt = $db->prepare("UPDATE orders SET download_count = download_count + 1 WHERE id = ?");
    $update_stmt->execute([$order['id']]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Download Your E-Book | TATVAM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <link rel="stylesheet" href="styles.css?v=2.3">
</head>
<body style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%); padding:1rem;">
    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <div class="glass-card" style="width:100%; max-width:520px; text-align:center; padding:2.5rem 2rem; border-color:rgba(251,191,36,0.4); box-shadow:var(--shadow-glow-gold); position:relative; z-index:10;">
        <div style="font-size:3rem; color:var(--color-gold); margin-bottom:1rem; display:flex; justify-content:center;">
            <i data-lucide="book-open" style="width:64px; height:64px; filter:drop-shadow(0 0 15px var(--color-gold));"></i>
        </div>
        <h1 class="gradient-gold" style="font-size:1.8rem; margin-bottom:0.5rem;">Your E-Book is Ready!</h1>
        <p style="color:var(--color-text-slate); margin-bottom:2rem; font-size:0.95rem;">
            <strong style="color:#fff;"><?php echo htmlspecialchars($order['title']); ?></strong><br>
            <?php echo count($file_paths); ?> file(s) available for download
        </p>

        <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.5rem;">
            <?php foreach ($file_paths as $i => $path):
                $fname = basename($path);
                $full_check = __DIR__ . '/' . $path;
                $exists = file_exists($full_check);
            ?>
            <a href="download.php?token=<?php echo urlencode($token); ?>&file=<?php echo $i; ?>"
               class="btn btn-primary"
               style="width:100%; justify-content:center; <?php echo !$exists ? 'opacity:0.4; pointer-events:none;' : ''; ?>">
                <i data-lucide="download" style="width:18px; height:18px; margin-right:8px;"></i>
                <?php if (count($file_paths) > 1): ?>
                    File <?php echo ($i+1); ?> — <?php echo htmlspecialchars($fname); ?>
                <?php else: ?>
                    Download E-Book PDF
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <p style="font-size:0.8rem; color:rgba(255,255,255,0.3);">Link valid for 7 days. Download as many times as needed.</p>
        <a href="index.html" style="display:block; margin-top:1rem; font-size:0.85rem; color:var(--color-text-slate);">← Return to TATVAM Store</a>
    </div>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
<?php

} catch (Exception $e) {
    die("Download Execution Error: " . $e->getMessage());
}

