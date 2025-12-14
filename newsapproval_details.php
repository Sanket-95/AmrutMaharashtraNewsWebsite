<?php
// newsapproval_details.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only admin and division_head can access this page
$allowed_roles = ['admin', 'division_head', 'Admin'];
if (!in_array($_SESSION['roll'], $allowed_roles)) {
    header('Location: index.php');
    exit();
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
include 'components/db_config.php';

// Get news_id from URL
$news_id = $_GET['news_id'] ?? 0;

// Check if edit mode is enabled
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1';

// Variables for toast messages
$toast_message = '';
$toast_type = '';

// Check if form was submitted for editing
$update_success = false;
$update_error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_news'])) {
    // Update news in database
    $title = $_POST['title'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? '';
    $district = $_POST['district'] ?? '';
    $region = $_POST['region'] ?? '';
    
    $update_sql = "UPDATE news_articles SET 
                    title = ?, 
                    summary = ?, 
                    content = ?, 
                    category_name = ?,
                    district_name = ?,
                    Region = ?,
                    updated_at = NOW()
                   WHERE news_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $title, $summary, $content, $category, $district, $region, $news_id);
    
    if ($update_stmt->execute()) {
        $update_success = true;
        $toast_message = "बातमी यशस्वीरित्या अपडेट केली गेली!";
        $toast_type = "success";
        // Refresh news data after update
        $sql = "SELECT * FROM news_articles WHERE news_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $news = $result->fetch_assoc();
        $stmt->close();
        // After update, disable edit mode
        $edit_mode = false;
    } else {
        $update_error = true;
        $toast_message = "बातमी अपडेट करताना त्रुटी आली. कृपया पुन्हा प्रयत्न करा.";
        $toast_type = "error";
    }
    $update_stmt->close();
}

// Check for approval/disapproval notifications from URL
if (isset($_GET['approval_success'])) {
    $toast_message = "बातमी यशस्वीरित्या मान्य केली गेली!";
    $toast_type = "success";
} elseif (isset($_GET['disapproval_success'])) {
    $toast_message = "बातमी यशस्वीरित्या नामंजूर केली गेली!";
    $toast_type = "warning";
} elseif (isset($_GET['approval_error'])) {
    $toast_message = "बातमी मंजुरी/नामंजुरी करताना त्रुटी आली.";
    $toast_type = "error";
}

// Fetch news details from news_articles table (if not already fetched after update)
if (!isset($news) || empty($news)) {
    $sql = "SELECT * FROM news_articles WHERE news_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
    } else {
        // Redirect if news not found
        $_SESSION['toast_message'] = "बातमी सापडली नाही!";
        $_SESSION['toast_type'] = "error";
        header('Location: newsapproval.php?status=pending');
        exit();
    }
    $stmt->close();
}

// Check if user has permission to view this news
$user_roll = $_SESSION['roll'];
$user_region = $_SESSION['location'];

