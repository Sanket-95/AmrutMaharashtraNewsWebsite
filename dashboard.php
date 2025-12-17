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

// Get selected dates - use GET instead of POST to avoid resubmission on refresh
$from_date = $_GET['from_date'] ?? $default_from_date;
$to_date = $_GET['to_date'] ?? $default_to_date;
?>

<div class="container-fluid mt-3 px-2 px-md-3">
    <!-- Compact datepicker in top-right corner -->
    <div class="d-flex justify-content-end mb-3">
        <form method="GET" action="" class="compact-datepicker">
            <div class="d-flex align-items-center gap-1">
                <input type="date" class="form-control form-control-sm date-input" id="from_date" name="from_date" 
                       value="<?php echo htmlspecialchars($from_date); ?>" 
                       max="<?php echo date('Y-m-d'); ?>">
                
                <span class="text-muted mx-1">to</span>
                
                <input type="date" class="form-control form-control-sm date-input" id="to_date" name="to_date" 
                       value="<?php echo htmlspecialchars($to_date); ?>" 
                       max="<?php echo date('Y-m-d'); ?>">
                
                <button type="submit" class="btn btn-primary btn-sm ms-1 px-2 px-md-3">
                    Go
                </button>
                <button type="button" id="resetDates" class="btn btn-outline-secondary btn-sm px-2 px-md-3">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Main content area - include both components -->
    <div class="row">
        <!-- Graph Component -->
        <div class="col-12 mb-4">
            <?php include 'components/district_graph.php'; ?>
        </div>
        
        <!-- Table Component -->
        <div class="col-12">
            <?php include 'components/district_table.php'; ?>
        </div>
    </div>
</div>

<style>
.compact-datepicker {
    max-width: 500px;
}

.date-input {
    width: 120px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .date-input {
        width: 110px;
    }
    .container-fluid {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}

@media (max-width: 576px) {
    .compact-datepicker {
        width: 100%;
        max-width: 100%;
    }
    
    .compact-datepicker .d-flex {
        flex-wrap: wrap;
        gap: 0.5rem !important;
    }
    
    .date-input {
        width: calc(50% - 0.5rem);
        min-width: auto;
    }
    
    .compact-datepicker .text-muted {
        display: none;
    }
    
    .compact-datepicker .btn {
        width: calc(50% - 0.5rem);
    }
}

@media (min-width: 769px) and (max-width: 992px) {
    .date-input {
        width: 115px;
    }
}

@media (min-width: 1200px) {
    .date-input {
        width: 125px;
    }
}
</style>

<!-- JavaScript for date handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset button functionality
    document.getElementById('resetDates').addEventListener('click', function() {
        const today = new Date().toISOString().split('T')[0];
        const lastMonth = new Date();
        lastMonth.setMonth(lastMonth.getMonth() - 1);
        const lastMonthStr = lastMonth.toISOString().split('T')[0];
        
        document.getElementById('from_date').value = lastMonthStr;
        document.getElementById('to_date').value = today;
        
        // Submit form after reset
        this.closest('form').submit();
    });
    
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
});
</script>

<?php
include 'components/footer.php';
?>