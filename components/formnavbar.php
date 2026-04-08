<?php
// website Visitor count logic
$visitor_count = 0;

if ($conn && !$conn->connect_error) {
    $conn->set_charset("utf8mb4");

    $count_query = "SELECT COUNT(*) AS total_visitors FROM visitors_log";
    $count_result = $conn->query($count_query);

    if ($count_result) {
        $count_row = $count_result->fetch_assoc();
        $visitor_count = $count_row['total_visitors'] ?? 0;
    }
}
// components/navbar.php
// Navbar with search magnifier on desktop (homepage only) – no changes to mobile view

// Detect if current page is index.php (homepage)
$current_script = basename($_SERVER['SCRIPT_NAME']);
$show_search = ($current_script === 'index.php');
?>

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
        /* color: var(--orange-color); */
        color:black;
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
        /* background: linear-gradient(135deg, #fff, var(--light-orange)); */
         background:#f7f7f7;
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
    
    /* Search icon – attractive orange gradient (matches size) */
    .social-search {
        background: linear-gradient(135deg, #f39c12, #e67e22);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(243, 156, 18, 0.4);
    }
    .social-search:hover {
        background: linear-gradient(135deg, #e67e22, #d35400);
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
    
    /* Right Logo – add small right margin to prevent cropping */
    .right-logo {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin: 0 !important;
        margin-right: 5px !important; /* added to give space on the right */
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
        
        /* Row 1 - MOBILE - Logo + First line combined */
        .row-1 {
            padding: 6px 0;
            background: linear-gradient(to right, var(--very-light-orange), #ffedd5, var(--very-light-orange));
            border-bottom: 1px solid #fed7aa;
        }
        
        .heading-container {
            flex-direction: column;
            text-align: center;
        }
        
        /* Mobile first row - logo + text in same line */
        .mobile-first-row {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-bottom: 4px;
        }
        
        .emblem-logo {
            height: 30px;
            margin-right: 8px;
            margin-bottom: 0;
        }
        
        .mobile-heading-line-2 {
            font-weight: 700;
            color: #333;
            font-size: 0.85rem;
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
            /* background: linear-gradient(135deg, #fff, var(--light-orange)); */
             background:#f7f7f7;
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
            margin-right: 5px !important; /* keep same margin on mobile */
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
        
        /* Hide the entire right-content (social icons + magnifier) on mobile */
        .right-content {
            display: none !important;
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
            margin-right: 6px;
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
            margin-right: 5px;
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
        
        .emblem-logo {
            height: 24px;
            margin-right: 4px;
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
        
        .logo-container {
            height: 30px;
        }
    }

    /* Search Overlay – responsive modal */
    .search-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    .search-overlay.active {
        display: flex;
    }
    .search-overlay-content {
        background: white;
        width: 90%;
        max-width: 500px;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .search-header h5 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    .close-overlay {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #666;
        line-height: 1;
    }
    .close-overlay:hover {
        color: #000;
    }
    .search-body input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }
    .search-body button {
        width: 100%;
        padding: 10px;
        background: #f97316;
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .search-body button:hover {
        background: #d35400;
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
                        <!-- Desktop View with Logo -->
                        <div class="d-none d-md-flex align-items-center justify-content-center w-100">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Emblem_of_India.svg/200px-Emblem_of_India.svg.png" 
                                 alt="National Emblem of India" 
                                 class="emblem-logo img-fluid">
                            <h2 class="main-heading mb-0">
                                महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी <span class="amrut-orange">(अमृत)</span> - महाराष्ट्र शासनाची स्वायत्त संस्था
                            </h2>
                        </div>
                        
                        <!-- Mobile Heading (2 lines with logo in first line) -->
                        <div class="d-md-none mobile-heading-wrapper">
                            <!-- First line: Logo + Main text combined -->
                            <div class="mobile-first-row">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Emblem_of_India.svg/200px-Emblem_of_India.svg.png" 
                                     alt="National Emblem of India" 
                                     class="emblem-logo img-fluid">
                                <div class="mobile-heading-line-2">
                                    महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी <span class="amrut-orange">(अमृत)</span>
                                </div>
                            </div>
                            <!-- Second line: Secondary text -->
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
   
</nav>

<?php if ($show_search): ?>
<!-- Search Overlay (hidden by default) – triggered only by desktop magnifier -->
<div id="searchOverlay" class="search-overlay">
    <div class="search-overlay-content">
        <div class="search-header">
            <h5>शोधा</h5>
            <span class="close-overlay">&times;</span>
        </div>
        <div class="search-body">
            <input type="text" id="searchInput" class="form-control" placeholder="कीवर्ड टाइप करा...">
            <button id="searchButton" class="btn btn-primary mt-2">शोधा</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const magnifier = document.getElementById('searchMagnifier');
    const overlay = document.getElementById('searchOverlay');
    const closeBtn = document.querySelector('.close-overlay');
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');

    // Functions
    function openSearch() {
        overlay.classList.add('active');
        if (searchInput) searchInput.focus();
    }

    function closeSearch() {
        overlay.classList.remove('active');
    }

    function performSearch() {
        const query = searchInput.value.trim();
        if (query !== '') {
            window.location.href = 'search.php?q=' + encodeURIComponent(query);
        }
    }

    // Make functions globally accessible so they can be called from index.php
    window.openSearch = openSearch;
    window.closeSearch = closeSearch;
    window.performSearch = performSearch;

    // Event listeners
    if (magnifier) {
        magnifier.addEventListener('click', openSearch);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSearch);
    }

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeSearch();
        }
    });

    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});
</script>
<?php endif; ?>