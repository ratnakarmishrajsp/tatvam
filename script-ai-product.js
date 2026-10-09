/**
 * TATTVAM â€” AI DIGITAL PRODUCT INCOME SYSTEM
 * Interactive Client Logic & Accessibility Controller
 * Brand: TATTVAM | Website: https://tattvam.shop
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initWorkflowStepper();
  initModuleFilters();
  initPreviewModal();
  initFaqAccordion();
  initPolicyModals();
  initCheckoutTriggers();
  initScrollReveal();
});

/* ===================================================================
   1. NAVIGATION & SCROLL OBSERVER
   =================================================================== */
function initNavbar() {
  const navbar = document.querySelector('.navbar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileMenu = document.querySelector('.mobile-nav-menu');
  const mobileLinks = document.querySelectorAll('.mobile-nav-link');

  // Sticky Navbar state
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }, { passive: true });

  // Mobile menu toggle
  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = mobileMenu.classList.toggle('open');
      mobileToggle.setAttribute('aria-expanded', isOpen);
      mobileToggle.innerHTML = isOpen ? 'âœ•' : 'â˜°';
    });

    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileToggle.innerHTML = 'â˜°';
      });
    });
  }

  // Active link scroll spy
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    const scrollPos = window.scrollY + 200;

    sections.forEach(section => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      if (scrollPos >= top && scrollPos < top + height) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${current}`) {
        link.classList.add('active');
      }
    });
  }, { passive: true });
}

/* ===================================================================
   2. INTERACTIVE WORKFLOW STEPPER (SECTION D)
   =================================================================== */
const workflowData = [
  {
    num: "STAGE 01",
    title: "Find a Real Problem (Problem First)",
    desc: "Digital product à¤¬à¤¨à¤¾à¤¨à¥‡ à¤¸à¥‡ à¤ªà¤¹à¤²à¥‡ customer à¤•à¥€ bleeding-neck pain identify à¤•à¤°à¥‡à¤‚. AI prompts à¤•à¥‡ à¤œà¤°à¤¿à¤ Reddit, Quora à¤”à¤° niche forums à¤•à¥‹ scan à¤•à¤°à¤•à¥‡ unfulfilled market demands uncover à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: Validated Customer Pain Profile & High-Intent Search Queries."
  },
  {
    num: "STAGE 02",
    title: "Validate Demand & Solvability",
    desc: "8-Vector Validation Scorecard (Bonus 6) use à¤•à¤°à¥‡à¤‚. Problem à¤•à¥€ severity, urgency, target audience accessibility, à¤”à¤° customer willingness-to-pay mathematically verify à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: Completed Validation Scorecard (>30/40 Score) with Green Light."
  },
  {
    num: "STAGE 03",
    title: "Create with AI (The 12-Step Engine)",
    desc: "AI-First 12-step creation pipeline execute à¤•à¤°à¥‡à¤‚. 15 forbidden generic buzzwords bypass à¤•à¤°à¥‡à¤‚, high-value original frameworks establish à¤•à¤°à¥‡à¤‚, à¤”à¤° structured content draft à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: Comprehensive, Original Digital Publication Draft."
  },
  {
    num: "STAGE 04",
    title: "Package & Premiumize The Asset",
    desc: "Visual hierarchy, typography, fillable worksheets, and 3D device mockups à¤œà¥‹à¤¡à¤¼à¤•à¤° perceived value elevate à¤•à¤°à¥‡à¤‚. Plain text dump à¤•à¥‹ commercial publication à¤®à¥‡à¤‚ transform à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: Commercially Formatted Master Guide & Printable Workbook."
  },
  {
    num: "STAGE 05",
    title: "Build an Irresistible Offer",
    desc: "Alex Hormozi Value Equation apply à¤•à¤°à¥‡à¤‚. Core asset à¤•à¥‡ à¤¸à¤¾à¤¥ 2-3 instant-implementation speed bonuses stack à¤•à¤°à¥‡à¤‚. Zero-friction entry price anchor à¤•à¤°à¥‡à¤‚ (â‚¹299â€“â‚¹999).",
    deliverable: "Deliverable: 10x Value Stack with Risk-Reversal Guarantee."
  },
  {
    num: "STAGE 06",
    title: "Build the 12-Section Landing Page",
    desc: "Direct-response conversion framework deploy à¤•à¤°à¥‡à¤‚. Hero hook, pain agitation, solution contrast, feature breakdown, social proof, objection FAQs, à¤”à¤° clear single CTA.",
    deliverable: "Deliverable: High-Converting Sales Page with Integrated Checkout."
  },
  {
    num: "STAGE 07",
    title: "Market with Organic Content & AI Ads",
    desc: "Problem -> Hook -> Agitation -> Solution -> CTA formula use à¤•à¤°à¤•à¥‡ short-form Reels, carousels, à¤”à¤° micro-budget Meta ads generate à¤•à¤°à¥‡à¤‚. 50 battle-tested ad hooks deploy à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: 30-Day Content Matrix & 5 High-CTR Creative Assets."
  },
  {
    num: "STAGE 08",
    title: "Launch, Acquire Customers & Optimize",
    desc: "Disciplined 7-Day Launch Checklist execute à¤•à¤°à¥‡à¤‚. Initial 10 paying customers acquire à¤•à¤°à¥‡à¤‚, real user feedback loops collect à¤•à¤°à¥‡à¤‚, à¤”à¤° product à¤•à¥‹ scale à¤•à¤°à¥‡à¤‚.",
    deliverable: "Deliverable: First Paying Customers & Repeatable Sales Loop."
  }
];

function initWorkflowStepper() {
  const steps = document.querySelectorAll('.workflow-step');
  const titleEl = document.querySelector('.workflow-detail-title');
  const descEl = document.querySelector('.workflow-detail-desc');
  const deliverableEl = document.querySelector('.workflow-deliverable-card');

  if (!steps.length || !titleEl) return;

  steps.forEach(step => {
    step.addEventListener('click', () => {
      const index = parseInt(step.getAttribute('data-step'), 10);
      steps.forEach(s => s.classList.remove('active'));
      step.classList.add('active');

      const data = workflowData[index];
      if (data) {
        titleEl.textContent = `${data.num}: ${data.title}`;
        descEl.textContent = data.desc;
        deliverableEl.innerHTML = `<strong>Deliverable:</strong> ${data.deliverable.replace('Deliverable: ', '')}`;
      }
    });

    step.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        step.click();
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
          card.style.display = 'flex';
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

  previewCards.forEach(card => {
    card.addEventListener('click', () => {
      const src = card.getAttribute('data-preview-src');
      const title = card.getAttribute('data-preview-title');
      const desc = card.getAttribute('data-preview-desc');

      modalImg.src = src;
      modalTitle.textContent = title;
      modalDesc.textContent = desc;

      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

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

    questionBtn.addEventListener('click', () => {
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
      questionBtn.setAttribute('aria-expanded', !isActive);
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
      title: "Privacy Policy â€” TATTVAM",
      content: `
        <p><strong>Last Updated:</strong> October 2026</p>
        <p>At TATTVAM (https://tattvam.shop), we respect and protect customer privacy. This Privacy Policy outlines our data handling practices for digital product purchases.</p>
        <h4>Information We Collect</h4>
        <p>When you purchase or request access to the AI Digital Product Income System, we collect your name, email address, and transaction identifier provided through our secure payment processor (e.g., Razorpay / Stripe / Gumroad). We do not store or process sensitive credit card or UPI passwords directly on our servers.</p>
        <h4>How We Use Information</h4>
        <p>Your email address is strictly used to deliver digital PDF download links, important system updates, and transaction receipts. We never sell, rent, or trade customer information to third-party data brokers.</p>
        <h4>Contact & Inquiries</h4>
        <p>For questions regarding your data or to request deletion of your purchase email record, please reach out via our contact channels at support@tattvam.shop.</p>
      `
    },
    terms: {
      title: "Terms & Conditions â€” TATTVAM",
      content: `
        <p><strong>Last Updated:</strong> October 2026</p>
        <p>By accessing or purchasing the "AI Digital Product Income System" and companion "Bonus Vault" from TATTVAM (tattvam.shop), you agree to the following terms:</p>
        <h4>Educational Purpose & No Income Guarantee</h4>
        <p>This product provides structured educational training, frameworks, worksheets, and prompts on digital product creation and AI workflows. TATTVAM does not promise, represent, or guarantee that you will make any specific amount of money or achieve business success. Your results depend on your effort, product concept, market demand, and execution.</p>
        <h4>Intellectual Property & Single-User License</h4>
        <p>Upon purchase, you receive a non-exclusive, non-transferable, single-user digital license for personal educational use. You may NOT re-sell, redistribute, upload to public cloud drives, torrent, or sub-license the PDF publications or materials.</p>
        <h4>Governing Jurisdiction</h4>
        <p>These terms are governed in accordance with applicable laws in India.</p>
      `
    },
    refund: {
      title: "Refund & Cancellation Policy â€” TATTVAM",
      content: `
        <p><strong>Clear & Transparent Terms:</strong></p>
        <p>Because the "AI Digital Product Income System" and "Bonus Vault" are instant-download digital assets provided immediately in full PDF format upon payment confirmation, traditional return of goods is not applicable.</p>
        <h4>Customer Satisfaction Commitment</h4>
        <p>We are dedicated to providing high-quality educational value. If you experience any technical difficulties downloading your files, corrupted files, or payment processing discrepancies, our support team will resolve it within 24â€“48 hours.</p>
        <p>Please review the product overview, 12 modules list, and interior page previews prior to purchase to confirm that this curriculum aligns with your goals.</p>
      `
    },
    contact: {
      title: "Contact & Business Support â€” TATTVAM",
      content: `
        <p>Have questions before enrolling or need assistance with your digital download?</p>
        <div style="background: rgba(255,255,255,0.04); padding: 18px; border-radius: 8px; margin: 15px 0;">
          <p style="margin-bottom: 8px;"><strong>Brand:</strong> TATTVAM</p>
          <p style="margin-bottom: 8px;"><strong>Website:</strong> <a href="https://tattvam.shop" target="_blank" style="color: #60a5fa;">https://tattvam.shop</a></p>
          <p style="margin-bottom: 8px;"><strong>Customer Support Email:</strong> <a href="mailto:support@tattvam.shop" style="color: #60a5fa;">support@tattvam.shop</a></p>
          <p style="margin-bottom: 0;"><strong>Operational Hours:</strong> Monday â€“ Saturday (10:00 AM â€“ 6:00 PM IST)</p>
        </div>
        <p style="font-size: 0.85rem; color: #94a3b8;">Queries are typically answered within 24 business hours.</p>
      `
    }
  };

  policyLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const policyKey = link.getAttribute('data-policy-modal');
      const policy = policies[policyKey];

      if (policy) {
        policyTitle.textContent = policy.title;
        policyBody.innerHTML = policy.content;
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
  const buyButtons = document.querySelectorAll('.btn-buy-trigger, .checkout-trigger');
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

  buyButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openCheckout();
    });
  });

  if (checkoutClose) checkoutClose.addEventListener('click', closeCheckout);

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
        alert('सर्वर से कनेक्ट करने में समस्या आई। कृपया इंटरनेट चेक करें।');
        if (submitBtn) {
          submitBtn.innerHTML = origText;
          submitBtn.disabled = false;
        }
      });
    });
  }
}
    });
  });

  if (checkoutModal) {
    const closeCheckout = () => {
      checkoutModal.classList.remove('active');
      document.body.style.overflow = '';
    };

    if (checkoutClose) checkoutClose.addEventListener('click', closeCheckout);

    checkoutModal.addEventListener('click', (e) => {
      if (e.target === checkoutModal) closeCheckout();
    });
  }
}

/* ===================================================================
   8. SCROLL REVEAL OBSERVER
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
    threshold: 0.1,
    rootMargin: '0px 0px -40px 0px'
  });

  targets.forEach(el => observer.observe(el));
}

