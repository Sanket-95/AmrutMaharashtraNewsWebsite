<?php
// components/header.php
// This is for general pages, news.php has its own dynamic meta tags

// Get current page URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="mr" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>अमृत महाराष्ट्र - Latest News & Updates</title>
    
    <!-- Default Open Graph Meta Tags for non-news pages -->
    <meta property="og:title" content="अमृत महाराष्ट्र - Latest News & Updates">
    <meta property="og:description" content="Stay updated with the latest news, government initiatives, and developments in Maharashtra. Your trusted source for authentic news from Maharashtra.">
    <meta property="og:image" content="https://amrutmaharashtra.org/assets/images/logo.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="अमृत महाराष्ट्र Logo">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="अमृत महाराष्ट्र">
    <meta property="og:locale" content="mr_IN">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="अमृत महाराष्ट्र - Latest News & Updates">
    <meta name="twitter:description" content="Stay updated with the latest news, government initiatives, and developments in Maharashtra.">
    <meta name="twitter:image" content="https://amrutmaharashtra.org/assets/images/logo.png">
    
    <!-- WhatsApp Specific -->
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:secure_url" content="https://amrutmaharashtra.org/assets/images/logo.png">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="description" content="Stay updated with the latest news, government initiatives, and developments in Maharashtra. Your trusted source for authentic news from Maharashtra.">
    <meta name="keywords" content="Maharashtra news, government news, Amrut Maharashtra, latest news, Marathi news">
    <meta name="author" content="अमृत महाराष्ट्र">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($current_url); ?>">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS (optional) -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .gov-header {
            background-color: #0d6efd;
            color: white;
        }
        
        /* Open Graph image optimization */
        .og-image-placeholder {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Body content starts here -->