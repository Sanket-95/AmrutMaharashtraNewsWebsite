<?php
// components/dynamic_category_sections.php

// Check if database connection exists
if (!isset($conn)) {
    require_once 'components/db_config.php';
}

// Function to adjust color brightness
function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));
    
    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }
    
    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';
    
    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }
    
    return $return;
}

// Step 1: Get categories array from index.php (excluding 'home')
global $categories;

// Remove 'home' category (only for navigation, not for sections)
$dynamic_categories = array_filter($categories, function($cat) {
    return $cat['value'] !== 'home';
});

// Reset array keys
$dynamic_categories = array_values($dynamic_categories);

// Professional colors for category headers
$professional_colors = [
    '#2c3e50', // Dark Blue-Gray
    '#34495e', // Dark Slate
    '#2c3e50', // Charcoal
    '#34495e', // Dark Gray-Blue
    '#2c3e50', // Navy Gray
    '#34495e', // Dark Steel
    '#2c3e50', // Graphite
    '#34495e', // Dark Slate Gray
    '#2c3e50', // Midnight
    '#34495e', // Dark Gunmetal
    '#2c3e50', // Dark Charcoal
    '#34495e', // Slate
    '#2c3e50', // Dark Gray
    '#34495e', // Charcoal Gray
];

// Advertisement images
$advertisements = [
    [
        'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'link' => '#',
        'alt' => 'Advertisement 1'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'link' => '#',
        'alt' => 'Advertisement 2'
    ]
];

// Function to get latest news for each category (max 3 per category)
function getLatestNewsByCategory($conn) {
    // Get latest date news for each category
    $query = "SELECT n1.* 
              FROM news_articles n1 
              INNER JOIN (
                  SELECT category_name, MAX(DATE(published_date)) as latest_date 
                  FROM news_articles 
                  WHERE DATE(published_date) <= CURDATE() 
                  AND is_aproved = 1 
                  GROUP BY category_name
              ) n2 ON n1.category_name = n2.category_name 
                     AND DATE(n1.published_date) = n2.latest_date 
              WHERE DATE(n1.published_date) <= CURDATE() 
              AND n1.is_aproved = 1 
              ORDER BY n1.category_name, n1.published_date DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return [];
    }
    
    $all_news = [];
    while($row = $result->fetch_assoc()) {
        $all_news[] = $row;
    }
    
    // Group by category and limit to 3 per category
    $grouped_news = [];
    $category_counts = [];
    
    foreach ($all_news as $news) {
        $category = $news['category_name'];
        
        if (!isset($category_counts[$category])) {
            $category_counts[$category] = 0;
            $grouped_news[$category] = [];
        }
        
        if ($category_counts[$category] < 3) {
            $grouped_news[$category][] = $news;
            $category_counts[$category]++;
        }
    }
    
    return $grouped_news;
}

// Function to get total news count per category
function getTotalNewsCountByCategory($conn) {
    $query = "SELECT category_name, COUNT(*) as total_count 
              FROM news_articles 
              WHERE is_aproved = 1 
              GROUP BY category_name";
    
    $result = $conn->query($query);
    
    if (!$result) {
        return [];
    }
    
    $counts = [];
    while($row = $result->fetch_assoc()) {
        $counts[$row['category_name']] = $row['total_count'];
    }
    
    return $counts;
}

// Get data from database
$latest_news_by_category = getLatestNewsByCategory($conn);
$total_counts_by_category = getTotalNewsCountByCategory($conn);

