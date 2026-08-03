document.addEventListener('DOMContentLoaded', () => {

    // ==========================================================================
    // 0. META PIXEL UNIVERSAL TRACKER (Client-Side)
    // ==========================================================================
    const metaPixelId = window.META_PIXEL_ID || '123456789012345';
    if (metaPixelId && metaPixelId !== '123456789012345' && typeof fbq !== 'function') {
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', metaPixelId);
        fbq('track', 'PageView');
        fbq('track', 'ViewContent', {
            content_name: document.title || 'TATVAM E-Book',
            currency: 'INR',
            value: 199.00
        });
    } else if (typeof fbq === 'function') {
        fbq('track', 'ViewContent', {
            content_name: document.title || 'TATVAM E-Book',
            currency: 'INR',
            value: 199.00
        });
    }

    // Dynamic Product Details & Price Sync from Database
    const currentSlug = document.body.getAttribute('data-page-slug') || 'positive-thinking';
    fetch(`get-product.php?slug=${encodeURIComponent(currentSlug)}`)
        .then(res => res.json())
        .then(prod => {
            if (prod && prod.success && prod.price) {
                const livePrice = Math.round(prod.price);
                const origPrice = Math.round(prod.original_price || (livePrice * 5));

                // Update all price tags on the landing page dynamically
                document.querySelectorAll('.dynamic-price-val').forEach(el => {
                    el.textContent = '₹' + livePrice;
                });
                document.querySelectorAll('.dynamic-orig-price-val').forEach(el => {
                    el.textContent = '₹' + origPrice;
                });
                document.querySelectorAll('.dynamic-buy-btn-text').forEach(el => {
                    el.innerHTML = `🛒 Buy Now – ₹${livePrice}`;
                });
            }
        })
        .catch(err => console.log('Dynamic price sync fallback to default HTML values.'));

    // ==========================================================================
    // 1. DUAL-MODE STICKY BUY WIDGET VISIBILITY CONTROLLER
    // ==========================================================================
    const desktopFloatingCard = document.getElementById('desktop-floating-card');
    const mobileStickyBuyBar = document.querySelector('.sticky-buy-bar');
    
    const handleScrollWidgets = () => {
        const scrollThreshold = 400;
        if (window.scrollY > scrollThreshold) {
            if (window.innerWidth > 768) {
                if (desktopFloatingCard) desktopFloatingCard.classList.add('show');
                if (mobileStickyBuyBar) mobileStickyBuyBar.classList.remove('show');
            } else {
                if (mobileStickyBuyBar) mobileStickyBuyBar.classList.add('show');
                if (desktopFloatingCard) desktopFloatingCard.classList.remove('show');
            }
        } else {
            if (desktopFloatingCard) desktopFloatingCard.classList.remove('show');
            if (mobileStickyBuyBar) mobileStickyBuyBar.classList.remove('show');
        }
    };
    
    window.addEventListener('scroll', handleScrollWidgets, { passive: true });
    window.addEventListener('resize', handleScrollWidgets, { passive: true });

    // ==========================================================================
    // 2. REAL-TIME COUNTDOWN TIMER (Syncs all countdown widgets)
    // ==========================================================================
    const desktopTimer = document.getElementById('desktop-floating-timer');
    const ctaMinutes = document.getElementById('cta-minutes');
    const ctaSeconds = document.getElementById('cta-seconds');
    const indexTimer = document.getElementById('countdown-timer');

    let timeRemaining = 15 * 60; // 15 Minutes

    const updateTimerDisplay = () => {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        const minStr = (minutes < 10 ? '0' : '') + minutes;
        const secStr = (seconds < 10 ? '0' : '') + seconds;
        const timeStr = `${minStr}:${secStr}`;

        // Update Desktop Corner Timer
        if (desktopTimer) desktopTimer.innerText = timeStr;
        
        // Update Indexurgency Timer
        if (indexTimer) indexTimer.innerText = timeStr;

        // Update Final CTA Timer
        if (ctaMinutes) ctaMinutes.innerText = minStr;
        if (ctaSeconds) ctaSeconds.innerText = secStr;

        if (timeRemaining > 0) {
            timeRemaining--;
        } else {
            timeRemaining = 15 * 60; // Auto reset
        }
    };

    setInterval(updateTimerDisplay, 1000);
    updateTimerDisplay();

    // ==========================================================================
    // 3. LIVE VISITOR COUNTER WIDGET
    // ==========================================================================
    const visitorText = document.getElementById('visitor-count-text');
    if (visitorText) {
        let currentCount = 138;
        const updateVisitorCounter = () => {
            const fluctuation = Math.floor(Math.random() * 11) - 5; // -5 to +5
            currentCount += fluctuation;
            if (currentCount < 80) currentCount = 80;
            if (currentCount > 250) currentCount = 250;
            
            visitorText.innerText = `${currentCount} people are viewing this offer right now`;
        };
        setInterval(updateVisitorCounter, 4000);
    }

    // ==========================================================================
    // 4. INTERACTIVE PDF BOOK PREVIEWER (Flip Engine)
    // ==========================================================================
    const previewPages = [
        {
            title: "Chapter 1: Overthinking Ke Triggers Kaise Pehchanein?",
            body: "Overthinking tab shuru hoti hai jab aapka subconscious mind continuous alert state me chala jaata hai. Is chapter me hum 3 triggers (Mind loops, Past regression, and Future projections) ko trace karne ke detailed exercises cover karenge taaki aap negative thoughts ko control kar sakein.",
            bookName: "Book: मन की शांति (The Power of Calm)",
            pageInfo: "Page 12 of 145"
        },
        {
            title: "Chapter 2: The 5-Minute Mind Silence Hack",
            body: "Apne mind ko silence karne ke liye sensory organs ka configuration control kiya jata hai. Learn karein '5-4-3-2-1 Grounding' and breathing metrics. Is hack se aap 5 minute me complete mental baseline restore kar sakte hain.",
            bookName: "Book: मन की शांति (The Power of Calm)",
            pageInfo: "Page 25 of 145"
        },
        {
            title: "Chapter 3: Routine Checkpoints & Checklists",
            body: "Daily micro checklist templates jo daily tasks and habits automatic banati hain. Waking up routines, target mapping logs, and laziness tracking parameters designed to maintain high daily focus.",
            bookName: "Book: अनुशासन क्रांति (Ultimate Discipline)",
            pageInfo: "Page 38 of 160"
        },
        {
            title: "Chapter 4: Financial Growth & Money Allocation",
            body: "Paison ko lekar poor self-limiting beliefs aur money blocks aapki growth block kar rahe hain? Abundance habits learn aur deploy karna seekhein. Set-up smart money allocation and expense tracking templates.",
            bookName: "Book: समृद्धि सूत्र (Wealth Principles)",
            pageInfo: "Page 42 of 150"
        }
    ];

    let currentPageIndex = 0;
    const pdfViewerFrame = document.getElementById('pdf-viewer-frame');
    const pdfChapterTitle = document.getElementById('pdf-chapter-title');
    const pdfChapterBody = document.getElementById('pdf-chapter-body');
    const pdfPageNumber = document.getElementById('pdf-page-number');
    const previewPageIndicator = document.getElementById('preview-page-indicator');
    
    const btnNextPage = document.getElementById('btn-next-page');
    const btnPrevPage = document.getElementById('btn-prev-page');

    const updatePreviewPage = (direction) => {
        if (!pdfViewerFrame) return;

        // Apply page-flip CSS class
        const animationClass = direction === 'next' ? 'page-flip-left' : 'page-flip-right';
        pdfViewerFrame.classList.add(animationClass);

        // Change content mid-way through animation (250ms)
        setTimeout(() => {
            const page = previewPages[currentPageIndex];
            if (pdfChapterTitle) pdfChapterTitle.innerText = page.title;
            if (pdfChapterBody) pdfChapterBody.innerText = page.body;
            if (pdfPageNumber) {
                pdfPageNumber.innerHTML = `<span>${page.bookName}</span><span>${page.pageInfo}</span>`;
            }
            if (previewPageIndicator) {
                previewPageIndicator.innerText = `Preview ${currentPageIndex + 1} / ${previewPages.length}`;
            }
        }, 250);

        // Remove class once animation finishes
        pdfViewerFrame.addEventListener('animationend', () => {
            pdfViewerFrame.classList.remove(animationClass);
        }, { once: true });
    };

    if (btnNextPage) {
        btnNextPage.addEventListener('click', () => {
            if (currentPageIndex < previewPages.length - 1) {
                currentPageIndex++;
                updatePreviewPage('next');
            } else {
                currentPageIndex = 0; // wrap around
                updatePreviewPage('next');
            }
        });
    }

    if (btnPrevPage) {
        btnPrevPage.addEventListener('click', () => {
            if (currentPageIndex > 0) {
                currentPageIndex--;
                updatePreviewPage('prev');
            } else {
                currentPageIndex = previewPages.length - 1; // wrap around
                updatePreviewPage('prev');
            }
        });
    }

    // ==========================================================================
    // 5. VIEWPORT SCROLL REVEALS (Constant 60 FPS)
    // ==========================================================================
    const revealElements = document.querySelectorAll('.glass-panel, .glass-card, .split-col, .timeline-step');
    
    if (revealElements.length > 0) {
        revealElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translate3d(0, 30px, 0)';
            el.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            el.style.willChange = 'transform, opacity';
        });

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translate3d(0, 0, 0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: 0.1
        });

        revealElements.forEach(el => {
            revealObserver.observe(el);
        });
    }

    // ==========================================================================
    // 6. LIVE PURCHASE SOCIAL PROOF ALERTS (Social Proof)
    // ==========================================================================
    const toast = document.querySelector('.toast');
    const buyerNameSpan = document.getElementById('toast-buyer-name');
    const buyerLocationSpan = document.getElementById('toast-buyer-location');
    const toastProductSpan = document.getElementById('toast-product-name');
    const toastProductImg = document.getElementById('toast-product-img');
    const toastTimeSpan = document.getElementById('toast-time-val');

    if (toast) {
        const names = ['Amit', 'Rahul', 'Sneha', 'Vikram', 'Aditya', 'Priya', 'Pooja', 'Nikhil', 'Siddharth', 'Karan', 'Deepak', 'Anjali', 'Neha', 'Rohan', 'Manish'];
        const locations = ['Delhi', 'Mumbai', 'Bangalore', 'Pune', 'Jaipur', 'Lucknow', 'Ahmedabad', 'Indore', 'Patna', 'Ranchi', 'Kolkata', 'Chennai', 'Bhopal', 'Surat'];
        const product = 'Positive Thinking (नकारात्मक सोच से बाहर निकलें)';
        const productCover = 'assets/calm-cover.jpg?v=1.1';
        const times = ['just now', '1m ago', '2m ago', '3m ago', '4m ago'];

        // Web Audio API Synthesizer for Notification Pop Chime Sound
        let audioCtx = null;
        const playNotificationSound = () => {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                if (!audioCtx) audioCtx = new AudioContext();
                
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                const now = audioCtx.currentTime;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sine';
                // Two-tone subtle luxury pop chime (E5 -> B5)
                osc.frequency.setValueAtTime(659.25, now); // E5
                osc.frequency.exponentialRampToValueAtTime(987.77, now + 0.08); // B5

                gain.gain.setValueAtTime(0.12, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start(now);
                osc.stop(now + 0.35);
            } catch (e) {
                // Ignore audio restriction errors gracefully
            }
        };

        // Initialize AudioContext on first user interaction to satisfy browser autoplay policy
        const initAudioOnUserInteraction = () => {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) audioCtx = new AudioContext();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            document.removeEventListener('click', initAudioOnUserInteraction);
            document.removeEventListener('touchstart', initAudioOnUserInteraction);
            document.removeEventListener('keydown', initAudioOnUserInteraction);
        };

        document.addEventListener('click', initAudioOnUserInteraction, { once: true });
        document.addEventListener('touchstart', initAudioOnUserInteraction, { once: true });
        document.addEventListener('keydown', initAudioOnUserInteraction, { once: true });

        const triggerActivityAlert = () => {
            if (document.hidden) return;

            const name = names[Math.floor(Math.random() * names.length)];
            const location = locations[Math.floor(Math.random() * locations.length)];
            const timeVal = times[Math.floor(Math.random() * times.length)];

            if (buyerNameSpan) buyerNameSpan.innerText = name;
            if (buyerLocationSpan) buyerLocationSpan.innerText = location;
            if (toastProductSpan) toastProductSpan.innerText = product;
            if (toastTimeSpan) toastTimeSpan.innerText = timeVal;
            if (toastProductImg) {
                toastProductImg.src = productCover;
                toastProductImg.alt = product;
            }

            toast.classList.add('show');
            playNotificationSound();

            setTimeout(() => {
                toast.classList.remove('show');
            }, 4500);
        };

        setInterval(triggerActivityAlert, 9000);
        setTimeout(triggerActivityAlert, 1500);
    }

    // ==========================================================================
    // 7. FAQ ACCORDION DRAWERS
    // ==========================================================================
    const faqCards = document.querySelectorAll('.faq-card');
    faqCards.forEach(card => {
        const trigger = card.querySelector('.faq-trigger');
        if (trigger) {
            trigger.addEventListener('click', () => {
                const isActive = card.classList.contains('active');
                faqCards.forEach(otherCard => otherCard.classList.remove('active'));
                if (!isActive) {
                    card.classList.add('active');
                }
            });
        }
    });

    // ==========================================================================
    // 8. CHECKOUT FORM AND PAYMENT SIMULATION
    // ==========================================================================
    const modalOverlay = document.querySelector('.modal-overlay');
    const checkoutTriggers = document.querySelectorAll('.checkout-trigger');
    const modalClose = document.querySelector('.modal-close');
    const checkoutForm = document.getElementById('checkout-form');
    const targetProductInput = document.getElementById('target-product-slug');

    const openCheckoutModal = (productSlug) => {
        if (targetProductInput) targetProductInput.value = productSlug;
        if (modalOverlay) {
            modalOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    const closeCheckoutModal = () => {
        if (modalOverlay) {
            modalOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    checkoutTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productSlug = btn.getAttribute('data-product-slug') || 'mega-bundle';
            openCheckoutModal(productSlug);
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', closeCheckoutModal);
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeCheckoutModal();
            }
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = checkoutForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `<i data-lucide="loader" class="animate-spin" style="width: 18px; height: 18px; display: inline-block;"></i> Processing Checkout...`;
            submitBtn.disabled = true;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const formData = new FormData(checkoutForm);

            try {
                const res = await fetch('create-order.php', {
                    method: 'POST',
                    body: formData,
                });
                
                const responseText = await res.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (jsonErr) {
                    console.error('Non-JSON response from server:', responseText);
                    alert('Server Response Error: ' + responseText.substring(0, 200));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                if (!data.success) {
                    alert('Checkout Error: ' + (data.message || 'Payment session creation failed'));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Determine Cashfree mode & initialize SDK instance
                const cfMode = (data.environment === 'PRODUCTION') ? 'production' : 'sandbox';
                let cashfreeObj = null;

                if (typeof Cashfree !== 'undefined') {
                    cashfreeObj = Cashfree({ mode: cfMode });
                } else if (typeof window.Cashfree !== 'undefined') {
                    cashfreeObj = window.Cashfree({ mode: cfMode });
                }

                if (!cashfreeObj) {
                    alert('Cashfree Payment SDK failed to load. Please refresh the page or check ad-blockers.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Trigger Meta Pixel InitiateCheckout Event
                if (typeof fbq === 'function') {
                    fbq('track', 'InitiateCheckout', {
                        content_name: data.product_name || 'E-Book',
                        currency: 'INR',
                        value: 199.00
                    });
                }

                closeCheckoutModal();

                // Launch Cashfree Drop checkout
                cashfreeObj.checkout({
                    paymentSessionId: data.payment_session_id,
                    redirectTarget: '_self',
                });

            } catch (err) {
                console.error('Checkout Submit Exception:', err);
                alert('Checkout Error: ' + (err.message || 'Network issue. Please try again.'));
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // ==========================================================================
    // 8. MOBILE NAVIGATION DRAWER & ACCORDIONS
    // ==========================================================================
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            
            // Toggle icon between '☰' and '✖'
            if (navLinks.classList.contains('active')) {
                mobileMenuBtn.innerHTML = '&#10006;'; // ✖ (Cross)
            } else {
                mobileMenuBtn.innerHTML = '&#9776;'; // ☰ (Hamburger)
            }
        });

        // Close menu drawer if clicking outside
        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active') && !navLinks.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                navLinks.classList.remove('active');
                mobileMenuBtn.innerHTML = '&#9776;'; // ☰ (Hamburger)
            }
        });
    }

    // Handle dropdown toggling on mobile (accordion click)
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        if (trigger) {
            trigger.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    dropdowns.forEach(d => {
                        if (d !== dropdown) {
                            d.classList.remove('active');
                        }
                    });
                    
                    dropdown.classList.toggle('active');
                }
            });
        }
    });

});
