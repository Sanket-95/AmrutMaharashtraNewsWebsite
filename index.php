<?php
include 'components/header.php';
include 'components/navbar.php';
include 'components/db_config.php';

// Fetch top news for carousel
$top_news_query = "SELECT 
    news_id,
    title,
    cover_photo_url,
    summary,
    published_by,
    published_date
FROM news_articles
WHERE category_name = 'today_special'
ORDER BY published_date DESC
LIMIT 10";

$top_news_result = $conn->query($top_news_query);

// Initialize array for top news
$top_news = [];
if ($top_news_result && $top_news_result->num_rows > 0) {
    while($row = $top_news_result->fetch_assoc()) {
        $top_news[] = $row;
    }
}


// Define categories array - WITHOUT about_us
$categories = [
    ['label' => 'मुख्यपृष्ठ', 'value' => 'home'],
    ['label' => 'अमृत घडामोडी', 'value' => 'amrut_events'],
    ['label' => 'लाभार्थी स्टोरी', 'value' => 'beneficiary_story'],
    ['label' => 'दिनविशेष', 'value' => 'today_special'],
    ['label' => 'यशस्वी उद्योजक', 'value' => 'successful_entrepreneur'],
    ['label' => 'शब्दांमृत', 'value' => 'words_amrut'],
    ['label' => 'स्मार्ट शेतकरी', 'value' => 'smart_farmer'],
    ['label' => 'सक्षम विद्यार्थी', 'value' => 'capable_student'],
    ['label' => 'अध्यात्म', 'value' => 'spirituality'],
    ['label' => 'सामाजिक परिवर्तक', 'value' => 'social_situation'],
    ['label' => 'स्त्रीशक्ती', 'value' => 'women_power'],
    ['label' => 'पर्यटन', 'value' => 'tourism'],
    ['label' => 'अमृत सेवा कार्य', 'value' => 'amrut_service']
    // Removed about_us from array - will be added manually
];
?>

<style>
/* Categories Navbar Styles Only - WITH LIGHTER ORANGE THEME */
.categories-navbar {
    border-top: 1px solid #fed7aa;
    border-bottom: 2px solid #fed7aa;
    background: #fffaf0; /* Lighter orange shade */
    position: sticky;
    top: 120px; /* CHANGED: Position below navbar */
    z-index: 900; /* CHANGED: Lower than navbar z-index */
}

.categories-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    flex-wrap: nowrap;
    overflow-x: auto;
}

.category-link {
    color: #7c2d12;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 10px;
    position: relative;
    transition: all 0.3s ease;
    font-size: 14px;
    display: inline-block;
    flex-shrink: 0;
    white-space: nowrap;
    margin: 0 4px;
    cursor: pointer;
    border-radius: 4px;
}

.category-link.active {
    color: #c2410c;
    font-weight: 600;
    background-color: rgba(249, 115, 22, 0.08); /* Lighter background */
}

.category-link.active:after,
.category-link:hover:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 10%;
    width: 80%;
    height: 2px;
    background-color: #f97316;
    transform-origin: center;
    animation: underlineAnimation 0.3s ease forwards;
}

.category-link:hover {
    color: #ea580c;
    background-color: rgba(249, 115, 22, 0.03); /* Lighter hover */
    transform: translateY(-1px);
}

.category-link:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 10%;
    width: 0;
    height: 2px;
    background-color: #f97316;
    transition: width 0.3s ease;
}

.category-link:hover:after {
    width: 80%;
}

.contact-btn {
    background: linear-gradient(135deg, #fb923c, #f97316);
    border: 1px solid #fb923c;
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 7px 14px;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    white-space: nowrap;
    margin-left: 12px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.2);
}

.contact-btn i {
    margin-right: 6px;
    color: white;
    transition: all 0.3s ease;
}

.contact-btn:hover {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    border-color: #f97316;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(249, 115, 22, 0.3);
}

.contact-btn:hover i {
    color: white;
}

.mobile-contact-btn {
    display: none;
    background: linear-gradient(135deg, #fb923c, #f97316);
    color: white;
    border: none;
    padding: 12px 15px;
    border-radius: 6px;
    margin-top: 10px;
    width: 100%;
    text-align: center;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.2);
}

.mobile-contact-btn i {
    margin-right: 8px;
}

@keyframes underlineAnimation {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}

/* HAMBURGER MENU STYLES - TEXT REMOVED, ONLY ICON */
.mobile-categories-toggle {
    display: none;
    background: #fffaf0; /* Same lighter orange as navbar */
    border: 1px solid #fed7aa;
    color: #7c2d12;
    font-size: 16px;
    cursor: pointer;
    padding: 10px 12px;
    width: 50px;
    height: 50px;
    font-weight: 600;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    align-items: center;
    justify-content: center;
    margin: 5px auto;
}

