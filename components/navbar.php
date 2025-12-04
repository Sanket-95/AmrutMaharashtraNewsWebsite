<!-- components/navbar.php -->
<style>
    /* Custom styles for navbar */
    :root {
        --orange-color: #f97316;
        --dark-orange: #d35400;
        --light-bg: #f8f9fa;
    }
    
    /* Navbar styling */
    .navbar-custom {
        background-color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
    }
    
    /* Row 1 styling - MORE COMPACT */
    .row-1 {
        background: linear-gradient(to right, #f8f9fa, #e9ecef, #f8f9fa);
        padding: 6px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .emblem-logo {
        height: 35px;
        width: auto;
    }
    
    .main-heading {
        font-size: 0.9rem;
        font-weight: 600;
        text-align: center;
        margin: 0;
        line-height: 1.2;
    }
    
    .amrut-orange {
        color: var(--orange-color);
        font-weight: 700;
    }
    
    /* Row 2 styling - MORE COMPACT WITH ORANGE */
    .row-2 {
        padding: 12px 0;
        background-color: white;
        position: relative;
    }
    
    .side-logo {
        height: 50px;
        width: auto;
    }
    
    .center-title {
        color: var(--orange-color);
        font-size: 1.7rem;
        font-weight: 800;
        text-align: center;
        margin: 0;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .subtitle {
        color: #666;
        font-size: 0.9rem;
        text-align: center;
        margin: 2px 0 0 0;
        font-style: italic;
    }
    
    /* ATTRACTIVE RIGHT-END NOTCH */
    .right-notch-container {
        position: absolute;
        right: 20px;
        bottom: -8px;
        z-index: 100;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .notch-label {
        font-size: 0.7rem;
        color: #666;
        background: white;
        padding: 1px 6px;
        border-radius: 10px;
        border: 1px solid #ddd;
        white-space: nowrap;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.3s ease;
    }
    
    .right-notch-container:hover .notch-label {
        opacity: 1;
        transform: translateX(0);
    }
    
    .notch-btn-right {
        background: linear-gradient(135deg, var(--orange-color), var(--dark-orange));
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }
    
    .notch-btn-right:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
    }
    
    .notch-btn-right:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    }
    
    .notch-btn-right:active {
        transform: scale(0.95);
    }
    
    .notch-btn-right .bi {
        font-size: 0.9rem;
        transition: transform 0.3s ease;
    }
    
    .notch-btn-right.collapsed .bi {
        transform: rotate(180deg);
    }
    
    /* Row 3 styling - COMPLETELY HIDABLE */
    .row-3 {
        background-color: var(--light-bg);
        padding: 6px 0;
        border-top: 1px solid #dee2e6;
        border-bottom: 2px solid var(--orange-color);
        position: relative;
        max-height: 50px;
        opacity: 1;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .row-3.collapsed {
        max-height: 0;
        padding: 0;
        opacity: 0;
        border: none;
        margin: 0;
    }
    
    /* Social icons */
    .social-icons-container {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        align-items: center;
        transition: opacity 0.3s ease;
    }
    
    .social-icon {
        color: #555;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .social-icon:hover {
        color: var(--orange-color);
        transform: translateY(-2px);
    }
    
    .social-divider {
        color: #999;
        font-size: 0.8rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .emblem-logo {
            height: 30px;
        }
        
        .main-heading {
            font-size: 0.8rem;
        }
        
        .side-logo {
            height: 40px;
        }
        
        .center-title {
            font-size: 1.4rem;
        }
        
        .subtitle {
            font-size: 0.8rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .social-icons-container {
            justify-content: center;
            margin-top: 5px;
            gap: 10px;
        }
        
        .row-3 {
            padding: 4px 0;
        }
        
        .right-notch-container {
            right: 10px;
            bottom: -6px;
        }
        
        .notch-btn-right {
            width: 24px;
            height: 24px;
        }
        
        .notch-btn-right .bi {
            font-size: 0.8rem;
        }
        
        .notch-label {
            font-size: 0.65rem;
            padding: 1px 5px;
        }
    }
    
    @media (max-width: 576px) {
        .center-title {
            font-size: 1.2rem;
        }
        
        .subtitle {
            font-size: 0.75rem;
        }
        
        .row-1 {
            padding: 4px 0;
        }
        
        .row-2 {
            padding: 8px 0;
        }
        
        .row-3 {
            padding: 3px 0;
        }
        
        .right-notch-container {
            right: 8px;
            bottom: -5px;
        }
        
        .notch-btn-right {
            width: 22px;
            height: 22px;
        }
        
        .notch-btn-right .bi {
            font-size: 0.75rem;
        }
        
        .notch-label {
            font-size: 0.6rem;
            padding: 0 4px;
        }
    }
    
    @media (max-width: 400px) {
        .right-notch-container {
            right: 5px;
        }
        
        .notch-label {
            display: none; /* Hide label on very small screens */
        }
    }
</style>

<!-- Navbar with 3 rows -->
<nav class="navbar-custom">
    <!-- Row 1: National Emblem + Heading -->
    <div class="row-1">
        <div class="container">
            <div class="row align-items-center">
                <!-- Three Lion Emblem (National Emblem of India) -->
                <div class="col-md-2 col-12 logo-container">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Emblem_of_India.svg/200px-Emblem_of_India.svg.png" 
                         alt="National Emblem of India" 
                         class="emblem-logo img-fluid">
                </div>
                
                <!-- Main Heading Text -->
                <div class="col-md-8 col-12 text-center">
                    <h2 class="main-heading">
                        महाराष्ट्र शासन निती शिक्षण पद्घती <span class="amrut-orange">(अमृत)</span> - महाराष्ट्र शासनाची स्वायत्त संस्था  
                    </h2>
                </div>
                
                <!-- Empty column for alignment -->
                <div class="col-md-2 d-none d-md-block"></div>
            </div>
        </div>
    </div>
    
    <!-- Row 2: Left Logo, Center Title with Subtitle, Right Logo -->
    <div class="row-2">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <!-- Left Logo (Indian Flag) -->
                <div class="col-md-3 col-4 text-center text-md-start">
                    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/41/Flag_of_India.svg/320px-Flag_of_India.svg.png" 
                         alt="Indian Flag" 
                         class="side-logo img-fluid">
                </div>
                
                <!-- Center Title with Subtitle -->
                <div class="col-md-6 col-12 text-center">
                    <h1 class="center-title">अमृत महाराष्ट्र</h1>
                    <p class="subtitle">श्रमेव जयते</p>
                </div>
                
                <!-- Right Logo (Maharashtra Emblem) -->
                <div class="col-md-3 col-4 text-center text-md-end">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Seal_of_Maharashtra.svg/240px-Seal_of_Maharashtra.svg.png" 
                         alt="Maharashtra Government Emblem" 
                         class="side-logo img-fluid">
                </div>
            </div>
        </div>
    </div>
    
    <!-- ATTRACTIVE RIGHT-END NOTCH -->
    <div class="right-notch-container">
        <span class="notch-label" id="notchLabel">Toggle Social</span>
        <button class="notch-btn-right" id="notchBtn" title="Toggle social media row">
            <i class="bi bi-chevron-up"></i>
        </button>
    </div>
    
    <!-- Row 3: Hidable Row with Social Icons -->
    <div class="row-3" id="socialRow">
        <!-- Social Media Icons -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="social-icons-container">
                        <a href="#" class="social-icon" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-icon" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-icon" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="#" class="social-icon" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <span class="social-divider ms-1 me-1">|</span>
                        <a href="#" class="social-icon" title="Share">
                            <i class="bi bi-share"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notchBtn = document.getElementById('notchBtn');
    const notchLabel = document.getElementById('notchLabel');
    const socialRow = document.getElementById('socialRow');
    
    // Check localStorage for saved state
    let isCollapsed = localStorage.getItem('socialRowCollapsed') === 'true';
    
    // Initialize state
    function updateUI() {
        if (isCollapsed) {
            socialRow.classList.add('collapsed');
            notchBtn.classList.add('collapsed');
            notchBtn.title = "Show social media row";
            notchLabel.textContent = "Show Social";
        } else {
            socialRow.classList.remove('collapsed');
            notchBtn.classList.remove('collapsed');
            notchBtn.title = "Hide social media row";
            notchLabel.textContent = "Hide Social";
        }
    }
    
    updateUI();
    
    // Toggle function
    function toggleSocialRow() {
        isCollapsed = !isCollapsed;
        localStorage.setItem('socialRowCollapsed', isCollapsed);
        updateUI();
    }
    
    // Add click event to notch button
    notchBtn.addEventListener('click', toggleSocialRow);
    
    // Accessibility: Add keyboard support
    notchBtn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleSocialRow();
        }
    });
    
    // Initialize ARIA attributes
    notchBtn.setAttribute('aria-label', 'Toggle social media row');
    notchBtn.setAttribute('aria-expanded', !isCollapsed);
    notchBtn.setAttribute('aria-controls', 'socialRow');
    
    // Add hover effect for label
    const rightNotchContainer = document.querySelector('.right-notch-container');
    rightNotchContainer.addEventListener('mouseenter', function() {
        notchLabel.style.opacity = '1';
        notchLabel.style.transform = 'translateX(0)';
    });
    
    rightNotchContainer.addEventListener('mouseleave', function() {
        notchLabel.style.opacity = '0';
        notchLabel.style.transform = 'translateX(10px)';
    });
});
</script>