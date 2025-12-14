<?php
// components/top_news_carousel.php

// Remove header() call for components
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// Include database configuration if not already included
if (!isset($conn)) {
    require_once 'db_config.php';
    
    // Ensure UTF-8 connection
    if (method_exists($conn, 'set_charset')) {
        $conn->set_charset("utf8mb4");
    }
}

// Fetch featured news from database
$sql = "SELECT 
            news_id,
            title,
            cover_photo_url,
            summary,
            published_by,
            published_date
        FROM news_articles
        WHERE category_name = 'home' AND is_approved = 1 AND cover_photo_url IS NOT NULL
        AND cover_photo_url <> ''
        ORDER BY published_date DESC
        LIMIT 10";

$result = $conn->query($sql);
$slides = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $slides[] = $row;
    }
}

// Close result set
if ($result) {
    $result->free();
}

// Array of fallback Unsplash images
$fallbackImages = [
    'https://images.unsplash.com/photo-1495020689067-958852a7765e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1542744095-fcf48d80b0fd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1574267432644-f410f8e6b74c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1518837695005-2083093ee35b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1540206395-68808572332f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'
];

// Function to format date with month name
function formatDateWithMonth($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d F Y');
}

// Function to format time
function formatTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('h:i A');
}

// Function to check and get image URL with fallback
function getImageUrl($url, $newsId, $fallbackImages) {
    if (!empty($url) && $url !== null && $url !== '') {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            if (file_exists($url)) {
                return $url;
            }
            
            $basePath = $_SERVER['DOCUMENT_ROOT'] . '/AmrutMaharashtra/';
            $fullPath = $basePath . ltrim($url, '/');
            
            if (file_exists($fullPath)) {
                return $url;
            }
        }
    }
    
    $fallbackIndex = ($newsId % count($fallbackImages));
    return $fallbackImages[$fallbackIndex];
}