// Function to get region from location
function getRegionFromLocation($location) {
    $location = strtolower($location);
    
    // List of all regions
    $regions = ['kokan', 'pune', 'sambhajinagar', 'nashik', 'amaravati', 'nagpur'];
    
    // If location is directly a region, return it
    if (in_array($location, $regions)) {
        return $location;
    }
    
    // District to region mapping
    $districtToRegion = [
        // Kokan region districts
        'palghar' => 'kokan',
        'thane' => 'kokan',
        'mumbai_city' => 'kokan',
        'mumbai' => 'kokan',
        'mumbai_suburban' => 'kokan',
        'raigad' => 'kokan',
        'ratnagiri' => 'kokan',
        'sindhudurg' => 'kokan',
        
        // Pune region districts
        'pune' => 'pune',
        'satara' => 'pune',
        'kolhapur' => 'pune',
        'sangli' => 'pune',
        'solapur' => 'pune',
        
        // Sambhajinagar region districts
        'chhatrapati_sambhajinagar' => 'sambhajinagar',
        'beed' => 'sambhajinagar',
        'jalna' => 'sambhajinagar',
        'parbhani' => 'sambhajinagar',
        'hingoli' => 'sambhajinagar',
        'nanded' => 'sambhajinagar',
        'latur' => 'sambhajinagar',
        'dharashiv' => 'sambhajinagar',
        
        // Nashik region districts
        'nashik' => 'nashik',
        'dhule' => 'nashik',
        'nandurbar' => 'nashik',
        'ahmednagar' => 'nashik',
        'jalgaon' => 'nashik',
        'ahilyanagar' => 'nashik',
        
        // Amaravati region districts
        'amaravati' => 'amaravati',
        'akola' => 'amaravati',
        'buldhana' => 'amaravati',
        'washim' => 'amaravati',
        'yavatmal' => 'amaravati',
        
        // Nagpur region districts
        'nagpur' => 'nagpur',
        'wardha' => 'nagpur',
        'bhandara' => 'nagpur',
        'gondia' => 'nagpur',
        'chandrapur' => 'nagpur',
        'gadchiroli' => 'nagpur'
    ];
    
    return isset($districtToRegion[$location]) ? $districtToRegion[$location] : $location;
}

// Get user's region from location
$user_region_for_check = getRegionFromLocation($user_region);

// For division_head, check if news belongs to their region
if ($user_roll === 'division_head' && strtolower($news['Region']) !== strtolower($user_region_for_check)) {
    $toast_message = "आपल्याला या बातमीवर परवानगी नाही!";
    $toast_type = "error";
    header('Location: newsapproval.php?status=pending');
    exit();
}

// Get Marathi names for display
function getMarathiStatusName($status) {
    $status_names = [
        0 => 'प्रलंबित',
        1 => 'मान्य',
        2 => 'नामंजूर'
    ];
    return $status_names[$status] ?? 'अज्ञात';
}

function getMarathiCategoryName($category) {
    $category_map = [
        'home' => 'मुख्यपृष्ठ',
        'amrut_events' => 'अमृत घडामोडी',
        'beneficiary_story' => 'लाभार्थी स्टोरी',
        'today_special' => 'दिनविशेष',
        'successful_entrepreneur' => 'यशस्वी उद्योजक',
        'words_amrut' => 'शब्दांमृत',
        'smart_farmer' => 'स्मार्ट शेतकरी',
        'capable_student' => 'सक्षम विद्यार्थी',
        'spirituality' => 'अध्यात्म',
        'social_situation' => 'सामाजिक परिवर्तक',
        'women_power' => 'स्त्रीशक्ती',
        'tourism' => 'पर्यटन'
    ];
    return $category_map[$category] ?? $category;
}

function getMarathiDivisionName($regionValue) {
    // Convert to lowercase for comparison
    $regionValue = strtolower($regionValue);
    $divisionMap = [
        'kokan' => 'कोकण विभाग',
        'pune' => 'पुणे विभाग',
        'sambhajinagar' => 'संभाजीनगर विभाग',
        'nashik' => 'नाशिक विभाग',
        'amaravati' => 'अमरावती विभाग',
        'nagpur' => 'नागपूर विभाग',
        'mumbai' => 'मुंबई विभाग',
        'ratnagiri' => 'रत्नागिरी विभाग',
        'solapur' => 'सोलापूर विभाग',
        'kolhapur' => 'कोल्हापूर विभाग',
        'thane' => 'ठाणे विभाग',
        'raigad' => 'रायगड विभाग',
        'jalna' => 'जालना विभाग',
        'nanded' => 'नांदेड विभाग',
        'ahilyanagar' => 'अहिल्यानगर विभाग',
        'sangli' => 'सांगली विभाग',
        'satara' => 'सातारा विभाग',
        'gadchiroli' => 'गडचिरोली विभाग'
    ];
    return isset($divisionMap[$regionValue]) ? $divisionMap[$regionValue] : $regionValue . ' विभाग';
}

