<?php
/**
 * TATVAM - Premium Digital Library & Bookstore
 * Dynamic collection viewer with interactive search, filters, and 3D mockups.
 */
require_once __DIR__ . '/db.php';

try {
    $stmt = $db->query("SELECT * FROM products WHERE slug = 'positive-thinking'");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Database Query Failed: " . $e->getMessage());
}

// Function to resolve product URLs
function getProductUrl($slug) {
    switch ($slug) {
        case 'positive-thinking':
            return 'positive-thinking.html';
        case 'stress-worry':
            return 'stress-worry.html';
        case 'habit-freedom':
            return 'habit-freedom.html';
        case 'wealth-mindset':
            return 'wealth-mindset.html';
        case 'mega-bundle':
            return 'bundle.html';
        default:
            return 'product.php?slug=' . urlencode($slug);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Book Library | TATVAM Digital Bookstore</title>
    <meta name="description" content="Browse our luxury digital library. Find guides on positive thinking, discipline, anxiety relief, and wealth frameworks.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest" defer></script>

    <!-- Master CSS -->
    <link rel="stylesheet" href="styles.css?v=2.0">
    
    <style>
        /* Specific Bookstore styling extensions */
        .library-hero {
            padding: 140px 0 60px;
            text-align: center;
        }
        .filter-container {
            display: flex;
            justify-content: center;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-light);
            color: var(--color-text-slate);
            padding: 0.6rem 1.4rem;
            border-radius: var(--radius-full);
            cursor: pointer;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .filter-btn.active, .filter-btn:hover {
            background: var(--color-gold);
            color: #03050c;
            border-color: var(--color-gold);
            box-shadow: var(--shadow-glow-gold);
        }
        .search-box-wrapper {
            max-width: 500px;
            margin: 0 auto var(--space-md);
            position: relative;
        }
        .search-box-wrapper i {
            position: absolute;
            left: 15px;
            top: 13px;
            color: var(--color-text-slate);
        }
        .search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-full);
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            color: white;
            font-family: var(--font-body);
            font-size: 0.95rem;
        }
        .search-input:focus {
            border-color: var(--color-gold);
            outline: none;
            background: rgba(255,255,255,0.06);
        }
        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space-md);
        }
        
        /* 3D Ebook Card Aesthetics */
        .ebook-card {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: var(--space-md);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            will-change: transform;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .ebook-card:hover {
            transform: translate3d(0, -8px, 0) scale(1.03);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6), 0 0 30px rgba(251,191,36,0.1);
        }
        .card-cover-space {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--space-sm);
            perspective: 1000px;
        }
        .card-book-3d {
            width: 130px;
            height: 180px;
            transform-style: preserve-3d;
            transform: rotateY(-15deg) rotateX(8deg);
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: -10px 15px 25px rgba(0,0,0,0.5);
            position: relative;
        }
        .ebook-card:hover .card-book-3d {
            transform: rotateY(-5deg) rotateX(4deg);
        }
        .card-book-3d img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 2px;
        }
        .card-book-3d::after {
            content: '';
            position: absolute;
            right: -6px;
            top: 3px;
            width: 6px;
            height: calc(100% - 6px);
            background: linear-gradient(90deg, #e2e8f0 0%, #cbd5e1 100%);
            transform: rotateY(90deg);
            transform-origin: left center;
        }
    </style>
</head>
<body>

    <!-- BACKGROUND CANVAS -->
    <div class="bg-canvas"></div>
    <div class="noise-overlay"></div>

    <!-- HEADER -->
    <header class="header">
        <div class="nav-glass">
            <a href="index.html" class="logo">TATVAM<span>.</span></a>
            <!-- Hamburger Button -->
            <button class="mobile-menu-btn" aria-label="Toggle Menu">&#9776;</button>
            <nav class="nav-links">
                <a href="index.html" class="nav-link">Home</a>
                <div class="nav-dropdown">
                    <a href="library.php" class="nav-link dropdown-trigger">E-Books <i data-lucide="chevron-down" class="dropdown-icon"></i></a>
                    <div class="dropdown-menu">
                        <a href="library.php" class="dropdown-item">All E-Books</a>
                        <a href="positive-thinking.html" class="dropdown-item">मन की शांति (Calm)</a>
                    </div>
                </div>
                <a href="index.html#faq" class="nav-link">About</a>
                <div class="nav-dropdown">
                    <span class="nav-link dropdown-trigger">Policies & Help <i data-lucide="chevron-down" class="dropdown-icon"></i></span>
                    <div class="dropdown-menu">
                        <a href="contact-us.html" class="dropdown-item">Contact Us</a>
                        <a href="privacy.html" class="dropdown-item">Privacy Policy</a>
                        <a href="refund-policy.html" class="dropdown-item">Refund Policy</a>
                        <a href="terms-conditions.html" class="dropdown-item">Terms & Conditions</a>
                    </div>
                </div>
                <a href="positive-thinking.html" class="btn btn-primary btn-sm"><i data-lucide="sparkles"></i> Get E-Book</a>
            </nav>
        </div>
    </header>

    <!-- LIBRARY HERO -->
    <section class="library-hero">
        <div class="container">
            <div class="hero-badge">
                <i data-lucide="book-open"></i>
                <span>Mindset & Discipline Library</span>
            </div>
            <h1 style="font-size: 3rem; margin-bottom: 0.5rem;">Explore Premium E-Books</h1>
            <p style="color: var(--color-text-slate); max-width: 600px; margin: 0 auto 2rem;">Read simple guides written in Hinglish to rewire your thinking models, eliminate stress, and rebuild daily motivation.</p>
            
            <!-- Search Bar -->
            <div class="search-box-wrapper">
                <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                <input type="text" id="library-search" class="search-input" placeholder="Search ebooks by title, category, keyword...">
            </div>

            <!-- Category Filters Removed (Single Product) -->
        </div>
    </section>

    <!-- BOOKSHELF SECTION -->
    <section class="section-pad" style="padding-top: 0;">
        <div class="container">
            <div class="library-grid" id="library-grid-container">
                
                <?php foreach ($products as $prod): ?>
                    <a href="<?php echo getProductUrl($prod['slug']); ?>" class="ebook-card" data-category="<?php echo htmlspecialchars($prod['category']); ?>" data-title="<?php echo htmlspecialchars(strtolower($prod['title'])); ?>" style="text-decoration: none; display: flex; flex-direction: column; justify-content: space-between;">
                        
                        <div>
                            <!-- Cover visual area -->
                            <div class="card-cover-space">
                                <div class="card-book-3d">
                                    <img src="<?php echo htmlspecialchars($prod['cover_image']); ?>" onerror="this.src='assets/book-cover.jpg';" alt="<?php echo htmlspecialchars($prod['title']); ?>">
                                </div>
                            </div>
                            
                            <!-- Badges row -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-gold); font-weight: 700; letter-spacing: 0.05em;"><?php echo htmlspecialchars($prod['category']); ?></span>
                                <span style="background: rgba(16, 185, 129, 0.08); color: var(--color-success); border: 1px solid rgba(16, 185, 129, 0.15); font-size: 0.7rem; padding: 2px 6px; border-radius: var(--radius-full); font-weight: bold;">Instant Delivery</span>
                            </div>
                            
                            <!-- Ebook Title -->
                            <h3 style="font-size: 1.2rem; color: #fff; margin-bottom: 0.35rem; line-height: 1.25;"><?php echo htmlspecialchars($prod['title']); ?></h3>
                            
                            <!-- Short Description -->
                            <p style="font-size: 0.85rem; line-height: 1.4; color: var(--color-text-slate); margin-bottom: var(--space-sm);">
                                <?php echo htmlspecialchars($prod['description'] ?? 'Downloadable PDF guide with actionable routine worksheets.'); ?>
                            </p>
                        </div>
                        
                        <!-- Pricing and actions -->
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: var(--space-sm); border-top: 1px solid rgba(255,255,255,0.04); padding-top: var(--space-xs);">
                                <span style="font-size: 1.2rem; font-weight: 700; color: var(--color-gold);">₹<?php echo number_format($prod['price']); ?></span>
                                <del style="font-size: 0.85rem; color: var(--color-text-slate);">₹<?php echo number_format($prod['original_price']); ?></del>
                            </div>
                            
                            <span class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8rem; padding: 0.55rem 1rem; display: flex; align-items: center; gap: 6px;">
                                View Details & Pricing <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer style="background: #03050c; color: var(--color-text-slate); padding: var(--space-md) 0; border-top: var(--glass-border) 1px solid; text-align: center; font-size: 0.85rem; position: relative; z-index: 2;">
        <div class="container">
            <p>&copy; 2026 tatvam.shop. All rights reserved. Support Desk: support@tatvam.shop</p>
        </div>
    </footer>

    <!-- JS Filtering Scripts -->
    <script>
        window.addEventListener('load', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Search Input Filter
        const searchInput = document.getElementById('library-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.ebook-card');
                
                cards.forEach(card => {
                    const title = card.getAttribute('data-title') || '';
                    if (title.includes(query)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Category Filter
        function filterCategory(cat, btn) {
            // Switch active buttons
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('.ebook-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category') || '';
                if (cat === 'all' || cardCat.toLowerCase() === cat.toLowerCase()) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
