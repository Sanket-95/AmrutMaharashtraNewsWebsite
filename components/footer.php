<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Column 1: Government Logo/Name -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-logo">
                    <h3 class="text-orange mb-3">
                        <i class="bi bi-buildings-fill"></i> AMRUT Maharashtra
                    </h3>
                    <p class="text-light mb-3">
                        Positive News & Information Portal<br>
                        Official News & Information Portal
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3" title="Facebook">
                            <i class="bi bi-facebook fs-5"></i>
                        </a>
                        <a href="#" class="text-white me-3" title="Twitter">
                            <i class="bi bi-twitter fs-5"></i>
                        </a>
                        <a href="#" class="text-white me-3" title="Instagram">
                            <i class="bi bi-instagram fs-5"></i>
                        </a>
                        <a href="#" class="text-white" title="YouTube">
                            <i class="bi bi-youtube fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Column 2: Quick Links - Home Page and About Us -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="text-orange mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-house-door me-2"></i> Home Page
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="about_us.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-info-circle me-2"></i> About Us
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Visitor Stats with Animated Counter -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="text-orange mb-3">Visitor Stats</h5>
                <ul class="list-unstyled">
                    <li class="mb-3 visitor-stats-item" id="visitorStatsItem">
                        <i class="bi bi-people-fill text-orange me-2"></i>
                        <span>Total Visitors: 
                            <span class="visitor-count" id="visitorCounter">0</span>
                        </span>
                    </li>
                </ul>
            </div>
            
            <!-- Column 4: Contact Info - UPDATED -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="text-orange mb-3">Contact Information</h5>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="bi bi-envelope text-orange me-2"></i>
                        <span>Email:  amrutmaharashtraorg@gmail.com</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-phone text-orange me-2"></i>
                        <span>Mobile: +91 9112226524</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-globe text-orange me-2"></i>
                        <span>Website: https://amrutmaharashtra.org/</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-geo-alt text-orange me-2"></i>
                        <span>Location: AMRUT Building, near Bhimsen Joshi Sabhagruh, Ward No. 8, PMRDA,<br>
                              Aundh, Pune, Maharashtra 411067</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Divider -->
        <hr class="border-orange my-4">
        
        <!-- Copyright & Additional Links -->
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> Amrut Maharashtra Government. All rights reserved.
                </p>
                <p class="text-light small">
                    <i class="bi bi-info-circle"></i> This is an official government website.
                </p>
            </div>
           
        </div>
    </div>
</footer>