function getMarathiDistrictName($districtValue) {
    // Convert to lowercase for comparison
    $districtValue = strtolower($districtValue);
    $districtMap = [
        'palghar' => 'पालघर',
        'thane' => 'ठाणे',
        'mumbai_city' => 'मुंबई शहर',
        'mumbai' => 'मुंबई',
        'mumbai_suburban' => 'मुंबई उपनगर',
        'raigad' => 'रायगड',
        'ratnagiri' => 'रत्नागिरी',
        'sindhudurg' => 'सिंधुदुर्ग',
        'pune' => 'पुणे',
        'satara' => 'सातारा',
        'kolhapur' => 'कोल्हापूर',
        'sangli' => 'सांगली',
        'solapur' => 'सोलापूर',
        'chhatrapati_sambhajinagar' => 'छत्रपती संभाजीनगर',
        'beed' => 'बीड',
        'jalna' => 'जालना',
        'parbhani' => 'परभणी',
        'hingoli' => 'हिंगोली',
        'nanded' => 'नांदेड',
        'latur' => 'लातूर',
        'dharashiv' => 'धाराशिव',
        'nashik' => 'नाशिक',
        'dhule' => 'धुळे',
        'nandurbar' => 'नंदुरबार',
        'ahmednagar' => 'अहमदनगर',
        'jalgaon' => 'जळगाव',
        'ahilyanagar' => 'अहिल्यानगर',
        'amaravati' => 'अमरावती',
        'akola' => 'अकोला',
        'buldhana' => 'बुलढाणा',
        'washim' => 'वाशीम',
        'yavatmal' => 'यवतमाळ',
        'nagpur' => 'नागपूर',
        'wardha' => 'वर्धा',
        'bhandara' => 'भंडारा',
        'gondia' => 'गोंदिया',
        'chandrapur' => 'चंद्रपूर',
        'gadchiroli' => 'गडचिरोली'
    ];
    return isset($districtMap[$districtValue]) ? $districtMap[$districtValue] : $districtValue;
}

// Get all categories for dropdown
$categories = [
    'home' => 'मुख्यपृष्ठ',
    'amrut_events' => 'अमृत घडामोडी',
    'beneficiary_story' => 'लाभार्थी स्टोरी',
    'today_special' => 'दिनविशेष',
    'successful_entrepreneur' => 'यशस्वी उद्योजक',
    'words_amrut' => 'शब्दांमृत',
    'smart_farmer' => 'स्मार्ट शेतकरी',
    'capable_student' => 'सक्षम विद्यार्थी',
    'spirituality' => 'अध्यात्म',
    'social_situation' => 'सामाजिक परिवर्तक',
    'women_power' => 'स्त्रीशक्ती',
    'tourism' => 'पर्यटन'
];

// Get all districts for dropdown
$all_districts = [
    'palghar' => 'पालघर',
    'thane' => 'ठाणे',
    'mumbai_city' => 'मुंबई शहर',
    'mumbai_suburban' => 'मुंबई उपनगर',
    'raigad' => 'रायगड',
    'ratnagiri' => 'रत्नागिरी',
    'sindhudurg' => 'सिंधुदुर्ग',
    'pune' => 'पुणे',
    'satara' => 'सातारा',
    'kolhapur' => 'कोल्हापूर',
    'sangli' => 'सांगली',
    'solapur' => 'सोलापूर',
    'chhatrapati_sambhajinagar' => 'छत्रपती संभाजीनगर',
    'beed' => 'बीड',
    'jalna' => 'जालना',
    'parbhani' => 'परभणी',
    'hingoli' => 'हिंगोली',
    'nanded' => 'नांदेड',
    'latur' => 'लातूर',
    'dharashiv' => 'धाराशिव',
    'nashik' => 'नाशिक',
    'dhule' => 'धुळे',
    'nandurbar' => 'नंदुरबार',
    'ahmednagar' => 'अहमदनगर',
    'jalgaon' => 'जळगाव',
    'ahilyanagar' => 'अहिल्यानगर',
    'amaravati' => 'अमरावती',
    'akola' => 'अकोला',
    'buldhana' => 'बुलढाणा',
    'washim' => 'वाशीम',
    'yavatmal' => 'यवतमाळ',
    'nagpur' => 'नागपूर',
    'wardha' => 'वर्धा',
    'bhandara' => 'भंडारा',
    'gondia' => 'गोंदिया',
    'chandrapur' => 'चंद्रपूर',
    'gadchiroli' => 'गडचिरोली'
];

