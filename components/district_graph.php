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

// Sort English districts by total news (descending) for PDF
arsort($english_district_totals);

// Prepare data for Chart.js (Marathi labels)
$district_labels = array_keys($district_totals);
$news_counts = array_values($district_totals);

// Find the longest district name for dynamic sizing
$max_label_length = 0;
foreach ($district_labels as $label) {
    $max_label_length = max($max_label_length, mb_strlen($label, 'UTF-8'));
}

// Prepare English data for PDF
$english_district_labels = array_keys($english_district_totals);
$english_news_counts = array_values($english_district_totals);

// Prepare hover data (category breakdown)
$hover_data = [];
foreach ($district_totals as $district => $total) {
    $category_items = [];
    foreach ($category_data[$district] as $category => $count) {
        $views = $views_data[$district][$category] ?? 0;
        $category_items[] = [
            'category' => $category,
            'count' => $count,
            'views' => $views
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

// Calculate dynamic height based on number of districts
$district_count = count($districts);
$chart_height = max(400, $district_count * 40); // Minimum 400px, 40px per district

// Calculate dynamic width based on longest label and number of districts
$base_width_per_district = 70; // Base width per district
$additional_width_for_long_labels = min(30, ($max_label_length - 10) * 3); // Extra width for long labels
$chart_wrapper_min_width = max(600, $district_count * ($base_width_per_district + $additional_width_for_long_labels));

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
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-bar-chart me-2"></i>
            <?php if ($selected_region != 'all'): ?>
                <?php echo htmlspecialchars($selected_region); ?> प्रदेश - 
            <?php endif; ?>
            जिल्हावार बातमी वितरण
            <small class="float-end d-none d-md-inline">
                <?php echo date('d M Y', strtotime($from_date)); ?> ते <?php echo date('d M Y', strtotime($to_date)); ?>
                <?php if ($selected_region != 'all'): ?>
                    <br><span class="fst-italic">फिल्टर: <?php echo htmlspecialchars($selected_region); ?> प्रदेश</span>
                <?php endif; ?>
            </small>
        </h5>
        
        <!-- PDF Download Button -->
        <?php if (!empty($districts)): ?>
        <button type="button" class="btn btn-light btn-sm" id="downloadPdfBtn" title="Download as PDF">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </button>
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
            <!-- Responsive container with scroll for mobile -->
            <div class="chart-responsive-container">
                <div class="chart-wrapper" style="min-width: <?php echo $chart_wrapper_min_width; ?>px; height: <?php echo $chart_height; ?>px;">
                    <canvas id="districtChart"></canvas>
                </div>
            </div>
            
            <!-- Hidden canvas for PDF generation (always desktop view) -->
            <div style="display: none;">
                <canvas id="pdfChartCanvas" width="1200" height="600"></canvas>
            </div>
            
            <!-- District count summary -->
            <div class="mt-3 text-center text-muted small">
                दाखवत आहे <?php echo count($districts); ?> जिल्हे
                <?php if ($selected_region != 'all'): ?>
                    <?php echo htmlspecialchars($selected_region); ?> प्रदेशात
                <?php endif; ?>
                <?php if (count($districts) > 10): ?>
                    <span class="d-block d-sm-inline">- सर्व पाहण्यासाठी क्षैतिज स्क्रोल करा</span>
                <?php endif; ?>
            </div>
            
            <!-- Compact category legend with show/hide -->
            <div class="mt-4">
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
    <div class="card-footer text-muted small">
        <div class="row">
            <div class="col-sm-4 mb-2 mb-sm-0">
                <i class="bi bi-geo-alt me-1"></i> 
                <?php if ($selected_region != 'all'): ?>
                    <?php echo htmlspecialchars($selected_region); ?>: 
                <?php endif; ?>
                <?php echo count($districts); ?> जिल्हे
            </div>
            <div class="col-sm-4 mb-2 mb-sm-0">
                <i class="bi bi-newspaper me-1"></i> एकूण बातम्या: <?php echo array_sum($news_counts); ?>
            </div>
            <div class="col-sm-4">
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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- jsPDF Library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
// Make jsPDF available globally
window.jsPDF = window.jspdf.jsPDF;

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($districts)): ?>
    const ctx = document.getElementById('districtChart').getContext('2d');
    const pdfCtx = document.getElementById('pdfChartCanvas').getContext('2d');
    
    // Data from PHP
    const districtLabels = <?php echo json_encode($district_labels); ?>;
    const newsCounts = <?php echo json_encode($news_counts); ?>;
    const hoverData = <?php echo json_encode($hover_data); ?>;
    const allCategories = <?php echo json_encode(array_values($allCategories)); ?>;
    const selectedRegion = "<?php echo $selected_region; ?>";
    const fromDate = "<?php echo date('d M Y', strtotime($from_date)); ?>";
    const toDate = "<?php echo date('d M Y', strtotime($to_date)); ?>";
    const totalDistricts = <?php echo count($districts); ?>;
    const totalNews = <?php echo array_sum($news_counts); ?>;
    const maxLabelLength = <?php echo $max_label_length; ?>;
    
    // English data for PDF
    const englishDistrictLabels = <?php echo json_encode($english_district_labels); ?>;
    const englishNewsCounts = <?php echo json_encode($english_news_counts); ?>;
    
    // PDF data
    const pdfTitle = "<?php echo $pdf_title; ?>";
    const pdfFromDate = "<?php echo $pdf_from_date; ?>";
    const pdfToDate = "<?php echo $pdf_to_date; ?>";
    
    // Check if we have many districts (for mobile optimization)
    const isManyDistricts = districtLabels.length > 15;
    
    // Calculate dynamic settings based on number of districts and label length
    const maxTicksLimitDesktop = Math.min(40, districtLabels.length); // Show all labels up to 40
    const maxTicksLimitMobile = Math.min(30, districtLabels.length); // Show all labels up to 30 on mobile
    
    // Calculate font size based on longest label
    const calculateFontSize = (isMobile) => {
        if (isMobile) {
            // Mobile: larger base font
            return Math.max(10, 12 - Math.max(0, maxLabelLength - 12) * 0.3);
        } else {
            // Desktop: adjust based on label length
            if (maxLabelLength <= 10) return 12;
            if (maxLabelLength <= 15) return 11;
            if (maxLabelLength <= 20) return 10;
            return 9; // For very long labels like "चत्रपती संभाजीनगर"
        }
    };
    
    // Generate colors for categories
    const categoryColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#8AC926', '#1982C4', '#6A4C93', '#FF595E',
        '#6D6875', '#B5838D', '#E5989B', '#FFB4A2', '#FFCDB2'
    ];
    
    // Map categories to colors
    const categoryColorMap = {};
    allCategories.forEach((category, index) => {
        categoryColorMap[category] = categoryColors[index % categoryColors.length];
    });
    
    // Create category legend
    const legendContainer = document.getElementById('categoryLegend');
    allCategories.forEach((category, index) => {
        const color = categoryColors[index % categoryColors.length];
        const legendItem = document.createElement('span');
        legendItem.className = 'category-badge';
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
    
    // Custom plugin to show total count on top of bars
    const barCountPlugin = {
        id: 'barCountPlugin',
        afterDatasetsDraw(chart, args, options) {
            const { ctx, data, chartArea: { top, bottom, left, right }, scales } = chart;
            
            ctx.save();
            ctx.font = 'bold 13px Arial, sans-serif';
            ctx.fillStyle = '#333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            
            data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                
                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index];
                    
                    // Only show count if value > 0 and bar is visible
                    if (value > 0) {
                        if (chart.options.indexAxis === 'x') {
                            // Vertical bars (desktop)
                            const x = bar.x;
                            const y = bar.y - 8; // Position above the bar
                            
                            // Check if bar is within chart area
                            const barTop = bar.y;
                            const barBottom = scales.y.getPixelForValue(0);
                            
                            // Only draw count if bar is tall enough and within bounds
                            if (barTop > top + 25 && barBottom > top + 25) {
                                // Add background for better readability
                                ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                                ctx.fillRect(x - 15, y - 18, 30, 18);
                                
                                // Draw border
                                ctx.strokeStyle = '#ddd';
                                ctx.lineWidth = 1;
                                ctx.strokeRect(x - 15, y - 18, 30, 18);
                                
                                // Draw the count
                                ctx.fillStyle = '#333';
                                ctx.fillText(value, x, y);
                            }
                        } else {
                            // Horizontal bars (mobile)
                            const x = bar.x + 25; // Position to the right of bar
                            const y = bar.y;
                            
                            // Check if bar is within chart area
                            const barRight = bar.x;
                            const barWidth = Math.abs(barRight - scales.x.getPixelForValue(0));
                            
                            // Only draw count if bar is wide enough and within bounds
                            if (barWidth > 40 && x < right - 40) {
                                // Add background for better readability
                                ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                                ctx.fillRect(x - 25, y - 9, 40, 18);
                                
                                // Draw border
                                ctx.strokeStyle = '#ddd';
                                ctx.lineWidth = 1;
                                ctx.strokeRect(x - 25, y - 9, 40, 18);
                                
                                // Draw the count
                                ctx.fillStyle = '#333';
                                ctx.fillText(value, x, y + 4);
                            }
                        }
                    }
                });
            });
            ctx.restore();
        }
    };
    
    // Custom plugin for PDF chart to show bar values
    const pdfBarCountPlugin = {
        id: 'pdfBarCountPlugin',
        afterDatasetsDraw(chart, args, options) {
            const { ctx, data, chartArea: { top, bottom, left, right }, scales } = chart;
            
            ctx.save();
            ctx.font = 'bold 14px Arial, sans-serif';
            ctx.fillStyle = '#333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            
            data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                
                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index];
                    
                    // Only show count if value > 0 and bar is visible
                    if (value > 0) {
                        const x = bar.x;
                        const y = bar.y - 10; // Position above the bar
                        
                        // Check if bar is within chart area
                        const barTop = bar.y;
                        const barBottom = scales.y.getPixelForValue(0);
                        
                        // Only draw count if bar is tall enough
                        if (barTop > top + 30 && barBottom > top + 30) {
                            // Add background for better readability
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                            ctx.fillRect(x - 20, y - 20, 40, 20);
                            
                            // Draw border
                            ctx.strokeStyle = '#ddd';
                            ctx.lineWidth = 1;
                            ctx.strokeRect(x - 20, y - 20, 40, 20);
                            
                            // Draw the count
                            ctx.fillStyle = '#333';
                            ctx.fillText(value, x, y);
                        }
                    }
                });
            });
            ctx.restore();
        }
    };
    
    // Function to create chart options
    function getChartOptions(isMobile) {
        const fontSize = calculateFontSize(isMobile);
        const rotationAngle = isMobile ? 0 : 45;
        const paddingBottom = isMobile ? 20 : (maxLabelLength > 15 ? 50 : 40);
        
        return {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: isMobile ? 'y' : 'x',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const district = context.label;
                            const total = context.raw;
                            return `एकूण बातम्या: ${total}`;
                        },
                        afterLabel: function(context) {
                            const district = context.label;
                            const categories = hoverData[district];
                            
                            if (!categories || categories.length === 0) {
                                return '';
                            }
                            
                            let tooltipText = '\nवर्गवार विभागणी:\n';
                            const maxCategories = isMobile && window.innerWidth < 576 ? 5 : 10;
                            const displayedCategories = categories.slice(0, maxCategories);
                            const remaining = categories.length - maxCategories;
                            
                            displayedCategories.forEach((item, index) => {
                                const color = categoryColorMap[item.category] || '#999';
                                
                                // Create colored square and category info
                                tooltipText += `■ ${item.category}: ${item.count} बातम्या (${item.views} दृश्ये)\n`;
                            });
                            
                            if (remaining > 0) {
                                tooltipText += `... आणि ${remaining} अधिक वर्ग\n`;
                            }
                            return tooltipText;
                        },
                        footer: function(context) {
                            const district = context[0].label;
                            const total = context[0].raw;
                            const categories = hoverData[district] || [];
                            const totalViews = categories.reduce((sum, cat) => sum + (cat.views || 0), 0);
                            
                            if (selectedRegion === 'all') {
                                return `${categories.length} वर्ग | एकूण दृश्ये: ${totalViews}`;
                            } else {
                                return `${categories.length} वर्ग | एकूण दृश्ये: ${totalViews} | प्रदेश: ${selectedRegion}`;
                            }
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    titleColor: '#fff',
                    bodyColor: function(context) {
                        if (context.dataIndex >= 0) {
                            const district = districtLabels[context.dataIndex];
                            const categories = hoverData[district] || [];
                            const lineIndex = context.dataIndex - 1;
                            
                            if (lineIndex >= 0 && lineIndex < categories.length) {
                                const category = categories[lineIndex].category;
                                return categoryColorMap[category] || '#fff';
                            }
                        }
                        return '#fff';
                    },
                    footerColor: '#36A2EB',
                    padding: 12,
                    displayColors: false,
                    bodyFont: {
                        size: isMobile && window.innerWidth < 576 ? 12 : 13,
                        weight: '500',
                        family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                    },
                    titleFont: {
                        size: isMobile && window.innerWidth < 576 ? 13 : 14,
                        weight: '600',
                        family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                    },
                    footerFont: {
                        size: isMobile && window.innerWidth < 576 ? 11 : 12,
                        weight: '500',
                        family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '10%',
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        },
                        font: {
                            size: isMobile && window.innerWidth < 576 ? 12 : 13,
                            weight: '600',
                            family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                        },
                        maxTicksLimit: 8,
                        padding: 8
                    },
                    title: {
                        display: true,
                        text: 'बातम्यांची संख्या',
                        font: {
                            size: isMobile && window.innerWidth < 576 ? 14 : 15,
                            weight: '700',
                            family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: fontSize,
                            weight: '600',
                            family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                        },
                        maxRotation: rotationAngle,
                        minRotation: rotationAngle,
                        maxTicksLimit: isMobile ? maxTicksLimitMobile : maxTicksLimitDesktop,
                        autoSkip: false,
                        padding: 5,
                        callback: function(value, index) {
                            const label = this.getLabelForValue(value);
                            
                            // For very long labels, show full text with smaller font
                            if (isMobile) {
                                if (label.length > 12) {
                                    return label.substring(0, 11) + '...';
                                }
                            } else {
                                // Desktop: with 45 degree rotation, we can show more text
                                if (label.length > 25) {
                                    return label.substring(0, 24) + '...';
                                }
                            }
                            return label;
                        }
                    },
                    offset: true,
                    afterFit: function(scale) {
                        if (!isMobile) {
                            // More height for long labels on desktop
                            scale.height = maxLabelLength > 15 ? 100 : 80;
                        }
                    }
                }
            },
            onClick: function(evt, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const district = districtLabels[index];
                    console.log('Clicked on district:', district);
                    // You could add functionality here to drill down to district details
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            // FIXED: Dynamic bar width based on number of districts
            barPercentage: isMobile ? 
                (districtLabels.length > 20 ? 0.8 : 0.9) : 
                (districtLabels.length > 20 ? 0.6 : 0.7),
            categoryPercentage: isMobile ? 
                (districtLabels.length > 20 ? 0.85 : 0.9) : 
                (districtLabels.length > 20 ? 0.7 : 0.8),
            layout: {
                padding: {
                    bottom: paddingBottom
                }
            }
        };
    }
    
    // Create main chart for display
    const isMobileInitial = window.innerWidth < 768;
    const chart = new Chart(ctx, {
        type: 'bar',
        plugins: [barCountPlugin],
        data: {
            labels: districtLabels,
            datasets: [{
                label: selectedRegion === 'all' ? 'एकूण बातम्या' : selectedRegion + ' प्रदेशातील बातम्या',
                data: newsCounts,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return '#36A2EB';
                    
                    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0, '#36A2EB');
                    gradient.addColorStop(1, '#2a7fc1');
                    return gradient;
                },
                borderColor: '#1a6fb3',
                borderWidth: 1,
                borderRadius: 3,
                borderSkipped: false,
                hoverBackgroundColor: '#1a6fb3'
            }]
        },
        options: getChartOptions(isMobileInitial)
    });
    
    // Force font updates after chart initialization
    setTimeout(function() {
        if (chart && chart.options && chart.options.scales) {
            // Update X-axis font
            if (chart.options.scales.x && chart.options.scales.x.ticks) {
                const isMobile = window.innerWidth < 768;
                const fontSize = calculateFontSize(isMobile);
                chart.options.scales.x.ticks.font = {
                    size: fontSize,
                    weight: '600',
                    family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                };
            }
            
            // Update Y-axis font
            if (chart.options.scales.y && chart.options.scales.y.ticks) {
                chart.options.scales.y.ticks.font = {
                    size: window.innerWidth < 768 ? 13 : 14,
                    weight: '600',
                    family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                };
            }
            
            // Update Y-axis title font
            if (chart.options.scales.y && chart.options.scales.y.title) {
                chart.options.scales.y.title.font = {
                    size: window.innerWidth < 768 ? 14 : 15,
                    weight: '700',
                    family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                };
            }
            
            chart.update('none');
        }
    }, 100);
    
    // Create PDF chart instance (always desktop view)
    let pdfChart = null;
    
    function createPdfChart() {
        if (pdfChart) {
            pdfChart.destroy();
        }
        
        // Calculate font size for PDF based on longest label
        const pdfFontSize = maxLabelLength <= 10 ? 10 : 
                           maxLabelLength <= 15 ? 9 : 
                           maxLabelLength <= 20 ? 8 : 7;
        
        pdfChart = new Chart(pdfCtx, {
            type: 'bar',
            plugins: [pdfBarCountPlugin],
            data: {
                labels: districtLabels,
                datasets: [{
                    label: selectedRegion === 'all' ? 'एकूण बातम्या' : selectedRegion + ' प्रदेशातील बातम्या',
                    data: newsCounts,
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return '#36A2EB';
                        
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, '#36A2EB');
                        gradient.addColorStop(1, '#2a7fc1');
                        return gradient;
                    },
                    borderColor: '#1a6fb3',
                    borderWidth: 1,
                    borderRadius: 3,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '10%',
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            },
                            font: {
                                size: 12,
                                weight: '600',
                                family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                            },
                            maxTicksLimit: 8,
                            padding: 8
                        },
                        title: {
                            display: true,
                            text: 'बातम्यांची संख्या',
                            font: {
                                size: 14,
                                weight: '700',
                                family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: pdfFontSize,
                                weight: '600',
                                family: "'Segoe UI', 'Roboto', 'Arial', sans-serif"
                            },
                            maxRotation: 45,
                            minRotation: 45,
                            maxTicksLimit: maxTicksLimitDesktop,
                            autoSkip: false,
                            padding: 5,
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                // PDF can handle longer labels with smaller font
                                if (label.length > 30) {
                                    return label.substring(0, 29) + '...';
                                }
                                return label;
                            }
                        },
                        offset: true,
                        afterFit: function(scale) {
                            scale.height = maxLabelLength > 15 ? 120 : 100;
                        }
                    }
                },
                animation: false,
                barPercentage: districtLabels.length > 20 ? 0.6 : 0.7,
                categoryPercentage: districtLabels.length > 20 ? 0.7 : 0.8,
                layout: {
                    padding: {
                        bottom: maxLabelLength > 15 ? 70 : 60
                    }
                }
            }
        });
    }
    
    // Create PDF chart initially
    createPdfChart();
    
    // PDF Download Functionality
    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        // Show loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating PDF...';
        this.disabled = true;
        
        try {
            // Update PDF chart with current data
            createPdfChart();
            
            // Get PDF chart image data
            const pdfChartImage = document.getElementById('pdfChartCanvas').toDataURL('image/png');
            
            // Create PDF in landscape mode
            const pdf = new jsPDF('landscape', 'mm', 'a4');
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            
            // Calculate chart height based on label length
            const baseChartHeight = 120;
            const extraHeightForLongLabels = maxLabelLength > 15 ? 30 : 20;
            const totalChartHeight = baseChartHeight + extraHeightForLongLabels;
            
            // Add header
            pdf.setFontSize(20);
            pdf.setFont("helvetica", "bold");
            pdf.setTextColor(13, 110, 253);
            
            pdf.text(pdfTitle, pageWidth / 2, 15, { align: 'center' });
            
            // Add date range
            pdf.setFontSize(12);
            pdf.setFont("helvetica", "normal");
            pdf.setTextColor(108, 117, 125);
            pdf.text(`Date: ${pdfFromDate} to ${pdfToDate}`, pageWidth / 2, 22, { align: 'center' });
            
            // Add summary info
            pdf.setFontSize(11);
            pdf.setFont("helvetica", "normal");
            pdf.setTextColor(33, 37, 41);
            
            const summaryY = 28;
            
            pdf.text(`• Districts: ${totalDistricts}`, 20, summaryY);
            pdf.text(`• Total News: ${totalNews}`, pageWidth / 2, summaryY, { align: 'center' });
            pdf.text(`• Categories: ${allCategories.length}`, pageWidth - 20, summaryY, { align: 'right' });
            
            const regionY = summaryY + 6;
            if (selectedRegion === 'all') {
                pdf.text(`• Region: All Regions`, pageWidth / 2, regionY, { align: 'center' });
            } else {
                const regionName = selectedRegion.charAt(0).toUpperCase() + selectedRegion.slice(1);
                pdf.text(`• Region: ${regionName}`, pageWidth / 2, regionY, { align: 'center' });
            }
            
            // Position chart with extra space for long labels
            const chartY = regionY + 12;
            const chartWidth = pageWidth - 40;
            
            // Add chart to PDF
            pdf.addImage(pdfChartImage, 'PNG', 20, chartY, chartWidth, totalChartHeight);
            
            // Add data table if space permits
            const tableStartY = chartY + totalChartHeight + 15;
            
            if (tableStartY < pageHeight - 60 && englishDistrictLabels.length <= 12) {
                pdf.setFontSize(10);
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(255, 255, 255);
                pdf.setFillColor(13, 110, 253);
                
                pdf.rect(20, tableStartY, pageWidth - 40, 8, 'F');
                pdf.text('District', 25, tableStartY + 6);
                pdf.text('News Count', pageWidth - 25, tableStartY + 6, { align: 'right' });
                
                pdf.setFont("helvetica", "normal");
                pdf.setTextColor(33, 37, 41);
                
                let rowY = tableStartY + 16;
                let rowIndex = 0;
                
                const displayCount = Math.min(12, englishDistrictLabels.length);
                
                for (let i = 0; i < displayCount; i++) {
                    if (rowY > pageHeight - 35) break;
                    
                    if (rowIndex % 2 === 0) {
                        pdf.setFillColor(248, 249, 250);
                        pdf.rect(20, rowY - 4, pageWidth - 40, 8, 'F');
                    }
                    
                    pdf.text(englishDistrictLabels[i], 25, rowY);
                    pdf.text(englishNewsCounts[i].toString(), pageWidth - 25, rowY, { align: 'right' });
                    
                    rowY += 8;
                    rowIndex++;
                }
                
                if (englishDistrictLabels.length > displayCount && rowY < pageHeight - 25) {
                    pdf.setFont("helvetica", "italic");
                    pdf.text(`... and ${englishDistrictLabels.length - displayCount} more districts`, pageWidth / 2, rowY + 5, { align: 'center' });
                }
            }
            
            // Add footer
            pdf.setFontSize(9);
            pdf.setTextColor(108, 117, 125);
            pdf.setFont("helvetica", "italic");
            
            const footerY = pageHeight - 12;
            
            pdf.text('Amrut Maharashtra - Official News Portal', pageWidth / 2, footerY, { align: 'center' });
            
            const now = new Date();
            const timestamp = now.toLocaleDateString('en-IN') + ' ' + now.toLocaleTimeString('en-IN', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
            pdf.setFontSize(8);
            pdf.text(`Generated: ${timestamp}`, pageWidth - 20, footerY, { align: 'right' });
            
            const pageCount = pdf.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                pdf.setPage(i);
                pdf.text(`Page ${i} of ${pageCount}`, 20, footerY);
            }
            
            // Save the PDF
            const fileName = 'district_news_report_' + 
                           (selectedRegion !== 'all' ? selectedRegion.toLowerCase() + '_' : 'all_') + 
                           fromDate.replace(/ /g, '_') + '_to_' + 
                           toDate.replace(/ /g, '_') + '.pdf';
            pdf.save(fileName);
            
        } catch (error) {
            console.error('PDF generation error:', error);
            alert('PDF generation failed. Please try again.');
        } finally {
            this.innerHTML = originalText;
            this.disabled = false;
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const isMobile = window.innerWidth < 768;
            chart.options = getChartOptions(isMobile);
            chart.options.indexAxis = isMobile ? 'y' : 'x';
            chart.update('none');
        }, 250);
    });
    
    // Initialize legend collapsed on mobile
    if (window.innerWidth < 768 && allCategories.length > 8) {
        document.getElementById('categoryLegendContainer').classList.add('collapsed');
        document.querySelector('#toggleLegend i').classList.remove('bi-chevron-down');
        document.querySelector('#toggleLegend i').classList.add('bi-chevron-up');
    }
    <?php endif; ?>
});
</script>

