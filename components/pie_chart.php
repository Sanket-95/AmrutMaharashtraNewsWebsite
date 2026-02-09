<?php
// District Graph Component
// Get filters from parent (dashboard.php)
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-1 month'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$selected_region = $_GET['region'] ?? 'all';

// Initialize arrays for data
$districts = [];
$district_totals = [];
$category_data = [];
$views_data = [];

// For PDF - we need English district names
$english_districts = [];
$english_district_totals = [];

// Build the query based on region selection
if ($selected_region == 'all') {
    // Query for ALL regions (no district filter) - get both English and Marathi names
    $query = "SELECT 
                na.district_name,
                na.district_name as english_district_name,
                cl.marathi_name AS category_name,
                COUNT(*) AS total_news,
                SUM(na.`view`) AS total_views
            FROM news_articles na
            JOIN catagory_list cl 
                ON cl.catagory = na.category_name
            WHERE 
                na.is_approved = 1
                AND na.published_date >= ?
                AND na.published_date <= ?
            GROUP BY 
                na.district_name,
                cl.marathi_name
            ORDER BY 
                na.district_name,
                cl.marathi_name;
            ";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ss", $from_date, $to_date);
    }
} else {
    // Query for specific region (with district filter) - get both English and Marathi names
    $query = "SELECT 
                md.dmarathi AS district_name_marathi,
                md.district AS english_district_name,
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
                md.district,
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
        // Use Marathi district name when region is selected, otherwise use English district name
        $district = ($selected_region == 'all') ? $row['district_name'] : $row['district_name_marathi'];
        $english_district = $row['english_district_name'];
        $category = $row['category_name'];
        $news_count = $row['total_news'];
        $views_count = $row['total_views'];
        
        // Store district totals (Marathi for display)
        if (!isset($district_totals[$district])) {
            $district_totals[$district] = 0;
            $category_data[$district] = [];
            $views_data[$district] = [];
            $districts[] = $district;
        }
        
        // Store English district totals for PDF
        if (!isset($english_district_totals[$english_district])) {
            $english_district_totals[$english_district] = 0;
            $english_districts[$english_district] = $district; // Map English to Marathi
        }
        
        $district_totals[$district] += $news_count;
        $english_district_totals[$english_district] += $news_count;
        $category_data[$district][$category] = $news_count;
        $views_data[$district][$category] = $views_count;
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Error preparing query: " . $conn->error . "</div>";
}

// Sort districts by total news (descending) - Marathi version
arsort($district_totals);

// Prepare data for Chart.js - ALL DISTRICTS
$district_labels = array_keys($district_totals);
$news_counts = array_values($district_totals);

// For pie chart - use ALL districts, no limit
$pie_district_labels = $district_labels;
$pie_news_counts = $news_counts;

// Prepare hover data (category breakdown) - WITHOUT views
$hover_data = [];
foreach ($district_totals as $district => $total) {
    $category_items = [];
    foreach ($category_data[$district] as $category => $count) {
        // Remove views from hover data as requested
        $category_items[] = [
            'category' => $category,
            'count' => $count
        ];
    }
    $hover_data[$district] = $category_items;
}

// Get all unique categories for color mapping
$allCategories = [];
foreach ($category_data as $cats) {
    $allCategories = array_merge($allCategories, array_keys($cats));
}
$allCategories = array_unique($allCategories);

// Generate attractive colors for ALL districts
$total_districts = count($district_totals);
$pie_colors = [];
$attractive_colors = [
    '#FF6B6B', '#4ECDC4', '#FFD166', '#06D6A0', '#118AB2',
    '#EF476F', '#7B2CBF', '#3A86FF', '#FB5607', '#8338EC',
    '#FF006E', '#FF9E00', '#00BBF9', '#00F5D4', '#9B5DE5',
    '#F15BB5', '#00BBF9', '#00F5D4', '#9B5DE5', '#F15BB5',
    '#7209B7', '#3A86FF', '#4CC9F0', '#4361EE', '#4895EF',
    '#560BAD', '#B5179E', '#F72585', '#480CA8', '#3A0CA3',
    '#3F37C9', '#4361EE', '#4895EF', '#4CC9F0', '#560BAD',
    '#7209B7', '#B5179E', '#F72585', '#FF6B6B', '#4ECDC4'
];

// Use attractive colors, repeat if needed
for ($i = 0; $i < $total_districts; $i++) {
    $pie_colors[] = $attractive_colors[$i % count($attractive_colors)];
}

