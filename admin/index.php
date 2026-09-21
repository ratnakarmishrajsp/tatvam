<?php
/**
 * TATVAM - Premium SaaS-Style Admin Dashboard
 * Displays sales analytics, order log lists, database CSV downloads, and E-Book uploads.
 */

session_start();
require_once __DIR__ . '/../db.php';

// Simple Administrative Login Session Gate
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'TATVAMAdmin108'); // Rebranded secure default password

$authenticated = false;

if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    $authenticated = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

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

// Handle adding a new product
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $slug = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $price = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price'];

    // Handle Cover Image upload
    $cover_image_path = 'assets/book-cover.jpg'; // fallback default
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['cover_image']['name']);
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        if (in_array($img_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $dest_dir = __DIR__ . '/../assets/uploads/';
            if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);
            $new_name = uniqid('cover_', true) . '.' . $img_ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest_dir . $new_name)) {
                $cover_image_path = 'assets/uploads/' . $new_name;
            }
        }
    }

    // Handle Multiple E-Book / Toolkit Files upload
    $allowed_file_exts = ['pdf', 'zip', 'epub', 'mobi', 'mp3', 'docx', 'doc', 'xlsx', 'csv', 'png', 'jpg'];
    $uploaded_files = [];
    $dest_dir = __DIR__ . '/../files/uploads/';
    if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

    // 1. Array inputs: ebook_files[] & file_titles[]
    if (isset($_FILES['ebook_files']) && is_array($_FILES['ebook_files']['name'])) {
        $count = count($_FILES['ebook_files']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['ebook_files']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['ebook_files']['name'][$i]);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (in_array($file_ext, $allowed_file_exts)) {
                    $new_name = uniqid('file_' . ($i + 1) . '_', true) . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['ebook_files']['tmp_name'][$i], $dest_dir . $new_name)) {
                        $f_title = !empty($_POST['file_titles'][$i]) ? trim($_POST['file_titles'][$i]) : ($i === 0 ? 'Main eBook Guide' : 'Bonus Toolkit #' . ($i + 1));
                        $uploaded_files[] = [
                            'title' => $f_title,
                            'file'  => 'files/uploads/' . $new_name
                        ];
                    }
                }
            }
        }
    }

    // 2. Single legacy file fallback: ebook_file
    if (isset($_FILES['ebook_file']) && $_FILES['ebook_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['ebook_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_file_exts)) {
            $new_name = uniqid('ebook_', true) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $dest_dir . $new_name)) {
                $uploaded_files[] = [
                    'title' => 'Main eBook Guide',
                    'file'  => 'files/uploads/' . $new_name
                ];
            }
        }
    }

    $file_path = !empty($uploaded_files) ? json_encode($uploaded_files, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

    if (!empty($title) && !empty($slug) && !empty($file_path)) {
        try {
            $stmt = $db->prepare("INSERT INTO products (title, slug, price, original_price, file_path, category, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $price, $original_price, $file_path, $category, $description, $cover_image_path]);
            $product_success = "Product added successfully with " . count($uploaded_files) . " attached file(s)!";
        } catch (Exception $e) {
            $product_error = "Error adding product: " . $e->getMessage();
        }
    } else {
        $product_error = "Title, slug, and at least one E-Book file are required.";
    }
}

/**
 * Synchronize prices on static landing pages when an ebook is edited in Admin
 */
function syncLandingPagePrice($slug, $new_price, $new_orig_price) {
    $new_price_num = (int)$new_price;
    $new_orig_num  = (int)$new_orig_price;
    $new_savings   = max(0, $new_orig_num - $new_price_num);

    $files = [];
    $base_dir = dirname(__DIR__);

    if ($slug === 'positive-thinking') {
        $files[] = $base_dir . '/positive-thinking.html';
        $desktop_path = 'C:/Users/Administrator/Desktop/positive-thinking.html';
        if (file_exists($desktop_path)) {
            $files[] = $desktop_path;
        }
    } else {
        $candidate = $base_dir . '/' . $slug . '.html';
        if (file_exists($candidate)) {
            $files[] = $candidate;
        }
    }

    foreach ($files as $file_path) {
        if (!file_exists($file_path)) continue;
        $content = file_get_contents($file_path);
        if (!$content) continue;

        // Replace specific price classes in HTML
        $content = preg_replace('/(<span class="nav-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="hero-current-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="hero-original-price">)[^<]*(<\/span>)/i', '$1₹' . $new_orig_num . '$2', $content);
        $content = preg_replace('/(<span class="hero-savings-pill">)[^<]*(<\/span>)/i', '$1₹' . $new_savings . ' बचाएं (One-Time)$2', $content);
        $content = preg_replace('/(<div class="offer-final-price">)[^<]*(<\/div>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="sticky-bar-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<div class="modal-summary-price">)[^<]*(<\/div>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="offer-title-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="toast-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);

        // Replace CTA button texts containing ₹... in trigger-checkout buttons
        $content = preg_replace('/(class="[^"]*trigger-checkout[^"]*"[^>]*>[^<]*[—\-–]\s*)₹\d+/u', '${1}₹' . $new_price_num, $content);
        $content = preg_replace('/(id="checkout-submit-btn"[^>]*>[^<]*[—\-–]\s*)₹\d+/u', '${1}₹' . $new_price_num, $content);

        file_put_contents($file_path, $content);
    }
}

// Handle editing an existing product
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $prod_id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $original_price = (float)($_POST['original_price'] ?? 0);

    if ($prod_id > 0 && !empty($title) && !empty($slug) && $price > 0) {
        try {
            // Fetch existing product details
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$prod_id]);
            $current_product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current_product) {
                $cover_image_path = $current_product['cover_image'];
                $file_path = $current_product['file_path'];

                // Handle Cover Image upload if provided
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $img_name = basename($_FILES['cover_image']['name']);
                    $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                    if (in_array($img_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $dest_dir = __DIR__ . '/../assets/uploads/';
                        if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);
                        $new_name = uniqid('cover_', true) . '.' . $img_ext;
                        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $dest_dir . $new_name)) {
                            $cover_image_path = 'assets/uploads/' . $new_name;
                        }
                    }
                }

                $allowed_file_exts = ['pdf', 'zip', 'epub', 'mobi', 'mp3', 'docx', 'doc', 'xlsx', 'csv', 'png', 'jpg'];
                $dest_dir = __DIR__ . '/../files/uploads/';
                if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

                $updated_files = [];

                // 1. Process existing files preserved or updated in edit modal
                if (isset($_POST['existing_file_paths']) && is_array($_POST['existing_file_paths'])) {
                    foreach ($_POST['existing_file_paths'] as $idx => $curr_path) {
                        $curr_path = trim($curr_path);
                        if (empty($curr_path)) continue;
                        $ftitle = !empty($_POST['existing_file_titles'][$idx]) ? trim($_POST['existing_file_titles'][$idx]) : 'Guide File';

                        // Check if a replacement file was uploaded for this item
                        if (isset($_FILES['replace_files']['name'][$idx]) && $_FILES['replace_files']['error'][$idx] === UPLOAD_ERR_OK) {
                            $r_name = basename($_FILES['replace_files']['name'][$idx]);
                            $r_ext = strtolower(pathinfo($r_name, PATHINFO_EXTENSION));
                            if (in_array($r_ext, $allowed_file_exts)) {
                                $r_new_name = uniqid('file_rep_', true) . '.' . $r_ext;
                                if (move_uploaded_file($_FILES['replace_files']['tmp_name'][$idx], $dest_dir . $r_new_name)) {
                                    $curr_path = 'files/uploads/' . $r_new_name;
                                }
                            }
                        }

                        $updated_files[] = [
                            'title' => $ftitle,
                            'file'  => $curr_path
                        ];
                    }
                }

                // 2. Process newly attached files added in edit modal
                if (isset($_FILES['new_ebook_files']) && is_array($_FILES['new_ebook_files']['name'])) {
                    $new_count = count($_FILES['new_ebook_files']['name']);
                    for ($j = 0; $j < $new_count; $j++) {
                        if ($_FILES['new_ebook_files']['error'][$j] === UPLOAD_ERR_OK) {
                            $new_fname = basename($_FILES['new_ebook_files']['name'][$j]);
                            $new_ext = strtolower(pathinfo($new_fname, PATHINFO_EXTENSION));
                            if (in_array($new_ext, $allowed_file_exts)) {
                                $new_dest_name = uniqid('file_extra_', true) . '.' . $new_ext;
                                if (move_uploaded_file($_FILES['new_ebook_files']['tmp_name'][$j], $dest_dir . $new_dest_name)) {
                                    $new_title = !empty($_POST['new_file_titles'][$j]) ? trim($_POST['new_file_titles'][$j]) : 'Bonus Toolkit #' . ($j + 1);
                                    $updated_files[] = [
                                        'title' => $new_title,
                                        'file'  => 'files/uploads/' . $new_dest_name
                                    ];
                                }
                            }
                        }
                    }
                }

                // 3. Fallback: single ebook_file input if submitted
                if (isset($_FILES['ebook_file']) && $_FILES['ebook_file']['error'] === UPLOAD_ERR_OK) {
                    $file_name = basename($_FILES['ebook_file']['name']);
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    if (in_array($file_ext, $allowed_file_exts)) {
                        $new_name = uniqid('ebook_', true) . '.' . $file_ext;
                        if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $dest_dir . $new_name)) {
                            if (!empty($updated_files)) {
                                $updated_files[0]['file'] = 'files/uploads/' . $new_name;
                            } else {
                                $updated_files[] = [
                                    'title' => 'Main Guide',
                                    'file'  => 'files/uploads/' . $new_name
                                ];
                            }
                        }
                    }
                }

                // If updated_files has content, store as JSON.
                if (!empty($updated_files)) {
                    $file_path = json_encode($updated_files, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } elseif (!isset($_POST['files_managed'])) {
                    // Retain untouched file_path if files manager wasn't rendered
                    $file_path = $current_product['file_path'];
                }

                $update_stmt = $db->prepare("UPDATE products SET title = ?, slug = ?, price = ?, original_price = ?, file_path = ?, category = ?, description = ?, cover_image = ? WHERE id = ?");
                $update_stmt->execute([$title, $slug, $price, $original_price, $file_path, $category, $description, $cover_image_path, $prod_id]);

                // Synchronize price with landing page
                syncLandingPagePrice($slug, $price, $original_price);

                $product_success = "E-Book '{$title}' updated successfully! Price set to ₹{$price} (" . count($updated_files) . " file(s) saved).";
            } else {
                $product_error = "Product not found.";
            }
        } catch (Exception $e) {
            $product_error = "Error updating product: " . $e->getMessage();
        }
    } else {
        $product_error = "Please fill in all required product details with a valid price.";
    }
}