<style>
/* Responsive chart container */
.chart-responsive-container {
    width: 100%;
    overflow-x: auto;
    position: relative;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 10px;
    max-height: 600px;
    overflow-y: auto;
}

.chart-wrapper {
    position: relative;
    /* Dynamic width and height set inline via PHP */
}

/* For mobile, adjust minimum width */
@media (max-width: 768px) {
    .chart-responsive-container {
        min-height: 300px;
        max-height: 500px;
    }
    
    .chart-wrapper {
        min-width: 600px;
    }
}

@media (max-width: 576px) {
    .chart-responsive-container {
        min-height: 350px;
        max-height: 450px;
        padding: 5px;
    }
    
    .chart-wrapper {
        min-width: 700px;
    }
}

/* Category badges */
.category-badge {
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    padding: 0.25em 0.5em;
    border-radius: 4px;
    cursor: default;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    margin: 2px;
    transition: opacity 0.2s;
}

.category-legend-container.collapsed {
    max-height: 60px;
    overflow: hidden;
    position: relative;
}

.category-legend-container.collapsed::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(to bottom, transparent, white);
}

#toggleLegend {
    padding: 0.15rem 0.4rem;
    font-size: 0.8rem;
}

/* PDF Download Button Styles */
#downloadPdfBtn {
    font-size: 0.875rem;
    padding: 0.25rem 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s;
}

