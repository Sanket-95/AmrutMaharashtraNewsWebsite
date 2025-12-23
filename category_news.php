<?php  
//  <!-- category_news.php -->
session_start();
include 'components/header.php';
include 'components/navbar.php';
include 'components/db_config.php';

// Category mapping array
$category_map = [
    'home' => 'मुख्य पृष्ठ',
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
    'tourism' => 'पर्यटन',
    'amrut_service' => 'अमृत सेवा कार्य'
];

// Fetch category from query parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'home';

// Get Marathi label for the category
$category_label = isset($category_map[$category]) ? $category_map[$category] : $category;

// Handle date filter if provided
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "WHERE category_name = ?";
$params = array($category);
$param_types = "s";

if (!empty($date_filter)) {
    $where_clause .= " AND DATE(published_date) = ? AND is_approved = 1";
    $params[] = $date_filter;
    $param_types .= "s";
}

// Fetch news articles based on category and optional date filters ............
$news_query = "SELECT 
    news_id,
    title,
    cover_photo_url,
    summary,
    published_by,
    published_date
FROM news_articles
$where_clause
ORDER BY published_date DESC";

$stmt = $conn->prepare($news_query);

// Dynamically bind parameters based on whether date filter is applied
if (!empty($date_filter)) {
    $stmt->bind_param($param_types, ...$params);
} else {
    $stmt->bind_param($param_types, $params[0]);
}

$stmt->execute();
$news_result = $stmt->get_result();

$news_articles = [];
if ($news_result && $news_result->num_rows > 0) {
    while($row = $news_result->fetch_assoc()) {
        $news_articles[] = $row;
    }
}
?>

<!-- Orange Theme CSS Override -->
<style>
    .btn-primary, .btn-primary:hover, .btn-primary:focus {
        background-color: #ff6b35;
        border-color: #ff6b35;
        color: white;
    }
    
    .btn-outline-primary {
        color: #ff6b35;
        border-color: #ff6b35;
    }
    
    .btn-outline-primary:hover {
        background-color: #ff6b35;
        border-color: #ff6b35;
        color: white;
    }
    
    .btn-warning {
        background-color: #ff9500;
        border-color: #ff9500;
    }
    
    .alert-info {
        background-color: #fff3e0;
        border-color: #ffcc80;
        color: #bf360c;
    }
    
    .card {
        border-color: #ffe0b2;
    }
    
    .card-header, .card-footer {
        background-color: #fff8e1;
    }
    
    .text-primary {
        color: #ff6b35 !important;
    }
    
    .badge-orange {
        background-color: #ff6b35;
        color: white;
        font-family: 'Mukta', sans-serif;
        font-size: 20px;
        padding: 10px 20px;
        border-radius: 8px;
    }
    
    .date-picker-compact {
        max-width: 180px;
        display: inline-block;
    }
    
    .date-filter-btn {
        padding: 6px 12px;
    }
    
    body {
        font-family: 'Mukta', sans-serif;
    }
    
    h2 {
        font-family: 'Khand', sans-serif;
        font-weight: 600;
    }
    
    .card-title {
        font-family: 'Khand', sans-serif;
        font-weight: 500;
    }
    
    /* Marathi text styling */
    .marathi-text {
        font-family: 'Mukta', sans-serif;
    }
    
    .marathi-title {
        font-family: 'Khand', sans-serif;
        font-weight: 600;
    }
</style>

<div class="container mt-4">
    <!-- Header with Home Button and Compact Date Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Home Button on Left -->
        <div>
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-home"></i> मुख्य पृष्ठ
            </a>
        </div>
        
        <!-- Page Title with Marathi Category Name -->
        <h2 class="mb-0 text-center flex-grow-1">
            <span class="badge badge-orange p-3"><?php echo htmlspecialchars($category_label); ?></span>
        </h2>
        
        <!-- Compact Date Picker on Right -->
        <div class="text-end">
            <form method="GET" action="" class="d-inline-block" id="dateFilterForm">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <div class="input-group input-group-sm">
                    <input type="date" 
                           class="form-control form-control-sm date-picker-compact" 
                           id="datePicker" 
                           name="date" 
                           value="<?php echo htmlspecialchars($date_filter); ?>"
                           max="<?php echo date('Y-m-d'); ?>"
                           onchange="this.form.submit()"
                           style="max-width: 120px;">
                    
                    <?php if (!empty($date_filter)): ?>
                        <a href="category_news.php?category=<?php echo urlencode($category); ?>" 
                           class="btn btn-warning btn-sm date-filter-btn" 
                           title="Clear Date Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary btn-sm date-filter-btn" title="Filter by Date">
                            <i class="fas fa-filter"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Date Filter Indicator -->
    <?php if (!empty($date_filter)): ?>
        <div class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center marathi-text">
            <div>
                <i class="fas fa-calendar-day"></i> 
                दिनांकानुसार निकाल दाखवत आहे: <strong><?php echo date("F j, Y", strtotime($date_filter)); ?></strong>
            </div>
            <a href="category_news.php?category=<?php echo urlencode($category); ?>" 
               class="btn btn-sm btn-outline-primary">
                सर्व दाखवा
            </a>
        </div>
    <?php endif; ?>
    
    <!-- News Articles -->
    <div class="row">
        <?php if (!empty($news_articles)): ?>
            <?php foreach ($news_articles as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($article['cover_photo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($article['cover_photo_url']); ?>" 
                                 class="card-img-top" 
                                 alt="Cover Photo"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-newspaper fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary marathi-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p class="card-text flex-grow-1 marathi-text"><?php echo htmlspecialchars($article['summary']); ?></p>
                            <div class="mt-auto">
                                <a href="news.php?id=<?php echo $article['news_id']; ?>" 
                                   class="btn btn-primary marathi-text">
                                    <i class="fas fa-newspaper"></i> अधिक वाचा
                                </a>
                            </div>
                        </div>
                        <div class="card-footer text-muted marathi-text">
                            <small>
                                <i class="fas fa-user text-primary"></i> <?php echo htmlspecialchars($article['published_by']); ?> 
                                | <i class="fas fa-calendar-alt text-primary"></i> <?php echo date("F j, Y", strtotime($article['published_date'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3" style="color: #ff6b35;"></i>
                    <h4 class="text-primary marathi-title">बातम्या सापडल्या नाहीत</h4>
                    <p class="marathi-text">
                        <?php if (!empty($date_filter)): ?>
                            <strong><?php echo htmlspecialchars($category_label); ?></strong> वर्गात 
                            <strong><?php echo date("F j, Y", strtotime($date_filter)); ?></strong> रोजी कोणत्याही बातम्या सापडल्या नाहीत
                        <?php else: ?>
                            <strong><?php echo htmlspecialchars($category_label); ?></strong> वर्गात कोणत्याही बातम्या सापडल्या नाहीत
                        <?php endif; ?>
                    </p>
                    <a href="category_news.php?category=<?php echo urlencode($category); ?>" 
                       class="btn btn-primary marathi-text">
                        <i class="fas fa-redo"></i> सर्व बातम्या पहा
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Fonts for Marathi -->
<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">

<!-- JavaScript for Date Picker -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as placeholder if no date is selected
    const datePicker = document.getElementById('datePicker');
    if (datePicker && !datePicker.value) {
        // Optional: Set placeholder text
        datePicker.setAttribute('title', 'Select date to filter');
    }
    
    // Set min date to prevent future dates
    datePicker.max = new Date().toISOString().split('T')[0];
});
</script>

<?php
include 'components/footer.php';
?>