// Handle deleting a product
if ($authenticated && isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['id'])) {
    $prod_id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$prod_id]);
        header('Location: index.php?msg=deleted');
        exit;
    } catch (Exception $e) {
        $product_error = "Error deleting product: " . $e->getMessage();
    }
}

// Handle CSV Database Export Request
if ($authenticated && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tatvam_customers_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Name', 'Email Address', 'WhatsApp Phone', 'Product Purchased', 'Amount', 'Date']);

    $stmt = $db->query("SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id WHERE orders.payment_status = 'paid' ORDER BY orders.id DESC");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['title'],
            'INR ' . $row['amount'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Fetch Metrics if authenticated
$total_revenue = 0.00;
$total_orders = 0;
$average_order_value = 0.00;
$recent_orders = [];
$all_products = [];

if ($authenticated) {
    try {
        $revenue_stmt = $db->query("SELECT SUM(amount) FROM orders WHERE payment_status = 'paid'");
        $total_revenue = (float)$revenue_stmt->fetchColumn();

        $orders_stmt = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
        $total_orders = (int)$orders_stmt->fetchColumn();

        if ($total_orders > 0) {
            $average_order_value = $total_revenue / $total_orders;
        }

        $recent_stmt = $db->query("SELECT orders.*, products.title FROM orders JOIN products ON orders.product_id = products.id ORDER BY orders.id DESC LIMIT 30");
        $recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

        $products_stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
        $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($all_products as &$p) {
            $p['files'] = getProductFiles($p['file_path'], $p['title']);
        }
        unset($p);

    } catch (Exception $e) {
        $db_error = "Failed to query analytical metrics: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | TATVAM Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
 
    <!-- Master CSS -->
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="admin-wrapper" style="padding-top: 0;">

    <!-- BACKGROUND CANVAS -->
    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <?php if (!$authenticated): ?>
        <!-- LOGIN PORTAL VIEW -->
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, var(--color-bg-2) 0%, var(--color-bg-1) 100%);">
            <div class="glass-card" style="width: 100%; max-width: 400px; padding: var(--space-md); border-color: var(--color-border-gold); z-index: 2; position: relative;">
                <div style="text-align: center; margin-bottom: var(--space-md);">
                    <h2 class="gradient-gold" style="font-size: 1.85rem; margin-bottom: 0.25rem;">TATVAM Admin</h2>
                    <p>Enter credentials to access revenue panel.</p>
                </div>

                <?php if (isset($login_error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.9rem; text-align: center;">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <input type="text" name="username" id="username" class="form-input" required placeholder=" ">
                        <label for="username" class="form-label">Username</label>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <input type="password" name="password" id="password" class="form-input" required placeholder=" ">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Authorize Panel <i data-lucide="shield-check"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- AUTHENTICATED PANEL VIEW -->
        <header class="admin-header" style="position: relative; z-index: 10; padding: var(--space-sm) 0; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(3,5,12,0.5);">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="admin-logo" style="font-size: 1.5rem;">TATVAM<span>.</span> <span style="font-size: 0.95rem; font-weight: 400; color: var(--color-text-slate); margin-left: 0.5rem;">Admin Panel</span></h1>
                <div style="display: flex; gap: var(--space-sm); align-items: center;">
                    <a href="?export=csv" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #FFE082 0%, var(--color-gold) 100%); color: #000; box-shadow: none;"><i data-lucide="download"></i> Export Customers (CSV)</a>
                    <a href="?action=logout" class="btn btn-secondary btn-sm" style="background: transparent; color: #EF4444; border-color: rgba(239,68,68,0.2);"><i data-lucide="log-out"></i> Logout</a>
                </div>
            </div>
        </header>

        <main class="container" style="padding-top: var(--space-md); padding-bottom: var(--space-lg); position: relative; z-index: 2;">
            
            <!-- Admin Navigation Tabs -->
            <div style="display: flex; gap: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: var(--space-xs); margin-bottom: var(--space-md);">
                <button class="tab-btn active" id="btn-orders" onclick="switchTab('orders-tab')" style="background: none; border: none; color: var(--color-text-white); font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; cursor: pointer; padding-bottom: var(--space-xs); border-bottom: 2px solid var(--color-gold); transition: all 0.2s;">Sales & Orders</button>
                <button class="tab-btn" id="btn-products" onclick="switchTab('products-tab')" style="background: none; border: none; color: var(--color-text-slate); font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; cursor: pointer; padding-bottom: var(--space-xs); border-bottom: 2px solid transparent; transition: all 0.2s;">Manage Ebooks</button>
            </div>

            <!-- TAB 1: ORDERS & ANALYTICS -->
            <div id="orders-tab">
                <!-- Metrics Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-lg);">
                    <div class="glass-card">
                        <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-text-slate);">Total Sales Volume</h4>
                        <p class="gradient-gold" style="font-size: 1.85rem; font-weight: 800; margin-top: 0.25rem;">INR <?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                    <div class="glass-card">
                        <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-text-slate);">Total Orders (Paid)</h4>
                        <p class="gradient-purple" style="font-size: 1.85rem; font-weight: 800; margin-top: 0.25rem; color: var(--color-primary);"><?php echo $total_orders; ?> Purchases</p>
                    </div>
                    <div class="glass-card">
                        <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-text-slate);">Average Order Value</h4>
                        <p style="font-size: 1.85rem; font-weight: 800; margin-top: 0.25rem; color: #fff;">INR <?php echo number_format($average_order_value, 2); ?></p>
                    </div>
                </div>

                <!-- Table Block -->
                <div class="section-head" style="text-align: left; margin-bottom: var(--space-sm); max-width: 100%;">
                    <h2 style="font-size: 1.75rem;">Recent Order Pipeline</h2>
                    <p>Real-time log of the latest 30 transactions.</p>
                </div>

                <div class="admin-table-container" style="background: var(--panel-bg); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-light); color: var(--color-text-slate);">
                                <th style="padding: 1rem;">Order ID</th>
                                <th style="padding: 1rem;">Customer Details</th>
                                <th style="padding: 1rem;">Product Name</th>
                                <th style="padding: 1rem;">Amount</th>
                                <th style="padding: 1rem;">Status</th>
                                <th style="padding: 1rem;">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) > 0): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                        <td style="padding: 1rem;">#<?php echo $order['id']; ?></td>
                                        <td style="padding: 1rem;">
                                            <div style="font-weight: 600; color: var(--color-text-white);"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($order['customer_email']); ?> | <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                        </td>
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($order['title']); ?></td>
                                        <td style="padding: 1rem;">INR <?php echo number_format($order['amount'], 2); ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($order['payment_status'] === 'paid'): ?>
                                                <span style="color: #10B981; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600;">Paid</span>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-slate); background: rgba(255,255,255,0.05); padding: 2px 8px; border-radius: var(--radius-full); font-size: 0.8rem;">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--color-text-slate); padding: var(--space-md) 0;">No transactions registered in database yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: MANAGE EBOOKS -->
            <div id="products-tab" style="display: none;">
                
                <?php if (isset($product_success)): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10B981; padding: 1rem; border-radius: var(--radius-md); margin-bottom: var(--space-md);">
                        <?php echo $product_success; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($product_error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 1rem; border-radius: var(--radius-md); margin-bottom: var(--space-md);">
                        <?php echo $product_error; ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: var(--space-lg); align-items: start;">
                    <!-- Add Product Card -->
                    <div class="glass-card" style="padding: var(--space-md);">
                        <h3 style="font-size: 1.5rem; margin-bottom: var(--space-sm); color: var(--color-gold);">Upload New E-Book</h3>
                        <form method="POST" action="index.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_product">
                            
                            <div class="form-group">
                                <input type="text" name="title" id="p-title" class="form-input" required placeholder=" ">
                                <label for="p-title" class="form-label">E-Book Title</label>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="slug" id="p-slug" class="form-input" required placeholder=" ">
                                <label for="p-slug" class="form-label">URL Slug (e.g. positive-thinking)</label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm);">
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
                                <label for="p-desc" class="form-label" style="top: 0.6rem;">E-Book Short Description</label>
                            </div>

                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-light); padding: var(--space-sm); border-radius: var(--radius-sm);">
                                <label style="display: block; font-size: 0.8rem; color: var(--color-text-slate); margin-bottom: var(--space-xxs);">E-Book Cover Image (JPG / PNG)</label>
                                <input type="file" name="cover_image" accept="image/*" required style="font-size: 0.9rem; color: var(--color-text-slate);">
                            </div>

                            <!-- Multiple Files Upload Container -->
                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-light); padding: var(--space-sm); border-radius: var(--radius-sm);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <label style="font-size: 0.85rem; font-weight: 600; color: #fff; margin: 0;">Files / Downloads (eBook + Toolkits)</label>
                                    <button type="button" onclick="addNewUploadFileRow()" style="background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: var(--color-gold); border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <i data-lucide="plus" style="width: 12px; height: 12px;"></i> + Add Another File
                                    </button>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--color-text-slate); margin-bottom: 10px;">Aap ek sath multiple files add kar sakte hain (e.g. Main eBook PDF + Bonus Toolkit/Workbook).</p>
                                
                                <div id="upload-files-container" style="display: flex; flex-direction: column; gap: 8px;">
                                    <div class="upload-file-row" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); padding: 8px 10px; border-radius: var(--radius-sm);">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; align-items: center;">
                                            <div>
                                                <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File 1 Title:</label>
                                                <input type="text" name="file_titles[]" value="Main E-Book Guide" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                                            </div>
                                            <div>
                                                <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File (PDF, ZIP, etc.):</label>
                                                <input type="file" name="ebook_files[]" accept=".pdf,.zip,.epub,.docx,.mp3" required style="font-size: 0.8rem; color: var(--color-text-slate); width: 100%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Upload E-Book <i data-lucide="plus-circle"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Products List Grid -->
                    <div>
                        <h3 style="font-size: 1.5rem; margin-bottom: var(--space-sm); color: var(--color-primary);">Current Catalog</h3>
                        <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                            <?php foreach ($all_products as $prod): ?>
                                <?php 
                                    $p_files = $prod['files'] ?? getProductFiles($prod['file_path'], $prod['title']);
                                    $file_count = count($p_files);
                                ?>
                                <div class="glass-card" style="display: flex; gap: var(--space-sm); align-items: center; padding: var(--space-xs);">
                                    <img src="../<?php echo htmlspecialchars($prod['cover_image']); ?>" onerror="this.src='../assets/book-cover.jpg';" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                                    <div style="flex: 1; min-width: 0;">
                                        <h4 style="font-size: 0.95rem; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($prod['title']); ?></h4>
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 2px;">
                                            <span style="font-size: 0.75rem; color: var(--color-text-slate); background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 3px;"><?php echo htmlspecialchars($prod['slug']); ?></span>
                                            <span style="font-size: 0.75rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($prod['category']); ?></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; flex-wrap: wrap; gap: 4px;">
                                            <div style="font-size: 0.9rem; font-weight: bold; color: var(--color-gold);">
                                                ₹<?php echo $prod['price']; ?> <del style="font-weight: normal; font-size: 0.75rem; color: var(--color-text-slate); margin-left: 4px;">₹<?php echo $prod['original_price']; ?></del>
                                            </div>
                                            <span style="font-size: 0.72rem; color: var(--color-gold); background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.25); border-radius: 3px; padding: 1px 6px; display: inline-flex; align-items: center; gap: 3px;" title="<?php echo htmlspecialchars(implode(', ', array_column($p_files, 'title'))); ?>">
                                                <i data-lucide="paperclip" style="width: 10px; height: 10px;"></i> <?php echo $file_count . ' ' . ($file_count === 1 ? 'File' : 'Files'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <button type="button" onclick='openEditModal(<?php echo json_encode($prod, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' style="color: var(--color-gold); background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: var(--radius-sm); padding: 8px 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 600;" title="Edit E-Book & Price">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i> Edit
                                        </button>
                                        <a href="?action=delete_product&id=<?php echo $prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color: #EF4444; background: rgba(239,68,68,0.08); padding: 8px; border: 1px solid rgba(239,68,68,0.25); border-radius: var(--radius-sm); display: inline-flex;" title="Delete Product">
                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- EDIT EBOOK MODAL -->
        <div class="modal-overlay" id="edit-product-modal" style="display: none; z-index: 3000;">
            <div class="modal-card" style="max-width: 560px; max-height: 90vh; overflow-y: auto; border-color: rgba(251, 191, 36, 0.4); box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
                <button type="button" class="modal-close" onclick="closeEditModal()" style="font-size: 1.8rem; line-height: 1; top: 12px; right: 16px; background: none; border: none; color: #fff; cursor: pointer;">&times;</button>
                
                <div style="margin-bottom: var(--space-sm);">
                    <h3 style="font-size: 1.35rem; color: var(--color-gold); display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <i data-lucide="edit-3" style="width: 20px; height: 20px;"></i> Edit E-Book Details
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--color-text-slate);">Change price, title, or replace files. Updates take effect immediately.</p>
                </div>

                <form method="POST" action="index.php" enctype="multipart/form-data" id="edit-product-form">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="id" id="edit-id" value="">

                    <div class="form-group">
                        <input type="text" name="title" id="edit-title" class="form-input" required placeholder=" ">
                        <label for="edit-title" class="form-label">E-Book Title</label>
                    </div>

                    <div class="form-group">
                        <input type="text" name="slug" id="edit-slug" class="form-input" required placeholder=" ">
                        <label for="edit-slug" class="form-label">URL Slug (e.g. positive-thinking)</label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm);">
                        <div class="form-group">
                            <input type="number" step="0.01" name="price" id="edit-price" class="form-input" required placeholder=" ">
                            <label for="edit-price" class="form-label">Selling Price (INR)</label>
                        </div>
                        <div class="form-group">
                            <input type="number" step="0.01" name="original_price" id="edit-orig-price" class="form-input" required placeholder=" ">
                            <label for="edit-orig-price" class="form-label">Original Price (INR)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" name="category" id="edit-cat" class="form-input" required placeholder=" ">
                        <label for="edit-cat" class="form-label">Category (e.g. mindset, peace, wealth)</label>
                    </div>

                    <div class="form-group">
                        <textarea name="description" id="edit-desc" class="form-input" rows="3" placeholder=" " style="resize: none; padding-top: 1rem;"></textarea>
                        <label for="edit-desc" class="form-label" style="top: 0.6rem;">E-Book Short Description</label>
                    </div>

                    <!-- Cover Image Preview & Replacement -->
                    <div class="form-group" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-light); padding: var(--space-sm); border-radius: var(--radius-sm);">
                        <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                            <img id="edit-cover-preview" src="../assets/book-cover.jpg" style="width: 44px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.15);">
                            <div>
                                <span style="display: block; font-size: 0.85rem; font-weight: 600; color: #fff;">Cover Image</span>
                                <span style="font-size: 0.75rem; color: var(--color-text-slate);">Upload a new file only if you want to replace it:</span>
                            </div>
                        </div>
                        <input type="file" name="cover_image" accept="image/*" style="font-size: 0.85rem; color: var(--color-text-slate);">
                    </div>

                    <input type="hidden" name="files_managed" value="1">

                    <!-- PDF / ZIP Attached Files & Toolkits Management -->
                    <div class="form-group" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-light); padding: var(--space-sm); border-radius: var(--radius-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                            <div>
                                <span style="display: block; font-size: 0.85rem; font-weight: 600; color: #fff;">Attached Files & Toolkits</span>
                                <span style="font-size: 0.72rem; color: var(--color-text-slate);">Manage main guide and companion bonus files:</span>
                            </div>
                            <button type="button" onclick="addEditNewFileRow()" style="background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: var(--color-gold); border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                <i data-lucide="plus" style="width: 12px; height: 12px;"></i> + Attach Another File
                            </button>
                        </div>

                        <!-- Current Attached Files List -->
                        <div id="edit-files-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px;">
                            <!-- Populated dynamically by openEditModal() -->
                        </div>

                        <!-- Newly Attached Files in Edit Modal -->
                        <div id="edit-new-files-container" style="display: flex; flex-direction: column; gap: 8px;">
                            <!-- Dynamically added when clicking + Attach Another File -->
                        </div>
                    </div>

                    <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-sm);">
                        <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                            <i data-lucide="check-circle-2"></i> Save Changes & Sync Price
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()" style="padding: 0 1.25rem;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            <?php if (isset($product_success) || isset($product_error) || (isset($_GET['msg']) && $_GET['msg'] === 'deleted')): ?>
            switchTab('products-tab');
            <?php endif; ?>
        });

        function switchTab(tabId) {
            const ordersTab = document.getElementById('orders-tab');
            const productsTab = document.getElementById('products-tab');
            const btnOrders = document.getElementById('btn-orders');
            const btnProducts = document.getElementById('btn-products');

            if (ordersTab) ordersTab.style.display = tabId === 'orders-tab' ? 'block' : 'none';
            if (productsTab) productsTab.style.display = tabId === 'products-tab' ? 'block' : 'none';
            
            if (btnOrders) {
                btnOrders.classList.toggle('active', tabId === 'orders-tab');
                btnOrders.style.color = tabId === 'orders-tab' ? 'var(--color-text-white)' : 'var(--color-text-slate)';
                btnOrders.style.borderBottomColor = tabId === 'orders-tab' ? 'var(--color-gold)' : 'transparent';
            }
            if (btnProducts) {
                btnProducts.classList.toggle('active', tabId === 'products-tab');
                btnProducts.style.color = tabId === 'products-tab' ? 'var(--color-text-white)' : 'var(--color-text-slate)';
                btnProducts.style.borderBottomColor = tabId === 'products-tab' ? 'var(--color-gold)' : 'transparent';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Add dynamic row in "Upload New E-Book" form
        function addNewUploadFileRow() {
            const container = document.getElementById('upload-files-container');
            const rowCount = container.querySelectorAll('.upload-file-row').length + 1;
            const row = document.createElement('div');
            row.className = 'upload-file-row';
            row.style.cssText = 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); padding: 8px 10px; border-radius: var(--radius-sm); position: relative;';
            row.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600;">File #${rowCount} (Bonus / Toolkit)</span>
                    <button type="button" onclick="this.closest('.upload-file-row').remove()" style="background: none; border: none; color: #EF4444; cursor: pointer; font-size: 0.75rem; padding: 0 4px;" title="Remove this file">&times; Remove</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title:</label>
                        <input type="text" name="file_titles[]" placeholder="e.g. Companion Toolkit / Action Sheets" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File (PDF, ZIP, etc.):</label>
                        <input type="file" name="ebook_files[]" accept=".pdf,.zip,.epub,.docx,.mp3" required style="font-size: 0.8rem; color: var(--color-text-slate); width: 100%;">
                    </div>
                </div>
            `;
            container.appendChild(row);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Add dynamic row in "Edit E-Book" modal
        function addEditNewFileRow() {
            const container = document.getElementById('edit-new-files-container');
            const row = document.createElement('div');
            row.className = 'edit-new-file-row';
            row.style.cssText = 'background: rgba(251, 191, 36, 0.05); border: 1px dashed rgba(251, 191, 36, 0.35); border-radius: var(--radius-sm); padding: 8px 10px;';
            row.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600;">+ New Attached File / Bonus</span>
                    <button type="button" onclick="this.closest('.edit-new-file-row').remove()" style="background: none; border: none; color: #EF4444; font-size: 0.75rem; cursor: pointer;">&times; Remove</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title:</label>
                        <input type="text" name="new_file_titles[]" placeholder="e.g. Companion Toolkit / Workbook" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File (PDF, ZIP, etc.):</label>
                        <input type="file" name="new_ebook_files[]" accept=".pdf,.zip,.epub,.docx,.mp3" required style="font-size: 0.75rem; color: var(--color-text-slate); width: 100%;">
                    </div>
                </div>
            `;
            container.appendChild(row);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function openEditModal(prod) {
            document.getElementById('edit-id').value = prod.id || '';
            document.getElementById('edit-title').value = prod.title || '';
            document.getElementById('edit-slug').value = prod.slug || '';
            document.getElementById('edit-price').value = prod.price || '';
            document.getElementById('edit-orig-price').value = prod.original_price || '';
            document.getElementById('edit-cat').value = prod.category || '';
            document.getElementById('edit-desc').value = prod.description || '';
            
            const coverPreview = document.getElementById('edit-cover-preview');
            if (coverPreview) {
                coverPreview.src = '../' + (prod.cover_image || 'assets/book-cover.jpg');
            }

            // Populate Attached Files
            const filesList = document.getElementById('edit-files-list');
            filesList.innerHTML = '';
            const newFilesContainer = document.getElementById('edit-new-files-container');
            newFilesContainer.innerHTML = '';

            let files = prod.files || [];
            if ((!files || files.length === 0) && prod.file_path) {
                try {
                    const parsed = JSON.parse(prod.file_path);
                    if (Array.isArray(parsed)) files = parsed;
                } catch (e) {
                    files = [{ title: 'Main eBook', file: prod.file_path }];
                }
            }

            if (!files || files.length === 0) {
                filesList.innerHTML = '<p style="font-size: 0.75rem; color: var(--color-text-slate); padding: 4px 0;">No files currently attached.</p>';
            } else {
                files.forEach((f, idx) => {
                    const fTitle = f.title || ('File #' + (idx + 1));
                    const fPath = f.file || '';
                    const fName = fPath.split('/').pop() || fPath;

                    const div = document.createElement('div');
                    div.className = 'edit-file-item';
                    div.style.cssText = 'background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-sm); padding: 8px 10px;';
                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i data-lucide="file-text" style="width: 12px; height: 12px;"></i> File #${idx + 1}
                            </span>
                            <button type="button" onclick="this.closest('.edit-file-item').remove()" style="background: none; border: none; color: #EF4444; font-size: 0.72rem; cursor: pointer;" title="Remove this file">&times; Delete File</button>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <label style="display: block; font-size: 0.68rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title / Label:</label>
                                <input type="text" name="existing_file_titles[${idx}]" value="${escapeHtml(fTitle)}" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                                <input type="hidden" name="existing_file_paths[${idx}]" value="${escapeHtml(fPath)}">
                            </div>
                            <div>
                                <div style="font-size: 0.68rem; color: var(--color-text-slate); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Current: <code style="color: #93C5FD; font-size: 0.68rem;">${escapeHtml(fName)}</code></div>
                                <label style="display: block; font-size: 0.65rem; color: var(--color-text-slate); margin-bottom: 2px;">Replace file (optional):</label>
                                <input type="file" name="replace_files[${idx}]" accept=".pdf,.zip,.epub,.docx,.mp3" style="font-size: 0.72rem; color: var(--color-text-slate); width: 100%;">
                            </div>
                        </div>
                    `;
                    filesList.appendChild(div);
                });
            }

            const modal = document.getElementById('edit-product-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-product-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        // Close on backdrop click
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('edit-product-modal');
            if (modal && e.target === modal) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
