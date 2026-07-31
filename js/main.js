/* ============================================
   RC Depósitos v2 — Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initMobileMenu();
    initFAQ();
    initScrollAnimations();
    initForm();
    initSmoothScroll();
    initCalculator();
});

/* ============================================
   NAVBAR SCROLL EFFECT
   ============================================ */
function initNavbar() {
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }, { passive: true });
}

/* ============================================
   MOBILE MENU
   ============================================ */
function initMobileMenu() {
    const toggle = document.getElementById('navToggle');
    const menu = document.getElementById('navMobileMenu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', function() {
        menu.classList.toggle('active');
        const spans = toggle.querySelectorAll('span');
        if (menu.classList.contains('active')) {
            spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
        } else {
            spans[0].style.transform = '';
            spans[1].style.opacity = '1';
            spans[2].style.transform = '';
        }
    });

    // Close menu on link click
    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('active');
            const spans = toggle.querySelectorAll('span');
            spans[0].style.transform = '';
            spans[1].style.opacity = '1';
            spans[2].style.transform = '';
        });
    });
}

/* ============================================
   FAQ ACCORDION
   ============================================ */
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');

        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');

            // Close all
            faqItems.forEach(i => i.classList.remove('active'));

            // Open clicked if it wasn't active
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

/* ============================================
   SCROLL ANIMATIONS
   ============================================ */
function initScrollAnimations() {
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -50px 0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Animate sections
    document.querySelectorAll('.benefit-card, .faq-item, .pricing-card-inner, .contact-form, .contact-item').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}

/* ============================================
   FORM HANDLING
   ============================================ */
function initForm() {
    const form = document.getElementById('contactForm');
    const messageEl = document.getElementById('formMessage');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = form.querySelector('.btn');
        const formData = new FormData(form);

        // Validate
        const nombre = formData.get('nombre').trim();
        const telefono = formData.get('telefono').trim();
        const email = formData.get('email').trim();

        if (!nombre || !telefono || !email) {
            showMessage('Por favor, completa todos los campos.', 'error');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showMessage('Por favor, ingresa un correo electrónico válido.', 'error');
            return;
        }

        const phoneRegex = /^[0-9\s]{9,15}$/;
        if (!phoneRegex.test(telefono.replace(/\s/g, ''))) {
            showMessage('Por favor, ingresa un número de teléfono válido.', 'error');
            return;
        }

        // Loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        hideMessage();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showMessage('¡Gracias! Tu información ha sido enviada. Te contactaremos pronto.', 'success');
                form.reset();
            } else {
                showMessage(result.message || 'Hubo un error al enviar el formulario. Inténtalo de nuevo.', 'error');
            }
        } catch (error) {
            showMessage('Error de conexión. Por favor, inténtalo más tarde.', 'error');
            console.error('Form error:', error);
        } finally {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });

    function showMessage(text, type) {
        messageEl.textContent = text;
        messageEl.className = 'form-message ' + type;
    }

    function hideMessage() {
        messageEl.className = 'form-message';
        messageEl.textContent = '';
    }
}

/* ============================================
   SMOOTH SCROLL
   ============================================ */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offset = 80;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/* ============================================
   CALCULATOR
   ============================================ */
function initCalculator() {
    const m2Input = document.getElementById('m2Input');
    const calcTotal = document.getElementById('calcTotal');
    const calcSize = document.getElementById('calcSize');

    if (!m2Input || !calcTotal || !calcSize) return;

    m2Input.addEventListener('input', function() {
        let m2 = parseInt(this.value) || 0;
        if (m2 < 0) {
            m2 = 0;
            this.value = 0;
        }
        const pricePerM2 = 25;
        calcTotal.textContent = m2 * pricePerM2;
        calcSize.textContent = m2 + 'm²';
    });
}
