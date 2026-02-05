<?php
// District Table Component
// Get filters from parent (dashboard.php)
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-1 month'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$selected_region = $_GET['region'] ?? 'all';

// Initialize arrays for data
$districts = [];
$district_totals = [];
$category_data = [];
$views_data = [];
$english_district_names = []; // Store English district names for linking

// Build the query based on region selection
if ($selected_region == 'all') {
    // Query for ALL regions - Show Marathi district names for all ...
    $query = "SELECT 
                md.dmarathi AS district_name_marathi,
                md.district AS district_name_english,
                cl.marathi_name AS category_name,
                cl.catagory AS category_english,
                COUNT(*) AS total_news,
                SUM(na.`view`) AS total_views
            FROM news_articles na
            JOIN catagory_list cl 
                ON cl.catagory = na.category_name
            JOIN mdistrict md
                ON md.district = na.district_name
            WHERE 
                na.is_approved = 1
                AND na.published_date >= ?
                AND na.published_date <= ?
            GROUP BY 
                md.dmarathi,
                md.district,
                cl.marathi_name,
                cl.catagory
            ORDER BY 
                md.dmarathi,
                cl.marathi_name;
            ";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ss", $from_date, $to_date);
    }
} else {
    // Query for specific region (with district filter and Marathi district names)
    $query = "SELECT 
                md.dmarathi AS district_name_marathi,
                md.district AS district_name_english,
                cl.marathi_name AS category_name,
                cl.catagory AS category_english,
                COUNT(*) AS total_news,
                SUM(na.`view`) AS total_views
            FROM news_articles na
            JOIN catagory_list cl 
                ON cl.catagory = na.category_name
            JOIN mdistrict md
                ON md.district = na.district_name
                -- LOWER(REGEXP_REPLACE(md.district, '[^a-zA-Z0-9]', '')) =
                -- LOWER(REGEXP_REPLACE(na.district_name, '[^a-zA-Z0-9]', ''))
            WHERE 
                na.is_approved = 1
                AND na.published_date >= ?
                AND na.published_date <= ?
                AND md.division = ?
            GROUP BY 
                md.dmarathi,
                md.district,
                cl.marathi_name,
                cl.catagory
            ORDER BY 
                md.dmarathi,
                cl.marathi_name;
            ";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("sss", $from_date, $to_date, $selected_region);
    }
}

