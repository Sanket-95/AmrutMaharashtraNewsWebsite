<?php
// ads_analytics.php - Complete Ad Analytics Dashboard
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Get date range filter from URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if ($start_date > $end_date) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

// ============== SUMMARY STATISTICS WITH DATE RANGE ==============
$stats = [];

// Total ads count within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['total_ads'] = $result->fetch_assoc()['total'];

// Active ads count within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE is_active = 1 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['active_ads'] = $result->fetch_assoc()['total'];

// Inactive ads count within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE is_active = 0 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['inactive_ads'] = $result->fetch_assoc()['total'];

// Total revenue within date range (paid ads only)
$result = $conn->query("SELECT SUM(price) as total FROM ads_management WHERE payment_status = 1 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Paid ads count within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE payment_status = 1 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['paid_ads'] = $result->fetch_assoc()['total'];

// Pending payment ads within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE payment_status = 0 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['pending_payment'] = $result->fetch_assoc()['total'];

// Primary ads within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE ad_type = 1 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['primary_ads'] = $result->fetch_assoc()['total'];

// Secondary ads within date range
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE ad_type = 2 AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$stats['secondary_ads'] = $result->fetch_assoc()['total'];

// ============== MONTHLY REVENUE CHART DATA (Last 6 months with date range context) ==============
$monthly_revenue = [];
$months = [];

for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $months[] = $month_name;
    
    // Filter by both date range and month
    $result = $conn->query("SELECT SUM(price) as total FROM ads_management 
                           WHERE payment_status = 1 
                           AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                           AND DATE(created_at) BETWEEN '$month_start' AND '$month_end'");
    $revenue = $result->fetch_assoc()['total'] ?? 0;
    $monthly_revenue[] = $revenue;
}

// ============== AD TYPE DISTRIBUTION WITH DATE RANGE ==============
$ad_types = [
    'primary' => $stats['primary_ads'],
    'secondary' => $stats['secondary_ads']
];

// Format dates for display
$formatted_start = date('d M Y', strtotime($start_date));
$formatted_end = date('d M Y', strtotime($end_date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ads Analytics Dashboard - Amrut Maharashtra</title>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <style>
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.2;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            height: 400px;
            position: relative;
            overflow: hidden;
        }
        .pie-chart-wrapper {
            height: 220px;
            position: relative;
            margin-bottom: 10px;
        }
        .legend-wrapper {
            padding: 10px 0 0 0;
            border-top: 1px solid #eee;
        }
        .legend-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 10px;
        }
        .legend-label {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #555;
        }
        .legend-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* Compact Date Picker Styles */
        .compact-date-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .date-range-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        .compact-date-input {
            background: white;
            border: none;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            color: #333;
            outline: none;
            width: 140px;
        }
        .compact-date-input::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
        }
        .compact-apply-btn {
            background: #ff9800;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            white-space: nowrap;
            cursor: pointer;
        }
        .compact-apply-btn:hover {
            background: #f57c00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        }
        
        /* Header layout */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }
        .dashboard-title {
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        /* Chart date range label */
        .chart-date-range {
            font-size: 0.8rem;
            color: #666;
            font-weight: normal;
            margin-left: 10px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: stretch;
            }
            .compact-date-bar {
                border-radius: 20px;
                padding: 15px;
                width: 100%;
            }
            .compact-date-input {
                width: 100%;
            }
            .date-range-badge {
                width: 100%;
                text-align: center;
            }
            .chart-date-range {
                display: block;
                margin-left: 0;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <!-- Header with Title and Compact Date Picker -->
    <div class="dashboard-header">
        <h1 class="h2 dashboard-title">
            <i class="bi bi-graph-up me-2" style="color: #FF6600;"></i>
            Ads Analytics Dashboard
        </h1>
        
        <form method="GET" action="" id="dateRangeForm" style="display: inline-block;">
            <div class="compact-date-bar">
                <span class="date-range-badge">
                    <i class="bi bi-calendar-range me-2"></i>
                    <span class="d-none d-sm-inline">Date Range:</span>
                </span>
                
                <input type="date" 
                       name="start_date" 
                       class="compact-date-input" 
                       value="<?php echo $start_date; ?>"
                       max="<?php echo date('Y-m-d'); ?>"
                       title="Start Date">
                
                <span style="color: white;">→</span>
                
                <input type="date" 
                       name="end_date" 
                       class="compact-date-input" 
                       value="<?php echo $end_date; ?>"
                       max="<?php echo date('Y-m-d'); ?>"
                       title="End Date">
                
                <button type="submit" class="compact-apply-btn">
                    <i class="bi bi-funnel me-1"></i>
                    <span class="d-none d-sm-inline">Apply</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Date Range Info (Small badge under header on mobile) -->
    <div class="text-muted mb-4 text-center text-sm-start">
        <i class="bi bi-info-circle me-1"></i>
        Showing data from <strong><?php echo $formatted_start; ?></strong> 
        to <strong><?php echo $formatted_end; ?></strong>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <!-- Total Ads -->
        <div class="col-xl-4 col-md-6">
            <div class="dashboard-card">
                <div class="card-icon" style="background: #e8f0fe; color: #1976d2;">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_ads']; ?></div>
                <div class="stat-label">Total Ads</div>
                <div class="d-flex mt-2">
                    <small class="text-success me-3">
                        <i class="bi bi-check-circle"></i> Active: <?php echo $stats['active_ads']; ?>
                    </small>
                    <small class="text-danger">
                        <i class="bi bi-x-circle"></i> Inactive: <?php echo $stats['inactive_ads']; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-xl-4 col-md-6">
            <div class="dashboard-card">
                <div class="card-icon" style="background: #fff4e5; color: #ed6c02;">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="stat-value"><?php echo $stats['paid_ads']; ?></div>
                <div class="stat-label">Paid Ads</div>
                <div class="d-flex mt-2">
                    <small class="text-success me-3">
                        <i class="bi bi-check-circle"></i> Paid: <?php echo $stats['paid_ads']; ?>
                    </small>
                    <small class="text-warning">
                        <i class="bi bi-clock"></i> Pending: <?php echo $stats['pending_payment']; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-xl-4 col-md-6">
            <div class="dashboard-card">
                <div class="card-icon" style="background: #e3f2e8; color: #2e7d32;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> <?php echo $formatted_start; ?> - <?php echo $formatted_end; ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Revenue & Performance Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-line me-2" style="color: #FF6600;"></i>
                        Monthly Revenue
                        <span class="chart-date-range">
                            (Filtered: <?php echo $formatted_start; ?> - <?php echo $formatted_end; ?>)
                        </span>
                    </h5>
                    <span class="badge bg-light text-dark">₹<?php echo number_format(array_sum($monthly_revenue), 2); ?> total</span>
                </div>
                <canvas id="revenueChart" style="height: 320px; width: 100%;"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container" style="height: 400px; display: flex; flex-direction: column;">
                <h5 class="mb-3">
                    <i class="bi bi-pie-chart me-2" style="color: #FF6600;"></i>
                    Ad Type Distribution
                    <span class="chart-date-range d-block d-lg-inline">
                        (<?php echo $formatted_start; ?> - <?php echo $formatted_end; ?>)
                    </span>
                </h5>
                <div class="pie-chart-wrapper">
                    <canvas id="adTypeChart" style="height: 100%; width: 100%;"></canvas>
                </div>
                <div class="legend-wrapper">
                    <div class="legend-item">
                        <div class="legend-label">
                            <span class="legend-color" style="background: #1976d2;"></span>
                            Primary Ads
                        </div>
                        <span class="legend-value"><?php echo $ad_types['primary']; ?></span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-label">
                            <span class="legend-color" style="background: #2e7d32;"></span>
                            Secondary Ads
                        </div>
                        <span class="legend-value"><?php echo $ad_types['secondary']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Accounting Report Component -->
    <?php include 'components/accounting_report.php'; ?>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($monthly_revenue); ?>,
            borderColor: '#FF6600',
            backgroundColor: 'rgba(255, 102, 0, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#FF6600',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { 
                callbacks: {
                    label: function(context) {
                        return '₹' + context.raw.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f0f0f0' },
                ticks: {
                    callback: function(value) {
                        return '₹' + value;
                    }
                }
            }
        }
    }
});

// Ad Type Chart
const typeCtx = document.getElementById('adTypeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Primary Ads', 'Secondary Ads'],
        datasets: [{
            data: <?php echo json_encode(array_values($ad_types)); ?>,
            backgroundColor: ['#1976d2', '#2e7d32'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '60%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.raw;
                    }
                }
            }
        },
        layout: {
            padding: {
                top: 5,
                bottom: 5,
                left: 5,
                right: 5
            }
        }
    }
});

// Form validation
document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
    const startDate = new Date(this.start_date.value);
    const endDate = new Date(this.end_date.value);
    
    if (startDate > endDate) {
        e.preventDefault();
        alert('Start date cannot be after end date!');
    }
});
</script>

<?php
include 'components/footer.php';
$conn->close();
?>