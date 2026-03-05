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

// Get date range filter from URL (default: last 12 months)
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : 'year';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-12 months'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// ============== SUMMARY STATISTICS ==============
$stats = [];

// Total ads count
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management");
$stats['total_ads'] = $result->fetch_assoc()['total'];

// Active ads count
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE is_active = 1");
$stats['active_ads'] = $result->fetch_assoc()['total'];

// Inactive ads count
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE is_active = 0");
$stats['inactive_ads'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(price) as total FROM ads_management WHERE payment_status = 1");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Paid ads count (payment_status = 1)
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE payment_status = 1");
$stats['paid_ads'] = $result->fetch_assoc()['total'];

// Pending payment ads (payment_status = 0)
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE payment_status = 0");
$stats['pending_payment'] = $result->fetch_assoc()['total'];

// Primary ads (ad_type = 1)
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE ad_type = 1");
$stats['primary_ads'] = $result->fetch_assoc()['total'];

// Secondary ads (ad_type = 2)
$result = $conn->query("SELECT COUNT(*) as total FROM ads_management WHERE ad_type = 2");
$stats['secondary_ads'] = $result->fetch_assoc()['total'];

// Current month revenue
$current_month = date('Y-m');
$result = $conn->query("SELECT SUM(price) as total FROM ads_management WHERE payment_status = 1 AND DATE_FORMAT(created_at, '%Y-%m') = '$current_month'");
$stats['current_month_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Previous month revenue
$prev_month = date('Y-m', strtotime('-1 month'));
$result = $conn->query("SELECT SUM(price) as total FROM ads_management WHERE payment_status = 1 AND DATE_FORMAT(created_at, '%Y-%m') = '$prev_month'");
$stats['prev_month_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Calculate growth percentage
if ($stats['prev_month_revenue'] > 0) {
    $stats['revenue_growth'] = round(($stats['current_month_revenue'] - $stats['prev_month_revenue']) / $stats['prev_month_revenue'] * 100, 1);
} else {
    $stats['revenue_growth'] = $stats['current_month_revenue'] > 0 ? 100 : 0;
}

// ============== MONTHLY REVENUE CHART DATA ==============
$monthly_revenue = [];
$months = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $months[] = $month_name;
    
    $result = $conn->query("SELECT SUM(price) as total FROM ads_management 
                           WHERE payment_status = 1 
                           AND DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    $revenue = $result->fetch_assoc()['total'] ?? 0;
    $monthly_revenue[] = $revenue;
}

// ============== AD TYPE DISTRIBUTION ==============
$ad_types = [
    'primary' => $stats['primary_ads'],
    'secondary' => $stats['secondary_ads']
];

// ============== TOP CLIENTS ==============
$top_clients = [];
$result = $conn->query("SELECT client_name, COUNT(*) as ad_count, SUM(price) as total_spent 
                       FROM ads_management 
                       GROUP BY client_name 
                       ORDER BY total_spent DESC 
                       LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $top_clients[] = $row;
}

// ============== RECENT ADS ==============
$recent_ads = [];
$result = $conn->query("SELECT ad_title, client_name, price, payment_status, is_active, created_at 
                       FROM ads_management 
                       ORDER BY created_at DESC 
                       LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $recent_ads[] = $row;
}
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
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            height: 400px;
        }
        .badge-active {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-paid {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .table-hover tbody tr:hover {
            background: #f1f5f9;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h2">
            <i class="bi bi-graph-up me-2" style="color: #FF6600;"></i>
            Ads Analytics Dashboard
        </h1>
    </div>

    <!-- Summary Cards - Now only 3 cards -->
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

        <!-- Active Campaigns -->
        <div class="col-xl-4 col-md-6">
            <div class="dashboard-card">
                <div class="card-icon" style="background: #e3f2e8; color: #2e7d32;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-value"><?php echo $stats['active_ads']; ?></div>
                <div class="stat-label">Active Campaigns</div>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Currently running
                </small>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-xl-4 col-md-6">
            <div class="dashboard-card">
                <div class="card-icon" style="background: #fff4e5; color: #ed6c02;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
                <div class="d-flex mt-2">
                    <small class="<?php echo $stats['revenue_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <i class="bi bi-arrow-<?php echo $stats['revenue_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo $stats['revenue_growth']; ?>% vs last month
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue & Performance Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2" style="color: #FF6600;"></i> Revenue Trend (Last 6 Months)</h5>
                    <span class="badge bg-light text-dark">₹<?php echo number_format(array_sum($monthly_revenue), 2); ?> total</span>
                </div>
                <canvas id="revenueChart" style="height: 320px; width: 100%;"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container">
                <h5 class="mb-3"><i class="bi bi-pie-chart me-2" style="color: #FF6600;"></i> Ad Type Distribution</h5>
                <canvas id="adTypeChart" style="height: 200px;"></canvas>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span><span class="badge bg-primary" style="background: #1976d2;">&nbsp;&nbsp;</span> Primary Ads</span>
                        <strong><?php echo $ad_types['primary']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge bg-success" style="background: #2e7d32;">&nbsp;&nbsp;</span> Secondary Ads</span>
                        <strong><?php echo $ad_types['secondary']; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Clients Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="bi bi-trophy me-2" style="color: #FF6600;"></i> Top Clients</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Client Name</th>
                                <th>Total Ads</th>
                                <th>Total Spent</th>
                                <th>Average per Ad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_clients as $index => $client): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($client['client_name']); ?></strong></td>
                                <td><?php echo $client['ad_count']; ?></td>
                                <td>₹<?php echo number_format($client['total_spent'], 2); ?></td>
                                <td>₹<?php echo number_format($client['total_spent'] / $client['ad_count'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Ads Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="bi bi-clock-history me-2" style="color: #FF6600;"></i> Recent Ads</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_ads as $ad): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ad['ad_title']); ?></td>
                                <td><?php echo htmlspecialchars($ad['client_name']); ?></td>
                                <td>₹<?php echo number_format($ad['price'], 2); ?></td>
                                <td>
                                    <?php if ($ad['is_active']): ?>
                                        <span class="badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ad['payment_status']): ?>
                                        <span class="badge-paid">Paid</span>
                                    <?php else: ?>
                                        <span class="badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($ad['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

<?php
include 'components/footer.php';
$conn->close();
?>