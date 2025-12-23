<?php
// components/header_fixed.php - ULTIMATE FIX V2
if (ob_get_level()) ob_end_clean();

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
ob_start('ob_gzhandler');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
}

$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

ob_clean();
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>अमृत महाराष्ट्र</title>
    
    <!-- META TAGS -->
    <meta name="description" content="Latest news, government initiatives, and developments in Maharashtra">
    <meta name="keywords" content="Maharashtra news, government news, Amrut Maharashtra">
    <meta name="author" content="Amrut Maharashtra">
    
    <!-- OPEN GRAPH TAGS -->
    <meta property="og:title" content="Amrut Maharashtra">
    <meta property="og:description" content="Latest news and government initiatives in Maharashtra">
    <meta property="og:image" content="https://amrutmaharashtra.org/assets/images/logo.png">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Amrut Maharashtra">
    
    <!-- TWITTER CARD -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Amrut Maharashtra">
    <meta name="twitter:description" content="Latest Maharashtra news and updates">
    
    <!-- CANONICAL URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- CRITICAL: INLINE STYLES -->
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', 'Nirmala UI', 'Arial Unicode MS', sans-serif;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }
    
    /* PROTECT ALL NAVBAR MARATHI TEXT */
    .center-title, .subtitle, 
    .main-heading, .mobile-heading-line-2, .mobile-heading-line-3,
    .heading-main-part, .heading-secondary-part {
        font-family: 'Segoe UI', 'Nirmala UI', 'Arial Unicode MS', sans-serif !important;
        text-transform: none !important;
        letter-spacing: normal !important;
    }
    
    /* Make ALL Marathi text unselectable to prevent changes */
    .center-title, .subtitle, 
    .main-heading, .mobile-heading-line-2, .mobile-heading-line-3 {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
        pointer-events: none !important;
    }
    </style>
    
    <!-- NUCLEAR OPTION: PROTECT ALL MARATHI TEXT -->
    <script>
    (function() {
        'use strict';
        
        // Store ALL CORRECT Marathi texts
        const CORRECT_TEXTS = {
            'center-title': 'अमृत महाराष्ट्र',
            'subtitle': 'श्रमेव जयते',
            'main-heading': 'महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी (अमृत) - महाराष्ट्र शासनाची स्वायत्त संस्था',
            'mobile-heading-line-2': 'महाराष्ट्र संशोधन उन्नती व प्रशिक्षण प्रबोधिनी (अमृत)',
            'mobile-heading-line-3': 'महाराष्ट्र शासनाची स्वायत्त संस्था'
        };
        
        // Common wrong patterns
        const WRONG_PATTERNS = [
            'महाराष्टर',  // Missing the रा combination
            'महाराष्ट्रार', // Extra र
            'महाराष्ट्',   // Missing र
            'महाराष्ट',    // Missing ्
            'महाराष्ट़'    // Wrong diacritic
        ];
        
        // Function to check if text contains wrong patterns
        function hasWrongPattern(text) {
            return WRONG_PATTERNS.some(pattern => text.includes(pattern));
        }
        
        // Function to get correct text for element
        function getCorrectText(element) {
            if (element.classList.contains('center-title')) return CORRECT_TEXTS['center-title'];
            if (element.classList.contains('subtitle')) return CORRECT_TEXTS['subtitle'];
            if (element.classList.contains('main-heading')) return CORRECT_TEXTS['main-heading'];
            if (element.classList.contains('mobile-heading-line-2')) return CORRECT_TEXTS['mobile-heading-line-2'];
            if (element.classList.contains('mobile-heading-line-3')) return CORRECT_TEXTS['mobile-heading-line-3'];
            
            // Check parent classes
            const parent = element.parentElement;
            if (parent) {
                if (parent.classList.contains('center-title')) return CORRECT_TEXTS['center-title'];
                if (parent.classList.contains('subtitle')) return CORRECT_TEXTS['subtitle'];
                if (parent.classList.contains('main-heading')) return CORRECT_TEXTS['main-heading'];
                if (parent.classList.contains('mobile-heading-line-2')) return CORRECT_TEXTS['mobile-heading-line-2'];
                if (parent.classList.contains('mobile-heading-line-3')) return CORRECT_TEXTS['mobile-heading-line-3'];
            }
            
            return null;
        }
        
        // Protect element from text changes
        function protectElement(element) {
            if (!element) return;
            
            const correctText = getCorrectText(element);
            if (!correctText) return; // Not a protected element
            
            const originalTextContent = Object.getOwnPropertyDescriptor(element, 'textContent');
            const originalInnerText = Object.getOwnPropertyDescriptor(element, 'innerText');
            const originalInnerHTML = Object.getOwnPropertyDescriptor(element, 'innerHTML');
            
            if (originalTextContent) {
                Object.defineProperty(element, 'textContent', {
                    get: function() {
                        return correctText;
                    },
                    set: function(value) {
                        if (value !== correctText && hasWrongPattern(value)) {
                            console.error('BLOCKED: Attempt to change protected text to:', value);
                            return originalTextContent.set.call(this, correctText);
                        }
                        return originalTextContent.set.call(this, value);
                    },
                    configurable: false
                });
            }
            
            if (originalInnerText) {
                Object.defineProperty(element, 'innerText', {
                    get: function() {
                        return correctText;
                    },
                    set: function(value) {
                        if (value !== correctText && hasWrongPattern(value)) {
                            console.error('BLOCKED: Attempt to change protected innerText to:', value);
                            return originalInnerText.set.call(this, correctText);
                        }
                        return originalInnerText.set.call(this, value);
                    },
                    configurable: false
                });
            }
            
            // Also protect immediately
            if (element.textContent !== correctText && hasWrongPattern(element.textContent)) {
                element.textContent = correctText;
            }
        }
        
        // Run protection
        function protectAllMarathiText() {
            const selectors = [
                '.center-title', '.subtitle', 
                '.main-heading', '.mobile-heading-line-2', '.mobile-heading-line-3',
                '.heading-main-part', '.heading-secondary-part'
            ];
            
            selectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(protectElement);
            });
            
            // Also check all text nodes in document
            const walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );
            
            let node;
            while (node = walker.nextNode()) {
                if (hasWrongPattern(node.textContent)) {
                    // Try to find correct text based on parent
                    const parent = node.parentElement;
                    if (parent) {
                        const correctText = getCorrectText(parent);
                        if (correctText) {
                            node.textContent = correctText;
                        }
                    }
                }
            }
        }
        
        // Run on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            protectAllMarathiText();
            
            // Monitor for new elements
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                protectAllMarathiText();
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Force check every 500ms
            setInterval(protectAllMarathiText, 500);
        });
        
        // Check immediately if DOM already loaded
        if (document.readyState !== 'loading') {
            protectAllMarathiText();
        }
        
    })();
    </script>
</head>
<body>
<!-- Body starts here -->