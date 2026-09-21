<?php
/**
 * TATVAM - Secure Token-Guarded Ebook Download Engine
 * Validates expiration limits, increments access counters, and pipes file bytes securely.
 * Supports multi-file products (Main Guide + Bonus Toolkits).
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

    // 4. Retrieve structured product files
    $files = getProductFiles($order['file_path'], $order['title']);

    if (empty($files)) {
        die("Error: No downloadable files associated with this product.");
    }

    // Determine target file
    $file_idx = isset($_GET['file']) && is_numeric($_GET['file']) ? (int)$_GET['file'] : null;

    // If multiple files exist and no specific file index was requested, render Multi-File Download Hub
    if ($file_idx === null && count($files) > 1) {
        ?>
        <!DOCTYPE html>
        <html lang="hi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Your Downloads | <?php echo htmlspecialchars($order['title']); ?> | TATVAM</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
            <script src="https://unpkg.com/lucide@latest" defer></script>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%); padding: 1.5rem 1rem;">
            <div class="bg-canvas">
                <div class="aurora aurora-1" style="opacity: 0.15;"></div>
                <div class="aurora aurora-2" style="opacity: 0.15;"></div>
            </div>
            <div class="noise-overlay"></div>
            
            <div class="glass-card" style="width: 100%; max-width: 580px; text-align: center; padding: var(--space-lg); border-color: rgba(251, 191, 36, 0.4); box-shadow: var(--shadow-glow-gold); position: relative; z-index: 10;">
                <div style="font-size: 3.5rem; color: var(--color-gold); margin-bottom: var(--space-sm); display: flex; justify-content: center;">
                    <i data-lucide="folder-down" style="width: 60px; height: 60px; filter: drop-shadow(0 0 15px var(--color-gold));"></i>
                </div>
                
                <h1 class="gradient-gold" style="font-size: 1.85rem; margin-bottom: var(--space-xs); line-height: 1.2;">Aapke Downloads Ready Hain!</h1>
                <p style="font-size: 0.95rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">
                    <strong><?php echo htmlspecialchars($order['title']); ?></strong> ke sabhi included guides and toolkits niche diye gaye hain.
                </p>

                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: var(--space-md); text-align: left;">
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-gold); font-weight: 700; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="layers" style="width: 14px; height: 14px;"></i> Included Materials (<?php echo count($files); ?> Files)
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($files as $idx => $f): ?>
                            <?php 
                                $fext = strtoupper(pathinfo($f['file'], PATHINFO_EXTENSION));
                                if (empty($fext)) $fext = 'FILE';
                            ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); padding: 10px 14px; border-radius: var(--radius-sm); gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                    <div style="background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.3); color: var(--color-gold); border-radius: 6px; padding: 6px 10px; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                                        <?php echo htmlspecialchars($fext); ?>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="color: #fff; font-weight: 600; font-size: 0.92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($f['title']); ?>
                                        </div>
                                        <div style="color: var(--color-text-slate); font-size: 0.75rem;">
                                            Part <?php echo ($idx + 1); ?> of <?php echo count($files); ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="download.php?token=<?php echo urlencode($token); ?>&file=<?php echo $idx; ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px;">
                                    <i data-lucide="download" style="width: 14px; height: 14px;"></i> Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="font-size: 0.8rem; color: var(--color-text-slate); margin-bottom: var(--space-md);">
                    <i data-lucide="clock" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 4px; color: var(--color-gold);"></i>
                    Yeh download links agle <strong>7 dino</strong> tak valid hain. Aap jab chahein tab download kar sakte hain.
                </div>

                <a href="index.html" class="btn btn-secondary" style="width: 100%;">
                    <i data-lucide="home"></i> Return to TATVAM Store
                </a>
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

    // Target a specific file or the primary file (index 0)
    $target_index = ($file_idx !== null && isset($files[$file_idx])) ? $file_idx : 0;
    $target_file = $files[$target_index]['file'];
    $target_title = $files[$target_index]['title'];

    // 5. Update download metrics
    $update_stmt = $db->prepare("UPDATE orders SET download_count = download_count + 1 WHERE id = ?");
    $update_stmt->execute([$order['id']]);

    // 6. Expose Ebook File Bytes securely
    $relative_file_path = $target_file;
    $full_file_path = __DIR__ . '/' . $relative_file_path;

    if (!file_exists($full_file_path)) {
        // Localhost Sandboxed Sandbox Helper: Display a beautiful HTML page instead of raw PDF stream
        $file_name = basename($relative_file_path);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <p style="margin-bottom: 0.5rem; color: var(--color-text-white);"><strong>Item Title:</strong> <?php echo htmlspecialchars($target_title); ?></p>
                    <p style="margin-bottom: 0.5rem; color: var(--color-text-white);"><strong>File Name:</strong> <?php echo htmlspecialchars($file_name); ?></p>
                    <p style="color: var(--color-text-white);"><strong>Status:</strong> Sandbox Simulation Mode 🛠️</p>
                </div>

                <p style="font-size: 0.95rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">Yaha click karne par file download simulate ho gayi hai. Production mode me customer ko real file download milegi.</p>
                
                <?php if (count($files) > 1): ?>
                    <a href="download.php?token=<?php echo urlencode($token); ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 8px;"><i data-lucide="folder-down"></i> Back to Download Hub</a>
                <?php endif; ?>
                <a href="index.html" class="btn btn-secondary" style="width: 100%;"><i data-lucide="home"></i> Return to TATVAM Store</a>
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
    $mime_type = function_exists('mime_content_type') ? @mime_content_type($full_file_path) : false;

    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($mime_type ? $mime_type : 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($full_file_path));
    
    // Clear buffer
    if (ob_get_level()) {
        ob_clean();
    }
    flush();
    
    readfile($full_file_path);
    exit;

} catch (Exception $e) {
    die("Download Execution Error: " . $e->getMessage());
}