// Get all regions for dropdown
$all_regions = [
    'kokan' => 'कोकण विभाग',
    'pune' => 'पुणे विभाग',
    'sambhajinagar' => 'संभाजीनगर विभाग',
    'nashik' => 'नाशिक विभाग',
    'amaravati' => 'अमरावती विभाग',
    'nagpur' => 'नागपूर विभाग'
];
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>बातमी तपशील - मंजुरी प्रक्रिया</title>
    
        
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <!-- Include Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #FFF8F0;
            font-family: 'Mukta', sans-serif;
        }
        
        .btn {
            transition: all 0.3s ease;
            font-family: 'Mukta', sans-serif;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        }
        
        .card {
            transition: transform 0.3s ease;
            font-family: 'Mukta', sans-serif;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #FFF3E0;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #FFA500;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #FF6600;
        }
        
        /* Form styles for edit mode */
        .form-control:focus, .form-select:focus {
            border-color: #FF6600 !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25) !important;
        }
        
        textarea.form-control {
            font-family: 'Mukta', sans-serif !important;
        }
        
        /* Edit mode indicator */
        .badge.bg-warning {
            font-family: 'Mukta', sans-serif;
            font-size: 12px;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .card-header h4 {
                font-size: 1.2rem !important;
            }
            
            h3 {
                font-size: 1.5rem !important;
            }
            
            .card-body {
                padding: 1rem !important;
            }
            
            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column !important;
                gap: 10px;
            }
            
            /* Toastify adjustments for mobile */
            .toastify {
                max-width: 90% !important;
                margin: 10px auto !important;
                border-radius: 10px !important;
                font-size: 14px !important;
            }
        }
        
        /* Toastify custom styles */
        .custom-toast {
            font-family: 'Mukta', sans-serif !important;
            font-size: 16px !important;
            border-radius: 8px !important;
            padding: 15px 20px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }
        
        .custom-toast-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            color: white !important;
        }
        
        .custom-toast-error {
            background: linear-gradient(135deg, #dc3545, #fd7e14) !important;
            color: white !important;
        }
        
        .custom-toast-warning {
            background: linear-gradient(135deg, #ffc107, #ff8c00) !important;
            color: black !important;
        }
        
        .custom-toast-info {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            color: white !important;
        }
        
        /* Hide approval buttons in edit mode */
        .edit-mode .approval-buttons {
            display: none !important;
        }
        
        /* Hide save buttons when not in edit mode */
        .view-mode .save-buttons {
            display: none !important;
        }
        
        /* Hide edit button in edit mode */
        .edit-mode .edit-button {
            display: none !important;
        }
    </style>
</head>
<body class="<?php echo $edit_mode ? 'edit-mode' : 'view-mode'; ?>">
  

    <div class="container mt-4 mb-5">
        <!-- Toastify Notifications will appear here -->
        
        <!-- Back Button and Edit Toggle -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="newsapproval.php?status=<?php echo $news['is_approved'] == 0 ? 'pending' : ($news['is_approved'] == 1 ? 'approved' : 'disapproved'); ?>" 
               class="btn btn-outline-primary" 
               style="font-family: 'Mukta', sans-serif;">
                <i class="fas fa-arrow-left me-2"></i> मागे जा
            </a>
            
            <?php if ($news['is_approved'] == 0): ?>
            <div class="d-flex gap-2">
                <?php if (!$edit_mode): ?>
                <a href="?news_id=<?php echo $news_id; ?>&edit=1" 
                   class="btn btn-warning edit-button" 
                   style="font-family: 'Mukta', sans-serif;">
                    <i class="fas fa-edit me-2"></i> एडिट करा
                </a>
                <?php else: ?>
                <a href="?news_id=<?php echo $news_id; ?>" 
                   class="btn btn-secondary" 
                   style="font-family: 'Mukta', sans-serif;">
                    <i class="fas fa-times me-2"></i> एडिट बंद करा
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Main Card -->
        <div class="card shadow-lg" style="border: 3px solid #FF6600; border-radius: 15px;">
            <div class="card-header text-center py-3" style="
                background: linear-gradient(135deg, 
                <?php 
                if($news['is_approved'] == 0) echo '#FF6600, #FF8C00';
                elseif($news['is_approved'] == 1) echo '#28a745, #20c997';
                else echo '#dc3545, #fd7e14';
                ?>); 
                color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge rounded-pill px-3 py-2" style="
                            background: rgba(255, 255, 255, 0.2); 
                            font-family: 'Mukta', sans-serif; 
                            font-size: 16px;">
                            <i class="fas fa-clipboard-check me-1"></i>
                            <?php echo getMarathiStatusName($news['is_approved']); ?>
                            <?php if ($edit_mode): ?>
                            <span class="ms-2"><i class="fas fa-edit"></i> एडिट मोड</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <h4 class="mb-0" style="font-family: 'Khand', sans-serif; font-weight: bold;">
                        <i class="fas fa-newspaper me-2"></i>बातमी तपशील
                    </h4>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Images Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header" style="background-color: #FFF3E0;">
                                <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-image me-2"></i> मुख्य चित्र
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($news['cover_photo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($news['cover_photo_url']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="मुख्य चित्र"
                                         style="max-height: 300px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="py-5 text-center">
                                        <i class="fas fa-image" style="font-size: 80px; color: #FFA500;"></i>
                                        <p class="mt-3 text-muted" style="font-family: 'Mukta', sans-serif;">
                                            चित्र उपलब्ध नाही
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header" style="background-color: #FFF3E0;">
                                <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-images me-2"></i> दुय्यम चित्र
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($news['secondary_photo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($news['secondary_photo_url']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="दुय्यम चित्र"
                                         style="max-height: 300px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="py-5 text-center">
                                        <i class="fas fa-images" style="font-size: 80px; color: #FFA500;"></i>
                                        <p class="mt-3 text-muted" style="font-family: 'Mukta', sans-serif;">
                                            दुय्यम चित्र उपलब्ध नाही
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- News Details Form -->
                <form method="POST" action="">
                    <input type="hidden" name="news_id" value="<?php echo $news['news_id']; ?>">
                    <input type="hidden" name="update_news" value="1">
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Title -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #FFF3E0;">
                                    <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-heading me-2"></i> बातमी शीर्षक
                                    </h5>
                                    <?php if ($edit_mode): ?>
                                    <span class="badge bg-warning">एडिट करता येईल</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($edit_mode): ?>
                                    <input type="text" 
                                           class="form-control" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($news['title']); ?>"
                                           required
                                           style="font-family: 'Mukta', sans-serif; font-size: 1.5rem; font-weight: bold; border: 2px solid #FFA500;">
                                    <?php else: ?>
                                    <h3 style="font-family: 'Khand', sans-serif; font-weight: bold; color: #333;">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </h3>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Summary -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #FFF3E0;">
                                    <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-align-left me-2"></i> सारांश
                                    </h5>
                                    <?php if ($edit_mode): ?>
                                    <span class="badge bg-warning">एडिट करता येईल</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($edit_mode): ?>
                                    <textarea class="form-control" 
                                              name="summary" 
                                              rows="5"
                                              required
                                              style="font-family: 'Mukta', sans-serif; font-size: 18px; line-height: 1.6; border: 2px solid #FFA500;"><?php echo htmlspecialchars($news['summary']); ?></textarea>
                                    <?php else: ?>
                                    <p style="font-family: 'Mukta', sans-serif; font-size: 18px; line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($news['summary'])); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Full Content -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #FFF3E0;">
                                    <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-newspaper me-2"></i> संपूर्ण बातमी
                                    </h5>
                                    <?php if ($edit_mode): ?>
                                    <span class="badge bg-warning">एडिट करता येईल</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($edit_mode): ?>
                                    <textarea class="form-control" 
                                              name="content" 
                                              rows="15"
                                              required
                                              style="font-family: 'Mukta', sans-serif; font-size: 16px; line-height: 1.8; border: 2px solid #FFA500;"><?php echo htmlspecialchars($news['content']); ?></textarea>
                                    <?php else: ?>
                                    <div style="font-family: 'Mukta', sans-serif; font-size: 16px; line-height: 1.8;">
                                        <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Meta Information -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-header" style="background-color: #FFF3E0;">
                                    <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-info-circle me-2"></i> माहिती
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless" style="font-family: 'Mukta', sans-serif;">
                                        <tr>
                                            <th width="40%"><i class="fas fa-building me-2"></i> विभाग:</th>
                                            <td>
                                                <?php if ($edit_mode): ?>
                                                <select class="form-select form-select-sm" name="region" style="border: 1px solid #FFA500;">
                                                    <?php foreach ($all_regions as $region_value => $region_name): ?>
                                                    <option value="<?php echo $region_value; ?>" <?php echo (strtolower($news['Region']) == strtolower($region_value)) ? 'selected' : ''; ?>>
                                                        <?php echo $region_name; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php else: ?>
                                                <?php echo getMarathiDivisionName($news['Region']); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-map-marker-alt me-2"></i> जिल्हा:</th>
                                            <td>
                                                <?php if ($edit_mode): ?>
                                                <select class="form-select form-select-sm" name="district" style="border: 1px solid #FFA500;">
                                                    <?php foreach ($all_districts as $district_value => $district_name): ?>
                                                    <option value="<?php echo $district_value; ?>" <?php echo (strtolower($news['district_name']) == strtolower($district_value)) ? 'selected' : ''; ?>>
                                                        <?php echo $district_name; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php else: ?>
                                                <?php echo getMarathiDistrictName($news['district_name']); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-tags me-2"></i> वर्ग:</th>
                                            <td>
                                                <?php if ($edit_mode): ?>
                                                <select class="form-select form-select-sm" name="category" style="border: 1px solid #FFA500;">
                                                    <?php foreach ($categories as $category_value => $category_name): ?>
                                                    <option value="<?php echo $category_value; ?>" <?php echo (strtolower($news['category_name']) == strtolower($category_value)) ? 'selected' : ''; ?>>
                                                        <?php echo $category_name; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php else: ?>
                                                <?php echo getMarathiCategoryName($news['category_name']); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-user me-2"></i> प्रकाशक:</th>
                                            <td><?php echo htmlspecialchars($news['published_by']); ?></td>
                                        </tr>
                                        <?php if (!empty($news['approved_by'])): ?>
                                        <tr>
                                            <th><i class="fas fa-user-check me-2"></i> मंजूर करणारा:</th>
                                            <td><?php echo htmlspecialchars($news['approved_by']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th><i class="fas fa-calendar-alt me-2"></i> प्रकाशन तारीख:</th>
                                            <td><?php echo date('d-m-Y', strtotime($news['published_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-clock me-2"></i> तयार केले:</th>
                                            <td><?php echo date('d-m-Y H:i', strtotime($news['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-sync-alt me-2"></i> अद्ययावत केले:</th>
                                            <td><?php echo date('d-m-Y H:i', strtotime($news['updated_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-star me-2"></i> टॉप न्यूज:</th>
                                            <td><?php echo (!empty($news['topnews']) && $news['topnews'] == 1) ? 'होय' : 'नाही'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Save Buttons (Only in Edit Mode) -->
                            <?php if ($edit_mode): ?>
                            <div class="card shadow-sm mb-4 border-primary save-buttons">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0" style="font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-save me-2"></i> अपडेट करा
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" 
                                                class="btn btn-lg btn-primary shadow"
                                                onclick="showLoadingToast()">
                                            <i class="fas fa-save me-2"></i> बदल सेव्ह करा
                                        </button>
                                        
                                        <a href="?news_id=<?php echo $news_id; ?>" 
                                           class="btn btn-lg btn-secondary shadow">
                                            <i class="fas fa-times me-2"></i> रद्द करा
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Approval Actions (only for pending news and NOT in edit mode) -->
                            <?php if ($news['is_approved'] == 0 && !$edit_mode): ?>
                            <div class="card shadow-sm mb-4 border-success approval-buttons">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0" style="font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-clipboard-check me-2"></i> मंजुरी क्रिया
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="backend/approve_news.php" method="POST" id="approvalForm">
                                        <input type="hidden" name="news_id" value="<?php echo $news['news_id']; ?>">
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" 
                                                    name="action" 
                                                    value="approve"
                                                    class="btn btn-lg btn-success shadow"
                                                    onclick="showApprovalToast('approve')">
                                                <i class="fas fa-check-circle me-2"></i> मान्य करा
                                            </button>
                                            
                                            <button type="submit" 
                                                    name="action" 
                                                    value="disapprove"
                                                    class="btn btn-lg btn-danger shadow"
                                                    onclick="showApprovalToast('disapprove')">
                                                <i class="fas fa-times-circle me-2"></i> नामंजूर करा
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Status Information -->
                            <div class="card shadow-sm border-<?php echo $news['is_approved'] == 0 ? 'warning' : ($news['is_approved'] == 1 ? 'success' : 'danger'); ?>">
                                <div class="card-header bg-<?php echo $news['is_approved'] == 0 ? 'warning' : ($news['is_approved'] == 1 ? 'success' : 'danger'); ?> text-white">
                                    <h5 class="mb-0" style="font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-info-circle me-2"></i> स्थिती माहिती
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas 
                                            <?php 
                                            if($news['is_approved'] == 0) echo 'fa-clock';
                                            elseif($news['is_approved'] == 1) echo 'fa-check-circle';
                                            else echo 'fa-times-circle';
                                            ?>" 
                                            style="font-size: 60px; 
                                            color: <?php 
                                            if($news['is_approved'] == 0) echo '#FFA500';
                                            elseif($news['is_approved'] == 1) echo '#28a745';
                                            else echo '#dc3545';
                                            ?>;">
                                        </i>
                                    </div>
                                    <h4 class="mb-2" style="font-family: 'Khand', sans-serif;">
                                        <?php echo getMarathiStatusName($news['is_approved']); ?>
                                    </h4>
                                    <p class="mb-0 text-muted" style="font-family: 'Mukta', sans-serif;">
                                        <?php 
                                        if($news['is_approved'] == 0) {
                                            echo 'बातमी मंजुरीसाठी प्रलंबित आहे';
                                        } elseif($news['is_approved'] == 1) {
                                            echo 'बातमी मंजूर केली गेली आहे';
                                            echo '<br><small>' . date('d-m-Y H:i', strtotime($news['updated_at'])) . '</small>';
                                        } else {
                                            echo 'बातमी नामंजूर केली गेली आहे';
                                            echo '<br><small>' . date('d-m-Y H:i', strtotime($news['updated_at'])) . '</small>';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-footer text-center py-3" style="background-color: #FFF8F0; border-top: 2px dashed #FFA500;">
                <small style="font-family: 'Mukta', sans-serif;">
                    <i class="fas fa-info-circle me-1" style="color: #FF6600;"></i>
                    बातमी ID: <?php echo $news['news_id']; ?> | 
                    तयार केली: <?php echo date('d-m-Y H:i', strtotime($news['created_at'])); ?> | 
                    अद्ययावत: <?php echo date('d-m-Y H:i', strtotime($news['updated_at'])); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // Function to show toast notification
        function showToast(message, type = 'info', duration = 5000) {
            let backgroundColor;
            let className;
            
            switch(type) {
                case 'success':
                    backgroundColor = "linear-gradient(135deg, #28a745, #20c997)";
                    className = "custom-toast custom-toast-success";
                    break;
                case 'error':
                    backgroundColor = "linear-gradient(135deg, #dc3545, #fd7e14)";
                    className = "custom-toast custom-toast-error";
                    break;
                case 'warning':
                    backgroundColor = "linear-gradient(135deg, #ffc107, #ff8c00)";
                    className = "custom-toast custom-toast-warning";
                    break;
                case 'info':
                    backgroundColor = "linear-gradient(135deg, #17a2b8, #20c997)";
                    className = "custom-toast custom-toast-info";
                    break;
                default:
                    backgroundColor = "linear-gradient(135deg, #333, #666)";
                    className = "custom-toast";
            }
            
            Toastify({
                text: message,
                duration: duration,
                gravity: "top",
                position: "center",
                stopOnFocus: true,
                className: className,
                style: {
                    background: backgroundColor,
                    fontFamily: "'Mukta', sans-serif",
                    fontSize: "16px",
                    borderRadius: "8px",
                    boxShadow: "0 5px 15px rgba(0,0,0,0.2)",
                    padding: "15px 20px"
                },
                offset: {
                    y: 70
                }
            }).showToast();
        }
        
        // Function to show loading toast
        function showLoadingToast() {
            showToast("बातमी अपडेट केली जात आहे... कृपया प्रतीक्षा करा", "info", 3000);
        }
        
        // Function to show approval/disapproval toast
        function showApprovalToast(action) {
            if (action === 'approve') {
                if (!confirm('तुम्हाला खात्री आहे की तुम्हाला ही बातमी मान्य करायची आहे?')) {
                    return false;
                }
                showToast("बातमी मान्य केली जात आहे...", "info", 2000);
            } else if (action === 'disapprove') {
                if (!confirm('तुम्हाला खात्री आहे की तुम्हाला ही बातमी नामंजूर करायची आहे?')) {
                    return false;
                }
                showToast("बातमी नामंजूर केली जात आहे...", "info", 2000);
            }
            return true;
        }
        
        // Display stored toast messages from PHP session
        document.addEventListener('DOMContentLoaded', function() {
            <?php 
            // Check for URL parameter notifications first
            if (isset($_GET['approval_success'])): ?>
                showToast("बातमी यशस्वीरित्या मान्य केली गेली!", "success");
                // Remove parameter from URL without reloading
                window.history.replaceState({}, document.title, window.location.pathname + '?news_id=<?php echo $news_id; ?>');
            <?php elseif (isset($_GET['disapproval_success'])): ?>
                showToast("बातमी यशस्वीरित्या नामंजूर केली गेली!", "warning");
                window.history.replaceState({}, document.title, window.location.pathname + '?news_id=<?php echo $news_id; ?>');
            <?php elseif (isset($_GET['approval_error'])): ?>
                showToast("बातमी मंजुरी/नामंजुरी करताना त्रुटी आली.", "error");
                window.history.replaceState({}, document.title, window.location.pathname + '?news_id=<?php echo $news_id; ?>');
            <?php endif; ?>
            
            // Check for local toast messages (from form submission)
            <?php if (!empty($toast_message)): ?>
                showToast("<?php echo $toast_message; ?>", "<?php echo $toast_type; ?>");
            <?php endif; ?>
            
            // Remove URL parameters after showing toast
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('approval_success') || urlParams.has('disapproval_success') || urlParams.has('approval_error')) {
                // URL already cleaned above
            }
        });
        
        // Confirm before disapproval in the form submission
        document.addEventListener('DOMContentLoaded', function() {
            const approvalForm = document.getElementById('approvalForm');
            if (approvalForm) {
                approvalForm.addEventListener('submit', function(e) {
                    const action = document.activeElement.value;
                    
                    if (action === 'disapprove') {
                        if (!confirm('तुम्हाला खात्री आहे की तुम्हाला ही बातमी नामंजूर करायची आहे?')) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>

    <?php include 'components/footer.php'; ?>
</body>
</html>