// For PDF - Create English version of data
$region_names = [
    'all' => 'All Regions',
    'Konkan' => 'Konkan',
    'Pune' => 'Pune',
    'Sambhajinagar' => 'Sambhajinagar',
    'Nashik' => 'Nashik',
    'Amravati' => 'Amravati',
    'Nagpur' => 'Nagpur'
];

$pdf_title = $selected_region != 'all' ? $region_names[$selected_region] . ' Region - ' : '';
$pdf_title .= 'District Wise News Distribution';

// Convert dates to English format for PDF
$pdf_from_date = date('d M Y', strtotime($from_date));
$pdf_to_date = date('d M Y', strtotime($to_date));

// Prepare English district labels for PDF
$pdf_english_district_labels = [];
$pdf_english_news_counts = [];
foreach ($english_district_totals as $english_district => $count) {
    $pdf_english_district_labels[] = $english_district;
    $pdf_english_news_counts[] = $count;
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 d-flex align-items-center flex-wrap">
            <i class="bi bi-pie-chart me-2"></i>
            <?php if ($selected_region != 'all'): ?>
                <span class="me-1"><?php echo htmlspecialchars($selected_region); ?> प्रदेश -</span>
            <?php endif; ?>
            <span>जिल्हावार बातमी वितरण</span>
            <small class="float-end d-none d-md-inline ms-auto text-end" style="min-width: 250px;">
                <?php echo date('d M Y', strtotime($from_date)); ?> ते <?php echo date('d M Y', strtotime($to_date)); ?>
                <?php if ($selected_region != 'all'): ?>
                    <br><span class="fst-italic">फिल्टर: <?php echo htmlspecialchars($selected_region); ?> प्रदेश</span>
                <?php endif; ?>
            </small>
        </h5>
        
        <!-- Action buttons -->
        <?php if (!empty($districts)): ?>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm" id="fullscreenBtn" title="Fullscreen">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
            <button type="button" class="btn btn-light btn-sm" id="downloadPdfBtn" title="Download as PDF">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
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
            <!-- Main content area -->
            <div class="row g-3">
                <!-- Pie chart column - Center aligned -->
                <div class="col-xl-8 col-lg-7">
                    <div class="chart-container-wrapper">
                        <div class="pie-chart-container">
                            <canvas id="districtPieChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Legend column - Compact layout -->
                <div class="col-xl-4 col-lg-5">
                    <div class="legend-container">
                        <div class="legend-header d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">जिल्हे (<?php echo count($district_labels); ?>)</h6>
                            <small class="text-muted">एकूण: <?php echo array_sum($news_counts); ?> बातम्या</small>
                        </div>
                        <div class="legend-scroll-container">
                            <div class="legend-items" id="pieLegend"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden canvas for PDF generation -->
            <div style="display: none;">
                <canvas id="pdfPieChartCanvas" width="1000" height="600"></canvas>
            </div>
            
            <!-- District count summary -->
            <div class="mt-3 text-center text-muted small">
                दाखवत आहे <?php echo count($districts); ?> जिल्हे
                <?php if ($selected_region != 'all'): ?>
                    <?php echo htmlspecialchars($selected_region); ?> प्रदेशात
                <?php endif; ?>
            </div>
            
            <!-- Compact category legend with show/hide -->
            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted mb-0">वर्ग (<?php echo count($allCategories); ?>)</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleLegend">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div class="category-legend-container" id="categoryLegendContainer">
                    <div class="d-flex flex-wrap gap-1" id="categoryLegend"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer text-muted small py-2">
        <div class="row g-1">
            <div class="col-md-4 col-sm-6">
                <i class="bi bi-geo-alt me-1"></i> 
                <?php if ($selected_region != 'all'): ?>
                    <?php echo htmlspecialchars($selected_region); ?>: 
                <?php endif; ?>
                <?php echo count($districts); ?> जिल्हे
            </div>
            <div class="col-md-4 col-sm-6">
                <i class="bi bi-newspaper me-1"></i> एकूण बातम्या: <?php echo array_sum($news_counts); ?>
            </div>
            <div class="col-md-4 col-sm-12">
                <i class="bi bi-eye me-1"></i> 
                <?php if ($selected_region == 'all'): ?>
                    सर्व प्रदेश
                <?php else: ?>
                    <?php echo htmlspecialchars($selected_region); ?> प्रदेश
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen modal -->
<div class="modal fade" id="fullscreenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pie-chart me-2"></i>
                    <?php if ($selected_region != 'all'): ?>
                        <?php echo htmlspecialchars($selected_region); ?> प्रदेश - 
                    <?php endif; ?>
                    जिल्हावार बातमी वितरण
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 h-100">
                    <div class="col-lg-8 h-100">
                        <div class="h-100 d-flex align-items-center justify-content-center">
                            <div class="fullscreen-chart-container">
                                <canvas id="fullscreenPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="fullscreen-legend-container h-100">
                            <div class="fullscreen-legend-scroll">
                                <div id="fullscreenLegend"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- jsPDF Library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
// Make jsPDF available globally
window.jsPDF = window.jspdf.jsPDF;

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($districts)): ?>
    const ctx = document.getElementById('districtPieChart').getContext('2d');
    const pdfCtx = document.getElementById('pdfPieChartCanvas').getContext('2d');
    
    // Data from PHP
    const districtLabels = <?php echo json_encode($pie_district_labels); ?>;
    const newsCounts = <?php echo json_encode($pie_news_counts); ?>;
    const hoverData = <?php echo json_encode($hover_data); ?>;
    const allCategories = <?php echo json_encode(array_values($allCategories)); ?>;
    const selectedRegion = "<?php echo $selected_region; ?>";
    const fromDate = "<?php echo date('d M Y', strtotime($from_date)); ?>";
    const toDate = "<?php echo date('d M Y', strtotime($to_date)); ?>";
    const totalDistricts = <?php echo count($districts); ?>;
    const totalNews = <?php echo array_sum($news_counts); ?>;
    const pieColors = <?php echo json_encode($pie_colors); ?>;
    
    // PDF variables from PHP - using English data
    const pdfTitle = "<?php echo addslashes($pdf_title); ?>";
    const pdfFromDate = "<?php echo $pdf_from_date; ?>";
    const pdfToDate = "<?php echo $pdf_to_date; ?>";
    const pdfEnglishDistrictLabels = <?php echo json_encode($pdf_english_district_labels); ?>;
    const pdfEnglishNewsCounts = <?php echo json_encode($pdf_english_news_counts); ?>;
    
    // Create category legend
    const legendContainer = document.getElementById('categoryLegend');
    allCategories.forEach((category, index) => {
        const color = pieColors[index % pieColors.length];
        const legendItem = document.createElement('span');
        legendItem.className = 'badge category-badge';
        legendItem.style.backgroundColor = color;
        legendItem.style.color = 'white';
        legendItem.textContent = category;
        legendItem.title = category;
        legendContainer.appendChild(legendItem);
    });
    
    // Toggle legend visibility
    document.getElementById('toggleLegend').addEventListener('click', function() {
        const container = document.getElementById('categoryLegendContainer');
        const icon = this.querySelector('i');
        
        if (container.classList.contains('collapsed')) {
            container.classList.remove('collapsed');
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        } else {
            container.classList.add('collapsed');
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        }
    });
    
    // Custom plugin to show percentage only for significant slices
    const piePercentagePlugin = {
        id: 'piePercentagePlugin',
        afterDatasetsDraw(chart, args, options) {
            if (chart.config.type === 'pie' || chart.config.type === 'doughnut') {
                const { ctx, data, chartArea } = chart;
                
                ctx.save();
                ctx.font = 'bold 11px Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                
                chart.getDatasetMeta(0).data.forEach((arc, index) => {
                    const value = data.datasets[0].data[index];
                    const percentage = Math.round((value / total) * 100);
                    
                    // Only show percentage if slice is large enough (>= 3%)
                    if (percentage >= 3) {
                        const angle = arc.startAngle + (arc.endAngle - arc.startAngle) / 2;
                        const radius = arc.outerRadius * 0.7;
                        
                        const x = arc.x + Math.cos(angle) * radius;
                        const y = arc.y + Math.sin(angle) * radius;
                        
                        // Add text background for readability
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                        const textWidth = ctx.measureText(percentage + '%').width;
                        ctx.fillRect(x - textWidth/2 - 3, y - 7, textWidth + 6, 14);
                        
                        // Draw the count
                        ctx.fillStyle = '#333';
                        ctx.fillText(percentage + '%', x, y);
                    }
                });
                ctx.restore();
            }
        }
    };
    
    // Create main pie chart with thicker borders for better separation
    const pieChart = new Chart(ctx, {
        type: 'pie',
        plugins: [piePercentagePlugin],
        data: {
            labels: districtLabels,
            datasets: [{
                data: newsCounts,
                backgroundColor: pieColors,
                borderColor: '#fff',
                borderWidth: 2, // Increased from 1.5 for better separation
                hoverOffset: 10,
                borderAlign: 'inner'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            
                            return `${label}: ${value} बातम्या (${percentage}%)`;
                        },
                        afterLabel: function(context) {
                            const label = context.label || '';
                            
                            if (hoverData[label]) {
                                let tooltipText = '\nवर्गवार विभागणी:\n';
                                const categoriesToShow = hoverData[label].slice(0, 4);
                                categoriesToShow.forEach((item, index) => {
                                    tooltipText += `• ${item.category}: ${item.count} बातम्या\n`;
                                });
                                
                                if (hoverData[label].length > 4) {
                                    tooltipText += `... आणि ${hoverData[label].length - 4} अधिक वर्ग\n`;
                                }
                                
                                return tooltipText;
                            }
                            return '';
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 10,
                    displayColors: false,
                    bodyFont: {
                        size: 12,
                        family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 800
            }
        }
    });
    
    // Create custom legend
    const pieLegendContainer = document.getElementById('pieLegend');
    districtLabels.forEach((label, index) => {
        const color = pieColors[index];
        const value = newsCounts[index];
        const total = newsCounts.reduce((a, b) => a + b, 0);
        const percentage = Math.round((value / total) * 100);
        
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        legendItem.dataset.index = index;
        
        legendItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="legend-color" style="background-color: ${color}"></div>
                <div class="legend-content flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span class="legend-label">${label}</span>
                        <span class="legend-value">${value}</span>
                    </div>
                    <div class="legend-percentage">${percentage}%</div>
                </div>
            </div>
        `;
        
        // Add hover effect
        legendItem.addEventListener('mouseover', function() {
            this.classList.add('hover');
            const index = parseInt(this.dataset.index);
            pieChart.setActiveElements([{ datasetIndex: 0, index: index }]);
            pieChart.update();
        });
        
        legendItem.addEventListener('mouseout', function() {
            this.classList.remove('hover');
            pieChart.setActiveElements([]);
            pieChart.update();
        });
        
        // Add click to toggle slice
        legendItem.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            const meta = pieChart.getDatasetMeta(0);
            const isHidden = meta.data[index].hidden;
            meta.data[index].hidden = !isHidden;
            pieChart.update();
            
            this.classList.toggle('hidden', !isHidden);
        });
        
        pieLegendContainer.appendChild(legendItem);
    });
    
    // Fullscreen functionality
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const fullscreenModal = new bootstrap.Modal(document.getElementById('fullscreenModal'));
    let fullscreenChart = null;
    
    fullscreenBtn.addEventListener('click', function() {
        fullscreenModal.show();
        
        // Create fullscreen chart after modal is shown
        setTimeout(() => {
            const fullscreenCtx = document.getElementById('fullscreenPieChart').getContext('2d');
            const fullscreenLegend = document.getElementById('fullscreenLegend');
            
            if (fullscreenChart) {
                fullscreenChart.destroy();
            }
            
            fullscreenChart = new Chart(fullscreenCtx, {
                type: 'pie',
                plugins: [piePercentagePlugin],
                data: {
                    labels: districtLabels,
                    datasets: [{
                        data: newsCounts,
                        backgroundColor: pieColors,
                        borderColor: '#fff',
                        borderWidth: 3, // Thicker border for fullscreen
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    
                                    return `${label}: ${value} बातम्या (${percentage}%)`;
                                },
                                afterLabel: function(context) {
                                    const label = context.label || '';
                                    
                                    if (hoverData[label]) {
                                        let tooltipText = '\nवर्गवार विभागणी:\n';
                                        hoverData[label].forEach((item, index) => {
                                            if (index < 6) {
                                                tooltipText += `• ${item.category}: ${item.count} बातम्या\n`;
                                            }
                                        });
                                        
                                        if (hoverData[label].length > 6) {
                                            tooltipText += `... आणि ${hoverData[label].length - 6} अधिक वर्ग\n`;
                                        }
                                        
                                        return tooltipText;
                                    }
                                    return '';
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            bodyFont: { size: 13 }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
            
            // Create fullscreen legend
            fullscreenLegend.innerHTML = '';
            districtLabels.forEach((label, index) => {
                const color = pieColors[index];
                const value = newsCounts[index];
                const total = newsCounts.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                
                const legendItem = document.createElement('div');
                legendItem.className = 'fullscreen-legend-item';
                legendItem.dataset.index = index;
                
                legendItem.innerHTML = `
                    <div class="d-flex align-items-center p-2">
                        <div class="fullscreen-legend-color me-3" style="background-color: ${color}"></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span class="fw-medium">${label}</span>
                                <span class="text-muted">${value}</span>
                            </div>
                            <div class="text-muted small">${percentage}%</div>
                        </div>
                    </div>
                `;
                
                // Add hover effect
                legendItem.addEventListener('mouseover', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    const index = parseInt(this.dataset.index);
                    fullscreenChart.setActiveElements([{ datasetIndex: 0, index: index }]);
                    fullscreenChart.update();
                });
                
                legendItem.addEventListener('mouseout', function() {
                    this.style.backgroundColor = '';
                    fullscreenChart.setActiveElements([]);
                    fullscreenChart.update();
                });
                
                fullscreenLegend.appendChild(legendItem);
            });
        }, 100);
    });
    
    // PDF Generation - With more spacing between chart and legend, larger font, better right-side usage
    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating PDF...';
        btn.disabled = true;
        
        try {
            // IMPORTANT: Create color mapping based on English district labels to ensure same colors
            const colorMapping = {};
            pdfEnglishDistrictLabels.forEach((district, index) => {
                // Find the corresponding Marathi district to get the correct color
                const marathiDistrict = <?php echo json_encode($english_districts); ?>[district];
                if (marathiDistrict) {
                    const marathiIndex = <?php echo json_encode(array_keys($district_totals)); ?>.indexOf(marathiDistrict);
                    if (marathiIndex !== -1) {
                        colorMapping[district] = pieColors[marathiIndex];
                    }
                }
                // If no mapping found, use sequential color
                if (!colorMapping[district]) {
                    colorMapping[district] = pieColors[index % pieColors.length];
                }
            });
            
            // Create array of colors in the same order as English districts
            const pdfColors = pdfEnglishDistrictLabels.map(district => colorMapping[district] || pieColors[0]);
            
            // Create a new square canvas for perfect circle with larger size
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');
            
            // Set larger square dimensions for better quality
            tempCanvas.width = 1400;
            tempCanvas.height = 1400;
            
            // Create PDF chart with English labels on SQUARE canvas
            const pdfChart = new Chart(tempCtx, {
                type: 'pie',
                data: {
                    labels: pdfEnglishDistrictLabels,
                    datasets: [{
                        data: pdfEnglishNewsCounts,
                        backgroundColor: pdfColors,
                        borderColor: '#fff',
                        borderWidth: 2.5,
                        borderAlign: 'inner'
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 14, // Increased to 14 for larger font
                                    family: "'Segoe UI', 'Roboto', 'Arial', sans-serif",
                                    weight: 'bold'
                                },
                                padding: 16, // Increased for more spacing
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 14, // Increased for larger color boxes
                                boxHeight: 14, // Increased for larger color boxes
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function(label, i) {
                                            const value = data.datasets[0].data[i];
                                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((value / total) * 100);
                                            
                                            // Truncate long labels for legend
                                            let displayLabel = label;
                                            if (displayLabel.length > 25) {
                                                displayLabel = displayLabel.substring(0, 23) + '...';
                                            }
                                            
                                            return {
                                                text: `${displayLabel}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            },
                            title: {
                                display: true,
                                text: 'District Legend',
                                font: {
                                    size: 16, // Increased for larger title
                                    weight: 'bold'
                                },
                                padding: 20 // Increased for more spacing
                            }
                        },
                        tooltip: { enabled: false }
                    },
                    layout: {
                        padding: {
                            left: 30, // Increased left padding to move chart more to right
                            right: 30, // Increased right padding for more spacing
                            top: 25, // Increased top padding
                            bottom: 25 // Increased bottom padding
                        }
                    },
                    animation: false
                }
            });
            
            // Wait for chart to render
            setTimeout(() => {
                const pdfChartImage = tempCanvas.toDataURL('image/png');
                
                // Create PDF in portrait mode
                const pdf = new jsPDF('portrait', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                
                // Header
                pdf.setFontSize(20);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(13, 110, 253);
                pdf.text(pdfTitle, pageWidth / 2, 20, { align: 'center' });
                
                // Date range
                pdf.setFontSize(12);
                pdf.setFont("helvetica", "normal");
                pdf.setTextColor(108, 117, 125);
                pdf.text(`Date: ${pdfFromDate} to ${pdfToDate}`, pageWidth / 2, 28, { align: 'center' });
                
                // Summary section with better styling
                pdf.setFontSize(11);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(33, 37, 41);
                
                // Create summary box
                pdf.setFillColor(248, 249, 250);
                pdf.rect(15, 35, pageWidth - 30, 12, 'F');
                pdf.setDrawColor(222, 226, 230);
                pdf.rect(15, 35, pageWidth - 30, 12);
                
                // Summary text
                pdf.text(`Total Districts: ${totalDistricts}`, 20, 42.5);
                pdf.text(`Total News: ${totalNews}`, pageWidth / 2, 42.5, { align: 'center' });
                
                // Region information
                const regionNames = {
                    'all': 'All Regions',
                    'Konkan': 'Konkan',
                    'Pune': 'Pune',
                    'Sambhajinagar': 'Sambhajinagar',
                    'Nashik': 'Nashik',
                    'Amravati': 'Amravati',
                    'Nagpur': 'Nagpur'
                };
                
                if (selectedRegion === 'all') {
                    pdf.text(`Region: All Regions`, pageWidth - 20, 42.5, { align: 'right' });
                } else {
                    pdf.text(`Region: ${regionNames[selectedRegion] || selectedRegion}`, pageWidth - 20, 42.5, { align: 'right' });
                }
                
                // Calculate dimensions for circle - move more to center to use right space
                const chartY = 52;
                const chartSize = 105; // Slightly reduced to make room for legend
                const chartX = 30; // Move more to right (from 20 to 30) to use right space better
                
                // Add the pie chart image as perfect circle
                pdf.addImage(pdfChartImage, 'PNG', chartX, chartY, chartSize, chartSize);
                
                // Add key insights section - keep at original position (below chart)
                const insightsY = chartY + chartSize + 15; // Increased from 10 to 15 for more spacing
                
                // Insights header
                pdf.setFontSize(14);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(13, 110, 253);
                pdf.text('Key Insights', pageWidth / 2, insightsY, { align: 'center' });
                
                // Top districts section
                const topDistrictsY = insightsY + 12; // Increased from 10 to 12 for more spacing
                
                // Find top 5 districts
                const districtsWithData = pdfEnglishDistrictLabels.map((label, index) => ({
                    label: label,
                    count: pdfEnglishNewsCounts[index],
                    percentage: Math.round((pdfEnglishNewsCounts[index] / totalNews) * 100),
                    color: pdfColors[index]
                }));
                
                // Sort by count descending
                districtsWithData.sort((a, b) => b.count - a.count);
                
                // Take top 5
                const topDistricts = districtsWithData.slice(0, 5);
                
                pdf.setFontSize(12);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(33, 37, 41);
                pdf.text('Top 5 Districts by News Count:', 20, topDistrictsY);
                
                // Display top districts
                let currentY = topDistrictsY + 8; // Increased from 7 to 8 for more spacing
                topDistricts.forEach((district, index) => {
                    pdf.setFontSize(11); // Increased from 10 to 11
                    pdf.setFont("helvetica", "normal");
                    pdf.setTextColor(33, 37, 41);
                    
                    // District name and count
                    pdf.text(`${index + 1}. ${district.label}: ${district.count} news (${district.percentage}%)`, 25, currentY);
                    
                    // Add color indicator using the same color
                    if (district.color) {
                        // Convert hex to RGB
                        const hex = district.color.replace('#', '');
                        const r = parseInt(hex.substring(0, 2), 16);
                        const g = parseInt(hex.substring(2, 4), 16);
                        const b = parseInt(hex.substring(4, 6), 16);
                        
                        pdf.setFillColor(r, g, b);
                        pdf.circle(20, currentY - 1.5, 2.5, 'F'); // Increased from 2 to 2.5
                    }
                    
                    currentY += 7; // Increased from 6 to 7 for more spacing
                });
                
                // Add distribution summary
                const summaryY = currentY + 6; // Increased from 5 to 6 for more spacing
                pdf.setFontSize(12);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(33, 37, 41);
                pdf.text('Distribution Summary:', 20, summaryY);
                
                pdf.setFontSize(11); // Increased from 10 to 11
                pdf.setFont("helvetica", "normal");
                
                // Calculate statistics
                const avgNews = Math.round(totalNews / totalDistricts);
                const maxNews = Math.max(...pdfEnglishNewsCounts);
                const minNews = Math.min(...pdfEnglishNewsCounts);
                const maxDistrict = pdfEnglishDistrictLabels[pdfEnglishNewsCounts.indexOf(maxNews)];
                const minDistrict = pdfEnglishDistrictLabels[pdfEnglishNewsCounts.indexOf(minNews)];
                
                let statY = summaryY + 8; // Increased from 7 to 8 for more spacing
                pdf.text(`• Average news per district: ${avgNews}`, 25, statY);
                statY += 6; // Increased from 5 to 6 for more spacing
                pdf.text(`• Highest: ${maxDistrict} (${maxNews} news)`, 25, statY);
                statY += 6; // Increased from 5 to 6 for more spacing
                pdf.text(`• Lowest: ${minDistrict} (${minNews} news)`, 25, statY);
                
                // Footer
                const footerY = pageHeight - 10;
                pdf.setFontSize(8);
                pdf.setTextColor(108, 117, 125);
                pdf.setFont("helvetica", "italic");
                
                pdf.text('Amrut Maharashtra - Official News Portal', pageWidth / 2, footerY, { align: 'center' });
                
                const now = new Date();
                const timestamp = now.toLocaleDateString('en-IN') + ' ' + 
                                now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: false });
                pdf.text(timestamp, pageWidth - 15, footerY, { align: 'right' });
                pdf.text(`Generated by District Analytics`, 15, footerY);
                
                // Save PDF
                const fileName = 'district_news_distribution_' + 
                               (selectedRegion !== 'all' ? selectedRegion.toLowerCase().replace(/ /g, '_') + '_' : 'all_') + 
                               pdfFromDate.replace(/ /g, '_').toLowerCase() + '_to_' + 
                               pdfToDate.replace(/ /g, '_').toLowerCase() + '.pdf';
                pdf.save(fileName);
                
                // Clean up
                pdfChart.destroy();
                btn.innerHTML = originalText;
                btn.disabled = false;
                
            }, 500);
            
        } catch (error) {
            console.error('PDF generation error:', error);
            alert('PDF generation failed: ' + error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
    
    <?php endif; ?>
});
</script>

