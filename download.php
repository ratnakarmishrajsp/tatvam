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
    $stmt = $db->prepare("SELECT orders.*, products.title, products.file_path, products.slug as product_slug FROM orders JOIN products ON orders.product_id = products.id WHERE orders.download_token = ?");
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
                                if (empty($fext)) $fext = 'PDF';
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

    // 6. Robust File Resolution
    $resolved_file_path = null;
    $baseDir = __DIR__;

    // Check direct path
    if (!empty($target_file) && file_exists($baseDir . '/' . $target_file)) {
        $resolved_file_path = $baseDir . '/' . $target_file;
    }

    // Check in files/ directory without uploads/
    if (!$resolved_file_path && !empty($target_file)) {
        $baseName = basename($target_file);
        if (file_exists($baseDir . '/files/' . $baseName)) {
            $resolved_file_path = $baseDir . '/files/' . $baseName;
        }
    }

    // Smart Resolution for Sanskar 30
    $isSanskar = stripos($order['title'], 'sanskar') !== false 
              || stripos($order['product_slug'] ?? '', 'sanskar') !== false
              || stripos($target_file, 'sanskar') !== false 
              || stripos($target_title, 'sanskar') !== false;

    if (!$resolved_file_path && $isSanskar) {
        if ($target_index === 1 || stripos($target_title, 'toolkit') !== false || stripos($target_title, 'parent') !== false || stripos($target_title, 'activity') !== false) {
            $candidate = $baseDir . '/files/sanskar_30_parent_toolkit.pdf';
            if (file_exists($candidate)) $resolved_file_path = $candidate;
        } else {
            $candidate = $baseDir . '/files/sanskar_30_main_ebook.pdf';
            if (file_exists($candidate)) $resolved_file_path = $candidate;
        }
    }

    // Match upload pattern if from admin upload (file_1_ is main, file_2_ is toolkit)
    if (!$resolved_file_path && preg_match('/file_1_/i', $target_file)) {
        if (file_exists($baseDir . '/files/sanskar_30_main_ebook.pdf')) {
            $resolved_file_path = $baseDir . '/files/sanskar_30_main_ebook.pdf';
        }
    }
    if (!$resolved_file_path && preg_match('/file_2_/i', $target_file)) {
        if (file_exists($baseDir . '/files/sanskar_30_parent_toolkit.pdf')) {
            $resolved_file_path = $baseDir . '/files/sanskar_30_parent_toolkit.pdf';
        }
    }

    // Standard product fallbacks
    if (!$resolved_file_path) {
        $catalogueMap = [
            'positive-thinking' => 'files/power_of_calm_hindi.pdf',
            'stress-worry'      => 'files/anxiety_relief_hindi.pdf',
            'habit-freedom'     => 'files/ultimate_discipline_hindi.pdf',
            'wealth-mindset'    => 'files/wealth_principles_hindi.pdf',
            'mega-bundle'       => 'files/tattvam_mega_bundle.zip',
        ];
        $slug = strtolower($order['product_slug'] ?? '');
        if (isset($catalogueMap[$slug]) && file_exists($baseDir . '/' . $catalogueMap[$slug])) {
            $resolved_file_path = $baseDir . '/' . $catalogueMap[$slug];
        }
    }

    // If file still not found on disk, show a friendly support assistance card
    if (!$resolved_file_path || !file_exists($resolved_file_path)) {
        ?>
        <!DOCTYPE html>
        <html lang="hi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Download Help | TATVAM Support</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
            <script src="https://unpkg.com/lucide@latest" defer></script>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%); padding: 1.5rem 1rem;">
            <div class="glass-card" style="width: 100%; max-width: 500px; text-align: center; padding: var(--space-lg); border-color: rgba(251, 191, 36, 0.4); position: relative; z-index: 10;">
                <div style="font-size: 3.5rem; color: var(--color-gold); margin-bottom: var(--space-sm); display: flex; justify-content: center;">
                    <i data-lucide="help-circle" style="width: 60px; height: 60px;"></i>
                </div>
                <h1 class="gradient-gold" style="font-size: 1.85rem; margin-bottom: var(--space-xs);">फाइल डाउनलोड सहायता</h1>
                <p style="font-size: 0.95rem; margin-bottom: var(--space-md); color: var(--color-text-slate);">
                    आपकी ई-बुक फाइल तैयार हो रही है। यदि डाउनलोड तुरंत शुरू न हो, तो कृपया नीचे दिए गए लिंक से तुरंत WhatsApp या Email सहायता लें।
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                    <a href="mailto:support@tatvam.shop?subject=Download%20Help%20Order%20<?php echo urlencode($order['id']); ?>" class="btn btn-primary" style="width: 100%;">
                        <i data-lucide="mail"></i> Email Support (support@tatvam.shop)
                    </a>
                    <a href="javascript:location.reload()" class="btn btn-secondary" style="width: 100%;">
                        <i data-lucide="refresh-cw"></i> Retry Download (पुनः प्रयास करें)
                    </a>
                </div>
            </div>
            <script>window.addEventListener('load', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });</script>
        </body>
        </html>
        <?php
        exit;
    }

    // 7. Serve Real File Bytes directly to browser
    $clean_filename = 'Ebook.pdf';
    if ($isSanskar) {
        $clean_filename = ($target_index === 1 || stripos($target_title, 'toolkit') !== false || stripos($target_title, 'parent') !== false)
            ? 'SANSKAR 30 - Parent and Activity Toolkit.pdf'
            : 'SANSKAR 30 - 30 Days of Good Habits and Strong Values.pdf';
    } else {
        $clean_filename = preg_replace('/[^A-Za-z0-9_\-\. ]/', '', $target_title);
        $ext = pathinfo($resolved_file_path, PATHINFO_EXTENSION) ?: 'pdf';
        if (!str_ends_with(strtolower($clean_filename), '.' . strtolower($ext))) {
            $clean_filename .= '.' . $ext;
        }
    }

    $mime_type = function_exists('mime_content_type') ? @mime_content_type($resolved_file_path) : 'application/pdf';
    if (!$mime_type) $mime_type = 'application/pdf';
    $filesize = filesize($resolved_file_path);

    // Clear all existing buffers to prevent corrupted PDF streams
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $clean_filename . '"; filename*="UTF-8\'\'' . rawurlencode($clean_filename) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $filesize);

    readfile($resolved_file_path);
    exit;

} catch (Exception $e) {
    die("Download Execution Error: " . $e->getMessage());
}
