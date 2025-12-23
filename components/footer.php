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
            
            <!-- Column 3: Policies & Terms - REMOVED -->
            <div class="col-lg-2 col-md-6 mb-4">
                <!-- Empty column for layout consistency -->
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
    }
</style>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS (optional) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Active nav link highlighting
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
        
        // Current year for copyright
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        
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
    });
</script>
</body>
</html>