<style>
/* Header fixes */
.card-header h5 {
    flex: 1;
    min-width: 0;
}

.card-header h5 span {
    white-space: nowrap;
}

.card-header small {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Chart container */
.chart-container-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    padding: 10px;
}

.pie-chart-container {
    width: 100%;
    max-width: 500px;
    height: 400px;
    margin: 0 auto;
}

/* Legend container - with better readability */
.legend-container {
    height: 400px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #dee2e6;
}

.legend-header {
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.legend-scroll-container {
    height: 340px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Legend items - better spacing and readability */
.legend-item {
    padding: 8px 10px;
    margin-bottom: 5px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
    background-color: white;
}

.legend-item:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.legend-item.hover {
    background-color: #e3f2fd;
    border-color: #bbdefb;
}

.legend-item.hidden {
    opacity: 0.4;
    background-color: #f8f9fa;
}

.legend-color {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    margin-right: 12px;
    flex-shrink: 0;
    border: 2px solid white;
    box-shadow: 0 2px 3px rgba(0,0,0,0.15);
}

.legend-content {
    flex: 1;
    min-width: 0;
}

.legend-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 130px;
    letter-spacing: 0.2px;
}

.legend-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0d6efd;
    margin-left: 10px;
    background-color: #e3f2fd;
    padding: 2px 6px;
    border-radius: 3px;
    min-width: 35px;
    text-align: center;
}

