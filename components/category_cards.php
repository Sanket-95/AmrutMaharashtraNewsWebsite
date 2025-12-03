<?php
// components/category_cards.php

// Get database connection if not already available
if (!isset($conn)) {
    require_once 'components/db_config.php';
}

// Define ONLY the 2 categories we need
$categories = [
    ['label' => 'दिनदिशेष', 'value' => 'today_special'],
    ['label' => 'अमृत घडामोडी', 'value' => 'amrut_events']
];

// Dummy news data for each category (3 items per category)
$dummyNews = [
    'today_special' => [
        [
            'title' => 'महाराष्ट्र दिनाच्या निमित्त विशेष कार्यक्रम',
            'summary' => 'महाराष्ट्र दिनाच्या निमित्त राज्यभरात विविध सांस्कृतिक कार्यक्रमांचे आयोजन.',
            'date' => '१ मे २०२४',
            'image' => 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'आजचे विशेष: युवा दिन साजरा',
            'summary' => 'युवा दिनानिमित्त तरुणांसाठी विशेष कार्यशाळा आणि स्पर्धांचे आयोजन.',
            'date' => '१२ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'साप्ताहिक विशेष: कृषी मेळावा',
            'summary' => 'शेतकऱ्यांसाठी आधुनिक शेती तंत्रज्ञानाचा प्रदर्शनासह विशेष मेळावा.',
            'date' => '१५ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ],
    'amrut_events' => [
        [
            'title' => 'अमृत कार्यशाळा: उद्योजकता विकास',
            'summary' => 'तरुण उद्योजकांसाठी व्यवसाय विकासावर मार्गदर्शक कार्यशाळा.',
            'date' => '५ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'अमृत स्वच्छता अभियान',
            'summary' => 'शहर स्वच्छतेसाठी स्वयंसेवकांसह विशेष स्वच्छता अभियान.',
            'date' => '१० जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'अमृत शैक्षणिक मेळावा',
            'summary' => 'विद्यार्थ्यांसाठी करिअर मार्गदर्शन आणि शैक्षणिक संधींचा मेळावा.',
            'date' => '१८ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ]
];

// Advertisement images (no text, just photos)
$advertisements = [
    [
        'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
        'link' => '#',
        'alt' => 'Advertisement 1'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w-1200&q=80',
        'link' => '#',
        'alt' => 'Advertisement 2'
    ]
];

// Function to generate news card HTML
function generateNewsCard($news) {
    return '
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-0 news-card">
            <div class="position-relative overflow-hidden">
                <img src="' . $news['image'] . '" class="card-img-top" alt="' . htmlspecialchars($news['title']) . '" style="height: 200px; object-fit: cover;">
                <div class="image-overlay"></div>
            </div>
            <div class="card-body p-4">
                <h6 class="card-title fw-bold text-dark mb-3" style="min-height: 60px;">' . htmlspecialchars($news['title']) . '</h6>
                <p class="card-text text-muted small mb-4" style="min-height: 72px; line-height: 1.6;">' . htmlspecialchars($news['summary']) . '</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar3 text-primary me-2"></i>
                        <small class="text-muted">' . $news['date'] . '</small>
                    </div>
                    <a href="#" class="btn btn-sm btn-primary px-3">
                        वाचा <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    ';
}

// Function to generate advertisement card HTML
function generateAdCard($ad) {
    return '
    <div class="col-md-6 mb-4">
        <a href="' . $ad['link'] . '" class="ad-card d-block" target="_blank">
            <div class="card border-0 shadow-sm ad-image-card">
                <img src="' . $ad['image'] . '" class="card-img-top" alt="' . htmlspecialchars($ad['alt']) . '" style="height: 250px; object-fit: cover;">
            </div>
        </a>
    </div>
    ';
}
?>

<!-- Category Cards Container -->
<div class="container-fluid px-0">
    <!-- दिनदिशेष Section -->
    <div class="category-section py-5" id="today_special">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">दिनदिशेष</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['today_special'] as $news): ?>
                    <?php echo generateNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- अमृत घडामोडी Section -->
    <div class="category-section py-5 bg-light" id="amrut_events">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">अमृत घडामोडी</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['amrut_events'] as $news): ?>
                    <?php echo generateNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Advertisement Section - NO HEADING -->
    <div class="ad-section py-5">
        <div class="container">
            <!-- No heading, just 2 advertisement cards -->
            <div class="row justify-content-center g-4">
                <?php foreach ($advertisements as $ad): ?>
                    <?php echo generateAdCard($ad); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Existing styles remain the same */
.categories-navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.category-link.active {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white !important;
    border-radius: 4px;
    padding: 6px 12px !important;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.category-section {
    scroll-margin-top: 120px;
    padding: 40px 0;
}

.section-header {
    text-align: left;
    padding-bottom: 15px;
    border-bottom: 3px solid #0d6efd;
    margin-bottom: 30px;
}

.section-header h2 {
    font-size: 1.8rem;
    position: relative;
    display: inline-block;
}

.news-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e9ecef !important;
    background: white;
}

.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    border-color: #0d6efd !important;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.news-card:hover .image-overlay {
    opacity: 1;
}

.news-card:hover .card-img-top {
    transform: scale(1.05);
}

.card-img-top {
    transition: transform 0.5s ease;
}

.card-body {
    display: flex;
    flex-direction: column;
}

.btn-primary {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7, #0a58ca);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

/* Advertisement Card Styles */
.ad-section {
    padding: 40px 0;
    background: #fff;
}

.ad-card {
    text-decoration: none;
    transition: all 0.3s ease;
}

.ad-card:hover {
    transform: translateY(-5px);
}

.ad-image-card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.ad-image-card:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    border-color: #0d6efd;
}

.ad-image-card img {
    transition: transform 0.5s ease;
}

.ad-card:hover .ad-image-card img {
    transform: scale(1.05);
}

/* Responsive adjustments for advertisements */
@media (max-width: 768px) {
    .categories-navbar {
        position: sticky;
        top: 0;
    }
    
    .category-section {
        scroll-margin-top: 80px;
        padding: 30px 0;
    }
    
    .news-card {
        margin-bottom: 20px;
    }
    
    .section-header h2 {
        font-size: 1.5rem;
    }
    
    .ad-section {
        padding: 30px 0;
    }
    
    .ad-image-card {
        margin-bottom: 20px;
    }
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

.category-section:target {
    animation: highlightSection 2s ease;
}

@keyframes highlightSection {
    0% { background-color: transparent; }
    50% { background-color: rgba(13, 110, 253, 0.05); }
    100% { background-color: transparent; }
}

.categories-navbar.scrolled {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: white;
}

.row.g-4 {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}

@media (min-width: 768px) {
    .col-md-6 {
        flex: 0 0 auto;
        width: 50%;
    }
}

@media (min-width: 992px) {
    .col-lg-4 {
        flex: 0 0 auto;
        width: 33.33333333%;
    }
}

.card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>

<script>
// Existing JavaScript remains the same
function navigateToCategory(categoryValue) {
    const categoryLinks = document.querySelectorAll('.category-link');
    categoryLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-category') === categoryValue) {
            link.classList.add('active');
        }
    });
    
    const section = document.getElementById(categoryValue);
    if (section) {
        section.classList.add('highlight');
        setTimeout(() => {
            section.classList.remove('highlight');
        }, 2000);
        
        section.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.categories-navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
    
    document.querySelectorAll('.category-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            
            if (categoryValue === 'today_special' || categoryValue === 'amrut_events') {
                navigateToCategory(categoryValue);
            } else {
                document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                if (categoryValue !== 'home') {
                    // alert('ही श्रेणी सध्या उपलब्ध नाही. केवळ "दिनदिशेष" आणि "अमृत घडामोडी" श्रेण्या उपलब्ध आहेत.');
                }
            }
        });
    });
    
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.substring(1);
        if (hash === 'today_special' || hash === 'amrut_events') {
            navigateToCategory(hash);
        }
    });
    
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        if (hash === 'today_special' || hash === 'amrut_events') {
            setTimeout(() => navigateToCategory(hash), 100);
        }
    }
    
    // Existing "Read" button handlers
    document.querySelectorAll('.news-card .btn-primary').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.news-card');
            const title = card.querySelector('.card-title').textContent;
            // alert('"' + title + '" बातमी वाचण्यासाठी नेण्यात येत आहे...');
        });
    });
    
    // Add click handlers for advertisement cards
    document.querySelectorAll('.ad-card').forEach(ad => {
        ad.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if (href === '#') {
                // alert('जाहिरात क्लिक केली!');
            } else {
                window.open(href, '_blank');
            }
        });
    });
});
</script>