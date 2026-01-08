<?php
// news.php
session_start();

// Database connection
include 'components/db_config.php';
include 'components/increment_views.php';

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
WHERE news_id = ? and is_approved = 1";

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

// ============ DYNAMIC OG TAGS CONFIGURATION ============
// Determine which image to use for sharing
$share_image_url = '';

// Function to check if image exists and is valid
function isValidImage($url) {
    if (empty($url) || $url === null) {
        return false;
    }
    
    $url = trim($url);
    
    // Check if it's a valid URL format
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        // Check if it's a local file path
        if (file_exists($url)) {
            // Check if it's an image file
            $imageInfo = @getimagesize($url);
            return $imageInfo !== false;
        }
        // Check if it's a relative path
        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($url, '/');
        if (file_exists($absolute_path)) {
            $imageInfo = @getimagesize($absolute_path);
            return $imageInfo !== false;
        }
        return false;
    }
    
    // For remote URLs, return true (will be checked by JS)
    return true;
}

// First priority: cover_photo_url
if (!empty($news['cover_photo_url']) && isValidImage($news['cover_photo_url'])) {
    $share_image_url = $news['cover_photo_url'];
}
// Second priority: secondary_photo_url
elseif (!empty($news['secondary_photo_url']) && isValidImage($news['secondary_photo_url'])) {
    $share_image_url = $news['secondary_photo_url'];
}
// Fallback: default logo
else {
    $share_image_url = 'https://amrutmaharashtra.org/assets/images/logo.png';
}

// Ensure the image URL is absolute
if (!empty($share_image_url) && strpos($share_image_url, 'http') !== 0) {
    // Convert relative URL to absolute URL
    if (strpos($share_image_url, 'photos/') === 0) {
        $share_image_url = 'https://amrutmaharashtra.org/' . $share_image_url;
    } else {
        $share_image_url = 'https://amrutmaharashtra.org/' . ltrim($share_image_url, '/');
    }
}

// Current URL for sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// FIXED VERSION - Use mb_substr for Unicode/Marathi text
$meta_description = !empty($news['summary']) ? 
    mb_substr(strip_tags($news['summary']), 0, 160, 'UTF-8') : 
    mb_substr(strip_tags($news['content']), 0, 160, 'UTF-8');

$meta_description = htmlspecialchars($meta_description . '...');

// Also fix title if needed
$meta_title = htmlspecialchars($news['title']);

// Function to sanitize and format rich text content for display
function formatRichTextForDisplay($content) {
    // Decode HTML entities first
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove script tags and other dangerous elements
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
    $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);
    $content = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $content);
    $content = preg_replace('/<embed\b[^>]*>(.*?)<\/embed>/is', '', $content);
    $content = preg_replace('/on\w+=\s*"[^"]*"/i', '', $content);
    $content = preg_replace('/on\w+=\s*\'[^\']*\'/i', '', $content);
    $content = preg_replace('/style\s*=\s*"[^"]*"/i', '', $content);
    $content = preg_replace('/style\s*=\s*\'[^\']*\'/i', '', $content);
    
    // Allow safe HTML tags
    $allowed_tags = '<p><br><br/><br /><strong><b><em><i><u><a><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6>';
    $content = strip_tags($content, $allowed_tags);
    
    // Preserve manual paragraph spacing - convert multiple line breaks to paragraph breaks
    // First, if content already has <p> tags, clean them up
    if (strpos($content, '<p>') !== false || strpos($content, '</p>') !== false) {
        // Remove empty paragraphs
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        // Ensure proper spacing
        $content = preg_replace('/<\/p>\s*<p>/', '</p><p>', $content);
    } else {
        // If no <p> tags, convert manual spacing to paragraphs
        // Split by multiple newlines (2 or more)
        $paragraphs = preg_split('/\n\s*\n/', trim($content));
        $content = '';
        foreach ($paragraphs as $paragraph) {
            $trimmed = trim($paragraph);
            if (!empty($trimmed)) {
                // Convert single line breaks within paragraph to <br>
                $paragraph_with_breaks = nl2br($trimmed);
                $content .= '<p>' . $paragraph_with_breaks . '</p>' . "\n";
            }
        }
    }
    
    // Clean up attributes on allowed tags
    $content = preg_replace_callback('/(<[^>]+)/', function($matches) {
        $tag = $matches[1];
        // For anchor tags, keep only href, title, target, rel
        if (preg_match('/^<a/i', $tag)) {
            $tag = preg_replace('/(\s(?!href|title|target|rel)[a-z][a-z0-9]*\s*=\s*"[^"]*")/i', '', $tag);
            $tag = preg_replace('/(\s(?!href|title|target|rel)[a-z][a-z0-9]*\s*=\s*\'[^\']*\')/i', '', $tag);
            $tag = preg_replace('/(\s(?!href|title|target|rel)[a-z][a-z0-9]*)/i', '', $tag);
            // Ensure target="_blank" and rel="noopener" for external links
            if (!str_contains($tag, 'target=')) {
                $tag = str_replace('<a', '<a target="_blank" rel="noopener"', $tag);
            }
        }
        // For other tags, remove all attributes
        else {
            $tag = preg_replace('/\s[a-z][a-z0-9]*\s*=\s*"[^"]*"/i', '', $tag);
            $tag = preg_replace('/\s[a-z][a-z0-9]*\s*=\s*\'[^\']*\'/i', '', $tag);
            $tag = preg_replace('/\s[a-z][a-z0-9]*/i', '', $tag);
        }
        return $tag;
    }, $content);
    
    // Final cleanup - remove extra whitespace
    $content = preg_replace('/\s+/', ' ', $content);
    $content = preg_replace('/>\s+</', '><', $content);
    
    return trim($content);
}

