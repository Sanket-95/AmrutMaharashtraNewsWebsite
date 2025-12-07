<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/db_config.php';

// Get user info for display
$user_name = $_SESSION['name'] ?? 'User';
$user_roll = $_SESSION['roll'] ?? '';
?>

<div class="container mt-4 mb-5">
    <!-- Top Bar with Logout Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">नवीन बातमी प्रकाशित करा</h2>
        
        <!-- User Info and Logout Button -->
        <div class="d-flex align-items-center gap-3">
            <!-- User Info -->
            <div class="user-info-box" style="
                background: linear-gradient(135deg, #FFF3E0, #FFE8D6);
                border: 2px solid #FFD8B5;
                border-radius: 10px;
                padding: 8px 15px;
                font-family: 'Mukta', sans-serif;
            ">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle me-2" style="color: #FF6600; font-size: 20px;"></i>
                    <div>
                        <div style="font-weight: 600; color: #FF6600; font-size: 16px;">
                            <?php echo htmlspecialchars($user_name); ?>
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            <i class="fas fa-user-tag me-1"></i>
                            <?php echo htmlspecialchars($user_roll); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Logout Button -->
            <a href="backend/logout.php" class="btn logout-btn-top" style="
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: white;
                border: none;
                padding: 8px 20px;
                border-radius: 8px;
                font-family: 'Mukta', sans-serif;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            ">
                <i class="fas fa-sign-out-alt"></i>
                <span>लॉगआउट</span>
            </a>
        </div>
    </div>
    
    <!-- Logout Button Hover Effect -->
    <style>
        .logout-btn-top:hover {
            background: linear-gradient(135deg, #c82333, #dc3545) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3) !important;
            color: white !important;
        }
        
        .user-info-box {
            transition: all 0.3s ease;
        }
        
        .user-info-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 102, 0, 0.1);
        }
    </style>
    
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
                        <select class="form-select shadow-sm" name="region" id="regionSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                            <option value="">-- प्रदेश निवडा --</option>
                            <option value="kokan">कोकण</option>
                            <option value="pune">पुणे</option>
                            <option value="sambhajinagar">संभाजीनगर</option>
                            <option value="nashik">नाशिक</option>
                            <option value="amaravati">अमरावती</option>
                            <option value="nagpur">नागपूर</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                            <i class="fas fa-map-marker-alt me-1"></i> जिल्हा निवडा *
                        </label>
                        <select class="form-select shadow-sm" name="district" id="districtSelect" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;" disabled>
                            <option value="">-- प्रथम प्रदेश निवडा --</option>
                        </select>
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
                           placeholder="प्रकाशकाचे नाव लिहा..." 
                           required
                           style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px; font-size: 16px;">
                </div>
                
                <!-- Category Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                        <i class="fas fa-tags me-1"></i> वर्ग निवडा *
                    </label>
                    <select class="form-select shadow-sm" name="category" required style="border-color: #FFA500; font-family: 'Mukta', sans-serif; height: 50px;">
                        <option value="">-- वर्ग निवडा --</option>
                        <option value="home">मुख्यपृष्ठ</option>
                        <option value="amrut_events">अमृत घडामोडी</option>
                        <option value="beneficiary_story">लाभार्थी स्टोरी</option>
                        <option value="today_special">दिनविशेष</option>
                        <option value="successful_entrepreneur">यशस्वी उद्योजक</option>
                        <option value="words_amrut">शब्दांमृत</option>
                        <option value="smart_farmer">स्मार्ट शेतकरी</option>
                        <option value="capable_student">सक्षम विद्यार्थी</option>
                        <option value="spirituality">अध्यात्म</option>
                        <option value="social_situation">सामाजिक परिवर्तक</option>
                        <option value="women_power">स्त्रीशक्ती</option>
                        <option value="tourism">पर्यटन</option>
                    </select>
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
                
                <!-- Publish Date -->
                <div class="mb-4">
                    <div class="card shadow-sm" style="border-color: #FFA500; max-width: 400px;">
                        <div class="card-header" style="background-color: #FFF3E0;">
                            <label class="form-label fw-bold mb-0" style="color: #FF6600; font-family: 'Mukta', sans-serif;">
                                <i class="fas fa-calendar-alt me-1"></i> प्रकाशन तारीख *
                            </label>
                        </div>
                        <div class="card-body">
                            <input type="date" 
                                   class="form-control" 
                                   name="publish_date" 
                                   required
                                   style="border-color: #FFA500; height: 50px; font-family: 'Mukta', sans-serif;">
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
        
        /* Responsive top bar */
        .d-flex.justify-content-between {
            flex-direction: column !important;
            gap: 15px !important;
        }
        
        .d-flex.justify-content-between > * {
            width: 100% !important;
            text-align: center !important;
        }
        
        .user-info-box, .logout-btn-top {
            width: 100% !important;
            justify-content: center !important;
        }
    }
    
    @media (max-width: 576px) {
        .btn-lg {
            padding: 8px 12px !important;
            font-size: 15px !important;
        }
        
        h2 {
            font-size: 1.5rem !important;
        }
        
        h4 {
            font-size: 1.2rem !important;
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
        
        regionSelect.addEventListener('change', function() {
            const selectedRegion = this.value;
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
        });
        
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
        
        // Check for URL parameters on page load
        function checkURLParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Show success message
            if (urlParams.get('success') === '1') {
                toastr.success('बातमी यशस्वीरित्या सबमिट केली गेली आहे!');
                
                // Clear form after success
                setTimeout(function() {
                    form.reset();
                    districtSelect.disabled = true;
                    districtSelect.innerHTML = '<option value="">-- प्रथम प्रदेश निवडा --</option>';
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