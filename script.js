/**
 * TATTVAM — AI DIGITAL PRODUCT INCOME SYSTEM
 * Interactive Client Logic, Modals, Checkout & Accessibility Controller
 * Brand: TATTVAM | Website: https://tatvam.shop
 */

/* ===================================================================
   MASTER INITIALIZATION CONTROLLER
   Ensures all event listeners attach regardless of script load timing
   =================================================================== */
function initAll() {
  try { initNavbar(); } catch (err) { console.error('Navbar error:', err); }
  try { initWorkflowStepper(); } catch (err) { console.error('Workflow error:', err); }
  try { initModuleFilters(); } catch (err) { console.error('Filters error:', err); }
  try { initPreviewModal(); } catch (err) { console.error('Preview error:', err); }
  try { initFaqAccordion(); } catch (err) { console.error('FAQ error:', err); }
  try { initPolicyModals(); } catch (err) { console.error('Policy error:', err); }
  try { initCheckoutTriggers(); } catch (err) { console.error('Checkout error:', err); }
  try { initSmoothScroll(); } catch (err) { console.error('Scroll error:', err); }
  try { initScrollReveal(); } catch (err) { console.error('Reveal error:', err); }
  try { initStickyBuyBar(); } catch (err) { console.error('Sticky error:', err); }
  try { initPurchaseToast(); } catch (err) { console.error('Toast error:', err); }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAll);
} else {
  initAll();
}

/* ===================================================================
   1. NAVIGATION & SCROLL OBSERVER
   =================================================================== */
function initNavbar() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }, { passive: true });
}

/* ===================================================================
   2. INTERACTIVE WORKFLOW STEPPER (SECTION D)
   =================================================================== */
const workflowData = [
  {
    num: "STAGE 01",
    title: "Find a Real Problem (Problem First)",
    desc: "Digital product बनाने से पहले customer की bleeding-neck pain identify करें। AI prompts के जरिए Reddit, Quora और niche forums को scan करके unfulfilled market demand discover करें।",
    deliverable: "Deliverable: Verified Customer Problem Statement & Audience Profile."
  },
  {
    num: "STAGE 02",
    title: "Pick the Right Product Format",
    desc: "Format overthinking बंद करें। शुरुआती stage पर eBook, practical Playbook, Prompt Pack या Checklist सबसे fast ship होते हैं। कम complexity में maximum value deliver करें।",
    deliverable: "Deliverable: Product Format Decision Matrix & Scope Lock."
  },
  {
    num: "STAGE 03",
    title: "Structure the Outline (Anti-Chaos Blueprint)",
    desc: "AI से सीधा 'write an ebook' नहीं बोलना है। पहले transformation journey map करें: Point A (Problem) से Point B (Desired Result)। 12 practical modules में outline freeze करें।",
    deliverable: "Deliverable: 12-Module Chapter-by-Chapter Architectural Outline."
  },
  {
    num: "STAGE 04",
    title: "Create Content with AI (Module by Module)",
    desc: "Anti-Generic Prompting formulas use करें। 15 forbidden buzzwords को bypass करके real-world examples, worksheets, action steps और relatable Hinglish context inject करें।",
    deliverable: "Deliverable: Complete Production-Grade Content Manuscript."
  },
  {
    num: "STAGE 05",
    title: "Build an Irresistible Offer",
    desc: "Alex Hormozi Value Equation apply करें। Core asset के साथ 2-3 instant-implementation speed bonuses stack करें। Zero-friction entry price anchor करें (₹199 launch offer)।",
    deliverable: "Deliverable: 10x Value Stack with Risk-Reversal Guarantee."
  },
  {
    num: "STAGE 06",
    title: "Design a High-Converting Landing Page",
    desc: "12 essential conversion sections build करें: Hero with clear promise, social proof, module breakdown, real interior previews, risk reversal और transparent FAQ stack।",
    deliverable: "Deliverable: Mobile-Optimized, Fast-Loading Sales Page."
  },
  {
    num: "STAGE 07",
    title: "Organic Traffic & High-Retention Ads",
    desc: "50 proven ad hooks और short-form video frameworks use करें। Instagram reels, carousel breakdowns और Twitter/LinkedIn threads से targeted audience attract करें।",
    deliverable: "Deliverable: 30-Day Content Distribution & Ad Creative Matrix."
  },
  {
    num: "STAGE 08",
    title: "7-Day Launch & The First Sale Engine",
    desc: "Day 01 से Day 07 तक structured countdown execute करें। Customer onboarding flow, instant delivery automation और feedback collection loop establish करें।",
    deliverable: "Deliverable: Automated Delivery Funnel & First-Sale Execution Plan."
  }
];