// Function to generate news card HTML
function generateNewsCard($news) {
    // Default image if URL is empty or invalid
    $default_image = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
    $image_url = !empty($news['cover_photo_url']) ? $news['cover_photo_url'] : $default_image;
    
    // Format date
    $formatted_date = date('d M Y', strtotime($news['published_date']));
    
    // Get published by
    $published_by = !empty($news['published_by']) ? $news['published_by'] : 'अमृत महाराष्ट्र';
    
    // Truncate summary if too long (show 4 lines)
    $summary = !empty($news['summary']) ? $news['summary'] : '';
    if (strlen($summary) > 250) {
        $summary = substr($summary, 0, 247) . '...';
    }
    
    return '
    <div class="col-md-6 col-lg-4 mb-4 news-item">
        <div class="card h-100 shadow-sm border-0 news-card card-hover">
            <div class="position-relative overflow-hidden">
                <img src="' . htmlspecialchars($image_url) . '" 
                     class="card-img-top" 
                     alt="' . htmlspecialchars($news['title']) . '" 
                     style="height: 220px; object-fit: cover;"
                     onerror="this.src=\'' . $default_image . '\'">
                <div class="image-overlay"></div>
            </div>
            <div class="card-body p-4" style="min-height: 280px;">
                <h6 class="card-title fw-bold text-dark mb-3" style="font-family: \'Noto Sans Devanagari\', sans-serif; font-size: 1.15rem; font-weight: 700; line-height: 1.4; min-height: 70px;">
                    ' . htmlspecialchars($news['title']) . '
                </h6>
                <p class="card-text text-muted mb-3" style="font-family: \'Noto Sans Devanagari\', sans-serif; font-size: 0.95rem; line-height: 1.6; min-height: 100px; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;">
                    ' . htmlspecialchars($summary) . '
                </p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-person-circle me-2" style="color: #6c757d;"></i>
                            <small class="text-muted" style="font-family: \'Noto Sans Devanagari\', sans-serif;">' . htmlspecialchars($published_by) . '</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 me-2" style="color: #6c757d;"></i>
                            <small class="text-muted" style="font-family: \'Noto Sans Devanagari\', sans-serif;">' . $formatted_date . '</small>
                        </div>
                    </div>
                    <a href="news.php?id=' . $news['news_id'] . '" class="btn btn-sm btn-outline-primary px-3 read-btn" style="font-family: \'Noto Sans Devanagari\', sans-serif;">
                        वाचा <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    ';
}
?>

