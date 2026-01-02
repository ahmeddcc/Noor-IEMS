        </div> <!-- End content-wrapper -->
    </main> <!-- End main-content -->
</div> <!-- End app-container -->

<script src="<?php echo asset('js/main.js'); ?>"></script>
<!-- Instant.page - Preloads pages on hover for instant navigation -->
<script src="<?php echo asset('js/instant.page.min.js'); ?>" type="module" defer></script>

<!-- Mobile Menu Toggle Script -->
<script>
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const btn = document.getElementById('mobileMenuToggle');
    const body = document.body;
    
    nav.classList.toggle('mobile-open');
    btn.classList.toggle('active');
    
    // Toggle body scroll
    if (nav.classList.contains('mobile-open')) {
        body.style.overflow = 'hidden';
        
        // Create overlay if doesn't exist
        if (!document.querySelector('.mobile-menu-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'mobile-menu-overlay active';
            overlay.onclick = toggleMobileMenu;
            document.body.appendChild(overlay);
        } else {
            document.querySelector('.mobile-menu-overlay').classList.add('active');
        }
    } else {
        body.style.overflow = '';
        const overlay = document.querySelector('.mobile-menu-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        }
    }
}

// Close menu on link click
document.querySelectorAll('.nav-section .nav-item').forEach(item => {
    item.addEventListener('click', function() {
        const nav = document.getElementById('mainNav');
        if (nav.classList.contains('mobile-open')) {
            toggleMobileMenu();
        }
    });
});

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const nav = document.getElementById('mainNav');
        if (nav.classList.contains('mobile-open')) {
            toggleMobileMenu();
        }
    }
});
</script>


</body>
</html>
