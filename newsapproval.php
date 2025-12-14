<?php
// newsapproval.php
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

// Get user info
$user_roll = $_SESSION['roll'] ?? '';
$user_location = $_SESSION['location'] ?? '';

// DEBUG: Check session values
// echo "Debug: user_roll = $user_roll, user_location = $user_location<br>";

// Get status filter from URL
$status_filter = $_GET['status'] ?? 'pending'; // Default: pending
$status_map = [
    'pending' => 0,
    'approved' => 1,
    'disapproved' => 2
];
$current_status = $status_map[$status_filter] ?? 0;

// Get Marathi status names
function getMarathiStatusName($status) {
    $status_names = [
        0 => 'प्रलंबित',
        1 => 'मान्य',
        2 => 'नामंजूर'
    ];
    return $status_names[$status] ?? 'अज्ञात';
}

// Get Marathi category names
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

// Get Marathi region names
function getMarathiRegionName($regionValue) {
    // Convert to lowercase for comparison
    $regionValue = strtolower($regionValue);
    $regionMap = [
        'kokan' => 'कोकण',
        'pune' => 'पुणे',
        'sambhajinagar' => 'संभाजीनगर',
        'nashik' => 'नाशिक',
        'amaravati' => 'अमरावती',
        'nagpur' => 'नागपूर',
        'mumbai' => 'मुंबई',
        'ratnagiri' => 'रत्नागिरी',
        'solapur' => 'सोलापूर',
        'kolhapur' => 'कोल्हापूर',
        'thane' => 'ठाणे',
        'raigad' => 'रायगड',
        'jalna' => 'जालना',
        'nanded' => 'नांदेड',
        'ahilyanagar' => 'अहिल्यानगर',
        'sangli' => 'सांगली',
        'satara' => 'सातारा',
        'gadchiroli' => 'गडचिरोली'
    ];
    return isset($regionMap[$regionValue]) ? $regionMap[$regionValue] : $regionValue;
}

// Get Marathi district names
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

// Determine user's region from their location
$user_region = '';
if (!empty($user_location)) {
    $user_region = getRegionFromLocation($user_location);
}

// DEBUG: Check region determination
// echo "Debug: user_region = $user_region<br>";

// Fetch news based on status and user role
function fetchNews($conn, $status, $user_roll, $user_region) {
    $news = [];
    
    // DEBUG: Check parameters
    // echo "Debug: status=$status, user_roll=$user_roll, user_region=$user_region<br>";
    
    if ($user_roll === 'admin' || $user_roll === 'Admin') {
        // Admin can see all news
        $sql = "SELECT * FROM news_articles WHERE is_approved = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
            $stmt->close();
        }
    } elseif ($user_roll === 'division_head') {
        // Division head can see news from their region only
        $sql = "SELECT * FROM news_articles WHERE Region = ? AND is_approved = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $user_region, $status);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
            $stmt->close();
        }
    }
    
    return $news;
}

// Get news count for each status
function getNewsCounts($conn, $user_roll, $user_region) {
    $counts = [
        'pending' => 0,
        'approved' => 0,
        'disapproved' => 0
    ];
    
    if ($user_roll === 'admin' || $user_roll === 'Admin') {
        // Admin: Count all news
        $sql = "SELECT is_approved, COUNT(*) as count FROM news_articles GROUP BY is_approved";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $status = $row['is_approved'];
                if ($status == 0) $counts['pending'] = $row['count'];
                if ($status == 1) $counts['approved'] = $row['count'];
                if ($status == 2) $counts['disapproved'] = $row['count'];
            }
        }
    } elseif ($user_roll === 'division_head') {
        // Division Head: Count only news from their region
        $sql = "SELECT is_approved, COUNT(*) as count FROM news_articles WHERE Region = ? GROUP BY is_approved";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $user_region);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['is_approved'];
                    if ($status == 0) $counts['pending'] = $row['count'];
                    if ($status == 1) $counts['approved'] = $row['count'];
                    if ($status == 2) $counts['disapproved'] = $row['count'];
                }
            }
            $stmt->close();
        }
    }
    
    return $counts;
}

$news_items = fetchNews($conn, $current_status, $user_roll, $user_region);
$news_counts = getNewsCounts($conn, $user_roll, $user_region);

