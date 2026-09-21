<?php
if (!isset($product) || empty($product)) {
    require_once __DIR__ . '/db.php';
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE slug = 'Sanskar30' OR slug = 'sanskar-30' OR slug = 'sanskar30' OR title LIKE '%Sanskar%' LIMIT 1");
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {}
}
$display_price = isset($product['price']) ? (int)$product['price'] : 199;
$product_slug = isset($product['slug']) ? $product['slug'] : 'Sanskar30';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SANSKAR 30 • Tatvam | 30 Days of Good Habits, Strong Values & Happy Growing</title>
  <meta name="description" content="हर दिन सिर्फ 10-15 मिनट में अपने बच्चे को सिखाएं 30 अनमोल भारतीय संस्कार और अच्छी आदतें। 72 पृष्ठों की मुख्य ई-बुक और 43 पृष्ठों का पैरेंट टूलकिट सिर्फ ₹199 में।">
  <link rel="stylesheet" href="styles-sanskar.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://sdk.cashfree.com/js/v3/cashfree.js" defer></script>
</head>
<body>

  <!-- Navigation Header -->
  <header class="header">
    <div class="container navbar">
      <div class="nav-brand">
        <a href="#" class="logo-badge">
          <span>TATVAM</span>
        </a>
        <span class="logo-tagline">One Story. One Lesson. One Good Habit.</span>
      </div>

      <nav class="nav-links">
        <a href="#problem">चुनौतियां</a>
        <a href="#solution">समाधान</a>
        <a href="#bundle">क्या मिलेगा</a>
        <a href="#values">30 संस्कार</a>
        <a href="#preview">झलक</a>
        <a href="#faq">सवाल-जवाब</a>
      </nav>

      <div class="nav-cta">
        <button type="button" class="btn btn-primary checkout-trigger" data-product-slug="<?php echo htmlspecialchars($product_slug); ?>" style="padding: 9px 20px; font-size: 14.5px;">अभी पाएं — ₹<?php echo $display_price; ?></button>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container hero-grid">
      
      <!-- Hero Content -->
      <div class="hero-content">
        <div class="trust-chip">
          <span>⭐ 5 से 12 वर्ष के बच्चों के लिए विशेष भारतीय संस्कार और आदतें</span>
        </div>

        <h1 class="hero-title">
          हर दिन सिर्फ 10–15 मिनट, बच्चे के लिए <span class="highlight">एक अच्छी सीख</span> और पक्की आदत।
        </h1>

        <p class="hero-subtitle">
          30 दिल छू लेने वाली भारतीय कहानियाँ, 30 इंटरैक्टिव एक्टिविटीज़, पैरेंट कन्वर्सेशन गाइड्स और लाइफटाइम एक्टिविटी किट — बिना स्क्रीन, बिना बोरिंग उपदेश।
        </p>

        <!-- Chips -->
        <div class="hero-chips">
          <div class="hero-chip-item">📖 72-पृष्ठ मुख्य ई-बुक</div>
          <div class="hero-chip-item">🧩 43-पृष्ठ पैरेंट टूलकिट</div>
          <div class="hero-chip-item">🎨 50 बोनस स्क्रीन-फ्री खेल</div>
          <div class="hero-chip-item">📜 रॉयल संस्कार सर्टिफिकेट</div>
        </div>

        <!-- Pricing & Hero CTA Box -->
        <div class="price-cta-box">
          <div class="price-row">
            <div class="price-tag">
              <span class="price-currency">₹</span>
              <span class="price-amount"><?php echo $display_price; ?></span>
            </div>
            <div class="price-label">
              <span>एक पिज़्ज़ा से भी कम में</span><br>
              <strong>जीवनभर की सीख (One-time)</strong>
            </div>
          </div>

          <button type="button" class="btn btn-primary btn-large btn-pulse checkout-trigger" data-product-slug="<?php echo htmlspecialchars($product_slug); ?>" style="width: 100%;">पूरा डिजिटल बंडल तुरंत डाउनलोड करें — ₹<?php echo $display_price; ?> ➔</button>

          <div class="price-security-note">
            <span>⚡ सुरक्षित UPI / कार्ड पेमेंट</span>
            <span>•</span>
            <span>तुरंत PDF डाउनलोड</span>
            <span>•</span>
            <span>लाइफटाइम एक्सेस</span>
          </div>
        </div>
      </div>

      <!-- Hero Mockup Display (Realistic 3D Stack) -->
      <div class="mockup-wrapper">
        <div class="bundle-stage">
          
          <!-- 3D Main Book Mockup -->
          <div class="book-mockup">
            <div class="book-spine"></div>
            <div class="book-cover-content">
              <div class="mockup-brand">TATVAM PRESENTS</div>
              <div>
                <div class="mockup-title">SANSKAR 30</div>
                <div class="mockup-subtitle">30 Days of Good Habits, Strong Values & Happy Growing</div>
              </div>
              
              <div class="mockup-hero-art">
                <svg class="hero-cover-svg" viewBox="0 0 600 420" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="coverSky" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#0F2D3C"/>
      <stop offset="45%" stop-color="#1E4E63"/>
      <stop offset="80%" stop-color="#D97706"/>
      <stop offset="100%" stop-color="#FBBF24"/>
    </linearGradient>
    <linearGradient id="groundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#2D5A27"/>
      <stop offset="100%" stop-color="#143313"/>
    </linearGradient>
    <radialGradient id="sunGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FEF08A" stop-opacity="0.9"/>
      <stop offset="40%" stop-color="#F59E0B" stop-opacity="0.4"/>
      <stop offset="100%" stop-color="#D97706" stop-opacity="0"/>
    </radialGradient>
    <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feComposite in="SourceGraphic" in2="blur" operator="over"/>
    </filter>
  </defs>

  <!-- Sky -->
  <rect width="600" height="420" fill="url(#coverSky)" rx="16"/>

  <!-- Warm Evening Sun Glow -->
  <circle cx="300" cy="240" r="140" fill="url(#sunGlow)"/>

  <!-- Distant Hill Silhouettes -->
  <path d="M0,320 Q150,260 320,290 T600,270 L600,420 L0,420 Z" fill="#20402E" opacity="0.75"/>
  <path d="M0,340 Q220,300 420,330 T600,310 L600,420 L0,420 Z" fill="url(#groundGrad)"/>

  <!-- Traditional Indian Courtyard Verandah / Banyan Canopy -->
  <path d="M-20,90 Q120,40 280,70 T620,50 L620,-10 L-20,-10 Z" fill="#143313" opacity="0.8"/>
  <path d="M20,120 Q160,70 300,100 T580,80 L580,-10 L20,-10 Z" fill="#254F22" opacity="0.6"/>

  <!-- Hanging Warm Lanterns -->
  <g filter="url(#softGlow)">
    <circle cx="160" cy="140" r="18" fill="#FEF08A" opacity="0.6"/>
    <circle cx="440" cy="130" r="18" fill="#FEF08A" opacity="0.6"/>
  </g>
  <!-- Lantern 1 -->
  <line x1="160" y1="70" x2="160" y2="130" stroke="#FDE68A" stroke-width="2"/>
  <rect x="150" y="130" width="20" height="24" rx="4" fill="#E06D28"/>
  <rect x="154" y="134" width="12" height="16" rx="2" fill="#FEF08A"/>
  <!-- Lantern 2 -->
  <line x1="440" y1="60" x2="440" y2="120" stroke="#FDE68A" stroke-width="2"/>
  <rect x="430" y="120" width="20" height="24" rx="4" fill="#E06D28"/>
  <rect x="434" y="124" width="12" height="16" rx="2" fill="#FEF08A"/>

  <!-- Grandparents in Background (Dadu & Dadi sitting on porch bench) -->
  <g transform="translate(110, 240)">
    <!-- Bench -->
    <rect x="0" y="70" width="130" height="8" rx="3" fill="#854D0E"/>
    <rect x="10" y="78" width="6" height="24" fill="#5A3207"/>
    <rect x="114" y="78" width="6" height="24" fill="#5A3207"/>
    <!-- Dadu (Grandpa) -->
    <circle cx="35" cy="30" r="14" fill="#E0A96D"/>
    <!-- White hair -->
    <path d="M22,28 C22,16 48,16 48,28 C45,20 25,20 22,28 Z" fill="#E2E8F0"/>
    <!-- Spectacles -->
    <circle cx="31" cy="29" r="4" fill="none" stroke="#64748B" stroke-width="1.5"/>
    <circle cx="39" cy="29" r="4" fill="none" stroke="#64748B" stroke-width="1.5"/>
    <line x1="35" y1="29" x2="35" y2="29" stroke="#64748B" stroke-width="1.5"/>
    <!-- Kurta -->
    <path d="M20,44 C20,44 26,42 35,42 C44,42 50,44 50,44 L48,72 L22,72 Z" fill="#F8FAFC"/>

    <!-- Dadi (Grandma) -->
    <circle cx="85" cy="32" r="13" fill="#E0A96D"/>
    <!-- Grey hair bun -->
    <circle cx="97" cy="30" r="7" fill="#CBD5E1"/>
    <!-- Saree pallu -->
    <path d="M72,44 C72,44 80,42 88,42 C96,42 100,44 100,44 L98,72 L72,72 Z" fill="#D8B4E2"/>
    <path d="M74,32 C74,24 94,22 96,34 L88,55 L74,45 Z" fill="#B388D6" opacity="0.85"/>
  </g>

  <!-- Parents in Warm Embrace Behind Children -->
  <g transform="translate(370, 235)">
    <!-- Papa -->
    <circle cx="35" cy="28" r="14" fill="#E0A96D"/>
    <!-- Dark neat hair -->
    <path d="M21,26 C21,14 49,14 49,26 C45,18 25,18 21,26 Z" fill="#1C1917"/>
    <!-- Shirt -->
    <path d="M20,42 C20,42 26,40 35,40 C44,40 50,42 50,42 L50,75 L20,75 Z" fill="#0284C7"/>
    <!-- Maa -->
    <circle cx="75" cy="30" r="13" fill="#E0A96D"/>
    <circle cx="87" cy="32" r="8" fill="#1C1917"/> <!-- Hair bun -->
    <!-- Saree -->
    <path d="M62,43 C62,43 70,41 78,41 C86,41 90,43 90,43 L88,75 L62,75 Z" fill="#F43F5E"/>
    <path d="M63,30 C63,22 84,20 87,32 L80,56 L63,45 Z" fill="#E11D48" opacity="0.9"/>
  </g>

  <!-- Central Hero Children: AARAV & ANAYA Reading Together -->
  <!-- Aarav (Left child) -->
  <g transform="translate(205, 275)">
    <!-- Body -->
    <rect x="20" y="44" width="34" height="42" rx="8" fill="#E59834"/> <!-- Ochre T-shirt -->
    <rect x="24" y="80" width="26" height="24" rx="4" fill="#1E3A8A"/> <!-- Navy shorts -->
    <!-- Head -->
    <circle cx="37" cy="24" r="18" fill="#E0A96D"/>
    <!-- Messy lively dark hair -->
    <path d="M19,22 C18,8 32,4 42,8 C50,12 55,20 54,26 C50,22 46,24 43,21 C40,24 35,21 31,23 C27,21 23,24 19,22 Z" fill="#1C1917"/>
    <!-- Happy Face -->
    <circle cx="32" cy="24" r="2.2" fill="#1C1917"/>
    <circle cx="43" cy="24" r="2.2" fill="#1C1917"/>
    <path d="M34,29 Q37.5,34 41,29" fill="none" stroke="#9A3412" stroke-width="2" stroke-linecap="round"/>
    <!-- Hands holding book -->
    <circle cx="48" cy="58" r="5" fill="#E0A96D"/>
  </g>

  <!-- The Open Golden Book between them -->
  <g transform="translate(270, 318)">
    <ellipse cx="30" cy="18" rx="34" ry="12" fill="#FEF3C7"/>
    <path d="M-2,14 Q30,6 62,14 L60,34 Q30,26 -2,34 Z" fill="#FFFBEB" stroke="#D97706" stroke-width="1.5"/>
    <line x1="30" y1="10" x2="30" y2="30" stroke="#B45309" stroke-width="1.5"/>
    <!-- Glowing Golden rays from book -->
    <path d="M10,8 L-4,-10 M30,4 L30,-14 M50,8 L64,-10" stroke="#FBBF24" stroke-width="2.5" stroke-linecap="round" opacity="0.85"/>
  </g>

  <!-- Anaya (Right child) -->
  <g transform="translate(325, 280)">
    <!-- Body -->
    <path d="M18,44 L46,44 L54,84 L10,84 Z" fill="#FACC15"/> <!-- Marigold yellow dress -->
    <path d="M14,84 L50,84 L52,87 L12,87 Z" fill="#0D9488"/> <!-- Teal hem -->
    <!-- Head -->
    <circle cx="32" cy="23" r="17" fill="#F3C99F"/>
    <!-- Hair with two pigtails and teal bows -->
    <path d="M15,22 C15,8 30,5 40,9 C48,13 49,22 48,26 C44,20 37,20 32,22 C26,20 20,22 15,22 Z" fill="#1C1917"/>
    <circle cx="12" cy="22" r="7" fill="#1C1917"/> <!-- Left pigtail -->
    <circle cx="51" cy="22" r="7" fill="#1C1917"/> <!-- Right pigtail -->
    <circle cx="14" cy="25" r="3" fill="#0D9488"/> <!-- Teal bow -->
    <circle cx="49" cy="25" r="3" fill="#0D9488"/> <!-- Teal bow -->
    <!-- Sweet Face -->
    <circle cx="28" cy="24" r="2.2" fill="#1C1917"/>
    <circle cx="38" cy="24" r="2.2" fill="#1C1917"/>
    <path d="M30,29 Q33,33 36,29" fill="none" stroke="#9A3412" stroke-width="2" stroke-linecap="round"/>
    <circle cx="25" cy="27" r="2.5" fill="#FDA4AF" opacity="0.6"/> <!-- Rosy cheek -->
    <circle cx="41" cy="27" r="2.5" fill="#FDA4AF" opacity="0.6"/>
  </g>

  <!-- Puppy Moti (Curled happily in front) -->
  <g transform="translate(210, 355)">
    <!-- Body -->
    <ellipse cx="28" cy="18" rx="16" ry="11" fill="#FFFFFF"/>
    <!-- Golden brown spot -->
    <circle cx="32" cy="16" r="7" fill="#D97706"/>
    <!-- Head -->
    <circle cx="16" cy="12" r="10" fill="#FFFFFF"/>
    <!-- Floppy ear -->
    <ellipse cx="11" cy="13" rx="4" ry="7" fill="#B45309"/>
    <!-- Eye & nose -->
    <circle cx="16" cy="11" r="1.5" fill="#1C1917"/>
    <circle cx="12" cy="14" r="1.5" fill="#1C1917"/>
    <!-- Wagging tail -->
    <path d="M42,16 Q48,10 50,14" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
  </g>

  <!-- Golden Sparkles / Stars in Sky -->
  <g fill="#FEF08A" opacity="0.8">
    <polygon points="120,45 122,50 127,52 122,54 120,59 118,54 113,52 118,50"/>
    <polygon points="480,50 482,55 487,57 482,59 480,64 478,59 473,57 478,55"/>
    <polygon points="260,35 261,39 265,40 261,41 260,45 259,41 255,40 259,39"/>
    <polygon points="360,40 361,44 365,45 361,46 360,50 359,46 355,45 359,44"/>
  </g>
  </svg>
              </div>

              <div class="mockup-footer">
                <span>72 Full-Color Pages</span>
                <span>Tatvam.shop</span>
              </div>
            </div>
          </div>

          <!-- 3D Toolkit Mockup (Behind Right) -->
          <div class="toolkit-mockup">
            <div class="toolkit-cover-content">
              <div class="mockup-brand">PARENT & ACTIVITY TOOLKIT</div>
              <div style="font-size: 18px; font-weight: 800; color: #FFFFFF; line-height: 1.15;">
                डेली कन्वर्सेशन & एक्टिविटी गाइड
              </div>
              <div style="height: 140px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.3);">
                <svg class="toolkit-cover-svg" viewBox="0 0 600 380" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="tkSky" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#0E7490"/>
      <stop offset="60%" stop-color="#155E75"/>
      <stop offset="100%" stop-color="#F59E0B"/>
    </linearGradient>
  </defs>
  <rect width="600" height="380" fill="url(#tkSky)" rx="16"/>

  <!-- Warm Living Room Table Setting -->
  <path d="M0,280 Q300,260 600,280 L600,380 L0,380 Z" fill="#F8FAFC" opacity="0.95"/>
  <!-- Wooden Table Surface -->
  <path d="M40,290 L560,290 L580,380 L20,380 Z" fill="#78350F" opacity="0.85"/>
  <rect x="50" y="295" width="500" height="80" rx="6" fill="#B45309" opacity="0.9"/>

  <!-- Family sitting around table (Maa, Papa, Aarav, Anaya) -->
  <!-- Papa -->
  <g transform="translate(100, 160)">
    <circle cx="40" cy="40" r="22" fill="#E0A96D"/>
    <path d="M18,36 C18,18 62,18 62,36 C55,25 25,25 18,36 Z" fill="#1C1917"/>
    <path d="M15,62 C15,62 25,58 40,58 C55,58 65,62 65,62 L62,130 L18,130 Z" fill="#0284C7"/>
  </g>

  <!-- Maa -->
  <g transform="translate(420, 160)">
    <circle cx="40" cy="40" r="21" fill="#E0A96D"/>
    <circle cx="60" cy="42" r="12" fill="#1C1917"/>
    <path d="M16,62 C16,62 26,58 40,58 C54,58 64,62 64,62 L62,130 L16,130 Z" fill="#E11D48"/>
  </g>

  <!-- Aarav -->
  <g transform="translate(200, 210)">
    <circle cx="35" cy="35" r="20" fill="#E0A96D"/>
    <path d="M15,32 C14,16 30,12 40,16 C50,20 55,30 53,36 C49,32 45,34 42,31 C39,34 34,31 30,33 C25,31 20,34 15,32 Z" fill="#1C1917"/>
    <circle cx="29" cy="35" r="2.5" fill="#1C1917"/>
    <circle cx="41" cy="35" r="2.5" fill="#1C1917"/>
    <path d="M31,42 Q35,47 39,42" fill="none" stroke="#9A3412" stroke-width="2"/>
    <rect x="15" y="55" width="40" height="45" rx="6" fill="#E59834"/>
  </g>

  <!-- Anaya -->
  <g transform="translate(320, 215)">
    <circle cx="35" cy="35" r="19" fill="#F3C99F"/>
    <path d="M16,33 C16,18 32,15 44,19 C52,23 53,33 52,37 C48,31 41,31 35,33 C29,31 22,33 16,33 Z" fill="#1C1917"/>
    <circle cx="12" cy="33" r="8" fill="#1C1917"/>
    <circle cx="56" cy="33" r="8" fill="#1C1917"/>
    <circle cx="15" cy="37" r="3.5" fill="#0D9488"/>
    <circle cx="53" cy="37" r="3.5" fill="#0D9488"/>
    <circle cx="30" cy="36" r="2.5" fill="#1C1917"/>
    <circle cx="41" cy="36" r="2.5" fill="#1C1917"/>
    <path d="M32,43 Q36,47 40,43" fill="none" stroke="#9A3412" stroke-width="2"/>
    <rect x="18" y="54" width="36" height="46" rx="6" fill="#FACC15"/>
  </g>

  <!-- Toolkit Cards on the Table -->
  <!-- Card 1: Conversation Card -->
  <g transform="translate(130, 310) rotate(-6)">
    <rect x="0" y="0" width="70" height="50" rx="4" fill="#FFFFFF" stroke="#0E7490" stroke-width="2"/>
    <rect x="6" y="6" width="58" height="8" rx="2" fill="#0E7490"/>
    <circle cx="35" cy="28" r="10" fill="#FEF08A"/>
    <text x="35" y="32" font-size="10" text-anchor="middle" fill="#78350F" font-weight="bold">?</text>
  </g>

  <!-- Card 2: Star Chart / Tracker -->
  <g transform="translate(240, 305)">
    <rect x="0" y="0" width="120" height="60" rx="4" fill="#FFFFFF" stroke="#D97706" stroke-width="2"/>
    <rect x="10" y="8" width="100" height="8" rx="2" fill="#FEF3C7"/>
    <!-- Tiny Stars -->
    <g fill="#F59E0B">
      <polygon points="25,28 27,33 32,34 28,37 29,42 25,39 21,42 22,37 18,34 23,33"/>
      <polygon points="50,28 52,33 57,34 53,37 54,42 50,39 46,42 47,37 43,34 48,33"/>
      <polygon points="75,28 77,33 82,34 78,37 79,42 75,39 71,42 72,37 68,34 73,33"/>
      <polygon points="100,28 102,33 107,34 103,37 104,42 100,39 96,42 97,37 93,34 98,33"/>
    </g>
  </g>

  <!-- Card 3: Certificate / Gold Badge -->
  <g transform="translate(390, 310) rotate(8)">
    <rect x="0" y="0" width="75" height="52" rx="4" fill="#FFFBEB" stroke="#B45309" stroke-width="2"/>
    <circle cx="37" cy="26" r="14" fill="#F59E0B"/>
    <circle cx="37" cy="26" r="11" fill="#FEF3C7"/>
    <text x="37" y="30" font-size="11" text-anchor="middle" fill="#B45309" font-weight="bold">★</text>
  </g>

  <!-- Colored pencils on table -->
  <rect x="80" y="340" width="35" height="4" rx="2" fill="#EF4444" transform="rotate(15 80 340)"/>
  <rect x="490" y="335" width="40" height="4" rx="2" fill="#10B981" transform="rotate(-20 490 335)"/>
  </svg>
              </div>
              <div class="mockup-footer">
                <span>43 Pages • Worksheets & Cards</span>
                <span>Complete Kit</span>
              </div>
            </div>
          </div>

          <!-- Floating Badge -->
          <div class="floating-card-badge">
            <div class="floating-icon">🏆</div>
            <div>
              <div class="floating-text-title">30 दिन का कम्पलीट ट्रांसफॉर्मेशन</div>
              <div class="floating-text-sub">रोज़ाना सिर्फ 10-15 मिनट की जादुई आदत</div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>

  <!-- Problem Section -->
  <section class="section section-alt" id="problem">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">आज की हकीकत</span>
        <h2 class="section-title">क्या आप भी इन 4 बातों को लेकर परेशान हैं?</h2>
        <p class="section-subtitle">
          आधुनिक जीवनशैली में हर जागरूक भारतीय माता-पिता इन चुनौतियों से रोज़ाना जूझ रहे हैं:
        </p>
      </div>

      <div class="problem-grid">
        <!-- Problem 1 -->
        <div class="problem-card">
          <div class="problem-icon-wrap">📱</div>
          <h3 class="problem-card-title">बढ़ता स्क्रीन टाइम</h3>
          <p class="problem-card-desc">
            खाना खाते समय, पढ़ाई के बाद या बोर होते ही बच्चे का हाथ फोन या टीवी रिमोट पर चला जाता है।
          </p>
        </div>

        <!-- Problem 2 -->
        <div class="problem-card">
          <div class="problem-icon-wrap">🗣️</div>
          <h3 class="problem-card-title">डांट और उपदेश का बेअसर होना</h3>
          <p class="problem-card-desc">
            "सच बोलो", "बड़ों का आदर करो" जैसे सीधे उपदेश बच्चे सुनते ही अनसुना कर देते हैं या चिढ़ जाते हैं।
          </p>
        </div>

        <!-- Problem 3 -->
        <div class="problem-card">
          <div class="problem-icon-wrap">⏳</div>
          <h3 class="problem-card-title">सार्थक बातचीत का अभाव</h3>
          <p class="problem-card-desc">
            ऑफिस और घर की व्यस्तता के कारण माता-पिता और बच्चों के बीच गहरी, दिल से जुड़ने वाली बातें कम हो गई हैं।
          </p>
        </div>

        <!-- Problem 4 -->
        <div class="problem-card">
          <div class="problem-icon-wrap">📚</div>
          <h3 class="problem-card-title">सही सामग्री की कमी</h3>
          <p class="problem-card-desc">
            बाज़ार में या तो भारी-भरकम धार्मिक किताबें हैं या फिर पश्चिमी संदर्भ, जो आज के भारतीय बच्चों से रिलेट नहीं कर पाते।
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Solution Section -->
  <section class="section" id="solution">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">हमारा अनोखा तरीका</span>
        <h2 class="section-title">संस्कार 30 का 4-स्टेप फॉर्मूला</h2>
        <p class="section-subtitle">
          हम उपदेश नहीं देते, बल्कि कहानी और एक्टिविटी के ज़रिए मूल्यों को बच्चे के स्वभाव का हिस्सा बनाते हैं।
        </p>
      </div>

      <div class="formula-container">
        <div class="formula-flow">
          <!-- Step 1 -->
          <div class="formula-step">
            <div class="formula-step-icon">📖</div>
            <div class="formula-step-num">STEP 01</div>
            <div class="formula-step-title">प्यारी कहानी</div>
            <div class="formula-step-desc">आरव और अनाया के जीवन की सच्ची, मनोरंजक घटना जो सीधे दिल को छूती है।</div>
          </div>

          <!-- Step 2 -->
          <div class="formula-step">
            <div class="formula-step-icon">💡</div>
            <div class="formula-step-num">STEP 02</div>
            <div class="formula-step-title">सहज सीख</div>
            <div class="formula-step-desc">बिना किसी जबरदस्ती के, बच्चा खुद कहानी से सही और गलत का अंतर समझता है।</div>
          </div>

          <!-- Step 3 -->
          <div class="formula-step">
            <div class="formula-step-icon">🧩</div>
            <div class="formula-step-num">STEP 03</div>
            <div class="formula-step-title">हैंड्स-ऑन एक्टिविटी</div>
            <div class="formula-step-desc">ड्राइंग, पज़ल या छोटा-सा घरेलू काम जो उस सीख को तुरंत व्यवहार में उतारता है।</div>
          </div>

          <!-- Step 4 -->
          <div class="formula-step">
            <div class="formula-step-icon">🌱</div>
            <div class="formula-step-num">STEP 04</div>
            <div class="formula-step-title">पक्की आदत</div>
            <div class="formula-step-desc">30 दिनों में यह सीख बच्चे के चरित्र और रोज़मर्रा के व्यवहार का स्थायी हिस्सा बन जाती है।</div>
          </div>
        </div>

        <div class="formula-footer-box">
          <span>✨</span>
          <span>फॉर्मूला: एक दिन • एक कहानी • एक सीख • एक पक्की आदत</span>
        </div>
      </div>
    </div>
  </section>

  <!-- What's Inside The Bundle -->
  <section class="section section-alt" id="bundle">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">कम्पलीट बंडल</span>
        <h2 class="section-title">इस बंडल में आपको क्या-क्या मिलता है?</h2>
        <p class="section-subtitle">
          सिर्फ एक किताब नहीं, बल्कि पूरे परिवार के लिए एक संपूर्ण 30-दिवसीय वैल्यू-बिल्डिंग सिस्टम।
        </p>
      </div>

      <div class="bundle-cards-grid">
        
        <!-- Card 1 -->
        <div class="bundle-card featured">
          <span class="featured-pill">मुख्य ई-बुक</span>
          <div>
            <div class="bundle-card-header">
              <div class="bundle-card-icon icon-blue">📘</div>
              <div>
                <h3 class="bundle-card-title">SANSKAR 30 सचित्र ई-बुक</h3>
                <span class="bundle-card-tag">72 फुल-कलर प्रीमियम पेजेस</span>
              </div>
            </div>
            <p style="font-size: 14.5px; color: var(--text-body);">
              30 दिनों के लिए 30 खूबसूरत मौलिक कहानियाँ। सहज, सरल हिंदी में लिखी गई जो 5-12 साल के बच्चों के दिल में उतर जाए।
            </p>
            <ul class="bundle-feature-list">
              <li><span class="check">✓</span> 30 मौलिक सचित्र भारतीय कहानियाँ</li>
              <li><span class="check">✓</span> सजीव और दोस्ताना चरित्र (आरव, अनाया और मोती)</li>
              <li><span class="check">✓</span> हर कहानी के अंत में विचारोत्तेजक सीख और कोट</li>
              <li><span class="check">✓</span> फोन, टैबलेट या प्रिंट — कहीं भी आसानी से पढ़ें</li>
            </ul>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="bundle-card">
          <div>
            <div class="bundle-card-header">
              <div class="bundle-card-icon icon-orange">📙</div>
              <div>
                <h3 class="bundle-card-title">पैरेंट & एक्टिविटी टूलकिट</h3>
                <span class="bundle-card-tag">43 व्यावहारिक पेजेस</span>
              </div>
            </div>
            <p style="font-size: 14.5px; color: var(--text-body);">
              माता-पिता के लिए रेडीमेड गाइड, जिससे आप बिना किसी तैयारी के बच्चे के साथ सार्थक चर्चा शुरू कर सकें।
            </p>
            <ul class="bundle-feature-list">
              <li><span class="check">✓</span> 30 डेली कन्वर्सेशन स्टार्टर कार्ड्स (डिनर/बेडटाइम)</li>
              <li><span class="check">✓</span> 30 इंटरैक्टिव एक्टिविटी शीट्स और रिफ्लेक्शन वर्कशीट्स</li>
              <li><span class="check">✓</span> मुश्किल व्यवहारों को संभालने के व्यावहारिक पैरेंटिंग टिप्स</li>
              <li><span class="check">✓</span> प्रिंट-रेडी A4 फॉर्मेट</li>
            </ul>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="bundle-card">
          <div>
            <div class="bundle-card-header">
              <div class="bundle-card-icon icon-green">🧩</div>
              <div>
                <h3 class="bundle-card-title">50 स्क्रीन-फ्री एक्टिविटी गाइड</h3>
                <span class="bundle-card-tag">बोनस किट</span>
              </div>
            </div>
            <p style="font-size: 14.5px; color: var(--text-body);">
              छुट्टियों और वीकेंड पर बच्चों को बिना टीवी और मोबाइल के व्यस्त और खुश रखने के 50 रचनात्मक खेल।
            </p>
            <ul class="bundle-feature-list">
              <li><span class="check">✓</span> इंडोर खोज और मजेदार साइंस एक्टिविटीज</li>
              <li><span class="check">✓</span> प्रकृति और पौधों से जुड़ने वाले मजेदार टास्क</li>
              <li><span class="check">✓</span> घर में उपलब्ध आसान सामान से खेलने योग्य गतिविधियाँ</li>
              <li><span class="check">✓</span> भाई-बहनों और माता-पिता के संग खेलने वाले खेल</li>
            </ul>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="bundle-card">
          <div>
            <div class="bundle-card-header">
              <div class="bundle-card-icon icon-purple">🏆</div>
              <div>
                <h3 class="bundle-card-title">प्रोग्रेस ट्रैकर & रॉयल सर्टिफिकेट</h3>
                <span class="bundle-card-tag">उत्साह और पुरस्कार</span>
              </div>
            </div>
            <p style="font-size: 14.5px; color: var(--text-body);">
              बच्चे के उत्साह को रोज़ाना बनाए रखने के लिए विज़ुअल स्टार चार्ट और 30 दिन पूरे करने पर सम्मान पत्र।
            </p>
            <ul class="bundle-feature-list">
              <li><span class="check">✓</span> 30-Day Sanskar Star Tracker (दीवार पर लगाने योग्य)</li>
              <li><span class="check">✓</span> आधिकारिक "संस्कार रत्न" पूर्णता सम्मान-पत्र</li>
              <li><span class="check">✓</span> बच्चे के आत्म-सम्मान और उपलब्धि की भावना में वृद्धि</li>
              <li><span class="check">✓</span> आजीवन यादगार उपहार</li>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 30 Days Values Visual Grid -->
  <section class="section" id="values">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">संपूर्ण पाठ्यक्रम</span>
        <h2 class="section-title">30 दिनों में 30 अनमोल जीवन-मूल्य</h2>
        <p class="section-subtitle">
          हर दिन एक नया विषय जो बच्चे के चरित्र की मजबूत नींव तैयार करता है:
        </p>
      </div>

      <div class="values-grid">
        
    <div class="value-item-card">
      <div class="value-day-badge">Day 1</div>
      <div class="value-names">
        <div class="value-name-hindi">ईमानदारी</div>
        <div class="value-name-eng">HONESTY • <em>आरव और मिट्टी का पुराना चिराग</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 2</div>
      <div class="value-names">
        <div class="value-name-hindi">दयालुता</div>
        <div class="value-name-eng">KINDNESS • <em>अनाया की पीली छतरी</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 3</div>
      <div class="value-names">
        <div class="value-name-hindi">सम्मान</div>
        <div class="value-name-eng">RESPECT • <em>दादू की छड़ी और पुरानी बातें</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 4</div>
      <div class="value-names">
        <div class="value-name-hindi">कृतज्ञता</div>
        <div class="value-name-eng">GRATITUDE • <em>टिफ़िन बॉक्स का जादुई खत</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 5</div>
      <div class="value-names">
        <div class="value-name-hindi">बाँटना</div>
        <div class="value-name-eng">SHARING • <em>सुनहरी कंचे और नया पड़ोसी</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 6</div>
      <div class="value-names">
        <div class="value-name-hindi">मदद करना</div>
        <div class="value-name-eng">HELPING OTHERS • <em>सड़क पर बिखरे नीबू</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 7</div>
      <div class="value-names">
        <div class="value-name-hindi">धैर्य</div>
        <div class="value-name-eng">PATIENCE • <em>आम की गुठली और नन्हा पौधा</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 8</div>
      <div class="value-names">
        <div class="value-name-hindi">साहस</div>
        <div class="value-name-eng">COURAGE • <em>क्लास की चौथी बेंच</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 9</div>
      <div class="value-names">
        <div class="value-name-hindi">ज़िम्मेदारी</div>
        <div class="value-name-eng">RESPONSIBILITY • <em>मोती और सुबह की सैर</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 10</div>
      <div class="value-names">
        <div class="value-name-hindi">स्वच्छता</div>
        <div class="value-name-eng">CLEANLINESS • <em>चमकती हुई क्लासरूम</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 11</div>
      <div class="value-names">
        <div class="value-name-hindi">अनुशासन</div>
        <div class="value-name-eng">DISCIPLINE • <em>रात के दस मिनट का जादू</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 12</div>
      <div class="value-names">
        <div class="value-name-hindi">क्षमा</div>
        <div class="value-name-eng">FORGIVENESS • <em>छत पर फटी हुई पतंग</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 13</div>
      <div class="value-names">
        <div class="value-name-hindi">हमदर्दी</div>
        <div class="value-name-eng">EMPATHY • <em>कबीर का खामोश लंच</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 14</div>
      <div class="value-names">
        <div class="value-name-hindi">आत्मविश्वास</div>
        <div class="value-name-eng">CONFIDENCE • <em>मंच और नन्हीं कविता</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 15</div>
      <div class="value-names">
        <div class="value-name-hindi">कड़ी मेहनत</div>
        <div class="value-name-eng">HARD WORK • <em>मिट्टी का मज़बूत मटका</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 16</div>
      <div class="value-names">
        <div class="value-name-hindi">आत्म-नियंत्रण</div>
        <div class="value-name-eng">SELF-CONTROL • <em>बेसन के लड्डुओं का जार</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 17</div>
      <div class="value-names">
        <div class="value-name-hindi">पशु-प्रेम</div>
        <div class="value-name-eng">CARING FOR ANIMALS • <em>घायल चिड़िया का घोंसला</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 18</div>
      <div class="value-names">
        <div class="value-name-hindi">माता-पिता का सम्मान</div>
        <div class="value-name-eng">RESPECTING PARENTS • <em>पापा के लिए एक गिलास पानी</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 19</div>
      <div class="value-names">
        <div class="value-name-hindi">गुरु-सत्कार</div>
        <div class="value-name-eng">RESPECTING TEACHERS • <em>ब्लैकबोर्ड पर बना दिल</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 20</div>
      <div class="value-names">
        <div class="value-name-hindi">टीमवर्क</div>
        <div class="value-name-eng">TEAMWORK • <em>गुलमोहर का ट्री-हाउस</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 21</div>
      <div class="value-names">
        <div class="value-name-hindi">वादा निभाना</div>
        <div class="value-name-eng">KEEPING PROMISES • <em>रविवार का मैच और वादा</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 22</div>
      <div class="value-names">
        <div class="value-name-hindi">माफ़ी माँगना</div>
        <div class="value-name-eng">SAYING SORRY • <em>रेत का टूटा हुआ महल</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 23</div>
      <div class="value-names">
        <div class="value-name-hindi">धन्यवाद कहना</div>
        <div class="value-name-eng">SAYING THANK YOU • <em>रामू काका की गरम चाय</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 24</div>
      <div class="value-names">
        <div class="value-name-hindi">हार से सीखना</div>
        <div class="value-name-eng">HANDLING FAILURE • <em>रजत पदक और असली जीत</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 25</div>
      <div class="value-names">
        <div class="value-name-hindi">उदास दोस्त का सहारा</div>
        <div class="value-name-eng">HELPING SOMEONE WHO IS SAD • <em>माया के लिए सूरज का चित्र</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 26</div>
      <div class="value-names">
        <div class="value-name-hindi">सच्ची मित्रता</div>
        <div class="value-name-eng">BEING A GOOD FRIEND • <em>टूटी हुई हरी पेंसिल</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 27</div>
      <div class="value-names">
        <div class="value-name-hindi">समय का सदुपयोग</div>
        <div class="value-name-eng">USING TIME WISELY • <em>दीवार घड़ी की टिक-टिक</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 28</div>
      <div class="value-names">
        <div class="value-name-hindi">प्रकृति की रक्षा</div>
        <div class="value-name-eng">CARING FOR NATURE • <em>गली में नीम का नन्हा पौधा</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 29</div>
      <div class="value-names">
        <div class="value-name-hindi">परिवार का प्यार</div>
        <div class="value-name-eng">APPRECIATING FAMILY • <em>छत पर तारों भरी रात</em></div>
      </div>
    </div>
  

    <div class="value-item-card">
      <div class="value-day-badge">Day 30</div>
      <div class="value-names">
        <div class="value-name-hindi">बेहतर इंसान बनना</div>
        <div class="value-name-eng">BECOMING A BETTER YOU • <em>आसमान की ओर तीस कदम</em></div>
      </div>
    </div>
  
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="section section-alt" id="how-it-works">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">दैनिक दिनचर्या</span>
        <h2 class="section-title">यह आपकी व्यस्त ज़िंदगी में कैसे फिट होता है?</h2>
        <p class="section-subtitle">
          आपको घंटों का समय निकालने की ज़रूरत नहीं है। सिर्फ 10-15 मिनट का समर्पित समय ही काफी है।
        </p>
      </div>

      <div class="steps-grid">
        <!-- Step 1 -->
        <div class="step-card">
          <div class="step-number">01</div>
          <div class="step-action-tag">OPEN & RELAX</div>
          <h3 class="step-card-title">10 मिनट निकालें</h3>
          <p class="step-card-desc">
            शाम को स्कूल के बाद या रात को सोने से ठीक पहले, फोन को किनारे रखकर 10 मिनट बच्चे के साथ बैठें।
          </p>
        </div>

        <!-- Step 2 -->
        <div class="step-card">
          <div class="step-number">02</div>
          <div class="step-action-tag">READ TOGETHER</div>
          <h3 class="step-card-title">कहानी पढ़ें</h3>
          <p class="step-card-desc">
            आज के दिन की छोटी और रोचक कहानी बच्चे को पढ़कर सुनाएँ या बच्चे से पढ़वाएँ।
          </p>
        </div>

        <!-- Step 3 -->
        <div class="step-card">
          <div class="step-number">03</div>
          <div class="step-action-tag">QUICK CHAT</div>
          <h3 class="step-card-title">2 सवाल पूछें</h3>
          <p class="step-card-desc">
            पैरेंट किट में दिए गए बातचीत कार्ड से 2 सवाल पूछें और बच्चे के मन की बात सुनें।
          </p>
        </div>

        <!-- Step 4 -->
        <div class="step-card">
          <div class="step-number">04</div>
          <div class="step-action-tag">MARK A STAR</div>
          <h3 class="step-card-title">स्टार लगाएं</h3>
          <p class="step-card-desc">
            एक्टिविटी पूरी करें और 30-Day ट्रैकर पर मिलकर स्टार भरें। बच्चे के चेहरे पर गर्व देखें!
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Parent Benefits -->
  <section class="section" id="benefits">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">वास्तविक परिणाम</span>
        <h2 class="section-title">30 दिनों में आपको क्या बदलाव नज़र आएंगे?</h2>
        <p class="section-subtitle">
          यह केवल ज्ञान नहीं देता, बल्कि बच्चे की आदतों और सोच में प्रत्यक्ष सकारात्मक बदलाव लाता है:
        </p>
      </div>

      <div class="benefits-grid">
        <div class="benefit-card">
          <div class="benefit-icon">📵</div>
          <h3 class="benefit-title">स्क्रीन टाइम में स्वाभाविक कमी</h3>
          <p class="benefit-desc">जब बच्चे को कहानियों और एक्टिविटीज में मज़ा आने लगता है, तो मोबाइल की ज़िद अपने आप कम हो जाती है।</p>
        </div>

        <div class="benefit-card">
          <div class="benefit-icon">🤝</div>
          <h3 class="benefit-title">शेयरिंग और दयालुता का व्यवहार</h3>
          <p class="benefit-desc">चीज़ें बांटना, दूसरों की मदद करना और जानवरों के प्रति करुणा बच्चे के स्वाभाविक स्वभाव में शामिल होने लगती है।</p>
        </div>

        <div class="benefit-card">
          <div class="benefit-icon">❤️</div>
          <h3 class="benefit-title">माता-पिता से गहरा भावनात्मक जुड़ाव</h3>
          <p class="benefit-desc">रोज़ाना 10 मिनट की बिना-स्क्रीन की बातचीत बच्चे के मन में सुरक्षा और विश्वास का अटूट रिश्ता बनाती है।</p>
        </div>

        <div class="benefit-card">
          <div class="benefit-icon">🗣️</div>
          <h3 class="benefit-title">सच बोलने और गलती स्वीकारने की हिम्मत</h3>
          <p class="benefit-desc">बच्चा डांट के डर से झूठ बोलना छोड़कर अपनी गलतियों को खुलकर साझा करना सीखता है।</p>
        </div>

        <div class="benefit-card">
          <div class="benefit-icon">🪔</div>
          <h3 class="benefit-title">भारतीय संस्कृति और मर्यादा का आदर</h3>
          <p class="benefit-desc">दादा-दादी, नाना-नानी और शिक्षकों के प्रति आदरभाव किसी दबाव से नहीं, बल्कि दिल से निकलता है।</p>
        </div>

        <div class="benefit-card">
          <div class="benefit-icon">😌</div>
          <h3 class="benefit-title">तनाव-मुक्त और खुशनुमा पेरेंटिंग</h3>
          <p class="benefit-desc">रोज़-रोज़ चिल्लाने और टोकने की ज़रूरत खत्म हो जाती है, जिससे घर का माहौल शांत और खुशहाल रहता है।</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Interactive Product Preview Gallery -->
  <section class="section section-alt" id="preview">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">किताब की झलक</span>
        <h2 class="section-title">अंदर से कैसा दिखता है संस्कार 30?</h2>
        <p class="section-subtitle">
          नीचे दिए गए टैब्स पर क्लिक करके देखें कि ई-बुक, एक्टिविटी पेजेस और पैरेंट गाइड्स का लेआउट कैसा है:
        </p>
      </div>

      <!-- Tabs Buttons -->
      <div class="preview-tabs">
        <button class="tab-btn active" data-tab="tab-cover">📘 ई-बुक कवर</button>
        <button class="tab-btn" data-tab="tab-story">📖 कहानी पेज</button>
        <button class="tab-btn" data-tab="tab-activity">🧩 एक्टिविटी शीट</button>
        <button class="tab-btn" data-tab="tab-conversation">💬 पैरेंट कार्ड</button>
        <button class="tab-btn" data-tab="tab-tracker">🌟 स्टार ट्रैकर</button>
        <button class="tab-btn" data-tab="tab-certificate">📜 सर्टिफिकेट</button>
      </div>

      <!-- Preview Stage Display Box -->
      <div class="preview-display-box">
        <div class="preview-mockup-frame">
          
  <!-- Panel 1: eBook Cover -->
  <div class="preview-panel" id="tab-cover" style="display: block; width: 100%;">
    <div style="background: linear-gradient(135deg, #0A1926 0%, #164E63 100%); border-radius: 12px; padding: 24px; color: #FFFFFF; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
      <div style="font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #FEF08A; margin-bottom: 8px;">TATVAM PRESENTS</div>
      <div style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 900; letter-spacing: 1px; color: #FFFFFF; line-height: 1.1;">SANSKAR 30</div>
      <div style="font-size: 13px; color: #FDE047; font-weight: 700; margin: 4px 0 16px 0;">30 Days of Good Habits, Strong Values & Happy Growing</div>
      <div style="max-width: 480px; margin: 0 auto; height: 260px; border-radius: 8px; overflow: hidden; border: 1.5px solid rgba(254, 240, 138, 0.4);">
        <svg class="hero-cover-svg" viewBox="0 0 600 420" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="coverSky" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#0F2D3C"/>
      <stop offset="45%" stop-color="#1E4E63"/>
      <stop offset="80%" stop-color="#D97706"/>
      <stop offset="100%" stop-color="#FBBF24"/>
    </linearGradient>
    <linearGradient id="groundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#2D5A27"/>
      <stop offset="100%" stop-color="#143313"/>
    </linearGradient>
    <radialGradient id="sunGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FEF08A" stop-opacity="0.9"/>
      <stop offset="40%" stop-color="#F59E0B" stop-opacity="0.4"/>
      <stop offset="100%" stop-color="#D97706" stop-opacity="0"/>
    </radialGradient>
    <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feComposite in="SourceGraphic" in2="blur" operator="over"/>
    </filter>
  </defs>

  <!-- Sky -->
  <rect width="600" height="420" fill="url(#coverSky)" rx="16"/>

  <!-- Warm Evening Sun Glow -->
  <circle cx="300" cy="240" r="140" fill="url(#sunGlow)"/>

  <!-- Distant Hill Silhouettes -->
  <path d="M0,320 Q150,260 320,290 T600,270 L600,420 L0,420 Z" fill="#20402E" opacity="0.75"/>
  <path d="M0,340 Q220,300 420,330 T600,310 L600,420 L0,420 Z" fill="url(#groundGrad)"/>

  <!-- Traditional Indian Courtyard Verandah / Banyan Canopy -->
  <path d="M-20,90 Q120,40 280,70 T620,50 L620,-10 L-20,-10 Z" fill="#143313" opacity="0.8"/>
  <path d="M20,120 Q160,70 300,100 T580,80 L580,-10 L20,-10 Z" fill="#254F22" opacity="0.6"/>

  <!-- Hanging Warm Lanterns -->
  <g filter="url(#softGlow)">
    <circle cx="160" cy="140" r="18" fill="#FEF08A" opacity="0.6"/>
    <circle cx="440" cy="130" r="18" fill="#FEF08A" opacity="0.6"/>
  </g>
  <!-- Lantern 1 -->
  <line x1="160" y1="70" x2="160" y2="130" stroke="#FDE68A" stroke-width="2"/>
  <rect x="150" y="130" width="20" height="24" rx="4" fill="#E06D28"/>
  <rect x="154" y="134" width="12" height="16" rx="2" fill="#FEF08A"/>
  <!-- Lantern 2 -->
  <line x1="440" y1="60" x2="440" y2="120" stroke="#FDE68A" stroke-width="2"/>
  <rect x="430" y="120" width="20" height="24" rx="4" fill="#E06D28"/>
  <rect x="434" y="124" width="12" height="16" rx="2" fill="#FEF08A"/>

  <!-- Grandparents in Background (Dadu & Dadi sitting on porch bench) -->
  <g transform="translate(110, 240)">
    <!-- Bench -->
    <rect x="0" y="70" width="130" height="8" rx="3" fill="#854D0E"/>
    <rect x="10" y="78" width="6" height="24" fill="#5A3207"/>
    <rect x="114" y="78" width="6" height="24" fill="#5A3207"/>
    <!-- Dadu (Grandpa) -->
    <circle cx="35" cy="30" r="14" fill="#E0A96D"/>
    <!-- White hair -->
    <path d="M22,28 C22,16 48,16 48,28 C45,20 25,20 22,28 Z" fill="#E2E8F0"/>
    <!-- Spectacles -->
    <circle cx="31" cy="29" r="4" fill="none" stroke="#64748B" stroke-width="1.5"/>
    <circle cx="39" cy="29" r="4" fill="none" stroke="#64748B" stroke-width="1.5"/>
    <line x1="35" y1="29" x2="35" y2="29" stroke="#64748B" stroke-width="1.5"/>
    <!-- Kurta -->
    <path d="M20,44 C20,44 26,42 35,42 C44,42 50,44 50,44 L48,72 L22,72 Z" fill="#F8FAFC"/>

    <!-- Dadi (Grandma) -->
    <circle cx="85" cy="32" r="13" fill="#E0A96D"/>
    <!-- Grey hair bun -->
    <circle cx="97" cy="30" r="7" fill="#CBD5E1"/>
    <!-- Saree pallu -->
    <path d="M72,44 C72,44 80,42 88,42 C96,42 100,44 100,44 L98,72 L72,72 Z" fill="#D8B4E2"/>
    <path d="M74,32 C74,24 94,22 96,34 L88,55 L74,45 Z" fill="#B388D6" opacity="0.85"/>
  </g>

  <!-- Parents in Warm Embrace Behind Children -->
  <g transform="translate(370, 235)">
    <!-- Papa -->
    <circle cx="35" cy="28" r="14" fill="#E0A96D"/>
    <!-- Dark neat hair -->
    <path d="M21,26 C21,14 49,14 49,26 C45,18 25,18 21,26 Z" fill="#1C1917"/>
    <!-- Shirt -->
    <path d="M20,42 C20,42 26,40 35,40 C44,40 50,42 50,42 L50,75 L20,75 Z" fill="#0284C7"/>
    <!-- Maa -->
    <circle cx="75" cy="30" r="13" fill="#E0A96D"/>
    <circle cx="87" cy="32" r="8" fill="#1C1917"/> <!-- Hair bun -->
    <!-- Saree -->
    <path d="M62,43 C62,43 70,41 78,41 C86,41 90,43 90,43 L88,75 L62,75 Z" fill="#F43F5E"/>
    <path d="M63,30 C63,22 84,20 87,32 L80,56 L63,45 Z" fill="#E11D48" opacity="0.9"/>
  </g>

  <!-- Central Hero Children: AARAV & ANAYA Reading Together -->
  <!-- Aarav (Left child) -->
  <g transform="translate(205, 275)">
    <!-- Body -->
    <rect x="20" y="44" width="34" height="42" rx="8" fill="#E59834"/> <!-- Ochre T-shirt -->
    <rect x="24" y="80" width="26" height="24" rx="4" fill="#1E3A8A"/> <!-- Navy shorts -->
    <!-- Head -->
    <circle cx="37" cy="24" r="18" fill="#E0A96D"/>
    <!-- Messy lively dark hair -->
    <path d="M19,22 C18,8 32,4 42,8 C50,12 55,20 54,26 C50,22 46,24 43,21 C40,24 35,21 31,23 C27,21 23,24 19,22 Z" fill="#1C1917"/>
    <!-- Happy Face -->
    <circle cx="32" cy="24" r="2.2" fill="#1C1917"/>
    <circle cx="43" cy="24" r="2.2" fill="#1C1917"/>
    <path d="M34,29 Q37.5,34 41,29" fill="none" stroke="#9A3412" stroke-width="2" stroke-linecap="round"/>
    <!-- Hands holding book -->
    <circle cx="48" cy="58" r="5" fill="#E0A96D"/>
  </g>

  <!-- The Open Golden Book between them -->
  <g transform="translate(270, 318)">
    <ellipse cx="30" cy="18" rx="34" ry="12" fill="#FEF3C7"/>
    <path d="M-2,14 Q30,6 62,14 L60,34 Q30,26 -2,34 Z" fill="#FFFBEB" stroke="#D97706" stroke-width="1.5"/>
    <line x1="30" y1="10" x2="30" y2="30" stroke="#B45309" stroke-width="1.5"/>
    <!-- Glowing Golden rays from book -->
    <path d="M10,8 L-4,-10 M30,4 L30,-14 M50,8 L64,-10" stroke="#FBBF24" stroke-width="2.5" stroke-linecap="round" opacity="0.85"/>
  </g>

  <!-- Anaya (Right child) -->
  <g transform="translate(325, 280)">
    <!-- Body -->
    <path d="M18,44 L46,44 L54,84 L10,84 Z" fill="#FACC15"/> <!-- Marigold yellow dress -->
    <path d="M14,84 L50,84 L52,87 L12,87 Z" fill="#0D9488"/> <!-- Teal hem -->
    <!-- Head -->
    <circle cx="32" cy="23" r="17" fill="#F3C99F"/>
    <!-- Hair with two pigtails and teal bows -->
    <path d="M15,22 C15,8 30,5 40,9 C48,13 49,22 48,26 C44,20 37,20 32,22 C26,20 20,22 15,22 Z" fill="#1C1917"/>
    <circle cx="12" cy="22" r="7" fill="#1C1917"/> <!-- Left pigtail -->
    <circle cx="51" cy="22" r="7" fill="#1C1917"/> <!-- Right pigtail -->
    <circle cx="14" cy="25" r="3" fill="#0D9488"/> <!-- Teal bow -->
    <circle cx="49" cy="25" r="3" fill="#0D9488"/> <!-- Teal bow -->
    <!-- Sweet Face -->
    <circle cx="28" cy="24" r="2.2" fill="#1C1917"/>
    <circle cx="38" cy="24" r="2.2" fill="#1C1917"/>
    <path d="M30,29 Q33,33 36,29" fill="none" stroke="#9A3412" stroke-width="2" stroke-linecap="round"/>
    <circle cx="25" cy="27" r="2.5" fill="#FDA4AF" opacity="0.6"/> <!-- Rosy cheek -->
    <circle cx="41" cy="27" r="2.5" fill="#FDA4AF" opacity="0.6"/>
  </g>

  <!-- Puppy Moti (Curled happily in front) -->
  <g transform="translate(210, 355)">
    <!-- Body -->
    <ellipse cx="28" cy="18" rx="16" ry="11" fill="#FFFFFF"/>
    <!-- Golden brown spot -->
    <circle cx="32" cy="16" r="7" fill="#D97706"/>
    <!-- Head -->
    <circle cx="16" cy="12" r="10" fill="#FFFFFF"/>
    <!-- Floppy ear -->
    <ellipse cx="11" cy="13" rx="4" ry="7" fill="#B45309"/>
    <!-- Eye & nose -->
    <circle cx="16" cy="11" r="1.5" fill="#1C1917"/>
    <circle cx="12" cy="14" r="1.5" fill="#1C1917"/>
    <!-- Wagging tail -->
    <path d="M42,16 Q48,10 50,14" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
  </g>

  <!-- Golden Sparkles / Stars in Sky -->
  <g fill="#FEF08A" opacity="0.8">
    <polygon points="120,45 122,50 127,52 122,54 120,59 118,54 113,52 118,50"/>
    <polygon points="480,50 482,55 487,57 482,59 480,64 478,59 473,57 478,55"/>
    <polygon points="260,35 261,39 265,40 261,41 260,45 259,41 255,40 259,39"/>
    <polygon points="360,40 361,44 365,45 361,46 360,50 359,46 355,45 359,44"/>
  </g>
  </svg>
      </div>
      <div style="margin-top: 16px; font-size: 13px; color: #E2E8F0; font-weight: 600;">
        ✨ 72 पृष्ठों की पूर्ण सचित्र ई-बुक • आरव और अनाया के संग 30 प्रेरणादायक कहानियाँ
      </div>
    </div>
  </div>

  <!-- Panel 2: Story Page Spread -->
  <div class="preview-panel" id="tab-story" style="display: none; width: 100%;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 8px 24px rgba(0,0,0,0.06); text-align: left;">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; margin-bottom: 16px;">
        <span style="font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; background: #FFEDD5; color: #C2410C; padding: 4px 12px; border-radius: 999px;">DAY 1: HONESTY • ईमानदारी</span>
        <span style="font-size: 12px; color: #64748B; font-weight: 600;">Tatvam.shop</span>
      </div>
      <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
        <div>
          <h3 style="font-size: 22px; color: #0F3240; margin-bottom: 6px;">आरव और मिट्टी का पुराना चिराग</h3>
          <p style="font-size: 13px; color: #D97706; font-style: italic; margin-bottom: 14px; font-weight: 600;">“सच्चे दिल को कभी सच बोलने से डर नहीं लगता।”</p>
          <div style="height: 180px; width: 100%; border-radius: 8px; overflow: hidden; margin-bottom: 14px; border: 1px solid #E2E8F0;">
            <svg class="story-ill-day-1" viewBox="0 0 460 220" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">
      <rect width="460" height="220" fill="#FFF7ED" rx="10"/>
      <!-- Room wall & window -->
      <rect x="0" y="0" width="460" height="150" fill="#FFEDD5"/>
      <rect x="30" y="20" width="70" height="65" fill="#BAE6FD" stroke="#94A3B8" stroke-width="4"/>
      <line x1="65" y1="20" x2="65" y2="85" stroke="#94A3B8" stroke-width="2"/>
      <line x1="30" y1="52" x2="100" y2="52" stroke="#94A3B8" stroke-width="2"/>
      <!-- Wooden floor -->
      <rect x="0" y="150" width="460" height="70" fill="#D97706" opacity="0.3"/>
      <!-- Aarav standing truthfully -->
      <g transform="translate(130, 45)">
        <circle cx="35" cy="30" r="18" fill="#E0A96D"/>
        <path d="M17,26 C17,10 38,8 51,14 C54,20 54,30 52,34 C47,28 35,28 17,26 Z" fill="#1C1917"/>
        <circle cx="30" cy="30" r="2" fill="#1C1917"/>
        <circle cx="42" cy="30" r="2" fill="#1C1917"/>
        <path d="M32,38 Q36,35 40,38" fill="none" stroke="#9A3412" stroke-width="1.8"/>
        <!-- Shirt & shorts -->
        <rect x="18" y="50" width="34" height="42" rx="6" fill="#E59834"/>
        <rect x="22" y="88" width="26" height="26" rx="4" fill="#1E3A8A"/>
        <!-- Holding red cricket ball behind back -->
        <circle cx="58" cy="70" r="8" fill="#DC2626"/>
      </g>
      <!-- Cracked terracotta lamp on stool -->
      <rect x="240" y="115" width="45" height="50" rx="3" fill="#B45309"/>
      <ellipse cx="262" cy="115" rx="25" ry="8" fill="#92400E"/>
      <path d="M250,90 Q240,115 255,115 Q270,115 274,90 Z" fill="#EA580C"/>
      <!-- Crack line -->
      <path d="M260,92 L258,102 L264,108 L261,114" fill="none" stroke="#7C2D12" stroke-width="2"/>
      <!-- Dadi listening with gentle kindness -->
      <g transform="translate(320, 35)">
        <circle cx="35" cy="32" r="18" fill="#E0A96D"/>
        <circle cx="49" cy="32" r="9" fill="#CBD5E1"/> <!-- Grey bun -->
        <circle cx="31" cy="32" r="4.5" fill="none" stroke="#64748B" stroke-width="1.5"/> <!-- glasses -->
        <circle cx="41" cy="32" r="4.5" fill="none" stroke="#64748B" stroke-width="1.5"/>
        <line x1="35.5" y1="32" x2="36.5" y2="32" stroke="#64748B" stroke-width="1.5"/>
        <path d="M33,40 Q36,44 40,40" fill="none" stroke="#9A3412" stroke-width="1.5"/>
        <!-- Saree -->
        <path d="M15,52 C15,52 28,48 40,48 C52,48 58,52 58,52 L56,120 L15,120 Z" fill="#D8B4E2"/>
        <!-- Gentle hand on Aarav's shoulder -->
        <path d="M15,65 Q-20,75 -110,95" fill="none" stroke="#E0A96D" stroke-width="7" stroke-linecap="round"/>
      </g>
      </svg>
          </div>
          <p style="font-size: 14px; line-height: 1.65; color: #374151;">
            रविवार की दोपहर थी। बरामदे में आरव प्लास्टिक के लाल बॉल से क्रिकेट खेल रहा था। माँ ने मना किया था कि घर के अंदर बैटिंग मत करो, लेकिन आरव का मन नहीं माना। जैसे ही उसने ज़ोर से बल्ला घुमाया—'धड़ाम!' बॉल सीधे जाकर लकड़ी की मेज़ पर रखे मिट्टी के खूबसूरत चिराग पर लगी...
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Panel 3: Activity Page -->
  <div class="preview-panel" id="tab-activity" style="display: none; width: 100%;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 8px 24px rgba(0,0,0,0.06); text-align: left;">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; margin-bottom: 16px;">
        <span style="font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; background: #DCFCE7; color: #15803D; padding: 4px 12px; border-radius: 999px;">HANDS-ON ACTIVITY • दिन 1</span>
        <span style="font-size: 12px; color: #64748B;">प्रिंट या डिजिटल उपयोग</span>
      </div>
      <h3 style="font-size: 20px; color: #0F3240; margin-bottom: 8px;">आईना और सच (The Mirror of Truth)</h3>
      <p style="font-size: 13.5px; color: #4B5563; margin-bottom: 16px;">
        आज की कहानी से सीखकर, बच्चा खुद अपने अनुभवों को व्यक्त करता है।
      </p>
      <div style="background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 8px; padding: 18px; margin-bottom: 14px;">
        <div style="font-weight: 700; color: #0F3240; font-size: 14px; margin-bottom: 6px;">📝 आज का विचार-बिंदु:</div>
        <p style="font-size: 13px; color: #475569; line-height: 1.5;">
          "क्या कभी आपसे कोई गलती हुई थी जिसे आपने सच-सच बता दिया? तब आपको कैसा महसूस हुआ? नीचे दिए गए बॉक्स में एक चित्र बनाएँ या दो पंक्तियाँ लिखें।"
        </p>
        <div style="height: 100px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 6px; margin-top: 10px; display: flex; align-items: center; justify-content: center; color: #94A3B8; font-size: 13px;">
          [बच्चे के लिए ड्राइंग और लेखन का सुंदर स्पेस]
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 8px; color: #C2410C; font-size: 13px; font-weight: 700;">
        <span>⭐ आज का संस्कर स्टार:</span>
        <span style="color: #F59E0B; font-size: 16px;">★★★★★</span>
      </div>
    </div>
  </div>

  <!-- Panel 4: Conversation Starter -->
  <div class="preview-panel" id="tab-conversation" style="display: none; width: 100%;">
    <div style="background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border-radius: 12px; padding: 26px; border: 1.5px solid #FCD34D; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.15); text-align: left;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <span style="font-size: 12px; font-weight: 800; background: #D97706; color: #FFFFFF; padding: 3px 10px; border-radius: 999px;">पैरेंट गाइड • रात की 5 मिनट बातचीत</span>
        <span style="font-size: 18px;">🌙</span>
      </div>
      <h3 style="font-size: 19px; color: #78350F; margin-bottom: 12px;">डिनर या बेडटाइम बातचीत के दो जादुई सवाल</h3>
      <div style="background: #FFFFFF; border-radius: 8px; padding: 14px; margin-bottom: 12px; border-left: 4px solid #D97706;">
        <div style="font-weight: 700; color: #1F2937; font-size: 14px;">1. "अगर गलती से कभी कुछ टूट जाए, तो सच बताने में सबसे ज़्यादा डर किस बात का लगता है?"</div>
        <p style="font-size: 12.5px; color: #64748B; margin-top: 4px;">माता-पिता के लिए टिप: बच्चे को भरोसा दिलाएं कि सच बोलने पर डांट नहीं, समाधान मिलेगा।</p>
      </div>
      <div style="background: #FFFFFF; border-radius: 8px; padding: 14px; border-left: 4px solid #164E63;">
        <div style="font-weight: 700; color: #1F2937; font-size: 14px;">2. "क्या आपको याद है जब आपने सच कहा था और दादी/माँ ने आपको गले लगाया था?"</div>
        <p style="font-size: 12.5px; color: #64748B; margin-top: 4px;">यह सवाल बच्चे के मन में सच और सुरक्षा का गहरा भावनात्मक संबंध बनाता है।</p>
      </div>
    </div>
  </div>

  <!-- Panel 5: Habit Tracker -->
  <div class="preview-panel" id="tab-tracker" style="display: none; width: 100%;">
    <div style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; text-align: left; box-shadow: 0 8px 24px rgba(0,0,0,0.06);">
      <div style="text-align: center; margin-bottom: 18px;">
        <h3 style="font-size: 21px; color: #0F3240;">🌟 30-Day Sanskar Star Tracker</h3>
        <p style="font-size: 13px; color: #64748B;">हर दिन एक कहानी पढ़ें, सीख अपनाएँ और अपना गोल्डन स्टार कलर करें!</p>
      </div>
      <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; text-align: center;">
        
          <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #B45309;">DAY 1</div>
            <div style="font-size: 16px; margin-top: 2px;">⭐</div>
          </div>
        
          <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #B45309;">DAY 2</div>
            <div style="font-size: 16px; margin-top: 2px;">⭐</div>
          </div>
        
          <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #B45309;">DAY 3</div>
            <div style="font-size: 16px; margin-top: 2px;">⭐</div>
          </div>
        
          <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #B45309;">DAY 4</div>
            <div style="font-size: 16px; margin-top: 2px;">⭐</div>
          </div>
        
          <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #B45309;">DAY 5</div>
            <div style="font-size: 16px; margin-top: 2px;">⭐</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 6</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 7</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 8</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 9</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 10</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 11</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 12</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 13</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 14</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 15</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 16</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 17</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 18</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 19</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 20</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 21</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 22</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 23</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 24</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 25</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 26</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 27</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 28</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 29</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 4px;">
            <div style="font-size: 10px; font-weight: 800; color: #94A3B8;">DAY 30</div>
            <div style="font-size: 16px; margin-top: 2px;">☆</div>
          </div>
        
      </div>
      <div style="margin-top: 16px; text-align: center; font-size: 12px; color: #059669; font-weight: 700;">
        ✓ बच्चों में लगातार 30 दिन तक अच्छी आदतें बनाए रखने का सिद्ध मनोवैज्ञानिक तरीका
      </div>
    </div>
  </div>

  <!-- Panel 6: Certificate -->
  <div class="preview-panel" id="tab-certificate" style="display: none; width: 100%;">
    <div style="background: #FFFDF7; border: 3px solid #D97706; border-radius: 12px; padding: 30px 20px; text-align: center; position: relative; box-shadow: 0 10px 25px rgba(217, 119, 6, 0.15);">
      <div style="font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 2px; color: #D97706;">TATVAM SANSKAR ACADEMY</div>
      <h3 style="font-size: 26px; color: #78350F; margin: 8px 0;">संस्कार रत्न सम्मान पत्र</h3>
      <p style="font-size: 13px; color: #64748B; max-width: 440px; margin: 0 auto 16px auto;">
        यह गर्व के साथ प्रमाणित किया जाता है कि हमारे प्यारे नन्हें साथी ने सफलता पूर्वक 30 दिनों का संस्कार सफर पूरा किया है।
      </p>
      <div style="border-bottom: 2px dashed #D97706; width: 240px; margin: 0 auto 16px auto; padding-bottom: 4px; font-weight: 700; color: #0F3240; font-size: 18px;">
        [ बच्चे का नाम यहाँ लिखें ]
      </div>
      <div style="display: flex; justify-content: center; gap: 40px; font-size: 11px; color: #854D0E; font-weight: 700; margin-top: 12px;">
        <div>✍️ माता-पिता के हस्ताक्षर</div>
        <div>🏆 Tatvam.shop सील</div>
      </div>
    </div>
  </div>

        </div>
        <div class="preview-caption" id="previewCaption">मुख्य ई-बुक कवर (72 प्रीमियम पेजेस)</div>
        <div class="preview-subcaption" id="previewSubcaption">हार्डकवर जैसी खूबसूरत 3D फिनिश, जिसे बच्चे देखते ही पढ़ना चाहें।</div>
      </div>
    </div>
  </section>

  <!-- Bonus Section -->
  <section class="section">
    <div class="container">
      <div class="bonus-banner">
        <div>
          <span class="bonus-pill">FREE SPECIAL BONUS • सीमित समय के लिए</span>
          <h2 class="bonus-title">50 स्क्रीन-फ्री एक्टिविटी गाइड (मूल्य: ₹499)</h2>
          <p class="bonus-desc">
            आज संस्कार 30 बंडल के साथ आपको मिलती है हमारी विशेष "50 Screen-Free Activity Kit" बिल्कुल मुफ्त! जब भी बच्चा कहे "मैं बोर हो रहा हूँ", बस इस गाइड में से एक खेल चुनें।
          </p>
        </div>

        <div class="bonus-categories">
          <div class="bonus-cat-item">🏡 15 इनडोर खोज और पहेलियां</div>
          <div class="bonus-cat-item">🌿 10 प्रकृति और बालकनी खेल</div>
          <div class="bonus-cat-item">🧪 10 किचन साइंस प्रयोग</div>
          <div class="bonus-cat-item">🎨 15 कला और आभार क्राफ्ट</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Who Is It For / Not For -->
  <section class="section section-alt">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">स्पष्ट और ईमानदार</span>
        <h2 class="section-title">क्या यह बंडल आपके लिए सही है?</h2>
        <p class="section-subtitle">
          हम चाहते हैं कि केवल वही माता-पिता इसे लें जिन्हें इससे वास्तविक लाभ मिले:
        </p>
      </div>

      <div class="audience-grid">
        <!-- For You -->
        <div class="audience-card for-you">
          <h3 class="audience-card-title" style="color: #15803D;">✓ यह आपके लिए है यदि:</h3>
          <ul class="audience-list">
            <li><span>✓</span> आपके घर में 5 से 12 वर्ष का बच्चा है।</li>
            <li><span>✓</span> आप बच्चे का स्क्रीन टाइम बिना झगड़े और ज़िद के कम करना चाहते हैं।</li>
            <li><span>✓</span> आप चाहते हैं कि आपका बच्चा भारतीय संस्कारों, बड़ों के आदर और दयालुता को समझे।</li>
            <li><span>✓</span> आप रोज़ाना 10-15 मिनट का समर्पित समय बच्चे को देने के लिए तैयार हैं।</li>
            <li><span>✓</span> आप डांटने और चिल्लाने के बजाय प्यार और समझदारी की पेरेंटिंग में विश्वास रखते हैं।</li>
          </ul>
        </div>

        <!-- Not For You -->
        <div class="audience-card not-for-you">
          <h3 class="audience-card-title" style="color: #B91C1C;">✕ यह आपके लिए नहीं है यदि:</h3>
          <ul class="audience-list">
            <li><span>✕</span> आप कोई ऐसा जादूई नुस्खा ढूंढ रहे हैं जिसमें माता-पिता को 5 मिनट भी न देना पड़े।</li>
            <li><span>✕</span> आप केवल मोबाइल वीडियो गेम्स या स्क्रीन-बेस्ड ऐप्स पसंद करते हैं।</li>
            <li><span>✕</span> आपका बच्चा 14 वर्ष से अधिक आयु का है।</li>
            <li><span>✕</span> आप सिर्फ किताबें खरीदकर अलमारी या ड्राइव में छोड़ देने में विश्वास रखते हैं।</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing Card Section -->
  <section class="section" id="pricing">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">सीमित विशेष ऑफर</span>
        <h2 class="section-title">आज ही शुरू करें संस्कार 30 का सफर</h2>
        <p class="section-subtitle">
          कोई छिपा हुआ शुल्क नहीं। कोई मासिक सब्सक्रिप्शन नहीं। एक बार लें, हमेशा इस्तेमाल करें।
        </p>
      </div>

      <div class="pricing-wrapper">
        <div class="pricing-badge">🔥 COMPLETE DIGITAL BUNDLE</div>
        <h3 class="pricing-product-name">संस्कार 30 संपूर्ण डिजिटल बंडल</h3>
        <p class="pricing-product-desc">तुरंत डाउनलोड करें • सभी डिवाइस पर काम करता है • प्रिंट-रेडी</p>

        <div class="pricing-big-row">
          <span class="pricing-symbol">₹</span>
          <span class="pricing-num"><?php echo $display_price; ?></span>
        </div>
        <p style="font-size: 13px; color: var(--emerald); font-weight: 700;">
          (मात्र ₹<?php echo $display_price; ?> में संपूर्ण परिवार के लिए जीवनभर की सीख)
        </p>

        <ul class="pricing-items-list">
          <li><span>✓</span> <strong>SANSKAR 30 मुख्य ई-बुक</strong> (72 रंगीन पृष्ठ, 30 सचित्र कहानियाँ)</li>
          <li><span>✓</span> <strong>पैरेंट & एक्टिविटी टूलकिट</strong> (43 पृष्ठ, 30 एक्टिविटीज & 30 कार्ड्स)</li>
          <li><span>✓</span> <strong>50 स्क्रीन-फ्री एक्टिविटी गाइड</strong> (बोरियत दूर करने वाले खेल)</li>
          <li><span>✓</span> <strong>30-डे संस्कर स्टार ट्रैकर</strong> (प्रिंट करने योग्य वॉल चार्ट)</li>
          <li><span>✓</span> <strong>संस्कार रत्न पूर्णता प्रमाण-पत्र</strong> (सम्मान-पत्र)</li>
          <li><span>✓</span> <strong>किड्स रिफ्लेक्शन शीट्स</strong> (डायरी & आभार पेज)</li>
          <li><span>✓</span> <strong>आजीवन एक्सेस</strong> (मोबाइल, टैबलेट, लैपटॉप & प्रिंटर पर सुरक्षित)</li>
          <li><span>✓</span> <strong>तुरंत डाउनलोड लिंक</strong> (ईमेल और स्क्रीन पर)</li>
        </ul>

        <button type="button" class="btn btn-primary btn-large btn-pulse checkout-trigger" data-product-slug="<?php echo htmlspecialchars($product_slug); ?>" style="width: 100%;">पूरा बंडल तुरंत डाउनलोड करें — सिर्फ ₹<?php echo $display_price; ?> ➔</button>

        <div style="margin-top: 16px; font-size: 12.5px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
          <span>🔒 256-Bit SSL सुरक्षित भुगतान (UPI, GPay, PhonePe, Paytm, नेटबैंकिंग, कार्ड)</span>
          <span>⚡ भुगतान के तुरंत बाद डाउनलोड लिंक आपकी स्क्रीन और ईमेल पर उपलब्ध हो जाएगा</span>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Accordion Section -->
  <section class="section section-alt" id="faq">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">अक्सर पूछे जाने वाले सवाल</span>
        <h2 class="section-title">माता-पिता के सामान्य सवाल</h2>
        <p class="section-subtitle">
          यदि आपके मन में कोई शंका है, तो यहाँ आपके सभी उत्तर मौजूद हैं:
        </p>
      </div>

      <div class="faq-list">
        
        <!-- FAQ 1 -->
        <div class="faq-item active">
          <div class="faq-question">
            <span>1. क्या यह एक फिजिकल किताब है या डिजिटल पीडीएफ (PDF)?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            यह एक <strong>प्रीमियम हाई-रिजॉल्यूशन डिजिटल प्रोडक्ट (PDF बंडल)</strong> है। पेमेंट करते ही आपको तुरंत दोनों बुक्स (72-पेज मुख्य ई-बुक और 43-पेज पैरेंट टूलकिट) के डायरेक्ट डाउनलोड लिंक मिल जाते हैं। आप इसे अपने मोबाइल, आईपैड, टैबलेट या लैपटॉप पर पढ़ सकते हैं, या फिर किसी भी प्रिंटर से आसानी से प्रिंट कर सकते हैं।
          </div>
        </div>

        <!-- FAQ 2 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>2. यह किस आयु वर्ग (Age Group) के बच्चों के लिए उपयुक्त है?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            यह विशेष रूप से <strong>5 से 12 वर्ष के बच्चों</strong> के लिए डिज़ाइन किया गया है। 5-8 साल के बच्चों के लिए माता-पिता कहानियाँ पढ़कर सुना सकते हैं और एक्टिविटीज साथ में कर सकते हैं। 9-12 साल के बच्चे इसे खुद आसानी से पढ़ सकते हैं और माता-पिता के साथ विचार साझा कर सकते हैं।
          </div>
        </div>

        <!-- FAQ 3 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>3. कहानियों की भाषा कैसी है? क्या बच्चे आसानी से समझ पाएंगे?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            भाषा बहुत ही सरल, स्वाभाविक और रोज़मर्रा की बोलचाल वाली हिंदी (Hindi-First) है, जिसमें लगभग 20% सामान्य अंग्रेजी शब्दों का स्वाभाविक समावेश है। इसमें कोई क्लिष्ट या कठिन संस्कृतनिष्ठ शब्द नहीं हैं, ताकि बच्चे बिना किसी परेशानी के कहानी का आनंद ले सकें।
          </div>
        </div>

        <!-- FAQ 4 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>4. क्या मुझे एक्टिविटीज के लिए प्रिंटआउट निकालना ज़रूरी है?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            बिल्कुल ज़रूरी नहीं है! आप एक्टिविटीज को सीधे मोबाइल या टैबलेट की स्क्रीन पर देखकर किसी भी साधारण नोटबुक या ड्राइंग बुक में करवा सकते हैं। यदि आपके पास प्रिंटर की सुविधा है, तो आप A4 साइज में प्रिंट भी निकाल सकते हैं।
          </div>
        </div>

        <!-- FAQ 5 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>5. रोज़ाना कितना समय लगेगा? मेरे पास बहुत कम समय रहता है।</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            संस्कार 30 को व्यस्त माता-पिता को ध्यान में रखकर ही बनाया गया है। रोज़ाना सिर्फ <strong>10 से 15 मिनट</strong> का समय लगता है—5 मिनट कहानी पढ़ने में, 3 मिनट बातचीत में और 5 मिनट छोटी एक्टिविटी या स्टार लगाने में।
          </div>
        </div>

        <!-- FAQ 6 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>6. पेमेंट करने के बाद मुझे फाइलें कैसे मिलेंगी?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            भुगतान पूरा होते ही स्क्रीन पर तुरंत "Download Bundle" का बटन आ जाएगा। साथ ही, आपके दिए गए ईमेल पते पर भी डाउनलोड लिंक तुरंत भेज दिया जाता है। आप इसे कभी भी दोबारा डाउनलोड कर सकते हैं।
          </div>
        </div>

        <!-- FAQ 7 -->
        <div class="faq-item">
          <div class="faq-question">
            <span>7. क्या यह पेमेंट पूरी तरह सुरक्षित है?</span>
            <span class="faq-icon">▼</span>
          </div>
          <div class="faq-answer">
            हाँ, 100% सुरक्षित। पेमेंट भारतीय रिज़र्व बैंक (RBI) द्वारा अधिकृत पेमेंट गेटवे के माध्यम से 256-बिट एन्क्रिप्शन के साथ प्रोसेस होता है। आप Google Pay, PhonePe, Paytm, UPI, क्रेडिट कार्ड, डेबिट कार्ड या नेट बैंकिंग से भुगतान कर सकते हैं।
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Final Emotional CTA -->
  <section class="final-cta-section">
    <div class="container final-cta-box">
      <div style="font-size: 40px;">🌱✨</div>
      <h2 class="final-title">
        बचपन एक बार आता है। आज ही दीजिए अपने बच्चे को संस्कारों की सबसे अनमोल धरोहर।
      </h2>
      <p class="final-subtitle">
        खिलौने और गैजेट्स कुछ ही दिनों में पुराने हो जाते हैं, लेकिन बचपन में सीखे गए अच्छे संस्कार जीवनभर साथ निभाते हैं।
      </p>

      <button type="button" class="btn btn-primary btn-large btn-pulse checkout-trigger" data-product-slug="<?php echo htmlspecialchars($product_slug); ?>" style="font-size: 20px; padding: 18px 42px;">अभी शुरू करें — सिर्फ ₹<?php echo $display_price; ?> ➔</button>

      <div class="brand-seal-footer">TATVAM • SANSKAR 30</div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container footer-content">
      <div>
        <div style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 900; color: #FFFFFF; letter-spacing: 1.5px;">
          TATVAM
        </div>
        <div style="font-size: 12px; margin-top: 4px; color: #94A3B8;">
          Tatvam.shop • All Rights Reserved © 2026
        </div>
        <div style="font-size: 11px; margin-top: 2px; color: #64748B;">
          Brand Watermark: Tatvam.shop | Empowering Indian Families
        </div>
      </div>

      <div class="footer-links">
        <a href="#problem">चुनौतियां</a>
        <a href="#solution">समाधान</a>
        <a href="#bundle">बंडल</a>
        <a href="#faq">सवाल-जवाब</a>
        <a href="mailto:support@tatvam.shop">सहयोग: support@tatvam.shop</a>
      </div>
    </div>
  </footer>

  
  <!-- One Step Checkout Modal -->
  <div class="modal-overlay" id="checkoutModal">
    <div class="modal-card">
      <button class="modal-close" id="modalCloseBtn" aria-label="Close">&times;</button>
      <div class="modal-title">विवरण भरें (Enter Details)</div>
      <div class="modal-subtitle">ई-बुक तुरंत डिलीवर करने के लिए अपना नाम और व्हाट्सएप नंबर दर्ज करें।</div>
      <form id="checkout-form">
        <input type="hidden" name="product_slug" id="target-product-slug" value="<?php echo htmlspecialchars($product_slug); ?>">
        <div class="form-group">
          <label for="cust_name">आपका नाम (Your Name)</label>
          <input type="text" id="cust_name" name="name" class="form-input" placeholder="उदा. राहुल शर्मा" required>
        </div>
        <div class="form-group">
          <label for="cust_email">ईमेल पता (Email Address)</label>
          <input type="email" id="cust_email" name="email" class="form-input" placeholder="rahul@gmail.com" required>
        </div>
        <div class="form-group">
          <label for="cust_phone">व्हाट्सएप फोन नंबर (WhatsApp Phone)</label>
          <input type="tel" id="cust_phone" name="phone" class="form-input" placeholder="9876543210" pattern="[0-9]{10}" title="10 अंकों का मोबाइल नंबर" required>
        </div>
        <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-top: 10px;" id="checkoutSubmitBtn">
          सुरक्षित भुगतान करें (Pay ₹<?php echo $display_price; ?>) ➔
        </button>
      </form>
    </div>
  </div>

  <!-- Mobile Sticky Bottom CTA Bar -->
  <div class="mobile-sticky-bar">
    <div class="sticky-price">
      <span class="sticky-price-label">ऑफर प्राइस</span>
      <span class="sticky-price-num">₹<?php echo $display_price; ?></span>
    </div>
    <button type="button" class="btn btn-primary sticky-btn checkout-trigger" data-product-slug="<?php echo htmlspecialchars($product_slug); ?>">अभी डाउनलोड करें ➔</button>
  </div>

  <!-- JavaScript Interactions -->
  <script src="script-sanskar.js"></script>
</body>
</html>
