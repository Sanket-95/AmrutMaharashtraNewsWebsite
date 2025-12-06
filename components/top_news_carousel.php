<?php
// components/carousel.php

// Include database configuration if not already included
if (!isset($conn)) {
    require_once 'components/db_config.php';
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
        WHERE category_name = 'today_special' AND is_approved = 1
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

// If no data found, use fallback
if (empty($slides)) {
    $slides = [
        [
            'news_id' => 1,
            'title' => 'Government Initiative Launch for Maharashtra',
            'cover_photo_url' => 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'summary' => 'New development project announced for Maharashtra infrastructure growth with focus on sustainable development and rural connectivity.',
            'published_by' => 'Government Press Bureau',
            'published_date' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'news_id' => 2,
            'title' => 'Economic Development Milestone Achieved',
            'cover_photo_url' => 'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'summary' => 'Maharashtra records 15% growth in industrial sector this quarter, creating over 50,000 new jobs.',
            'published_by' => 'Economic Affairs Department',
            'published_date' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ],
        [
            'news_id' => 3,
            'title' => 'Health Ministry Announces New Facilities',
            'cover_photo_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'summary' => 'New healthcare facilities to be established across rural Maharashtra, benefiting over 2 million citizens.',
            'published_by' => 'Health Department',
            'published_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
}

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

// Function to check and get image URL
function getImageUrl($url) {
    if (empty($url) || $url === null) {
        return 'https://images.unsplash.com/photo-1495020689067-958852a7765e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
    }
    
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    
    if (strpos($url, 'http') !== 0) {
        $url = ltrim($url, '/');
        return '/' . $url;
    }
    
    return $url;
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
                    $imageUrl = getImageUrl($slide['cover_photo_url']);
                    $formattedDate = formatDateWithMonth($slide['published_date']);
                    $formattedTime = formatTime($slide['published_date']);
                    $isActive = $index === 0 ? 'active' : '';
                    $newsId = $slide['news_id'];
                    // Generate share URL
                    $shareUrl = "news.php?id=" . $newsId;
                    $fullShareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/news.php?id=" . $newsId;
                    ?>
                    
                    <!-- Slide <?php echo $index + 1; ?> -->
                    <div class="carousel-item <?php echo $isActive; ?>" data-bs-interval="3000">
                        <div class="card border-0 overflow-hidden">
                            <div class="row g-0 flex-md-row flex-column-reverse">
                                <!-- Desktop: Image on Left, Content on Right -->
                                <!-- Mobile: Content on Top, Image on Bottom -->
                                
                                <!-- Image Section (Desktop Left / Mobile Bottom) -->
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="image-container position-relative h-100">
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                             class="img-fluid w-100 h-100" 
                                             alt="<?php echo htmlspecialchars($slide['title']); ?>" 
                                             style="object-fit: cover; min-height: 320px; max-height: 320px;"
                                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1495020689067-958852a7765e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';">
                                        <!-- Overlay Gradient for better text readability on image -->
                                        <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-to-top from-black/50 to-transparent p-3 d-md-none">
                                            <h5 class="text-white mb-1 fw-bold"><?php echo htmlspecialchars($slide['title']); ?></h5>
                                            <small class="text-white-80"><?php echo $formattedDate; ?> • <?php echo $formattedTime; ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- News Content Section (Desktop Right / Mobile Top) -->
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="card-body p-3 p-md-4 h-100 d-flex flex-column position-relative">
                                        <!-- विशेष Badge - Top Right Corner -->
                                        <div class="special-badge position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-warning text-dark fs-6 px-3 py-2 fw-bold">
                                                विशेष
                                            </span>
                                        </div>
                                        
                                        <!-- News Title (Hidden on mobile - shown on image overlay) -->
                                        <h4 class="card-title text-primary fw-bold mb-3 mt-2 d-none d-md-block">
                                            <?php echo htmlspecialchars($slide['title']); ?>
                                        </h4>
                                        
                                        <!-- News Content/Summary -->
                                        <div class="news-content flex-grow-1 mt-2 mt-md-0">
                                            <p class="card-text fs-5 d-none d-md-block" style="text-align: justify; line-height: 1.6; color: #495057;">
                                                <?php 
                                                $summary = htmlspecialchars($slide['summary']);
                                                if (strlen($summary) > 220) { // Increased character limit for taller cards
                                                    $summary = substr($summary, 0, 220) . '...';
                                                }
                                                echo $summary;
                                                ?>
                                            </p>
                                            <!-- Mobile Summary (shorter) -->
                                            <p class="card-text d-md-none" style="line-height: 1.5; color: #495057;">
                                                <?php 
                                                $summary = htmlspecialchars($slide['summary']);
                                                if (strlen($summary) > 150) { // Increased for mobile too
                                                    $summary = substr($summary, 0, 150) . '...';
                                                }
                                                echo $summary;
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
                                                               onclick="viewPublisherNews(event, <?php echo $newsId; ?>, '<?php echo htmlspecialchars(addslashes($slide['published_by'])); ?>')">
                                                                <p class="mb-0 fw-bold text-truncate text-dark hover-primary">
                                                                    <?php echo htmlspecialchars($slide['published_by']); ?>
                                                                </p>
                                                            </a>
                                                            <!-- Date & Time in same line -->
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
                                                                data-news-title="<?php echo htmlspecialchars($slide['title']); ?>"
                                                                data-share-url="<?php echo $fullShareUrl; ?>"
                                                                onclick="shareNews(event, <?php echo $newsId; ?>, '<?php echo htmlspecialchars(addslashes($slide['title'])); ?>', '<?php echo $fullShareUrl; ?>')"
                                                                title="Share this news">
                                                            <i class="bi bi-share me-1"></i> Share
                                                        </button>
                                                        
                                                        <!-- Read More Button -->
                                                        <a href="news.php?id=<?php echo $newsId; ?>" 
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
        
        <!-- Navigation Arrows (only show if there are slides) -->
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
    
    /* Card styling - Increased height */
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
    
    /* Image container */
    .image-container {
        overflow: hidden;
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
        
        .image-container img {
            min-height: 250px;
            max-height: 250px;
            border-radius: 0 0 0.5rem 0.5rem;
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
        
        .image-container img {
            border-radius: 0.375rem 0 0 0.375rem;
            min-height: 320px;
            max-height: 320px;
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
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('newsCarousel');
    const slides = <?php echo count($slides); ?>;
    
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
        new bootstrap.Carousel(carousel, {
            interval: 6000,
            wrap: true,
            touch: true,
            pause: window.innerWidth >= 768 ? 'hover' : false
        });
    }
    
    // Adjust carousel for mobile/desktop on resize
    window.addEventListener('resize', function() {
        const carouselInstance = bootstrap.Carousel.getInstance(carousel);
        if (carouselInstance) {
            carouselInstance._config.pause = window.innerWidth >= 768 ? 'hover' : false;
        }
    });
});

// Function to handle Read More button click
function viewNewsDetail(event, newsId) {
    event.preventDefault();
    
    // Get the current news ID
    console.log('Viewing news detail for ID:', newsId);
    
    // Construct the URL
    const newsUrl = `news.php?id=${newsId}`;
    
    // Redirect to news detail page
    window.location.href = newsUrl;
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

// Function to share news
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

// Function to show share modal
function showShareModal(newsTitle, shareUrl) {
    // Create modal HTML with updated colors
    const modalHTML = `
        <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark fw-bold">
                            <i class="bi bi-share-fill me-2"></i> Share News
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    
    // Remove existing modal if any
    const existingModal = document.getElementById('shareModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show modal
    const modalElement = document.getElementById('shareModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Focus on input
    setTimeout(() => {
        const input = document.getElementById('shareUrlInput');
        if (input) input.select();
    }, 500);
    
    // Add event listeners after modal is shown
    modalElement.addEventListener('shown.bs.modal', function() {
        // Copy URL button
        const copyBtn = document.getElementById('copyUrlBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const input = document.getElementById('shareUrlInput');
                input.select();
                document.execCommand('copy');
                
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
            });
        }
        
        // Social share buttons
        document.querySelectorAll('.share-facebook').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        });
        
        document.querySelectorAll('.share-twitter').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(newsTitle)}&url=${encodeURIComponent(shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        });
        
        document.querySelectorAll('.share-whatsapp').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = `https://wa.me/?text=${encodeURIComponent(newsTitle + ' ' + shareUrl)}`;
                window.open(url, '_blank', 'width=600,height=400');
            });
        });
    });
    
    // Clean up modal when closed
    modalElement.addEventListener('hidden.bs.modal', function() {
        modalElement.remove();
    });
}

// Add click event listeners to all Read More buttons
document.querySelectorAll('.read-more-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        const newsId = this.getAttribute('data-news-id');
        viewNewsDetail(e, newsId);
    });
});

// Add click event listeners to all Share buttons
document.querySelectorAll('.share-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        const newsId = this.getAttribute('data-news-id');
        const newsTitle = this.getAttribute('data-news-title');
        const shareUrl = this.getAttribute('data-share-url');
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

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    const carousel = document.getElementById('newsCarousel');
    const carouselInstance = bootstrap.Carousel.getInstance(carousel);
    
    if (carouselInstance && slides > 1) {
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
</script>