// Format the content for display
$formatted_content = formatRichTextForDisplay($news['content']);

// COMMENT PAGINATION CONFIGURATION
$comments_per_page = 5; // Show only 5 comments per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $comments_per_page;

// Get total approved comments count for pagination
$count_query = "SELECT COUNT(*) as total_comments FROM news_comments WHERE news_id = ? AND approve = 1";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $news_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_approved_comments = $count_row['total_comments'];
$count_stmt->close();

// Calculate total pages
$total_pages = ceil($total_approved_comments / $comments_per_page);

// Fetch only APPROVED comments (approve = 1) with pagination (5 per page)
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
LIMIT ? OFFSET ?";

$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("iii", $news_id, $comments_per_page, $offset);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Marathi category names mapping
$marathi_categories = [
    'today_special' => 'दिनविशेष',
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
    'home' => 'मुख्य पृष्ठ',
    'news' => 'वार्ता',
    'articles' => 'लेख'
];

// Get Marathi category name
$category_marathi = $marathi_categories[$news['category_name']] ?? 'अमृत कार्यदीप';

// FETCH RELATED NEWS
$current_date = date('Y-m-d');
$related_query =  "SELECT news_id, title, cover_photo_url, category_name, published_date 
                  FROM `news_articles` 
                  WHERE category_name = ? 
                  AND news_id <> ? 
                  AND is_approved = 1 
                  AND DATE(published_date) <= ?  -- केवळ मागील दिवसाच्या बातम्या
                  ORDER BY published_date DESC 
                  LIMIT 6";

$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("sis", $news['category_name'], $news_id, $current_date);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_news_count = $related_result->num_rows;

// Default images
$default_cover_image = 'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
$default_secondary_image = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
?>