.legend-percentage {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 3px;
    font-weight: 500;
}

/* Category badges */
.category-badge {
    font-size: 0.75rem !important;
    padding: 0.3em 0.5em !important;
    margin: 2px !important;
    border-radius: 4px !important;
}

.category-legend-container {
    max-height: 120px;
    transition: max-height 0.3s ease;
    overflow-y: auto;
    padding: 5px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.category-legend-container.collapsed {
    max-height: 45px;
    overflow: hidden;
}

/* Fullscreen modal */
.fullscreen-chart-container {
    width: 100%;
    max-width: 700px;
    height: 600px;
}

.fullscreen-legend-container {
    height: 600px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #dee2e6;
}

.fullscreen-legend-scroll {
    height: 570px;
    overflow-y: auto;
    padding-right: 10px;
}

.fullscreen-legend-item {
    margin-bottom: 6px;
    border-radius: 6px;
    transition: background-color 0.2s;
    padding: 8px 12px;
    background-color: white;
    border: 1px solid #dee2e6;
}

.fullscreen-legend-item:hover {
    background-color: #e9ecef;
    transform: translateX(3px);
    transition: all 0.2s;
}

.fullscreen-legend-color {
    width: 24px;
    height: 24px;
    border-radius: 5px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    margin-right: 15px;
}

.fullscreen-legend-item .fw-medium {
    font-size: 1rem;
    font-weight: 600;
}

.fullscreen-legend-item .text-muted {
    font-size: 0.9rem;
}

/* PDF Download Button */
#downloadPdfBtn, #fullscreenBtn {
    padding: 0.3rem 0.6rem;
    font-size: 0.85rem;
}