// DEBUG: Check news counts
// echo "Debug: pending={$news_counts['pending']}, approved={$news_counts['approved']}, disapproved={$news_counts['disapproved']}<br>";
?>

<div class="container mt-4 mb-5">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
            <i class="fas fa-clipboard-check me-2"></i>बातम्या मंजुरी
        </h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                <i class="fas fa-question-circle me-1"></i> मदत
            </button>
            <!-- <a href="post_news.php" class="btn btn-sm" style="background: linear-gradient(135deg, #FF6600, #FF8C00); color: white;">
                <i class="fas fa-plus me-1"></i> नवीन बातमी
            </a> -->
        </div>
    </div>
    
    <!-- User Role Info Badge -->
    <div class="mb-4 text-center">
        <span class="badge rounded-pill px-4 py-2" 
              style="font-size: 16px; font-family: 'Mukta', sans-serif; 
                     background: linear-gradient(135deg, #6c757d, #495057);
                     color: white;">
            <i class="fas fa-user-shield me-1"></i>
            <?php 
            if ($user_roll === 'admin' || $user_roll === 'Admin') {
                echo 'प्रशासक - सर्व बातम्या';
            } elseif ($user_roll === 'division_head') {
                echo 'प्रदेश प्रमुख - ' . getMarathiRegionName($user_region) . ' विभाग';
            }
            ?>
        </span>
    </div>
    
    <!-- Status Filter Tabs -->
    <div class="card shadow-sm mb-4" style="border: 2px solid #FF6600; border-radius: 10px;">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                <!-- Pending Tab -->
                <a href="?status=pending" class="btn btn-lg shadow position-relative <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" 
                   style="<?php echo $status_filter === 'pending' ? 'background: linear-gradient(135deg, #FF6600, #FF8C00); color: white;' : 'background: #FFF3E0; color: #FF6600; border: 2px solid #FFA500;'; ?> 
                          padding: 12px 24px; font-family: 'Khand', sans-serif; font-weight: bold; min-width: 180px; text-decoration: none;">
                    <i class="fas fa-clock me-2"></i> प्रलंबित
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $news_counts['pending']; ?>
                    </span>
                </a>
                
                <!-- Approved Tab -->
                <a href="?status=approved" class="btn btn-lg shadow position-relative <?php echo $status_filter === 'approved' ? 'active' : ''; ?>" 
                   style="<?php echo $status_filter === 'approved' ? 'background: linear-gradient(135deg, #28a745, #20c997); color: white;' : 'background: #E8F5E9; color: #28a745; border: 2px solid #28a745;'; ?> 
                          padding: 12px 24px; font-family: 'Khand', sans-serif; font-weight: bold; min-width: 180px; text-decoration: none;">
                    <i class="fas fa-check-circle me-2"></i> मान्य
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                        <?php echo $news_counts['approved']; ?>
                    </span>
                </a>
                
                <!-- Disapproved Tab -->
                <a href="?status=disapproved" class="btn btn-lg shadow position-relative <?php echo $status_filter === 'disapproved' ? 'active' : ''; ?>" 
                   style="<?php echo $status_filter === 'disapproved' ? 'background: linear-gradient(135deg, #dc3545, #fd7e14); color: white;' : 'background: #FFEBEE; color: #dc3545; border: 2px solid #dc3545;'; ?> 
                          padding: 12px 24px; font-family: 'Khand', sans-serif; font-weight: bold; min-width: 180px; text-decoration: none;">
                    <i class="fas fa-times-circle me-2"></i> नामंजूर
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                        <?php echo $news_counts['disapproved']; ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Current Status Badge -->
    <div class="mb-4 text-center">
        <span class="badge rounded-pill px-4 py-2" 
              style="font-size: 18px; font-family: 'Khand', sans-serif; 
                     <?php 
                     if($status_filter === 'pending') echo 'background: linear-gradient(135deg, #FF6600, #FF8C00);';
                     elseif($status_filter === 'approved') echo 'background: linear-gradient(135deg, #28a745, #20c997);';
                     else echo 'background: linear-gradient(135deg, #dc3545, #fd7e14);';
                     ?> color: white;">
            <i class="fas fa-filter me-1"></i>
            <?php echo getMarathiStatusName($current_status); ?> बातम्या
            (एकूण: <?php echo count($news_items); ?>)
        </span>
    </div>
    
    <!-- News Cards Grid -->
    <?php if (empty($news_items)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-newspaper" style="font-size: 80px; color: #FFA500;"></i>
            </div>
            <h4 style="color: #FF6600; font-family: 'Khand', sans-serif;">
                कोणत्याही <?php echo getMarathiStatusName($current_status); ?> बातम्या नाहीत
            </h4>
            <p class="text-muted" style="font-family: 'Mukta', sans-serif;">
                <?php echo getMarathiStatusName($current_status); ?> स्थितीतील सर्व बातम्या दाखवल्या गेल्या आहेत.
            </p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($news_items as $news): ?>
                <div class="col">
                    <div class="card h-100 shadow news-card" 
                         style="border: 2px solid <?php 
                         if($news['is_approved'] == 0) echo '#FFA500';
                         elseif($news['is_approved'] == 1) echo '#28a745';
                         else echo '#dc3545';
                         ?>; 
                         border-radius: 12px; 
                         transition: all 0.3s ease;
                         cursor: default;">
                        <div class="card-header p-3" style="
                            background: linear-gradient(135deg, 
                            <?php 
                            if($news['is_approved'] == 0) echo '#FFF3E0';
                            elseif($news['is_approved'] == 1) echo '#E8F5E9';
                            else echo '#FFEBEE';
                            ?>, 
                            white); 
                            border-bottom: 2px solid 
                            <?php 
                            if($news['is_approved'] == 0) echo '#FFA500';
                            elseif($news['is_approved'] == 1) echo '#28a745';
                            else echo '#dc3545';
                            ?>;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge rounded-pill px-3 py-2" style="
                                    background: 
                                    <?php 
                                    if($news['is_approved'] == 0) echo 'linear-gradient(135deg, #FF6600, #FF8C00)';
                                    elseif($news['is_approved'] == 1) echo 'linear-gradient(135deg, #28a745, #20c997)';
                                    else echo 'linear-gradient(135deg, #dc3545, #fd7e14)';
                                    ?>; 
                                    font-family: 'Mukta', sans-serif; font-weight: 500;">
                                    <?php echo getMarathiStatusName($news['is_approved']); ?>
                                </span>
                                <small class="text-muted" style="font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('d-m-Y', strtotime($news['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Cover Photo -->
                        <div style="height: 180px; overflow: hidden;">
                            <?php if (!empty($news['cover_photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($news['cover_photo_url']); ?>" 
                                     class="card-img-top" 
                                     alt="Cover Photo"
                                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center" 
                                     style="height: 100%; background: linear-gradient(135deg, #FFA500, #FFD700);">
                                    <i class="fas fa-newspaper" style="font-size: 60px; color: white;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <!-- Title -->
                            <h5 class="card-title mb-3" style="
                                color: #FF6600; 
                                font-family: 'Khand', sans-serif; 
                                font-weight: bold;
                                height: 60px;
                                overflow: hidden;
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;">
                                <?php echo htmlspecialchars($news['title']); ?>
                            </h5>
                            
                            <!-- Details -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-globe me-2" style="color: #FF6600; width: 20px;"></i>
                                        <small style="font-family: 'Mukta', sans-serif;">
                                            <strong>विभाग:</strong>
                                            <?php echo getMarathiRegionName($news['Region']); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt me-2" style="color: #FF6600; width: 20px;"></i>
                                        <small style="font-family: 'Mukta', sans-serif;">
                                            <strong>जिल्हा:</strong>
                                            <?php echo getMarathiDistrictName($news['district_name']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tags me-2" style="color: #FF6600; width: 20px;"></i>
                                        <small style="font-family: 'Mukta', sans-serif;">
                                            <strong>वर्ग:</strong>
                                            <?php echo getMarathiCategoryName($news['category_name']); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user me-2" style="color: #FF6600; width: 20px;"></i>
                                        <small style="font-family: 'Mukta', sans-serif;">
                                            <strong>प्रकाशक:</strong>
                                            <?php echo htmlspecialchars($news['published_by']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Summary -->
                            <div class="mb-3">
                                <p class="card-text" style="
                                    font-family: 'Mukta', sans-serif;
                                    font-size: 14px;
                                    color: #666;
                                    height: 60px;
                                    overflow: hidden;
                                    display: -webkit-box;
                                    -webkit-line-clamp: 3;
                                    -webkit-box-orient: vertical;">
                                    <?php echo htmlspecialchars($news['summary']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <div class="d-grid">
                                <a href="newsapproval_details.php?news_id=<?php echo $news['news_id']; ?>" 
                                   class="btn btn-sm view-details-btn"
                                   style="
                                        background: linear-gradient(135deg, #FF6600, #FF8C00);
                                        color: white;
                                        font-family: 'Khand', sans-serif;
                                        font-weight: bold;
                                        padding: 8px 16px;
                                        text-decoration: none;
                                        text-align: center;
                                        display: block;">
                                    <i class="fas fa-eye me-1"></i> तपशील पहा
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FF6600, #FF8C00); color: white;">
                <h5 class="modal-title" id="helpModalLabel" style="font-family: 'Khand', sans-serif;">
                    <i class="fas fa-question-circle me-2"></i>बातम्या मंजुरी मार्गदर्शन
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-family: 'Mukta', sans-serif;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-clock me-1"></i> प्रलंबित बातम्या</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>नवीन सबमिट केलेल्या बातम्या</li>
                                    <li>मंजुरीसाठी प्रलंबित</li>
                                    <li>मान्य/नामंजूर करण्यासाठी क्लिक करा</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-check-circle me-1"></i> मान्य बातम्या</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>मंजुर केलेल्या बातम्या</li>
                                    <li>वेबसाइटवर प्रकाशित होतील</li>
                                    <li>माहिती पुन्हा तपासण्यासाठी</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fas fa-times-circle me-1"></i> नामंजूर बातम्या</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>नामंजूर केलेल्या बातम्या</li>
                                    <li>वेबसाइटवर प्रकाशित होणार नाहीत</li>
                                    <li>कारणासह नामंजूर केल्या</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-user-shield me-1"></i> परवानग्या</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>प्रशासक:</strong> सर्व बातम्या पहा/मंजूर करा</li>
                                    <li><strong>प्रदेश प्रमुख:</strong> फक्त आपल्या प्रदेशातील बातम्या</li>
                                    <li><strong>जिल्हा वापरकर्ता:</strong> या पेजवर प्रवेश नाही</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-family: 'Mukta', sans-serif;">
                    बंद करा
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery and Toastr -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Include Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        background-color: #FFF8F0;
        font-family: 'Mukta', sans-serif;
    }
    
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(255, 102, 0, 0.2) !important;
    }
    
    .news-card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    .view-details-btn:hover {
        background: linear-gradient(135deg, #FF8C00, #FFA500) !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3) !important;
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
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .btn-lg {
            min-width: 140px !important;
            padding: 10px 16px !important;
            font-size: 14px !important;
        }
        
        .card-title {
            font-size: 16px !important;
            height: 48px !important;
        }
        
        .card-text {
            font-size: 13px !important;
            height: 54px !important;
        }
    }
    
    @media (max-width: 576px) {
        .btn-lg {
            min-width: 120px !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        
        h2 {
            font-size: 1.3rem !important;
        }
    }
</style>

<script>
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Check for URL parameters on page load
    function checkURLParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Show success message
        if (urlParams.get('approved') === '1') {
            toastr.success('बातमी यशस्वीरित्या मान्य केली गेली!');
            setTimeout(() => {
                window.history.replaceState({}, document.title, window.location.pathname + '?status=approved');
            }, 100);
        }
        
        if (urlParams.get('disapproved') === '1') {
            toastr.success('बातमी यशस्वीरित्या नामंजूर केली गेली!');
            setTimeout(() => {
                window.history.replaceState({}, document.title, window.location.pathname + '?status=disapproved');
            }, 100);
        }
        
        if (urlParams.get('error')) {
            const errorMsg = urlParams.get('error');
            toastr.error(decodeURIComponent(errorMsg));
            setTimeout(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 100);
        }
    }

    $(document).ready(function() {
        checkURLParameters();
        
        // Only need to handle card hover effects now
        $('.news-card').on('mouseenter', function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 25px rgba(255, 102, 0, 0.2)'
            });
        }).on('mouseleave', function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 5px rgba(0,0,0,0.1)'
            });
        });
    });
</script>

<?php
include 'components/footer.php';
?>