if (isset($stmt) && $stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Process the data
    while ($row = $result->fetch_assoc()) {
        // Always use Marathi district name from md.dmarathi
        $district_marathi = $row['district_name_marathi'];
        $district_english = $row['district_name_english'];
        $category_marathi = $row['category_name'];
        $category_english = $row['category_english'];
        $news_count = $row['total_news'];
        $views_count = $row['total_views'];
        
        // Store district totals
        if (!isset($district_totals[$district_marathi])) {
            $district_totals[$district_marathi] = 0;
            $category_data[$district_marathi] = [];
            $views_data[$district_marathi] = [];
            $english_district_names[$district_marathi] = $district_english;
            $districts[] = $district_marathi;
        }
        
        $district_totals[$district_marathi] += $news_count;
        $category_data[$district_marathi][$category_marathi] = [
            'count' => $news_count,
            'views' => $views_count,
            'english_name' => $category_english
        ];
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Error preparing query: " . $conn->error . "</div>";
}

// Sort districts by total news (descending) for default view
arsort($district_totals);

// Get all unique categories
$allCategories = [];
foreach ($category_data as $cats) {
    $allCategories = array_merge($allCategories, array_keys($cats));
}
$allCategories = array_unique($allCategories);
sort($allCategories);

$total_all_news = array_sum($district_totals);
$total_all_views = 0;
foreach ($category_data as $districtCats) {
    foreach ($districtCats as $catData) {
        $total_all_views += $catData['views'];
    }
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>
            <?php if ($selected_region != 'all'): ?>
                <?php echo htmlspecialchars($selected_region); ?> प्रदेश - 
            <?php endif; ?>
            जिल्हावार बातमी डेटा तक्ता
        </h5>
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($districts)): ?>
            <button type="button" class="btn btn-light btn-sm" id="downloadTablePdf" title="PDF डाउनलोड करा">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </button>
            <?php endif; ?>
            <small class="d-none d-md-inline">
                <?php echo date('d M Y', strtotime($from_date)); ?> ते <?php echo date('d M Y', strtotime($to_date)); ?>
                <?php if ($selected_region != 'all'): ?>
                    <br><span class="fst-italic"><?php echo htmlspecialchars($selected_region); ?> प्रदेश</span>
                <?php endif; ?>
            </small>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($districts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">
                    बातम्या आढळल्या नाहीत 
                    <?php if ($selected_region != 'all'): ?>
                        <?php echo htmlspecialchars($selected_region); ?> प्रदेशात 
                    <?php endif; ?>
                    निवडलेल्या तारखेपर्यंत.
                </p>
            </div>
        <?php else: ?>
            <!-- Table Summary Stats -->
            <div class="bg-light p-3 border-bottom">
                <div class="row">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt text-info fs-5 me-2"></i>
                            <div>
                                <div class="text-muted small">एकूण जिल्हे</div>
                                <div class="fw-bold"><?php echo count($districts); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-newspaper text-info fs-5 me-2"></i>
                            <div>
                                <div class="text-muted small">एकूण बातम्या</div>
                                <div class="fw-bold"><?php echo number_format($total_all_news); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-eye text-info fs-5 me-2"></i>
                            <div>
                                <div class="text-muted small">एकूण दृश्ये</div>
                                <div class="fw-bold"><?php echo number_format($total_all_views); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Controls -->
            <div class="p-3 border-bottom bg-white">
                <div class="row g-2">
                    <div class="col-md-8">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="tableSearch" placeholder="जिल्हा किंवा वर्ग शोधा...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="sortBy">
                            <option value="district">जिल्हानुसार क्रमवारी (अ-ह)</option>
                            <option value="district_desc">जिल्हानुसार क्रमवारी (ह-अ)</option>
                            <option value="total" selected>बातम्यानुसार क्रमवारी (जास्त-कमी)</option>
                            <option value="total_asc">बातम्यानुसार क्रमवारी (कमी-जास्त)</option>
                            <option value="views">दृश्यांनुसार क्रमवारी (जास्त-कमी)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Responsive Table Container -->
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0" id="districtTable">
                    <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th width="40%">जिल्हा</th>
                            <th width="15%" class="text-end">एकूण बातम्या</th>
                            <th width="15%" class="text-end">एकूण दृश्ये</th>
                            <th width="15%" class="text-end">सरासरी दृश्ये/बातमी</th>
                            <th width="15%" class="text-center">वर्ग</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 0;
                        foreach ($district_totals as $district_marathi => $total_news): 
                            $district_english = $english_district_names[$district_marathi] ?? $district_marathi;
                            $district_views = 0;
                            $district_categories = $category_data[$district_marathi] ?? [];
                            
                            // Calculate total views for this district
                            if (isset($district_categories)) {
                                foreach ($district_categories as $catData) {
                                    $district_views += $catData['views'];
                                }
                            }
                            
                            // Calculate average views per news
                            $avg_views = $total_news > 0 ? round($district_views / $total_news, 1) : 0;
                            $counter++;
                        ?>
                        <tr class="district-row" data-district="<?php echo htmlspecialchars($district_marathi); ?>">
                            <td class="fw-bold">
                                <a href="#" class="text-decoration-none district-toggle" data-bs-toggle="collapse" 
                                   data-bs-target="#details-<?php echo $counter; ?>">
                                    <?php echo htmlspecialchars($district_marathi); ?>
                                    <i class="bi bi-chevron-down float-end"></i>
                                </a>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                <?php echo number_format($total_news); ?>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <?php echo number_format($district_views); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-<?php echo $avg_views >= 50 ? 'success' : ($avg_views >= 20 ? 'warning' : 'secondary'); ?> bg-opacity-10 text-<?php echo $avg_views >= 50 ? 'success' : ($avg_views >= 20 ? 'warning' : 'secondary'); ?>">
                                    <?php echo $avg_views; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill">
                                    <?php echo count($district_categories); ?> वर्ग
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Category Details Row -->
                        <tr class="collapse" id="details-<?php echo $counter; ?>">
                            <td colspan="5" class="bg-light p-0">
                                <div class="p-3">
                                    <h6 class="mb-3"><?php echo htmlspecialchars($district_marathi); ?> साठी वर्गवार विभागणी</h6>
                                    <div class="row">
                                        <?php 
                                        $cat_counter = 0;
                                        foreach ($district_categories as $category_marathi => $catData):
                                            $count = $catData['count'];
                                            $views = $catData['views'];
                                            $category_english = $catData['english_name'];
                                            $percentage = $total_news > 0 ? round(($count / $total_news) * 100, 1) : 0;
                                            $cat_counter++;
                                            
                                            // Create URL for mdnews.php
                                            $redirect_url = "mdnews.php?" . http_build_query([
                                                'district' => $district_english,
                                                'category' => $category_english,
                                                'from_date' => $from_date,
                                                'to_date' => $to_date
                                            ]);
                                        ?>
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <a href="<?php echo $redirect_url; ?>" class="text-decoration-none category-card-link">
                                                <div class="card border-0 shadow-sm h-100 clickable-card">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($category_marathi); ?></h6>
                                                            <span class="badge bg-primary"><?php echo $percentage; ?>%</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <div class="text-muted small">बातम्या</div>
                                                                <div class="fw-bold"><?php echo number_format($count); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="text-muted small">दृश्ये</div>
                                                                <div class="fw-bold text-info"><?php echo number_format($views); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="progress mt-2" style="height: 5px;">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: <?php echo min($percentage, 100); ?>%;" 
                                                                 aria-valuenow="<?php echo $percentage; ?>" 
                                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <div class="mt-2 text-end">
                                                            <small class="text-primary">
                                                                <i class="bi bi-arrow-right-circle me-1"></i>सर्व बातम्या पहा
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($district_categories) === 0): ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                या जिल्ह्यासाठी वर्ग डेटा उपलब्ध नाही.
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold text-end">एकूण:</td>
                            <td class="text-end fw-bold text-primary">
                                <?php echo number_format($total_all_news); ?>
                            </td>
                            <td class="text-end fw-bold text-info">
                                <?php echo number_format($total_all_views); ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?php echo $total_all_news > 0 ? round($total_all_views / $total_all_news, 1) : 0; ?>
                            </td>
                            <td class="text-center fw-bold">
                                <?php echo count($allCategories); ?> एकूण वर्ग
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Table Footer -->
            <div class="p-3 border-top">
                <div class="text-muted small">
                    दाखवत आहे <?php echo count($districts); ?> जिल्हे सह <?php echo count($allCategories); ?> वर्ग
                    <?php if ($selected_region != 'all'): ?>
                        <span class="text-info"> | प्रदेश: <?php echo htmlspecialchars($selected_region); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Table JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($districts)): ?>
    const table = document.getElementById('districtTable');
    const tableSearch = document.getElementById('tableSearch');
    const clearSearch = document.getElementById('clearSearch');
    const sortBy = document.getElementById('sortBy');
    const downloadPdfBtn = document.getElementById('downloadTablePdf');
    
    // Store original data for sorting
    const districtRows = Array.from(table.querySelectorAll('.district-row'));
    
    // PDF Download functionality
    downloadPdfBtn.addEventListener('click', function() {
        downloadPdfBtn.disabled = true;
        const originalText = downloadPdfBtn.innerHTML;
        downloadPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> तयार करत आहे...';
        
        // Prepare data for PDF
        const pdfData = {
            title: '<?php echo ($selected_region != "all" ? $selected_region . " प्रदेश - " : "") ?>जिल्हावार बातमी डेटा तक्ता',
            from_date: '<?php echo $from_date; ?>',
            to_date: '<?php echo $to_date; ?>',
            region: '<?php echo $selected_region; ?>',
            total_districts: <?php echo count($districts); ?>,
            total_news: <?php echo $total_all_news; ?>,
            total_views: <?php echo $total_all_views; ?>,
            districts: [],
            generated_at: new Date().toLocaleString('mr-IN')
        };
        
        // Collect district data from the table
        districtRows.forEach(row => {
            const districtName = row.getAttribute('data-district');
            const totalNews = parseInt(row.querySelector('td:nth-child(2)').textContent.replace(/,/g, ''));
            const totalViews = parseInt(row.querySelector('td:nth-child(3) .badge').textContent.replace(/,/g, ''));
            const avgViews = parseFloat(row.querySelector('td:nth-child(4) .badge').textContent);
            const categoryCount = parseInt(row.querySelector('td:nth-child(5) .badge').textContent.match(/\d+/)[0]);
            
            const districtData = {
                name: districtName,
                total_news: totalNews,
                total_views: totalViews,
                avg_views: avgViews,
                category_count: categoryCount,
                categories: []
            };
            
            // Get category details if available
            const detailsId = row.querySelector('.district-toggle')?.getAttribute('data-bs-target');
            if (detailsId) {
                const detailsRow = document.querySelector(detailsId);
                if (detailsRow) {
                    const categoryCards = detailsRow.querySelectorAll('.category-card-link');
                    categoryCards.forEach(card => {
                        const categoryName = card.querySelector('.card-title').textContent.trim();
                        const newsCount = parseInt(card.querySelector('.row .col-6:first-child .fw-bold').textContent.replace(/,/g, ''));
                        const viewsCount = parseInt(card.querySelector('.row .col-6:last-child .fw-bold').textContent.replace(/,/g, ''));
                        const percentage = parseInt(card.querySelector('.badge.bg-primary').textContent.replace('%', ''));
                        
                        districtData.categories.push({
                            name: categoryName,
                            news_count: newsCount,
                            views_count: viewsCount,
                            percentage: percentage
                        });
                    });
                }
            }
            
            pdfData.districts.push(districtData);
        });
        
        // Generate PDF
        generatePDF(pdfData);
        
        // Reset button after 1 second
        setTimeout(() => {
            downloadPdfBtn.disabled = false;
            downloadPdfBtn.innerHTML = originalText;
        }, 1000);
    });
    
    // PDF Generation function
    function generatePDF(data) {
        // Create a new window for PDF
        const printWindow = window.open('', '_blank');
        
        // Prepare date strings
        const fromDate = new Date(data.from_date);
        const toDate = new Date(data.to_date);
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        const fromDateStr = fromDate.toLocaleDateString('mr-IN', options);
        const toDateStr = toDate.toLocaleDateString('mr-IN', options);
        
        // Prepare HTML content for PDF
        const pdfContent = `
        <!DOCTYPE html>
        <html lang="mr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>${data.title}</title>
            <style>
                @page {
                    size: A4;
                    margin: 15mm;
                }
                body {
                    font-family: 'Arial Unicode MS', 'Nirmala UI', 'Arial', sans-serif;
                    direction: ltr;
                    margin: 0;
                    padding: 0;
                    color: #333;
                    font-size: 12px;
                    line-height: 1.4;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 3px solid #0d6efd;
                    padding-bottom: 15px;
                }
                .header h1 {
                    color: #0d6efd;
                    margin: 0;
                    font-size: 22px;
                    margin-bottom: 5px;
                }
                .header .subtitle {
                    color: #666;
                    font-size: 14px;
                }
                .header .date-range {
                    color: #dc3545;
                    font-weight: bold;
                    margin-top: 5px;
                    font-size: 13px;
                }
                .summary-stats {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 25px;
                    flex-wrap: wrap;
                    gap: 15px;
                }
                .stat-card {
                    flex: 1;
                    min-width: 180px;
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 15px;
                    text-align: center;
                }
                .stat-card .label {
                    font-size: 11px;
                    color: #666;
                    text-transform: uppercase;
                    margin-bottom: 8px;
                }
                .stat-card .value {
                    font-size: 24px;
                    font-weight: bold;
                    color: #0d6efd;
                }
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                    margin-top: 15px;
                }
                .data-table th {
                    background-color: #343a40;
                    color: white;
                    padding: 10px 8px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #454d55;
                }
                .data-table td {
                    padding: 8px 8px;
                    border: 1px solid #dee2e6;
                }
                .data-table tr:nth-child(even) {
                    background-color: #f8f9fa;
                }
                .data-table .district-name {
                    font-weight: bold;
                    color: #0d6efd;
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .badge {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 12px;
                    font-size: 10px;
                    font-weight: bold;
                }
                .badge-primary {
                    background-color: #0d6efd;
                    color: white;
                }
                .badge-success {
                    background-color: #198754;
                    color: white;
                }
                .badge-warning {
                    background-color: #ffc107;
                    color: #212529;
                }
                .badge-info {
                    background-color: #0dcaf0;
                    color: #212529;
                }
                .table-footer {
                    background-color: #f8f9fa !important;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #dee2e6;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
                .category-section {
                    margin-top: 30px;
                    page-break-inside: avoid;
                }
                .category-section h3 {
                    color: #0d6efd;
                    border-bottom: 2px solid #0d6efd;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                    font-size: 16px;
                }
                .category-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                    gap: 12px;
                    margin-bottom: 20px;
                }
                .category-card {
                    border: 1px solid #dee2e6;
                    border-radius: 6px;
                    padding: 10px;
                    background: #f8f9fa;
                    page-break-inside: avoid;
                }
                .category-card h4 {
                    margin: 0 0 8px 0;
                    font-size: 12px;
                    color: #495057;
                }
                .no-print {
                    display: none;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .stat-card {
                        break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${data.title}</h1>
                <div class="subtitle">
                    ${data.region !== 'all' ? data.region + ' प्रदेश' : 'सर्व प्रदेश'}
                </div>
                <div class="date-range">${fromDateStr} ते ${toDateStr}</div>
            </div>
            
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="label">एकूण जिल्हे</div>
                    <div class="value">${data.total_districts}</div>
                </div>
                <div class="stat-card">
                    <div class="label">एकूण बातम्या</div>
                    <div class="value">${data.total_news.toLocaleString('mr-IN')}</div>
                </div>
                <div class="stat-card">
                    <div class="label">एकूण दृश्ये</div>
                    <div class="value">${data.total_views.toLocaleString('mr-IN')}</div>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="35%">जिल्हा</th>
                        <th width="15%" class="text-right">एकूण बातम्या</th>
                        <th width="15%" class="text-right">एकूण दृश्ये</th>
                        <th width="15%" class="text-right">सरासरी दृश्ये</th>
                        <th width="20%" class="text-center">वर्ग</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.districts.map((district, index) => `
                        <tr>
                            <td class="district-name">${district.name}</td>
                            <td class="text-right">${district.total_news.toLocaleString('mr-IN')}</td>
                            <td class="text-right">
                                <span class="badge badge-info">${district.total_views.toLocaleString('mr-IN')}</span>
                            </td>
                            <td class="text-right">
                                <span class="badge ${district.avg_views >= 50 ? 'badge-success' : district.avg_views >= 20 ? 'badge-warning' : 'badge-info'}">
                                    ${district.avg_views}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary">${district.category_count} वर्ग</span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr class="table-footer">
                        <td><strong>एकूण:</strong></td>
                        <td class="text-right"><strong>${data.total_news.toLocaleString('mr-IN')}</strong></td>
                        <td class="text-right"><strong>${data.total_views.toLocaleString('mr-IN')}</strong></td>
                        <td class="text-right"><strong>${(data.total_news > 0 ? (data.total_views / data.total_news).toFixed(1) : 0)}</strong></td>
                        <td class="text-center">
                            <span class="badge badge-primary">
                                ${data.districts.reduce((sum, d) => sum + d.category_count, 0)} वर्ग
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="footer">
                <div>रिपोर्ट जनरेट केले: ${data.generated_at}</div>
                <div>© ${new Date().getFullYear()} न्यूझ डॅशबोर्ड</div>
            </div>
            
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                    
                    window.onafterprint = function() {
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                };
            <\/script>
        </body>
        </html>
        `;
        
        // Write content to new window
        printWindow.document.open();
        printWindow.document.write(pdfContent);
        printWindow.document.close();
    }
    
    // Search functionality
    tableSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        districtRows.forEach(row => {
            const district = row.getAttribute('data-district').toLowerCase();
            const text = row.textContent.toLowerCase();
            const isVisible = district.includes(searchTerm) || text.includes(searchTerm);
            row.style.display = isVisible ? '' : 'none';
            
            // Also hide/show the details row
            const detailsId = row.querySelector('.district-toggle')?.getAttribute('data-bs-target');
            if (detailsId) {
                const detailsRow = document.querySelector(detailsId);
                if (detailsRow) {
                    detailsRow.style.display = isVisible ? '' : 'none';
                }
            }
        });
    });
    
    clearSearch.addEventListener('click', function() {
        tableSearch.value = '';
        districtRows.forEach(row => row.style.display = '');
        document.querySelectorAll('.collapse').forEach(collapse => {
            collapse.style.display = '';
        });
    });
    
    // Sorting functionality
    sortBy.addEventListener('change', function() {
        const sortValue = this.value;
        
        districtRows.sort((a, b) => {
            const districtA = a.getAttribute('data-district');
            const districtB = b.getAttribute('data-district');
            
            // Get numeric values
            const totalA = parseInt(a.querySelector('td:nth-child(2)').textContent.replace(/,/g, ''));
            const totalB = parseInt(b.querySelector('td:nth-child(2)').textContent.replace(/,/g, ''));
            
            const viewsA = parseInt(a.querySelector('td:nth-child(3) .badge').textContent.replace(/,/g, ''));
            const viewsB = parseInt(b.querySelector('td:nth-child(3) .badge').textContent.replace(/,/g, ''));
            
            switch(sortValue) {
                case 'district':
                    return districtA.localeCompare(districtB);
                case 'district_desc':
                    return districtB.localeCompare(districtA);
                case 'total':
                    return totalB - totalA;
                case 'total_asc':
                    return totalA - totalB;
                case 'views':
                    return viewsB - viewsA;
                default:
                    return 0;
            }
        });
        
        // Reorder table rows
        const tbody = table.querySelector('tbody');
        
        // Remove all rows
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        // Add rows back in sorted order
        districtRows.forEach(row => {
            tbody.appendChild(row);
            
            // Add details row after each district row
            const detailsId = row.querySelector('.district-toggle')?.getAttribute('data-bs-target');
            if (detailsId) {
                const detailsRow = document.querySelector(detailsId + '--original');
                if (detailsRow) {
                    const clonedDetails = detailsRow.cloneNode(true);
                    clonedDetails.id = detailsId.replace('#', '');
                    clonedDetails.classList.remove('show');
                    tbody.appendChild(clonedDetails);
                }
            }
        });
    });
    
    // Store original details rows for sorting
    document.querySelectorAll('.district-row .collapse').forEach(collapse => {
        const clone = collapse.cloneNode(true);
        clone.id = collapse.id + '--original';
        clone.style.display = 'none';
        document.body.appendChild(clone);
    });
    <?php endif; ?>
    
    // Add hover effect to clickable cards
    document.querySelectorAll('.clickable-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 6px 15px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.05)';
        });
        
        card.addEventListener('click', function(e) {
            // Let the anchor tag handle the navigation
            // This is just for visual feedback
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 10px rgba(0,0,0,0.08)';
        });
    });
});
</script>

<style>
/* Table specific styles */
.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #888 #f1f1f1;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.district-row:hover {
    background-color: #f8f9fa !important;
}

.district-toggle {
    color: #333 !important;
    transition: color 0.2s;
}

.district-toggle:hover {
    color: #0d6efd !important;
}

.district-toggle.collapsed .bi-chevron-down {
    transform: rotate(-90deg);
    transition: transform 0.2s;
}

.district-toggle:not(.collapsed) .bi-chevron-down {
    transform: rotate(0);
}

.badge.bg-opacity-10 {
    opacity: 1;
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.card-title {
    font-size: 0.9rem;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #0d6efd;
}

/* Clickable card styles */
.category-card-link {
    display: block;
    text-decoration: none !important;
    color: inherit;
}

.clickable-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    cursor: pointer;
}

.clickable-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.clickable-card .card-body {
    transition: all 0.3s ease;
}

.clickable-card:hover .card-body {
    background-color: #f8f9fa;
}

/* Fix table header z-index to stay below navbar */
.table-dark {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important; /* Lower than navbar */
}

/* PDF Button Styles */
#downloadTablePdf {
    background-color: white;
    border: 1px solid #dee2e6;
    color: #dc3545;
    font-weight: 500;
    transition: all 0.3s ease;
}

#downloadTablePdf:hover {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
}

#downloadTablePdf:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
    
    .card-header small {
        align-self: flex-end;
    }
    
    #downloadTablePdf {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .table th, .table td {
        padding: 0.5rem;
        font-size: 0.85rem;
    }
    
    .table-responsive {
        max-height: 400px;
    }
    
    .card-body .row > div {
        margin-bottom: 10px;
    }
    
    /* Adjust table header position on mobile */
    .table-dark {
        top: 0 !important;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        max-height: 350px;
    }
    
    .card-title {
        font-size: 0.8rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .table th, .table td {
        padding: 0.4rem;
        font-size: 0.8rem;
    }
    
    .clickable-card {
        margin-bottom: 10px;
    }
    
    #downloadTablePdf {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
        top: 8px;
    }
}
</style>