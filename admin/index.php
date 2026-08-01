<?php
/**
 * TATVAM - Premium SaaS-Style Admin Dashboard
 * Displays sales analytics, order log lists, database CSV downloads, and E-Book uploads.
 */

session_start();
require_once __DIR__ . '/../db.php';

// Simple Administrative Login Session Gate
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Tatvam@2025');

$authenticated = false;

if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    $authenticated = true;
}

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

    // Handle PDF file upload
    $file_path = '';
    if (isset($_FILES['ebook_file']) && $_FILES['ebook_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['ebook_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_ext === 'pdf' || $file_ext === 'zip') {
            $dest_dir = __DIR__ . '/../files/uploads/';
            if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);
            $new_name = uniqid('ebook_', true) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $dest_dir . $new_name)) {
                $file_path = 'files/uploads/' . $new_name;
            }
        }
    }

    if (!empty($title) && !empty($slug) && !empty($file_path)) {
        try {
            $stmt = $db->prepare("INSERT INTO products (title, slug, price, original_price, file_path, category, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $price, $original_price, $file_path, $category, $description, $cover_image_path]);
            $product_success = "Product added successfully!";
        } catch (Exception $e) {
            $product_error = "Error adding product: " . $e->getMessage();
        }
    } else {
        $product_error = "Title, slug, and Ebook file are required.";
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

                            <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-light); padding: var(--space-sm); border-radius: var(--radius-sm);">
                                <label style="display: block; font-size: 0.8rem; color: var(--color-text-slate); margin-bottom: var(--space-xxs);">PDF Guide File or Bundle ZIP</label>
                                <input type="file" name="ebook_file" accept=".pdf,.zip" required style="font-size: 0.9rem; color: var(--color-text-slate);">
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
                                <div class="glass-card" style="display: flex; gap: var(--space-sm); align-items: center; padding: var(--space-xs);">
                                    <img src="../<?php echo htmlspecialchars($prod['cover_image']); ?>" onerror="this.src='../assets/book-cover.jpg';" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                                    <div style="flex: 1;">
                                        <h4 style="font-size: 0.95rem; color: #fff;"><?php echo htmlspecialchars($prod['title']); ?></h4>
                                        <span style="font-size: 0.8rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($prod['category']); ?></span>
                                        <div style="font-size: 0.85rem; font-weight: bold; color: var(--color-gold); margin-top: 4px;">
                                            INR <?php echo $prod['price']; ?> <del style="font-weight: normal; font-size: 0.75rem; color: var(--color-text-slate); margin-left: 4px;">INR <?php echo $prod['original_price']; ?></del>
                                        </div>
                                    </div>
                                    <a href="?action=delete_product&id=<?php echo $prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color: #EF4444; padding: 8px; border: 1px solid rgba(239,68,68,0.2); border-radius: var(--radius-sm); display: inline-flex;" title="Delete Product">
                                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    <?php endif; ?>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function switchTab(tabId) {
            document.getElementById('orders-tab').style.display = tabId === 'orders-tab' ? 'block' : 'none';
            document.getElementById('products-tab').style.display = tabId === 'products-tab' ? 'block' : 'none';
            
            document.getElementById('btn-orders').classList.toggle('active', tabId === 'orders-tab');
            document.getElementById('btn-products').classList.toggle('active', tabId === 'products-tab');

            document.getElementById('btn-orders').style.color = tabId === 'orders-tab' ? 'var(--color-text-white)' : 'var(--color-text-slate)';
            document.getElementById('btn-products').style.color = tabId === 'products-tab' ? 'var(--color-text-white)' : 'var(--color-text-slate)';
            
            document.getElementById('btn-orders').style.borderBottomColor = tabId === 'orders-tab' ? 'var(--color-gold)' : 'transparent';
            document.getElementById('btn-products').style.borderBottomColor = tabId === 'products-tab' ? 'var(--color-gold)' : 'transparent';
        }
    </script>
</body>
</html>
