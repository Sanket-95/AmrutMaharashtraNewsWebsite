<?php
// components/accounting_report.php
// Get date range from main page
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Pagination settings
$page = isset($_GET['report_page']) ? (int)$_GET['report_page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records count for pagination
$count_query = "SELECT COUNT(*) as total FROM ads_management WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch accounting data with pagination
$query = "SELECT 
            client_name,
            state,
            district,
            CASE 
                WHEN ad_type = 1 THEN 'Big Advertise'
                WHEN ad_type = 2 THEN 'Small Advertise'
                ELSE 'Unknown'
            END as ad_type_display,
            payment_method,
            DATE_FORMAT(created_at, '%d %b %Y') as formatted_date,
            created_at,
            transaction_id,
            price
          FROM ads_management 
          WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
          ORDER BY created_at DESC
          LIMIT $offset, $records_per_page";

$result = $conn->query($query);

// Calculate total price for the entire date range (not just current page)
$total_query = "SELECT SUM(price) as total_price FROM ads_management WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$total_result = $conn->query($total_query);
$total_price = $total_result->fetch_assoc()['total_price'] ?? 0;

// Fetch all data for PDF
$all_data_query = "SELECT 
                    client_name,
                    state,
                    district,
                    CASE 
                        WHEN ad_type = 1 THEN 'Big'
                        WHEN ad_type = 2 THEN 'Small'
                        ELSE 'Unknown'
                    END as ad_type_display,
                    payment_method,
                    DATE_FORMAT(created_at, '%d %b %Y') as formatted_date,
                    transaction_id,
                    price
                  FROM ads_management 
                  WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                  ORDER BY created_at DESC";
$all_data_result = $conn->query($all_data_query);

// Build data array
$pdf_rows = [];
while ($row = $all_data_result->fetch_assoc()) {
    $pdf_rows[] = [
        $row['client_name'],
        $row['state'] ?? '-',
        $row['district'] ?? '-',
        $row['ad_type_display'],
        $row['payment_method'] ?? '-',
        $row['formatted_date'],
        $row['transaction_id'] ?? '-',
        '₹ ' . number_format($row['price'], 2)
    ];
}
?>

<div class="row mt-5">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-file-text me-2" style="color: #FF6600;"></i>
                        Accounting Report
                    </h4>
                    <small class="text-muted">
                        <i class="bi bi-calendar-range me-1"></i>
                        <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>
                    </small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <span class="text-muted d-block small">Total Revenue</span>
                        <span class="h4 mb-0" style="color: #2e7d32;">₹<?php echo number_format($total_price, 2); ?></span>
                    </div>
                    <button onclick="downloadPDF()" class="btn" style="background: #dc3545; color: white; border-radius: 30px; padding: 10px 25px;">
                        <i class="bi bi-file-pdf me-2"></i>
                        <span class="d-none d-sm-inline">Download PDF</span>
                        <span class="d-sm-none">PDF</span>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="accountingTable">
                    <thead class="table-light">
                        <tr>
                            <th>Client Name</th>
                            <th>State</th>
                            <th>District</th>
                            <th>Ad Type</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th class="text-end">Price (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['state'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['district'] ?? '-'); ?></td>
                            <td>
                                <?php if ($row['ad_type_display'] == 'Big Advertise'): ?>
                                    <span class="badge" style="background: #1976d2; color: white;">Big</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #2e7d32; color: white;">Small</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['payment_method'] ?? '-'); ?></td>
                            <td><?php echo $row['formatted_date']; ?></td>
                            <td><small><?php echo htmlspecialchars($row['transaction_id'] ?? '-'); ?></small></td>
                            <td class="text-end fw-bold">₹<?php echo number_format($row['price'], 2); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="8" class="text-center py-4">No records found for selected date range</td></tr>';
                        }
                        ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">Total:</th>
                            <th class="text-end fw-bold" style="color: #2e7d32;">₹<?php echo number_format($total_price, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
                <div class="text-muted small">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
                </div>
                <nav aria-label="Accounting report pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- First page -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=1" aria-label="First">
                                <i class="bi bi-chevron-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- Previous page -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        
                        <!-- Next page -->
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=<?php echo $page + 1; ?>" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        
                        <!-- Last page -->
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=<?php echo $total_pages; ?>" aria-label="Last">
                                <i class="bi bi-chevron-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Records per page selector -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Show:</span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="changeRecordsPerPage(this.value)">
                        <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include jsPDF for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
function changeRecordsPerPage(value) {
    window.location.href = '?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_page=1&per_page=' + value;
}

function downloadPDF() {
    // Create PDF with standard settings
    const doc = new jspdf.jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4'
    });
    
    // Use standard font
    doc.setFont('helvetica');
    doc.setFontSize(16);
    
    // Add title
    doc.setTextColor(255, 102, 0);
    doc.text('Accounting Report', 20, 20);
    
    // Add date range
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text('Date Range: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>', 20, 30);
    
    // Add generation date
    doc.text('Generated on: <?php echo date('d M Y H:i:s'); ?>', 20, 37);
    
    // Add total records
    doc.text('Total Records: <?php echo $total_records; ?>', 20, 44);
    
    // Add total revenue
    doc.text('Total Revenue: ₹ <?php echo number_format($total_price, 2); ?>', 20, 51);
    
    // Table data
    const tableData = <?php echo json_encode($pdf_rows); ?>;
    
    // Add table
    doc.autoTable({
        head: [['Client', 'State', 'District', 'Type', 'Payment', 'Date', 'Transaction ID', 'Price']],
        body: tableData,
        startY: 58,
        theme: 'striped',
        headStyles: {
            fillColor: [102, 126, 234],
            textColor: 255,
            fontSize: 9,
            fontStyle: 'bold'
        },
        foot: [['', '', '', '', '', '', 'Total:', '₹ <?php echo number_format($total_price, 2); ?>']],
        footStyles: {
            fillColor: [240, 240, 240],
            textColor: [46, 125, 50],
            fontSize: 9,
            fontStyle: 'bold'
        },
        columnStyles: {
            7: { halign: 'right' }
        },
        styles: {
            fontSize: 8,
            font: 'helvetica',
            cellPadding: 3
        },
        margin: { top: 58, right: 15, bottom: 20, left: 15 },
        didDrawPage: function(data) {
            // Add page number
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(
                'Page ' + data.pageNumber,
                data.settings.margin.left,
                doc.internal.pageSize.height - 10
            );
        }
    });
    
    // Save PDF
    doc.save('accounting_report_<?php echo date('Y-m-d'); ?>.pdf');
}
</script>

