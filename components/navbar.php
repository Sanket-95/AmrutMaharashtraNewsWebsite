<!-- Navbar Component -->
<style>
    /* Custom styles for navbar */
    :root {
        --orange-color: #f97316;
        --dark-orange: #d35400;
        --light-orange: #ffedd5;
        --very-light-orange: #fff7ed;
        --light-bg: #f8f9fa;
    }
    
    /* Navbar styling - STICKY TO TOP */
    .navbar-custom {
        background-color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
    }
    
    /* Container with small left margin only */
    .navbar-custom .container,
    .navbar-custom .container-fluid {
        padding-left: 10px !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    /* Row with no margins */
    .navbar-custom .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        --bs-gutter-x: 0 !important;
    }
    
    /* Column padding - minimal left, no right */
    .navbar-custom .col,
    .navbar-custom [class*="col-"] {
        padding-left: 5px !important;
        padding-right: 0 !important;
    }
    
    /* Row 1 styling - ADDED ORANGE-THEMED BACKGROUND */
    .row-1 {
        background: linear-gradient(to right, var(--very-light-orange), #ffe4cc, var(--very-light-orange));
        padding: 8px 0;
        border-bottom: 1px solid #fecba1;
        margin: 0;
        width: 100%;
    }
    
    .row-1 .container-fluid {
        width: 100%;
        max-width: 100%;
    }
    
    .emblem-logo {
        height: 35px;
        width: auto;
        margin-right: 10px;
        object-fit: contain;
    }
    
    .main-heading {
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
        display: inline;
    }
    
    /* NEW: Separate heading parts for better mobile control */
    .heading-main-part {
        font-weight: 700;
        color: #333;
    }
    
    .heading-secondary-part {
        font-weight: 500;
        color: #555;
    }
    
    .amrut-orange {
        color: var(--orange-color);
        font-weight: 700;
    }
    
    .heading-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        padding: 0;
        text-align: center;
        width: 100%;
    }
    
    /* Row 2 styling - HORIZONTAL LAYOUT - ADDED LIGHT ORANGE BACKGROUND */
    .row-2 {
        padding: 12px 0 12px 0;
        background: linear-gradient(135deg, #fff, var(--light-orange));
        position: relative;
        margin: 0;
        width: 100%;
        border-bottom: 2px solid #fed7aa;
    }
    
    .row-2 .container-fluid {
        width: 100%;
        max-width: 100%;
        padding-right: 0 !important;
    }
    
    .side-logo {
        width: auto;
        margin: 0;
        display: block;
        filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.1));
        object-fit: contain;
    }
    
    /* Left Logo - SMALL LEFT MARGIN */
    .left-logo {
        padding-left: 5px !important;
        padding-right: 0 !important;
        margin: 0 !important;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 60px; /* Fixed height for consistent alignment */
    }
    
    .left-logo img {
        margin-left: 0 !important;
        max-height: 100%;
        max-width: 100%;
        height: auto;
        width: auto;
    }
    
    /* Custom logo container for consistent sizing */
    .logo-container {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 60px; /* Fixed height */
        width: 100%;
    }
    
    .left-logo-container {
        justify-content: flex-start;
    }
    
    .right-logo-container {
        justify-content: flex-end;
    }
    
    /* Center Content - JUST TEXT NOW */
    .center-content {
        padding-left: 5px !important;
        padding-right: 5px !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    
    .center-title {
        color: var(--orange-color);
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        line-height: 1.1;
    }
    
    .subtitle {
        color: #c2410c;
        font-size: 0.85rem;
        margin: 2px 0 0 0;
        font-style: italic;
        font-weight: 500;
    }
    
    /* Social Media Icons Container - HORIZONTAL GROUP */
    .social-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        padding-right: 8px;
    }
    
    .social-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid white;
    }
    
    .social-circle:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
    }
    
    .social-circle:hover {
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Different colors for each social media */
    .social-fb {
        background: linear-gradient(135deg, #1877F2, #0d5cb6);
    }
    
    .social-tw {
        background: linear-gradient(135deg, #1DA1F2, #0c85d0);
    }
    
    .social-ig {
        background: linear-gradient(135deg, #E4405F, #c13584);
    }
    
    .social-yt {
        background: linear-gradient(135deg, #FF0000, #cc0000);
    }
    
    .social-in {
        background: linear-gradient(135deg, #0A66C2, #004182);
    }
    
    /* Hover color changes */
    .social-fb:hover {
        background: linear-gradient(135deg, #0d5cb6, #1877F2);
    }
    
    .social-tw:hover {
        background: linear-gradient(135deg, #0c85d0, #1DA1F2);
    }
    
    .social-ig:hover {
        background: linear-gradient(135deg, #c13584, #E4405F);
    }
    
    .social-yt:hover {
        background: linear-gradient(135deg, #cc0000, #FF0000);
    }
    
    .social-in:hover {
        background: linear-gradient(135deg, #004182, #0A66C2);
    }
    
    /* Right Content - SOCIAL ICONS + LOGO IN SAME COLUMN */
    .right-content {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        height: 100%;
    }
    
    /* Right Logo */
    .right-logo {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 60px; /* Fixed height for consistent alignment */
    }
    
    .right-logo img {
        margin-right: 0 !important;
        max-height: 100%;
        max-width: 100%;
        height: auto;
        width: auto;
        object-fit: contain;
    }
    
    /* MOBILE VIEW STYLES */
    @media (max-width: 768px) {
        .navbar-custom {
            position: sticky;
            top: 0;
        }
        
        .navbar-custom .container,
        .navbar-custom .container-fluid {
            padding-left: 8px !important;
            padding-right: 0 !important;
        }
        
        .navbar-custom .col,
        .navbar-custom [class*="col-"] {
            padding-left: 4px !important;
            padding-right: 0 !important;
        }
        
        /* Row 1 - MOBILE - IMPROVED TEXT WRAPPING */
        .row-1 {
            padding: 6px 0;
            background: linear-gradient(to right, var(--very-light-orange), #ffedd5, var(--very-light-orange));
            border-bottom: 1px solid #fed7aa;
        }
        
        .heading-container {
            flex-direction: column;
            text-align: center;
        }
        
        .emblem-logo {
            margin-right: 0;
            margin-bottom: 8px;
            height: 30px;
        }
        
        /* Mobile: Split heading into 3 lines */
        .mobile-heading-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        
        .mobile-heading-line-1 {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }
        
        .mobile-heading-line-2 {
            font-weight: 700;
            color: #333;
            font-size: 0.85rem;
            margin-bottom: 3px;
            line-height: 1.2;
            text-align: center;
        }
        
        .mobile-heading-line-3 {
            font-weight: 500;
            color: #555;
            font-size: 0.8rem;
            line-height: 1.2;
            text-align: center;
        }
        
        /* Hide desktop heading on mobile */
        .main-heading {
            display: none;
        }
        
        /* Row 2 - MOBILE REARRANGEMENT */
        .row-2 {
            padding: 8px 0;
            background: linear-gradient(135deg, #fff, var(--light-orange));
            border-bottom: 2px solid #fed7aa;
        }
        
        .side-logo {
            height: auto;
            max-height: 45px; /* Reduced for mobile */
        }
        
        /* Mobile: First Row - Logos only */
        .mobile-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .left-logo {
            padding-left: 0 !important;
            order: 1;
            flex: 1;
            justify-content: flex-start;
            height: 45px; /* Reduced for mobile */
        }
        
        .left-logo img {
            max-height: 45px;
        }
        
        .right-logo {
            padding-right: 0 !important;
            order: 3;
            flex: 1;
            justify-content: flex-end;
            height: 45px; /* Reduced for mobile */
        }
        
        .right-logo img {
            max-height: 45px;
        }
        
        /* Center content - MOBILE */
        .center-content {
            order: 2;
            flex: 2;
            padding: 0 !important;
        }
        
        .center-title {
            font-size: 1.2rem;
            line-height: 1;
        }
        
        .subtitle {
            font-size: 0.75rem;
            margin-top: 1px;
            color: #c2410c;
        }
        
        /* Social Media - MOBILE: BELOW LOGOS */
        .right-content {
            order: 4;
            width: 100%;
            justify-content: center;
            margin-top: 8px !important;
            gap: 8px;
        }
        
        .social-container {
            gap: 5px;
            padding-right: 0;
            justify-content: center;
        }
        
        .social-circle {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
            border: 2px solid white;
        }
        
        /* Logo container for mobile */
        .logo-container {
            height: 45px; /* Reduced for mobile */
        }
    }
    
    @media (max-width: 576px) {
        .navbar-custom .container,
        .navbar-custom .container-fluid {
            padding-left: 6px !important;
            padding-right: 0 !important;
        }
        
        .navbar-custom .col,
        .navbar-custom [class*="col-"] {
            padding-left: 3px !important;
            padding-right: 0 !important;
        }
        
        /* Mobile heading adjustments */
        .mobile-heading-line-2 {
            font-size: 0.8rem;
        }
        
        .mobile-heading-line-3 {
            font-size: 0.75rem;
        }
        
        .emblem-logo {
            height: 28px;
            margin-bottom: 6px;
        }
        
        .center-title {
            font-size: 1rem;
        }
        
        .subtitle {
            font-size: 0.7rem;
            color: #c2410c;
        }
        
        .side-logo {
            max-height: 40px;
        }
        
        .left-logo {
            height: 40px;
        }
        
        .left-logo img {
            max-height: 40px;
        }
        
        .right-logo {
            height: 40px;
        }
        
        .right-logo img {
            max-height: 40px;
        }
        
        .social-circle {
            width: 30px;
            height: 30px;
            font-size: 0.85rem;
            border: 2px solid white;
        }
        
        .right-content {
            gap: 6px;
            margin-top: 6px !important;
        }
        
        .social-container {
            gap: 4px;
        }
        
        .logo-container {
            height: 40px;
        }
    }
    
    @media (max-width: 400px) {
        .navbar-custom .container,
        .navbar-custom .container-fluid {
            padding-left: 4px !important;
            padding-right: 0 !important;
        }
        
        /* Mobile heading adjustments */
        .mobile-heading-line-2 {
            font-size: 0.75rem;
        }
        
        .mobile-heading-line-3 {
            font-size: 0.7rem;
        }
        
        .emblem-logo {
            height: 26px;
            margin-bottom: 5px;
        }
        
        .center-title {
            font-size: 0.9rem;
        }
        
        .subtitle {
            font-size: 0.65rem;
            color: #c2410c;
        }
        
        .side-logo {
            max-height: 35px;
        }
        
        .left-logo {
            height: 35px;
        }
        
        .left-logo img {
            max-height: 35px;
        }
        
        .right-logo {
            height: 35px;
        }
        
        .right-logo img {
            max-height: 35px;
        }
        
        .social-circle {
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
            border: 2px solid white;
        }
        
        .right-content {
            gap: 4px;
            margin-top: 5px !important;
        }
        
        .social-container {
            gap: 3px;
        }
        
        .logo-container {
            height: 35px;
        }
    }
    
    @media (max-width: 350px) {
        .navbar-custom .container,
        .navbar-custom .container-fluid {
            padding-left: 3px !important;
            padding-right: 0 !important;
        }
        
        /* Mobile heading adjustments */
        .mobile-heading-line-2 {
            font-size: 0.7rem;
        }
        
        .mobile-heading-line-3 {
            font-size: 0.65rem;
        }
        
        .center-title {
            font-size: 0.85rem;
        }
        
        .side-logo {
            max-height: 30px;
        }
        
        .left-logo {
            height: 30px;
        }
        
        .left-logo img {
            max-height: 30px;
        }
        
        .right-logo {
            height: 30px;
        }
        
        .right-logo img {
            max-height: 30px;
        }
        
        .social-circle {
            width: 26px;
            height: 26px;
            font-size: 0.75rem;
            border: 2px solid white;
        }
        
        .social-container {
            gap: 2px;
        }
        
        .logo-container {
            height: 30px;
        }
    }
</style>

<!-- Navbar with 2 rows -->
<nav class="navbar-custom">
    <!-- Row 1: Logo + Heading -->
    <div class="row-1">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="heading-container">
                        <!-- Logo (visible on all devices) -->
                        <div class="mobile-heading-line-1">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Emblem_of_India.svg/200px-Emblem_of_India.svg.png" 
                                 alt="National Emblem of India" 
                                 class="emblem-logo img-fluid">
                        </div>
                        
                        <!-- Desktop Heading (hidden on mobile) -->
                        <h2 class="main-heading d-none d-md-block">
                            महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी <span class="amrut-orange">(अमृत)</span> - महाराष्ट्र शासनाची स्वायत्त संस्था
                        </h2>
                        
                        <!-- Mobile Heading (3 lines, visible only on mobile) -->
                        <div class="d-md-none mobile-heading-wrapper">
                            <!-- Line 2: Main part (bold) -->
                            <div class="mobile-heading-line-2">
                                महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी <span class="amrut-orange">(अमृत)</span>
                            </div>
                            
                            <!-- Line 3: Secondary part (lighter) -->
                            <div class="mobile-heading-line-3">
                                महाराष्ट्र शासनाची स्वायत्त संस्था
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 2: Desktop and Mobile Layouts -->
    <div class="row-2">
        <div class="container-fluid">
            <!-- Desktop View (768px and above) -->
            <div class="d-none d-md-block">
                <div class="row align-items-center justify-content-center">
                    <!-- 1st: Left Logo - Your custom Amrutmh.jpeg -->
                    <div class="col-md-3 left-logo">
                        <div class="logo-container left-logo-container">
                            <img src="components/assets/Amrutmh.jpeg" 
                                 alt="Amrut Maharashtra Left Logo" 
                                 class="side-logo img-fluid">
                        </div>
                    </div>
                    
                    <!-- 2nd: Center Content -->
                    <div class="col-md-6 center-content text-center">
                        <h1 class="center-title">अमृत महाराष्ट्र</h1>
                        <p class="subtitle">श्रमेव जयते</p>
                    </div>
                    
                    <!-- 3rd: Right Content -->
                    <div class="col-md-3 right-content">
                        <div class="social-container">
                            <a href="#" class="social-circle social-fb" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-circle social-tw" title="Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social-circle social-ig" title="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-circle social-yt" title="YouTube">
                                <i class="bi bi-youtube"></i>
                            </a>
                            <a href="#" class="social-circle social-in" title="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                        
                        <!-- Right Logo - Your custom Amrut logo -->
                        <div class="right-logo">
                            <div class="logo-container right-logo-container">
                                <img src="components/assets/Amrut.jpeg" 
                                     alt="Amrut Maharashtra Right Logo" 
                                     class="side-logo img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile View (below 768px) -->
            <div class="d-md-none">
                <!-- Top Row: Left Logo + Center Text + Right Logo -->
                <div class="mobile-top-row">
                    <div class="left-logo">
                        <div class="logo-container left-logo-container">
                            <img src="components/assets/Amrutmh.jpeg" 
                                 alt="Amrut Maharashtra Left Logo" 
                                 class="side-logo img-fluid">
                        </div>
                    </div>
                    
                    <div class="center-content text-center">
                        <h1 class="center-title">अमृत महाराष्ट्र</h1>
                        <p class="subtitle">श्रमेव जयते</p>
                    </div>
                    
                    <div class="right-logo">
                        <div class="logo-container right-logo-container">
                            <img src="components/assets/Amrut.jpeg" 
                                 alt="Amrut Maharashtra Right Logo" 
                                 class="side-logo img-fluid">
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Row: Social Media Icons (Centered) -->
                <div class="right-content">
                    <div class="social-container">
                        <a href="#" class="social-circle social-fb" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-circle social-tw" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-circle social-ig" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-circle social-yt" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="#" class="social-circle social-in" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>