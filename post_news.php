<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if db_config.php exists and include it
if (!file_exists('components/db_config.php')) {
    die('Database configuration file not found. Please check the path.');
}
include 'components/db_config.php';

// Test database connection
if (!$conn || mysqli_connect_errno()) {
    die('Database connection failed: ' . mysqli_connect_error());
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Get user info for display
$user_name = $_SESSION['name'] ?? 'User';
$user_roll = $_SESSION['roll'] ?? '';
$user_location = $_SESSION['location'] ?? '';
$username = $_SESSION['username'] ?? '';

// ============================================
// DYNAMIC DATABASE FUNCTIONS (Replacing static arrays)
// ============================================

/**
 * Fetch all regions (divisions) from database
 * @return array Array of regions with id, division, marathiname
 */
function getAllRegions($conn) {
    $regions = [];
    $query = "SELECT id, division, marathiname FROM mdivision ORDER BY division ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $regions[] = [
                'id' => $row['id'],
                'division' => $row['division'],
                'marathiname' => $row['marathiname']
            ];
        }
    }
    return $regions;
}

/**
 * Fetch all categories from database
 * @return array Array of categories with catagory, marathi_name
 */
function getAllCategories($conn) {
    $categories = [];
    $query = "SELECT catagory, marathi_name FROM catagory_list WHERE is_enable = 1 ORDER BY catagory ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = [
                'catagory' => $row['catagory'],
                'marathi_name' => $row['marathi_name']
            ];
        }
    }
    return $categories;
}

/**
 * Fetch districts by region (division) from database
 * @param int $divisionId Division ID
 * @return array Array of districts
 */
function getDistrictsByRegion($conn, $divisionId) {
    $districts = [];
    $query = "SELECT district, dmarathi FROM mdistrict WHERE divisionid = ? ORDER BY district ASC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $divisionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $districts[] = [
                    'district' => $row['district'],
                    'dmarathi' => $row['dmarathi']
                ];
            }
        }
        mysqli_stmt_close($stmt);
    }
    return $districts;
}

/**
 * Get all districts with their region mapping (for quick lookup)
 * @return array Associative array of district -> region
 */
function getAllDistrictsWithRegion($conn) {
    $districtToRegion = [];
    $query = "SELECT d.district, d.divisionid, m.division 
              FROM mdistrict d 
              JOIN mdivision m ON d.divisionid = m.id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $districtToRegion[$row['district']] = $row['division'];
        }
    }
    return $districtToRegion;
}

/**
 * Get region from location (district name or region name)
 * @param string $location District name or region name
 * @return string Region name
 */
function getRegionFromLocation($conn, $location) {
    if (empty($location)) {
        return '';
    }
    
    // First, check if location is a region
    $regionQuery = "SELECT division FROM mdivision WHERE division = ?";
    $stmt = mysqli_prepare($conn, $regionQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $location);
        mysqli_stmt_execute($stmt);
        $regionResult = mysqli_stmt_get_result($stmt);
        
        if ($regionResult && mysqli_num_rows($regionResult) > 0) {
            $row = mysqli_fetch_assoc($regionResult);
            mysqli_stmt_close($stmt);
            return $row['division'];
        }
        mysqli_stmt_close($stmt);
    }
    
    // If not a region, check if it's a district and map to region
    $districtQuery = "SELECT m.division 
                      FROM mdistrict d 
                      JOIN mdivision m ON d.divisionid = m.id 
                      WHERE d.district = ?";
    $stmt = mysqli_prepare($conn, $districtQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $location);
        mysqli_stmt_execute($stmt);
        $districtResult = mysqli_stmt_get_result($stmt);
        
        if ($districtResult && mysqli_num_rows($districtResult) > 0) {
            $row = mysqli_fetch_assoc($districtResult);
            mysqli_stmt_close($stmt);
            return $row['division'];
        }
        mysqli_stmt_close($stmt);
    }
    
    return '';
}

