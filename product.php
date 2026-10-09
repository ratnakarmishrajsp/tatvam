<?php
/**
 * TATVAM - Dynamic E-Book Landing Page
 * Dynamically generated high-converting landing page for uploaded ebooks.
 */
require_once __DIR__ . '/db.php';

$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($slug)) {
    header('Location: library.php');
    exit;
}

$clean_slug = strtolower(str_replace(['-', '_', ' '], '', (string)$slug));
if ($clean_slug === 'sanskar30') {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE slug = ? OR slug = 'Sanskar30' OR slug = 'sanskar-30' OR slug = 'sanskar30' OR title LIKE '%Sanskar%' LIMIT 1");
        $stmt->execute([$slug]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {}

    include __DIR__ . '/sanskar30.php';
    exit;
}

if (strpos($clean_slug, 'ai') !== false && strpos($clean_slug, 'digital') !== false) {
    header('Location: ai-digital-product.html');
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: 404.html');
        exit;
    }
} catch (Exception $e) {
    die("Database Query Failed: " . $e->getMessage());
}

$title = $product['title'];
$price = $product['price'];
$original_price = $product['original_price'];
$category = $product['category'];
$description = $product['description'] ?? 'Downloadable digital guide with worksheets.';
$cover_image = $product['cover_image'] ?? 'assets/book-cover.jpg';
$file_path = $product['file_path'];

