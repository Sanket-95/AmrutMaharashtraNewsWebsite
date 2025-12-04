<?php  
//  <!-- category_news.php -->
include 'components/header.php';
include 'components/navbar.php';
include 'components/db_config.php';

// Fetch category from query parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'home';

// Handle date filter if provided
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "WHERE category_name = ?";
$params = array($category);
$param_types = "s";

if (!empty($date_filter)) {
    $where_clause .= " AND DATE(published_date) = ?";
    $params[] = $date_filter;
    $param_types .= "s";
}

// Fetch news articles based on category and optional date filter
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
    }
    
    .date-picker-compact {
        max-width: 180px;
        display: inline-block;
    }
    
    .date-filter-btn {
        padding: 6px 12px;
    }
</style>

<div class="container mt-4">
    <!-- Header with Home Button and Compact Date Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Home Button on Left -->
        <div>
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-home"></i> मुख्यपृष्ठ
            </a>
        </div>
        
        <!-- Page Title -->
        <h2 class="mb-0 text-capitalize text-center flex-grow-1">
            <span class="badge badge-orange p-2"><?php echo htmlspecialchars($category); ?></span> News
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
        <div class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-calendar-day"></i> 
                Showing results for: <strong><?php echo date("F j, Y", strtotime($date_filter)); ?></strong>
            </div>
            <a href="category_news.php?category=<?php echo urlencode($category); ?>" 
               class="btn btn-sm btn-outline-primary">
                Show All
            </a>
        </div>
    <?php endif; ?>
    
    <!-- News Articles -->
    <div class="row">
        <?php if (!empty($news_articles)): ?>
            <?php foreach ($news_articles as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo htmlspecialchars($article['cover_photo_url']); ?>" 
                             class="card-img-top" 
                             alt="Cover Photo"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($article['summary']); ?></p>
                            <div class="mt-auto">
                                <a href="news.php?id=<?php echo $article['news_id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-newspaper"></i> Read More
                                </a>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
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
                    <h4 class="text-primary">No news articles found</h4>
                    <p>
                        <?php if (!empty($date_filter)): ?>
                            No articles found for <strong><?php echo htmlspecialchars($category); ?></strong> category 
                            on <strong><?php echo date("F j, Y", strtotime($date_filter)); ?></strong>
                        <?php else: ?>
                            No articles found in <strong><?php echo htmlspecialchars($category); ?></strong> category
                        <?php endif; ?>
                    </p>
                    <a href="category_news.php?category=<?php echo urlencode($category); ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-redo"></i> View All Articles
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Date Picker -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as placeholder if no date is selected
    const datePicker = document.getElementById('datePicker');
    if (datePicker && !datePicker.value) {
        // Optional: Set placeholder text
        datePicker.setAttribute('title', 'Select date to filter');
    }
});
</script>

<?php
include 'components/footer.php';
?>