<style>
/* Mobile responsive styles for accounting report */
@media (max-width: 768px) {
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination .page-link {
        padding: 0.3rem 0.6rem;
        font-size: 0.85rem;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        text-align: left;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        width: 100%;
        margin-bottom: 10px;
    }
    
    table.dataTable thead th {
        font-size: 12px;
        white-space: nowrap;
    }
    
    table.dataTable tbody td {
        font-size: 11px;
        padding: 8px 5px;
    }
    
    .badge {
        font-size: 10px;
        padding: 3px 6px;
    }
    
    /* Pagination mobile styles */
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: center !important;
        gap: 15px;
    }
    
    .pagination-sm {
        margin: 0 auto;
    }
    
    .form-select-sm {
        width: 100% !important;
    }
}

/* Pagination styling */
.page-link {
    color: #667eea;
    border: none;
    margin: 0 2px;
    border-radius: 8px !important;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.page-item.active .page-link {
    background: #667eea;
    border-color: #667eea;
    color: white;
    font-weight: 600;
}

.page-item.disabled .page-link {
    background: #f8f9fa;
    color: #aaa;
    opacity: 0.7;
}

/* Records per page selector */
.form-select-sm {
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    padding: 0.4rem 2rem 0.4rem 1rem;
    font-size: 0.85rem;
    cursor: pointer;
}

.form-select-sm:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>