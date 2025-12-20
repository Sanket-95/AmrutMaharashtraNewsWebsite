<?php
// components/header_fixed.php - ULTIMATE FIX

// ========== PHASE 1: CLEAN UTF-8 SETUP ==========
// Stop any previous output
if (ob_get_level()) ob_end_clean();

// Set headers BEFORE any output
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Start fresh output buffer
ob_start('ob_gzhandler');

// Force UTF-8 for all string functions
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
}

// Remove BOM if present
function removeUtf8Bom($text) {
    $bom = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

// ========== PHASE 2: SIMPLIFIED HEADER ==========
// Get current page URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Use Unicode escapes for ALL Marathi text
$amrut_maharashtra = '&#x0905;&#x092E;&#x0943;&#x0924; &#x092E;&#x0939;&#x093E;&#x0930;&#x093E;&#x0937;&#x094D;&#x091F;&#x094D;&#x0930;';
$shramev_jayate = '&#x0936;&#x094D;&#x0930;&#x092E;&#x0947;&#x0935; &#x091C;&#x092F;&#x0924;&#x0947;';

// Clean the output buffer
ob_clean();
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <!-- ONE AND ONLY ONE charset declaration -->
    <meta charset="UTF-8">
    
    <!-- NO http-equiv Content-Type here - let the HTTP header handle it -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Title with Unicode escapes -->
    <title><?php echo $amrut_maharashtra; ?></title>
    
    <!-- ========== META TAGS - ENGLISH ONLY ========== -->
    <!-- Using English prevents encoding issues with social platforms -->
    <meta name="description" content="Latest news, government initiatives, and developments in Maharashtra">
    <meta name="keywords" content="Maharashtra news, government news, Amrut Maharashtra">
    <meta name="author" content="Amrut Maharashtra">
    
    <!-- ========== OPEN GRAPH TAGS - ENGLISH ONLY ========== -->
    <meta property="og:title" content="Amrut Maharashtra">
    <meta property="og:description" content="Latest news and government initiatives in Maharashtra">
    <meta property="og:image" content="https://amrutmaharashtra.org/assets/images/logo.png">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Amrut Maharashtra">
    
    <!-- ========== TWITTER CARD ========== -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Amrut Maharashtra">
    <meta name="twitter:description" content="Latest Maharashtra news and updates">
    
    <!-- ========== CANONICAL URL ========== -->
    <link rel="canonical" href="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- ========== BOOTSTRAP ========== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- ========== CRITICAL: INLINE STYLES ========== -->
    <style>
    /* RESET EVERYTHING FIRST */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    /* Force UTF-8 rendering */
    body {
        font-family: Arial, sans-serif;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }
    
    /* CRITICAL: Protect navbar text with !important */
    .center-title {
        font-family: 'Arial Unicode MS', 'Segoe UI', sans-serif !important;
        color: #f97316 !important;
        font-weight: 800 !important;
        font-size: 1.6rem !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        content: "<?php echo $amrut_maharashtra; ?>" !important;
    }
    
    .subtitle {
        font-family: 'Arial Unicode MS', 'Segoe UI', sans-serif !important;
        color: #c2410c !important;
        font-style: italic !important;
        font-weight: 500 !important;
        font-size: 0.85rem !important;
        content: "<?php echo $shramev_jayate; ?>" !important;
    }
    
    /* Make these elements unselectable to prevent changes */
    .center-title, .subtitle {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
        pointer-events: none !important;
    }
    </style>
    
    <!-- ========== NUCLEAR OPTION: BLOCK ALL EXTERNAL SCRIPTS FROM MODIFYING TEXT ========== -->
    <script>
    // RUN THIS BEFORE ANY OTHER SCRIPT
    (function() {
        'use strict';
        
        // Store the correct text
        const CORRECT_TITLE = 'अमृत महाराष्ट्र';
        const CORRECT_SUBTITLE = 'श्रमेव जयते';
        const WRONG_TITLE = 'अमृत महाराष्ट्रार';
        
        // Nuclear option: Override DOM methods
        const originalCreateElement = document.createElement;
        const originalCreateTextNode = document.createTextNode;
        const originalQuerySelector = document.querySelector;
        const originalQuerySelectorAll = document.querySelectorAll;
        
        // Block creation of elements with wrong text
        document.createElement = function(tagName) {
            const element = originalCreateElement.call(this, tagName);
            
            if (tagName.toLowerCase() === 'script') {
                const originalAppend = element.appendChild;
                element.appendChild = function(child) {
                    if (child && child.nodeType === 3) { // Text node
                        if (child.textContent.includes(WRONG_TITLE)) {
                            console.error('BLOCKED: Script trying to create wrong text');
                            child.textContent = child.textContent.replace(WRONG_TITLE, CORRECT_TITLE);
                        }
                    }
                    return originalAppend.call(this, child);
                };
            }
            
            return element;
        };
        
        // Block creation of text nodes with wrong text
        document.createTextNode = function(text) {
            if (text.includes(WRONG_TITLE)) {
                console.error('BLOCKED: TextNode with wrong text');
                text = text.replace(WRONG_TITLE, CORRECT_TITLE);
            }
            return originalCreateTextNode.call(this, text);
        };
        
        // Intercept text content changes
        function protectElement(element) {
            if (!element) return;
            
            const originalTextContent = Object.getOwnPropertyDescriptor(element, 'textContent');
            const originalInnerText = Object.getOwnPropertyDescriptor(element, 'innerText');
            const originalInnerHTML = Object.getOwnPropertyDescriptor(element, 'innerHTML');
            
            if (originalTextContent) {
                Object.defineProperty(element, 'textContent', {
                    get: function() {
                        if (this.classList.contains('center-title')) return CORRECT_TITLE;
                        if (this.classList.contains('subtitle')) return CORRECT_SUBTITLE;
                        return originalTextContent.get.call(this);
                    },
                    set: function(value) {
                        if (this.classList.contains('center-title')) {
                            if (value !== CORRECT_TITLE) {
                                console.error('BLOCKED: Attempt to change center-title to:', value);
                                value = CORRECT_TITLE;
                            }
                        }
                        if (this.classList.contains('subtitle')) {
                            if (value !== CORRECT_SUBTITLE) {
                                console.error('BLOCKED: Attempt to change subtitle to:', value);
                                value = CORRECT_SUBTITLE;
                            }
                        }
                        return originalTextContent.set.call(this, value);
                    },
                    configurable: false
                });
            }
            
            if (originalInnerText) {
                Object.defineProperty(element, 'innerText', {
                    get: function() {
                        if (this.classList.contains('center-title')) return CORRECT_TITLE;
                        if (this.classList.contains('subtitle')) return CORRECT_SUBTITLE;
                        return originalInnerText.get.call(this);
                    },
                    set: function(value) {
                        if (this.classList.contains('center-title')) {
                            if (value !== CORRECT_TITLE) {
                                console.error('BLOCKED: Attempt to change center-title innerText to:', value);
                                value = CORRECT_TITLE;
                            }
                        }
                        if (this.classList.contains('subtitle')) {
                            if (value !== CORRECT_SUBTITLE) {
                                console.error('BLOCKED: Attempt to change subtitle innerText to:', value);
                                value = CORRECT_SUBTITLE;
                            }
                        }
                        return originalInnerText.set.call(this, value);
                    },
                    configurable: false
                });
            }
        }
        
        // Run immediately
        document.addEventListener('DOMContentLoaded', function() {
            // Protect existing elements
            document.querySelectorAll('.center-title, .subtitle').forEach(protectElement);
            
            // Monitor for new elements
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                if (node.classList && (node.classList.contains('center-title') || node.classList.contains('subtitle'))) {
                                    protectElement(node);
                                }
                                node.querySelectorAll('.center-title, .subtitle').forEach(protectElement);
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Force correct text every 100ms (nuclear option)
            setInterval(function() {
                const titles = document.querySelectorAll('.center-title');
                const subtitles = document.querySelectorAll('.subtitle');
                
                titles.forEach(function(title) {
                    if (title.textContent !== CORRECT_TITLE) {
                        console.log('Fixing title from:', title.textContent, 'to:', CORRECT_TITLE);
                        title.textContent = CORRECT_TITLE;
                    }
                });
                
                subtitles.forEach(function(subtitle) {
                    if (subtitle.textContent !== CORRECT_SUBTITLE) {
                        console.log('Fixing subtitle from:', subtitle.textContent, 'to:', CORRECT_SUBTITLE);
                        subtitle.textContent = CORRECT_SUBTITLE;
                    }
                });
            }, 100);
        });
        
        // Check immediately
        if (document.readyState !== 'loading') {
            document.querySelectorAll('.center-title, .subtitle').forEach(protectElement);
        }
        
    })();
    </script>
</head>
<body>
<!-- Body starts here -->