/**
 * Get Marathi name for a district
 * @param string $districtValue English district name
 * @return string Marathi district name
 */
function getMarathiDistrictName($conn, $districtValue) {
    if (empty($districtValue)) {
        return '';
    }
    
    $query = "SELECT dmarathi FROM mdistrict WHERE district = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $districtValue);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $row['dmarathi'];
        }
        mysqli_stmt_close($stmt);
    }
    return $districtValue;
}

/**
 * Get Marathi name for a region
 * @param string $regionValue English region name
 * @return string Marathi region name
 */
function getMarathiRegionName($conn, $regionValue) {
    if (empty($regionValue)) {
        return '';
    }
    
    $query = "SELECT marathiname FROM mdivision WHERE division = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $regionValue);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $row['marathiname'];
        }
        mysqli_stmt_close($stmt);
    }
    return $regionValue;
}

/**
 * Get all regions with their districts as JSON (for JavaScript)
 * @return array Complete region-district mapping
 */
function getAllRegionDistrictMapping($conn) {
    $mapping = [];
    $regions = getAllRegions($conn);
    
    foreach ($regions as $region) {
        $districts = getDistrictsByRegion($conn, $region['id']);
        $districtList = [];
        foreach ($districts as $district) {
            $districtList[] = [
                'value' => $district['district'],
                'text' => $district['dmarathi']
            ];
        }
        $mapping[$region['division']] = $districtList;
    }
    
    return $mapping;
}

// ============================================
// FETCH DYNAMIC DATA FOR CURRENT PAGE
// ============================================

// Initialize variables with default values to prevent undefined variable errors
$allRegions = [];
$allCategories = [];
$regionDistrictMapping = [];
$regionDistrictJSON = '{}';
$user_region = '';
$divisionHeadDistricts = [];
$userDistrictMarathi = '';
$publisher_editable = false;
$publisher_value = $user_name;

// Get all regions for dropdowns
try {
    $allRegions = getAllRegions($conn);
} catch (Exception $e) {
    error_log("Error fetching regions: " . $e->getMessage());
}

// Get all categories for dropdown
try {
    $allCategories = getAllCategories($conn);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get district-to-region mapping for quick lookup
try {
    $districtToRegionMap = getAllDistrictsWithRegion($conn);
} catch (Exception $e) {
    error_log("Error fetching district mapping: " . $e->getMessage());
    $districtToRegionMap = [];
}

// Determine user's region
if (!empty($user_location)) {
    try {
        $user_region = getRegionFromLocation($conn, $user_location);
    } catch (Exception $e) {
        error_log("Error getting user region: " . $e->getMessage());
    }
}

// Get all region-district mapping for JavaScript
try {
    $regionDistrictMapping = getAllRegionDistrictMapping($conn);
    $regionDistrictJSON = json_encode($regionDistrictMapping);
} catch (Exception $e) {
    error_log("Error creating region-district mapping: " . $e->getMessage());
    $regionDistrictJSON = '{}';
}

// Determine if publisher name should be editable
$publisher_editable = ($user_roll === 'admin');
$publisher_value = $user_name;

// For division_head, get districts of their region for pre-population
if ($user_roll === 'division_head' && !empty($user_location)) {
    try {
        // Find division ID for this region
        $divIdQuery = "SELECT id FROM mdivision WHERE division = ?";
        $stmt = mysqli_prepare($conn, $divIdQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $user_location);
            mysqli_stmt_execute($stmt);
            $divResult = mysqli_stmt_get_result($stmt);
            
            if ($divResult && mysqli_num_rows($divResult) > 0) {
                $divRow = mysqli_fetch_assoc($divResult);
                $divisionHeadDistricts = getDistrictsByRegion($conn, $divRow['id']);
            }
            mysqli_stmt_close($stmt);
        }
    } catch (Exception $e) {
        error_log("Error fetching division head districts: " . $e->getMessage());
    }
}