<style>
    .text-orange {
        color: #FF6600 !important;
    }
    
    .bg-orange {
        background-color: #FF6600 !important;
    }
    
    .border-orange {
        border-color: #FF6600 !important;
    }
    
    .hover-orange:hover {
        color: #FF6600 !important;
        padding-left: 5px;
        transition: all 0.3s ease;
    }
    
    .footer-logo h3 {
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    .social-links a {
        display: inline-block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }
    
    .social-links a:hover {
        background: #FF6600;
        transform: translateY(-3px);
    }
    
    .footer-badges .badge {
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .footer-badges .badge:hover {
        opacity: 0.9;
        cursor: default;
    }
    
    /* Location text styling */
    ul.list-unstyled li span {
        display: inline-block;
        vertical-align: top;
        width: calc(100% - 30px);
    }
    
    /* Visitor stats styling */
    .visitor-stats-item {
        background: rgba(255, 102, 0, 0.1);
        border-radius: 8px;
        padding: 8px 12px !important;
        transition: all 0.3s ease;
    }
    
    .visitor-stats-item:hover {
        background: rgba(255, 102, 0, 0.2);
        transform: translateX(5px);
    }
    
    /* Counter animation styles */
    .visitor-count {
        display: inline-block;
        font-weight: bold;
        color: #FF6600;
        font-size: 1.1rem;
        min-width: 60px;
        text-align: right;
    }
    
    /* Pulse animation when count completes */
    @keyframes countPulse {
        0% {
            transform: scale(1);
            color: #FF6600;
        }
        50% {
            transform: scale(1.15);
            color: #ffaa33;
        }
        100% {
            transform: scale(1);
            color: #FF6600;
        }
    }
    
    .count-animate {
        animation: countPulse 0.6s ease-in-out;
    }
    
    /* Shine effect on counter */
    @keyframes shine {
        0% {
            text-shadow: 0 0 0px rgba(255, 102, 0, 0);
        }
        50% {
            text-shadow: 0 0 8px rgba(255, 102, 0, 0.6);
        }
        100% {
            text-shadow: 0 0 0px rgba(255, 102, 0, 0);
        }
    }
    
    .shine-effect {
        animation: shine 0.8s ease-in-out;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer-logo h3 {
            font-size: 1.3rem;
        }
        
        .text-md-end {
            text-align: left !important;
            margin-top: 15px;
        }
        
        .social-links a {
            width: 36px;
            height: 36px;
            line-height: 36px;
            font-size: 0.9rem;
        }
        
        /* Adjust location text on mobile */
        ul.list-unstyled li span {
            width: calc(100% - 25px);
            font-size: 0.9rem;
        }
        
        /* Visitor stats on mobile */
        .col-lg-2.col-md-6.mb-4 {
            margin-top: 10px;
        }
        
        .visitor-count {
            font-size: 1rem;
            min-width: 50px;
        }
    }

    /* WhatsApp Floating Button */
    .whatsapp-float {
        position: fixed !important;
        bottom: 20px !important;
        right: 20px !important;
        width: 56px;
        height: 56px;
        background-color: #25D366 !important;
        color: #fff !important;
        border-radius: 50%;
        display: flex !important;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        z-index: 99999; /* above everything */
        box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        text-decoration: none !important;
    }
    
    .whatsapp-float i {
        color: #fff !important;
    }
    
    .whatsapp-float:hover {
        transform: scale(1.05);
        color: #fff;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .whatsapp-float {
            bottom: 15px !important;
            right: 15px !important;
            width: 48px;
            height: 48px;
            font-size: 24px;
        }
    }
</style>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================
        // VISITOR COUNTER ANIMATION ON SCROLL TO BOTTOM
        // ============================================
        
        // Get the actual visitor count from PHP (passed as variable)
        // This should be set in your PHP before including this footer
        const targetCount = <?php echo isset($visitor_count) ? intval($visitor_count) : 12458; ?>;
        const counterElement = document.getElementById('visitorCounter');
        
        // Flag to track if animation has been triggered
        let animationTriggered = false;
        let animationInProgress = false;
        
        // Function to animate the counter
        function animateCounter(targetNumber) {
            if (animationInProgress) return;
            animationInProgress = true;
            
            let currentNumber = 0;
            const duration = 2000; // Animation duration in milliseconds
            const steps = 60; // Number of steps for smooth animation
            const increment = targetNumber / steps;
            let step = 0;
            
            // Add shine effect to the counter container
            const statsItem = document.getElementById('visitorStatsItem');
            if (statsItem) {
                statsItem.classList.add('shine-effect');
                setTimeout(() => {
                    statsItem.classList.remove('shine-effect');
                }, 800);
            }
            
            const timer = setInterval(() => {
                step++;
                currentNumber = Math.min(Math.ceil(increment * step), targetNumber);
                counterElement.textContent = currentNumber.toLocaleString('en-IN');
                
                if (step >= steps) {
                    counterElement.textContent = targetNumber.toLocaleString('en-IN');
                    clearInterval(timer);
                    animationInProgress = false;
                    
                    // Add pulse animation when counting completes
                    counterElement.classList.add('count-animate');
                    setTimeout(() => {
                        counterElement.classList.remove('count-animate');
                    }, 600);
                }
            }, duration / steps);
        }
        
        // Function to check if footer/visitor stats is in viewport
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            const windowHeight = window.innerHeight || document.documentElement.clientHeight;
            
            // Element is considered visible when it's in the viewport or near the bottom
            // Allow some offset to trigger before fully visible
            const offset = 100;
            return rect.top <= windowHeight - offset && rect.bottom >= offset;
        }
        
        // Function to check if user has scrolled near bottom of page
        function isNearBottom() {
            const scrollPosition = window.scrollY + window.innerHeight;
            const pageHeight = document.documentElement.scrollHeight;
            // Trigger when within 200px of bottom OR when footer is visible
            return (pageHeight - scrollPosition) <= 300;
        }
        
        // Function to check and trigger animation
        function checkAndTriggerAnimation() {
            if (animationTriggered) return;
            
            const statsItem = document.getElementById('visitorStatsItem');
            const isVisible = statsItem && isElementInViewport(statsItem);
            const nearBottom = isNearBottom();
            
            if (isVisible || nearBottom) {
                animationTriggered = true;
                animateCounter(targetCount);
                // Remove scroll listener once triggered
                window.removeEventListener('scroll', checkAndTriggerAnimation);
                window.removeEventListener('resize', checkAndTriggerAnimation);
                window.removeEventListener('touchmove', checkAndTriggerAnimation);
            }
        }
        
        // Initialize counter with 0 initially
        if (counterElement) {
            counterElement.textContent = '0';
        }
        
        // Add scroll listeners
        window.addEventListener('scroll', checkAndTriggerAnimation);
        window.addEventListener('resize', checkAndTriggerAnimation);
        window.addEventListener('touchmove', checkAndTriggerAnimation);
        
        // Also trigger on initial load if footer is already visible
        setTimeout(checkAndTriggerAnimation, 500);
        
        // ============================================
        // EXISTING FUNCTIONALITY (PRESERVED)
        // ============================================
        
        // Active nav link highlighting
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
        
        // Current year for copyright
        const yearElement = document.getElementById('currentYear');
        if (yearElement) {
            yearElement.textContent = new Date().getFullYear();
        }
        
        // Smooth scroll to top
        const backToTop = document.querySelector('.back-to-top');
        if (backToTop) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTop.style.display = 'flex';
                } else {
                    backToTop.style.display = 'none';
                }
            });
            
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Form validation for contact forms in footer
        const footerForms = document.querySelectorAll('footer form');
        footerForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input[required], textarea[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill all required fields.');
                }
            });
        });
        
        // Add hover effect to all links    
        const footerLinks = document.querySelectorAll('footer a');
        footerLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });
        
        // Make website links clickable
        document.querySelectorAll('footer span').forEach(span => {
            if (span.textContent.includes('http')) {
                const text = span.textContent;
                const url = text.substring(text.indexOf('http'));
                const displayText = text.substring(0, text.indexOf('http'));
                
                span.innerHTML = displayText + 
                    '<a href="' + url + '" class="text-white text-decoration-underline" target="_blank" rel="noopener noreferrer">' + 
                    url + '</a>';
            }
        });
        
        // Add hover effect to visitor stats item
        const visitorStatsItem = document.getElementById('visitorStatsItem');
        if (visitorStatsItem) {
            visitorStatsItem.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            visitorStatsItem.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        }
    });
</script>

<!-- WhatsApp Floating Button -->
<?php if (!empty($showWhatsapp) && $showWhatsapp === true): ?>
    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/919112226524"
       class="whatsapp-float"
       target="_blank"
       aria-label="Chat on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
<?php endif; ?>

</body>
</html>