.hamburger-icon {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 24px;
    height: 20px;
}

.hamburger-icon span {
    display: block;
    height: 3px;
    width: 100%;
    background-color: #c2410c; /* Orange color */
    border-radius: 2px;
    transition: all 0.3s ease;
}

.hamburger-icon span:nth-child(1) {
    transform-origin: top left;
}

.hamburger-icon span:nth-child(2) {
    transform-origin: center;
}

.hamburger-icon span:nth-child(3) {
    transform-origin: bottom left;
}

.mobile-categories-toggle.active .hamburger-icon span:nth-child(1) {
    transform: rotate(45deg) translate(5px, -1px);
}

.mobile-categories-toggle.active .hamburger-icon span:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}

.mobile-categories-toggle.active .hamburger-icon span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, 1px);
}

.mobile-categories-menu {
    display: none;
    background: white;
    border-top: 2px solid #fed7aa;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mobile-categories-menu .category-link {
    display: block;
    padding: 12px 15px;
    border-bottom: 1px solid #fed7aa;
    white-space: normal;
    font-size: 15px;
    margin: 0;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.mobile-categories-menu .category-link:last-child {
    border-bottom: none;
}

.mobile-categories-menu .category-link:hover {
    background-color: rgba(249, 115, 22, 0.05); /* Lighter hover */
    transform: translateX(5px);
}

.mobile-categories-menu .category-link.active {
    background-color: rgba(249, 115, 22, 0.1); /* Lighter active */
    border-left: 4px solid #f97316;
}

/* Responsive adjustments for top value */
@media (max-width: 768px) {
    .categories-navbar {
        top: 110px; /* Smaller navbar height on mobile */
        background: #fffaf0; /* Same lighter orange */
        padding: 5px 0;
    }
    .categories-container { display: none; }
    .contact-btn { display: none; }
    .mobile-contact-btn { display: inline-flex; }
    .mobile-categories-toggle { 
        display: flex;
    }
}

@media (max-width: 576px) {
    .categories-navbar {
        top: 100px; /* Even smaller navbar on mobile */
    }
    .mobile-categories-toggle {
        padding: 8px 10px;
        width: 45px;
        height: 45px;
    }
    .hamburger-icon {
        width: 22px;
        height: 18px;
    }
}

@media (min-width: 769px) {
    .mobile-categories-menu { display: none !important; }
    .mobile-contact-btn { display: none !important; }
    .mobile-categories-toggle { display: none !important; }
}

/* DARKER GRAY BACKGROUND FOR DYNAMIC CATEGORY SECTIONS */
.category-section {
    background-color: #f1f5f9 !important; /* Darker gray */
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.category-section .section-header {
    border-bottom: 2px solid #cbd5e1;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.category-section .section-title {
    color: #334155;
    font-weight: 700;
}

.category-section .news-item {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.category-section .news-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #cbd5e1;
}
</style>

<!-- Categories Navigation -->
<div class="categories-navbar">
    <div class="container-fluid px-3">
        <div class="categories-container d-none d-md-flex">
            <?php foreach ($categories as $index => $category): ?>
                <a href="javascript:void(0);" 
                   class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                   data-category="<?php echo htmlspecialchars($category['value']); ?>">
                    <?php echo htmlspecialchars($category['label']); ?>
                </a>
            <?php endforeach; ?>
            <!-- MANUALLY ADDED: About Us link with redirect -->
            <a href="about_us.php" class="category-link">
                आमच्या विषयी
            </a>
            <a href="javascript:void(0);" class="contact-btn">
                <i class="bi bi-bell"></i> संपर्क साधा
            </a>
        </div>
        <!-- HAMBURGER BUTTON - NO TEXT, ONLY ICON -->
        <button class="mobile-categories-toggle d-md-none" id="mobileCategoriesToggle" title="श्रेण्या दाखवा/लपवा" aria-label="श्रेण्या मेनू">
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <div class="mobile-categories-menu" id="mobileCategoriesMenu">
            <?php foreach ($categories as $index => $category): ?>
                <a href="javascript:void(0);" 
                   class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                   data-category="<?php echo htmlspecialchars($category['value']); ?>">
                    <?php echo htmlspecialchars($category['label']); ?>
                </a>
            <?php endforeach; ?>
            <!-- MANUALLY ADDED: About Us link with redirect -->
            <a href="about_us.php" class="category-link">
                आमच्या विषयी
            </a>
            <a href="javascript:void(0);" class="mobile-contact-btn">
                <i class="bi bi-bell"></i> संपर्क साधा
            </a>
        </div>
    </div>
</div>

<main class="container mt-4">
    <!-- Include Top News Carousel Component -->
    <?php 
    // Pass $top_news to the component
    include 'components/top_news_carousel.php'; 
    ?>
    
    <!-- Include Dynamic Category Sections -->
    <?php include 'components/dynamic_category_sections.php'; ?>
</main>

<script>
// Store categories data (without about_us)
const categoriesData = <?php echo json_encode($categories); ?>;

// Function to filter news based on category
function filterNews(categoryValue, marathiLabel = '', event = null) {
    // Prevent default anchor behavior
    if (event) {
        event.preventDefault();
    }
    
    // Only update active state for links with data-category attribute
    const categoryLinks = document.querySelectorAll('.category-link[data-category]');
    categoryLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-category') === categoryValue) {
            link.classList.add('active');
        }
    });
    
    if (!marathiLabel) {
        const selectedCategory = categoriesData.find(cat => cat.value === categoryValue);
        marathiLabel = selectedCategory ? selectedCategory.label : categoryValue;
    }
    
    console.log('Selected Category:', marathiLabel, 'DB Value:', categoryValue);
    
    // Handle navigation
    if (categoryValue === 'home') {
        // Scroll to top for home
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        // Scroll to dynamic category section
        if (typeof scrollToDynamicCategory === 'function') {
            scrollToDynamicCategory(categoryValue);
        } else {
            // Fallback navigation
            const sectionId = 'category-' + categoryValue;
            const section = document.getElementById(sectionId);
            if (section) {
                const navbar = document.querySelector('.categories-navbar');
                const navbarHeight = navbar ? navbar.offsetHeight : 80;
                const elementPosition = section.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - navbarHeight - 10;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }
    }
    
    // Close mobile menu if open
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    const toggleButton = document.getElementById('mobileCategoriesToggle');
    if (mobileMenu && mobileMenu.style.display === 'block') {
        mobileMenu.style.display = 'none';
        toggleButton.classList.remove('active');
    }
    
    // Return false to prevent default behavior
    return false;
}

