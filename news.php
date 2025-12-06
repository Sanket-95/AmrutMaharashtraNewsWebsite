<?php
// news.php
session_start();

// Database connection
include 'components/db_config.php';

// Get news ID from URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch news details from database
$query = "SELECT 
    news_id,
    district_name,
    category_name,
    title,
    cover_photo_url,
    secondary_photo_url,
    summary,
    content,
    published_by,
    published_date,
    view
FROM news_articles 
WHERE news_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5 text-center'><h2>न्यूज सापडली नाही</h2><a href='index.php' class='btn btn-primary mt-3'>होमपेज वर जा</a></div>";
    include 'components/footer.php';
    exit();
}

$news = $result->fetch_assoc();

// Format dates
$published_date = date('d-m-Y', strtotime($news['published_date']));
$published_time = date('h:i A', strtotime($news['published_date']));

// Fetch only APPROVED comments (approve = 1) with LIMIT 15
$comments_query = "SELECT 
    comment_id, 
    name, 
    email, 
    comment, 
    DATE_FORMAT(comment_date, '%d-%m-%Y %h:%i %p') as formatted_date
FROM news_comments 
WHERE news_id = ? 
    AND approve = 1 
ORDER BY comment_date DESC 
LIMIT 15";

$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $news_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Marathi category names mapping
$marathi_categories = [
    'today_special' => 'दिनदिशेष',
    'amrut_events' => 'अमृत घडामोडी',
    'beneficiary_story' => 'लाभार्थी स्टोरी',
    'successful_entrepreneur' => 'यशस्वी उद्योजक',
    'words_amrut' => 'शब्दामृत',
    'smart_farmer' => 'स्मार्ट शेतकरी',
    'capable_student' => 'सक्षम दिद्यार्थी',
    'spirituality' => 'अध्यात्म',
    'social_situation' => 'सामाजिक परिस्थिती',
    'women_power' => 'स्त्रीशक्ती',
    'tourism' => 'पर्यटन',
    'amrut_service' => 'अमृत सेवा कार्य',
    'about_us' => 'आमच्या दिशयी',
    'home' => 'मुख्यपृष्ठ'
];

// Get Marathi category name
$category_marathi = $marathi_categories[$news['category_name']] ?? 'अमृत कार्यदीप';

// Include header
include 'components/header.php';
include 'components/navbar.php';

// Current URL for sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Default images
$default_cover_image = 'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
$default_secondary_image = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';