<!-- Step 2: Generate Dynamic Category Sections -->
<div class="container-fluid px-0" id="dynamic-categories-container">
    <?php 
    $category_counter = 0;
    foreach ($dynamic_categories as $category): 
        $category_counter++;
        // Get a professional color for this category header
        $color_index = ($category_counter - 1) % count($professional_colors);
        $header_color = $professional_colors[$color_index];
        
        // Check if category has news in database
        $category_has_news = isset($latest_news_by_category[$category['value']]) && 
                            !empty($latest_news_by_category[$category['value']]);
        
        // Get total news count for this category
        $total_news_count = isset($total_counts_by_category[$category['value']]) 
                          ? $total_counts_by_category[$category['value']] 
                          : 0;
        
        // Get news for this category (max 3)
        $category_news = $category_has_news ? $latest_news_by_category[$category['value']] : [];
        
        // Calculate how many news to show (max 3)
        $news_to_show = min(count($category_news), 3);
    ?>
        
        <!-- Category Section -->
        <div class="category-section py-5 <?php echo $category_counter % 2 == 0 ? 'bg-light' : ''; ?>" 
             id="category-<?php echo htmlspecialchars($category['value']); ?>"
             data-category="<?php echo htmlspecialchars($category['value']); ?>">
            
            <div class="container">
                <!-- Category Header - Simple design with underline effect -->
                <div class="section-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="category-title-container" style="position: relative;">
                            <h2 class="fw-bold mb-0" 
                                style="color: <?php echo $header_color; ?>; 
                                       font-family: 'Noto Sans Devanagari', sans-serif;
                                       font-size: 1.9rem;
                                       font-weight: 700;
                                       padding-bottom: 8px;
                                       display: inline-block;">
                                <?php echo htmlspecialchars($category['label']); ?>
                            </h2>
                            <div class="header-underline" 
                                 style="position: absolute; 
                                        bottom: 0; 
                                        left: 0; 
                                        width: 50%; 
                                        height: 3px; 
                                        background-color: <?php echo adjustBrightness($header_color, -30); ?>;
                                        border-radius: 2px;
                                        transition: all 0.3s ease;"></div>
                            <div class="header-underline-hover" 
                                 style="position: absolute; 
                                        bottom: 0; 
                                        left: 0; 
                                        width: 0; 
                                        height: 3px; 
                                        background-color: #ff6600;
                                        border-radius: 2px;
                                        transition: all 0.3s ease;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- News Cards Container - MAX 3 CARDS -->
                <div class="row g-4 news-container" id="news-container-<?php echo htmlspecialchars($category['value']); ?>">
                    <?php if ($category_has_news && $news_to_show > 0): ?>
                        <?php for ($i = 0; $i < $news_to_show; $i++): ?>
                            <?php echo generateNewsCard($category_news[$i]); ?>
                        <?php endfor; ?>
                    <?php else: ?>
                        <!-- No news message for entire category -->
                        <div class="col-12 text-center py-5">
                            <div class="card border-0 shadow-sm bg-light">
                                <div class="card-body py-5">
                                    <i class="bi bi-newspaper display-4 text-muted mb-3"></i>
                                    <h5 class="text-muted" style="font-family: 'Noto Sans Devanagari', sans-serif;">या श्रेणीमध्ये अद्याप बातम्या उपलब्ध नाहीत</h5>
                                    <p class="text-muted small mt-2" style="font-family: 'Noto Sans Devanagari', sans-serif;">लवकरच बातम्या प्रकाशित केल्या जातील</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ALL NEWS Button at Bottom for EVERY Category (if has any news) -->
                <?php if ($category_has_news): ?>
                    <div class="text-center mt-5 pt-3">
                        <a href="category_news.php?category=<?php echo urlencode($category['value']); ?>" 
                           class="btn btn-lg all-news-btn" 
                           style="background-color: <?php echo $header_color; ?>; 
                                  color: white; 
                                  border: none; 
                                  font-family: 'Noto Sans Devanagari', sans-serif;
                                  font-weight: 600;
                                  font-size: 1.15rem;
                                  padding: 12px 35px;
                                  border-radius: 10px;
                                  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                                  border: 2px solid rgba(255,255,255,0.3);
                                  transition: all 0.3s ease;
                                  position: relative;
                                  overflow: hidden;
                                  text-decoration: none;">
                            <i class="bi bi-newspaper me-2"></i>
                            या श्रेणीच्या सर्व बातम्या पहा
                            <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ADVERTISEMENT SECTION - ONLY AFTER FIRST 2 CATEGORIES -->
        <?php if ($category_counter == 2): ?>
            <div class="ad-section py-5">
                <div class="container">
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($advertisements as $ad): ?>
                            <div class="col-md-6">
                                <a href="<?php echo htmlspecialchars($ad['link']); ?>" class="ad-card d-block ad-hover" target="_blank">
                                    <div class="card border-0 shadow-sm ad-image-card">
                                        <img src="<?php echo htmlspecialchars($ad['image']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($ad['alt']); ?>"
                                             style="height: 200px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endforeach; ?>
</div>

<style>
/* Marathi Font */
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@300;400;500;600;700;800&display=swap');

/* Category Title with Underline Effect */
.category-title-container {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.category-title-container:hover .header-underline {
    width: 0;
}

.category-title-container:hover .header-underline-hover {
    width: 100%;
}

.section-header {
    position: relative;
    margin-bottom: 40px;
}

.section-header h2 {
    font-size: 1.9rem;
    font-weight: 700;
    font-family: 'Noto Sans Devanagari', sans-serif;
    position: relative;
    transition: all 0.3s ease;
}

.category-title-container:hover h2 {
    color: #ff6600;
    transform: translateX(5px);
}

/* ALL NEWS Button at Bottom */
.all-news-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
    background-color: <?php echo adjustBrightness($header_color, -20); ?> !important;
}

.all-news-btn::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.all-news-btn:hover::after {
    left: 100%;
}

/* Category section styles */
.category-section {
    scroll-margin-top: 80px;
    transition: all 0.3s ease;
}

