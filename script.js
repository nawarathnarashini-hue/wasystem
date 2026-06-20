// PAGE INITIALIZATION
window.addEventListener('DOMContentLoaded', () => {
    // Hide Loader
    const loader = document.getElementById('loader');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 800);
    }

    // Initialize Auto Testimonial Slider if present
    initAutoSlider();

    // Back to top scroll listener
    window.addEventListener('scroll', () => {
        const btn = document.getElementById('backToTop');
        if (btn) {
            if (window.scrollY > 300) btn.classList.add('visible');
            else btn.classList.remove('visible');
        }
    });

    // Close modal on overlay click
    const modalOverlay = document.getElementById('modalOverlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    }

    // FAQ items accordion
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            q.parentElement.classList.toggle('active');
        });
    });
});

// NAVIGATION FUNCTIONS
function toggleMobile() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) navLinks.classList.toggle('active');
}

// CLIENT-SIDE SEARCH REDIRECT
function heroSearch() {
    const dest = document.getElementById('heroDest').value;
    const dur = document.getElementById('heroDuration').value;
    const price = document.getElementById('heroPrice').value;

    window.location.href = `packages.php?dest=${encodeURIComponent(dest)}&dur=${encodeURIComponent(dur)}&price=${encodeURIComponent(price)}`;
}

// MODAL CONTROLLERS
function closeModal() {
    const overlay = document.getElementById('modalOverlay');
    if (overlay) overlay.classList.remove('active');
}

// TAB CONTROL FOR TRAVEL GUIDES
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    // Add active state to clicked button
    const activeBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.getAttribute('onclick').includes(tabId));
    if (activeBtn) activeBtn.classList.add('active');

    const targetContent = document.getElementById(tabId);
    if (targetContent) targetContent.classList.add('active');
}

// TESTIMONIAL SLIDER HANDLERS
let currentSlide = 0;
function moveSlide(dir) {
    const slider = document.getElementById('testimonialSlider');
    if (!slider) return;
    const cards = slider.children;
    currentSlide = (currentSlide + dir + cards.length) % cards.length;
    slider.style.transform = `translateX(-${currentSlide * 100}%)`;
}

function initAutoSlider() {
    const slider = document.getElementById('testimonialSlider');
    if (!slider) return;
    setInterval(() => moveSlide(1), 5000);
}

// TOAST NOTIFICATIONS
function showToast(msg) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// SCROLL TO TOP
function scrollToTop() {
    window.scrollTo({top: 0, behavior: 'smooth'});
}

// DASHBOARD SIDEBAR TAB NAVIGATION
function showDashTab(tabId) {
    document.querySelectorAll('.dash-tab').forEach(t => t.style.display = 'none');
    
    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.style.display = 'block';

    // Remove active class from menu links
    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
    
    // Add active class to the clicked link
    const link = Array.from(document.querySelectorAll('.sidebar-menu a')).find(a => a.getAttribute('onclick').includes(tabId));
    if (link) link.classList.add('active');
}
