<?php
/**
 * TATVAM - Advanced SaaS Admin Dashboard & Order Management
 * Includes:
 * - Real-time Sales & Revenue Analytics
 * - 1-Click WhatsApp E-Book Download Delivery (for Paid Orders)
 * - 1-Click WhatsApp Abandoned Checkout Recovery (for Pending Orders)
 * - 1-Click Copy Download Link with Clipboard Toast
 * - Instant Order Search & Status Filter Tabs (All / Paid / Pending)
 * - 1-Click Mark as Paid with Auto-Token Generation
 * - Product Catalog, Pricing Sync & Multi-file Uploads
 */

session_start();
require_once __DIR__ . '/../db.php';

// Administrative Login Session Gate
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'TATVAMAdmin108');

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

// Flash Message helper
$flash_msg = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

// 1. Action: Mark Order as Paid (Manual UPI payment confirmation)
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
    $order_id = (int)$_POST['order_id'];
    if ($order_id > 0) {
        $token = bin2hex(random_bytes(16));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', download_token = ?, token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $order_id]);
        $_SESSION['flash_msg'] = "Order #{$order_id} marked as Paid! Download token generated.";
    }
    header('Location: index.php');
    exit;
}

// 2. Action: Mark Order as Pending
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_pending') {
    $order_id = (int)$_POST['order_id'];
    if ($order_id > 0) {
        $stmt = $db->prepare("UPDATE orders SET payment_status = 'pending' WHERE id = ?");
        $stmt->execute([$order_id]);
        $_SESSION['flash_msg'] = "Order #{$order_id} updated to Pending.";
    }
    header('Location: index.php');
    exit;
}

// 3. Action: Delete Order
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $order_id = (int)$_POST['order_id'];
    if ($order_id > 0) {
        $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $_SESSION['flash_msg'] = "Order #{$order_id} deleted successfully.";
    }
    header('Location: index.php');
    exit;
}

// 4. Action: Regenerate Download Token
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'regen_token') {
    $order_id = (int)$_POST['order_id'];
    if ($order_id > 0) {
        $token = bin2hex(random_bytes(16));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt = $db->prepare("UPDATE orders SET download_token = ?, token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $order_id]);
        $_SESSION['flash_msg'] = "New 30-day download token generated for Order #{$order_id}.";
    }
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
    $cover_image_path = 'assets/book-cover.jpg';
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

        $content = preg_replace('/(<span class="nav-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="hero-current-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="hero-original-price">)[^<]*(<\/span>)/i', '$1₹' . $new_orig_num . '$2', $content);
        $content = preg_replace('/(<div class="offer-final-price">)[^<]*(<\/div>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<span class="sticky-bar-price">)[^<]*(<\/span>)/i', '$1₹' . $new_price_num . '$2', $content);
        $content = preg_replace('/(<div class="modal-summary-price">)[^<]*(<\/div>)/i', '$1₹' . $new_price_num . '$2', $content);

        file_put_contents($file_path, $content);
    }
}

