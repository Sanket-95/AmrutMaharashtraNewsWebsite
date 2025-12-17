<?php
// District Graph Component
// Get dates from parent (dashboard.php)
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-1 month'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');

// Initialize arrays for data
$districts = [];
$district_totals = [];
$category_data = [];
$views_data = [];

// Query to get district-wise news and category breakdown
$query = "SELECT 
            na.district_name,
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
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Process the data
    while ($row = $result->fetch_assoc()) {
        $district = $row['district_name'];
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

// Sort districts by total news (descending)
arsort($district_totals);

// Prepare data for Chart.js
$district_labels = array_keys($district_totals);
$news_counts = array_values($district_totals);

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
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-bar-chart me-2"></i>District-wise News Distribution
            <small class="float-end d-none d-md-inline">
                <?php echo date('d M Y', strtotime($from_date)); ?> to <?php echo date('d M Y', strtotime($to_date)); ?>
            </small>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($districts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No news articles found for the selected date range.</p>
            </div>
        <?php else: ?>
            <!-- Responsive container with scroll for mobile -->
            <div class="chart-responsive-container">
                <div class="chart-wrapper">
                    <canvas id="districtChart"></canvas>
                </div>
            </div>
            
            <!-- District count summary -->
            <div class="mt-3 text-center text-muted small">
                Showing <?php echo count($districts); ?> districts
                <?php if (count($districts) > 15): ?>
                    <span class="d-block d-sm-inline">- Scroll horizontally to view all</span>
                <?php endif; ?>
            </div>
            
            <!-- Compact category legend with show/hide -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted mb-0">Categories (<?php echo count($allCategories); ?>)</h6>
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
                <i class="bi bi-geo-alt me-1"></i> Districts: <?php echo count($districts); ?>
            </div>
            <div class="col-sm-4 mb-2 mb-sm-0">
                <i class="bi bi-newspaper me-1"></i> Total News: <?php echo array_sum($news_counts); ?>
            </div>
            <div class="col-sm-4">
                <i class="bi bi-eye me-1"></i> Hover for details
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($districts)): ?>
    const ctx = document.getElementById('districtChart').getContext('2d');
    
    // Data from PHP
    const districtLabels = <?php echo json_encode($district_labels); ?>;
    const newsCounts = <?php echo json_encode($news_counts); ?>;
    const hoverData = <?php echo json_encode($hover_data); ?>;
    const allCategories = <?php echo json_encode(array_values($allCategories)); ?>;
    
    // Check if we have many districts (for mobile optimization)
    const isManyDistricts = districtLabels.length > 20;
    
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
            ctx.font = 'bold 12px Arial';
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
    
    // Create chart with responsive settings
    const chart = new Chart(ctx, {
        type: 'bar',
        plugins: [barCountPlugin],
        data: {
            labels: districtLabels,
            datasets: [{
                label: 'Total News',
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
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: window.innerWidth < 768 ? 'y' : 'x',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const district = context.label;
                            const total = context.raw;
                            return `Total News: ${total}`;
                        },
                        afterLabel: function(context) {
                            const district = context.label;
                            const categories = hoverData[district];
                            
                            if (!categories || categories.length === 0) {
                                return '';
                            }
                            
                            let tooltipText = '\nCategory Breakdown:\n';
                            const maxCategories = window.innerWidth < 576 ? 5 : 10;
                            const displayedCategories = categories.slice(0, maxCategories);
                            const remaining = categories.length - maxCategories;
                            
                            displayedCategories.forEach((item, index) => {
                                const color = categoryColorMap[item.category] || '#999';
                                
                                // Create colored square and category info
                                tooltipText += `■ ${item.category}: ${item.count} news (${item.views} views)\n`;
                            });
                            
                            if (remaining > 0) {
                                tooltipText += `... and ${remaining} more categories\n`;
                            }
                            
                            return tooltipText;
                        },
                        footer: function(context) {
                            const district = context[0].label;
                            const total = context[0].raw;
                            const categories = hoverData[district] || [];
                            return `${categories.length} categories | Total Views: ${categories.reduce((sum, cat) => sum + (cat.views || 0), 0)}`;
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    titleColor: '#fff',
                    bodyColor: function(context) {
                        // Color each line based on category
                        if (context.dataIndex >= 0) {
                            const district = districtLabels[context.dataIndex];
                            const categories = hoverData[district] || [];
                            const lineIndex = context.dataIndex - 1; // Adjust for label line
                            
                            if (lineIndex >= 0 && lineIndex < categories.length) {
                                const category = categories[lineIndex].category;
                                return categoryColorMap[category] || '#fff';
                            }
                        }
                        return '#fff';
                    },
                    footerColor: '#36A2EB',
                    padding: 12,
                    displayColors: false, // We'll handle colors manually
                    bodyFont: {
                        size: window.innerWidth < 576 ? 11 : 12
                    },
                    titleFont: {
                        size: window.innerWidth < 576 ? 12 : 13
                    },
                    footerFont: {
                        size: window.innerWidth < 576 ? 10 : 11
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '10%', // Add 10% grace at top to prevent cutting
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
                            size: window.innerWidth < 576 ? 10 : 11
                        },
                        maxTicksLimit: isManyDistricts ? 5 : 8
                    },
                    title: {
                        display: true,
                        text: 'Number of News Articles',
                        font: {
                            size: window.innerWidth < 576 ? 11 : 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: window.innerWidth < 576 ? 9 : 10
                        },
                        maxRotation: window.innerWidth < 768 ? 0 : 45,
                        minRotation: 0,
                        maxTicksLimit: isManyDistricts ? 15 : 30,
                        callback: function(value, index) {
                            const label = this.getLabelForValue(value);
                            if (window.innerWidth < 576 && label.length > 12) {
                                return label.substring(0, 10) + '...';
                            }
                            if (window.innerWidth < 768 && label.length > 15) {
                                return label.substring(0, 13) + '...';
                            }
                            return label;
                        }
                    }
                }
            },
            onClick: function(evt, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const district = districtLabels[index];
                    console.log('Clicked on district:', district);
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            barPercentage: isManyDistricts ? 0.6 : 0.8,
            categoryPercentage: isManyDistricts ? 0.7 : 0.9
        }
    });
    
    // Handle window resize for better mobile experience
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            chart.options.indexAxis = window.innerWidth < 768 ? 'y' : 'x';
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
    min-height: 400px;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 10px;
}

.chart-wrapper {
    min-width: 600px;
    position: relative;
    height: 400px;
}

/* For mobile, make the chart wrapper larger to accommodate many bars */
@media (max-width: 768px) {
    .chart-responsive-container {
        min-height: 450px;
    }
    
    .chart-wrapper {
        min-width: 800px;
        height: 450px;
    }
}

@media (max-width: 576px) {
    .chart-responsive-container {
        min-height: 500px;
        padding: 5px;
    }
    
    .chart-wrapper {
        min-width: 1000px;
        height: 500px;
    }
}

/* Category badges */
.category-badge {
    font-size: 0.7rem;
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

/* Scrollbar styling */
.chart-responsive-container::-webkit-scrollbar {
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
}

/* Chart.js tooltip custom colors */
.chartjs-tooltip-key {
    display: inline-block;
    width: 10px;
    height: 10px;
    margin-right: 5px;
}
</style>