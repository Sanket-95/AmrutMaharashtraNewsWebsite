<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'components/header_fixed.php';
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
WHERE topnews = 1 AND is_approved = 1
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

// Define categories array with groups - FIXED STRUCTURE
$categories = [
    ['label' => 'मुख्य पृष्ठ', 'value' => 'home', 'group' => 'main'],
    // Group header - NOT a real category, just for dropdown
    ['label' => 'अमृत विषयी', 'value' => 'amrut_about_group', 'group' => 'main', 'is_group' => true, 'children' => [
        ['label' => 'आमच्याविषयी', 'value' => 'about_us', 'type' => 'link', 'url' => 'about_us.php'],
        ['label' => 'अमृत घडामोडी', 'value' => 'amrut_events'],
        ['label' => 'लाभार्थी स्टोरी', 'value' => 'beneficiary_story'],
        ['label' => 'ब्लॉग', 'value' => 'blog'],
        ['label' => 'अमृत सेवाकार्य', 'value' => 'amrut_service']
    ]],
    // Regular categories
    ['label' => 'दिनविशेष', 'value' => 'today_special', 'group' => 'content'],
    ['label' => 'यशस्वी उद्योजक', 'value' => 'successful_entrepreneur', 'group' => 'content'],
    ['label' => 'शब्दामृत', 'value' => 'words_amrut', 'group' => 'content'],
    ['label' => 'स्मार्ट शेतकरी', 'value' => 'smart_farmer', 'group' => 'content'],
    ['label' => 'सक्षम विद्यार्थी', 'value' => 'capable_student', 'group' => 'content'],
    ['label' => 'अध्यात्म', 'value' => 'spirituality', 'group' => 'content'],
    ['label' => 'सामाजिक परिवर्तक', 'value' => 'social_situation', 'group' => 'content'],
    ['label' => 'स्त्रीशक्ती', 'value' => 'women_power', 'group' => 'content'],
    ['label' => 'पर्यटन', 'value' => 'tourism', 'group' => 'content']
];

// Create a flattened version of ALL REAL categories (including group children)
$all_real_categories = [];
foreach ($categories as $category) {
    if (isset($category['is_group']) && $category['is_group']) {
        // Add group children as separate REAL categories (except links)
        foreach ($category['children'] as $child) {
            if (!isset($child['type']) || $child['type'] !== 'link') {
                $all_real_categories[] = [
                    'label' => $child['label'],
                    'value' => $child['value'],
                    'is_from_group' => true,
                    'group_name' => $category['label']
                ];
            }
        }
    } else if ($category['value'] !== 'home') {
        // Add regular categories (except home)
        $all_real_categories[] = [
            'label' => $category['label'],
            'value' => $category['value'],
            'is_from_group' => false,
            'group_name' => null
        ];
    }
}

// Debug: Check what real categories we have
error_log("All real categories: " . print_r($all_real_categories, true));
?>

<style>
/* Categories Navbar Styles Only - MODIFIED TO ORANGE BACKGROUND */
.categories-navbar {
    border-top: 1px solid #f97316;
    border-bottom: 2px solid #f97316;
    background: #f97316; /* CHANGED: Orange background */
    position: sticky;
    top: 8.4375rem;
    z-index: 999; /* Increased to ensure dropdown appears on top */
}

.categories-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    flex-wrap: nowrap;
    overflow-x: auto;
    position: relative;
}

.category-link {
    color: #000000; /* CHANGED: Black text color */
    text-decoration: none;
    font-weight: 500;
    padding: 6px 10px;
    position: relative;
    transition: all 0.3s ease;
    font-size: 16px;
    display: inline-block;
    flex-shrink: 0;
    white-space: nowrap;
    margin: 0 4px;
    cursor: pointer;
    border-radius: 4px;
}

.category-link.active {
    color: #ffffff; /* CHANGED: White text for active */
    font-weight: 600;
    background-color: rgba(255, 255, 255, 0.15); /* Light white overlay */
}

.category-link.active:after,
.category-link:hover:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 10%;
    width: 80%;
    height: 2px;
    background-color: #ffffff; /* CHANGED: White underline */
    transform-origin: center;
    animation: underlineAnimation 0.3s ease forwards;
}

.category-link:hover {
    color: #ffffff; /* CHANGED: White text on hover */
    background-color: rgba(255, 255, 255, 0.1); /* Light white overlay */
    transform: translateY(-1px);
}

