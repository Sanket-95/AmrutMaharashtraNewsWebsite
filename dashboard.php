<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
include 'components/db_config.php';

// Get user info
$user_name = $_SESSION['name'] ?? 'User';

// Set default date range
$default_to_date = date('Y-m-d');
$default_from_date = date('Y-m-d', strtotime('-1 month'));

// Get selected dates and region - use GET instead of POST to avoid resubmission on refresh
$from_date = $_GET['from_date'] ?? $default_from_date;
$to_date = $_GET['to_date'] ?? $default_to_date;
$selected_region = $_GET['region'] ?? 'all';

// Define region options with Marathi names
$region_options = [
    'all' => 'सर्व प्रदेश',
    'Konkan' => 'कोकण',
    'Pune' => 'पुणे',
    'Sambhajinagar' => 'संभाजीनगर',
    'Nashik' => 'नाशिक',
    'Amravati' => 'अमरावती',
    'Nagpur' => 'नागपूर'
];
?>
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="container-fluid mt-3 px-2 px-md-3">
    <!-- Unified filter form -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <form method="GET" action="" class="d-flex align-items-center gap-2 unified-filter flex-wrap w-100">
            <!-- Region dropdown -->
            <div class="region-filter">
                <select class="form-select form-select-sm region-select" name="region" id="regionSelect">
                    <?php foreach ($region_options as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" 
                            <?php echo ($selected_region == $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Date inputs -->
            <div class="d-flex align-items-center gap-1 date-filter">
                <input type="text" class="form-control form-control-sm date-input" id="from_date" name="from_date" 
                       value="<?php echo htmlspecialchars($from_date); ?>" 
                       max="<?php echo date('Y-m-d'); ?>">
                
                <span class="text-muted mx-1">to</span>
                
                <input type="text" class="form-control form-control-sm date-input" id="to_date" name="to_date" 
                       value="<?php echo htmlspecialchars($to_date); ?>" 
                       max="<?php echo date('Y-m-d'); ?>">
            </div>
            <script>
                flatpickr("#from_date", {
                    dateFormat: "Y-m-d",    // Force YYYY-MM-DD format
                    maxDate: "today",        // Prevent future dates
                    defaultDate: "<?php echo $from_date; ?>"
                });

                flatpickr("#to_date", {
                    dateFormat: "Y-m-d",    // Force YYYY-MM-DD format
                    maxDate: "today",
                    defaultDate: "<?php echo $to_date; ?>"
                });
            </script>

            
            <!-- Action buttons -->
            <div class="d-flex align-items-center gap-1 action-buttons">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
                
                <button type="button" id="resetAll" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset All
                </button>
                
                <?php if ($selected_region != 'all'): ?>
                    <button type="button" id="resetRegionOnly" class="btn btn-outline-warning btn-sm px-3">
                        <i class="bi bi-globe me-1"></i> सर्व प्रदेश
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Main content area - include both components -->
    <div class="row">
        <!-- Graph Component -->
        <div class="col-12 mb-4">
            <?php include 'components/pie_chart.php'; ?>
        </div>
        
        <!-- Table Component -->
        <div class="col-12">
            <?php include 'components/district_table.php'; ?>
        </div>
    </div>
</div>

<style>
.unified-filter {
    max-width: 100%;
}

.region-filter {
    min-width: 200px;
    flex: 1;
}

.date-filter {
    flex: 2;
    min-width: 300px;
}

.action-buttons {
    flex: 0 0 auto;
}

.region-select {
    width: 100%;
}

.date-input {
    width: 140px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .unified-filter {
        flex-direction: column;
        align-items: stretch !important;
        gap: 1rem !important;
    }
    
    .region-filter,
    .date-filter,
    .action-buttons {
        width: 100%;
        min-width: 100%;
    }
    
    .date-filter {
        justify-content: center;
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .date-input {
        width: calc(50% - 0.5rem);
        min-width: auto;
    }
    
    .region-select {
        width: 100%;
    }
    
    .container-fluid {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}

@media (max-width: 576px) {
    .date-filter {
        flex-wrap: wrap;
        gap: 0.5rem !important;
    }
    
    .date-input {
        width: calc(50% - 0.5rem);
    }
    
    .date-filter .text-muted {
        display: none;
    }
    
    .action-buttons .btn {
        flex: 1;
        min-width: 120px;
    }
}

@media (min-width: 993px) and (max-width: 1200px) {
    .region-select {
        min-width: 180px;
    }
    
    .date-input {
        width: 130px;
    }
}

@media (min-width: 1201px) {
    .date-input {
        width: 150px;
    }
    
    .region-select {
        min-width: 200px;
    }
}

/* Active filter indicators */
.btn-outline-warning {
    border-color: #ffc107;
    color: #856404;
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    color: #212529;
}
</style>

<!-- JavaScript for unified filter handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset ALL button functionality
    document.getElementById('resetAll').addEventListener('click', function() {
        // Reset dates to default (last month to today)
        const today = new Date().toISOString().split('T')[0];
        const lastMonth = new Date();
        lastMonth.setMonth(lastMonth.getMonth() - 1);
        const lastMonthStr = lastMonth.toISOString().split('T')[0];
        
        document.getElementById('from_date').value = lastMonthStr;
        document.getElementById('to_date').value = today;
        
        // Reset region to "all"
        document.getElementById('regionSelect').value = 'all';
        
        // Submit form
        this.closest('form').submit();
    });
    
    // Reset region only button functionality
    const resetRegionOnlyBtn = document.getElementById('resetRegionOnly');
    if (resetRegionOnlyBtn) {
        resetRegionOnlyBtn.addEventListener('click', function() {
            document.getElementById('regionSelect').value = 'all';
            this.closest('form').submit();
        });
    }
    
    // Date validation
    const fromDate = document.getElementById('from_date');
    const toDate = document.getElementById('to_date');
    
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
    
    // Quick date presets (optional - can be added as buttons if needed)
    const quickDatePresets = {
        'last7days': function() {
            const today = new Date();
            const lastWeek = new Date(today);
            lastWeek.setDate(today.getDate() - 7);
            
            document.getElementById('from_date').value = lastWeek.toISOString().split('T')[0];
            document.getElementById('to_date').value = today.toISOString().split('T')[0];
            document.querySelector('form').submit();
        },
        'last30days': function() {
            const today = new Date();
            const lastMonth = new Date(today);
            lastMonth.setDate(today.getDate() - 30);
            
            document.getElementById('from_date').value = lastMonth.toISOString().split('T')[0];
            document.getElementById('to_date').value = today.toISOString().split('T')[0];
            document.querySelector('form').submit();
        },
        'last90days': function() {
            const today = new Date();
            const lastQuarter = new Date(today);
            lastQuarter.setDate(today.getDate() - 90);
            
            document.getElementById('from_date').value = lastQuarter.toISOString().split('T')[0];
            document.getElementById('to_date').value = today.toISOString().split('T')[0];
            document.querySelector('form').submit();
        }
    };
    
    // If you want to add quick date preset buttons, add them like this:
    // <button type="button" class="btn btn-sm btn-outline-info" onclick="quickDatePresets.last7days()">Last 7 Days</button>
    // <button type="button" class="btn btn-sm btn-outline-info" onclick="quickDatePresets.last30days()">Last 30 Days</button>
    // <button type="button" class="btn btn-sm btn-outline-info" onclick="quickDatePresets.last90days()">Last 90 Days</button>
    
    // Show active filter status
    function updateActiveFilterStatus() {
        const region = document.getElementById('regionSelect').value;
        const fromDateVal = document.getElementById('from_date').value;
        const toDateVal = document.getElementById('to_date').value;
        
        // You can add visual indicators here if needed
        console.log('Active filters:', {
            region: region,
            fromDate: fromDateVal,
            toDate: toDateVal
        });
    }
    
    // Update status on change
    document.getElementById('regionSelect').addEventListener('change', updateActiveFilterStatus);
    fromDate.addEventListener('change', updateActiveFilterStatus);
    toDate.addEventListener('change', updateActiveFilterStatus);
    
    // Initialize status
    updateActiveFilterStatus();
});
</script>

<?php
include 'components/footer.php';
?>