// High converting tags and calculations
$save_percent = round((($original_price - $price) / $original_price) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/includes/meta-pixel-header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | TATVAM Bookstore</title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>

    <!-- Master CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<body data-page-slug="<?php echo htmlspecialchars($slug); ?>">

    <!-- STATIC LOW STOCK BANNER -->
    <div style="background: linear-gradient(90deg, #b45309, #d97706); text-align: center; color: white; padding: 0.5rem 0; font-size: 0.85rem; font-weight: 700; position: relative; z-index: 1001; letter-spacing: 0.05em; text-transform: uppercase;">
        ⚡ SPECIAL LAUNCH OFFER – GET <?php echo $save_percent; ?>% OFF TODAY!
    </div>

    <!-- BACKGROUND CANVAS -->
    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <!-- HEADER -->
    <header class="header scrolled">
        <div class="nav-glass">
            <a href="index.html" class="logo">TATVAM<span>.</span></a>
            <nav class="nav-links">
                <a href="index.html" class="nav-link">Home</a>
                <a href="library.php" class="nav-link">E-Books</a>
                <a href="#content" class="nav-link">What's Inside</a>
                <a href="#reviews" class="nav-link">Reviews</a>
                <a href="#" id="open-wishlist-btn" class="nav-link" style="display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="heart" style="width: 18px; height: 18px;"></i> Wishlist
                </a>
            </nav>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <div class="hero-badge">
                    <i data-lucide="sparkles"></i>
                    <span>TATVAM Mindset Guides</span>
                </div>
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p class="lead" style="margin-top: 1rem; color: var(--color-text-slate);"><?php echo htmlspecialchars($description); ?></p>
                
                <div class="hero-cta-wrapper" style="margin-top: 2rem;">
                    <div class="visitor-badge" style="margin-bottom: var(--space-sm);">
                        <div class="visitor-dot"></div>
                        <span id="visitor-count-text">118 people are viewing this right now</span>
                    </div>

                    <div class="hero-cta">
                        <button class="btn btn-primary checkout-trigger" data-product-slug="<?php echo htmlspecialchars($slug); ?>">
                            Get Ebook for ₹<?php echo number_format($price); ?> <i data-lucide="arrow-right"></i>
                        </button>
                    </div>
                    <div style="margin-top: 0.75rem; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <span style="color: var(--color-gold); font-size: 1.15rem; font-weight: 700;">₹<?php echo number_format($price); ?></span>
                        <del style="color: var(--color-text-slate);">₹<?php echo number_format($original_price); ?></del>
                        <span style="background: rgba(16, 185, 129, 0.1); color: var(--color-success); font-size: 0.75rem; padding: 2px 8px; border-radius: var(--radius-full); font-weight: 700;">Save <?php echo $save_percent; ?>%</span>
                    </div>
                </div>

                <div class="hero-trust-bullets" style="margin-top: var(--space-md);">
                    <span><i data-lucide="check"></i> Instant PDF Delivery</span>
                    <span><i data-lucide="check"></i> Works on Mobile & Kindle</span>
                    <span><i data-lucide="check"></i> Lifetime Access</span>
                </div>
            </div>
            
            <div class="showcase-wrapper">
                <div class="glow-ambient"></div>
                <div class="book-shadow-wrapper">
                    <div class="book-3d">
                        <div class="book-front">
                            <img src="<?php echo htmlspecialchars($cover_image); ?>" onerror="this.src='assets/book-cover.jpg';" alt="Book Cover" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT / WHAT'S INSIDE -->
    <section class="section-pad" id="content">
        <div class="container">
            <div class="section-head">
                <span class="tag">Inside View</span>
                <h2>What's Inside?</h2>
                <p>Learn core lessons and action frameworks designed to create immediate transformations.</p>
            </div>

            <div class="review-grid">
                <div class="glass-card">
                    <div style="color: var(--color-gold); font-size: 1.5rem; margin-bottom: 0.5rem;"><i data-lucide="book-open" style="width: 32px; height: 32px;"></i></div>
                    <h3>Practical Mind Hacks</h3>
                    <p style="font-size: 0.9rem; margin-top: 0.35rem;">Action-oriented frameworks designed for daily application instead of dry philosophical theories.</p>
                </div>
                <div class="glass-card">
                    <div style="color: var(--color-gold); font-size: 1.5rem; margin-bottom: 0.5rem;"><i data-lucide="layout" style="width: 32px; height: 32px;"></i></div>
                    <h3>Printable Routine Checklists</h3>
                    <p style="font-size: 0.9rem; margin-top: 0.35rem;">Complete checklists that help you stay aligned and log your habits easily on your smartphone.</p>
                </div>
                <div class="glass-card">
                    <div style="color: var(--color-gold); font-size: 1.5rem; margin-bottom: 0.5rem;"><i data-lucide="zap" style="width: 32px; height: 32px;"></i></div>
                    <h3>21-Day Habit Loops</h3>
                    <p style="font-size: 0.9rem; margin-top: 0.35rem;">Step-by-step guidance to set up automated discipline templates that run on autopilot.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CUSTOMER REVIEWS -->
    <section id="reviews" class="section-pad">
        <div class="container">
            <div class="section-head">
                <span class="tag">Reader Testimonials</span>
                <h2>What Other Seekers Say</h2>
                <p>Honest reviews from readers who bought this e-book guide.</p>
            </div>

            <div class="review-grid">
                <div class="glass-card">
                    <div class="card-rating">★★★★★</div>
                    <p style="font-style: italic; font-size: 0.95rem; margin-top: 0.5rem; color: var(--color-text-gray);">"Maine ye e-book download karke padhna shuru kiya aur morning routine build karne me bohot help mili. Highly recommended!"</p>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-gold); color: #000; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">AK</div>
                        <div>
                            <h4 style="font-size: 0.9rem;">Amit Kumar</h4>
                            <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 500;"><i data-lucide="check-circle" style="width: 10px; height: 10px; display: inline-block;"></i> Verified Purchaser</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-rating">★★★★★</div>
                    <p style="font-style: italic; font-size: 0.95rem; margin-top: 0.5rem; color: var(--color-text-gray);">"Hinglish language is so simple to read. Tasks list are easy to implement in daily life."</p>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">SD</div>
                        <div>
                            <h4 style="font-size: 0.9rem;">Sonia Dixit</h4>
                            <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 500;"><i data-lucide="check-circle" style="width: 10px; height: 10px; display: inline-block;"></i> Verified Purchaser</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TRUST ELEMENTS -->
    <section class="section-pad" style="border-top: 1px solid var(--border-light); background: rgba(255,255,255,0.003);">
        <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-md); text-align: center;">
            <div>
                <i data-lucide="bolt" style="color: var(--color-gold); width: 36px; height: 36px; margin-bottom: 0.5rem;"></i>
                <h4 style="font-size: 1.1rem; margin-bottom: 4px;">Instant Access</h4>
                <p style="font-size: 0.85rem;">Download directly on payment page & backup link to your email.</p>
            </div>
            <div>
                <i data-lucide="shield-check" style="color: var(--color-success); width: 36px; height: 36px; margin-bottom: 0.5rem;"></i>
                <h4 style="font-size: 1.1rem; margin-bottom: 4px;">100% Risk Free</h4>
                <p style="font-size: 0.85rem;">7 Days satisfaction backup money-back refund guarantee.</p>
            </div>
            <div>
                <i data-lucide="smartphone" style="color: var(--color-secondary); width: 36px; height: 36px; margin-bottom: 0.5rem;"></i>
                <h4 style="font-size: 1.1rem; margin-bottom: 4px;">Mobile Optimized</h4>
                <p style="font-size: 0.85rem;">Specifically formatted for perfect readability on mobile screens.</p>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="section-pad" style="text-align: center; border-top: 1px solid var(--border-light); background: radial-gradient(circle at center, rgba(139,92,246,0.03) 0%, rgba(3,5,12,0.9) 100%);">
        <div class="container" style="max-width: 600px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Claim Your Copy Now</h2>
            <p style="margin-bottom: var(--space-md);">Get instant download access to this guide at a special launch price.</p>
            <div style="font-size: 2rem; font-weight: 800; color: var(--color-gold); margin-bottom: var(--space-md);">
                ₹<?php echo number_format($price); ?> <del style="font-size: 1.15rem; color: var(--color-text-slate); margin-left: 4px;">₹<?php echo number_format($original_price); ?></del>
            </div>
            <button class="btn btn-primary checkout-trigger" data-product-slug="<?php echo htmlspecialchars($slug); ?>" style="padding: 1.1rem 3rem;">Buy Now <i data-lucide="arrow-right"></i></button>
        </div>
    </section>

    <!-- FOOTER -->
    <footer style="background: #03050c; color: var(--color-text-slate); padding: var(--space-md) 0; border-top: var(--glass-border) 1px solid; text-align: center; font-size: 0.85rem; position: relative; z-index: 2;">
        <div class="container">
            <p>&copy; 2026 tatvam.shop. All rights reserved. Support Desk: support@tatvam.shop</p>
        </div>
    </footer>

    <!-- STICKY FLOATING BUY BAR (NEW) -->
    <!-- Desktop Corner checkout trigger card -->
    <div class="floating-checkout-card" id="desktop-floating-card">
        <div class="floating-book-row">
            <img src="<?php echo htmlspecialchars($cover_image); ?>" onerror="this.src='assets/book-cover.jpg';" alt="Book cover">
            <div>
                <h4 style="font-size: 0.9rem; font-weight: bold; line-height: 1.2;"><?php echo htmlspecialchars($title); ?></h4>
                <span style="font-size: 0.75rem; color: var(--color-gold); font-weight: 500;">Get Instant PDF</span>
            </div>
        </div>
        
        <div class="floating-pricing-box">
            <div>
                <span style="font-size: 1.15rem; font-weight: 700; color: var(--color-gold);">₹<?php echo number_format($price); ?></span>
                <del style="font-size: 0.8rem; color: var(--color-text-slate); margin-left: 4px;">₹<?php echo number_format($original_price); ?></del>
            </div>
            <div id="desktop-floating-timer" style="font-size: 0.85rem; font-family: var(--font-heading); color: var(--color-gold); font-weight: bold;">15:00</div>
        </div>
        
        <button class="btn btn-primary btn-sm checkout-trigger" data-product-slug="<?php echo htmlspecialchars($slug); ?>" style="width: 100%; justify-content: center; box-shadow: none;">Buy Ebook Now</button>
    </div>

    <!-- Mobile sticky bottom bar -->
    <div class="sticky-buy-bar">
        <div class="container buy-bar-container">
            <div class="buy-bar-info">
                <h4 style="font-size: 0.85rem; line-height: 1.2; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?php echo htmlspecialchars($title); ?></h4>
                <span class="price">₹<?php echo number_format($price); ?></span>
            </div>
            <button class="btn btn-primary btn-sm checkout-trigger" data-product-slug="<?php echo htmlspecialchars($slug); ?>">Buy Now <i data-lucide="arrow-right"></i></button>
        </div>
    </div>

    <!-- ONE STEP GLASS CHECKOUT MODAL -->
    <div class="modal-overlay">
        <div class="modal-card">
            <button class="modal-close" aria-label="Close Checkout Modal">&times;</button>
            <h2 class="gradient-gold" style="background: linear-gradient(135deg, #FFE082 0%, var(--color-gold) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Fill Your Details</h2>
            <p>E-book instantly deliver karne ke liye details enter karein.</p>
            
            <form id="checkout-form">
                <input type="hidden" name="product_slug" id="target-product-slug" value="<?php echo htmlspecialchars($slug); ?>">
                
                <div class="form-group">
                    <input type="text" name="name" id="name" class="form-input" placeholder=" " required>
                    <label for="name" class="form-label">Your Name</label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-input" placeholder=" " required>
                    <label for="email" class="form-label">Email Address</label>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" id="phone" class="form-input" placeholder=" " required>
                    <label for="phone" class="form-label">WhatsApp Phone Number</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-xs);">
                    Proceed to Payment <i data-lucide="credit-card"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- WISHLIST SIDEBAR DRAWER -->
    <div class="wishlist-sidebar">
        <div class="wishlist-header">
            <h3><i data-lucide="heart" style="color: #EF4444; fill: #EF4444; vertical-align: middle; margin-right: 6px;"></i> My Wishlist</h3>
            <button class="wishlist-close" aria-label="Close Wishlist">&times;</button>
        </div>
        <div class="wishlist-items" id="wishlist-items-container">
            <!-- Renders dynamically -->
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    <script src="script.js" defer></script>
    <!-- WHATSAPP FLOATING WIDGET -->
    <div class="whatsapp-widget">
        <a href="https://wa.me/918417894808?text=%E0%A4%A8%E0%A4%AE%E0%A4%B8%E0%A5%8D%E0%A4%A4%E0%A5%87%20TATVAM%20%E0%A4%9F%E0%A5%80%E0%A4%AE%2C%20%E0%A4%AE%E0%A5%81%E0%A4%9D%E0%A5%87%20%E0%A4%B8%E0%A4%B9%E0%A4%BE%E0%A4%AF%E0%A4%A4%E0%A4%BE%20%E0%A4%9A%E0%A4%BE%E0%A4%B9%E0%A4%BF%E0%A4%8F%E0%A5%A4" target="_blank" rel="noopener" class="whatsapp-btn" aria-label="WhatsApp Support" title="Chat on WhatsApp">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
                <path d="M12.031 2C6.51 2 2.015 6.484 2.015 12c0 1.954.563 3.774 1.532 5.32L2 22l4.83-1.503A9.957 9.957 0 0 0 12.031 22c5.522 0 10.015-4.484 10.015-10S17.553 2 12.031 2zm5.836 14.195c-.244.688-1.42 1.32-1.957 1.378-.512.055-1.18.077-1.914-.158-.47-.15-1.077-.35-1.859-.69-3.28-1.422-5.412-4.734-5.577-4.953-.165-.218-1.332-1.773-1.332-3.383 0-1.61.844-2.404 1.144-2.733.3-.328.656-.41.875-.41.219 0 .438.002.63.011.203.01.474-.077.74.563.275.66.936 2.285 1.018 2.453.082.167.137.364.027.583-.11.218-.165.355-.328.547-.164.192-.346.429-.494.577-.165.163-.337.34-.145.669.192.328.854 1.408 1.832 2.28 1.258 1.121 2.318 1.468 2.646 1.632.328.164.52.137.712-.082.192-.218.82-1.018 1.04-1.368.218-.35.437-.291.738-.182.3.11 1.913.902 2.242 1.066.328.164.547.246.63.383.082.137.082.793-.164 1.48z"/>
            </svg>
        </a>
    </div>
</body>
</html>