// Handle editing an existing product
if ($authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $prod_id = (int)$_POST['id'];
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $slug = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $price = (float)$_POST['price'];
    $original_price = (float)$_POST['original_price'];

    if ($prod_id > 0 && !empty($title) && !empty($slug) && $price > 0) {
        try {
            $cur_stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $cur_stmt->execute([$prod_id]);
            $current_product = $cur_stmt->fetch(PDO::FETCH_ASSOC);

            if ($current_product) {
                $cover_image_path = $current_product['cover_image'];
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
                $updated_files = [];
                $dest_dir = __DIR__ . '/../files/uploads/';
                if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

                if (isset($_POST['existing_file_titles']) && is_array($_POST['existing_file_titles'])) {
                    foreach ($_POST['existing_file_titles'] as $idx => $f_title) {
                        $f_title = trim($f_title);
                        $f_path  = trim($_POST['existing_file_paths'][$idx] ?? '');

                        if (isset($_FILES['replace_files']['name'][$idx]) && $_FILES['replace_files']['error'][$idx] === UPLOAD_ERR_OK) {
                            $r_name = basename($_FILES['replace_files']['name'][$idx]);
                            $r_ext = strtolower(pathinfo($r_name, PATHINFO_EXTENSION));
                            if (in_array($r_ext, $allowed_file_exts)) {
                                $new_name = uniqid('file_rep_', true) . '.' . $r_ext;
                                if (move_uploaded_file($_FILES['replace_files']['tmp_name'][$idx], $dest_dir . $new_name)) {
                                    $f_path = 'files/uploads/' . $new_name;
                                }
                            }
                        }

                        if (!empty($f_title) && !empty($f_path)) {
                            $updated_files[] = [
                                'title' => $f_title,
                                'file'  => $f_path
                            ];
                        }
                    }
                }

                if (isset($_FILES['new_ebook_files']) && is_array($_FILES['new_ebook_files']['name'])) {
                    $new_count = count($_FILES['new_ebook_files']['name']);
                    for ($i = 0; $i < $new_count; $i++) {
                        if ($_FILES['new_ebook_files']['error'][$i] === UPLOAD_ERR_OK) {
                            $file_name = basename($_FILES['new_ebook_files']['name'][$i]);
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            if (in_array($file_ext, $allowed_file_exts)) {
                                $new_name = uniqid('file_extra_', true) . '.' . $file_ext;
                                if (move_uploaded_file($_FILES['new_ebook_files']['tmp_name'][$i], $dest_dir . $new_name)) {
                                    $f_title = !empty($_POST['new_file_titles'][$i]) ? trim($_POST['new_file_titles'][$i]) : 'Extra Resource #' . ($i + 1);
                                    $updated_files[] = [
                                        'title' => $f_title,
                                        'file'  => 'files/uploads/' . $new_name
                                    ];
                                }
                            }
                        }
                    }
                }

                if (isset($_POST['files_managed']) && $_POST['files_managed'] === '1') {
                    $file_path = !empty($updated_files) ? json_encode($updated_files, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $current_product['file_path'];
                } else {
                    $file_path = $current_product['file_path'];
                }

                $update_stmt = $db->prepare("UPDATE products SET title = ?, slug = ?, price = ?, original_price = ?, file_path = ?, category = ?, description = ?, cover_image = ? WHERE id = ?");
                $update_stmt->execute([$title, $slug, $price, $original_price, $file_path, $category, $description, $cover_image_path, $prod_id]);

                syncLandingPagePrice($slug, $price, $original_price);
                $product_success = "E-Book '{$title}' updated successfully!";
            } else {
                $product_error = "Product not found.";
            }
        } catch (Exception $e) {
            $product_error = "Error updating product: " . $e->getMessage();
        }
    } else {
        $product_error = "Please fill in all required product details.";
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

// Handle CSV Export
if ($authenticated && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tatvam_orders_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Name', 'Email Address', 'Phone Number', 'Product', 'Amount', 'Status', 'Download Token', 'Date']);

    $stmt = $db->query("SELECT orders.*, products.title as product_title FROM orders LEFT JOIN products ON orders.product_id = products.id ORDER BY orders.id DESC");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['product_title'] ?? 'Product #' . $row['product_id'],
            'INR ' . $row['amount'],
            strtoupper($row['payment_status']),
            $row['download_token'] ?? '',
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Analytics and Orders Fetching
$total_revenue = 0.00;
$total_paid_orders = 0;
$total_pending_orders = 0;
$pending_revenue = 0.00;
$total_all_orders = 0;
$average_order_value = 0.00;
$conversion_rate = 0.00;
$recent_orders = [];
$all_products = [];

if ($authenticated) {
    try {
        // Auto-heal: Ensure all paid orders have a download token
        $missing_tokens = $db->query("SELECT id FROM orders WHERE payment_status = 'paid' AND (download_token IS NULL OR download_token = '')")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($missing_tokens)) {
            $tokStmt = $db->prepare("UPDATE orders SET download_token = ?, token_expiry = ? WHERE id = ?");
            foreach ($missing_tokens as $m_id) {
                $tokStmt->execute([bin2hex(random_bytes(16)), date('Y-m-d H:i:s', strtotime('+30 days')), $m_id]);
            }
        }

        // Metrics calculations
        $rev_stmt = $db->query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE payment_status = 'paid'");
        $total_revenue = (float)$rev_stmt->fetchColumn();

        $paid_stmt = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
        $total_paid_orders = (int)$paid_stmt->fetchColumn();

        $pend_stmt = $db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'");
        $total_pending_orders = (int)$pend_stmt->fetchColumn();

        $pend_rev_stmt = $db->query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE payment_status = 'pending'");
        $pending_revenue = (float)$pend_rev_stmt->fetchColumn();

        $all_stmt = $db->query("SELECT COUNT(*) FROM orders");
        $total_all_orders = (int)$all_stmt->fetchColumn();

        if ($total_paid_orders > 0) {
            $average_order_value = $total_revenue / $total_paid_orders;
        }

        if ($total_all_orders > 0) {
            $conversion_rate = round(($total_paid_orders / $total_all_orders) * 100, 1);
        }

        // Fetch recent orders with product info
        $recent_stmt = $db->query("SELECT orders.*, products.title as product_title, products.slug as product_slug 
                                  FROM orders 
                                  LEFT JOIN products ON orders.product_id = products.id 
                                  ORDER BY orders.id DESC LIMIT 150");
        $recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch catalog
        $products_stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
        $all_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($all_products as &$p) {
            $p['files'] = getProductFiles($p['file_path'], $p['title']);
        }
        unset($p);

    } catch (Exception $e) {
        $db_error = "Error querying metrics: " . $e->getMessage();
    }
}

// Helpers for WhatsApp formatting
function cleanWhatsAppPhone($phone) {
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    if (strlen($clean) === 10) {
        $clean = '91' . $clean;
    } elseif (strlen($clean) === 11 && str_starts_with($clean, '0')) {
        $clean = '91' . substr($clean, 1);
    }
    return $clean;
}

function getProductCheckoutLink($slug) {
    $s = strtolower(trim((string)$slug));
    if (str_contains($s, 'sanskar')) {
        return SITE_URL . '/sanskar30.php';
    } elseif (str_contains($s, 'bundle')) {
        return SITE_URL . '/bundle.html';
    } elseif (str_contains($s, 'positive')) {
        return SITE_URL . '/positive-thinking.html';
    } elseif (str_contains($s, 'stress') || str_contains($s, 'anxiety')) {
        return SITE_URL . '/stress-worry.html';
    } elseif (str_contains($s, 'habit') || str_contains($s, 'discipline')) {
        return SITE_URL . '/habit-freedom.html';
    } elseif (str_contains($s, 'wealth')) {
        return SITE_URL . '/wealth-mindset.html';
    } elseif (!empty($slug)) {
        return SITE_URL . '/product.php?slug=' . urlencode($slug);
    }
    return SITE_URL . '/sanskar30.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard & WhatsApp Automation | TATVAM</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
 
    <!-- Master CSS -->
    <link rel="stylesheet" href="../styles.css">

    <style>
        :root {
            --wa-green: #25D366;
            --wa-green-hover: #1EBE5D;
            --wa-dark: #075E54;
            --amber-recovery: #F59E0B;
        }
        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: #FFFFFF !important;
            border: 1px solid rgba(255,255,255,0.2);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
            transition: all 0.2s ease;
        }
        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
            filter: brightness(1.08);
        }
        .btn-recovery {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #FFFFFF !important;
            border: 1px solid rgba(255,255,255,0.2);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            transition: all 0.2s ease;
        }
        .btn-recovery:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
            filter: brightness(1.08);
        }
        .status-pill-paid {
            color: #10B981;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-pill-pending {
            color: #F59E0B;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--color-text-slate);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-btn.active, .filter-btn:hover {
            background: var(--color-gold);
            color: #000;
            border-color: var(--color-gold);
        }
        .toast-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #10B981;
            color: #FFFFFF;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideUpToast 0.3s ease forwards;
        }
        @keyframes slideUpToast {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="admin-wrapper" style="padding-top: 0; background: #03050c; color: #F1F5F9;">

    <!-- BACKGROUND CANVAS -->
    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <!-- Notification Toast -->
    <div id="toastBox" class="toast-box">
        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
        <span id="toastMsg">Link Copied to Clipboard!</span>
    </div>

    <?php if (!$authenticated): ?>
        <!-- LOGIN PORTAL VIEW -->
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, #0F172A 0%, #03050C 100%); padding: 1rem;">
            <div class="glass-card" style="width: 100%; max-width: 420px; padding: 2.25rem 2rem; border-color: rgba(251, 191, 36, 0.4); box-shadow: 0 20px 50px rgba(0,0,0,0.8); z-index: 2; position: relative;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.35); display: inline-flex; align-items: center; justify-content: center; color: var(--color-gold); margin-bottom: 12px;">
                        <i data-lucide="shield-check" style="width: 28px; height: 28px;"></i>
                    </div>
                    <h2 class="gradient-gold" style="font-size: 1.85rem; margin-bottom: 0.35rem; font-weight: 800;">TATVAM Admin</h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-slate);">Sign in to access sales pipeline & WhatsApp automation.</p>
                </div>

                <?php if (isset($login_error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #FCA5A5; padding: 0.85rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; text-align: center;">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <input type="text" name="username" id="username" class="form-input" required placeholder=" " autofocus>
                        <label for="username" class="form-label">Username</label>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.75rem;">
                        <input type="password" name="password" id="password" class="form-input" required placeholder=" ">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700; padding: 12px;">
                        Authorize Dashboard <i data-lucide="log-in" style="width: 16px; height: 16px;"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- AUTHENTICATED PANEL VIEW -->
        <header class="admin-header" style="position: sticky; top: 0; z-index: 100; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(3,5,12,0.85); backdrop-filter: blur(12px);">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h1 class="admin-logo" style="font-size: 1.4rem; margin: 0; font-weight: 900; letter-spacing: 1px;">TATVAM<span>.</span></h1>
                    <span style="font-size: 0.8rem; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.3); color: var(--color-gold); padding: 3px 8px; border-radius: 6px; font-weight: 600;">Control Center</span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="?export=csv" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #FFE082 0%, var(--color-gold) 100%); color: #000; box-shadow: none; font-weight: 700;">
                        <i data-lucide="download"></i> Export CSV
                    </a>
                    <a href="../sanskar30.php" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">
                        <i data-lucide="external-link"></i> Live Site
                    </a>
                    <a href="?action=logout" class="btn btn-secondary btn-sm" style="color: #EF4444; border-color: rgba(239,68,68,0.3); font-size: 0.8rem;">
                        <i data-lucide="log-out"></i> Logout
                    </a>
                </div>
            </div>
        </header>

        <main class="container" style="padding-top: 1.5rem; padding-bottom: 3rem; position: relative; z-index: 2;">
            
            <?php if (!empty($flash_msg)): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34D399; padding: 12px 18px; border-radius: 10px; margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    <span><?php echo htmlspecialchars($flash_msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <div style="display: flex; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
                <button class="tab-btn active" id="btn-orders" onclick="switchTab('orders-tab')" style="background: none; border: none; color: var(--color-text-white); font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; cursor: pointer; padding-bottom: 8px; border-bottom: 2px solid var(--color-gold); transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="shopping-bag" style="width: 18px; height: 18px; color: var(--color-gold);"></i> Orders & WhatsApp Hub
                </button>
                <button class="tab-btn" id="btn-products" onclick="switchTab('products-tab')" style="background: none; border: none; color: var(--color-text-slate); font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; cursor: pointer; padding-bottom: 8px; border-bottom: 2px solid transparent; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="book-open" style="width: 18px; height: 18px;"></i> Manage E-Books
                </button>
            </div>

            <!-- TAB 1: ORDERS & ANALYTICS -->
            <div id="orders-tab">
                
                <!-- KPI Analytics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
                    
                    <!-- Card 1: Total Paid Revenue -->
                    <div class="glass-card" style="padding: 1.25rem; border-color: rgba(251, 191, 36, 0.35); box-shadow: 0 4px 20px rgba(251, 191, 36, 0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-gold); font-weight: 700; letter-spacing: 0.5px;">Paid Revenue</span>
                            <i data-lucide="banknote" style="width: 18px; height: 18px; color: var(--color-gold);"></i>
                        </div>
                        <p style="font-size: 1.85rem; font-weight: 900; color: #FFFFFF; font-family: 'Outfit', sans-serif; margin: 0;">
                            ₹<?php echo number_format($total_revenue, 2); ?>
                        </p>
                        <span style="font-size: 0.75rem; color: #10B981; font-weight: 600;">Direct Bank / Gateway settlements</span>
                    </div>

                    <!-- Card 2: Paid Customers -->
                    <div class="glass-card" style="padding: 1.25rem; border-color: rgba(16, 185, 129, 0.35); box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: #10B981; font-weight: 700; letter-spacing: 0.5px;">Successful Orders</span>
                            <i data-lucide="check-circle-2" style="width: 18px; height: 18px; color: #10B981;"></i>
                        </div>
                        <p style="font-size: 1.85rem; font-weight: 900; color: #FFFFFF; font-family: 'Outfit', sans-serif; margin: 0;">
                            <?php echo $total_paid_orders; ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--color-text-slate);">Sales</span>
                        </p>
                        <span style="font-size: 0.75rem; color: var(--color-text-slate);">Avg Order: ₹<?php echo number_format($average_order_value, 0); ?></span>
                    </div>

                    <!-- Card 3: Pending / Abandoned Checkouts -->
                    <div class="glass-card" style="padding: 1.25rem; border-color: rgba(245, 158, 11, 0.35); box-shadow: 0 4px 20px rgba(245, 158, 11, 0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: #F59E0B; font-weight: 700; letter-spacing: 0.5px;">Abandoned / Pending</span>
                            <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #F59E0B;"></i>
                        </div>
                        <p style="font-size: 1.85rem; font-weight: 900; color: #FFFFFF; font-family: 'Outfit', sans-serif; margin: 0;">
                            <?php echo $total_pending_orders; ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--color-text-slate);">Leads</span>
                        </p>
                        <span style="font-size: 0.75rem; color: #FBBF24; font-weight: 600;">₹<?php echo number_format($pending_revenue, 0); ?> recoverable on WhatsApp</span>
                    </div>

                    <!-- Card 4: Total Traffic Initiated -->
                    <div class="glass-card" style="padding: 1.25rem; border-color: rgba(59, 130, 246, 0.35);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.75rem; text-transform: uppercase; color: #60A5FA; font-weight: 700; letter-spacing: 0.5px;">Checkout Traffic</span>
                            <i data-lucide="users" style="width: 18px; height: 18px; color: #60A5FA;"></i>
                        </div>
                        <p style="font-size: 1.85rem; font-weight: 900; color: #FFFFFF; font-family: 'Outfit', sans-serif; margin: 0;">
                            <?php echo $total_all_orders; ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--color-text-slate);">Total</span>
                        </p>
                        <span style="font-size: 0.75rem; color: #60A5FA; font-weight: 600;"><?php echo $conversion_rate; ?>% Paid Conversion</span>
                    </div>

                </div>

                <!-- Controls & Filters Bar -->
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 18px; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    
                    <!-- Status Filter Tabs -->
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="filter-btn active" onclick="filterOrderStatus('all', this)">
                            All Orders (<?php echo count($recent_orders); ?>)
                        </button>
                        <button type="button" class="filter-btn" onclick="filterOrderStatus('paid', this)">
                            <span style="color: #10B981;">●</span> Paid / Completed (<?php echo $total_paid_orders; ?>)
                        </button>
                        <button type="button" class="filter-btn" onclick="filterOrderStatus('pending', this)">
                            <span style="color: #F59E0B;">●</span> Abandoned / Pending (<?php echo $total_pending_orders; ?>)
                        </button>
                    </div>

                    <!-- Live Instant Search -->
                    <div style="min-width: 280px; flex: 1; max-width: 380px; position: relative;">
                        <input type="text" id="orderSearchInput" onkeyup="searchOrdersTable()" placeholder="🔍 Search name, phone, email, #ID..." class="form-input" style="padding: 8px 12px 8px 36px; font-size: 0.85rem; border-radius: 8px; border-color: rgba(255, 255, 255, 0.15);">
                        <i data-lucide="search" style="position: absolute; left: 10px; top: 10px; width: 16px; height: 16px; color: var(--color-text-slate); pointer-events: none;"></i>
                    </div>

                </div>

                <!-- Orders Table Container -->
                <div class="admin-table-container" style="background: rgba(10, 15, 29, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <table id="ordersTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                        <thead>
                            <tr style="background: rgba(255, 255, 255, 0.03); border-bottom: 1px solid rgba(255, 255, 255, 0.08); color: var(--color-text-slate); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                <th style="padding: 14px 16px;">#ID</th>
                                <th style="padding: 14px 16px;">Customer Info</th>
                                <th style="padding: 14px 16px;">Product</th>
                                <th style="padding: 14px 16px;">Amount</th>
                                <th style="padding: 14px 16px;">Status</th>
                                <th style="padding: 14px 16px;">Date & Time</th>
                                <th style="padding: 14px 16px; text-align: center;">⚡ WhatsApp & Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_orders)): ?>
                                <?php foreach ($recent_orders as $order): 
                                    $is_paid = ($order['payment_status'] === 'paid');
                                    $cust_name = trim($order['customer_name']);
                                    $cust_first = explode(' ', $cust_name)[0];
                                    $clean_phone = cleanWhatsAppPhone($order['customer_phone']);
                                    $p_title = !empty($order['product_title']) ? $order['product_title'] : 'SANSKAR 30 Bundle';
                                    $p_slug = !empty($order['product_slug']) ? $order['product_slug'] : 'Sanskar30';
                                    
                                    // Generate Download Link for Paid
                                    $download_token = $order['download_token'] ?? '';
                                    $download_url = SITE_URL . '/download.php?token=' . urlencode($download_token);

                                    // Generate Checkout Link for Pending
                                    $checkout_url = getProductCheckoutLink($p_slug);

                                    // Paid WhatsApp message
                                    $paid_msg = "नमस्ते {$cust_first} जी! 🙏\n\nTATVAM Store से *{$p_title}* सफलतापूर्वक खरीदने के लिए आपका बहुत-बहुत धन्यवाद!\n\n📥 आपकी ई-बुक और बोनस टूलकिट डाउनलोड करने का ऑफिशियल लिंक यह रहा:\n{$download_url}\n\n✨ यह लिंक सुरक्षित है और आप इसे कभी भी दोबारा डाउनलोड कर सकते हैं।\n\nयदि आपको फ़ाइल डाउनलोड या ओपन करने में कोई भी सहायता चाहिए, तो आप हमें इसी WhatsApp नंबर पर तुरंत मैसेज कर सकते हैं।\n\nशुभकामनाएँ! 🌸\n- TATVAM Support Desk";
                                    $wa_paid_url = "https://wa.me/{$clean_phone}?text=" . rawurlencode($paid_msg);

                                    // Abandoned Checkout recovery message
                                    $pending_msg = "नमस्ते {$cust_first} जी! 🙏\n\nहमने देखा कि आप TATVAM Store से *{$p_title}* ऑर्डर करने की कोशिश कर रहे थे, लेकिन शायद किसी तकनीकी कारण से पेमेंट पूरा नहीं हो पाया।\n\n👉 क्या आपको चेकआउट या पेमेंट करने में कोई परेशानी आई थी?\n\nयदि आप अभी अपना ऑर्डर पूरा करना चाहते हैं, तो आप नीचे दिए गए डायरेक्ट लिंक से तुरंत डाउनलोड कर सकते हैं:\n{$checkout_url}\n\n💡 यदि आप सीधे PhonePe, Google Pay या Paytm UPI से पेमेंट करना चाहते हैं, तो हमें यहीं बताएं—हम आपको तुरंत पेमेंट QR / UPI ID भेज देंगे और ई-बुक यहीं डिलीवर कर देंगे!\n\nधन्यवाद! 😊\n- TATVAM Support Team";
                                    $wa_pending_url = "https://wa.me/{$clean_phone}?text=" . rawurlencode($pending_msg);
                                ?>
                                    <tr class="order-row" data-status="<?php echo htmlspecialchars($order['payment_status']); ?>" style="border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s ease;">
                                        
                                        <!-- ID -->
                                        <td style="padding: 12px 16px; font-weight: 700; color: var(--color-gold);">
                                            #<?php echo $order['id']; ?>
                                        </td>

                                        <!-- Customer Details -->
                                        <td style="padding: 12px 16px;">
                                            <div style="font-weight: 700; color: #FFFFFF; font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($cust_name); ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--color-text-slate); margin-top: 2px;">
                                                <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" style="color: #93C5FD; text-decoration: none;">
                                                    <?php echo htmlspecialchars($order['customer_email']); ?>
                                                </a>
                                            </div>
                                            <div style="font-size: 0.82rem; margin-top: 3px; display: flex; align-items: center; gap: 6px;">
                                                <a href="tel:+<?php echo htmlspecialchars($clean_phone); ?>" style="color: #34D399; font-weight: 600; text-decoration: none;">
                                                    📞 +<?php echo htmlspecialchars($clean_phone); ?>
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Product -->
                                        <td style="padding: 12px 16px;">
                                            <span style="font-weight: 600; color: #E2E8F0; display: block;">
                                                <?php echo htmlspecialchars($p_title); ?>
                                            </span>
                                            <span style="font-size: 0.72rem; color: var(--color-text-slate); background: rgba(255,255,255,0.04); padding: 1px 6px; border-radius: 4px;">
                                                <?php echo htmlspecialchars($p_slug); ?>
                                            </span>
                                        </td>

                                        <!-- Amount -->
                                        <td style="padding: 12px 16px; font-weight: 800; color: #FFFFFF; font-family: 'Outfit', sans-serif; font-size: 1rem;">
                                            ₹<?php echo number_format($order['amount'], 2); ?>
                                        </td>

                                        <!-- Status -->
                                        <td style="padding: 12px 16px;">
                                            <?php if ($is_paid): ?>
                                                <span class="status-pill-paid">
                                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i> Paid
                                                </span>
                                            <?php else: ?>
                                                <span class="status-pill-pending">
                                                    <i data-lucide="clock" style="width: 12px; height: 12px;"></i> Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Date -->
                                        <td style="padding: 12px 16px; color: var(--color-text-slate); font-size: 0.8rem; white-space: nowrap;">
                                            <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?>
                                        </td>

                                        <!-- WhatsApp & Quick Actions -->
                                        <td style="padding: 12px 16px; text-align: center;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: center;">
                                                
                                                <?php if ($is_paid): ?>
                                                    <!-- 1. Send Ebook Link on WhatsApp -->
                                                    <a href="<?php echo htmlspecialchars($wa_paid_url); ?>" target="_blank" rel="noopener" class="btn-whatsapp" title="Send official download link on WhatsApp">
                                                        <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i> Send Download Link
                                                    </a>

                                                    <!-- 2. Copy Download Link -->
                                                    <button type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($download_url); ?>', 'Download link copied to clipboard!')" class="btn btn-secondary btn-sm" style="padding: 6px 10px; font-size: 0.78rem; border-color: rgba(255,255,255,0.15);" title="Copy Download URL">
                                                        <i data-lucide="copy" style="width: 13px; height: 13px;"></i> Copy Link
                                                    </button>

                                                <?php else: ?>
                                                    <!-- 1. Send Abandoned Checkout Recovery Message on WhatsApp -->
                                                    <a href="<?php echo htmlspecialchars($wa_pending_url); ?>" target="_blank" rel="noopener" class="btn-recovery" title="Send WhatsApp follow-up to recover this abandoned checkout">
                                                        <i data-lucide="message-square" style="width: 14px; height: 14px;"></i> Recover on WhatsApp
                                                    </a>

                                                    <!-- 2. Quick Mark as Paid Button -->
                                                    <form method="POST" action="index.php" style="display: inline;" onsubmit="return confirm('Mark Order #<?php echo $order['id']; ?> as Paid and generate download token?');">
                                                        <input type="hidden" name="action" value="mark_paid">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" class="btn btn-sm" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34D399; padding: 6px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer; border-radius: 6px;" title="Confirm manual payment and generate download link">
                                                            <i data-lucide="check" style="width: 13px; height: 13px;"></i> Mark Paid
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <!-- Delete Order Option -->
                                                <form method="POST" action="index.php" style="display: inline;" onsubmit="return confirm('Permanently delete Order #<?php echo $order['id']; ?>?');">
                                                    <input type="hidden" name="action" value="delete_order">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" style="background: none; border: none; color: #EF4444; opacity: 0.6; cursor: pointer; padding: 4px;" title="Delete order">
                                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--color-text-slate); padding: 3rem 1rem;">
                                        <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 8px; opacity: 0.4;"></i>
                                        <p style="margin: 0;">No transactions recorded in database yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- TAB 2: MANAGE EBOOKS & CATALOG -->
            <div id="products-tab" style="display: none;">
                
                <?php if (isset($product_success)): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34D399; padding: 1rem; border-radius: 10px; margin-bottom: 1.25rem;">
                        <?php echo $product_success; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($product_error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #FCA5A5; padding: 1rem; border-radius: 10px; margin-bottom: 1.25rem;">
                        <?php echo $product_error; ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.75rem; align-items: start;">
                    
                    <!-- Upload New Product Form -->
                    <div class="glass-card" style="padding: 1.75rem; border-color: rgba(251, 191, 36, 0.35);">
                        <h3 style="font-size: 1.35rem; margin-bottom: 1rem; color: var(--color-gold); display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Add New E-Book
                        </h3>

                        <form method="POST" action="index.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_product">
                            
                            <div class="form-group" style="margin-bottom: 1.2rem;">
                                <input type="text" name="title" id="p-title" class="form-input" required placeholder=" ">
                                <label for="p-title" class="form-label">E-Book Title</label>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.2rem;">
                                <input type="text" name="slug" id="p-slug" class="form-input" required placeholder=" ">
                                <label for="p-slug" class="form-label">URL Slug (e.g. sanskar30)</label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1.2rem;">
                                <div class="form-group">
                                    <input type="number" step="0.01" name="price" id="p-price" class="form-input" required placeholder=" ">
                                    <label for="p-price" class="form-label">Selling Price (₹)</label>
                                </div>
                                <div class="form-group">
                                    <input type="number" step="0.01" name="original_price" id="p-orig-price" class="form-input" required placeholder=" ">
                                    <label for="p-orig-price" class="form-label">Original Price (₹)</label>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.2rem;">
                                <input type="text" name="category" id="p-cat" class="form-input" required placeholder=" ">
                                <label for="p-cat" class="form-label">Category (e.g. parenting, mindset)</label>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.2rem;">
                                <textarea name="description" id="p-desc" class="form-input" rows="3" required placeholder=" " style="resize: none; padding-top: 1rem;"></textarea>
                                <label for="p-desc" class="form-label" style="top: 0.6rem;">Short Description</label>
                            </div>

                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; margin-bottom: 1.2rem;">
                                <label style="display: block; font-size: 0.8rem; color: var(--color-text-slate); margin-bottom: 6px;">Cover Image (JPG / PNG)</label>
                                <input type="file" name="cover_image" accept="image/*" required style="font-size: 0.85rem; color: var(--color-text-slate);">
                            </div>

                            <!-- Multiple Files Upload Container -->
                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <label style="font-size: 0.85rem; font-weight: 600; color: #fff; margin: 0;">Attached Files (Main Book + Toolkit)</label>
                                    <button type="button" onclick="addNewUploadFileRow()" style="background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: var(--color-gold); border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <i data-lucide="plus" style="width: 12px; height: 12px;"></i> + Add File
                                    </button>
                                </div>
                                
                                <div id="upload-files-container" style="display: flex; flex-direction: column; gap: 8px;">
                                    <div class="upload-file-row" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); padding: 8px 10px; border-radius: 6px;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                            <div>
                                                <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File 1 Title:</label>
                                                <input type="text" name="file_titles[]" value="Main E-Book Guide" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                                            </div>
                                            <div>
                                                <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File:</label>
                                                <input type="file" name="ebook_files[]" accept=".pdf,.zip,.epub,.docx,.mp3" required style="font-size: 0.78rem; color: var(--color-text-slate); width: 100%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700;">
                                Upload & Publish E-Book <i data-lucide="plus-circle"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Catalog List Grid -->
                    <div>
                        <h3 style="font-size: 1.35rem; margin-bottom: 1rem; color: #FFFFFF; font-weight: 700;">Active Catalog (<?php echo count($all_products); ?>)</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($all_products as $prod): 
                                $p_files = $prod['files'] ?? getProductFiles($prod['file_path'], $prod['title']);
                                $file_count = count($p_files);
                            ?>
                                <div class="glass-card" style="display: flex; gap: 12px; align-items: center; padding: 12px 16px; border-radius: 12px;">
                                    <img src="../<?php echo htmlspecialchars($prod['cover_image']); ?>" onerror="this.src='../assets/book-cover.jpg';" style="width: 48px; height: 68px; object-fit: cover; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.4); flex-shrink: 0;">
                                    <div style="flex: 1; min-width: 0;">
                                        <h4 style="font-size: 0.95rem; color: #fff; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700;">
                                            <?php echo htmlspecialchars($prod['title']); ?>
                                        </h4>
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 3px;">
                                            <span style="font-size: 0.72rem; color: var(--color-text-slate); background: rgba(255,255,255,0.06); padding: 1px 6px; border-radius: 4px;"><?php echo htmlspecialchars($prod['slug']); ?></span>
                                            <span style="font-size: 0.72rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($prod['category']); ?></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                            <div style="font-size: 0.95rem; font-weight: 800; color: var(--color-gold);">
                                                ₹<?php echo $prod['price']; ?> <del style="font-weight: normal; font-size: 0.75rem; color: var(--color-text-slate); margin-left: 4px;">₹<?php echo $prod['original_price']; ?></del>
                                            </div>
                                            <span style="font-size: 0.72rem; color: var(--color-gold); background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.25); border-radius: 4px; padding: 2px 6px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i data-lucide="paperclip" style="width: 10px; height: 10px;"></i> <?php echo $file_count . ' ' . ($file_count === 1 ? 'File' : 'Files'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <button type="button" onclick='openEditModal(<?php echo json_encode($prod, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' style="color: var(--color-gold); background: rgba(251, 191, 36, 0.12); border: 1px solid rgba(251, 191, 36, 0.35); border-radius: 8px; padding: 8px 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 700;">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i> Edit
                                        </button>
                                        <a href="?action=delete_product&id=<?php echo $prod['id']; ?>" onclick="return confirm('Delete this product from catalog?');" style="color: #EF4444; background: rgba(239,68,68,0.1); padding: 8px; border: 1px solid rgba(239,68,68,0.25); border-radius: 8px; display: inline-flex;" title="Delete Product">
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
            <div class="modal-card" style="max-width: 560px; max-height: 90vh; overflow-y: auto; border-color: rgba(251, 191, 36, 0.4); box-shadow: 0 20px 50px rgba(0,0,0,0.8); background: #0B1120; color: #fff;">
                <button type="button" class="modal-close" onclick="closeEditModal()" style="font-size: 1.8rem; line-height: 1; top: 12px; right: 16px; background: none; border: none; color: #fff; cursor: pointer;">&times;</button>
                
                <div style="margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1.35rem; color: var(--color-gold); display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-weight: 800;">
                        <i data-lucide="edit-3" style="width: 20px; height: 20px;"></i> Edit E-Book Details
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--color-text-slate);">Change price, title, or replace attached files.</p>
                </div>

                <form method="POST" action="index.php" enctype="multipart/form-data" id="edit-product-form">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="id" id="edit-id" value="">

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" name="title" id="edit-title" class="form-input" required placeholder=" ">
                        <label for="edit-title" class="form-label">E-Book Title</label>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" name="slug" id="edit-slug" class="form-input" required placeholder=" ">
                        <label for="edit-slug" class="form-label">URL Slug</label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 1rem;">
                        <div class="form-group">
                            <input type="number" step="0.01" name="price" id="edit-price" class="form-input" required placeholder=" ">
                            <label for="edit-price" class="form-label">Selling Price (₹)</label>
                        </div>
                        <div class="form-group">
                            <input type="number" step="0.01" name="original_price" id="edit-orig-price" class="form-input" required placeholder=" ">
                            <label for="edit-orig-price" class="form-label">Original Price (₹)</label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" name="category" id="edit-cat" class="form-input" required placeholder=" ">
                        <label for="edit-cat" class="form-label">Category</label>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <textarea name="description" id="edit-desc" class="form-input" rows="3" placeholder=" " style="resize: none; padding-top: 1rem;"></textarea>
                        <label for="edit-desc" class="form-label" style="top: 0.6rem;">Description</label>
                    </div>

                    <div class="form-group" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; margin-bottom: 1rem;">
                        <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                            <img id="edit-cover-preview" src="../assets/book-cover.jpg" style="width: 44px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.15);">
                            <div>
                                <span style="display: block; font-size: 0.85rem; font-weight: 600; color: #fff;">Cover Image</span>
                                <span style="font-size: 0.75rem; color: var(--color-text-slate);">Upload to replace:</span>
                            </div>
                        </div>
                        <input type="file" name="cover_image" accept="image/*" style="font-size: 0.85rem; color: var(--color-text-slate);">
                    </div>

                    <input type="hidden" name="files_managed" value="1">

                    <div class="form-group" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #fff;">Attached Files & Toolkits</span>
                            <button type="button" onclick="addEditNewFileRow()" style="background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: var(--color-gold); border-radius: 4px; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                + Attach File
                            </button>
                        </div>
                        <div id="edit-files-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px;"></div>
                        <div id="edit-new-files-container" style="display: flex; flex-direction: column; gap: 8px;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700; padding: 12px;">
                        Save Changes & Update Pricing <i data-lucide="check-circle"></i>
                    </button>
                </form>
            </div>
        </div>

    <?php endif; ?>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // Tab Switcher
        function switchTab(tabId) {
            document.getElementById('orders-tab').style.display = (tabId === 'orders-tab') ? 'block' : 'none';
            document.getElementById('products-tab').style.display = (tabId === 'products-tab') ? 'block' : 'none';

            const btnOrders = document.getElementById('btn-orders');
            const btnProducts = document.getElementById('btn-products');

            if (tabId === 'orders-tab') {
                btnOrders.classList.add('active');
                btnOrders.style.borderBottomColor = 'var(--color-gold)';
                btnOrders.style.color = 'var(--color-text-white)';
                btnProducts.classList.remove('active');
                btnProducts.style.borderBottomColor = 'transparent';
                btnProducts.style.color = 'var(--color-text-slate)';
            } else {
                btnProducts.classList.add('active');
                btnProducts.style.borderBottomColor = 'var(--color-gold)';
                btnProducts.style.color = 'var(--color-text-white)';
                btnOrders.classList.remove('active');
                btnOrders.style.borderBottomColor = 'transparent';
                btnOrders.style.color = 'var(--color-text-slate)';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Live Order Filter by Status (All / Paid / Pending)
        function filterOrderStatus(status, btnElement) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            if (btnElement) btnElement.classList.add('active');

            const rows = document.querySelectorAll('.order-row');
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Real-Time Search in Orders Table
        function searchOrdersTable() {
            const query = document.getElementById('orderSearchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Copy text to clipboard with Toast Notification
        function copyToClipboard(text, message) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(message || 'Copied to clipboard!');
            }).catch(err => {
                // Fallback for older browsers
                const temp = document.createElement('textarea');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                showToast(message || 'Copied to clipboard!');
            });
        }

        // Show Toast Notification
        function showToast(msg) {
            const toast = document.getElementById('toastBox');
            const toastMsg = document.getElementById('toastMsg');
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.style.display = 'flex';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            }
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Dynamic File row for "Add E-Book"
        function addNewUploadFileRow() {
            const container = document.getElementById('upload-files-container');
            const rowCount = container.children.length + 1;
            const row = document.createElement('div');
            row.className = 'upload-file-row';
            row.style.cssText = 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); padding: 8px 10px; border-radius: 6px;';
            row.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600;">File #${rowCount}</span>
                    <button type="button" onclick="this.closest('.upload-file-row').remove()" style="background: none; border: none; color: #EF4444; font-size: 0.75rem; cursor: pointer;">&times; Remove</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title:</label>
                        <input type="text" name="file_titles[]" placeholder="e.g. Activity Toolkit" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File:</label>
                        <input type="file" name="ebook_files[]" accept=".pdf,.zip,.epub,.docx,.mp3" required style="font-size: 0.78rem; color: var(--color-text-slate); width: 100%;">
                    </div>
                </div>
            `;
            container.appendChild(row);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Dynamic File row for "Edit E-Book"
        function addEditNewFileRow() {
            const container = document.getElementById('edit-new-files-container');
            const row = document.createElement('div');
            row.className = 'edit-new-file-row';
            row.style.cssText = 'background: rgba(251, 191, 36, 0.05); border: 1px dashed rgba(251, 191, 36, 0.35); border-radius: 6px; padding: 8px 10px;';
            row.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600;">+ New Extra File / Toolkit</span>
                    <button type="button" onclick="this.closest('.edit-new-file-row').remove()" style="background: none; border: none; color: #EF4444; font-size: 0.75rem; cursor: pointer;">&times; Remove</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title:</label>
                        <input type="text" name="new_file_titles[]" placeholder="e.g. Companion Toolkit" class="form-input" style="padding: 6px 10px; font-size: 0.8rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--color-text-slate); margin-bottom: 2px;">Select File:</label>
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
                    div.style.cssText = 'background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 8px 10px;';
                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.72rem; color: var(--color-gold); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i data-lucide="file-text" style="width: 12px; height: 12px;"></i> File #${idx + 1}
                            </span>
                            <button type="button" onclick="this.closest('.edit-file-item').remove()" style="background: none; border: none; color: #EF4444; font-size: 0.72rem; cursor: pointer;">&times; Delete File</button>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <label style="display: block; font-size: 0.68rem; color: var(--color-text-slate); margin-bottom: 2px;">File Title:</label>
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
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-product-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('edit-product-modal');
            if (modal && e.target === modal) closeEditModal();
        });
    </script>
</body>
</html>