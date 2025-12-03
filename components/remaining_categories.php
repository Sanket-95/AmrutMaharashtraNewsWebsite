<?php
// components/remaining_categories.php

// Get database connection if not already available
if (!isset($conn)) {
    require_once 'components/db_config.php';
}

// Define the remaining categories
$categories = [
    ['label' => 'लाभार्थी स्टोरी', 'value' => 'beneficiary_story'],
    ['label' => 'यशस्वी उद्योजक', 'value' => 'successful_entrepreneur'],
    ['label' => 'शब्दामृत', 'value' => 'words_amrut'],
    ['label' => 'स्मार्ट शेतकरी', 'value' => 'smart_farmer']
];

// Dummy news data for each category (3 items per category)
$dummyNews = [
    'beneficiary_story' => [
        [
            'title' => 'सरकारी योजनेने बदलले आयुष्य',
            'summary' => 'अन्नधान्य योजनेचा लाभ घेऊन कुटुंबाचे आर्थिक स्थितीत सुधारणा.',
            'date' => '५ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'आरोग्य योजनेचा लाभ घेऊन बदल',
            'summary' => 'आयुष्मान भारत योजनेने दिलेल्या उपचारांनी आरोग्यात सुधारणा.',
            'date' => '१२ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1516549655669-df6654e435de?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'शैक्षणिक कर्ज माफीचा लाभ',
            'summary' => 'विद्यार्थी कर्ज माफी योजनेने शिक्षण पूर्ण करण्यास मदत.',
            'date' => '१८ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ],
    'successful_entrepreneur' => [
        [
            'title' => 'तरुण उद्योजकाचा यशस्वी प्रवास',
            'summary' => 'स्टार्टअप सुरू करून वार्षिक कोट्यवधीचा उलाढाल करणारा उद्योजक.',
            'date' => '३ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'महिला उद्योजकाचे यशोगीत',
            'summary' => 'घरगुती उत्पादनापासून मोठ्या उद्योगापर्यंतचा प्रवास.',
            'date' => '१० जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'कृषी उद्योजकतेत नाविन्य',
            'summary' => 'आधुनिक शेती पद्धतींचा वापर करून उत्पादनात वाढ.',
            'date' => '२० जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ],
    'words_amrut' => [
        [
            'title' => 'अमृत महाराष्ट्र: नवीन संकल्पना',
            'summary' => 'राज्याच्या विकासासाठी नवीन दृष्टीकोन आणि संकल्पना.',
            'date' => '२ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'समाज विकासाचे मार्गदर्शक तत्त्वे',
            'summary' => 'समाजातील सर्व घटकांच्या समावेशक विकासाची संकल्पना.',
            'date' => '९ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'युवा पिढीसाठी प्रेरणादायी विचार',
            'summary' => 'तरुण पिढीला प्रेरणा देणारे विचार आणि संदेश.',
            'date' => '१६ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ],
    'smart_farmer' => [
        [
            'title' => 'ड्रिप सिंचन प्रणालीचा यशस्वी वापर',
            'summary' => 'पाण्याची बचत करताना उत्पादनात वाढ करणारा शेतकरी.',
            'date' => '४ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'जैविक शेतीतून उत्पन्न वाढ',
            'summary' => 'जैविक पद्धतीने शेती करून आरोग्यदायी उत्पादन.',
            'date' => '११ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1586771107445-d3ca888129fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ],
        [
            'title' => 'तंत्रज्ञानाचा वापर करणारा शेतकरी',
            'summary' => 'मोबाईल ॲप आणि आधुनिक यंत्रसामग्रीचा वापर.',
            'date' => '१९ जानेवारी २०२४',
            'image' => 'https://images.unsplash.com/photo-1516937941344-00b4e0337589?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
        ]
    ]
];

// Function to generate news card HTML - RENAMED to avoid conflict
function generateRemainingNewsCard($news) {
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
?>

<!-- Remaining Categories Container -->
<div class="container-fluid px-0">
    <!-- लाभार्थी स्टोरी Section -->
    <div class="category-section py-5" id="beneficiary_story">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">लाभार्थी स्टोरी</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['beneficiary_story'] as $news): ?>
                    <?php echo generateRemainingNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- यशस्वी उद्योजक Section -->
    <div class="category-section py-5 bg-light" id="successful_entrepreneur">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">यशस्वी उद्योजक</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['successful_entrepreneur'] as $news): ?>
                    <?php echo generateRemainingNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- शब्दामृत Section -->
    <div class="category-section py-5" id="words_amrut">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">शब्दामृत</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['words_amrut'] as $news): ?>
                    <?php echo generateRemainingNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- स्मार्ट शेतकरी Section -->
    <div class="category-section py-5 bg-light" id="smart_farmer">
        <div class="container">
            <!-- Only Category Name -->
            <div class="section-header mb-4">
                <h2 class="text-primary fw-bold">स्मार्ट शेतकरी</h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($dummyNews['smart_farmer'] as $news): ?>
                    <?php echo generateRemainingNewsCard($news); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* All styles from category_cards.php remain the same */
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

/* Responsive adjustments */
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
// Function to handle category navigation for these categories
function navigateToRemainingCategory(categoryValue) {
    // Update active state in navbar
    const categoryLinks = document.querySelectorAll('.category-link');
    categoryLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-category') === categoryValue) {
            link.classList.add('active');
        }
    });
    
    // Scroll to the section
    const section = document.getElementById(categoryValue);
    if (section) {
        // Add highlight animation
        section.classList.add('highlight');
        setTimeout(() => {
            section.classList.remove('highlight');
        }, 2000);
        
        // Smooth scroll
        section.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle category clicks from navbar for these categories
    document.querySelectorAll('.category-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            
            // Check if it's one of our remaining categories
            const remainingCategories = ['beneficiary_story', 'successful_entrepreneur', 'words_amrut', 'smart_farmer'];
            
            if (remainingCategories.includes(categoryValue)) {
                navigateToRemainingCategory(categoryValue);
            } else if (categoryValue === 'today_special' || categoryValue === 'amrut_events') {
                // These are handled by the other file
                // Just update active state
                document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            } else {
                // For other categories, just update active state
                document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Show message for non-implemented categories
                if (categoryValue !== 'home') {
                    // alert('ही श्रेणी सध्या उपलब्ध नाही.');
                }
            }
        });
    });
    
    // Handle hash changes (direct URL navigation)
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.substring(1);
        const remainingCategories = ['beneficiary_story', 'successful_entrepreneur', 'words_amrut', 'smart_farmer'];
        
        if (remainingCategories.includes(hash)) {
            navigateToRemainingCategory(hash);
        }
    });
    
    // Check initial hash
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const remainingCategories = ['beneficiary_story', 'successful_entrepreneur', 'words_amrut', 'smart_farmer'];
        
        if (remainingCategories.includes(hash)) {
            setTimeout(() => navigateToRemainingCategory(hash), 100);
        }
    }
    
    // Add click handlers to "Read" buttons
    document.querySelectorAll('.news-card .btn-primary').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.news-card');
            const title = card.querySelector('.card-title').textContent;
            // alert('"' + title + '" बातमी वाचण्यासाठी नेण्यात येत आहे...');
        });
    });
});
</script>