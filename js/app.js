/* hospital/js/app.js */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggler
    setupMobileMenu();

    // 2. Scroll Header Sticky Animation
    setupScrollHeader();
    
    // 3. Auto Active Nav Items
    highlightActiveNav();
});

function setupMobileMenu() {
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            
            // Toggle hamburger icon animation
            const spans = toggle.querySelectorAll('span');
            if (navLinks.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }
}

function setupScrollHeader() {
    const header = document.querySelector('.app-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.style.padding = '0.5rem 0';
                header.style.boxShadow = 'var(--shadow-md)';
                header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            } else {
                header.style.padding = '1rem 0';
                header.style.boxShadow = 'none';
                header.style.backgroundColor = 'var(--bg-glass)';
            }
        });
    }
}

function highlightActiveNav() {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href) {
            // Handle home page case
            if (href === 'index.php' || href === '../index.php') {
                if (currentPath.endsWith('/') || currentPath.endsWith('/index.php')) {
                    item.classList.add('active');
                }
            } else {
                // Get filename (e.g. login.php)
                const filename = href.split('/').pop();
                if (currentPath.includes(filename)) {
                    item.classList.add('active');
                }
            }
        }
    });
}
