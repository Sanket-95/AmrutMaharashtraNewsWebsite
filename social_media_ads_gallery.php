<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only Super Admin can access this page
if ($_SESSION['roll'] !== 'Super Admin') {
    header('Location: login.php');
    exit();
}

include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 12;
$offset = ($page - 1) * $records_per_page;

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'active';
$filter_district = isset($_GET['district']) ? $_GET['district'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Build query conditions - Only Ad Type 1 (Social Media)
$where_conditions = ["ad_type = 1"]; // Only social media ads

// Status filter (active/past)
if ($filter_status == 'active') {
    $where_conditions[] = "end_date >= CURDATE() AND is_active = 1";
    // For active ads, apply date filter only if both dates are provided
    if (!empty($from_date) && !empty($to_date)) {
        $where_conditions[] = "(start_date <= '$to_date' AND end_date >= '$from_date')";
    } elseif (!empty($from_date)) {
        $where_conditions[] = "end_date >= '$from_date'";
    } elseif (!empty($to_date)) {
        $where_conditions[] = "start_date <= '$to_date'";
    }
} elseif ($filter_status == 'past') {
    $where_conditions[] = "end_date < CURDATE()";
    // For expired ads, NO date filter - show all expired ads
    $from_date = '';
    $to_date = '';
}

// District filter
if (!empty($filter_district)) {
    $where_conditions[] = "district = '" . mysqli_real_escape_string($conn, $filter_district) . "'";
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// Get total records count for pagination
$count_query = "SELECT COUNT(*) as total FROM ads_management $where_sql";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch advertisements with pagination
$query = "SELECT * FROM ads_management $where_sql ORDER BY created_at DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);

// Fetch districts for filter
$district_query = "SELECT DISTINCT district FROM ads_management WHERE ad_type = 1 AND district IS NOT NULL AND district != '' ORDER BY district";
$district_result = mysqli_query($conn, $district_query);
$districts = [];
if ($district_result) {
    while ($row = mysqli_fetch_assoc($district_result)) {
        $districts[] = $row['district'];
    }
}

// Image paths
$social_media_path = 'components/primary_advertised_social_media/';
$base_path = dirname(__FILE__) . '/';

// Collect valid ads with existing images
$valid_ads = [];
while ($row = mysqli_fetch_assoc($result)) {
    $image_filename = $row['social_media_image'];
    $image_path = $social_media_path . $image_filename;
    $full_image_path = $base_path . $image_path;
    
    // Check if image exists
    if (!empty($image_filename) && file_exists($full_image_path)) {
        $row['image_path'] = $image_path;
        $row['full_image_path'] = $full_image_path;
        $valid_ads[] = $row;
    }
}

// Update total valid records count
$valid_records_count = count($valid_ads);
?>

<style>
    .gallery-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .filter-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .filter-title {
        color: #FF6600;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 8px 20px;
        border-radius: 25px;
        border: 2px solid #FF6600;
        background: white;
        color: #FF6600;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .filter-btn.active,
    .filter-btn:hover {
        background: #FF6600;
        color: white;
    }
    
    .ad-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 25px;
        break-inside: avoid;
    }
    
    .ad-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .ad-image-container {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        background: #f8f9fa;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .ad-image {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    
    .ad-image:hover {
        transform: scale(1.05);
    }
    
    .ad-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .ad-image-container:hover .ad-overlay {
        opacity: 1;
    }
    
    .overlay-btn {
        background: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        color: #333;
    }
    
    .overlay-btn:hover {
        background: #FF6600;
        color: white;
        transform: scale(1.05);
    }
    
    .ad-info {
        padding: 15px;
    }
    
    .ad-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .ad-client {
        color: #FF6600;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .ad-details {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
    }
    
    .ad-details i {
        width: 20px;
        color: #FF6600;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-expired {
        background: #f8d7da;
        color: #721c24;
    }
    
    .masonry-grid {
        column-count: 4;
        column-gap: 25px;
    }
    
    .image-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        z-index: 9999;
        cursor: pointer;
    }
    
    .image-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-image {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }
    
    .modal-close {
        position: absolute;
        top: 20px;
        right: 40px;
        color: white;
        font-size: 40px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .modal-close:hover {
        color: #FF6600;
    }
    
    .modal-download {
        position: absolute;
        bottom: 30px;
        right: 30px;
        background: #FF6600;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-download:hover {
        background: #e55a00;
        transform: scale(1.05);
    }
    
    .info-note {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .date-range-info {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 8px 12px;
        margin-bottom: 15px;
        border-radius: 6px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .pagination-container {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }
    
    .pagination {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-link {
        padding: 8px 16px;
        border: 2px solid #FFD8B0;
        border-radius: 8px;
        color: #FF6600;
        text-decoration: none;
        transition: all 0.3s ease;
        font-family: 'Mukta', sans-serif;
    }
    
    .page-link:hover {
        background: #FF6600;
        color: white;
        border-color: #FF6600;
        transform: translateY(-2px);
    }
    
    .page-item.active .page-link {
        background: #FF6600;
        color: white;
        border-color: #FF6600;
    }
    
    .page-item.disabled .page-link {
        color: #ccc;
        border-color: #FFD8B0;
        cursor: not-allowed;
    }
    
    @media (max-width: 1200px) {
        .masonry-grid {
            column-count: 3;
        }
    }
    
    @media (max-width: 768px) {
        .masonry-grid {
            column-count: 2;
        }
        
        .filter-buttons {
            justify-content: center;
        }
        
        .modal-download {
            padding: 8px 16px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .masonry-grid {
            column-count: 1;
        }
    }
    
    .btn-download-all {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-download-all:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .date-input {
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .date-input:focus {
        border-color: #FF6600;
        outline: none;
    }
    
    .clear-dates {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .clear-dates:hover {
        background: #5a6268;
    }
    
    .results-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .debug-info {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 20px;
        font-size: 12px;
        display: none;
    }
</style>

<div class="gallery-container">
    <!-- Info Note -->
    <div class="info-note">
        <i class="bi bi-info-circle-fill" style="color: #2196F3; font-size: 18px;"></i>
        <strong>माहिती:</strong> येथे फक्त <strong>सोशल मीडिया जाहिराती</strong> (Ad Type 1) दर्शविल्या जात आहेत.
    </div>
    
    <!-- Debug Info (Hidden - Remove in production) -->
    <div class="debug-info" id="debugInfo">
        <strong>Debug Info:</strong><br>
        Total records from DB: <?php echo $total_records; ?><br>
        Valid images found: <?php echo $valid_records_count; ?><br>
        Image path: <?php echo $social_media_path; ?><br>
        Base path: <?php echo $base_path; ?>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-title">
            <i class="bi bi-funnel"></i>
            फिल्टर
        </div>
        
        <div class="filter-buttons">
            <a href="?status=active&page=1<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($from_date) ? '&from_date=' . $from_date : ''; ?><?php echo !empty($to_date) ? '&to_date=' . $to_date : ''; ?>" 
               class="filter-btn <?php echo $filter_status == 'active' ? 'active' : ''; ?>">
                <i class="bi bi-play-circle"></i> सक्रिय जाहिराती
            </a>
            <a href="?status=past&page=1<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?>" 
               class="filter-btn <?php echo $filter_status == 'past' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i> मुदत संपलेल्या जाहिराती
            </a>
        </div>
        
        <form method="GET" action="" class="row g-3" id="filterForm">
            <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
            <input type="hidden" name="page" value="1">
            
            <div class="col-md-4">
                <label class="form-label">जिल्हा</label>
                <select class="form-select" name="district" onchange="this.form.submit()">
                    <option value="">सर्व जिल्हे</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?php echo htmlspecialchars($district); ?>" <?php echo $filter_district == $district ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($district); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($filter_status == 'active'): ?>
            <div class="col-md-3">
                <label class="form-label">सुरुवात तारीख</label>
                <input type="date" class="form-control date-input" name="from_date" value="<?php echo $from_date; ?>" id="from_date">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">शेवटची तारीख</label>
                <input type="date" class="form-control date-input" name="to_date" value="<?php echo $to_date; ?>" id="to_date">
            </div>
            
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> फिल्टर
                </button>
                <?php if (!empty($from_date) || !empty($to_date)): ?>
                    <a href="?status=active&district=<?php echo urlencode($filter_district); ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> रीसेट
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
        
        <?php if ($filter_status == 'active' && (!empty($from_date) || !empty($to_date))): ?>
        <div class="date-range-info mt-3">
            <i class="bi bi-calendar-range"></i>
            <strong>निवडलेली तारीख श्रेणी:</strong>
            <?php if (!empty($from_date) && !empty($to_date)): ?>
                <?php echo date('d-m-Y', strtotime($from_date)); ?> ते <?php echo date('d-m-Y', strtotime($to_date)); ?>
            <?php elseif (!empty($from_date)): ?>
                <?php echo date('d-m-Y', strtotime($from_date)); ?> पासून पुढे
            <?php elseif (!empty($to_date)): ?>
                <?php echo date('d-m-Y', strtotime($to_date)); ?> पर्यंत
            <?php endif; ?>
            <span style="font-size: 12px; color: #666;">(या कालावधीत सक्रिय असलेल्या जाहिराती)</span>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Results Info -->
    <div class="results-info">
        <h4 style="color: #FF6600; margin: 0;">
            <i class="bi bi-images"></i> 
            एकूण जाहिराती: <?php echo $valid_records_count; ?>
        </h4>
        <?php if ($valid_records_count > 0 && $filter_status == 'active'): ?>
            <button class="btn-download-all" onclick="downloadAllImages()">
                <i class="bi bi-download"></i> सर्व इमेजेस डाउनलोड करा (ZIP)
            </button>
        <?php endif; ?>
    </div>
    
    <!-- Gallery Grid -->
    <div class="masonry-grid">
        <?php if ($valid_records_count > 0): ?>
            <?php foreach ($valid_ads as $row): 
                $is_active = strtotime($row['end_date']) >= strtotime(date('Y-m-d'));
                $image_path = $row['image_path'];
                $image_filename = $row['social_media_image'];
            ?>
                <div class="ad-card" data-ad-id="<?php echo $row['id']; ?>">
                    <div class="ad-image-container" onclick="openImageModal('<?php echo htmlspecialchars($image_path); ?>', '<?php echo htmlspecialchars($image_filename); ?>')">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($row['ad_title']); ?>" class="ad-image" loading="lazy">
                        <div class="ad-overlay">
                            <button class="overlay-btn" onclick="event.stopPropagation(); downloadImage('<?php echo htmlspecialchars($image_path); ?>', '<?php echo htmlspecialchars($image_filename); ?>')">
                                <i class="bi bi-download"></i> डाउनलोड
                            </button>
                            <button class="overlay-btn" onclick="event.stopPropagation(); openImageModal('<?php echo htmlspecialchars($image_path); ?>', '<?php echo htmlspecialchars($image_filename); ?>')">
                                <i class="bi bi-zoom-in"></i> मोठे पहा
                            </button>
                        </div>
                    </div>
                    <div class="ad-info">
                        <div class="ad-title"><?php echo htmlspecialchars($row['ad_title'] ?: 'शीर्षक नाही'); ?></div>
                        <div class="ad-client"><i class="bi bi-person"></i> <?php echo htmlspecialchars($row['client_name']); ?></div>
                        <div class="ad-details"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['district'] ?: 'N/A'); ?></div>
                        <div class="ad-details"><i class="bi bi-calendar-range"></i> <?php echo date('d-m-Y', strtotime($row['start_date'])); ?> ते <?php echo date('d-m-Y', strtotime($row['end_date'])); ?></div>
                        <div class="ad-details"><i class="bi bi-clock-history"></i> कालावधी: <?php echo $row['duration']; ?> दिवस</div>
                        <div class="mt-2">
                            <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-expired'; ?>">
                                <?php echo $is_active ? 'सक्रिय' : 'मुदत संपलेली'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5" style="grid-column: 1 / -1;">
                <i class="bi bi-images" style="font-size: 80px; color: #FFA500;"></i>
                <h4 style="color: #FF6600; margin-top: 20px;">कोणतीही जाहिरात सापडली नाही</h4>
                <p class="text-muted">कृपया फिल्टर बदला किंवा नवीन जाहिरात तयार करा.</p>
                <?php if ($total_records > 0 && $valid_records_count == 0): ?>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <?php echo $total_records; ?> जाहिराती सापडल्या परंतु त्यांच्या इमेजेस उपलब्ध नाहीत. 
                        कृपया इमेजेस योग्य फोल्डरमध्ये अपलोड केल्या आहेत का ते तपासा.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <nav aria-label="पेज नेव्हिगेशन">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $page - 1; ?><?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($from_date) ? '&from_date=' . $from_date : ''; ?><?php echo !empty($to_date) ? '&to_date=' . $to_date : ''; ?>">
                        <i class="bi bi-chevron-left"></i> मागील
                    </a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-left"></i> मागील</span>
                </li>
                <?php endif; ?>
                
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $i; ?><?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($from_date) ? '&from_date=' . $from_date : ''; ?><?php echo !empty($to_date) ? '&to_date=' . $to_date : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $page + 1; ?><?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($from_date) ? '&from_date=' . $from_date : ''; ?><?php echo !empty($to_date) ? '&to_date=' . $to_date : ''; ?>">
                        पुढील <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">पुढील <i class="bi bi-chevron-right"></i></span>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <span class="modal-close" onclick="closeImageModal()">&times;</span>
    <img id="modalImage" class="modal-image" src="" alt="">
    <button id="modalDownloadBtn" class="modal-download" onclick="downloadCurrentImage()">
        <i class="bi bi-download"></i> इमेज डाउनलोड करा
    </button>
</div>

<!-- JSZip library for ZIP download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
let currentImagePath = '';
let currentImageName = '';

// Open image modal
function openImageModal(imagePath, imageName) {
    currentImagePath = imagePath;
    currentImageName = imageName;
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.classList.add('active');
    modalImg.src = imagePath;
}

// Close image modal
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.remove('active');
}

// Download single image
function downloadImage(imagePath, imageName) {
    // Create an anchor element and trigger download
    const link = document.createElement('a');
    link.href = imagePath;
    link.download = imageName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Download current image from modal
function downloadCurrentImage() {
    if (currentImagePath && currentImageName) {
        downloadImage(currentImagePath, currentImageName);
    }
}

// Download all images as ZIP
function downloadAllImages() {
    const images = [];
    const adCards = document.querySelectorAll('.ad-card');
    
    adCards.forEach(card => {
        const imgElement = card.querySelector('.ad-image');
        if (imgElement && imgElement.src) {
            const imgPath = imgElement.src;
            const imgName = imgPath.split('/').pop();
            images.push({ path: imgPath, name: imgName });
        }
    });
    
    if (images.length === 0) {
        alert('कोणतीही इमेज डाउनलोड करण्यासाठी उपलब्ध नाही.');
        return;
    }
    
    // Show loading
    const downloadBtn = document.querySelector('.btn-download-all');
    const originalText = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> तयार करत आहे...';
    downloadBtn.disabled = true;
    
    const zip = new JSZip();
    const promises = [];
    
    images.forEach((img, index) => {
        const promise = fetch(img.path)
            .then(response => response.blob())
            .then(blob => {
                let fileName = img.name;
                if (!fileName || fileName === '') {
                    fileName = `social_media_ad_${index + 1}.jpg`;
                }
                zip.file(fileName, blob);
            })
            .catch(error => console.error('Error fetching image:', img.path, error));
        
        promises.push(promise);
    });
    
    Promise.all(promises)
        .then(() => {
            return zip.generateAsync({ type: 'blob' });
        })
        .then(content => {
            saveAs(content, `social_media_ads_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.zip`);
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error creating zip:', error);
            alert('ZIP तयार करताना त्रुटी आली.');
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
        });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Prevent modal close when clicking on modal content
document.getElementById('modalImage')?.addEventListener('click', function(e) {
    e.stopPropagation();
});

document.querySelector('.modal-download')?.addEventListener('click', function(e) {
    e.stopPropagation();
});

// Date validation
const fromDate = document.getElementById('from_date');
const toDate = document.getElementById('to_date');

if (fromDate && toDate) {
    fromDate.addEventListener('change', function() {
        if (toDate.value && this.value > toDate.value) {
            toDate.value = this.value;
        }
    });
    
    toDate.addEventListener('change', function() {
        if (fromDate.value && this.value < fromDate.value) {
            fromDate.value = this.value;
        }
    });
}

// To see debug info (remove in production)
// document.getElementById('debugInfo').style.display = 'block';
</script>

<?php
include 'components/footer.php';
?>