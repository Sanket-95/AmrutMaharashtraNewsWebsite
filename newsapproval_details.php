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

// Fetch news details from news_articles table
$news = [];
$sql = "SELECT * FROM news_articles WHERE news_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $news = $result->fetch_assoc();
} else {
    // Redirect if news not found
    header('Location: newsapproval.php?status=pending&error=बातमी%20सापडली%20नाही');
    exit();
}

$stmt->close();

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
    header('Location: newsapproval.php?status=pending&error=आपल्याला%20या%20बातमीवर%20परवानगी%20नाही');
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
?>

<div class="container mt-4 mb-5">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="newsapproval.php?status=<?php echo $news['is_approved'] == 0 ? 'pending' : ($news['is_approved'] == 1 ? 'approved' : 'disapproved'); ?>" 
           class="btn btn-outline-primary" 
           style="font-family: 'Mukta', sans-serif;">
            <i class="fas fa-arrow-left me-2"></i> मागे जा
        </a>
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
                    </span>
                </div>
                <h4 class="mb-0" style="font-family: 'Khand', sans-serif; font-weight: bold;">
                    <i class="fas fa-newspaper me-2"></i>बातमी तपशील
                </h4>
                <!-- <div>
                    <span class="badge rounded-pill px-3 py-2" style="
                        background: rgba(255, 255, 255, 0.2); 
                        font-family: 'Mukta', sans-serif; 
                        font-size: 16px;">
                        ID: <?php echo $news['news_id']; ?>
                    </span>
                </div> -->
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
            
            <!-- News Details -->
            <div class="row">
                <div class="col-lg-8">
                    <!-- Title -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header" style="background-color: #FFF3E0;">
                            <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-heading me-2"></i> बातमी शीर्षक
                            </h5>
                        </div>
                        <div class="card-body">
                            <h3 style="font-family: 'Khand', sans-serif; font-weight: bold; color: #333;">
                                <?php echo htmlspecialchars($news['title']); ?>
                            </h3>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header" style="background-color: #FFF3E0;">
                            <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-align-left me-2"></i> सारांश
                            </h5>
                        </div>
                        <div class="card-body">
                            <p style="font-family: 'Mukta', sans-serif; font-size: 18px; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($news['summary'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Full Content -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header" style="background-color: #FFF3E0;">
                            <h5 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-newspaper me-2"></i> संपूर्ण बातमी
                            </h5>
                        </div>
                        <div class="card-body">
                            <div style="font-family: 'Mukta', sans-serif; font-size: 16px; line-height: 1.8;">
                                <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                            </div>
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
                                    <th width="40%"><i class="fas fa-globe me-2"></i> प्रदेश:</th>
                                    <td><?php echo getMarathiRegionName($news['Region']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-map-marker-alt me-2"></i> जिल्हा:</th>
                                    <td><?php echo getMarathiDistrictName($news['district_name']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-tags me-2"></i> वर्ग:</th>
                                    <td><?php echo getMarathiCategoryName($news['category_name']); ?></td>
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
                                    <th><i class="fas fa-eye me-2"></i> दृश्ये:</th>
                                    <td><?php echo $news['view'] ?? 0; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-star me-2"></i> टॉप न्यूज:</th>
                                    <td><?php echo (!empty($news['topnews']) && $news['topnews'] == 1) ? 'होय' : 'नाही'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Approval Actions (only for pending news) -->
                    <?php if ($news['is_approved'] == 0): ?>
                    <div class="card shadow-sm mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0" style="font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-clipboard-check me-2"></i> मंजुरी क्रिया
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="backend/approve_news.php" method="POST" id="approvalForm">
                                <input type="hidden" name="news_id" value="<?php echo $news['news_id']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold" style="font-family: 'Mukta', sans-serif;">
                                        <i class="fas fa-comment me-1"></i> टिप्पणी (ऐच्छिक)
                                    </label>
                                    <textarea class="form-control" 
                                              name="approval_comment" 
                                              rows="3" 
                                              placeholder="मंजुरीसाठी टिप्पणी..."
                                              style="font-family: 'Mukta', sans-serif;"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" 
                                            name="action" 
                                            value="approve"
                                            class="btn btn-lg btn-success shadow">
                                        <i class="fas fa-check-circle me-2"></i> मान्य करा
                                    </button>
                                    
                                    <button type="submit" 
                                            name="action" 
                                            value="disapprove"
                                            class="btn btn-lg btn-danger shadow">
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
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }
    
    .card {
        transition: transform 0.3s ease;
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

    // Check for URL parameters
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('error')) {
            const errorMsg = urlParams.get('error');
            toastr.error(decodeURIComponent(errorMsg));
        }
        
        // Confirm before disapproval
        $('#approvalForm').on('submit', function(e) {
            const action = $('button[type="submit"]:focus').val();
            
            if (action === 'disapprove') {
                if (!confirm('तुम्हाला खात्री आहे की तुम्हाला ही बातमी नामंजूर करायची आहे?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>

<?php
include 'components/footer.php';
?>