function initWorkflowStepper() {
  const steps = document.querySelectorAll('.workflow-step');
  const titleEl = document.querySelector('.workflow-detail-title');
  const descEl = document.querySelector('.workflow-detail-desc');
  const deliverableEl = document.querySelector('.workflow-deliverable-card');

  if (!steps.length) return;

  steps.forEach(step => {
    const activateStep = () => {
      const index = parseInt(step.getAttribute('data-step'), 10);
      steps.forEach(s => s.classList.remove('active'));
      step.classList.add('active');

      const data = workflowData[index];
      if (data) {
        if (titleEl) titleEl.textContent = `${data.num}: ${data.title}`;
        if (descEl) descEl.textContent = data.desc;
        if (deliverableEl) {
          deliverableEl.innerHTML = `<strong>Deliverable:</strong> ${data.deliverable.replace('Deliverable: ', '')}`;
        }
      }
    };

    step.addEventListener('click', activateStep);
    step.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        activateStep();
      }
    });
  });
}

/* ===================================================================
   3. MODULE ROADMAP CATEGORY FILTERS (SECTION E)
   =================================================================== */
function initModuleFilters() {
  const filterBtns = document.querySelectorAll('.module-filter-btn');
  const moduleCards = document.querySelectorAll('.module-card');

  if (!filterBtns.length) return;

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const filter = btn.getAttribute('data-filter');

      moduleCards.forEach(card => {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

/* ===================================================================
   4. DOCUMENT PREVIEW MODAL (SECTION F)
   =================================================================== */
function initPreviewModal() {
  const modal = document.getElementById('preview-modal');
  const modalImg = document.getElementById('modal-preview-image');
  const modalTitle = document.getElementById('modal-preview-title');
  const modalDesc = document.getElementById('modal-preview-desc');
  const closeBtn = document.getElementById('modal-preview-close');
  const previewCards = document.querySelectorAll('.preview-card');

  if (!modal || !previewCards.length) return;

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  previewCards.forEach(card => {
    card.addEventListener('click', () => {
      const src = card.getAttribute('data-preview-src') || card.querySelector('img')?.src;
      const title = card.getAttribute('data-preview-title') || 'Page Preview';
      const desc = card.getAttribute('data-preview-desc') || 'Authentic sample from official publication.';

      if (modalImg && src) modalImg.src = src;
      if (modalTitle) modalTitle.textContent = title;
      if (modalDesc) modalDesc.textContent = desc;

      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
}

/* ===================================================================
   5. FAQ ACCORDION (SECTION K)
   =================================================================== */
function initFaqAccordion() {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(item => {
    const questionBtn = item.querySelector('.faq-question');
    if (!questionBtn) return;

    questionBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isActive = item.classList.contains('active');

      // Close other accordion items for clean single-open view
      faqItems.forEach(otherItem => {
        if (otherItem !== item) {
          otherItem.classList.remove('active');
          const btn = otherItem.querySelector('.faq-question');
          if (btn) btn.setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle current
      item.classList.toggle('active', !isActive);
      questionBtn.setAttribute('aria-expanded', String(!isActive));
    });
  });
}

/* ===================================================================
   6. LEGAL & POLICY MODALS (TERMS, PRIVACY, REFUND, CONTACT)
   =================================================================== */
function initPolicyModals() {
  const policyLinks = document.querySelectorAll('[data-policy-modal]');
  const policyModal = document.getElementById('policy-modal');
  const policyTitle = document.getElementById('policy-modal-title');
  const policyBody = document.getElementById('policy-modal-body');
  const policyClose = document.getElementById('policy-modal-close');

  if (!policyModal) return;

  const policies = {
    privacy: {
      title: "Privacy Policy — TATTVAM",
      content: `
        <p><strong>Last Updated:</strong> October 2026</p>
        <p>At TATTVAM (https://tatvam.shop), we respect and protect customer privacy. This Privacy Policy outlines our data handling practices for digital product purchases.</p>
        <h4>Information We Collect</h4>
        <p>When you purchase or request access to the AI Digital Product Income System, we collect your name, email address, and transaction identifier provided through our secure payment processor (Cashfree Payments). We do not store or process sensitive credit card numbers or UPI passwords directly on our servers.</p>
        <h4>How We Use Information</h4>
        <p>Your email address is strictly used to deliver digital PDF download links, important system updates, and transaction receipts. We never sell, rent, or trade customer information to third-party data brokers.</p>
        <h4>Contact & Inquiries</h4>
        <p>For questions regarding your data or to request deletion of your purchase email record, please reach out via our contact channels at <a href="mailto:tatvam.shop01@gmail.com" style="color: #60a5fa;">tatvam.shop01@gmail.com</a>.</p>
      `
    },
    terms: {
      title: "Terms & Conditions — TATTVAM",
      content: `
        <p><strong>Last Updated:</strong> October 2026</p>
        <p>By accessing or purchasing the "AI Digital Product Income System" and companion "Bonus Vault" from TATTVAM (tatvam.shop), you agree to the following terms:</p>
        <h4>Educational Purpose & No Income Guarantee</h4>
        <p>This product provides structured educational training, frameworks, worksheets, and prompts on digital product creation and AI workflows. TATTVAM does not promise, represent, or guarantee that you will make any specific amount of money or achieve business success. Your results depend on your effort, product concept, market demand, and execution.</p>
        <h4>Intellectual Property & Single-User License</h4>
        <p>Upon purchase, you receive a non-exclusive, non-transferable, single-user digital license for personal educational use. You may NOT re-sell, redistribute, upload to public cloud drives, torrent, or sub-license the PDF publications or materials.</p>
        <h4>Governing Jurisdiction</h4>
        <p>These terms are governed in accordance with applicable laws in India.</p>
      `
    },
    refund: {
      title: "Refund & Cancellation Policy — TATTVAM",
      content: `
        <p><strong>Clear & Transparent Terms:</strong></p>
        <p>Because the "AI Digital Product Income System" and "Bonus Vault" are instant-download digital assets provided immediately in full PDF format upon payment confirmation, traditional return of goods is not applicable.</p>
        <h4>Customer Satisfaction Commitment</h4>
        <p>We are dedicated to providing high-quality educational value. If you experience any technical difficulties downloading your files, corrupted files, or payment processing discrepancies, our support team will resolve it within 24–48 hours.</p>
        <p>Please review the product overview, 12 modules list, and interior page previews prior to purchase to confirm that this curriculum aligns with your goals.</p>
      `
    },
    contact: {
      title: "Contact & Business Support — TATTVAM",
      content: `
        <p>Have questions before enrolling or need assistance with your digital download?</p>
        <div style="background: rgba(255,255,255,0.04); padding: 18px; border-radius: 8px; margin: 15px 0;">
          <p style="margin-bottom: 8px;"><strong>Brand:</strong> TATTVAM</p>
          <p style="margin-bottom: 8px;"><strong>Website:</strong> <a href="https://tatvam.shop" target="_blank" style="color: #60a5fa;">https://tatvam.shop</a></p>
          <p style="margin-bottom: 8px;"><strong>Customer Support Email:</strong> <a href="mailto:tatvam.shop01@gmail.com" style="color: #60a5fa;">tatvam.shop01@gmail.com</a></p>
          <p style="margin-bottom: 0;"><strong>Operational Hours:</strong> Monday – Saturday (10:00 AM – 6:00 PM IST)</p>
        </div>
        <p style="font-size: 0.85rem; color: #94a3b8;">Queries are typically answered within 24 business hours.</p>
      `
    }
  };

  policyLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const policyKey = link.getAttribute('data-policy-modal');
      const data = policies[policyKey];

      if (data && policyTitle && policyBody) {
        policyTitle.textContent = data.title;
        policyBody.innerHTML = data.content;
        policyModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  const closePolicy = () => {
    policyModal.classList.remove('active');
    document.body.style.overflow = '';
  };

  if (policyClose) policyClose.addEventListener('click', closePolicy);

  policyModal.addEventListener('click', (e) => {
    if (e.target === policyModal) closePolicy();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && policyModal.classList.contains('active')) {
      closePolicy();
    }
  });
}

/* ===================================================================
   7. CHECKOUT TRIGGER & LIVE CASHFREE ORDER FLOW
   =================================================================== */
function initCheckoutTriggers() {
  const checkoutTriggers = document.querySelectorAll('.checkout-trigger, .pricing-cta, #pricing-checkout-btn');
  const checkoutModal = document.getElementById('checkout-modal');
  const checkoutClose = document.getElementById('checkout-modal-close');
  const checkoutForm = document.getElementById('ai-checkout-form');
  const submitBtn = document.getElementById('ai-submit-btn');

  function openCheckout() {
    if (checkoutModal) {
      checkoutModal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeCheckout() {
    if (checkoutModal) {
      checkoutModal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  // Robust Click Delegation: captures all Buy Buttons across entire DOM
  document.addEventListener('click', (e) => {
    const buyTrigger = e.target.closest('.btn-buy-trigger, .checkout-trigger, .sticky-buy-btn, .pricing-cta, a[href="#pricing"]');
    if (buyTrigger) {
      e.preventDefault();
      openCheckout();
    }
  });

  if (checkoutClose) {
    checkoutClose.addEventListener('click', (e) => {
      e.preventDefault();
      closeCheckout();
    });
  }

  if (checkoutModal) {
    checkoutModal.addEventListener('click', (e) => {
      if (e.target === checkoutModal) closeCheckout();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && checkoutModal && checkoutModal.classList.contains('active')) {
      closeCheckout();
    }
  });

  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const origText = submitBtn ? submitBtn.innerHTML : 'Proceed to Payment';
      if (submitBtn) {
        submitBtn.innerHTML = '<span>कृपया प्रतीक्षा करें... (Processing...)</span>';
        submitBtn.disabled = true;
      }

      // Check if running on local file:// protocol
      if (window.location.protocol === 'file:') {
        setTimeout(() => {
          alert('Demo Order Mode (Local File View):\n\nइस समय आप लोकल फाइल (file://) देख रहे हैं।\nलाइव वेबसाइट (https://tatvam.shop) पर यह फॉर्म सीधे Cashfree Payment Gateway से कनेक्ट होता है और UPI/Card से ₹199 पेमेंट पूरी होती है।');
          if (submitBtn) {
            submitBtn.innerHTML = origText;
            submitBtn.disabled = false;
          }
          closeCheckout();
        }, 500);
        return;
      }

      const formData = new FormData(checkoutForm);

      fetch('create-order.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.payment_session_id) {
          const cfMode = (data.environment === 'PRODUCTION') ? 'production' : 'sandbox';
          let cashfreeObj = null;

          if (typeof Cashfree !== 'undefined') {
            cashfreeObj = Cashfree({ mode: cfMode });
          } else if (typeof window.Cashfree !== 'undefined') {
            cashfreeObj = window.Cashfree({ mode: cfMode });
          }

          if (!cashfreeObj) {
            alert('पेमेंट गेटवे लोड नहीं हो सका। कृपया पेज रिफ्रेश करें।');
            if (submitBtn) {
              submitBtn.innerHTML = origText;
              submitBtn.disabled = false;
            }
            return;
          }

          closeCheckout();

          // Launch Cashfree redirect
          cashfreeObj.checkout({
            paymentSessionId: data.payment_session_id,
            redirectTarget: '_self'
          });
        } else {
          alert('त्रुटि: ' + (data.message || 'पेमेंट सत्र शुरू नहीं हो सका। कृपया पुनः प्रयास करें।'));
          if (submitBtn) {
            submitBtn.innerHTML = origText;
            submitBtn.disabled = false;
          }
        }
      })
      .catch(err => {
        console.error('Order Error:', err);
        alert('सर्वर से कनेक्ट करने में समस्या आई। Live server पर यह Cashfree Gateway से कनेक्ट होता है।');
        if (submitBtn) {
          submitBtn.innerHTML = origText;
          submitBtn.disabled = false;
        }
      });
    });
  }
}

/* ===================================================================
   8. SMOOTH SCROLLING FOR ALL SECTION ANCHOR LINKS
   =================================================================== */
function initSmoothScroll() {
  const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
  anchorLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const targetId = link.getAttribute('href').substring(1);
      const targetEl = document.getElementById(targetId);
      if (targetEl) {
        e.preventDefault();
        const navbar = document.querySelector('.navbar');
        const navHeight = navbar ? navbar.offsetHeight : 70;
        const targetPos = targetEl.getBoundingClientRect().top + window.scrollY - navHeight - 15;
        window.scrollTo({
          top: targetPos,
          behavior: 'smooth'
        });
      }
    });
  });
}

/* ===================================================================
   9. SCROLL REVEAL OBSERVER
   =================================================================== */
function initScrollReveal() {
  if (!('IntersectionObserver' in window)) return;

  const targets = document.querySelectorAll(
    '.module-card, .bonus-item-card, .problem-card, .comparison-card, .audience-card, .pricing-card, .preview-card'
  );
  targets.forEach(el => el.classList.add('reveal-on-scroll'));

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.08,
    rootMargin: '0px 0px -20px 0px'
  });

  targets.forEach(el => observer.observe(el));
}

/* ===================================================================
   10. STICKY BOTTOM BUY NOW BAR (ON-SCROLL REVEAL)
   =================================================================== */
function initStickyBuyBar() {
  const stickyBar = document.getElementById('sticky-buy-bar');
  if (!stickyBar) return;

  const updateSticky = () => {
    if (window.scrollY > 260) {
      stickyBar.classList.add('is-visible');
    } else {
      stickyBar.classList.remove('is-visible');
    }
  };

  window.addEventListener('scroll', updateSticky, { passive: true });
  // Initial check
  updateSticky();
}

/* ===================================================================
   11. RECENT PURCHASE NOTIFICATION TOAST (EVERY 15 SECONDS)
   =================================================================== */
function initPurchaseToast() {
  const toast = document.getElementById('purchase-toast');
  const userEl = document.getElementById('toast-user');
  const timeEl = document.getElementById('toast-time');
  const closeBtn = document.getElementById('purchase-toast-close');

  if (!toast) return;

  const purchases = [
    { name: "Rahul S.", city: "Bengaluru", time: "2 minutes ago" },
    { name: "Priya M.", city: "Mumbai", time: "4 minutes ago" },
    { name: "Amit K.", city: "Delhi NCR", time: "Just now" },
    { name: "Sneha P.", city: "Pune", time: "6 minutes ago" },
    { name: "Vikram R.", city: "Hyderabad", time: "8 minutes ago" },
    { name: "Ananya D.", city: "Kolkata", time: "11 minutes ago" },
    { name: "Rohan V.", city: "Jaipur", time: "14 minutes ago" },
    { name: "Deepak S.", city: "Ahmedabad", time: "Just now" },
    { name: "Pooja N.", city: "Chandigarh", time: "3 minutes ago" },
    { name: "Arjun T.", city: "Chennai", time: "9 minutes ago" }
  ];

  let currentIndex = 0;
  let isDismissed = false;

  function showToast() {
    if (isDismissed) return;

    const data = purchases[currentIndex];
    if (userEl) userEl.textContent = `${data.name} from ${data.city}`;
    if (timeEl) timeEl.textContent = `${data.time} • Instant PDF Download ✓`;

    toast.classList.add('is-visible');

    // Display for 4.5 seconds then smoothly hide
    setTimeout(() => {
      toast.classList.remove('is-visible');
    }, 4500);

    currentIndex = (currentIndex + 1) % purchases.length;
  }

  // Initial trigger after 4 seconds, then repeat every 20 seconds
  setTimeout(() => {
    showToast();
    setInterval(showToast, 20000);
  }, 4000);

  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      toast.classList.remove('is-visible');
      isDismissed = true;
    });
  }

  // Clicking on toast opens the instant checkout modal
  toast.addEventListener('click', () => {
    const checkoutModal = document.getElementById('checkout-modal');
    if (checkoutModal) {
      checkoutModal.classList.add('active');
      document.body.style.overflow = 'hidden';
      toast.classList.remove('is-visible');
    }
  });
}