// For district_user, get their district Marathi name
if ($user_roll === 'district_user' && !empty($user_location)) {
    try {
        $userDistrictMarathi = getMarathiDistrictName($conn, $user_location);
    } catch (Exception $e) {
        error_log("Error fetching user district name: " . $e->getMessage());
    }
}

// Get current date and time in the format for datetime-local input
$currentDateTime = date('Y-m-d\TH:i');
?>

<div class="container mt-4 mb-5">
    <!-- Title only -->
    <h2 class="mb-4 text-center" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
        नवीन बातमी प्रकाशित करा
    </h2>
    
    <div class="card shadow-lg" style="border: 3px solid #FF6600; border-radius: 15px;">
        <div class="card-header text-center py-3" style="background: linear-gradient(135deg, #FF6600, #FF8C00); color: white;">
            <h4 class="mb-0" style="font-family: 'Khand', sans-serif; font-weight: bold;">
                <i class="fas fa-newspaper me-2"></i>बातमी तपशील फॉर्म
            </h4>
        </div>
        
        <div class="card-body p-4">
            <form action="backend/submit_news.php" method="POST" enctype="multipart/form-data" id="newsForm">
                <!-- Region and District Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                            <i class="fas fa-globe me-1"></i> प्रदेश निवडा *
                        </label>
                        <?php if($user_roll === 'admin'): ?>
                            <!-- For admin - editable dropdown from database -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <?php if(!empty($allRegions)): ?>
                                    <?php foreach($allRegions as $region): ?>
                                        <option value="<?php echo htmlspecialchars($region['division']); ?>">
                                            <?php echo htmlspecialchars($region['marathiname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>कोणतेही प्रदेश उपलब्ध नाहीत</option>
                                <?php endif; ?>
                            </select>
                        <?php elseif($user_roll === 'division_head'): ?>
                            <!-- For division_head - auto-filled region from session, but editable -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <?php if(!empty($allRegions)): ?>
                                    <?php foreach($allRegions as $region): ?>
                                        <option value="<?php echo htmlspecialchars($region['division']); ?>" <?php echo ($user_location === $region['division']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($region['marathiname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>कोणतेही प्रदेश उपलब्ध नाहीत</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> 
                                <?php if(!empty($user_location)): ?>
                                    आपला प्रदेश "<?php echo htmlspecialchars(getMarathiRegionName($conn, $user_location)); ?>" आपोआप निवडला गेला आहे (बदलू शकता)
                                <?php else: ?>
                                    कृपया आपला प्रदेश निवडा
                                <?php endif; ?>
                            </div>
                        <?php elseif($user_roll === 'district_user'): ?>
                            <!-- For district_user - readonly field showing auto-filled region -->
                            <div class="form-control shadow-sm" style="
                                border-color: #FFA500; 
                                font-family: 'Mukta', sans-serif; 
                                height: 50px;
                                background-color: #f8f9fa;
                                display: flex;
                                align-items: center;
                                padding: 0.375rem 0.75rem;
                            ">
                                <input type="hidden" name="region" value="<?php echo htmlspecialchars($user_region); ?>">
                                <span>
                                    <?php echo htmlspecialchars(getMarathiRegionName($conn, $user_region)); ?>
                                </span>
                            </div>
                            <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> आपल्या जिल्ह्याचा प्रदेश आपोआप भरला गेला आहे
                            </div>
                        <?php else: ?>
                            <!-- Default dropdown for other roles -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <?php if(!empty($allRegions)): ?>
                                    <?php foreach($allRegions as $region): ?>
                                        <option value="<?php echo htmlspecialchars($region['division']); ?>">
                                            <?php echo htmlspecialchars($region['marathiname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>कोणतेही प्रदेश उपलब्ध नाहीत</option>
                                <?php endif; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                            <i class="fas fa-map-marker-alt me-1"></i> जिल्हा निवडा *
                        </label>
                        <?php if($user_roll === 'admin'): ?>
                            <!-- For admin - dropdown (initially disabled) -->
                            <select class="form-select shadow-sm" name="district" id="districtSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;" disabled>
                                <option value="">-- प्रथम प्रदेश निवडा --</option>
                            </select>
                        <?php elseif($user_roll === 'division_head'): ?>
                            <!-- For division_head - dropdown shows districts from selected region from database -->
                            <select class="form-select shadow-sm" name="district" id="districtSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- जिल्हा निवडा --</option>
                                <?php if(!empty($divisionHeadDistricts)): ?>
                                    <?php foreach($divisionHeadDistricts as $district): ?>
                                        <option value="<?php echo htmlspecialchars($district['district']); ?>">
                                            <?php echo htmlspecialchars($district['dmarathi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>कोणतेही जिल्हे उपलब्ध नाहीत</option>
                                <?php endif; ?>
                            </select>
                        <?php elseif($user_roll === 'district_user'): ?>
                            <!-- For district_user - readonly field showing auto-filled district -->
                            <div class="form-control shadow-sm" style="
                                border-color: #FFA500; 
                                font-family: 'Mukta', sans-serif; 
                                height: 50px;
                                background-color: #f8f9fa;
                                display: flex;
                                align-items: center;
                                padding: 0.375rem 0.75rem;
                            ">
                                <input type="hidden" name="district" value="<?php echo htmlspecialchars($user_location); ?>">
                                <span>
                                    <?php echo htmlspecialchars($userDistrictMarathi); ?>
                                </span>
                            </div>
                            <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> आपला जिल्हा आपोआप भरला गेला आहे
                            </div>
                        <?php else: ?>
                            <!-- Default dropdown for other roles -->
                            <select class="form-select shadow-sm" name="district" id="districtSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;" disabled>
                                <option value="">-- प्रथम प्रदेश निवडा --</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Publisher Name -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-user-edit me-1"></i> प्रकाशकाचे नाव *
                    </label>
                    <input type="text" 
                        class="form-control shadow-sm" 
                        name="publisher_name" 
                        value="<?php echo htmlspecialchars($publisher_value); ?>"
                        placeholder="प्रकाशकाचे नाव लिहा..." 
                        required
                        style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px; font-size: 16px;">
                    <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-info-circle"></i> आपले नाव आपोआप भरले गेले आहे (बदलू शकता)
                    </div>
                </div>
                
                <!-- Category Selection - DYNAMIC FROM DATABASE -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-tags me-1"></i> वर्ग निवडा *
                    </label>
                    <select class="form-select shadow-sm" name="category" id="categorySelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                        <option value="">-- वर्ग निवडा --</option>
                        <?php if(!empty($allCategories)): ?>
                            <?php foreach($allCategories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['catagory']); ?>">
                                    <?php echo htmlspecialchars($category['marathi_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>कोणतेही वर्ग उपलब्ध नाहीत</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-info-circle"></i> कृपया बातमीचा योग्य वर्ग निवडा
                    </div>
                </div>
                
                <!-- Top News Checkbox -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="topnews" id="topNewsCheckbox" value="1" style="transform: scale(1.2);">
                        <label class="form-check-label fw-bold" for="topNewsCheckbox" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                            <i class="fas fa-star me-1"></i> मुख्य पृष्ठाची टॉप बातमी
                        </label>
                        <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                            <i class="fas fa-info-circle"></i> टिक करल्यास ही बातमी मुख्य पृष्ठावर टॉप बातमी म्हणून दिसेल
                        </div>
                    </div>
                </div>
                
                <!-- News Title -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-heading me-1"></i> बातमी शीर्षक *
                    </label>
                    <input type="text" 
                           class="form-control shadow-sm" 
                           name="news_title" 
                           placeholder="बातमीचे शीर्षक लिहा..." 
                           required
                           style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px; font-size: 16px;">
                </div>
                
                <!-- News Summary -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-align-left me-1"></i> बातमी सारांश *
                    </label>
                    <textarea class="form-control shadow-sm" 
                              name="news_summary" 
                              rows="3" 
                              placeholder="बातमीचा संक्षिप्त सारांश लिहा (जास्तीत जास्त ३०० अक्षरे)"
                              maxlength="1000"
                              required
                              style="border-color: #FFA500; font-family: 'Mukta', sans-serif; font-size: 16px;"></textarea>
                    <div class="form-text text-end" style="font-family: 'Mukta', sans-serif;">
                        <span id="charCount">०</span> / 1000 अक्षरे
                    </div>
                </div>
                
                <!-- Full News Content -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-newspaper me-1"></i> संपूर्ण बातमी *
                    </label>
                    <textarea class="form-control shadow-sm" 
                              name="full_news" 
                              rows="8" 
                              placeholder="संपूर्ण बातमी तपशीलात लिहा..."
                              required
                              style="border-color: #FFA500; font-family: 'Mukta', sans-serif; font-size: 16px;"></textarea>
                </div>
                
                <!-- File Uploads -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm" style="border-color: #FFA500;">
                            <div class="card-header" style="background-color: #FFF3E0;">
                                <label class="form-label fw-bold mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-image me-1"></i> मुख्य चित्र *
                                </label>
                            </div>
                            <div class="card-body">
                                <input type="file" 
                                       class="form-control" 
                                       name="cover_photo" 
                                       id="coverPhoto"
                                       accept="image/*"
                                       required
                                       style="border-color: #FFA500;">
                                <div class="form-text mt-2" style="font-family: 'Mukta', sans-serif;">
                                    प्रमुख प्रतिमा (JPG, PNG, WebP) - जास्तीत जास्त ५MB
                                </div>
                                <div class="mt-3 text-center">
                                    <img id="coverPreview" src="" alt="पूर्वावलोकन" class="img-fluid rounded" style="max-height: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm" style="border-color: #FFA500;">
                            <div class="card-header" style="background-color: #FFF3E0;">
                                <label class="form-label fw-bold mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-image me-1"></i> अतिरिक्त चित्र (ऐच्छिक)
                                </label>
                            </div>
                            <div class="card-body">
                                <input type="file" 
                                       class="form-control" 
                                       name="news_image" 
                                       id="newsImage"
                                       accept="image/*"
                                       style="border-color: #FFA500;">
                                <div class="form-text mt-2" style="font-family: 'Mukta', sans-serif;">
                                    अतिरिक्त चित्र (JPG, PNG, WebP) - जास्तीत जास्त ५MB
                                </div>
                                <div class="mt-3">
                                    <div id="imagePreview" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Publish Date and Time -->
                <div class="mb-4">
                    <div class="card shadow-sm" style="border-color: #FFA500; max-width: 400px;">
                        <div class="card-header" style="background-color: #FFF3E0;">
                            <label class="form-label fw-bold mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-calendar-alt me-1"></i> प्रकाशन तारीख आणि वेळ *
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="datetime-local" 
                                       class="form-control" 
                                       name="publish_date" 
                                       id="publishDateTime"
                                       value="<?php echo $currentDateTime; ?>"
                                       required
                                       style="border-color: #FFA500; height: 50px; font-family: 'Mukta', sans-serif;">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="setCurrentDateTimeBtn"
                                        style="border-color: #FFA500; color: #FF6600; height: 50px; font-family: 'Mukta', sans-serif;">
                                    <i class="fas fa-clock"></i> आत्ताची वेळ
                                </button>
                            </div>
                            <div class="form-text mt-2" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> तारीख आणि वेळ आपोआप आजच्या तारखेसह भरली गेली आहे (बदलू शकता)
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mt-5">
                    <button type="submit" 
                            class="btn btn-lg shadow mb-2 mb-md-0" 
                            style="background: linear-gradient(135deg, #FF6600, #FF8C00); color: white; padding: 12px 20px; font-family: 'Khand', sans-serif; font-weight: bold; width: 100%; max-width: 300px;">
                        <i class="fas fa-paper-plane me-2"></i> बातमी प्रकाशित करा
                    </button>
                    <button type="reset" 
                            class="btn btn-lg btn-outline-secondary shadow" 
                            style="border-color: #FF6600; color: #FF6600; padding: 12px 20px; font-family: 'Khand', sans-serif; font-weight: bold; width: 100%; max-width: 300px;">
                        <i class="fas fa-redo me-2"></i> फॉर्म रीसेट करा
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-footer text-center py-3" style="background-color: #FFF8F0; border-top: 2px dashed #FFA500;">
            <small style="font-family: 'Mukta', sans-serif;">
                <i class="fas fa-info-circle me-1" style="color: #FF6600;"></i>
                <span class="fw-bold" style="color: #FF6600;">*</span> चिन्हांकित सर्व फील्ड भरणे अनिवार्य आहे
                <?php if($user_roll === 'division_head'): ?>
                    <br><span style="color: #FF6600;">
                        <i class="fas fa-user-shield"></i> प्रदेश प्रमुख: 
                        <?php if(!empty($user_location)): ?>
                            आपला प्रदेश "<?php echo htmlspecialchars(getMarathiRegionName($conn, $user_location)); ?>" आपोआप निवडला गेला आहे (बदलू शकता), जिल्हा निवडू शकता
                        <?php else: ?>
                            कृपया आपला प्रदेश निवडा, त्यानंतर जिल्हा निवडू शकता
                        <?php endif; ?>
                    </span>
                <?php elseif($user_roll === 'district_user'): ?>
                    <br><span style="color: #FF6600;">
                        <i class="fas fa-map-marker-alt"></i> जिल्हा वापरकर्ता: आपले प्रदेश आणि जिल्हा आपोआप भरले गेले आहेत
                    </span>
                <?php elseif($user_roll === 'admin'): ?>
                    <br><span style="color: #FF6600;">
                        <i class="fas fa-crown"></i> प्रशासक: सर्व फील्ड संपादन करण्यायोग्य आहेत
                    </span>
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>

<!-- Include jQuery (required for Toastr) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Toastr for notifications -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Include Fonts for Marathi -->
<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        background-color: #FFF8F0;
        font-family: 'Mukta', sans-serif;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #FF6600 !important;
        box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25) !important;
    }
    
    .btn {
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3) !important;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    input::placeholder, textarea::placeholder {
        font-family: 'Mukta', sans-serif;
        color: #999 !important;
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
    
    /* Style for datetime-local input */
    input[type="datetime-local"] {
        font-family: 'Mukta', sans-serif !important;
    }
    
    /* Responsive button styles */
    @media (max-width: 768px) {
        .btn-lg {
            padding: 10px 15px !important;
            font-size: 16px !important;
        }
        
        .card-body {
            padding: 1rem !important;
        }
        
        .container {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        h2 {
            font-size: 1.5rem !important;
        }
        
        /* Adjust datetime input for mobile */
        input[type="datetime-local"] {
            font-size: 14px !important;
        }
        
        #setCurrentDateTimeBtn {
            font-size: 14px !important;
            padding: 0.375rem 0.5rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .btn-lg {
            padding: 8px 12px !important;
            font-size: 15px !important;
        }
        
        h2 {
            font-size: 1.3rem !important;
        }
        
        h4 {
            font-size: 1.2rem !important;
        }
        
        /* Adjust datetime input for very small screens */
        input[type="datetime-local"] {
            font-size: 13px !important;
        }
        
        #setCurrentDateTimeBtn {
            font-size: 13px !important;
            padding: 0.25rem 0.4rem !important;
        }
        
        .input-group {
            flex-direction: column;
        }
        
        .input-group input,
        .input-group button {
            width: 100%;
            margin-bottom: 5px;
        }
         .input-group {
            flex-direction: column;
            align-items: stretch;
        }

        .input-group input,
        .input-group button {
            width: 100% !important;
            margin-left: 0 !important;
            border-radius: 0.375rem !important;
            height: 50px !important;
            font-size: 16px !important;      /* Prevents auto-zoom on focus */
            box-sizing: border-box;
        }

        .input-group button {
            margin-top: 8px;
        }

        /* Ensure the date text fits */
        input[type="datetime-local"] {
            padding: 0 0.75rem !important;    /* Enough horizontal padding */
            text-overflow: clip;               /* Shows full text instead of ellipsis */
        }   
    }
    /* For extremely narrow screens (<360px) */
        @media (max-width: 360px) {
            input[type="datetime-local"] {
                font-size: 14px !important;
            }
        }
    /* Toastr customization */
    .toast {
        font-family: 'Mukta', sans-serif !important;
        font-size: 16px !important;
        background-color: white !important;
        color: #333 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    
    .toast-success {
        border-left: 5px solid #28a745 !important;
    }
    
    .toast-error {
        border-left: 5px solid #dc3545 !important;
    }
    
    .toast-info {
        border-left: 5px solid #17a2b8 !important;
    }
    
    .toast-title {
        font-weight: bold !important;
        margin-bottom: 5px !important;
    }
    
    .toast-message {
        font-size: 15px !important;
    }
    
    .toast-close-button {
        font-size: 18px !important;
        font-weight: bold !important;
    }
    
    /* Role-based styling */
    .readonly-field {
        background-color: #f8f9fa !important;
        cursor: not-allowed !important;
    }
    
    /* Top News checkbox styling */
    .form-check-input:checked {
        background-color: #FF6600;
        border-color: #FF6600;
    }
    
    .form-check-input:focus {
        border-color: #FFA500;
        box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25);
    }
    
    /* Set current datetime button styling */
    #setCurrentDateTimeBtn:hover {
        background-color: #FF6600 !important;
        color: white !important;
        border-color: #FF6600 !important;
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

    // Region-District mapping from database (dynamic)
    const regionDistrictMapping = <?php echo $regionDistrictJSON; ?>;
    
    // Character counter for summary
    document.addEventListener('DOMContentLoaded', function() {
        const summaryTextarea = document.querySelector('textarea[name="news_summary"]');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            const count = summaryTextarea.value.length;
            // Convert to Marathi numerals
            const marathiNumerals = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
            let marathiCount = '';
            count.toString().split('').forEach(digit => {
                marathiCount += marathiNumerals[parseInt(digit)];
            });
            charCount.textContent = marathiCount;
        }
        
        if (summaryTextarea) {
            summaryTextarea.addEventListener('input', updateCharCount);
            updateCharCount();
        }
        
        // Cover photo preview
        const coverPhotoInput = document.getElementById('coverPhoto');
        const coverPreview = document.getElementById('coverPreview');
        
        if (coverPhotoInput && coverPreview) {
            coverPhotoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        coverPreview.src = e.target.result;
                        coverPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Single image preview for additional image
        const newsImageInput = document.getElementById('newsImage');
        const imagePreviewDiv = document.getElementById('imagePreview');
        
        if (newsImageInput && imagePreviewDiv) {
            newsImageInput.addEventListener('change', function(e) {
                imagePreviewDiv.innerHTML = '';
                const file = e.target.files[0];
                
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail';
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.margin = '5px';
                        imagePreviewDiv.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Region-based district selection using dynamic database mapping
        const regionSelect = document.getElementById('regionSelect');
        const districtSelect = document.getElementById('districtSelect');
        
        function populateDistricts(selectedRegion) {
            if (districtSelect) {
                districtSelect.innerHTML = '<option value="">-- जिल्हा निवडा --</option>';
                
                if (selectedRegion && regionDistrictMapping && regionDistrictMapping[selectedRegion] && regionDistrictMapping[selectedRegion].length > 0) {
                    districtSelect.disabled = false;
                    regionDistrictMapping[selectedRegion].forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.value;
                        option.textContent = district.text;
                        districtSelect.appendChild(option);
                    });
                } else {
                    districtSelect.disabled = true;
                    districtSelect.innerHTML = '<option value="">-- प्रथम प्रदेश निवडा --</option>';
                }
            }
        }
        
        // Only add event listener for admin (division_head doesn't need dynamic update as districts are pre-loaded)
        if (regionSelect && districtSelect && '<?php echo $user_roll; ?>' === 'admin') {
            regionSelect.addEventListener('change', function() {
                const selectedRegion = this.value;
                populateDistricts(selectedRegion);
            });
            
            // Initialize districts if region is already selected
            const selectedRegion = regionSelect.value;
            if (selectedRegion) {
                populateDistricts(selectedRegion);
            }
        }
        
        // For division_head, districts are already pre-loaded in PHP, no JS needed
        // For district_user, both fields are readonly
        
        // Form validation
        const form = document.getElementById('newsForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Check file size (5MB limit)
                const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                
                if (coverPhotoInput && coverPhotoInput.files[0] && coverPhotoInput.files[0].size > maxSize) {
                    toastr.error('मुख्य चित्र ५MB पेक्षा मोठे नसावे!');
                    valid = false;
                }
                
                if (newsImageInput && newsImageInput.files[0] && newsImageInput.files[0].size > maxSize) {
                    toastr.error('अतिरिक्त चित्र ५MB पेक्षा मोठे नसावे!');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
        
        // Set current datetime button functionality
        const setCurrentDateTimeBtn = document.getElementById('setCurrentDateTimeBtn');
        const publishDateTimeInput = document.getElementById('publishDateTime');
        
        if (setCurrentDateTimeBtn && publishDateTimeInput) {
            setCurrentDateTimeBtn.addEventListener('click', function() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                publishDateTimeInput.value = currentDateTime;
                toastr.success('आत्ताची तारीख आणि वेळ सेट केली गेली आहे!');
            });
        }
        
        // Check for URL parameters on page load
        function checkURLParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('success') === '1') {
                toastr.success('बातमी यशस्वीरित्या सबमिट केली गेली आहे!');
                
                setTimeout(function() {
                    if (form) form.reset();
                    
                    const userRoll = '<?php echo $user_roll; ?>';
                    
                    if (userRoll === 'admin') {
                        if (regionSelect) {
                            regionSelect.value = '';
                        }
                        if (districtSelect) {
                            districtSelect.disabled = true;
                            districtSelect.innerHTML = '<option value="">-- प्रथम प्रदेश निवडा --</option>';
                        }
                    } else if (userRoll === 'division_head') {
                        if (regionSelect) {
                            regionSelect.value = '<?php echo htmlspecialchars($user_location); ?>';
                        }
                    }
                    
                    const publisherInput = document.querySelector('input[name="publisher_name"]');
                    if (publisherInput && !<?php echo $publisher_editable ? 'false' : 'true'; ?>) {
                        publisherInput.value = '<?php echo htmlspecialchars($publisher_value); ?>';
                    }
                    
                    const topNewsCheckbox = document.getElementById('topNewsCheckbox');
                    if (topNewsCheckbox) topNewsCheckbox.checked = false;
                    
                    if (publishDateTimeInput) {
                        publishDateTimeInput.value = '<?php echo $currentDateTime; ?>';
                    }
                    
                    if (coverPreview) coverPreview.style.display = 'none';
                    if (imagePreviewDiv) imagePreviewDiv.innerHTML = '';
                    if (summaryTextarea) updateCharCount();
                    
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            }
            
            if (urlParams.get('error')) {
                const errorMsg = urlParams.get('error');
                toastr.error(decodeURIComponent(errorMsg));
                
                setTimeout(function() {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            }
        }
        
        checkURLParameters();
    });
</script>

<?php
include 'components/footer.php';
?>