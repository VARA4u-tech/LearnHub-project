document.addEventListener('DOMContentLoaded', () => {
    // Main mobile menu
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        const openIcon = mobileMenuButton.querySelector('.menu-open-icon');
        const closeIcon = mobileMenuButton.querySelector('.menu-close-icon');
        mobileMenuButton.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            if (openIcon && closeIcon) {
                openIcon.classList.toggle('hidden', !isHidden);
                closeIcon.classList.toggle('hidden', isHidden);
            }
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
        });
    }

    // Dashboard sidebar menu
    const dashboardMenuButton = document.getElementById('dashboard-menu-button');
    const dashboardSidebar = document.getElementById('dashboard-sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');

    if (dashboardMenuButton && dashboardSidebar) {
        dashboardMenuButton.addEventListener('click', () => {
            dashboardSidebar.classList.remove('-translate-x-full');
            dashboardSidebar.classList.add('translate-x-0');
            if(sidebarBackdrop) sidebarBackdrop.classList.remove('hidden');
        });

        const closeSidebar = () => {
            dashboardSidebar.classList.remove('translate-x-0');
            dashboardSidebar.classList.add('-translate-x-full');
            if(sidebarBackdrop) sidebarBackdrop.classList.add('hidden');
        }

        // Close with backdrop
        if(sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);

        // Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dashboardSidebar.classList.contains('translate-x-0')) {
                closeSidebar();
            }
        });
    }

    // Simulate hover effects on touch devices
    const hoverElements = document.querySelectorAll('[data-hover]');

    hoverElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.classList.add('touch-hover');
        });

        element.addEventListener('touchend', function() {
            this.classList.remove('touch-hover');
        });

        element.addEventListener('touchcancel', function() {
            this.classList.remove('touch-hover');
        });
    });

    // Scroll-reveal animation
    const scrollElements = document.querySelectorAll('[data-scroll-reveal]');

    const elementInView = (el, dividend = 1) => {
        const elementTop = el.getBoundingClientRect().top;
        return (
            elementTop <=
            (window.innerHeight || document.documentElement.clientHeight) / dividend
        );
    };

    const displayScrollElement = (element) => {
        element.classList.add('is-visible');
    };

    const handleScrollAnimation = () => {
        scrollElements.forEach((el) => {
            if (elementInView(el, 1.25)) {
                displayScrollElement(el);
            }
        });
    };

    window.addEventListener('scroll', handleScrollAnimation);
    // Run on load to catch elements already in view
    handleScrollAnimation();
});