#downloadPdfBtn:hover, #fullscreenBtn:hover {
    background-color: #fff;
    color: #0d6efd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Scrollbars */
.legend-scroll-container::-webkit-scrollbar,
.fullscreen-legend-scroll::-webkit-scrollbar,
.category-legend-container::-webkit-scrollbar {
    width: 8px;
}

.legend-scroll-container::-webkit-scrollbar-track,
.fullscreen-legend-scroll::-webkit-scrollbar-track,
.category-legend-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.legend-scroll-container::-webkit-scrollbar-thumb,
.fullscreen-legend-scroll::-webkit-scrollbar-thumb,
.category-legend-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.legend-scroll-container::-webkit-scrollbar-thumb:hover,
.fullscreen-legend-scroll::-webkit-scrollbar-thumb:hover,
.category-legend-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .pie-chart-container {
        height: 380px;
        max-width: 450px;
    }
    
    .legend-container {
        height: 380px;
    }
    
    .legend-scroll-container {
        height: 320px;
    }
}

@media (max-width: 992px) {
    .chart-container-wrapper {
        min-height: 350px;
    }
    
    .pie-chart-container {
        height: 350px;
        max-width: 400px;
    }
    
    .legend-container {
        height: 350px;
        margin-top: 20px;
    }
    
    .legend-scroll-container {
        height: 290px;
    }
    
    .legend-label {
        max-width: 110px;
    }
    
    .legend-value {
        font-size: 0.85rem;
        min-width: 30px;
        padding: 1px 4px;
    }
}

