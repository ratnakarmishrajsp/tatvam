<?php
/**
 * TATVAM - Refined Modular SaaS Admin Dashboard
 * Modules / Tabs:
 * 1. 📊 Executive Overview (KPIs & Sales Chart)
 * 2. 💰 Financial P&L Tracker (Manual Date Add/Edit, Daily Ad Spend, Net Profit & ROAS)
 * 3. 🛍️ Order Pipeline (Search, Filters, Direct WhatsApp Recovery & Thank-You Link Buttons)
 * 4. 📦 Manage E-Books (Dynamic product creator & Multi-PDF upload)
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/smtp-helper.php';
require_once __DIR__ . '/../includes/meta-capi-helper.php';

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Tatvam@2025');

$authenticated = false;
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    $authenticated = true;
}

// -------------------------------------------------------------
// 1. AUTHENTICATION HANDLERS
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_auth'] = true;
        $authenticated = true;
        header('Location: index.php');
        exit;
    } else {
        $login_error = "Invalid username or password credentials.";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($authenticated && isset($_GET['action']) && $_GET['action'] === 'reset_test_data') {
    try {
        $db->exec("DELETE FROM orders");
        $db->exec("DELETE FROM daily_calculations");
        header('Location: index.php?msg=reset_done');
        exit;
    } catch (Exception $e) {
        $login_error = "Reset Error: " . $e->getMessage();
    }
}

// -------------------------------------------------------------
// 2. DAILY P&L / AD SPEND SAVER (MANUAL DATE ENTRY & UPDATE)
// -------------------------------------------------------------
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_ad_spend') {
    header('Content-Type: application/json');
    $date     = filter_input(INPUT_POST, 'calc_date', FILTER_SANITIZE_SPECIAL_CHARS);
    $ad_spend = (float)($_POST['ad_spend'] ?? 0);
    $notes    = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($date) {
        try {
            $stmt = $db->prepare("INSERT INTO daily_calculations (calc_date, ad_spend, notes) VALUES (?, ?, ?)
                                  ON CONFLICT(calc_date) DO UPDATE SET ad_spend = EXCLUDED.ad_spend, notes = EXCLUDED.notes");
            $stmt->execute([$date, $ad_spend, $notes]);
            echo json_encode(['success' => true, 'message' => 'Record saved successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Valid date is required.']);
    }
    exit;
}

// -------------------------------------------------------------
// 3. MANUAL RESEND / RECOVERY EMAIL HANDLER
// -------------------------------------------------------------
if ($authenticated && isset($_GET['action']) && $_GET['action'] === 'resend_email' && isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id WHERE orders.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order && $order['payment_status'] === 'paid') {
        $token = $order['download_token'];
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            $db->prepare("UPDATE orders SET download_token = ?, token_expiry = ? WHERE id = ?")
               ->execute([$token, date('Y-m-d H:i:s', strtotime('+7 days')), $order['id']]);
        }
        $link = SITE_URL . "/download.php?token=" . $token;
        sendEbookEmail($order['customer_email'], $order['customer_name'], $order['title'], $link);
        header('Location: index.php?tab=orders-tab&msg=email_sent');
        exit;
    }
}

// -------------------------------------------------------------
// 4. ADD & EDIT PRODUCT HANDLERS
// -------------------------------------------------------------
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $title          = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $slug           = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);
    $category       = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
    $description    = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $price          = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price'];

    $cover_image_path = 'assets/book-cover.jpg';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $img_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($img_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $dest_dir = __DIR__ . '/../assets/uploads/';
            if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);
            $new_name = uniqid('cover_', true) . '.' . $img_ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest_dir . $new_name)) {
                $cover_image_path = 'assets/uploads/' . $new_name;
            }
        }
    }

    $file_paths = [];
    if (!empty($_FILES['ebook_file']['name'][0])) {
        $dest_dir = __DIR__ . '/../files/uploads/';
        if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

        foreach ($_FILES['ebook_file']['tmp_name'] as $i => $tmp) {
            if ($_FILES['ebook_file']['error'][$i] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['ebook_file']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($file_ext, ['pdf', 'zip'])) {
                    $new_name = uniqid('ebook_', true) . '.' . $file_ext;
                    if (move_uploaded_file($tmp, $dest_dir . $new_name)) {
                        $file_paths[] = 'files/uploads/' . $new_name;
                    }
                }
            }
        }
    }

    $file_path_str = !empty($file_paths) ? json_encode($file_paths) : 'files/power_of_calm_hindi.pdf';

    if (!empty($title) && !empty($slug)) {
        try {
            $stmt = $db->prepare("INSERT INTO products (title, slug, price, original_price, file_path, category, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $price, $original_price, $file_path_str, $category, $description, $cover_image_path]);
            header('Location: index.php?tab=products-tab&msg=added');
            exit;
        } catch (Exception $e) {
            $product_error = "Error adding product: " . $e->getMessage();
        }
    }
}

if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $edit_id        = (int)($_POST['edit_id'] ?? 0);
    $title          = trim($_POST['title'] ?? '');
    $slug           = trim($_POST['slug'] ?? '');
    $category       = trim($_POST['category'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $price          = (float)($_POST['price'] ?? 0);
    $original_price = (float)($_POST['original_price'] ?? 0);

    $cover_sql = '';
    $cover_val = [];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $img_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($img_ext, ['jpg','jpeg','png','webp'])) {
            $dest_dir = __DIR__ . '/../assets/uploads/';
            if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);
            $new_name = uniqid('cover_', true) . '.' . $img_ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest_dir . $new_name)) {
                $cover_sql = ', cover_image = ?';
                $cover_val[] = 'assets/uploads/' . $new_name;
            }
        }
    }

    $file_sql = '';
    $file_val = [];
    if (!empty($_FILES['ebook_file']['name'][0])) {
        $existing_stmt = $db->prepare("SELECT file_path FROM products WHERE id = ?");
        $existing_stmt->execute([$edit_id]);
        $existing_row = $existing_stmt->fetch(PDO::FETCH_ASSOC);
        $existing_paths = [];
        if ($existing_row && !empty($existing_row['file_path'])) {
            $decoded = json_decode($existing_row['file_path'], true);
            $existing_paths = is_array($decoded) ? $decoded : [$existing_row['file_path']];
        }

        $dest_dir = __DIR__ . '/../files/uploads/';
        if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

        foreach ($_FILES['ebook_file']['tmp_name'] as $i => $tmp) {
            if ($_FILES['ebook_file']['error'][$i] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['ebook_file']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($file_ext, ['pdf', 'zip'])) {
                    $new_name = uniqid('ebook_', true) . '.' . $file_ext;
                    if (move_uploaded_file($tmp, $dest_dir . $new_name)) {
                        $existing_paths[] = 'files/uploads/' . $new_name;
                    }
                }
            }
        }

        if (!empty($existing_paths)) {
            $file_sql = ', file_path = ?';
            $file_val[] = json_encode($existing_paths);
        }
    }

    if ($edit_id > 0 && !empty($title) && !empty($slug)) {
        try {
            $params = array_merge([$title, $slug, $price, $original_price, $category, $description], $cover_val, $file_val, [$edit_id]);
            $sql = "UPDATE products SET title=?, slug=?, price=?, original_price=?, category=?, description=?{$cover_sql}{$file_sql} WHERE id=?";
            $db->prepare($sql)->execute($params);
            echo "SUCCESS";
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
            exit;
        }
    }
}

if ($authenticated && isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['id'])) {
    $prod_id = (int)$_GET['id'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$prod_id]);
    header('Location: index.php?tab=products-tab&msg=deleted');
    exit;
}

// -------------------------------------------------------------
// 5. CSV EXPORT
// -------------------------------------------------------------
if ($authenticated && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tatvam_customers_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Name', 'Email Address', 'WhatsApp Phone', 'Product Purchased', 'Amount', 'Payment Status', 'Date']);

    $stmt = $db->query("SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id ORDER BY orders.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['id'], $row['customer_name'], $row['customer_email'], $row['customer_phone'], $row['title'], 'INR ' . $row['amount'], strtoupper($row['payment_status']), $row['created_at']]);
    }
    fclose($output);
    exit;
}

// -------------------------------------------------------------
// 6. METRICS & DATA AGGREGATION FOR ALL MODULES
// -------------------------------------------------------------
$total_revenue       = 0.00;
$total_paid_orders   = 0;
$total_pending_orders= 0;
$total_failed_orders = 0;
$average_order_value = 0.00;

$search_query   = trim($_GET['search'] ?? '');
$status_filter  = trim($_GET['status'] ?? '');
$product_filter = (int)($_GET['product_id'] ?? 0);

if ($authenticated) {
    try {
        $total_revenue        = (float)$db->query("SELECT SUM(amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
        $total_paid_orders    = (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
        $total_pending_orders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
        $total_failed_orders  = (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'failed'")->fetchColumn();
        $average_order_value  = $total_paid_orders > 0 ? ($total_revenue / $total_paid_orders) : 0.00;

        // Build Order Search & Filter Query
        $where_clauses = [];
        $params = [];

        if (!empty($search_query)) {
            $where_clauses[] = "(orders.customer_name LIKE ? OR orders.customer_email LIKE ? OR orders.customer_phone LIKE ? OR orders.id = ?)";
            $params[] = "%$search_query%";
            $params[] = "%$search_query%";
            $params[] = "%$search_query%";
            $params[] = (int)$search_query;
        }

        if (!empty($status_filter)) {
            $where_clauses[] = "orders.payment_status = ?";
            $params[] = $status_filter;
        }

        if ($product_filter > 0) {
            $where_clauses[] = "orders.product_id = ?";
            $params[] = $product_filter;
        }

        $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        $orders_sql = "SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id {$where_sql} ORDER BY orders.id DESC LIMIT 100";
        $stmt = $db->prepare($orders_sql);
        $stmt->execute($params);
        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $all_products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Financial P&L Records (All saved dates + Last 14 Days)
        $pnl_stmt = $db->query("SELECT * FROM daily_calculations ORDER BY calc_date DESC LIMIT 30");
        $saved_pnl = $pnl_stmt->fetchAll(PDO::FETCH_ASSOC);
        $saved_dates = array_column($saved_pnl, 'calc_date');

        $daily_pnl = [];
        // Generate date list starting from August 1st (2026-08-01) up to today
        $start_date_obj = new DateTime('2026-08-01');
        $today_obj      = new DateTime('today');
        $date_list      = [];

        while ($start_date_obj <= $today_obj) {
            $date_list[] = $start_date_obj->format('Y-m-d');
            $start_date_obj->modify('+1 day');
        }

        foreach ($saved_dates as $sd) {
            if (!in_array($sd, $date_list)) $date_list[] = $sd;
        }
        rsort($date_list);

        foreach ($date_list as $d) {
            $rev_stmt = $db->prepare("SELECT SUM(amount), COUNT(*) FROM orders WHERE payment_status = 'paid' AND date(created_at) = ?");
            $rev_stmt->execute([$d]);
            $row = $rev_stmt->fetch(PDO::FETCH_NUM);
            $day_gross = (float)($row[0] ?? 0);
            $day_orders= (int)($row[1] ?? 0);

            $pg_fee = $day_gross * 0.0236; // 2.36% Cashfree + GST
            $net_remittance = $day_gross - $pg_fee;

            $ad_stmt = $db->prepare("SELECT ad_spend, notes FROM daily_calculations WHERE calc_date = ?");
            $ad_stmt->execute([$d]);
            $calc_row = $ad_stmt->fetch(PDO::FETCH_ASSOC);

            $ad_spend = (float)($calc_row['ad_spend'] ?? 0);
            $notes    = $calc_row['notes'] ?? '';

            $net_profit = $net_remittance - $ad_spend;
            $roas       = $ad_spend > 0 ? number_format($day_gross / $ad_spend, 2) . 'x' : 'N/A';

            $daily_pnl[] = [
                'date'           => $d,
                'gross_revenue'  => $day_gross,
                'orders'         => $day_orders,
                'pg_fee'         => $pg_fee,
                'net_remittance' => $net_remittance,
                'ad_spend'       => $ad_spend,
                'net_profit'     => $net_profit,
                'roas'           => $roas,
                'notes'          => $notes,
            ];
        }

    } catch (Exception $e) {
        $db_error = "Error querying analytics: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Admin Dashboard | TATVAM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../styles.css?v=2.4">
    <style>
        .pnl-table th, .pnl-table td { padding: 0.75rem 0.6rem; text-align: left; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .pnl-table input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 4px; padding: 4px 8px; width: 95px; font-size: 0.85rem; }
        .badge-paid { background: rgba(16,185,129,0.15); color: #10B981; border: 1px solid rgba(16,185,129,0.3); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.78rem; display: inline-block; }
        .badge-pending { background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.78rem; display: inline-block; }
        .badge-failed { background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.78rem; display: inline-block; }
        .btn-whatsapp-recover { background: #25D366; color: #000 !important; font-weight: 700; border-radius: 6px; padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; justify-content: center; gap: 4px; text-decoration: none; box-shadow: 0 4px 10px rgba(37,211,102,0.25); white-space: nowrap; width: 100%; }
        .btn-whatsapp-recover:hover { background: #1EBE5D; transform: scale(1.02); }
        .btn-whatsapp-thankyou { background: rgba(37,211,102,0.12); color: #25D366 !important; border: 1px solid rgba(37,211,102,0.3); border-radius: 6px; padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; justify-content: center; gap: 4px; text-decoration: none; white-space: nowrap; width: 100%; }
        .btn-whatsapp-thankyou:hover { background: rgba(37,211,102,0.25); }

        /* Mobile Aesthetics & Touch Friendly Controls */
        @media (max-width: 768px) {
            .admin-header .container {
                flex-direction: column !important;
                gap: 12px !important;
                align-items: stretch !important;
                text-align: center;
            }
            .admin-header div {
                justify-content: center !important;
                width: 100%;
            }
            .admin-logo {
                font-size: 1.25rem !important;
            }
            .admin-tabs-nav {
                display: flex !important;
                gap: 8px !important;
                overflow-x: auto !important;
                padding-bottom: 8px !important;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            .admin-tabs-nav::-webkit-scrollbar { display: none; }
            .tab-btn {
                font-size: 0.9rem !important;
                padding: 0.6rem 0.9rem !important;
                border-radius: 8px !important;
                background: rgba(255,255,255,0.03) !important;
                border: 1px solid rgba(255,255,255,0.08) !important;
                white-space: nowrap !important;
                flex-shrink: 0;
            }
            .tab-btn.active {
                background: rgba(251,191,36,0.15) !important;
                border-color: var(--color-gold) !important;
                color: var(--color-gold) !important;
            }
            .kpi-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 10px !important;
            }
            .kpi-card {
                padding: 1rem !important;
            }
            .kpi-card p {
                font-size: 1.35rem !important;
            }
            .admin-table-container, .glass-card {
                padding: 1rem !important;
                border-radius: 12px !important;
            }
            .mobile-stack-form {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }
            .mobile-stack-form > div, .mobile-stack-form input, .mobile-stack-form select {
                width: 100% !important;
            }
            /* Manage Ebook Layout Mobile */
            .manage-ebooks-grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .kpi-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body class="admin-wrapper" style="padding-top: 0; background: var(--color-bg-1);">

    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <?php if (!$authenticated): ?>
        <!-- LOGIN PORTAL VIEW -->
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%);">
            <div class="glass-card" style="width: 100%; max-width: 400px; padding: 2rem; border-color: var(--color-border-gold); z-index: 2; position: relative;">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <h2 class="gradient-gold" style="font-size: 1.85rem; margin-bottom: 0.25rem;">TATVAM Admin</h2>
                    <p style="color: var(--color-text-slate); font-size: 0.9rem;">Enter authorized credentials</p>
                </div>

                <?php if (isset($login_error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.9rem; text-align: center;">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <input type="text" name="username" id="username" class="form-input" required placeholder=" ">
                        <label for="username" class="form-label">Username</label>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <input type="password" name="password" id="password" class="form-input" required placeholder=" ">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; color: #000 !important;">
                        Authorize Panel <i data-lucide="shield-check"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- AUTHENTICATED SaaS DASHBOARD VIEW -->
        <header class="admin-header" style="position: relative; z-index: 10; padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(3,5,12,0.85); backdrop-filter: blur(10px);">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="admin-logo" style="font-size: 1.5rem;">TATVAM<span>.</span> <span style="font-size: 0.85rem; font-weight: 400; color: var(--color-gold); margin-left: 0.5rem; background: rgba(251,191,36,0.1); padding: 2px 10px; border-radius: 12px; border: 1px solid rgba(251,191,36,0.2);">Executive SaaS Panel</span></h1>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <a href="?export=csv" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #FFE082 0%, var(--color-gold) 100%); color: #000 !important; box-shadow: none; font-weight: 700;"><i data-lucide="download"></i> Export Orders (CSV)</a>
                    <a href="?action=logout" class="btn btn-secondary btn-sm" style="background: transparent; color: #EF4444; border-color: rgba(239,68,68,0.2);"><i data-lucide="log-out"></i> Logout</a>
                </div>
            </div>
        </header>

        <main class="container" style="padding-top: 1.5rem; padding-bottom: 3rem; position: relative; z-index: 2;">

            <!-- Flash Alert Messages -->
            <?php if (isset($_GET['msg'])): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10B981; padding: 0.85rem 1.2rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; font-size: 0.9rem;">
                    <?php 
                        if ($_GET['msg'] === 'email_sent') echo '📩 E-Book download email resent successfully!';
                        elseif ($_GET['msg'] === 'added') echo '✨ New E-Book product created successfully!';
                        elseif ($_GET['msg'] === 'deleted') echo '🗑️ E-Book product deleted.';
                        elseif ($_GET['msg'] === 'reset_done') echo '🧹 All testing orders and revenue data cleared! Everything is reset to zero (0).';
                    ?>
                </div>
            <?php endif; ?>

            <!-- DISTINCT MODULAR TABS -->
            <div class="admin-tabs-nav" style="display: flex; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.75rem; margin-bottom: 1.5rem; overflow-x: auto;">
                <button class="tab-btn active" id="btn-overview" onclick="switchTab('overview-tab')" style="background: none; border: none; color: var(--color-gold); font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; cursor: pointer; padding: 0.4rem 0.8rem; border-bottom: 2px solid var(--color-gold); white-space: nowrap;">📊 Overview & Charts</button>
                <button class="tab-btn" id="btn-pnl" onclick="switchTab('pnl-tab')" style="background: none; border: none; color: var(--color-text-slate); font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; cursor: pointer; padding: 0.4rem 0.8rem; border-bottom: 2px solid transparent; white-space: nowrap;">💰 Financial P&L Tracker</button>
                <button class="tab-btn" id="btn-orders" onclick="switchTab('orders-tab')" style="background: none; border: none; color: var(--color-text-slate); font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; cursor: pointer; padding: 0.4rem 0.8rem; border-bottom: 2px solid transparent; white-space: nowrap;">🛍️ Order Pipeline & WhatsApp</button>
                <button class="tab-btn" id="btn-products" onclick="switchTab('products-tab')" style="background: none; border: none; color: var(--color-text-slate); font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; cursor: pointer; padding: 0.4rem 0.8rem; border-bottom: 2px solid transparent; white-space: nowrap;">📦 Manage E-Books</button>
            </div>

            <!-- MODULE 1: OVERVIEW & CHARTS -->
            <div id="overview-tab">
                <!-- KPI CARDS OVERVIEW -->
                <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
                    <div class="glass-card kpi-card" style="padding: 1.25rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-slate); font-weight: 700; letter-spacing: 0.05em;">Total Gross Revenue</span>
                        <p class="gradient-gold" style="font-size: 1.85rem; font-weight: 800; margin-top: 0.35rem;">₹<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                    <div class="glass-card kpi-card" style="padding: 1.25rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: #10B981; font-weight: 700; letter-spacing: 0.05em;">Paid Completed Orders</span>
                        <p style="font-size: 1.85rem; font-weight: 800; margin-top: 0.35rem; color: #10B981;"><?php echo $total_paid_orders; ?> Sales</p>
                    </div>
                    <div class="glass-card kpi-card" style="padding: 1.25rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: #F59E0B; font-weight: 700; letter-spacing: 0.05em;">Pending Checkouts</span>
                        <p style="font-size: 1.85rem; font-weight: 800; margin-top: 0.35rem; color: #F59E0B;"><?php echo $total_pending_orders; ?></p>
                    </div>
                    <div class="glass-card kpi-card" style="padding: 1.25rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-slate); font-weight: 700; letter-spacing: 0.05em;">Average Order Value (AOV)</span>
                        <p style="font-size: 1.85rem; font-weight: 800; margin-top: 0.35rem; color: #fff;">₹<?php echo number_format($average_order_value, 2); ?></p>
                    </div>
                </div>

                <!-- CHART & ANALYTICS VISUALIZER -->
                <div class="glass-card" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.2rem; color: var(--color-gold); margin-bottom: 1rem;">📈 14-Day Sales & Remittance Trend Graph</h3>
                    <div style="height: 320px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- MODULE 2: FINANCIAL P&L TRACKER (SEPARATE TAB WITH MANUAL DATE ADDITION) -->
            <div id="pnl-tab" style="display: none;">
                <!-- MANUAL DATE & AD SPEND ADDITION FORM -->
                <div class="glass-card" style="padding: 1.25rem; margin-bottom: 1.5rem; border-color: rgba(251,191,36,0.3);">
                    <h3 style="font-size: 1.15rem; color: var(--color-gold); margin-bottom: 0.75rem;">➕ Add / Update Manual Date Record</h3>
                    <form id="manual-pnl-form" class="mobile-stack-form" onsubmit="saveManualAdSpend(event)" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <label style="display: block; font-size: 0.78rem; color: var(--color-text-slate); margin-bottom: 4px;">Select Date</label>
                            <input type="date" id="manual-date" class="form-input" required value="<?php echo date('Y-m-d'); ?>" style="padding: 0.5rem 0.8rem; font-size: 0.85rem; color: #fff; background: rgba(255,255,255,0.05);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.78rem; color: var(--color-text-slate); margin-bottom: 4px;">Ad Spend (₹)</label>
                            <input type="number" step="0.01" id="manual-spend" class="form-input" placeholder="e.g. 500" style="padding: 0.5rem 0.8rem; font-size: 0.85rem; width: 130px;">
                        </div>
                        <div style="flex: 1; min-width: 180px;">
                            <label style="display: block; font-size: 0.78rem; color: var(--color-text-slate); margin-bottom: 4px;">Notes / Campaign Info</label>
                            <input type="text" id="manual-notes" class="form-input" placeholder="e.g. FB Campaign #1" style="padding: 0.5rem 0.8rem; font-size: 0.85rem;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="color: #000 !important; font-weight: 700; padding: 0.6rem 1.2rem;">Save Date Record</button>
                    </form>
                </div>

                <!-- P&L CALCULATOR TABLE -->
                <div class="glass-card" style="padding: 1.5rem; overflow-x: auto;">
                    <h3 style="font-size: 1.25rem; color: #fff; margin-bottom: 0.25rem;">💰 Financial Profit & Loss (P&L) Ledger</h3>
                    <p style="font-size: 0.82rem; color: var(--color-text-slate); margin-bottom: 1rem;">Calculates Gross Revenue, PG Fee (2.36%), Net Remittance, Net Profit, and ROAS per date.</p>

                    <table class="pnl-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="color: var(--color-gold); font-size: 0.8rem; border-bottom: 1px solid var(--border-light);">
                                <th>Date</th>
                                <th>Paid Orders</th>
                                <th>Gross Revenue</th>
                                <th>PG Fee (2.36%)</th>
                                <th>Net Remittance</th>
                                <th>Ad Spend (₹)</th>
                                <th>Net Profit (₹)</th>
                                <th>ROAS</th>
                                <th>Action / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daily_pnl as $row): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #fff;"><?php echo date('d M Y (D)', strtotime($row['date'])); ?></td>
                                    <td><span class="badge-paid"><?php echo $row['orders']; ?> sales</span></td>
                                    <td style="color: #fff; font-weight: 600;">₹<?php echo number_format($row['gross_revenue'], 2); ?></td>
                                    <td style="color: rgba(255,255,255,0.4);">₹<?php echo number_format($row['pg_fee'], 2); ?></td>
                                    <td style="color: var(--color-gold); font-weight: 600;">₹<?php echo number_format($row['net_remittance'], 2); ?></td>
                                    <td>
                                        <input type="number" step="0.01" id="adspend-<?php echo $row['date']; ?>" value="<?php echo $row['ad_spend'] > 0 ? $row['ad_spend'] : ''; ?>" placeholder="0.00">
                                    </td>
                                    <td style="font-weight: 700; color: <?php echo $row['net_profit'] >= 0 ? '#10B981' : '#EF4444'; ?>;">
                                        ₹<?php echo number_format($row['net_profit'], 2); ?>
                                    </td>
                                    <td style="font-weight: 700; color: var(--color-gold);"><?php echo $row['roas']; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <input type="text" id="notes-<?php echo $row['date']; ?>" value="<?php echo htmlspecialchars($row['notes']); ?>" placeholder="Notes..." style="width: 110px;">
                                            <button onclick="saveAdSpend('<?php echo $row['date']; ?>')" class="btn btn-primary btn-sm" style="padding: 4px 10px; font-size: 0.75rem; color: #000 !important;">Save</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODULE 3: ORDER PIPELINE & DIRECT WHATSAPP RECOVERY -->
            <div id="orders-tab" style="display: none;">
                <!-- SEARCH & FILTER BAR -->
                <div class="glass-card" style="padding: 1rem; margin-bottom: 1.5rem;">
                    <form method="GET" class="mobile-stack-form" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                        <input type="hidden" name="tab" value="orders-tab">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" name="search" class="form-input" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search Order ID, Name, Email or Phone..." style="padding: 0.6rem 1rem; font-size: 0.85rem;">
                        </div>
                        <select name="status" class="form-input" style="width: 140px; padding: 0.6rem 0.8rem; font-size: 0.85rem; color: #fff; background: rgba(255,255,255,0.05);">
                            <option value="" style="background:#0b132b;">All Statuses</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?> style="background:#0b132b;">Paid Only</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?> style="background:#0b132b;">Pending Only</option>
                        </select>
                        <select name="product_id" class="form-input" style="width: 160px; padding: 0.6rem 0.8rem; font-size: 0.85rem; color: #fff; background: rgba(255,255,255,0.05);">
                            <option value="0" style="background:#0b132b;">All Products</option>
                            <?php foreach ($all_products as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo $product_filter == $p['id'] ? 'selected' : ''; ?> style="background:#0b132b;"><?php echo htmlspecialchars($p['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm" style="color: #000 !important; font-weight: 700; padding: 0.65rem 1.2rem;">Filter Orders</button>
                        <a href="index.php?tab=orders-tab" class="btn btn-secondary btn-sm" style="padding: 0.65rem 1rem; font-size: 0.8rem;">Reset</a>
                    </form>
                </div>

                <!-- ORDERS PIPELINE TABLE WITH AUTOMATED WHATSAPP BUTTONS -->
                <div class="admin-table-container" style="background: var(--panel-bg); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-light); color: var(--color-gold); font-size: 0.8rem;">
                                <th style="padding: 1rem;">ID</th>
                                <th style="padding: 1rem;">Customer Details</th>
                                <th style="padding: 1rem;">Product Purchased</th>
                                <th style="padding: 1rem;">Amount</th>
                                <th style="padding: 1rem;">Status</th>
                                <th style="padding: 1rem;">Date & Time</th>
                                <th style="padding: 1rem;">Automated WhatsApp Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) > 0): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <?php 
                                        // Clean phone number for WhatsApp
                                        $clean_phone = preg_replace('/[^0-9]/', '', $order['customer_phone']);
                                        if (strlen($clean_phone) == 10) $clean_phone = '91' . $clean_phone; // add India country code if omitted

                                        // Prepare Automated WhatsApp Messages
                                        if ($order['payment_status'] === 'pending') {
                                            $wa_msg = "Namaste " . $order['customer_name'] . " ji! AAPKA TATVAM Order #" . $order['id'] . " (" . $order['title'] . ") checkout incomplete reh gaya tha. Complete karne ke liye yahan click karein: https://tatvam.shop/positive-thinking.html - Koi help chahiye to hume batayein!";
                                            $wa_url = "https://api.whatsapp.com/send?phone=" . $clean_phone . "&text=" . urlencode($wa_msg);
                                        } else {
                                            // Paid Order: Thank-You & Download Access Link
                                            $token = $order['download_token'];
                                            $download_url = SITE_URL . "/download.php?token=" . $token;
                                            $wa_msg = "Dhanyawad " . $order['customer_name'] . " ji! AAPKA TATVAM Order #" . $order['id'] . " (" . $order['title'] . ") confirmed hai. Aapka instant download link yahan hai: " . $download_url . " - TATVAM se judne ke liye dhanyawad!";
                                            $wa_url = "https://api.whatsapp.com/send?phone=" . $clean_phone . "&text=" . urlencode($wa_msg);
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                        <td style="padding: 1rem; font-weight: 600;">#<?php echo $order['id']; ?></td>
                                        <td style="padding: 1rem;">
                                            <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($order['customer_email']); ?> | <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                        </td>
                                        <td style="padding: 1rem; color: #fff;"><?php echo htmlspecialchars($order['title']); ?></td>
                                        <td style="padding: 1rem; font-weight: 700; color: var(--color-gold);">₹<?php echo number_format($order['amount'], 2); ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($order['payment_status'] === 'paid'): ?>
                                                <span class="badge-paid">PAID</span>
                                            <?php else: ?>
                                                <span class="badge-pending">PENDING</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem; color: rgba(255,255,255,0.6); font-size: 0.8rem;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($order['payment_status'] === 'pending'): ?>
                                                <!-- WhatsApp Pending Order Recovery Button -->
                                                <a href="<?php echo $wa_url; ?>" target="_blank" class="btn-whatsapp-recover" title="Send WhatsApp Recovery Message">
                                                    💬 Recover on WhatsApp
                                                </a>
                                            <?php else: ?>
                                                <!-- WhatsApp Paid Order Thank-You & Download Link Button -->
                                                <a href="<?php echo $wa_url; ?>" target="_blank" class="btn-whatsapp-thankyou" title="Send Thank-You & Download Link on WhatsApp">
                                                    💬 Send Thank-You & Link
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--color-text-slate); padding: 2rem 0;">No matching orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODULE 4: MANAGE E-BOOKS -->
            <div id="products-tab" style="display: none;">
                <div class="manage-ebooks-grid" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 1.5rem; align-items: start;">
                    <!-- Add Product Card -->
                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.35rem; margin-bottom: 1rem; color: var(--color-gold);">✨ Add New E-Book Product</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_product">
                            
                            <div class="form-group">
                                <input type="text" name="title" id="p-title" class="form-input" required placeholder=" ">
                                <label for="p-title" class="form-label">E-Book Title</label>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="slug" id="p-slug" class="form-input" required placeholder=" ">
                                <label for="p-slug" class="form-label">URL Slug (e.g. positive-thinking)</label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <input type="number" step="0.01" name="price" id="p-price" class="form-input" required placeholder=" ">
                                    <label for="p-price" class="form-label">Price (INR)</label>
                                </div>
                                <div class="form-group">
                                    <input type="number" step="0.01" name="original_price" id="p-orig-price" class="form-input" required placeholder=" ">
                                    <label for="p-orig-price" class="form-label">Original Price (INR)</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="text" name="category" id="p-cat" class="form-input" required placeholder=" ">
                                <label for="p-cat" class="form-label">Category (e.g. mindset, peace, wealth)</label>
                            </div>

                            <div class="form-group">
                                <textarea name="description" id="p-desc" class="form-input" rows="3" required placeholder=" " style="resize: none; padding-top: 1rem;"></textarea>
                                <label for="p-desc" class="form-label" style="top: 0.6rem;">Description</label>
                            </div>

                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-light); padding: 0.75rem; border-radius: 6px;">
                                <label style="display: block; font-size: 0.8rem; color: var(--color-gold); margin-bottom: 4px;">🖼️ Cover Image (JPG / PNG)</label>
                                <input type="file" name="cover_image" accept="image/*" required style="font-size: 0.85rem; color: var(--color-text-slate);">
                            </div>

                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-light); padding: 0.75rem; border-radius: 6px;">
                                <label style="display: block; font-size: 0.8rem; color: var(--color-gold); margin-bottom: 4px;">📄 E-Book Files (Main & Bonus PDFs - Multiple allowed)</label>
                                <input type="file" name="ebook_file[]" accept=".pdf,.zip" multiple required style="font-size: 0.85rem; color: var(--color-text-slate);">
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; color: #000 !important; font-weight: 700;">
                                Create E-Book <i data-lucide="plus-circle"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Products Catalog Grid -->
                    <div>
                        <h3 style="font-size: 1.35rem; margin-bottom: 1rem; color: var(--color-primary);">Current Catalog</h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <?php foreach ($all_products as $prod): ?>
                                <div class="glass-card" style="display: flex; gap: 1rem; align-items: center; padding: 0.85rem;">
                                    <img src="../<?php echo htmlspecialchars($prod['cover_image']); ?>" onerror="this.src='../assets/book-cover.jpg';" style="width: 45px; height: 65px; object-fit: cover; border-radius: 4px;">
                                    <div style="flex: 1;">
                                        <h4 style="font-size: 0.92rem; color: #fff; margin: 0;"><?php echo htmlspecialchars($prod['title']); ?></h4>
                                        <span style="font-size: 0.78rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($prod['category']); ?></span>
                                        <div style="font-size: 0.85rem; font-weight: bold; color: var(--color-gold); margin-top: 2px;">
                                            ₹<?php echo $prod['price']; ?> <del style="font-weight: normal; font-size: 0.75rem; color: var(--color-text-slate);">₹<?php echo $prod['original_price']; ?></del>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)" style="color: var(--color-gold); padding: 6px 10px; border: 1px solid rgba(212,175,55,0.3); border-radius: 6px; background: rgba(212,175,55,0.08); cursor: pointer;" title="Edit E-Book">
                                            ✏️ Edit
                                        </button>
                                        <a href="?action=delete_product&id=<?php echo $prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color: #EF4444; padding: 6px 10px; border: 1px solid rgba(239,68,68,0.2); border-radius: 6px; text-decoration: none; font-size: 0.8rem;" title="Delete Product">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- EDIT E-BOOK MODAL WITH LIVE AJAX PROGRESS BAR -->
        <div id="edit-modal-overlay" onclick="closeEditModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
            <div onclick="event.stopPropagation()" style="background:#0b132b; border:1px solid rgba(212,175,55,0.35); border-radius:16px; width:100%; max-width:580px; max-height:90vh; overflow-y:auto; padding:2rem; position:relative;">
                <button onclick="closeEditModal()" style="position:absolute; top:1rem; right:1rem; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; width:36px; height:36px; border-radius:50%; font-size:1.2rem; cursor:pointer;">&times;</button>
                
                <h3 style="font-size:1.4rem; color:var(--color-gold); margin-bottom:1.5rem;">✏️ Edit E-Book Details</h3>

                <form method="POST" enctype="multipart/form-data" id="edit-ebook-form">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="edit_id" id="edit-id">

                    <div class="form-group">
                        <input type="text" name="title" id="edit-title" class="form-input" required placeholder=" ">
                        <label for="edit-title" class="form-label">E-Book Title</label>
                    </div>

                    <div class="form-group">
                        <input type="text" name="slug" id="edit-slug" class="form-input" required placeholder=" ">
                        <label for="edit-slug" class="form-label">URL Slug</label>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <input type="number" step="0.01" name="price" id="edit-price" class="form-input" required placeholder=" ">
                            <label for="edit-price" class="form-label">Price (INR)</label>
                        </div>
                        <div class="form-group">
                            <input type="number" step="0.01" name="original_price" id="edit-orig-price" class="form-input" required placeholder=" ">
                            <label for="edit-orig-price" class="form-label">Original Price (INR)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" name="category" id="edit-category" class="form-input" required placeholder=" ">
                        <label for="edit-category" class="form-label">Category</label>
                    </div>

                    <div class="form-group">
                        <textarea name="description" id="edit-description" class="form-input" rows="3" placeholder=" " style="resize:none; padding-top:1rem;"></textarea>
                        <label for="edit-description" class="form-label" style="top:0.6rem;">Description</label>
                    </div>

                    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border-light); padding:0.75rem; border-radius:6px; margin-bottom:1rem;">
                        <label style="display:block; font-size:0.8rem; color:var(--color-gold); margin-bottom:4px;">🖼️ Replace Cover Image (optional)</label>
                        <input type="file" name="cover_image" accept="image/*" style="font-size:0.85rem; color:var(--color-text-slate);">
                    </div>

                    <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border-light); padding:0.75rem; border-radius:6px; margin-bottom:1.5rem;">
                        <label style="display:block; font-size:0.8rem; color:var(--color-gold); margin-bottom:4px;">📄 Add PDF/ZIP Files (Main & Bonus PDFs)</label>
                        <p id="edit-existing-files" style="font-size:0.75rem; color:rgba(255,255,255,0.5); margin-bottom:8px;"></p>
                        <input type="file" name="ebook_file[]" id="edit-ebook-files" accept=".pdf,.zip" multiple style="font-size:0.85rem; color:var(--color-text-slate);">
                    </div>

                    <!-- Progress bar container -->
                    <div id="upload-progress-box" style="display:none; margin-bottom:1.5rem; background:rgba(255,255,255,0.04); border:1px solid var(--border-light); border-radius:6px; padding:1rem;">
                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--color-gold); margin-bottom:6px;">
                            <span id="upload-status-text">Uploading files...</span>
                            <span id="upload-percent-text">0%</span>
                        </div>
                        <div style="width:100%; height:8px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden;">
                            <div id="upload-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg, #FBBF24, #10B981); transition:width 0.15s ease;"></div>
                        </div>
                    </div>

                    <button type="submit" id="edit-submit-btn" class="btn btn-primary" style="width:100%; color: #000 !important; font-weight: 700;">
                        Save Changes &nbsp;<i data-lucide="save" style="display:inline; vertical-align:middle;"></i>
                    </button>
                </form>
            </div>
        </div>

    <?php endif; ?>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            initChart();
            
            // Check query tab
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) switchTab(tabParam);
        });

        // Initialize Chart.js Trend Visualizer
        function initChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            const pnlData = <?php echo json_encode(array_reverse($daily_pnl ?? [])); ?>;
            const labels = pnlData.map(d => d.date);
            const grossData = pnlData.map(d => d.gross_revenue);
            const profitData= pnlData.map(d => d.net_profit);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Gross Revenue (₹)',
                            data: grossData,
                            borderColor: '#FBBF24',
                            backgroundColor: 'rgba(251, 191, 36, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Net Profit (₹)',
                            data: profitData,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.05)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#CBD5E1' } }
                    },
                    scales: {
                        x: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    }
                }
            });
        }

        // Save Manual Date Ad Spend Record
        function saveManualAdSpend(e) {
            e.preventDefault();
            const dateVal  = document.getElementById('manual-date').value;
            const spendVal = document.getElementById('manual-spend').value;
            const notesVal = document.getElementById('manual-notes').value;

            if (!dateVal) { alert('Please select a date.'); return; }

            const formData = new FormData();
            formData.append('action', 'save_ad_spend');
            formData.append('calc_date', dateVal);
            formData.append('ad_spend', spendVal || 0);
            formData.append('notes', notesVal || '');

            fetch('index.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php?tab=pnl-tab';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Save failed. Connection error.'));
        }

        // AJAX Ad Spend Saver for Table
        function saveAdSpend(dateStr) {
            const spendInput = document.getElementById('adspend-' + dateStr);
            const notesInput = document.getElementById('notes-' + dateStr);
            if (!spendInput) return;

            const formData = new FormData();
            formData.append('action', 'save_ad_spend');
            formData.append('calc_date', dateStr);
            formData.append('ad_spend', spendInput.value || 0);
            formData.append('notes', notesInput ? notesInput.value : '');

            fetch('index.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php?tab=pnl-tab';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Save failed. Connection error.'));
        }

        function openEditModal(prod) {
            document.getElementById('edit-id').value          = prod.id;
            document.getElementById('edit-title').value       = prod.title;
            document.getElementById('edit-slug').value        = prod.slug;
            document.getElementById('edit-price').value       = prod.price;
            document.getElementById('edit-orig-price').value  = prod.original_price;
            document.getElementById('edit-category').value    = prod.category;
            document.getElementById('edit-description').value = prod.description;

            var existingEl = document.getElementById('edit-existing-files');
            try {
                var paths = JSON.parse(prod.file_path);
                if (Array.isArray(paths)) {
                    existingEl.textContent = '📂 Attached: ' + paths.length + ' file(s) (' + paths.map(function(p){ return p.split('/').pop(); }).join(', ') + ')';
                } else {
                    existingEl.textContent = prod.file_path ? ('📂 Attached: 1 file (' + prod.file_path.split('/').pop() + ')') : '📂 No files attached';
                }
            } catch(e) {
                existingEl.textContent = prod.file_path ? ('📂 Attached: 1 file (' + prod.file_path.split('/').pop() + ')') : '📂 No files attached';
            }

            var overlay = document.getElementById('edit-modal-overlay');
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
        }

        function closeEditModal() {
            document.getElementById('edit-modal-overlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        // AJAX Form Submit with Smooth Progress Bar
        const editForm = document.getElementById('edit-ebook-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(editForm);
                const xhr = new XMLHttpRequest();

                const progressBox = document.getElementById('upload-progress-box');
                const progressBar = document.getElementById('upload-progress-bar');
                const percentText = document.getElementById('upload-percent-text');
                const statusText  = document.getElementById('upload-status-text');
                const submitBtn   = document.getElementById('edit-submit-btn');

                progressBox.style.display = 'block';
                submitBtn.disabled = true;

                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = percent + '%';
                        percentText.textContent = percent + '%';
                        statusText.textContent = percent < 100 ? 'Uploading Files...' : 'Processing...';
                    }
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        progressBar.style.width = '100%';
                        percentText.textContent = '100%';
                        statusText.textContent = '✅ Saved!';
                        setTimeout(() => window.location.reload(), 400);
                    } else {
                        alert('Upload error.');
                        submitBtn.disabled = false;
                    }
                });

                xhr.open('POST', 'index.php', true);
                xhr.send(formData);
            });
        }

        function switchTab(tabId) {
            document.getElementById('overview-tab').style.display = tabId === 'overview-tab' ? 'block' : 'none';
            document.getElementById('pnl-tab').style.display      = tabId === 'pnl-tab' ? 'block' : 'none';
            document.getElementById('orders-tab').style.display   = tabId === 'orders-tab' ? 'block' : 'none';
            document.getElementById('products-tab').style.display = tabId === 'products-tab' ? 'block' : 'none';
            
            document.getElementById('btn-overview').style.color  = tabId === 'overview-tab' ? 'var(--color-gold)' : 'var(--color-text-slate)';
            document.getElementById('btn-pnl').style.color       = tabId === 'pnl-tab' ? 'var(--color-gold)' : 'var(--color-text-slate)';
            document.getElementById('btn-orders').style.color    = tabId === 'orders-tab' ? 'var(--color-gold)' : 'var(--color-text-slate)';
            document.getElementById('btn-products').style.color  = tabId === 'products-tab' ? 'var(--color-gold)' : 'var(--color-text-slate)';

            document.getElementById('btn-overview').style.borderBottomColor  = tabId === 'overview-tab' ? 'var(--color-gold)' : 'transparent';
            document.getElementById('btn-pnl').style.borderBottomColor       = tabId === 'pnl-tab' ? 'var(--color-gold)' : 'transparent';
            document.getElementById('btn-orders').style.borderBottomColor    = tabId === 'orders-tab' ? 'var(--color-gold)' : 'transparent';
            document.getElementById('btn-products').style.borderBottomColor  = tabId === 'products-tab' ? 'var(--color-gold)' : 'transparent';
        }
    </script>
</body>
</html>
