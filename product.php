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
            <!-- Hamburger Button -->
            <button class="mobile-menu-btn" aria-label="Toggle Menu">
                <i data-lucide="menu"></i>
            </button>
            <nav class="nav-links">
                <a href="index.html" class="nav-link">Home</a>
                <div class="nav-dropdown">
                    <a href="library.php" class="nav-link dropdown-trigger">E-Books <i data-lucide="chevron-down" class="dropdown-icon"></i></a>
                    <div class="dropdown-menu">
                        <a href="library.php" class="dropdown-item">All E-Books</a>
                        <a href="positive-thinking.html" class="dropdown-item">मन की शांति (Calm)</a>
                        <a href="stress-worry.html" class="dropdown-item">चिंता मुक्ति (Anxiety)</a>
                        <a href="habit-freedom.html" class="dropdown-item">अनुशासन क्रांति (Discipline)</a>
                        <a href="wealth-mindset.html" class="dropdown-item">समृद्धि सूत्र (Wealth)</a>
                    </div>
                </div>
                <a href="#content" class="nav-link">What's Inside</a>
                <a href="#reviews" class="nav-link">Reviews</a>
                <div class="nav-dropdown">
                    <span class="nav-link dropdown-trigger">Policies & Help <i data-lucide="chevron-down" class="dropdown-icon"></i></span>
                    <div class="dropdown-menu">
                        <a href="contact-us.html" class="dropdown-item">Contact Us</a>
                        <a href="privacy.html" class="dropdown-item">Privacy Policy</a>
                        <a href="refund-policy.html" class="dropdown-item">Refund Policy</a>
                        <a href="terms-conditions.html" class="dropdown-item">Terms & Conditions</a>
                    </div>
                </div>
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
</body>
</html>