.category-link:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 10%;
    width: 0;
    height: 2px;
    background-color: #ffffff; /* CHANGED: White underline */
    transition: width 0.3s ease;
}

.category-link:hover:after {
    width: 80%;
}

/* Dropdown Toggle Button */
.category-dropdown-toggle {
    color: #000000;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 10px;
    position: relative;
    transition: all 0.3s ease;
    font-size: 14px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    white-space: nowrap;
    margin: 0 4px;
    cursor: pointer;
    border-radius: 4px;
    background: none;
    border: none;
}

.category-dropdown-toggle:after {
    content: '';
    display: inline-block;
    margin-left: 6px;
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid #000000;
    transition: transform 0.3s ease;
}

.category-dropdown-toggle:hover,
.category-dropdown-toggle.active {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.1);
}

.category-dropdown-toggle.active:after {
    transform: rotate(180deg);
    border-top-color: #ffffff;
}

/* FIXED DROPDOWN POSITIONING - BEAUTIFUL STYLING */
.dropdown-wrapper {
    position: fixed;
    left: 0;
    right: 0;
    top: 8.4375rem; /* Same as categories-navbar top */
    z-index: 1000;
    pointer-events: none; /* Allow clicks to pass through when hidden */
}

.category-dropdown-menu {
    display: none;
    position: absolute;
    top: 38px; /* Height of the navbar (approx) */
    left: 0;
    width: 250px;
    background: #f97316;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0 0 12px 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    z-index: 1001;
    padding: 12px 0;
    pointer-events: auto; /* Enable clicks when visible */
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: dropdownSlide 0.3s ease forwards;
    transform-origin: top center;
}

@keyframes dropdownSlide {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.category-dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 10px 20px;
    color: #000000;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    white-space: nowrap;
    position: relative;
    border-left: 3px solid transparent;
}

.dropdown-item:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.15);
    padding-left: 24px;
    border-left-color: #ffffff;
    transform: translateX(5px);
}

.dropdown-item.active {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
    border-left-color: #ffffff;
}

.dropdown-item.link-item {
    color: #000000;
    display: flex;
    align-items: center;
}

.dropdown-item.link-item:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.15);
}

.dropdown-item.link-item:after {
    content: '↗';
    margin-left: 8px;
    font-size: 12px;
    opacity: 0.7;
}

.dropdown-item.link-item:hover:after {
    opacity: 1;
    transform: translateX(2px);
}