/* News Cards */
.news-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #dee2e6 !important;
    height: 100%;
}

.card-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    border-color: #adb5bd !important;
}

/* Read button */
.read-btn {
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid #0d6efd;
    color: #0d6efd;
    text-decoration: none;
    font-family: 'Noto Sans Devanagari', sans-serif;
}

.read-btn:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateX(3px);
    text-decoration: none;
}

/* ADVERTISEMENT SECTION */
.ad-section {
    padding: 40px 0;
    background: #fff;
    border-top: 2px solid #dee2e6;
    border-bottom: 2px solid #dee2e6;
    margin: 30px 0;
}

.ad-card {
    text-decoration: none;
    transition: all 0.3s ease;
}

.ad-hover:hover {
    transform: translateY(-5px);
}

.ad-image-card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.ad-hover:hover .ad-image-card {
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    border-color: #adb5bd;
}

/* Spacing */
.row.g-4 {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .col-lg-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    .section-header h2 {
        font-size: 1.7rem;
    }
}

@media (max-width: 768px) {
    .category-section {
        scroll-margin-top: 60px;
        padding: 2rem 0;
    }
    
    .section-header h2 {
        font-size: 1.5rem;
    }
    
    .all-news-btn {
        padding: 10px 25px !important;
        font-size: 1.05rem !important;
    }
    
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .row.g-4 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }
    
    .card-body {
        min-height: 250px !important;
    }
    
    .card-img-top {
        height: 180px !important;
    }
}

/* Light background for alternating sections */
.bg-light {
    background-color: #f8f9fa !important;
}

/* Smooth scroll */
html {
    scroll-behavior: smooth;
    font-family: 'Noto Sans Devanagari', sans-serif;
}

body {
    font-family: 'Noto Sans Devanagari', sans-serif;
}

/* Animation for card hover */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.card-hover:hover {
    animation: float 0.3s ease-in-out;
}
</style>

<script>
// Function to scroll to category
function scrollToDynamicCategory(categoryValue) {
    const categorySection = document.getElementById(`category-${categoryValue}`);
    if (categorySection) {
        // Calculate position (accounting for sticky navbar)
        const navbar = document.querySelector('.categories-navbar');
        const navbarHeight = navbar ? navbar.offsetHeight : 80;
        
        // Use scrollIntoView with offset
        const header = categorySection.querySelector('.section-header');
        if (header) {
            const headerOffset = header.offsetTop - navbarHeight - 20;
            window.scrollTo({
                top: headerOffset,
                behavior: 'smooth'
            });
        } else {
            // Fallback method
            const elementPosition = categorySection.offsetTop;
            const offsetPosition = elementPosition - navbarHeight - 20;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
        
        // Highlight the section temporarily
        const originalBg = categorySection.style.backgroundColor;
        categorySection.style.transition = 'background-color 0.5s ease';
        categorySection.style.backgroundColor = 'rgba(108, 117, 125, 0.08)';
        
        setTimeout(() => {
            categorySection.style.backgroundColor = originalBg;
        }, 1500);
    }
}

// Add hover effects
document.addEventListener('DOMContentLoaded', function() {
    // Card hover effects
    const cards = document.querySelectorAll('.news-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // All News button hover effects
    const allNewsButtons = document.querySelectorAll('.all-news-btn');
    allNewsButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Category title hover effects
    const categoryTitles = document.querySelectorAll('.category-title-container');
    categoryTitles.forEach(title => {
        title.addEventListener('mouseenter', function() {
            const h2 = this.querySelector('h2');
            h2.style.color = '#ff6600';
            h2.style.transform = 'translateX(5px)';
        });
        
        title.addEventListener('mouseleave', function() {
            const h2 = this.querySelector('h2');
            const headerColor = h2.style.color || getComputedStyle(h2).color;
            // Reset to original color (will be set by inline style)
            h2.style.color = '';
            h2.style.transform = 'translateX(0)';
        });
    });
});
</script>