// Get total approved comments count
$count_query = "SELECT COUNT(*) as total_comments FROM news_comments WHERE news_id = ? AND approve = 1";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $news_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_approved_comments = $count_row['total_comments'];
$count_stmt->close();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - अमृत महाराष्ट्र</title>
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        .news-detail-container {
            max-width: 1200px;
            margin: 30px auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .news-header {
            border-bottom: 3px solid #ff6600;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .news-publish {
            border-top: 3px solid #ff6600;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .news-title {
            color: #2c3e50;
            font-weight: 700;
            line-height: 1.4;
            margin: 20px 0;
            font-size: 2rem;
        }
        
        .news-meta {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .news-meta .meta-item {
            position: relative;
            display: inline-block;
            margin: 0 15px;
            cursor: help;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .news-meta .meta-item:hover {
            background: rgba(255, 102, 0, 0.1);
        }
        
        .news-meta .meta-item i {
            color: #ff6600;
            margin-right: 8px;
        }
        
        .news-meta .meta-item strong {
            color: #2c3e50;
        }
        
        .news-meta .meta-item .meta-value {
            color: #666;
        }
        
        /* Tooltip styles */
        .meta-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .meta-tooltip:after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: #2c3e50 transparent transparent transparent;
        }
        
        .news-meta .meta-item:hover .meta-tooltip {
            opacity: 1;
            visibility: visible;
        }
        
        /* Divider between items */
        .meta-divider {
            color: #ccc;
            display: inline-block;
            margin: 0 5px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #f1982bff 0%, #f3cc59ff 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 152, 0, 0.3);
            background: linear-gradient(135deg, #FF9800 0%, #d3b17dff 100%);
        }
        
        .submit-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 5px 10px rgba(255, 152, 0, 0.2);
        }
        
        .submit-btn:disabled {
            background: #cccccc !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .news-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin: 25px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .news-content {
            line-height: 1.8;
            font-size: 18px;
            color: #333;
            text-align: justify;
            margin-top: 25px;
        }
        
        .news-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #ff6600;
            margin: 30px 0;
            font-style: italic;
            color: #555;
            font-size: 17px;
        }
        
        .social-share {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 40px 0;
            text-align: center;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 20px;
        }
        
        .share-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .facebook { background: #3b5998; }
        .twitter { background: #1da1f2; }
        .linkedin { background: #0077b5; }
        .whatsapp { background: #25d366; }
        .copy-link { background: #6c757d; }
        
        .breadcrumb {
            background: transparent;
            padding: 15px 0;
        }
        
        .breadcrumb-item a {
            color: #ff6600;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #666;
        }
        
        .comments-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #dee2e6;
        }
        
        .comment-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        /* Comments list styles */
        .comments-list {
            margin-bottom: 30px;
        }
        
        .comment-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 4px solid #ff6600;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .comment-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6600, #ff9933);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin-right: 15px;
        }
        
        .comment-author {
            flex: 1;
        }
        
        .comment-author h6 {
            margin: 0;
            color: #2c3e50;
        }
        
        .comment-date {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .comment-text {
            color: #333;
            line-height: 1.6;
            margin: 0;
        }
        
        .no-comments {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        }
        
        .comments-count {
            color: #ff6600;
            font-weight: bold;
            background: #fff3e0;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        /* Custom Toastify styles */
        .custom-toast {
            border-radius: 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            padding: 15px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .toast-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .toast-error {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }
        
        .toast-info {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
        }
        
        .toast-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #212529;
        }
        
        @media (max-width: 768px) {
            .news-title {
                font-size: 1.5rem;
            }
            
            .news-content {
                font-size: 16px;
            }
            
            .share-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .news-meta .meta-item {
                display: block;
                margin: 5px 0;
                text-align: center;
            }
            
            .meta-divider {
                display: none;
            }
        }
        
        /* Loading spinner */
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</head>
<body>
    <!-- Breadcrumb Navigation -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php"><i class="bi bi-house-door"></i> मुख्यपृष्ठ</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars(mb_substr($news['title'], 0, 50)) . (mb_strlen($news['title']) > 50 ? '...' : ''); ?>
                </li>
            </ol>
        </nav>
    </div>

    <div class="container news-detail-container">
        <!-- News Header -->
        <div class="news-header">
            <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>     
        </div>

        <!-- Summary -->
        <?php if (!empty($news['summary'])): ?>
        <div class="news-summary">
            <p class="mb-0"><i class="bi bi-quote"></i> <?php echo nl2br(htmlspecialchars($news['summary'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Secondary Photo -->
        <div class="text-center">
            <?php 
            $secondary_photo = !empty($news['secondary_photo_url']) ? htmlspecialchars($news['secondary_photo_url']) : $default_secondary_image;
            ?>
            <img src="<?php echo $secondary_photo; ?>" 
                 alt="<?php echo htmlspecialchars($news['title']); ?> अतिरिक्त फोटो" 
                 class="news-image"
                 style="max-height: 400px;"
                 onerror="this.onerror=null; this.src='<?php echo $default_secondary_image; ?>';">
        </div>

        <!-- Main Content -->
        <div class="news-content">
            <?php echo nl2br(htmlspecialchars($news['content'])); ?>
        </div>

       <!-- News Meta Information -->
        <div class="news-publish">
            <div class="news-meta">
                <div class="text-center">
                    <!-- Publisher -->
                    <span class="meta-item">
                        <i class="bi bi-person-fill"></i>
                        <strong>Publisher:</strong>
                        <span class="meta-value"><?php echo htmlspecialchars($news['published_by']); ?></span>
                        <span class="meta-tooltip">News publisher name</span>
                    </span>
                    
                    <span class="meta-divider">|</span>
                    
                    <!-- Date -->
                    <span class="meta-item">
                        <i class="bi bi-calendar-event"></i>
                        <strong>Date:</strong>
                        <span class="meta-value"><?php echo $published_date; ?></span>
                        <span class="meta-tooltip">News publication date</span>
                    </span>
                    
                    <span class="meta-divider">|</span>
                    
                    <!-- Time -->
                    <span class="meta-item">
                        <i class="bi bi-clock"></i>
                        <strong>Time:</strong>
                        <span class="meta-value"><?php echo $published_time; ?></span>
                        <span class="meta-tooltip">News publication time</span>
                    </span>
                    
                    <span class="meta-divider">|</span>
                    
                    <!-- Views -->
                    <span class="meta-item">
                        <i class="bi bi-eye-fill"></i>
                        <strong>Views:</strong>
                        <span class="meta-value"><?php echo number_format($news['view']); ?></span>
                        <span class="meta-tooltip">Number of times this news has been viewed</span>
                    </span>
                    
                    <?php if (!empty($news['district_name'])): ?>
                    <span class="meta-divider">|</span>
                    
                    <!-- District -->
                    <span class="meta-item">
                        <i class="bi bi-geo-alt"></i>
                        <strong>District:</strong>
                        <span class="meta-value"><?php echo htmlspecialchars($news['district_name']); ?></span>
                        <span class="meta-tooltip">Related district of the news</span>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Social Share Section -->
        <div class="social-share">
            <h4 class="mb-4"><i class="bi bi-share-fill text-primary"></i> ही बातमी शेअर करा</h4>
            
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" 
                   target="_blank" 
                   class="share-btn facebook"
                   title="Facebook वर शेअर करा">
                    <i class="bi bi-facebook"></i>
                </a>
                
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($news['title']); ?>" 
                   target="_blank" 
                   class="share-btn twitter"
                   title="Twitter वर शेअर करा">
                    <i class="bi bi-twitter"></i>
                </a>
                
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($current_url); ?>&title=<?php echo urlencode($news['title']); ?>" 
                   target="_blank" 
                   class="share-btn linkedin"
                   title="LinkedIn वर शेअर करा">
                    <i class="bi bi-linkedin"></i>
                </a>
                
                <a href="https://wa.me/?text=<?php echo urlencode($news['title'] . ' ' . $current_url); ?>" 
                   target="_blank" 
                   class="share-btn whatsapp"
                   title="WhatsApp वर शेअर करा">
                    <i class="bi bi-whatsapp"></i>
                </a>
                
                <button onclick="copyToClipboard()" 
                        class="share-btn copy-link border-0"
                        title="लिंक कॉपी करा">
                    <i class="bi bi-link-45deg"></i>
                </button>
            </div>
            
            <div class="mt-3">
                <small class="text-muted" id="copy-success" style="display: none;">
                    <i class="bi bi-check-circle-fill text-success"></i> लिंक कॉपी झाला!
                </small>
            </div>
        </div>

        <!-- Comments Section -->
       <div class="comments-section">
    <h3 class="mb-4 border-bottom pb-2">
        <i class="bi bi-chat-left-text"></i> Comments 
        <span class="comments-count ms-2"><?php echo $total_approved_comments; ?> Comments</span>
    </h3>
    
    <!-- Comments List -->
    <div class="comments-list" id="commentsContainer">
        <?php if ($comments_result->num_rows > 0): ?>
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div class="comment-avatar">
                            <?php echo mb_substr(htmlspecialchars($comment['name']), 0, 1); ?>
                        </div>
                        <div class="comment-author">
                            <h6><?php echo htmlspecialchars($comment['name']); ?></h6>
                            <div class="comment-date">
                                <i class="bi bi-clock"></i> <?php echo $comment['formatted_date']; ?>
                            </div>
                        </div>
                    </div>
                    <p class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-comments text-center py-5" id="noCommentsMessage">
                <i class="bi bi-chat-left-text display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No comments yet</h4>
                <p class="text-muted">You can start the discussion</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Comment Form -->
    <div class="comment-form">
        <h5 class="mb-4"><i class="bi bi-pencil-square"></i> Write a Comment</h5>
        
        <form id="commentForm" method="POST">
            <input type="hidden" name="news_id" id="news_id" value="<?php echo $news_id; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="col-12">
                    <label for="comment" class="form-label">Your Comment <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="comment" name="comment" rows="5" required></textarea>
                </div>
                
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="saveInfo" name="save_info">
                        <label class="form-check-label" for="saveInfo">
                            Save my name, email, and website for next time
                        </label>
                    </div>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn submit-btn px-4 py-2" id="submitBtn">
                        <i class="bi bi-send"></i> Post Comment
                    </button>
                    <div id="loadingSpinner" class="d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
    </div>

    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Toastify notification function - SET TO 3 SECONDS ONLY
    function showToast(message, type = 'info') {
        const toastClass = `toast-${type}`;
        
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            className: `custom-toast ${toastClass}`,
            stopOnFocus: true,
            escapeMarkup: false,
            style: {
                background: type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' :
                         type === 'error' ? 'linear-gradient(135deg, #dc3545, #e83e8c)' :
                         type === 'warning' ? 'linear-gradient(135deg, #ffc107, #fd7e14)' :
                         'linear-gradient(135deg, #17a2b8, #20c997)'
            }
        }).showToast();
    }
    
    // Copy to clipboard function with toast
    function copyToClipboard() {
        const url = "<?php echo $current_url; ?>";
        
        // Create temporary input element
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        
        // Select and copy text
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        // Remove temporary input
        document.body.removeChild(tempInput);
        
        // Show success toast for 3 seconds
        showToast('लिंक कॉपी झाला!', 'success');
    }
    
    // Handle comment form submission with AJAX
    document.getElementById('commentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const comment = document.getElementById('comment').value.trim();
        
        // Basic validation
        if (!name || !email || !comment) {
            showToast('कृपया सर्व आवश्यक फील्ड भरा', 'error');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('कृपया वैध ईमेल पत्ता टाका', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> सबमिट करत आहे...';
        submitBtn.disabled = true;
        
        // Send AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_comment.php', true);
        
        xhr.onload = function() {
            // Reset button state
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        // Show success message
                        showToast(response.message, 'success');
                        
                        // Clear form
                        document.getElementById('name').value = '';
                        document.getElementById('email').value = '';
                        document.getElementById('comment').value = '';
                        document.getElementById('saveInfo').checked = false;
                        
                        // Add new comment to the list
                        addCommentToList({
                            name: name,
                            comment: comment,
                            formatted_date: 'आत्ताच'
                        });
                        
                        // Hide "no comments" message if it exists
                        const noCommentsMessage = document.getElementById('noCommentsMessage');
                        if (noCommentsMessage) {
                            noCommentsMessage.style.display = 'none';
                        }
                        
                        // Update comments count
                        updateCommentsCount();
                        
                        // Scroll to the new comment
                        setTimeout(() => {
                            const commentsContainer = document.getElementById('commentsContainer');
                            const firstComment = commentsContainer.querySelector('.comment-item');
                            if (firstComment) {
                                firstComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 500);
                    } else {
                        showToast(response.message, 'error');
                    }
                } catch (e) {
                    showToast('त्रुटी: अवैध प्रतिसाद', 'error');
                    console.error('Parse error:', e);
                }
            } else {
                showToast('सर्व्हर त्रुटी: ' + xhr.status, 'error');
            }
        };
        
        xhr.onerror = function() {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            showToast('नेटवर्क त्रुटी', 'error');
        };
        
        // Send the request
        xhr.send(formData);
    });
    
    // Function to add new comment to the list
    function addCommentToList(commentData) {
        const commentsContainer = document.getElementById('commentsContainer');
        
        // Create new comment element
        const commentElement = document.createElement('div');
        commentElement.className = 'comment-item';
        commentElement.innerHTML = `
            <div class="comment-header">
                <div class="comment-avatar">
                    ${commentData.name.charAt(0)}
                </div>
                <div class="comment-author">
                    <h6>${commentData.name}</h6>
                    <div class="comment-date">
                        <i class="bi bi-clock"></i> ${commentData.formatted_date}
                    </div>
                </div>
            </div>
            <p class="comment-text">${commentData.comment}</p>
        `;
        
        // Insert at the top
        commentsContainer.insertBefore(commentElement, commentsContainer.firstChild);
        
        // Remove comments if more than 15
        removeExtraComments();
    }
    
    // Function to remove extra comments if more than 15
    function removeExtraComments() {
        const commentsContainer = document.getElementById('commentsContainer');
        const comments = commentsContainer.querySelectorAll('.comment-item');
        
        if (comments.length > 15) {
            for (let i = 15; i < comments.length; i++) {
                commentsContainer.removeChild(comments[i]);
            }
        }
    }
    
    // Function to update comments count
    function updateCommentsCount() {
        const commentsCountElement = document.querySelector('.comments-count');
        if (commentsCountElement) {
            const currentCount = parseInt(commentsCountElement.textContent.split(' ')[0]) || 0;
            commentsCountElement.textContent = (currentCount + 1) + ' प्रतिक्रिया';
        }
    }
    
    // Clear form function with toast
    function clearForm() {
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('comment').value = '';
        document.getElementById('saveInfo').checked = false;
        showToast('फॉर्म क्लियर झाला', 'info');
    }
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to copy link
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            copyToClipboard();
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            clearForm();
        }
        
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            document.getElementById('commentForm').submit();
        }
    });
    
    // Back to top button functionality
    window.onscroll = function() {
        const scrollBtn = document.getElementById('scrollToTop');
        if (scrollBtn) {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollBtn.style.display = 'block';
            } else {
                scrollBtn.style.display = 'none';
            }
        }
    };
    
    function scrollToTop() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
    
    // Function to handle image errors
    function handleImageError(img) {
        img.onerror = null;
        if (img.classList.contains('cover-photo')) {
            img.src = '<?php echo $default_cover_image; ?>';
        } else {
            img.src = '<?php echo $default_secondary_image; ?>';
        }
        img.alt = 'डीफॉल्ट फोटो';
    }
    
    // Add error handlers to images
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.news-image');
        images.forEach(function(img) {
            img.addEventListener('error', function() {
                handleImageError(this);
            });
        });
        
        // Add click handlers to share buttons with toast
        const shareButtons = document.querySelectorAll('.share-btn');
        shareButtons.forEach(button => {
            if (button.tagName === 'A') {
                button.addEventListener('click', function() {
                    const platform = this.classList.contains('facebook') ? 'Facebook' :
                                  this.classList.contains('twitter') ? 'Twitter' :
                                  this.classList.contains('linkedin') ? 'LinkedIn' :
                                  'WhatsApp';
                    showToast(`${platform} वर शेअर करत आहे...`, 'info');
                });
            }
        });
        
        // Add toast for save info checkbox
        const saveInfoCheckbox = document.getElementById('saveInfo');
        if (saveInfoCheckbox) {
            saveInfoCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    showToast('तुमची माहिती पुढील वेळीसाठी सेव्ह केली जाईल', 'info');
                }
            });
        }
    });
    </script>

    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" 
            id="scrollToTop" 
            class="btn btn-primary rounded-circle position-fixed"
            style="bottom: 30px; right: 30px; width: 50px; height: 50px; display: none; z-index: 1000;">
        <i class="bi bi-arrow-up"></i>
    </button>
</body>
</html>

<?php
// Close database connections
$stmt->close();
$comments_stmt->close();
$conn->close();
include 'components/footer.php';
?>