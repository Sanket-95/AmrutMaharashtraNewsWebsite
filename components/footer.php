<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Column 1: Government Logo/Name -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-logo">
                    <h3 class="text-orange mb-3">
                        <i class="bi bi-buildings-fill"></i> अमृत महाराष्ट्र
                    </h3>
                    <p class="text-light mb-3">
                        शासकीय बातमी व माहिती पोर्टल<br>
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
            
            <!-- Column 2: Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="text-orange mb-3">द्रुत लिंक्स</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-house-door me-2"></i> मुख्यपृष्ठ
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="about.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-info-circle me-2"></i> आमच्या दिशयी
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="contact.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-telephone me-2"></i> संपर्क
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="news.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-newspaper me-2"></i> सर्व बातम्या
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Policies & Terms -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="text-orange mb-3">धोरणे</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="privacy_policy.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-shield-check me-2"></i> गोपनीयता धोरण
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="terms_conditions.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-file-text me-2"></i> अटी व शर्ती
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="disclaimer.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-exclamation-triangle me-2"></i> अस्वीकरण
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="accessibility.php" class="text-white text-decoration-none hover-orange">
                            <i class="bi bi-universal-access me-2"></i> सुलभता
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 4: Contact Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="text-orange mb-3">संपर्क माहिती</h5>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="bi bi-geo-alt text-orange me-2"></i>
                        <span>मंत्रालय भवन, मुंबई, महाराष्ट्र</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-envelope text-orange me-2"></i>
                        <span>contact@amrutmaharashtra.gov.in</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-telephone text-orange me-2"></i>
                        <span>टोल फ्री: 1800-XXX-XXXX</span>
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
                    &copy; <?php echo date('Y'); ?> अमृत महाराष्ट्र सरकार. सर्व हक्क राखीव.
                </p>
                <p class="text-light small">
                    <i class="bi bi-info-circle"></i> ही अधिकृत शासकीय वेबसाइट आहे.
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
                    alert('कृपया सर्व आवश्यक फील्ड भरा.');
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
    });
</script>
</body>
</html> 