#downloadPdfBtn:hover {
    background-color: #fff;
    color: #0d6efd;
    border-color: #fff;
}

#downloadPdfBtn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#downloadPdfBtn i {
    margin-right: 0.25rem;
}

/* Scrollbar styling */
.chart-responsive-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.chart-responsive-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.chart-responsive-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.chart-responsive-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Card adjustments for mobile */
@media (max-width: 768px) {
    .card-header small.float-end {
        display: block !important;
        float: none !important;
        margin-top: 0.5rem;
        font-size: 0.75rem;
    }
    
    .card-footer .row > div {
        text-align: center !important;
    }
    
    #downloadPdfBtn {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 1;
    }
    
    .card-header {
        position: relative;
        padding-right: 80px !important;
    }
    
    .card-header h5 {
        padding-right: 50px;
        font-weight: 700 !important;
        font-size: 1.25rem !important;
    }
}

/* Chart.js font improvements */
@media (min-width: 768px) {
    /* Desktop view - vertical bars with 45 degree labels */
    .chartjs-render-monitor .chartjs-scale-x .chartjs-tick {
        font-size: 11px !important;
        font-weight: 600 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* Y-axis labels */
    .chartjs-render-monitor .chartjs-scale-y .chartjs-tick {
        font-size: 13px !important;
        font-weight: 600 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* Y-axis title */
    .chartjs-render-monitor .chartjs-scale-y .chartjs-axis-title {
        font-size: 15px !important;
        font-weight: 700 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* Bar value labels (on top of bars) */
    .chartjs-render-monitor .chartjs-datalabels {
        font-size: 13px !important;
        font-weight: 700 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
}

@media (max-width: 767px) {
    /* Mobile view - horizontal bars */
    .chartjs-render-monitor .chartjs-scale-y .chartjs-tick {
        font-size: 12px !important;
        font-weight: 600 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* X-axis labels for mobile (horizontal bars) */
    .chartjs-render-monitor .chartjs-scale-x .chartjs-tick {
        font-size: 11px !important;
        font-weight: 600 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* X-axis title for mobile */
    .chartjs-render-monitor .chartjs-scale-x .chartjs-axis-title {
        font-size: 14px !important;
        font-weight: 700 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
    
    /* Bar value labels for mobile */
    .chartjs-render-monitor .chartjs-datalabels {
        font-size: 12px !important;
        font-weight: 700 !important;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
    }
}

/* Make rotated labels more readable with bolder font */
.chartjs-tick-text {
    text-shadow: 0.5px 0.5px 0.5px rgba(255, 255, 255, 0.8) !important;
}

/* Improve tooltip font */
.chartjs-tooltip {
    font-size: 13px !important;
    font-weight: 500 !important;
    font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif !important;
}

/* For better label display when there are many bars */
@media (min-width: 768px) {
    .chart-wrapper canvas {
        width: 100% !important;
    }
    
    /* Ensure labels are visible */
    .chartjs-render-monitor {
        overflow: visible !important;
    }
    
    /* Style for cross-angle labels */
    .chartjs-axis-x .chartjs-tick {
        transform: rotate(45deg);
        transform-origin: top left;
        text-align: start;
    }
}

/* Adjust bar spacing for many districts */
@media (max-width: 767px) and (min-width: 576px) {
    .chart-wrapper {
        min-width: 800px;
    }
}

/* Ensure chart area is visible */
.chart-container {
    overflow: visible !important;
}

.chart-wrapper {
    overflow: visible !important;
}

/* Adjust for very long labels */
.long-label-chart {
    min-width: 900px !important;
}

/* Summary text in footer */
.card-footer {
    font-weight: 500 !important;
    font-size: 0.85rem !important;
}
</style>