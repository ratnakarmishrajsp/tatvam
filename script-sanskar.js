// script-sanskar.js - Interactive & Cashfree Checkout for Sanskar 30 (Tatvam.shop)

document.addEventListener('DOMContentLoaded', () => {

  // 1. Global Checkout Modal Controller (Bulletproof with Event Delegation)
  const modalOverlay = document.getElementById('checkoutModal');
  const modalCloseBtn = document.getElementById('modalCloseBtn');
  const checkoutForm = document.getElementById('checkout-form');
  const submitBtn = document.getElementById('checkoutSubmitBtn');

  function openModal() {
    if (modalOverlay) {
      modalOverlay.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal() {
    if (modalOverlay) {
      modalOverlay.style.display = 'none';
      document.body.style.overflow = '';
    }
  }

  // Delegated click listener for all checkout triggers anywhere on the page
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.checkout-trigger');
    if (trigger) {
      e.preventDefault();
      openModal();
    }
  });

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });
  }

  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        closeModal();
      }
    });
  }

  // Esc key closes modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalOverlay && modalOverlay.style.display === 'flex') {
      closeModal();
    }
  });

  // 2. Submit Order to Cashfree API (create-order.php)
  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const origText = submitBtn ? submitBtn.innerHTML : 'Pay';
      if (submitBtn) {
        submitBtn.innerHTML = 'कृपया प्रतीक्षा करें... (Processing...)';
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
            alert('पेमेंट गेटवे लोड नहीं हो सका। कृपया पेज रिफ्रेश करके दोबारा प्रयास करें।');
            if (submitBtn) {
              submitBtn.innerHTML = origText;
              submitBtn.disabled = false;
            }
            return;
          }

          closeModal();

          // Launch Cashfree Drop checkout / Payment redirect
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
        console.error('Checkout error:', err);
        alert('इंटरनेट कनेक्शन त्रुटि। कृपया पुनः प्रयास करें।');
        if (submitBtn) {
          submitBtn.innerHTML = origText;
          submitBtn.disabled = false;
        }
      });
    });
  }

  // 3. Preview Gallery Tabs
  try {
    const tabButtons = document.querySelectorAll('.preview-tabs .tab-btn');
    const previewPanels = document.querySelectorAll('.preview-panel');
    const previewCaption = document.getElementById('previewCaption');
    const previewSubcaption = document.getElementById('previewSubcaption');

    const captionsData = {
      'tab-cover': {
        title: 'मुख्य ई-बुक कवर (72 प्रीमियम पेजेस)',
        sub: 'हार्डकवर जैसी खूबसूरत 3D फिनिश, जिसे बच्चे देखते ही पढ़ना चाहें।'
      },
      'tab-story': {
        title: 'कहानी पृष्ठ लेआउट (दिन 1: नन्हें कदम, बड़ा सच)',
        sub: 'बड़ा पठनीय फॉन्ट, आकर्षक चित्रण और एक स्पष्ट जीवन-मूल्य।'
      },
      'tab-activity': {
        title: 'इंटरैक्टिव एक्टिविटी पेज (करके सीखने का अभ्यास)',
        sub: 'ड्राइंग, पज़ल और व्यावहारिक काम जो सीख को आदत में बदल दें।'
      },
      'tab-conversation': {
        title: 'पैरेंट-चाइल्ड कन्वर्सेशन स्टार्टर कार्ड',
        sub: 'डिनर या सोने से पहले 5 मिनट की गहरी, सार्थक बातचीत के संकेत।'
      },
      'tab-tracker': {
        title: '30-डे संस्कर स्टार प्रोग्रेस ट्रैकर',
        sub: 'प्रिंट करने योग्य आकर्षक चार्ट—हर रोज़ एक स्टार भरें और उत्साह बढ़ाएँ।'
      },
      'tab-certificate': {
        title: 'संस्कार रत्न पूर्णता प्रमाण-पत्र (Certificate)',
        sub: '30 दिन पूरे होने पर बच्चे के नाम का गर्व भरा गोल्डन रॉयल सर्टिफिकेट।'
      }
    };

    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        tabButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const targetTabId = btn.getAttribute('data-tab');

        previewPanels.forEach(panel => {
          if (panel.id === targetTabId) {
            panel.style.display = 'block';
            panel.style.opacity = '0';
            setTimeout(() => {
              panel.style.transition = 'opacity 0.3s ease';
              panel.style.opacity = '1';
            }, 10);
          } else {
            panel.style.display = 'none';
          }
        });

        if (captionsData[targetTabId]) {
          if (previewCaption) previewCaption.textContent = captionsData[targetTabId].title;
          if (previewSubcaption) previewSubcaption.textContent = captionsData[targetTabId].sub;
        }
      });
    });
  } catch (err) {
    console.warn('Tabs init error:', err);
  }

  // 4. FAQ Accordion
  try {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
      const question = item.querySelector('.faq-question');
      if (question) {
        question.addEventListener('click', () => {
          const isActive = item.classList.contains('active');
          faqItems.forEach(i => i.classList.remove('active'));
          if (!isActive) {
            item.classList.add('active');
          }
        });
      }
    });
  } catch (err) {
    console.warn('FAQ init error:', err);
  }

  // 5. Smooth Scroll for Anchor Links
  try {
    const ctaLinks = document.querySelectorAll('a[href^="#"]');
    ctaLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        const targetId = link.getAttribute('href');
        if (targetId && targetId !== '#' && !link.classList.contains('checkout-trigger')) {
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            e.preventDefault();
            targetElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        }
      });
    });
  } catch (err) {
    console.warn('Smooth scroll error:', err);
  }

  // 6. Interactive 3D Book Parallax / Tilt on Mouse Move
  try {
    const bundleStage = document.querySelector('.bundle-stage');
    const bookMockup = document.querySelector('.book-mockup');
    
    if (bundleStage && bookMockup && window.innerWidth > 768) {
      bundleStage.addEventListener('mousemove', (e) => {
        const rect = bundleStage.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = ((y - centerY) / centerY) * -10 + 5;
        const rotateY = ((x - centerX) / centerX) * 14 - 16;
        
        bookMockup.style.transform = 'rotateY(' + rotateY.toFixed(1) + 'deg) rotateX(' + rotateX.toFixed(1) + 'deg) translateZ(20px)';
      });

      bundleStage.addEventListener('mouseleave', () => {
        bookMockup.style.transform = '';
      });
    }
  } catch (err) {
    console.warn('Tilt error:', err);
  }

});