@media (max-width: 768px) {
    .card-header {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .card-header h5 {
        flex-basis: 100%;
        margin-bottom: 5px;
    }
    
    .card-header .d-flex.gap-2 {
        position: absolute;
        top: 12px;
        right: 15px;
    }
    
    .chart-container-wrapper {
        min-height: 320px;
        padding: 5px;
    }
    
    .pie-chart-container {
        height: 320px;
        max-width: 350px;
    }
    
    .legend-container {
        height: 300px;
    }
    
    .legend-scroll-container {
        height: 240px;
    }
    
    .legend-label {
        max-width: 90px;
        font-size: 0.85rem;
    }
    
    .legend-value {
        font-size: 0.85rem;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        margin-right: 10px;
    }
}

@media (max-width: 576px) {
    .pie-chart-container {
        height: 300px;
        max-width: 320px;
    }
    
    .legend-container {
        height: 280px;
    }
    
    .legend-scroll-container {
        height: 220px;
    }
    
    .card-footer .row > div {
        margin-bottom: 5px;
    }
    
    .card-footer .col-sm-12 {
        margin-top: 5px;
    }
    
    .legend-label {
        max-width: 80px;
    }
}

/* Mobile specific */
@media (max-width: 768px) {
    .modal-dialog.modal-fullscreen {
        margin: 0;
        width: 100%;
        max-width: 100%;
    }
    
    .fullscreen-chart-container {
        height: 400px;
        max-width: 100%;
    }
    
    .fullscreen-legend-container {
        height: 300px;
        margin-top: 20px;
    }
    
    .fullscreen-legend-scroll {
        height: 270px;
    }
}

/* Fix header text wrapping */
@media (min-width: 768px) {
    .card-header h5 {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 5px;
    }
    
    .card-header h5 small {
        margin-left: auto;
        white-space: nowrap;
        text-align: right;
    }
}

/* Card footer compact */
.card-footer {
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.card-footer .row > div {
    padding: 0.25rem 0.5rem;
}

/* Animation for smooth hover effects */
.legend-item, .fullscreen-legend-item, #downloadPdfBtn, #fullscreenBtn {
    transition: all 0.2s ease-in-out;
}

/* Improve tooltip visibility */
.chartjs-tooltip {
    border-radius: 6px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

/* Chart border improvements for better separation */
canvas {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}
</style>