// Function to safely output text
function safeOutput($text) {
    if ($text === null) {
        return '';
    }
    
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    }
    
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// If no data found, use fallback
if (empty($slides)) {
    $slides = [
        [
            'news_id' => 1,
            'title' => 'Government Initiative Launch for Maharashtra',
            'cover_photo_url' => '',
            'summary' => 'New development project announced for Maharashtra infrastructure growth with focus on sustainable development and rural connectivity.',
            'published_by' => 'Government Press Bureau',
            'published_date' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'news_id' => 2,
            'title' => 'Economic Development Milestone Achieved',
            'cover_photo_url' => '',
            'summary' => 'Maharashtra records 15% growth in industrial sector this quarter, creating over 50,000 new jobs.',
            'published_by' => 'Economic Affairs Department',
            'published_date' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ],
        [
            'news_id' => 3,
            'title' => 'Health Ministry Announces New Facilities',
            'cover_photo_url' => '',
            'summary' => 'New healthcare facilities to be established across rural Maharashtra, benefiting over 2 million citizens.',
            'published_by' => 'Health Department',
            'published_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
}
?>

<!-- Carousel Component -->
<div class="container my-4 px-3 px-md-0">
    <!-- Carousel Container -->
    <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Carousel Items -->
        <div class="carousel-inner rounded-3 shadow-sm">
            <?php if (!empty($slides)): ?>
                <?php foreach ($slides as $index => $slide): ?>
                    <?php 
                    $imageUrl = getImageUrl($slide['cover_photo_url'], $slide['news_id'], $fallbackImages);
                    $formattedDate = formatDateWithMonth($slide['published_date']);
                    $formattedTime = formatTime($slide['published_date']);
                    $isActive = $index === 0 ? 'active' : '';
                    $newsId = $slide['news_id'];
                    
                    // Generate URLs
                    $baseUrl = '/AmrutMaharashtra/';
                    // $viewsUrl = $baseUrl . 'backend/views.php?id=' . $newsId;
                    $viewsUrl = 'backend/views.php?id=' . $newsId;
                    $fullShareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                                   "://$_SERVER[HTTP_HOST]" . $baseUrl . "backend/views.php?id=" . $newsId;
                    
                    // Prepare safe output
                    $title = safeOutput($slide['title']);
                    $summary = safeOutput($slide['summary']);
                    $publishedBy = safeOutput($slide['published_by']);
                    ?>
                    
                    <!-- Slide <?php echo $index + 1; ?> -->
                    <div class="carousel-item <?php echo $isActive; ?>" data-bs-interval="3000">
                        <div class="card border-0 overflow-hidden">
                            <div class="row g-0 flex-md-row flex-column-reverse">
                                <!-- Image Section -->
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="image-container position-relative h-100 d-flex align-items-center justify-content-center">
                                        <img src="<?php echo $imageUrl; ?>" 
                                             class="img-fluid w-100" 
                                             alt="<?php echo $title; ?>" 
                                             style="object-fit: contain; min-height: 320px; max-height: 320px;"
                                             onerror="this.onerror=null; this.src='<?php echo $fallbackImages[0]; ?>';">
                                        <!-- Overlay Gradient -->
                                        <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-to-top from-black/50 to-transparent p-3 d-md-none">
                                            <h5 class="text-white mb-1 fw-bold"><?php echo $title; ?></h5>
                                            <small class="text-white-80"><?php echo $formattedDate; ?> • <?php echo $formattedTime; ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- News Content Section -->
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="card-body p-3 p-md-4 h-100 d-flex flex-column position-relative">
                                        <!-- विशेष Badge -->
                                        <div class="special-badge position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-warning text-dark fs-6 px-3 py-2 fw-bold">
                                                विशेष
                                            </span>
                                        </div>
                                        
                                        <!-- News Title -->
                                        <h4 class="card-title text-primary fw-bold mb-3 mt-2 d-none d-md-block">
                                            <?php echo $title; ?>
                                        </h4>
                                        
                                        <!-- News Content/Summary -->
                                        <div class="news-content flex-grow-1 mt-2 mt-md-0">
                                            <p class="card-text fs-5 d-none d-md-block" style="text-align: justify; line-height: 1.6; color: #495057;">
                                                <?php 
                                                if (mb_strlen($summary) > 220) {
                                                    echo mb_substr($summary, 0, 220) . '...';
                                                } else {
                                                    echo $summary;
                                                }
                                                ?>
                                            </p>
                                            <!-- Mobile Summary -->
                                            <p class="card-text d-md-none" style="line-height: 1.5; color: #495057;">
                                                <?php 
                                                if (mb_strlen($summary) > 150) {
                                                    echo mb_substr($summary, 0, 150) . '...';
                                                } else {
                                                    echo $summary;
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        
                                        <!-- Publisher and Date Section with Share Button -->
                                        <div class="publisher-section mt-3 mt-md-4 pt-3 border-top">
                                            <div class="row align-items-center g-2">
                                                <!-- Publisher Name -->
                                                <div class="col-md-7 col-12">
                                                    <div class="d-flex align-items-center">
                                                        <div class="publisher-icon bg-light rounded-circle p-2 me-3 d-none d-md-flex">
                                                            <i class="bi bi-person-fill text-primary fs-4"></i>
                                                        </div>
                                                        <div class="w-100">
                                                            <a href="#" class="publisher-name text-decoration-none" 
                                                               data-news-id="<?php echo $newsId; ?>"
                                                               onclick="viewPublisherNews(event, <?php echo $newsId; ?>, '<?php echo addslashes($publishedBy); ?>')">
                                                                <p class="mb-0 fw-bold text-truncate text-dark hover-primary">
                                                                    <?php echo $publishedBy; ?>
                                                                </p>
                                                            </a>
                                                            <!-- Date & Time -->
                                                            <div class="d-flex align-items-center mt-1">
                                                                <i class="bi bi-calendar3 text-secondary me-2"></i>
                                                                <span class="text-muted small">
                                                                    <?php echo $formattedDate; ?> • <?php echo $formattedTime; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Action Buttons: Read More & Share -->
                                                <div class="col-md-5 col-12">
                                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                                        <!-- Share Button -->
                                                        <button class="share-btn btn btn-sm btn-outline-secondary" 
                                                                data-news-id="<?php echo $newsId; ?>"
                                                                data-news-title="<?php echo $title; ?>"
                                                                data-share-url="<?php echo $fullShareUrl; ?>"
                                                                onclick="shareNews(event, <?php echo $newsId; ?>, '<?php echo addslashes($title); ?>', '<?php echo $fullShareUrl; ?>')"
                                                                title="Share this news">
                                                            <i class="bi bi-share me-1"></i> Share
                                                        </button>
                                                        
                                                        <!-- Read More Button -->
                                                        <a href="<?php echo $viewsUrl; ?>" 
                                                           class="read-more-btn btn btn-sm btn-outline-primary"
                                                           data-news-id="<?php echo $newsId; ?>"
                                                           onclick="viewNewsDetail(event, <?php echo $newsId; ?>)">
                                                            Read More <i class="bi bi-arrow-right ms-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback when no data -->
                <div class="carousel-item active">
                    <div class="card border-0">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="card-body p-4 p-md-5 text-center">
                                    <i class="bi bi-newspaper display-5 text-muted"></i>
                                    <h5 class="mt-3 text-muted">No Featured News Available</h5>
                                    <p class="text-muted small">Check back later for updates</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <?php if (count($slides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Custom Styles for Carousel -->
<style>
    /* Remove indicators (dots) */
    .carousel-indicators {
        display: none;
    }
    
    /* Adjust arrow positions */
    .carousel-control-prev {
        left: 5px;
        width: 40px;
        height: 40px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(249, 115, 22, 0.8);
        border-radius: 50%;
        opacity: 0.8;
        border: 2px solid white;
    }
    
    .carousel-control-next {
        right: 5px;
        width: 40px;
        height: 40px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(249, 115, 22, 0.8);
        border-radius: 50%;
        opacity: 0.8;
        border: 2px solid white;
    }
    
    /* Style arrows */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-size: 40%;
        width: 100%;
        height: 100%;
        filter: brightness(0) invert(1);
    }
    
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        background: rgba(249, 115, 22, 1);
        opacity: 1;
    }
    
    /* विशेष Badge Styling */
    .special-badge .badge {
        background: linear-gradient(135deg, #ff9800, #ff5722);
        box-shadow: 0 4px 6px rgba(255, 87, 34, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        letter-spacing: 1px;
        font-size: 0.9rem !important;
        padding: 0.4rem 0.8rem !important;
    }
    
    .special-badge .badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(255, 87, 34, 0.4);
        background: linear-gradient(135deg, #ff5722, #ff9800);
    }
    
    /* Card styling */
    .card {
        border: 1px solid #e9ecef !important;
        height: auto;
        min-height: 320px;
        transition: all 0.3s ease;
    }
    
    .card-body {
        background: #ffffff;
    }
    
    /* Hover effects for publisher name */
    .publisher-name .hover-primary {
        transition: all 0.2s ease;
        color: #212529 !important;
    }
    
    .publisher-name:hover .hover-primary {
        color: #f97316 !important;
        transform: translateX(3px);
    }
    
    /* Read More button hover effect */
    .read-more-btn {
        transition: all 0.3s ease;
        border-width: 1.5px;
    }
    
    .read-more-btn:hover {
        background-color: #f97316;
        color: white !important;
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(249, 115, 22, 0.3);
    }
    
    /* Share button styling */
    .share-btn {
        transition: all 0.3s ease;
        border-width: 1.5px;
        color: #6c757d;
        border-color: #6c757d;
    }
    
    .share-btn:hover {
        background-color: #6c757d;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    }
    
    .share-btn i {
        transition: transform 0.3s ease;
    }
    
    .share-btn:hover i {
        transform: rotate(-20deg);
    }
    
    /* Image container - UPDATED FOR FULL IMAGE DISPLAY */
    .image-container {
        overflow: hidden;
        background-color: #f8f9fa; /* Background color for empty spaces */
        position: relative;
    }
    
    .image-container img {
        object-fit: contain !important; /* Changed from 'cover' to 'contain' */
        width: auto;
        max-width: 100%;
        height: auto;
        max-height: 320px;
        display: block;
        margin: 0 auto; /* Center the image */
    }
    
    /* Gradient overlay for mobile */
    .bg-gradient-to-top {
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
    }
    
    /* Publisher icon styling */
    .publisher-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    /* Action buttons container */
    .gap-2 {
        gap: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .carousel-inner {
            border-radius: 0.5rem !important;
        }
        
        .carousel-item .row > div {
            height: auto;
        }
        
        .special-badge {
            position: relative !important;
            margin: 0.5rem 0 !important;
            text-align: left;
            display: inline-block;
        }
        
        .special-badge .badge {
            font-size: 0.8rem !important;
            padding: 0.3rem 0.6rem !important;
        }
        
        .card-title {
            font-size: 1.25rem !important;
        }
        
        .card-text.fs-5 {
            font-size: 1rem !important;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            width: 35px;
            height: 35px;
        }
        
        .image-container {
            min-height: 250px;
            max-height: 250px;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        .image-container img {
            max-height: 250px;
            min-height: 250px;
            object-fit: contain !important;
            width: auto;
            max-width: 100%;
        }
        
        .card-body {
            padding: 1.25rem !important;
        }
        
        .publisher-section {
            margin-top: 1.25rem !important;
            padding-top: 1.25rem !important;
        }
        
        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .publisher-name:hover .hover-primary {
            transform: none;
        }
        
        /* Stack buttons on mobile */
        .col-md-5 .d-flex {
            flex-wrap: wrap;
            justify-content: flex-start !important;
        }
        
        .share-btn, .read-more-btn {
            margin-bottom: 0.25rem;
        }
    }
    
    @media (min-width: 768px) {
        .card {
            height: 320px;
        }
        
        .image-container {
            border-radius: 0.375rem 0 0 0.375rem;
            min-height: 320px;
            max-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .image-container img {
            border-radius: 0.375rem 0 0 0.375rem;
            min-height: 320px;
            max-height: 320px;
            object-fit: contain !important;
            width: auto;
            max-width: 100%;
        }
        
        .card-body {
            border-radius: 0 0.375rem 0.375rem 0;
        }
        
        /* More content space */
        .news-content {
            max-height: 160px;
            overflow: hidden;
        }
        
        /* Card hover effect */
        .carousel-item .card {
            transition: all 0.3s ease;
        }
        
        .carousel-item:hover .card {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.1) !important;
            border-color: #f97316 !important;
        }
        
        /* Button hover effects for desktop */
        .share-btn:hover, .read-more-btn:hover {
            transform: translateY(-3px);
        }
    }
    
    /* Desktop specific optimizations */
    @media (min-width: 992px) {
        .carousel-control-prev {
            left: 15px;
            width: 45px;
            height: 45px;
        }
        
        .carousel-control-next {
            right: 15px;
            width: 45px;
            height: 45px;
        }
        
        .card-title {
            font-size: 1.6rem;
            line-height: 1.3;
        }
        
        .image-container {
            min-height: 340px;
            max-height: 340px;
        }
        
        .image-container img {
            min-height: 340px;
            max-height: 340px;
        }
    }
    
    /* Text truncation for long titles */
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Smooth transitions */
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }
    
    /* Consistent theme colors */
    .text-primary {
        color: #f97316 !important;
    }
    
    .btn-outline-primary {
        color: #f97316;
        border-color: #f97316;
    }
    
    .btn-outline-primary:hover {
        background-color: #f97316;
        border-color: #f97316;
    }
    
    .border-primary {
        border-color: #f97316 !important;
    }
</style>

<!-- JavaScript for Carousel, Navigation, and Share Functionality -->
<script>
// Global flag to prevent double navigation
let isNavigating = false;

// Function to handle Read More button click
function viewNewsDetail(event, newsId) {
    event.preventDefault();
    
    // Prevent multiple clicks
    if (isNavigating) {
        console.log('Already navigating, please wait...');
        return;
    }
    
    isNavigating = true;
    
    console.log('Viewing news detail for ID:', newsId);
    
    // Get the URL from the clicked button
    const button = event.currentTarget;
    const newsUrl = button.getAttribute('href');
    
    // Redirect to news detail page
    window.location.href = newsUrl;
    
    // Reset navigation flag after 2 seconds (in case redirect fails)
    setTimeout(() => {
        isNavigating = false;
    }, 2000);
}

// Function to handle Publisher name click
function viewPublisherNews(event, newsId, publisherName) {
    event.preventDefault();
    
    // Get the publisher name from the clicked element
    console.log('Viewing all news from publisher:', publisherName);
    console.log('Current news ID:', newsId);
    
    // Optional: Redirect to publisher page
    // window.location.href = `publisher.php?name=${encodeURIComponent(publisherName)}`;
}

// Function to share news - FIXED VERSION
function shareNews(event, newsId, newsTitle, shareUrl) {
    event.preventDefault();
    event.stopPropagation();
    
    console.log('Sharing news:', {
        id: newsId,
        title: newsTitle,
        url: shareUrl
    });
    
    // Check if Web Share API is available (mobile devices)
    if (navigator.share) {
        // Use native share dialog on mobile devices
        navigator.share({
            title: newsTitle,
            text: `Check out this news: ${newsTitle}`,
            url: shareUrl
        })
        .then(() => console.log('News shared successfully'))
        .catch((error) => {
            console.log('Error sharing:', error);
            // Fallback to custom share modal
            showShareModal(newsTitle, shareUrl);
        });
    } else {
        // Fallback for desktop browsers
        showShareModal(newsTitle, shareUrl);
    }
}

// Function to show share modal - FIXED VERSION
function showShareModal(newsTitle, shareUrl) {
    console.log('Showing share modal for:', newsTitle);
    
    // Remove existing modal if any
    const existingModal = document.getElementById('shareModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark fw-bold">
                            <i class="bi bi-share-fill me-2"></i> Share News
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Share this news:</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="shareUrlInput" value="${shareUrl}" readonly>
                                <button class="btn btn-dark" type="button" id="copyUrlBtn">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">Copy the link or share via:</small>
                        </div>
                        
                        <div class="share-buttons d-flex gap-3 justify-content-center">
                            <button class="btn btn-primary share-facebook" data-platform="facebook">
                                <i class="bi bi-facebook"></i> Facebook
                            </button>
                            <button class="btn btn-info text-white share-twitter" data-platform="twitter">
                                <i class="bi bi-twitter"></i> Twitter
                            </button>
                            <button class="btn btn-success share-whatsapp" data-platform="whatsapp">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show modal
    const modalElement = document.getElementById('shareModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Set up modal event listeners after it's shown
    modalElement.addEventListener('shown.bs.modal', function() {
        console.log('Share modal shown');
        
        // Focus and select the URL input
        const urlInput = document.getElementById('shareUrlInput');
        if (urlInput) {
            urlInput.focus();
            urlInput.select();
        }
        
        // Copy URL button
        const copyBtn = document.getElementById('copyUrlBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const input = document.getElementById('shareUrlInput');
                if (input) {
                    input.select();
                    navigator.clipboard.writeText(input.value).then(() => {
                        // Show feedback
                        const originalText = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="bi bi-check-circle"></i> Copied!';
                        copyBtn.classList.remove('btn-dark');
                        copyBtn.classList.add('btn-success');
                        
                        setTimeout(() => {
                            copyBtn.innerHTML = originalText;
                            copyBtn.classList.remove('btn-success');
                            copyBtn.classList.add('btn-dark');
                        }, 2000);
                    }).catch(err => {
                        // Fallback for older browsers
                        document.execCommand('copy');
                        
                        const originalText = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="bi bi-check-circle"></i> Copied!';
                        copyBtn.classList.remove('btn-dark');
                        copyBtn.classList.add('btn-success');
                        
                        setTimeout(() => {
                            copyBtn.innerHTML = originalText;
                            copyBtn.classList.remove('btn-success');
                            copyBtn.classList.add('btn-dark');
                        }, 2000);
                    });
                }
            });
        }
        
        // Facebook share button
        const facebookBtn = modalElement.querySelector('.share-facebook');
        if (facebookBtn) {
            facebookBtn.addEventListener('click', function() {
                const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        }
        
        // Twitter share button
        const twitterBtn = modalElement.querySelector('.share-twitter');
        if (twitterBtn) {
            twitterBtn.addEventListener('click', function() {
                const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(newsTitle)}&url=${encodeURIComponent(shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        }
        
        // WhatsApp share button
        const whatsappBtn = modalElement.querySelector('.share-whatsapp');
        if (whatsappBtn) {
            whatsappBtn.addEventListener('click', function() {
                const url = `https://wa.me/?text=${encodeURIComponent(newsTitle + ' ' + shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        }
    });
    
    // Clean up modal when closed
    modalElement.addEventListener('hidden.bs.modal', function() {
        console.log('Share modal hidden');
        if (modalElement && modalElement.parentNode) {
            modalElement.parentNode.removeChild(modalElement);
        }
    });
}

// Initialize carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('newsCarousel');
    const slides = <?php echo count($slides); ?>;
    
    console.log('Carousel initialized with', slides, 'slides');
    
    if (slides > 1) {
        // Pause carousel on hover (desktop only)
        if (window.innerWidth >= 768) {
            carousel.addEventListener('mouseenter', function() {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.pause();
                }
            });
            
            carousel.addEventListener('mouseleave', function() {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.cycle();
                }
            });
        }
        
        // Initialize carousel with options
        const carouselInstance = new bootstrap.Carousel(carousel, {
            interval: 6000,
            wrap: true,
            touch: true,
            pause: window.innerWidth >= 768 ? 'hover' : false
        });
        
        console.log('Carousel instance created');
    }
    
    // Adjust carousel for mobile/desktop on resize
    window.addEventListener('resize', function() {
        const carouselInstance = bootstrap.Carousel.getInstance(carousel);
        if (carouselInstance) {
            carouselInstance._config.pause = window.innerWidth >= 768 ? 'hover' : false;
        }
    });
    
    // Add click event listeners to all Read More buttons
    document.querySelectorAll('.read-more-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const newsId = this.getAttribute('data-news-id');
            console.log('Read More clicked for ID:', newsId);
            viewNewsDetail(e, newsId);
        });
    });
    
    // Add click event listeners to all Share buttons
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const newsId = this.getAttribute('data-news-id');
            const newsTitle = this.getAttribute('data-news-title');
            const shareUrl = this.getAttribute('data-share-url');
            console.log('Share clicked for ID:', newsId, 'Title:', newsTitle);
            shareNews(e, newsId, newsTitle, shareUrl);
        });
    });
    
    // Add click event listeners to all Publisher names
    document.querySelectorAll('.publisher-name').forEach(link => {
        link.addEventListener('click', function(e) {
            const newsId = this.getAttribute('data-news-id');
            const publisherName = this.querySelector('.hover-primary').textContent;
            viewPublisherNews(e, newsId, publisherName);
        });
    });
    
    console.log('Event listeners attached');
});

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    const carousel = document.getElementById('newsCarousel');
    const carouselInstance = bootstrap.Carousel.getInstance(carousel);
    
    if (carouselInstance && <?php echo count($slides); ?> > 1) {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            carouselInstance.prev();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            carouselInstance.next();
        }
    }
    
    // Close modal on Escape key
    if (e.key === 'Escape') {
        const modal = document.getElementById('shareModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    }
});

// Handle page visibility to reset navigation flag
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        isNavigating = false;
    }
});
</script>