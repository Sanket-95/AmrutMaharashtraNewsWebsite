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

// Build the query based on region selection
if ($selected_region == 'all') {
    // Query for ALL regions - Show Marathi district names for all
    $query = "SELECT 
                md.dmarathi AS district_name_marathi,
                cl.marathi_name AS category_name,
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
                cl.marathi_name
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
                cl.marathi_name AS category_name,
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
                AND md.division = ?
            GROUP BY 
                md.dmarathi,
                cl.marathi_name
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
        $district = $row['district_name_marathi'];
        $category = $row['category_name'];
        $news_count = $row['total_news'];
        $views_count = $row['total_views'];
        
        // Store district totals
        if (!isset($district_totals[$district])) {
            $district_totals[$district] = 0;
            $category_data[$district] = [];
            $views_data[$district] = [];
            $districts[] = $district;
        }
        
        $district_totals[$district] += $news_count;
        $category_data[$district][$category] = $news_count;
        $views_data[$district][$category] = $views_count;
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
foreach ($views_data as $districtViews) {
    foreach ($districtViews as $views) {
        $total_all_views += $views;
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
                        foreach ($district_totals as $district => $total_news): 
                            $district_views = 0;
                            $district_categories = $category_data[$district] ?? [];
                            
                            // Calculate total views for this district
                            if (isset($views_data[$district])) {
                                foreach ($views_data[$district] as $views) {
                                    $district_views += $views;
                                }
                            }
                            
                            // Calculate average views per news
                            $avg_views = $total_news > 0 ? round($district_views / $total_news, 1) : 0;
                            $counter++;
                        ?>
                        <tr class="district-row" data-district="<?php echo htmlspecialchars($district); ?>">
                            <td class="fw-bold">
                                <a href="#" class="text-decoration-none district-toggle" data-bs-toggle="collapse" 
                                   data-bs-target="#details-<?php echo $counter; ?>">
                                    <?php echo htmlspecialchars($district); ?>
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
                                    <h6 class="mb-3"><?php echo htmlspecialchars($district); ?> साठी वर्गवार विभागणी</h6>
                                    <div class="row">
                                        <?php 
                                        $cat_counter = 0;
                                        foreach ($district_categories as $category => $count):
                                            $views = $views_data[$district][$category] ?? 0;
                                            $percentage = $total_news > 0 ? round(($count / $total_news) * 100, 1) : 0;
                                            $cat_counter++;
                                        ?>
                                        <div class="col-md-4 col-lg-3 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($category); ?></h6>
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
                                                </div>
                                            </div>
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
    
    // Store original data for sorting
    const districtRows = Array.from(table.querySelectorAll('.district-row'));
    
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

/* Fix table header z-index to stay below navbar */
.table-dark {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important; /* Lower than navbar */
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
}
</style>