<!DOCTYPE html>
<html lang="mr" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $meta_title; ?> - अमृत महाराष्ट्र</title>
    
    <!-- ========== DYNAMIC OPEN GRAPH META TAGS ========== -->
    <meta property="og:title" content="<?php echo $meta_title; ?>">
    <meta property="og:description" content="<?php echo $meta_description; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($share_image_url); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo $meta_title; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="अमृत महाराष्ट्र">
    <meta property="og:locale" content="mr_IN">
    
    <!-- Article specific OG tags -->
    <meta property="article:published_time" content="<?php echo date('c', strtotime($news['published_date'])); ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($news['published_by']); ?>">
    <meta property="article:section" content="<?php echo htmlspecialchars($news['category_name']); ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $meta_title; ?>">
    <meta name="twitter:description" content="<?php echo $meta_description; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($share_image_url); ?>">
    
    <!-- WhatsApp Specific -->
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($share_image_url); ?>">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="Maharashtra news, <?php echo htmlspecialchars($news['category_name']); ?>, <?php echo htmlspecialchars($news['district_name']); ?>, Amrut Maharashtra">
    <meta name="author" content="<?php echo htmlspecialchars($news['published_by']); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($current_url); ?>">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Include Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Hidden OG image for better WhatsApp sharing -->
    <div class="og-image-placeholder" style="display:none;">
        <img src="<?php echo htmlspecialchars($share_image_url); ?>" 
             alt="<?php echo $meta_title; ?>"
             crossorigin="anonymous">
    </div>
    
    <style>
        .news-detail-container {
            max-width: 1200px;
            margin: 30px auto;
            font-family: 'Mukta', sans-serif;
        }
        
        .news-header {
            border-bottom: 3px solid #ff6600;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .news-publish {
            border-top: 3px solid #ff6600;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .news-title {
            color: #2c3e50;
            font-weight: 700;
            line-height: 1.3;
            margin: 5px 0 15px 0;
            font-size: 2rem;
            font-family: 'Khand', sans-serif;
        }
        
        /* DESKTOP META SECTION - After title, before summary */
        .desktop-meta-section {
            margin-bottom: 20px;
        }
        
        .news-meta {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
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
        
        /* DUAL IMAGE STYLES */
        .images-section {
            margin: 30px 0;
        }
        
        .image-container {
            width: 100%;
            height: 400px; /* Fixed height for both images */
            background-color: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        
        .news-dual-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain; /* Show full image without cropping */
            transition: transform 0.3s ease;
        }
        
        .news-dual-image:hover {
            transform: scale(1.02);
        }
        
        /* Single image container */
        .single-image-container {
            width: 100%;
            height: 450px;
            background-color: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .news-single-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        
        .single-image-label {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(255, 102, 0, 0.9);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Fallback image text */
        .image-fallback-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
            z-index: 1;
        }
        
        .image-fallback-text i {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
            color: #ccc;
        }
        
        .image-fallback-text span {
            display: block;
            font-size: 16px;
        }
        
        /* No images message */
        .no-images-message {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 60px 20px;
            text-align: center;
            border: 2px dashed #dee2e6;
        }
        
        .no-images-icon {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .no-images-message h5 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .no-images-message p {
            color: #888;
            font-size: 14px;
        }
        
        /* Rich Text Content Styling */
        .news-content {
            line-height: 1.8;
            font-size: 18px;
            color: #333;
            text-align: justify;
            margin-top: 25px;
            font-family: 'Mukta', sans-serif;
        }
        
        .news-content p {
            margin-bottom: 1.5em;
            text-align: justify;
            text-indent: 40px;
        }
        
        .news-content p:first-of-type {
            text-indent: 0;
        }
        
        .news-content strong,
        .news-content b {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .news-content em,
        .news-content i {
            font-style: italic;
        }
        
        .news-content u {
            text-decoration: underline;
        }
        
        .news-content a {
            color: #FF6600;
            text-decoration: underline;
            transition: all 0.3s ease;
        }
        
        .news-content a:hover {
            color: #FF8C00;
            text-decoration: underline;
        }
        
        .news-content ul,
        .news-content ol {
            padding-left: 20px;
            margin-bottom: 1.5em;
        }
        
        .news-content li {
            margin-bottom: 0.5em;
        }
        
        .news-content h1,
        .news-content h2,
        .news-content h3,
        .news-content h4,
        .news-content h5,
        .news-content h6 {
            font-weight: bold;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            color: #2c3e50;
            font-family: 'Khand', sans-serif;
        }
        
        .news-content h1 { font-size: 2rem; }
        .news-content h2 { font-size: 1.75rem; }
        .news-content h3 { font-size: 1.5rem; }
        .news-content h4 { font-size: 1.25rem; }
        .news-content h5 { font-size: 1.1rem; }
        .news-content h6 { font-size: 1rem; }
        
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
            padding: 5px;
            border-radius: 10px;
            margin: 5px 0;
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
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 20px;
            cursor: pointer;
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
            padding: 2px 0;
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
        
        /* Character counter */
        .char-counter {
            font-size: 14px;
            margin-top: 5px;
            text-align: right;
        }
        
        .char-counter .remaining {
            color: #28a745;
            font-weight: bold;
        }
        
        .char-counter .warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .char-counter .exceeded {
            color: #dc3545;
            font-weight: bold;
        }
        
        /* PAGINATION STYLES */
        .pagination-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .pagination {
            margin: 0;
        }
        
        .page-link {
            color: #ff6600;
            border: 1px solid #dee2e6;
            padding: 8px 16px;
            margin: 0 3px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background-color: #ff6600;
            color: white;
            border-color: #ff6600;
        }
        
        .page-item.active .page-link {
            background-color: #ff6600;
            border-color: #ff6600;
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .comments-per-page {
            font-size: 14px;
            color: #666;
            margin-left: 20px;
        }
        
        /* RELATED NEWS STYLES */
        .related-news-section {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #ff6600;
        }
        
        .section-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 10px;
            font-family: 'Khand', sans-serif;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: #ff6600;
        }
        
        .related-news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .related-news-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .related-news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }
        
        /* FIXED RELATED NEWS IMAGE STYLES - Show full image without cropping */
        .related-news-image-container {
            width: 100%;
            height: 160px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .related-news-image {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.5s ease;
            background-color: #f8f9fa;
        }
        
        .related-news-card:hover .related-news-image {
            transform: scale(1.05);
        }
        
        .related-news-content {
            padding: 20px;
        }
        
        .related-news-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 16px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .related-news-category {
            display: inline-block;
            background: #ff6600;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .no-related-news {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #666;
        }
        
        .no-related-news i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        /* Custom Toastify styles */
        .custom-toast {
            border-radius: 8px;
            font-family: 'Mukta', sans-serif;
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
        
        /* MOBILE RESPONSIVE ADJUSTMENTS */
        @media (max-width: 768px) {
            .news-title {
                font-size: 1.5rem;
            }
            
            .news-content {
                font-size: 16px;
            }
            
            .news-content p {
                text-indent: 30px;
            }
            
            .share-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            /* MOBILE META SECTION - After content, before share section */
            .mobile-news-meta {
                padding: 12px;
                margin: 25px 0 15px 0;
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                border-radius: 12px;
                border: 1px solid rgba(255, 102, 0, 0.1);
            }
            
            .mobile-news-meta .text-center {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
                row-gap: 10px;
                align-items: center;
            }
            
            .mobile-news-meta .meta-item {
                display: flex;
                align-items: center;
                margin: 0;
                padding: 6px 12px;
                background: white;
                border-radius: 20px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.08);
                border: 1px solid rgba(255, 102, 0, 0.2);
                min-height: 36px;
                white-space: nowrap;
                cursor: default;
            }
            
            .mobile-news-meta .meta-item i {
                font-size: 14px;
                margin-right: 6px;
                color: #ff6600;
            }
            
            .mobile-news-meta .meta-item strong {
                font-size: 12px;
                margin-right: 4px;
                color: #555;
                display: inline;
            }
            
            .mobile-news-meta .meta-item .meta-value {
                font-size: 12px;
                color: #ff6600;
                font-weight: 500;
            }
            
            /* Hide tooltips on mobile (touch devices) */
            .mobile-news-meta .meta-tooltip {
                display: none;
            }
            
            .mobile-meta-divider {
                display: none !important;
            }
            
            /* Ensure items don't break into new lines individually */
            .mobile-news-meta .meta-item br {
                display: none;
            }
            
            .related-news-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .comments-per-page {
                margin-left: 0;
            }
            
            /* DUAL IMAGE MOBILE ADJUSTMENTS */
            .image-container {
                height: 300px;
                margin-bottom: 20px;
            }
            
            .single-image-container {
                height: 350px;
            }
            
            .news-dual-image, .news-single-image {
                max-height: 280px;
            }
            
            .image-fallback-text i {
                font-size: 36px;
            }
            
            .image-fallback-text span {
                font-size: 14px;
            }
            
            .no-images-icon {
                font-size: 48px;
            }
        }
        
        /* Adjust for very small screens */
        @media (max-width: 576px) {
            .related-news-grid {
                grid-template-columns: 1fr;
            }
            
            .news-content p {
                text-indent: 20px;
            }
            
            /* DUAL IMAGE SMALL MOBILE ADJUSTMENTS */
            .image-container {
                height: 250px;
            }
            
            .single-image-container {
                height: 300px;
            }
            
            .news-dual-image, .news-single-image {
                max-height: 230px;
            }
            
            /* Mobile meta section for very small screens */
            .mobile-news-meta {
                padding: 10px;
            }
            
            .mobile-news-meta .meta-item {
                padding: 5px 10px;
                min-height: 32px;
            }
            
            .mobile-news-meta .meta-item i {
                font-size: 12px;
                margin-right: 4px;
            }
            
            .mobile-news-meta .meta-item strong {
                font-size: 13px;
            }
            
            .mobile-news-meta .meta-item .meta-value {
                font-size: 13px;
            }
            
            .mobile-news-meta .text-center {
                gap: 6px;
                row-gap: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .mobile-news-meta .text-center {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .mobile-news-meta .meta-item {
                width: 100%;
                justify-content: center;
                padding: 8px 5px;
            }
        }
        
        @media (max-width: 360px) {
            .mobile-news-meta .text-center {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            
            .mobile-news-meta .meta-item {
                justify-content: flex-start;
                padding: 6px 10px;
            }
        }
        
        /* Desktop view - Show desktop meta, hide mobile meta */
        @media (min-width: 769px) {
            .desktop-meta-section {
                display: block;
            }
            
            .mobile-meta-section {
                display: none;
            }
            
            .news-meta .text-center {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
            }
            
            .news-meta .meta-item {
                display: inline-flex;
                align-items: center;
                margin: 0 15px;
                padding: 5px 10px;
            }
            
            .meta-divider {
                display: inline-block;
                color: #dee2e6;
                font-weight: bold;
            }
        }
        
        /* Mobile view - Hide desktop meta, show mobile meta */
        @media (max-width: 768px) {
            .desktop-meta-section {
                display: none;
            }
            
            .mobile-meta-section {
                display: block;
            }
        }
        
        /* Loading spinner */
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
        
        /* Floating Mobile Meta Bar (optional) */
        .floating-meta-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 8px 0;
            z-index: 1050;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            display: none;
            border-top: 2px solid #ff6600;
        }
        
        .floating-meta-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 12px;
        }
        
        .floating-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0.9;
        }
        
        .floating-meta-item i {
            color: #ff6600;
            font-size: 14px;
        }
        
        .floating-meta-divider {
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .floating-meta-bar {
                display: none; /* Initially hidden, shown via JS */
            }
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Breadcrumb Navigation -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php"><i class="bi bi-house-door"></i> मुख्य पृष्ठ</a>
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
            
            <!-- DESKTOP META SECTION - After title, before summary -->
            <div class="desktop-meta-section">
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
        </div>

        <!-- Summary -->
        <?php if (!empty($news['summary'])): ?>
        <div class="news-summary">
            <p class="mb-0"><i class="bi bi-quote"></i> <?php echo nl2br(htmlspecialchars($news['summary'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Image Display Section - Show both images if available -->
        <div class="images-section mb-5">
            <div class="row g-4">
                <?php
                // Check if cover photo exists
                $has_cover_photo = !empty($news['cover_photo_url']) && isValidImage($news['cover_photo_url']);
                $cover_photo_url = $has_cover_photo ? $news['cover_photo_url'] : $default_cover_image;
                
                // Check if secondary photo exists
                $has_secondary_photo = !empty($news['secondary_photo_url']) && isValidImage($news['secondary_photo_url']);
                $secondary_photo_url = $has_secondary_photo ? $news['secondary_photo_url'] : $default_secondary_image;
                
                // Determine if we have both images
                $has_both_images = $has_cover_photo && $has_secondary_photo;
                
                // Check if we have at least one real image (not fallback)
                $has_at_least_one_real_image = $has_cover_photo || $has_secondary_photo;
                
                // If we have both images, show them side by side
                if ($has_both_images): ?>
                    <!-- Both images exist - show side by side -->
                    <div class="col-md-6">
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($cover_photo_url); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?> मुख्य फोटो" 
                                 class="news-dual-image"
                                 onerror="handleImageError(this, 'cover')">
                            <?php if (!$has_cover_photo): ?>
                                <div class="image-fallback-text">
                                    <i class="bi bi-image"></i>
                                    <span>मुख्य फोटो उपलब्ध नाही</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($secondary_photo_url); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?> अतिरिक्त फोटो" 
                                 class="news-dual-image"
                                 onerror="handleImageError(this, 'secondary')">
                            <?php if (!$has_secondary_photo): ?>
                                <div class="image-fallback-text">
                                    <i class="bi bi-image"></i>
                                    <span>अतिरिक्त फोटो उपलब्ध नाही</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php elseif ($has_at_least_one_real_image): 
                    // Only one real image exists, show it centered with label
                    $real_image_url = $has_cover_photo ? $cover_photo_url : $secondary_photo_url;
                    $image_type = $has_cover_photo ? 'मुख्य फोटो' : 'अतिरिक्त फोटो';
                    ?>
                    
                    <div class="col-12">
                        <div class="single-image-container">
                            <img src="<?php echo htmlspecialchars($real_image_url); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title'] . ' ' . $image_type); ?>" 
                                 class="news-single-image"
                                 onerror="handleSingleImageError(this)">
                            <div class="single-image-label">
                                <?php echo $image_type; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: 
                    // No images exist, show fallback message
                    ?>
                    <div class="col-12">
                        <div class="no-images-message">
                            <div class="no-images-icon">
                                <i class="bi bi-images"></i>
                            </div>
                            <h5>फोटो उपलब्ध नाहीत</h5>
                            <p>या बातमीशी संबंधित कोणतेही फोटो उपलब्ध नाहीत</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content with Rich Text Formatting -->
        <div class="news-content">
            <?php echo $formatted_content; ?>
        </div>

        <!-- MOBILE META SECTION - After content, before social share -->
        <div class="mobile-meta-section">
            <div class="mobile-news-meta">
                <div class="text-center">
                    <!-- Publisher -->
                    <span class="meta-item">
                        <i class="bi bi-person-fill"></i>
                        <strong>Publisher:</strong>
                        <span class="meta-value"><?php echo htmlspecialchars($news['published_by']); ?></span>
                        <span class="meta-tooltip">News publisher name</span>
                    </span>
                    
                    <span class="mobile-meta-divider">|</span>
                    
                    <!-- Date -->
                    <span class="meta-item">
                        <i class="bi bi-calendar-event"></i>
                        <strong>Date:</strong>
                        <span class="meta-value"><?php echo $published_date; ?></span>
                        <span class="meta-tooltip">News publication date</span>
                    </span>
                    
                    <span class="mobile-meta-divider">|</span>
                    
                    <!-- Time -->
                    <span class="meta-item">
                        <i class="bi bi-clock"></i>
                        <strong>Time:</strong>
                        <span class="meta-value"><?php echo $published_time; ?></span>
                        <span class="meta-tooltip">News publication time</span>
                    </span>
                    
                    <span class="mobile-meta-divider">|</span>
                    
                    <!-- Views -->
                    <span class="meta-item">
                        <i class="bi bi-eye-fill"></i>
                        <strong>Views:</strong>
                        <span class="meta-value"><?php echo number_format($news['view']); ?></span>
                        <span class="meta-tooltip">Number of times this news has been viewed</span>
                    </span>
                    
                    <?php if (!empty($news['district_name'])): ?>
                    <span class="mobile-meta-divider">|</span>
                    
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
                <!-- Facebook -->
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" 
                   target="_blank" 
                   class="share-btn facebook"
                   title="Facebook वर शेअर करा">
                    <i class="bi bi-facebook"></i>
                </a>
                
                <!-- Twitter -->
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($news['title']); ?>" 
                   target="_blank" 
                   class="share-btn twitter"
                   title="Twitter वर शेअर करा">
                    <i class="bi bi-twitter"></i>
                </a>
                
                <!-- LinkedIn -->
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($current_url); ?>&title=<?php echo urlencode($news['title']); ?>&summary=<?php echo urlencode($meta_description); ?>" 
                   target="_blank" 
                   class="share-btn linkedin"
                   title="LinkedIn वर शेअर करा">
                    <i class="bi bi-linkedin"></i>
                </a>
                
                <!-- WhatsApp -->
                <button onclick="shareOnWhatsApp()" 
                        class="share-btn whatsapp border-0"
                        title="WhatsApp वर शेअर करा">
                    <i class="bi bi-whatsapp"></i>
                </button>
                
                <!-- Copy Link -->
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
            <h3 class="mb-4 border-bottom pb-2" style="cursor:pointer;" onclick="scrollToName()">
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
                    <div class="no-comments text-center py-3" id="noCommentsMessage">
                        <i class="bi bi-chat-left-text display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">No comments yet</h4>
                        <p class="text-muted">You can start the discussion</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_approved_comments > $comments_per_page): ?>
                <div class="pagination-container">
                    <nav aria-label="Comments pagination">
                        <ul class="pagination">
                            <!-- Previous Page -->
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="news.php?id=<?php echo $news_id; ?>&page=<?php echo $current_page - 1; ?>#commentsContainer">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-left"></i> Previous</span>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="news.php?id=<?php echo $news_id; ?>&page=<?php echo $i; ?>#commentsContainer">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Next Page -->
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="news.php?id=<?php echo $news_id; ?>&page=<?php echo $current_page + 1; ?>#commentsContainer">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="comments-per-page">
                        Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $comments_per_page, $total_approved_comments); ?> of <?php echo $total_approved_comments; ?> comments
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comment Form -->
            <div class="comment-form">
                <h5 class="mb-4"><i class="bi bi-pencil-square"></i> Write a Comment</h5>
                
                <form id="commentForm" method="POST">
                    <input type="hidden" name="news_id" id="news_id" value="<?php echo $news_id; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="Your name">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="Your email address">
                        </div>
                        
                        <div class="col-12">
                            <label for="comment" class="form-label">Your Comment <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comment" name="comment" rows="5" required 
                                      placeholder="Write your comment here (Maximum 50 words)"
                                      oninput="updateCharCounter(this)"></textarea>
                            <div class="char-counter">
                                <span id="charCount">0</span> / <span id="maxChars">50</span> words
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn submit-btn px-4 py-2" id="submitBtn">
                                <i class="bi bi-send"></i> Post Comment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RELATED NEWS SECTION -->
        <div class="related-news-section">
            <h3 class="section-title">
                <i class="bi bi-newspaper"></i> संबंधित बातम्या
            </h3>
            
            <?php if ($related_news_count > 0): ?>
                <div class="related-news-grid">
                    <?php while ($related_news = $related_result->fetch_assoc()): ?>
                        <a href="news.php?id=<?php echo $related_news['news_id']; ?>" class="related-news-card">
                            <?php 
                            // Check if related news has cover photo
                            $has_related_photo = !empty($related_news['cover_photo_url']) && isValidImage($related_news['cover_photo_url']);
                            ?>
                            
                            <?php if ($has_related_photo): ?>
                            <div class="related-news-image-container">
                                <img src="<?php echo htmlspecialchars($related_news['cover_photo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($related_news['title']); ?>"
                                     class="related-news-image"
                                     onerror="handleRelatedImageError(this)">
                            </div>
                            <?php endif; ?>
                            
                            <div class="related-news-content">
                                <h5 class="related-news-title">
                                    <?php echo htmlspecialchars(mb_substr($related_news['title'], 0, 60)) . (mb_strlen($related_news['title']) > 60 ? '...' : ''); ?>
                                </h5>
                                <span class="related-news-category">
                                    <?php echo $marathi_categories[$related_news['category_name']] ?? 'अमृत कार्यदीप'; ?>
                                </span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-related-news">
                    <i class="bi bi-newspaper"></i>
                    <h5>अजून संबंधित बातम्या नाहीत</h5>
                    <p>या श्रेणीतील इतर बातम्या उपलब्ध नाहीत</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Mobile Meta Bar -->
    <!-- <div class="floating-meta-bar d-lg-none">
        <div class="container">
            <div class="floating-meta-content">
                <span class="floating-meta-item">
                    <i class="bi bi-eye-fill"></i> <?php echo number_format($news['view']); ?>
                </span>
                <span class="floating-meta-divider">•</span>
                <span class="floating-meta-item">
                    <i class="bi bi-calendar-event"></i> <?php echo $published_date; ?>
                </span>
                <?php if (!empty($news['district_name'])): ?>
                <span class="floating-meta-divider">•</span>
                <span class="floating-meta-item">
                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($news['district_name']); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div> -->

    <script>
    function scrollToName() {
        const el = document.getElementById('name');

        const elementTop = el.getBoundingClientRect().top + window.pageYOffset;
        const offset = window.innerHeight * 0.3; // 30% from top

        window.scrollTo({
            top: elementTop - offset,
            behavior: 'smooth'
        });

        el.focus();
    }
    </script>

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
    
    // Enhanced WhatsApp sharing with image
    function shareOnWhatsApp() {
        const url = "<?php echo $current_url; ?>";
        const title = "<?php echo addslashes($news['title']); ?>";
        const description = "<?php echo addslashes($meta_description); ?>";
        const image = "<?php echo $share_image_url; ?>";
        
        // Create WhatsApp message
        const whatsappText = `*${title}*\n\n${description}\n\n📰 बातमी वाचा 'अमृत महाराष्ट्र'च्या पुढील लिंकवर...\n${url}`;
        
        // Use WhatsApp's share API
        const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(whatsappText)}`;
        
        // Open in new window
        window.open(whatsappUrl, '_blank');
        
        // Show toast notification
        showToast('WhatsApp वर शेअर करत आहे...', 'info');
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
    
    // Handle image errors for dual images
    function handleImageError(img, imageType) {
        img.onerror = null;
        
        // Show fallback image based on type
        const fallbackImage = imageType === 'cover' ? 
            '<?php echo $default_cover_image; ?>' : 
            '<?php echo $default_secondary_image; ?>';
        
        img.src = fallbackImage;
        
        // Show fallback text if not already showing
        const container = img.closest('.image-container');
        if (container) {
            const existingFallback = container.querySelector('.image-fallback-text');
            if (!existingFallback) {
                const fallbackDiv = document.createElement('div');
                fallbackDiv.className = 'image-fallback-text';
                fallbackDiv.innerHTML = `
                    <i class="bi bi-image"></i>
                    <span>${imageType === 'cover' ? 'मुख्य फोटो उपलब्ध नाही' : 'अतिरिक्त फोटो उपलब्ध नाही'}</span>
                `;
                container.appendChild(fallbackDiv);
            }
        }
    }
    
    // Handle single image error
    function handleSingleImageError(img) {
        img.onerror = null;
        img.src = '<?php echo $default_cover_image; ?>';
        
        const container = img.closest('.single-image-container');
        if (container) {
            const label = container.querySelector('.single-image-label');
            if (label) {
                label.textContent = 'फॉलबॅक फोटो';
                label.style.background = 'rgba(108, 117, 125, 0.9)';
            }
        }
    }
    
    function handleRelatedImageError(img) {
        img.onerror = null;
        
        // Hide the image container for related news
        const imageContainer = img.closest('.related-news-image-container');
        if (imageContainer) {
            imageContainer.style.display = 'none';
        }
    }
    
    // Function to count words in text (supports both Marathi and English)
    function countWords(text) {
        // Remove extra whitespace and split by word separators
        const cleanedText = text.trim().replace(/\s+/g, ' ');
        
        if (!cleanedText) {
            return 0;
        }
        
        // Split by spaces and filter out empty strings
        const words = cleanedText.split(' ').filter(word => word.length > 0);
        
        return words.length;
    }
    
    // Function to update character counter
    function updateCharCounter(textarea) {
        const text = textarea.value;
        const wordCount = countWords(text);
        const maxWords = 100;
        
        // Update counter display
        const charCountElement = document.getElementById('charCount');
        charCountElement.textContent = wordCount;
        
        // Update color based on word count
        const charCounter = textarea.nextElementSibling;
        
        if (wordCount > maxWords) {
            charCounter.querySelector('#charCount').className = 'exceeded';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').style.opacity = '0.6';
        } else if (wordCount > maxWords - 10) {
            charCounter.querySelector('#charCount').className = 'warning';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').style.opacity = '1';
        } else {
            charCounter.querySelector('#charCount').className = 'remaining';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').style.opacity = '1';
        }
    }
    
    // Function to check if image exists
    function checkImageExists(url, callback) {
        const img = new Image();
        img.onload = function() {
            callback(true);
        };
        img.onerror = function() {
            callback(false);
        };
        img.src = url;
    }
    
    // Function to initialize rich text content links
    function initializeContentLinks() {
        const contentContainer = document.querySelector('.news-content');
        if (contentContainer) {
            const links = contentContainer.querySelectorAll('a');
            links.forEach(link => {
                if (!link.hasAttribute('target')) {
                    link.setAttribute('target', '_blank');
                    link.setAttribute('rel', 'noopener');
                }
                if (!link.hasAttribute('href') || link.getAttribute('href') === '#') {
                    link.style.color = '#dc3545';
                    link.style.cursor = 'not-allowed';
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        showToast('लिंक उपलब्ध नाही', 'warning');
                    });
                }
            });
        }
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
            showToast('Please fill all required fields', 'error');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('Please enter a valid email address', 'error');
            return;
        }
        
        // Word count validation (max 50 words)
        const wordCount = countWords(comment);
        if (wordCount > 50) {
            showToast('Please write maximum 50 words', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
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
                        showToast('Your comment has been posted successfully!', 'success');
                        
                        // Clear form
                        document.getElementById('name').value = '';
                        document.getElementById('email').value = '';
                        document.getElementById('comment').value = '';
                        document.getElementById('saveInfo').checked = false;
                        
                        // Reset character counter
                        document.getElementById('charCount').textContent = '0';
                        document.getElementById('charCount').className = 'remaining';
                        
                    } else {
                        showToast(response.message, 'error');
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                }
            } else {
                showToast('Server error: ' + xhr.status, 'error');
            }
        };
        
        xhr.onerror = function() {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            showToast('Network error', 'error');
        };
        
        // Send the request
        xhr.send(formData);
    });
    
    // Clear form function with toast
    function clearForm() {
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('comment').value = '';
        document.getElementById('saveInfo').checked = false;
        document.getElementById('charCount').textContent = '0';
        document.getElementById('charCount').className = 'remaining';
        showToast('Form cleared', 'info');
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
    
    // Show floating meta bar after scrolling past news
    window.addEventListener('scroll', function() {
        const floatingBar = document.querySelector('.floating-meta-bar');
        if (floatingBar && window.innerWidth < 768) {
            if (window.scrollY > 400) {
                floatingBar.style.display = 'block';
            } else {
                floatingBar.style.display = 'none';
            }
        }
    });
    
    // Initialize on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        // Check cover image
        const coverImage = document.querySelector('img[alt*="मुख्य फोटो"]');
        if (coverImage && coverImage.src !== '<?php echo $default_cover_image; ?>') {
            checkImageExists(coverImage.src, function(exists) {
                if (!exists) {
                    handleImageError(coverImage, 'cover');
                }
            });
        }
        
        // Check secondary image
        const secondaryImage = document.querySelector('img[alt*="अतिरिक्त फोटो"]');
        if (secondaryImage && secondaryImage.src !== '<?php echo $default_secondary_image; ?>') {
            checkImageExists(secondaryImage.src, function(exists) {
                if (!exists) {
                    handleImageError(secondaryImage, 'secondary');
                }
            });
        }
        
        // Add error handlers to images
        const images = document.querySelectorAll('.news-dual-image, .news-single-image, .related-news-image');
        images.forEach(function(img) {
            img.addEventListener('error', function() {
                if (this.classList.contains('related-news-image')) {
                    handleRelatedImageError(this);
                }
            });
        });
        
        // Add click handlers to share buttons with toast
        const shareButtons = document.querySelectorAll('.share-btn');
        shareButtons.forEach(button => {
            if (button.tagName === 'A' && !button.classList.contains('whatsapp')) {
                button.addEventListener('click', function() {
                    const platform = this.classList.contains('facebook') ? 'Facebook' :
                                  this.classList.contains('twitter') ? 'Twitter' :
                                  'LinkedIn';
                    showToast(`Sharing on ${platform}...`, 'info');
                });
            }
        });
        
        // Add toast for save info checkbox
        const saveInfoCheckbox = document.getElementById('saveInfo');
        if (saveInfoCheckbox) {
            saveInfoCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    showToast('Your information will be saved for next time', 'info');
                }
            });
        }
        
        // Add hover effect to related news cards
        const relatedCards = document.querySelectorAll('.related-news-card');
        relatedCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Initialize character counter
        const commentTextarea = document.getElementById('comment');
        if (commentTextarea) {
            commentTextarea.addEventListener('input', function() {
                updateCharCounter(this);
            });
        }
        
        // Initialize meta tags
        updateMetaTags();
        
        // Initialize rich text content links
        initializeContentLinks();
    });
    
    // Function to update meta tags for social sharing
    function updateMetaTags() {
        // Set canonical URL
        const canonicalLink = document.querySelector("link[rel='canonical']") || document.createElement('link');
        canonicalLink.rel = 'canonical';
        canonicalLink.href = "<?php echo $current_url; ?>";
        document.head.appendChild(canonicalLink);
        
        // Set additional meta tags
        const metaTags = [
            { property: 'og:title', content: "<?php echo addslashes($news['title']); ?>" },
            { property: 'og:description', content: "<?php echo addslashes($meta_description); ?>" },
            { property: 'og:image', content: "<?php echo $share_image_url; ?>" },
            { property: 'og:url', content: "<?php echo $current_url; ?>" },
            { name: 'twitter:title', content: "<?php echo addslashes($news['title']); ?>" },
            { name: 'twitter:description', content: "<?php echo addslashes($meta_description); ?>" },
            { name: 'twitter:image', content: "<?php echo $share_image_url; ?>" }
        ];
        
        metaTags.forEach(tag => {
            let meta = document.querySelector(`meta[property="${tag.property}"]`) || 
                      document.querySelector(`meta[name="${tag.name}"]`);
            if (meta) {
                meta.setAttribute('content', tag.content);
            }
        });
    }
    </script>

    <!-- Back to Top Button -->
    <!-- <button onclick="scrollToTop()" 
            id="scrollToTop" 
            class="btn btn-primary rounded-circle position-fixed"
            style="bottom: 30px; right: 30px; width: 50px; height: 50px; display: none; z-index: 1000;">
        <i class="bi bi-arrow-up"></i>
    </button> -->
    <script>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    window.addEventListener('load', function () {
        window.scrollTo(0, 0);
    });
    
    // Back to top button functionality
    window.addEventListener('scroll', function() {
        const scrollToTopBtn = document.getElementById('scrollToTop');
        if (scrollToTopBtn) {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.display = 'block';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        }
    });
    
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    </script>
</body>
</html>

<?php
// Close database connections
$stmt->close();
$comments_stmt->close();
$related_stmt->close();
$conn->close();
$showWhatsapp = true;
include 'components/footer.php';
?>