function goToContact(event) {
    if (event) event.preventDefault();
    alert('संपर्क पृष्ठावर नेण्यात येत आहे...');
    return false;
}

// Mobile Categories Toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('mobileCategoriesToggle');
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    
    if (toggleButton && mobileMenu) {
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (mobileMenu.style.display === 'block') {
                mobileMenu.style.display = 'none';
                toggleButton.classList.remove('active');
            } else {
                mobileMenu.style.display = 'block';
                toggleButton.classList.add('active');
            }
        });
    }
    
    // Update category link event listeners - ONLY for links with data-category attribute
    document.querySelectorAll('.category-link[data-category]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            const marathiLabel = this.textContent;
            filterNews(categoryValue, marathiLabel, e);
        });
    });
    
    // Update contact button event listeners
    document.querySelectorAll('.contact-btn, .mobile-contact-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            goToContact(e);
        });
    });
    
    document.addEventListener('click', function(event) {
        if (mobileMenu && mobileMenu.style.display === 'block') {
            if (!mobileMenu.contains(event.target) && !toggleButton.contains(event.target)) {
                mobileMenu.style.display = 'none';
                toggleButton.classList.remove('active');
            }
        }
    });
    
    // Set default active category to home
    filterNews('home');
});

// Handle hash changes in URL
window.addEventListener('hashchange', function() {
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'home') {
        // Extract category value from hash (remove 'category-' prefix if present)
        const categoryValue = hash.startsWith('category-') ? hash.substring(9) : hash;
        if (categoryValue && categoriesData.find(cat => cat.value === categoryValue)) {
            setTimeout(() => {
                const categoryLinks = document.querySelectorAll('.category-link[data-category]');
                categoryLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-category') === categoryValue) {
                        link.classList.add('active');
                    }
                });
                
                if (typeof scrollToDynamicCategory === 'function') {
                    scrollToDynamicCategory(categoryValue);
                }
            }, 100);
        }
    }
});

// Handle initial page load with hash
if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'home') {
        const categoryValue = hash.startsWith('category-') ? hash.substring(9) : hash;
        if (categoryValue && categoriesData.find(cat => cat.value === categoryValue)) {
            setTimeout(() => {
                const categoryLinks = document.querySelectorAll('.category-link[data-category]');
                categoryLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-category') === categoryValue) {
                        link.classList.add('active');
                    }
                });
                
                if (typeof scrollToDynamicCategory === 'function') {
                    scrollToDynamicCategory(categoryValue);
                }
            }, 300);
        }
    }
}
</script>

<?php 
$conn->close();
include 'components/footer.php'; 
?>