<?php
session_start();

// Check if user is logged in ...
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
include 'components/db_config.php';

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Get user info for display
$user_name = $_SESSION['name'] ?? 'User';
$user_roll = $_SESSION['roll'] ?? '';
$user_location = $_SESSION['location'] ?? '';
$username = $_SESSION['username'] ?? '';

// DEBUG: Check what's in session
// echo "Debug: user_roll = $user_roll, user_location = $user_location<br>";

// For division_head, location might be the region name itself, not a district
// Let's check if location is a region or district
function getRegionFromLocation($location) {
    // List of all regions
    $regions = ['kokan', 'pune', 'sambhajinagar', 'nashik', 'amaravati', 'nagpur'];
    
    // If location is directly a region, return it
    if (in_array($location, $regions)) {
        return $location;
    }
    
    // Otherwise, try to map district to region
    $districtToRegion = [
        // Kokan region districts
        'palghar' => 'kokan',
        'thane' => 'kokan',
        'mumbai_city' => 'kokan',
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
    
    return isset($districtToRegion[$location]) ? $districtToRegion[$location] : '';
}

// Determine user's region
$user_region = '';
if (!empty($user_location)) {
    $user_region = getRegionFromLocation($user_location);
}

// DEBUG: Check region determination
// echo "Debug: Determined user_region = $user_region<br>";

// Determine if publisher name should be editable
$publisher_editable = ($user_roll === 'admin');
$publisher_value = $user_name;

// Get Marathi district names for display
function getMarathiDistrictName($districtValue) {
    $districtMap = [
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
    
    return isset($districtMap[$districtValue]) ? $districtMap[$districtValue] : $districtValue;
}

// Get Marathi region names for display
function getMarathiRegionName($regionValue) {
    $regionMap = [
        'kokan' => 'कोकण',
        'pune' => 'पुणे',
        'sambhajinagar' => 'संभाजीनगर',
        'nashik' => 'नाशिक',
        'amaravati' => 'अमरावती',
        'nagpur' => 'नागपूर'
    ];
    
    return isset($regionMap[$regionValue]) ? $regionMap[$regionValue] : $regionValue;
}

// Get current date and time in the format for datetime-local input
// Using simple PHP approach that should work
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
                            <!-- For admin - editable dropdown -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <option value="kokan">कोकण</option>
                                <option value="pune">पुणे</option>
                                <option value="sambhajinagar">संभाजीनगर</option>
                                <option value="nashik">नाशिक</option>
                                <option value="amaravati">अमरावती</option>
                                <option value="nagpur">नागपूर</option>
                            </select>
                        <?php elseif($user_roll === 'division_head'): ?>
                            <!-- For division_head - auto-filled region from session, but editable -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <?php 
                                $regions = ['kokan', 'pune', 'sambhajinagar', 'nashik', 'amaravati', 'nagpur'];
                                foreach($regions as $region): ?>
                                    <option value="<?php echo $region; ?>" <?php echo ($user_location === $region) ? 'selected' : ''; ?>>
                                        <?php echo getMarathiRegionName($region); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> 
                                <?php if(!empty($user_location)): ?>
                                    आपला प्रदेश "<?php echo getMarathiRegionName($user_location); ?>" आपोआप निवडला गेला आहे (बदलू शकता)
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
                                <input type="hidden" name="region" value="<?php echo $user_region; ?>">
                                <span>
                                    <?php echo getMarathiRegionName($user_region); ?>
                                </span>
                            </div>
                            <div class="form-text" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-info-circle"></i> आपल्या जिल्ह्याचा प्रदेश आपोआप भरला गेला आहे
                            </div>
                        <?php else: ?>
                            <!-- Default dropdown for other roles -->
                            <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- प्रदेश निवडा --</option>
                                <option value="kokan">कोकण</option>
                                <option value="pune">पुणे</option>
                                <option value="sambhajinagar">संभाजीनगर</option>
                                <option value="nashik">नाशिक</option>
                                <option value="amaravati">अमरावती</option>
                                <option value="nagpur">नागपूर</option>
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
                            <!-- For division_head - dropdown shows districts from selected region -->
                            <select class="form-select shadow-sm" name="district" id="districtSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                                <option value="">-- जिल्हा निवडा --</option>
                                <?php 
                                // Determine which region to show districts for
                                $region_for_districts = $user_location; // For division_head, location is region
                                $districts_for_region = [];
                                
                                switch($region_for_districts) {
                                    case 'kokan':
                                        $districts_for_region = ['palghar', 'thane', 'mumbai_city', 'mumbai_suburban', 'raigad', 'ratnagiri', 'sindhudurg'];
                                        break;
                                    case 'pune':
                                        $districts_for_region = ['pune', 'satara', 'kolhapur', 'sangli', 'solapur'];
                                        break;
                                    case 'sambhajinagar':
                                        $districts_for_region = ['chhatrapati_sambhajinagar', 'beed', 'jalna', 'parbhani', 'hingoli', 'nanded', 'latur', 'dharashiv'];
                                        break;
                                    case 'nashik':
                                        $districts_for_region = ['nashik', 'dhule', 'nandurbar', 'ahmednagar', 'jalgaon', 'ahilyanagar'];
                                        break;
                                    case 'amaravati':
                                        $districts_for_region = ['amaravati', 'akola', 'buldhana', 'washim', 'yavatmal'];
                                        break;
                                    case 'nagpur':
                                        $districts_for_region = ['nagpur', 'wardha', 'bhandara', 'gondia', 'chandrapur', 'gadchiroli'];
                                        break;
                                    default:
                                        $districts_for_region = [];
                                }
                                
                                foreach($districts_for_region as $district): ?>
                                    <option value="<?php echo $district; ?>"><?php echo getMarathiDistrictName($district); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif($user_roll === 'district_user'): ?>
                            <!-- For district_user - readonly field showing auto-filled district ----------->
                            <div class="form-control shadow-sm" style="
                                border-color: #FFA500; 
                                font-family: 'Mukta', sans-serif; 
                                height: 50px;
                                background-color: #f8f9fa;
                                display: flex;
                                align-items: center;
                                padding: 0.375rem 0.75rem;
                            ">
                                <input type="hidden" name="district" value="<?php echo $user_location; ?>">
                                <span>
                                    <?php echo getMarathiDistrictName($user_location); ?>
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
                
                <!-- Category Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-tags me-1"></i> वर्ग निवडा *
                    </label>
                    <select class="form-select shadow-sm" name="category" id="categorySelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                        <option value="">-- वर्ग निवडा --</option>
                        <option value="amrut_events">अमृत घडामोडी</option>
                        <option value="beneficiary_story">लाभार्थी स्टोरी</option>
                        <option value="blog">ब्लॉग</option>
                        <option value="today_special">दिनविशेष</option>
                        <option value="successful_entrepreneur">यशस्वी उद्योजक</option>
                        <option value="words_amrut">शब्दांमृत</option>
                        <option value="smart_farmer">स्मार्ट शेतकरी</option>
                        <option value="capable_student">सक्षम विद्यार्थी</option>
                        <option value="spirituality">अध्यात्म</option>
                        <option value="social_situation">सामाजिक परिवर्तक</option>
                        <option value="women_power">स्त्रीशक्ती</option>
                        <option value="tourism">पर्यटन</option>
                        <option value="news">वार्ता</option>
                        <option value="tourism">पर्यटन</option>
                    </select>
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
                            आपला प्रदेश "<?php echo getMarathiRegionName($user_location); ?>" आपोआप निवडला गेला आहे (बदलू शकता), जिल्हा निवडू शकता
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
        
        summaryTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
        
        // Cover photo preview
        const coverPhotoInput = document.getElementById('coverPhoto');
        const coverPreview = document.getElementById('coverPreview');
        
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
        
        // Single image preview for additional image
        const newsImageInput = document.getElementById('newsImage');
        const imagePreviewDiv = document.getElementById('imagePreview');
        
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
        
        // Region-based district selection
        const regionSelect = document.getElementById('regionSelect');
        const districtSelect = document.getElementById('districtSelect');
        
        // District data for each region (English values for database, Marathi display)
        const districtData = {
            'kokan': [
                {value: 'palghar', text: 'पालघर'},
                {value: 'thane', text: 'ठाणे'},
                {value: 'mumbai_city', text: 'मुंबई शहर'},
                {value: 'mumbai_suburban', text: 'मुंबई उपनगर'},
                {value: 'raigad', text: 'रायगड'},
                {value: 'ratnagiri', text: 'रत्नागिरी'},
                {value: 'sindhudurg', text: 'सिंधुदुर्ग'}
            ],
            'pune': [
                {value: 'pune', text: 'पुणे'},
                {value: 'satara', text: 'सातारा'},
                {value: 'kolhapur', text: 'कोल्हापूर'},
                {value: 'sangli', text: 'सांगली'},
                {value: 'solapur', text: 'सोलापूर'}
            ],
            'sambhajinagar': [
                {value: 'chhatrapati_sambhajinagar', text: 'छत्रपती संभाजीनगर'},
                {value: 'beed', text: 'बीड'},
                {value: 'jalna', text: 'जालना'},
                {value: 'parbhani', text: 'परभणी'},
                {value: 'hingoli', text: 'हिंगोली'},
                {value: 'nanded', text: 'नांदेड'},
                {value: 'latur', text: 'लातूर'},
                {value: 'dharashiv', text: 'धाराशिव'}
            ],
            'nashik': [
                {value: 'nashik', text: 'नाशिक'},
                {value: 'dhule', text: 'धुळे'},
                {value: 'nandurbar', text: 'नंदुरबार'},
                {value: 'ahmednagar', text: 'अहमदनगर'},
                {value: 'jalgaon', text: 'जळगाव'},
                {value: 'ahilyanagar', text: 'अहिल्यानगर'}
            ],
            'amaravati': [
                {value: 'amaravati', text: 'अमरावती'},
                {value: 'akola', text: 'अकोला'},
                {value: 'buldhana', text: 'बुलढाणा'},
                {value: 'washim', text: 'वाशीम'},
                {value: 'yavatmal', text: 'यवतमाळ'}
            ],
            'nagpur': [
                {value: 'nagpur', text: 'नागपूर'},
                {value: 'wardha', text: 'वर्धा'},
                {value: 'bhandara', text: 'भंडारा'},
                {value: 'gondia', text: 'गोंदिया'},
                {value: 'chandrapur', text: 'चंद्रपूर'},
                {value: 'gadchiroli', text: 'गडचिरोली'}
            ]
        };
        
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
        
        function populateDistricts(selectedRegion) {
            if (districtSelect) {
                districtSelect.innerHTML = '<option value="">-- जिल्हा निवडा --</option>';
                
                if (selectedRegion && districtData[selectedRegion]) {
                    districtSelect.disabled = false;
                    districtData[selectedRegion].forEach(district => {
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
        
        // Form validation
        const form = document.getElementById('newsForm');
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Check file size (5MB limit)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            
            if (coverPhotoInput.files[0] && coverPhotoInput.files[0].size > maxSize) {
                toastr.error('मुख्य चित्र ५MB पेक्षा मोठे नसावे!');
                valid = false;
            }
            
            if (newsImageInput.files[0] && newsImageInput.files[0].size > maxSize) {
                toastr.error('अतिरिक्त चित्र ५MB पेक्षा मोठे नसावे!');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
        
        // Set current datetime button functionality - SIMPLIFIED
        const setCurrentDateTimeBtn = document.getElementById('setCurrentDateTimeBtn');
        const publishDateTimeInput = document.getElementById('publishDateTime');
        
        if (setCurrentDateTimeBtn && publishDateTimeInput) {
            setCurrentDateTimeBtn.addEventListener('click', function() {
                // SIMPLE approach - use the browser's local time directly
                const now = new Date();
                
                // Get local date components
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                
                // Format: YYYY-MM-DDTHH:MM
                const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                
                // Set the value
                publishDateTimeInput.value = currentDateTime;
                
                // Debug log
                console.log('Current time set to:', currentDateTime);
                console.log('Browser local time:', now.toString());
                
                // Show success message
                toastr.success('आत्ताची तारीख आणि वेळ सेट केली गेली आहे!');
            });
            
            // Also set on page load if needed
            // Note: We're already setting it via PHP, so this is just for the button
        }
        
        // Check for URL parameters on page load
        function checkURLParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Show success message
            if (urlParams.get('success') === '1') {
                toastr.success('बातमी यशस्वीरित्या सबमिट केली गेली आहे!');
                
                // Clear form after success
                setTimeout(function() {
                    form.reset();
                    
                    // Reset region and district based on user role
                    const userRoll = '<?php echo $user_roll; ?>';
                    
                    if (userRoll === 'admin') {
                        // For admin, reset both dropdowns
                        if (regionSelect) {
                            regionSelect.value = '';
                        }
                        if (districtSelect) {
                            districtSelect.disabled = true;
                            districtSelect.innerHTML = '<option value="">-- प्रथम प्रदेश निवडा --</option>';
                        }
                    } else if (userRoll === 'division_head') {
                        // For division_head, reset region to session location
                        if (regionSelect) {
                            regionSelect.value = '<?php echo $user_location; ?>';
                            // Districts will be auto-populated by PHP on page reload
                        }
                    }
                    // For district_user, no reset needed as fields are readonly
                    
                    // Set publisher name back to user's name
                    const publisherInput = document.querySelector('input[name="publisher_name"]');
                    if (publisherInput && !<?php echo $publisher_editable ? 'false' : 'true'; ?>) {
                        publisherInput.value = '<?php echo htmlspecialchars($publisher_value); ?>';
                    }
                    
                    // Uncheck topnews checkbox
                    document.getElementById('topNewsCheckbox').checked = false;
                    
                    // Reset datetime to current date and time using PHP value
                    if (publishDateTimeInput) {
                        // This will use the PHP-generated datetime which is in IST
                        // The datetime-local input will interpret it as local time
                        publishDateTimeInput.value = '<?php echo $currentDateTime; ?>';
                    }
                    
                    coverPreview.style.display = 'none';
                    imagePreviewDiv.innerHTML = '';
                    updateCharCount();
                    
                    // Clear URL parameters
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            }
            
            // Show error message
            if (urlParams.get('error')) {
                const errorMsg = urlParams.get('error');
                toastr.error(decodeURIComponent(errorMsg));
                
                // Clear URL parameters after showing error
                setTimeout(function() {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 100);
            }
        }
        
        // Call the function when page loads
        checkURLParameters();
    });
</script>

<?php
include 'components/footer.php';
?>