.contact-btn {
    background: linear-gradient(135deg, #ffffff, #f0f0f0); /* Light gradient */
    border: 1px solid #ffffff;
    color: #f97316; /* Orange text color */
    text-decoration: none;
    font-weight: 600; /* Made bolder */
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.contact-btn i {
    margin-right: 6px;
    color: #f97316; /* Orange icon color */
    transition: all 0.3s ease;
}

.contact-btn:hover {
    background: linear-gradient(135deg, #f97316, #ea580c); /* Orange gradient on hover */
    color: #ffffff; /* White text on hover */
    border-color: #f97316;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(249, 115, 22, 0.4);
}

.contact-btn:hover i {
    color: #ffffff; /* White icon on hover */
}

.mobile-contact-btn {
    display: none;
    background: linear-gradient(135deg, #f97316, #ea580c); /* Orange gradient */
    color: white;
    border: none;
    padding: 12px 15px;
    border-radius: 6px;
    margin-top: 10px;
    width: 100%;
    text-align: center;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
}

.mobile-contact-btn i {
    margin-right: 8px;
    color: white;
}

@keyframes underlineAnimation {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}

/* HAMBURGER MENU STYLES - TEXT REMOVED, ONLY ICON */
.mobile-categories-toggle {
    display: none;
    background: #f97316; /* CHANGED: Same orange as navbar */
    border: 1px solid #ffffff;
    color: #ffffff;
    font-size: 16px;
    cursor: pointer;
    padding: 10px 12px;
    width: 50px;
    height: 50px;
    font-weight: 600;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
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
    background-color: #ffffff; /* CHANGED: White color */
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
    background: #f97316; /* CHANGED: Orange background */
    border-top: 2px solid #ffffff;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.mobile-category-item {
    margin-bottom: 8px;
}

.mobile-category-main {
    display: block;
    padding: 12px 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    white-space: normal;
    font-size: 15px;
    margin: 0;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
    color: #000000;
    text-decoration: none;
    cursor: pointer;
}

.mobile-category-main:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    transform: translateX(5px);
}

.mobile-category-main.active {
    background-color: rgba(255, 255, 255, 0.25);
    color: #ffffff;
    border-left: 4px solid #ffffff;
}

.mobile-submenu {
    display: none;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 10px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.mobile-submenu.show {
    display: block;
    animation: mobileSubmenuSlide 0.3s ease;
}

@keyframes mobileSubmenuSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-submenu-item {
    display: block;
    padding: 10px 12px;
    color: #000000;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 6px;
    border-radius: 6px;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.mobile-submenu-item:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    transform: translateX(5px);
    border-left-color: #ffffff;
}

.mobile-submenu-item.active {
    background-color: rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-weight: 600;
    border-left-color: #ffffff;
}

.mobile-submenu-item.link-item {
    color: #000000;
    display: flex;
    align-items: center;
}

.mobile-submenu-item.link-item:hover {
    color: #ffffff;
}

.mobile-submenu-item.link-item:after {
    content: '↗';
    margin-left: 8px;
    font-size: 12px;
}

.mobile-category-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-category-toggle:after {
    content: '';
    display: inline-block;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #000000;
    transition: transform 0.3s ease;
}

.mobile-category-toggle.active:after {
    transform: rotate(180deg);
    border-top-color: #ffffff;
}

/* Responsive adjustments for top value */
@media (max-width: 768px) {
    .categories-navbar {
        top: 110px; /* Smaller navbar height on mobile */
        background: #f97316; /* Same orange */
        padding: 5px 0;
    }
    .dropdown-wrapper {
        top: 110px;
    }
    .categories-container { display: none; }
    .contact-btn { display: none; }
    .mobile-contact-btn { display: inline-flex; }
    .mobile-categories-toggle { 
        display: flex;
    }
    .category-dropdown-menu {
        width: 220px;
    }
}

@media (max-width: 576px) {
    .categories-navbar {
        top: 100px; /* Even smaller navbar on mobile */
    }
    .dropdown-wrapper {
        top: 100px;
    }
    .mobile-categories-toggle {
        padding: 8px 10px;
        width: 2.8125rem;
        height: 1.4375rem;
    }
    .hamburger-icon {
        width: 1.25rem;
        height: 1rem;
    }
    .category-dropdown-menu {
        width: 200px;
        left: 50%;
        transform: translateX(-50%);
        top: 45px;
    }
}

@media (min-width: 769px) {
    .mobile-categories-menu { display: none !important; }
    .mobile-contact-btn { display: none !important; }
    .mobile-categories-toggle { display: none !important; }
}

/* DARKER GRAY BACKGROUND FOR DYNAMIC CATEGORY SECTIONS - KEPT SAME */
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
                <?php if (isset($category['is_group']) && $category['is_group']): ?>
                    <!-- Grouped Category with Dropdown Toggle -->
                    <button class="category-dropdown-toggle <?php echo $index === 1 ? 'active' : ''; ?>" 
                            data-category="<?php echo htmlspecialchars($category['value']); ?>"
                            id="dropdown-toggle-<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </button>
                <?php else: ?>
                    <!-- Regular Category Link -->
                    <a href="javascript:void(0);" 
                       class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                       data-category="<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <a href="javascript:void(0);" class="contact-btn">
                <i class="bi bi-bell"></i> संपर्क
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
                <?php if (isset($category['is_group']) && $category['is_group']): ?>
                    <!-- Mobile Grouped Category with Submenu -->
                    <div class="mobile-category-item">
                        <a href="javascript:void(0);" 
                           class="mobile-category-main mobile-category-toggle <?php echo $index === 1 ? 'active' : ''; ?>"
                           data-category="<?php echo htmlspecialchars($category['value']); ?>"
                           data-toggle="submenu">
                            <?php echo htmlspecialchars($category['label']); ?>
                        </a>
                        <div class="mobile-submenu" id="submenu-<?php echo htmlspecialchars($category['value']); ?>">
                            <?php foreach ($category['children'] as $child): ?>
                                <?php if (isset($child['type']) && $child['type'] === 'link'): ?>
                                    <a href="<?php echo htmlspecialchars($child['url']); ?>" 
                                       class="mobile-submenu-item link-item">
                                        <?php echo htmlspecialchars($child['label']); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" 
                                       class="mobile-submenu-item" 
                                       data-category="<?php echo htmlspecialchars($child['value']); ?>">
                                        <?php echo htmlspecialchars($child['label']); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Mobile Regular Category Link -->
                    <a href="javascript:void(0);" 
                       class="mobile-category-main <?php echo $index === 0 ? 'active' : ''; ?>" 
                       data-category="<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <a href="javascript:void(0);" class="mobile-contact-btn">
                <i class="bi bi-bell"></i> संपर्क साधा
            </a>
        </div>
    </div>
</div>

<!-- SEPARATE DROPDOWN LAYER (outside fixed navbar) -->
<div class="dropdown-wrapper" id="dropdownWrapper">
    <div class="category-dropdown-menu" id="amrutAboutDropdown">
        <?php 
        // Find the "अमृत विषयी" group
        foreach ($categories as $category):
            if (isset($category['is_group']) && $category['value'] === 'amrut_about_group'): 
        ?>
            <?php foreach ($category['children'] as $child): ?>
                <?php if (isset($child['type']) && $child['type'] === 'link'): ?>
                    <a href="<?php echo htmlspecialchars($child['url']); ?>" 
                       class="dropdown-item link-item">
                        <?php echo htmlspecialchars($child['label']); ?>
                    </a>
                <?php else: ?>
                    <a href="javascript:void(0);" 
                       class="dropdown-item" 
                       data-category="<?php echo htmlspecialchars($child['value']); ?>">
                        <?php echo htmlspecialchars($child['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; endforeach; ?>
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
// Store all REAL categories for JavaScript
const allRealCategories = <?php echo json_encode($all_real_categories); ?>;

// Store original categories with groups structure
const originalCategories = <?php echo json_encode($categories); ?>;

// Debug: Check what's in allRealCategories
console.log('All REAL Categories (including group children):', allRealCategories);

// Store dropdown position
let currentDropdown = null;
let dropdownToggleRect = null;

// Function to update dropdown position
function updateDropdownPosition() {
    if (currentDropdown && dropdownToggleRect) {
        const dropdownMenu = document.getElementById('amrutAboutDropdown');
        if (dropdownMenu) {
            // Get the container
            const container = document.querySelector('.container-fluid.px-3');
            const containerRect = container.getBoundingClientRect();
            
            // Calculate position
            const toggleCenter = dropdownToggleRect.left + (dropdownToggleRect.width / 2);
            const dropdownWidth = 250; // Width from CSS
            
            let leftPosition = toggleCenter - (dropdownWidth / 2);
            
            // Ensure dropdown stays within viewport
            const minLeft = containerRect.left + 15;
            const maxLeft = containerRect.right - dropdownWidth - 15;
            
            leftPosition = Math.max(minLeft, Math.min(leftPosition, maxLeft));
            
            // Apply position
            dropdownMenu.style.left = (leftPosition - containerRect.left) + 'px';
        }
    }
}

// Function to scroll to category section
function scrollToCategorySection(categoryValue) {
    console.log('Attempting to scroll to category section:', categoryValue);
    
    // Check if this is a valid category that should scroll
    if (categoryValue === 'home') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return true;
    }
    
    // Check if this is a group (not a real category)
    if (categoryValue === 'amrut_about_group') {
        console.log('This is a group, not a category. Opening dropdown instead.');
        return false;
    }
    
    // Find the category in allRealCategories
    const categoryData = allRealCategories.find(cat => cat.value === categoryValue);
    if (!categoryData) {
        console.warn('Category not found in allRealCategories:', categoryValue);
        return false;
    }
    
    console.log('Found category data:', categoryData);
    
    // Check if dynamic function exists
    if (typeof scrollToDynamicCategory === 'function') {
        console.log('Using scrollToDynamicCategory function');
        scrollToDynamicCategory(categoryValue);
        return true;
    }
    
    // Fallback: Try to find the section manually
    const sectionId = 'category-' + categoryValue;
    const section = document.getElementById(sectionId);
    
    if (section) {
        console.log('Found section with ID:', sectionId);
        const navbar = document.querySelector('.categories-navbar');
        const navbarHeight = navbar ? navbar.offsetHeight : 80;
        
        // Scroll to the section
        window.scrollTo({
            top: section.offsetTop - navbarHeight - 20,
            behavior: 'smooth'
        });
        
        // Highlight the section briefly
        const originalBg = section.style.backgroundColor;
        section.style.transition = 'background-color 0.5s ease';
        section.style.backgroundColor = 'rgba(249, 115, 22, 0.1)';
        
        setTimeout(() => {
            section.style.backgroundColor = originalBg;
        }, 1500);
        
        return true;
    } else {
        console.warn('Section not found with ID:', sectionId);
    }
    
    console.warn('Could not find section or scroll function for category:', categoryValue);
    return false;
}

// Function to filter news based on category
function filterNews(categoryValue, marathiLabel = '', event = null) {
    // Prevent default anchor behavior
    if (event) {
        event.preventDefault();
    }
    
    console.log('filterNews called for category:', categoryValue, 'Label:', marathiLabel);
    
    // If this is the group toggle, just open/close dropdown
    if (categoryValue === 'amrut_about_group') {
        const toggleButton = document.querySelector('.category-dropdown-toggle[data-category="amrut_about_group"]');
        if (toggleButton) {
            toggleDropdown(toggleButton);
        }
        return false;
    }
    
    // Close dropdown if open
    closeDropdown();
    
    // Update active state for ALL category links (including dropdown items)
    updateActiveCategory(categoryValue);
    
    if (!marathiLabel) {
        const selectedCategory = allRealCategories.find(cat => cat.value === categoryValue);
        marathiLabel = selectedCategory ? selectedCategory.label : categoryValue;
    }
    
    console.log('Navigating to REAL category:', marathiLabel, 'Value:', categoryValue);
    
    // Try to scroll to the section
    const scrolled = scrollToCategorySection(categoryValue);
    
    if (!scrolled && categoryValue !== 'amrut_about_group') {
        console.warn('Initial scroll failed for category:', categoryValue);
        
        // Try again after a short delay
        setTimeout(() => {
            const retryScrolled = scrollToCategorySection(categoryValue);
            if (!retryScrolled && categoryValue !== 'amrut_about_group') {
                console.error('Failed to scroll to category after retry:', categoryValue);
            }
        }, 300);
    }
    
    // Close mobile menu if open
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    const toggleButton = document.getElementById('mobileCategoriesToggle');
    if (mobileMenu && mobileMenu.style.display === 'block') {
        mobileMenu.style.display = 'none';
        toggleButton.classList.remove('active');
    }
    
    return false;
}

// Function to update active category state
function updateActiveCategory(categoryValue) {
    // Reset all active states first
    document.querySelectorAll('.category-link, .dropdown-item, .mobile-category-main, .mobile-submenu-item, .category-dropdown-toggle').forEach(element => {
        element.classList.remove('active');
    });
    
    // If it's a group, activate the group toggle
    if (categoryValue === 'amrut_about_group') {
        const groupToggle = document.querySelector('.category-dropdown-toggle[data-category="amrut_about_group"]');
        if (groupToggle) {
            groupToggle.classList.add('active');
        }
        return;
    }
    
    // Activate ALL elements with this category value
    const categoryElements = document.querySelectorAll(`[data-category="${categoryValue}"]`);
    categoryElements.forEach(element => {
        element.classList.add('active');
    });
    
    // Also check if this category is a child of a group
    for (const category of originalCategories) {
        if (category.is_group && category.children) {
            const isChild = category.children.some(child => child.value === categoryValue);
            if (isChild) {
                // Activate the parent group toggle (desktop)
                const groupToggle = document.querySelector(`.category-dropdown-toggle[data-category="${category.value}"]`);
                if (groupToggle) {
                    groupToggle.classList.add('active');
                }
                
                // Also activate mobile group toggle
                const mobileGroupToggle = document.querySelector(`.mobile-category-toggle[data-category="${category.value}"]`);
                if (mobileGroupToggle) {
                    mobileGroupToggle.classList.add('active');
                }
                break;
            }
        }
    }
}

// Function to toggle dropdown
function toggleDropdown(toggleButton) {
    const dropdownMenu = document.getElementById('amrutAboutDropdown');
    
    if (dropdownMenu.classList.contains('show')) {
        closeDropdown();
    } else {
        // Close mobile menu if open
        const mobileMenu = document.getElementById('mobileCategoriesMenu');
        const mobileToggle = document.getElementById('mobileCategoriesToggle');
        if (mobileMenu && mobileMenu.style.display === 'block') {
            mobileMenu.style.display = 'none';
            mobileToggle.classList.remove('active');
        }
        
        // Set current dropdown and get position
        currentDropdown = toggleButton;
        dropdownToggleRect = toggleButton.getBoundingClientRect();
        
        // Update position and show
        updateDropdownPosition();
        dropdownMenu.classList.add('show');
        toggleButton.classList.add('active');
        
        // Add resize and scroll listeners
        window.addEventListener('resize', updateDropdownPosition);
        window.addEventListener('scroll', updateDropdownPosition);
    }
}

// Function to close dropdown
function closeDropdown() {
    const dropdownMenu = document.getElementById('amrutAboutDropdown');
    dropdownMenu.classList.remove('show');
    
    document.querySelectorAll('.category-dropdown-toggle').forEach(toggle => {
        toggle.classList.remove('active');
    });
    
    currentDropdown = null;
    dropdownToggleRect = null;
    
    // Remove listeners
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('scroll', updateDropdownPosition);
}

function goToContact(event) {
    if (event) event.preventDefault();
    
    // Close dropdown if open
    closeDropdown();
    
    // Get the footer element
    const footer = document.querySelector('footer');
    
    if (footer) {
        // Scroll to footer smoothly
        footer.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Optional: Highlight the footer briefly
        const originalBg = footer.style.backgroundColor;
        footer.style.transition = 'background-color 0.5s ease';
        footer.style.backgroundColor = 'rgba(255, 102, 0, 0.1)';
        
        setTimeout(() => {
            footer.style.backgroundColor = originalBg;
        }, 1500);
    }
    
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
                closeDropdown();
            } else {
                mobileMenu.style.display = 'block';
                toggleButton.classList.add('active');
                closeDropdown();
            }
        });
    }
    
    // Toggle mobile submenus
    document.querySelectorAll('.mobile-category-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            const submenu = document.getElementById('submenu-' + categoryValue);
            
            if (submenu) {
                // Toggle current submenu
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    this.classList.remove('active');
                } else {
                    // Close all other submenus
                    document.querySelectorAll('.mobile-submenu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.mobile-category-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                    
                    // Open current submenu
                    submenu.classList.add('show');
                    this.classList.add('active');
                }
            }
        });
    });
    
    // Handle dropdown toggle click (for the group)
    document.querySelectorAll('.category-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const categoryValue = this.getAttribute('data-category');
            
            if (categoryValue === 'amrut_about_group') {
                toggleDropdown(this);
            }
        });
    });
    
    // Update category link event listeners for ALL category links
    document.querySelectorAll('.category-link[data-category], .dropdown-item[data-category], .mobile-category-main[data-category], .mobile-submenu-item[data-category]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            const marathiLabel = this.textContent;
            
            console.log('Category link clicked:', categoryValue, marathiLabel);
            
            // Handle both regular category links and dropdown items
            filterNews(categoryValue, marathiLabel, e);
        });
    });
    
    // Handle direct link items (like about_us)
    document.querySelectorAll('.dropdown-item.link-item, .mobile-submenu-item.link-item').forEach(link => {
        link.addEventListener('click', function(e) {
            // Close dropdown before navigating
            closeDropdown();
            // These are direct links, let them navigate naturally
            console.log('Direct link clicked:', this.href);
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.category-dropdown-toggle') && !event.target.closest('.category-dropdown-menu')) {
            closeDropdown();
        }
        
        // Close mobile menu
        if (mobileMenu && mobileMenu.style.display === 'block') {
            if (!mobileMenu.contains(event.target) && !toggleButton.contains(event.target)) {
                mobileMenu.style.display = 'none';
                toggleButton.classList.remove('active');
                closeDropdown();
            }
        }
    });
    
    // Set default active category to home
    updateActiveCategory('home');
});

// Handle hash changes in URL
window.addEventListener('hashchange', function() {
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'home') {
        // Extract category value from hash (remove 'category-' prefix if present)
        const categoryValue = hash.startsWith('category-') ? hash.substring(9) : hash;
        
        // Check if this category exists in allRealCategories
        if (allRealCategories.find(cat => cat.value === categoryValue)) {
            setTimeout(() => {
                // Update active state
                updateActiveCategory(categoryValue);
                
                // Scroll to section
                scrollToCategorySection(categoryValue);
            }, 100);
        }
    }
});

// Handle initial page load with hash
if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    if (hash && hash !== 'home') {
        const categoryValue = hash.startsWith('category-') ? hash.substring(9) : hash;
        if (allRealCategories.find(cat => cat.value === categoryValue)) {
            setTimeout(() => {
                updateActiveCategory(categoryValue);
                scrollToCategorySection(categoryValue);
            }, 300);
        }
    }
}
</script>
<?php 
$conn->close();
$showWhatsapp = true;
include 'components/footer.php'; 
?>