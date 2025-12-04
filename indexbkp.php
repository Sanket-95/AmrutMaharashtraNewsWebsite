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

// Define categories array
$categories = [
    ['label' => 'मुख्यपृष्ठ', 'value' => 'home'],
    ['label' => 'दिनदिशेष', 'value' => 'today_special'],
    ['label' => 'अमृत घडामोडी', 'value' => 'amrut_events'],
    ['label' => 'लाभार्थी स्टोरी', 'value' => 'beneficiary_story'],
    ['label' => 'यशस्वी उद्योजक', 'value' => 'successful_entrepreneur'],
    ['label' => 'शब्दामृत', 'value' => 'words_amrut'],
    ['label' => 'स्मार्ट शेतकरी', 'value' => 'smart_farmer'],
    ['label' => 'सक्षम दिद्यार्थी', 'value' => 'capable_student'],
    ['label' => 'अध्यात्म', 'value' => 'spirituality'],
    ['label' => 'सामाजिक परिस्थिती', 'value' => 'social_situation'],
    ['label' => 'स्त्रीशक्ती', 'value' => 'women_power'],
    ['label' => 'पर्यटन', 'value' => 'tourism'],
    ['label' => 'अमृत सेवा कार्य', 'value' => 'amrut_service'],
    ['label' => 'आमच्या दिशयी', 'value' => 'about_us']
];
?>

<style>
/* Categories Navbar Styles Only */
.categories-navbar {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
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
    color: #333;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 8px;
    position: relative;
    transition: color 0.3s ease;
    font-size: 14px;
    display: inline-block;
    flex-shrink: 0;
    white-space: nowrap;
    margin: 0 4px;
    cursor: pointer;
}

.category-link.active {
    color: #ff6600;
    font-weight: 600;
}

.category-link.active:after,
.category-link:hover:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #ff6600;
    transform-origin: left;
    animation: underlineAnimation 0.3s ease forwards;
}

.category-link:hover {
    color: #ff6600;
}

.category-link:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #ff6600;
    transition: width 0.3s ease;
}

.category-link:hover:after {
    width: 100%;
}

.contact-btn {
    background: transparent;
    border: 1px solid #ff6600;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    white-space: nowrap;
    margin-left: 8px;
    cursor: pointer;
}

.contact-btn i {
    margin-right: 6px;
    color: #ff6600;
    transition: all 0.3s ease;
}

.contact-btn:hover {
    background: #ff6600;
    color: white;
    border-color: #ff6600;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 102, 0, 0.2);
}

.contact-btn:hover i {
    color: white;
}

.mobile-contact-btn {
    display: none;
    background: #ff6600;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    margin-top: 10px;
    width: 100%;
    text-align: center;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
}

.mobile-contact-btn i {
    margin-right: 8px;
}

@keyframes underlineAnimation {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}

.mobile-categories-toggle {
    display: none;
    background: none;
    border: none;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    padding: 10px;
    width: 100%;
    font-weight: 500;
    text-align: left;
}

.mobile-categories-toggle i {
    margin-left: 8px;
    transition: transform 0.3s ease;
    float: right;
}

.mobile-categories-toggle.active i {
    transform: rotate(180deg);
}

.mobile-categories-menu {
    display: none;
    background: white;
    border-top: 1px solid #dee2e6;
    padding: 10px 15px;
    max-height: 400px;
    overflow-y: auto;
}

.mobile-categories-menu .category-link {
    display: block;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    white-space: normal;
    font-size: 15px;
    margin: 0;
}

.mobile-categories-menu .category-link:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .categories-container { display: none; }
    .contact-btn { display: none; }
    .mobile-contact-btn { display: inline-flex; }
    .mobile-categories-toggle { display: block; }
}

@media (min-width: 769px) {
    .mobile-categories-menu { display: none !important; }
    .mobile-contact-btn { display: none !important; }
}
</style>

<!-- Categories Navigation -->
<div class="categories-navbar">
    <div class="container-fluid px-3">
        <div class="categories-container d-none d-md-flex">
            <?php foreach ($categories as $index => $category): ?>
                <a href="#" 
                   class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                   data-category="<?php echo htmlspecialchars($category['value']); ?>"
                   onclick="filterNews('<?php echo $category['value']; ?>', '<?php echo htmlspecialchars($category['label']); ?>')">
                    <?php echo htmlspecialchars($category['label']); ?>
                </a>
            <?php endforeach; ?>
            <a href="#" class="contact-btn" onclick="goToContact()">
                <i class="bi bi-bell"></i> संपर्क साधा
            </a>
        </div>
        <button class="mobile-categories-toggle d-md-none" id="mobileCategoriesToggle">
            <span>श्रेण्या</span>
            <i class="bi bi-chevron-down"></i>
        </button>
        <div class="mobile-categories-menu" id="mobileCategoriesMenu">
            <?php foreach ($categories as $index => $category): ?>
                <a href="#" 
                   class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                   data-category="<?php echo htmlspecialchars($category['value']); ?>"
                   onclick="filterNews('<?php echo $category['value']; ?>', '<?php echo htmlspecialchars($category['label']); ?>')">
                    <?php echo htmlspecialchars($category['label']); ?>
                </a>
            <?php endforeach; ?>
            <a href="#" class="mobile-contact-btn" onclick="goToContact()">
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






    //  <!-- Include Category Cards Component -->
    include 'components/category_cards.php'; 
    include 'components/remaining_categories.php'; 




    
    ?>
</main>

<script>
// Store categories data
const categoriesData = <?php echo json_encode($categories); ?>;

// Function to filter news based on category
function filterNews(categoryValue, marathiLabel = '') {
    const categoryLinks = document.querySelectorAll('.category-link');
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
    
    // Close mobile menu if open
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    const toggleButton = document.getElementById('mobileCategoriesToggle');
    if (mobileMenu && mobileMenu.style.display === 'block') {
        mobileMenu.style.display = 'none';
        toggleButton.classList.remove('active');
    }
}

function goToContact() {
    alert('संपर्क पृष्ठावर नेण्यात येत आहे...');
}

// Mobile Categories Toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('mobileCategoriesToggle');
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    
    if (toggleButton && mobileMenu) {
        toggleButton.addEventListener('click', function() {
            if (mobileMenu.style.display === 'block') {
                mobileMenu.style.display = 'none';
                toggleButton.classList.remove('active');
            } else {
                mobileMenu.style.display = 'block';
                toggleButton.classList.add('active');
            }
        });
    }
    
    document.addEventListener('click', function(event) {
        if (mobileMenu && mobileMenu.style.display === 'block') {
            if (!mobileMenu.contains(event.target) && !toggleButton.contains(event.target)) {
                mobileMenu.style.display = 'none';
                toggleButton.classList.remove('active');
            }
        }
    });
    
    filterNews('home');
});
</script>

<?php 
$conn->close();
include 'components/footer.php'; 
?>