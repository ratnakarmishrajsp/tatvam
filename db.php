<?php
/**
 * TATVAM - Database Connectivity & Auto-Initialization Engine
 * Supporting both SQLite (zero-config localhost) and MySQL (production standard)
 */

require_once __DIR__ . '/config.php';

try {
    if (DB_DRIVE === 'sqlite') {
        // Ensure database file directory exists
        $db_dir = dirname(SQLITE_DB_PATH);
        if (!file_exists($db_dir)) {
            mkdir($db_dir, 0755, true);
        }

        // Initialize SQLite Connection
        $db = new PDO('sqlite:' . SQLITE_DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Auto-create SQLite tables if they do not exist
        $db->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            price REAL NOT NULL,
            original_price REAL NOT NULL,
            file_path TEXT NOT NULL,
            category TEXT NOT NULL,
            description TEXT,
            cover_image TEXT
        )");

        // Dynamic column additions for existing installations
        $cols = $db->query("PRAGMA table_info(products)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('description', $cols)) {
            $db->exec("ALTER TABLE products ADD COLUMN description TEXT");
        }
        if (!in_array('cover_image', $cols)) {
            $db->exec("ALTER TABLE products ADD COLUMN cover_image TEXT");
        }

        // Fill descriptions and cover images for default seeded products if empty
        $db->exec("UPDATE products SET description = 'Overthinking aur faaltu thoughts se pareshan hain? Apne mind ko reprogram karna seekhein.', cover_image = 'assets/calm-cover.jpg' WHERE slug = 'positive-thinking' AND (description IS NULL OR description = '')");
        $db->exec("UPDATE products SET description = 'Anxiety, future doubts aur daily stress aapka din kharab kar rahe hain? Stress clean karna seekhein.', cover_image = 'assets/anxiety-cover.jpg' WHERE slug = 'stress-worry' AND (description IS NULL OR description = '')");
        $db->exec("UPDATE products SET description = 'Procrastination aur lazy hours aapki speed slow kar rahe hain? Self-discipline build karna seekhein.', cover_image = 'assets/discipline-cover.jpg' WHERE slug = 'habit-freedom' AND (description IS NULL OR description = '')");
        $db->exec("UPDATE products SET description = 'Paison ko lekar poor self-limiting beliefs aur money blocks aapki growth block kar rahe hain? Abundance habits seekhein.', cover_image = 'assets/wealth-cover.jpg' WHERE slug = 'wealth-mindset' AND (description IS NULL OR description = '')");
        $db->exec("UPDATE products SET description = 'Sari single guides ek bundle me paayein. Total Value ₹3,996 par abhi grab karein!', cover_image = 'assets/bundle-cover.jpg' WHERE slug = 'mega-bundle' AND (description IS NULL OR description = '')");

        $db->exec("CREATE TABLE IF NOT EXISTS daily_calculations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            calc_date TEXT UNIQUE NOT NULL,
            ad_spend REAL DEFAULT 0,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS site_analytics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_type TEXT NOT NULL,
            page_slug TEXT DEFAULT 'positive-thinking',
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            product_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            payment_status TEXT DEFAULT 'pending',
            razorpay_order_id TEXT,
            razorpay_payment_id TEXT,
            download_token TEXT,
            download_count INTEGER DEFAULT 0,
            token_expiry TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(product_id) REFERENCES products(id)
        )");
        
        // Seed default products if empty
        $count = $db->query("SELECT count(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $insert = $db->prepare("INSERT INTO products (title, slug, price, original_price, file_path, category, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute(["Positive Thinking (नकारात्मक सोच से बाहर निकलें)", "positive-thinking", 199.00, 999.00, "files/power_of_calm_hindi.pdf", "mindset", "नकारात्मक सोच से बाहर निकलें और सकारात्मक, शांत एवं आत्मविश्वासी जीवन की ओर बढ़ें।", "assets/calm-cover.jpg"]);
            $insert->execute(["चिंता मुक्ति (Anxiety Relief)", "stress-worry", 199.00, 999.00, "files/anxiety_relief_hindi.pdf", "peace", "Anxiety, future doubts aur daily stress aapka din kharab kar rahe hain? Stress clean karna seekhein.", "assets/anxiety-cover.jpg"]);
            $insert->execute(["अनुशासन क्रांति (Ultimate Discipline)", "habit-freedom", 199.00, 999.00, "files/ultimate_discipline_hindi.pdf", "discipline", "Procrastination aur lazy hours aapki speed slow kar rahe hain? Self-discipline build karna seekhein.", "assets/discipline-cover.jpg"]);
            $insert->execute(["समृद्धि सूत्र (Wealth Principles)", "wealth-mindset", 199.00, 999.00, "files/wealth_principles_hindi.pdf", "wealth", "Paison ko lekar poor self-limiting beliefs aur money blocks aapki growth block kar rahe hain? Abundance habits seekhein.", "assets/wealth-cover.jpg"]);
            $insert->execute(["TATVAM Mega Mindset Bundle (4-in-1)", "mega-bundle", 199.00, 3996.00, "files/tattvam_mega_bundle.zip", "bundle", "Sari single guides ek bundle me paayein. Total Value ₹3,996 par abhi grab karein!", "assets/bundle-cover.jpg"]);
        }

        // Overwrite title and description for positive-thinking product in existing database
        $db->exec("UPDATE products SET title = 'Positive Thinking (नकारात्मक सोच से बाहर निकलें)', description = 'नकारात्मक सोच से बाहर निकलें और सकारात्मक, शांत एवं आत्मविश्वासी जीवन की ओर बढ़ें।' WHERE slug = 'positive-thinking'");

    } else {
        // Initialize MySQL Connection
        $dsn = "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DB . ";charset=utf8mb4";
        $db = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
